<?php
/*
*收货地址
*/
class News extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }

    /**
     * 0:错误 1：正确
     * 判断用户是否有删除news中记录的权限
     * @param $user_id
     * @param $news_id
     * @return int
     * @author
     */
    public function isUserHasDeletePrivilege($user_id,$news_id){

        if($user_id<1 or $news_id<1){
            return 0;
        }

        $num = $this->countRecords(" id='{$news_id}' and u_id='{$user_id}'  ");
        if($num){
            return 1;
        }else{
           return 0;

        }

    }

}