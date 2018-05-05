<?php

class Buddha_Tool_Watermark extends Buddha_Base_Component{
    /**
     * 图片路径
     * @var string
     */
    var $_img = '';
    /**
     * 水印位置
     * @var int 0~9
     */
    var $_pos = 3;
    /**
     * jpeg图片质量
     * @var int 0~100
     */
    var $_jquality = 80;
    /**
     * 水印图片路径
     * @var string
     */
    var $_wimg = '';
    /**
     * 水印图片与原图片的融合度,数值越小越透明 (1到100)
     * @var int
     */
    var $_transition = 65;
    /**
     * 水印文字(支持中英文以及带有\r\n的跨行文字)
     * @var string
     */
    var $_text = '';
    /**
     * 水印文字大小
     * @var int
     */
    var $_tsize = 20;
    /**
     * 水印文字的字体
     * @var int
     */
    var $_tfont = '';
    /**
     * 水印字体的颜色值
     * @var int
     */
    var $_tcolor = '#ffffff';
    /**
     * 水印文字角度
     * @var int
     */
    var $text_angle = 0;
    var $t_x = 0;
    var $t_y = 0;
    /**
     * 原图宽
     */
    var $_rw = 0;
    /**
     * 原图高
     */
    var $_rh = 0;
    /**
     * 原图类型
     */
    var $_imgtype = '';
    /**
     * 水印宽
     */
    var $_ww = 0;
    /**
     * 水印高
     */
    var $_wh = 0;
    /**
     * 构造函数
     */
    function Watermark()
    {
        global $hsk_watertext, $hsk_waterfont, $hsk_waterfontsize, $hsk_waterfontcolor, $hsk_waterimg, $hsk_watertransition, $hsk_waterquality, $hsk_waterposition;
        $this->_text = $hsk_watertext ? $hsk_watertext : 'huosuke.com';
        $this->_tfont = strtolower(trim(PATH_ROOT.'images/font/'.$hsk_waterfont));
        $this->_tsize = (int)$hsk_waterfontsize;
        $this->_tcolor = $hsk_waterfontcolor;
        $this->_wimg = PATH_ROOT.'images/water/'.$hsk_waterimg;
        $this->_transition = $hsk_watertransition;
        $this->_jquality = $hsk_waterquality;
        $this->_pos = $hsk_waterposition;
    }
    /**
     * 设置图片路径
     */
    function setImg($var)
    {
        global $hsk_waterminsize, $hsk_watertype;
        if (empty($var) || !file_exists($var))
        {
            return FALSE;
        }
        $this->_img = strtolower(trim($var));
        $imginfo = @getimagesize($this->_img);
        list($minh, $minw) = explode("\t", $hsk_waterminsize);
        if (($minw && $imginfo[0] < $minw) || ($minh && $imginfo[1] < $minh))
        {
            return FALSE;
        }
        $this->_rw = $imginfo[0];
        $this->_rh = $imginfo[1];
        $this->_imgtype = $imginfo[2];
        $this->_im = $this->createimage($this->_imgtype, $this->_img);
        if ($hsk_watertype == '1')
        {
            $this->maketext($this->_im);
        }
        elseif ($hsk_watertype == '2')
        {
            $this->makeimage($this->_im);
        }
        return TRUE;
    }
    function createimage($type, $img_name)
    {
        switch($type)
        {
            case 1:
                $tmp_img = @imagecreatefromgif($img_name);
                break;
            case 2:
                $tmp_img = imagecreatefromjpeg($img_name);
                break;
            case 3:
                $tmp_img = imagecreatefrompng($img_name);
                break;
            default:
                $tmp_img = imagecreatefromstring($img_name);
        }
        return $tmp_img;
    }

