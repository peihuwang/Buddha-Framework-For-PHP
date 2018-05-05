<?php
/*
 * Buddha_Log_Console::getInstance(Buddha_Log_Csvadapter::getInstance("404:Not Found"))->write();
 * 输出到控制台,原始错误执行代码
 */
class Buddha_Log_Console{
    protected static $_instance;
    private $_errorObject;
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
        $this->_errorObject=$o;
    }

    function write(){
        $data = $this->_errorObject->getError();
        if (is_array($data) || is_object($data))
            {   echo("<script>console.log('".json_encode($data)."');</script>");
             } else {
                     echo("<script>console.log('".$data."');</script>");
            }




      //  $stdout = fopen('php://stdout', 'w');
      // fwrite($stdout,json_encode($this->_errorObject->getError())."\n");   //为了打印出来的格式更加清晰，把所有数据都格式化成Json字符串
     //  fclose($stdout);

       // fwrite(STDERR, $this->_errorObject->getError());
    }
}