<?php

/**
 * Class MessageController
 */
class MessageController extends Buddha_App_Action{




    protected $tablenamestr;
    protected $tablename;

    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

        //权限
        $webface_access_token = Buddha_Http_Input::getParameter('webface_access_token');
        if($webface_access_token==''){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444002,'webface_access_token必填');
        }

        $ApptokenObj = new Apptoken();
        $num =$ApptokenObj->getTokenNum($webface_access_token);
        if($num==0){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444003,'webface_access_token不正确请从新获取');
        }
        $this->tablenamestr='反馈信息';
        $this->tablename='message';

    }


    public function  beforefeedback()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }

        $UserObj = new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        if (strlen($usertoken) > 1) {
            $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 3, 'usertoken不正确请从新获取');
        }
        $fieldsarray = array('id');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = (int)$Db_User['id'];

        $jsondata = array();

        $typearr[]=  array('name'=>'请选择','select'=>0,'typevalue'=>0);
        $typearr[]=  array('name'=>'建议','select'=>0,'typevalue'=>1);
        $typearr[]=  array('name'=>'技术','select'=>1,'typevalue'=>2);
        $typearr[]=  array('name'=>'其他','select'=>0,'typevalue'=>3);

        $jsondata['type'] = $typearr;

        $jsondata['services'] = 'message.feedback';
        $jsondata['param'] = array();

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "{$this->tablenamestr}之前必须请求的页面");

    }

    public function  feedback()
        {
        if (Buddha_Http_Input::checkParameter(array('b_display','type','title','realname','mobile','question'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }

        $UserObj = new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        if(strlen($usertoken) > 1){
            $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],3,'usertoken不正确请从新获取');
        }
        $fieldsarray= array('id');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = (int)$Db_User['id'];

        $type = (int)Buddha_Http_Input::getParameter('type');

        $title = Buddha_Http_Input::getParameter('title');
        $realname = Buddha_Http_Input::getParameter('realname');
        $mobile = Buddha_Http_Input::getParameter('mobile');
        $email = Buddha_Http_Input::getParameter('email');
        $question = Buddha_Http_Input::getParameter('question');

        $ip = Buddha_Explorer_Network::getIp();
        $createtime = Buddha::$buddha_array['buddha_timestamp'];
        $createtimestr = Buddha::$buddha_array['buddha_timestr'];
        $today = date('Y-m-d', Buddha::$buddha_array['buddha_timestamp']);

        $MessageObj = new Message();

        $num = $MessageObj->countRecords("today='{$today}' and ip='{$ip}' and isdel=0 ");
        if($num>50)
        {
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],511100,"提交失败：天最多10条反馈");
        }

        $data = array();
        $data['user_id'] = $user_id;
        $data['type'] = $type;
        $data['title'] = $title;
        $data['realname'] = $realname;
        $data['mobile'] = $mobile;
        $data['email'] = $email;
        $data['ip'] = $ip;
        $data['createtime'] = $createtime;
        $data['createtimestr'] = $createtimestr;
        $data['today'] = $today;
        $data['question'] = $question;

        $add = $MessageObj->add($data);

        if($add)
        {
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],0,$this->tablenamestr.'添加成功');

        }else{
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444006,$this->tablenamestr.'添加失败');

        }

    }


}