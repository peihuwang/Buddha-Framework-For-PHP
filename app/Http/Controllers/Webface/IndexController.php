<?php
/**
 * Created by PhpStorm.
 * User: Administrator   首页
 * Date: 2017/9/7
 * Time: 13:55
 */
class IndexController extends Buddha_App_Action{
    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

        //权限
        $webface_access_token = Buddha_Http_Input::getParameter('webface_access_token');
        if ($webface_access_token == '') {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444002, 'webface_access_token必填');
        }

        $ApptokenObj = new Apptoken();
        $num = $ApptokenObj->getTokenNum($webface_access_token);
        if ($num == 0) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444003, 'webface_access_token不正确请从新获取');
        }
    }

    /**
 * 首页
 */
    public function homepage(){
        $host = Buddha::$buddha_array['host'];//https安全连接
        if(Buddha_Http_Input::checkParameter(array('api_number'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $api_number = Buddha_Http_Input::getParameter('api_number');
        $ImgObj = new Image();
        $DemandObj = new Demand();
        $SupplyObj = new Supply();
        $ImagecatalogObj = new Imagecatalog();
        $num1 = $ImagecatalogObj->getSingleFiledValues(array('imgmax'),"identify='mobile_home_1'");
        $num2 = $ImagecatalogObj->getSingleFiledValues(array('imgmax'),"identify='mobile_home_2'");
        $num3 = $ImagecatalogObj->getSingleFiledValues(array('imgmax'),"identify='mobile_home_3'");
        $Db_img1 = $ImgObj->getIndexBannerArr(5,$api_number,$num1['imgmax'],$_REQUEST['Services']);
        $Db_img2 = $ImgObj->getIndexBannerArr(6,$api_number,$num2['imgmax'],$_REQUEST['Services']);
        $Db_img3 = $ImgObj->getIndexBannerArr(7,$api_number,$num3['imgmax'],$_REQUEST['Services']);

        $param = array('storetype'=>1,'api_storetypename'=>'沿街商铺','is_indexheader'=>1);
        $block_arr1[] = array('name'=>'沿街商铺','logo'=>$host.'apiindex/menuplus/yanjieshangpu.png','Services'=>'shop.more','param'=>$param);
        $param = array('storetype'=>2,'api_storetypename'=>'市场','is_indexheader'=>1);
        $block_arr1[] = array('name'=>'市场','logo'=>$host.'apiindex/menuplus/shichang.png','Services'=>'shop.shopproperty','param'=>$param);
        $param = array('storetype'=>3,'api_storetypename'=>'商场','is_indexheader'=>1);
        $block_arr1[] = array('name'=>'商场','logo'=>$host.'apiindex/menuplus/shangchang.png','Services'=>'shop.shopproperty','param'=>$param);
        $param = array('storetype'=>4,'api_storetypename'=>'写字楼','is_indexheader'=>1);
        $block_arr1[] = array('name'=>'写字楼','logo'=>$host.'apiindex/menuplus/xiezilou.png','Services'=>'shop.shopproperty','param'=>$param);
        $param = array('storetype'=>5,'api_storetypename'=>'生产制造','is_indexheader'=>1);
        $block_arr1[] = array('name'=>'生产制造','logo'=>$host.'apiindex/menuplus/shengchanzhizao.png','Services'=>'shop.more','param'=>$param);

        $param = array('api_number'=>$api_number);
        $block_arr2[] = array('name'=>'需求','logo'=>$host.'apiindex/menuplus/xuqiu.png','Services'=>'multilist.demandmore','param'=>$param);
        $block_arr2[] = array('name'=>'供应','logo'=>$host.'apiindex/menuplus/gongying.png','Services'=>'multilist.supplymore','param'=>$param);
        $block_arr2[] = array('name'=>'促销','logo'=>$host.'apiindex/menuplus/cuxiao.png','Services'=>'multilist.promotionsarr','param'=>$param);
        $block_arr2[] = array('name'=>'招聘','logo'=>$host.'apiindex/menuplus/zhaopin.png','Services'=>'multilist.recruitarr','param'=>$param);
        $block_arr2[] = array('name'=>'活动','logo'=>$host.'apiindex/menuplus/huodong.png','Services'=>'activity.more','param'=>$param);
        $block_arr2[] = array('name'=>'传单','logo'=>$host.'apiindex/menuplus/xinxi.png','Services'=>'singleinformation.more','param'=>$param);

        $listnavi1 = array('最新需求','最新招聘','今日促销','热门活动');
        $listnavi2 = array('最新供应','推荐商家','最近开业','最新租赁');
        $moreservices1 = array(
            0 =>array(
                'services'=>'multilist.demandmore',
                'param'=>array('view'=>2,'pagsize'=>6,'page'=>1)
            ),
            1 =>array(
                'services'=>'multilist.recruitarr',
                'param'=>array('view'=>2,'pagsize'=>6,'page'=>1)
            ),
            2 =>array(
                'services'=>'multilist.promotionsarr',
                'param'=>array('pagsize'=>6,'page'=>1)
            ),
            3 =>array(
                'services'=>'activity.more',
                'param'=>array('api_activitytype'=>5,'pagsize'=>6,'page'=>1)
            ),
        );
        $moreservices2 = array(
            0 =>array(
                'services'=>'multilist.supplymore',
                'param'=>array('view'=>2,'pagsize'=>6,'page'=>1)
            ),
            1 =>array(
                'services'=>'shop.more',
                'param'=>array('pagsize'=>6,'page'=>1,'api_saletype'=>0)
            ),
            2 =>array(
                'services'=>'shop.more',
                'param'=>array('pagsize'=>6,'page'=>1,'api_saletype'=>4)
            ),
            3 =>array(
                'services'=>'multilist.leasearr',
                'param'=>array('view'=>2,'pagsize'=>6,'page'=>1)
            ),
        );
        $Db_demand = $DemandObj->getDemandArr($api_number,6);//最新需求
        $Db_supply = $SupplyObj->getSupplyArr($api_number,6);//最新供应
        $jsondata['scrobantop'] = $Db_img1;
        $jsondata['mainmenu'] = $block_arr1;
        $jsondata['menaplus'] = $block_arr2;
        $jsondata['scrobanmid1'] = $Db_img2;
        $jsondata['info1'][0]['name'] = $listnavi1[0];
        $jsondata['info1'][0]['services'] = $moreservices1[0]['services'];
        $jsondata['info1'][0]['param'] = $moreservices1[0]['param'];
        $jsondata['info1'][0]['list'] = $Db_demand;
        $jsondata['info1'][1]['name'] = $listnavi1[1];
        $jsondata['info1'][1]['services'] = $moreservices1[1]['services'];
        $jsondata['info1'][1]['param'] = $moreservices1[1]['param'];
        $jsondata['info1'][1]['list'] = array();
        $jsondata['info1'][2]['name'] = $listnavi1[2];
        $jsondata['info1'][2]['services'] = $moreservices1[2]['services'];
        $jsondata['info1'][2]['param'] = $moreservices1[2]['param'];
        $jsondata['info1'][2]['list'] = array();
        $jsondata['info1'][3]['name'] = $listnavi1[3];
        $jsondata['info1'][3]['services'] = $moreservices1[3]['services'];
        $jsondata['info1'][3]['param'] = $moreservices1[3]['param'];
        $jsondata['info1'][3]['list'] = array();
        $jsondata['scrobanmid2'] = $Db_img3;
        $jsondata['info2'][0]['name'] = $listnavi2[0];
        $jsondata['info2'][0]['services'] = $moreservices2[0]['services'];
        $jsondata['info2'][0]['param'] = $moreservices2[0]['param'];
        $jsondata['info2'][0]['list'] = $Db_supply;
        $jsondata['info2'][1]['name'] = $listnavi2[1];
        $jsondata['info2'][1]['services'] = $moreservices2[1]['services'];
        $jsondata['info2'][1]['param'] = $moreservices2[1]['param'];
        $jsondata['info2'][1]['list'] = array();
        $jsondata['info2'][2]['name'] = $listnavi2[2];
        $jsondata['info2'][2]['services'] = $moreservices2[2]['services'];
        $jsondata['info2'][2]['param'] = $moreservices2[2]['param'];
        $jsondata['info2'][2]['list'] = array();
        $jsondata['info2'][3]['name'] = $listnavi2[3];
        $jsondata['info2'][3]['services'] = $moreservices2[3]['services'];
        $jsondata['info2'][3]['param'] = $moreservices2[3]['param'];
        $jsondata['info2'][3]['list'] = array();
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'首页');
    }

    /**
     * 首页
     */
    public function homepage2(){
        $host = Buddha::$buddha_array['host'];//https安全连接
        if(Buddha_Http_Input::checkParameter(array('api_number'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $api_number = Buddha_Http_Input::getParameter('api_number');
        $ImgObj = new Image();
        $DemandObj = new Demand();
        $SupplyObj = new Supply();
        $ImagecatalogObj = new Imagecatalog();
        $num1 = $ImagecatalogObj->getSingleFiledValues(array('imgmax'),"identify='mobile_home_1'");
        $num2 = $ImagecatalogObj->getSingleFiledValues(array('imgmax'),"identify='mobile_home_2'");
        $num3 = $ImagecatalogObj->getSingleFiledValues(array('imgmax'),"identify='mobile_home_3'");
        $Db_img1 = $ImgObj->getIndexBannerArr(5,$api_number,$num1['imgmax'],$_REQUEST['Services']);
        $Db_img2 = $ImgObj->getIndexBannerArr(6,$api_number,$num2['imgmax'],$_REQUEST['Services']);
        $Db_img3 = $ImgObj->getIndexBannerArr(7,$api_number,$num3['imgmax'],$_REQUEST['Services']);

        $param = array('storetype'=>1,'api_storetypename'=>'商家导航','is_indexheader'=>1);
        $block_arr1[] = array('name'=>'商家导航','logo'=>$host.'apiindex/menuplus/shangjiadaohang.png','Services'=>'shop.shopclassification','param'=>$param);
        $param = array('storetype'=>2,'api_storetypename'=>'市场','is_indexheader'=>1);
        $block_arr1[] = array('name'=>'市场','logo'=>$host.'apiindex/menuplus/shichang.png','Services'=>'shop.shopproperty','param'=>$param);
        $param = array('storetype'=>3,'api_storetypename'=>'商场','is_indexheader'=>1);
        $block_arr1[] = array('name'=>'商场','logo'=>$host.'apiindex/menuplus/shangchang.png','Services'=>'shop.shopproperty','param'=>$param);
        $param = array('storetype'=>4,'api_storetypename'=>'写字楼','is_indexheader'=>1);
        $block_arr1[] = array('name'=>'写字楼','logo'=>$host.'apiindex/menuplus/xiezilou.png','Services'=>'shop.shopproperty','param'=>$param);
        $param = array('storetype'=>5,'api_storetypename'=>'沿街商铺','is_indexheader'=>1);
        $block_arr1[] = array('name'=>'沿街商铺','logo'=>$host.'apiindex/menuplus/yanjieshangpu.png','Services'=>'shop.more','param'=>$param);
        $param = array('storetype'=>6,'api_storetypename'=>'生产制造','is_indexheader'=>1);
        $block_arr1[] = array('name'=>'生产制造','logo'=>$host.'apiindex/menuplus/shengchanzhizao.png','Services'=>'shop.more','param'=>$param);
        $param = array('storetype'=>7,'api_storetypename'=>'本地生活','is_indexheader'=>1);
        $block_arr1[] = array('name'=>'本地生活','logo'=>$host.'apiindex/menuplus/bendishenghuo.png','Services'=>'willdo','param'=>$param);

        $param = array('api_number'=>$api_number);
        $block_arr2[] = array('name'=>'需求','logo'=>$host.'apiindex/menuplus/111.png','Services'=>'multilist.demandmore','param'=>$param);
        $block_arr2[] = array('name'=>'招聘','logo'=>$host.'apiindex/menuplus/112.png','Services'=>'multilist.recruitarr','param'=>$param);
        $block_arr2[] = array('name'=>'转发有赏','logo'=>$host.'apiindex/menuplus/index_zhuanfa.png','Services'=>'shop.rewardforwarding','param'=>$param);
        $block_arr2[] = array('name'=>'一分营销','logo'=>$host.'apiindex/menuplus/13.png','Services'=>'heartpro.frontlist','param'=>$param);
        $block_arr2[] = array('name'=>'本地商城','logo'=>$host.'apiindex/menuplus/24.png','Services'=>'willdo','param'=>$param);
        $block_arr2[] = array('name'=>'活动','logo'=>$host.'apiindex/menuplus/11.png','Services'=>'activity.more','param'=>$param);
        $block_arr2[] = array('name'=>'本地广告','logo'=>$host.'apiindex/menuplus/118.png','Services'=>'shop.shopclassification','param'=>$param);
        $block_arr2[] = array('name'=>'本地传单','logo'=>$host.'apiindex/menuplus/123.png','Services'=>'singleinformation.more','param'=>$param);
        $block_arr2[] = array('name'=>'促销','logo'=>$host.'apiindex/menuplus/116.png','Services'=>'multilist.promotionsarr','param'=>$param);
        $block_arr2[] = array('name'=>'本地租赁','logo'=>$host.'apiindex/menuplus/114.png','Services'=>'multilist.leasearr','param'=>$param);
        $block_arr2[] = array('name'=>'本地团购','logo'=>$host.'apiindex/menuplus/12.png','Services'=>'willdo','param'=>$param);
        $block_arr2[] = array('name'=>'供应','logo'=>$host.'apiindex/menuplus/115.png','Services'=>'multilist.supplymore','param'=>$param);

        $listnavi1 = array('最新需求','最新招聘','今日促销','热门活动');
        $listnavi2 = array('最新供应','推荐商家','最近开业','最新租赁');
        $moreservices1 = array(
            0 =>array(
                'services'=>'multilist.demandmore',
                'param'=>array('view'=>2,'pagsize'=>6,'page'=>1)
            ),
            1 =>array(
                'services'=>'multilist.recruitarr',
                'param'=>array('view'=>2,'pagsize'=>6,'page'=>1)
            ),
            2 =>array(
                'services'=>'multilist.promotionsarr',
                'param'=>array('pagsize'=>6,'page'=>1)
            ),
            3 =>array(
                'services'=>'activity.more',
                'param'=>array('api_activitytype'=>5,'pagsize'=>6,'page'=>1)
            ),
        );
        $moreservices2 = array(
            0 =>array(
                'services'=>'multilist.supplymore',
                'param'=>array('view'=>2,'pagsize'=>6,'page'=>1)
            ),
            1 =>array(
                'services'=>'shop.more',
                'param'=>array('pagsize'=>6,'page'=>1,'api_saletype'=>0)
            ),
            2 =>array(
                'services'=>'shop.more',
                'param'=>array('pagsize'=>6,'page'=>1,'api_saletype'=>4)
            ),
            3 =>array(
                'services'=>'multilist.leasearr',
                'param'=>array('view'=>2,'pagsize'=>6,'page'=>1)
            ),
        );
        $Db_demand = $DemandObj->getDemandArr($api_number,6);//最新需求
        $Db_supply = $SupplyObj->getSupplyArr($api_number,6);//最新供应
        $jsondata['scrobantop'] = $Db_img1;
        $jsondata['mainmenu'] = $block_arr1;
        $jsondata['menaplus'] = $block_arr2;
        $jsondata['scrobanmid1'] = $Db_img2;
        $jsondata['info1'][0]['name'] = $listnavi1[0];
        $jsondata['info1'][0]['services'] = $moreservices1[0]['services'];
        $jsondata['info1'][0]['param'] = $moreservices1[0]['param'];
        $jsondata['info1'][0]['list'] = $Db_demand;
        $jsondata['info1'][1]['name'] = $listnavi1[1];
        $jsondata['info1'][1]['services'] = $moreservices1[1]['services'];
        $jsondata['info1'][1]['param'] = $moreservices1[1]['param'];
        $jsondata['info1'][1]['list'] = array();
        $jsondata['info1'][2]['name'] = $listnavi1[2];
        $jsondata['info1'][2]['services'] = $moreservices1[2]['services'];
        $jsondata['info1'][2]['param'] = $moreservices1[2]['param'];
        $jsondata['info1'][2]['list'] = array();
        $jsondata['info1'][3]['name'] = $listnavi1[3];
        $jsondata['info1'][3]['services'] = $moreservices1[3]['services'];
        $jsondata['info1'][3]['param'] = $moreservices1[3]['param'];
        $jsondata['info1'][3]['list'] = array();
        $jsondata['scrobanmid2'] = $Db_img3;
        $jsondata['info2'][0]['name'] = $listnavi2[0];
        $jsondata['info2'][0]['services'] = $moreservices2[0]['services'];
        $jsondata['info2'][0]['param'] = $moreservices2[0]['param'];
        $jsondata['info2'][0]['list'] = $Db_supply;
        $jsondata['info2'][1]['name'] = $listnavi2[1];
        $jsondata['info2'][1]['services'] = $moreservices2[1]['services'];
        $jsondata['info2'][1]['param'] = $moreservices2[1]['param'];
        $jsondata['info2'][1]['list'] = array();
        $jsondata['info2'][2]['name'] = $listnavi2[2];
        $jsondata['info2'][2]['services'] = $moreservices2[2]['services'];
        $jsondata['info2'][2]['param'] = $moreservices2[2]['param'];
        $jsondata['info2'][2]['list'] = array();
        $jsondata['info2'][3]['name'] = $listnavi2[3];
        $jsondata['info2'][3]['services'] = $moreservices2[3]['services'];
        $jsondata['info2'][3]['param'] = $moreservices2[3]['param'];
        $jsondata['info2'][3]['list'] = array();
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'首页');
    }






    /**
     * 首页代理商电话
     */
    public function mobile(){
        if(Buddha_Http_Input::checkParameter(array('api_number'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $api_number = Buddha_Http_Input::getParameter('api_number');
        $UserObj = new User();
        $RegionObj = new Region();
        $api_number = $RegionObj -> getSingleFiledValues(array('id'),"number='{$api_number}'");
        $referral=$UserObj->getSingleFiledValues(array('tel'),"isdel=0 and groupid='2' AND level3='{$api_number['id']}'");
        $jsondata = array();
        $jsondata['tel'] = $referral['tel'];
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'首页代理商电话');
    }

}