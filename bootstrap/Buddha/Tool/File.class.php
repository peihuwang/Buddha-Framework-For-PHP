<?php

class Buddha_Tool_File extends Buddha_Base_Component{
    /**
     * Buddha_Tool_File Instance
     *
     * @var Buddha_Tool_File
     */
    protected static $_instance;
    /**
     * 实例化
     *
     * @static
     * @access	public
     * @return	object 返回对象
     */
    public static function getInstance($options)
    {
        if (self::$_instance === null) {
            $createObj=  new self();
            if (is_array($options))
            {
                foreach ($options as $option => $value)
                {
                    $createObj->$option = $value;
                }
            }
            self::$_instance =$createObj;
        }
        return self::$_instance;
    }
    /**
     * 构造
     *
     */
    public function __construct(){

    }



    public static function getRealSize($size) {
        if ($size < 1024) {
            return $size . ' Byte';
        }
        if ($size < 1048576) {
            return round($size / 1024, 2) . ' KB';
        }
        if ($size < 1073741824) {
            return round($size / 1048576, 2) . ' MB';
        }
        if ($size < 1099511627776) {
            return round($size / 1073741824, 2) . ' GB';
        }
    }
    /**
     * 获取文件后缀
     * @static
     * @access	public
     * @return string 返回字符串
     **/
    public static function fileExtenttion($filename){
        return strtolower(trim(substr(strrchr($filename, '.'), 1)));
    }
    /**
     * 列出文件目录返回数组
     *
     * @static
     * @access	public
     * @param	string $uri 文件目录位置
     * @param	string  $var  遍历目录位置
     * @param	array  $type  遍历的文件后缀语序数组 例如array('php')就是遍历后缀是.php的文件
     * @return	array 返回数组
     */
    public static function listFile($uri,$var, $type = array()) {
        $rt = array();
        if (is_dir($var)) {

            $rs = @opendir($var);

            while (($file = readdir($rs)) !== FALSE) {
                if ($file != '..' && $file != '.') {
                    if ($type && in_array(Buddha_Tool_File::fileExtenttion($file), $type)){

                        $rt[substr($file,0,strrpos($file, '.'))] = $uri.$file;
                    }
                }
            }


            return $rt;
        }
        return FALSE;
    }


    public static  function img2data( $source){


        return $imgdata=fread(fopen($source,'rb'),filesize($source));
    }

    public static  function data2img($source){
        $info=getimagesize($source);
        $mime= $info['mime'];
        $imgdata=fread(fopen($source,'rb'),filesize($source));
        header("content-type:$mime");
        echo $imgdata;



        return $imgdata=fread(fopen($source,'rb'),filesize($source));
    }


    public static function thumbImageSameWidth($img, $width, $height, $save_prefix = 'thumbnail_', $repath = '', $del = false) {
        $imginfo = @getimagesize($img);
        $hskwidth =  $width;
        $hskheight =  $height;
        switch ($imginfo[2]) {
            case 1:
                $tmp_img = @imagecreatefromgif($img);
                break;
            case 2:
                $tmp_img = imagecreatefromjpeg($img);
                break;
            case 3:
                $tmp_img = imagecreatefrompng($img);
                break;
            default:
                $tmp_img = imagecreatefromstring($img);
                break;
        }
        if ($repath) {
            $savepath = $repath;
        } else {
            if ($save_prefix) {
                $imgpath = substr($img, 0, strrpos($img, '/'));
                $filename = substr($img, strrpos($img, '/') + 1);
                $savepath = $imgpath . '/' . $save_prefix . $filename;
            } else {
                $savepath = $img;
            }
        }
        if (($height >= $imginfo[1] || !$height) && ($width >= $imginfo[0] || !$width)) {
            if ($save_prefix) {

                @copy($img, $savepath) || Buddha_Tool_File::writeFile($savepath,Buddha_Tool_File::readFile($img,'wb'));
                $del &&  Buddha_Tool_File::delete($img);

            }

            return str_replace(PATH_ROOT, '', $savepath);
        }


//源图对象
        $src_image = imagecreatefromstring(file_get_contents($img));
        $src_width = imagesx($src_image);
        $src_height = imagesy($src_image);

//生成等比例的缩略图
        $tmp_image_width = 0;
        $tmp_image_height = 0;



        $tmp_image_height =round($width*$src_height/$src_width);
        $tmpImage = imagecreatetruecolor($width, $tmp_image_height);
        imagecopyresampled($tmpImage, $src_image, 0, 0, 0, 0, $width, $tmp_image_height, $src_width, $src_height);




        switch ($imginfo[2]) {
            case '1':
                imagegif($tmpImage, $savepath);
                break;
            case '2':
                imagejpeg($tmpImage, $savepath);
                break;
            case '3':
                imagepng($tmpImage, $savepath);
                break;
            default :
                imagejpeg($tmpImage, $savepath);
                break;
        }


        return str_replace(PATH_ROOT, '', $savepath);
    }





