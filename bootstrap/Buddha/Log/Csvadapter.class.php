<?php
/*
 * 错误基类
 */
class Buddha_Log_Csvadapter  extends Buddha_Log_Object{
    protected static $_instance;
    private $_errorNumber;
    private $_errorText;



    /**
     * 获取实例
     *
     */
    public static function getInstance($error)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($error);
        }
        return self::$_instance;
    }
    function __construct($error){
        //继承父类的初始化.并且增加新的方法.
        parent::__construct($error);
        $parts=explode(":", $error);
        $this->_errorNumber=$parts[0];
        $this->_errorText=$parts[1];

    }

    function getErrorNumber(){
        return $this->_errorNumber;
    }

    function getErrorText(){
        return $this->_errorText;
    }


}