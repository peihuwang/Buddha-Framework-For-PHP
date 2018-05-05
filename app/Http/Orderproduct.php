<?php
class Orderproduct extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }
    /**
     * @param $Db_Order_Goodid_arr
     * @return mixed
     * 1.0   获取订单详情
     */
    public function getProductByOrderGoodid($Bb_Order)
    {
        $OrderproductObj = New Orderproduct();
        $supplyObj = New Supply();

        $Orderproduct_array = array('product_name as goods_name','product_img as goods_thumb','product_price as product_total','merchant_amt as promote_price');

        $Db_Orderproduct = $OrderproductObj->getSingleFiledValues($Orderproduct_array,"product_id='{$Bb_Order['good_id']}'");

        if(Buddha_Atom_Array::isValidArray($Db_Orderproduct)){
            $goods = $Db_Orderproduct;
        }else{

            if($Bb_Order['order_type']=='heartpro')
            {
                $table = $this->db->getSingleFiledValues(array('table_id','small'),$Bb_Order['order_type'],"id='{$Bb_Order['good_id']}'");

                if(Buddha_Atom_Array::isValidArray($table)){
                    $supply_id = $table['table_id'];
                    $goods = $supplyObj->getSingleFiledValues(array('id','goods_name','market_price','promote_price','goods_thumb'),"id={$supply_id}");

                }else{
                    $goods = array();
                }

            }else{
                $goods = $supplyObj->getSingleFiledValues(array('id','goods_name','market_price','promote_price','goods_thumb'),"id={$Bb_Order['good_id']}");

            }
        }

        return $goods;
    }


    /**
     * @param $Db_Order_Goodid_arr
     * @return mixed
     * 1.0   获取订单列表下==》每一个订单==》全部列表的详情
     */
    public function getProductByOrderGoodidarr($Db_Order_Goodid_arr)
    {
        $OrderproductObj = New Orderproduct();
        $supplyObj = New Supply();

        $Orderproduct_array = array('product_name as goods_name','product_img as goods_thumb','product_price as product_total','merchant_amt as promote_price');

        if(Buddha_Atom_Array::isValidArray($Db_Order_Goodid_arr)){
            foreach($Db_Order_Goodid_arr as $k => $v)
            {//获取订单对应商品的详情

                $Db_Orderproduct = $OrderproductObj->getSingleFiledValues($Orderproduct_array,"product_id='{$v['good_id']}'");

                if(Buddha_Atom_Array::isValidArray($Db_Orderproduct)){
                    $goods = $Db_Orderproduct;
                }else{

                    $supply_id = $v['good_id'];

                    if($v['order_type']=='heartpro')
                    {
                        $table = $this->db->getSingleFiledValues(array('table_id','small'),$v['order_type'],"id='{$v['good_id']}'");

                        if(Buddha_Atom_Array::isValidArray($table)){
                            $supply_id = $table['table_id'];
                            $goods = $supplyObj->getSingleFiledValues(array('id','goods_name','market_price','promote_price','goods_thumb'),"id={$supply_id}");

                        }else{
                            unset($Db_Order_Goodid_arr[$k]);
                        }

                    }else{
                        $goods = $supplyObj->getSingleFiledValues(array('id','goods_name','market_price','promote_price','goods_thumb'),"id={$supply_id}");

                    }
                }

                mb_internal_encoding("UTF-8");

                if(mb_strlen($goods['goods_name'])>10)
                {
                    $goods['goods_name'] = mb_substr($goods['goods_name'],0,10).'...';
                }

                if($v['order_type'] == 'shopping')
                {
                    $orderinfo[$k]['type_name'] = '购物';

                }elseif($v['order_type'] == 'heartpro')
                {
                    $orderinfo[$k]['type_name'] = '1分购';

                    $goods['goods_thumb'] = substr($table['small'],1) ;

                }else{
                    $orderinfo[$k]['type_name'] = '';
                }
                $goodinfo[$k] = $goods;
            }
        }else{
            $goodinfo = array();
        }
        return $goodinfo;
    }


    /**
     * 根据购物车数据源来创建子产品
     * @param $order_id
     * @param $DB_Cart
     */
    public function createOrderproductFromCart($order_id,$order_sn,$DB_Cart){
       $CartObj = new Cart();
       if(strlen($order_id) and Buddha_Atom_Array::isValidArray($DB_Cart)){
           foreach($DB_Cart as $k=>$v){
               $product_id = $v['product_id'];
               $product_table = $v['product_table'];
               $product_name = $v['goods_name'];
               $product_img = $CartObj->getProductImg($product_table,$product_id);
               $product_price = $v['goods_price'];
               $product_total = $v['goods_number'];
               $product_amt = $v['goods_price']*$v['goods_number'];
               $merchant_id = $v['merchant_id'];
               $merchant_amt = $v['goods_price']*$v['goods_number'];

               $data = array();
               $data['product_id'] = $product_id;
               $data['product_table'] = $product_table;
               $data['product_name'] = $product_name;
               $data['product_img'] = $product_img;
               $data['product_price'] = $product_price;
               $data['product_total'] = $product_total;
               $data['order_id'] = $order_id;
               $data['product_amt'] = $product_amt;
               $data['merchant_id'] = $merchant_id;
               $data['merchant_amt'] = $merchant_amt;
               $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
               $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];

               $this->add($data);



           }
       }

    }

    public function getProductNamesByOrderId($order_id){
        $order_id = (int)$order_id;
        $product_name = '';
        if($order_id>1){
           $Db_Orderproduct = $this->getFiledValues(array('product_name'),"order_id = '{$order_id}' ");
           if(Buddha_Atom_Array::isValidArray($Db_Orderproduct)){
               foreach ($Db_Orderproduct as $k=>$v){
                   $product_name.="{$v['product_name']}";
               }
           }

        }

        return $product_name;



    }

    /**
     * 查询订单子表 判断是否有订单产品的记录
     * @param $order_id
     * @return int
     */
    public function isHasOrderproductRecord($order_id){
        $num = $this->countRecords("order_id='{$order_id}' ");
        if($num==0){
            return 0;
        }else{
            return 1;
        }

    }

}