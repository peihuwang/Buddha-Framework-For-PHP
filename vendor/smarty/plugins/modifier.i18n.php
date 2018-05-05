<?php

function smarty_modifier_i18n($string, $default_lang= null)
{

    if(@$_COOKIE['lang']==NULL){
        $_COOKIE['lang']='zh_CN';
    }

    if ($default_lang === null) {
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
