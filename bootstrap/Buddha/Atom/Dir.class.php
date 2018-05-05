<?php

/**
 * Class Buddha_Atom_Dir
 */
class Buddha_Atom_Dir
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
     * 把数据库不标准的转化成为标准的格式
     * @param $filelocation
     * @return string
     * @author 2017-11-24
     */
    public static function getformatDbStorageDir($filelocation)
    {

        if (!Buddha_Atom_String::isValidString($filelocation)) {
            return '';
        }

        if (Buddha_Atom_String::hasNeedleString($filelocation, 'storage/')) {

            $arr = explode("storage/", $filelocation);
            $filelocation = 'storage/' . $arr[1];
        }


        return $filelocation;


    }


}