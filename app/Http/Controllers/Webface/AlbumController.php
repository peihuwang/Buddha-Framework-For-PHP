<?php

/**
 * Class AlbumController
 */
class AlbumController extends Buddha_App_Action
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


   public function deleteimage(){

       if (Buddha_Http_Input::checkParameter(array('usertoken','table_name','album_id'))) {
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
       }

       $MysqlplusObj = new Mysqlplus();
       $UserObj = new User();
       $AlbumObj = new Album();

       $album_id = Buddha_Http_Input::getParameter('album_id');
       $table_name = Buddha_Http_Input::getParameter('table_name');
       $usertoken = Buddha_Http_Input::getParameter('usertoken');

       $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
       $fieldsarray= array('id','usertoken');
       $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
       $user_id = $Db_User['id'];

       if(!$MysqlplusObj->isValidTable($table_name)){
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000001, 'good_table不存在');
       }

       if(strlen($table_name)<2 or $table_name=='album'){

           if(!$AlbumObj->isHasRecord($album_id)){
               Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000004, 'album_id不存在');
           }

       }

       /*if(strlen($table_name)>=2 and $table_name!='album'){
           if(!$MysqlplusObj->isValidTableId($table_name,$album_id)){
               Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000003, $table_name.' 内码id不存在');

           }
       }*/


     if(!$AlbumObj->isDeleteImageFileOk($album_id,$table_name,$user_id)){
         Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000005, $table_name.' 相册删除失败');
     }


       $jsondata = array();
       $jsondata['user_id'] = $user_id;
       $jsondata['usertoken'] = $usertoken;
       $jsondata['album_id'] = $album_id;
       $jsondata['table_name'] = $table_name;

       Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, ' 相册删除成功');
       

   }


    public function deleteSupplyImage(){

        if (Buddha_Http_Input::checkParameter(array('usertoken','gallery_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $GalleryObj = new Gallery();

        $gallery_id= Buddha_Http_Input::getParameter('gallery_id');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$GalleryObj->countRecords("id='{$gallery_id}'")){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000003, $gallery_id.' 内码id不存在');
        }
        if(!$GalleryObj->delGelleryimagesss($gallery_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000005, ' 相册删除失败');
        }


        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['gallery_id'] = $gallery_id;
        $jsondata['table_name'] = 'gallery';

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, ' 相册删除成功');


    }



}