<?php

/**
 * Class ChatfriendController
 */
class ChatfriendController extends Buddha_App_Action
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


    public function searchfriendoutside(){

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','mobile'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $ChatfriendObj = new Chatfriend();
        $UserObj = new User();

        $mobile = Buddha_Http_Input::getParameter('mobile');
        $friend_id = (int)Buddha_Http_Input::getParameter('friend_id');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        //如果搜索的是好友内码Id
        if($friend_id>0){

            if(!$UserObj->isValidUserId($friend_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000001, '该friend_id用户不存在');
            }

            $Db_User_Search = $UserObj->getSingleFiledValues(array('mobile','logo','id as friend_id','realname')," isdel=0 and id='{$friend_id}' ");


        }else{
            if(!$UserObj->isHasMobile($mobile)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000001, '该用户不存在');
            }

            $Db_User_Search = $UserObj->getSingleFiledValues(array('mobile','logo','id as friend_id','realname')," isdel=0 and mobile='{$mobile}' ");
        }




        $my_id = $user_id;
        $friend_id = $Db_User_Search['friend_id'];
        $jsondata = array();

        $isfriend = 0 ;
        if($ChatfriendObj->isMyFriend($my_id,$friend_id)){

            $isfriend = 1;

        }





        $logo = $Db_User_Search['logo'];
        if(Buddha_Atom_String::isValidString($logo)){

            if(!Buddha_Atom_String::hasNeedleString($logo,'http')){

                $logo = $host.$logo;
            }else{
                $logo = Buddha_Atom_String::getAfterReplaceStr($logo,'http','https');
            }


        }else{
            $logo = $host."resources/worldchat/portrait/default.png";
        }
        $jsondata['logo'] = $logo;


        $nickname =  '';

        if(Buddha_Atom_String::isValidString($Db_User_Search['realname'])){
            $nickname = $Db_User_Search['realname'];
        }



        if(!Buddha_Atom_String::isValidString($nickname)){
            $nickname = $Db_User_Search['mobile'];
        }




        $jsondata['isfriend'] = $isfriend;
        $jsondata['mobile'] = $Db_User_Search['mobile'];
        $jsondata['friend_id'] = $Db_User_Search['friend_id'];
        $jsondata['message'] = '我是'.$Db_User_Search['realname'];
        $jsondata['nickname'] = $nickname;




        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'添加朋友');




    }

    public function addressbook(){

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','mobile','message'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $ChatfriendObj = new Chatfriend();
        $ChatfriendmsgObj = new  Chatfriendmsg();

        $mobile = Buddha_Http_Input::getParameter('mobile');
        $message = Buddha_Http_Input::getParameter('message');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$UserObj->isHasMobile($mobile)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000001, '该用户不存在');
        }

        $Db_User_Search = $UserObj->getSingleFiledValues(array('mobile','logo','id as friend_id','realname')," isdel=0 and mobile='{$mobile}' ");

        $my_id = $user_id;
        $friend_id = $Db_User_Search['friend_id'];
        if($ChatfriendObj->isMyFriend($my_id,$friend_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000060, '该好友已经在通讯录里');
        }


        /* 发送好友验证消息 */
        $send_id = $my_id;
        $receive_id = $friend_id;



        if($my_id==$friend_id){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '不能自己加自己为好友');
        }

