<?php

/**
 * Class AppController
 */
class AppController extends Buddha_App_Action{


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

    public function tip(){
        if(Buddha_Http_Input::checkParameter(array('find'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],444444,'必填信息没填写');
        }
        $find = strtolower(Buddha_Http_Input::getParameter('find'));
        $AppObj = new App();
        $jsondata=array();
        $num =$AppObj->countRecords("isdel=0 and find='{$find}' ");
        if($num){
            $Db_App = $AppObj->getSingleFiledValues('',"isdel=0 and find='{$find}' ");
            $jsondata['title']=$Db_App['title'];
            $jsondata['keywords']=$Db_App['keywords'];
            $jsondata['description']=$Db_App['description'];
        }  else{
            $jsondata['title']='标题';
            $jsondata['keywords']='关键字';
            $jsondata['description']='描述';
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'项目页面搜索引擎信息');

    }




}