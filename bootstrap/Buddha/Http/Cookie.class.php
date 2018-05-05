<?php

/**
 * Class Buddha_Http_Cookie
 */
class Buddha_Http_Cookie
{
    protected static $_instance;

    /**
     * @param $options
     * @return Buddha_Http_Cookie
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

    /**
     * @param $var string
     * @return string
     */
    public static function  getCookie($var)
    {
        $hsk_ckpre = Buddha::$buddha_array['cookie_pre'];
        $ckpre = $hsk_ckpre ? substr(md5($hsk_ckpre), 8, 6) . '_' : '';
        return isset($_COOKIE[$ckpre . $var]) ? $_COOKIE[$ckpre . $var] : '';
    }


    /**
     * 设置COOKIE信息
     *
     * @param $name string
     * @param $value string
     * @param $expire string 过期时间：-1：删除，0：即时，1：永久
     * @return Boolean 设置cookie是否成功
     */
    public static function setCookie($name, $value, $expire = 0, $httponly = true)
    {

        switch ($expire) {
            case 0:
                $expire = 0;
                break;
            case 1:
                $expire = Buddha::$buddha_array['buddha_timestamp'] + 31536000;
                break;
            case -1:
                $expire = Buddha::$buddha_array['buddha_timestamp'] - 31536000;
                break;
            default:
                $expire += Buddha::$buddha_array['buddha_timestamp'];
                break;
        }
        $hsk_ckpath = Buddha::$buddha_array['cookie_path'];
        !$hsk_ckpath && $hsk_ckpath = '/';
        $hsk_ckdomain = Buddha::$buddha_array['cookie_domain'];
        $secure = ($_SERVER['SERVER_PORT'] == '443') ? 1 : 0;
        $ckpre = Buddha::$buddha_array['cookie_pre'] ? substr(md5(Buddha::$buddha_array['cookie_pre']), 8, 6) . '_' : '';

        if (PHP_VERSION >= '5.2.0') {
            return setcookie($ckpre . $name, $value, $expire, $hsk_ckpath, $hsk_ckdomain, $secure, ($httponly ? 1 : 0));
        } else {
            return setcookie($ckpre . $name, $value, $expire, $hsk_ckpath, $hsk_ckdomain, $secure);
        }

    }


}