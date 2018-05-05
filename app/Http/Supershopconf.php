<?php
class Supershopconf extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    /**
     * 删除:隐藏开通的无敌店铺
     * @param $id
     * @return mixed
     * @author 2017-11-24
     */
    public  function del($id){
        return  $this->db->update($this->table, array(
            'isdel'=>1
        ), array(
            "id" => $id
        ));
    }


    public function getOption($id=0){
        $table='';
        $topdata = $this->db->getFiledValues('',  $this->table," buddhastatus=0 AND isdel=0 order by id asc");
        $this->getCateOption ( $topdata,$table,0,$id);
        return $table;
    }

    public function getCateOption($cate, &$table, $startID = 0, $index = 0, $level = 0) {
        foreach ( $cate as $key => $value ) {

                $table .= '<option value="' . $value ['id'] . '"';
                $value ['id'] == $index && $table .= ' selected="select" style="background:#ffffde"';
                $table .= '>' . str_repeat ( '&nbsp;&nbsp;', $level ) . '└ ' . htmlspecialchars ( $value ['appname'] ) . '</option>';


        }
   }

    /**
     * 获取店铺的内码id
     * @param $appKey
     * @param $appSecret
     * @return int
     * @author wph 2017-12-01
     */
    public function getShopIdByAppKeyAndAppSecretInt($appKey,$appSecret){

        if(!Buddha_Atom_String::isValidString($appKey) or  !Buddha_Atom_String::isValidString($appSecret)){
            return 0;
        }

        $num = $this->countRecords("appKey='{$appKey}' AND  appSecret='{$appSecret}' ");
        if($num>0){
            $Db_Supershopconf = $this->getSingleFiledValues(array('shop_id'),"appKey='{$appKey}' AND  appSecret='{$appSecret}' ");
            if(Buddha_Atom_String::isValidString($Db_Supershopconf['shop_id'])){
                return $Db_Supershopconf['shop_id'];
            }else{
                return 0;
            }

        }else{
            return 0;
        }

    }


    /**
     * 获取店铺的会员内码id
     * @param $appKey
     * @param $appSecret
     * @return int
     * @author wph 2017-12-01
     */
    public function getUserIdByAppKeyAndAppSecretInt($appKey,$appSecret){

        if(!Buddha_Atom_String::isValidString($appKey) or  !Buddha_Atom_String::isValidString($appSecret)){
            return 0;
        }

        $num = $this->countRecords("appKey='{$appKey}' AND  appSecret='{$appSecret}' ");
        if($num>0){
            $Db_Supershopconf = $this->getSingleFiledValues(array('user_id'),"appKey='{$appKey}' AND  appSecret='{$appSecret}' ");
            if(Buddha_Atom_String::isValidString($Db_Supershopconf['user_id'])){
                return $Db_Supershopconf['user_id'];
            }else{
                return 0;
            }

        }else{
            return 0;
        }

    }





}


