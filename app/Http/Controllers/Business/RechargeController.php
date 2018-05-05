<?php

/**充值
 * Class RechargeController
 */
class RechargeController extends Buddha_App_Action{

    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }
    public function index(){
    	list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if(!$uid){
           	Buddha_Http_Head::redirectofmobile('请登录','../index.php?a=login&c=account');
        }
        $RechargeObj = new Recharge();
        $order_id=Buddha_Http_Input::getParameter('order_id');//订单id
        if($order_id){//支付成功后增加余额等相应操作
            $money=Buddha_Http_Input::getParameter('money');//充值金额
            $forwarding_money=Buddha_Http_Input::getParameter('forwarding_money');//转发朋友圈或QQ空间单次赏金
            //$hao_forwarding_money=Buddha_Http_Input::getParameter('hao_forwarding_money');//转发好友或好友群单次赏金
    		$num = $RechargeObj->countRecords("uid={$uid}");//是否有充值记录
	    	if($num){//有充值记录直接进行编辑
	    		$Rechargeinfo = $RechargeObj->getSingleFiledValues('',"uid={$uid}");
	    		$data['total_amount'] = $Rechargeinfo['total_amount'] + $money;//总金额增加
	    		$data['balance'] = $Rechargeinfo['balance'] + $money;//余额增加
	    		$data['money'] = $money;//本次充值金额
	    		//$data['forwarding_money'] = $forwarding_money;//转发朋友圈或QQ空间单次赏金
                //$data['hao_forwarding_money'] = $hao_forwarding_money;//转发好友或好友群单次赏金
	    		$data['createtime'] = time();
	    		$re = $RechargeObj->updateRecords($data,"uid={$uid}");
	    	}else{//没有充值记录进行添加
	    		$data['uid'] = $uid;
	    		$data['total_amount'] = $money;//总金额增加
	    		$data['balance'] = $money;//余额增加
	    		$data['money'] = $money;//本次充值金额
	    		//$data['forwarding_money'] = $forwarding_money;//转发朋友圈或QQ空间单次赏金
                //$data['hao_forwarding_money'] = $hao_forwarding_money;//转发好友或好友群单次赏金
	    		$data['createtime'] = time();
	    		$re = $RechargeObj->add($data);
	    	}
            header("Location:http://{$_SERVER['HTTP_HOST']}/business/index.php?a=index&c=recharge"); 
    	}
        $rechargeinfo = $RechargeObj->getSingleFiledValues('',"uid={$uid}");
        if(!$rechargeinfo){
        	$rechargeinfo = array();
        	$rechargeinfo['balance'] = '0.00';
        }
        $this->smarty->assign('rechargeinfo',$rechargeinfo);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function addrecharge(){//充值
    	list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
    	$RechargeObj = new Recharge();
    	$OrderObj = new Order();

    	$urls = substr($_SERVER["REQUEST_URI"],1,stripos($_SERVER["REQUEST_URI"],'?'));
    	$money=Buddha_Http_Input::getParameter('money');//充值金额
    	//$forwarding_money=Buddha_Http_Input::getParameter('forwarding_money');//转发朋友圈或QQ空间单次赏金
        //$hao_forwarding_money=Buddha_Http_Input::getParameter('hao_forwarding_money');//转发好友或好友群单次赏金
    	$backurl = urlencode($urls.'a=index&c=recharge&money='.$money);
    	//生成订单
		$data = array();
        $data['user_id'] = $uid;
        $data['order_sn'] = $OrderObj->birthOrderId($uid);
        $data['good_table'] = 'recharge';
        $data['referral_id'] =0;
        $data['partnerrate'] =0;
        $data['pay_type'] = 'third';
        $data['order_type'] = 'recharge.cz';//充值
        $data['goods_amt'] = $money;
        $data['final_amt'] = $money;
        $data['payname'] = '微信支付';
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $order_id=$OrderObj->add($data);
        $datas=array();
        if($order_id){
	        $datas['isok']='true';
	        $datas['info']='操作成功，即将跳转到支付页面';
	        $datas['url']='/topay/wxpay/wxpayto.php?order_id='.$order_id.'&backurl='.$backurl;
        }else{
        	$datas['isok']='false';
            $datas['info']='店铺添加失败';
        }
        Buddha_Http_Output::makeJson($datas);
    }
    public function set_money(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $RechargeObj = new Recharge();
        $ShopObj = new Shop();
        $rechageinfo = $RechargeObj->getSingleFiledValues('',"uid={$uid}");
        $shop_name = $ShopObj->getSingleFiledValues(array('name'),"id='{$rechageinfo['shop_id']}'");
        $rechageinfo['shop_name'] = $shop_name['name'];
        $getshoplistOption=$ShopObj->getShoplistOption($uid,0);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        if(Buddha_Http_Input::isPost()){
            $shop_ids=Buddha_Http_Input::getParameter('shop_id');//转发赏金
            $forwarding_money=Buddha_Http_Input::getParameter('forwarding_money');//转发赏金
            $time_period=Buddha_Http_Input::getParameter('time_period');//转发时间段
            $is_open=Buddha_Http_Input::getParameter('is_open');//是否开启转发有赏，1开启，2关闭
            $data = array();
            $data['shop_id'] = $shop_ids;
            $data['forwarding_money'] = $forwarding_money;//转发赏金
            $data['time_period'] = $time_period;
            $data['is_open'] = $is_open;
            if($rechageinfo){//记录存在编辑
                $RechargeObj->edit($data,$rechageinfo['id']);
                $data = array();
                $data['isok'] = 'true';
                $data['info'] = '设置成功';
            }else{//记录不存在添加
                $re = $RechargeObj->add($data);
                $data = array();
                if($re){
                    $data['isok'] = 'true';
                    $data['info'] = '设置成功';
                }else{
                    $data['isok'] = 'false';
                    $data['info'] = '服务器忙';
                }
            }
            
            
            Buddha_Http_Output::makeJson($data);
        }
        $this->smarty->assign('rechageinfo',$rechageinfo);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

}