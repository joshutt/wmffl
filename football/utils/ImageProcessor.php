<?php

namespace utils;

use GdImage;

class ImageProcessor
{
    private $pathConfig;
    private $image;
    private $originalImagePath;
    private $imageFileName;
    private $imageType;


    public function __construct($pathConfig)
    {
        $this->pathConfig = $pathConfig;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return mixed
     */
    public function getImageFileName()
    {
        return $this->imageFileName;
    }

    /**
     * @return mixed
     */
    public function getImageType()
    {
        return $this->imageType;
    }

    /**
     * @param mixed $imageType
     */
    public function setImageType($imageType): void
    {
        $this->imageType = $imageType;
    }

    /**
     * Given a url of a jpeg or png create the image to work with and return it
     *
     * @param $filepath
     * @param $maxSize
     * @return void|null
     */
    public function createImageFromURL($filepath, $maxSize)
    {
        // Determine url type
        $this->originalImagePath = $filepath;
        $imgInfo = getimagesize($filepath);
        $type = $imgInfo[2];
        $width = $imgInfo[0];
        $height = $imgInfo[1];
        $allowedTypes = array(
            1,  // [] gif
            2,  // [] jpg
            3,  // [] png
            6   // [] bmp
        );

        $im = null;
        // If not allowed type return null
        if (!in_array($type, $allowedTypes)) {
            error_log('Type not allowed: '.$type);
            error_log(print_r($type, true));
            return null;
        }
        error_log(print_r($imgInfo, true));

        //
        switch ($type) {
            case 1 :
                error_log('Image is Gif');
                $this->imageType = 'gif';
                $im = imageCreateFromGif($filepath);
                break;
            case 2 :
                error_log('Image is JPG');
                $this->imageType = 'jpg';
                $im = imageCreateFromJpeg($filepath);
                break;
            case 3 :
                error_log('Image is png');
                $this->imageType = 'png';
                $im = imageCreateFromPng($filepath);
                break;
            case 6 :
                error_log('Image is bmp');
                $this->imageType = 'bmp';
                $im = imageCreateFromBmp($filepath);
                break;
        }
        $this->image = $this->resizeImage($im, $height, $width, $maxSize);
    }


    public function saveImage($conn = null, $format = null) {
        // If no format provided, use this one
        if (is_null($format)) {
            $format = $this->imageType;
        }

        if (is_null($conn)) {

            $dir = $this->pathConfig['wwwPath'] . '/' . $this->pathConfig['imagesPath'];
            error_log("Image Directory [$dir]");

            $fileName = hash('md5', $this->originalImagePath, false) . '.' . $format;
            error_log("Filename: [$fileName]");

            $fullName = $dir . '/' . $fileName;
            error_log("Fullname: [$fullName]");

            switch ($format) {
                case 'png':
                    error_log('Save as PNG image');
                    imagepng($this->image, $fullName);
                    break;
                case 'jpg':
                    error_log('Save as JPEG image');
                    imagejpeg($this->image, $fullName);
                    break;
                default:
                    error_log('Unsupported format');
            }

            $this->imageFileName = $this->pathConfig['imagesPath'] . '/' . $fileName;
            error_log("Short name: [$this->imageFileName]");

        } else {

            // New way of doing this
            ob_start();
            switch ($format) {
                case 'png':
                case 'gif':
                case 'bmp':
                    error_log('Save as PNG image');
                    imagepng($this->image);
                    break;
                case 'jpg':
                    error_log('Save as JPEG image');
                    imagejpeg($this->image);
                    break;
                default:
                    error_log('Unsupported format');
            }
            $image_data = ob_get_contents();
            error_log("Data: $image_data");
            ob_end_clean();
            $small_data = null;


            $null = NULL;
//            $fileName = md5($image_data) . ".$format";
            $fileName = md5($image_data);
            $stmt = $conn->prepare('INSERT INTO images (url, fullImage, smallImage) VALUES (?, ?, ?)');
            error_log("Filename: $fileName");
            error_log("Null: $null");
            error_log("Small Data: $small_data");
            $stmt->bind_param('sbb', $fileName, $null, $small_data);
            $stmt->send_long_data(1, utf8_encode($image_data));
            $stmt->execute();

            error_log($stmt->affected_rows . ' rows inserted');

            $this->imageFileName = "img/l/$fileName";
            error_log("Short name: [$this->imageFileName]");
        }
    }


    private function resizeImage($image, $currentHeight, $currentWidth, $maxSize) {
        $percent = 1.0;

        // If landscape and larger than max set percent to scale down width
        if ($currentWidth >= $currentHeight && $currentWidth > $maxSize) {
            $percent = $maxSize / $currentWidth;
        } elseif ($currentHeight > $maxSize) {  // if height too large scale down
            $percent = $maxSize / $currentHeight;
        }

        // Determine new sizes, create new image to that size
        $newwidth = $currentWidth * $percent;
        $newheight = $currentHeight * $percent;
        $newImage = imagecreatetruecolor($newwidth, $newheight);

        // scale old image to new image
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newwidth, $newheight, $currentWidth, $currentHeight);
        return $newImage;
    }
}