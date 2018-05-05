<?php
class Bill extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }


    function insertBill($user_id,$order_id,$money,$order_sn){//添加账单
        $BullObj = new Bill();
        $num = $BullObj->countRecords("user_id='{$user_id}' AND order_id='$order_id'");
        if(!$num){
            $data = array();
            $data['user_id'] = $userInfo['id'];
            $data['order_sn'] = $order_sn;
            $data['order_id'] = $order_id;
            $data['order_type'] = 'commission';//分润
            $data['order_desc'] = '分润';
            $data['is_order'] = 1;
            $data['orient'] = '+';
            $data['billamt'] = '+' . $money;
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $insert_id = $BillObj->add($data);
        }

    }





    /**2017-09-11 判断用户是否可以提现 0=不能 1=可以提现
     * @param $money
     * @return int
     */
    public function isCouldWithdrawals($money,$lightarr){
        $will_out_money = (float)$money;
        $out_money_limit = $this->getLeastMoneyOutByLightFloat($lightarr);
        $out_money_limit = (float)$out_money_limit;
        if($will_out_money<$out_money_limit or $money<1 or $out_money_limit<1){
            return 0;
        }else{
            return 1;
        }

    }

    /**
     * 根据用户groupid数值 返回相应的提现最低金额
     * @param string $which
     * @return float|int
     */
    public function getMoneyLimitByGroupid($which=''){

        $arr = array();
        $arr[1] = array('name'=>'商家','groupid'=>1,'money_limit_out'=>4800);
        $arr[2] = array('name'=>'代理商','groupid'=>2,'money_limit_out'=>20);
        $arr[3] = array('name'=>'商家','groupid'=>3,'money_limit_out'=>20);
        $arr[4] = array('name'=>'普通会员','groupid'=>4,'money_limit_out'=>20);
        if(Buddha_Atom_Array::isKeyExists($which,$arr)){
            return $arr[$which]['money_limit_out'];
        }else{
            $money = 20;
            $money = (float)$money;
            return $money;
        }

    }

    /**
     * 通过客户的点亮数组来得到客户的最低提现金额
     */
    public function getLeastMoneyOutByLightFloat($lightarr){

        if(Buddha_Atom_Array::isValidArray($lightarr)){


            $money_arr = array();
            foreach($lightarr as $k=>$v){
                $temp_is_light= $v['is_light'];
                $temp_groupid = $v['groupid'];
                $money_arr[] = $this->getMoneyLimitByGroupid($temp_groupid);

                if($temp_is_light==1){
                    $money_arr[] = $this->getMoneyLimitByGroupid($temp_groupid);
                }else{
                    $money_arr[] = $this->getMoneyLimitByGroupid();
                }



            }

            $max_value = Buddha_Atom_Array::getMaxValueFromArr($money_arr);


            return $max_value;

        }else{
            return $this->getMoneyLimitByGroupid();
        }


    }



    /**
     * 2017-09-11 返回指定下标配置数组,
     * groupid=4 普通会员
     * groupid=3 合伙人
     * groupid=1 商家
     * groupid=2 代理
     *
     * @return array
     * @author wph
     */
    public function getBillConfigArr($which='',$lightarr){


        $money_limit_out =$this->getLeastMoneyOutByLightFloat($lightarr);

        $arr = array();
        $arr['out_tip'] = "提现金额满{$money_limit_out}元方能提现";
        $arr['out_money_limit']= $money_limit_out;

        if(isset($arr[$which])){
            return $arr[$which];
        }else{
            return $arr;
        }

    }


    /**
     * @param $order_type
     * @return string
     * 消费类型
     */
    public function  getBillOrderdescByOrderType($order_type){
        $BillOrderdesc='';
        if($order_type=='shop.v'){
            $BillOrderdesc='店铺认证';
        }
        if($order_type=='info.top'){
            $BillOrderdesc='置顶';
        }

        if($order_type=='info.market'){
            $BillOrderdesc='跨区域推广';
        }
        if($order_type=='info.see'){
            $BillOrderdesc='信息查看';
        }
        if($order_type=='money.out') {
            $BillOrderdesc = '会员提现';
        }
        if($order_type=='shopping') {
            $BillOrderdesc = '商品交易收入';
        }
        if($order_type=='heartpro') {
            $BillOrderdesc = '1分购购买';
        }
        if($order_type=='e_netcom'){
            $BillOrderdesc = 'e网通认证费';
        }
        return $BillOrderdesc;
    }

/*
    public function setPlatOrderBill($order_id_or_order_sn){
        $OrderObj = new Order();
        $num = $OrderObj->countRecords( " isdel=0
        and (id='{$order_id_or_order_sn}' or order_sn='{$order_id_or_order_sn}')
          and pay_status=1
         ");
        if($num){
            $Db_Order = $OrderObj->getSingleFiledValues(''," isdel=0
        and (id='{$order_id_or_order_sn}' or order_sn='{$order_id_or_order_sn}' and pay_status=1");
            $final_amt=$Db_Order['final_amt'];
            $order_type=$Db_Order['order_type'];
            $user_id =$Db_Order['user_id'];


        }else{
            return 0;
        }


    }

    public function insertOrderBill($order_id_or_order_sn,$order_final_amt){
        $insert_id=0;
        $OrderObj = new Order();
        $num = $OrderObj->countRecords( " isdel=0
        and (id='{$order_id_or_order_sn}' or order_sn='{$order_id_or_order_sn}')
          and pay_status=1
         ");
        if($num){
            $Db_Order = $OrderObj->getSingleFiledValues(''," isdel=0
        and (id='{$order_id_or_order_sn}' or order_sn='{$order_id_or_order_sn}' and pay_status=1");

            $final_amt=$Db_Order['final_amt'];
            $order_type=$Db_Order['order_type'];
            $user_id =$Db_Order['user_id'];
            if(abs($final_amt)>0){
                $data =array();


            }


        }


        return $insert_id;
    }*/

}