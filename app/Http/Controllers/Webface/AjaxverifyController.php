<?php

/**
 * Class AjaxverifyController
 */
class AjaxverifyController extends Buddha_App_Action
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
    /****AJAX操作-用户名可用否********/
    public function registerByUserName()
    {
        $answer = array();
        $username = Buddha_Http_Input::getParameter('username');
        if (Buddha_Http_Input::checkParameter(array('username'))) {

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 1, '必填信息没填写');

        }
        $UserObj = new User();
        $usernum = $UserObj->countRecords("isdel=0 and username='{$username}' ");
        if ($usernum == 0) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 0, '用户名可用');

        } else {
            $answer['isok'] = 'false';
            $answer['msg'] = '用户名存在';
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 1, '用户名存在');
        }
    }
    /*****AJAX操作-邮箱可用否*******/
    public function registerByEmail()
    {
        $email = Buddha_Http_Input::getParameter('email');
        if (Buddha_Http_Input::checkParameter(array('email'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 1, '请输入正确的手机号');
        }
        $UserObj = new User();
        $usernum = $UserObj->countRecords("isdel=0 and email='{$email}' ");
        if ($usernum == 0) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 0, '邮箱可用');

        } else {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 1, '邮箱存在');
        }

    }
    /*****AJAX操作-手机可用否*******/
    public function registerByMobile()
    {
        $mobile = Buddha_Http_Input::getParameter('mobile');
        if (Buddha_Http_Input::checkParameter(array('mobile'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 1, '请输入正确的手机号');


        }
        $UserObj = new User();
        $usernum = $UserObj->hasMobile($mobile);
        if ($usernum == 0) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 0, '手机号可用');

        } else {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 1, '手机号存在');

        }
    }

    /**AJAX操作-获取邮箱验证码***/
    public function sendEmailVerifyCode()
    {
        $nowtime = time();
        $email = strtolower(Buddha_Http_Input::getParameter('email'));
        if (Buddha_Http_Input::checkParameter(array('email'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 1, '请输入正确的邮箱');


        }
        $data = array();
        $data['email'] = $email;
        $data['code'] = Buddha_Atom_String::getRandNumber(4);
        $data['ip'] = Buddha_Explorer_Network::getIp();
        $data['createtime'] = $nowtime;
        $data['createtimestr'] = date('Y-m-d H:i:s', $nowtime);
        $data['regtime'] = $nowtime;
        $data['buddhastatus'] = 0;
        $data['isdel'] = 0;
        $VerifyObj = new Verify();

        $createtime = $nowtime - 3600 * 24;
        $num1 = $VerifyObj->countRecords("ip='{$data['ip']}'  and createtime>=$createtime ");
        $num2 = $VerifyObj->countRecords("email='{$data['email']}'   and createtime>=$createtime ");
        if ($num1 < 20 and $num2 < 5 and $data['email']) {  //一个IP 一天注册人小于20人 同一个手机号最多一天让发5次验证码

            $sendflag = $VerifyObj->emailSend($email, $data['code']);
            if ($sendflag) {

                $VerifyObj->add($data);
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 0, '验证码已发送,注意接收');


            } else {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 1, '验证码发送失败,重新获取验证码');

            }
        }


    }

    /**AJAX操作-获取手机验证码**/
    public function sendMobileVerifyCode()
    {

        $answer = array();
        $nowtime = time();
        $mobile = Buddha_Http_Input::getParameter('mobile');
        if (Buddha_Http_Input::checkParameter(array('mobile'))) {

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 1, '请输入正确的手机号,重新获取验证码');

        }

        $data = array();
        $data['mobile'] = $mobile;
        $data['code'] = Buddha_Atom_String::getRandNumber(4);
        $data['ip'] = Buddha_Explorer_Network::getIp();
        $data['createtime'] = $nowtime;
        $data['createtimestr'] = date('Y-m-d H:i:s', $nowtime);
        $data['regtime'] = $nowtime;
        $data['buddhastatus'] = 0;
        $data['isdel'] = 0;
        $VerifyObj = new Verify();

        $createtime = $nowtime - 3600 * 24;
        $num1 = $VerifyObj->countRecords("ip='{$data['ip']}'  and createtime>=$createtime ");
        $num2 = $VerifyObj->countRecords("mobile='{$data['mobile']}'   and createtime>=$createtime ");
        if ($num1 < 20 and $num2 < 5 and $data['mobile']) {  //一个IP 一天注册人小于20人 同一个手机号最多一天让发5次验证码
            $sendflag = $VerifyObj->smsSend($mobile, $data['code']);

            if ($sendflag) {
                $VerifyObj->add($data);
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 0, '验证码已发送,注意接收');

            } else {
                $answer['isok'] = 'false';
                $answer['msg'] = '验证码发送失败,重新获取验证码';
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 1, '验证码发送失败,重新获取验证码');

            }

        }


    }



}