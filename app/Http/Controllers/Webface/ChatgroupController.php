<?php

/**
 * Class ChatgroupController
 */
class ChatgroupController extends Buddha_App_Action
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


    public function groupcreate(){
        $host = Buddha::$buddha_array['host'];



        if (Buddha_Http_Input::checkParameter(array('usertoken','friend_id_arr'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $ChatfriendObj = new Chatfriend();


        $friend_id_arr = Buddha_Http_Input::getParameter('friend_id_arr');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');


/*
        $arr[]='3903';
        $arr[]='10570';
        $arr[]='6545';
        $friend_id_arr = $arr;*/

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $my_id = $user_id;

        if(Buddha_Atom_String::isJson($friend_id_arr)){
            $friend_id_arr = Buddha_Atom_Json::decodeJsonToArr($friend_id_arr);
        }


        if(count($friend_id_arr)<2){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000063, '创群人数至少加自己在内要3个人');
        }


        if(!$ChatfriendObj->isValidFriendIdArr($my_id,$friend_id_arr)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000064, '进群的会员不是自己的好友');
        }






        $new_friend_id_arr = Buddha_Atom_Array::getInsertHeadElementArr($friend_id_arr,  $my_id);


        /*确保每个好友都有chattoken 方便进行聊天*/
        if(Buddha_Atom_Array::isValidArray($new_friend_id_arr)){

            foreach($new_friend_id_arr as $k=>$v){
                $temp_user_id = $v;

                if($UserObj->getUserHasWorldChatTokenStr($temp_user_id)==0){

                   $UserObj->setUserWorldChatTokenIfNull($temp_user_id);

                }
            }

        }


        $isOk = Buddha_Thirdpart_Message::getInstance()->groupCreate($new_friend_id_arr);

        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['groupId'] = $isOk;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '创建群组');


    }

    public function groupmore(){


        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $ChatgroupmemberObj = new Chatgroupmember();



        $usertoken = Buddha_Http_Input::getParameter('usertoken');




        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $my_id = $user_id;

        $where = " cgm.account_id='{$my_id}' ";
        $sql  =" SELECT cg.groupId,cg.name,cg.founder_id,cg.membertotal

                FROM {$this->prefix}chatgroup AS cg
                        LEFT JOIN {$this->prefix}chatgroupmember as cgm

                        ON cg.groupId = cgm.groupId

                        where $where

                        order by cgm.vieworder desc ,cgm.gid asc

                       limit 0,500
                 ";
        $Db_Chatgroup = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);


        $jsdata = array();

        if(Buddha_Atom_Array::isValidArray($Db_Chatgroup)){

            foreach($Db_Chatgroup as $k=>$v){

                $temp_groupId = $v['groupId'];
                $Db_Chatgroup[$k]['logolist'] = $ChatgroupmemberObj->getMaxNineLogo($temp_groupId);




            }

        }



        Buddha_Http_Output::makeWebfaceJson($Db_Chatgroup, '/webface/?Services=' . $_REQUEST['Services'], 0, '群列表');

    }

    public function grouprefresh(){
        if (Buddha_Http_Input::checkParameter(array('usertoken','groupId','newGroupName'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $ChatgroupObj = new Chatgroup();


        $groupId = Buddha_Http_Input::getParameter('groupId');
        $newGroupName = Buddha_Http_Input::getParameter('newGroupName');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');


        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $my_id = $user_id;
        $founder_id = $my_id;

        if(!$ChatgroupObj->isFounderGroup($founder_id,$groupId)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000065, '操作人不是群主');

        }

        $isOk = Buddha_Thirdpart_Message::getInstance()->groupRefresh($groupId, $newGroupName);

        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '群组重命名');

    }

    public function groupmemberout(){

        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('usertoken','groupId'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }



        $UserObj = new User();
        $ChatgroupmemberObj = new Chatgroupmember();


        $groupId = Buddha_Http_Input::getParameter('groupId');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');


        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $my_id = $user_id;


        /* 查询无效的消息,如果有再进行删除*/
        $delsql  =" DELETE  FROM {$this->prefix}chatfriend  WHERE id
                   IN (select n.id from  (SELECT cf.id FROM {$this->prefix}chatfriend as cf
                        LEFT JOIN {$this->prefix}user as u
                        ON cf.friend_id = u.id
                        WHERE  cf.my_id='{$my_id}' AND u.id is NULL) as n)
                         ";



        $this->db->query($delsql)->fetchAll(PDO::FETCH_ASSOC);


        $where = " cf.my_id='{$my_id}'  ";
        $sql  =" SELECT cf.my_id,cf.friend_id,cf.friend_nickname,
                        u.logo,u.mobile,u.realname,u.nickname
                FROM {$this->prefix}chatfriend AS cf
                        LEFT JOIN {$this->prefix}user as u
                        ON cf.friend_id = u.id

                  WHERE $where

                       limit 0,500
                 ";

        $Db_Chatfriend = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);


        $In_Chatgroupmember_arr = $ChatgroupmemberObj->getGroupMemberIdArr($groupId);



        $jsondata = array();
        if(Buddha_Atom_Array::isValidArray($Db_Chatfriend)){

            foreach($Db_Chatfriend as $k=>$v){

                $friend_id = $v['friend_id'];
                $isInGroup = 0;
                if(Buddha_Atom_Array::isKeyExists($friend_id,$In_Chatgroupmember_arr)){
                $isInGroup = 1;
                }



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
                $v['isInGroup']  = $isInGroup;
                $v['friend_nickname']  = $nickname;
                $v['first']  = Buddha_Atom_String::getFirstCharter($nickname);
                $jsondata[] = $v;
            }

        }



        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'拉好友进群前的列表');

    }


    public function groupmemberin()
    {

        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('usertoken', 'groupId'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();



        $groupId = Buddha_Http_Input::getParameter('groupId');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');


        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $my_id = $user_id;

        $where = " cgm.groupId='{$groupId}' AND  cf.my_id='{$my_id}'   ";
        $sql  =" SELECT cgm.account_id as friend_id ,cf.friend_nickname,
                        u.logo,u.mobile,u.realname,u.nickname

                 FROM {$this->prefix}chatgroupmember AS cgm
                 LEFT JOIN {$this->prefix}user as u
                 ON cgm.account_id = u.id
                 LEFT JOIN {$this->prefix}chatfriend as cf
                 ON u.id = cf.friend_id
                 WHERE $where

                   order by cgm.vieworder desc ,cgm.gid asc

                       limit 0,500
                 ";

        $Db_Chatgroupmember = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata = array();
        if(Buddha_Atom_Array::isValidArray($Db_Chatgroupmember)){

            foreach($Db_Chatgroupmember as $k=>$v){

                $friend_id = $v['friend_id'];



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

                $nickname = '';
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


        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'群组成员列表');

    }

    public function groupjoin(){

        if (Buddha_Http_Input::checkParameter(array('usertoken', 'groupId','groupName','friend_id_arr'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $ChatfriendObj = new Chatfriend();
        $ChatgroupmemberObj = new Chatgroupmember();

        $groupId = Buddha_Http_Input::getParameter('groupId');
        $groupName = Buddha_Http_Input::getParameter('groupName');
        $friend_id_arr = Buddha_Http_Input::getParameter('friend_id_arr');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');


        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $my_id = $user_id;

        if(Buddha_Atom_String::isJson($friend_id_arr)){
            $friend_id_arr = Buddha_Atom_Json::decodeJsonToArr($friend_id_arr);
        }


        if(count($friend_id_arr)<1){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000066, '邀请好友进群人数至少1个人');
        }



        if(!$ChatgroupmemberObj->isGroupMember($my_id,$groupId)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000067, '自身不是群组成员');
        }

        if(!$ChatfriendObj->isValidFriendIdArr($my_id,$friend_id_arr)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000064, '进群的会员不是自己的好友');
        }


        /*确保每个好友都有chattoken 方便进行聊天*/
        if(Buddha_Atom_Array::isValidArray($friend_id_arr)){

            foreach($friend_id_arr as $k=>$v){
                $temp_user_id = $v;

                if($UserObj->getUserHasWorldChatTokenStr($temp_user_id)==0){

                    $UserObj->setUserWorldChatTokenIfNull($temp_user_id);

                }
            }

        }

     $isOk = Buddha_Thirdpart_Message::getInstance()->groupJoin($my_id,$friend_id_arr, $groupId, $groupName);




        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'加入群组');
    }

    public function groupquit(){

        if (Buddha_Http_Input::checkParameter(array('usertoken', 'groupId','friend_id_arr'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $ChatgroupObj = new Chatgroup();
        $ChatgroupmemberObj = new Chatgroupmember();

        $groupId = Buddha_Http_Input::getParameter('groupId');
        $friend_id_arr = Buddha_Http_Input::getParameter('friend_id_arr');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');


        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $my_id = $user_id;


        if(Buddha_Atom_String::isJson($friend_id_arr)){
            $friend_id_arr = Buddha_Atom_Json::decodeJsonToArr($friend_id_arr);
        }


        if(count($friend_id_arr)<1){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000068, '退群人数至少1个人');
        }



        if(!$ChatgroupmemberObj->isGroupMember($my_id,$groupId)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000067, '自身不是群组成员');
        }






        /*确保每个好友都有chattoken 方便进行聊天*/
        if(Buddha_Atom_Array::isValidArray($friend_id_arr)){

            foreach($friend_id_arr as $k=>$v){
                $temp_user_id = $v;

                if($UserObj->getUserHasWorldChatTokenStr($temp_user_id)==0){

                    $UserObj->setUserWorldChatTokenIfNull($temp_user_id);

                }
            }

        }

        $quit_people_num = count($friend_id_arr);

        /* 退群人数是一人 非群主的群组成员进行退群*/
        $is_qunzhu = $ChatgroupObj->isFounderGroup($my_id,$groupId);

        if(!$is_qunzhu and $quit_people_num==1){
            $isOk = Buddha_Thirdpart_Message::getInstance()->groupQuit($friend_id_arr, $groupId);
        }

        if(!$is_qunzhu and $quit_people_num>1){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000069, '群主才可以踢群组其它成员');
        }

        $Db_Chatgroup = $ChatgroupObj->getSingleFiledValues(array('founder_id'),"groupId='{$groupId}' ");

        $qunzhu_id = $Db_Chatgroup['founder_id'];

        /*判断数组有没有群主*/
        $has_qunzhu = 0;
        foreach($friend_id_arr as $k=>$v){

            if($v==$qunzhu_id){
                $has_qunzhu =1;
            }

        }

        if($has_qunzhu==0){

            $isOk = Buddha_Thirdpart_Message::getInstance()->groupQuit($friend_id_arr, $groupId);

        }else{



            $group_total_num = $ChatgroupmemberObj->countRecords(" groupId ='{$groupId}' ");

            if($group_total_num>$quit_people_num){
                /*退群里面有群主,并且退群人数小于群的总人数 要先进行群主管理权转让操作*/
               $ChatgroupmemberObj->updateRecords(array('host'=>0)," groupId ='{$groupId}' ");
              // $ChatgroupObj->updateRecords(array('founder_id'=>0,'founder'=>0)," groupId ='{$groupId}' ");




                $ids = implode ( ',', $friend_id_arr);




                $Db_Temp_Chatgroupmember = $ChatgroupmemberObj->getSingleFiledValues(array('gid','account_id')," groupId ='{$groupId}' and account_id NOT IN ($ids)
                order by vieworder desc ,gid asc
                limit 0,1 ");


                if(Buddha_Atom_Array::isValidArray($Db_Temp_Chatgroupmember)){

                    $gid = $Db_Temp_Chatgroupmember['gid'];
                    $ChatgroupmemberObj->updateRecords(array("host"=>1),"gid='{$gid}' ");

                    
                    $account_id = $Db_Temp_Chatgroupmember['account_id'];
                    $Db_Temp_User = $UserObj->getSingleFiledValues(array('mobile'),"id='{$account_id}'");
                    $account_name = $Db_Temp_User['mobile'];
                    $ChatgroupObj->updateRecords(array('founder_id'=>$account_id,'founder'=>$account_name)," groupId ='{$groupId}' ");

                }



            }
            $isOk = Buddha_Thirdpart_Message::getInstance()->groupQuit($friend_id_arr, $groupId);
        }







        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'退出群组');
    }

    public function groupview()
    {

        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('usertoken', 'groupId'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $ChatgroupObj = new Chatgroup();
        $ChatgroupmemberObj = new Chatgroupmember();
        $ChatfriendObj = new Chatfriend();

        $groupId = Buddha_Http_Input::getParameter('groupId');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');


        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $founder_id =  $user_id ;

        if(!$ChatgroupmemberObj->isGroupMember($founder_id,$groupId)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000067, '自身不是群组成员');
        }

        $is_master = 0;
        if($ChatgroupObj->isFounderGroup($founder_id,$groupId)){
            $is_master =1;
        }

        //更新这个群目前的人数
        $ChatgroupObj->updateMemberTotal($groupId);


        $where = " cgm.groupId='{$groupId}'  ";
         $sql  =" SELECT cgm.account_id as friend_id ,cg.name as groupname,cg.membertotal,cg.summary,
                        u.logo,u.mobile,u.realname,u.nickname

                 FROM {$this->prefix}chatgroupmember AS cgm
                 LEFT JOIN {$this->prefix}user as u
                 ON cgm.account_id = u.id

                 LEFT JOIN {$this->prefix}chatgroup as cg
                 ON cgm.groupId = cg.groupId



                 WHERE $where



                   order by cgm.vieworder desc ,cgm.gid asc

                       limit 0,43
                 ";


        $Db_Chatgroupmember = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata = array();

        $jsondata['increaseservices'] = array('Services'=>'chatgroup.groupmemberout','param'=>array('groupId'=>$groupId));
        $jsondata['reduceservices'] = array('Services'=>'chatgroup.groupquit','param'=>array('groupId'=>$groupId,'friend_id_arr'=>'好友内码id数组或者json格式的数据'));
        $jsondata['morememberservices'] = array('Services'=>'chatgroup.groupmemberin','param'=>array('groupId'=>$groupId));
        $jsondata['noticeservices'] = array('Services'=>'chatgroup.noticeview','param'=>array('groupId'=>$groupId));

        $jsondata['is_showincrease'] = 1;
        if($is_master==1){
            $jsondata['is_showreduce'] = 1;

        }else{
            $jsondata['is_showreduce'] = 0;
        }
        $jsondata['is_master'] = $is_master;

        if(Buddha_Atom_Array::isValidArray($Db_Chatgroupmember)){

            foreach($Db_Chatgroupmember as $k=>$v){

                $friend_id = $v['friend_id'];


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

                $nickname = '';
                if(Buddha_Atom_String::isValidString($v['realname'])){
                    $nickname = $v['realname'];
                }

                $Db_friend_nickname = $ChatfriendObj->getSingleFiledValues(array('friend_nickname'),"my_id='{$user_id}' AND friend_id='{$friend_id}' ");
                $friend_nickname = $Db_friend_nickname['friend_nickname'];

                if(Buddha_Atom_String::isValidString($friend_nickname)){
                    $nickname = $friend_nickname;
                }

                if(!Buddha_Atom_String::isValidString($nickname)){
                    $nickname = $v['mobile'];
                }

                if($k==0){
                    $jsondata['groupname'] = $v['groupname'];
                    $jsondata['membertotal'] = $v['membertotal'];

                    if(Buddha_Atom_String::isValidString( $v['summary'])){
                        $jsondata['summary'] = $v['summary'];
                    }else{
                        $jsondata['summary'] = '';
                    }

                    if($v['membertotal']>43){

                        $jsondata['membermoretitle'] = '查看更多群成员 >';

                    }else{
                        $jsondata['membermoretitle'] = '';
                    }

                    $jsondata['grouptitle'] = "{$v['groupname']}({$v['membertotal']})";
                }
                unset( $v['realname']);
                unset( $v['nickname']);
                unset( $v['summary']);
                unset( $v['groupname']);
                unset( $v['membertotal']);

                $v['friend_nickname']  = $nickname;
                $v['first']  = Buddha_Atom_String::getFirstCharter($nickname);
                $jsondata['list'][] = $v;
            }

        }


        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'群组详情');

    }

    public function noticeview(){

        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('usertoken', 'groupId'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $ChatgroupObj = new Chatgroup();
        $ChatgroupmemberObj = new Chatgroupmember();
        $ChatfriendObj = new Chatfriend();

        $groupId = Buddha_Http_Input::getParameter('groupId');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');


        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $my_id = $user_id;

        if(!$ChatgroupmemberObj->isGroupMember($my_id,$groupId)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000067, '自身不是群组成员');
        }

        $is_master = 0;
        if($ChatgroupObj->isFounderGroup($my_id,$groupId)){
            $is_master =1;
        }


        $Db_Chatgroup  = $ChatgroupObj->getSingleFiledValues(array('founder_id','summary_id','summary','createtime','createtimestr'),"groupId='{$groupId}'");

        $summary_id = $Db_Chatgroup['summary_id'];
        $summary = $Db_Chatgroup['summary'];
        if(!Buddha_Atom_String::isValidString($summary_id)){
            $summary_id = $Db_Chatgroup['founder_id'];
        }

        if(!Buddha_Atom_String::isValidString($summary_id)){
            $summary_id = $Db_Chatgroup['create_id'];
        }

        if(!Buddha_Atom_String::isValidString($summary)){
            $summary = '';
        }


        $Db_User = $UserObj->getSingleFiledValues(array('logo','mobile','realname'),"id='{$summary_id}' ");


        $Db_Chatfriend = $ChatfriendObj->getSingleFiledValues(array('friend_nickname')," friend_id='{$summary_id}' ");



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



        $nickname = '';
        if(Buddha_Atom_String::isValidString($Db_User['realname'])){
            $nickname = $Db_User['realname'];
        }

        if(Buddha_Atom_String::isValidString($Db_Chatfriend['friend_nickname'])){
            $nickname = $Db_Chatfriend['friend_nickname'];
        }


        $jsondata = array();

        $jsondata['is_master'] = $is_master;
        $jsondata['summary_id'] = $summary_id;
        $jsondata['summary'] = $summary;
        $jsondata['logo'] = $logo;
        $jsondata['nickname'] = $nickname;
        $jsondata['createtime'] = $Db_Chatgroup['createtime'];
        $jsondata['createtimestr'] = $Db_Chatgroup['createtimestr'];

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'群组公告查看');
    }


    public function noticeupdate(){

        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('usertoken', 'groupId','summary'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $ChatgroupObj = new Chatgroup();


        $groupId = Buddha_Http_Input::getParameter('groupId');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $summary = Buddha_Http_Input::getParameter('summary');


        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $my_id = $user_id;

        if(!$ChatgroupObj->isFounderGroup($my_id,$groupId)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000065, '操作人不是群主');
        }



        $ChatgroupObj->updateNotice($groupId,$my_id,$summary);





        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'群组公告更新');
    }

    public function replacemaster(){

        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('usertoken', 'groupId','friend_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $ChatgroupObj = new Chatgroup();
        $ChatgroupmemberObj = new Chatgroupmember();

        $groupId = Buddha_Http_Input::getParameter('groupId');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $friend_id = Buddha_Http_Input::getParameter('friend_id');


        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $my_id = $user_id;



        if(!$ChatgroupmemberObj->isGroupMember($friend_id,$groupId)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000067, '自身不是群组成员');
        }

        if(!$ChatgroupmemberObj->isGroupMember($my_id,$groupId)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000067, '自身不是群组成员');
        }


        if(!$ChatgroupObj->isFounderGroup($my_id,$groupId)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000065, '操作人不是群主');
        }


        /*进行群主管理权进行转移*/
        $isok = $ChatgroupObj->isReplaceGroupMaster($my_id,$friend_id,$groupId);


        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'群主管理权转让');
    }



    public function cardview(){

        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('usertoken', 'groupId','friend_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $UserObj = new User();
        $ChatgroupObj = new Chatgroup();
        $ChatgroupmemberObj = new Chatgroupmember();
        $ChatfriendObj = new Chatfriend();

        $groupId = Buddha_Http_Input::getParameter('groupId');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $friend_id = Buddha_Http_Input::getParameter('friend_id');


        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $my_id = $user_id;



        if(!$ChatgroupmemberObj->isGroupMember($friend_id,$groupId)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000067, '自身不是群组成员');
        }

        if(!$ChatgroupmemberObj->isGroupMember($my_id,$groupId)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000067, '自身不是群组成员');
        }


        $isfriend = 0 ;
        if($ChatfriendObj->isMyFriend($my_id,$friend_id)){
            $isfriend = 1;
        }


        if($my_id == $friend_id){
            $isfriend = 1;
        }


        $Db_User = $UserObj->getSingleFiledValues(array('logo','mobile','realname'),"id='{$isfriend}' ");
        $Db_Chatfriend = $ChatfriendObj->getSingleFiledValues(array('friend_nickname')," friend_id='{$isfriend}' ");



        if(Buddha_Atom_String::isValidString($Db_User['mobile'])){
            $mobile = $Db_User['mobile'];
        }else{
            $mobile='';
        }



        $logo = $Db_User['logo'];
        if(Buddha_Atom_String::isValidString($logo)){

            if(!Buddha_Atom_String::hasNeedleString($logo,'http')){

                $logo = $host.$logo;
            }else{
                $logo = Buddha_Atom_String::getAfterReplaceStr($logo,'http','https');
            }


        }else{
            $logo = $host."resources/worldchat/portrait/default.png";
        }



        $nickname = '';
        if(Buddha_Atom_String::isValidString($Db_User['realname'])){
            $nickname = $Db_User['realname'];
        }

        if(Buddha_Atom_String::isValidString($Db_Chatfriend['friend_nickname'])){
            $nickname = $Db_Chatfriend['friend_nickname'];
        }





        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['mobile'] = $mobile;
        $jsondata['isfriend'] = $isfriend;
        $jsondata['logo'] = $logo;
        $jsondata['nickname'] = $nickname;


        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'群名片查看');
    }







}