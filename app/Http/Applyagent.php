<?php
/*
*代理商申请
*/
class Applyagent extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }
}