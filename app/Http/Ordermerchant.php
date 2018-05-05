<?php
class Ordermerchant extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    /**
     * 插入老的订单商家表 并且返回insertid
     * @param $order_id
     * @param $order_sn
     * @param $merchant_id
     * @param $marchant_amt
     * @param $product_idstr
     * @return mixed
     */
    public function getInsertVersion1OrderMerchantInt($order_id,$order_sn,$merchant_id,$marchant_amt,$product_idstr,$createtime=0,$createtimestr=0){

        $num = $this->countRecords("order_id='{$order_id}' AND order_sn='{$order_sn}' AND merchant_id='{$merchant_id}' ");
        if($num==0){

            $data = array();
            if($createtime==0 or $createtimestr==0){
                $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            }else{
                $data['createtime'] = $createtime;
                $data['createtimestr'] = $createtimestr;
            }
            
            $data['merchant_id'] = $merchant_id;
            $data['marchant_amt'] = $marchant_amt;
            $data['product_idstr'] = $product_idstr;
            $data['order_id'] = $order_id;
            $data['order_sn'] = $order_sn;
            return $this->add($data);

        }


    }

    /**
     * 获取商家的销售数组
     * @param $DB_Cart
     * @return array
     */
    public function getMerchatCartArr($DB_Cart){
        $merchantcartarr = array();
        if(Buddha_Atom_Array::isValidArray($DB_Cart)){
            foreach($DB_Cart as $k=>$v){
                $merchant_id = $v['merchant_id'];
                $merchantcartarr[$merchant_id][] = $v;

            }
        }

        return $merchantcartarr;
    }


    /**
     * 把购物车的产品生成商家订单
     * @param $order_id
     * @param $order_sn
     * @param $DB_Cart
     */
    public function createOrdermerchantFromCart($order_id,$order_sn,$DB_Cart){
        $Merchatarr = $this->getMerchatCartArr($DB_Cart);
        if(Buddha_Atom_Array::isValidArray($Merchatarr)){

            foreach($Merchatarr as $k1=>$v1){

                $merchant_id = $k1;
                $marchant_amt = 0;
                $product_idstr='';
                foreach($v1 as $k2=>$v2){
                    $goods_number = (int)$v2['goods_number'];
                    if($goods_number<1){
                        $goods_number=1;
                    }
                    $marchant_amt+=$v2['goods_price']*$goods_number;
                    $temp_product_idstr = "{$v2['product_table']}:{$v2['product_id']}";
                    $product_idstr.=$temp_product_idstr.',';

                }

                $data = array();
                $data['merchant_id'] = $merchant_id;
                $data['marchant_amt'] = $marchant_amt;
                $data['order_id'] = $order_id;
                $data['order_sn'] = $order_sn;
                $data['product_idstr'] = Buddha_Atom_String::toDeleteTailCharacter($product_idstr,1);
                $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                $this->add($data);



            }


        }


    }

}