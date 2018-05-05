<?php

/**
 * Class Buddha_Atom_Html
 */
class Buddha_Atom_Html
{
    protected static $_instance;

    /**
     * @param $options
     * @return Buddha_Http_Input
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


    public static function tripHtmlTag($str)
    {


        $st = -1; //ʼ
        $et = -1; //
        $stmp = array();
        $stmp[] = "&nbsp;";
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $ss = substr($str, $i, 1);
            if (ord($ss) == 60) { //ord("<")==60
                $st = $i;
            }
            if (ord($ss) == 62) { //ord(">")==62
                $et = $i;
                if ($st != -1) {
                    $stmp[] = substr($str, $st, $et - $st + 1);
                }
            }
        }
        $str = str_replace($stmp, "", $str);
        return $str;


    }

    //html 代码转义
    public static function toConvert($var)
    {
        if (is_array($var)) {
            foreach ($var as $key => $value) {
                $var[$key] = Buddha_Atom_Html::HConvert($value);
            }
        } else {
            do {
                $clean = $var;
                $var = preg_replace('~&(?!(#[0-9]+|[a-z]+);)~is', '&amp;', $var);
                $var = preg_replace(array('~%0[0-8]~', '~%1[124-9]~', '~%2[0-9]~', '~%3[0-1]~', '~[\x00-\x08\x0b\x0c\x0e-\x1f]~'), '', $var);
            } while ($clean != $var);
            $var = str_replace(array('"', '\'', '<', '>', "\t", "\r"), array('&quot;', '&#39;', '&lt;', '&gt;', '', ''), $var);
        }
        return $var;
    }

    public static function radioChecked($prefix, $index = 0, $number = 2)
    {
        for ($i = 0; $i < $number; $i++) {
            $GLOBALS[$prefix . $i] = $i == $index ? 'checked="checked"' : '';
        }
    }


}