    /**
     * 图片水印
     */
    function makeimage($_im)
    {
        if($this->_imgtype == 1 && (!function_exists('imagegif') || !function_exists('imagecreatefromgif'))) return FALSE;
        if (empty($this->_wimg) || !file_exists($this->_wimg))
        {
            return FALSE;
        }
        $imginfo = @getimagesize($this->_wimg);
        $_wim = $this->createimage($imginfo[2],$this->_wimg);
        $this->_ww = $imginfo[0];
        $this->_wh = $imginfo[1];
        $temp_wim = $this->getPosition('image');
        $wim_x = $temp_wim[0];
        $wim_y = $temp_wim[1];
        imagecopymerge($_im, $_wim, $wim_x, $wim_y, 0, 0, $this->_ww, $this->_wh, $this->_transition);
        @imagedestroy($_wim);
        $this->saveImg($_im);
    }
    /**
     * 文字水印
     */
    function maketext($_im)
    {
        if($this->_imgtype == 1 && (!function_exists('imagegif') || !function_exists('imagecreatefromgif'))) return FALSE;
        $textinfo = imagettfbbox($this->_tsize, $this->text_angle, $this->_tfont, $this->_text);
        $this->_ww = $textinfo[2] - $textinfo[6];
        $this->_wh = $textinfo[3] - $textinfo[7];
        $this->_text = $this->gb2utf8($this->_text);
        $temp_text = $this->getPosition('text');
        if(preg_match('~([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])~i', $this->_tcolor, $color))
        {
            $text_color = imagecolorallocate($_im, hexdec($color[1]), hexdec($color[2]), hexdec($color[3]));
        }
        else
        {
            $text_color = imagecolorallocate($_im, 255, 255, 255);
        }
        imagettftext($_im, $this->_tsize, $this->text_angle, $temp_text[0], $temp_text[1], $text_color, $this->_tfont, $this->_text);
        $this->saveImg($_im);
    }
    /**
     * 保存图片
     */
    function saveImg($_im)
    {
        switch($this->_imgtype)
        {
            case '1':
                imagegif($_im, $this->_img);
                break;
            case '2':
                imagejpeg($_im, $this->_img, $this->_jquality);
                break;
            case '3':
                imagepng($_im, $this->_img);
                break;
            default :
                imagejpeg($_im, $this->_img, $this->_jquality);
                break;
        }
    }
    /**
     * @水印位置
     */
    function getPosition($type = 'image')
    {
        if(($this->_rw < $this->_ww) || ($this->_rh < $this->_wh)) return FALSE;
        switch($this->_pos)
        {
            case 0:
                $posX = 0;
                $posY = $type == 'image' ? 0 : $this->_wh;
                break;
            case 1:
                $posX = ($this->_rw - $this->_ww) / 2;
                $posY = $type == 'image' ? 0 : $this->_wh;
                break;
            case 2:
                $posX = $this->_rw - $this->_ww;
                $posY = $type == 'image' ? 0 : $this->_wh;
                break;
            case 3:
                $posX = 0;
                $posY = ($this->_rh - $this->_wh) / 2;
                break;
            case 4:
                $posX = ($this->_rw - $this->_ww) / 2;
                $posY = ($this->_rh - $this->_wh) / 2;
                break;
            case 5:
                $posX = $this->_rw - $this->_ww;
                $posY = ($this->_rh - $this->_wh) / 2;
                break;
            case 6:
                $posX = 0;
                $posY = $this->_rh - $this->_wh;
                break;
            case 7:
                $posX = ($this->_rw - $this->_ww) / 2;
                $posY = $this->_rh - $this->_wh;
                break;
            case 8:
                $posX = $this->_rw - $this->_ww;
                $posY = $this->_rh - $this->_wh;
                break;
            default:
                $posX = rand(0, ($this->_rw - $this->_ww));
                $posY = rand(0, ($this->_rh - $this->_wh));
                break;
        }
        return array($posX, $posY);
    }
    function gb2utf8($gb)
    {
        if(!trim($gb)) return $gb;
        $filename = PATH_ROOT.'includes/encoding/gb-unicode.table';
        $tmp=file($filename);
        $codetable=array();
        while(list($key,$value)=each($tmp))
            $codetable[hexdec(substr($value,0,6))]=substr($value,7,6);

        $utf8='';
        while($gb)
        {
            if(ord(substr($gb,0,1))>127)
            {
                $tthis=substr($gb,0,2);
                $gb=substr($gb,2,strlen($gb)-2);
                $utf8.=$this->u2utf8(hexdec($codetable[hexdec(bin2hex($tthis))-0x8080]));
            }
            else
            {
                $tthis=substr($gb,0,1);
                $gb=substr($gb,1,strlen($gb)-1);
                $utf8.=$this->u2utf8($tthis);
            }
        }
        return $utf8;
    }
    function u2utf8($c)
    {
        $str='';
        if($c < 0x80)
        {
            $str.=$c;
        }
        elseif($c < 0x800)
        {
            $str.=chr(0xC0 | $c>>6);
            $str.=chr(0x80 | $c & 0x3F);
        }
        elseif($c < 0x10000)
        {
            $str.=chr(0xE0 | $c>>12);
            $str.=chr(0x80 | $c>>6 & 0x3F);
            $str.=chr(0x80 | $c & 0x3F);
        }
        elseif($c < 0x200000)
        {
            $str.=chr(0xF0 | $c>>18);
            $str.=chr(0x80 | $c>>12 & 0x3F);
            $str.=chr(0x80 | $c>>6 & 0x3F);
            $str.=chr(0x80 | $c & 0x3F);
        }
        return $str;
    }
}