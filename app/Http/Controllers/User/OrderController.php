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
    /**
     *  订单列表
     */
    public function index()
    {//订单列表
    	list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if(!$uid){
           	Buddha_Http_Head::redirectofmobile('请登录','../index.php?a=login&c=account');  	
        }
        $order_id=Buddha_Http_Input::getParameter('order_id');
        $view=Buddha_Http_Input::getParameter('view');
        $orderObj = new Order();
        $userObj = new User();
        $billObj = new Bill();
        $supplyObj = new Supply();
        $OrderproductObj = new Orderproduct();
        if($order_id)
        {//付款成功后增加商户余额
            $orders = $orderObj->getSingleFiledValues(array('good_id','final_amt'),"id={$order_id}");//获取商品编号和成交价格
            $goodsinfo = $supplyObj->getSingleFiledValues(array('user_id'),"id={$orders['good_id']}");//获取商品所属用户的id
            $ordetinfo = $orderObj->getSingleFiledValues(array('order_sn'),"id={$order_id}");
            $userinfo = $userObj->getSingleFiledValues('',"id={$goodsinfo['user_id']}");
            $data = array();
            $data['banlance'] = $userinfo['banlance'] + $orders['final_amt'];
            $userObj->edit($data,$goodsinfo['user_id']);//更新商户余额
            //商家账单明细
            $data = array();
            $data['user_id'] = $goodsinfo['user_id'];
            $data['order_sn'] = $ordetinfo['order_sn'];
            $data['order_id'] = $order_id;
            $data['is_order'] = 1;
            $data['order_type'] = 'shopping';
            $data['order_desc']  ='商品交易收入';
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $data['billamt'] = $orders['final_amt']; 
            $billObj->add($data);
            //购买者账单明细
            $datas = array();
            $datas['user_id'] = $uid;
            $datas['order_sn'] = $ordetinfo['order_sn'];
            $datas['order_id'] = $order_id;
            $datas['is_order'] = 1;
            $datas['order_type'] = 'consume';
            $datas['order_desc']  ='购物消费';
            $datas['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $datas['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $datas['orient']  ='-';
            $datas['billamt'] = '-'.$orders['final_amt']; 
            $billObj->add($datas);

        }


        $where = "(order_type='shopping' OR order_type='heartpro') and user_id={$uid}";

        if(!$view){
            $where .= " AND (pay_status=1 OR  pay_type='selfTrading') ";
        }elseif($view == 1){
            $where = " pay_type='selfTrading' AND good_id!=0 AND merchant_uid='{$uid} AND order_status=0 ' ";
        }elseif($view == 2){
            $where .= " AND (pay_status=1 OR  pay_type='selfTrading') AND order_status=0 ";
        }elseif($view == 3){
            $where .= " AND (pay_status=1 OR  pay_type='selfTrading') AND order_status=1 ";
        }elseif($view == 4){
            $where .= " AND (pay_status=1 OR  pay_type='selfTrading') AND order_status=2 ";
        }

        $orderinfo = $orderObj->getFiledValues(array('id','good_id','final_amt','order_sn','pay_status','pay_type','order_type')," {$where} order by id desc");//获取所有订单的详情


        $goodinfo = $OrderproductObj->getProductByOrderGoodidarr($orderinfo);

        $this->smarty->assign('view',$view);
       	$this->smarty->assign('orderinfo',$orderinfo);
       	$this->smarty->assign('goodinfo',$goodinfo);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    /**
     *  订单详情
     */
    public function detail()
    {//用户订单详情
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=Buddha_Http_Input::getParameter('id');
        $orderObj = new Order();
        $supplyObj = new Supply();
        $RegionObj = new Region();
        $addressObj = new Address();
        $OrderproductObj = New Orderproduct();


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


    /**
     * 更改收货状态
     */
    public function confirm_goods()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $order_id=Buddha_Http_Input::getParameter('order_id');
        $orderObj = new Order();
        $data['order_status'] = 2;
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