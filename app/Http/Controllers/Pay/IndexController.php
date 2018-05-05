<?php

/**
 * Class IndexController
 */
class IndexController extends Buddha_App_Action{

	public function __construct(){
		parent::__construct();
		$this->classname=__CLASS__;
		$this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
	}

	public function test(){
		$OrderObj = new Order();
		$UserObj = new User();
		$OrderObj = new Order();
		$order_sn= '20170508155750000000000006';


		$OrderObj->setOrderPaid($order_sn);
		exit;

	}



	public function index(){

	    //$order_type  $good_table $good_id $final_amt

		$OrderObj = new Order();
		$UserObj = new User();
		$user_id = (int)Buddha_Http_Cookie::getCookie('uid');

		$level = array('level1'=>0,'level2'=>0,'level3'=>0);
		if($user_id){
			$feildarr = array('level1','level2','level3');
			$level = $UserObj->getSingleFiledValues($feildarr,"isdel=0 and id='{$user_id}' ");
		}

		$good_id = Buddha_Http_Input::getParameter('id');
		$good_table = Buddha_Http_Input::getParameter('good_table');
		$order_type = Buddha_Http_Input::getParameter('order_type');
		$final_amt = Buddha_Http_Input::getParameter('final_amt');
		$pc = Buddha_Http_Input::getParameter('pc');

		if($user_id==0){
		    if($pc==1){
                $jsondata = array();
                $jsondata['url'] = 'index.php?a=login&c=account';
                $jsondata['errcode'] = 1;
                $jsondata['errmsg'] = "请登陆";
                Buddha_Http_Output::makeJson($jsondata);
            }else{
                $jsondata = array();
                $jsondata['url'] = 'index.php?a=login&c=account';
                $jsondata['errcode'] = 1;
                $jsondata['errmsg'] = "请登陆";
                Buddha_Http_Output::makeJson($jsondata);
            }
        }

		$payname='微信支付';
		$payment_code='wxpay';
		$payment_code = Buddha_Http_Input::getParameter('payment_code');
		if($payment_code==''){
			$payment_code='wxpay';
			 $payname='微信支付';
		}

		$referral_id =0;
		$agent_id =0;
		$partnerrate =0;
		$agentrate =0;
		if($order_type=='info.see' ){
			$backurl = "index.php?a=index&c={$good_table}&id={$good_id}";
			if($pc==1){
			    $pc="&pc={$pc}";
            }
			if($final_amt<0.2){
				$final_amt=0.2;
			}
		}
        if($order_type=='info.see2' ){
            $backurl =  "index.php?a=info&c={$good_table}&id={$good_id}";
            if($pc==1){
                $pc="&pc={$pc}";
            }
            if($final_amt<0.2){
                $final_amt=0.2;
            }
        }
        if($order_type=='info.top'){
            $backurl =  "index.php?a=index&c={$good_table}&id={$good_id}";
            if($pc==1){
                $pc="&pc={$pc}";
            }
            if($final_amt<0.2){
                $final_amt=0.2;
            }
        }

        if($order_type=='info.maket'){
            $backurl =  "index.php?a=index&c={$good_table}&id={$good_id}";
            if($pc==1){
                $pc="&pc={$pc}";
            }
            if($final_amt<990){
                $final_amt=990;
            }
        }

        /////////debug$final_amt =0.01;
		//店铺



        if(!empty($good_table)){
            $ShopObj = new Shop();/*$ShopObj*/
            $title='';
            if ($good_table == 'shop') {
                $ShopObj = new Shop();
                $shopid = $ShopObj->getSingleFiledValues(array('id'), "isdel=0 and id='{$good_id}' ");
                $title = '店铺';
            }

            if ($good_table == 'supply') {
                $SupplyObj = new Supply();
                $shopid = $SupplyObj->getSingleFiledValues(array('shop_id'), "isdel=0 and id='{$good_id}' ");
                $title = '供应';
            }
            if ($good_table == 'activity') {
                $ActivityObj = new Activity();
                $shopid = $ActivityObj->getSingleFiledValues(array('shop_id'), "isdel=0 and id='{$good_id}' ");
                $title = '活动';
            }

            if ($good_table == 'lease') {
                $LeaseObj = new Lease();
                $shopid = $LeaseObj->getSingleFiledValues(array('shop_id'), "isdel=0 and id='{$good_id}'");
                $title = '租赁';
            }

            if ($good_table == 'recruit') {
                $RecruitObj = new Recruit();
                $shopid = $RecruitObj->getSingleFiledValues(array('shop_id'), "isdel=0 and id='{$good_id}'");
                $title = '招聘';
            }

            if ($good_table == 'demand') {
                $DemandObj = new Demand();
                $shopid = $DemandObj->getSingleFiledValues(array('shop_id'), "isdel=0 and id='{$good_id}'");
                $title = '需求';
            }

            if ($good_table == 'singleinformation') {
                $SingleinformationObj = new Singleinformation();
                $shopid = $SingleinformationObj->getSingleFiledValues(array('shop_id'), "isdel=0 and id='{$good_id}'");
                $title = '单页';
            }
            if ($good_table == 'heartpro') {
                $HeartproObj = new Heartpro();
                $shopid = $HeartproObj->getSingleFiledValues(array('shop_id'), "isdel=0 and id='{$good_id}'");
                $title = '1分营销';
            }

            if (!$shopid) {
                $jsondata = array();
                $jsondata['backurl'] = $backurl;
                $jsondata['url'] = $backurl;
                $jsondata['errcode'] = 1;
                $jsondata['errmsg'] = "无此{$title}信息";
                Buddha_Http_Output::makeJson($jsondata);
            }

            $Db_Shop = $ShopObj->getSingleFiledValues(array('referral_id','agent_id','partnerrate','agentrate'), "isdel=0 and id='{$shopid['shop_id']}' ");
            $referral_id = (int)$Db_Shop['referral_id'];
            $agent_id = (int)$Db_Shop['agent_id'];
            $partnerrate = (int)$Db_Shop['partnerrate'];
            $agentrate = (int)$Db_Shop['agentrate'];
            if ($good_table == 'shop') {
                $getMoneyArray = $ShopObj->getMoneyArrayFromShop($shopid['id'], $final_amt);//该函数用于查询店铺
            }else{
                $getMoneyArray = $ShopObj->getMoneyArrayFromShop($shopid['shop_id'], $final_amt);//该函数用于查询店铺
            }



        }

        //提现
        if ($order_type == 'money.out') {
            $payment_code='';
            $payname='';
            $getMoneyArray['final_amt']=$final_amt;
            $getMoneyArray['goods_amt']=$final_amt;
            $getMoneyArray['final_amt']=0;
            $getMoneyArray['money_plat']=0;;
            $getMoneyArray['money_agent']=0;
            $getMoneyArray['money_partner']=0;


            if($good_id=='' or $good_id!=$user_id ){
                $jsondata = array();
                $jsondata['backurl'] = $backurl;
                $jsondata['url'] = $backurl;
                $jsondata['errcode'] = 1;
                $jsondata['errmsg'] = "提现出错";
                Buddha_Http_Output::makeJson($jsondata);
            }
            $Db_User = $UserObj->getSingleFiledValues(array('banlance'), "isdel=0 and id='{$good_id}'");
            $banlance=$Db_User['banlance'];
            if($final_amt>$banlance){
                $jsondata = array();
                $jsondata['backurl'] = $backurl;
                $jsondata['url'] = $backurl;
                $jsondata['errcode'] = 1;
                $jsondata['errmsg'] = "提现金额出错";
                Buddha_Http_Output::makeJson($jsondata);
            }

            if($final_amt==0){
                $final_amt=$banlance;
            }

            if($final_amt==0){
                $jsondata = array();
                $jsondata['backurl'] = $backurl;
                $jsondata['url'] = $backurl;
                $jsondata['errcode'] = 1;
                $jsondata['errmsg'] = "提现金额出错";
                Buddha_Http_Output::makeJson($jsondata);
            }
            $getMoneyArray['final_amt'] = $final_amt;
            $getMoneyArray['goods_amt']=$final_amt;
            $getMoneyArray['money_plat']=0;
            $getMoneyArray['money_agent']=0;
            $getMoneyArray['money_partner']=0;
            //给会员余额进行调整
            $prefix = $this->prefix;
            $this->db->query("UPDATE {$prefix}user SET  banlance=banlance-{$final_amt},Withamount=Withamount+{$final_amt} WHERE id='{$user_id}' ");


        }
        $data = array();
		$data['good_id'] = $good_id;
		$data['user_id'] = $user_id;
		$data['order_sn'] = $OrderObj->birthOrderId($user_id);
		$data['good_table'] = $good_table;
		$data['referral_id'] =$referral_id;
		$data['partnerrate'] =$partnerrate;
		$data['agent_id'] = $agent_id;
		$data['agentrate'] = $agentrate;
		$data['pay_type'] = 'third';
		$data['order_type'] =$order_type;
		$data['goods_amt'] = $getMoneyArray['goods_amt'];
		$data['final_amt'] = $getMoneyArray['final_amt'];
		$data['money_plat'] = $getMoneyArray['money_plat'];
		$data['money_agent'] = $getMoneyArray['money_agent'];
		$data['money_partner'] = $getMoneyArray['money_partner'];
		$data['payname'] = $payname;
		$data['make_level0'] = 1;
		$data['make_level1'] = $level[0];
		$data['make_level2'] = $level[1];
		$data['make_level3'] = $level[2];
		$data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
		$data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
	    $order_id=$OrderObj->add($data);

		$backurl = urlencode($backurl);
		$url="/topay/{$payment_code}/{$payment_code}to.php?order_id={$order_id}&pc={$pc}&backurl={$backurl}";
		$jsondata = array();
		$jsondata['backurl'] = $backurl;
		$jsondata['url'] = $url;
		$jsondata['errcode'] = 0;
		$jsondata['errmsg'] = "{$order_id}";

		Buddha_Http_Output::makeJson($jsondata);



	}


	
}