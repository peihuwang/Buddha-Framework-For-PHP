<?php
class Bank extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }


    /**
     * 判断某人的银行是否可以添加
     * @param $carenum
     * @param $user_id
     * @return int
     */
    public function isCouldAddBank($carenum,$user_id,$bank_id=0){

        $num = $this->countRecords("carenum='{$carenum}' AND uid='{$user_id}' AND id!='{$bank_id}' ");
        if($num == 0){
            return 1;
        }else{
            return 0;
        }

    }

}