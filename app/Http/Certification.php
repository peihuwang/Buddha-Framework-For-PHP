<?php
/*
*店铺认证码
*/
class Certification extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }

    //判断认证码是否正确
    public function isAuthenticationCodeCorrect($code){
        $time = time();
        $num = $this->countRecords("code='{$code}' AND is_use=0 AND overdue_time>{$time}");
        if($num){
            return 1;
        }else{
            return 0;
        }
    }

}