    public static function thumbImage($img, $width, $height, $save_prefix = 'thumbnail_', $repath = '', $del = false) {
        $imginfo = @getimagesize($img);
        $hskwidth =  $width;
        $hskheight =  $height;
        switch ($imginfo[2]) {
            case 1:
                $tmp_img = @imagecreatefromgif($img);
                break;
            case 2:
                $tmp_img = imagecreatefromjpeg($img);
                break;
            case 3:
                $tmp_img = imagecreatefrompng($img);
                break;
            default:
                $tmp_img = imagecreatefromstring($img);
                break;
        }
        if ($repath) {
            $savepath = $repath;
        } else {
            if ($save_prefix) {
                $imgpath = substr($img, 0, strrpos($img, '/'));
                $filename = substr($img, strrpos($img, '/') + 1);
                $savepath = $imgpath . '/' . $save_prefix . $filename;
            } else {
                $savepath = $img;
            }
        }
        if (($height >= $imginfo[1] || !$height) && ($width >= $imginfo[0] || !$width)) {
            if ($save_prefix) {

                @copy($img, $savepath) || Buddha_Tool_File::writeFile($savepath,Buddha_Tool_File::readFile($img,'wb'));
                $del &&  Buddha_Tool_File::delete($img);

            }

            return str_replace(PATH_ROOT, '', $savepath);
        }


//源图对象
        $src_image = imagecreatefromstring(file_get_contents($img));
        $src_width = imagesx($src_image);
        $src_height = imagesy($src_image);

//生成等比例的缩略图
        $tmp_image_width = 0;
        $tmp_image_height = 0;
        if ($src_width / $src_height >= $width / $height) {
            $tmp_image_width = $width;
            $tmp_image_height = round($tmp_image_width * $src_height / $src_width);
        } else {
            $tmp_image_height = $height;
            $tmp_image_width = round($tmp_image_height * $src_width / $src_height);
        }

        $tmpImage = imagecreatetruecolor($tmp_image_width, $tmp_image_height);
        imagecopyresampled($tmpImage, $src_image, 0, 0, 0, 0, $tmp_image_width, $tmp_image_height, $src_width, $src_height);

     //添加白边
        $final_image = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($final_image, 255, 255, 255);
        imagefill($final_image, 0, 0, $color);

        $x = round(($width - $tmp_image_width) / 2);
        $y = round(($height - $tmp_image_height) / 2);

        imagecopy($final_image, $tmpImage, $x, $y, 0, 0, $tmp_image_width, $tmp_image_height);

        switch ($imginfo[2]) {
            case '1':
                imagegif($final_image, $savepath);
                break;
            case '2':
                imagejpeg($final_image, $savepath);
                break;
            case '3':
                imagepng($final_image, $savepath);
                break;
            default :
                imagejpeg($final_image, $savepath);
                break;
        }

        $save_prefix && $del && Buddha_Tool_File::delete($img);
        return str_replace(PATH_ROOT, '', $savepath);
    }


    //文件删除
    public static function delete($var) {
        return strpos($var, '..') === FALSE && is_file($var) && @unlink($var) ? TRUE : FALSE;
    }


    /**
     * @param $fileName
     * @return string
     */
    public static  function getExt($fileName)
    {
        $fileName = strtolower($fileName);

        $ext = explode ( ".", $fileName );
        $ext = $ext [count ( $ext ) - 1];
        return  strtolower ( $ext );

    }



    /**
     * 导出excel
     *
     * @static
     * @access	public
     * @param	string $filename 导出文件名
     * @param	string  $data  导出的数据字符串
     * @return	string 返回字符串
     */
    public static  function exportExcel($filename,$data) {
        header("Content-Type: application/vnd.ms-execl");
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=".$filename);
        header("Pragma: no-cache");
        header("Expires: 0");
        return $data;

    }

    //写文件
public  static function writeFile($filename, $content, $mode = 'ab', $chmod = 1) {
        strpos($filename, '..') !== FALSE && exit('Access Denied!');
        $fp = @fopen($filename, $mode);
        if ($fp) {
            flock($fp, LOCK_EX);
            fwrite($fp, $content);
            fclose($fp);
            $chmod && @chmod($filename, 0666);
            return TRUE;
        }
        return FALSE;
    }

//读取文件
public  static function readFile($filename, $mode = 'rb') {
        strpos($filename, '..') !== FALSE && exit('Access Denied!');
        if ($fp = @ fopen($filename, $mode)) {
            flock($fp, LOCK_SH);
            $filedata = @ fread($fp, filesize($filename));
            fclose($fp);
        }
        return $filedata;
    }


    public  static function writeCache($fileName, $content) {
        $cacheFile = PATH_ROOT . 'bootstrap/cache/cache_' . $fileName . '.php';
        $cacheContent = "<?php\r\n\r\n/** Buddha Cache File, DO NOT Modify! **/\r\n\r\n";
        $cacheContent .= $content;
        $cacheContent .= "\r\n?>";

        return Buddha_Tool_File::writeFile($cacheFile, $cacheContent, 'wb');

    }

}