<?php
class Chatfripraise extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    /**
     * 判断是不是有 有效数据
     * 判断表是否有有效数据
     * 如果有就返1 没有就返0   如果返1就进行数据更新操作 如果返0就进行数据插入操作.
     * @return int
     * @author wph 2017-11-25
     */
    public function isHasValidRecord($chatfricircle_id,$user_id){

        $num = $this->countRecords("buddhastatus=0 AND user_id='{$user_id}' AND chatfricircle_id='{$chatfricircle_id}' ");
        if($num>0){
            return 1;
        }else{
            return 0;
        }

    }

    /**
     * 进行点赞或者取消点赞
     * @param $chatfricircle_id
     * @param $user_id
     * @author wph 2017-12-15
     */
    public function toFraiseOrCancelFraise($chatfricircle_id,$user_id){

        if($this->isHasValidRecord($chatfricircle_id,$user_id)){
            $this->delRecords("buddhastatus=0 AND user_id='{$user_id}' AND chatfricircle_id='{$chatfricircle_id}'  ");
        }else{
            $data  = array();
            $data['chatfricircle_id'] = $chatfricircle_id;
            $data['user_id'] = $user_id;
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $this->add($data);
        }

    }

    /**
     * 返回点赞总数
     * @param $chatfricircle_id
     * @param $user_id
     * @return mixed
     * @author wph 2017-12-18
     */
    public function countFraise($chatfricircle_id,$user_id){
         $num = $this->countRecords("chatfricircle_id='{$chatfricircle_id}' AND user_id='{$user_id}' ");
         return $num;
    }


}