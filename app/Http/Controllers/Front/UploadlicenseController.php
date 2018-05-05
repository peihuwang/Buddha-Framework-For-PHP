<?php
/**
 * Class UploadlicenseController
 */
class UploadlicenseController extends Buddha_App_Action{
    public function __construct() {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }

    public function add(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopdatumObj = new Shopdatum();
        $UserObj = new User();
        $OrderObj = new Order();
        $ShopObj = new Shop();
        $urls = substr($_SERVER["REQUEST_URI"],1,stripos($_SERVER["REQUEST_URI"],'?'));
        $backurl = urlencode('business/'.$urls.'a=index&c=business');

        $username = Buddha_Http_Input::getParameter('username');
        $number = Buddha_Http_Input::getParameter('number');
        $img = Buddha_Http_Input::getParameter('img');
        $imgs = Buddha_Http_Input::getParameter('imgs');
        $canvasImg = Buddha_Http_Input::getParameter('canvasImg');
        $Db_referral = $UserObj->getSingleFiledValues(array('level1,level2','level3'),"id='{$uid}'");
        $data['user_id'] = $uid;
        $data['user_name'] = $username;
        $data['number'] = $number;
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $shopdatum_id = $ShopdatumObj->add($data);
        if($shopdatum_id){
            if($img){
                $img = $ShopdatumObj->base64_upload_img($img,$shopdatum_id);
                $datas['idpos_img'] = '/' . $img;
            }
            if($imgs){
                $imgs = $ShopdatumObj->base64_upload_img($imgs,$shopdatum_id);
                $datas['idback_img'] = '/' . $imgs;
            }
            if($canvasImg){
                $canvasImg = $ShopdatumObj->base64_upload_img($canvasImg,$shopdatum_id);
                $datas['license_img'] = '/' . $canvasImg;
            }
            $ShopdatumObj->updateRecords($datas,"id='{$shopdatum_id}'");
            $data = array();
            $UserfeeObj = new Userfee();
            $data['user_id'] = $uid;
            $data['shopdatum_id'] = $shopdatum_id;
            $data['fee_type'] = 1;
            $data['money'] = 360;
            $data['isdel'] = 0;
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['starttime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['starttimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $data['endtime'] = mktime(0, 0, 0, date('m'), date('d'), date('Y')+1);
            $data['endtimestr'] = date("Y-m-d H:i:s", strtotime("+1 year"));
            $userfee_id = $UserfeeObj->add($data);
            if($userfee_id){
                $data=array();
                $data['good_id'] = $userfee_id;
                $data['user_id'] = $uid;
                $data['order_sn'] = $OrderObj->birthOrderId($uid);
                $data['good_table'] ='userfee';
                $data['pay_type']= 'third';
                $data['order_type'] = 'e_netcom';
                $data['goods_amt'] = 360;
                $data['final_amt'] = 360;
                $data['payname'] = '微信支付';
                $data['make_level0'] = $Db_referral['level0'];
                $data['make_level1'] = $Db_referral['level1'];
                $data['make_level2'] = $Db_referral['level2'];
                $data['make_level3'] = $Db_referral['level3'];
                $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                $order_id = $OrderObj->add($data);
                if($order_id){
                    $datass['isok'] = 1;
                    $datass['info'] = '操作成功,即将跳转到支付页面。';
                    $datass['url'] = '/topay/wxpay/wxpayto.php?order_id='.$order_id."&backurl=".$backurl;
                }else{
                    $datass['isok'] = 0;
                    $datass['info'] = '服务器忙';
                }
                Buddha_Http_Output::makeJson($datass);
            }
            
        }
        

    }

    function accountUpgrade(){//账户升级
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ids = Buddha_Http_Input::getParameter('ids');
        $ShopdatumObj = new Shopdatum();
        $UserObj = new User();
        $OrderObj = new Order();
        $ShopObj = new Shop();
        $urls = substr($_SERVER["REQUEST_URI"],1,stripos($_SERVER["REQUEST_URI"],'?'));
        $backurl = urlencode('business/'.$urls.'a=index&c=business');
        $Db_referral = $UserObj->getSingleFiledValues(array('level1,level2','level3'),"id='{$uid}'");
        $data = array();
        $UserfeeObj = new Userfee();
        $data['user_id'] = $uid;
        $data['shopdatum_id'] = $shopdatum_id;
        if($ids == 1){
            $data['fee_type'] = 1;
            $data['money'] = 360;
        }elseif($ids == 2){
            $data['fee_type'] = 2;
            $data['money'] = 990;
        }
        $data['is_sure'] = 1;
        $data['isdel'] = 0;
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['starttime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['starttimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $data['endtime'] = mktime(0, 0, 0, date('m'), date('d'), date('Y')+1);
        $data['endtimestr'] = date("Y-m-d H:i:s", strtotime("+1 year"));
        $userfee_id = $UserfeeObj->add($data);
        if($userfee_id){
            $data=array();
            $data['good_id'] = $userfee_id;
            $data['user_id'] = $uid;
            $data['order_sn'] = $OrderObj->birthOrderId($uid);
            $data['good_table'] ='userfee';
            $data['pay_type']= 'third';
            $data['order_type'] = 'e_netcom';
            if($ids ==1){
                $data['goods_amt'] = 360;
                $data['final_amt'] = 360;
            }elseif($ids ==2){
                $data['goods_amt'] = 990;
                $data['final_amt'] = 990;
            }
            $data['payname'] = '微信支付';
            $data['make_level0'] = $Db_referral['level0'];
            $data['make_level1'] = $Db_referral['level1'];
            $data['make_level2'] = $Db_referral['level2'];
            $data['make_level3'] = $Db_referral['level3'];
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $order_id = $OrderObj->add($data);
            if($order_id){
                $datass['isok'] = 1;
                $datass['info'] = '操作成功,即将跳转到支付页面。';
                $datass['url'] = '/topay/wxpay/wxpayto.php?order_id='.$order_id."&backurl=".$backurl;
            }else{
                $datass['isok'] = 0;
                $datass['info'] = '服务器忙';
            }
            Buddha_Http_Output::makeJson($datass);
        }
    }





}