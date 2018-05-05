<?php
class Userbasic extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }


    /**
     * 判断数据库里有没有有效的数据
     * @param $user_id
     * @return int
     * @author wph 2017-12-11
     */
    public function isHasValidRecord($user_id){

        if($user_id==0 ){

            return 0;
        }
        $num = $this->countRecords(" user_id='{$user_id}' AND isdel=0 ");
        if($num>0){
            return 1;
        }else{
            return 0;
        }

    }

    /**
     * 对用户基础数据表中的封面相册进行添加或者更新操作
     * @param $user_id
     * @param $albumcover
     * @author wph 2017-12-11
     */
    public function addOrUpdateAlbumCover($user_id,$albumcover){

        if($this->isHasValidRecord($user_id)){
          //有数据要进行先删除图片 再进行更新图片
            $data  =  array();
            $data['albumcover'] = $albumcover;
            $this->updateUserbasic($data,$user_id);
        }else{
         //无数据要进行add
            $data  =  array();
            $data['albumcover'] = $albumcover;
            $this->add($data);

        }

    }


    public function lazyaddUserbaisc($user_id){

        $num = $this->countRecords("isdel=0 and user_id='{$user_id}' ");
        if($num==0){
            $data = array();
            $data['user_id'] =$user_id;
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $this->add($data);
        }


    }

   public function updateUserbasic($row,$user_id){
       $this->updateRecords($row," isdel=0 and user_id='{$user_id}' ");
   }

   public function checkIdNumber($user_id){
       $idnumber_ide = $this->getSingleFiledValues(array('idnumber_ide'),"isdel=0 and user_id='{$user_id}' ");
       return $idnumber_ide=(int)$idnumber_ide['idnumber_ide'];
   }

    public  function deleteFileOfImage($user_id,$delpicrow){
        $Db_UserImage  = array();
        $Db_UserImage = $this->getSingleFiledValues($delpicrow," isdel=0 and user_id='{$user_id}' ");

        foreach($Db_UserImage as $k=>$v ){
            if($v){
                @unlink(PATH_ROOT . $v);
            }

        }
    }

}