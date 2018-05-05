<?php
include 'rongcloud.php';
class Buddha_Thirdpart_Message_Rongcloud extends Buddha_Base_Component{

    private $appKey;
    private $appSecret;
    private $format;
    private $jsonPath;
    protected $rongCloud;
    protected $isOpen;
    protected $portraitUri;

    protected $db;
    protected $prefix;
    protected $classname;
    protected $table;
    protected $debug_mode = false;


    protected static $_instance;
    public static function getInstance()
    {
        if (self::$_instance === null) {
            $createObj=  new self();

            self::$_instance =$createObj;
        }
        return self::$_instance;
    }
//------------------------------------------------------用户体系
    /**
     * 初始化参数
     *
     * @param array $options
     * @param $options['client_id']
     * @param $options['client_secret']
     * @param $options['org_name']
     * @param $options['app_name']
     */
    public function __construct() {
        ########开发环境#######################
        $options['appKey']='0vnjpoad0gatz';
        $options['appSecret']='pkWk2RMLaW';//rV5TcWFAzKc0Xu
        ########开发环境#######################

        ########生产环境#######################
        $options['appKey']='e5t4ouvpeqv3a';
        $options['appSecret']='CFAXZrB8lRVnFY';
        ########开发环境#######################

        $options['format']='json';
        $options['jsonPath']=PATH.'bootstrap/Thirdpart/Message/jsonsource/';

        $this->portraitUri = 'http://api.bendishangjia.com/resources/worldchat/portrait/default.png';
        $this->appKey = isset ( $options ['appKey'] ) ? $options ['appKey'] : '';
        $this->appSecret = isset ( $options ['appSecret'] ) ? $options ['appSecret'] : '';
        $this->format = isset ( $options ['format'] ) ? $options ['format'] : '';
        $this->jsonPath = isset ( $options ['jsonPath'] ) ? $options ['jsonPath'] : '';

        $rongCloud = new RongCloud($this->appKey,$this->appSecret);
        $this->rongCloud = $rongCloud;

        $this->db=Buddha_Driver_Db::getInstance(
            Buddha::getDatabaseConfig()
        );
        $this->prefix=$this->db->getPrefix();
        $this->classname = __CLASS__;
        $this->isOpen = 1;


    }
    /**
     *获取token
     * {"code":200,"userId":"userId1","token":"v0ohN1N38MC+GqlGP9lojPD/2dzHEhnoH7MuXfXDmw+t60ycISV9q7h5Sq/MjuT5skBukOGB7hgBiQgrx6xu15PXqv9m5ctf"}
     */

    function getToken($userId, $name, $portraitUri){
        if($this->isOpen == 0){
            return 0;
        }

        $UserObj = new User();

        if(!Buddha_Atom_String::isValidString($portraitUri)){
            $portraitUri = $this->portraitUri;
        }


        $db_chattoken = $UserObj->getUserHasWorldChatTokenStr($userId);
        if(Buddha_Atom_String::isValidString($db_chattoken)){
            return $db_chattoken;
        }

        $result = $this->rongCloud->user()->getToken($userId, $name, $portraitUri);
        $result_arr = Buddha_Atom_Json::decodeJsonToArr($result);
        if(Buddha_Atom_Array::isValidArray($result_arr) and $result_arr['code'] == 200){
            $token = $result_arr['token'];
            $UserObj->setUserWorldChatToken($userId,$token);
        }else{
            $token = 0;
        }
        return $token;

    }

    /**
     * 刷新用户信息方法
     */

    public function refresh($userId, $name, $portraitUri){
        $return = 0;
        if($this->isOpen == 0){
            return 0;
        }

        if(!Buddha_Atom_String::isValidString($portraitUri)){
            $portraitUri = $this->portraitUri;
        }

        $result = $this->rongCloud->user()->refresh($userId, $name, $portraitUri);
        $result_arr = Buddha_Atom_Json::decodeJsonToArr($result);
        if(Buddha_Atom_Array::isValidArray($result_arr) and $result_arr['code'] == 200){
            $return = 1;
        }else{
            $return = 0;
        }
        return $return;



    }

    /**
     * 刷新用户信息方法
     */

    public function checkOnline($userId){
        $return = 0;
        if($this->isOpen == 0){
            return 0;
        }

        $result = $this->rongCloud->user()->checkOnline($userId);
        $result_arr = Buddha_Atom_Json::decodeJsonToArr($result);

        if(Buddha_Atom_Array::isValidArray($result_arr) and $result_arr['code'] == 200){
            $return = $result_arr['status'];
        }else{
            $return = 0;
        }
        return $return;



    }

    /**
     * 封禁用户
     */

    public function block($userId,$minute){
        $return = 0;
        if($this->isOpen == 0){
            return 0;
        }

        $result = $this->rongCloud->user()->block($userId,$minute);
        $result_arr = Buddha_Atom_Json::decodeJsonToArr($result);



        if(Buddha_Atom_Array::isValidArray($result_arr) and $result_arr['code'] == 200){
            $return = 1;
        }else{
            $return = 0;
        }
        return $return;



    }



