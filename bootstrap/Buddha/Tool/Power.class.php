<?php

class Buddha_Tool_Power{
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
    public function __construct(){

    }

    /**
     * 对图片进行base64编码转换
     *
     * @param string $image_file
     * @return string
     */
    function getImageBase64Encode ($image_file)
    {
        $base64_image = '';
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }

    /**
     * 获取一个指定长度的随机字符串
     *
     * @param int $len
     * @return string
     */
    function getStringRandom ($len = 8)
    {
        $str = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $strLen = strlen($str);
        $randomString = '';
        if (!is_int($len) || $len <= 0) $len = 8;
        for ($i=0; $i<$len; $i++) {
            $randomString .= $str[rand(0, $strLen-1)];
        }

        return $randomString;
    }
    /**
     * 改变数组KEY
     *
     * @param array $array
     * @param mixed $field
     * @return array
     */
    function getArrayChangeKey ($array, $field)
    {
        $tmp = array();
        if (is_array($array)) {
            foreach ($array as $value) {
                $tmp[$value[$field]] = $value;
            }
        } else {
            return false;
        }

        return $tmp;
    }
}