<?php
class Chatgroupmember extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    public function insertNewMember($drag_id,$account_id,$groupId){

        $UserObj =  new User();


        $num = $this->countRecords("account_id='{$account_id}' and groupId='{$groupId}' ");
        if($num==0){
            $Db_User = $UserObj->getSingleFiledValues(array('mobile'),"id='{$account_id}' ");
            $data = array();
            $data['groupId'] = $groupId;
            $data['account_id'] = $account_id;
            $data['account'] = $Db_User['mobile'];
            $data['drag_id'] = $drag_id;
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $this->add($data);

        }

    }

    /**
     * 判断当前登录人是不是群组的成员 1=是 0=不是
     * @param $my_id
     * @param $groupId
     * @return int
     * @author wph
     */
    public function isGroupMember($account_id,$groupId){

        $num = $this->countRecords(" account_id='{$account_id}' and groupId='{$groupId}' ");
        if($num){
            return 1;
        }else{
            return 0;
        }


    }
    /**
     * 返回群组成员的内码id数组
     * @param $groupId
     * @return array
     */
    public function getGroupMemberIdArr($groupId){

        $return = array();
        $Db_Chatgroupmember = $this->getFiledValues(array('account_id'),"groupId='{$groupId}' ");

        if(Buddha_Atom_Array::isValidArray($Db_Chatgroupmember)){

            foreach($Db_Chatgroupmember as $k=>$v){
                $temp_userId = $v['account_id'];
                $return[$temp_userId] = $temp_userId;
            }

        }

        return $return;

    }
    /**
     * 返回相应群组1-9个群组成员头像
     * @param $groupId
     * @return array
     */
    public function getMaxNineLogo($groupId){
        $return = array();
        $host = Buddha::$buddha_array['host'];
        $Db_arr = $this->db->select("chatgroupmember",
            array("[>]user" => array("account_id" => "id")),
            array("user.logo"),

            array(
            "chatgroupmember.groupId" => $groupId,
            "ORDER" => array("chatgroupmember.vieworder" => "DESC","chatgroupmember.gid" => "ASC"),
            "LIMIT" => 9
            )

        );

        if(Buddha_Atom_Array::isValidArray($Db_arr)){

            foreach($Db_arr as $k=>$v){

                $logo = $v['logo'];
                if(Buddha_Atom_String::isValidString($logo)){

                    if(!Buddha_Atom_String::hasNeedleString($logo,'http')){

                        $logo = $host.$logo;
                    }else{
                        $logo = Buddha_Atom_String::getAfterReplaceStr($logo,'http','https');
                    }


                }else{
                    $logo = $host."resources/worldchat/portrait/default.png";
                }

                $Db_arr[$k]['logo'] = $logo;

            }
            $return = $Db_arr;
        }

        return $return;



    }


}