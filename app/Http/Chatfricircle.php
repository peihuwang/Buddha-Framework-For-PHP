<?php
class Chatfricircle extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }


    /**
     * 操作相册文章表,进行删除,同时清空相册文件,再删除相册表数据和相册文章数据
     * @param $user_id
     * @param $chatfricircle_id
     * @author wph 2017-12-18
     */
    public function deleteCircle($user_id,$chatfricircle_id){
        if($this->isOwnCircle($user_id,$chatfricircle_id)){

            $ChatfrialbumObj = new Chatfrialbum();
            $num = $ChatfrialbumObj->countRecords("user_id='{$user_id}' AND chatfricircle_id='{$chatfricircle_id}' ");
            if($num){
                $Db_Chatfrialbum  = $ChatfrialbumObj->getFiledValues(array('image','id'),"user_id='{$user_id}' AND chatfricircle_id='{$chatfricircle_id}' ");
                if(Buddha_Atom_Array::isValidArray($Db_Chatfrialbum)){

                    foreach($Db_Chatfrialbum as $k=>$v){
                        $image = $v['image'];
                        $id = $v['id'];
                        @unlink(PATH_ROOT.$image);
                        if(!file_exists(PATH_ROOT.$image)){
                            $ChatfrialbumObj->del($id);
                        }


                    }

                }


            }
            $this->delRecords("user_id='{$user_id}' AND id='{$chatfricircle_id}' ");

        }

    }
    /**
     * 判断这个是不是本人的
     * @param $user_id
     * @param $chatfricircle_id
     * @return int
     * @author wph 2017-12-18
     */
    public function isOwnCircle($user_id,$chatfricircle_id){

        $num = $this->countRecords("user_id='{$user_id}' AND id='{$chatfricircle_id}' ");
        if($num){
            return 1;
        }else{
            return 0;
        }

    }


    /**
     * 返回提醒谁看的数据例如 |2|3|4| 当返回 -1代表数据提供的不是好友数据
     * @param $seetype
     * @param $my_id
     * @param $noticearr
     * @return int|string
     * @author wph 2017-12-13
     */
    public function getNoticeArr($seetype,$my_id,$noticearr){
        $ChatfriendObj = new Chatfriend();

         if(Buddha_Atom_String::isJson($noticearr)){
             $noticearr = json_decode($noticearr);
         }

        if(Buddha_Atom_Array::isValidArray($noticearr)){

            foreach($noticearr as $k=>$v){

                if(!$ChatfriendObj->isMyFriend($my_id,$v)){
                     return -1;
                }

            }

            return $noticearr = Buddha_Atom_Array::getVerticalBarOneDimensionStr($noticearr);

        }else{

            return 0;
        }

    }

    /**
     * 返回提醒谁看的数据例如 |2|3|4| 当返回 -1代表数据提供的不是好友数据
     * @param $seetype
     * @param $my_id
     * @param $noticearr
     * @return int|string
     * @author wph 2017-12-13
     */
    public function getPartSeeArr($seetype,$my_id,$partseearr){
        $ChatfriendObj = new Chatfriend();
        if($seetype!=2){
            return 0;
        }
        if(Buddha_Atom_String::isJson($partseearr)){
            $partseearr = json_decode($partseearr);
        }


        if(Buddha_Atom_Array::isValidArray($partseearr)){

            foreach($partseearr as $k=>$v){

                if(!$ChatfriendObj->isMyFriend($my_id,$v)){
                    return -1;
                }

            }

            return $partseearr = Buddha_Atom_Array::getVerticalBarOneDimensionStr($partseearr);

        }else{

            return 0;
        }

    }


    /**
     * 返回提醒谁看的数据例如 |2|3|4| 当返回 -1代表数据提供的不是好友数据
     * @param $seetype
     * @param $my_id
     * @param $noticearr
     * @return int|string
     * @author wph 2017-12-13
     */
    public function getNoSeeArr($seetype,$my_id,$noseearr){
        $ChatfriendObj = new Chatfriend();
        if($seetype!=3){
            return 0;
        }
        if(Buddha_Atom_String::isJson($noseearr)){
            $noseearr = json_decode($noseearr);
        }


        if(Buddha_Atom_Array::isValidArray($noseearr)){

            foreach($noseearr as $k=>$v){

                if(!$ChatfriendObj->isMyFriend($my_id,$v)){
                    return -1;
                }

            }

            return $noseearr = Buddha_Atom_Array::getVerticalBarOneDimensionStr($noseearr);

        }else{

            return 0;
        }

    }



    /**
     * 返回查询朋友圈文章的SQL 为空字符串或者带AND 的字符串
     * @return mixed
     */
    public function getSqlWhereByUserIdStr($table_as='',$login_user_id){
        if(Buddha_Atom_String::isValidString($table_as)){
            $table_as = "{$table_as}.";
        }
        $ChatfriendObj = new Chatfriend();
        $friend_in_str = $ChatfriendObj->getFriendIdInStr($login_user_id); //2,3,4

        $sql ='';
        if(Buddha_Atom_String::isValidString($friend_in_str)){
            $sql .='(';
            $sql.="   ({$table_as}user_id IN ({$friend_in_str}) AND seetype=0 )";

            $sql.=" OR  ( {$table_as}user_id IN ({$friend_in_str}) AND   {$table_as}partseearr like '%{$login_user_id}%' AND seetype=2)   ";

            $sql.=" OR ( {$table_as}user_id IN ({$friend_in_str}) AND  {$table_as}noseearr NOT like '%{$login_user_id}%' AND seetype=3) ";
            $sql .=')';
        }

        $sql .= " OR {$table_as}user_id='{$login_user_id}' ";


        return $sql;


    }



}