<?php

/**
 * Class PlatController
 */
class PlatController extends Buddha_App_Action
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

    public function androidbendiinstall(){

/*        {
            "errcode": 0,
    "errmsg": "安装本地商家最新版",
    "data": {

            "is_force": 1,
        "is_force_what": "是否强制更新",

        "version": "2.0.2",
        "version_what": "版本",

        "desc": "本地商家网上线;\n大家来体验.",
        "des_what": "更新内容",

        "url: "http://api.bendishangjia.com/install/bendishangjia.apk",
        "url_what": "apk下载路径",

        "versionCode: "2",
        "versionCode_what": "版本号"
    },
    "other": "0",
    "action": "/webface/?Services=plat.androidinstall"
}*/

        $jsondata = array();
        $jsondata['is_force'] = 1;
        $jsondata['is_force_what'] = '是否强制更新';
        $jsondata['version'] = '2.0.7_bendishangjia_bendishangjia_bendishangjia';
        $jsondata['version_what'] = '版本';
        $jsondata['desc'] = '加入一分营销,大家来体验.';
        $jsondata['desc_what'] = '更新内容';
        $jsondata['url'] = 'http://api.bendishangjia.com/install/bendishangjia.apk';
        $jsondata['url_what'] = 'apk下载路径';
        $jsondata['versionCode'] = '10';
        $jsondata['versionCode_what'] = '版本号';



        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '安装本地商家最新版');

    }

    public function switchglob(){

        $SwitchglobObj = new Switchglob();

        $jsondata = array();
        $jsondata['is_openworldchat']= $SwitchglobObj->getIsOpenWorldChatInt();

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, 'APP全局开关');


    }





}