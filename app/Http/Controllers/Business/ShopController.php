<?php

/**
 * Class ShopController
 */
class ShopController extends Buddha_App_Action{
    protected $tablenamestr;
    protected $tablename;
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='店铺';
        $this->tablename='shop';
    }

    /**
     * 一码营销
     */
    public function codesales()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopObj = new Shop();

        //        获取正常的店铺

        $Db_Shop = $ShopObj->getFiledValues(array('id','name'),"user_id='{$uid}' AND isdel=0 AND is_sure=1 AND state=0");

        if(!Buddha_Atom_Array::isValidArray($Db_Shop))
        {
            Buddha_Http_Head::redirectofmobile('您还没用创建店铺，或者店铺还未通过审核，或者已经全部下架！','index.php?a=index&c=shop',2);
        }

        $this->smarty->assign('shop', $Db_Shop);

        $shop_id = (int)Buddha_Http_Input::getParameter('id')?(int)Buddha_Http_Input::getParameter('id'):0;
        if($shop_id)
        {

            $shop_where = "id='{$shop_id}' and user_id='{$uid}'";
            $shopinfo = $ShopObj->getSingleFiledValues(array('small','name'),$shop_where);

            $ShopObj->createQrcodeForCodeSales($shop_id,$shopinfo['small'],$shopinfo['name'],$event='shop',$eventpage='info');
            $shopinfo = $ShopObj->getSingleFiledValues(array('codeimg'),$shop_where);
            if($shopinfo){
                $data['isok']='true';
                $data['data']=$shopinfo['codeimg'];
            }else{
                $data['isok']='false';
                $data['data']='没有数据!';
            }


            Buddha_Http_Output::makeJson($data);
        }

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }




    public function index()
    {
        //exit;
        /*   查询当前用户是否有合伙人
         *   有：则查看店铺有没有合伙人（当前用户有合伙人而店铺没有就要加上）
         */
        $ShopObj=new Shop();
        
        $ShopObj->referral_id_func();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $act=Buddha_Http_Input::getParameter('act');
        $order_id = Buddha_Http_Input::getParameter('order_id');
        $shopNum = $ShopObj->countRecords("user_id='{$uid}'");
        $this->smarty->assign('shopNum',$shopNum);
        if($order_id){
            $OrderObj=new Order();
            //$BillObj=new Bill();
            $OrderObj->batchOrderShareProfit($order_id);
            $orderInfo = $OrderObj->getSingleFiledValues(array('good_id'),"id={$order_id} and good_table='shop' and pay_status=1");
            if($orderInfo){
                $data['is_verify'] = 1;
                $data['veifytime'] = time();
                $data['veryfyendtime'] = time() + 31536000;
                $data['veryfyendtimestr'] = date('Y-m-d H:i:s',time() + 31536000);
                $data['isdel'] = 0;
                $ShopObj->updateRecords($data,"id={$orderInfo['good_id']}");
            }
        }
        if($act=='list'){
            $where = " user_id='{$uid}'";
         // $rcount = $this->db->countRecords( $this->prefix.'shop', $where);
            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
            $pagesize = Buddha::$buddha_array['page']['pagesize'];
         /*   $pcount = ceil($rcount/$pagesize);
            if($page > $pcount){
                $page=$pcount;
            }*/
            $orderby = " order by createtime DESC ";
            $list = $this->db->getFiledValues (array('id','name','small','is_sure','createtimestr','state','number','is_sure','is_verify'),  $this->prefix.'shop', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

            $UsercommonObj = new Usercommon();

            foreach($list as $k=>$v)
            {
                if($v['is_verify']==1){
                    $is_verify='已认证<em>V1</em>';
                }else{
                    $is_verify='店铺未认证<em onclick="verify(json[i].id)">点击认证</em>';
                }

                $listnow[]=array(
                    'id'=>$v['id'],
                    'name'=>$v['name'],
                    'small'=>$v['small'],
                    'number'=>$v['number'],
                    'state'=>$UsercommonObj->businessstatestr($v['state']),
                    'is_sure'=>$UsercommonObj->agentsissure($v['is_sure']),
                    'is_verify'=>$v['is_verify'],
                    'createtime'=>$v['createtimestr'],
                );
            }

            if($listnow){
                $data['isok']='true';
                $data['data']=$listnow;
            }else{
                $data['isok']='false';
                $data['data']='没有数据!';
            }

            Buddha_Http_Output::makeJson($data);
        }

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    /*验证认证码*/
    public function verifycode(){
        $certifObj = new Certification();
        $orderObj = new Order();
        $rzcodes=Buddha_Http_Input::getParameter('rzcodes');//获取用户所填写的认证码
        $time = time();
        $certifinfo = $certifObj->countRecords("code='{$rzcodes}' and is_use=0 and overdue_time>{$time}");
        $orderinfo = $orderObj->countRecords("pay_status = 1 and payname = '{$rzcodes}'");
        if($certifinfo && !$orderinfo){
            $data['isok'] = 'true';
            $data['info'] = '认证码验证通过';
        }else{
            $data['isok'] = 'false';
            $data['info'] = '您输入的认证码不正确或已使用，请联系客服';
        }
        Buddha_Http_Output::makeJson($data);
    }


    //认证码认证
    function authentication_codes(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $shop_id=(int)Buddha_Http_Input::getParameter('shop_id');
        $codes = Buddha_Http_Input::getParameter('codes');
        $certifObj = new Certification();
        $ShopObj = new Shop();
        $OrderObj = new Order();
        $time = time();
        $certifinfo = $certifObj->getSingleFiledValues('',"code='{$codes}' and is_use=0 and overdue_time>{$time}");
        
        if(!$certifinfo){
            $data['isok'] = 2;
            Buddha_Http_Output::makeJson($data);
        }
        $data = array();
        $data['good_id'] = $shop_id;
        $data['user_id'] = $uid;
        $data['order_sn'] = $OrderObj->birthOrderId($uid);
        $data['good_table'] = 'shop';
        $data['pay_status'] =1;
        $data['pay_type'] = 'certification';
        $data['order_type'] = 'shop.v';
        $data['payname'] = $codes;
        $data['make_level0'] = $level0;
        $data['make_level1'] = $level[0];
        $data['make_level2'] = $level[1];
        $data['make_level3'] = $level[2];
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
            $datass['user_id'] = $uid;
            $datass['usetime'] = time();
            $datass['is_use'] = 1;//改变认证码状态
            $certifObj->edit($datass,$certifinfo['id']);
            $data['isok'] = 1;
        }else{
            $data['isok'] = 2;
        }
        Buddha_Http_Output::makeJson($data);
    }

    public  function  add(){
        $UserfeeObj = new Userfee();
        $hsk_is_shop_needverify =isset(Buddha::$buddha_array['cache']['config']['hsk_is_shop_needverify']) ? Buddha::$buddha_array['cache']['config']['hsk_is_shop_needverify']: 1;//是否收费标识
        $this->smarty->assign('hsk_is_shop_needverify',$hsk_is_shop_needverify);
        
        $urls = substr($_SERVER["REQUEST_URI"],1,stripos($_SERVER["REQUEST_URI"],'?'));
        $backurl = urlencode($urls.'a=index&c=shop');
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $times = time();
        $usaerfeeNum = $UserfeeObj->countRecords("user_id='{$uid}' AND fee_type=1 AND isdel=0 AND endtime>'{$times}'");
        $title='店铺';
        $ShopObj=new Shop();
        $RegionObj = new Region();
        $UserObj=new User();
        $OrderObj=new Order();
        $GalleryObj=new Gallery();
        $getNatureOption = $ShopObj->getNatureOption();
        $this->smarty->assign('getNatureOption',$getNatureOption);

        $qq=Buddha_Http_Input::getParameter('qq');
        $wechatnumber=Buddha_Http_Input::getParameter('wechatnumber');
        $name=Buddha_Http_Input::getParameter('name');
        $rzcodes=Buddha_Http_Input::getParameter('rzcodes');//认证码
        $is_verify=Buddha_Http_Input::getParameter('is_verify');

        $shopcat_id=Buddha_Http_Input::getParameter('shopcat');
        $realname=Buddha_Http_Input::getParameter('realname');
        $mobile=Buddha_Http_Input::getParameter('mobile');
        $tel=Buddha_Http_Input::getParameter('tel');
        $opentime=Buddha_Http_Input::getParameter('opentime');
        $level0=Buddha_Http_Input::getParameter('level0');
        $regionstr=Buddha_Http_Input::getParameter('regionstr');
        $specticloc=Buddha_Http_Input::getParameter('specticloc');
        $storetype=Buddha_Http_Input::getParameter('storetype');

        $property=Buddha_Http_Input::getParameter('property');

//        var_dump($storetype);

        $propertyselectid=Buddha_Http_Input::getParameter('propertyselect');
        $bushour=Buddha_Http_Input::getParameter('bushour');
        $myrange=Buddha_Http_Input::getParameter('myrange');
        $brief=Buddha_Http_Input::getParameter('brief');
        $shopdesc=Buddha_Http_Input::getParameter('shopdesc');
        $lnglat = Buddha_Http_Input::getParameter('lnglat');//地图自动获取的经纬度
        if(Buddha_Http_Input::isPost())
        {
//            if(($storetype==2 or $storetype==3 or $storetype==4) )
//            {
//                if(!Buddha_Atom_String::isValidString($property))
//                {
//                    Buddha_Http_Head::redirectofmobile('物业名称不能为空，请更改后再提交！','index.php?a=index&c=shop',2);
//                    echo "< script language='javascript' type='text/javascript'>";
//                }
//            }

//            if(!Buddha_Atom_String::isValidString($property) AND ($storetype!=1 OR $storetype!=5))//沿街商铺和生产制造没有物业名称
//            {
//                Buddha_Http_Head::redirectofmobile('物业名称不能为空，请更改后再提交！','index.php?a=index&c=shop',2);
//            }

            $level=explode(",", $regionstr);
            //拼接地址获取经纬度
            $str = $RegionObj->getAddress($level[2]);
            if(!$lnglat){      
                //获取经纬度
                $lt = $ShopObj->location($str . $specticloc); 
            }else{
                $lnglats = explode(',',$lnglat);
                $lt['lng'] = $lnglats[0];
                $lt['lat'] = $lnglats[1];
            }
            $Db_agentrate = $UserObj->getSingleFiledValues(array('id', 'agentrate'), "isdel=0 and level3='{$level[2]}'and groupid=2");
            $datas = array();
            $datas['wechatnumber'] = $wechatnumber;
            $datas['qq'] = $qq;
            $datas['user_id'] = $uid;
            $datas['agent_id'] = $Db_agentrate['id'];
            $datas['agentrate'] = $Db_agentrate['agentrate'];
            $datas['referral_id'] = 0;
            $datas['partnerrate'] = 0;
            $datas['shopcat_id'] = $shopcat_id;
            $datas['realname'] = $realname;
            $datas['name'] = $name;
            $datas['mobile'] = $mobile;
            $datas['tel'] = $tel;
            $datas['opentime'] = strtotime($opentime);;
            $datas['level0'] = $level0;
            $datas['level1'] = $level[0];
            $datas['level2'] = $level[1];
            $datas['level3'] = $level[2];
            $datas['regionstr'] = $regionstr;
            $datas['lng'] = $lt['lng'];
            $datas['lat'] = $lt['lat'];
            $datas['number'] = date("ymdHis") . mt_rand(1000, 9999);
            $datas['specticloc'] = $specticloc;
            $datas['storetype'] = $storetype;
            $datas['property'] = $property;
            $datas['bushour'] = $bushour;
            $datas['myrange'] = $myrange;
            $datas['brief'] = $brief;
            $datas['state'] = 0;
            $datas['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $datas['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $datas['is_sure'] = 1;
            $datas['isdel'] = 0;
            /*if($hsk_is_shop_needverify == 1){
                if($is_verify == 1){
                    $datas['isdel'] = 5;
                }
            }*/
            $shop_id=$ShopObj->add($datas);
            if($shop_id){
              $Image = Buddha_Http_Upload::getInstance()->setUpload(PATH_ROOT . "storage/shop/{$shop_id}/",
                    array('gif', 'jpg', 'jpeg', 'png'), Buddha::$buddha_array['upload_maxsize'])->run('Image')
                    ->getOneReturnArray();
                if ($Image){
                    $GalleryObj->resolveImageForRotate(PATH_ROOT.$Image,NULL);
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 320, 320, 'S_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 640, 640, 'M_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 1200, 640, 'L_');
                }
                $sourcepic = str_replace("storage/shop/{$shop_id}/", '', $Image);
                $data=array();
                if ($Image) {
                    $data['small'] = "storage/shop/{$shop_id}/S_" . $sourcepic;
                    $data['medium'] = "storage/shop/{$shop_id}/M_" . $sourcepic;
                    $data['large'] = "storage/shop/{$shop_id}/L_" . $sourcepic;
                    $data['sourcepic'] = "storage/shop/{$shop_id}/" . $sourcepic;
                    $ShopObj->edit($data,$shop_id);
                }
                if($shopdesc){//富文本编辑器图片处理
                    $saveData = $GalleryObj->base_upload($shopdesc,$shop_id,'shop');
                    $saveData = str_replace(PATH_ROOT,'/', $saveData);
                    $details['shopdesc'] = $saveData;
                    $ShopObj->edit($details,$shop_id);
                }

                //添加店铺选择认证为1添加订单usernoticedeleting
                if ($is_verify == 1) {
                    $data=array();
                    $getMoneyArrayFromShop = $ShopObj->getMoneyArrayFromShop($shop_id,990);
                    $data['good_id'] = $shop_id;
                    $data['user_id'] = $uid;
                    $data['order_sn'] = $OrderObj->birthOrderId($uid);
                    $data['good_table'] = 'shop';
                    $data['referral_id'] =0;
                    $data['partnerrate'] =0;
                    $data['agent_id'] = (int)$Db_agentrate['id'];
                    $data['agentrate'] = (int)$Db_agentrate['agentrate'];
                    $data['pay_type'] = 'third';
                    $data['order_type'] = 'shop.v';
                    $data['goods_amt'] = $getMoneyArrayFromShop['goods_amt'];
                    $data['final_amt'] = $getMoneyArrayFromShop['final_amt'];
                    $data['money_plat'] = $getMoneyArrayFromShop['money_plat'];
                    $data['money_agent'] = $getMoneyArrayFromShop['money_agent'];
                    $data['money_partner'] = $getMoneyArrayFromShop['money_partner'];
                    $data['payname'] = '微信支付';
                    $data['make_level0'] = $level0;
                    $data['make_level1'] = $level[0];
                    $data['make_level2'] = $level[1];
                    $data['make_level3'] = $level[2];
                    $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                    $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                    $order_id=$OrderObj->add($data);
                    $data=array();
                    $data['isok']='true';
                    $data['data']=$title.'添加成功,去支付';
                    $data['url']='/topay/wxpay/wxpayto.php?order_id='.$order_id.'&backurl='.$backurl;
                    Buddha_Http_Output::makeJson($data);
                }elseif($is_verify == 2){//认证码

                    $certifObj = new Certification();
                    $time = time();
                    $certifinfo = $certifObj->getSingleFiledValues('',"code='{$rzcodes}' and is_use=0 and overdue_time>{$time}");
                    $data = array();
                    $data['good_id'] = $shop_id;
                    $data['user_id'] = $uid;
                    $data['order_sn'] = $OrderObj->birthOrderId($uid);
                    $data['good_table'] = 'shop';
                    $data['pay_status'] =1;
                    $data['pay_type'] = 'certification';
                    $data['order_type'] = 'shop.v';
                    $data['payname'] = $rzcodes;
                    $data['make_level0'] = $level0;
                    $data['make_level1'] = $level[0];
                    $data['make_level2'] = $level[1];
                    $data['make_level3'] = $level[2];
                    $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                    $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                    $order_id=$OrderObj->add($data);
                    if($order_id){
                        $datas = array();
                        $datas['is_verify'] = 1;//改变店铺状态
                        $datas['isdel'] = 0;//改变店铺状态
                        $ShopObj->edit($datas,$shop_id);
                        $datass = array();
                        $datass['is_use'] = 1;//改变认证码状态
                        $certifObj->edit($datass,$certifinfo['id']);
                    }
                    /*$data=array();
                    $data['isok']='true';
                    $data['data']=$title.'添加成功';
                    $data['url']='index.php?a=index&c=shop';*/
                }
                $data=array();
                $data['isok']='true';
                $data['data']=$title.'添加成功';
                $data['url']='index.php?a=index&c=shop';
            }else{
                $data['isok']='false';
                $data['data']=$title.'添加失败';
                $data['url'] = 'index.php?a=index&c=shop';
            }
            Buddha_Http_Output::makeJson($data);
        }

        $PropertyObj = new Property();

        $this->smarty->assign('properties', $PropertyObj->propertylist($UserInfo['level1'],$UserInfo['level2'],$UserInfo['level3']));

        $this->smarty->assign('title', $title);
        $this->smarty->assign('usaerfeeNum', $usaerfeeNum);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $ShopcatObj=new Shopcat();
        $UserObj=new User();
        $OrderObj=new Order();
        $GalleryObj=new Gallery();
        $title='店铺';
        $UserfeeObj = new Userfee();
        $times = time();
        
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=shop',2);
        }
        $shopinfo=$ShopObj->getSingleFiledValues('',"id='{$id}' and user_id='{$uid}'");
        $usaerfeeNum = $UserfeeObj->countRecords("user_id='{$uid}' AND fee_type=1 AND isdel=0 AND endtime>'{$times}'");
        if($shopinfo['is_verify'] == 1 || $usaerfeeNum > 0){
            $number = 1;
        }else{
            $number = 0;
        }
        if(!$shopinfo){
            Buddha_Http_Head::redirectofmobile('没有找到您要的信息！','index.php?a=index&c=shop',2);
        }
            $name = Buddha_Http_Input::getParameter('name');
            $referral_id = Buddha_Http_Input::getParameter('al_id');
            $shopcat_id = Buddha_Http_Input::getParameter('shopcat');
            $realname = Buddha_Http_Input::getParameter('realname');
            $mobile = Buddha_Http_Input::getParameter('mobile');
            $tel = Buddha_Http_Input::getParameter('tel');
            $opentime = Buddha_Http_Input::getParameter('opentime');
            $level0 = Buddha_Http_Input::getParameter('level0');
            $regionstr = Buddha_Http_Input::getParameter('regionstr');
            $specticloc = Buddha_Http_Input::getParameter('specticloc');
            $storetype = Buddha_Http_Input::getParameter('storetype');
            $property = Buddha_Http_Input::getParameter('property');
            $bushour = Buddha_Http_Input::getParameter('bushour');
            $myrange = Buddha_Http_Input::getParameter('myrange');
            $brief = Buddha_Http_Input::getParameter('brief');
            $shopdesc = Buddha_Http_Input::getParameter('shopdesc');
            $qq = Buddha_Http_Input::getParameter('qq');
            $wechatnumber = Buddha_Http_Input::getParameter('wechatnumber');

            $level = explode(",", $regionstr);
            //地址ID转换转成文字
            $str = $RegionObj->getAddress($level[2]);
            //获取经纬度
            $lt = $ShopObj->location($str . $specticloc);

            $Db_agentrate=$UserObj->getSingleFiledValues(array('id','agentrate'),"isdel=0 and level3='{$level[2]}'and groupid=2");
            if (Buddha_Http_Input::isPost()) {
                $datas = array();
                $datas['wechatnumber'] = $wechatnumber;
                $datas['qq'] = $qq;
                $datas['name'] = $name;
                $datas['agent_id'] = (int)$Db_agentrate['id'];
                $datas['agentrate'] = (int)$Db_agentrate['agentrate'];
                $datas['referral_id'] = $referral_id;
                $datas['partnerrate'] = 0;
                $datas['shopcat_id'] = $shopcat_id;
                $datas['realname'] = $realname;
                $datas['mobile'] = $mobile;
                $datas['tel'] = $tel;
                $datas['opentime'] = strtotime($opentime);
                $datas['level0'] = $level0;
                //$datas['level1'] = $level[0];
                //$datas['level2'] = $level[1];
                //$datas['level3'] = $level[2];
                $datas['regionstr'] = $regionstr;
                $datas['lng'] = $lt['lng'];
                $datas['lat'] = $lt['lat'];
                $datas['specticloc'] = $specticloc;
                $datas['storetype'] = $storetype;
                $datas['property'] = $property;
                $datas['bushour'] = $bushour;
                $datas['myrange'] = $myrange;
                $datas['brief'] = $brief;
                //$datas['shopdesc'] =$shopdesc;
                $datas['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                $datas['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];

                $Image = Buddha_Http_Upload::getInstance()->setUpload(PATH_ROOT . "storage/shop/{$id}/",
                    array('gif', 'jpg', 'jpeg', 'png'), Buddha::$buddha_array['upload_maxsize'])->run('Image')
                    ->getOneReturnArray();
                if ($Image) {
                    $GalleryObj->resolveImageForRotate(PATH_ROOT.$Image,NULL);
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 320, 320, 'S_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 640, 640, 'M_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 1200, 640, 'L_');
                }
                $sourcepic = str_replace("storage/shop/{$id}/", '', $Image);
                if ($Image) {
                    $ShopObj->deleteFIleOfPicture($id);
                    $datas['small'] = "storage/shop/{$id}/S_" . $sourcepic;
                    $datas['medium'] = "storage/shop/{$id}/M_" . $sourcepic;
                    $datas['large'] = "storage/shop/{$id}/L_" . $sourcepic;
                    $datas['sourcepic'] = "storage/shop/{$id}/" . $sourcepic;
                }
                $ShopObj->edit($datas,$id);
                if($shopdesc){//富文本编辑器图片处理
                    $saveData = $GalleryObj->base_upload($shopdesc,$id,'shop');
                    $saveData = str_replace(PATH_ROOT,'/', $saveData);
                    $details['shopdesc'] = $saveData;
                    $ShopObj->edit($details,$id);
                }
                $data = array();
                if ($ShopObj) {
                    $data['isok'] = 'true';
                    $data['data'] = $title.'编辑成功';
                    $data['url']='index.php?a=index&c=shop';
                } else {
                    $data['isok'] = 'false';
                    $data['data'] = $title.'编辑失败..';
                    $data['url'] = 'index.php?a=index&c=shop';
                }
               Buddha_Http_Output::makeJson($data);
            }

        $getNatureOption = $ShopObj->getNatureOption($shopinfo['storetype']);
        $agent_area = $RegionObj->getAllArrayAddressByLever($shopinfo['level3']);
        if ($agent_area) {
            $address = '';
            $adderr = '';
            foreach ($agent_area as $k => $v) {
                if ($k > 0 and $k < 4) {
                    $address .= $v['name'] . '>';
                }
            }
            $shopinfo['adderr'] = Buddha_Atom_String::toDeleteTailCharacter($adderr);
            $shopinfo['address'] = Buddha_Atom_String::toDeleteTailCharacter($address);
        }

        $shopcat = $ShopcatObj->goods_thumbgoods_thumb($shopinfo['shopcat_id']);
        if ($shopcat) {
            $cat = '';
            foreach ($shopcat as $k => $v) {
                $cat .= $v['cat_name'] . '>';
            }
            $shopinfo['cat'] = Buddha_Atom_String::toDeleteTailCharacter($cat);
        }
        $this->smarty->assign('shopinfo', $shopinfo);
        $this->smarty->assign('getNatureOption', $getNatureOption);

        //消息置顶
        $Top=$OrderObj->getFiledValues(array('final_amt','createtime'),"isdel=0 and good_id='{$id}' and order_type='info.top' and pay_status=1 and user_id='{$uid}'");
        if(count($Top)>0){
            foreach ($Top as $k=>$v){
                $Top[$k]['name']=$shopinfo['name'];
            }
        }
        $this->smarty->assign('Top', $Top);
        $infotop=array('id'=>$id,'good_table'=>'shop','order_type'=>'info.top','final_amt'=>'0.2');
        $this->smarty->assign('infotop', $infotop);
        $this->smarty->assign('number', $number);
        $this->smarty->assign('title', $title);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function shopcat(){
        $ShopcatObj=new Shopcat();
        $fid = Buddha_Http_Input::getParameter('fid');
        $Db_Shopcat= $ShopcatObj->getShopcatlist($fid);
        $datas = array();
        if($Db_Shopcat){
            $datas['isok']='true';
            $datas['data']=$Db_Shopcat;
        }else{
            $datas['isok']='false';
            $datas['data']='';
        }
        Buddha_Http_Output::makeJson($datas);
    }


    public function arear(){
        $RegionObj=new Region();
        $fid = Buddha_Http_Input::getParameter('fid');
        $Db_arear= $RegionObj->getChildlist($fid);
        $datas = array();
        //print_r($Db_arear);

        if($Db_arear){
            $datas['isok']='true';
            $datas['datas']=$Db_arear;
        }else{
            $datas['isok']='false';
            $datas['datas']='';
        }
        Buddha_Http_Output::makeJson($datas);
    }

    /**
     * 启用/停用店铺
     */
    public  function state()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopObj=new Shop();
        $id=(int)Buddha_Http_Input::getParameter('id');


        $Db_Shop = $ShopObj->businessEnableDisabledShop($id,$uid);

        if( $Db_Shop['is_ok']==1)
        {
            $is_ok = 'true';

        }else{

            $is_ok = 'false';
        }

        $list = array('id'=>$id,'state'=>$Db_Shop['buttonname']);//启用后要显示的标签为  停用
        $datas['isok'] = $is_ok;
        $datas['data'] = $Db_Shop['is_msg'];
        $datas['list'] = $list;

        Buddha_Http_Output::makeJson($datas);
    }


    public function  del()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=(int)Buddha_Http_Input::getParameter('id');
