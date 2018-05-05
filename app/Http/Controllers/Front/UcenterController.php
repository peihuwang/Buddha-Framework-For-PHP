<?php

/**
 * Class UcenterController
 */
class UcenterController extends Buddha_App_Action{
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
    }
    /**
     * 获取当前页面完整URL地址
     */
    public function hsk_wx_get_url() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }
    /**
     * php转发post函数
     */
    public function https_request($url){
        
        $curl = curl_init();  //初始化一个cURL会话
        
        //设置请求选项, 包括具体的url
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);  //禁用后cURL将终止从服务端进行验证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl,CURLOPT_HEADER,0); //??
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10); //??
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);      
        $response = curl_exec($curl);  
        curl_close($curl);
        
        $jsoninfo = json_decode($response,true); 
        return $jsoninfo;
    }
    /*public function wxLogin(){
        $WxuserObj = new Wxuser();
        if(!$openid && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
            $WechatconfigObj = new Wechatconfig();
            $wechatcon = $WechatconfigObj->getSingleFiledValues(array('appsecret','appid','accesstoken'),"type='wechat' and son='token'");
            $appid = $wechatcon['appid'];
            $appsecret = $wechatcon['appsecret'];
            $openid = $_COOKIE['openid'];
            if (!isset($_GET['code'])){
                $backurl = $this->hsk_wx_get_url();
                $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($backurl)."&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect";
                Header("Location: $url");
            }else{
                //获取code码，以获取openid
                $code = $_GET['code'];
                $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
                $re = file_get_contents($url);
                $rearr = json_decode($re,true);
                $unionid = $rearr['unionid'];
                $counts = $WxuserObj->countRecords("unionid='{$unionid}'");
                if(!$counts){
                    $access_token = $rearr['access_token'];
                    $openid = $rearr['openid'];
                    // 判断是否已经关注公众号
                    $subscribe_msg = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid;
                    $subscribe = $this->https_request($subscribe_msg);
                    $zyxx = $subscribe['subscribe'];
                    if($zyxx !== 1){
                        header("Location:https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzIxMDY2ODM2NQ%3D%3D#wechat_redirect");
                        exit;
                    }
                    $userinfo_url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN"; 
                    $user_info = $this->https_request($userinfo_url);
                    if(!$user_info['errcode']){
                        $data['openid'] = $user_info['openid'];
                        $data['nickname'] = $user_info['nickname'];
                        $data['sex'] = $user_info['sex'];
                        $data['province'] = $user_info['province'];
                        $data['city'] = $user_info['city'];
                        $data['head_pic'] = $user_info['headimgurl'];
                        $data['unionid'] = $user_info['unionid'];
                        $data['reg_time'] = time();
                        $data['oauth'] = 'weixin';
                        $re = $WxuserObj->add($data);
                    }
                }
                //print_r($data);
                $setopenid = $rearr['openid'];
                $unionid = $rearr['unionid'];
                //$_COOKIE['unionid']=$unionid;
                Buddha_Http_Cookie::setCookie('unionid', $unionid,1);
                //print_r($_COOKIE);
            }
        }
    }*/
    public function index(){
        /*$this->wxLogin();
        var_dump($_COOKIE);*/
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        //$unionid = Buddha_Http_Cookie::getCookie('unionid');
        if(!$uid){// && !$unionid
            Buddha_Http_Head::jump('/index.php?a=login&c=account');
        }//elseif($unionid){
            //$url='/user/index.php?a=index&c=user';
            //Buddha_Http_Head::jump($url);
            //exit;
        //}
        $_SESSION['groupid'] = '';
        $j = $_GET['j'];
        if($j){
            $to_group_id = explode(',',$UserInfo['to_group_id']);
            if(in_array($j,$to_group_id)){
                if($j==1){
                    $_SESSION['groupid'] = 1;
                    $url='/business/index.php?a=index&c=business';
                }elseif($j==2){
                    $_SESSION['groupid'] = 2;
                    $url='/agent/index.php?a=index&c=agent';
                }elseif($j==3){
                    $_SESSION['groupid'] = 3;
                    $url='/partner/index.php?a=index&c=partner';
                }elseif($j==4){
                    $_SESSION['groupid'] = 4;
                    $url='/user/index.php?a=index&c=user';
                }
                Buddha_Http_Head::jump($url);
                exit;
            }else{
                echo '<script>alert("您还没有申请此角色,请在个人中心提交您的申请！")</script>';
                echo"<script>history.go(-1);</script>";
                exit; 
            }
        }
        if($UserInfo['groupid']==1){
            $url='/business/index.php?a=index&c=business';
        }elseif($UserInfo['groupid']==2){
            $url='/agent/index.php?a=index&c=agent';
        }elseif($UserInfo['groupid']==3){
             $url='/partner/index.php?a=index&c=partner';
        }else{
             $url='/user/index.php?a=index&c=user';
        }

        Buddha_Http_Head::jump($url);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

     public function partnre_apply(){//判断合伙人资料是否完整
        $j = $_GET['j'];
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $userObj = new User();
        if(stripos($UserInfo['to_group_id'],$j)){
            $datas['status'] = 0;
        }else{
            if($UserInfo['realname'] && $UserInfo['level1']){//判断真实姓名和地区是否存在
                if(stripos($UserInfo['to_group_id'],'1')){
                    $data['to_group_id'] = $UserInfo['to_group_id'] . ',' . $j;
                }else{
                    $data['to_group_id'] = $UserInfo['to_group_id'] . ',' . '1' . ',' . $j; 
                }
                
                $re = $userObj -> updateRecords($data,"id = {$uid}");
                if($re){
                    $datas['status'] = 1;;
                    
                }else{
                    $datas['status'] = 2;
                }
            }else{
                $datas['status'] = 3;
            }
        }
        Buddha_Http_Output::makeJson($datas);
    }

    public function role_changing(){//添加角色
        $j = $_GET['j'];
        $uid = Buddha_Http_Cookie::getCookie('uid');
        $userObj = new User();
        $UserInfo=$userObj->getSingleFiledValues(array('groupid','to_group_id'),"id={$uid}");
        if($UserInfo['gropuid'] == $j){
            if(strstr($UserInfo['to_group_id'],$j)){

            }else{
               $data['to_group_id'] = $UserInfo['to_group_id'] . ',' . $j;
                $re = $userObj -> updateRecords($data,"id = {$uid}");
                echo '<script>alert("您已存在此角色！")</script>';
                echo"<script>history.go(-1);</script>";
                exit; 
            }
        }
        if(stripos($UserInfo['to_group_id'],$j)){
            echo '<script>alert("您已存在此角色！")</script>';
            echo"<script>history.go(-1);</script>";
            exit;
        }else{
            $data['to_group_id'] = $UserInfo['to_group_id'] . ',' . $j;
            $re = $userObj -> updateRecords($data,"id = {$uid}");
            if($re){

                echo '<script>alert("角色添加成功！")</script>';
                echo"<script>history.go(-1);</script>";
            }else{
                echo '<script>alert("服务器忙！")</script>';
                echo"<script>history.go(-1);</script>";
            }
        }
    }

}