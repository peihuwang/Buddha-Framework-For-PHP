<?php
class Supershopglob extends  Buddha_App_Model{
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


    public  function deleteFIleOfPicture($id){
        $Db_Image =$this->fetch($id);
        $sourcepic = $Db_Image['logo'];


        @unlink(PATH_ROOT . $sourcepic);


    }

}