<?php

/**
 * Class Buddha_App_Model
 */
class Buddha_App_Model{
    protected $db;
    protected $prefix;
    protected $smarty;
    protected $classname;
    protected $table;
    protected $debug_mode = false;
    public function __construct(){
        $this->db=Buddha_Driver_Db::getInstance(
            Buddha::getDatabaseConfig()
        );
        $this->prefix=$this->db->getPrefix();
        $this->smarty = Smarty::getInstance(
            Buddha::getSmartyConfig()
        );
        $this->classname = __CLASS__;
    }


    public function debug()
    {
        $this->debug_mode = true;

        return $this;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function add($data){
        if($this->debug_mode){



            return $this->db->debug()->addRecords($data, $this->table) ;
        }else{
            return $this->db->addRecords($data, $this->table) ;
        }


    }






    public  function del($id){

        if($this->debug_mode){
            return $this->db->debug()->delete($this->table, array("id" => $id));
        }else{
            return   $this->db->delete($this->table, array("id" => $id));
        }


    }

    public function edit($data,$id){

        if($this->debug_mode){
            return $this->db->debug()->update($this->table, $data, array(
                "id" => $id
            ));
        }else{
            return  $this->db->update($this->table, $data, array(
                "id" => $id
            ));
        }




    }
    public function fetch($id){
        $datas = $this->db->select($this->table, '*', array('id'=>$id));
        return  $datas[0];
    }

    public function getFiledValues($field = array(), $condition = ''){

        if($this->debug_mode){
            return $this->db->debug()->getFiledValues($field , $this->table, $condition);
        }else{
            return $this->db->getFiledValues($field , $this->table, $condition);
        }




    }

    public function getSingleFiledValues($field = array(), $condition = ''){

        if($this->debug_mode){
            $datas = $this->db->debug()->getFiledValues($field , $this->table, $condition);
            if(count($datas) and is_array($datas)){
                return $datas[0];
            }else{
                return 0;
            }
        }else{
            $datas = $this->db->getFiledValues($field , $this->table, $condition);
            if(count($datas) and is_array($datas)){
                return $datas[0];
            }else{
                return 0;
            }
        }



    }

    public function updateRecords($field = array(), $condition = '') {

        if($this->debug_mode){
            return $this->db->debug()->updateRecords($field, $this->table, $condition);
        }else{
            return $this->db->updateRecords($field, $this->table, $condition);
        }


    }

    public function delRecords($condition = '') {

        if($this->debug_mode){
            return $this->db->debug()->delRecords($this->table, $condition);
        }else{
            return $this->db->delRecords($this->table, $condition);
        }

    }
    public function countRecords($condition = '') {

        if($this->debug_mode){
            return $this->db->debug()->countRecords($this->table, $condition);
        }else{
            return $this->db->countRecords($this->table, $condition);
        }

    }


}