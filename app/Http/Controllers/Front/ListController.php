<?php

/**
 * Class ListController
 */
class ListController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
    }

    public function testindex(){
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
        $this->smarty->assign('signPackage', $getJsSignData);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }

    public function index(){
        $RegionObj=new Region();
        $ShopObj = new Shop();
        $locdata = $RegionObj->getLocationDataFromCookie();
        $act=Buddha_Http_Input::getParameter('act');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $store=Buddha_Http_Input::getParameter('storetype');
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') : 15;
            $where = " isdel=0 and is_sure=1  and state=0 {$locdata['sql']}";
            if($store){
                switch($store){
                    case 1;
                        $where.=" and storetype=1 " ;
                        break;
                    case 2;
                        $where.=" and storetype=2 " ;
                        break;
                    case 3;
                        $where.=" and storetype=3 ";
                        break;
                    case 4;
                        $where.=" and storetype=4 ";
                        break;
                    case 5;
                        $where.=" and storetype=5 ";
                        break;
                };
            };
            if ($keyword) {
                $where .= " and (property like '%$keyword%' or specticloc like '%$keyword%')";
            };
            $orderby = " order by createtime DESC ";
        $sql ="select DISTINCT property,  storetype   from {$this->prefix}shop  where {$where} {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        $getAtt = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        foreach( $getAtt as $k=>$v){
            $property = $v['property'];
            $total = $ShopObj->countRecords("isdel=0 and is_sure=1 and state=0 and property='{$property}' {$locdata['sql']} ");
            $getAtt[$k]['total'] = $total;
        };

        if($act=='list') {
            $data=array();
            if($getAtt){
                $data['isok']='true';
                $data['list']=$getAtt;
                $data['data']='加载完成';

            }else{
                $data['isok']='false';
                $data['list']='';
                $data['data']='没数据了';
            };
            Buddha_Http_Output::makeJson($data);
        };

        $storetype=$ShopObj->getstoretypeindex();
        foreach ($storetype as $k=>$v){
            if($k==$store){
                $title=$v;
            };
        };



        $this->smarty->assign('title',$title);
        $this->smarty->assign('keyword',$keyword);
        $this->smarty->assign('store',$store);





