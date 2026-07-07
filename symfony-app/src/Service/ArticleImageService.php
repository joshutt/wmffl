<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * Ports the article-image handling from football/utils/ImageProcessor.php
 * (the DB-storage path used by the member publish flow): resize to 600px
 * max, re-encode, store the blob in the images table. The image is then
 * served at /img/l/{hash} by legacy img.php.
 */
class ArticleImageService
{
    private const MAX_SIZE = 600;

    public function __construct(
        private Connection $connection
    ) {
    }

    private const MAX_DOWNLOAD_BYTES = 10 * 1024 * 1024;
    private const FETCH_TIMEOUT_SECONDS = 10;

    /**
     * Resize, re-encode and store an image from a local file or URL,
     * returning the link path (img/l/{hash}) to put on the article.
     *
     * A remote URL is downloaded through a hardened fetch (SSRF-guarded:
     * http/https only, resolved to a public IP, no redirects, size-capped)
     * to a local temp file; only that local file is ever handed to GD.
     *
     * @throws \RuntimeException when the source is not a usable image
     */
    public function store(string $source): string
    {
        $tempFile = null;
        if (preg_match('#^https?://#i', $source)) {
            $tempFile = $this->fetchRemoteImage($source);
            $source = $tempFile;
        }

        try {
            return $this->storeLocalFile($source);
        } finally {
            if ($tempFile !== null && is_file($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    private function storeLocalFile(string $source): string
    {
        $info = @getimagesize($source);
        if ($info === false) {
            throw new \RuntimeException('Provide a full URL to a JPEG, GIF or PNG image');
        }

        [$width, $height, $type] = $info;

        $image = match ($type) {
            IMAGETYPE_GIF => @imagecreatefromgif($source),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_PNG => @imagecreatefrompng($source),
            IMAGETYPE_BMP => @imagecreatefrombmp($source),
            default => false,
        };
        if ($image === false) {
            throw new \RuntimeException('Provide a full URL to a JPEG, GIF or PNG image');
        }

        $resized = $this->resize($image, $height, $width);

        ob_start();
        if ($type === IMAGETYPE_JPEG) {
            imagejpeg($resized);
        } else {
            imagepng($resized);
        }
        $data = ob_get_clean();

        $hash = md5($data);

        // Legacy quirk kept for compatibility: blobs are stored
        // latin-1-to-utf-8 expanded because img.php utf8_decode()s on read
        $this->connection->executeStatement(
            'INSERT INTO images (url, fullImage, smallImage) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE smallImage = ?',
            [$hash, mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1'), null, null],
            [ParameterType::STRING, ParameterType::LARGE_OBJECT, ParameterType::NULL, ParameterType::NULL]
        );

        return "img/l/$hash";
    }

    private function resize(\GdImage $image, int $height, int $width): \GdImage
    {
        $percent = 1.0;
        if ($width >= $height && $width > self::MAX_SIZE) {
            $percent = self::MAX_SIZE / $width;
        } elseif ($height > self::MAX_SIZE) {
            $percent = self::MAX_SIZE / $height;
        }

        $newWidth = (int) ($width * $percent);
        $newHeight = (int) ($height * $percent);
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $resized;
    }

    /**
     * Download a remote image URL to a local temp file with SSRF protections:
     * only http/https, the host must resolve to a public IP, the connection is
     * pinned to that validated IP (blocking DNS-rebinding), redirects are
     * refused, and the body is size-capped. The caller deletes the temp file.
     *
     * @throws \RuntimeException on any disallowed or failed fetch
     */
    private function fetchRemoteImage(string $url): string
    {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['host']) || empty($parts['scheme'])) {
            throw new \RuntimeException('Provide a valid image URL');
        }

        $scheme = strtolower($parts['scheme']);
        if ($scheme !== 'http' && $scheme !== 'https') {
            throw new \RuntimeException('Image URL must use http or https');
        }

        $host = $parts['host'];
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
        $ip = $this->resolvePublicIp($host);

        $tempFile = tempnam(sys_get_temp_dir(), 'articleimg_');
        if ($tempFile === false) {
            throw new \RuntimeException('Could not process the image');
        }

        $handle = fopen($tempFile, 'wb');
        if ($handle === false) {
            @unlink($tempFile);
            throw new \RuntimeException('Could not process the image');
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            // Pin the hostname to the IP we validated so a second DNS lookup
            // inside curl can't swing to an internal address (DNS rebinding).
            CURLOPT_RESOLVE => ["$host:$port:$ip"],
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => self::FETCH_TIMEOUT_SECONDS,
            CURLOPT_TIMEOUT => self::FETCH_TIMEOUT_SECONDS,
            CURLOPT_FILE => $handle,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_MAXFILESIZE => self::MAX_DOWNLOAD_BYTES,
            // Enforce the size cap even when no Content-Length is sent.
            CURLOPT_NOPROGRESS => false,
            CURLOPT_PROGRESSFUNCTION => static function ($ch, $dlTotal, $dlNow) {
                return $dlNow > self::MAX_DOWNLOAD_BYTES ? 1 : 0;
            },
        ]);

        $ok = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        fclose($handle);

        if ($ok === false || $status < 200 || $status >= 300) {
            @unlink($tempFile);
            throw new \RuntimeException('Could not download the image URL');
        }

        return $tempFile;
    }

    /**
     * Resolve a hostname to a single IP address that is guaranteed to be
     * public, rejecting private, loopback, link-local and reserved ranges
     * (IPv4 and IPv6). Every resolved address must be public — if a host
     * has any internal address we refuse it rather than pick a public one.
     *
     * @throws \RuntimeException when the host is unresolvable or non-public
     */
    private function resolvePublicIp(string $host): string
    {
        // A literal IP in the URL still has to pass the public-range check.
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ips = [$host];
        } else {
            $v4 = gethostbynamel($host) ?: [];
            $v6 = array_column(@dns_get_record($host, DNS_AAAA) ?: [], 'ipv6');
            $ips = array_merge($v4, $v6);
        }

        if (!$ips) {
            throw new \RuntimeException('Could not resolve the image URL host');
        }

        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new \RuntimeException('Image URL host is not allowed');
            }
        }

        return $ips[0];
    }
}
