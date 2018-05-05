<?php
class Goods extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }
    //给goods表添加默认图片
    public function setFirstGalleryImgToGoods($goods_id){
        $defaultgimages= $this->db->getSingleFiledValues('','goodsimages',"goods_id='{$goods_id}' order by id ASC");
        $this->db->updateRecords(array('isdefault'=>'1'),'goodsimages',"id='{$defaultgimages['id']}'");
        $dataImg=array();
        $dataImg ['goods_thumb'] = $defaultgimages['goods_thumb'];
        $dataImg ['goods_img'] = $defaultgimages['goods_img'];
        $dataImg ['goods_large'] = $defaultgimages['goods_large'];
        $this->updateRecords($dataImg,"id='{$goods_id}'");
    }





}