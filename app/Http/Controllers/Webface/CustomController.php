<?php

/**
 * Class CustomController  自定义表单
 */
class CustomController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

        //权限
        $webface_access_token = Buddha_Http_Input::getParameter('webface_access_token');
        if ($webface_access_token == '') {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444002, 'webface_access_token必填');
        }

        $ApptokenObj = new Apptoken();
        $num = $ApptokenObj->getTokenNum($webface_access_token);
        if ($num == 0) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444003, 'webface_access_token不正确请从新获取');
        }
    }





    /**
     *  删除自定义表单
     * costom_id  自定义表单ID
     * table_name  来源于哪一张表
     */
    public function delcustom(){

        if (Buddha_Http_Input::checkParameter(array('custom_id','usertoken','table_name','table_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $ActivityObj = new Activity();
        $CommonObj = new Common();
        $MysqlplusObj = new Mysqlplus();
        $CommonObj = new Common();

        $custom_id =  Buddha_Http_Input::getParameter('custom_id')?Buddha_Http_Input::getParameter('custom_id'):0;
        $table_id =  Buddha_Http_Input::getParameter('table_id')?Buddha_Http_Input::getParameter('table_id'):0;
        $table_name =  Buddha_Http_Input::getParameter('table_name');
        $state =  Buddha_Http_Input::getParameter('state')?Buddha_Http_Input::getParameter('state'):0;


        /*判断 $table_name 字符串的有效性*/
        if(!Buddha_Atom_String::isValidString($table_name)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误');
        }

        /*判断 通过 $table_name 中的$table_id 判断 $table_id 是否存在*/
        if(!$CommonObj->isCustomidEffectiveByCustomid($table_name,$custom_id,$table_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000, $table_name.'表内码ID错误');
        }

////////////////////////说明：下面的还没做完///////////////////////////////////////////
        /*判断 $table_name 字符串的有效性*/
        if(!$CommonObj->isAuditStatusCodesBy($costom_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000, '自定义表单内码ID错误');
        }

        if(!$MysqlplusObj->isValidTable($table_name)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000001, 'good_table 表 不存在');
        }
        /*判断 自定义表单内码 的有效性*/
        if(!$CommonObj->isAuditStatusCodesBy($state)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000, '自定义表单内码ID错误');
        }


        /*判断 $table_name 是不是存在*/
        if(!$CommonObj->isaudittableEffectivenessBytablename($table_name)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误');
        }

        /*判断 $table_id 是否属于 需要审核表中的ID*/

        if(!$CommonObj->isaudittableEffectivenessBytablename_id($table_name,$table_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误');
        }
        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','banlance','level3',
            'username'
        );

        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $level3 = $Db_User['level3'];

        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有商家权限');
        }

        /*判断该 是否审核过了 */
        if(!$CommonObj->isTableAuditBytablename_id($table_name,$table_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 50000002, '已经审核过了');
        }

        $tablechinesename=$CommonObj->getaudittableEffectivenessBytablename($table_name);

        /*判断是否属于该商家*/
        if(!$CommonObj->isOwnedAuditBytablename_id($table_name,$table_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, "此{$tablechinesename}不属于当前的商家管理");
        }

        $data = array();
        $data['is_sure'] =$state ;

        $Db_Activity_num=$this->db->updateRecords($data, $table_name," id = '{$table_id}'" );

        $jsondata = array();
        $jsondata['data'] = array();
        $datas=array();
        if($Db_Activity_num){
            $datas['is_ok']=1;
            $datas['is_msg']="{$tablechinesename}审核成功！";
        }else{
            $datas['is_ok']=0;
            $datas['is_msg']="{$tablechinesename}审核失败！";
        }

        $jsondata['data']=$datas;
        $t_id=$table_name.'_id';
        $jsondata[$t_id] = $table_id;
        $jsondata['usertoken'] = $usertoken;


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "代理商{$tablechinesename}审核");

    }



}