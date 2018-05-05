<?php

/**
 * Class AccountController
 */
class AccountController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function login(){
        $UserObj=new User();
        $username=Buddha_Http_Input::getParameter('lg_username');
        $password=Buddha_Http_Input::getParameter('lg_pwd');
        $isok=Buddha_Http_Input::getParameter('isok');
        if(Buddha_Http_Input::isPost()){
            $password = Buddha_Tool_Password::md5($password);
            $num = $UserObj->countRecords("isdel=0 and username='{$username}' and password='{$password}' ");
            if($num==0){
                echo 0;
                exit;
            }else{
                $Db_User = $UserObj->getSingleFiledValues(array('id','state','groupid'),"isdel=0 and username='{$username}' and password='{$password}' ");
                if($Db_User['state']==0){
                    echo 2;
                    exit;
                }elseif($Db_User['state']==4){
                    echo 1;
                    exit;
                }else{
                   if($Db_User['groupid']==1){
                       Buddha_Http_Head::jump();
                   }elseif($Db_User['groupid']==2){

                   }elseif($Db_User['groupid']==3){

                   }else{

                   }
                }
            }

            if($isok==1){
           $uid=$Db_User['id'];
           Buddha_Http_Cookie::setCookie('uid', $uid);
            }

        }

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }



    public function register(){
        $UserObj=new User();
        $VerifyObj = new Verify();
        $username=Buddha_Http_Input::getParameter('reg_username');
        $mobile=Buddha_Http_Input::getParameter('reg_mobile');
        $code=Buddha_Http_Input::getParameter('reg_yanzheng');
        $password=Buddha_Http_Input::getParameter('reg_pwd');
        $groupid=Buddha_Http_Input::getParameter('usertype');

        if(Buddha_Http_Input::isPost()) {
            $step1 = $this->existnickname($username);
            $step2 = $this->existmobile($mobile);
            $step3 = $this->verify($mobile, $code);

            if ($step1 and $step2 and $step3) {

                $data = array();
                $data['username'] = $username;
                $data['mobile'] = $mobile;
                $data['mobile_ide'] = 1;
                $data['password'] = Buddha_Tool_Password::md5($password);
                $data['codes'] = $password;
                $data['groupid'] = $groupid;
                $data['state'] = 1;
                $data['onlineregtime'] = Buddha::$buddha_array['buddha_timestamp'];

                $adduser = $UserObj->add($data);
                if ($adduser) {
                    $VerifyObj->hadPass($mobile,$code);
                    echo 1;
                    exit;
                }
            }
        }
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
            echo "true";
        }else{
            echo 'false';
        }
    }


    public function existmobile($param_mobile=''){
         $Mobile='';
        if($param_mobile){
            $Mobile = $param_mobile;
        }else{
            $Mobile = Buddha_Http_Input::getParameter('Mobile');
        }
        $CountryCode = Buddha_Http_Input::getParameter('CountryCode');
        $UserObj = new User();
        $num = $UserObj->countRecords("isdel=0 and buddhastatus=0 and mobile='{$Mobile}'");

        if($param_mobile){
            if($num==0){
                return 1;
            }else{
                return  0;
            }
        }
        if($num==0){
            echo 'true';//可以注册
        }else{
            echo 'false';//false
        }

    }


    public function  verifyrequest(){
        $nowtime = time();
        $json = Buddha_Http_Input::getParameter('json');
        $json_arr =Buddha_Atom_Array::jsontoArray($json);
        $mobile =$json_arr['mobile'];
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
           $snedflag= $VerifyObj->smsSend($mobile,$data['code']);
            $datas = array();
            if($snedflag){
                $datas['isok']='true';
                $VerifyObj->add($data);
            }else{
                $datas['isok']='false';
            }
            Buddha_Http_Output::makeJson($datas);
        }
    }

}