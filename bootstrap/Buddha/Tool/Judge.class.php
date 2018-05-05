<?php

class Buddha_Tool_Judge{
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
     * 校验日期格式是否正确
     *
     * @param string $date 日期
     * @param string $formats 需要检验的格式数组
     * @return boolean
     */
    function isDate($date, $formats = array('Y-m-d', 'Y/m/d'))
    {
        $timestamp = strtotime($date);
        if (!$timestamp) {
            return false;
        }
        foreach ($formats as $format) {
            if (date($format, $timestamp) == $date) {
                return true;
            }
        }

        return false;
    }


}