    /**
     * 解除用户封禁
     */

    public function unBlock($userId){
        $return = 0;
        if($this->isOpen == 0){
            return 0;
        }

        $result = $this->rongCloud->user()->unBlock($userId);
        $result_arr = Buddha_Atom_Json::decodeJsonToArr($result);



        if(Buddha_Atom_Array::isValidArray($result_arr) and $result_arr['code'] == 200){
            $return = 1;
        }else{
            $return = 0;
        }
        return $return;



    }


    /**
     * 解除用户封禁
     */

    public function queryBlock(){
        $userList = array();
        if($this->isOpen == 0){
            return 0;
        }

        $result = $this->rongCloud->user()->queryBlock();
        $result_arr = Buddha_Atom_Json::decodeJsonToArr($result);



        if(Buddha_Atom_Array::isValidArray($result_arr) and $result_arr['code'] == 200){
            $userList = $result_arr['users'];
        }


        return $userList;

    }


    /**
     * 添加用户到黑名单方法
     */

    public function addBlacklist($userId1, $userId2){

        if($this->isOpen == 0){
            return 0;
        }

        $result = $this->rongCloud->user()->addBlacklist($userId1, $userId2);
        $result_arr = Buddha_Atom_Json::decodeJsonToArr($result);


        if(Buddha_Atom_Array::isValidArray($result_arr) and $result_arr['code'] == 200){
            $return = 1;
        }else{
            $return = 0;
        }


        return $return;

    }


    /**
     * 获取被封禁用户方法
     */

    public function queryBlacklist($userId1){
        $userbackList = array();
        if($this->isOpen == 0){
            return 0;
        }

        $result = $this->rongCloud->user()->queryBlacklist($userId1);
        $result_arr = Buddha_Atom_Json::decodeJsonToArr($result);


        if(Buddha_Atom_Array::isValidArray($result_arr) and $result_arr['code'] == 200){
            $userbackList = $result_arr['users'];
        }


        return $userbackList;

    }


    /**
     * 封禁用户方法
     */

    public function removeBlacklist($userId1,$userId2){

        if($this->isOpen == 0){
            return 0;
        }

        $result = $this->rongCloud->user()->removeBlacklist($userId1,$userId2);
        $result_arr = Buddha_Atom_Json::decodeJsonToArr($result);


        if(Buddha_Atom_Array::isValidArray($result_arr) and $result_arr['code'] == 200){
            $return = 1;
        }else{
            $return = 0;
        }


        return $return;

    }



    /**
     * 创建群组
     */

    public function groupCreate($userIdArr){

        $ChatgroupObj = new Chatgroup();
        $ChatgroupmemberObj = new Chatgroupmember();
        $UserObj = new User();


        if($this->isOpen == 0){
            return 0;
        }

        if(!Buddha_Atom_Array::isValidArray($userIdArr)){
            return 0;
        }
        $create_userId = $userIdArr[0];

        if(!Buddha_Atom_String::isValidString($create_userId)){
            return 0;
        }

        /**
         * 检查提供的用户内码数组,每个是否在数据库user表中有,如果没有就返回0
         *
         */

        if(!$UserObj->isValidUserIdArr($userIdArr)){
            return 0;
        }

        /*
         * 先进行群组垃圾回收机制
         */

        $ChatgroupObj->garbageCollection();


        $Db_User = $UserObj->getSingleFiledValues(array('id','username'),"id='{$create_userId}' ");
        $groupName= '未命名';

        $data = array();
        $data['name'] = $groupName;
        $data['founder'] = $Db_User['username'];
        $data['founder_id'] = $create_userId;
        $data['create_id'] = $create_userId;
        $data['drag_id'] = $create_userId;
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];


        $groupId = $ChatgroupObj->add($data);
        unset($data);

        if(!Buddha_Atom_String::isValidString($groupId)){
            return 0;
        }

        $result = $this->rongCloud->group()->create($userIdArr, $groupId, $groupName);


        /**
         * 添加成员到chatgroupmember
         */

        foreach($userIdArr as $k=>$v){

            if($k==0){
                $host = 1;
            }else{
                $host = 0;
            }

            $account_id = $v;
            $Db_Temp_User = $UserObj->getSingleFiledValues(array('id','username'),"id='{$account_id}' ");



            $data  = array();
            $data['account'] = $Db_Temp_User['username'];
            $data['account_id'] = $account_id;
            $data['groupId'] = $groupId;
            $data['host'] = $host;
            $data['vieworder'] = 0;
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $ChatgroupmemberObj->add($data);



        }

        /* 更新会员数目*/
        $ChatgroupObj->updateMemberTotal($groupId);



