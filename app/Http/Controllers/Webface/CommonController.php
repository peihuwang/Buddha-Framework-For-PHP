<?php

/**
 * Class CommonController  公共函数
 */
class CommonController extends Buddha_App_Action
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


    public function bottom(){

        $bottomarray=array(
            0=>array('bottomid'=>1,'bottomname'=>'首页','services'=>'index.homepage','param'=>array()),
            1=>array('bottomid'=>2,'bottomname'=>'本地信息','services'=>'shop.shopclassification','param'=>array()),
            2=>array('bottomid'=>3,'bottomname'=>'附近商家','services'=>'shop.more','param'=>array()),
            3=>array('bottomid'=>4,'bottomname'=>'我的','services'=>'user.center','param'=>array()),
        );

        $jsondata=array();
        if(Buddha_Atom_Array::isValidArray($bottomarray)){
            $jsondata=$bottomarray;
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'底部信息！');

    }





//判断用户是否登录
    public function IsUserlog(){
        //////////判断用户是否登录
        $UserObj = new User();
        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        return $user_id;
        //////////

//        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
//
//        $usertoken = Buddha_Http_Input::getParameter('usertoken');
//        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
//        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
//        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
//        $user_id = $Db_User['id'];


    }





    /**
     * 代理商审核
     */
    public function agentverify(){

        if (Buddha_Http_Input::checkParameter(array('table_id','usertoken','table_name','state'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $ActivityObj = new Activity();
        $CommonObj = new Common();

        $table_id =  Buddha_Http_Input::getParameter('table_id')?Buddha_Http_Input::getParameter('table_id'):0;
        $table_name =  Buddha_Http_Input::getParameter('table_name');
        $state =  Buddha_Http_Input::getParameter('state')?Buddha_Http_Input::getParameter('state'):0;

        /*判断 $table_name 字符串的有效性*/
        if(!Buddha_Atom_String::isValidString($table_name)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误');
        }
        /*判断 $table_name 字符串的有效性*/
        if(!$CommonObj->isAuditStatusCodesBy($state)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000039, '审核状态码不正确');
        }


        /*判断$table_name 是不是属于 需要审核表的*/
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

        if(!$UserObj->isHasAgentPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }

        /*判断该信息是否审核过了*/
        if(!$CommonObj->isTableAuditBytablename_id($table_name,$table_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 50000002, '已经审核过了');
        }

        $tablechinesename=$CommonObj->getaudittableEffectivenessBytablename($table_name);

        /*判断是否属于该代理商*/
        if(!$CommonObj->isOwnedAuditBytablename_id($table_name,$table_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, "此{$tablechinesename}不属于当前的代理商管理");
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