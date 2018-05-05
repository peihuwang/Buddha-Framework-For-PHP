<?php
class Chatfricomment extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    /**
     * 判断此评论是不是当前用户的评论
     * @param $chatfricomment_id
     * @param $user_id
     * @return int
     * #author wph 2017-12-15
     */
    public function isOwerCommon($chatfricomment_id,$user_id){
        $num = $this->countRecords("id='{$chatfricomment_id}' AND user_id='{$user_id}'  ");
        if($num){
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * 返回评论总数
     * @param $chatfricircle_id
     * @param $user_id
     * @return mixed
     * @author wph 2017-12-18
     */
    public function countCommnent($chatfricircle_id,$user_id){
        $num = $this->countRecords("chatfricircle_id='{$chatfricircle_id}' AND user_id='{$user_id}' ");
        return $num;
    }

    /**
     * 判断提供的father_id对不对
     * @param $user_id
     * @param $father_id
     * @return int
     * @author  wph 2017-12-15
     */
    public function couldCommon($user_id,$father_id){
        $ChatfriendObj = new Chatfriend();
        if($father_id==0){
           return 1;
        }else{

            $num = $this->countRecords("id='{$father_id}' ");
            if($num==0){
                return 0;
            }

            $Db_Chatfricomment = $this->getSingleFiledValues(array('user_id',"id='{$father_id}' "));
            $article_user_id = $Db_Chatfricomment['user_id'];

            if($ChatfriendObj->isMyFriend($user_id,$article_user_id)){
                return 1;
            }else{
                return 0;
            }


        }

    }




}