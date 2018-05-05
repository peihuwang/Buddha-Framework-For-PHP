<?php


class IndexController extends Buddha_App_Action{

 public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    //http://bendi.com/pcajax/?identify=mobile_home

    public  function index(){

        $identify = Buddha_Http_Input::getParameter('identify');
        $number = Buddha_Http_Input::getParameter('number');
        $lat = Buddha_Http_Input::getParameter('lat');
        $lng = Buddha_Http_Input::getParameter('lng');
        //首页广告位置 $identify = mobile_home
        $RegionObj = new Region();
        $UserObj = new User();
        $ImageObj = new Image();
        $ImagecatalogObj = new Imagecatalog();
        $ShopObj = new Shop();
        $SupplyObj = new Supply();
        $RecruitObj = new Recruit();
        $LeaseObj = new Lease();
        $DemandObj = new Demand();
        //默认锁定不到区域
        $lock_area = 0;
        //默认图片用户是平台

        $jsonarray = array();
        $regionleve3num = $RegionObj->countRecords("isdel=0 and number='{$number}'  and level=3");

        if ($regionleve3num) {
            //可以锁定到区域
            $Db_Region = $RegionObj->getSingleFiledValues(array('id'), "isdel=0 and number='{$number}' and level=3");
            $region_level3_id = $Db_Region['id'];
            //有没有这个区域的代理
            $agent_area_num = $UserObj->countRecords("isdel=0 and groupid=2 and level3='{$region_level3_id}' ");
            if ($agent_area_num){
                //如果存在代理商 取出id 查图片广告
                $Db_User = $UserObj->getSingleFiledValues(array('id'), "isdel=0 and groupid=2 and level3='{$region_level3_id}' ");
                $pic_user_id = $Db_User['id'];
            }else{
                $pic_user_id = 0;
            }
        }

       //查找图片类别
        $Db_Imagecatalog = $ImagecatalogObj->getFiledValues('', "isdel=0 and identify like '%{$identify}%' ");
        if (count($Db_Imagecatalog)) {
            foreach ($Db_Imagecatalog as $k => $v) {
                $cat_id = $v['id'];
                $the_identify = $v['identify'];
                $imagenum = $ImageObj->countRecords("isdel=0 and cat_id='{$cat_id}' and user_id='{$pic_user_id}'");
                if ($imagenum>0) {
                    $Db_Image = $ImageObj->getFiledValues(array('large', 'name', 'link', 'openmethod'), "isdel=0 and cat_id='{$cat_id}' and user_id='{$pic_user_id}'");
                } else {
                    $Db_Image = $ImageObj->getFiledValues(array('large', 'name', 'link', 'openmethod'), "isdel=0 and cat_id='{$cat_id}'and user_id=0");
                }
                if ($Db_Image) {
                    if ($identify != 'pc_home') {
                        $guanggao1 = array(
                            'name' => $the_identify,
                            'list' => $Db_Image,
                            'status' => '0',
                            'message' => 'ok',
                        );
                    } else {
                        $guanggao[] = array(
                            'name' => $the_identify,
                            'list' => $Db_Image,
                            'status' => '0',
                            'message' => 'ok',
                        );
                    }
                }
            }
        }
        if($identify!='pc_home'){
            Buddha_Http_Output::makeJson($guanggao1);
        }

        $locdata = $RegionObj->getLocationDataFromCookie($number);

        //推荐商家
        $reclist = $ShopObj->getFiledValues(array('id', 'small', 'specticloc', 'name'), "isdel=0 and is_sure=1 and state=0 and is_rec=1 {$locdata['sql']} order by  createtime  desc  limit 0,6");
        if ($reclist) {
            $rec = array(
                'list' => $reclist,
                'status' => '0',
                'message' => 'ok',
            );
        }
        //热门供应
        $nwstime = Buddha::$buddha_array['buddha_timestamp'];
        $goodshot = $SupplyObj->getFiledValues(array('id', 'shop_id', 'goods_name', 'market_price', 'promote_price', 'is_promote', 'promote_start_date', 'promote_end_date', 'goods_thumb'), "isdel=0 and is_sure=1 and buddhastatus=0 and is_hot=1 and promote_end_date >=$nwstime {$locdata['sql']}  order by  add_time  desc  limit 0,14");
        if ($goodshot) {
            $hot = array(
                'list' => $goodshot,
                'status' => '0',
                'message' => 'ok',
            );
        }

        //今日促销
        $nwstime = Buddha::$buddha_array['buddha_timestamp'];
        $promote = $SupplyObj->getFiledValues(array('id', 'shop_id', 'goods_name', 'market_price', 'promote_price', 'is_promote', 'promote_start_date', 'promote_end_date', 'goods_thumb'), "isdel=0 and is_sure=1 and buddhastatus=0 and is_hot=1 and promote_end_date>=$nwstime  {$locdata['sql']} order by  add_time  desc  limit 0,15");
        if ($promote) {
            $is_promote = array(
                'list' => $promote,
                'status' => '0',
                'message' => 'ok',
            );
        }

        //最新开业
        $shopnws = $ShopObj->getFiledValues(array('id', 'small', 'specticloc', 'name', 'brief'), "isdel=0 and is_sure=1 and state=0 {$locdata['sql']} order by  createtime  desc  limit 0,9");
        if ($shopnws) {
            $shop = array(
                'list' => $shopnws,
                'status' => '0',
                'message' => 'ok',
            );
        }

        //最新供应
        $goodsnws = $SupplyObj->getFiledValues(array('id', 'shop_id', 'goods_name', 'market_price', 'promote_price', 'is_promote', 'promote_start_date', 'promote_end_date', 'goods_thumb'), "isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']} order by  add_time  desc  limit 0,14");

        if ($goodsnws) {
            $goodnws = array(
                'list' => $goodsnws,
                'status' => '0',
                'message' => 'ok',
            );
        }


        //最新招聘
        $Regionnws = $RecruitObj->getFiledValues(array('id', 'shop_id', 'recruit_name', 'pay'), "isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']} order by  add_time  desc limit 0,2");
        if ($Regionnws) {
            foreach ($Regionnws as $k => $v) {
                $Db_shop = $ShopObj->getSingleFiledValues(array('name', 'specticloc'), "id='{$v['shop_id']}'");

                $Region[] = array(
                    'id' => $v['id'],
                    'name' => $v['recruit_name'],
                    'pay' => $v['pay'],
                    'shop_name' => $Db_shop['name'],
                );
            }

            $RegioNws = array(
                'list' => $Region,
                'status' => '0',
                'message' => 'ok',
            );
        }

        //最新需求
        $Demandnws = $DemandObj->getFiledValues(array('id', 'shop_id', 'name', 'budget'), "isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']} order by  add_time  desc  limit 0,2");
        if ($Demandnws) {
            foreach ($Demandnws as $k => $v) {
                $Db_shop = $ShopObj->getSingleFiledValues(array('name', 'specticloc'), "id='{$v['shop_id']}'");


                $Demand[] = array(
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'shop_name' => $Db_shop['name'],
                    'budget' => $v['budget'],
                );
            }

            $DemandNws = array(
                'list' => $Demand,
                'status' => '0',
                'message' => 'ok',
            );
        }
        //最新租赁
        $Leasenws = $LeaseObj->getFiledValues(array('id', 'shop_id', 'lease_name', 'rent'), "isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']} order by  add_time  desc   limit 0,2");
        if($Leasenws){
        foreach ($Leasenws as $k => $v) {
            $Db_shop = $ShopObj->getSingleFiledValues(array('name', 'specticloc'), "id='{$v['shop_id']}'");

            $Lease[] = array(
                'id' => $v['id'],
                'name' => $v['lease_name'],
                'rent' => $v['rent'],
                'shop_name' => $Db_shop['name'],
            );
        }
            $leaseNws = array(
            'list' =>$Lease,
            'status' => '0',
            'message' => 'ok',
        );
    }

        $jsonarray['guanggao']=$guanggao;
        $jsonarray['reclist']=$rec;
        $jsonarray['goodshot']=$hot;
        $jsonarray['promote']=$is_promote;
        $jsonarray['shop']=$shop;
        $jsonarray['goodsNws']=$goodnws;
        $jsonarray['RegioNws']=$RegioNws;
        $jsonarray['DemandNws']=$DemandNws;
        $jsonarray['leaseNws']=$leaseNws;

        Buddha_Http_Output::makeJson($jsonarray);
    }


}