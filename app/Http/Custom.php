<?php
class Custom extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }


    /**
     * @return object
     * 判断 该自定义表单是否属于该商家
     */




    /**
     * @return object
     * 判断该自定义表单的ID是否有效
     */
    public function isCustomidEffectiveByCustomid($table_name,$custom_id,$table_id)
    {
        $custom_id=(int)$custom_id;
        $table_id=(int)$table_id;

        if($custom_id AND Buddha_Atom_String::isValidString($table_name)){

            $where= " id='$custom_id' AND tabla_name='{$table_name}' AND t_id='{$table_id}'";

            $Db_Custom_num = $this->countRecords($where);
        }
        return $Db_Custom_num;
    }


    public function customadd($arr,$table_name,$good_id){
        if(Buddha_Atom_Array::isValidArray($arr)){
            $cu['t_id'] = $good_id;
            $cu['t_name'] = $table_name;
            $cu['add_time'] = time();
            foreach ($arr as $k=>$v){
                $sub1 = $sub2 = $sub3 = '';
                $aa = explode(',', $k);
                $cu['sub'] = $aa[0];
                $cu['c_type'] = $aa[1];
                $cu['sort'] = $aa[2];
                $cu['c_title'] = $v;

                if(substr_count($k)==3){
                    $cu['sub_1'] = $aa[3];
                }
                $cu['arrkey'] = array_keys($arr[0]);
                $custom_id[] = $this->add($cu);
            }
            return $custom_id;
        }
    }



    public function Custom_add($arr,$table_name,$good_id)
    {
        $cu['t_id'] = $good_id;
        $cu['t_name'] = $table_name;
        $cu['add_time'] = time();
        if($arr['type']==1 || $arr['type']==2 || $arr['type']==3 || $arr['type']==4){
            foreach($arr['list'] as $k=>$v) {
                $aa = explode(',', $k);
                $sub0 = $aa[0];
                $sub1 = $aa[1];
                $sub2 = $aa[2];
    //     --------------------------
                $cu['sub'] = $sub0;
                $cu['c_type'] = $sub1;
                $cu['sort'] = $sub2;
                $cu['c_title'] = $v;
                $txt_id[] = $this->add($cu);
    //     -----------------------
            }
        }else if($arr['type']==5||$arr['type']==6){
            foreach ($arr['list'] as $kk => $vv) {
                $sub1 =  $sub2 = $sub3='';
                $aa_1 = explode(',', $kk);
                $sub0 = $aa_1[2];
                $sub1 = $aa_1[1];
                $sub2 = $aa_1[2];
                $sub3 = $aa_1[3];
    //     --------------------------
                $cu['sub'] = $sub0;
                $cu['c_type'] = $sub1;
                $cu['sort'] = $sub2;
                $cu['sub_1'] = $sub3;
                $cu['c_title'] = $vv;
                $txt_id[] = $this->add($cu);
    //     -----------------------
            }
        }
        return $txt_id;
    }





}