<?php

/**
 * Class MenuplusController
 */
class MenuplusController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

        //权限
        $webface_access_token = Buddha_Http_Input::getParameter('webface_access_token');
        if ($webface_access_token == '') {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444002, 'webface_access_token必填');
        }

        $ApptokenObj = new Apptoken();
        $num = $ApptokenObj->getTokenNum($webface_access_token);
        if ($num == 0) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444003, 'webface_access_token不正确请从新获取');
        }


    }

    /**
     *  提现操作 生成订单
     * @author wph 2017-09-12
     */
    public function cashwithdrawaltodo(){

        if (Buddha_Http_Input::checkParameter(array('usertoken','final_amt'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }
        $UserObj = new  User();
        $BillObj = new Bill();
        $BankObj = new Bank();
        $OrderObj = new Order();

        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $final_amt = Buddha_Http_Input::getParameter('final_amt');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','banlance',
            'username'
        );
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");

        $user_id = $Db_User['id'];
        $Db_groupid = $Db_User['groupid'];
        $Db_to_group_id = $Db_User['to_group_id'];
        $banlance = $Db_User['banlance'];


        $lightarr = $UserObj->getRankByGroupId(0,$Db_groupid,$Db_to_group_id,$Db_User);

        $Db_Bank = $BankObj->getSingleFiledValues('',"uid={$user_id}");//获取银行卡信息

        $is_couldwithdrawal = $BillObj->isCouldWithdrawals($banlance,$lightarr);
        $is_needbindbank = 0;
        $bankarr = array();
        if(!Buddha_Atom_Array::isValidArray($Db_Bank)){
            $is_couldwithdrawal = 0;
            $is_needbindbank = 1;
        }else{
            $Db_Bank['bank_id'] = $Db_Bank['id'];
            unset($Db_Bank['id']);
            $bankarr = $Db_Bank;

        }

        $out_tip = $BillObj->getBillConfigArr('out_tip',$lightarr);
        if($is_needbindbank){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000016, '需要绑定银行再进行提现操作');
        }

        if($is_couldwithdrawal==0){
            /* 提现金额满4800元方能提现！*/
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000017, $out_tip);

        }


        if($final_amt>$banlance){
            /* 提现金额出错！*/
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000018, '提现金额出错');
        }

        if($final_amt==0){
            $final_amt=$banlance;
        }

        if($final_amt==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000018, '提现金额出错');
        }
        $level = array('level1'=>0,'level2'=>0,'level3'=>0);
        $getMoneyArray['final_amt'] = $final_amt;
        $getMoneyArray['goods_amt']=$final_amt;
        $getMoneyArray['money_plat']=0;
        $getMoneyArray['money_agent']=0;
        $getMoneyArray['money_partner']=0;
        //给会员余额进行调整
        $prefix = $this->prefix;
        $this->db->query("UPDATE {$prefix}user SET  banlance=banlance-{$final_amt},Withamount=Withamount+{$final_amt} WHERE id='{$user_id}' ");
        $referral_id =0;
        $agent_id =0;
        $partnerrate =0;
        $agentrate =0;
        $good_id = $user_id;
        $good_table = 'user';
        $order_type = 'money.out';
        $payment_code='';
        $payname='';
        $order_sn = $OrderObj->birthOrderId($user_id);
        $getMoneyArray['final_amt']=$final_amt;
        $getMoneyArray['goods_amt']=$final_amt;
        $getMoneyArray['final_amt']=0;
        $getMoneyArray['money_plat']=0;;
        $getMoneyArray['money_agent']=0;
        $getMoneyArray['money_partner']=0;
        $data = array();
        $data['good_id'] = $good_id;
        $data['user_id'] = $user_id;
        $data['order_sn'] = $order_sn;
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

        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['order_id'] = $order_id;
        $jsondata['order_sn'] = $order_sn;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '提现成功等待管理员操作');


    }
    /**提现展示页面
     * @author wph 2017-09-12
     */
    public function cashwithdrawalshow(){
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '必填信息没填写');
        }

        $UserObj = new  User();
        $BillObj = new Bill();
        $BankObj = new Bank();


        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','banlance',
            'username'
        );
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");

        $user_id = $Db_User['id'];
        $Db_groupid = $Db_User['groupid'];
        $Db_to_group_id = $Db_User['to_group_id'];
        $banlance = $Db_User['banlance'];

        $billogo = $host.'style/images/bill.png';
        $lightarr = $UserObj->getRankByGroupId(0,$Db_groupid,$Db_to_group_id,$Db_User);

        $Db_Bank = $BankObj->getFiledValues('',"uid={$user_id}");//获取银行卡信息

        $is_couldwithdrawal = $BillObj->isCouldWithdrawals($banlance,$lightarr);
        $is_needbindbank = 0;
        $bankarr = array();
        if(!Buddha_Atom_Array::isValidArray($Db_Bank)){
            $is_couldwithdrawal = 0;
            $is_needbindbank = 1;
        }else{
            $Db_Bank[0]['bank_id'] = $Db_Bank[0]['id'];
            unset($Db_Bank['id']);
            $bankarr = $Db_Bank;

        }

        $jsondata = array();

        $jsondata['user_id'] = $Db_User['id'];
        $jsondata['usertoken'] = $Db_User['usertoken'];
        $jsondata['banlance'] = $Db_User['banlance'];
        $jsondata['banlanceformat'] = '￥'.$Db_User['banlance'];
        $jsondata['billogo'] = $billogo;
        $jsondata['out_tip'] = $BillObj->getBillConfigArr('out_tip',$lightarr);
        $jsondata['is_couldwithdrawal'] = $is_couldwithdrawal;
        $jsondata['is_needbindbank'] = $is_needbindbank;
        $jsondata['bankarr'] = $bankarr;
        $jsondata['button_name'] = '申请提现';
        $jsondata['text_1'] = '可提现的余额';
        $jsondata['text_2'] = '温馨提示：';
        $jsondata['text_3'] = '提现金额：';
        $jsondata['text_4'] = '请输入提现金额！';

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '提现');





    }




}