//        $ShopObj=new Shop();
//        $result = $ShopObj->del($id);

        $UsercommonObj = new Usercommon();
        $Db_Usercommon = $UsercommonObj->businessDelShopAndBelongByShopid($id,$uid);

        if($Db_Usercommon){
//            $ShopObj-> delshop($uid,$id);
            $datas['isok']='true';
            $datas['data']=$id;
        }else{
            $datas['isok']='false';
            $datas['data']='删除失败';
        }
        Buddha_Http_Output::makeJson($datas);
    }

    public  function  verifya(){//店铺认证
        $urls = substr($_SERVER["REQUEST_URI"],1,stripos($_SERVER["REQUEST_URI"],'?'));
        $backurl = urlencode($urls.'a=index&c=shop');
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopObj=new Shop();
        $OrderObj=new Order();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $shopinfo= $ShopObj->fetch($id);
        $getMoneyArrayFromShop = $ShopObj->getMoneyArrayFromShop($id,990);
        $data=array();
        $data['good_id']=$id;//指定产品id
        $data['user_id']=$uid;
        $data['order_sn']= $OrderObj->birthOrderId($uid);//订单编号
        $data['good_table']='shop';//哪个表
        $data['referral_id']=$shopinfo['referral_id'];//业务员id
        $data['partnerrate']=$shopinfo['partnerrate'];//合伙人提成比例
        $data['agent_id']=$shopinfo['agent_id'];//代理商id
        $data['agentrate']=$shopinfo['agentrate'];//代理商提成比例
        $data['pay_type']='third';//third第三方支付，point积分，balance余额
        $data['order_type']='shop.v';//money.out提现, 店铺认证shop.v,信息置顶info.top ,跨区域信息推广info.market,信息查看info.see
        /*if($uid == 3903){
            $data['goods_amt']=0.1;//产品价格
            $data['final_amt']=0.1;//产品最终价格
        }else{
            $data['goods_amt']=$getMoneyArrayFromShop['goods_amt'];//产品价格
            $data['final_amt']=$getMoneyArrayFromShop['final_amt'];//产品最终价格
        }*/
        $data['goods_amt']=$getMoneyArrayFromShop['goods_amt'];//产品价格
        $data['final_amt']=$getMoneyArrayFromShop['final_amt'];//产品最终价格
        $data['money_agent']=$getMoneyArrayFromShop['money_agent'];//代理商分润金额
        $data['money_plat']=$getMoneyArrayFromShop['money_plat'];//平台分润金额
        $data['money_partner']=$getMoneyArrayFromShop['money_partner'];//合伙人分润金额
        $data['payname']='微信支付';
        $data['make_level0']=$shopinfo['level0'];//国家
        $data['make_level1']=$shopinfo['level1'];//省
        $data['make_level2']=$shopinfo['level2'];//市
        $data['make_level3']=$shopinfo['level3'];//区县
        $data['make_level4']=$shopinfo['level4'];//乡镇
        $data['make_level5']=$shopinfo['level5'];
        $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];//时间戳
        $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];//时间日期
        $order_id=$OrderObj->add($data);
         if($OrderObj){
             $datas['isok']='true';
             $datas['data']='/topay/wxpay/wxpayto.php?order_id='.$order_id.'&backurl='.$backurl;
         }else{
             $datas['isok']='false';
             $datas['data']='认证失败';
         }
        Buddha_Http_Output::makeJson($datas);
    }

    public function fail(){
        $ShopObj=new Shop();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=(int)Buddha_Http_Input::getParameter('id');
        $Db_shop=$ShopObj->getSingleFiledValues(array('remarks'),"isdel=0 and user_id='{$uid}' and id='{$id}'");
        $failinfo=array();
        if($Db_shop){
            $failinfo['isok']=0;
            $failinfo['remarks']=$Db_shop['remarks'];
        }else{
            $failinfo['isok']=1;
            $failinfo['data']='错误';
            $failinfo['remarks']='';
        }
     Buddha_Http_Output::makeJson($failinfo);
    }
    public function mylist(){
        $order_id=(int)Buddha_Http_Input::getParameter('order_id');
        $user_id = (int)Buddha_Http_Cookie::getCookie('uid');
        $ShopObj=new Shop();
        $OrderObj=new Order();
        $RegionObj=new Region();
        $UserObj=new User();
        $locdata = $RegionObj->getLocationDataFromCookie();
        $view=Buddha_Http_Input::getParameter('view')? Buddha_Http_Input::getParameter('view'):'promote';

        $id=(int)Buddha_Http_Input::getParameter('id');
        $act=Buddha_Http_Input::getParameter('act');
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误','index.php?a=index&c=list');
        }
        $shopinfo=$ShopObj->fetch($id);
        if(!$shopinfo){
            Buddha_Http_Head::redirectofmobile('信息不存在','index.php?a=index&c=list');
        }
 ///////////////////////////////////
        if ($shopinfo['is_verify']==0){
            //$UserObj=new User();
            //$user = $UserObj->getSingleFiledValues(array('onlineregtime'),"id={$shop['user_id']}");
            //$createtime=$user['onlineregtime'];//免费7天的开始时间
            $endtime = strtotime($shopinfo['createtimestr']) + 7*86400;//免费7天的结束时间
            $newtime=time();
            if($newtime< $endtime){
                $shopinfo['verify']=1;
            }else{
                $shopinfo['verify']=0;
            }
        }
        //print_r($shopinfo);
        $start = time()-15*60; //付费查看电话过期时间
        if($user_id){
            $see= $OrderObj->countRecords("user_id='{$user_id}' and good_id='{$id}' and pay_status=1 and createtime>=$start");
        }else{
            $see= $OrderObj->countRecords("id='{$order_id}' and good_id='{$id}' and pay_status=1 and createtime>=$start");
        }
        $this->smarty->assign('shopinfo',$shopinfo);
        $this->smarty->assign('see',$see);


        ////////分享
        switch($view){
            case 'promote':
                $titles = '促销';
                break;
            case 'supply':
                $titles = '供应';
                break;
            case 'demand':
                $titles = '需求';
                break;
            case 'recruit':
                $titles = '招聘';
                break;
            case 'lease':
                $titles = '租赁';
                break;
        }
        $names = $ShopObj->getSingleFiledValues(array('name','small'),"id={$id}");
        $contentss = '本地商家网：实体商家展示新渠道、广告传播新工具';
        $WechatconfigObj  = new Wechatconfig();
        if($goods['promote_price'] != '0.00'){
           $goods['jia'] =  $goods['promote_price'];
        }else{
            $goods['jia'] =  $goods['market_price'];
        }
        $sharearr = array(
            'share_title'=>$names['name'] .'('.$titles.')',
            'share_desc'=>$contentss,
            'ahare_link'=>"index.php?a=".__FUNCTION__."&c=".$this->c,
            'share_imgUrl'=>'/'.$names['small'],
        );
        $WechatconfigObj->getJsSign($sharearr['share_title'],$sharearr['share_desc'],$sharearr['ahare_link'],$sharearr['share_imgUrl']);
        ////////分享
        if($act=='list'){
            $view1=$view;
            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
            $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') : 15;
            $where = " isdel=0 and is_sure=1 and  buddhastatus=0 and shop_id='{$id}' and is_promote=1 {$locdata['sql']}";

            if($view=='supply'){
                $where = " isdel=0 and is_sure=1 and  buddhastatus=0 and shop_id='{$id}' ";
            $fields = array('id', 'shop_id', 'goods_name', 'market_price', 'promote_price', 'is_promote', 'promote_start_date', 'promote_end_date', 'goods_thumb');
            }elseif($view=='demand'){
                $where = " isdel=0 and is_sure=1 and  buddhastatus=0 and shop_id='{$id}'";
            $fields = array('id', 'shop_id','user_id', 'name','budget', 'demand_thumb');
            }elseif($view=='recruit'){
                $where = " isdel=0 and is_sure=1 and  buddhastatus=0 and shop_id='{$id}'";
                $fields = array('id', 'shop_id','user_id', 'recruit_name','pay');
            }elseif($view=='lease'){
                $where = " isdel=0 and is_sure=1 and  buddhastatus=0 and shop_id='{$id}'";
               $fields = array('id','shop_id','user_id', 'lease_name','rent', 'lease_thumb');
            }elseif($view=='promote'){
                $where = " isdel=0 and is_sure=1 and  buddhastatus=0 and shop_id='{$id}' and is_promote=1";
                $view1='supply';
                $fields = array('id', 'shop_id', 'goods_name', 'market_price', 'promote_price', 'is_promote', 'promote_start_date', 'promote_end_date', 'goods_thumb');
            }
            $orderby = " order by add_time DESC ";
            $list = $this->db->getFiledValues($fields,$this->prefix.$view1, $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

            if($view=='supply'){
                $nwstiem=time();
            foreach($list as $k=>$v){
                $shop=$ShopObj->getSingleFiledValues(array('name','specticloc','lng','lat'),"id='{$id}}'");
                $distance=$RegionObj->getDistance($locdata['lng'],$locdata['lat'],$shop['lng'],$shop['lat'],2);
                if($shop['specticloc']=='0'){
                    $roadfullname='';
                }else{
                    $roadfullname=$shop['specticloc'];
                }
                if($v['is_promote']==1){
                    if($nwstiem>$v['promote_start_date'] and $v['promote_end_date']>$nwstiem){
                        $price=$v['promote_price'];
                    }else{
                        $price= $v['market_price'];
                    }
                }else{
                    $price= $v['market_price'];
                }
                $mylist[]=array(
                    'id'=>$v['id'],
                    'goods_name'=>$v['goods_name'],
                    'is_promote'=>$v['is_promote'],
                    'price'=>$price,
                    'shop_name'=>$shop['name'],
                    'distance'=>$distance,
                    'roadfullname'=>$roadfullname,
                    'goods_thumb'=>$v['goods_thumb'],
                );
            }
            }elseif($view=='demand') {
                foreach ($list as $k => $v) {
                    if ($v['shop_id'] != '0') {
                        $Db_shop = $ShopObj->getSingleFiledValues(array('name', 'specticloc', 'lng', 'lat'), "id='{$v['shop_id']}'");
                        $name = $Db_shop['name'];
                        if ($Db_shop['roadfullname'] == '0') {
                            $roadfullname = '';
                        } else {
                            $roadfullname = $Db_shop['specticloc'];
                        }
                    } else {
                        $Db_user = $UserObj->getSingleFiledValues(array('username', 'realname', 'address'), "id='{$v['user_id']}'");
                        if ($Db_user['address'] == '0') {
                            $roadfullname = '';
                        } else {
                            $roadfullname = $Db_user['address'];
                        }
                        if ($Db_user['realname'] == '0') {
                            $name = $Db_user['username'];
                        } else {
                            $name = $Db_user['realname'];
                        }
                    }
                    $mylist[] = array(
                        'id' => $v['id'],
                        'name' => $v['name'],
                        'price' => $v['budget'],
                        'shop_name' => $name,
                        'roadfullname' => $roadfullname,
                        'demand_thumb' => $v['demand_thumb']
                    );
                }
            }elseif($view=='recruit'){
                foreach($list as $k=>$v){
                    $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc','lng','lat'),"id='{$v['shop_id']}'");
                    $name=$Db_shop['name'];
                    if($Db_shop['roadfullname']=='0'){
                        $roadfullname='';
                    }else{
                        $roadfullname=$Db_shop['specticloc'];
                    }
                    $mylist[]=array(
                        'id'=>$v['id'],
                        'name'=>$v['recruit_name'],
                        'price'=>$v['pay'],
                        'shop_name'=>$name,
                        'roadfullname'=>$roadfullname,
                    );
                }
            }elseif($view=='lease'){
                foreach($list as $k=>$v){
                    if($v['shop_id']!='0'){
                        $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc','lng','lat'),"id='{$v['shop_id']}'");
                        $name=$Db_shop['name'];
                        if($Db_shop['roadfullname']=='0'){
                            $roadfullname='';
                        }else{
                            $roadfullname=$Db_shop['specticloc'];
                        }
                    }else{
                        $Db_user=$UserObj->getSingleFiledValues(array('username','realname','address'),"id='{$v['user_id']}'");
                        if($Db_user['address']=='0'){
                            $roadfullname='' ;
                        }else{
                            $roadfullname=$Db_user['address'];
                        }
                        if($Db_user['realname']=='0'){
                            $name=$Db_user['username'];
                        }else{
                            $name=$Db_user['realname'];
                        }
                    }

                    $mylist[]=array(
                        'id'=>$v['id'],
                        'lease_name'=>$v['lease_name'],
                        'price'=>$v['rent'],
                        'shop_name'=>$name,
                        'roadfullname'=>$roadfullname,
                        'lease_thumb'=>$v['lease_thumb'],
                    );
                }

            }elseif($view=='promote'){
                foreach($list as $k=>$v){
                    $shop=$ShopObj->getSingleFiledValues(array('name','specticloc','lng','lat'),"id='{$id}}'");
                    $distance=$RegionObj->getDistance($locdata['lng'],$locdata['lat'],$shop['lng'],$shop['lat'],2);
                    if($shop['specticloc']=='0'){
                        $roadfullname='';
                    }else{
                        $roadfullname=$shop['specticloc'];
                    }
                    $mylist[]=array(
                        'id'=>$v['id'],
                        'goods_name'=>$v['goods_name'],
                        'is_promote'=>$v['is_promote'],
                        'price'=>$v['promote_price'],
                        'shop_name'=>$shop['name'],
                        'distance'=>$distance,
                        'roadfullname'=>$roadfullname,
                        'goods_thumb'=>$v['goods_thumb'],
                    );
                }
            }

            $data=array();
            if($mylist){
                $data['isok']='true';
                $data['list']=$mylist;
                $data['data']='加载完成';

            }else{
                $data['isok']='false';
                $data['list']='';
                $data['data']='没数据了';
            }
            Buddha_Http_Output::makeJson($data);
        }
        $this->smarty->assign('view',$view);
        $this->smarty->assign('shop',$id);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function info(){
        $user_id = (int)Buddha_Http_Cookie::getCookie('uid');
        $ShopObj=new Shop();
        $OrderObj=new Order();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $order_id=(int)Buddha_Http_Input::getParameter('order_id');

        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误','index.php?a=index&c=list');
        }
        $shop=$ShopObj->fetch($id);
        if(!$shop){
            Buddha_Http_Head::redirectofmobile('信息不存在','index.php?a=index&c=list');
        }
        //判断用户是否认证：非认证显示7天（is_verify）
        if ($shop['is_verify']==0){
            //$UserObj=new User();
            //$user = $UserObj->getSingleFiledValues(array('onlineregtime'),"id={$shop['user_id']}");
            //$createtime=$user['onlineregtime'];//免费7天的开始时间
            $endtime = strtotime($shop['createtimestr']) + 7*86400;//免费7天的结束时间
            $newtime=time();
            if($newtime< $endtime){
                $shop['verify']=1;
            }else{
                $shop['verify']=0;
            }
        }
        //print_r($shop);
///////////////////////////////////
        $start = time()-15*60; //付费查看电话过期时间
        if($user_id){
            $see= $OrderObj->countRecords("user_id='{$user_id}' and good_id='{$id}' and pay_status=1 and createtime>=$start");
        }else{
            $see= $OrderObj->countRecords("id='{$order_id}' and good_id='{$id}' and pay_status=1 and createtime>=$start");
        }
        
        $this->smarty->assign('shop',$shop);
        $this->smarty->assign('see',$see);
        ////////分享
        $WechatconfigObj  = new Wechatconfig();
        if($shop['brief']){
            $brief = $shop['brief'];
        }else{
            $brief = '商家/个人:免费发布各类产品,需求,招聘,租赁,促销，活动,简介,地址,导航,名片,传单等功能。';
        }
        if($shop['small']){
            $share_imgUrl = $shop['small'];
        }else{
           $share_imgUrl = 'style/images/index_sq.png';
        }
        $sharearr = array(
            'share_title'=>$shop['name'],
            'share_desc'=>$brief,            
            'ahare_link'=>"index.php?a=".__FUNCTION__."&c=".$this->c,
            'share_imgUrl'=>$share_imgUrl,
        );
        $WechatconfigObj->getJsSign($sharearr['share_title'],$sharearr['share_desc'],$sharearr['ahare_link'],$sharearr['share_imgUrl']);
        ////////分享
        ////////我的店铺头部信息
        //print_r($user_id);
        $header_Category= $this->header_title();
        $this->smarty->assign('header_category',$header_Category);
        $this->smarty->assign('user_id',$user_id);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function header_title(){
        ////////我的店铺头部信息
        $header_Category=array(
            0=>array('id'=>1,'name'=>'供应','err'=>'supply','img'=>'supply'),
            1=>array('id'=>2,'name'=>'需求','err'=>'demand','img'=>'need'),
            2=>array('id'=>3,'name'=>'招聘','err'=>'recruit','img'=>'recruit'),
            3=>array('id'=>4,'name'=>'租赁','err'=>'lease','img'=>'lease'),
            4=>array('id'=>5,'name'=>'简介','err'=>'abstract','img'=>'intro'),
            5=>array('id'=>6,'name'=>'名片','err'=>'card','img'=>'about'),
//            6=>array('id'=>7,'name'=>'导航','err'=>'navigation','img'=>'nav'),
//            7=>array('id'=>8,'name'=>'活动','err'=>'activity','img'=>'campaign'),
//            8=>array('id'=>9,'name'=>'房屋','err'=>'house','img'=>'building'),
            9=>array('id'=>10,'name'=>'促销','err'=>'promote','img'=>'sales'),
        );
        return $header_Category;
    }

    /**
     *  判断物业名称是否已经存在
     */
    public function isproperty()
    {
        $property = Buddha_Http_Input::getParameter('property');
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $PropertyObj  = new Property();
        $Db_Usercommon = $PropertyObj->isExistence($UserInfo['level1'],$UserInfo['level2'],$UserInfo['level3'],$property);

        if (!$Db_Usercommon)
        {
            $datas['isok'] = 'true';
            $datas['data'] = '该物业名称可用';
        } else {
            $datas['isok'] = 'false';
            $datas['data'] = '对不起，该物业名称已经存在了';
        }

        Buddha_Http_Output::makeJson($datas);

    }

}