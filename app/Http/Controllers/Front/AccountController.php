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

    /**
     * 登录
     */
    public function login()
    {
        $UserObj=new User();
        $username=Buddha_Http_Input::getParameter('lg_username');
        $password=Buddha_Http_Input::getParameter('lg_pwd');
        $isok=Buddha_Http_Input::getParameter('isok');
        $backreturnurl=Buddha_Http_Input::getParameter('backreturnurl');
        $backreturn_url = base64_decode($backreturnurl);

        if(Buddha_Http_Input::isPost()){
            $password = Buddha_Tool_Password::md5($password);
            $num = $UserObj->countRecords("isdel=0 and username='{$username}' and password='{$password}'");
            if($num==0){
                Buddha_Http_Output::makeValue(0);
            }else{
                $Db_User = $UserObj->getSingleFiledValues(array('id','state','groupid'),"isdel=0 and username='{$username}' and password='{$password}' ");
                if($Db_User['state']==1){
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
                    Buddha_Http_Output::makeValue(4);
                }
            }
        }

        $this->smarty->assign('backreturn_url',$backreturn_url);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    /**
     *  退出
     */
    public function logout(){
        $UserObj=new User();
        $uid= Buddha_Http_Cookie::getCookie('uid');
        $data=array();
        $data['lastlogintime']=Buddha::$buddha_array['buddha_timestamp'];
        $UserObj->edit($data,$uid);
        if($UserObj){
            Buddha_Http_Cookie::setCookie( 'uid', '', -1 );
            Buddha_Http_Head::jump('index.php?a=login&c=account');
        }
    }

    /**
     * 注册
     */
    public function register(){
        $UserObj = new User();
        $VerifyObj = new Verify();
        $UserassoObj = new Userasso();
        $origin_id=Buddha_Http_Input::getParameter('origin_id');
        $username=Buddha_Http_Input::getParameter('reg_username');
        $mobile=Buddha_Http_Input::getParameter('reg_mobile');
        $code=Buddha_Http_Input::getParameter('reg_yanzheng');
        $password=Buddha_Http_Input::getParameter('reg_pwd');
        $groupid=Buddha_Http_Input::getParameter('usertype');
        $UserObj->synxOrigin($origin_id);
        $father_id = $UserObj->getOriginId();
        if($username == 'bendisahngjia'){
           $username = $mobile;
        }
        //$isok = array();
        if(Buddha_Http_Input::isPost()) {
            $step1 = $this->existnickname($username);
            if(!$step1){
                Buddha_Http_Output::makeValue(3);
                //$isok['isok'] = '3';
            }
            $step2 = $this->existmobile($mobile);
            if(!$step2){
                Buddha_Http_Output::makeValue(4);
                //$isok['isok'] = '4';
            }
            $step3 = $this->verify($mobile, $code);
            if(!$step3){
                Buddha_Http_Output::makeValue(2);
                //$isok['isok'] = '2';
            }
            if ($step1 and $step2 and $step3) {
                $data = array();
                $data['username'] = $mobile;
                $data['mobile'] = $mobile;
                $data['mobile_ide'] = 1;
                $data['password'] = Buddha_Tool_Password::md5($password);
                $data['codes'] = $password;
                $data['groupid'] = $groupid;
                //$data['logo'] = "style/images/userlogo" . mt_rand(1,5) . ".png";
                $data['father_id'] = $father_id;
                if($groupid == 1){
                   $data['to_group_id'] = '4,3'.','.$groupid;
                }
                $data['state'] = 1;
                $data['onlineregtime'] = Buddha::$buddha_array['buddha_timestamp'];
                $insert_id_userId = $UserObj->add($data);
                $UserassoObj->addOrUpdateUserAsso($insert_id_userId,$father_id);
                //$urls = substr($_SERVER["REQUEST_URI"],1,stripos($_SERVER["REQUEST_URI"],'?'));
                if ($insert_id_userId) {
                    Buddha_Http_Cookie::setCookie('uid', $insert_id_userId,1);
                    $VerifyObj->hadPass($mobile,$code);
                    Buddha_Http_Output::makeValue(1);
                    //$isok['isok'] = '1';
                }
            }
            //Buddha_Http_Output::makeJson($isok);
        }
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
            $CommonObj = new Common();
            $rearrs = $CommonObj->getWeChatUserInformation();
            if($rearrs['subscribe'] !== 1 && $rearrs){
                $judge = 1;
                $this->smarty->assign("judge",$judge);
            }
        }
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty->display($TPL_URL.'.html');
    }

    /**
     * @param string $param_username
     * @return int
     * 检查用户名是否已经存在了
     */
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

    /**
     * @param string $param_mobile
     * @param string $param_code
     * @return int
     *  验证
     */
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

    /**
     * @param string $param_mobile
     * @return int
     * 检查手机号是否可以使用(存在)
     */
    public function existmobile($param_mobile=''){
         $Mobile='';
        if($param_mobile){
            $Mobile = $param_mobile;
        }else{
            $Mobile = Buddha_Http_Input::getParameter('Mobile');
        }
        $CountryCode = Buddha_Http_Input::getParameter('CountryCode');
        $UserObj = new User();
        $num = $UserObj->countRecords("isdel=0 and mobile='{$Mobile}'");

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

    /**
     * 发送验证码（验证请求）
     */
    public function  verifyrequest()
    {
        $nowtime = time();
        $json = Buddha_Http_Input::getParameter('json');
        $json_arr =Buddha_Atom_Array::jsontoArray($json);
        $mobile =$json_arr['mobile'];

        /**↓↓↓↓↓↓↓↓↓↓↓ 更新点击量 ↓↓↓↓↓↓↓↓↓↓↓**/
        if($this->existmobile($mobile)=='false'){
            $datas['isok']='false';
            $datas['msg']='对不起，该手机号已经注册过了！';
            Buddha_Http_Output::makeJson($datas);
        }
        /**↑↑↑↑↑↑↑↑↑↑ 更新点击量 ↑↑↑↑↑↑↑↑↑↑**/

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
           //$snedflag= $VerifyObj->smsSend($mobile,$data['code']);

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

            $datas = array();
            if(curl_exec($ch)>0){
                $datas['isok']='true';
                $datas['msg']='发送成功！';
                $VerifyObj->add($data);
            }else{
                $datas['isok']='false';
                $datas['msg']='发送失败！';
            }
            Buddha_Http_Output::makeJson($datas);
        }else{
            $datas['isok']='false';
            $datas['msg']='24小时内最多发10次验证码';
            Buddha_Http_Output::makeJson($datas);

        }


    }

    /**
     * forgotten pwd   被遗忘的密码
     */
    public function forgottenpwd(){
        $VerifyObj = new Verify();
        $UserObj = new User();

        if(Buddha_Http_Input::isPost()){
            $reg_mobile = Buddha_Http_Input::getParameter('reg_mobile');
            $reg_yanzheng = Buddha_Http_Input::getParameter('reg_yanzheng');
            $reg_pwd = Buddha_Http_Input::getParameter('reg_pwd');
            $check = $this->verify($reg_mobile,$reg_yanzheng);


            if($check){
                $VerifyObj->hadPass($reg_mobile,$reg_yanzheng);
                $data  = array();
                $data['password'] = Buddha_Tool_Password::md5($reg_pwd);
                $data['codes'] = $reg_pwd;
                $UserObj->updateRecords($data,"isdel=0 and mobile='{$reg_mobile}' and mobile_ide=1  ");
                echo 1;
                exit;
            }

           echo 0;

            exit;

        }


        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


}