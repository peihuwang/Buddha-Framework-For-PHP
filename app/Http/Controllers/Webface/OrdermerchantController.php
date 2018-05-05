<?php

/**
 * Class OrdermerchantController
 */
class OrdermerchantController extends Buddha_App_Action
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
    /*
     *  @merchantordermore 订单：商家订单列表
     * $orderstatus == 0 代表全部；$orderstatus == 2  待发货；$orderstatus == 3  待收货;$orderstatus == 4  已完成
     * pay_status==2 已付款 ； pay_status==1 付款中（表中的注释是付款中；其实是付款成功） ；pay_status==0未付款
     * order_status=0待发货；order_status=1 已发货；order_status=2已收货；order_status=3 已退款
     */



    /**
     * 商家订单列表
     * merchantordermore 订单：商家订单列表
     * $orderstatus == 0 代表全部；$orderstatus == 2  待发货；$orderstatus == 3  待收货;$orderstatus == 4  已完成
     * pay_status==2 已付款 ； pay_status==1 付款中（表中的注释是付款中；其实是付款成功） ；pay_status==0未付款
     * order_status=0待发货；order_status=1 已发货；order_status=2已收货；order_status=3 已退款
     */
    public function merchantordermore(){
        $host = Buddha::$buddha_array['host'];
        $UserObj = new User();
        $SupplyObj = new Supply();
        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $page = (int)Buddha_Http_Input::getParameter('page') ? (int)Buddha_Http_Input::getParameter('page') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        $where = " om.merchant_id='{$user_id}'";
        $orderstatus = (int)Buddha_Http_Input::getParameter('orderstatus');
        if($orderstatus == 2){
            $where .= "  and o.pay_status=1 and o.order_status=0 ";
        }elseif($orderstatus == 3){
            $where .= " and o.pay_status=1 and o.order_status=1 ";
        }elseif($orderstatus == 4){
            $where .= " and o.pay_status=1 and o.order_status=2 ";
        }
        $sql = "select count(*) as total 
                from {$this->prefix}ordermerchant  as om
                INNER  join {$this->prefix}order as o
                on o.id = om.order_id  
                where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];

        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }

        $orderby = " order by om.id DESC ";
        $limit = Buddha_Tool_Page::sqlLimit($page, $pagesize);

        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        $jsondata['totalrecord'] = $rcount;
        $jsondata['totalpage'] = $pcount;

        $sql = "SELECT om.id AS ordermerchant_id,om.merchant_id,om.marchant_amt,om.order_id,om.createtime,
                       o.order_sn,o.pay_status,o.order_status
                FROM {$this->prefix}ordermerchant  AS om 
                LEFT  JOIN {$this->prefix}order AS o 
                ON o.id = om.order_id 
                WHERE  {$where}{$orderby} {$limit}";


        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
