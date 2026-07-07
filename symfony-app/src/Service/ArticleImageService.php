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

    /**
     * Resize, re-encode and store an image from a local file or URL,
     * returning the link path (img/l/{hash}) to put on the article.
     *
     * @throws \RuntimeException when the source is not a usable image
     */
    public function store(string $source): string
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
}
