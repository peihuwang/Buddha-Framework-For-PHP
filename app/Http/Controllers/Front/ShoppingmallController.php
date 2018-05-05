<?php
/**
 * Class ShoppingmallController
 */
class ShoppingmallController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $RegionObj=new Region();
        $ShopObj=new Shop();
        $GoodsObj=new Goods();
        $GoodscatObj = new Goodscat();
        $cid = Buddha_Http_Input::getParameter('cid');
        $getcategory =$GoodscatObj->getcategory();
        if($cid){
            $insql = $GoodscatObj->getInSqlByID($getcategory,$cid);
        }

        $locdata = $RegionObj->getLocationDataFromCookie();
        $act=Buddha_Http_Input::getParameter('act');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $cat_id=Buddha_Http_Input::getParameter('cat_index');
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;
        $CartObj = new Cart();
        $cartNum = $CartObj->countRecords("user_id='{$uid}' AND goods_table='goods' ");
        if($act=='list') {
            $where = " isdel=0 and is_sure=1 and type=0 and  buddhastatus=0 {$locdata['sql']} ";
            if ($view) {
                switch ($view) {
                    case 2;
                       // $where .=" and add_time ";//最新
                        break;
                    case 3;
                        $where .= " and is_hot=1";//热门
                        break;
                    case 4;
                        $where .= " and is_promote=1";//促销
                        break;
                }
            }
            if($cat_id){
                if($cat_id == 1){
                    $where .= " and is_rec=1 ";  
                }else{
                    $where .= " and goodscat_id='{$cat_id}' ";
                }
            	
            }
            if ($keyword) {
                $where .= " and goods_name like '%$keyword%'";
            }

           if($cid){
               $where.=" and  goodscat_id IN {$insql}";
           }


            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
            $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') : 16;

            $orderby = " order by add_time DESC ";
            $fields = array('id', 'shop_id', 'goods_name', 'market_price', 'promote_price', 'is_promote', 'promote_start_date', 'promote_end_date', 'goods_thumb');
            $list = $this->db->getFiledValues ($fields,  $this->prefix.'goods', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
            $nwstiem=time();
            foreach($list as $k=>$v){
                if($v['is_promote']==1){
                    if($nwstiem>$v['promote_start_date'] and $v['promote_end_date']>$nwstiem){
                        $price=$v['promote_price'];
                    }else{
                        $price= $v['market_price'];
                    }
                }else{
                    $price= $v['market_price'];
                }
                $goodsNws[]=array(
                    'id'=>$v['id'],
                    'goods_name'=>$v['goods_name'],
                    'is_promote'=>$v['is_promote'],
                    'price'=>$price,
                    'shop_name'=>$Db_shop['name'],
                    'distance'=>$distance,
                    'roadfullname'=>$roadfullname,
                    'goods_thumb'=>$v['goods_thumb'],
                );
            }
            $data=array();
            if($goodsNws){
                $data['isok']=1;
                $data['list']=$goodsNws;
                $data['data']='加载完成';
            }else{
                $data['isok']=0;
                $data['list']='';
                $data['data']='没数据了';
            }
            Buddha_Http_Output::makeJson($data);
        }

        //商城推荐
        $recommend = $GoodsObj->getFiledValues(array('id','goods_thumb')," is_promote=0 AND buddhastatus=0 AND is_sure=1 AND isdel=0 {$locdata['sql']} AND is_hot=1 ORDER BY id DESC LIMIT 0,3" );

        //人气商品
        $sales = $GoodsObj->getFiledValues(array('id','shop_id','goods_name','market_price','promote_price','goods_thumb')," is_promote=0 AND buddhastatus=0 AND is_sure=1 AND isdel=0 {$locdata['sql']} ORDER BY click_count DESC LIMIT 0,6" );
        $goodscatinfo=$GoodscatObj->goods_cats();
        $this->smarty->assign('cartNum',$cartNum);
        $this->smarty->assign('recommend',$recommend);
        $this->smarty->assign('sales',$sales);
        $this->smarty->assign('goodscatinfo',$goodscatinfo);
        $this->smarty->assign('view',$view);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


	public function ajax_info(){

        /*****************************
         * 1、获取当前的区域
         * 2、判断当前区域有没有代理商和该区域有没有添加图片
         * 3、添加了就显示该区域的图片；没有添加就显示系统默认的图片
         * 4、判断是第几块  (P)
         * 5、列表
         *********************************/
        $p = Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p'): 1;
        $type_id = Buddha_Http_Input::getParameter('cat_id')?(int)Buddha_Http_Input::getParameter('cat_id'): 0;
        $cat_index = Buddha_Http_Input::getParameter('cat_index')?(int)Buddha_Http_Input::getParameter('cat_index'): 0;
        $ImageObj=new Image();
////////////////////////////////////↓↓↓↓↓↓轮播图区域↓↓↓↓↓↓↓↓//////////////////////////////////////////////////////////////

        $image = $ImageObj->DB_img($cat_index,$type_id);

///////////////////////////////////↑↑↑↑↑轮播图区域↑↑↑↑↑↑↑////////////////////////////////////////5//////////////////////

///////////////////////////////////↓↓↓↓↓↓列表↓↓↓↓↓↓↓↓//////////////////////////////////////////////////////////////
        $goodsNws= $this->ajax_info_list($type_id,$p);
///////////////////////////////////↑↑↑↑↑↑列表↑↑↑↑↑↑///////////////////////////////////////////////////////////////
        $data=array(
            'image'=>$image,
            'goods'=>$goodsNws,
        );


        Buddha_Http_Output::makeJson($data);
    }

    public function detil(){        
        $GoodsObj=new Goods();
        $RegionObj=new Region();
        $GoodsimagesObj=new Goodsimages();
        $ShopObj=new Shop();
        $OrderObj=new Order();
        $locdata = $RegionObj->getLocationDataFromCookie();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $user_id = (int)Buddha_Http_Cookie::getCookie('uid');
        $order_id=(int)Buddha_Http_Input::getParameter('order_id');
        if(!$id){
            Buddha_Http_Head::redirectofmobile('链接参数错误！','index.php?a=index&c=shoppingmall',2);
        }
        $goods= $GoodsObj->fetch($id);
        if(!$goods){
            Buddha_Http_Head::redirectofmobile('信息不存在或已删除！','index.php?a=index&c=shoppingmall',2);
        }
        $gsllery=$GoodsimagesObj->getFiledValues(array('goods_img'),"goods_id='$id'");
        $shopinfo= $ShopObj->getSingleFiledValues(array('id','user_id','small','name','is_verify','specticloc','lat','lng','mobile','createtimestr','level2','level3','is_verify'),"id='{$goods['shop_id']}' and isdel=0");
        if($shopinfo['level2']){
            $citys = $RegionObj->getSingleFiledValues('name',"id={$shopinfo['level2']}");
            $shopinfo['level2'] = $citys['name'];
        }
        if($shopinfo['level3']){
           $xian = $RegionObj->getSingleFiledValues('name',"id={$shopinfo['level3']}");
           $shopinfo['level3'] = $xian['name'];
        }
        $lat1=$locdata['lat'];
        $lng1=$locdata['lng'];

        //产品属性
        $GoodspecObj = new Goodspec();
        $GoodsproductObj = new Goodsproduct();
        $attr = $GoodspecObj->getSingleFiledValues(''," good_id='{$id}' AND good_table='supply'");
        if(Buddha_Atom_Array::isValidArray($attr)){
            if(stripos($attr['attrvalue1'],'|')){
                $attr['attrvalue1'] = explode('|', $attr['attrvalue1']);
            }
            if(stripos($attr['attrvalue2'],'|')){
                $attr['attrvalue2'] = explode('|', $attr['attrvalue2']);
            }
        }
        //商品子表
        $goodsson = $GoodsproductObj->getFiledValues('',"goods_id='{$id}' AND goodspec_id='{$attr['id']}'");

        foreach($goodsson as $k=>$v){
            if($v['stock']){
                $stockNum += $v['stock'];
            }
        }
        $distance=$RegionObj->getDistance($lat1,$lng1,$shopinfo['lat'],$shopinfo['lng'],2);
        @$shopinfo['distance']=$distance;
        $data=array();
        $data['click_count']=$goods['click_count']+1;
        $GoodsObj->edit($data,$id);
        $start = time()-15*60;
        if($user_id){
            $see= $OrderObj->countRecords("user_id='{$user_id}' and good_id='{$id}' and pay_status=1 and createtime>=$start");
        }else{
            $see= $OrderObj->countRecords("id='{$order_id}' and good_id='{$id}' and pay_status=1 and createtime>=$start");
        }
///////////////////////////////////
        //判断用户是否认证：非认证显示7天（is_verify)

        if ($shopinfo['is_verify']==0){
            //$createtime=$goods['add_time'];//免费7天的开始时间
            $endtime = strtotime($shopinfo['createtimestr']) + 7*86400;//免费7天的结束时间
            $newtime=time();
            if($newtime< $endtime){
                $shopinfo['verify']=1;
            }else{
                @$shopinfo['verify']=0;
            }
        }
        $StaffObj = new Staff();
        $staffInfo = $StaffObj->getFiledValues('',"boss_id='{$shopinfo['user_id']}'");
        if(Buddha_Atom_Array::isValidArray($staffInfo)){
            foreach ($staffInfo as $k => $v) {
                if($v['staff_id'] == $user_id && $shopinfo['is_verify'] == 1){
                    $isok = 1;
                }
            }
        }

        $this->smarty->assign('see',$see);
        $this->smarty->assign('isok',$isok);
        $this->smarty->assign('stockNum',$stockNum);
        $this->smarty->assign('goodsson',$goodsson);
        $this->smarty->assign('attr',$attr);
        $this->smarty->assign('goods',$goods);
        $this->smarty->assign('uid',$user_id);
        $this->smarty->assign('gsllery',$gsllery);
        $this->smarty->assign('shopinfo',$shopinfo);


        ////////分享
        if($goods['goods_brief']){
            if(mb_strlen($goods['goods_brief']) > 20){
                $goods['goods_brief'] = mb_substr($goods['goods_brief'],0,20) . '...';
            }else{
                $goods['goods_brief'] = $goods['goods_brief'];
            }
        }else{
            $goods['goods_brief'] = '本地商家网：实体商家展示新渠道、广告传播新工具';
        }
        $WechatconfigObj  = new Wechatconfig();
        if($goods['promote_price'] != '0.00'){
           $goods['jia'] =  $goods['promote_price'];
        }else{
            $goods['jia'] =  $goods['market_price'];
        }
        
        $sharearr = array(
            'share_title'=>$goods['goods_name'],
            'share_desc'=>"{$goods['goods_name']}，价格：{$goods['jia']}。 ".$goods['goods_brief'],
            'ahare_link'=>"index.php?a=".__FUNCTION__."&c=".$this->c,
            'share_imgUrl'=>$goods['goods_thumb'],
        );
        $WechatconfigObj->getJsSign($sharearr['share_title'],$sharearr['share_desc'],$sharearr['ahare_link'],$sharearr['share_imgUrl']);
        ////////分享

        $shopObj=new Shop();
        $this->smarty->assign('shop_url',$shopObj->shop_url());
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }

    function attrvalue(){//获取属对应的详情
        $attrid = Buddha_Http_Input::getParameter('attrid');
        $attrvalue1 = Buddha_Http_Input::getParameter('attrvalue1');
        $attrvalue2 = Buddha_Http_Input::getParameter('attrvalue2');
        $GoodsproductObj = new Goodsproduct();
        $attrs = $GoodsproductObj->getSingleFiledValues('',"goodspec_id='{$attrid}' AND goods_table='supply' AND sonattr1='{$attrvalue1}' AND sonattr2='{$attrvalue2}'");
        $attrs['money'] = $attrs['cost'] + $attrs['profit'];
        $data = array();
        if($attrs){
            $data['isok'] = 1;
            $data['info'] = $attrs;
        }
        Buddha_Http_Output::makeJson($data);
    }

    public function promote(){

        $RegionObj=new Region();
        $ShopObj=new Shop();
        $locdata = $RegionObj->getLocationDataFromCookie();
        $act=Buddha_Http_Input::getParameter('act');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;

        if($act=='list') {
            $where = " isdel=0 and is_sure=1 and is_promote=1 and buddhastatus=0 {$locdata['sql']}";

            if ($keyword){
                $where .= " and (goods_name like '%$keyword%'  OR keywords like '%{$keyword}%') ";
            }

            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
            $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') : 15;

            $orderby = " order by add_time DESC ";
            $fields = array('id', 'shop_id', 'goods_name', 'market_price', 'promote_price', 'is_promote', 'promote_start_date', 'promote_end_date', 'goods_thumb');
            $list = $this->db->getFiledValues ($fields,  $this->prefix.'goods', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

            foreach($list as $k=>$v){
                $Db_shop=$ShopObj->getSingleFiledValues(array('name','roadfullname','lng','lat'),"id='{$v['shop_id']}'");

                $distance=$RegionObj->getDistance($locdata['lng'],$locdata['lat'],$Db_shop['lng'],$Db_shop['lat'],2);
                if($Db_shop['roadfullname']=='0'){
                    $roadfullname='';
                }else{
                    $roadfullname=$Db_shop['roadfullname'];
                }
                $goodsNws[]=array(
                    'id'=>$v['id'],
                    'goods_name'=>$v['goods_name'],
                    'is_promote'=>$v['is_promote'],
                    'price'=>$v['market_price'],
                    'shop_name'=>$Db_shop['name'],
                    'distance'=>$distance,
                    'roadfullname'=>$roadfullname,
                    'goods_thumb'=>$v['goods_thumb'],
                );
            }
            $data=array();
            if($goodsNws){
                $data['isok']='true';
                $data['list']=$goodsNws;
                $data['data']='加载完成';

            }else{
                $data['isok']='false';
                $data['list']='';
                $data['data']='没数据了';
            }
            Buddha_Http_Output::makeJson($data);
        }
        $this->smarty->assign('view',$view);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
    public function shopping(){
        //list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $uid = Buddha_Http_Input::getParameter('uid');
        if(!$uid){
            $datas['isok']='true';
            $datas['data'] = '';
            $datas['url'] = "index.php?a=login&c=account";
            Buddha_Http_Output::makeJson($datas);
            exit;
            //$datas['data']='/topay/wxpay/wxpayto.php?order_id='.$order_id.'&backurl='.$backurl;
        }
        $UserObj = new User();
        $UserInfo = $UserObj->getSingleFiledValues('',"id='{$uid}'");
        $id = Buddha_Http_Input::getParameter('id');
        $money = Buddha_Http_Input::getParameter('money');
        $number = Buddha_Http_Input::getParameter('number');
        $OrderObj=new Order();
        $OrdermerchantObj=new Ordermerchant();
        $GoodsObj = new Goods();
        $merchant_uid = $GoodsObj->getSingleFiledValues(array('user_id'),"id={$id}");
        $data=array();
        $order_sn = $OrderObj->birthOrderId($uid);//订单编号
        $data['good_id']=$id;//指定产品id
        $data['user_id']=$uid;
        $data['merchant_uid'] = $merchant_uid['user_id'];
        $data['order_sn']= $order_sn;
        $data['good_table']='goods';//哪个表
        $data['pay_type']='third';//third第三方支付，point积分，balance余额
        $data['order_type']='shopping';//money.out提现, 店铺认证shop.v,信息置顶info.top ,跨区域信息推广info.market,信息查看info.see,shopping购物
        $data['goods_amt']=$money;//产品价格
        $data['final_amt']=$money;//产品最终价格
        $data['order_total']=$number;//件数
        $data['payname']='微信支付';
        $data['make_level0']=$UserInfo['level0'];//国家
        $data['make_level1']=$UserInfo['level1'];//省
        $data['make_level2']=$UserInfo['level2'];//市
        $data['make_level3']=$UserInfo['level3'];//区县
        $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];  //  时间戳
        $data['createtimestr']=Buddha::$buddha_array['buddha_timestr']; //  时间日期
        $order_id=$OrderObj->add($data);

        $OrdermerchantObj->getInsertVersion1OrderMerchantInt($order_id,$order_sn,$merchant_uid['user_id'],$money * $number,"goods:{$id}");

        //$urls = substr($_SERVER["REQUEST_URI"],1,stripos($_SERVER["REQUEST_URI"],'?'));
        $backurl = urlencode('user/index.php?a=index&c=order');
        if($OrderObj){
            $datas['isok']='true';
            $datas['data'] = '成功';
            $datas['url'] = 'index.php?a=orderinfo&c=shoppingmall&goods_id='.$id.'&order_id='.$order_id.'&backurl='.$backurl;
            //$datas['data']='/topay/wxpay/wxpayto.php?order_id='.$order_id.'&backurl='.$backurl;
        }else{
            $datas['isok']='false';
            $datas['data']='服务器忙';
        }
        Buddha_Http_Output::makeJson($datas);
    }
    public function orderinfo(){//支付前订单详情
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $goods_id = Buddha_Http_Input::getParameter('goods_id');//商品id
        $backurl = Buddha_Http_Input::getParameter('backurl');//支付后跳转的url地址
        $type = Buddha_Http_Input::getParameter('type');
        $backurl = urlencode($backurl);
        $order_id = Buddha_Http_Input::getParameter('order_id');//订单id
        $OrderObj=new Order();
        $GoodsObj = new Goods();
        $GoodsproductObj = new Goodsproduct();
        $RegionObj = new Region();
        $AddressObj = new Address();
        $CartObj = new Cart();
        if($type == 'cart'){
            $productIdInfo = $CartObj->getFiledValues(array('goods_id'),"user_id='{$uid}' AND is_pick=1");
            $Db_cart = $CartObj->getFiledValues('',"user_id='{$uid}' AND is_pick=1");
            $product_id = Buddha_Atom_Array::getIdInStr($Db_cart['product_id']);
            $goods_ids = Buddha_Atom_Array::getIdInStr($productIdInfo);
            $goodsinfo = $GoodsObj->getFiledValues(array('id','goods_name','goods_thumb','promote_price','market_price'),"id in({$goods_ids})");//获取购买商品的详情
            foreach ($Db_cart as $key => $value) {
                $Db_cart['money'] += $value['goods_price'] * $value['goods_number'];
            }
            $this->smarty->assign('Db_cart',$Db_cart);
        }else{
            $goodsinfo = $GoodsObj->getFiledValues(array('id','goods_name','goods_thumb','promote_price','market_price'),"id={$goods_id}");//获取购买商品的详情
        }
        $orderinfo = $OrderObj->getSingleFiledValues('',"id={$order_id}");//获取订单号
        $addressinfo = $AddressObj->getSingleFiledValues('',"uid={$uid} and isdef = 1");//获取收货地址
        $url = '/topay/wxpay/wxpayto.php?order_id='.$order_id.'&addressid='.$addressinfo['id'].'&backurl='.$backurl;//跳转url
        if($addressinfo['pro']){//省
            $pro = $RegionObj->getSingleFiledValues(array('name'),"id={$addressinfo['pro']}");
            $addressinfo['addre'] = $pro['name'].'省';
        }
        if($addressinfo['city']){//市
            $city = $RegionObj->getSingleFiledValues(array('name'),"id={$addressinfo['city']}");
            $addressinfo['addre'] .= ' '.$city['name'].'市';
        }
        if($addressinfo['area']){//区县
            $area = $RegionObj->getSingleFiledValues(array('name'),"id={$addressinfo['area']}");
            $addressinfo['addre'] .= ' '.$area['name'];
        }
        $this->smarty->assign('uid',$uid);
        $this->smarty->assign('url',$url);
        $this->smarty->assign('goods_id',$goods_id);
        $this->smarty->assign('orderinfo',$orderinfo);
        $this->smarty->assign('goodsinfo',$goodsinfo);
        $this->smarty->assign('addressinfo',$addressinfo);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function updateaddress(){//给订单表添加收货地址id号
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $addressid = Buddha_Http_Input::getParameter('addressinfo');//收货地址id
        $orderid = Buddha_Http_Input::getParameter('orderid');//订单id
        $OrderObj=new Order();
        $data['addressid'] = $addressid;
        if($OrderObj->edit($data,$orderid)){//编辑order表
            $datas['isok']='true';
            $datas['data'] = '即将跳转到支付页面';
            //$datas['data']='/topay/wxpay/wxpayto.php?order_id='.$order_id.'&backurl='.$backurl;
        }else{
            $datas['isok']='false';
            $datas['data']='服务器忙';
        }
        Buddha_Http_Output::makeJson($datas);
    }
    public function sharingmoney(){//转发有赏
        $ShopObj = new Shop();
        $s_id=(int)Buddha_Http_Input::getParameter('s_id');//店铺编号
        $uid = (int)Buddha_Http_Cookie::getCookie('uid');
        $types = Buddha_Http_Input::getParameter('types');
        $shopinfo = $ShopObj->getSingleFiledValues('',"id=$s_id");
        $rechargeObj = new Recharge();
        $rechargeinfo = $rechargeObj->getSingleFiledValues('',"uid={$shopinfo['user_id']}");
        ////////分享有赏
        if($rechargeinfo && $rechargeinfo['balance'] >= $rechargeinfo['forwarding_money'] && $rechargeinfo['is_open'] == 1){
            //$user_id = (int)Buddha_Http_Cookie::getCookie('uid');
            $userObj = new User();
            $billObj = new Bill();
            $orderObj = new Order();
            $sharingObj = new Sharing();
            $sharinginfo = $sharingObj ->getSingleFiledValues('',"uid={$uid} and shop_id={$shopinfo['id']}");//该用户有没有分享过次店铺
            if($sharinginfo){
                if($rechargeinfo['time_period']){
                    $set_time = explode($rechargeinfo['time_period'],'-');//转发有赏起始时间段
                    $starttime = strtotime(date('Y-m-d').' '.$set_time[0].':00:00');
                    $endtime = strtotime(date('Y-m-d').' '.$set_time[1].':00:00');
                }
                
                if((time() - $sharinginfo['ceratetime']) >= 86400 && $set_time<=time() && $endtime>=time()){//  同家店铺分享每天分享第一次在此分享才有赏金，转发有时间段限制
                    $times['createtime'] = strtotime(date('Ymd'));
                    $re = $sharingObj->edit($times,$sharinginfo['id']);//更新分享时间
                    if($re){
                        $banlance = $userObj->getSingleFiledValues(array('id','banlance'),"id={$uid}");
                        if($types == 'quan'){
                            $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['forwarding_money'];//增加账户余额
                        }elseif($types == 'hao'){
                            $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['hao_forwarding_money'];//增加账户余额
                        }
                        $userObj->edit($dataes,$uid);//更新账户余额
                        //生成订单和账单明细
                        $data = array();
                        $data['good_id'] = $shopinfo['id'];
                        $data['user_id'] = $uid;
                        $data['order_sn'] = $orderObj->birthOrderId($uid);
                        $data['good_table'] = 'shop';
                        if($types == 'quan'){
                            $data['goods_amt'] = $rechargeinfo['forwarding_money'];
                            $data['final_amt'] = $rechargeinfo['forwarding_money'];
                        }elseif($types == 'hao'){
                            $data['goods_amt'] = $rechargeinfo['hao_forwarding_money'];
                            $data['final_amt'] = $rechargeinfo['hao_forwarding_money'];
                        }
                        $data['pay_status'] =1;
                        $data['pay_type'] = 'balance';
                        $data['order_type'] = 'forwarding_money';
                        $data['payname'] = '余额支付转发有赏';
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        $order_id=$orderObj->add($data);
                        $order_sn = $orderObj->getSingleFiledValues(array('order_sn'),"id={$order_id}");
                        $data = array();
                        $data['user_id'] = $uid;
                        $data['order_sn'] = $order_sn['order_sn'];
                        $data['order_id'] = $order_id;
                        $data['is_order'] = 1;
                        $data['order_type'] = 'forwarding.money';
                        $data['order_desc']  ='转发赏金';
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        if($types == 'quan'){
                            $data['billamt'] = $rechargeinfo['forwarding_money']; 
                        }elseif($types == 'hao'){
                            $data['billamt'] = $rechargeinfo['hao_forwarding_money']; 
                        }
                        $billObj->add($data);
                        $data = array();//商家转发后资金减少的记录
                        $data['user_id'] = $shopinfo['user_id'];
                        $data['is_order'] = 0;
                        $data['order_type'] = 'forwarding.money';
                        $data['order_desc']  ='扣除转发赏金';
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        if($types == 'quan'){
                            $data['billamt'] = '-' . $rechargeinfo['forwarding_money']; 
                        }elseif($types == 'hao'){
                            $data['billamt'] = '-' . $rechargeinfo['hao_forwarding_money']; 
                        }
                        $billObj->add($data);
                        //改变对应的充值表余额
                        if($types == 'quan'){
                            $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['forwarding_money'];
                        }elseif($types == 'hao'){
                            $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['hao_forwarding_money'];
                        }
                        
                        $rechargeObj->edit($rech,$rechargeinfo['id']);
                        $data = array();
                        $data['isok'] = 'true';
                        $data['info'] = '赏金已充入余额';
                    }
                }elseif((time() - $sharinginfo['ceratetime']) >= 86400){//转发没有时间段限制
                    $times['createtime'] = strtotime(date('Ymd'));
                    $re = $sharingObj->edit($times,$sharinginfo['id']);//更新分享时间
                    if($re){
                        $banlance = $userObj->getSingleFiledValues(array('id','banlance'),"id={$uid}");
                        if($types == 'quan'){
                            $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['forwarding_money'];//增加账户余额
                        }elseif($types == 'hao'){
                            $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['hao_forwarding_money'];//增加账户余额
                        }
                        $userObj->edit($dataes,$uid);//更新账户余额
                        //生成订单和账单明细
                        $data = array();
                        $data['good_id'] = $shopinfo['id'];
                        $data['user_id'] = $uid;
                        $data['order_sn'] = $orderObj->birthOrderId($uid);
                        $data['good_table'] = 'shop';
                        if($types == 'quan'){
                            $data['goods_amt'] = $rechargeinfo['forwarding_money'];
                            $data['final_amt'] = $rechargeinfo['forwarding_money'];
                        }elseif($types == 'hao'){
                            $data['goods_amt'] = $rechargeinfo['hao_forwarding_money'];
                            $data['final_amt'] = $rechargeinfo['hao_forwarding_money'];
                        }
                        $data['pay_status'] =1;
                        $data['pay_type'] = 'balance';
                        $data['order_type'] = 'forwarding_money';
                        $data['payname'] = '余额支付转发有赏';
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        $order_id=$orderObj->add($data);
                        $order_sn = $orderObj->getSingleFiledValues(array('order_sn'),"id={$order_id}");
                        $data = array();
                        $data['user_id'] = $uid;
                        $data['order_sn'] = $order_sn['order_sn'];
                        $data['order_id'] = $order_id;
                        $data['is_order'] = 1;
                        $data['order_type'] = 'forwarding.money';
                        $data['order_desc']  ='转发赏金';
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        if($types == 'quan'){
                            $data['billamt'] = $rechargeinfo['forwarding_money']; 
                        }elseif($types == 'hao'){
                            $data['billamt'] = $rechargeinfo['hao_forwarding_money']; 
                        }
                        $billObj->add($data);
                        $data = array();//商家转发后资金减少的记录
                        $data['user_id'] = $shopinfo['user_id'];
                        $data['is_order'] = 0;
                        $data['order_type'] = 'forwarding.money';
                        $data['order_desc']  ='扣除转发赏金';
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        if($types == 'quan'){
                            $data['billamt'] = '-' . $rechargeinfo['forwarding_money']; 
                        }elseif($types == 'hao'){
                            $data['billamt'] = '-' . $rechargeinfo['hao_forwarding_money']; 
                        }
                        $billObj->add($data);
                        //改变对应的充值表余额
                        if($types == 'quan'){
                            $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['forwarding_money'];
                        }elseif($types == 'hao'){
                            $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['hao_forwarding_money'];
                        }
                        
                        $rechargeObj->edit($rech,$rechargeinfo['id']);
                        $data = array();
                        $data['isok'] = 'true';
                        $data['info'] = '赏金已充入余额';
                    }
                }
            }else{
                $datass['uid'] = $uid;
                $datass['shop_id'] = $shopinfo['id'];
                $datass['ceratetime'] = strtotime(date('Ymd'));
                //添加记录
                if($sharingObj->add($datass)){
                    $banlance = $userObj->getSingleFiledValues(array('id','banlance'),"id={$uid}");
                   if($types == 'quan'){
                        $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['forwarding_money'];//增加账户余额
                    }elseif($types == 'hao'){
                        $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['hao_forwarding_money'];//增加账户余额
                    }
                    $userObj->edit($dataes,$uid);//更新账户余额
                    //生成订单和账单明细
                    $data = array();
                    $data['good_id'] = $shopinfo['id'];
                    $data['user_id'] = $uid;
                    $data['order_sn'] = $orderObj->birthOrderId($uid);
                    $data['good_table'] = 'shop';
                    if($types == 'quan'){
                        $data['goods_amt'] = $rechargeinfo['forwarding_money'];
                        $data['final_amt'] = $rechargeinfo['forwarding_money'];
                    }elseif($types == 'hao'){
                        $data['goods_amt'] = $rechargeinfo['hao_forwarding_money'];
                        $data['final_amt'] = $rechargeinfo['hao_forwarding_money'];
                    }
                    $data['pay_status'] =1;
                    $data['pay_type'] = 'balance';
                    $data['order_type'] = 'forwarding_money';
                    $data['payname'] = '余额支付转发有赏';
                    $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                    $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                    $order_id=$orderObj->add($data);
                    $order_sn = $orderObj->getSingleFiledValues(array('order_sn'),"id={$order_id}");
                    $data = array();
                    $data['user_id'] = $uid;
                    $data['order_sn'] = $order_sn['order_sn'];
                    $data['order_id'] = $order_id;
                    $data['is_order'] = 1;
                    $data['order_type'] = 'forwarding.money';
                    $data['order_desc']  ='转发赏金';
                    $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                    $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                    if($types == 'quan'){
                        $data['billamt'] = $rechargeinfo['forwarding_money']; 
                    }elseif($types == 'hao'){
                        $data['billamt'] = $rechargeinfo['hao_forwarding_money']; 
                    }
                    $billObj->add($data);

                    //商家转发后资金减少的记录
                    $data = array();
                    $data['user_id'] = $shopinfo['user_id'];
                    $data['is_order'] = 0;
                    $data['order_type'] = 'forwarding.money';
                    $data['order_desc']  ='扣除转发赏金';
                    $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                    $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                    if($types == 'quan'){
                        $data['billamt'] = '-' . $rechargeinfo['forwarding_money']; 
                    }elseif($types == 'hao'){
                        $data['billamt'] = '-' . $rechargeinfo['hao_forwarding_money']; 
                    }
                    $billObj->add($data);
                    //改变对应的充值表余额
                    if($types == 'quan'){
                            $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['forwarding_money'];
                        }elseif($types == 'hao'){
                            $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['hao_forwarding_money'];
                        }
                    $rechargeObj->edit($rech,$rechargeinfo['id']);
                    $data = array();
                    $data['isok'] = 'true';
                    $data['info'] = '赏金已充入余额';
                }
            }
        }else{
            $data = array();
            $data['isok'] = 'false';
            $data['info'] = '';
        }
        Buddha_Http_Output::makeJson($data);
    }


    function goodsAddCart(){
        $uid = Buddha_Http_Input::getParameter('uid');
        if(!$uid){
            $datas['isok']='true';
            $datas['data'] = '';
            $datas['url'] = "index.php?a=login&c=account";
            Buddha_Http_Output::makeJson($datas);
            exit;
            //$datas['data']='/topay/wxpay/wxpayto.php?order_id='.$order_id.'&backurl='.$backurl;
        }
        $UserObj = new User();
        $CartObj = new Cart();
        $UserInfo = $UserObj->getSingleFiledValues('',"id='{$uid}'");
        $id = Buddha_Http_Input::getParameter('id');
        $money = Buddha_Http_Input::getParameter('money');
        $number = Buddha_Http_Input::getParameter('number');
        $attrs_id = Buddha_Http_Input::getParameter('attrs_id');
        $product_table = 'goods';
        $Db_Good= array();
        if($product_table=='goods'){
            $Db_Good = $CartObj->getGoodsArr($product_table,$id,$uid,$number,'goodsproduct',$attrs_id);
        }
        if(!$CartObj->isExistGoods($product_table,$id,$user_id)){
            $return_id = $CartObj->add($Db_Good);
        }else{
           $return_id = $CartObj->addOneGoodsToCart($product_table,$product_id,$user_id,$number);
        }
        $data = array();
        if($return_id){
            $data['isok'] = 1;
            $data['info'] = '成功加入购物车';
        }
        Buddha_Http_Output::makeJson($data);

    }

    function cart(){
        $uid = (int)Buddha_Http_Cookie::getCookie('uid');
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') :1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $CartObj = new Cart();
        $paging = Buddha_Tool_Page::sqlLimit($page, $pagesize);
        $DB_Cart = $CartObj->getCartArr($uid,'goods',$paging);
        foreach ($DB_Cart as $k=>$v){
            $tableFiled=array('goods_thumb as img','goods_brief');
            $Db_Goodstable=$this->db->getSingleFiledValues($tableFiled,$v['goods_table'],"id='{$v['goods_id']}'");
            if($v['product_id']){
                $GoodsproductObj = new Goodsproduct();
                $attr = $GoodsproductObj->getSingleFiledValues('',"id='{$v['product_id']}'");
                $DB_Cart[$k]['attr'] = $attr['sonattr1'] . ' ' . $attr['sonattr2'];
            }
            if(mb_strlen($Db_Goodstable['goods_brief'])>32){
                $DB_Cart[$k]['goods_brief'] = mb_substr($Db_Goodstable['goods_brief'],0,32);
            }else{
                $DB_Cart[$k]['goods_brief'] = $Db_Goodstable['goods_brief'];
            }
            if(Buddha_Atom_Array::isValidArray($Db_Goodstable)){
                $DB_Cart[$k]['img'] = '/' . $Db_Goodstable['img'];
            }else{
                $DB_Cart[$k]['img']='';
            }
        }
        $this->smarty->assign('DB_Cart',$DB_Cart);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    function delcart(){
        $CartObj = new Cart();
        $uid = (int)Buddha_Http_Cookie::getCookie('uid');
        $id = Buddha_Http_Input::getParameter('id');
        $data = array();
        if(!$CartObj->isValidCartGoods($uid,$id)){
            $data['isok'] = 0;
            $data['info'] = '此产品不是您购物车里的里的产品，您无权删除';
        }else{
            if($CartObj->delRecords(" user_id='{$uid}' AND  id='{$id}'")){
                $data['isok'] = 1;
                $data['info'] = '操作成功';
            }else{
                $data['isok'] = 0;
                $data['info'] = '服务器忙';
            }
        }
        Buddha_Http_Output::makeJson($data);
    }
    function settlement_cart(){
        $UserObj = new User();
        $PaymentObj = new Payment();
        $CartObj = new Cart();
        $OrderObj = new Order();
        $OrderproductObj = new Orderproduct();
        $OrdermerchantObj = new Ordermerchant();
        $uid = (int)Buddha_Http_Cookie::getCookie('uid');
        $selected_id = Buddha_Http_Input::getParameter('selected_id');
        $cart_arr = $CartObj->getFiledValues('',"id in({$selected_id})");
        $Total_Arr = $CartObj->getTotalArr($cart_arr);
        $order_type = 'shopping';
        $referral_id =0;
        $agent_id =0;
        $partnerrate =0;
        $agentrate =0;
        $final_amt = $Total_Arr['goods_total_price'];
        $payname = '微信支付';
        $fieldsarray= array('id','usertoken','realname','mobile','username','level1','level2','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and id='{$uid}' ");
        $level = array('level1'=>$Db_User['level1'],'level2'=>$Db_User['level2'],'level3'=>$Db_User['level3']);
        //生成订单
        $order_sn = $OrderObj->birthOrderId($uid);
        $data = array();
        $data['user_id'] = $uid;
        $data['good_id'] = $selected_id;
        $data['good_table'] = 'goods';
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
        $OrderproductObj->createOrderproductFromCart($order_id,$order_sn,$cart_arr);
       //生成商户订单产品表
        $OrdermerchantObj->createOrdermerchantFromCart($order_id,$order_sn,$cart_arr);
        $CartObj->updateRecords(array('is_pick'=>1),"id in({$selected_id})");
        if($order_id){
            $backurl = urlencode('user/index.php?a=index&c=order');
            $datas['isok']=1;
            $datas['data'] = '正在跳转到支付页面';
            $datas['url'] = 'index.php?a=orderinfo&c=shoppingmall&type=cart&order_id='.$order_id.'&backurl='.$backurl;
            //$datas['data']='/topay/wxpay/wxpayto.php?order_id='.$order_id.'&backurl='.$backurl;
        }else{
            $datas['isok']=0;
            $datas['data']='服务器忙';
        }
        Buddha_Http_Output::makeJson($datas);
    }
    function plusMinus(){//改变购物车里单品的数量
        $UserObj = new User();
        $CartObj = new Cart();
        $uid = (int)Buddha_Http_Cookie::getCookie('uid');
        $type = Buddha_Http_Input::getParameter('type');
        $cart_id = Buddha_Http_Input::getParameter('cart_id');
        $returnValue = $CartObj->goodsCartNumberPlusMinus($cart_id,$type);
        if($returnValue == 1){
            $data['isok'] = 1;
            $data['info'] = '库存不足';
            Buddha_Http_Output::makeJson($data);
        }
        
    }
    function category(){//商品类别
        $GoodscatObj = new Goodscat();
        $categorys = $GoodscatObj->goods_cat();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $CartObj = new Cart();
        $cartNum = $CartObj->countRecords("user_id='{$uid}' AND goods_table='goods' ");
        foreach ($categorys as $k => $v){
            $category[$k]['id'] = $v['id'];
            $category[$k]['cat_name'] = $v['cat_name'];
            if($v['sub'] == 0){
                $category[$k]['sub'] = $GoodscatObj->getFiledValues('',"sub={$v['id']}");
            }
        }
        //print_r($category);
        $this->smarty->assign('cartNum',$cartNum);
        $this->smarty->assign('category',$category);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    function lists(){
        $uid = (int)Buddha_Http_Cookie::getCookie('uid');
        $type = Buddha_Http_Input::getParameter('type');
        $cid = Buddha_Http_Input::getParameter('cid');
        $CartObj = new Cart();
        $cartNum = $CartObj->countRecords("user_id='{$uid}' AND goods_table='goods' ");
        $this->smarty->assign('cartNum',$cartNum);
        $GoodscatObj = new Goodscat();
        if ($type) {
            switch ($type) {
                case 'self';
                    $headers = '自营';
                    break;
                case 'new';
                    $headers = '新品';
                    break;
                case 'integral';
                    $headers = '积分换购';
                    break;
            }
        }
        if($cid){
            $cartName = $GoodscatObj->getSingleFiledValues(array('cat_name'),"id='{$cid}'");
            $headers = $cartName['cat_name'];
        }
        $this->smarty->assign('cid',$cid);
        $this->smarty->assign('type',$type);
        $this->smarty->assign('headers',$headers);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }

    function ajaxList(){
        $RegionObj=new Region();
        $ShopObj=new Shop();
        $GoodsObj=new Goods();
        $GoodscatObj = new Goodscat();
        $cid = Buddha_Http_Input::getParameter('cid');
        /*$getcategory =$GoodscatObj->getcategory();
        if($cid){
            $insql = $GoodscatObj->getInSqlByID($getcategory,$cid);
        }*/
        $locdata = $RegionObj->getLocationDataFromCookie();
        $act=Buddha_Http_Input::getParameter('act');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $cat_id=Buddha_Http_Input::getParameter('cat_id');
        $type=Buddha_Http_Input::getParameter('type');
        if($act=='list') {
            $where = " isdel=0 and is_sure=1 and type=0 and  buddhastatus=0 {$locdata['sql']} ";
            if ($type) {
                switch ($type) {
                    case 'self';
                        $where .=" and goods_type=1 ";
                        $headers = '自营';
                        break;
                    case 'new';
                        //$where .= " and is_hot=1";
                        $headers = '新品';
                        break;
                    case 'integral';
                        $where .= " and goods_type=3";
                        $headers = '积分换购';
                        break;
                }
            }
            if ($keyword) {
                $where .= " and goods_name like '%$keyword%'";
            }

            if($cid){
                $where.=" and  goodscat_id2='{$cid}'";
            }
            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
            $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') : 16;

            $orderby = " order by add_time DESC ";
            $fields = array('id', 'shop_id', 'goods_name', 'market_price', 'promote_price', 'is_promote', 'promote_start_date', 'promote_end_date', 'goods_thumb');
            $list = $this->db->getFiledValues ($fields,  $this->prefix.'goods', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
            $nwstiem=time();
            foreach($list as $k=>$v){
                if($v['is_promote']==1){
                    if($nwstiem>$v['promote_start_date'] and $v['promote_end_date']>$nwstiem){
                        $price=$v['promote_price'];
                    }else{
                        $price= $v['market_price'];
                    }
                }else{
                    $price= $v['market_price'];
                }
                $goodsNws[]=array(
                    'id'=>$v['id'],
                    'goods_name'=>$v['goods_name'],
                    'is_promote'=>$v['is_promote'],
                    'price'=>$price,
                    'shop_name'=>$Db_shop['name'],
                    'distance'=>$distance,
                    'roadfullname'=>$roadfullname,
                    'goods_thumb'=>$v['goods_thumb'],
                );
            }
            $data=array();
            if($goodsNws){
                $data['isok']=1;
                $data['list']=$goodsNws;
                $data['data']='加载完成';
            }else{
                $data['isok']=0;
                $data['list']='';
                $data['data']='没数据了';
            }
            Buddha_Http_Output::makeJson($data);
        }
    }



}