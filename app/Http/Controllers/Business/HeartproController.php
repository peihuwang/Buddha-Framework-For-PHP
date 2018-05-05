<?php

/**
 * Class BillController
 */
class HeartproController extends Buddha_App_Action{

    protected $tablenamestr;
    protected $tablename;
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='1分营销';
        $this->tablename='heartpro';
    }

    /**获取店铺下的产品**/
    public function ajaxsupply()
    {
        header("Content-Type: text/html; charset=utf-8");

        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        if(empty($uid)){
            Buddha_Http_Head::redirectofmobile('请登录后发布！','index.php?a=login&c=account',2);
            exit;
        }

        $SupplyObj = new Supply();

        $shop_id = (int)Buddha_Http_Input::getParameter('fid')?(int)Buddha_Http_Input::getParameter('fid'):0;
        $keyword = Buddha_Http_Input::getParameter('keyword')?Buddha_Http_Input::getParameter('keyword'):'';

        $Db_Supply = $SupplyObj-> getSupplyBelongShopbyShopid($shop_id,$uid,$keyword);

        $datas = array();

        if($Db_Supply)
        {
            $datas['isok'] = 'true';
            $datas['data'] = $Db_Supply;

        }else{

            $datas['isok']='false';
            $datas['data']='';
        }
        Buddha_Http_Output::makeJson($datas);

    }

    /**1分购添加**/
    public function add()
    {
        header("Content-Type: text/html; charset=utf-8");

        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopObj = new Shop();
        $SupplycatObj = new Supplycat();
        $OrderObj = new Order();
        $HeartproObj = new Heartpro();
        $MoregalleryObj = new Moregallery();
        $CommonObj = new Common();
        $SupplyObj = new Supply();

        $Db_Shop = $ShopObj->getFiledValues(array('id','name'),"user_id='{$uid}' AND isdel=0 AND is_sure=1 AND state=0");


        if(!Buddha_Atom_Array::isValidArray($Db_Shop))
        {
            Buddha_Http_Head::redirectofmobile('您还没用创建店铺，或者店铺还未通过审核，或者已经全部下架！','index.php?a=index&c=shop',2);
        }


        if(!$SupplyObj->IsUserHasNormalSupply($uid))
        {
            Buddha_Http_Head::redirectofmobile('您还没用发布供应，或者供应还未通过审核，或者已经全部下架！','index.php?a=index&c=supplyinfo',2);
        }


////////////////////
        $getshoplistOption = $ShopObj->getShoplistOption($uid,0);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $gettableOption = $SupplycatObj->getunitOption_id();
        $this->smarty->assign('gettableOption', $gettableOption);
/////////////
        $this->smarty->assign('title', $this->tablenamestr);

        $this->smarty->assign('partake', $HeartproObj->partake());

/////////////////////////////////////

        $title = $this->tablenamestr;

        $goods_name = Buddha_Http_Input::getParameter('good_name');//1分购名称
        $shop_id = Buddha_Http_Input::getParameter('shop_id');//发布店铺内码ID
        $shopsupply_id = Buddha_Http_Input::getParameter('shopsupply_id');//商品内码ID
        $goods_unit = Buddha_Http_Input::getParameter('goods_unit');//属性单位内码ID
        $price = Buddha_Http_Input::getParameter('price');//销售价
        $stock = Buddha_Http_Input::getParameter('stock');//库存量
        $votecount = Buddha_Http_Input::getParameter('votecount');//投票数量

        $start_date = Buddha_Http_Input::getParameter('start_date');  //报名开始时间


        $end_date = Buddha_Http_Input::getParameter('end_date');      //报名结束时间
//        $shelvesstart_date=Buddha_Http_Input::getParameter('shelvesstart_date');  //上架时间
//        $shelvesend_date=Buddha_Http_Input::getParameter('shelvesend_date');      //下架时间
        $shelvesstart_date = Buddha::$buddha_array['buddha_timestamp'];  //上架时间
        $HeartproObj= new Heartpro();
        $shelvesend_date = $HeartproObj->shelvesend();      //下架时间

        //商品异地发布
        $is_remote = Buddha_Http_Input::getParameter('is_remote');    //是否异地发布
        $regionstr = Buddha_Http_Input::getParameter('regionstr');    //异地发布区域的ID

        $keywords = Buddha_Http_Input::getParameter('keywords');    //关键词


        //描述、图片
        $goods_desc = Buddha_Http_Input::getParameter('goods_desc');//1分购规则详情
        $partake = Buddha_Http_Input::getParameter('partake');//参与规则



        if(Buddha_Http_Input::isPost())
        {
            $data=array();
            $data['user_id'] = $uid;
            $data['name'] = $goods_name;
            $data['shop_id'] = $shop_id;
            $data['table_id'] = $shopsupply_id;
            $data['table_name'] = 'supply';
            $data['unit_id'] = $goods_unit;
            $data['partake'] = $partake;
            $data['price'] = $price;
            $data['originalstock'] = $stock;
            $data['stock'] = $stock;
            $data['votecount'] = $votecount;
            $data['applystarttime'] = strtotime($start_date);
            $data['applystarttimestr'] = str_replace("T"," ",$start_date);
            $data['applyendtime'] = strtotime($end_date);
            $data['applyendtimestr'] =  str_replace("T"," ",$end_date);

//            $data['onshelftime'] = strtotime($shelvesstart_date);
//            $data['onshelftimestr'] = str_replace("T"," ",$shelvesstart_date);
//            $data['offshelftime'] = strtotime($shelvesend_date);
//            $data['offshelftimestr'] =str_replace("T"," ",$shelvesend_date);

            $data['onshelftime'] = $shelvesstart_date;
            $data['onshelftimestr'] = date("Y-m-d H:i",$shelvesstart_date) ;
            $data['offshelftime'] = $shelvesend_date;
            $data['offshelftimestr'] = date("Y-m-d H:i",$shelvesend_date) ;

            if($is_remote=='')
            {//0本地
                $data['is_remote'] = 0;
                $Db_level = $ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$uid}' and id='{$shop_id}' and isdel=0");
                $data['level0'] = $Db_level['level0'];
                $data['level1'] = $Db_level['level1'];
                $data['level2'] = $Db_level['level2'];
                $data['level3'] = $Db_level['level3'];
            }elseif($is_remote==1){//1为异地
                $level = explode(",", $regionstr);
                $data['is_remote'] = 1;
                $data['level0'] = 1;
                $data['level1'] = $level[0];
                $data['level2'] = $level[1];
                $data['level3'] = $level[2];
            }
//            $data['details'] = $goods_desc;
            $data['keywords'] = $keywords;
            $data['number']=$CommonObj->GeneratingNumber();
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];

            $good_id = $HeartproObj->add($data);

            $datas = array();

            if($good_id){
                $table_name = Buddha_Http_Input::getParameter('c');
                $MoreImage = Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/{$table_name}/{$good_id}/",
                    array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('Image')
                    ->getAllReturnArray();
                if(is_array($MoreImage) and count($MoreImage)>0){
                    $moregallery_id = $MoregalleryObj->pcaddimage('file',$MoreImage, $good_id,$table_name,$uid);

                    if(count($moregallery_id)>0){
                        $num = $HeartproObj->setFirstGalleryImgToSupply($good_id,$table_name,'file');
                        if($num==0){
                            $datas['err'] = 5;
                        }
                    }else{
                        $datas['err'] = 6;
                    }
                }


                if($goods_desc){//富文本编辑器图片处理

                    $saveData = $MoregalleryObj->base_upload($goods_desc,$good_id,$this->tablename);
                    $saveData = str_replace(PATH_ROOT,'/', $saveData);

                    $details['details'] = $saveData;
                    $HeartproObj->edit($details,$good_id);
                }

                //$remote为1表示发布异地产品添加订单
                if($is_remote==1){
                    $Db_referral = $ShopObj->getSingleFiledValues(array('referral_id','partnerrate','agent_id','agentrate','level0','level1','level2','level3'),"id='{$shop_id}' and user_id='{$uid}' and isdel=0");
                    $getMoneyArrayFromShop = $ShopObj->getMoneyArrayFromShop($shop_id,0.2);
                    $data=array();
                    $data['good_id'] = $good_id;
                    $data['user_id'] = $uid;
                    $data['order_sn'] = $OrderObj->birthOrderId($uid);
                    $data['good_table'] = $table_name;
                    $data['referral_id'] = $Db_referral['referral_id'];
                    $data['partnerrate'] = $Db_referral['partnerrate'];
                    $data['agent_id'] = $Db_referral['agent_id'];
                    $data['agentrate'] = $Db_referral['agentrate'];
                    $data['pay_type'] = 'third';
                    $data['order_type'] = 'info.market';
                    $data['goods_amt'] = $getMoneyArrayFromShop['goods_amt'];
                    $data['final_amt'] = $getMoneyArrayFromShop['final_amt'];
                    $data['money_plat'] = $getMoneyArrayFromShop['money_plat'];
                    $data['money_agent'] = $getMoneyArrayFromShop['money_agent'];
                    $data['money_partner'] = $getMoneyArrayFromShop['money_partner'];
                    $data['payname'] = '微信支付';
                    $data['make_level0'] = $Db_referral['level0'];
                    $data['make_level1'] = $Db_referral['level1'];
                    $data['make_level2'] = $Db_referral['level2'];
                    $data['make_level3'] = $Db_referral['level3'];
                    $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                    $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                    $order_id=$OrderObj->add($data);
                    $datas['isok']='true';
                    $datas['data']='添加成功,去支付。';
                    $datas['url']='/topay/wxpay/wxpayto.php?order_id='.$order_id;
                }else{
                    $datas['isok']='true';
                    $datas['data']=$title.'添加成功';
                    $datas['url']='index.php?a=index&c=heartpro';
                }
            }else{
                $datas['isok']='false';
                $datas['data']=$title.'添加失败';
                $datas['url']='index.php?a=add&c=heartpro';

            }
            Buddha_Http_Output::makeJson($datas);
        }

        /////////////////////////////////////////////////////


        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }


    /**列表**/
    public function index()
    {
        header("Content-Type: text/html; charset=utf-8");

        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $HeartproObj = new Heartpro();


        $act=Buddha_Http_Input::getParameter('act');
        $uid = Buddha_Http_Cookie::getCookie('uid');
//==================================================================
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):0;

        $currentdate=time();//当前时间戳

        /**判断有没有时间上已经下架但状态为下架的产品***/
        $n_where="user_id='{$uid}' AND buddhastatus=0 AND (onshelftime > $currentdate or $currentdate > offshelftime)";

        $num =  $HeartproObj->countRecords ($n_where);

        if($num){
            $Promotion = $HeartproObj->getFiledValues('',$n_where);

            foreach ($Promotion as $k => $v) {
                $data['buddhastatus'] = 1;
                $HeartproObj->updateRecords($data,"id='{$v['id']}'");
            }
        }