////////分享
        $WechatconfigObj  = new Wechatconfig();
        $sharearr = array(
            'share_title'=>'市场',
            'share_desc'=>'本地商家-市场',
            'ahare_link'=>"index.php?a=".__FUNCTION__."&c=".$this->c,
            'share_imgUrl'=>'',
        );
        $WechatconfigObj->getJsSign($sharearr['share_title'],$sharearr['share_desc'],$sharearr['ahare_link'],$sharearr['share_imgUrl']);
 ////////分享


        $TPL_URL = $this->c.'.'.__FUNCTION__;
      $this->smarty -> display($TPL_URL.'.html');
    }

    public function listshop (){
        $RegionObj=new Region();
        $ShopObj = new Shop();
        $locdata = $RegionObj->getLocationDataFromCookie();
        $act=Buddha_Http_Input::getParameter('act');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $store=Buddha_Http_Input::getParameter('storetype');
        $property=Buddha_Http_Input::getParameter('property');
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') : 15;

        $where = " isdel=0  and is_sure=1 and state=0 and storetype='{$store}' and property='{$property}'  {$locdata['sql']}";

        if ($keyword) {
            $where .= " and (name like '%$keyword%' or specticloc like '%$keyword%')";
        }
        $orderby = " order by createtime DESC ";
        $fields = array('id', 'name', 'brief', 'small','lat','lng','specticloc','storetype');
        $list = $this->db->getFiledValues ($fields,  $this->prefix.'shop', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
$ShopObj=  new Shop();
        foreach($list as $k=>$v){
            $distance=$RegionObj->getDistance($locdata['lng'],$locdata['lat'],$v['lng'],$v['lat'],2);
            $goodsNws[]=array(
                'id'=>$v['id'],
                'name'=>$v['name'],
                'brief'=>$v['brief'],
                'small'=>$v['small'],
                'roadfullname'=>$v['specticloc'],
                'distance'=>$distance,
                'url'=>$ShopObj->shop_url(),
            );
        }

        if($act=='list'){
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
        $storetype=$ShopObj->getstoretypeindex();
        foreach ($storetype as $k=>$v){
            if($k==$store){
                $title=$v;
            }
        }
        $this->smarty->assign('title',$title);
        $this->smarty->assign('store',$store);
        $this->smarty->assign('property',$property);
        $this->smarty->assign('keyword',$keyword);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function shop()
    {
        $RegionObj=new Region();
        $ShopObj=new Shop();
        $ShopcatObj=new Shopcat();
        $cid = Buddha_Http_Input::getParameter('cid');
        $getcategory =$ShopcatObj->getcategory();
        if($cid){
            $insql = $ShopcatObj->getInSqlByID($getcategory,$cid);
        }
        $store= (int)Buddha_Http_Input::getParameter('storetype');// 店铺属性分类  (0全部；1沿街商铺；2市场；3商场；4写字楼；5生产制造
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $type=Buddha_Http_Input::getParameter('type')?Buddha_Http_Input::getParameter('type'):'is_nws';///*销售类型： 0 is_rec 推荐（默认）；1 is_nws 最新；2 is_promotion 促销；3 is_hot热门；*/
        $lats=Buddha_Http_Input::getParameter('lat');
        $lngs=Buddha_Http_Input::getParameter('lng');
        $locdata = $RegionObj->getLocationDataFromCookie();
        //print_r($locdata);
        $act=Buddha_Http_Input::getParameter('act');
        if($act == 'list' && $lats && $lngs){
            $locdata['lat'] = $lats;
            $locdata['lng'] = $lngs;
        }
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') : 15;
        $where = " isdel=0 and is_sure=1  and state=0  {$locdata['sql']}";
        $orderby = " order by createtime DESC ";
        if($type){
            switch ($type){
                case 'is_rec':
                    $where.=" and is_rec=1 ";
                    break;
                case 'is_nws';
                $orderby=" order by createtime DESC  ";
                    break;
                case 'is_promotion';
                    $where.=" and is_promotion=1 ";
                    break;
                case 'is_hot';
                    $where.=" and is_hot=1 ";
                    break;
            }
        }
        if($store){
            $where.=" and  storetype={$store}";
        }
        if ($keyword) {
            $where .= " and (name like '%$keyword%' or specticloc like '%$keyword%')";
        }
        if($cid){
            $where.=" and  shopcat_id IN {$insql}";
        }
        $squares = $this->returnSquarePoint($locdata['lng'], $locdata['lat']);//获取四个点
        
        $fields = array('id', 'name', 'brief', 'small','lat','lng','specticloc','storetype','shopcat_id');


        if($store)
        {
            $list = $this->db->getFiledValues ($fields,  $this->prefix.'shop', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        }else{

            $list = $this->db->getFiledValues ($fields,  $this->prefix.'shop', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        }
        
        //$where.= " and lat<>0 and lat>{$squares['right-bottom']['lat']} and lat<{$squares['left-top']['lat']} and lng>{$squares['left-top']['lng']} and lng<{$squares['right-bottom']['lng']}";//拼接sql语句
            //$list = $this->db->getFiledValues ($fields,  $this->prefix.'shop', $where .$orderby );
        //print_r($list);

        $rechargeObj = new Recharge();
        foreach($list as $k=>$v){
            $icon = '';



            $distance=$RegionObj->getdistance($locdata['lng'],$locdata['lat'],$v['lng'],$v['lat'],2);//根据经纬度计算距离
            if($rechargeObj->countRecords("shop_id={$v['id']}")){
                $icon = "/style/images/icon_reward.png"; 
            }
            $goodsNws[]=array(
                'id'=>$v['id'],
                'name'=>$v['name'],
                'brief'=>$v['brief'],
                'small'=>$v['small'],
                'shopcat_id'=>$v['shopcat_id'],
                'roadfullname'=>$v['specticloc'],
                'distance'=>$distance,
                'icon_shang'=>$icon
            );

        }
        if(!$store){
            $sort = array(  
                'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序  
                'field'     => 'distance',       //排序字段  
            ); 
            $arrSort = array();


            foreach($goodsNws AS $uniqid => $row){  
                foreach($row AS $key=>$value){  
                    $arrSort[$key][$uniqid] = $value;  
                }  
            }  
            if($sort['direction']){  
                array_multisort($arrSort[$sort['field']], constant($sort['direction']), $goodsNws);  
            } 

        }
       
        if($act=='list'){
            $data=array();
            if($goodsNws){
                $data['isok']= 'true';
                $data['list']=$goodsNws;
                $data['data']='加载完成';

            }else{
                $data['isok']='false';
                $data['list']='';
                $data['data']='没数据了';
            }
            Buddha_Http_Output::makeJson($data);
        }
        $storetype=$ShopObj->getstoretypeindex();
        foreach ($storetype as $k=>$v){
            if($k==$store){
                $title=$v;
            }
        }
        ////////分享
        $WechatconfigObj  = new Wechatconfig();
        $sharearr = array(
            'share_title'=>'本地商家综合展示中心,供求信息发布中心(附近商家)',
            'share_desc'=>'您附近的店铺,商城。帮您快速定位,快速查找,以最短的时间找到您想要的...',
            'ahare_link'=>"index.php?a=".__FUNCTION__."&c=".$this->c,
            'share_imgUrl'=>'style/images/index_sq.png',
        );
        $WechatconfigObj->getJsSign($sharearr['share_title'],$sharearr['share_desc'],$sharearr['ahare_link'],$sharearr['share_imgUrl']);
        ////////分享


        $CommonindexObj = new Commonindex();
        $filarr = array(
            0=>array('filed'=>'zuixin','a'=>'index','view'=>2),
            1=>array('filed'=>'fujin','a'=>'index','view'=>1),
            2=>array('filed'=>'remen','a'=>'index','view'=>3),
            3=>array('filed'=>'shangjia','a'=>'index','view'=>5),
            4=>array('filed'=>'fenlei','a'=>'category','view'=>6));

        $Common = $CommonindexObj->indexmorenavlist($this->tablename,$filarr);
        $this->smarty->assign('navlist',$Common);




        $this->smarty->assign('title',$title);
        $this->smarty->assign('keyword',$keyword);
        $this->smarty->assign('type',$type);
        $this->smarty->assign('store',$store);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function category(){
        $ShopcatObj=new Shopcat();
        $type=Buddha_Http_Input::getParameter('type');
        $arr =$ShopcatObj->getcategory();
        $table ='';
        $ShopcatObj->getDivRelation($arr,$table,$type);
        $this->smarty->assign('category',$table);
        $this->smarty->assign('type',$type);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }



    /**
    *计算某个经纬度的周围某段距离的正方形的四个点
    *
    *@param lng float 经度
    *@param lat float 纬度
    *@param distance float 该点所在圆的半径，该圆与此正方形内切，默认值为0.5千米
    *@return array 正方形的四个点的经纬度坐标
    */
    public function returnSquarePoint($lng, $lat,$distance = 1.5){
        define(EARTH_RADIUS, 6370.996);//地球半径，平均半径为6371km
        $dlng = 2 * asin(sin($distance / (2 * EARTH_RADIUS)) / cos(deg2rad($lat)));
        $dlng = rad2deg($dlng);

        $dlat = $distance/EARTH_RADIUS;
        $dlat = rad2deg($dlat);

        return array(
        'left-top'=>array('lat'=>$lat + $dlat,'lng'=>$lng-$dlng),
        'right-top'=>array('lat'=>$lat + $dlat, 'lng'=>$lng + $dlng),
        'left-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng - $dlng),
        'right-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng + $dlng)
        );
    }
    /*public function getdistance($lng1,$lat1,$lng2,$lat2){//根据经纬度计算距离 单位为公里
    //将角度转为狐度
    $radLat1=deg2rad($lat1);
    $radLat2=deg2rad($lat2);
    $radLng1=deg2rad($lng1);
    $radLng2=deg2rad($lng2);
    $a=$radLat1-$radLat2;//两纬度之差,纬度<90
    $b=$radLng1-$radLng2;//两经度之差纬度<180
    $s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137;
    return round($s,2);
    }*/
    function getdistance($lng1, $lat1, $lng2, $lat2) {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        $d=round($s/1000,2);
        if(0<$d && $d<1){
            $distance=($d*1000).' m';
        }else{
             $distance=$d.' m';
        }
        return $distance;
    }


    public function reccharges(){
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $rechargeObj = new Recharge();
        $locdata = $RegionObj->getLocationDataFromCookie();
        $act=Buddha_Http_Input::getParameter('act');
        //print_r($locdata);
        $act=Buddha_Http_Input::getParameter('act');
        if($act == 'list' && $lats && $lngs){
            $locdata['lat'] = $lats;
            $locdata['lng'] = $lngs;
        }
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') : 15;
        $where = " isdel=0 and is_sure=1  and state=0  {$locdata['sql']}";
        $orderby = " order by createtime DESC ";

        $rechargeInfo = $rechargeObj->getFiledValues(array('shop_id'),"balance >= forwarding_money AND is_open=1");
        $shop_ids = Buddha_Atom_Array::getIdInStr($rechargeInfo);
        $where .= " and id in({$shop_ids})";
        $orderby = " ORDER BY id DESC ";
        $fields = array('id', 'name', 'brief', 'small','lat','lng','specticloc','storetype','shopcat_id');
        $list = $ShopObj->getFiledValues($fields,$where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ));
        foreach($list as $k=>$v){
            $icon = '';
            $distance=$RegionObj->getdistance($locdata['lng'],$locdata['lat'],$v['lng'],$v['lat'],2);//根据经纬度计算距离
            if($rechargeObj->countRecords("shop_id={$v['id']}")){
                $icon = "/style/images/icon_reward.png"; 
            }
            $goodsNws[]=array(
                'id'=>$v['id'],
                'name'=>$v['name'],
                'brief'=>$v['brief'],
                'small'=>$v['small'],
                'shopcat_id'=>$v['shopcat_id'],
                'roadfullname'=>$v['specticloc'],
                'distance'=>$distance,
                'icon_shang'=>$icon
            );

        }
        if($act=='list'){
            $data=array();
            if($goodsNws){
                $data['isok']= 'true';
                $data['list']=$goodsNws;
                $data['data']='加载完成';

            }else{
                $data['isok']='false';
                $data['list']='';
                $data['data']='没数据了';
            }

            Buddha_Http_Output::makeJson($data);
        }

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

}
