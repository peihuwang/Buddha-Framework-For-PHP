<?php
class Paylog extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }


    /**存放支付记录 包括充值 付款 提款
     * @param mixed $order_id   订单号或者订单编号
     * @param mixed $amount  付款金额
     * @param int $is_pay  0未付款  1付款
     */
    public function insertOrderPaylog($order_id_or_order_sn,$order_final_amt,$is_pay = 0){
        $OrderObj = new Order();
        $Db_Order = $OrderObj->getSingleFiledValues(array('id'), " isdel=0
        and (id='{$order_id_or_order_sn}' or order_sn='{$order_id_or_order_sn}') ");
        $order_id = (int)$Db_Order['id'] ? $Db_Order['id'] : $order_id_or_order_sn;
        $order_sn = $Db_Order['order_sn'];
        $data = array();
        $data['order_id'] = $order_id;
        $data['order_sn'] = $order_sn;
        $data['is_pay'] = (int)$is_pay;
        $data['order_final_amt'] = $order_final_amt;
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $insert_id = $OrderObj->add($data);
        return $insert_id;
    }

    /**取得上次未支付的paylog_id
     * @param mixed $order_id_or_order_sn 订单号或者订单编号
     * @return int
     */
    public function getPaylogid($order_id_or_order_sn){
        $OrderObj = new Order();
        $Db_Order = $OrderObj->getSingleFiledValues(array('id'), " isdel=0
        and (id='{$order_id_or_order_sn}' or order_sn='{$order_id_or_order_sn}') ");
        $order_id = (int)$Db_Order['id'] ;
        return $order_id;
    }



}