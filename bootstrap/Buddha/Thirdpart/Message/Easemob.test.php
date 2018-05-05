<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 2017/10/19
 * Time: 22:51
 */
define('TPL_DIR', 'NONE');
require_once  './bootstrap/Init.php';

//获取token
//$token = Buddha_Thirdpart_Message::getInstance()->getToken();

//创建单个用户
//print_r(Buddha_Thirdpart_Message::getInstance()->createUser("wangwu","123456"));

//重置用户密码
//print_r(Buddha_Thirdpart_Message::getInstance()->resetPassword("zhangsan","123456"));



//创建批量用户
/*(Buddha_Thirdpart_Message::getInstance()->createBatchUsers(

    array(

        array(
            "username"=>"lisi",
            "password"=>"123456"
        )
        ,
          array(
              "username"=>"zhangsan",
              "password"=>"123456"
          ),
    )

)

);
*/

//获取单个用户
//print_r(Buddha_Thirdpart_Message::getInstance()->getUser("zhangsan"));

//获取批量用户---不分页(默认返回10个)
//print_r(Buddha_Thirdpart_Message::getInstance()->getUsers());


//获取批量用户----分页
//print_r(Buddha_Thirdpart_Message::getInstance()->getBatchUsersForPage(10));

//删除单个用户
//print_r(Buddha_Thirdpart_Message::getInstance()->deleteUser("zhangsan"));

//删除批量用户
//print_r(Buddha_Thirdpart_Message::getInstance()->deleteBatchUsers(2));


//修改昵称
//print_r(Buddha_Thirdpart_Message::getInstance()->editNickname("zhangsan","小B"));

//添加好友------400
//print_r(Buddha_Thirdpart_Message::getInstance()->addFriend("zhangsan","lisi"));

//删除好友
//print_r(Buddha_Thirdpart_Message::getInstance()->deleteFriend("zhangsan","lisi"));

//查看好友
//print_r(Buddha_Thirdpart_Message::getInstance()->showFriends("zhangsan"));

//查看黑名单
//print_r(Buddha_Thirdpart_Message::getInstance()->getBlacklist("zhangsan"));

//往黑名单中加人
/*
print_r(Buddha_Thirdpart_Message::getInstance()->addUserForBlacklist("zhangsan",array(
"usernames"=>array("wangwu","lisi")
        ))
);
*/


//从黑名单中减人
//print_r(Buddha_Thirdpart_Message::getInstance()->deleteUserFromBlacklist("zhangsan","lisi"));

//查看用户是否在线
//print_r(Buddha_Thirdpart_Message::getInstance()->isOnline("zhangsan"));


//查看用户离线消息数
//print_r(Buddha_Thirdpart_Message::getInstance()->getOfflineMessages("zhangsan"));


//查看用户离线消息数
//print_r(Buddha_Thirdpart_Message::getInstance()->getOfflineMessageStatus("zhangsan","ff7e10b0-b310-11e7-bb14-397262c316b7"));


//禁用用户账号
//print_r(Buddha_Thirdpart_Message::getInstance()->getOfflineMessageStatus("zhangsan","ff7e10b0-b310-11e7-bb14-397262c316b7"));

//禁用用户账号----
//print_r(Buddha_Thirdpart_Message::getInstance()->deactiveUser("zhangsan"));

//解禁用户账号----
//print_r(Buddha_Thirdpart_Message::getInstance()->activeUser("zhangsan"));


//强制用户下线----
//print_r(Buddha_Thirdpart_Message::getInstance()->disconnectUser("zhangsan"));

//强制用户下线----
//print_r(Buddha_Thirdpart_Message::getInstance()->uploadFile(PATH."resources/up/pujing.jpg"));
//var_dump(Buddha_Thirdpart_Message::getInstance()->uploadFile(PATH"resource/up/mangai.mp3"));
//var_dump(Buddha_Thirdpart_Message::getInstance()->uploadFile(PATH"resource/up/sunny.mp4"));
/*
 *
Array
(
    [action] => post
    [application] => 67aac020-acae-11e7-a292-015f2e0ea000
    [path] => /chatfiles
    [uri] => https://a1.easemob.com/1120171009115543/worldchat/chatfiles
    [entities] => Array
        (
            [0] => Array
                (
                    [uuid] => a1ed5750-b3b8-11e7-b5e6-1991c955a513
                    [type] => chatfile
                    [share-secret] => oe1XWrO4Eeetuj_axpaBAsAjbWyexRBuonmH080wY71iNcGx
                )

        )

    [timestamp] => 1508299123018
    [duration] => 0
    [organization] => 1120171009115543
    [applicationName] => worldchat
)

 */

//下载图片或文件
//print_r(Buddha_Thirdpart_Message::getInstance()->downloadFile('a1ed5750-b3b8-11e7-b5e6-1991c955a513','oe1XWrO4Eeetuj_axpaBAsAjbWyexRBuonmH080wY71iNcGx'));

