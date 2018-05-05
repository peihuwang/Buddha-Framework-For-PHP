<?php

/**
 * Class Buddha_Http_Head
 */
class Buddha_Http_Head
{
    protected static $_instance;
    protected $smarty;

    /**
     * @param null $options
     * @return Buddha_Http_Head
     */
    public static function getInstance($options = null)
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
        $this->smarty = Smarty::getInstance(
            Buddha::getSmartyConfig()
        );
    }

    public static function redirect($msg, $url, $time = 2, $pagename = 'msg.html')
    {
        $msg = Buddha_Locale_Lang::i18n($msg);
        Buddha_Http_Head::getInstance()->showMsg($msg, $url, $time * 1000, 1, $pagename);
    }

    public function showMsg($msg, $url = '', $time = 0, $redirect = 0, $pagename = 'msg.html')
    {

        $this->smarty->assign('db_charset', 'utf8');
        $this->smarty->assign('msg', $msg);
        $this->smarty->assign('url', $url);
        $this->smarty->assign('time', $time);
        $this->smarty->assign('redirect', $redirect);
        $this->smarty->display("../public/" . $pagename);
        exit();
    }

    public static function redirectofmobile($msg, $url, $time = 1, $pagename = 'redirectofmobile.html')
    {
        $msg = Buddha_Locale_Lang::i18n($msg);
        Buddha_Http_Head::getInstance()->showtop($msg, $url, $time * 1000, 1, $pagename);
    }

    public function showtop($msg, $url = '', $time = 0, $redirect = 0, $pagename = 'error.html')
    {

        $this->smarty->assign('db_charset', 'utf8');
        $this->smarty->assign('msg', $msg);
        $this->smarty->assign('url', $url);
        $this->smarty->assign('time', $time);
        $this->smarty->assign('redirect', $redirect);
        $this->smarty->display("../public/" . $pagename);
        exit();
    }


    /**
     * @param $url
     * @param int $info
     * @param int $sec
     */
    public static function jump($url, $info = 0, $sec = 3)
    {
        if ($info == 0) {
            header("Location:$url");
        } elseif ($info == 1) {
            header("Refersh:$sec;URL=$url");
        } else {
            echo "<meta http-equiv=\"refresh\" content=" . $sec . ";URL=" . $url . ">";
            echo $info;
        }


        die;
    }


}