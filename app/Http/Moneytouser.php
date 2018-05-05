<?php

include_once PATH_ROOT . "topay/wxpay/demo/WxPay.JsApiPay.php";
include_once PATH_ROOT . "topay/wxpay/demo/log.php";
include_once PATH_ROOT . "topay/wxpay/lib/WxPay.Api.php";
include_once PATH_ROOT . "topay/wxpay/lib/WxPay.Config.php";
class Moneytouser extends  Buddha_App_Model{ //微信企业 支付到个人
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }
    //动态获取用户的openid
    public function getOpenid(){

        $tools = new JsApiPay();
        $openId = $tools->GetOpenid();
        if($openId){
        	return $openId;
        }else{
        	return 0;
        }

    }

    public function enterprisePayment($desc,$money){
    	list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if ($pay_callback_arr && $pay_callback_arr['result_code'] != 'FAIL' && 'success' == strtolower($pay_callback_arr['return_code'])){
			//企业付款更新状态
			$admin_name = '';//操作人
			$admin_info = HSKDecode(gCookie('hsk_auth'), $hsk_sitehash);
			if ($admin_info){
				$admin_arr = explode("\t",$admin_info);
				if ($admin_arr && 0 < (int)$admin_arr[0]){
					$admin_arr2 = $DB->getSingleFiledValues(array('username','nickname'), 'member', "id={$admin_arr[0]}");
					if ($admin_arr2){
						$admin_name = $admin_arr2['username']."({$admin_arr2['nickname']})";
					}
				}
				unset($admin_info,$admin_arr,$admin_arr2);
			}						
			$data = array('status'=>1,'pay_ip'=>$ip,'pay_user'=>$admin_name,'paytime'=>time());			
			$DB->updateRecords($data,'cash', 'trade_type=1 and id='.$user_pay_info['id']);
			
			//用户日志记录
			$data = array();
			$data['uid'] = $user_pay_info['uid'];
			$data['num'] = $user_pay_info['amount_ed'];
			$data['uname'] = $user_pay_info['uname'];
			$data['types'] = 5;//提现(通知)-企业付款
			$data['order_id'] = $user_pay_info['order_id'];
			$data['notice'] = '提现已到账-税后：'.$user_pay_info['amount_ed'].'元(实为'.$user_pay_info['amount'].'元)，时间：'. date('Y-m-d H:i:s',time());
			$data['createtime'] = strtotime(date('Y-m-d',time()));
			$DB->addRecords($data,'user_log');
			
			$result['status'] = '提现申请－付款已到账！';
			$result['ispay'] = 'OK';
		}
    }

}