//下载图片缩略图
//print_r(Buddha_Thirdpart_Message::getInstance()->downloadThumbnail('a1ed5750-b3b8-11e7-b5e6-1991 c955a513','oe1XWrO4Eeetuj_axpaBAsAjbWyexRBuonmH080wY71iNcGx'));

//发送文本消息
/*
$from='admin';
$target_type="users";
//$target_type="chatgroups";
$target=array("zhangsan","lisi","wangwu");
//$target=array("122633509780062768");
$content="Hello HuanXin!";
$ext['a']="a";
$ext['b']="b";
*/
//print_r(Buddha_Thirdpart_Message::getInstance()->sendText($from,$target_type,$target,$content,$ext));

//发送透传消息
/*
		$from='admin';
		$target_type="users";
		//$target_type="chatgroups";
		$target=array("zhangsan","lisi","wangwu");
		//$target=array("122633509780062768");
		$action="Hello HuanXin!";
		$ext['a']="a";
		$ext['b']="b";
*/
//print_r(Buddha_Thirdpart_Message::getInstance()->sendCmd($from,$target_type,$target,$content,$ext));


//发送图片消息
/*
		$filePath=PATH."resources/up/pujing.jpg";
		$from='admin';
		$target_type="users";
		$target=array("zhangsan","lisi");
		$filename="pujing.jpg";
		$ext['a']="a";
		$ext['b']="b";
*/
//print_r(Buddha_Thirdpart_Message::getInstance()->sendImage($filePath,$from,$target_type,$target,$filename,$ext));


//发送语音消息
/*		$filePath=PATH."resources/up/mangai.mp3";
		$from='admin';
		$target_type="users";
		$target=array("zhangsan","lisi");
		$filename="mangai.mp3";
		$length=10;
		$ext['a']="a";
		$ext['b']="b";*/
//print_r(Buddha_Thirdpart_Message::getInstance()->sendAudio($filePath,$from="admin",$target_type,$target,$filename,$length,$ext));



//发送视频消息
/*		$filePath=PATH."resources/up/sunny.mp4";
		$from='admin';
		$target_type="users";
		$target=array("zhangsan","lisi");
		$filename="sunny.mp4";
		$length=10;//时长
		$thumb='https://a1.easemob.com/easemob-demo/chatdemoui/chatfiles/c06588c0-7df4-11e5-932c-9f90699e6d72';
		$thumb_secret='wGWIyn30EeW9AD1fA7wz23zI8-dl3PJI0yKyI3Iqk08NBqCJ';
		$ext['a']="a";
		$ext['b']="b";*/
//print_r(Buddha_Thirdpart_Message::getInstance()->sendVedio($filePath,$from="admin",$target_type,$target,$filename,$length,$thumb,$thumb_secret,$ext));

//获取app中的所有群组-----不分页（默认返回10个）
//print_r(Buddha_Thirdpart_Message::getInstance()->getGroups());

//获取一个或多个群组的详情
//$group_ids=array("1445830526109","1445833238210");
//print_r(Buddha_Thirdpart_Message::getInstance()->getGroupDetail($group_ids));


//创建一个群组
/*		$options ['groupname'] = "group001";
		$options ['desc'] = "this is a love group";
		$options ['public'] = true;
		$options ['owner'] = "zhangsan";
		$options['members']=Array("wangwu","lisi");*/
//print_r(Buddha_Thirdpart_Message::getInstance()->createGroup($options));
/*
 *
Array
(
    [action] => post
    [application] => 67aac020-acae-11e7-a292-015f2e0ea000
    [uri] => http://a1.easemob.com/1120171009115543/worldchat/chatgroups
    [entities] => Array
        (
        )

    [data] => Array
        (
            [groupid] => 30319133655041
        )

    [timestamp] => 1508312979405
    [duration] => 0
    [organization] => 1120171009115543
    [applicationName] => worldchat
)
 */


//修改群组信息
/*		$group_id="30319133655041";
		$options['groupname']="group002";
		$options['description']="修改群描述";
		$options['maxusers']=300;*/
//print_r(Buddha_Thirdpart_Message::getInstance()->modifyGroupInfo($group_id,$options));

//删除群组
//$group_id="30319133655041";
//print_r(Buddha_Thirdpart_Message::getInstance()->deleteGroup($group_id));


/*
Array
(
    [action] => post
    [application] => 67aac020-acae-11e7-a292-015f2e0ea000
    [uri] => http://a1.easemob.com/1120171009115543/worldchat/chatgroups
    [entities] => Array
(
)

[data] => Array
(
    [groupid] => 30320591175682
)

[timestamp] => 1508314369849
    [duration] => 1
    [organization] => 1120171009115543
    [applicationName] => worldchat
)*/


//获取群组中的成员
//$group_id="30320591175682";
//print_r(Buddha_Thirdpart_Message::getInstance()->getGroupUsers($group_id));

