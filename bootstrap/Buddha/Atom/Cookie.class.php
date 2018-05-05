<?php

class Buddha_Atom_Cookie{
    protected static $_instance;

    /**
     * @param $options
     * @return Buddha_Http_Input
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
     * 判断cookie里是否存在某个key的值
     * @param $keystr
     * @return int
     * @author wph 2017-11-27
     */
    public static function isCookieHasValueByKey($keystr){

        if(isset($_COOKIE[$keystr]) AND Buddha_Atom_String::isValidString($_COOKIE[$keystr])) {
            return 1;
        }else{
            return 0;
        }

    }

    /**
     * 获取cookie里这个键值的内容值
     * @param $keystr
     * @return int
     * @author wph 2017-11-27
     */
    public static function getCookieValueByKey($keystr){

        if(Buddha_Atom_Cookie::isCookieHasValueByKey($keystr)){

            return $_COOKIE[$keystr];

        }else{

            return 0;
        }

    }



    public static function setCookieValueByKey($keystr,$value,$day=1){
        setcookie($keystr,$value, time()+3600*24*$day);
    }

}