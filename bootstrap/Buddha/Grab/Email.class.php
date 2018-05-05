<?php

class Buddha_Grab_Email{
    protected static $_instance;
    protected $smarty;

    /**
     * @param null $options
     * @return Buddha_Http_Head
     */
    public static function getInstance($options=null)
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
        $this->smarty = Smarty::getInstance(
            Buddha::getSmartyConfig()
        );
    }

   public static function getEmail($str) {                 //匹配邮箱内容
        $pattern = "/([a-z0-9\-_\.]+@[a-z0-9]+\.[a-z0-9\-_\.]+)/";
        preg_match_all($pattern,$str,$emailArr);
        return $emailArr[0];
    }

    public static function beforeAt($email){

        $arr = explode('@',$email);
        if(count($arr)<1){
            return  FALSE;
        }else{
            return strtolower($arr[0]);
        }


    }

    public static function afterAt($email){

        $arr = explode('@',$email);
        if(count($arr)<1){
            return  FALSE;
        }else{
            return strtolower($arr[1]);
        }


    }



}