//群组单个加人------
/*$group_id="30320591175682";
$username="lisi";
print_r(Buddha_Thirdpart_Message::getInstance()->addGroupMember($group_id,$username));*/

//群组批量加人
/*$group_id="30320591175682";
$usernames['usernames']=array("wangwu","lisi");
print_r(Buddha_Thirdpart_Message::getInstance()->addGroupMembers($group_id,$usernames));*/

//群组单个减人
//$group_id="30320591175682";
//$username="wangwu";
//print_r(Buddha_Thirdpart_Message::getInstance()->deleteGroupMember($group_id,$username));


//群组批量减人-----
/*		$group_id="30320591175682";
		//$usernames['usernames']=array("wangwu","lisi");
		$usernames='wangwu,lisi';
print_r(Buddha_Thirdpart_Message::getInstance()->deleteGroupMembers($group_id,$usernames));*/



//获取一个用户参与的所有群组
//print_r(Buddha_Thirdpart_Message::getInstance()->getGroupsForUser("zhangsan"));

//群组转让
/*		$group_id="30320591175682";
		$options['newowner']="lisi";
print_r(Buddha_Thirdpart_Message::getInstance()->changeGroupOwner($group_id,$options));*/


//查询一个群组黑名单用户名列表
//$group_id="30320591175682";
//print_r(Buddha_Thirdpart_Message::getInstance()->getGroupBlackList($group_id));


//群组黑名单单个加人-----
/*		$group_id="30320591175682";
		$username="wangwu";
print_r(Buddha_Thirdpart_Message::getInstance()->addGroupBlackMember($group_id,$username));*/



//群组黑名单批量加人
/*		$group_id="30320591175682";
		$usernames['usernames']=array("lisi");
print_r(Buddha_Thirdpart_Message::getInstance()->addGroupBlackMembers($group_id,$usernames));*/





//群组黑名单单个减人
/*		$group_id="30320591175682";
		$username="zhangsan";
print_r(Buddha_Thirdpart_Message::getInstance()->deleteGroupBlackMember($group_id,$username));*/

//群组黑名单批量减人
/*		$group_id="30320591175682";
        $usernames='wangwu';
print_r(Buddha_Thirdpart_Message::getInstance()->deleteGroupBlackMembers($group_id,$usernames));*/



//创建聊天室
/*		$options ['name'] = "chatroom001";
		$options ['description'] = "this is a love chatroom";
		$options ['maxusers'] = 300;
		$options ['owner'] = "zhangsan";
		$options['members']=Array("wangwu","lisi");
print_r(Buddha_Thirdpart_Message::getInstance()->createChatRoom($options));*/



//修改聊天室信息
/*	$chatroom_id="30327163650049";
    $options['name']="chatroom002";
    $options['description']="修改聊天室描述";
    $options['maxusers']=300;
print_r(Buddha_Thirdpart_Message::getInstance()->modifyChatRoom($chatroom_id,$options));*/



//删除聊天室
/*$chatroom_id="30327395385345";
print_r(Buddha_Thirdpart_Message::getInstance()->deleteChatRoom($chatroom_id));*/





//获取app中所有的聊天室
/*print_r(Buddha_Thirdpart_Message::getInstance()->getChatRooms());*/


//获取一个聊天室的详情
/*$chatroom_id="124121939693277716";
print_r(Buddha_Thirdpart_Message::getInstance()->getChatRoomDetail($chatroom_id));*/

//获取一个用户加入的所有聊天室
/*print_r(Buddha_Thirdpart_Message::getInstance()->getChatRoomJoined("zhangsan"));*/


//聊天室单个成员添加--
/*		$chatroom_id="124121939693277716";
		$username="zhangsan";
print_r(Buddha_Thirdpart_Message::getInstance()->addChatRoomMember($chatroom_id,$username));*/




//聊天室批量成员添加
/*		$chatroom_id="124121939693277716";
		$usernames['usernames']=array('wangwu','lisi');
print_r(Buddha_Thirdpart_Message::getInstance()->addChatRoomMembers($chatroom_id,$usernames));*/


//聊天室单个成员删除
/*$chatroom_id="124121939693277716";
$username="zhangsan";
print_r(Buddha_Thirdpart_Message::getInstance()->deleteChatRoomMember($chatroom_id,$username));*/



//聊天室批量成员删除
/*		$chatroom_id="124121939693277716";
		$usernames='zhangsan,lisi';
print_r(Buddha_Thirdpart_Message::getInstance()->deleteChatRoomMembers($chatroom_id,$usernames));*/

//导出聊天记录-------不分页
/*$ql="select+*+where+timestamp>1435536480000";
print_r(Buddha_Thirdpart_Message::getInstance()->getChatRecord($ql));*/

//导出聊天记录-------分页
/*$ql="select+*+where+timestamp>1435536480000";
print_r(Buddha_Thirdpart_Message::getInstance()->getChatRecordForPage($ql,10));*/