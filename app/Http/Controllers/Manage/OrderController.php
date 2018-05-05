<?php

/**
 * Class OrderController
 */
class OrderController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function more(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $OrderObj = new Order();
        $UserObj = new User();

        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;
        $p=(int)Buddha_Http_Input::getParameter('p');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $searchType = array (1 => '会员提现',2 => '财富列表');

        $where = " o.isdel=0 ";
        if($view) {
            $params['view'] = $view;
            switch ($view) {
                case 1:
                   $where .= " and o.order_type='money.out' "  ;
                  //  $where .= " and o.order_type='info.top' "  ;
                    break;

                case 2:

                    $where .= " and o.pay_status='1' and o.order_type!='money.out' "  ;
                    break;

            }
        }

        if($keyword){
            $where.=" and (o.order_sn like '%$keyword%' or  u.mobile like '%$keyword%')   ";
            $params['keyword'] = $keyword;
        }

          $sql ="select count(*) as total  from {$this->prefix}order as o left join {$this->prefix}user as u
    on o.user_id = u.id
    where {$where} ";


        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

         $rcount =$count_arr[0]['total'];

        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by o.id DESC ";
        $limit =Buddha_Tool_Page::sqlLimit ( $page, $pagesize );

         $sql ="select o.pay_status,o.id, o.createtime,o.order_sn,o.order_type,o.pay_type,o.final_amt,
               u.mobile,u.username,u.realname,u.banlance,u.Withamount

	 from {$this->prefix}order as o left join {$this->prefix}user as u
    on o.user_id = u.id
    where {$where} {$orderby}  {$limit}
";



        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $strPages =  Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=order&' .http_build_query($params).'&', $pagesize);

        $this->smarty->assign('rcount', $rcount);
        $this->smarty->assign('pcount', $pcount);
        $this->smarty->assign('page', $page);
        $this->smarty->assign('strPages', $strPages);
        $this->smarty->assign('list', $list);
        $this->smarty->assign( 'params', $params );
        $this->smarty->assign('view',$view);

        $this->smarty->assign('searchType',$searchType);
        $this->smarty->assign( 'params', $params );
        $this->smarty->assign( 'keyword', $keyword );


        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/
        $BankObj = new Bank();
        $BillObj = new Bill();
        $OrderObj = new Order();
        $UserObj = new User();
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;
        $page=(int)Buddha_Http_Input::getParameter('p');
        $id=(int)Buddha_Http_Input::getParameter('id');
        $remark=Buddha_Http_Input::getParameter('remark');
        $pay_status=(int)Buddha_Http_Input::getParameter('pay_status');
        $Db_Order=$OrderObj->fetch($id);
        $user_id = $Db_Order['user_id'];
        $order_id=$Db_Order['id'];
        $order_sn=$Db_Order['order_sn'];
        $order_type=$Db_Order['order_type'];
        $order_desc = $BillObj->getBillOrderdescByOrderType($order_type);
        $banlance=$Db_Order['final_amt'];
        //获取绑定的银行卡信息
        $bankinfo = $BankObj->getSingleFiledValues('',"uid={$user_id}");
        if($pay_status){
            $OrderObj->edit(array('pay_status'=>1),$id);
        }

        if($view ==1 and $pay_status){
            $orient='-';
            //判断是不是已经分润 防止重复分润
            $billnum = $BillObj->countRecords(" isdel=0
                     and user_id='{$user_id}' and order_id='{$order_id}' ");
            if($billnum==0){
                //进行账单记录
                $data = array();
                $data['user_id']=$user_id;
                $data['order_sn']=$order_sn;
                $data['order_id'] = $order_id;
                $data['is_order']=1;
                $data['order_type']=$order_type;
                $data['order_desc']=$order_desc;
                $data['is_order']=1;
                $data['orient']=$orient;
                $data['billamt']=$orient.$banlance;
                $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                $insert_id =$BillObj->add($data);
            }
            if($remark){
                echo $remark;
                exit;
            }

        }

        $Db_Order=$OrderObj->fetch($id);
        $this->smarty->assign('view',$view);
        $this->smarty->assign('page',$page);

        $user_id = $Db_Order['user_id'];
        $Db_User = $UserObj->fetch($user_id);

        $this->smarty->assign('order',$Db_Order);
        $this->smarty->assign('user',$Db_User);
        $this->smarty->assign('bankinfo',$bankinfo);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    //后台管理员操作企业付款
    public function surepay(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/
        $page=(int)Buddha_Http_Input::getParameter('page');
        $order_id=(int)Buddha_Http_Input::getParameter('order_id');
        $view=Buddha_Http_Input::getParameter('view');
        $UserObj = new User();
        $OrderObj = new Order();
        $CompanypayObj = new Companypay();
        $MoneytouserObj = new Moneytouser();
        $orderinfo = $OrderObj->getSingleFiledValues('',"id='{$order_id}'");
        $moneytouserinfo = $MoneytouserObj->getSingleFiledValues('',"user_id='{$orderinfo['user_id']}' ORDER BY id DESC");
        if($orderinfo['final_amount'] >= 1){
            $payable = $orderinfo['final_amount'];
        }else{
            $payable = $orderinfo['total_amount'];
        }
        $userinfo = $UserObj->getSingleFiledValues('',"id='{$orderinfo['user_id']}'");

        $openId = $userinfo['openid'];
        $desc = '提现';
        $pay_callback = $CompanypayObj->payToUser($openId,$desc,$payable * 100);
        $pay_callback_xml = @simplexml_load_string($pay_callback,NULL,LIBXML_NOCDATA);
        $pay_callback_arr = json_decode(json_encode($pay_callback_xml),true);
        if ($pay_callback_arr && $pay_callback_arr['result_code'] != 'FAIL' && 'success' == strtolower($pay_callback_arr['return_code'])){
            $data = array();
            $data['pay_status'] = 1;
            $OrderObj->updateRecords($data,"id='{$order_id}'");
            $data = array();
            $data['Withamount'] = $userinfo['Withamount'] + $orderinfo['total_amount'];
            $UserObj->updateRecords($data,"id='{$orderinfo['user_id']}'");
        }



    }


    public function  export(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $OrderObj = new Order();
        $UserObj = new User();

        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;
        $p=(int)Buddha_Http_Input::getParameter('p');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $searchType = array (1 => '会员提现',2 => '财富列表');

        $where = " o.isdel=0 ";
        if($view) {
            $params['view'] = $view;
            switch ($view) {
                case 1:
                    $where .= " and o.order_type='money.out' "  ;
                    //  $where .= " and o.order_type='info.top' "  ;
                    break;

                case 2:

                    $where .= " and o.pay_status='1' and o.order_type!='money.out' "  ;
                    break;

            }
        }

        if($keyword){
            $where.=" and (o.order_sn like '%$keyword%' or  u.mobile like '%$keyword%')   ";
            $params['keyword'] = $keyword;
        }

        $sql ="select count(*) as total  from {$this->prefix}order as o left join {$this->prefix}user as u
    on o.user_id = u.id
    where {$where} ";


        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $rcount =$count_arr[0]['total'];

        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by o.id DESC ";
        $limit =Buddha_Tool_Page::sqlLimit ( $page, $pagesize );

        $sql ="select o.pay_status,o.id, o.createtime,o.order_sn,o.order_type,o.pay_type,o.final_amt,
               u.mobile,u.username,u.realname

	 from {$this->prefix}order as o left join {$this->prefix}user as u
    on o.user_id = u.id
    where {$where} {$orderby}  {$limit}
";



        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);;

        $str = "订单号\t订单类型\t用户名\t收货人\t手机号\t付款状态\t订单日期\n";
        $str = iconv('utf-8','gb2312',$str);
        foreach($list as $k=>$row) {
            $order_sn=$row['order_sn'];
            $order_type = $row['order_type'];
            if($order_type=='money.out'){
                $order_type='会员提现';
            }
            if($order_type=='shop.v'){
                $order_type='店铺认证';
            }
            if($order_type=='info.top'){
                $order_type='信息置顶';
            }
            if($order_type=='info.market'){
                $order_type='跨区域信息推广';
            }
            if($order_type=='info.see'){
                $order_type='信息查看';
            }

            $order_type = iconv('utf-8', 'gb2312', $order_type);

            $username =iconv('utf-8', 'gb2312', $row['username']);
            $realname =iconv('utf-8', 'gb2312', $row['realname']);
            $mobile =iconv('utf-8', 'gb2312', $row['mobile']);

            $pay_status= $row['pay_status'];
            if($pay_status==0) {
                $pay_status='未付款';
            }
            if($pay_status=1) {
                $pay_status='已付款';
            }

            $pay_status =iconv('utf-8', 'gb2312', $pay_status);

            $createtime=date("Y-m-d",$row['createtime']) ;

            $str .= $order_sn."\t".$order_type."\t".$username."\t".$realname."\t".$mobile."\t".$pay_status."\t".$createtime."\n";

        }


        $filename = 'order.'.date('YmdHis').'.xls'; //设置文件名
      echo   Buddha_Tool_File::exportExcel($filename,$str);//导出

        die();








    }


}