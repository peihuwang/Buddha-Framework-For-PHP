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
        /*融云*/
        $this->message = Buddha_Thirdpart_Message_Rongcloud::getInstance();
        $this->flag = 2;




    }

    public  function  getToken($userId, $name, $portraitUri){
       return  $token = $this->message->getToken($userId, $name, $portraitUri);
    }

    public  function  refresh($userId, $name, $portraitUri){
        return  $return = $this->message->refresh($userId, $name, $portraitUri);
    }

    public  function  checkOnline($userId){
        return  $return = $this->message->checkOnline($userId);
    }

    public  function  block($userId,$minute){
        return  $return = $this->message->block($userId,$minute);
    }

    public  function  queryBlock(){
        return  $return = $this->message->queryBlock();
    }

    public  function  addBlacklist($userId1, $userId2){
        return  $return = $this->message->addBlacklist($userId1, $userId2);
    }

    public  function  queryBlacklist($userId1){
        return  $return = $this->message->queryBlacklist($userId1);
    }

    public  function  removeBlacklist($userId1, $userId2){
        return  $return = $this->message->removeBlacklist($userId1, $userId2);
    }


    public  function  groupCreate($userIdArr){
        return  $return = $this->message->groupCreate($userIdArr);
    }

    public  function  groupDismiss($userId, $groupId){
        return  $return = $this->message->groupDismiss($userId, $groupId);
    }


    public  function  groupJoin($drag_id,$userIdArr, $groupId, $groupName){

        return  $return = $this->message->groupJoin($drag_id,$userIdArr, $groupId, $groupName);
    }

    public  function  groupQuit($userIdArr, $groupId){
        return  $return = $this->message->groupQuit($userIdArr,  $groupId);
    }

    public  function  groupRefresh($groupId, $newGroupName){
        return  $return = $this->message->groupRefresh($groupId, $newGroupName);
    }

    public  function  groupQueryUser($groupId){
        return  $return = $this->message->groupQueryUser($groupId);
    }

}