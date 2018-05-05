<?php

/**
 * Class Buddha_Http_Session
 */
class Buddha_Http_Session
{
    protected static $_instance;

    /**
     * @param $options
     * @return Buddha_Http_Session
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


    public static function getSession($name = 'buddha_checkcode')
    {
        return $_SESSION['buddha_checkcode'];
    }

    /**
     * @param $postcode string
     * @return bool
     */
    public static function checkCaptcha($postcode)
    {
        $ckcode = Buddha_Http_Session::getSession('buddha_checkcode');
        if (!$postcode || !$ckcode)
            return FALSE;

        return $ckcode == $postcode;
    }


}