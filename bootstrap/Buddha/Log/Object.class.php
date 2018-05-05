<?php
/*
 * 错误基类
 */
class Buddha_Log_Object extends Buddha_Base_Component{
    private $_error;
    public function __construct($error){
        parent::__construct();
        $this->_error=$error;
    }


public function getError(){
        return $this->_error;
    }
}