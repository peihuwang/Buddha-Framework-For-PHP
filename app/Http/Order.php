<?php
class Order extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    /**
     * 根据订单表中的order_type数值显示订单的标题信息
     * @param $order_type
     * @return string
     * @author wph 2017-09-14
     */
    public function getOrderTypeNameByOrderTypeStr($order_type){
        $Orderdesc ='预支付订单如下';
        if($order_type=='shop.v'){
            $Orderdesc='店铺认证';
        }

        if($order_type=='info.top'){
            $Orderdesc='置顶';
        }

        if($order_type=='info.market'){
            $Orderdesc='跨区域推广';
        }
        if($order_type=='info.see'){
            $Orderdesc='信息查看';
        }
        if($order_type=='money.out') {
            $Orderdesc = '会员提现';
        }
        if($order_type=='shopping') {
            $Orderdesc = '商品交易收入';
        }

        return $Orderdesc;
    }

    /**
     * @param int $is_area
     * @return string
     * @author csh
     * 查询信息 的公共条件（必须为第一条件）
     * 对应角色显示的公共条件和对应角色要显示的公共字段
     */

    function public_where($usertoken,$usergroupid=0){


        if(!empty($usertoken)){
            if($usergroupid==1){

                /*商家*/
                $where=' ';
                $public_filed=' ';
            }elseif($usergroupid==4){
                /*普通会员*/
                $where=' ';
                $public_filed=' ';
            }elseif($usergroupid==2){

                /*代理商*/
                $where='';
                $public_filed=' ';
            }elseif($usergroupid==3){

                /*合伙人*/
                $where='';
                $public_filed=' ';
            }
        }else{

            /*首页*/
            $where='';
            $public_filed=' ';
        }

        $publicarray['where']=$where;
        $publicarray['filed']=$public_filed;



        return $publicarray;
    }





    /**
     * @param $api_number
     * @return string
     * @author csh
     */
    public function queryRegion($addressid){
        if($addressid == 0){
            return '';
        }
        $AddressObj=new Address();
        $api_address='';
        $Db_Address = $AddressObj->getSingleFiledValues('',"id='{$addressid}' ");
        if($Db_Address['pro']){
            $pro = $this->getSingleFiledValues(array('name'),"id='{$Db_Address['pro']}' ");
            $api_address .= $pro['name'].'省';
        }
        if($Db_Address['city']){
            $city = $this->getSingleFiledValues(array('name'),"id='{$Db_Address['city']}' ");
            $api_address .= ' '.$city['name'].'市';
        }
        if($Db_Address['area']){
            $area = $this->getSingleFiledValues(array('name'),"id='{$Db_Address['area']}' ");
            $api_address .= ' '.$area['name'];
        }

        if(Buddha_Atom_String::isValidString($Db_Address['address']))
            $api_address .= $Db_Address['address'];

        return $api_address;
    }










    public  static function birthOrderId($uid){
        $uid = (int)$uid;
        $nowtime = time();
        $nowtimestr = date('YmdHis',$nowtime);//14
        $str_uid=str_pad($uid,12,"0",STR_PAD_LEFT);//12
        return $nowtimestr.''.$str_uid;

    }



    public function givePartnerOrderProfit($order_id){

        $UserObj = new User();
        $BillObj = new Bill();
         $num = $this->countRecords( " isdel=0
          and id='{$order_id}'
          and pay_status=1
         ");

        if($num){
            $Db_Order = $this->getSingleFiledValues(''," isdel=0
        and id='{$order_id}'  and pay_status=1");

            $order_id = $Db_Order['id'];
            $order_sn = $Db_Order['order_sn'];
            $order_type = $Db_Order['order_type'];
            $final_amt = $Db_Order['final_amt'];
             $order_desc=$BillObj->getBillOrderdescByOrderType($order_type);

            $banlance=$Db_Order['money_partner'];
            if($final_amt<0){
                $orient='-';
            }else{
                $orient='+';
            }
            //业务员
             $user_id = $referral_id = $Db_Order['referral_id'];
            if($user_id and abs($final_amt)>0  and abs($banlance)>0){
                //会员是否存在
                 $usernum = $UserObj->countRecords(" isdel=0
                and id='{$user_id}'  ");
                 if($usernum){
                     //判断是不是已经分润 防止重复分润
                   $billnum = $BillObj->countRecords(" isdel=0
                     and user_id='{$user_id}' and order_id='{$order_id}'  and orient='{$orient}' ");
                   if($billnum){
                       return 0;
                   }else{
                       //进行分润前先插入分润账单记录
                       $data = array();
                       $data['user_id']=$user_id;
                       $data['order_id']=$order_id;
                       $data['order_sn']=$order_sn;
                       $data['user_id']=$user_id;
                       $data['is_order']=1;
                       $data['order_type']=$order_type;
                       $data['order_desc']=$order_desc;
                       $data['is_order']=1;
                       $data['orient']=$orient;
                       $data['billamt']=$banlance;
                       $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                       $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                       $insert_id =$BillObj->add($data);
                       if($insert_id){
                           //给会员余额进行调整
                           $prefix = $this->prefix;
                           $this->db->query("UPDATE {$prefix}user SET  banlance=banlance+{$banlance} WHERE id='{$user_id}'  ");


                       }else{
                           return 0;
                       }

                   }

                 }else{
                    return 0;
                 }


            }else{
              return 0;
            }



        }



    }

    /**
     * e网通付费360，代理商分润百分之十
     *$agent_id 代理商id
     *$money  代理商分润
     *$order_id  订单id
     */

    public function giveAgentOrderProfitTenPercent($agent_id,$money,$order_id){
        $UserObj = new User();
        $BillObj = new Bill();
        if($agent_id and abs($money)>0){
            //会员是否存在
            $usernum = $UserObj->countRecords(" isdel=0 and id='{$agent_id}'  ");
            $Db_Order = $this->getSingleFiledValues(''," isdel=0 and id='{$order_id}'  and pay_status=1");
            $order_sn = $Db_Order['order_sn'];
            $order_type = $Db_Order['order_type'];
            $order_desc=$BillObj->getBillOrderdescByOrderType($order_type);
            if($usernum){
                //判断是不是已经分润 防止重复分润
                  $billnum = $BillObj->countRecords(" isdel=0
                 and user_id='{$agent_id}' and order_id='{$order_id}' ");
                if($billnum){
                   return 0;
                }else{
                    //进行分润前先插入分润账单记录
                    $data = array();
                    $data['user_id']=$agent_id;
                    $data['order_sn']=$order_sn;
                    $data['order_id'] = $order_id;
                    $data['order_type']=$order_type;
                    $data['order_desc']=$order_desc;
                    $data['is_order']=1;
                    $data['orient']='+';
                    $data['billamt']=$money;
                    $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                    $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];

                    $insert_id =$BillObj->add($data);
                    if($insert_id){
                        //给会员余额进行调整
                        $prefix = $this->prefix;
                        $this->db->query("UPDATE {$prefix}user SET  banlance=banlance+{$money} WHERE id='{$agent_id}'  ");
                    }else{
                      return 0;
                    }

                }

            }else{
               return 0;
            }
        }
    }

    public function giveAgentOrderProfit($order_id){

        $UserObj = new User();
        $BillObj = new Bill();
         $num = $this->countRecords( " isdel=0
          and id='{$order_id}'
          and pay_status=1
         ");
        if($num){
            $Db_Order = $this->getSingleFiledValues(''," isdel=0
        and id='{$order_id}'  and pay_status=1");
            $order_id = $Db_Order['id'];
            $order_sn = $Db_Order['order_sn'];
            $order_type = $Db_Order['order_type'];
            $final_amt = $Db_Order['final_amt'];
            $order_desc=$BillObj->getBillOrderdescByOrderType($order_type);
            $banlance=$Db_Order['money_agent'];
            if($final_amt<0){
                $orient='-';
            }else{
                $orient='+';
            }
            //代理商
            $user_id = $agent_id = $Db_Order['agent_id'];

            if($user_id and abs($final_amt)>0  and abs($banlance)>0){
                //会员是否存在

                   $usernum = $UserObj->countRecords(" isdel=0 and
               id='{$user_id}'  ");

                if($usernum){
                    //判断是不是已经分润 防止重复分润
                      $billnum = $BillObj->countRecords(" isdel=0
                     and user_id='{$user_id}' and order_id='{$order_id}' ");
                    if($billnum){
                       return 0;
                    }else{
                        //进行分润前先插入分润账单记录
                        $data = array();
                        $data['user_id']=$user_id;
                        $data['order_sn']=$order_sn;
                        $data['order_id'] = $order_id;
                        $data['order_type']=$order_type;
                        $data['order_desc']=$order_desc;
                        $data['is_order']=1;
                        $data['orient']=$orient;
                        $data['billamt']=$banlance;
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];

                        $insert_id =$BillObj->add($data);
                        if($insert_id){
                            //给会员余额进行调整
                            $prefix = $this->prefix;
                            $this->db->query("UPDATE {$prefix}user SET  banlance=banlance+{$banlance} WHERE id='{$user_id}'  ");


                        }else{
                          return 0;
                        }

                    }

                }else{
                   return 0;
                }


            }else{
              return 0;
            }



        }





    }

    public function givePlatOrderProfit($order_id){

        $BillObj = new Bill();
         $num = $this->countRecords( " isdel=0
          and id='{$order_id}'
          and pay_status=1
         ");

        if($num) {
            $Db_Order = $this->getSingleFiledValues('', " isdel=0
        and id='{$order_id}'  and pay_status=1 ");

            $order_id = $Db_Order['id'];
            $order_sn = $Db_Order['order_sn'];
            $order_type = $Db_Order['order_type'];
            $final_amt = $Db_Order['final_amt'];
            $order_desc = $BillObj->getBillOrderdescByOrderType($order_type);
            $banlance = $Db_Order['money_plat'];

            if ($final_amt < 0) {
                $orient = '-';
            } else {
                $orient = '+';
            }
            //平台
            $user_id = 0;

            if (abs($final_amt) > 0 and abs($banlance) > 0) {

                //判断是不是已经分润 防止重复分润

                  $billnum = $BillObj->countRecords(" isdel=0
                     and user_id='{$user_id}' and order_id='{$order_id}' and  orient='{$orient}' ");




                if ($billnum) {
                    //////return 0;
                } else {
                    //进行分润前先插入分润账单记录
                    $data = array();
                    $data['user_id'] = 0;
                    $data['order_sn'] = $order_sn;
                    $data['order_id'] = $order_id;
                    $data['order_type'] = $order_type;
                    $data['order_desc'] = $order_desc;
                    $data['is_order'] = 1;
                    $data['orient'] = $orient;
                    $data['billamt'] = $banlance;
                    $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                    $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                    $insert_id = $BillObj->add($data);


                }


            }


        }

    }


    public function batchOrderShareProfit($order_id){

        $num = $this->countRecords( " isdel=0
          and id='{$order_id}'
          and pay_status=1
         ");


        if($num){

            //通过订单号给业务员进行分润
             $this->givePartnerOrderProfit($order_id);

            //通过订单号给代理商进行分润
             $this->giveAgentOrderProfit($order_id);
            //通过订单号给平台进行分润
             $this->givePlatOrderProfit($order_id);
        }
    }


    public function setOrderPaid($order_id_or_order_sn)
    {
         $PaylogObj = new Paylog();
         $BillObj = new Bill();
         $CommonObj = new Common();
         $UserObj = new User();
         $where = " isdel=0 AND (id='{$order_id_or_order_sn}' OR order_sn='{$order_id_or_order_sn}') ";

       /////////debug  $this->edit(array('pay_status'=>0),36);

         $num = $this->db->countRecords('order',$where.' AND pay_status=0 ');

         if($num)
         {
             $Db_Order = $this->db->getSingleFiledValues('','order',$where);
             if ($Db_Order && $Db_Order['pay_status'] == 0)
             {
                  /* 修改此次支付操作的状态为已付款*/
             //   $paylogid = $PaylogObj->getPaylogid($order_id_or_order_sn);
               //  if($paylogid){
              //       $PaylogObj->edit(array('is_pay'=>1),$paylogid);

                     //处理逻辑关系 例如
                     $order_type = $Db_Order['order_type'];
                     $order_pay_user_id = $Db_Order['user_id'];//
                     $order_pay_good_id = $Db_Order['good_id'];//
                     $good_table = $Db_Order['good_table'];
                     $good_id = $Db_Order['good_id'];
                     $user_id = $Db_Order['user_id'];
                     $order_id = $Db_Order['id'];
                     $order_sn = $Db_Order['order_sn'];
                     $final_amt = $Db_Order['final_amt'];
                     $order_desc = $BillObj->getBillOrderdescByOrderType($order_type);

                     //付款我加个条账单
                     $billnum = $BillObj->countRecords(" isdel=0
                     and user_id='{$user_id}' and order_id='{$order_id}' ");
                      if ($billnum==0 and $user_id and $order_type != 'shopping'){
                          $data = array();
                          $data['user_id'] = $user_id;
                          $data['order_sn'] = $order_sn;
                          $data['order_id'] = $order_id;
                          $data['order_type'] = $order_type;
                          $data['order_desc'] = $order_desc;
                          $data['is_order'] = 1;
                          $data['orient'] = '-';
                          $data['billamt'] = '-'.$final_amt;
                          $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                          $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                          $insert_id = $BillObj->add($data);
                     }
                     if($order_type=='shop.v'){
                         $ShopObj = new Shop();
                         $veifytime = Buddha::$buddha_array['buddha_timestamp'];

                         $veryfyendtime =  strtotime("+1 year");
                         $veryfyendtimestr = date('Y-m-d :H:i:s',$veryfyendtime);
                         $ShopObj->edit(array('is_verify'=>1,
                                 'veifytime'=>$veifytime,
                                 'veryfyendtime'=>$veryfyendtime,
                                 'veryfyendtimestr'=>$veryfyendtimestr)
                             ,$good_id);

                     }

                     if($order_type=='info.top'){
                         $createtime = Buddha::$buddha_array['buddha_timestamp'];
                         $createtimestr = Buddha::$buddha_array['buddha_timestr'];
                         //
//                         if($good_table=='shop'){
//                             $data =array('createtime'=>$createtime);
//                         }else{
//                             $data =array('add_time'=>$createtime);
//                         }
                         $data =array('toptime'=>$createtime,'toptimestr'=>$createtimestr);
                         $this->db->updateRecords( $data, $good_table,"id ={$good_id}" );

                     }
                    if($order_type=='e_netcom'){
                        if($good_table=='userfee'){
                            $UserfeeObj = new Userfee();
                            $data = array();
                            $data['ispay'] = 1;
                            $data['starttime'] = Buddha::$buddha_array['buddha_timestamp'];
                            $data['starttimestr'] = Buddha::$buddha_array['buddha_timestr'];
                            $data['endtime'] = mktime(0, 0, 0, date('m'), date('d'), date('Y')+1);
                            $data['endtimestr'] = date("Y-m-d H:i:s", strtotime("+1 year"));
                            $UserfeeObj->updateRecords( $data,"id ='{$good_id}'" );
                        }
                        $Db_agentrate = $UserObj->getSingleFiledValues(array('id', 'agentrate'), "isdel=0 and level3='{$Db_Order['make_level3']}'and groupid=2");

                        if($final_amt == 990){
                            $final_amt = 360;
                            $money = 630 + 36;
                            $this->giveAgentOrderProfitTenPercent($Db_agentrate['id'],$money,$order_id);
                        }elseif($final_amt == 360){
                            $maney = 360 *0.1;
                            $this->giveAgentOrderProfitTenPercent($Db_agentrate['id'],$money,$order_id);
                        }
                        $CommonObj->getGlobalProfitDistribution($user_id,$final_amt,$order_sn,$order_id);
                    }

               //////  }

                 $order_id = $Db_Order['id'];
                $this->edit(array('pay_status'=>1,'isdel'=>0),$order_id);

                 /**↓↓↓↓↓↓↓↓↓↓↓ 如果为 1分购 就 更新 减库存 和 已购买 ↓↓↓↓↓↓↓↓↓↓↓**/
                if($Db_Order['order_type']=='heartpro' )
                {
                    $HeartproObj = new Heartpro();
                    $HeartapplyObj = new Heartapply();

                    /**↓↓↓↓↓↓↓↓↓↓↓ 减库存 ↓↓↓↓↓↓↓↓↓↓↓**/
                    $HeartproObj->changeStockByOrderIdOrOrderSn($order_id);
                    /**↑↑↑↑↑↑↑↑↑↑  减库存  ↑↑↑↑↑↑↑↑↑↑**/

                    /**↓↓↓↓↓↓↓↓↓↓↓ 已购买  ↓↓↓↓↓↓↓↓↓↓↓**/
                    $Db_table = $this->db->getSingleFiledValues(array('id'),'heartapply',"user_id='{$order_pay_user_id}' AND heartpro_id='{$order_pay_good_id}'");
                    $Heartapply_data['is_buy'] = 1;
                    $HeartapplyObj->updateRecords($Heartapply_data," id='{$Db_table['id']}' AND user_id='{$order_pay_user_id}'" );
                    /**↑↑↑↑↑↑↑↑↑↑  已购买  ↑↑↑↑↑↑↑↑↑↑**/
                }
                 /**↑↑↑↑↑↑↑↑↑↑ 如果为 1分购 就 更新 减库存 和 已购买 ↑↑↑↑↑↑↑↑↑↑ **/
                
                /* 根据订单号进入订单批量分润函数 */
                if($order_type=='info.see' || $order_type=='info.top'){
                    $this->batchOrderShareProfit($order_id);
                }
            }
        }
    }

 public function Remote_order($shop_id,$uid,$house_id,$level,$table='lease',$money=0.2){
     $ShopObj=new Shop();
     $OrderObj=new Order();
     $Db_referral=$ShopObj->getSingleFiledValues(array('referral_id','partnerrate','agent_id','agentrate','level0','level1','level2','level3'),"id='{$shop_id}' and user_id='{$uid}' and isdel=0");
     $getMoneyArrayFromShop = $ShopObj->getMoneyArrayFromShop($house_id,$money);
     $data=array();
     $data['good_id']=$house_id;
     $data['user_id']=$uid;
     $data['order_sn']= $OrderObj->birthOrderId($uid);
     $data['good_table']=$table;
     $data['pay_type']='third';
     $data['order_type'] = 'info.market';
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
     $data['make_level0']=1;
     $data['make_level1']=$level[0];
     $data['make_level2']=$level[1];
     $data['make_level3']=$level[2];
     $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
     $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
     $order_id= $OrderObj->add($data);
     $datas['isok']='true';
     $datas['data']='商品添加成功,去支付。';
     $datas['url']='/topay/wxpay/wxpayto.php?order_id='.$order_id;
     return $datas;
 }


    /**
     * @param $Db_pay_status
     * @param $Db_order_status
     * @return int
     * @author csh
     * api mind $api_orderstatus=2 待发货 $api_orderstatus=3 已发货  $api_orderstatus=4 已完成 $api_orderstatus=0 未付款
     * param int $Db_pay_status    付款状态
     * param int $Db_order_status  订单状态
     */
   public function getApiOrderStatus($Db_pay_status,$Db_order_status)
   {

       $pay_status = (int)$Db_pay_status;
       $order_status = (int)$Db_order_status;
       if($pay_status==1 and $order_status==0){
           return 2;
       }
       elseif($pay_status==1 and $order_status==1)
       {
           return 3;
       }
       elseif($pay_status==1 and $order_status==2)
       {
           return 4;
       }
       else{
           return 0;
       }
   }









}