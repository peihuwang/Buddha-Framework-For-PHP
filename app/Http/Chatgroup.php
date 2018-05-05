<?php
class Chatgroup extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    /**
     * 2017-11-05 进行群主管理权转移
     * @param $old_master_id
     * @param $new_master_id
     * @param $groupId
     * @return int
     * @author wph
     */
    public function isReplaceGroupMaster($old_master_id,$new_master_id,$groupId){

        $UserObj = new User();
        $ChatgroupmemberObj = new Chatgroupmember();
        $Num_Old_Chatgroup = $this->countRecords("groupId='{$groupId}' and founder_id='{$old_master_id}' ");
        $Num_Old_User = $UserObj->countRecords("id='{$old_master_id}' ");
        $Num_New_User = $UserObj->countRecords("id='{$new_master_id}' ");

        $Num_New_Chatgroupmember = $ChatgroupmemberObj->countRecords("groupId='{$groupId}' and  account_id='{$new_master_id}'  ");

        if($Num_Old_Chatgroup>0 and $Num_Old_User>0 and $Num_New_User>0 and $Num_New_Chatgroupmember>0 ){

            $old_vieworder = Buddha::$buddha_array['buddha_timestamp'];
            $new_vieworder = $old_vieworder+1;

            /*要先进行群主管理权转让操作 首先让群变成无群主的群*/
            $ChatgroupmemberObj->updateRecords(array('host'=>0)," groupId ='{$groupId}' ");

            $Db_Old_Chatgroupmember = $ChatgroupmemberObj->updateRecords(array('vieworder'=>$old_vieworder),"host=1 and  groupId='{$groupId}'
            and account_id='{$old_master_id}' ");
            $Db_New_Chatgroupmember = $ChatgroupmemberObj->updateRecords(array('vieworder'=>$new_vieworder,'host'=>1),"host=0 and  groupId='{$groupId}'
            and account_id='{$new_master_id}'
            ");

            $Db_New_User = $UserObj->getSingleFiledValues(array('mobile',"id='{$new_master_id}' "));
            $founder = $Db_New_User['mobile'];
            $this->updateRecords(array('founder_id'=>$new_master_id,'founder'=>$founder),"groupId ='{$groupId}'");


        }else{
            return 0;
        }




    }

    /**
     * 2017-11-04 公告进行更新
     * @param $groupId
     * @param $summary_id
     * @param $summary
     * @author wph
     */
    public function updateNotice($groupId,$summary_id,$summary){

        $data = array();
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $data['summary_id'] = $summary_id;
        $data['summary'] = $summary;
        $this->updateRecords($data,"groupId='{$groupId}' ");

    }
    /**
     * 判断操作群信息的人是不是这个群组的群主 1=是 0=不是
     * @param $founder_id
     * @param $groupId
     * @return int
     */
    public function isFounderGroup($founder_id,$groupId){

        $num = $this->countRecords(" founder_id='{$founder_id}' AND groupId='{$groupId}' ");

        if($num){

            return 1;

        }else{

            return 0;
        }

    }

    /**
     * 垃圾回收机制:当群成员<=0 群自动删除
     */
    public function garbageCollection(){

        $Db_Chatgroup = $this->getFiledValues('',"membertotal<=0");

        if(Buddha_Atom_Array::isValidArray($Db_Chatgroup)){

            foreach($Db_Chatgroup as $k=>$v){

                $groupId = $v['groupId'];
                $this->delRecords(" groupId='{$groupId}' AND  membertotal<=0");
                $founder_id =  $v['founder_id'];
                /**
                 * 群进行解散
                 */
                Buddha_Thirdpart_Message::getInstance()->groupDismiss($founder_id, $groupId);

            }
        }




    }

    /**
     * 更新群组成员总人数
     * @param $groupId
     */
    public function updateMemberTotal($groupId){

        $ChatgroupmemberObj = new Chatgroupmember();
        $num = $ChatgroupmemberObj->countRecords("groupId='{$groupId}' ");
        $this->updateRecords(array('membertotal'=>$num),"groupId='{$groupId}' ");


    }


}