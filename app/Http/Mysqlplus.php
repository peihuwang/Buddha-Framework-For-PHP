<?php
class Mysqlplus extends  Buddha_App_Model{
    public function __construct(){
        parent::__construct();
        $this->table = strtolower(__CLASS__);

    }

    /**
     * 判断指定表中的内码id是否存在
     * @param $tablestr
     * @param $table_id
     * @return int
     * @author wph 2017-09-21
     */
    public function isValidTableId($tablestr,$table_id){

        if($this->isValidTable($tablestr)==0){
            return 0;
        }

        $num = $this->db->countRecords($tablestr,"id='{$table_id}'");
        if($num){
            return 1;
        }else{
            return 0;
        }

    }


    /**判断表是否存在
     * @param $tablestr
     * @return int
     * @author wph 2017-09-21
     */
    public function isValidTable($tablestr)
    {
        if(Buddha_Atom_String::isValidString($tablestr)){
            $sql = "SHOW TABLES LIKE  '{$this->prefix}{$tablestr}'";
            $hasdata = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            if(count($hasdata)){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }

    public function getCatNameByCatidStr($demandcatid,$table)
    {
        if($demandcatid>0){
            $sql = "select cat_name from  {$this->prefix}{$table} WHERE id='{$demandcatid}' AND ifopen=0 AND isdel=0 ";
            $Db_Demandcat = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            if(Buddha_Atom_Array::isValidArray($Db_Demandcat)){
                return $Db_Demandcat[0]['cat_name'];
            }else{
                return '';
            }
        }
        return '';
    }



}