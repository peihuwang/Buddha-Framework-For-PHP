<?php

/**
 * Class BillController
 */
class BillController extends Buddha_App_Action{

    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $act=Buddha_Http_Input::getParameter('act');

        if($act=='list'){
            $where = " isdel=0 and user_id='$uid' ";

        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];

        $orderby = " order by createtime DESC ";
        $list = $this->db->getFiledValues ( '*',  $this->prefix.'bill', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

            if($list){
            $data= array(
                'list'=>$list,
                'isok'=>'true',
                'data'=>'没有了',
            );
            }else{
                $data= array(
                    'list'=>$list,
                    'isok'=>'false',
                    'data'=>'没有数据',
                );
            }
            Buddha_Http_Output::makeJson($data);
        }
        
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }

    public function getout (){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $get_id = (int)Buddha_Http_Input::getParameter('mid')?(int)Buddha_Http_Input::getParameter('mid') : 0;
        $MoneytouserObj = new Moneytouser();
        $times = strtotime(date('Y-m-d'));
        $moneytouser = $MoneytouserObj->getSingleFiledValues('',"user_id='{$uid}' AND createtime='{$times}' AND pay_status=8");
        if($get_id>0){
            if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
                $UserObj=new User();
                $uid= Buddha_Http_Cookie::getCookie('uid');
                $DB_User = $UserObj->getSingleFiledValues(array('banlance')," id='{$uid}' and state=1");
                $totle_money=$DB_User['banlance'];
                $difference=$totle_money-$get_id;
                if($difference>=20){
                    $state=array('state'=>'提现申请成功！');
                    $datas['isok']='true';
                    $datas['data']=$state;
                }else if($difference < 20){
                    $state=array('state'=>'提现后余额不足,请重新输入！');
                    $datas['isok']='false';
                    $datas['data']=$state;
                }
                
            }else{
                $state=array('state'=>'请在微信公众号里进行此操作，给您带来不便深表歉意！');
                $datas['isok']='false';
                $datas['data']=$state;
            }
            Buddha_Http_Output::makeJson($datas);
        }
        $this->smarty->assign('moneytouser',$moneytouser);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
    public function getoutss(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $OrderObj = new Order();
        $UserObj = new User();
        $MoneytouserObj = new Moneytouser();
        $times = strtotime(date('Y-m-d'));
        $url = $_SERVER['PHP_SELF'];
        $moneytouser = $MoneytouserObj->getSingleFiledValues('',"user_id='{$uid}' AND createtime='{$times}' AND pay_status=8");
        $act=Buddha_Http_Input::getParameter('act');
        $extract_monmey=Buddha_Http_Input::getParameter('extract_monmey');
        $upmoney = 200;
        if($act == 'qy'){
            if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
                $totle_money=$UserInfo['banlance'];
                $difference=$totle_money-$extract_monmey;
                if($difference>=20){         
                    $poundage = $extract_monmey * 0.006;
                    $final_amount = $extract_monmey - $poundage;
                    //添加
                    $data = array();
                    $data['user_id'] = $uid;
                    $data['openid'] = $openId;
                    $data['total_amount'] = $extract_monmey;//总金额
                    $data['final_amount'] = $final_amount;//应付金额
                    $data['poundage'] = $poundage;//手续费
                    $data['createtime']=strtotime(date('Y-m-d'));
                    $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
                    $data['pay_status'] = 1;
                    $mid = $MoneytouserObj->add($data);
                    $banlance['banlance'] = $totle_money - $extract_monmey;
                    $UserObj->edit($banlance,$UserInfo['id']);
                    if($mid){
                        if($extract_monmey >= $upmoney){
                            //生成订单
                            $data = array();
                            $data['user_id'] = $uid;
                            $data['order_sn'] = $OrderObj->birthOrderId($uid);
                            $data['good_table'] = 'user';
                            $data['pay_type'] = 'third';
                            $data['pay_status'] = 0;
                            $data['order_type'] = 'money.out';
                            $data['goods_amt'] = $extract_monmey;
                            $data['final_amt'] = $extract_monmey;
                            $data['payname'] = '微信支付';
                            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                            $order_id=$OrderObj->add($data);
                            $data = array();
                            $data['isok'] = 1;
                            $data['info'] = "提现金额超过限制金额，需后台审核，我们会在两天之内确认您的操作，请注意查收您的微信零钱";
                            $data['url'] = $url . "?a=index&c=bill";
                        }else{
                            $data = array();
                            $data['isok'] = 1;
                            $data['info'] = "正在进行企业付款";
                            $data['url'] = "/topay/wxpay/getopenid.php?money=" . $extract_monmey . '&mid=' . $mid.'&final_amount='.$final_amount;
                        }
                        
                    }else{
                        $data = array();
                        $data['isok'] = 0;
                        $data['url'] = '';
                    }
                }
            }else{
                $data['isok']=2;
                $data['info']='请在微信公众号里进行此操作，给您带来不便深表歉意！';
            }
            Buddha_Http_Output::makeJson($data);
        }



    }

}