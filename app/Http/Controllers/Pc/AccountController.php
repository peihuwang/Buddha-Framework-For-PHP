<?php

/**
 * Class AccountController
 */
class AccountController extends Buddha_App_Action{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function login (){
        $identify= $_REQUEST['a'];
        $ImagecatalogObj=new Imagecatalog();
        $ImageObj=new Image();
        $UserObj=new User();
        $login=$ImagecatalogObj->getSingleFiledValues('',"identify='$identify' and isdel=0");
        if(count($login)){
           $loginimg= $ImageObj->getSingleFiledValues(array('sourcepic'),"cat_id='{$login['id']}' and isdel=0");
        }

        $username=Buddha_Http_Input::getParameter('username');
        $password=Buddha_Http_Input::getParameter('password');
        $isok=Buddha_Http_Input::getParameter('isok');
        if(Buddha_Http_Input::isPost()){
            $password = Buddha_Tool_Password::md5($password);
            $num = $UserObj->countRecords("isdel=0 and username='{$username}' and password='{$password}'");
            if($num==0){
                Buddha_Http_Output::makeValue(0);
            }else{
                $Db_User = $UserObj->getSingleFiledValues(array('id','state','groupid'),"isdel=0 and username='{$username}' and password='{$password}' ");
                if($Db_User['state']==1) {
                    if($isok==1){
                    $uid=$Db_User['id'];
                    Buddha_Http_Cookie::setCookie('uid', $uid,1);
                    }else{
                        $uid=$Db_User['id'];
                        Buddha_Http_Cookie::setCookie('uid', $uid,0);
                    }
                    Buddha_Http_Output::makeValue(1);
                }elseif($Db_User['state']==0){
                    Buddha_Http_Output::makeValue(2);
                }elseif($Db_User['state']==4){
                    Buddha_Http_Output::makeValue(3);
                }
            }
        }

        $this->smarty->assign('loginimg',$loginimg);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function register (){
        $identify= $_REQUEST['a'];
        $ImagecatalogObj=new Imagecatalog();
        $ImageObj=new Image();

        $UserObj=new User();
        $VerifyObj = new Verify();

        $login=$ImagecatalogObj->getSingleFiledValues('',"identify='$identify' and isdel=0");
        if(count($login)){
            $loginimg= $ImageObj->getSingleFiledValues(array('sourcepic'),"cat_id='{$login['id']}' and isdel=0");
        }

        $username=Buddha_Http_Input::getParameter('username');
        $mobile=Buddha_Http_Input::getParameter('mobile');
        $code=Buddha_Http_Input::getParameter('code');
        $password=Buddha_Http_Input::getParameter('password');
        $usertype=Buddha_Http_Input::getParameter('usertype');


        if(Buddha_Http_Input::isPost()){
            $step = $this->existnickname($username);
            if(!$step){
                Buddha_Http_Output::makeValue(3);
            }
            $step1 = $this->verify($mobile,$code);
            if(!$step1){
                Buddha_Http_Output::makeValue(2);
            }

            $data=array();
            $data['username']=trim($mobile);
            $data['mobile']=trim($mobile);
            $data['mobile_ide']=1;
            $data['state']=1;
            $data['onlineregtime']=time();
            $data['password']=Buddha_Tool_Password::md5(trim($password));
            $data['codes']=$password;
            $data['groupid']=$usertype;
            if($usertype == 1){
                   $data['to_group_id'] = '4'.','.$groupid;
                }
            $adduser=$UserObj->add($data);
            if($adduser){
                $VerifyObj->hadPass($mobile,$code);
               Buddha_Http_Output::makeValue(1);

            }
        }

        $this->smarty->assign('loginimg',$loginimg);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }

    public function existnickname($param_username=''){
        if($param_username){
            $username = $param_username;
        }else{
            $json = Buddha_Http_Input::getParameter('json');
            $json_arr =Buddha_Atom_Array::jsontoArray($json);
            $username = $json_arr['username'];
        }
        $UserObj = new User();
        $num = $UserObj->countRecords("isdel=0 and username='{$username}'");
        if($param_username){
            if($num==0){
                return 1;

            }else{
                return  0;

            }
        }
        $data = array();
        if($num==0){
            $data['isok']='true';
        }else{
            $data['isok']='false';
        }
        Buddha_Http_Output::makeJson($data);

    }

    public function existmobile($param_mobile=''){
        if($param_mobile){
            $mobile = $param_mobile;
        }else{
            $mobile =Buddha_Http_Input::getParameter('mobile');
        }

        $UserObj = new User();
        $num = $UserObj->countRecords("isdel=0 and mobile='{$mobile}'");

        if($param_mobile){
            if($num==0){
                return 1;
            }else{
                return  0;
            }
        }
        if($num==0){
            Buddha_Http_Output::makeValue(1);
        }else{
            Buddha_Http_Output::makeValue(0);
        }
    }

