<?php

/**
 * Class SupplyinfoController
 */
class SupplyinfoController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $act=Buddha_Http_Input::getParameter('act');
        $uid = Buddha_Http_Cookie::getCookie('uid');

//==================================================================
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):1;
        $Today=$Tomorrow=$currentdate=0;
        $Today=strtotime(date('Y-m-d'));//今天0点时间戳
        $Tomorrow=strtotime(date('Y-m-d',strtotime('+1 day')));//明天0点时间戳
        $currentdate=time();//当前时间戳
//==================================================================
        if($act=='list'){
            $keyword=Buddha_Http_Input::getParameter('keyword');
                $where = "isdel=0 and user_id='{$uid}'";
                if($keyword){
                    $where.=" and goods_name like '%{$keyword}%' or goods_sn like '%{$keyword}%'";
                }
//==================================================================
                if($view){
                    switch($view){
                        case 2;
                            $where.=' and isdel=0 and is_sure=0';
                            break;
                        case 3;
                            $where.=" and isdel=0 and is_sure=1";
                            break;
                        case 4;
                            $where.=" and isdel=0 and is_sure=4 ";
                            break;
                        case 5;
//    //////////////↓↓↓↓/////////////////
                    $where.=" and isdel=0 and is_sure=1 and promote_price > 0 and {$Today } <  {$currentdate} and {$currentdate} < {$Tomorrow}";

//    ///////////↑↑↑↑↑↑↑////////////////////
                            break;
                    }
                }
//==================================================================
              //  $rcount = $this->db->countRecords( $this->prefix.'supply', $where);
                $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') :1;
                $pagesize = Buddha::$buddha_array['page']['pagesize'];
            /* $pcount = ceil($rcount/$pagesize);
           if($page > $pcount){
                $page=$pcount;
            }*/

                $orderby = " order by id DESC ";
                $list = $this->db->getFiledValues (array('id','user_id','goods_thumb','goods_name','market_price','goods_sn','is_sure'),  $this->prefix.'supply', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
            $UserObj= new User();
            $sure=$UserObj->getSingleFiledValues(array('groupid'),"id='{$uid}' and isdel=0");
//======================================================================

            foreach($list as $k=>$v){

                if($v['is_sure']==0){
                    if($sure['groupid']==1  || $sure['groupid']==4){
                        $is_sure='business';
                    }else{
                        $is_sure='not';
                    }
                }elseif($v['is_sure']==4){
                    $is_sure='no';
                }else{
                    $is_sure='yes';
                }
                $jsondata[]=array(
                    'id'=>$v['id'],
                    'title'=>$v['goods_name'],
                    'images'=>$v['goods_thumb'],
                    'goods_sn'=>$v['goods_sn'],
                    'user_id'=>$v['user_id'],
/////////////////////////////////////////
                    'is_sure'=>$is_sure,
//////////////////////////////////
                    'goods_name'=>$v['goods_name'],
                    'market_price'=>$v['market_price'],
                    'goods_thumb'=>$v['goods_thumb'],
                );
            }
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
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function add(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $SupplycatObj=new Supplycat();
        $OrderObj=new Order();
        $ShopObj=new Shop();
        $SupplyObj=new Supply();
        $GalleryObj=new Gallery();
        $num=$ShopObj->countRecords("isdel=0 and state=0 and is_sure=1 and user_id='{$uid}'");
          if($num==0){
              Buddha_Http_Head::redirectofmobile('您还没用创建店铺，或者店铺还未通过审核！','index.php?a=index&c=shop',2);
          }

        $goods_name=Buddha_Http_Input::getParameter('good_name');
        $supplycat_id=Buddha_Http_Input::getParameter('shopcat_id');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $goods_unit=Buddha_Http_Input::getParameter('goods_unit');
        $price=Buddha_Http_Input::getParameter('price');
        $keywords=Buddha_Http_Input::getParameter('keywords');

        //商品促销
        $is_promote=Buddha_Http_Input::getParameter('is_promote');
        $promote_price=Buddha_Http_Input::getParameter('promote_price');
        $promote_start_date=Buddha_Http_Input::getParameter('promote_start_date');
        $promote_end_date=Buddha_Http_Input::getParameter('promote_end_date');

        //商品异地发布
        $is_remote=Buddha_Http_Input::getParameter('is_remote');
        $regionstr=Buddha_Http_Input::getParameter('regionstr');

        //描述、图片
        $goods_brief=Buddha_Http_Input::getParameter('goods_brief');
        $goods_desc=Buddha_Http_Input::getParameter('goods_desc');
        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['goods_name']=$goods_name;
            $data['user_id']=$uid;
            $data['goods_sn']=date('ymdmis',time()).rand(10000,99999);
            $data['supplycat_id']=$supplycat_id;
            $data['shop_id']=$shop_id;
            $data['goods_unit']=$goods_unit;
            $data['market_price']=$price;
            $data['keywords']=$keywords;
            $data['add_time']=Buddha::$buddha_array['buddha_timestamp'];
            $data['goods_brief']=$goods_brief;
            $data['goods_desc']=$goods_desc;

                if ($is_promote == 1) {
                    $data['is_promote'] = $is_promote;
                    $data['promote_price'] = $promote_price;
                    $data['promote_start_date'] = strtotime($promote_start_date);
                    $data['promote_end_date'] = strtotime($promote_end_date);
                }

                if($regionstr){
                    $level = explode(",", $regionstr);
                    $data['is_remote']=$is_remote;
                    $data['level0']=1;
                    $data['level1']=$level[0];
                    $data['level2']=$level[1];
                    $data['level3']=$level[2];
                }else{

                    $data['is_remote']=0;
                    $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$uid}' and id='{$shop_id}' and isdel=0");
                    $data['level0']=$Db_level['level0'];
                    $data['level1']=$Db_level['level1'];
                    $data['level2']=$Db_level['level2'];
                    $data['level3']=$Db_level['level3'];
                }

                $good_id = $SupplyObj->add($data);
                $datas = array();
                if($good_id) {
                    $MoreImage= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/supply/{$good_id}/",
                        array ('gif', 'jpg', 'jpeg', 'png' ),  Buddha::$buddha_array['upload_maxsize'] )->run('Image')
                        ->getAllReturnArray();
                    if(is_array($MoreImage) and count($MoreImage)>0) {
                        $GalleryObj->pcaddimage($MoreImage, $good_id);
                        $SupplyObj->setFirstGalleryImgToSupply($good_id);
                    }

                   //$remote为1表示发布异地产品添加订单
                    if($is_remote==1){
                       $Db_referral=$ShopObj->getSingleFiledValues(array('referral_id','partnerrate','agent_id','agentrate','level0','level1','level2','level3'),"id='{$shop_id}' and user_id='{$uid}' and isdel=0");
                        $getMoneyArrayFromShop = $ShopObj->getMoneyArrayFromShop($shop_id,0.2);
                        $data=array();
                        $data['good_id']=$good_id;
                        $data['user_id']=$uid;
                        $data['order_sn']= $OrderObj->birthOrderId($uid);
                        $data['good_table']='shop';
                        $data['referral_id']=$Db_referral['referral_id'];
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
                        $datas['data']='商品添加成功,去支付。';
                        $datas['url']='/topay/wxpay/wxpayto.php?order_id='.$order_id;
                    }else{
                        $datas['isok']='true';
                        $datas['data']='商品添加成功';
                        $datas['url']='index.php?a=index&c=supplyinfo';
                    }
                }else{
                    $datas['isok']='false';
                    $datas['data']='商品添加失败';
                    $datas['url']='index.php?a=add&c=supplyinfo';

            }
                Buddha_Http_Output::makeJson($datas);
        }


        $getshoplistOption=$ShopObj->getShoplistOption($uid,0);
        $gettableOption=$SupplycatObj->getunitOption();
        $this->smarty->assign('gettableOption', $gettableOption);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit(){
        $SupplyObj=new Supply();
        $SupplycatObj=new Supplycat();
        $OrderObj=new Order();
        $ShopObj=new Shop();
        $GalleryObj=new Gallery();
        $RegionObj=new Region();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
          Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=shop',2);
        }
        $goods=$SupplyObj->getSingleFiledValues('',"id='{$id}' and user_id='{$uid}'");
        if(!$goods){
            Buddha_Http_Head::redirectofmobile('没有找到您要的信息！','index.php?a=index&c=shop',2);
        }

        $act=Buddha_Http_Input::getParameter('act');
        $goods_name=Buddha_Http_Input::getParameter('good_name');
        $supplycat_id=Buddha_Http_Input::getParameter('shopcat_id');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $goods_unit=Buddha_Http_Input::getParameter('goods_unit');
        $price=Buddha_Http_Input::getParameter('price');
        $keywords=Buddha_Http_Input::getParameter('keywords');


        //商品促销
        $is_promote=Buddha_Http_Input::getParameter('is_promote');
        $promote_price=Buddha_Http_Input::getParameter('promote_price');
        $promote_start_date=Buddha_Http_Input::getParameter('promote_start_date');
        $promote_end_date=Buddha_Http_Input::getParameter('promote_end_date');

        //商品异地发布
        $is_remote=Buddha_Http_Input::getParameter('is_remote');
        $regionstr=Buddha_Http_Input::getParameter('regionstr');

        //描述、图片
        $goods_brief=Buddha_Http_Input::getParameter('goods_brief');
        $goods_desc=Buddha_Http_Input::getParameter('goods_desc');


            if(Buddha_Http_Input::isPost()){
                $data=array();
                $data['goods_name']=$goods_name;
                $data['user_id']=$uid;
                $data['supplycat_id']=$supplycat_id;
                $data['shop_id']=$shop_id;
                $data['goods_unit']=$goods_unit;
                $data['market_price']=$price;
                $data['keywords']=$keywords;
                $data['goods_brief']=$goods_brief;
                $data['goods_desc']=$goods_desc;
                if($promote_price){
                    $data['is_promote']=$is_promote;
                    $data['promote_price']=$promote_price;
                    $data['promote_start_date']=strtotime($promote_start_date);
                    $data['promote_end_date']=strtotime($promote_end_date);
                }else{
                    $data['is_promote']=0;
                    $data['promote_price']='';
                    $data['promote_start_date']='';
                    $data['promote_end_date']='';
                }
                    if($regionstr){
                        $level = explode(",", $regionstr);
                        $data['is_remote']=$is_remote;
                        $data['level0']=1;
                        $data['level1']=$level[0];
                        $data['level2']=$level[1];
                        $data['level3']=$level[2];
                    }else{
                        $data['is_remote']=0;
                        $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$uid}' and id='{$shop_id}' and isdel=0");
                        $data['level0']=$Db_level['level0'];
                        $data['level1']=$Db_level['level1'];
                        $data['level2']=$Db_level['level2'];
                        $data['level3']=$Db_level['level3'];
                    }
                  $SupplyObj->edit($data,$id);
                $datas = array();
                if($SupplyObj){
                    $MoreImage= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/supply/{$id}/",
                        array ('gif', 'jpg', 'jpeg', 'png' ),  Buddha::$buddha_array['upload_maxsize'] )->run('Image')
                        ->getAllReturnArray();
                    if(is_array($MoreImage) and count($MoreImage)>0) {
                        $GalleryObj->pcaddimage($MoreImage, $id);
                        $SupplyObj->setFirstGalleryImgToSupply($id);
                    }
                    //$is_remote为1表示发布异地产品添加订单
                    if($is_remote==1){
                        $Db_referral=$ShopObj->getSingleFiledValues(array('referral_id','partnerrate','agent_id','agentrate','level0','level1','level2','level3','level4','level5'),"id='{$shop_id}' and user_id='{$uid}' and isdel=0");
                        $getMoneyArrayFromShop = $ShopObj->getMoneyArrayFromShop($shop_id,0.2);
                        $data=array();
                        $data['good_id']=$id;
                        $data['user_id']=$uid;
                        $data['order_sn']= $OrderObj->birthOrderId($uid);
                        $data['good_table']='shop';
                        $data['referral_id']=$Db_referral['referral_id'];
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
                        $data['make_level4']=$Db_referral['level4'];
                        $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
                        $order_id=$OrderObj->add($data);

                        $datas['isok']='true';
                        $datas['data']='商品编辑成功,去支付。';
                        $datas['url']='/topay/wxpay/wxpayto.php?order_id='.$order_id;
                    }else{
                        $datas['isok']='true';
                        $datas['data']='商品编辑成功';
                        $datas['url']='index.php?a=index&c=supplyinfo';
                    }
                }else{
                    $datas['isok']='false';
                    $datas['data']='商品编辑失败';
                    $datas['url']='index.php?a=edit&c=supplyinfo';
                }
                Buddha_Http_Output::makeJson($datas);
            }

        //产品相册
        $gimages=$GalleryObj->getGoodsImage($id);
        $getshoplistOption=$ShopObj->getShoplistOption($uid,$goods['shop_id']);
        $Supplycat=$SupplycatObj->goods_thumbgoods_thumb($goods['supplycat_id']);
        if($Supplycat){
        $cat_name='';
        foreach ($Supplycat as $k=>$v){
            $cat_name.=$v['cat_name'].' > ';
        }
        $goods['cat_name']=Buddha_Atom_String::toDeleteTailCharacter($cat_name);
    }
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
        $gettableOption=$SupplycatObj->getunitOption($goods['goods_unit']);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('gettableOption', $gettableOption);
        $this->smarty->assign('gimages', $gimages);
        $this->smarty->assign('goods', $goods);

       //消息置顶
        $Top=$OrderObj->getFiledValues(array('final_amt','createtime'),"isdel=0 and good_id='{$id}' and order_type='info.top' and pay_status=1 and user_id='{$uid}'");
        if(count($Top)>0){
        foreach ($Top as $k=>$v){
            $Top[$k]['name']=$goods['goods_name'];
        }
        }
        $this->smarty->assign('Top', $Top);
        $infotop=array('good_table'=>'supply','order_type'=>'info.top','final_amt'=>'0.2');
        $this->smarty->assign('infotop', $infotop);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function del(){
        $GalleryObj=new Gallery();
        $SupplyObj=new Supply();
        $id=(int)Buddha_Http_Input::getParameter('id');
       $SupplyObj->del($id);
        $GalleryObj->delGelleryimage($id);
        $thumimg=array();
        if($SupplyObj){
            $thumimg['isok']='true';
            $thumimg['data']='删除成功';
        }else{
            $thumimg['isok']='false';
            $thumimg['data']='删除失败';
        }
        Buddha_Http_Output::makeJson($thumimg);
    }

    //相册图片删除
    public  function delimage(){
        $GalleryObj=new Gallery();
        $SupplyObj=new Supply();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $thumimg=array();
        if(!$id){
            $thumimg['isok']='false';
            $thumimg['data']='参数错误';
        }
        $gimages=$GalleryObj->fetch($id);
        if ($gimages and $gimages['isdefault']==0){
            $GalleryObj->del($id);
            @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
            @unlink(PATH_ROOT . $gimages ['goods_img']);
            @unlink(PATH_ROOT . $gimages ['goods_large']);
            @unlink(PATH_ROOT . $gimages ['sourcepic'] );
            $thumimg['isok']='true';
            $thumimg['data']='图片删除成功';
        }else{
           $GalleryObj->del($id);
            @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
            @unlink(PATH_ROOT . $gimages ['goods_img']);
            @unlink(PATH_ROOT . $gimages ['goods_large']);
            @unlink(PATH_ROOT . $gimages ['sourcepic'] );
         $SupplyObj->setFirstGalleryImgToSupply($gimages['goods_id']);
            $thumimg['isok']='true';
            $thumimg['data']='图片删除成功';
        }

        Buddha_Http_Output::makeJson($thumimg);
    }

    public function supplycat(){
       $SupplycatObj=new Supplycat();
        $fid = Buddha_Http_Input::getParameter('fid');
        $Db_Shopcat= $SupplycatObj->getSupplycatlist($fid);

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

     public function ajaxadderr(){
         $RegionObj=new Region();
         $fid = Buddha_Http_Input::getParameter('fid');
         if($fid==''){
             $fid=1;
         }
         $Db_Region= $RegionObj->getFiledValues(array('id','immchildnum','name','father','level'),"father='{$fid}' and isdel=0");
         $datas = array();
         if($Db_Region){
             $datas['isok']='true';
             $datas['data']=$Db_Region;
         }else{
             $datas['isok']='false';
             $datas['data']='';
         }
         Buddha_Http_Output::makeJson($datas);
     }

    public function fail(){
        $Demandbj=new Demand();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=(int)Buddha_Http_Input::getParameter('id');
        $Db_shop=$Demandbj->getSingleFiledValues(array('remarks'),"isdel=0 and user_id='{$uid}' and id='{$id}'");
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



}