/*        if($ChatfriendmsgObj->isFriendMsgHadPass($send_id,$receive_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000060, '该好友已经在通讯录里');
        }*/


        Buddha_Thirdpart_Message::getInstance()->removeBlacklist($send_id,$receive_id);
        //如果对方的朋友有我,那么我加对方好友就直接成功就行
        if($ChatfriendObj->isMyFriend($receive_id,$send_id)){
            $ChatfriendObj->isMakeFriendSuccess($send_id,$receive_id)   ;
        }else{

            /* 如果好友验证消息已经发送 就更新发送的好友消息 如果未通过就进行发送好友消息通知 */
            if($ChatfriendmsgObj->isFriendMsgHadSend($receive_id,$send_id)){

                $ChatfriendmsgObj->updateFriendMsg($receive_id,$send_id,$message);

            }else{

                $ChatfriendmsgObj->addFriendMsg($receive_id,$send_id,$message);
            }
        }




        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'添加通讯录');



    }

    public function newfriendmore(){

        $host = Buddha::$buddha_array['host'];


        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $ChatfriendObj = new Chatfriend();
        $ChatfriendmsgObj = new  Chatfriendmsg();

        $mobile = Buddha_Http_Input::getParameter('mobile');
        $message = Buddha_Http_Input::getParameter('message');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        $page = (int)Buddha_Http_Input::getParameter('page') ? (int)Buddha_Http_Input::getParameter('page') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);


        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $my_id = $user_id;

        $where = " cf.receive_id ='{$my_id}' ";
        $orderby = " ORDER BY  cf.createtime DESC ";


        /* 查询无效的消息,如果有再进行删除*/
        $delsql  =" DELETE  FROM {$this->prefix}chatfriendmsg  WHERE msgid
                   IN (select n.msgid from  (SELECT cf.msgid FROM {$this->prefix}chatfriendmsg as cf
                        LEFT JOIN {$this->prefix}user as u
                        ON cf.send_id = u.id
                        WHERE  {$where} AND u.id is NULL) as n)
                         ";

        $this->db->query($delsql)->fetchAll(PDO::FETCH_ASSOC);




        $sql = "select count(*) as total from {$this->prefix}chatfriendmsg as cf where {$where} ";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }

        $limit = Buddha_Tool_Page::sqlLimit($page, $pagesize);
        $sql  =" SELECT cf.message,cf.receive_id,cf.send_id,cf.msgid,
                        cf.buddhastatus,cf.createtime,cf.createtimestr,
                        u.logo,u.mobile,u.realname
                FROM {$this->prefix}chatfriendmsg AS cf
                        LEFT JOIN {$this->prefix}user as u
                        ON cf.send_id = u.id
                        WHERE  {$where}{$orderby} {$limit}
                 ";
        $Db_Chatfriendmsg = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);



        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        if($rcount){
            $jsondata['totalrecord'] = $rcount;
        }else{
            $jsondata['totalrecord'] = 0;
        }
        if($pcount){
            $jsondata['totalpage'] = $pcount;
        }else{
            $jsondata['totalpage'] = 0;
        }


        if(Buddha_Atom_Array::isValidArray($Db_Chatfriendmsg)){

            foreach($Db_Chatfriendmsg as $k=>$v){

                $logo = $v['logo'];
                if(Buddha_Atom_String::isValidString($logo)){

                    if(!Buddha_Atom_String::hasNeedleString($logo,'http')){

                        $logo = $host.$logo;
                    }else{
                        $logo = Buddha_Atom_String::getAfterReplaceStr($logo,'http','https');
                    }


                }else{
                    $logo = $host."resources/worldchat/portrait/default.png";
                }

                $buddhastatus = $v['buddhastatus'];

                $msg = '';
                $v['acceptservices'] = array('Services'=>'','param'=>array());
                if($buddhastatus==1){
                    $msg = '接受';
                    $param = array('msgid'=>$v['msgid']);
                    $v['acceptservices'] = array('Services'=>'chatfriend.acceptnewfriend','param'=>$param);
                }
                if($buddhastatus==6){
                    $msg = '已添加';
                }

                $v['logo'] = $logo;
                $v['msg'] = $msg;


                if(!Buddha_Atom_String::isValidString($v['mobile'])){
                    $v['mobile']='';
                }

                if(!Buddha_Atom_String::isValidString($v['realname'])){
                    $v['realname']='';
                }

                $param = array('msgid'=>$v['msgid']);
                $v['deleteservices'] = array('Services'=>'chatfriend.deletenewfriendmsg','param'=>$param);
                $jsondata['list'][] = $v;




            }

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'新朋友消息列表');



    }

    public function acceptnewfriend(){

        if (Buddha_Http_Input::checkParameter(array('usertoken','msgid'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $ChatfriendmsgObj = new  Chatfriendmsg();


        $msgid = Buddha_Http_Input::getParameter('msgid');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $receive_id = $user_id;

        if(!$ChatfriendmsgObj->isReceiveMsg($receive_id,$msgid)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000061, '新好友消息不是本人所有');
        }

        $ChatfriendmsgObj->updateRecords(array('buddhastatus'=>6)," receive_id='{$receive_id}' AND msgid='{$msgid}'  ");

        /*通过接收消息人的内码id 和 新朋友消息内码id 来处理接口朋友*/


        $ChatfriendmsgObj->acceptFriend($receive_id,$msgid);

        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '同意加好友');

    }

    public function deletenewfriendmsg(){


        if (Buddha_Http_Input::checkParameter(array('usertoken','msgid'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $ChatfriendmsgObj = new  Chatfriendmsg();


        $msgid = Buddha_Http_Input::getParameter('msgid');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $receive_id = $user_id;

        if(!$ChatfriendmsgObj->isReceiveMsg($receive_id,$msgid)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000061, '新好友消息不是本人所有');
        }

        $ChatfriendmsgObj->delRecords(" receive_id='{$receive_id}' AND msgid='{$msgid}'  ");

        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '删除新朋友消息');



    }

    public function friendmore(){
        $host = Buddha::$buddha_array['host'];


        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();


        $usertoken = Buddha_Http_Input::getParameter('usertoken');




        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $my_id = $user_id;

        $sql  =" SELECT cf.my_id,cf.friend_id,cf.friend_nickname,
                        u.logo,u.mobile,u.realname,u.nickname
                FROM {$this->prefix}chatfriend AS cf
                        LEFT JOIN {$this->prefix}user as u
                        ON cf.friend_id = u.id
                        WHERE cf.my_id= '{$my_id}'
                       limit 0,500
                 ";
        $Db_Chatfriend = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);



        $jsondata = array();
        if(Buddha_Atom_Array::isValidArray($Db_Chatfriend)){

            foreach($Db_Chatfriend as $k=>$v){

                $logo = $v['logo'];
                if(Buddha_Atom_String::isValidString($logo)){

                    if(!Buddha_Atom_String::hasNeedleString($logo,'http')){

                        $logo = $host.$logo;
                    }else{
                        $logo = Buddha_Atom_String::getAfterReplaceStr($logo,'http','https');
                    }


                }else{
                    $logo = $host."resources/worldchat/portrait/default.png";
                }

                $v['logo']  = $logo;
                $nickname =  '';

                if(Buddha_Atom_String::isValidString($v['realname'])){
                    $nickname = $v['realname'];
                }

                if(Buddha_Atom_String::isValidString($v['friend_nickname'])){
                    $nickname = $v['friend_nickname'];
                }

                if(!Buddha_Atom_String::isValidString($nickname)){
                    $nickname = $v['mobile'];
                }

                unset( $v['realname']);
                unset( $v['nickname']);
                $v['friend_nickname']  = $nickname;
                $v['first']  = Buddha_Atom_String::getFirstCharter($nickname);
                $jsondata[] = $v;
            }

        }






        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'通讯录列表');
    }

    public function friendremark(){
        $host = Buddha::$buddha_array['host'];


        if (Buddha_Http_Input::checkParameter(array('usertoken','friend_id','friend_nickname'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $ChatfriendObj = new Chatfriend();

        $friend_id = Buddha_Http_Input::getParameter('friend_id');
        $friend_nickname = Buddha_Http_Input::getParameter('friend_nickname');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');




        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $my_id = $user_id;

        if(!$ChatfriendObj->isMyFriend($my_id,$friend_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000062, '对方不是好友');
        }


        $ChatfriendObj->isModifyNickname($my_id,$friend_id,$friend_nickname);


        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '好友备注');


    }

    public function deletefriend(){
        $host = Buddha::$buddha_array['host'];


        if (Buddha_Http_Input::checkParameter(array('usertoken','friend_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $ChatfriendObj = new Chatfriend();

        $friend_id = Buddha_Http_Input::getParameter('friend_id');
        $friend_nickname = Buddha_Http_Input::getParameter('friend_nickname');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');




        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $my_id = $user_id;

        if(!$ChatfriendObj->isMyFriend($my_id,$friend_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000062, '对方不是好友');
        }


        $ChatfriendObj->isDeleteFriend($my_id,$friend_id);


        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '好友备注');


    }


}