    public function verify($param_mobile='',$param_code=''){
        $nowtime = time();
        if($param_mobile){
            $mobile = $param_mobile;
            $code = $param_code;
        }else{
            $mobile = Buddha_Http_Input::getParameter('Mobile');
            $code = Buddha_Http_Input::getParameter('Code');
        }

        $ip = Buddha_Explorer_Network::getIp();
        $createtime= $nowtime-3600;
        $VerifyObj = new Verify();
        $num = $VerifyObj->countRecords("ip='{$ip}' and mobile='{$mobile}' and code='{$code}' and createtime>=$createtime ");
        if($num){
            $DB_Verify = $VerifyObj->getSingleFiledValues(array('code'),"
           ip='{$ip}' and mobile='{$mobile}' and code='{$code}' order by id desc ");
        }else{
            $DB_Verify=0;
        }
        if($param_mobile){
            if($num and $DB_Verify and $DB_Verify['code']==$code){
                return 1;
            }else{
                return 0;
            }
        }
        if($num and $DB_Verify and $DB_Verify['code']==$code){
            Buddha_Http_Output::makeValue(1);
        }else{
            Buddha_Http_Output::makeValue(0);
        }
    }


    public function  verifyrequest(){
        $nowtime = time();
        $mobile =Buddha_Http_Input::getParameter('mobile');
        $data = array();
        $data['mobile'] =$mobile;
        $data['code'] = Buddha_Tool_String::getRand();
        $data['ip'] = Buddha_Explorer_Network::getIp();
        $data['createtime'] =  $nowtime;
        $data['createtimestr'] = date('Y-m-d H:i:s',$nowtime);
        $data['regtime'] = $nowtime;
        $data['buddhastatus'] = 0;
        $data['isdel'] = 0;
        $VerifyObj = new Verify();
        $createtime= $nowtime-3600*24;
        $num1 = $VerifyObj->countRecords("ip='{$data['ip']}'  and createtime>=$createtime ");
        $num2= $VerifyObj->countRecords("mobile='{$data['mobile']}'   and createtime>=$createtime ");
        if($num1<50 and $num2<10 and $data['mobile']) {  //一个IP 一天注册人小于50人 同一个手机号最多一天让发10次验证码
           $ch = curl_init();
            $post_data = array(
            "account" => "sdk_haibojs",
            "password" => "dkghh46",
            "destmobile" => $mobile,
            "msgText" => "您的验证码是 ". $data['code'] . " 此验证码只用于绑定、修改手机、修改密码验证，请勿将此验证码发给任何号码及其他人【本地商家网】",
            "sendDateTime" => ""
            );
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POST, true);  
            curl_setopt($ch,CURLOPT_BINARYTRANSFER,true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $post_data = http_build_query($post_data);
            //echo $post_data;
            curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
            curl_setopt($ch, CURLOPT_URL, 'http://www.jianzhou.sh.cn/JianzhouSMSWSServer/http/sendBatchMessage');
            //$info= 
            //curl_exec($ch);

           //$snedflag= $VerifyObj->smsSend($mobile,$data['code']);
            if(curl_exec($ch)>0){
                $VerifyObj->add($data);
                Buddha_Http_Output::makeValue(1);
            }else{
                Buddha_Http_Output::makeValue(0);
            }
        }
    }


    public function forgottenpwd(){
        $VerifyObj = new Verify();
        $UserObj = new User();

        $identify= $_REQUEST['a'];
        $ImagecatalogObj=new Imagecatalog();
        $ImageObj=new Image();
        $login=$ImagecatalogObj->getSingleFiledValues('',"identify='$identify' and isdel=0");
        if(count($login)){
            $loginimg= $ImageObj->getSingleFiledValues(array('sourcepic'),"cat_id='{$login['id']}' and isdel=0");
        }
        $username = Buddha_Http_Input::getParameter('username');
        $mobile = Buddha_Http_Input::getParameter('mobile');
        $code = Buddha_Http_Input::getParameter('code');
        $password = Buddha_Http_Input::getParameter('password');

        if(Buddha_Http_Input::isPost()){
            $num = $UserObj->countRecords("isdel=0 and username='{$username}' and mobile='{$mobile}'");
            if($num==0){
                Buddha_Http_Output::makeValue(3);
            }
            $check = $this->verify($code,$mobile);
            if(!$check){
                Buddha_Http_Output::makeValue(2);
            }
            $data  = array();
            $data['password'] = Buddha_Tool_Password::md5($password);
            $data['codes'] = $password;
            $edituser=$UserObj->updateRecords($data,"isdel=0 and mobile='{$mobile}' and mobile_ide=1  ");
            if($edituser){
                $VerifyObj->hadPass($code,$mobile);
                Buddha_Http_Output::makeValue(1);
            }

        }

        $this->smarty->assign('loginimg',$loginimg);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function logout(){
        $UserObj=new User();
        $uid= Buddha_Http_Cookie::getCookie('uid');
        $data=array();
        $data['lastlogintime']=Buddha::$buddha_array['buddha_timestamp'];
        $UserObj->edit($data,$uid);
        if($UserObj){
            Buddha_Http_Cookie::setCookie( 'uid', '', -1 );
            Buddha_Http_Head::jump('/pc');
        }
    }






}