<?php

/**
 * Class Buddha_Http_Input
 */
class Buddha_Http_Input
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

    /**
     * @param $value
     * @param string $method
     * @return string|mixed
     */
    public static function getParameter($value, $method = 'G')
    {

        if ($method == 'G' && isset($_GET[$value])) {
            $return_value = $_GET[$value];
        }else{
            $return_value = isset($_POST[$value]) ? $_POST[$value] : '';
        }


        $out_arr = array();
        preg_match('/{.*}/', $return_value, $out_arr);

        if (!empty($out_arr) AND get_magic_quotes_gpc()) {
            $return_value = stripslashes($return_value);
        }

        if(strtotime($value)=='pagesize'){
            $return_value = Buddha_Atom_Secury::getMaxPageSize($return_value);

        }
        return $return_value;

    }

    public static function checkParameter($arr)
    {
        $return = 0;
        if (count($arr) < 1) {
            $return = 1;
        }

        foreach ($arr as $v) {
            if (!isset($_REQUEST[$v])) {
                $return = 1;
                break;
            }

        }

        foreach ($arr as $v) {
            if (isset($_REQUEST[$v]) and $_REQUEST[$v] == '') {
                $return = 1;
                break;
            }

        }

        return $return;

    }

    public static function getSameName()
    {
        $id = array();
        $input = file_get_contents("php://input");
        $input_arr = explode('&', $input);
        if (count($input_arr) and is_array($input_arr)) {
            foreach ($input_arr as $k => $v) {
                $v_arr = explode('=', $v);
                $id[] = $v_arr[1];
            }
        }

        return $id;
    }


    public static function isPost()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            return TRUE;
        } else {
            return FALSE;
        }

    }

    public static function isGET()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            return TRUE;
        } else {
            return FALSE;
        }

    }


}