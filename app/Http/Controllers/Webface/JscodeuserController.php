<?php

/**
 * Class JscodeuserController
 */
class JscodeuserController extends Buddha_App_Action{



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


    }

    public function  login()
    {
        $UseroauthObj = new Useroauth();
        $UserObj = new User();

        if (Buddha_Http_Input::checkParameter(array('code'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }

        $code = Buddha_Http_Input::getParameter('code');


        $checkurl = "https://api.weixin.qq.com/sns/jscode2session?appid=wx5ef16e81168ff848&secret=df478b456e11afe394b1ecc67f62f612&js_code={$code}&grant_type=authorization_code";
        $result = Buddha_Explorer_Curl::http_get($checkurl);
        if($result) {
            $json = json_decode($result, true);

        }

        //{"errcode":40163,"errmsg":"code been used, hints: [ req_id: ltSJYa0613ns89 ]"}
        //{"session_key":"\/Ei2k8+u+XOul3D9Wlt+NA==","expires_in":7200,"openid":"o1p7v0ENnAyZWq0UJzYy3RVYES9U"}

//        $json['openid']='1111';
//        $json['session_key']='111';

       if(!isset($json['openid']) ){
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, $json['errmsg']);
       }

        $nickname = $json['openid'];
        $oauth_id = $json['openid'];
        $oauth_name = 'wechat';
        $oauth_access_token=$json['session_key'];

        $num = $UseroauthObj->countRecords(" isdel=0 and oauth_id='{$oauth_id}' and oauth_name='{$oauth_name}'  ");


        $data = array();
        if ($num == 0) {
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $data['ip'] = Buddha_Explorer_Network::getIp();
            $data['oauth_expires'] = Buddha::$buddha_array['buddha_timestamp'] + 86400;

            $data['oauth_id'] = $oauth_id;
            $data['oauth_name'] = $oauth_name;
            $data['oauth_from'] = '小程序';
            $data['oauth_access_token'] = $oauth_access_token;
           
           $third_id = $UseroauthObj->add($data);

            $third_user_id = 0;
            //微信判断unionid 在系统有没有关联 如果有 此信息进行相同的管理 并且设置$third_is_bind=1
            $third_is_bind=0;
            if($third_is_bind==0){
                $userdata = array();


                $userdata['groupid'] = 4;
                if($nickname)
                    $userdata['nickname'] =$nickname;

                $userdata['password'] = Buddha_Tool_Password::md5($nickname);
                $userdata['codes'] =$nickname;
                $userdata['username'] =$nickname;
                $userdata['state'] = 1;
                $userdata['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                $userdata['createtimestr'] =  Buddha::$buddha_array['buddha_timestr'];
                $user_id = $UserObj->add($userdata);


                $UseroauthObj->edit(array('user_id'=>$user_id,'is_bind'=>1),$third_id);


            }

            $third_operator = 'add';
            $third_is_bind = 1;

        } else {
            $Db_Useroauth = $UseroauthObj->getSingleFiledValues(array('id', 'is_bind', 'user_id'), " isdel=0 and  oauth_id='{$oauth_id}' and oauth_name='{$oauth_name}'  ");
            $third_id = $Db_Useroauth['id'];
            $third_user_id = $Db_Useroauth['user_id'];
            $third_operator = 'update';
            $data['oauth_access_token'] = Buddha::$buddha_array['oauth_access_token'];
            $third_is_bind = $Db_Useroauth['is_bind'];
            $data['ip'] = Buddha_Explorer_Network::getIp();
            $data['oauth_expires'] = Buddha::$buddha_array['buddha_timestamp'] + 86400;
            $UseroauthObj->edit($data, $third_id);


        }


        $jsondata = array();
        if ($third_is_bind == 1) {
            $Db_User = $UserObj->getSingleFiledValues(" isdel=0 and id='{$third_user_id}' ");
            $usertoken = $Db_User['usertoken'];
            $jsondata['user_id'] = $Db_User['id'];
            $jsondata['usertoken'] = $usertoken;
            Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '微信小程序登录 ');

        }

    }


}