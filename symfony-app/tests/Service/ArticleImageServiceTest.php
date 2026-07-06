<?php

namespace App\Tests\Service;

use App\Service\ArticleImageService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ArticleImageServiceTest extends TestCase
{
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }
    }

    public function testStoreRejectsNonImage(): void
    {
        $path = $this->makeTempFile('not an image');
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->never())->method('executeStatement');

        $this->expectException(\RuntimeException::class);
        (new ArticleImageService($conn))->store($path);
    }

    public function testStoreResizesAndStoresBlobKeyedByHash(): void
    {
        $path = $this->makePng(width: 800, height: 400);

        $storedParams = null;
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('executeStatement')
            ->willReturnCallback(function (string $sql, array $params) use (&$storedParams) {
                $this->assertStringContainsString('INSERT INTO images', $sql);
                $storedParams = $params;
                return 1;
            });

        $link = (new ArticleImageService($conn))->store($path);

        // Link is img/l/{md5 of the re-encoded bytes}
        $this->assertMatchesRegularExpression('#^img/l/[0-9a-f]{32}$#', $link);
        $this->assertSame('img/l/' . $storedParams[0], $link);

        // Blob round-trips through the legacy utf8 expand/decode and is
        // resized so the longest side is 600px (landscape 800x400 -> 600x300)
        $raw = mb_convert_encoding($storedParams[1], 'ISO-8859-1', 'UTF-8');
        $this->assertSame($storedParams[0], md5($raw));
        $image = imagecreatefromstring($raw);
        $this->assertSame(600, imagesx($image));
        $this->assertSame(300, imagesy($image));
    }

    public function testStoreKeepsSmallImagesAtOriginalSize(): void
    {
        $path = $this->makePng(width: 200, height: 100);

        $storedParams = null;
        $conn = $this->createMock(Connection::class);
        $conn->method('executeStatement')
            ->willReturnCallback(function (string $sql, array $params) use (&$storedParams) {
                $storedParams = $params;
                return 1;
            });

        (new ArticleImageService($conn))->store($path);

        $raw = mb_convert_encoding($storedParams[1], 'ISO-8859-1', 'UTF-8');
        $image = imagecreatefromstring($raw);
        $this->assertSame(200, imagesx($image));
        $this->assertSame(100, imagesy($image));
    }

    // ---- Helpers ----

    private function makeTempFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'wmffl_img_svc');
        file_put_contents($path, $contents);
        $this->tempFiles[] = $path;

        return $path;
    }

    private function makePng(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 30, 90, 160));

        $path = tempnam(sys_get_temp_dir(), 'wmffl_img_svc');
        imagepng($image, $path);
        $this->tempFiles[] = $path;

        return $path;
    }
}
