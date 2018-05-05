<?php

/**购物订单
 * Class OrderController
 */
class OrderController extends Buddha_App_Action{
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
    }
    public function index(){//订单列表
    	list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
    	$OrderproductObj = new Orderproduct();
        if(!$uid){
           	Buddha_Http_Head::redirectofmobile('请登录','../index.php?a=login&c=account');
        }
        //$view ='';
        $view = Buddha_Http_Input::getParameter('view');
        $where = "(order_type='shopping' OR order_type='heartpro' OR order_type='selfTrading')  AND good_id!=0 AND merchant_uid='{$uid}'";
        if(!$view){
            $where .= " AND (pay_status=1 OR  pay_type='selfTrading') ";
        }elseif($view == 1){
            $where = " pay_type='selfTrading' AND good_id!=0 AND merchant_uid='{$uid}' ";
        }elseif($view == 2){
            $where .= " AND (pay_status=1 OR  pay_type='selfTrading') AND order_status=0 ";
        }elseif($view == 3){
            $where .= " AND (pay_status=1 OR  pay_type='selfTrading') AND order_status=1 ";
        }elseif($view == 4){
            $where .= " AND (pay_status=1 OR  pay_type='selfTrading') AND order_status=2 ";
        }
        $orderObj = new Order();
        $supplyObj = new Supply();
        $orderinfo = $orderObj->getFiledValues(array('id','good_id','final_amt','order_sn','pay_status','pay_type','order_type')," {$where} order by id desc");//获取所有订单的详情
        $Orderproduct_array = array('product_name as goods_name','product_img as goods_thumb','product_price as product_total','merchant_amt as promote_price');
        $goodinfo = $OrderproductObj->getProductByOrderGoodidarr($orderinfo);
        $this->smarty->assign('view',$view);
       	$this->smarty->assign('orderinfo',$orderinfo);
       	$this->smarty->assign('goodinfo',$goodinfo);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
    public function detail(){//用户订单详情
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id = Buddha_Http_Input::getParameter('id');
        $orderObj = new Order();
        $supplyObj = new Supply();
        $RegionObj = new Region();
        $addressObj = new Address();
        $OrderproductObj = new Orderproduct();
        $orderinfo = $orderObj->getSingleFiledValues(array('id','good_id','final_amt','order_sn','pay_status','order_status','addressid','order_type','remarks'),"(order_type='shopping' OR order_type='heartpro') and id='{$id}'");//获取订单详情
        $goodinfo = $OrderproductObj-> getProductByOrderGoodid($orderinfo);
        $addressinfo = $addressObj->getSingleFiledValues('',"id={$orderinfo['addressid']}");//收货地址详情
        if($addressinfo['pro']){//省
            $pro = $RegionObj->getSingleFiledValues(array('name'),"id={$addressinfo['pro']}");
            $addressinfo['addre'] = $pro['name'].'省';
        }
        if($addressinfo['city']){//市
            $city = $RegionObj->getSingleFiledValues(array('name'),"id={$addressinfo['city']}");
            $addressinfo['addre'] .= ' '.$city['name'].'市';
        }
        if($addressinfo['area']){//区县
            $area = $RegionObj->getSingleFiledValues(array('name'),"id={$addressinfo['area']}");
            $addressinfo['addre'] .= ' '.$area['name'];
        }
        $this->smarty->assign('orderinfo',$orderinfo);
        $this->smarty->assign('goodinfo',$goodinfo);
        $this->smarty->assign('addressinfo',$addressinfo);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }



    public function confirm_goods(){//更改发货状态
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $order_id=Buddha_Http_Input::getParameter('order_id');
        $orderObj = new Order();
        $data['order_status'] = 1;
        if($orderObj->edit($data,$order_id)){
            $datas['isok']='true';
            $datas['data'] = '操作成功';
        }else{
            $datas['isok']='false';
            $datas['data']='服务器忙';
        }
        Buddha_Http_Output::makeJson($datas);
    }


}