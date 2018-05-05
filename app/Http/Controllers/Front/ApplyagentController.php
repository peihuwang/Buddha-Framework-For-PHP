<?php
/**
 * Class 代理商申请
 */
class ApplyAgentController extends Buddha_App_Action{
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function add(){
    	$uid = (int)Buddha_Http_Cookie::getCookie('uid');
    	$RegionObj = new Region();
    	$OrderObj = new Order();
    	$ApplyagentObj = new Applyagent();
    	$order_id = Buddha_Http_Input::getParameter('order_id');//订单号
        if($order_id){//改变状态
            $OrderObj=new Order();
            //$BillObj=new Bill();
            $OrderObj->batchOrderShareProfit($order_id);
            $orderInfo = $OrderObj->getSingleFiledValues(array('good_id'),"id={$order_id} and good_table='applyagent' and pay_status=1");
            if($orderInfo){//改变代理商申请表的支付状态
                $ispay['ispay'] = 1;
                $ApplyagentObj->updateRecords($ispay,"id={$orderInfo['good_id']}");
            }
        }
    	
    	$province = $RegionObj->getFiledValues(array('id','name'),"level=1");

    	if($_POST){
    		$party_b=Buddha_Http_Input::getParameter('Party_b');//公司名称
    		$pross=Buddha_Http_Input::getParameter('pross');//省
    		$citys=Buddha_Http_Input::getParameter('citys');//市
    		$area=Buddha_Http_Input::getParameter('area');//区县
    		$signature=Buddha_Http_Input::getParameter('signature');//详细地址
    		$id_card=Buddha_Http_Input::getParameter('id_card');//身份证号
    		$mobile=Buddha_Http_Input::getParameter('mobile');//手机号
    		$address=Buddha_Http_Input::getParameter('address');//详细地址
    		$email=Buddha_Http_Input::getParameter('email');//邮箱
    		$referees=Buddha_Http_Input::getParameter('referees');//推荐人
    		$dates=Buddha_Http_Input::getParameter('dates');//电子合同填写日期
            $notes=Buddha_Http_Input::getParameter('notes');//备注
            //判断代理区域是否空白
            if($citys && !$area){//市代
                $cityNum = $ApplyagentObj->countRecords("level2={$citys} and level3='' and isok=1 and ispay=1");
                if($cityNum){
                    $data['isok']='false';
                    $data['data']='对不起，您所选市已有代理';
                    Buddha_Http_Output::makeJson($data);
                    exit;
                }
            }
            if($area){//区县代理
                $areaNum = $ApplyagentObj->countRecords("level3={$area} and isok=1 and ispay=1");
                if($areaNum){
                    $data['isok']='false';
                    $data['data']='对不起，所选区域已有代理';
                    Buddha_Http_Output::makeJson($data);
                    exit;
                }
            }
            ////////////////////
    		$datase = array();
    		$datase['party_b'] = $party_b;
    		$datase['level1'] = $pross;
    		$datase['level2'] = $citys;
    		$datase['level3'] = $area;
    		$datase['signature'] = $signature;
    		$datase['id_card'] = $id_card;
    		$datase['mobile'] = $mobile;
    		$datase['address'] = $address;
    		$datase['email'] = $email;
    		$datase['referees'] = $referees;
    		$datase['dates'] = $dates;
            $datase['notes'] = $notes;
    		$datase['createtime'] = time();
    		$datase['isok'] = 0;
    		$insert_id = $ApplyagentObj->add($datase);
    		if($insert_id){
    			$datas=array();
		        $datas['good_id']=$insert_id;//指定产品id
		        $datas['user_id']=$uid;
		        $datas['order_sn']= $OrderObj->birthOrderId($uid);//订单编号
		        $datas['good_table']='applyagent';//哪个表
		        $datas['pay_type']='third';//third第三方支付，point积分，balance余额
		        $datas['order_type']='applyagent';//money.out提现, 店铺认证shop.v,信息置顶info.top 
		        $datas['goods_amt']=3000.00;//产品价格
		        $datas['final_amt']=3000.00;//产品最终价格	        
		        $datas['payname']='微信支付';
		        $datas['make_level0']=$shopinfo['level0'];//国家
		        $datas['make_level1']=$shopinfo['level1'];//省
		        $datas['make_level2']=$shopinfo['level2'];//市
		        $datas['make_level3']=$shopinfo['level3'];//区县
		        $datas['make_level4']=$shopinfo['level4'];//乡镇
		        $datas['make_level5']=$shopinfo['level5'];
		        $datas['createtime']=time();//时间戳
		        $datas['createtimestr']=date('Y-m-d H:i:s');//时间日期
		        $order_id=$OrderObj->add($datas);
		        $urls = substr($_SERVER["REQUEST_URI"],1,stripos($_SERVER["REQUEST_URI"],'?'));
        		$backurl = urlencode($urls.'a=add&c=applyagent');
		        if($OrderObj){
		            $data=array();
                    $data['isok']='true';
                    $data['data']='申请成功,去支付';
                    $data['url']='/topay/wxpay/wxpayto.php?order_id='.$order_id.'&backurl='.$backurl;
		        }else{
		             $data['isok']='false';
		             $data['data']='服务器忙';
		        }
		        Buddha_Http_Output::makeJson($data);
    		}
    	}
    	
    	$this->smarty->assign('province',$province);
    	$TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty->display($TPL_URL.'.html');
    }

    public function city(){
    	list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=Buddha_Http_Input::getParameter('id');
        $RegionObj = new Region(); 
    	$Db_arear= $RegionObj->getChildlist($id);
        $datas = array();
        if($Db_arear){
            $datas['isok']='true';
            $datas['datas']=$Db_arear;
        }else{
            $datas['isok']='false';
            $datas['datas']='';
        }
        Buddha_Http_Output::makeJson($datas);
    }

    public function area(){
    	list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=Buddha_Http_Input::getParameter('id');
        $RegionObj = new Region();
    	$Db_arear= $RegionObj->getChildlist($id);
        $datas = array();
        if($Db_arear){
            $datas['isok']='true';
            $datas['datas']=$Db_arear;
        }else{
            $datas['isok']='false';
            $datas['datas']='';
        }
        Buddha_Http_Output::makeJson($datas);
    }


}