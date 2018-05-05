<?php

/**
 * Class Buddha_Http_Output
 */
class Buddha_Http_Output
{
    protected static $_instance;


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


    public static function makeValue($str)
    {
        echo $str;
        exit;
    }


    public static function makeWebfaceJson($data, $action, $errcode = 0, $errmsg = '成功', $other = '0')
    {


        $result['errcode'] = $errcode;
        $result['errmsg'] = $errmsg;


        if (isset($totalrecord) and $totalrecord == 0) {

            $result['list'] = array();
            $result['page'] = 0;
            $result['pagesize'] = 15;
            $result['totalrecord'] = 0;
            $result['totalpage'] = 0;

        }

        if (is_null($data)) {
            $result['data'] = (object)array();
        } else {
            $result['data'] = $data;
        }

        $result['other'] = $other;
        $result['action'] = $action;


        $result = str_replace("\\/", "/", json_encode($result, TRUE));
        $result = str_replace("\n", "", $result);


        if (!isset($_REQUEST['callback'])) {
            $callback = 'json';
        } else {
            $callback = $_REQUEST['callback'];
        }

        if ($callback == 'json') {
            echo $result;
            exit;
        }

        if ($callback != 'json' and $callback != 'xml') {
            echo $callback . "(" . $result . ")";
            exit;
        }

    }


    public static function makeJson($result = array())
    {
        header('content-type:application:json;charset=utf8');
        header('Content-type: application/json');
        $result = json_encode($result);
        $result = str_replace("\\/", "/", $result);

        if (!isset($_REQUEST['callback'])) {
            $callback = 'json';
        } else {
            $callback = $_REQUEST['callback'];
        }

        if ($callback == 'json') {
            echo $result;
            exit;
        }
        if ($callback != 'json' and $callback != 'xml') {
            echo $callback . "(" . $result . ")";
            exit;
        }

    }

    /**
     * @param $data
     * @param $action
     * @param int $errcode
     * @param string $errmsg
     */
    public static function result($data, $action, $errcode = 0, $errmsg = '成功')
    {
        $result['errcode'] = $errcode;
        $result['errmsg'] = $errmsg;
        $result['data'] = $data;
        $result['action'] = $action;


        $callback = Buddha_Http_Input::getParameter('callback') ? Buddha_Http_Input::getParameter('callback') : 'json';

        if ($callback == 'json') {
            $result = str_replace("\\/", "/", json_encode($result));
            echo $result;
            exit;
        } elseif ($callback == 'jsonp') {
            $result = str_replace("\\/", "/", json_encode($result));
            echo $callback . "(" . $result . ")";
            exit;
        } elseif ($callback == 'xml') {

            $xml = Buddha_Http_Output::arrayToXml($result);
            echo $xml;
            exit;
        } else {
            $result = str_replace("\\/", "/", json_encode($result));
            echo $callback . "(" . $result . ")";
            exit;
        }


    }


    public static function arrayToXml($arr, $sxe = NULL)
    {
        $str = '<xmlData></xmlData>';
        if ($sxe instanceOf SimpleXMLElement) {
            $xmlDoc = $sxe;
        } else {
            $xmlDoc = new SimpleXMLElement($str);
        }

        foreach ($arr as $key => $val) {
            if (is_array($val)) {

                if (is_numeric($key)) {  // 一般来说XML中的标签都是带字母的字符串
                    $child = $xmlDoc->addChild('num');  // 添加新标签，并返回指向该新标签的变量
                } else {
                    $child = $xmlDoc->addChild($key);
                }
                Buddha_Http_Output::arrayToXml($val, $child);  // 以当前子节点为对象，在该节点基础上插入子数组中的元素
            } else {
                $val = mb_convert_encoding($val, 'UTF-8');
                if (is_numeric($key)) {
                    $xmlDoc->addChild('num', $val);
                } else {
                    $xmlDoc->addChild($key, $val);
                }
            }
        }
        return $xmlDoc->asXML();
    }

}