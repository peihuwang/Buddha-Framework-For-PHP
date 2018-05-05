<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/6
 * Time: 21:17
 */
class PaymentController extends Buddha_App_Action
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
     * 支付方式
     */
    public function choice()
    {
        $host = Buddha::$buddha_array['host'];
        $PaymentObj = new Payment();

        $Db_payment = $PaymentObj->getFiledValues(array('payment_id','payment_code','payment_name'),"ifopen=1 ");
        foreach($Db_payment as $k => $v){
            if($v['payment_code'] == 'wxpay'){
                $Db_payment[$k]['icon'] = $host . "resources/payment/images/icon_wxpay.png";
            }elseif($v['payment_code'] == 'alipay'){
                $Db_payment[$k]['icon'] = $host . "resources/payment/images/icon_alipay.png";
            }
        }

        $jsondata = $Db_payment;

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'支付类型');
    }
    /**
     * 赏金充值前页面
     */
    public function beforerecharge(){
        $host = Buddha::$buddha_array['host'];
        $UserObj = new User();
        if(Buddha_Http_Input::checkParameter(array('usertoken','b_display'))){//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $RechargeObj = new Recharge();
        $Rechargeinfo = $RechargeObj->getSingleFiledValues('',"uid='{$user_id}'");
        if($Rechargeinfo){
            $balance = $Rechargeinfo['balance'];
        }else{
            $balance = '0.00';
        }
        $jsondata = array();
        $jsondata['balance'] = $balance;
        $jsondata['icon_money'] = $host . "apiuser/menuplus/yu_e.png";
        $jsondata['icon_setting'] = $host . "apiuser/menuplus/shangjishezhi.png";
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'赏金充值前页面');
    }
    /**
     * 赏金设置前页面
     */
    public function beforsetting()
    {
        $host = Buddha::$buddha_array['host'];
        $UserObj = new User();
        if(Buddha_Http_Input::checkParameter(array('usertoken','b_display'))){//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $ShopObj = new Shop();
        $RechargeObj = new Recharge();
        if(!$ShopObj->getShopOfSureToUserTotalInt(0,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000028, '您还没用创建店铺，或者店铺还未通过审核！');
        }
        $Rechargeinfo = $RechargeObj->getSingleFiledValues('',"uid='{$user_id}'");
        if($Rechargeinfo){
            $shop_id = $Rechargeinfo['shop_id'];
        }else{
            $shop_id = 0;
        }
        $shop_id_list=$ShopObj->getUserShopArr($user_id,$shop_id);
        $RechargeObj = new Recharge();
        $Rechargeinfo = $RechargeObj->getSingleFiledValues('',"uid='{$user_id}'");
        if($Rechargeinfo){
            $balance = $Rechargeinfo['balance'];
            $forwarding_money = $Rechargeinfo['forwarding_money'];
            $is_open = $Rechargeinfo['is_open'];
        }else{
            $balance = '0.00';
            $is_open = 0;
        }
        $jsondata = array();
        $jsondata['balance'] = $balance;
        $jsondata['forwarding_money'] = $forwarding_money;
        $jsondata['balance'] = $balance;
        $jsondata['balance'] = $balance;
        $jsondata['time_period'] = $Rechargeinfo['time_period'];
        $jsondata['is_open'] = $is_open;
        $jsondata['shop_id_list'] = $shop_id_list;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '赏金设置之前的展示页面');
}

    /**
     * 赏金设置
     */
    public function bountysetting()
    {
        $UserObj = new User();
        if(Buddha_Http_Input::checkParameter(array('usertoken','b_display','shop_id','forwarding_money',
            'time_period','is_open'))){//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $forwarding_money = Buddha_Http_Input::getParameter('forwarding_money');
        $time_period = Buddha_Http_Input::getParameter('time_period');
        $is_open = Buddha_Http_Input::getParameter('is_open');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $ShopObj = new Shop();
        $RechargeObj = new Recharge();
        if($UserObj->isHasMerchantPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有商家用户权限，你还未申请商家角色');
        }
        if($ShopObj->getShopOfSureToUserTotalInt($shop_id,$user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000028, '您还没用创建店铺，或者店铺还未通过审核！');
        }
        if(!$ShopObj->isShopBelongToUser($shop_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000029, '此店铺不属于当前用户');
        }
        $Rechargeinfo = $RechargeObj->getSingleFiledValues('',"uid='{$user_id}'");
        $data = array();
        $data['uid'] = $user_id;
        $data['shop_id'] = $shop_id;
        $data['forwarding_money'] = $forwarding_money;
        $data['time_period'] = $time_period;
        $data['is_open'] = $is_open;
        if($Rechargeinfo){
            $RechargeObj->updateRecords($data,"id='{$Rechargeinfo['id']}'");
        }else{//记录不存在添加
            $data['createtime'] = time();
            $RechargeObj->add($data);
        }
        $jsondata['is_ok'] = 1;
        $jsondata['info'] = '操作成功';
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'赏金设置');

    }

    /**
     * 赏金充值
     */
    public function initiatepayment()
    {
        $host = Buddha::$buddha_array['host'];
        $UserObj = new User();
        if(Buddha_Http_Input::checkParameter(array('usertoken','money','payment_code','payname'))){//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $money= Buddha_Http_Input::getParameter('money');
        $payname= Buddha_Http_Input::getParameter('payname');
        $payment_code= Buddha_Http_Input::getParameter('payment_code');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $OrderObj=new Order();
        //生成订单
        $order_sn = $OrderObj->birthOrderId($user_id);
        $data = array();
        $data['user_id'] = $user_id;
        $data['order_sn'] =$order_sn;
        $data['good_table'] = 'recharge';
        $data['referral_id'] =0;
        $data['partnerrate'] =0;
        $data['pay_type'] = 'third';
        $data['order_type'] = 'recharge.cz';//充值
        $data['goods_amt'] = $money;
        $data['final_amt'] = $money;
        $data['payname'] = $payname;
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $order_id=$OrderObj->add($data);

        $jsondata = array();
        $jsondata['Services'] = "suibianxiedian";
        $jsondata['param'] = array();
        $jsondata['b_from'] = "order";
        $jsondata['order_sn'] = $order_sn;
        $jsondata['order_id'] = $order_id;
        if($order_id){
            $jsondata['info'] = '订单已生成';
            $jsondata['url'] = "{$host}appsdk/{$payment_code}/beforepay.php?order_id={$order_id}";
        }else{
            $jsondata['info'] = "服务器忙";
            $jsondata['url'] = '';
        }


        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'赏金充值');
    }

    /**
     * 供应（促销）信息查看费支付
     * author sys
     */
    public function checkinformationpaysupply()
    {
        $host = Buddha::$buddha_array['host'];
        $UserObj = new User();
        if(Buddha_Http_Input::checkParameter(array('usertoken','payment_code','payname','supply_id'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        //$money= Buddha_Http_Input::getParameter('money');
        $payname= Buddha_Http_Input::getParameter('payname');
        $supply_id= Buddha_Http_Input::getParameter('supply_id');
        $payment_code= Buddha_Http_Input::getParameter('payment_code');
        $OrderObj=new Order();
        //生成订单
        $order_sn = $OrderObj->birthOrderId($user_id);
        $data = array();
        $data['user_id'] = $user_id;
        $data['good_id'] = $supply_id;
        $data['order_sn'] =$order_sn;
        $data['good_table'] = "supply";
        $data['referral_id'] =0;
        $data['partnerrate'] =0;
        $data['pay_type'] = 'third';
        $data['order_type'] = 'info.see';//充值
        $data['goods_amt'] = '0.2';
        $data['final_amt'] = '0.2';
        $data['payname'] = $payname;
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $order_id=$OrderObj->add($data);

        $jsondata = array();
        //回调参数
        $jsondata['Services'] = "suibianxiedian";
        $jsondata['param'] = array();
        $jsondata['b_from'] = "order";
        $jsondata['order_sn'] = $order_sn;
        $jsondata['order_id'] = $order_id;
        if($order_id){
            $jsondata['info'] = '订单已生成';
            $jsondata['url'] = "{$host}appsdk/{$payment_code}/beforepay.php?order_id={$order_id}";
        }else{
            $jsondata['info'] = "服务器忙";
            $jsondata['url'] = '';
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'供应（促销）付费信息查看');
    }

    public function heartpro(){
        $host = Buddha::$buddha_array['host'];
        $UserObj = new User();
        $HeartproObj = new Heartpro();
        if(Buddha_Http_Input::checkParameter(array('usertoken','payment_code','payname','heartpro_id'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $heartpro_id = Buddha_Http_Input::getParameter('heartpro_id');
        $payname= Buddha_Http_Input::getParameter('payname');
        $payment_code= Buddha_Http_Input::getParameter('payment_code');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $number = 1;

        if(!$HeartproObj->isStart($heartpro_id))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '竞买时间还未开始，不能投票');
        }

        if($HeartproObj->isExpire($heartpro_id))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '报名已结束，不能报名!');
        }

        $Db_Heartpro = $HeartproObj->getSingleFiledValues(array('price','applystarttime','applyendtime','partake','user_id')," id='{$heartpro_id}'");

        $money = $Db_Heartpro['price'];


        if(!Buddha_Atom_String::isValidString($money)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '竞买产品价格不对');

        }

        $HeartproObj = new Heartpro();
        $OrderObj=new Order();
        $OrdermerchantObj=new Ordermerchant();

        $merchant_uid = $HeartproObj->getSingleFiledValues(array('user_id','votecount','stock'),"id={$heartpro_id}");
        $Minvotes= $merchant_uid['votecount'];//最少投票数量

        $HeartapplyObj = new Heartapply();
        $Db_Heartapply = $HeartapplyObj->getSingleFiledValues(array('vote_num','is_buy'),"heartpro_id='{$heartpro_id}' AND user_id='{$user_id}' ");

        $Currentvotes = $Db_Heartapply['vote_num']; // 当前投票数量

        if(!Buddha_Atom_Array::isValidArray($Db_Heartapply))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '您还未参加竞买，请先参加竞买吧！');

        }

        if(!($Currentvotes >= $Minvotes))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '对不起，您的票数还不够，还须加油！喊一下好友前来助力吧！');

        }

        if(!Buddha_Atom_String::isValidString($merchant_uid['stock']))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444,'对不起，你来晚了，现在库存为'.$merchant_uid['stock'].',欢迎你下次光临！');

        }

        $OrderObj = new Order();
        $Db_Order_count = $OrderObj->countRecords("good_id='{$heartpro_id}' AND good_table='heartpro' AND user_id='{$user_id}' AND pay_status=2");

        if($Db_Heartapply['is_buy']==1)
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444,'本活动一人只能有一次购买权哦!您已经成功竞买，快去帮好友拉票吧！');

        }

        if($Db_Order_count)
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444,'本活动一人只能有一次购买权哦!您已经成功竞买，快去帮好友拉票吧！');
            $HeartapplyObj = new Heartapply();
            $HeartapplyObj->updateRecords(array('is_buy'=>1),"user_id='{$user_id}' AND heartpro_id='{$heartpro_id}'");

        }

        if($payment_code==0 and $payname==0){
            $jsondata=array();
            $jsondata['user_id'] = $user_id;
            $jsondata['usertoken'] = $usertoken;
            $jsondata['msg'] = '可以调用支付选择同时再调用这个接口了';
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 0,'生成订单前的支付检查,然后再调用支付选择,再调用这个接口');

        }


        $UserInfo = $UserObj->getSingleFiledValues('',"id='{$user_id}'");
        $data=array();
        $order_sn = $OrderObj->birthOrderId($user_id);//订单编号
        $data['good_id'] = $heartpro_id;//指定产品id
        $data['user_id'] = $user_id;
        $data['merchant_uid'] = $merchant_uid['user_id'];
        $data['order_sn'] = $order_sn;
        $data['good_table'] = 'heartpro';//哪个表
        $data['pay_type'] = 'third';//third第三方支付，point积分，balance余额
        $data['order_type'] = 'heartpro';//money.out提现, 店铺认证shop.v,信息置顶info.top ,跨区域信息推广info.market,信息查看info.see,shopping购物,heartpro1分购
        $data['goods_amt'] = $money * $number;//产品价格
        $data['final_amt'] = $money * $number;//产品最终价格
        $data['order_total'] = $number;//件数
        $data['payname']=$payname;
        $data['make_level0'] = $UserInfo['level0'];//国家
        $data['make_level1'] = $UserInfo['level1'];//省
        $data['make_level2'] = $UserInfo['level2'];//市
        $data['make_level3'] = $UserInfo['level3'];//区县
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];  //  时间戳
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr']; //  时间日期

        $order_id = $OrderObj->add($data);

        /**↓↓↓↓↓↓↓↓↓↓↓ 订单子表(订购商品详情表) ↓↓↓↓↓↓↓↓↓↓↓**/
        $OrderproductObj = new Orderproduct();
        $SupplyObj = new Supply();

        $Db_Heartpro = $HeartproObj->getSingleFiledValues(array('table_id','price','small'),"id='{$heartpro_id}'");
        //
        $goodsinfo = $SupplyObj->getSingleFiledValues(array('id','goods_name','goods_thumb','user_id'),"id={$Db_Heartpro['table_id']}");//获取购买商品的详情
        //
        $Orderproductdata['product_id'] = $heartpro_id;//指定产品id
        $Orderproductdata['product_table'] = 'heartpro';//哪个表
        $Orderproductdata['product_name'] = $goodsinfo['goods_name'];//产品名称
        $Orderproductdata['product_img'] = $Db_Heartpro['small'];//商品在线图片
        $Orderproductdata['product_price'] =$Db_Heartpro['price'];//商品在线价格
        $Orderproductdata['product_total'] = $number;//产品订购数量
        $Orderproductdata['order_id'] = $order_id;//订单表内码ID
        $Orderproductdata['product_amt'] =  $money * $number;//产品小计
        $Orderproductdata['merchant_id'] =  $goodsinfo['user_id'];//产品所有者ID
        $Orderproductdata['merchant_amt'] = $money * $number;//此产品最终价格

        $Orderproductdata['createtime'] = Buddha::$buddha_array['buddha_timestamp'];  //  时间戳
        $Orderproductdata['createtimestr'] = Buddha::$buddha_array['buddha_timestr']; //  时间日期

        $Orderproduct_id = $OrderproductObj->add($Orderproductdata);

        /**↑↑↑↑↑↑↑↑↑↑ 订单子表(订购商品详情表) ↑↑↑↑↑↑↑↑↑↑**/


        $OrdermerchantObj->getInsertVersion1OrderMerchantInt($order_id,$order_sn,$merchant_uid['user_id'],$money * $number,"heartpro:{$id}");

        $jsondata = array();
        //回调参数
        $jsondata['Services'] = "suibianxiedian";
        $jsondata['param'] = array();
        $jsondata['b_from'] = "order";
        $jsondata['order_sn'] = $order_sn;
        $jsondata['order_id'] = $order_id;
        if($order_id){
            $jsondata['info'] = '订单已生成';
            $jsondata['url'] = "{$host}appsdk/{$payment_code}/beforepay.php?order_id={$order_id}";
        }else{
            $jsondata['info'] = "服务器忙";
            $jsondata['url'] = '';
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'1分购生成订单');



    }


    /**
     * 代理商申请订单生成
     */
    public function agentorder(){
        $host = Buddha::$buddha_array['host'];

        if(Buddha_Http_Input::checkParameter(array('usertoken','payment_code','payname','applyagent_id'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $PaymentObj = new Payment();
        $OrderObj = new Order();
        $ApplyagentObj = new Applyagent();
        $payname= Buddha_Http_Input::getParameter('payname');
        $applyagent_id= Buddha_Http_Input::getParameter('applyagent_id');
        $payment_code= Buddha_Http_Input::getParameter('payment_code');

        $Db_Payment = $PaymentObj->getSingleFiledValues(array('payment_id'),"payment_code = '{$payment_code}' ");
        $payname_id =(int)$Db_Payment['payment_id'];

        if($payname_id<1){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 60000001, $payname.'支付方式没有开通');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','realname','mobile','username','level1','level2','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $applyagentInfo = $ApplyagentObj->getSingleFiledValues('',"id='{$applyagent_id}'");

        //生成订单
        $order_sn = $OrderObj->birthOrderId($user_id);
        $datas=array();
        $datas['good_id']=$applyagent_id;//指定产品id
        $datas['user_id']=$user_id;
        $datas['order_sn']= $order_sn;//订单编号
        $datas['good_table']='applyagent';//哪个表
        $datas['pay_type']='third';//third第三方支付，point积分，balance余额
        $datas['order_type']='applyagent';//money.out提现, 店铺认证shop.v,信息置顶info.top
        $datas['goods_amt']=3000.00;//产品价格
        $datas['final_amt']=3000.00;//产品最终价格
        $datas['payname']='微信支付';
        $datas['make_level0']=1;//国家
        $datas['make_level1']=$applyagentInfo['level1'];//省
        $datas['make_level2']=$applyagentInfo['level2'];//市
        $datas['make_level3']=$applyagentInfo['level3'];//区县
        $datas['createtime']=time();//时间戳
        $datas['createtimestr']=date('Y-m-d H:i:s');//时间日期
        $order_id=$OrderObj->add($datas);

        $jsondata = array();
        //回调参数
        $jsondata['Services'] = "suibianxiedian";
        $jsondata['param'] = array();
        $jsondata['b_from'] = "order";
        $jsondata['order_sn'] = $order_sn;
        $jsondata['order_id'] = $order_id;
        if($order_id){
            $jsondata['info'] = '订单已生成';
            $jsondata['url'] = "{$host}appsdk/{$payment_code}/beforepay.php?order_id={$order_id}";
        }else{
            $jsondata['info'] = "服务器忙";
            $jsondata['url'] = '';
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'代理商申请订单生成成功');
    }
    /**
     * 购物车产品转成订单
     */
    public function carttoorder(){
        $host = Buddha::$buddha_array['host'];
        if(Buddha_Http_Input::checkParameter(array('usertoken','payment_code','payname'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $PaymentObj = new Payment();
        $CartObj = new Cart();
        $OrderObj = new Order();
        $OrderproductObj = new Orderproduct();
        $OrdermerchantObj = new Ordermerchant();
        $payname= Buddha_Http_Input::getParameter('payname');
        $payment_code= Buddha_Http_Input::getParameter('payment_code');
        $Db_Payment = $PaymentObj->getSingleFiledValues(array('payment_id'),"payment_code = '{$payment_code}' ");
        $payname_id =(int)$Db_Payment['payment_id'];
        if($payname_id<1){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 60000001, $payname.'支付方式没有开通');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','realname','mobile','username','level1','level2','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $level = array('level1'=>$Db_User['level1'],'level2'=>$Db_User['level2'],'level3'=>$Db_User['level3']);

        $DB_Cart = $CartObj->getCartArr($user_id);
        $Total_Arr = $CartObj->getTotalArr($DB_Cart);

        $order_type = 'shopping';
        $referral_id =0;
        $agent_id =0;
        $partnerrate =0;
        $agentrate =0;
        $final_amt = $Total_Arr['goods_total_price'];
        //生成订单
        $order_sn = $OrderObj->birthOrderId($user_id);
        $data = array();
        $data['user_id'] = $user_id;
        $data['order_sn'] = $order_sn;
        $data['referral_id'] =$referral_id;
        $data['partnerrate'] =$partnerrate;
        $data['agent_id'] = $agent_id;
        $data['agentrate'] = $agentrate;
        $data['pay_type'] = 'third';
        $data['order_type'] =$order_type;
        $data['goods_amt'] = $final_amt;
        $data['final_amt'] = $final_amt;
        $data['payname'] = $payname;
        $data['payname_id'] = $payname_id;
        $data['make_level0'] = 1;
        $data['make_level1'] = $level[0];
        $data['make_level2'] = $level[1];
        $data['make_level3'] = $level[2];
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $order_id=$OrderObj->add($data);
        //生成子产品
        $OrderproductObj->createOrderproductFromCart($order_id,$order_sn,$DB_Cart);
       //生成商户订单产品表
        $OrdermerchantObj->createOrdermerchantFromCart($order_id,$order_sn,$DB_Cart);
       //清空购物车
        $CartObj->cleanUserCartGoods($user_id);
        $jsondata = array();
        //回调参数
        $jsondata['Services'] = "suibianxiedian";
        $jsondata['param'] = array();
        $jsondata['b_from'] = "order";
        $jsondata['order_sn'] = $order_sn;
        $jsondata['order_id'] = $order_id;
        if($order_id){
            $jsondata['info'] = '订单已生成';
            $jsondata['url'] = "{$host}appsdk/{$payment_code}/beforepay.php?order_id={$order_id}";
        }else{
            $jsondata['info'] = "服务器忙";
            $jsondata['url'] = '';
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'购物车产品转成订单成功');

    }



    /**
     * XXX 信息置顶
     */
    public function infotop(){

        $host = Buddha::$buddha_array['host'];

        if(Buddha_Http_Input::checkParameter(array('usertoken','payment_code','payname','good_id','good_table'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $OrderObj=new Order();
        $ShopObj = new Shop();
        $PaymentObj = new Payment();
        $MysqlplusObj = new Mysqlplus();


        $payname= Buddha_Http_Input::getParameter('payname');
        $payment_code= Buddha_Http_Input::getParameter('payment_code');

        $Db_Payment = $PaymentObj->getSingleFiledValues(array('payment_id'),"payment_code = '{$payment_code}' ");
        $payname_id =(int)$Db_Payment['payment_id'];

        if($payname_id<1){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 60000001, $payname.'支付方式没有开通');
        }

        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','realname','mobile','username','level1','level2','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $level = array('level1'=>$Db_User['level1'],'level2'=>$Db_User['level2'],'level3'=>$Db_User['level3']);

        $good_id = Buddha_Http_Input::getParameter('good_id');
        $good_table = Buddha_Http_Input::getParameter('good_table');


        if(!$MysqlplusObj->isValidTable($good_table)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000001, 'good_tablei不存在');
        }

        $num = $this->db->countRecords($good_table,"id='{$good_id}'");
        if($num<1){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000002, 'good_id不正确');
        }


        $order_type = 'info.top';
        $referral_id =0;
        $agent_id =0;
        $partnerrate =0;
        $agentrate =0;
        $final_amt = 0.2;

        if(!empty($good_table)){

            $title='';
            if ($good_table == 'shop') {
                $ShopObj = new Shop();
                $shopid_arr = $ShopObj->getSingleFiledValues(array('id'), "isdel=0 and id='{$good_id}' ");
                $title = '店铺';
            }

            if ($good_table == 'supply') {
                $SupplyObj = new Supply();
                $shopid_arr = $SupplyObj->getSingleFiledValues(array('shop_id'), "isdel=0 and id='{$good_id}' ");
                $title = '供应';
            }
            if ($good_table == 'activity') {
                $ActivityObj = new Activity();
                $shopid_arr = $ActivityObj->getSingleFiledValues(array('shop_id'), "isdel=0 and id='{$good_id}' ");
                $title = '活动';
            }

            if ($good_table == 'lease') {
                $LeaseObj = new Lease();
                $shopid_arr = $LeaseObj->getSingleFiledValues(array('shop_id'), "isdel=0 and id='{$good_id}'");
                $title = '租赁';
            }

            if ($good_table == 'recruit') {
                $RecruitObj = new Recruit();
                $shopid_arr = $RecruitObj->getSingleFiledValues(array('shop_id'), "isdel=0 and id='{$good_id}'");
                $title = '招聘';
            }

            if ($good_table == 'demand') {
                $DemandObj = new Demand();
                $shopid_arr = $DemandObj->getSingleFiledValues(array('shop_id'), "isdel=0 and id='{$good_id}'");
                $title = '需求';
            }

            if ($good_table == 'singleinformation') {
                $SingleinformationObj = new Singleinformation();
                $shopid_arr = $SingleinformationObj->getSingleFiledValues(array('shop_id'), "isdel=0 and id='{$good_id}'");
                $title = '单页';
            }



            $Db_Shop = $ShopObj->getSingleFiledValues(array('referral_id','agent_id','partnerrate','agentrate'), "isdel=0 and id='{$shopid_arr['shop_id']}' ");
            $referral_id = (int)$Db_Shop['referral_id'];
            $agent_id = (int)$Db_Shop['agent_id'];
            $partnerrate = (int)$Db_Shop['partnerrate'];
            $agentrate = (int)$Db_Shop['agentrate'];
            if ($good_table == 'shop') {
                $getMoneyArray = $ShopObj->getMoneyArrayFromShop($shopid_arr['id'], $final_amt);//该函数用于查询店铺
            }else{
                $getMoneyArray = $ShopObj->getMoneyArrayFromShop($shopid_arr['shop_id'], $final_amt);//该函数用于查询店铺
            }



        }



        //生成订单
        $order_sn = $OrderObj->birthOrderId($user_id);
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
        $data['payname_id'] = $payname_id;
        $data['make_level0'] = 1;
        $data['make_level1'] = $level[0];
        $data['make_level2'] = $level[1];
        $data['make_level3'] = $level[2];
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $order_id=$OrderObj->add($data);



        $jsondata = array();
        //回调参数
        $jsondata['Services'] = "suibianxiedian";
        $jsondata['param'] = array();
        $jsondata['b_from'] = "order";
        $jsondata['order_sn'] = $order_sn;
        $jsondata['order_id'] = $order_id;
        if($order_id){
            $jsondata['info'] = '订单已生成';
            $jsondata['url'] = "{$host}appsdk/{$payment_code}/beforepay.php?order_id={$order_id}";
        }else{
            $jsondata['info'] = "服务器忙";
            $jsondata['url'] = '';
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,$title.'的信息置顶成功！');
//        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,$good_table.'异地付费成功！');
    }


    /**
     * 异地付费
     */
   public function remoteinfo()
   {
       $host = Buddha::$buddha_array['host'];
       if(Buddha_Http_Input::checkParameter(array('usertoken','payment_code','payname','good_id','good_table'))) {//判断参数的有效性
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
       }

       $UserObj = new User();
       $OrderObj=new Order();
       $ShopObj = new Shop();
       $PaymentObj = new Payment();
       $MysqlplusObj = new Mysqlplus();

       $payname= Buddha_Http_Input::getParameter('payname');
       $good_id= (int)Buddha_Http_Input::getParameter('good_id')?Buddha_Http_Input::getParameter('good_id'):0;

       $payment_code= Buddha_Http_Input::getParameter('payment_code');

       $Db_Payment = $PaymentObj->getSingleFiledValues(array('payment_id'),"payment_code = '{$payment_code}' ");
       $payname_id =(int)$Db_Payment['payment_id'];


       if($payname_id<1){
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 60000001, $payname.'支付方式没有开通');
       }

       $usertoken = Buddha_Http_Input::getParameter('usertoken');
       $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
       $fieldsarray= array('id','usertoken','realname','mobile','username','level1','level2','level3');
       $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
       $user_id = $Db_User['id'];

       $good_id = Buddha_Http_Input::getParameter('good_id');
       $good_table = Buddha_Http_Input::getParameter('good_table');


       if(!$MysqlplusObj->isValidTable($good_table)){
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000001, 'good_table不存在');
       }

       $num = $this->db->countRecords($good_table,"id='{$good_id}'");
       if($num<1){
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000002, 'good_id不正确');
       }


       $Db_Table= $this->db->getSingleFiledValues(array('shop_id'),$good_table,"id='{$good_id}'");
       $shop_id = $Db_Table['shop_id'];


       $Db_referral=$ShopObj->getSingleFiledValues(array('referral_id','partnerrate','agent_id','agentrate','level0','level1','level2','level3'),"id='{$shop_id}' and user_id='{$user_id}' and isdel=0");
       $getMoneyArrayFromShop = $ShopObj->getMoneyArrayFromShop($shop_id,0.2);

       $order_sn = $OrderObj->birthOrderId($user_id);
       $data=array();
       $data['good_id']=$good_id;
       $data['user_id']=$user_id;
       $data['order_sn']= $order_sn;
       $data['good_table']='lease';
       $data['pay_type']='third';
       $data['good_table'] = 'info.market';
       $data['referral_id']=$Db_referral['referral_id'];
       $data['partnerrate']=$Db_referral['partnerrate'];
       $data['agent_id']=$Db_referral['agent_id'];
       $data['agentrate']=$Db_referral['agentrate'];
       $data['goods_amt'] = $getMoneyArrayFromShop['goods_amt'];
       $data['final_amt'] = $getMoneyArrayFromShop['final_amt'];
       $data['money_plat'] = $getMoneyArrayFromShop['money_plat'];
       $data['money_agent'] = $getMoneyArrayFromShop['money_agent'];
       $data['money_partner'] = $getMoneyArrayFromShop['money_partner'];
       $data['payname'] = '微信支付';
       $data['make_level0']=$Db_referral['level0'];
       $data['make_level1']=$Db_referral['level1'];
       $data['make_level2']=$Db_referral['level2'];
       $data['make_level3']=$Db_referral['level3'];
       $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
       $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
       $order_id= $OrderObj->add($data);



       $jsondata = array();
       //回调参数
       $jsondata['Services'] = "suibianxiedian";
       $jsondata['param'] = array();
       $jsondata['b_from'] = "order";
       $jsondata['order_sn'] = $order_sn;
       $jsondata['order_id'] = $order_id;
       if($order_id){
           $jsondata['info'] = '订单已生成';
           $jsondata['url'] = "{$host}appsdk/{$payment_code}/beforepay.php?order_id={$order_id}";
       }else{
           $jsondata['info'] = "服务器忙";
           $jsondata['url'] = '';
       }
       Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'异地付费！');
   }


    /**
     * 店铺认证
     */
    public function shopverify(){

        $host = Buddha::$buddha_array['host'];

        if(Buddha_Http_Input::checkParameter(array('usertoken','payment_code','payname','good_id','good_table'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $OrderObj=new Order();
        $ShopObj = new Shop();
        $PaymentObj = new Payment();
        $MysqlplusObj = new Mysqlplus();


        $is_verify= Buddha_Http_Input::getParameter('is_verify');
        //if($is_verify==1){
            $pay_type='third';
        /*}else if($is_verify==1){
            /*认证码 认证
            //$pay_type='certification';
        }*/


        $payname= Buddha_Http_Input::getParameter('payname');
        $good_id= (int)Buddha_Http_Input::getParameter('good_id')?Buddha_Http_Input::getParameter('good_id'):0;

        $payment_code= Buddha_Http_Input::getParameter('payment_code');

        $Db_Payment = $PaymentObj->getSingleFiledValues(array('payment_id'),"payment_code = '{$payment_code}' ");
        $payname_id =(int)$Db_Payment['payment_id'];
        $rzcodes =(int)$Db_Payment['rzcodes'];

        if($payname_id<1){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 60000001, $payname.'支付方式没有开通');
        }

        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','realname','mobile','username','level1','level2','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if($good_id>0){
           $Db_Shop= $ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),'id='.$good_id);
        }


        $level = array('level0'=>$Db_Shop['level0'],'level1'=>$Db_Shop['level1'],'level2'=>$Db_Shop['level2'],'level3'=>$Db_Shop['level3']);

        $good_id = Buddha_Http_Input::getParameter('good_id');
        $good_table = Buddha_Http_Input::getParameter('good_table');


        if(!$MysqlplusObj->isValidTable($good_table)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000001, 'good_tablei不存在');
        }

        $num = $this->db->countRecords($good_table,"id='{$good_id}'");
        if($num<1){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000002, 'good_id不正确');
        }

        /*商家店铺自行认证*/
        $order_type = 'shop.v';
        $referral_id =0;
        $agent_id =0;
        $partnerrate =0;
        $agentrate =0;
        $final_amt = 990;

        if(!empty($good_table)){
            if ($good_table == 'shop') {
                $ShopObj = new Shop();
                $shopid_arr = $ShopObj->getSingleFiledValues(array('id'), "isdel=0 and id='{$good_id}' ");
            }

            $Db_Shop = $ShopObj->getSingleFiledValues(array('referral_id','agent_id','partnerrate','agentrate'), " isdel=0 and id='{$shopid_arr['shop_id']}' ");
            $referral_id = (int)$Db_Shop['referral_id'];
            $agent_id = (int)$Db_Shop['agent_id'];
            $partnerrate = (int)$Db_Shop['partnerrate'];
            $agentrate = (int)$Db_Shop['agentrate'];
            $getMoneyArray = $ShopObj->getMoneyArrayFromShop($shopid_arr['id'], $final_amt);//该函数用于查询店铺里的参数
        }

        $data = array();
        if($is_verify==1){
            $data['referral_id'] =$referral_id;
            $data['partnerrate'] =$partnerrate;
            $data['agent_id'] = $agent_id;
            $data['agentrate'] = $agentrate;
            $data['goods_amt'] = $getMoneyArray['goods_amt'];
            $data['final_amt'] = $getMoneyArray['final_amt'];
            $data['money_plat'] = $getMoneyArray['money_plat'];
            $data['money_agent'] = $getMoneyArray['money_agent'];
            $data['money_partner'] = $getMoneyArray['money_partner'];
            $data['payname'] = $payname;
            $data['payname_id'] = $payname_id;
            $order_sn=$OrderObj->birthOrderId($user_id);
            $data['order_sn'] = $order_sn;
            $data['good_table'] = $good_table;
            $data['good_id'] = $good_id;
            $data['user_id'] = $user_id;
            $data['pay_type'] = $pay_type;
            $data['order_type'] =$order_type;
            $data['make_level0'] = $level[0];
            $data['make_level1'] = $level[1];
            $data['make_level2'] = $level[2];
            $data['make_level3'] = $level[3];
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $order_id=$OrderObj->add($data);
        }else if($is_verify==1){
            $certifObj = new Certification();
            $time = time();
            $certifinfo = $certifObj->getSingleFiledValues('',"code='{$rzcodes}' AND is_use=0 AND overdue_time>{$time}");
            $data['pay_status'] =1;
            $data['payname'] = $rzcodes;

            /*生成订单*/
            $order_sn=$OrderObj->birthOrderId($user_id);
            $data['order_sn'] = $order_sn;
            $data['good_table'] = $good_table;
            $data['good_id'] = $good_id;
            $data['user_id'] = $user_id;
            $data['pay_type'] = $pay_type;
            $data['order_type'] = $order_type;
            $data['make_level0'] = $level[0];
            $data['make_level1'] = $level[1];
            $data['make_level2'] = $level[2];
            $data['make_level3'] = $level[3];
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $order_id=$OrderObj->add($data);
            if($order_id){
                $datas = array();
                $datas['is_verify'] = 1;//改变订单状态
                $datas['is_del'] = 0;//改变订单状态
                $ShopObj->edit($datas,$good_id);
                $datass = array();
                $datass['is_use'] = 1;//改变认证码状态
                $certifObj->edit($datass,$certifinfo['id']);
            }
        }

        $jsondata = array();
        //回调参数
        $jsondata['Services'] = "suibianxiedian";
        $jsondata['param'] = array();
        $jsondata['b_from'] = "order";
        $jsondata['order_sn'] = $order_sn;
        $jsondata['order_id'] = $order_id;
        if($order_id){
            $jsondata['info'] = '订单已生成';
            $jsondata['url'] = "{$host}appsdk/{$payment_code}/beforepay.php?order_id={$order_id}";
        }else{
            $jsondata['info'] = "服务器忙";
            $jsondata['url'] = '';
        }


        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'店铺认证成功！');
    }



    /**
     * 租赁付费信息查看
     * author sys
     */
    public function checkinformationpaylease()
    {
        $host = Buddha::$buddha_array['host'];
        $UserObj = new User();
        if(Buddha_Http_Input::checkParameter(array('usertoken','payment_code','payname','lease_id'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        //$money= Buddha_Http_Input::getParameter('money');
        $payname= Buddha_Http_Input::getParameter('payname');
        $lease_id= Buddha_Http_Input::getParameter('lease_id');
        $payment_code= Buddha_Http_Input::getParameter('payment_code');
        $OrderObj=new Order();
        //生成订单
        $order_sn = $OrderObj->birthOrderId($user_id);
        $data = array();
        $data['user_id'] = $user_id;
        $data['good_id'] = $lease_id;
        $data['order_sn'] =$order_sn;
        $data['good_table'] = "lease";
        $data['referral_id'] =0;
        $data['partnerrate'] =0;
        $data['pay_type'] = 'third';
        $data['order_type'] = 'info.see';//充值
        $data['goods_amt'] = '0.2';
        $data['final_amt'] = '0.2';
        $data['payname'] = $payname;
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $order_id=$OrderObj->add($data);

        $jsondata = array();
        //回调参数
        $jsondata['Services'] = "suibianxiedian";
        $jsondata['param'] = array();
        $jsondata['b_from'] = "order";
        $jsondata['order_sn'] = $order_sn;
        $jsondata['order_id'] = $order_id;
        if($order_id){
            $jsondata['info'] = '订单已生成';
            $jsondata['url'] = "{$host}appsdk/{$payment_code}/beforepay.php?order_id={$order_id}";
        }else{
            $jsondata['info'] = "服务器忙";
            $jsondata['url'] = '';
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'租赁付费信息查看');
    }

    /**
     * 招聘付费信息查看
     * author sys
     */
    public function checkinformationpayrecruit()
    {
        $host = Buddha::$buddha_array['host'];
        $UserObj = new User();
        if(Buddha_Http_Input::checkParameter(array('usertoken','payment_code','payname','recruit_id'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        //$money= Buddha_Http_Input::getParameter('money');
        $payname= Buddha_Http_Input::getParameter('payname');
        $recruit_id= Buddha_Http_Input::getParameter('recruit_id');
        $payment_code= Buddha_Http_Input::getParameter('payment_code');
        $OrderObj=new Order();
        //生成订单
        $order_sn = $OrderObj->birthOrderId($user_id);
        $data = array();
        $data['user_id'] = $user_id;
        $data['good_id'] = $recruit_id;
        $data['order_sn'] =$order_sn;
        $data['good_table'] = "recruit";
        $data['referral_id'] =0;
        $data['partnerrate'] =0;
        $data['pay_type'] = 'third';
        $data['order_type'] = 'info.see';//充值
        $data['goods_amt'] = '0.2';
        $data['final_amt'] = '0.2';
        $data['payname'] = $payname;
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $order_id=$OrderObj->add($data);

        $jsondata = array();
        //回调参数
        $jsondata['Services'] = "suibianxiedian";
        $jsondata['param'] = array();
        $jsondata['b_from'] = "order";
        $jsondata['order_sn'] = $order_sn;
        $jsondata['order_id'] = $order_id;
        if($order_id){
            $jsondata['info'] = '订单已生成';
            $jsondata['url'] = "{$host}appsdk/{$payment_code}/beforepay.php?order_id={$order_id}";
        }else{
            $jsondata['info'] = "服务器忙";
            $jsondata['url'] = '';
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'招聘付费信息查看');
    }

    /**
     * 需求付费信息查看
     * author sys
     */
    public function checkinformationpaydemand()
    {
        $host = Buddha::$buddha_array['host'];
        $UserObj = new User();
        if(Buddha_Http_Input::checkParameter(array('usertoken','payment_code','payname','demand_id'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        //$money= Buddha_Http_Input::getParameter('money');
        $demand_id= Buddha_Http_Input::getParameter('demand_id');
        $payname= Buddha_Http_Input::getParameter('payname');
        $payment_code= Buddha_Http_Input::getParameter('payment_code');
        $OrderObj=new Order();
        //生成订单
        $order_sn = $OrderObj->birthOrderId($user_id);
        $data = array();
        $data['user_id'] = $user_id;
        $data['good_id'] = $demand_id;
        $data['order_sn'] =$order_sn;
        $data['good_table'] = "demand";
        $data['referral_id'] =0;
        $data['partnerrate'] =0;
        $data['pay_type'] = 'third';
        $data['order_type'] = 'info.see';//充值
        $data['goods_amt'] = '0.2';
        $data['final_amt'] = '0.2';
        $data['payname'] = $payname;
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $order_id=$OrderObj->add($data);

        $jsondata = array();
        //回调参数
        $jsondata['Services'] = "suibianxiedian";
        $jsondata['param'] = array();
        $jsondata['b_from'] = "order";
        $jsondata['order_sn'] = $order_sn;
        $jsondata['order_id'] = $order_id;
        if($order_id){
            $jsondata['info'] = '订单已生成';
            $jsondata['url'] = "{$host}appsdk/{$payment_code}/beforepay.php?order_id={$order_id}";
        }else{
            $jsondata['info'] = "服务器忙";
            $jsondata['url'] = '';
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'需求付费信息查看');
    }

    /**
     * 店铺付费查看电话号码
     * author sys
     */
    public function checkinformationpayshop()
    {
        $host = Buddha::$buddha_array['host'];
        $UserObj = new User();
        if(Buddha_Http_Input::checkParameter(array('usertoken','payment_code','payname','shop_id'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        //$money= Buddha_Http_Input::getParameter('money');
        $payname= Buddha_Http_Input::getParameter('payname');
        $shop_id= Buddha_Http_Input::getParameter('shop_id');
        $payment_code= Buddha_Http_Input::getParameter('payment_code');
        $OrderObj=new Order();
        //生成订单
        $order_sn = $OrderObj->birthOrderId($user_id);
        $data = array();
        $data['user_id'] = $user_id;
        $data['good_id'] = $shop_id;
        $data['order_sn'] =$order_sn;
        $data['good_table'] = "recruit";
        $data['referral_id'] =0;
        $data['partnerrate'] =0;
        $data['pay_type'] = 'third';
        $data['order_type'] = 'info.see';//充值
        $data['goods_amt'] = '0.2';
        $data['final_amt'] = '0.2';
        $data['payname'] = $payname;
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $order_id=$OrderObj->add($data);

        $jsondata = array();
        //回调参数
        $jsondata['Services'] = "suibianxiedian";
        $jsondata['param'] = array();
        $jsondata['b_from'] = "order";
        $jsondata['order_sn'] = $order_sn;
        $jsondata['order_id'] = $order_id;
        if($order_id){
            $jsondata['info'] = '订单已生成';
            $jsondata['url'] = "{$host}appsdk/{$payment_code}/beforepay.php?order_id={$order_id}";
        }else{
            $jsondata['info'] = "服务器忙";
            $jsondata['url'] = '';
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'店铺付费查看电话号码');
    }


    /**
     * 认证码认证店铺
     */
    public function authenticationCode()
    {
        $UserObj = new User();
        if(Buddha_Http_Input::checkParameter(array('usertoken','shop_id','codes'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services=' . $_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $shop_id=(int)Buddha_Http_Input::getParameter('shop_id');
        $codes = Buddha_Http_Input::getParameter('codes');
        $certifObj = new Certification();
        $ShopObj = new Shop();
        $OrderObj = new Order();
        if(!$ShopObj->isShopBelongToUser($shop_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000029, '此店铺不属于当前用户');
        }
        if(!$certifObj->isAuthenticationCodeCorrect($codes)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000048, '认证码不存在或已使用或已过期');
        }
        $time = time();
        $certifinfo = $certifObj->getSingleFiledValues('',"code='{$codes}' and is_use=0 and overdue_time>{$time}");

        if(!$certifinfo){
            $data['isok'] = 2;
            Buddha_Http_Output::makeJson($data);
        }
        $data = array();
        $data['good_id'] = $shop_id;
        $data['user_id'] = $user_id;
        $data['order_sn'] = $OrderObj->birthOrderId($user_id);
        $data['good_table'] = 'shop';
        $data['pay_status'] =1;
        $data['pay_type'] = 'certification';
        $data['order_type'] = 'shop.v';
        $data['payname'] = $codes;
        $data['make_level0'] = $Db_User['leve0'];
        $data['make_level1'] = $Db_User['level'];
        $data['make_level2'] = $Db_User['leve2'];
        $data['make_level3'] = $Db_User['leve3'];
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $order_id=$OrderObj->add($data);
        if($order_id){
            $datas = array();
            $datas['is_verify'] = 1;//改变店铺状态
            $datas['isdel'] = 0;//改变店铺状态
            $ShopObj->edit($datas,$shop_id);
            $datass = array();
            $datass['shop_id'] = $shop_id;
            $datass['user_id'] = $user_id;
            $datass['usetime'] = Buddha::$buddha_array['buddha_timestamp'];
            $datass['is_use'] = 1;//改变认证码状态
            $certifObj->edit($datass,$certifinfo['id']);
            $jsondata['isok'] = 1;
            $jsondata['info'] = '店铺认证成功';
        }else{
            $jsondata['isok'] = 2;
            $jsondata['info'] = '服务器忙';
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'店铺使用认证码认证成功');





    }


















}