        $result_arr = Buddha_Atom_Json::decodeJsonToArr($result);


        if(Buddha_Atom_Array::isValidArray($result_arr) and $result_arr['code'] == 200){
            $return = 1;
        }else{
            $return = 0;
        }


        if($return==1){
            return $groupId;
        }


        return $return;

    }


    /**
     * 封禁用户方法
     */

    public function groupDismiss($userId, $groupId){

        if($this->isOpen == 0){
            return 0;
        }

        $result = $this->rongCloud->group()->dismiss($userId, $groupId);
        $result_arr = Buddha_Atom_Json::decodeJsonToArr($result);


        if(Buddha_Atom_Array::isValidArray($result_arr) and $result_arr['code'] == 200){
            $return = 1;
        }else{
            $return = 0;
        }


        return $return;

    }


    /**
     * 加入群组
     */

    public function groupJoin($drag_id,$userIdArr, $groupId, $groupName){
        $ChatgroupObj = new Chatgroup();
        $ChatgroupmemberObj = new Chatgroupmember();
        if($this->isOpen == 0){
            return 0;
        }



        if(!Buddha_Atom_String::isValidString($groupName)){

            $Db_Chatgroup = $ChatgroupObj->getSingleFiledValues(array('name'),"groupId='{$groupId}' ");
            $groupName = $Db_Chatgroup['name'];

        }else{
            $ChatgroupObj->updateRecords(array('name'=>$groupName),"groupId='{$groupId}' ");
        }

        $result = $this->rongCloud->group()->join($userIdArr, $groupId, $groupName);
        $result_arr = Buddha_Atom_Json::decodeJsonToArr($result);


        if(Buddha_Atom_Array::isValidArray($result_arr) and $result_arr['code'] == 200){

            foreach($userIdArr as $k=>$v){
                $ChatgroupmemberObj->insertNewMember($drag_id,$v,$groupId);
            }

            $num = $ChatgroupmemberObj->countRecords(" groupId='{$groupId}' ");
            $ChatgroupObj->updateRecords(array('membertotal'=>$num)," groupId='{$groupId}' ");


            $return = 1;
        }else{
            $return = 0;
        }


        return $return;

    }


    /**
     * 退出群组
     */

    public function groupQuit($userIdArr, $groupId){
        $ChatgroupmemberObj = new Chatgroupmember();
        $ChatgroupObj = new Chatgroup();
        if($this->isOpen == 0){
            return 0;
        }





        $result = $this->rongCloud->group()->quit($userIdArr, $groupId);
        $result_arr = Buddha_Atom_Json::decodeJsonToArr($result);

        $Db_Chatgroup  = $ChatgroupObj->getSingleFiledValues(array('create_id'),"groupId='{$groupId}' ");
        $create_id = $Db_Chatgroup['create_id'];


        if(Buddha_Atom_Array::isValidArray($result_arr) and $result_arr['code'] == 200){


            foreach($userIdArr as $k=>$v){

                $ChatgroupmemberObj->delRecords("account_id='{$v}' and groupId='{$groupId}' ");

            }

            $num = $ChatgroupmemberObj->countRecords(" groupId='{$groupId}' ");
            if($num==0){
                $isOk = Buddha_Thirdpart_Message::getInstance()->groupDismiss($create_id, $groupId);

            }
            $ChatgroupObj->updateRecords(array('membertotal'=>$num)," groupId='{$groupId}' ");

            $return = 1;
        }else{
            $return = 0;
        }


        return $return;

    }

    /**
     * 退出群组
     */

    public function groupRefresh($groupId, $newGroupName){
        $ChatgroupObj = new Chatgroup();
        if($this->isOpen == 0){
            return 0;
        }


        if(!Buddha_Atom_String::isValidString($newGroupName)){

            $Db_Chatgroup = $ChatgroupObj->getSingleFiledValues(array('name'),"groupId='{$groupId}' ");
            $newGroupName = $Db_Chatgroup['name'];

        }else{
            $ChatgroupObj->updateRecords(array('name'=>$newGroupName),"groupId='{$groupId}' ");
        }



        $result = $this->rongCloud->group()->refresh($groupId, $newGroupName);
        $result_arr = Buddha_Atom_Json::decodeJsonToArr($result);


        if(Buddha_Atom_Array::isValidArray($result_arr) and $result_arr['code'] == 200){
            $return = 1;
        }else{
            $return = 0;
        }


        return $return;

    }


    /**
     * 查询群成员
     */

    public function groupQueryUser($groupId){

        $userIdList = array();

        if($this->isOpen == 0){
            return 0;
        }





        $result = $this->rongCloud->group()->queryUser($groupId);
        $result_arr = Buddha_Atom_Json::decodeJsonToArr($result);



        if(Buddha_Atom_Array::isValidArray($result_arr) and $result_arr['code'] == 200){
            $userIdList = $result_arr['users'];
        }


        return $userIdList;

    }





}