//        foreach($list as $k => $v){
//            $images = $SupplyObj->getSingleFiledValues(array('goods_thumb'),"id='{$v['good_id']}'");
//            $list[$k]['goods_thumb'] = $host . $images['goods_thumb'];
//            $list[$k]['icon_order_num'] = $host . '/apiuser/menuplus/order_icon.png';
//        }
        $jsondata['list'] = $list;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '商家订单列表');

    }




    /**
     * 商家订单详情
     */
    public function merchantorderview(){
        $host = Buddha::$buddha_array['host'];
        $UserObj = new User();
        $OrderObj = new Order();
        $SupplyObj = new Supply();
        $RegionObj = new Region();
        $AddressObj = new Address();
        $OrderproductObj = new Orderproduct();
        $MysqlplusObj = new Mysqlplus();
        $OrdermerchantObj = new Ordermerchant();
        if (Buddha_Http_Input::checkParameter(array('usertoken','order_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $b_display = Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        $order_id = (int)Buddha_Http_Input::getParameter('order_id');


        $where = " order_type='shopping' and buddhastatus='0' and isdel='0' ";
        $fieldsarray = array('id','good_id','good_table','goods_amt','final_amt','order_total','order_sn','pay_status','order_status','addressid');

        $Db_Order = $OrderObj->getSingleFiledValues($fieldsarray,"{$where}  AND id='{$order_id}' ") ;
//        print_r($Db_Order);

        /*收货地址详情*/
        if($Db_Order['addressid']){
            $Db_Address = $AddressObj->getSingleFiledValues('',"id='{$Db_Order['addressid']}' ");
        }else{
            $Db_Address = array();
        }

        $api_address = '';
        if($Db_Address['pro']){
            $pro = $RegionObj->getSingleFiledValues(array('name'),"id='{$Db_Address['pro']}' ");
            $api_address .= $pro['name'].'省';
        }
        if($Db_Address['city']){
            $city = $RegionObj->getSingleFiledValues(array('name'),"id='{$Db_Address['city']}' ");
            $api_address .= ' '.$city['name'].'市';
        }
        if($Db_Address['area']){
            $area = $RegionObj->getSingleFiledValues(array('name'),"id='{$Db_Address['area']}' ");
            $api_address .= ' '.$area['name'];
        }

        if(Buddha_Atom_String::isValidString($Db_Address['address']))
            $api_address .= $Db_Address['address'];


        /*订单状态：api_orderstatus=2 待发货 $api_orderstatus=3 已发货  $api_orderstatus=4 已完成 $api_orderstatus=0 未付款*/
        $api_orderstatus = $OrderObj->getApiOrderStatus($Db_Order['pay_status'],$Db_Order['order_status']);

        $orderproduct = array();

        $jsondata = array();
        if($OrderproductObj->isHasOrderproductRecord($Db_Order['id'])){
            $Db_Orderproduct=$OrderproductObj->getFiledValues(array('product_id','product_table','product_name','product_img','product_total','product_amt','merchant_amt'),"order_id='{$Db_Order['id']}' AND merchant_id='{$user_id}'  ");
            if(Buddha_Atom_Array::isValidArray($Db_Orderproduct)){
                foreach($Db_Orderproduct as $k=>$v){
                    $product_id = $v['product_id'];
                    $product_table = $v['product_table'];
                    if($MysqlplusObj->isValidTable($product_table)){
                        $orderproduct[] =   array('product_name'=>$v['product_name'],'product_price'=>$v['product_price'],'product_total'=>$v['product_total'],'product_img'=>$host.$v['product_img']);
                    }else{
                        $orderproduct[0] = array('product_name'=>'','product_price'=>'','product_total'=>'','product_img'=>'');
                    }
                }
            }else{
                $orderproduct[0] = array('product_name'=>'','product_price'=>'','product_total'=>'','product_img'=>'');
            }

            $Db_Ordermerchant = $OrdermerchantObj->getSingleFiledValues('',"order_id = '{$order_id}' ");
            $jsondata['final_amt'] = $Db_Ordermerchant['marchant_amt'];
            $jsondata['order_total'] = $Db_Ordermerchant['order_total'];

        }else{

            if(Buddha_Atom_Array::isValidArray($Db_Order)){
                $filedsarray= array('id','goods_name');

                if ($b_display == 1) {
                    $filedsarray[]='goods_img as img';
                } elseif ($b_display == 2) {
                    $filedsarray[]='goods_thumb as img';
                }

                $Db_Supply = $SupplyObj->getSingleFiledValues($filedsarray,"id='{$Db_Order['good_id']}' ");

                $orderproduct[0] = array(
                    'product_name'=>$Db_Supply['goods_name'],
                    'product_price'=>$Db_Order['goods_amt'],
                    'product_finalamt'=>$Db_Order['final_amt'],
                    'product_total'=>$Db_Order['order_total'],
                    'product_img'=>$host.$Db_Supply['img']);
                $jsondata['final_amt'] = $Db_Order['final_amt'];
                $jsondata['order_total'] = $Db_Order['order_total'];

            }
        }


        $jsondata['address_name'] = $Db_Address['name'];
        $jsondata['api_address'] = $api_address;
        $jsondata['api_orderstatus'] = $api_orderstatus;
        $jsondata['order_sn'] = $Db_Order['order_sn'];
        $jsondata['list'] = $orderproduct;



        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '商家订单详情');
    }




    /**
     * 订单 ：商家订单详情
     */
    public function merchantorderviewaaa(){
        //////////判断用户是否登录
        $UserObj = new User();
        if (Buddha_Http_Input::checkParameter(array('usertoken','ordermerchant_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
//        $user_id=9;
        //////////

        $merchantorder_id = (int)Buddha_Http_Input::getParameter('merchantorder_id');//商家订单表的ID

        $where = " om.id='{$merchantorder_id}' AND om.merchant_id='{$user_id}' AND o.buddhastatus=0 ";//传过来的订单ID=订单表的订单ID 并且当前登录的用户ID
        $sql = "SELECT op.id AS orderproduct_id,op.product_id,op.product_table,op.product_name,op.product_total,om.id AS  merchant_id,om.marchant_amt,om.marchant_amt,om.order_id,om.order_sn,om.product_idstr,om.createtime,o.pay_status,o.order_status
                FROM ({$this->prefix}ordermerchant  AS om  LEFT JOIN  {$this->prefix}order AS o ON om.order_id=o.order_sn)
                LEFT  JOIN {$this->prefix}orderproduct AS op ON  op.order_id=om.order_id
                WHERE {$where}";

        $Db_newsInfo = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata = $Db_newsInfo[0];

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '订单：个人订单详情');
    }
}