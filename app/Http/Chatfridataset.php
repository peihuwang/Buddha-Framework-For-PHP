<?php
class Chatfridataset extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }


    /**
     * 判断数据库里有没有有效的星标数据
     * @param $user_id
     * @return int
     * @author wph 2017-12-09
     */
    public function isHasValidStarRecord($user_id,$friend_id){

        if($user_id==0 or $friend_id==0){

            return 0;
        }
        $num = $this->countRecords(" buddhastatus=0 AND  user_id='{$user_id}' AND friend_id='{$friend_id}' AND type_id=1 ");
        if($num>0){
            return 1;
        }else{
            return 0;
        }

    }


    /**
     * @param $user_id
     * @param $friend_id
     * @param $is_black
     * @author wph 2017-12-09
     */
    public function addOrUpdateStarData($user_id,$friend_id,$is_star){

        $hasRecord = $this->isHasValidStarRecord($user_id,$friend_id);

        if($hasRecord==1 AND $is_star==0){
            //删除数据
            $this->delRecords("buddhastatus=0 AND user_id='{$user_id}' AND friend_id='{$friend_id}' AND type_id=1  ");
        }else{
            //添加数据
            $data = array();
            $data['type_id'] = 1;
            $data['user_id'] = $user_id;
            $data['friend_id'] = $friend_id;
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] =  Buddha::$buddha_array['buddha_timestr'];
            $this->add($data);


        }




    }


    /**
     * 判断数据库里有没有有效的黑名单数据
     * @param $user_id
     * @return int
     * @author wph 2017-12-09
     */
    public function isHasValidBlackRecord($user_id,$friend_id){

        if($user_id==0 or $friend_id==0){

            return 0;
        }
        $num = $this->countRecords(" buddhastatus=0 AND  user_id='{$user_id}' AND friend_id='{$friend_id}' AND type_id=2 ");
        if($num>0){
            return 1;
        }else{
            return 0;
        }

    }

    /**
     * @param $user_id
     * @param $friend_id
     * @param $is_black
     * @author wph 2017-12-09
     */
    public function addOrUpdateBlackData($user_id,$friend_id,$is_black){

        $hasRecord = $this->isHasValidBlackRecord($user_id,$friend_id);

        if($hasRecord==1 AND $is_black==0){
            //删除数据
            $this->delRecords("buddhastatus=0 AND user_id='{$user_id}' AND friend_id='{$friend_id}' AND type_id=2  ");
            //删除黑名单
            Buddha_Thirdpart_Message::getInstance()->removeBlacklist($user_id,$friend_id);

        }else{
            //添加数据
            $data = array();
            $data['type_id'] = 2;
            $data['user_id'] = $user_id;
            $data['friend_id'] = $friend_id;
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] =  Buddha::$buddha_array['buddha_timestr'];
            $this->add($data);
            //加入黑名单
            Buddha_Thirdpart_Message::getInstance()->addBlacklist($user_id,$friend_id);


        }




    }



    /**
     * 判断数据库里有没有不让她看我的朋友圈数据
     * @param $user_id
     * @return int
     * @author wph 2017-12-09
     */
    public function isHasValidNoSeeMeRecord($user_id,$friend_id){

        if($user_id==0 or $friend_id==0){

            return 0;
        }
        $num = $this->countRecords(" buddhastatus=0 AND user_id='{$user_id}' AND friend_id='{$friend_id}' AND type_id=3 ");
        if($num>0){
            return 1;
        }else{
            return 0;
        }

    }

    /**
     * @param $user_id
     * @param $friend_id
     * @param $is_noseeme
     * @author wph 2017-12-09
     */
    public function addOrUpdateNoSeeMeData($user_id,$friend_id,$is_noseeme){

        $hasRecord = $this->isHasValidNoSeeMeRecord($user_id,$friend_id);

        if($hasRecord==1 AND $is_noseeme==0){
            //删除数据
            $this->delRecords("buddhastatus=0 AND user_id='{$user_id}' AND friend_id='{$friend_id}' AND type_id=3  ");
        }else{
            //添加数据
            $data = array();
            $data['type_id'] = 3;
            $data['user_id'] = $user_id;
            $data['friend_id'] = $friend_id;
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] =  Buddha::$buddha_array['buddha_timestr'];
            $this->add($data);


        }




    }

    /**
     * 判断数据库里有没有不让她看我的朋友圈数据
     * @param $user_id
     * @return int
     * @author wph 2017-12-09
     */
    public function isHasValidNoSeeItRecord($user_id,$friend_id){

        if($user_id==0 or $friend_id==0){

            return 0;
        }
        $num = $this->countRecords(" buddhastatus=0 AND user_id='{$user_id}' AND friend_id='{$friend_id}' AND type_id=4 ");
        if($num>0){
            return 1;
        }else{
            return 0;
        }

    }


    /**
     * @param $user_id
     * @param $friend_id
     * @param $is_noseeit
     * @author wph 2017-12-09
     */
    public function addOrUpdateNoSeeItData($user_id,$friend_id,$is_noseeit){

         $hasRecord = $this->isHasValidNoSeeItRecord($user_id,$friend_id);

         if($hasRecord==1 AND $is_noseeit==0){
             //删除数据
             $this->delRecords("buddhastatus=0 AND user_id='{$user_id}' AND friend_id='{$friend_id}' AND type_id=4  ");
         }else{
             //添加数据
             $data = array();
             $data['type_id'] = 4;
             $data['user_id'] = $user_id;
             $data['friend_id'] = $friend_id;
             $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
             $data['createtimestr'] =  Buddha::$buddha_array['buddha_timestr'];
             $this->add($data);


         }




    }



}