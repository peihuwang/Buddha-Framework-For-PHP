<?php

/**
 * Class Buddha_Atom_File
 */
class Buddha_Atom_File
{
    protected static $_instance;

    /**
     * @param $options
     * @return Buddha_Http_Input
     */
    public static function getInstance($options)
    {
        if (self::$_instance === null) {
            $createObj = new self();
            if (is_array($options)) {
                foreach ($options as $option => $value) {
                    $createObj->$option = $value;
                }
            }
            self::$_instance = $createObj;
        }
        return self::$_instance;
    }

    public function __construct()
    {

    }

    public static function imgToBase64($image_file)
    {

        $image_info = getimagesize($image_file);
        return $base64_image_content = "data:{$image_info['mime']};base64," . chunk_split(base64_encode(file_get_contents($image_file)));

    }

    public static function checkBase64Img($base64string)
    {

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64string, $result)) {
            return 1;
        } else {
            return 0;
        }
    }

    public static function checkStringIsBase64($str)
    {

        $str = str_replace(' ', '+', $str);
        return $str == base64_encode(base64_decode($str)) ? 1 : 0;
    }

    public static function base64contentToImg($filePath, $base64_string)
    {

        $base64_string = str_replace(' ', '+', $base64_string);

        Buddha_Atom_Secury::setFileSecury($filePath);
        $ifp = fopen($filePath, "wb");
        fwrite($ifp, base64_decode($base64_string));
        fclose($ifp);

    }

    public static function getImageInfo($filePath)
    {
        return $imginfo = @getimagesize($filePath);
    }

    public static function resolveImageForRotate($filePath, $base64_string = NULL)
    {
        $imginfo = Buddha_Atom_File::getImageInfo($filePath);

        if ($base64_string != NULL) {
            Buddha_Atom_File::base64contentToImg($filePath, $base64_string);
            $imginfo = Buddha_Atom_File::getImageInfo($filePath);
            $image = imagecreatefromstring(base64_decode($base64_string));
        } else {
            $image = imagecreatefromstring(file_get_contents($filePath));
        }

        $exif = @exif_read_data($filePath);
        $fugai = 0;
        $fugaiimage = '';


        if (!empty($exif['Orientation'])) {
            switch ($exif['Orientation']) {
                case 8:
                    $fugai = 1;
                    $fugaiimage = imagerotate($image, 90, 0);
                    break;
                case 3:
                    $fugai = 1;
                    $fugaiimage = imagerotate($image, 180, 0);
                    break;
                case 6:
                    $fugai = 1;
                    $fugaiimage = imagerotate($image, -90, 0);
                    break;
            }
        }


        if ($fugai) {
            switch ($imginfo[2]) {
                case '1':
                    imagegif($fugaiimage, $filePath);
                    break;
                case '2':
                    imagejpeg($fugaiimage, $filePath);
                    break;
                case '3':
                    imagepng($fugaiimage, $filePath);
                    break;
                default :
                    imagejpeg($fugaiimage, $filePath);
                    break;
            }
        }
    }


}