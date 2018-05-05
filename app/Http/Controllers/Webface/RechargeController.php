<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/6
 * Time: 15:55
 */
class RechargeController extends Buddha_App_Action
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

    /**
     * 赏金充值
     */

    public function bonusrecharge(){
        $UserObj = new User();
        $RechargeObj = new Recharge();
        if(Buddha_Http_Input::checkParameter(array('usertoken','money','modepay'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],444444,'必填信息没填写');
        }
        $modepay = Buddha_Http_Input::getParameter('modepay');
        $money = Buddha_Http_Input::getParameter('money');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','email',
            'level1','level2','level3','address',
            'username'
        );
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
    }

    /**
     * 赏金余额
     */
    public function balancebounty(){
        $host = Buddha::$buddha_array['host'];
        $UserObj = new User();
        $RechargeObj = new Recharge();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        if(Buddha_Http_Input::checkParameter(array('usertoken'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','email',
            'level1','level2','level3','address',
            'username'
        );
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $rechargeFields = array('id as recharge_id','uid','balance');
        $rechargeinfo = $RechargeObj->getSingleFiledValues($rechargeFields,"uid={$user_id}");
        $rechargeinfo['rechargeicon'] = $host . "appuser/menuplus/shangjishezhi.png";
        $rechargeinfo['rechargeimg'] = $host . "appuser/menuplus/yu_e.png";
        $jsondata = array();
        $jsondata = $rechargeinfo;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'赏金余额');
    }


    /**
     * 赏金设置
     */
    public function bonusset(){
        $UserObj = new User();
        $RechargeObj = new Recharge();
        if(Buddha_Http_Input::checkParameter(array('usertoken','forwarding_money','is_open'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],444444,'必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $forwarding_money = Buddha_Http_Input::getParameter('forwarding_money');
        $starttimestr = Buddha_Http_Input::getParameter('starttimestr');
        $endtimestr = Buddha_Http_Input::getParameter('endtimestr');
        $is_open = (int)Buddha_Http_Input::getParameter('is_open');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','email',
            'level1','level2','level3','address',
            'username'
        );
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $rechargeinfo = $RechargeObj->getSingleFiledValues('',"uid='{$user_id}'");
        $data = array();
        $data['forwarding_money'] = $forwarding_money;
        $data['is_open'] = $is_open;
        if($starttimestr){
            $starttime = strtotime($starttimestr);
            $data['starttimestr'] = $starttimestr;
            $data['starttime'] = $starttime;
        }
        if($endtimestr){
            $endtime = strtotime($endtimestr);
            $data['endtimestr'] = $endtimestr;
            $data['endtime'] = $endtime;
        }
        $re = $RechargeObj->edit($data,$rechargeinfo['id']);
        if($re){
            $jsondata['info'] = '设置成功';
        }else{
            $jsondata['info'] = '服务器忙或者您还没有充值';
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'赏金设置');

    }










}