<?php


class IndexController extends Buddha_App_Action{

 public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }


    //体现接口
    public function withdrawals(){
        $user_id = Buddha_Http_Input::getParameter('user_id');
        $banlance = Buddha_Http_Input::getParameter('banlance');

    }


    public function getJsSign(){
        $type ='wechat';
        $son ='token';
        $WechatconfigObj = new Wechatconfig();
        $wetchatconfig=$WechatconfigObj->getSingleFiledValues(''," type='{$type}'
      and son='{$son}'
      ");

        $options = array(
            'encodingaeskey'=>$wetchatconfig['encodingaeskey'], //填写加密用的EncodingAESKey
            'appid'=>$wetchatconfig['appid'], //填写高级调用功能的app id
            'appsecret'=>$wetchatconfig['appsecret'] //填写高级调用功能的密钥
        );
        $weObj = new Buddha_Bridge_Wechat($options);
        $getJsSignData = $weObj->getJsSign();
        Buddha_Http_Output::makeJson($getJsSignData);
    }

    //http://bendi.com/ajax/?identify=mobile_home

    public  function index()
    {
        $identify = Buddha_Http_Input::getParameter('identify');
        $number = Buddha_Http_Input::getParameter('number');
        $lat = Buddha_Http_Input::getParameter('lat');
        $lng = Buddha_Http_Input::getParameter('lng');
        //首页广告位置 $identify = mobile_home
        $RegionObj= new Region();
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
        $pic_user_id =0;
        $jsonarray =array();
        $regionleve3num = $RegionObj->countRecords("isdel=0 and number='{$number}' and level=3 ");
        if($regionleve3num){
            //可以锁定到区域
            $Db_Region = $RegionObj->getSingleFiledValues(array('id'),"isdel=0 and number='{$number}' and level=3 ");
            $region_level3_id = $Db_Region['id'];
            //有没有这个区域的代理
            $agent_area_num =$UserObj->countRecords("isdel=0 and groupid=2 and level3='{$region_level3_id}' ");
            if($agent_area_num){
                //如果存在代理商 取出id 查图片广告
                $Db_User = $UserObj->getSingleFiledValues(array('id'),"isdel=0 and groupid=2 and level3='{$region_level3_id}' ");
                $pic_user_id = $Db_User['id'];
            }
        }
        //查找图片类别
      $Db_Imagecatalog= $ImagecatalogObj->getFiledValues('',"isdel=0 and identify like '%{$identify}%' ");
        if(count($Db_Imagecatalog)){
            foreach($Db_Imagecatalog as $k=>$v){
                $cat_id = $v['id'];
                 $the_identify = $v['identify'];
                $imagenum = $ImageObj->countRecords("isdel=0 and cat_id='{$cat_id}' and user_id='{$pic_user_id}'");
   
                if ($imagenum>0) {
                    $Db_Image = $ImageObj->getFiledValues(array('large', 'name', 'link', 'openmethod','shop_id'), "isdel=0 and cat_id='{$cat_id}' and user_id='{$pic_user_id}'");
                } else {
                    $Db_Image = $ImageObj->getFiledValues(array('large', 'name', 'link', 'openmethod','shop_id'), "isdel=0 and cat_id='{$cat_id}'and user_id=0");
                }
                if($Db_Image){
                    if($identify!='mobile_home'){
                        $guanggao1 = array(
                            'name'=>$the_identify,
                            'image'=>$Db_Image,
                        );
                    }else{
                        $guanggao[]=array(
                            'name'=>$the_identify,
                            'image'=>$Db_Image,
                        );
                    }
                }
            }
        }
        if($identify!='mobile_home'){
            Buddha_Http_Output::makeJson($guanggao1);
        }

        //*推荐店铺*//
        $locdata = $RegionObj->getLocationDataFromCookie();
        $Shoptot = $ShopObj->getFiledValues(array('id','name','small','brief','lng','lat','specticloc'), "isdel=0 and is_sure=1 and state=0 and is_rec=1  {$locdata['sql']} order by  toptime,createtime  desc  limit 0,4");
        if(count($Shoptot)<1){
            $Shoptot = $ShopObj->getFiledValues(array('id','name','small','brief','lng','lat','specticloc'), "id in(3479,1647,4768,6795,1) order by  createtime  desc");//没有数据显示默认
        }
        foreach ($Shoptot as $k => $v) {
            $distance = $RegionObj->getDistance($lng,$lat,$v['lng'],$v['lat'],2);
            if(mb_strlen($v['brief']) > 35){
                $v['brief'] = mb_substr($v['brief'],0,35) . '...';
            }
            if(mb_strlen($v['name']) > 18){
                $v['name'] = mb_substr($v['name'],0,18) . '...';
            }

            $shopRec[]=array(
                'id'=>$v['id'],
                'name'=>$v['name'],
                'brief'=>$v['brief'],
                'small'=>$v['small'],
                'roadfullname'=>$v['specticloc'],
                'distance'=>$distance,
            );
        };

       //最近开业
        $Shopnews = $ShopObj->getFiledValues(array('id','name','small','brief','lng','lat','specticloc'), "isdel=0 and is_sure=1 and state=0 {$locdata['sql']} {$ShopObj->openedrecently()} ORDER BY toptime,createtime desc limit 0,4");

        if(count($Shopnews)<1)
        {
            $Shopnews = $ShopObj->getFiledValues(array('id','name','small','brief','lng','lat','specticloc'), " id in(5730,5695,5702,3479) ORDER BY createtime desc");//没有数据显示默认
        }
        foreach ($Shopnews as $k => $v)
        {
            $distance = $RegionObj->getDistance($lng,$lat,$v['lng'],$v['lat'],2);
            if(mb_strlen($v['brief']) > 35){
                $v['brief'] = mb_substr($v['brief'],0,35) . '...';
            }
            if(mb_strlen($v['name']) > 18){
                $v['name'] = mb_substr($v['name'],0,18) . '...';
            }
            $shopNws[]=array(
                'id'=>$v['id'],
                'name'=>$v['name'],
                'small'=>$v['small'],
                'brief'=>$v['brief'],
                'roadfullname'=>$v['specticloc'],
                'distance'=>$distance,
            );
        }
        //店铺促销
//        $SupplyObj = new Supply();
//        $ShopPro = $SupplyObj->getFiledValues(array( 'id','user_id','shop_id','goods_name','promote_start_date','promote_end_date','goods_brief','goods_thumb','promote_price'),"isdel=0 and is_sure=1 and buddhastatus=0 and is_promote=1 {$locdata['sql']}  group by shop_id  order by toptime,add_time DESC limit 0,4");

        $Supply_where = "isdel=0 and is_sure=1 and buddhastatus=0 and is_promote=1 {$locdata['sql']} ";
        $Supply_filed = array( 'id','user_id','shop_id','goods_name','promote_start_date','promote_end_date','goods_brief','goods_thumb','promote_price');
        $CommonindexObj = new Commonindex();
        $ShopPro =  $CommonindexObj->newestmore('supply',$Supply_filed,1,4,$Supply_where);


        if(count($ShopPro)<1){
            $ShopPro = $SupplyObj->getFiledValues(array( 'id','user_id','shop_id','goods_name','promote_start_date','promote_end_date','goods_brief','goods_thumb','promote_price')," id in(791,289,1855,1321) order by add_time DESC");//没有数据显示默认
        }                       
        //print_r($ShopPro);                                       
        foreach ($ShopPro as $k => $v) {
            $jingwei = $ShopObj->getSingleFiledValues(array('lat','lng','specticloc'),"id={$v['shop_id']}");
            $distance=$RegionObj->getDistance($lng,$lat,$jingwei['lng'],$jingwei['lat'],2);
            if(mb_strlen($v['goods_brief']) > 35){
                $v['goods_brief'] = mb_substr($v['goods_brief'],0,35) . '...';
            }
            if(mb_strlen($v['goods_name']) > 18){
                $v['goods_name'] = mb_substr($v['goods_name'],0,18) . '...';
            }
            
            $shopPro[]=array(
                'id'=>$v['id'],
                'name'=>$v['goods_name'],
                'small'=>$v['goods_thumb'],
                'brief'=>$v['goods_brief'],
                'roadfullname'=>$jingwei['specticloc'],
                'distance'=>$distance,
            );
        } 
        //热门活动
        $ActivityObj= new Activity();
        $time = time();
//        $Actwhere = " isdel=0 and is_sure=1 and buddhastatus=0 and {$time}<=end_date AND is_hot=1 {$locdata['sql']} order by toptime,add_time DESC  limit 0,4";
        $Actwhere = " isdel=0 and is_sure=1 and buddhastatus=0 and {$time}<=end_date AND is_hot=1 {$locdata['sql']} ";
        $filed=array('id','name','activity_thumb','brief','address','type','shop_id');
//        $Shophot = $ActivityObj->getFiledValues($filed, $Actwhere);


        $CommonindexObj = new Commonindex();
        $Shophot =  $CommonindexObj->newestmore('activity',$filed,1,4,$Actwhere);


        if(!Buddha_Atom_Array::isValidArray($Shophot)){
            $Actwhere = " isdel=0 and is_sure=1 and buddhastatus=0 and {$time}<=end_date  {$locdata['sql']} order by click_count DESC  limit 0,4";
        }
        $Shophot = $ActivityObj->getFiledValues($filed, $Actwhere);

        if(count($Shophot)<1){
            $Shophot = $ActivityObj->getFiledValues($filed, " id in(176,177) order by  add_time DESC");//没有数据显示默认
        }
        //print_r($ShopPro);
        foreach ($Shophot as $k => $v) {
            //$distance=$RegionObj->getDistance($lng,$lat,$v['lng'],$v['lat'],2);
            if(mb_strlen($v['brief']) > 35){
                $v['brief'] = mb_substr($v['brief'],0,35) . '...';
            }
            if(mb_strlen($v['name']) > 18){
                $v['name'] = mb_substr($v['name'],0,18) . '...';
            }

            $brief=mb_substr($v['brief'],0,32).'...';
            $address=mb_substr($v['address'],0,18);
            if($v['type']==3){
                $a='vodelist';
            }else{
                $a='mylist';
            }
            $shopHot[]=array(
                'id'=>$v['id'],
                'name'=>$v['name'],
                'small'=>$v['activity_thumb'],
                'brief'=>$brief,
                'roadfullname'=>$address,
                'a'=>$a,
//                'distance'=>$distance,
            );
        }

        //最新供应
//        $goodsnws = $SupplyObj->getFiledValues(array('id','shop_id','goods_name','market_price','promote_price','is_promote','promote_start_date','promote_end_date','goods_thumb'),"isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']} group by shop_id order by toptime,add_time DESC limit 0,4");// desc,last_update


        $Supply_where = "isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']}";
        $Supply_filed = array('id','shop_id','goods_name','market_price','promote_price','is_promote','promote_start_date','promote_end_date','goods_thumb');
        $CommonindexObj = new Commonindex();
        $goodsnws =  $CommonindexObj->newestmore('supply',$Supply_filed,1,4,$Supply_where);


        if(count($goodsnws)<1){
            $goodsnws=$SupplyObj->getFiledValues(array('id','shop_id','goods_name','market_price','promote_price','is_promote','promote_start_date','promote_end_date','goods_thumb')," id in(1880,1882,1883,1884) order by  add_time DESC ");
        }//没有数据显示默认
        $nwstiem=time();
        foreach($goodsnws as $k=>$v){
            $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc'),"id='{$v['shop_id']}'");
           if($Db_shop['roadfullname']==0){
               $roadfullname='';
           }else{
               $roadfullname=$Db_shop['specticloc'];
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
           if(mb_strlen($v['goods_name']) > 18){
                $v['goods_name'] = mb_substr($v['goods_name'],0,18) . '...';
            }
            $goodsNws[]=array(
              'id'=>$v['id'],
              'name'=>$v['goods_name'],
              'is_promote'=>$v['is_promote'],
              'price'=>$price,
              'shop_name'=>$Db_shop['name'],
              'roadfullname'=>$roadfullname,
              'goods_thumb'=>$v['goods_thumb'],
          );
        }
        //最新招聘
//        $Regionnws = $RecruitObj->getFiledValues(array('id','shop_id','recruit_name','pay','small'),"isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']} group by shop_id  order by toptime,add_time desc limit 0,4");

        $Recruit_where = "isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']} ";
        $Recruit_filed =array('id','shop_id','recruit_name','pay','small');
        $CommonindexObj = new Commonindex();
        $Regionnws =  $CommonindexObj->newestmore('recruit',$Recruit_filed,1,4,$Recruit_where);


        if(count($Regionnws)<1){
            $Regionnws = $RecruitObj->getFiledValues(array('id','shop_id','recruit_name','pay'),"id in(326,327,232,325) order by   add_time  desc");//没有数据显示默认
        }
        $CommonObj = new Common();
        foreach($Regionnws as $k=>$v){
            $Db_shop = $ShopObj->getSingleFiledValues(array('name','specticloc','small'),"id='{$v['shop_id']}'");
            if($Db_shop['roadfullname']==0){
                $roadfullname='';
            }else{
                $roadfullname=$Db_shop['specticloc'];
            }
            if(mb_strlen($v['recruit_name']) > 18){
                $v['recruit_name'] = mb_substr($v['recruit_name'],0,18) . '...';
            }

            if(Buddha_Atom_String::isValidString($v['small']))
            {
                $img = $v['small'];
            }else{
                if(Buddha_Atom_String::isValidString($Db_shop['small']))
                {
                    $img = $Db_shop['small'];
                }else{
                    $img = '';
                }
            }

            $RegioNws[]=array(
                'id'=>$v['id'],
                'name'=>$v['recruit_name'],
                'pay'=>$v['pay'],
                'shop_name'=>$Db_shop['name'],
                'roadfullname'=>$roadfullname,
//                'img'=>$img,
                'img'=>$CommonObj->handleImgSlashByImgurl($img),

            );
        }
        //最新需求
//        $Demandnws=$DemandObj->getFiledValues(array('id','shop_id','name','budget','demand_brief','demand_thumb'),"isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']} group by shop_id  order by toptime,add_time  desc  limit 0,4");

        $Demandnws_where ="isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']} ";
        $Demandnws_filed = array('id','shop_id','name','budget','demand_brief','demand_thumb');
        $CommonindexObj = new Commonindex();

        $Demandnws =  $CommonindexObj->newestmore('demand',$Demandnws_filed,1,4,$Demandnws_where);



        if(count($Demandnws)<1){
            $Demandnws=$DemandObj->getFiledValues(array('id','shop_id','name','budget','demand_brief','demand_thumb'),"id in(333,225) order by add_time  desc");//没有数据显示默认
        }

        $CommonObj = new Common();

        foreach($Demandnws as $k=>$v)
        {
            $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc'),"id='{$v['shop_id']}'");
            if($Db_shop['roadfullname']==0){
                $roadfullname='';
            }else{
                $roadfullname=$Db_shop['specticloc'];
            }
            if($v['demand_thumb']){
                $demand_img = $v['demand_thumb'];
            }else{
               $demand_img = 'style/images/demand_img.jpg'; 
            }
            if(mb_strlen($v['name']) > 18){
                $v['name'] = mb_substr($v['name'],0,18) . '...';
            }

            $DemandNws[]=array(
                'id'=>$v['id'],
                'name'=>$v['name'],
                'demand_thumb'=>$CommonObj->handleImgSlashByImgurl($demand_img),
                'budget'=>$v['budget'],
                'shop_name'=>$Db_shop['name'],
                'roadfullname'=>$roadfullname,
            );
        }
        //最新租赁 
//        $Leasenws=$LeaseObj->getFiledValues(array('id','shop_id','lease_name','rent','lease_thumb'),"isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']} group by shop_id  order by  toptime,add_time  desc  limit 0,4");

        $Lease_where = "isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']}";
        $Lease_filed = array('id','shop_id','lease_name','rent','lease_thumb');
        $CommonindexObj = new Commonindex();
        $Leasenws =  $CommonindexObj->newestmore('lease',$Lease_filed,1,4,$Lease_where);

        if(count($Leasenws)<1){
            $Leasenws=$LeaseObj->getFiledValues(array('id','shop_id','lease_name','rent','lease_thumb'),"id in(43,100,105) order by  add_time  desc ");
        }
        foreach($Leasenws as $k=>$v)
        {
            $Db_shop = $ShopObj->getSingleFiledValues(array('name','specticloc'),"id='{$v['shop_id']}'");

            if(!Buddha_Atom_String::isValidString($Db_shop['specticloc']))
            {
                $roadfullname='';
            }else{
                $roadfullname=$Db_shop['specticloc'];
            }
            if(mb_strlen($v['lease_name']) > 18){
                $v['lease_name'] = mb_substr($v['lease_name'],0,18) . '...';
            }
            $leaseNws[]=array(
                'id'=>$v['id'],
                'name'=>$v['lease_name'],
                'rent'=>$v['rent'],
                'lease_thumb'=>$v['lease_thumb'],
                'lease_thumb'=>$CommonObj->handleImgSlashByImgurl($v['lease_thumb']),
                'roadfullname'=>$roadfullname,
                'shop_name'=>$Db_shop['name'],
            );
        }

        $jsonarray['guanggao'] = $guanggao;
        $jsonarray['shopRec'] = $shopRec;
        $jsonarray['shopNws'] = $shopNws;
        $jsonarray['shopPro'] = $shopPro;
        $jsonarray['shopHot'] = $shopHot;
        $jsonarray['goodsNws'] = $goodsNws;
        $jsonarray['RegioNws'] = $RegioNws;
        $jsonarray['DemandNws'] = $DemandNws;
        $jsonarray['leaseNws'] = $leaseNws;
        $jsonarray['shopurl'] = $ShopObj->shop_url();

        Buddha_Http_Output::makeJson($jsonarray);
    }
}



