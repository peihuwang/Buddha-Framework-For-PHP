<?php

/**
 * Class LocalController
 */
class LocalController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        $ShopcatObj=new Shopcat();
        $shopcat=$ShopcatObj->shop_cat();
        $this->smarty->assign('shopcat',$shopcat);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function infonew(){
        $ShopcatObj=new Shopcat();
        $shopcat=$ShopcatObj->shop_cat();
        $this->smarty->assign('shopcat',$shopcat);
        $screenwidth=$_COOKIE['screenwidth'];
        if(320<=$screenwidth && $screenwidth<375){//
            $height=300;
        }elseif(375<=$screenwidth && $screenwidth<414){
            $height=360;
        }elseif(414<=$screenwidth && $screenwidth<768){
            $height=420;
        }elseif($screenwidth=768){
            $height=620;
        }
        $this->smarty->assign('height',$height);
      ////////分享
        $WechatconfigObj  = new Wechatconfig();
        $sharearr = array(
            'share_title'=>'本地商家综合展示中心,供求信息发布中心(本地信息)',
            'share_desc'=>'本地各行各业实体商家全部展示。助您按类综合查找，行业查找，精准查找...',
            'ahare_link'=>"index.php?a=".__FUNCTION__."&c=".$this->c,
            'share_imgUrl'=>'style/images/index_sq.png',
        );
        $WechatconfigObj->getJsSign($sharearr['share_title'],$sharearr['share_desc'],$sharearr['ahare_link'],$sharearr['share_imgUrl']);
        ////////分享
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



function ajax_info_list($type_id,$p){
    $pagesize=15;
    $ShopObj=new Shop();
    $RegionObj= new Region();

   $CommonObj= new Common();
    $content_size=$CommonObj->words_number(2);
//    var_dump($content_size);

    $addres_size=$CommonObj->words_number(1);
    $locdata = $RegionObj->getLocationDataFromCookie();//区域信息
///////////////////////////////////↓↓↓↓↓↓列表↓↓↓↓↓↓↓↓//////////////////////////////////////////////////////////////
    $where="isdel=0 and is_sure=1 and state=0  and shopcat_id={$type_id}  {$locdata['sql']} ";
    $orderby='order by createtime desc';
    $Db_Shop = $ShopObj->getFiledValues(array('id','name','small','brief','lng','lat','specticloc','roadfullname','level1','level2','level3'),  $where . $orderby.Buddha_Tool_Page::sqlLimit ( $p, $pagesize ));
    $count = $ShopObj->countRecords($where);

    foreach($Db_Shop as $k=>$v){
        $distance=$RegionObj->getDistance($locdata['lng'],$locdata['lat'],$v['lng'],$v['lat'],2);//计算距离
        if($v['brief']){
            $brief= mb_substr($v['brief'],0,$content_size,'utf-8').'...';//
        }else{
            $brief='';
        }
        $area_id['level1']=$Db_Shop[0]['level1'];
        $area_id['level2']=$Db_Shop[0]['level2'];
        $area_id['level3']=$Db_Shop[0]['level3'];
        $area= $RegionObj->select_provincialcity($area_id,'id');
//        $roadfullname= $area['level2']['fullname'].$area['level3']['fullname'].mb_substr($v['specticloc'],0,7,'utf-8').'...';//
        $roadfullname= mb_substr($v['specticloc'],0,$addres_size,'utf-8').'...';//
        $goods[]=array(
            'id'=>$v['id'],
            'name'=>$v['name'],
            'brief'=>$brief,
            'small'=>$v['small'],
            'shopcat_id'=>$v['shopcat_id'],
            'roadfullname'=>$roadfullname,
//            'distance'=>$distance,
        );
    }
    $Nws='';
    if($p==1){
        if(count($Db_Shop)==0){
            $goods['length']=0;
            $Nws['information']='对不起，你查询的数据不存在，请看看别的吧';
        }elseif(count($Db_Shop)>=0 && count($Db_Shop)<$pagesize){
            $Nws['information']='你的数据加载完毕！';
        }elseif(count($Db_Shop)==$pagesize){
            $Nws['information']='向上拉加载更多！';
        }
    }elseif($p>1){
        if(count($Db_Shop)==0){
            $Nws['information']='你的数据加载完毕';
            $goods['length']=0;
        }elseif(count($Db_Shop)>=0 && count($Db_Shop)<$pagesize){
            $Nws['information']='你的数据加载完毕！';
            $goods['length']=0;
        }elseif(count($Db_Shop)==$pagesize){
            $Nws['information']='向上拉加载更多！';
        }
    }


$shop_url=$ShopObj->shop_url();
    $goodsNws=array(
        'goods'=>$goods,
        'infor'=>$Nws,
        'url'=>$shop_url,
        'count'=>$count,
    );
    ///////////////////////////////////↑↑↑↑↑↑列表↑↑↑↑↑↑///////////////////////////////////////////////////////////////
return $goodsNws;
}



}