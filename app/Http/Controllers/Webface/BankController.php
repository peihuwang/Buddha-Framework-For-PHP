<?php

/**
 * Class BankController
 */
class BankController extends Buddha_App_Action
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

    public function bindusertoadd(){
        if (Buddha_Http_Input::checkParameter(array('usertoken','carenum','bankname','openbank','username'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }

        $UserObj = new  User();
        $BankObj = new Bank();


        $carenum = Buddha_Http_Input::getParameter('carenum');
        $bankname = Buddha_Http_Input::getParameter('bankname');
        $openbank = Buddha_Http_Input::getParameter('openbank');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $username = Buddha_Http_Input::getParameter('username');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $isCouldAddBank = $BankObj->isCouldAddBank($carenum,$user_id);
        if($isCouldAddBank==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000015, '用户银行卡已经被绑定');
        }

        $data = array();
        $data['name'] = $Db_User['username'];
        $data['carenum'] = $carenum;
        $data['bankname'] = $bankname;
        $data['openbank'] = $openbank;
        $data['uid'] = $user_id;
        $data['addtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['addtimestr'] = Buddha::$buddha_array['buddha_timestr'];

        $bank_id = $BankObj->add($data);

        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $user_id;
        $jsondata['bank_id'] = $bank_id;

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'添加银行卡');

    }

    public function updateuserbank(){
        if (Buddha_Http_Input::checkParameter(array('usertoken','carenum','bankname','openbank','username'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }

        $UserObj = new  User();
        $BankObj = new Bank();


        $carenum = Buddha_Http_Input::getParameter('carenum');
        $bankname = Buddha_Http_Input::getParameter('bankname');
        $openbank = Buddha_Http_Input::getParameter('openbank');
        $username = Buddha_Http_Input::getParameter('username');
        $bank_id = Buddha_Http_Input::getParameter('bank_id');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $isCouldAddBank = $BankObj->isCouldAddBank($carenum,$user_id,$bank_id);
        if($isCouldAddBank==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000015, '用户银行卡已经被绑定');
        }

        $data = array();
        $data['name'] = $username;
        $data['carenum'] = $carenum;
        $data['bankname'] = $bankname;
        $data['openbank'] = $openbank;

        $BankObj->edit($data,$bank_id);

        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $user_id;
        $jsondata['bank_id'] = $bank_id;

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,' 更新银行卡');
    }




}