//==================================================================
        if($act=='list'){
            $keyword=Buddha_Http_Input::getParameter('keyword');
            $where = "isdel=0 and user_id='{$uid}'";
            if($keyword){
                $where.=" and (name like '%{$keyword}%' or number like '%{$keyword}%') ";
            }
//==================================================================
            if($view)
            {
                switch($view){
                    case 2;
                        $where.=' and is_sure=0';
                        break;
                    case 3;
                        $where.=" and is_sure=1";
                        break;
                    case 4;
                        $where.=" and is_sure=4 ";
                        break;
                }
            }

            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') :1;
            $pagesize = Buddha::$buddha_array['page']['pagesize'];

            $orderby = " order by id DESC ";

            $list = $this->db->getFiledValues (array('id','user_id','small as goods_thumb','name as goods_name','price','number as goods_sn','is_sure','buddhastatus'),  $this->prefix.'heartpro', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

            $UserObj= new User();
            $sure=$UserObj->getSingleFiledValues(array('groupid'),"id='{$uid}' and isdel=0");


            $CommonObj = new Common();
            $UsercommonObj = new Usercommon();

            foreach($list as $k=>$v)
            {
                $market_price = $v['price'];
                $jsondata[] = array(
                    'id'=>$v['id'],
                    'title'=>$v['goods_name'],
                    'goods_sn'=>$v['goods_sn'],
                    'user_id'=>$v['user_id'],
                    'is_sure'=>$v['is_sure'],
                    'issureimg'=>$UsercommonObj->businessissurestr($v['is_sure']),
                    'goods_thumb'=>$CommonObj->handleImgSlashByImgurl($v['goods_thumb']),
                    'goods_name'=>$v['goods_name'],
                    'buddhastatus'=>$v['buddhastatus'],
                    'price'=>$market_price,
                    'is_promote'=>$v['is_promote'],
                );
            }
            $CommonObj = new Common();
            $Nws= $CommonObj->page_where($page,$jsondata,$pagesize);
            $datas['info']=$Nws;

            /**↓↓↓↓↓↓↓↓↓↓↓ 置顶参数 ↓↓↓↓↓↓↓↓↓↓↓**/
            $datas['top']['good_table']=$this->tablename;
            $datas['top']['order_type']='info.top';
            $datas['top']['final_amt']=0.2;
            /**↑↑↑↑↑↑↑↑↑↑ 置顶参数 ↑↑↑↑↑↑↑↑↑↑**/

//======================================================================

            if(is_array($list) and count($list)>0){
                $datas['isok']='true';
//======================================================================
                $datas['data']=$jsondata;
//======================================================================
            }else{
                $datas['isok']='false';
                $datas['data']='没有数据';
            }

            Buddha_Http_Output::makeJson($datas);
        }

        $this->smarty->assign('view', $view);
        $this->smarty->assign('c', $this->tablename);
        $this->smarty->assign('title', $this->tablenamestr);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }


    /**编辑**/
    public function edit()
    {

        $ShopObj = new Shop();
        $SupplycatObj = new Supplycat();
        $OrderObj = new Order();
        $HeartproObj = new Heartpro();
        $HeartapplyObj = new Heartapply();
        $MoregalleryObj = new Moregallery();
        $RegionObj = new Region();
        $CommonObj = new Common();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if(!$uid)
        {
            Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c='.$this->tablename,2);
        }


        $id=(int)Buddha_Http_Input::getParameter('id');

        if(!$id)
        {
            Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c='.$this->tablename,2);
        }
        if($HeartapplyObj->countRecords("heartpro_id='{$id}'"))
        {
            Buddha_Http_Head::redirectofmobile('已经有人报名了，不可以修改！','index.php?a=index&c='.$this->tablename,2);
        }

        $filed=array('id,name,price,stock,votecount,onshelftimestr, onshelftime,offshelftime,offshelftimestr,applystarttime,applystarttimestr,applyendtime,applyendtimestr,keywords','table_id','shop_id','level1','level2','level3','table_name','user_id','unit_id','is_remote','details','partake');

        $goods = $HeartproObj->getSingleFiledValues($filed,"id='{$id}' and user_id='{$uid}'");

        if(!$goods)
        {
            Buddha_Http_Head::redirectofmobile('没有找到您要的信息！','index.php?a=index&c='.$this->tablename,2);
        }

        if($goods['user_id'] != $uid)
        {
            Buddha_Http_Head::redirectofmobile('您没有权限进行此操作！','index.php?a=index&c='.$this->tablename,2);
        }


        $Supply_name =$this->db->getSingleFiledValues(array('id','goods_name'), $goods['table_name'],"id='{$goods['table_id']}' and user_id='{$uid}'");
        $goods['supply_name']=$Supply_name['goods_name'];


        $goods['applystarttimestr'] = str_replace(" ","T",$goods['applystarttimestr']);
        $goods['applyendtimestr'] = str_replace(" ","T",$goods['applyendtimestr']);

        $goods['offshelftimestr'] = str_replace(" ","T",$goods['offshelftimestr']);
        $goods['onshelftimestr'] = str_replace(" ","T",$goods['onshelftimestr']);


        //产品相册
        $gimages = $MoregalleryObj->getEditGoodsImage($this->tablename,$id,$uid);


        $getshoplistOption=$ShopObj->getShoplistOption($uid,$goods['shop_id']);

        //区域名称拼接
        $Region_name=$RegionObj->getAllArrayAddressByLever($goods['level3']);
        if($Region_name){
            $regionname='';
            foreach($Region_name as $k=>$v){
                if($k!=0){
                    $regionname.=$v['name'].' > ';
                }
            }
            $goods['region_name']=Buddha_Atom_String::toDeleteTailCharacter($regionname);
        }

        $gettableOption = $SupplycatObj->getunitOption_id($goods['unit_id']);


        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('gettableOption', $gettableOption);
        $this->smarty->assign('gimages', $gimages);
        $this->smarty->assign('goods', $goods);
        $this->smarty->assign('partake', $HeartproObj->partake());

///////////////////////////////////


/////////////////////////////////////////////


        $goods_name=Buddha_Http_Input::getParameter('good_name');//1分购名称
        $shop_id=Buddha_Http_Input::getParameter('shop_id');//发布店铺内码ID
        $shopsupply_id=Buddha_Http_Input::getParameter('shopsupply_id');//商品内码ID
        $goods_unit=Buddha_Http_Input::getParameter('goods_unit');//属性单位内码ID
        $price=Buddha_Http_Input::getParameter('price');//销售价
        $stock=Buddha_Http_Input::getParameter('stock');//库存量
        $votecount=Buddha_Http_Input::getParameter('votecount');//投票数量

        $start_date=Buddha_Http_Input::getParameter('start_date');  //报名开始时间


        $end_date=Buddha_Http_Input::getParameter('end_date');      //报名结束时间
//        $shelvesstart_date=Buddha_Http_Input::getParameter('shelvesstart_date');  //上架时间
//        $shelvesend_date=Buddha_Http_Input::getParameter('shelvesend_date');      //下架时间

        //商品异地发布
        $is_remote = Buddha_Http_Input::getParameter('is_remote');    //是否异地发布
        $regionstr = Buddha_Http_Input::getParameter('regionstr');    //异地发布区域的ID

        $keywords = Buddha_Http_Input::getParameter('keywords');    //关键词
        $partake = Buddha_Http_Input::getParameter('partake');//参与规则


        //描述、图片
        $goods_desc = Buddha_Http_Input::getParameter('goods_desc');//1分购规则详情

        if(Buddha_Http_Input::isPost()) {
            $data = array();
            $data['user_id'] = $uid;
            $data['name'] = $goods_name;
            $data['shop_id'] = $shop_id;
            $data['table_id'] = $shopsupply_id;
            $data['table_name'] = 'supply';
            $data['unit_id'] = $goods_unit;
            $data['partake'] = $partake;
            $data['price'] = $price;
            $data['originalstock'] = $stock;
            $data['stock'] = $stock;
            $data['votecount'] = $votecount;
            $data['applystarttime'] = strtotime($start_date);
            $data['applystarttimestr'] = str_replace("T", " ", $start_date);
            $data['applyendtime'] = strtotime($end_date);
            $data['applyendtimestr'] = str_replace("T", " ", $end_date);
//            $data['onshelftime'] = strtotime($shelvesstart_date);
//            $data['onshelftimestr'] = str_replace("T", " ", $shelvesstart_date);
//            $data['offshelftime'] = strtotime($shelvesend_date);
//            $data['offshelftimestr'] = str_replace("T", " ", $shelvesend_date);

            if ($is_remote == '') {//0本地
                $data['is_remote'] = 0;
                $Db_level = $ShopObj->getSingleFiledValues(array('level0', 'level1', 'level2', 'level3'), "user_id='{$uid}' and id='{$shop_id}' and isdel=0");
                $data['level0'] = $Db_level['level0'];
                $data['level1'] = $Db_level['level1'];
                $data['level2'] = $Db_level['level2'];
                $data['level3'] = $Db_level['level3'];
            } elseif ($is_remote == 1) {//1为异地
                $level = explode(",", $regionstr);
                $data['is_remote'] = 1;
                $data['level0'] = 1;
                $data['level1'] = $level[0];
                $data['level2'] = $level[1];
                $data['level3'] = $level[2];
            }
//            $data['desc'] = $goods_desc;
            $data['keywords'] = $keywords;
            $data['number'] = $CommonObj->GeneratingNumber();

            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];

            $HeartproObj->edit($data, $id);

            $datas = array();
            if($id){

                $MoreImage= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/{$this->tablename}/{$id}/",
                    array ('gif', 'jpg', 'jpeg', 'png' ),  Buddha::$buddha_array['upload_maxsize'] )->run('Image')
                    ->getAllReturnArray();
                if(is_array($MoreImage) and count($MoreImage)>0){
                    $moregallery_id = $MoregalleryObj->pcaddimage('file',$MoreImage, $id,$this->tablename,$uid);

                    if(count($moregallery_id)>0){
                        $num = $HeartproObj->setFirstGalleryImgToSupply($id,$this->tablename,'file');
                        if($num==0){
                            $datas['err'] = 5;
                        }
                    }else{
                        $datas['err'] = 6;
                    }
                }
                if($goods_desc){//富文本编辑器图片处理
                    $dirs = PATH_ROOT."storage/quill/{$this->tablename}/{$id}/";
                    if(is_dir($dirs)){
                        if ($dh = opendir($dirs)){
                            while (($file = readdir($dh)) !== false){
                                //$filePath = $dirs.$file;
                                if(!strstr($goods_desc,$file) and $file != '.' and $file !='..'){
                                    @unlink($dirs.$file);//删除修改后的图片
                                    /*echo $file;
                                    exit;*/
                                }
                            }
                        }
                    }
                    $saveData = $MoregalleryObj->base_upload($goods_desc,$id);//base64图片上传
                    if($saveData){
                        $saveData = str_replace(PATH_ROOT,'/', $saveData);//替换
                        $details['details'] = $saveData;
                    }else{
                        $details['details'] = $goods_desc;
                    }
                    $HeartproObj->edit($details,$id);//更新数据
                }


                //$remote为1表示发布异地产品添加订单
                if($is_remote==1){
                    $Db_referral=$ShopObj->getSingleFiledValues(array('referral_id','partnerrate','agent_id','agentrate','level0','level1','level2','level3'),"id='{$shop_id}' and user_id='{$uid}' and isdel=0");
                    $getMoneyArrayFromShop = $ShopObj->getMoneyArrayFromShop($shop_id,0.2);
                    $data=array();
                    $data['good_id'] = $id;
                    $data['user_id'] = $uid;
                    $data['order_sn'] = $OrderObj->birthOrderId($uid);
                    $data['good_table'] = $this->tablename;
                    $data['referral_id'] = $Db_referral['referral_id'];
                    $data['partnerrate']=$Db_referral['partnerrate'];
                    $data['agent_id']=$Db_referral['agent_id'];
                    $data['agentrate']=$Db_referral['agentrate'];
                    $data['pay_type']='third';
                    $data['order_type']='info.market';
                    $data['goods_amt'] = $getMoneyArrayFromShop['goods_amt'];
                    $data['final_amt'] = $getMoneyArrayFromShop['final_amt'];
                    $data['money_plat'] = $getMoneyArrayFromShop['money_plat'];
                    $data['money_agent'] = $getMoneyArrayFromShop['money_agent'];
                    $data['money_partner'] = $getMoneyArrayFromShop['money_partner'];
                    $data['payname']='微信支付';
                    $data['make_level0']=$Db_referral['level0'];
                    $data['make_level1']=$Db_referral['level1'];
                    $data['make_level2']=$Db_referral['level2'];
                    $data['make_level3']=$Db_referral['level3'];
                    $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
                    $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
                    $order_id=$OrderObj->add($data);
                    $datas['isok']='true';
                    $datas['data']='添加成功,去支付。';
                    $datas['url']='/topay/wxpay/wxpayto.php?order_id='.$order_id;
                }else{
                    $datas['isok']='true';
                    $datas['data']=$this->tablenamestr.'编辑成功';
                    $datas['url']='index.php?a=index&c=heartpro';
                }
            }else{
                $datas['isok']='false';
                $datas['data']=$this->tablenamestr.'编辑失败';
                $datas['url']='index.php?a=edit&c=heartpro&id='.$id;

            }
            Buddha_Http_Output::makeJson($datas);

        }

        //消息置顶
        $Top=$OrderObj->getFiledValues(array('final_amt','createtime'),"isdel=0 and good_id='{$id}' and order_type='info.top' and pay_status=1 and user_id='{$uid}'");
        if(count($Top)>0){
            foreach ($Top as $k=>$v){
                $Top[$k]['name']=$goods['name'];
            }
        }
        $this->smarty->assign('Top', $Top);
        $infotop=array('id'=>$id,'good_table'=>$this->tablename,'order_type'=>'info.top','final_amt'=>'0.2');

        $this->smarty->assign('infotop', $infotop);
        $this->smarty->assign('title', $this->tablenamestr);
        $this->smarty->assign('c', $this->tablename);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    /**删除**/
    public function del()
    {
        $id = (int)Buddha_Http_Input::getParameter('id');

        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        //相册删除并且信息删除
        $UsercommonObj=new Usercommon();
        $Db_Usercommon = $UsercommonObj->photoalbumDel('moregallery',$this->tablename,$id,$uid);

        $thumimg=array();
        if($Db_Usercommon){
            $thumimg['isok']='true';
            $thumimg['data']='删除成功';
        }else{
            $thumimg['isok']='false';
            $thumimg['data']='删除失败';
        }
        Buddha_Http_Output::makeJson($thumimg);
    }


    //相册图片删除
    public  function delimage()
    {
        $MoregalleryObj = new Moregallery();
        $HeartproObj = new Heartpro();
        $id = (int)Buddha_Http_Input::getParameter('id');
        $thumimg=array();
        if(!$id)
        {
            $thumimg['isok']='false';
            $thumimg['data']='参数错误';
        }

        $gimages = $MoregalleryObj->fetch($id);

        if ($gimages and $gimages['isdefault']==0){

            $MoregalleryObj->del($id);
            @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
            @unlink(PATH_ROOT . $gimages ['goods_img']);
            @unlink(PATH_ROOT . $gimages ['goods_large']);
            @unlink(PATH_ROOT . $gimages ['sourcepic'] );
            $thumimg['isok']='true';
            $thumimg['data']='图片删除成功';
        }else{
            $MoregalleryObj->del($id);
            @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
            @unlink(PATH_ROOT . $gimages ['goods_img']);
            @unlink(PATH_ROOT . $gimages ['goods_large']);
            @unlink(PATH_ROOT . $gimages ['sourcepic'] );
            $HeartproObj->setFirstGalleryImgToSupply($gimages['goods_id'],$this->tablename,'file');
            $thumimg['isok']='true';
            $thumimg['data']='图片删除成功';

        }
        Buddha_Http_Output::makeJson($thumimg);
    }


//上下架
    public  function shelves()
    {
        $id = (int)Buddha_Http_Input::getParameter('id');
        $thumimg = array();
        $UsercommonObj = new Usercommon();
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $Db_Usercommon = $UsercommonObj->businessshelf($this->tablename,$id,$uid);
        if($Db_Usercommon['is_ok']==1)
        {
            $isok = 'true';
        }else{
            $isok = 'false';
        }

        $thumimg['id'] = $id;
        $thumimg['isok'] = $isok;
        $thumimg['data'] = $Db_Usercommon['is_msg'];
        $thumimg['buttonname'] = $Db_Usercommon['buttonname'];

        Buddha_Http_Output::makeJson($thumimg);
    }


}