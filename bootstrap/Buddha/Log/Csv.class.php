<?php
/*
 * 错误基类
 *   Buddha_Log_Csv::getInstance(Buddha_Log_Csvadapter::getInstance("404:Not Found"))->write();

 */
class Buddha_Log_Csv extends Buddha_Base_Component{
    protected static $_instance;
    private $_csvfile;

    public $_errorObject;
    /**
     * 获取实例
     *
     */
    public static function getInstance($o)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($o);
        }
        return self::$_instance;
    }
    function __construct($o){
        parent::__construct(array('_errorObject'=>$o));
        $this->_csvfile=PATH_ROOT.'storage/logs/log.csv';
    }
    function write(){
        $line=$this->_errorObject->getErrorNumber();
        $line.=',';
        $line.=$this->_errorObject->getErrorText();
        $line.=',';
        $line.=date('Y-m-d H:i:s',time());
        $line.="\n";
        file_put_contents($this->_csvfile, $line,FILE_APPEND);
    }

}