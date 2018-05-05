<?php
class Userasso extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    /**
     * 返回分润层级SQL 为空字符串或者带AND 的字符串
     * @return mixed
     */
    public function getSqlFrontByLayerLimitNumberStr($table_as='',$login_user_id){
        if(Buddha_Atom_String::isValidString($table_as)){
            $table_as = "{$table_as}.";
        }
        $UserassomoneyObj = new Userassomoney();
        $sql = '';
        $layerlim =(int) $UserassomoneyObj->getLayerLimitNumber();
        if($layerlim>0 AND $layerlim<=10){
            $sql = ' AND ( ';
            for($i=1;$i<=$layerlim;$i++){
                $sql.="  {$table_as}layer{$i}='{$login_user_id}' OR";
            }
            //$sql = substr($sql,0,-2);
            $sql = Buddha_Atom_String::toDeleteTailCharacter($sql,2);
            $sql .= ')';

        }

        return $sql;


    }


    /**
     * 判断数据库里有没有有效的数据,如果没有 就进行插入,有就进行更新
     * @param $user_id
     * @return int
     * @author wph 2017-11-25
     */
    public function isHasValidRecordByUserId($user_id){

        if($user_id==0){

            return 0;
        }
        $num = $this->countRecords(" user_id='{$user_id}' ");
        if($num>0){
            return 1;
        }else{
            return 0;
        }

    }


    /**
     * 人脉关系表进行添加或者更新
     * @param $user_id
     * @param $father_id
     * @author wph 2017-11-25
     */
    public function addOrUpdateUserAsso($user_id,$father_id){

        $UserObj = new User();
        $layer1 = 0 ;
        if($UserObj->isValidUserId($father_id)){
            $layer1 = $father_id;
        }

        $layer2  = $UserObj->getFatherId($layer1);
        $layer3  = $UserObj->getFatherId($layer2);
        $layer4  = $UserObj->getFatherId($layer3);
        $layer5  = $UserObj->getFatherId($layer4);
        $layer6  = $UserObj->getFatherId($layer5);
        $layer7  = $UserObj->getFatherId($layer6);
        $layer8  = $UserObj->getFatherId($layer7);
        $layer9  = $UserObj->getFatherId($layer8);
        $layer10 = $UserObj->getFatherId($layer9);

        $data = array('user_id'=>$user_id,'layer1'=>$layer1,'layer2'=>$layer2,'layer3'=>$layer3,'layer4'=>$layer4,
            'layer5'=>$layer5,'layer6'=>$layer6,'layer7'=>$layer7,'layer8'=>$layer8,'layer9'=>$layer9,'layer10'=>$layer10
            );

        if(!$this->isHasValidRecordByUserId($user_id)){
            //add
            $this->add($data);

        }else{
            //update
            $this->updateRecords($data,"user_id='{$user_id}' ");

        }



    }




}