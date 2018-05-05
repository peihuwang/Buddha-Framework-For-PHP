<?php

/**
 * Class ChatfridatasetController
 */
class ChatfridatasetController extends Buddha_App_Action
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

    public function vieworupdate(){




        if (Buddha_Http_Input::checkParameter(array('usertoken','friend_id','is_update'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $ChatfriendObj = new Chatfriend();
        $ChatfridatasetObj = new Chatfridataset();

        $usertoken   = Buddha_Http_Input::getParameter('usertoken');
        $is_update   = Buddha_Http_Input::getParameter('is_update');
        $friend_id   = Buddha_Http_Input::getParameter('friend_id');



        $is_star = Buddha_Http_Input::getParameter('is_star');//设为星标朋友
        $is_black = Buddha_Http_Input::getParameter('is_black');//加入黑名单

        $is_noseeme = Buddha_Http_Input::getParameter('is_noseeme');//不让她看我的朋友圈
        $is_noseeit = Buddha_Http_Input::getParameter('is_noseeit');//不看她的朋友圈






        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','gender');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $my_id = $user_id;



        if(!$ChatfriendObj->isMyFriend($my_id,$friend_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000062, '对方不是好友');
        }

        $fieldsarray= array('gender');
        $Db_FriendUser = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and id='{$friend_id}' ");
        $gender = $Db_FriendUser['gender'];
        if($gender==2){
            $genderdesc='她';
        }else{
            $genderdesc='他';
        }




        if($is_update){
            $ChatfridatasetObj->addOrUpdateStarData($user_id,$friend_id,$is_star);
            $ChatfridatasetObj->addOrUpdateBlackData($user_id,$friend_id,$is_black);
            $ChatfridatasetObj->addOrUpdateNoSeeMeData($user_id,$friend_id,$is_noseeme);
            $ChatfridatasetObj->addOrUpdateNoSeeItData($user_id,$friend_id,$is_noseeit);

        }

        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;


        $jsondata['friend_nickname_desc'] = '设置备注及标签';
        $jsondata['friend_nickname'] = $ChatfriendObj->getFriendNickName($my_id,$friend_id);

        $jsondata['is_star_desc'] = '设为星标朋友';
        $jsondata['is_star'] = $ChatfridatasetObj->isHasValidStarRecord($user_id,$friend_id);

        $jsondata['is_noseeme_desc'] = "不让{$genderdesc}看我的朋友圈";
        $jsondata['is_noseeme'] = $ChatfridatasetObj->isHasValidBlackRecord($user_id,$friend_id);

        $jsondata['is_noseeitdesc'] ="不看{$genderdesc}的朋友圈";
        $jsondata['is_noseeit'] = $ChatfridatasetObj->isHasValidNoSeeItRecord($user_id,$friend_id);

        $jsondata['is_black_desc'] ='加入黑名单';
        $jsondata['is_black'] = $ChatfridatasetObj->isHasValidBlackRecord($user_id,$friend_id);








        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'资料设置');



    }




}