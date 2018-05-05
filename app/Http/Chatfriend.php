<?php
class Chatfriend extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    /**
     * 获取好友的内码id in 例如 1,2,3
     * @param $my_id
     * @return string
     */
    public function getFriendIdInStr($my_id){

        $Db_Chatfriend = $this->getFiledValues(array('friend_id'),"buddhastatus=0 AND my_id='{$my_id}' ");

        return Buddha_Atom_Array::getIdInStr($Db_Chatfriend);

    }

    /**
     * 获取好友的昵称
     * @param $my_id
     * @param $friend_id
     * @return string
     * @author wph 2017-12-09
     */
    public function getFriendNickName($my_id,$friend_id){
        $friend_nickname = '';
        $Db_Chatfriend = $this->getSingleFiledValues(array('friend_nickname'),"my_id='{$my_id}' AND friend_id='{$friend_id}' ");
        $friend_nickname = $Db_Chatfriend['friend_nickname'];

        if(!Buddha_Atom_String::isValidString($friend_nickname)){

            $UserObj = new User();
            $Db_User= $UserObj->getSingleFiledValues(array('realname','mobile'),"id='{$friend_id}' ");
            $friend_nickname = $Db_User['realname'];
            if(!Buddha_Atom_String::isValidString($friend_nickname)){
                $friend_nickname = $Db_User['mobile'];
            }


        }


        return $friend_nickname;

    }

    /**
     * 判断是不是正确的朋友数组  数组是会员内码id
     * @param $my_id
     * @param $friend_id_arr
     * @return int
     */
    public function isValidFriendIdArr($my_id,$friend_id_arr){

        if(!Buddha_Atom_Array::isValidArray($friend_id_arr)){

            return 0;
        }

        $num = count($friend_id_arr);
        /**
         * 查userIdArr在数据库里存在的记录数目
         */

        $ids = implode ( ',', $friend_id_arr);
        $db_num = $this->countRecords(" my_id='{$my_id}' AND friend_id IN ($ids)");

        if($db_num==$num){

            return 1;
        }else{
            return 0;
        }

    }

    /**
     * 2017-11-01 删除好友
     * @param $my_id
     * @param $friend_id
     * @return mixed
     * @author
     */
    public function isDeleteFriend($my_id,$friend_id){
        //融云实现加入黑名单,防止好友在本地关系表删除了,但实际是两个人还可以进行通讯.

        Buddha_Thirdpart_Message::getInstance()->addBlacklist($my_id,$friend_id);

        return  $this->delRecords("my_id='{$my_id}' AND friend_id='{$friend_id}'  ");
    }
    /**2017-11-01 更新好友的备注信息
     * @param $my_id
     * @param $friend_id
     * @param $friend_nickname
     * @return int
     * @author wph
     */
    public function isModifyNickname($my_id,$friend_id,$friend_nickname){
        $host = Buddha::$buddha_array['host'];
        $UserObj = new User();

        if(Buddha_Atom_String::isValidString($friend_nickname)){

            $this->updateRecords(array('friend_nickname'=>$friend_nickname),"my_id='{$my_id}' AND friend_id='{$friend_id}'  ");

            $Db_User = $UserObj->getSingleFiledValues(array('logo','chattoken','realname','mobile'),"id='{$friend_id}'");
            $chattoken = $Db_User['chattoken'];


            $logo = $Db_User['logo'];
            if(Buddha_Atom_String::isValidString($logo)){
                $logo = $host.$logo;
            }else{
                $logo = $host."resources/worldchat/portrait/default.png";
            }

            if(!Buddha_Atom_String::isValidString($chattoken)){

                $token = Buddha_Thirdpart_Message::getInstance()->getToken($friend_id, $friend_nickname, $logo);

            }


            $isOk = Buddha_Thirdpart_Message::getInstance()->refresh($friend_id, $friend_nickname, $logo);
            return 1;
        }else{
            return 0;
        }

    }


    /**
     * 检查某个会员内码id是否是自己的好友
     * my_id 登录人的会员内码id friend_id 添加好友人的会员内码id
     * @param $my_id
     * @param $friend_id
     * @return int
     * @author wph
     */
    public function isMyFriend($my_id,$friend_id){

        $num = $this->countRecords("my_id='{$my_id}' AND friend_id='{$friend_id}' ");
        if($num){

            return 1;

        }else{

            return 0;
        }
    }

    /**
     * 如果成功交朋友就返回添加的记录内码id 如果本来就是朋友就返回0
     * @param $my_id
     * @param $friend_id
     * @return int|mixed
     * @author wph
     */
    public function isMakeFriendSuccess($my_id,$friend_id){

        if(!$this->isMyFriend($my_id,$friend_id)){

            $data = array();
            $data['my_id'] = $my_id;
            $data['friend_id'] = $friend_id;
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
             return $this->add($data);

        }

        return 0;

    }


}