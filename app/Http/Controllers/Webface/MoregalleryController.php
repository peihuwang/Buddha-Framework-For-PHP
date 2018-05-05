<?php

/**
 * Class AlbumController
 */
class MoregalleryController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));


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
     * 相册删除
     * table_name   来源于哪一张表的名称
     * moregallery_id   对应图片ID
     */
   public function deleteimage()
   {
       if (Buddha_Http_Input::checkParameter(array('usertoken','table_name','moregallery_id')))
       {
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
       }

       $MysqlplusObj = new Mysqlplus();
       $UserObj = new User();
       $MoregalleryObj = new Moregallery();

       $moregallery_id = Buddha_Http_Input::getParameter('moregallery_id');
       $table_name = Buddha_Http_Input::getParameter('table_name');
       $usertoken = Buddha_Http_Input::getParameter('usertoken');

       $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
       $fieldsarray= array('id','usertoken');
       $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
       $user_id = $Db_User['id'];

       if(!$MysqlplusObj->isValidTable($table_name))
       {
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000001, 'good_table不存在');
       }

       if(strlen($table_name)<2 or $table_name=='moregallery')
       {
           if(!$MoregalleryObj->isHasRecord($moregallery_id)){
               Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000010, 'moregallery_id 不存在');
           }
       }


       if(strlen($table_name)>=2 and $table_name!='moregallery')
       {
           if(!$MysqlplusObj->isValidTableId($table_name,$moregallery_id)){
               Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000010, $table_name.' 内码id不存在');
           }
       }


     if(!$MoregalleryObj->isDeleteImageFileOk($moregallery_id,$table_name,$user_id))
     {
         Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000011, $table_name.' moregallery 相册删除失败');
     }


       $jsondata = array();
       $jsondata['user_id'] = $user_id;
       $jsondata['usertoken'] = $usertoken;
       $jsondata['moregallery_id'] = $moregallery_id;
       $jsondata['table_name'] = $table_name;

       Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, 'moregallery 相册删除');
       

   }



}