<?php
class Activityapplication extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }

    public function GeneratingNumber(){
        $time=date(YmdHis);
        $random =rand(11111111,99999999);
        $num=$time.$random;
        return $num;
    }

}