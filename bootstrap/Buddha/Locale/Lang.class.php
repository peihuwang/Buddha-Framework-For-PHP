<?php

class Buddha_Locale_Lang{
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
     * @param $string string
     * @param null $default_lang
     * @return mixed
     */
  public static function i18n($string, $default_lang= null){


      if(@$_COOKIE['lang']==NULL){
          $_COOKIE['lang']='zh_CN';
      }


      if ($default_lang === NULL) {
          $default_lang = $_COOKIE['lang'];
      }



      if(!is_dir(PATH_ROOT."app/locale/{$default_lang}") or  $default_lang=='')
      {
          $default_lang='zh_CN';
      }



      if(file_exists(PATH_ROOT."app/locale/{$default_lang}/LC_MESSAGES/".TPL_DIR.".locate.php")){
          $lang= require PATH_ROOT."app/locale/{$default_lang}/LC_MESSAGES/".TPL_DIR.".locate.php";

          if(array_key_exists($string,$lang)){
              $string = $lang[$string];
              return $string;
          }else{
              return $string;
          }


      }
      return $string;
}

}