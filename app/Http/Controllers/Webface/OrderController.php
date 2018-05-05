<?php

/**
 * Class OrderController
 */
class OrderController extends Buddha_App_Action
{

    protected $tablenamestr;
    protected $tablename;
    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));


        $webface_access_token = Buddha_Http_Input::getParameter('webface_access_token');
        if ($webface_access_token == '') {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444002, 'webface_access_token必填');
        }

        $ApptokenObj = new Apptoken();
        $num = $ApptokenObj->getTokenNum($webface_access_token);
        if ($num == 0) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444003, 'webface_access_token不正确请从新获取');
        }
        $this->tablenamestr='个人订单';
        $this->tablename='order';
    }

    /**
     * 个人订单列表
     */
    public function personalordermore()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','groupid'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        $OrderObj=new Order();
        $CommonObj = new Common();
        $SupplyObj = new Supply();
        $UserObj=new User();
        if(Buddha_Atom_String::isValidString($usertoken)){
            if (Buddha_Http_Input::checkParameter(array('groupid'))) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
            }
            $groupid = (int)Buddha_Http_Input::getParameter('groupid');
            $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
            $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
            $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
            $user_id = $Db_User['id'];
        }

//        $OrderproductObj = new Orderproduct();
//        $supplyObj =  new Supply();


        $orderstatus = (int)Buddha_Http_Input::getParameter('orderstatus')?Buddha_Http_Input::getParameter('orderstatus'):0;
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        /*是否加入搜索时间条件；0 为否，1为是 */
        $api_isJoinTime = (int)Buddha_Http_Input::getParameter('api_istime')?Buddha_Http_Input::getParameter('api_isJoinTime'):0/**/;
        $api_keywordtime = (int)Buddha_Http_Input::getParameter('api_keywordtime') ? (int)Buddha_Http_Input::getParameter('api_keywordtime') : '';
        $api_keywordtime1 = (int)Buddha_Http_Input::getParameter('api_keywordtime1') ? (int)Buddha_Http_Input::getParameter('api_keywordtime1') : '';

        $page = (int)Buddha_Http_Input::getParameter('page') ? (int)Buddha_Http_Input::getParameter('page') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        $is_today = (int)Buddha_Http_Input::getParameter('is_today') ? (int)Buddha_Http_Input::getParameter('is_today') :0;

        /*搜索订单编号*/
        $api_keyword = (int)Buddha_Http_Input::getParameter('api_keyword')?(int)Buddha_Http_Input::getParameter('api_keyword') : '';

        $where = "(order_type='shopping' OR order_type='heartpro') AND good_id>0 AND good_table!='0' AND buddhastatus=0 AND isdel=0 ";
        $fields =' id AS order_id, order_sn, pay_status, createtimestr, good_id, good_table,order_type,goods_amt,order_total,final_amt';

        /*时间条件判断*/
        if($api_isJoinTime==1)
        {
            $where .= Buddha_Atom_Sql::getSqlByTimeIntervalString($api_keywordtime,$api_keywordtime1,'createtime',$is_today);
        }

        if(Buddha_Atom_String::isValidString($usertoken)){

            $public_where=$OrderObj->public_where($usertoken,$groupid);

            $WhereUser = " AND user_id='{$user_id}' ";

            $where .= $WhereUser.$public_where['where'];

            $fields .= $public_where['filed'];

        }


        /*关键字搜索*/
        $where .=Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($api_keyword,'order_sn');


        /* db mind
        *  $orderstatus == 0 代表全部；$orderstatus == 2  待发货；$orderstatus == 3  待收货;$orderstatus == 4  已完成
        *  pay_status==2 已付款 ； pay_status==1 付款中（表中的注释是付款中；其实是付款成功） ；pay_status==0未付款
        *  order_status=0待发货；order_status=1 已发货；order_status=2已收货；order_status=3 已退款
        */

        /* api mind
        $orderstatus=2 待发货 $orderstatus=3 已发货  $orderstatus=4 已完成
         */
        if($orderstatus == 2)
        {
            $where .= " and pay_status=1 and order_status=0 ";
        }elseif($orderstatus == 3)
        {
            $where .= " and pay_status=1 and order_status=1 ";
        }elseif($orderstatus == 4)
        {
            $where .= " and pay_status=1 and order_status=2 ";
        }


        $orderby = " order by id DESC ";

        $limit = Buddha_Tool_Page::sqlLimit($page, $pagesize);

        $jsondata = array();

        $tablewhere=$this->prefix.'order';

        $sql = "SELECT {$fields} 
                FROM {$this->prefix}order
                WHERE  {$where}{$orderby} {$limit}";

        $Db_Order = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);


        $jsondata['list'] = array();
        if(Buddha_Atom_Array::isValidArray($Db_Order))
        {

            foreach ($Db_Order as $k=>$v)
            {
                $table_filed = array();
                /** 获取订单对应商品的详情 ***/

                $supply_id = $v['good_id'];
                if($v['good_table']=='heartpro')
                {
                    if($b_display==2)
                    {
                        $tableimgfiledstr = 'small';

                    }elseif ($b_display==1)
                    {
                        $tableimgfiledstr = 'medium';
                    }
                    array_push($table_filed,"{$tableimgfiledstr} as img" );

                    $table = $this->db->getSingleFiledValues(array('table_id','small','name'),$v['order_type'],"id='{$v['good_id']}'");

//                    $supply_id = $table['table_id'];
//                    $table_Supply = $SupplyObj->getSingleFiledValues(array('goods_name'),"id='{$supply_id}'");
//                    $table['goods_name']=$Db_Supply['goods_name'];
                }elseif ($v['good_table']=='supply')
                {
                    $table_filed = array('id','goods_name as name');
                    if($b_display==2)
                    {
                        $supplyimgfiledstr = 'goods_thumb';

                    }elseif ($b_display==1)
                    {
                        $supplyimgfiledstr = 'goods_img';
                    }

                    array_push($table_filed,"{$supplyimgfiledstr} as img" );

                    $table = $SupplyObj->getSingleFiledValues($table_filed,"id='{$supply_id}'");

                }

                if(Buddha_Atom_Array::isValidArray($table)){

                    if(Buddha_Atom_String::isValidString($table['name']))
                    {
                        @$Db_Order[$k]['name'] = mb_substr($table['name'],0,10).'...';

                    }else{
                        @$Db_Order[$k]['name'] ='';
                    }

                    if(Buddha_Atom_String::isValidString($table['img'])){
                        $Db_Order[$k]['img'] = $host. $table['img'];
                    }else{
                        $Db_Order[$k]['img'] ='';
                    }

                }else{
                    @$Db_Order[$k]['name'] = '';
                    $Db_Order[$k]['img'] = '';
                }

                if($v['order_type'] == 'shopping')
                {
                    $Db_Order[$k]['type_name'] = '购物';

                }elseif($v['order_type'] == 'heartpro')
                {
                    $Db_Order[$k]['type_name'] = '1分购';

                }else{
                    $Db_Order[$k]['type_name'] = '';
                }

//                unset($Db_Order[$k]['good_table']);

                $Db_Order[$k]['services'] = $this->tablename.'.personalorderview';
                $Db_Order[$k]['param'] = array($this->tablename.'_id'=>$v[$this->tablename.'_id']);

                unset($Db_Order[$k]['order_type']);
            }


            $jsondata['list'] = $Db_Order;

            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);

            $jsondata['page'] = $temp_Common['page'];
            $jsondata['pagesize'] = $temp_Common['pagesize'];
            $jsondata['totalrecord'] = $temp_Common['totalrecord'];
            $jsondata['totalpage'] = $temp_Common['totalpage'];

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '个人订单列表');

    }


    /**
     * 个人订单详情
     */
    public function personalorderview()
    {

        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('usertoken','order_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $OrderObj = new Order();
        $SupplyObj = new Supply();
        $RegionObj = new Region();
        $OrderproductObj = new Orderproduct();
        $MysqlplusObj = new Mysqlplus();


        $order_id = (int)Buddha_Http_Input::getParameter('order_id');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$UserObj->isHasUserPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000021, '没有普通用户权限');

        }

        /*注意：千万不要在$WhereUser 的 user_id 前面加空格（WhereUser   当为多表时，只需要在紧挨$WhereUser的user_id得前面加入多表的< 简称和点 >就可以接入）*/
        $where = " o.order_type='shopping' AND o.buddhastatus=0 AND o.isdel=0 AND o.user_id='{$user_id}' AND o.id='{$order_id}' ";




        $sql  =" SELECT o.id AS order_id, o.order_sn, o.pay_status, o.order_status,
                    a.mobile, a.pro, a.city, a.area, a.address, a.name
                    
                FROM {$this->prefix}order AS o 
                        LEFT JOIN {$this->prefix}address as a  
                        ON o.addressid = a.id
                WHERE {$where} ";
        $Db_Order_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);


        if(Buddha_Atom_Array::isValidArray($Db_Order_arr)){
            $Db_Order_arr = $Db_Order_arr[0];
        }else{
            $Db_Order_arr = array('pro'=>0,'city'=>0,'area'=>0,'address'=>'');
        }

        $api_address=$RegionObj->getDetailOfAdrressByRegionIdStr($Db_Order_arr['pro'],$Db_Order_arr['city'],$Db_Order_arr['area']);
        $api_addressStr=$api_address.' '.$Db_Order_arr['address'];
        $Db_Order_arr['detailedAddress']=$api_addressStr;

        $Db_Order=$Db_Order_arr[0];

        unset($Db_Order['pro']);
        unset($Db_Order['city']);
        unset($Db_Order['area']);


        $orderproduct = array();

        if($OrderproductObj->isHasOrderproductRecord($Db_Order['id'])){

            $Db_Orderproduct = $OrderproductObj->getFiledValues(array('product_id','product_table','product_name','product_img','product_total','product_amt','merchant_amt'),"order_id='{$Db_Order['id']}'");
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
        }else{

            $Db_Order = $OrderObj->getSingleFiledValues(array('good_id','good_table','goods_amt','order_sn','final_amt','order_total','order_status'),"id='{$order_id}' and buddhastatus=0 and user_id='{$user_id}'");


            $Db_Supply = $SupplyObj->getSingleFiledValues(array('id','goods_name','market_price','promote_price','goods_thumb')," id='{$Db_Order['good_id']}' ");
            $orderproduct[0] = array('product_name'=>$Db_Supply['goods_name'],'product_price'=>$Db_Order['goods_amt'],'product_total'=>1,'product_img'=>$host.$Db_Supply['goods_thumb']);
        }

        $api_orderstatus = '';
        if($Db_Order['order_status'] == 0){
            $api_orderstatus = '待发货';
        }elseif($Db_Order['order_status'] == 1){
            $api_orderstatus = '已发货';
        }elseif($Db_Order['order_status'] == 2){
            $api_orderstatus = '已收货';
        }elseif($Db_Order['order_status'] == 3){
            $api_orderstatus = '已退款';
        }

        $jsondata = array();
        $jsondata['list'] =array();
        $jsondata['address_name'] = '';
        $jsondata['api_orderstatus'] = '';
        $jsondata['order_sn'] = '';
        $jsondata['final_amt'] = '';
        $jsondata['order_total'] = '';
        if(Buddha_Atom_Array::isValidArray($orderproduct)){
            $jsondata['address_name'] = $api_address;
            $jsondata['order_status'] = $Db_Order['order_status'];
            $jsondata['api_orderstatus'] = $api_orderstatus;
            $jsondata['order_sn'] = $Db_Order['order_sn'];
            $jsondata['final_amt'] = $Db_Order['final_amt'];
            $jsondata['order_total'] = $Db_Order['order_total'];
            $jsondata['list'] = $orderproduct;

        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '个人订单详情');
    }



    /**
     * 个人订单确认
     */
    public function personalorderconfirmation()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','order_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        $AddressObj = new Address();
        $RegionObj = new Region();
        $OrderObj = new Order();
        $HeartproObj = new Heartpro();
        $SupplyObj = new Supply();
        $UserObj=new User();

        $order_id = (int)Buddha_Http_Input::getParameter('order_id')?(int)Buddha_Http_Input::getParameter('order_id'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $Order_filed = array('id as order_id','good_id as product_id','good_table as product_table','goods_amt as product_price','final_amt as merchant_amt','order_total as product_total','addressid');

        $Order_where = "id='{$order_id}' AND user_id='{$user_id}' AND (order_type='shoping' OR order_type='heartpro')";

        $Db_Order = $OrderObj->getSingleFiledValues($Order_filed,$Order_where);

        if(!Buddha_Atom_Array::isValidArray($Db_Order))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000012, '订单内码ID不正确！');
        }

        $jsondata = array();
        /**↓↓↓↓↓↓↓↓↓↓↓ 查询产品信息 ↓↓↓↓↓↓↓↓↓↓↓**/
        if($Db_Order['good_table'] == 'heartpro')
        {
            $Db_Heartpro = $HeartproObj->getSingleFiledValues(array('id','table_id','table_name','goods_thumb','promote_price','market_price'),"id={$Db_Order['good_id']}");   //  获取购买商品的详情
            if(Buddha_Atom_Array::isValidArray($Db_Heartpro))
            {
                $Db_Table = $this->db->getSingleFiledValues(array('goods_name'),$Db_Heartpro['table_name'],"id='{$Db_Heartpro['table_id']}'");
            }
            $Db_Order['goods_name'] = $Db_Table['goods_name'];
            $Db_Order['goods_thumb'] = $Db_Heartpro['goods_name'];
        }else{
            $Db_Supply = $SupplyObj->getSingleFiledValues(array('id','table_id','table_name','goods_thumb','promote_price','market_price'),"id={$Db_Order['good_id']}");   //  获取购买商品的详情
        }
        /**↑↑↑↑↑↑↑↑↑↑ 查询产品信息 ↑↑↑↑↑↑↑↑↑↑**/


        /**↓↓↓↓↓↓↓↓↓↓↓ 查询收货地址信息 ↓↓↓↓↓↓↓↓↓↓↓**/

        if(Buddha_Atom_String::isValidString($Db_Order['addressid']))
        {
            $Address_where = "id='{$Db_Order['addressid']}' AND user_id='{$user_id}'";
        }else{
            $Address_where = "user_id='{$user_id}' AND isdef=0";
        }
        $Address_where = array('id as address_id','mobile','pro','city','area','address','name');
        $Db_Address = $AddressObj->getSingleFiledValues($Address_where,$Address_where);

        $Db_Region = $RegionObj->getDetailOfAdrressByRegionIdStr($Db_Address['pro'],$Db_Address['city'],$Db_Address['area']);

        $Db_Address['areastr'] = $Db_Region;
        /**↑↑↑↑↑↑↑↑↑↑ 查询收货地址信息 ↑↑↑↑↑↑↑↑↑↑**/

        $jsondata['order'] = $Db_Order;
        $jsondata['address'] = $Db_Order;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '个人订单确认');

    }









}