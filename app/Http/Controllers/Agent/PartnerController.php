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
        $realname=Buddha_Http_Input::getParameter('realname');
        $groupid=3;
        $mobile=Buddha_Http_Input::getParameter('mobile');
        $email=Buddha_Http_Input::getParameter('email');
        $password=Buddha_Http_Input::getParameter('password');
        $regionstr=Buddha_Http_Input::getParameter('regionstr');
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
            if($regionstr){
                $level = explode(",", $regionstr);
                $data['level0']=1;
                $data['level1']=$level[0];
                $data['level2']=$level[1];
                $data['level3']=$level[2];
            }
//            print_r($data);exit;
            $adduser_id=$UserObj->add($data);
            $datas = array();
            if($adduser_id){
                $datas['isok']='true';
                $datas['data']='添加成功';
                $datas['url']='index.php?a=index&c=partner';
            }else{
                $datas['isok']='false';
                $datas['data']='添加失败';
                $datas['url']='index.php?a=add&c=partner';
            }
            Buddha_Http_Output::makeJson($datas);
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
        $datas=$RegionObj->ajaxadderr($fid);
        Buddha_Http_Output::makeJson($datas);
    }

}