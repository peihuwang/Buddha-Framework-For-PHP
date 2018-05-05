<?php

/**
 * Class UserController
 */
class PartnerController extends Buddha_App_Action{

    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
    }
    public function add(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if($UserInfo['groupid']!=2){
            Buddha_Http_Head::redirectofmobile('你没有权限！','index.php?a=index&c=agent',2);
        }
        $UserObj = new User();
        //获取数据
//        $username=Buddha_Http_Input::getParameter('username');
        $realname=Buddha_Http_Input::getParameter('realname');
        $groupid=3;
        $mobile=Buddha_Http_Input::getParameter('mobile');
        $email=Buddha_Http_Input::getParameter('email');
        $password=Buddha_Http_Input::getParameter('password');
        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');
        $agentrate=Buddha_Http_Input::getParameter('agentrate');
        if($password=="" || !$password){
            $password=substr($mobile,5);
        }
        $source=2;
        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['referral_id']=$uid;
            $data['username']=$mobile;
            $data['realname']=$realname;
            $data['groupid']=$groupid;
            $data['to_group_id']= '';
            $data['mobile']=$mobile;
            $data['email']=$email;
            $data['password']=Buddha_Tool_Password::md5($password);
            $data['codes']=$password;
            $data['state']=1;
            $data['onlineregtime']=Buddha::$buddha_array['buddha_timestamp'];
            $data['partnerrate']=$agentrate;
            $data['source']=$source;
            $data['level0']=1;
            $data['level1']=$level1;
            $data['level2']=$level2;
            $data['level3']=$level3;

            $adduser_id=$UserObj->add($data);
            if($adduser_id){
                Buddha_Http_Head::redirect('添加成功！',"index.php?a=index&c=agent");
            }else{
                Buddha_Http_Head::redirect('添加失败！',"index.php?a=add&c=partner");
            }
        }
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
    /*手机检测*/
    public function existmobile()
    {
        $UserObj = new User();
        $mobile=Buddha_Http_Input::getParameter('mobile');
        $num=$UserObj->countRecords("isdel=0 and mobile='{$mobile}'");
        Buddha_Http_Output::makeJson($num);
    }
    /*
     *    ajaxadderr   ajax 请求地区(地区查询)
     */
    public function ajaxadderr(){
        $RegionObj= new Region();
        $fid = Buddha_Http_Input::getParameter('fid');
        $err = Buddha_Http_Input::getParameter('err');

        $datas=$RegionObj->ajaxadderr($fid);
        $datas['filed']=$err;
        Buddha_Http_Output::makeJson($datas);
    }

}