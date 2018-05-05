<?php

class IndexController extends Buddha_App_Action{
    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

        //权限
        $webface_access_token = Buddha_Http_Input::getParameter('webface_access_token');
        if ($webface_access_token == '') {
            Buddha_Http_Output::makeWebfaceJson(null, '/webshopface/?Services=' . $_REQUEST['Services'], 4444002, 'webface_access_token必填');
        }

        $ApptokenObj = new Apptoken();
        $num = $ApptokenObj->getTokenNum($webface_access_token);
        if ($num == 0) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webshopface/?Services=' . $_REQUEST['Services'], 4444003, 'webface_access_token不正确请从新获取');
        }
    }


    /**
     * 首页代理商电话
     */
    public function index(){
        if(Buddha_Http_Input::checkParameter(array('api_number'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $api_number = Buddha_Http_Input::getParameter('api_number');
        $UserObj = new User();
        $RegionObj = new Region();
        $api_number = $RegionObj -> getSingleFiledValues(array('id'),"number='{$api_number}'");
        $referral=$UserObj->getSingleFiledValues(array('tel'),"isdel=0 and groupid='2' AND level3='{$api_number['id']}'");
        $jsondata = array();
        $jsondata['tel'] = $referral['tel'];
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webshopface/?Services='.$_REQUEST['Services'],0,'首页代理商电话');
    }

}