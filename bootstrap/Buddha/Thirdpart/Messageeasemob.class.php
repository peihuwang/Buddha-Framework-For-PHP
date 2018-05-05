<?php

class Buddha_Thirdpart_Message {
    protected $message;
    protected $flag;//1环信 2融云
    protected static $_instance;

    public static function getInstance()
    {
        if (self::$_instance === null) {
            $createObj=  new self();

            self::$_instance =$createObj;
        }
        return self::$_instance;
    }


    public function __construct()
    {
        /*环信*/
        $this->message = Buddha_Thirdpart_Message_Easemob::getInstance();
        $this->flag = 1;




    }

    public  function  getToken(){
       return  $token = $this->message->getToken();
    }

    /**
    授权注册
     */
    public function createUser($username,$password){
        $result = array();
        if($this->flag == 1){
            $result =$this->message->createUser($username,$password);

            if(isset($result['error'])){

                $result = 0;
            }

        }
        return  $result;
    }

    /**
    重置用户密码
     */
    public function resetPassword($username,$password){
        $result = array();
        if($this->flag == 1){
            $result =$this->message->resetPassword($username,$password);

            if(isset($result['error'])){

                $result = 0;
            }

        }
        return  $result;
    }


    /**
    创建批量用户
     */
    public function createBatchUsers($userarr){
        $result = array();

        if($this->flag == 1){
            $result = $this->message->createUsers($userarr);
            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    获取单个用户
     */
    public function getUser($username){
        $result = array();

        if($this->flag == 1){
            $result = $this->message->getUser($username);
            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    获取批量用户---不分页(默认返回10个)
     */
    public function getUsers(){
        $result = array();

        if($this->flag == 1){
            $result = $this->message->getUsers();
            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    获取批量用户----分页
     */
    public function getBatchUsersForPage($page=10){
        $page = (int)$page;

        $result = array();

        if($this->flag == 1){

            $cursor=$this->message->readCursor("userfile.txt");
            $result =$this->message->getUsersForPage($page,$cursor);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    获取批量用户----分页
     */
    public function deleteUser($username){
        $result = array();

        if($this->flag == 1){

            $result =$this->message->deleteUser($username);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    获取批量用户----分页
     */
    public function deleteBatchUsers($num){
        $result = array();

        if($this->flag == 1){

            $result =$this->message->deleteUsers($num);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    修改昵称
     */
    public function editNickname($username,$nickname){
        $result = array();

        if($this->flag == 1){

            $result =$this->message->editNickname($username,$nickname);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    修改昵称
     */
    public function addFriend($username_one,$username_two){
        $result = array();

        if($this->flag == 1){

            $result =$this->message->addFriend($username_one,$username_two);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    删除好友
     */
    public function deleteFriend($username_one,$username_two){
        $result = array();

        if($this->flag == 1){

            $result =$this->message->deleteFriend($username_one,$username_two);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    删除好友
     */
    public function showFriends($username){
        $result = array();

        if($this->flag == 1){

            $result =$this->message->showFriends($username);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    查看黑名单
     */
    public function getBlacklist($username){
        $result = array();

        if($this->flag == 1){

            $result =$this->message->getBlacklist($username);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
查看黑名单
 */
    public function addUserForBlacklist($username,$userarr){
        $result = array();

        if($this->flag == 1){

            $result =$this->message->addUserForBlacklist($username,$userarr);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    查看黑名单
     */
    public function deleteUserFromBlacklist($username_one,$username_two){
        $result = array();

        if($this->flag == 1){

            $result =$this->message->deleteUserFromBlacklist($username_one,$username_two);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    查看用户是否在线
     */
    public function isOnline($username){
        $result = array();

        if($this->flag == 1){

            $result =$this->message->isOnline($username);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    查看用户离线消息数
     */
    public function getOfflineMessages($username){
        $result = array();

        if($this->flag == 1){

            $result =$this->message->getOfflineMessages($username);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    查看用户离线消息数
     */
    public function getOfflineMessageStatus($username,$uuid){
        $result = array();

        if($this->flag == 1){

            $result =$this->message->getOfflineMessageStatus($username,$uuid);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    禁用用户账号----
     */
    public function deactiveUser($username){
        $result = array();

        if($this->flag == 1){

            $result =$this->message->deactiveUser($username);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }



    /**
    解禁用户账号-----
     */
    public function activeUser($username){
        $result = array();

        if($this->flag == 1){

            $result =$this->message->activeUser($username);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }



    /**
    强制用户下线-----
     */
    public function disconnectUser($username){
        $result = array();

        if($this->flag == 1){

            $result =$this->message->disconnectUser($username);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    强制用户下线-----
     */
    public function uploadFile($absolutepathfilename){
        $result = array();

        if($this->flag == 1){

            $result =$this->message->uploadFile($absolutepathfilename);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    下载图片或文件
     */
    public function downloadFile($uuid,$share_secret){
        $result = array();
        if($this->flag == 1){

            $result =$this->message->downloadFile($uuid,$share_secret);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    下载图片缩略图
     */
    public function downloadThumbnail($uuid,$share_secret){
        $result = array();
        if($this->flag == 1){

            $result =$this->message->downloadFile($uuid,$share_secret);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    发送文本消息
     */
    public function sendText($from,$target_type,$target,$content,$ext){
        $result = array();
        if($this->flag == 1){

            $result =$this->message->sendText($from,$target_type,$target,$content,$ext);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    发送透传消息
     */
    public function sendCmd($from,$target_type,$target,$content,$ext){
        $result = array();
        if($this->flag == 1){

            $result =$this->message->sendCmd($from,$target_type,$target,$content,$ext);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }



    /**
    发送图片消息
     */
    public function sendImage($filePath,$from,$target_type,$target,$filename,$ext){
        $result = array();
        if($this->flag == 1){

            $result =$this->message->sendImage($filePath,$from,$target_type,$target,$filename,$ext);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    发送语音消息
     */
    public function sendAudio($filePath,$from="admin",$target_type,$target,$filename,$length,$ext){
        $result = array();
        if($this->flag == 1){

            $result =$this->message->sendAudio($filePath,$from="admin",$target_type,$target,$filename,$length,$ext);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    发送视频消息
     */
    public function sendVedio($filePath,$from="admin",$target_type,$target,$filename,$length,$thumb,$thumb_secret,$ext){
        $result = array();
        if($this->flag == 1){

            $result =$this->message->sendVedio($filePath,$from="admin",$target_type,$target,$filename,$length,$thumb,$thumb_secret,$ext);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    获取app中的所有群组-----不分页（默认返回10个）
     */
    public function getGroups(){
        $result = array();
        if($this->flag == 1){

            $result =$this->message->getGroups();

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    获取app中的所有群组--------分页
     */
    public function getGroupsForPage($page=10){
        $page = (int)$page;

        $result = array();

        if($this->flag == 1){

            $cursor=$this->message->readCursor("groupfile.txt");
            $result =$this->message->getGroupsForPage($page,$cursor);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    获取app中的所有群组--------分页
     */
    public function getGroupDetail($group_ids){

        $result = array();

        if($this->flag == 1){


            $result =$this->message->getGroupDetail($group_ids);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    创建一个群组
     */
    public function createGroup($options){

        $result = array();

        if($this->flag == 1){


            $result =$this->message->createGroup($options);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    删除群组
     */
    public function deleteGroup($group_id){

        $result = array();

        if($this->flag == 1){


            $result =$this->message->deleteGroup($group_id);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    获取群组中的成员
     */
    public function getGroupUsers($group_id){

        $result = array();

        if($this->flag == 1){


            $result =$this->message->getGroupUsers($group_id);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    群组单个加人------
     */
    public function addGroupMember($group_id,$username){

        $result = array();

        if($this->flag == 1){


            $result =$this->message->addGroupMember($group_id,$username);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    群组单个加人------
     */
    public function addGroupMembers($group_id,$usernames){

        $result = array();

        if($this->flag == 1){


            $result =$this->message->addGroupMembers($group_id,$usernames);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    群组单个减人
     */
    public function deleteGroupMember($group_id,$username){

        $result = array();

        if($this->flag == 1){


            $result =$this->message->deleteGroupMember($group_id,$username);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    群组批量减人-----
     */
    public function deleteGroupMembers($group_id,$usernames){

        $result = array();

        if($this->flag == 1){


            $result =$this->message->deleteGroupMembers($group_id,$usernames);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    获取一个用户参与的所有群组
     */
    public function getGroupsForUser($username){

        $result = array();

        if($this->flag == 1){


            $result =$this->message->getGroupsForUser($username);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    群组转让
     */
    public function changeGroupOwner($group_id,$options){

        $result = array();

        if($this->flag == 1){


            $result =$this->message->changeGroupOwner($group_id,$options);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    群组转让
     */
    public function getGroupBlackList($group_id){

        $result = array();

        if($this->flag == 1){


            $result =$this->message->getGroupBlackList($group_id);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    群组黑名单单个加人-----
     */
    public function addGroupBlackMember($group_id,$username){

        $result = array();

        if($this->flag == 1){


            $result =$this->message->addGroupBlackMember($group_id,$username);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    群组黑名单批量加人
     */
    public function addGroupBlackMembers($group_id,$usernames){

        $result = array();

        if($this->flag == 1){


            $result =$this->message->addGroupBlackMembers($group_id,$usernames);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    群组黑名单单个减人
     */
    public function deleteGroupBlackMember($group_id,$username){

        $result = array();

        if($this->flag == 1){


            $result =$this->message->deleteGroupBlackMember($group_id,$username);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    群组黑名单批量减人
     */
    public function deleteGroupBlackMembers($group_id,$usernames){

        $result = array();

        if($this->flag == 1){


            $result =$this->message->deleteGroupBlackMembers($group_id,$usernames);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    创建聊天室
     */
    public function createChatRoom($options){

        $result = array();

        if($this->flag == 1){


            $result =$this->message->createChatRoom($options);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    修改聊天室信息
     */
    public function modifyChatRoom($chatroom_id,$options){

        $result = array();

        if($this->flag == 1){

            $result =$this->message->modifyChatRoom($chatroom_id,$options);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    删除聊天室
     */
    public function deleteChatRoom($chatroom_id){

        $result = array();

        if($this->flag == 1){

            $result =$this->message->deleteChatRoom($chatroom_id);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    获取app中所有的聊天室
     */
    public function getChatRooms(){

        $result = array();

        if($this->flag == 1){

            $result =$this->message->getChatRooms();

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    获取一个聊天室的详情
     */
    public function getChatRoomDetail($chatroom_id){

        $result = array();

        if($this->flag == 1){

            $result =$this->message->getChatRoomDetail($chatroom_id);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    获取一个用户加入的所有聊天室
     */
    public function getChatRoomJoined($username){

        $result = array();

        if($this->flag == 1){

            $result =$this->message->getChatRoomJoined($username);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }



    /**
    聊天室单个成员添加--
     */
    public function addChatRoomMember($chatroom_id,$username){

        $result = array();

        if($this->flag == 1){

            $result =$this->message->addChatRoomMember($chatroom_id,$username);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    聊天室批量成员添加
     */
    public function addChatRoomMembers($chatroom_id,$usernames){

        $result = array();

        if($this->flag == 1){

            $result =$this->message->addChatRoomMembers($chatroom_id,$usernames);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    聊天室批量成员添加
     */
    public function deleteChatRoomMember($chatroom_id,$username){

        $result = array();

        if($this->flag == 1){

            $result =$this->message->deleteChatRoomMember($chatroom_id,$username);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    聊天室批量成员删除
     */
    public function deleteChatRoomMembers($chatroom_id,$usernames){

        $result = array();

        if($this->flag == 1){

            $result =$this->message->deleteChatRoomMembers($chatroom_id,$usernames);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


    /**
    导出聊天记录-------不分页
     */
    public function getChatRecord($ql){

        $result = array();

        if($this->flag == 1){

            $result =$this->message->getChatRecord($ql);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }

    /**
    导出聊天记录-------分页
     */
    public function getChatRecordForPage($ql,$page=10){

        $result = array();

        if($this->flag == 1){

            $cursor=$this->message->readCursor("chatfile.txt");
            $result =$this->message->getChatRecordForPage($ql,$page,$cursor);

            if(isset($result['error'])){

                $result = 0;
            }
        }
        return $result;
    }


}