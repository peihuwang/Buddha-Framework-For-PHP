<?php

/**
 * Class Buddha_Atom_Array
 */
class Buddha_Atom_Array
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


    public static function jsontoArray($json)
    {
        return json_decode($json, true);
    }

    public static function sort($arrays, $sort_key, $sort_order = SORT_ASC, $sort_type = SORT_NUMERIC)
    {
        if (is_array($arrays)) {
            foreach ($arrays as $array) {
                if (is_array($array)) {
                    $key_arrays[] = $array[$sort_key];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
        array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
        return $arrays;
    }

    /**
     * 插入新元素到数组头部
     * @param $arr
     * @param $new_id
     * @return mixed
     */
    public static function getInsertHeadElementArr($arr, $new_id)
    {

        Array_unshift($arr, $new_id);
        return $arr;
    }

    /**
     * 插入新元素到数组尾部
     * @param $arr
     * @param $new_id
     * @return mixed
     */
    public static function getInsertTailElementArr($arr, $new_id)
    {

        Array_push($arr, $new_id);
        return $arr;
    }


    public static function isValidArray($arr)
    {
        if (count($arr) and is_array($arr)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 返回数组客户人key 的数值
     * @param $arr
     * @param $key
     * @return string
     */
    public static function  getApiKeyOfValueByArrayString($arr, $key)
    {

        if (Buddha_Atom_Array::isValidArray($arr) and Buddha_Atom_Array::isKeyExists($key, $arr)) {

            if (Buddha_Atom_String::isValidString($arr[$key])) {
                return $arr[$key];
            } else {
                return '';
            }


        } else {
            return '';
        }


    }

    public static function isKeyExists($key, $arr)
    {

        if (strlen($key) < 1 or count($arr) == 0 or !is_array($arr)) {
            return 0;
        }

        if (array_key_exists($key, $arr)) {
            return 1;
        } else {
            return 0;
        }

    }

    /**
     * 返回数组中最大的数值
     * @param $arr
     * @return mixed
     */
    public static function getMaxValueFromArr($arr)
    {
        return max($arr);
    }

    /**
     * 根据一维数据返回用|隔开的字符串 例如 |1|2|3|
     * @param $onedimension
     * @return string
     * @author wph 2017-12-13
     */
    public static function getVerticalBarOneDimensionStr($onedimension)
    {
        return '|' . implode('|', $onedimension) . '|';
    }

    /**
     * 根据二维数据返回用,隔开的字符串 例如 1,2,3
     * @param $twodimension
     * @return string
     * @author wph 2017-12-13
     */
    public static function getIdInStr($twodimension)
    {

        if (Buddha_Atom_Array::isValidArray($twodimension)) {
            $ids = array();

            foreach ($twodimension as $k1 => $v1) {

                foreach ($v1 as $k2 => $v2) {
                    $ids[] = $v2;

                }

            }

            return implode(',', $ids);


        } else {
            return 0;
        }

    }


}