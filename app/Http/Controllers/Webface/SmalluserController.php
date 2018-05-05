<?php

/**
 * Class SmalluserController
 */
class SmalluserController extends Buddha_App_Action{



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

        $nickname = Buddha_Http_Input::getParameter('nickname');
        $oauth_id = Buddha_Http_Input::getParameter('oauth_id');
        $oauth_name = Buddha_Http_Input::getParameter('oauth_name');
        $oauth_access_token = Buddha_Http_Input::getParameter('oauth_access_token');
        if (Buddha_Http_Input::checkParameter(array('oauth_id', 'oauth_name', 'oauth_access_token'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }

        $num = $UseroauthObj->countRecords(" isdel=0 and oauth_id='{$oauth_id}' and oauth_name='{$oauth_name}'  ");


        $data = array();
        if ($num == 0) {
            $data['oauth_access_token'] = Buddha::$buddha_array['oauth_access_token'];
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $data['ip'] = Buddha_Explorer_Network::getIp();
            $data['oauth_expires'] = Buddha::$buddha_array['buddha_timestamp'] + 86400;

            $data['oauth_id'] = $oauth_id;
            $data['oauth_name'] = $oauth_name;
            $data['oauth_from'] = '虚拟会员';
            $data['oauth_access_token'] = $oauth_access_token;
            $third_id = $UseroauthObj->add($data);
            $third_user_id = 0;
            $third_is_bind = 0;

            //微信判断unionid 在系统有没有关联 如果有 此信息进行相同的管理 并且设置$third_is_bind=1
            $third_is_bind = $UseroauthObj->checkIsBindByUnionid($oauth_id, $oauth_name, $oauth_access_token);
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
            Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '虚拟会员登录 ');

        }

    }


}