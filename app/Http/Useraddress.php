<?php
class Useraddress extends  Buddha_App_Model{
    public function __construct(){
        parent::__construct();
        $this->table = strtolower(__CLASS__);

    }

    public function editSilent($data,$id){
        return $this->db->updateRecords($data, $this->prefix.'useraddress',"user_id={$id}");
    }

    public function  useraddressRegion($list){
        if(is_array($list)){
            foreach($list as $k=>$v){
                $provincenumber = $v['province'];
                $province= $provincenumber;//省
                if($province){
                    $provincearr = $this->db->getSingleFiledValues('', $this->prefix.'region', " number='{$province}'");
                    $province = $provincearr['name'];
                    $list[$k]['province']=$province;
                }
                $citynumber = $v['city'];
                $city= $citynumber;//市
                if($city){
                    $cityarr = $this->db->getSingleFiledValues('', $this->prefix.'region', " number='{$city}'");
                    $city = $cityarr['name'];
                    $list[$k]['city']=$city;
                }
                $areanumber =$v['area'];
                $area= $areanumber;//区
                if($area){
                    $areaarr = $this->db->getSingleFiledValues('', $this->prefix.'region', " number='{$area}'");
                    $area = $areaarr['name'];
                    $list[$k]['area']=$area;
                }

            }
            return $list;
        }
        return $list;
    }



}