<?php
class Chatfriendmsg extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }


    public function acceptFriend($receive_id,$msgid){
        $host = Buddha::$buddha_array['host'];
        $UserObj  = new User();
        $ChatfriendObj = new Chatfriend();

        if($this->isReceiveMsg($receive_id,$msgid)){
            $Db_Chatfriendmsg = $this->getSingleFiledValues(array('send_id')," msgid='{$msgid}' ");
            $send_id = $Db_Chatfriendmsg['send_id'];
            $friend_id = $send_id;
            $my_id = $receive_id;
            $Db_User = $UserObj->getSingleFiledValues(array('logo','realname','mobile')," id='{$send_id}' ");
            $logo = $Db_User['logo'];
            if(Buddha_Atom_String::isValidString($logo)){
                $logo = $host.$logo;
            }else{
                $logo = $host."resources/worldchat/portrait/default.png";
            }

            $realname = $Db_User['realname'];
            $mobile = $Db_User['mobile'];
            $nickname = $mobile;
            if(Buddha_Atom_String::isValidString($realname)){
                $nickname = $realname;
            }

            $token = Buddha_Thirdpart_Message::getInstance()->getToken($send_id, $nickname, $logo);

            //融云实现解除黑名单,防止好友实际两个加好友了,但实际是两个人还不能进行通讯.
            Buddha_Thirdpart_Message::getInstance()->removeBlacklist($send_id,$receive_id);
            //融云实现解除黑名单,防止好友实际两个加好友了,但实际是两个人还不能进行通讯.
            Buddha_Thirdpart_Message::getInstance()->removeBlacklist($receive_id,$send_id);
            $ChatfriendObj->isMakeFriendSuccess($my_id,$friend_id);

            $ChatfriendObj->isMakeFriendSuccess($friend_id,$my_id);


        }

    }

    /**
     * 2017-11-01 判断新好友消息是不是本人所有 1=是 0=不是
     * @param $receive_id
     * @param $msgid
     * @return int
     * @author wph
     */
    public function isReceiveMsg($receive_id,$msgid){

        $num = $this->countRecords("receive_id='{$receive_id}' AND msgid='{$msgid}'  ");
        if($num){
            return 1;
        }else{
            return 0;
        }
    }
    /**
     * 2017-10-31 判断添加好友到通讯录,进行发送的消息验证是否通过,1=通过 0=未通过
     * @param $receive_id
     * @param $send_id
     * @return int
     * @author wph
     */
   public function isFriendMsgHadPass($receive_id,$send_id){

       $num = $this->countRecords("receive_id='{$receive_id}' AND send_id='{$send_id}' AND buddhastatus=6 ");
       if($num){
           return 1;
       }else{
           return 0;
       }

   }
    /**
     * 2017-10-31 判断添加好友到通讯录,进行发送的消息验证是否进行发送,1=发送 0=未发送
     * @param $receive_id
     * @param $send_id
     * @return int
     * @author wph
     */
   public function isFriendMsgHadSend($receive_id,$send_id){
       $num = $this->countRecords("receive_id='{$receive_id}' AND send_id='{$send_id}' AND buddhastatus=1 ");
       if($num){
           return 1;
       }else{
           return 0;
       }
   }

    /**2017-10-31 更新好友的验证消息
     * @param $receive_id
     * @param $send_id
     * @param $message
     * @author wph
     */
    public function updateFriendMsg($receive_id,$send_id,$message){

   /*     $this->updateRecords(array('message'=>$message,'createtime'=>Buddha::$buddha_array['buddha_timestamp'],
            'createtimestr'=>Buddha::$buddha_array['buddha_timestr'],
            'sendcount'=>'sendcount'+1,
        ),"receive_id='{$receive_id}' AND send_id='{$send_id}' AND buddhastatus=1");*/

      $this->db->update($this->table, array(
            "message" => $message,
            "createtime" => Buddha::$buddha_array['buddha_timestamp'],
            "createtimestr" => Buddha::$buddha_array['buddha_timestr'],
            // plus one
            "sendcount[+]" => 1
      ),

            array(
                "AND" => array(
                    "receive_id" => $receive_id,
                    "send_id" => $send_id,
                    "buddhastatus" => 1
                )
            )

            );



    }

    public function addFriendMsg($receive_id,$send_id,$message){
        $data = array();
        $data['receive_id'] = $receive_id;
        $data['send_id'] = $send_id;
        $data['message'] = $message;
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $data['buddhastatus'] = 1;

        $this->add($data);
    }




}