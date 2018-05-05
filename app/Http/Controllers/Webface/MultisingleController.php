<?php
/**
 * 所有详情页
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/10
 * Time: 1:40
 * author sys
 */
class MultisingleController extends Buddha_App_Action
{
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
     * 供应详情
     * author sys
     */

    public function supplysingle()
    {
        $host = Buddha::$buddha_array['host'];
        $SupplyObj = new Supply();
        if (Buddha_Http_Input::checkParameter(array( 'supply_id','lat','lng','b_display'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $supply_id = (int)Buddha_Http_Input::getParameter('supply_id')?(int)Buddha_Http_Input::getParameter('supply_id'):0;
        /*判断商品是否过了促销时间(当商品为促销时)*/
        $SupplyObj->updateispromote($supply_id);

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?(int)Buddha_Http_Input::getParameter('b_display'):2;
        $lat = Buddha_Http_Input::getParameter('lat');
        $lng = Buddha_Http_Input::getParameter('lng');
        $order_id = Buddha_Http_Input::getParameter('order_id');
        $UserObj = new User();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $field = array( 'id as supply_id','user_id','supplycat_id','shop_id','goods_name','click_count','goods_number','goods_unit'
                        ,'market_price','promote_price','promote_start_date','promote_end_date','goods_brief','goods_desc','add_time'
                        ,'is_promote','goods_thumb');

        $Db_goods = $SupplyObj->getSingleFiledValues($field,"id='{$supply_id}' AND buddhastatus=0 AND is_sure=1");
        if(!$Db_goods)
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 30000000, '此id编号不存在');
        }

        //$Db_goods['img'] = $host . $Db_goods['img'];

        $Db_goods['goods_desc'] = Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_goods['goods_desc']);

        $GalleryObj = new Gallery();
        $ShopObj = new Shop();
        $RegionObj  = new Region();
        $OrderObj = new Order();
        $CommonObj = new Common();
        $gsllery = $GalleryObj->getFiledValues(array('goods_img'),"goods_id='{$supply_id}'");
        foreach($gsllery as $k => $v)
        {
            if($v['goods_img'])
            {
                if($b_display == 2)
                {
                    $gsllery[$k]['goods_img'] = $host . $v['goods_img'];
                }else{
                    $gsllery[$k]['goods_img'] = $host . $v['goods_large'];
                }
            }else{
                $gsllery[$k]['goods_img'] = '';
            }
        }
        $shopinfo= $ShopObj->getSingleFiledValues(array('id as shop_id','small','name','is_verify','specticloc','lat','lng','mobile','createtimestr','level1','level2','level3','is_verify','user_id'),"id='{$Db_goods['shop_id']}' and isdel=0");

        $shopinfo['small'] = $host . $shopinfo['small'];
        $shopinfo['icon_position'] = $host . "apiindex/menuplus/icon_position.png";//距离图标
        if($shopinfo['level1'] && $shopinfo['level2'] && $shopinfo['level3'])
        {
            $shopinfo['area'] = $RegionObj->getDetailOfAdrressByRegionIdStr($shopinfo['level1'],$shopinfo['level2'],$shopinfo['level3']);
        }
        $distance=$RegionObj->getDistance($lat,$lng,$shopinfo['lat'],$shopinfo['lng'],2);
        $shopinfo['distance']=$distance;
        $data=array();


        /**↓↓↓↓↓↓↓↓↓↓↓ 更新点击量 ↓↓↓↓↓↓↓↓↓↓↓**/
        $clickdata['click_count']=$Db_goods['click_count']+1;
        $SupplyObj->edit($clickdata,$supply_id);
        /**↑↑↑↑↑↑↑↑↑↑ 更新点击量 ↑↑↑↑↑↑↑↑↑↑**/

        /**↓↓↓↓↓↓↓↓↓↓↓ 是否过期：过期就要非促销 ↓↓↓↓↓↓↓↓↓↓↓**/
        $newtime=time();
        if(!($Db_goods['promote_start_date']<$newtime AND $newtime<$Db_goods['promote_end_date'])){
            $data['is_promote']=0;
            $SupplyObj->edit($data,$Db_goods['supply_id']);
        }
        /**↑↑↑↑↑↑↑↑↑↑ 是否过期：过期就要非促销 ↑↑↑↑↑↑↑↑↑↑**/


        $Db_goods['api_addtimestr']=$CommonObj->getDateStrOfTime($Db_goods['add_time'],0,1,0);
        $Db_goods['api_promotestartdate']=$CommonObj->getDateStrOfTime($Db_goods['promote_start_date'],0,1,0);
        $Db_goods['api_promoteenddate']=$CommonObj->getDateStrOfTime($Db_goods['promote_end_date'],0,1,0);


//        $start = time()-15*60;
//        if($user_id){
//            $see= $OrderObj->countRecords("user_id='{$user_id}' and good_id='{$Db_goods['supply_id']}' and pay_status=1 and createtime>=$start");
//        }else{
//            $see= $OrderObj->countRecords("id='{$order_id}' and good_id='{$Db_goods['supply_id']}' and pay_status=1 and createtime>=$start");
//        }
//
//
//        /**↓↓↓↓↓↓↓↓↓↓↓ 判断用户是否认证：非认证显示7天（is_verify) ↓↓↓↓↓↓↓↓↓↓↓**/
//        if($shopinfo['is_verify']){
//            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_certified.png";
//            $shopinfo['is_showcellphone'] = $shopinfo['is_verify'];
//        }else{
//            //$createtime=$goods['add_time'];//免费7天的开始时间
//            $endtime = $shopinfo['createtime'] + 7*86400;//免费7天的结束时间
//            $newtime = time();
//            if($newtime< $endtime || $see){
//                $shopinfo['is_showcellphone']=1;
//            }else{
//                $shopinfo['is_showcellphone']=0;
//            }
//            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_unauthorized.png";
//            $shopinfo['is_showcellphone'] = $shopinfo['is_showcellphone'];
//        }
//        /**↑↑↑↑↑↑↑↑↑↑ 判断用户是否认证：非认证显示7天（is_verify) ↑↑↑↑↑↑↑↑↑↑**/
//
//

        /**↓↓↓↓↓↓↓↓↓↓↓ 显示电话 ↓↓↓↓↓↓↓↓↓↓↓**/
        if($shopinfo['is_verify'])
        {
            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_certified.png";
        }else{
            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_unauthorized.png";
        }
        // 是否显示电话
        $shopinfo['is_showcellphone'] = $ShopObj->isCouldSeeCellphone($Db_goods['shop_id'],$Db_goods['user_id'],$user_id);
        $shopinfo['mobile'] = $ShopObj->showCellphone($supply_id,'supply',$Db_goods['user_id'],$user_id,$Db_goods['shop_id'],$order_id);;
        /**↑↑↑↑↑↑↑↑↑↑ 显示电话 ↑↑↑↑↑↑↑↑↑↑**/




        $shopinfo['services'] = "shop.view";
        $shopinfo['param'] = array('shop_id'=>"'{$shopinfo['shop_id']}'",'b_display'=>2,'webface_access_token'=>'buddhaaccesstoken');

        $jsondata['supplyinfo'] = $Db_goods;
        $jsondata['bannerimg'] = $gsllery;
        $jsondata['shopinfo'] = $shopinfo;

        $jsondata['shopping_cart']['services'] = 'cart.add';
        $jsondata['shopping_cart']['param'] = array('product_table'=>'supply','product_id'=>$Db_goods['supply_id'],'goods_number'=>1);

        $jsondata['index']=array(
            'services' =>'index.homepage',
            'param' => array(),
        );
        /**↓↓↓↓↓↓↓↓↓↓↓ 分享 ↓↓↓↓↓↓↓↓↓↓↓**/
        if($Db_goods['goods_brief'])
        {
            if(mb_strlen($Db_goods['goods_brief']) > 20){
                $goods_brief = mb_substr($Db_goods['goods_brief'],0,20) . '...';
            }else{
                $goods_brief = $Db_goods['goods_brief'];
            }
        }else{
            $goods_brief = '本地商家网：实体商家展示新渠道、广告传播新工具';
        }

        if($Db_goods['promote_price'] != '0.00')
        {
            $jia =  $Db_goods['promote_price'];
        }else{
            $jia =  $Db_goods['market_price'];
        }

        if(Buddha_Atom_String::isValidString($Db_goods['goods_thumb']))
        {
            $share_imgUrl = $host.$Db_goods['goods_thumb'];
        }else{
            $share_imgUrl = '';
        }

        $sharearr = array(
            'share_title'=>$Db_goods['goods_name'],
            'share_desc'=>"{$Db_goods['goods_name']}，价格：{$jia}。 ".$goods_brief,
            'share_link'=> Buddha_Atom_Share::getShareUrl('supply.detail',$supply_id),
            'share_imgUrl'=>$share_imgUrl,
        );
        $jsondata['sharearr'] = $sharearr;
        /**↑↑↑↑↑↑↑↑↑↑ 分享 ↑↑↑↑↑↑↑↑↑↑**/

        /**↓↓↓↓↓↓↓↓↓↓↓ 店铺是否有转发有赏 ↓↓↓↓↓↓↓↓↓↓↓**/
        $rechargeObj = new Recharge();//充值表
        $rechargeinfo = $rechargeObj->getSingleFiledValues('',"uid={$shopinfo['user_id']} and shop_id='{$shopinfo['shop_id']} and is_open=1'");
        unset($shopinfo['user_id']);
        $is_reward = 0;//是否转发有赏：0否；1是
        $is_reward_img = '';
        $is_reward_url = array();
        if($rechargeinfo && $rechargeinfo['balance'] >= $rechargeinfo['forwarding_money'])
        {
            $is_reward = 1;
            $is_reward_img = $host.'style/images/zhuanfayoushang.png';
            $is_reward_url = array(
                'services' =>'shop.sharingmoney',
                'param' => array('shop_id'=>$Db_goods['shop_id']),
            );
        }
        $issharearr = array(
            'is_reward'=>$is_reward,        //  是否转发有赏：0否；1是
            'is_reward_img'=>$is_reward_img,//  转发的图标
            'is_reward_url'=>$is_reward_url,//  转发的后访问发的有赏接口
        );
        $jsondata['issharearr'] = $issharearr;
        /**↑↑↑↑↑↑↑↑↑↑ 店铺是否有转发有赏 ↑↑↑↑↑↑↑↑↑↑**/



        /**↓↓↓↓↓↓↓↓↓↓↓ 是否显示电话号码 ↓↓↓↓↓↓↓↓↓↓↓**/
        $jsondata['isshowcellphone'] = array(
            'services' =>'shop.isshowcellphone',
            'param' => array('shop_id'=>$Db_goods['shop_id']),
        );
        /**↑↑↑↑↑↑↑↑↑↑ 是否显示电话号码 ↑↑↑↑↑↑↑↑↑↑**/

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '供应详情');
    }

    /**
     * 促销详情
     * author sys
     */

    public function promotionsingle()
    {
        $host = Buddha::$buddha_array['host'];
        $SupplyObj = new Supply();
        $CommonObj = new Common();
        if (Buddha_Http_Input::checkParameter(array( 'promotion_id','lat','lng','b_display'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $promotion_id = (int)Buddha_Http_Input::getParameter('promotion_id');

        /*判断商品是否过了促销时间(当商品为促销时)*/
        $SupplyObj->updateispromote($promotion_id);

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?(int)Buddha_Http_Input::getParameter('b_display'):2;
        $lat = Buddha_Http_Input::getParameter('lat');
        $lng = Buddha_Http_Input::getParameter('lng');
        $order_id = Buddha_Http_Input::getParameter('order_id');
        $UserObj = new User();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $field = array( 'id as promotion_id','user_id','supplycat_id','shop_id','goods_name','click_count',
                        'goods_number','goods_unit','market_price','promote_price','promote_start_date',
                        'promote_end_date','goods_brief','goods_desc','add_time','is_promote','goods_thumb');

        $Db_goods= $SupplyObj->getSingleFiledValues($field,"id='{$promotion_id}'");
        if(!$Db_goods)
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 30000000, '此id编号不存在');
        }
        //$Db_goods['img'] = $host . $Db_goods['img'];

        $Db_goods['goods_desc'] = Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_goods['goods_desc']);

        $GalleryObj = new Gallery();
        $ShopObj = new Shop();
        $RegionObj  = new Region();
        $OrderObj = new Order();
        $gsllery=$GalleryObj->getFiledValues(array('goods_img'),"goods_id='{$promotion_id}'");
        foreach($gsllery as $k => $v)
        {
            if($v['goods_img']){
                if($b_display == 2){
                    $gsllery[$k]['goods_img'] = $host . $v['goods_img'];
                }else{
                    $gsllery[$k]['goods_img'] = $host . $v['goods_large'];
                }
            }else{
                $gsllery[$k]['goods_img'] = '';
            }
        }
        $shopinfo= $ShopObj->getSingleFiledValues(array('small','name','is_verify','specticloc','lat','lng','mobile','createtimestr','level2','level3','is_verify','user_id'),"id='{$Db_goods['shop_id']}' and isdel=0");

        $shopinfo['small'] = $host . $shopinfo['small'];
        $shopinfo['icon_position'] = $host . "apiindex/menuplus/icon_position.png";//距离图标
        if($shopinfo['level2'] && $shopinfo['level3'])
        {
            $citys = $RegionObj->Region_area2($shopinfo['level2'],$shopinfo['level3']);
            $shopinfo['level2'] = $citys['level2']['name'];
            $shopinfo['level3'] = $citys['level3']['name'];
        }
        $distance=$RegionObj->getDistance($lat,$lng,$shopinfo['lat'],$shopinfo['lng'],2);
        $shopinfo['distance']=$distance;
        $data=array();

        /**↓↓↓↓↓↓↓↓↓↓↓ 更新点击量 ↓↓↓↓↓↓↓↓↓↓↓**/
        $clickdata['click_count'] = $Db_goods['click_count']+1;
        $SupplyObj->edit($clickdata,$promotion_id);
        /**↑↑↑↑↑↑↑↑↑↑ 更新点击量 ↑↑↑↑↑↑↑↑↑↑**/


        /**↓↓↓↓↓↓↓↓↓↓↓ 是否过期：过期就要非促销 ↓↓↓↓↓↓↓↓↓↓↓**/

        $newtime=time();
        if(!($Db_goods['promote_start_date']<$newtime AND $newtime<$Db_goods['promote_end_date'])){
            $data['is_promote']=0;
            $SupplyObj->edit($data,$Db_goods['supply_id']);
        }
        /**↑↑↑↑↑↑↑↑↑↑ 是否过期：过期就要非促销 ↑↑↑↑↑↑↑↑↑↑**/

        $Db_goods['api_addtimestr']=$CommonObj->getDateStrOfTime($Db_goods['add_time'],0,1,0);
        $Db_goods['api_promotestartdate']=$CommonObj->getDateStrOfTime($Db_goods['promote_start_date'],0,1,0);
        $Db_goods['api_promoteenddate']=$CommonObj->getDateStrOfTime($Db_goods['promote_end_date'],0,1,0);

//        $start = time()-15*60;
//        if($user_id){
//            $see= $OrderObj->countRecords("user_id='{$user_id}' and good_id='{$Db_goods['supply_id']}' and pay_status=1 and createtime>=$start");
//        }else{
//            $see= $OrderObj->countRecords("id='{$order_id}' and good_id='{$Db_goods['supply_id']}' and pay_status=1 and createtime>=$start");
//        }
//
//
//        /**↓↓↓↓↓↓↓↓↓↓↓ 判断用户是否认证：非认证显示7天（is_verify) ↓↓↓↓↓↓↓↓↓↓↓**/
//        if($shopinfo['is_verify']){
//            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_certified.png";
//            $shopinfo['is_showcellphone'] = $shopinfo['is_verify'];
//        }else{
//            //$createtime=$goods['add_time'];//免费7天的开始时间
//            $endtime = $shopinfo['createtime'] + 7*86400;//免费7天的结束时间
//            $newtime=time();
//            if($newtime< $endtime || $see){
//                $shopinfo['is_showcellphone']=1;
//            }else{
//                $shopinfo['is_showcellphone']=0;
//            }
//            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_unauthorized.png";
//            $shopinfo['is_showcellphone'] = $shopinfo['is_showcellphone'];
//        }
//        /**↑↑↑↑↑↑↑↑↑↑ 判断用户是否认证：非认证显示7天（is_verify)  ↑↑↑↑↑↑↑↑↑↑**/



        /**↓↓↓↓↓↓↓↓↓↓↓ 显示电话 ↓↓↓↓↓↓↓↓↓↓↓**/
        if($shopinfo['is_verify'])
        {
            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_certified.png";
        }else{
            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_unauthorized.png";
        }
        // 是否显示电话
        $shopinfo['is_showcellphone'] = $ShopObj->isCouldSeeCellphone($Db_goods['shop_id'],$Db_goods['user_id'],$user_id);
        $shopinfo['mobile'] = $ShopObj->showCellphone($promotion_id,'supply',$Db_goods['user_id'],$user_id,$Db_goods['shop_id'],$order_id);;
        /**↑↑↑↑↑↑↑↑↑↑ 显示电话 ↑↑↑↑↑↑↑↑↑↑**/


        $shopinfo['services'] = "shop.view";
        $shopinfo['param'] = array('shop_id'=>"'{$shopinfo['id']}'",'b_display'=>2,'webface_access_token'=>'buddhaaccesstoken');
        $jsondata['supplyinfo'] = $Db_goods;
        $jsondata['bannerimg'] = $gsllery;
        $jsondata['shopinfo'] = $shopinfo;

        $jsondata['shopping_cart']['services'] = 'cart.add';
        $jsondata['shopping_cart']['param'] = array('product_table'=>'supply','product_id'=>$Db_goods['promotion_id'],'goods_number'=>1);
        $jsondata['index']=array(
            'services' =>'index.homepage',
            'param' => array(),
        );


        /**↓↓↓↓↓↓↓↓↓↓↓ 分享 ↓↓↓↓↓↓↓↓↓↓↓**/
        if($Db_goods['goods_brief'])
        {
            if(mb_strlen($Db_goods['goods_brief']) > 20){
                $goods_brief = mb_substr($Db_goods['goods_brief'],0,20) . '...';
            }else{
                $goods_brief = $Db_goods['goods_brief'];
            }
        }else{
            $goods_brief = '本地商家网：实体商家展示新渠道、广告传播新工具';
        }
        if($Db_goods['promote_price'] != '0.00')
        {
            $jia =  $Db_goods['promote_price'];
        }else{
            $jia =  $Db_goods['market_price'];
        }
        if(Buddha_Atom_String::isValidString($Db_goods['goods_thumb'])){
            $share_imgUrl = $host.$Db_goods['goods_thumb'];
        }else{
            $share_imgUrl = '';
        }
        $sharearr = array(
            'share_title'=>$Db_goods['goods_name'],
            'share_desc'=>"{$Db_goods['goods_name']}，价格：{$jia}。 ".$goods_brief,
            'share_link'=> Buddha_Atom_Share::getShareUrl('supply.detail',$promotion_id),
            'share_imgUrl'=>$share_imgUrl,
        );
        $jsondata['sharearr'] = $sharearr;
        /**↑↑↑↑↑↑↑↑↑↑ 分享 ↑↑↑↑↑↑↑↑↑↑**/


        /**↓↓↓↓↓↓↓↓↓↓↓ 店铺是否有转发有赏 ↓↓↓↓↓↓↓↓↓↓↓**/
        $rechargeObj = new Recharge();//充值表
        $rechargeinfo = $rechargeObj->getSingleFiledValues('',"uid={$shopinfo['user_id']} and shop_id='{$shopinfo['shop_id']} and is_open=1'");
        unset($shopinfo['user_id']);
        $is_reward = 0;//是否转发有赏：0否；1是
        $is_reward_img = '';
        $is_reward_url = array();
        if($rechargeinfo && $rechargeinfo['balance'] >= $rechargeinfo['forwarding_money'])
        {
            $is_reward = 1;
            $is_reward_img = $host.'style/images/zhuanfayoushang.png';
            $is_reward_url = array(
                'services' =>'shop.sharingmoney',
                'param' => array('shop_id'=>$Db_goods['shop_id']),
            );
        }
        $issharearr = array(
            'is_reward'=>$is_reward,        //  是否转发有赏：0否；1是
            'is_reward_img'=>$is_reward_img,//  转发的图标
            'is_reward_url'=>$is_reward_url,//  转发的后访问发的有赏接口
        );
        $jsondata['issharearr'] = $issharearr;
        /**↑↑↑↑↑↑↑↑↑↑ 店铺是否有转发有赏 ↑↑↑↑↑↑↑↑↑↑**/


        /**↓↓↓↓↓↓↓↓↓↓↓ 是否显示电话号码 ↓↓↓↓↓↓↓↓↓↓↓**/
        $jsondata['isshowcellphone']=array(
            'services' =>'shop.isshowcellphone',
            'param' => array('shop_id'=>$Db_goods['shop_id']),
        );
        /**↑↑↑↑↑↑↑↑↑↑ 是否显示电话号码 ↑↑↑↑↑↑↑↑↑↑**/

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '促销详情');
    }

    /**
     * 租赁详情
     * author sys
     */

    public function leasesingle()
    {
        $host = Buddha::$buddha_array['host'];
        $LeaseObj = new Lease();
        if (Buddha_Http_Input::checkParameter(array( 'lease_id','lat','lng','b_display'))) {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $lease_id = (int)Buddha_Http_Input::getParameter('lease_id');
        $lat = Buddha_Http_Input::getParameter('lat');
        $lng = Buddha_Http_Input::getParameter('lng');
        $b_display = Buddha_Http_Input::getParameter('b_display');
        $order_id = Buddha_Http_Input::getParameter('order_id');
        $UserObj = new User();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $field = array('id as lease_id','user_id','rent','leasecat_id','shop_id','lease_name','lease_start_time',
                        'lease_end_time','click_count','lease_brief','lease_desc','sourcepic','add_time');
        if($b_display == 2)
        {
            array_push($field,'lease_img');
        }else{
            array_push($field,'lease_large as lease_img');
        }

        $Db_lease= $LeaseObj->getSingleFiledValues($field,"id='{$lease_id}'");

        if(!$Db_lease)
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 30000000, '此id编号不存在');
        }

        $newtime = Buddha::$buddha_array['buddha_timestamp'];

        /**↓↓↓↓↓↓↓↓↓↓↓ 更新点击量 ↓↓↓↓↓↓↓↓↓↓↓**/
        $clickdata['click_count'] = $Db_lease['click_count']+1;
        $LeaseObj->edit($clickdata,$lease_id);
        /**↑↑↑↑↑↑↑↑↑↑ 更新点击量 ↑↑↑↑↑↑↑↑↑↑**/


        $Db_lease['api_add_timestr'] = date('Y-m-d H:i:s',$Db_lease['add_time']);

        $CommonObj = new Common();
        $Db_lease['lease_start_timestr'] = $CommonObj->getDateStrOfTime($Db_lease['lease_start_time'],1,1,0);
        $Db_lease['lease_end_timestr'] = $CommonObj->getDateStrOfTime($Db_lease['lease_end_time'],1,1,0);

        if($Db_lease['lease_img'])
        {
            $Db_lease['lease_img'] = $host . $Db_lease['lease_img'];
        }else{
            $Db_lease['lease_img'] = '';
        }

        /**↓↓↓↓↓↓↓↓↓↓↓ 是否过期：过期就要下架 ↓↓↓↓↓↓↓↓↓↓↓**/
        if(!($Db_lease['lease_start_time']<$newtime AND $newtime<$Db_lease['lease_end_time'])){
            $data['buddhastatus'] = 0;
            $LeaseObj->edit($data,$Db_lease['lease_id']);
        }
        /**↑↑↑↑↑↑↑↑↑↑ 是否过期：过期就要下架 ↑↑↑↑↑↑↑↑↑↑**/

        if($Db_lease['lease_desc']){
            if(mb_strlen($Db_lease['lease_desc']) > 20)
            {
               $share_desc = mb_substr($Db_lease['lease_desc'],0,20) . '...';
            }else{
                $share_desc = $Db_lease['lease_desc'];
            }
        }else{
            $share_desc = '快速发布您的租赁，让您的信息快速传播到用户手中，万人同时在线，为您排忧解难';
        }

        /**↓↓↓↓↓↓↓↓↓↓↓ 分享 ↓↓↓↓↓↓↓↓↓↓↓**/
        $sharearr = array(
            'share_title'=>$Db_lease['lease_name'],
            'share_desc'=>$share_desc,
            'share_link'=> Buddha_Atom_Share::getShareUrl('lease.detail',$lease_id),
            'share_imgUrl'=> $Db_lease['lease_img'],
        );
        $jsondata['sharearr'] = $sharearr;
        /**↑↑↑↑↑↑↑↑↑↑ 分享 ↑↑↑↑↑↑↑↑↑↑**/

        $ShopObj = new Shop();
        $RegionObj  = new Region();
        $OrderObj = new Order();
        $shopinfo= $ShopObj->getSingleFiledValues(array('id as shop_id','user_id','small','name','is_verify','specticloc','lat','lng','mobile','createtimestr','level2','level3','is_verify'),"id='{$Db_lease['shop_id']}' and isdel=0");

        $shopinfo['small'] = $host . $shopinfo['small'];
        $shopinfo['icon_position'] = $host . "apiindex/menuplus/icon_position.png";//距离图标,
        if($shopinfo['level2'] && $shopinfo['level3'])
        {
            $citys = $RegionObj->Region_area2($shopinfo['level2'],$shopinfo['level3']);
            $shopinfo['level2'] = $citys['level2']['name'];
            $shopinfo['level3'] = $citys['level3']['name'];
        }
        $distance=$RegionObj->getDistance($lat,$lng,$shopinfo['lat'],$shopinfo['lng'],2);
        $shopinfo['distance']=$distance;
        $data=array();
        $data['click_count']=$Db_lease['click_count']+1;
        $LeaseObj->edit($data,$lease_id);
//        $start = time()-15*60;
//        if($user_id)
//        {
//            $see= $OrderObj->countRecords("user_id='{$user_id}' and good_id='{$lease_id}' and pay_status=1 and createtime>=$start");
//        }else{
//            $see= $OrderObj->countRecords("id='{$order_id}' and good_id='{$lease_id}' and pay_status=1 and createtime>=$start");
//        }
//
//        /**↓↓↓↓↓↓↓↓↓↓↓ 判断用户是否认证：非认证显示7天（is_verify) ↓↓↓↓↓↓↓↓↓↓↓**/
//        if($shopinfo['is_verify']){
//            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_certified.png";
//            $shopinfo['is_showcellphone'] = 1;
//        }else{
//            //$createtime=$goods['add_time'];//免费7天的开始时间
//            $endtime = $shopinfo['createtime'] + 7*86400;//免费7天的结束时间
//            $newtime=time();
//            if($newtime< $endtime || $see){
//                $shopinfo['is_showcellphone']=1;
//            }else{
//                $shopinfo['is_showcellphone']=0;
//            }
//            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_unauthorized.png";
//            $shopinfo['is_showcellphone'] = $shopinfo['is_showcellphone'];
//        }
//        /**↑↑↑↑↑↑↑↑↑↑ 判断用户是否认证：非认证显示7天（is_verify)  ↑↑↑↑↑↑↑↑↑↑**/

        /**↓↓↓↓↓↓↓↓↓↓↓ 显示电话 ↓↓↓↓↓↓↓↓↓↓↓**/
        if($shopinfo['is_verify'])
        {
            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_certified.png";
        }else{
            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_unauthorized.png";
        }
        // 是否显示电话
        $shopinfo['is_showcellphone'] = $ShopObj->isCouldSeeCellphone($Db_lease['shop_id'],$Db_lease['user_id'],$user_id);
        $shopinfo['mobile'] = $ShopObj->showCellphone($lease_id,'lease',$Db_lease['user_id'],$user_id,$Db_lease['shop_id'],$order_id);;
        /**↑↑↑↑↑↑↑↑↑↑ 显示电话 ↑↑↑↑↑↑↑↑↑↑**/

        $shopinfo['services'] = "shop.view";
        $shopinfo['param'] = array('shop_id'=>"'{$shopinfo['id']}'",'b_display'=>2,'webface_access_token'=>'buddhaaccesstoken');



        /**↓↓↓↓↓↓↓↓↓↓↓ 店铺是否有转发有赏 ↓↓↓↓↓↓↓↓↓↓↓**/
        $rechargeObj = new Recharge();//充值表
        $rechargeinfo = $rechargeObj->getSingleFiledValues('',"uid={$shopinfo['user_id']} and shop_id='{$shopinfo['shop_id']} and is_open=1'");
        unset($shopinfo['user_id']);
        $is_reward = 0;//是否转发有赏：0否；1是
        $is_reward_img = '';
        $is_reward_url = array();
        if($rechargeinfo && $rechargeinfo['balance'] >= $rechargeinfo['forwarding_money']){
            $is_reward = 1;
            $is_reward_img = $host.'style/images/zhuanfayoushang.png';
            $is_reward_url = array(
                'services' =>'shop.sharingmoney',
                'param' => array('shop_id'=>$Db_lease['shop_id']),
            );
        }
        $issharearr = array(
            'is_reward'=>$is_reward,        //  是否转发有赏：0否；1是
            'is_reward_img'=>$is_reward_img,//  转发的图标
            'is_reward_url'=>$is_reward_url,//  转发的后访问发的有赏接口
        );
        $jsondata['issharearr'] = $issharearr;
        /**↑↑↑↑↑↑↑↑↑↑ 店铺是否有转发有赏 ↑↑↑↑↑↑↑↑↑↑**/



        /**↓↓↓↓↓↓↓↓↓↓↓ 是否显示电话号码 ↓↓↓↓↓↓↓↓↓↓↓**/
        $jsondata['isshowcellphone']=array(
            'services' =>'shop.isshowcellphone',
            'param' => array('shop_id'=>$Db_lease['shop_id']),
        );
        /**↑↑↑↑↑↑↑↑↑↑ 是否显示电话号码 ↑↑↑↑↑↑↑↑↑↑**/

        $jsondata['leaseyinfo'] = $Db_lease;
        $jsondata['shopinfo'] = $shopinfo;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '租赁详情');
    }

    /**
     * 招聘详情
     * author sys
     */

    public function recruitsingle()
    {
        $host = Buddha::$buddha_array['host'];
        $RecruitObj = new Recruit();
        if (Buddha_Http_Input::checkParameter(array( 'rec_id','lat','lng','b_display')))
        {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $recruit_id= (int)Buddha_Http_Input::getParameter('rec_id');
        $lat = Buddha_Http_Input::getParameter('lat');
        $lng = Buddha_Http_Input::getParameter('lng');
        $order_id = Buddha_Http_Input::getParameter('order_id');
        $UserObj = new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $field = array( 'id as recr_id','user_id','recruit_id as cat_id','shop_id','recruit_name','pay','education','work',
                        'number','treatment','recruit_start_time','recruit_end_time','contacts','tel','recruit_desc',
                        'recruit_desc','click_count','add_time','last_update');
        $Db_recruit= $RecruitObj->getSingleFiledValues($field,"id='{$recruit_id}'");
        if(!$Db_recruit)
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 30000000, '此id编号不存在');
        }

        $Db_recruit['add_timestr'] = date('Y-m-d H:i:s',$Db_recruit['add_time']);

        $CommonObj = new Common();
        $Db_recruit['recruit_start_timestr'] = $CommonObj->getDateStrOfTime($Db_recruit['recruit_start_time'],1,1);
        $Db_recruit['recruit_end_timestr'] = $CommonObj->getDateStrOfTime($Db_recruit['recruit_end_time'],1,1);
        $ShopObj = new Shop();
        $RegionObj  = new Region();
        $OrderObj = new Order();
        $shopinfo= $ShopObj->getSingleFiledValues(array('id as shop_id','user_id','small','name','is_verify','specticloc','lat','lng','mobile','createtimestr','level2','level3','is_verify'),"id='{$Db_recruit['shop_id']}' and isdel=0");

        $shopinfo['small'] = $host . $shopinfo['small'];
        $shopinfo['icon_position'] = $host . "apiindex/menuplus/icon_position.png";//距离图标
        if($shopinfo['level2'] && $shopinfo['level3'])
        {
            $citys = $RegionObj->Region_area2($shopinfo['level2'],$shopinfo['level3']);
            $shopinfo['level2'] = $citys['level2']['name'];
            $shopinfo['level3'] = $citys['level3']['name'];
        }

        $distance=$RegionObj->getDistance($lat,$lng,$shopinfo['lat'],$shopinfo['lng'],2);
        $shopinfo['distance']=$distance;
        $data=array();
        $data['click_count']=$Db_recruit['click_count']+1;
        $RecruitObj->edit($data,$recruit_id);
//        $start = time()-15*60;
//        if($user_id){
//            $see= $OrderObj->countRecords("user_id='{$user_id}' and good_id='{$recruit_id}' and pay_status=1 and createtime>=$start");
//        }else{
//            $see= $OrderObj->countRecords("id='{$order_id}' and good_id='{$recruit_id}' and pay_status=1 and createtime>=$start");
//        }
//
//        /**↓↓↓↓↓↓↓↓↓↓↓ 判断用户是否认证：非认证显示7天（is_verify) ↓↓↓↓↓↓↓↓↓↓↓**/
//        if($shopinfo['is_verify'])
//        {
//            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_certified.png";
//            $shopinfo['is_showcellphone'] = 1;
//        }else{
//            //$createtime=$goods['add_time'];//免费7天的开始时间
//            $endtime = $shopinfo['createtime'] + 7*86400;//免费7天的结束时间
//            $newtime = time();
//            if($newtime< $endtime || $see)
//            {
//                $shopinfo['is_showcellphone']=1;
//            }else{
//                $shopinfo['is_showcellphone']=0;
//            }
//            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_unauthorized.png";
//            $shopinfo['is_showcellphone'] = $shopinfo['is_showcellphone'];
//        }
//        /**↑↑↑↑↑↑↑↑↑↑ 判断用户是否认证：非认证显示7天（is_verify)  ↑↑↑↑↑↑↑↑↑↑**/
        /**↓↓↓↓↓↓↓↓↓↓↓ 显示电话 ↓↓↓↓↓↓↓↓↓↓↓**/
        if($shopinfo['is_verify'])
        {
            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_certified.png";
        }else{
            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_unauthorized.png";
        }
        // 是否显示电话
        $shopinfo['is_showcellphone'] = $ShopObj->isCouldSeeCellphone($Db_recruit['shop_id'],$Db_recruit['user_id'],$user_id);
        $shopinfo['mobile'] = $ShopObj->showCellphone($recruit_id,'recruit',$Db_recruit['user_id'],$user_id,$Db_recruit['shop_id'],$order_id);;
        /**↑↑↑↑↑↑↑↑↑↑ 显示电话 ↑↑↑↑↑↑↑↑↑↑**/


        $shopinfo['services'] = "shop.view";
        $shopinfo['param'] = array('shop_id'=>"'{$shopinfo['shop_id']}'",'b_display'=>2,'webface_access_token'=>'buddhaaccesstoken');

        /**↓↓↓↓↓↓↓↓↓↓↓ 是否显示电话号码 ↓↓↓↓↓↓↓↓↓↓↓**/
        $jsondata['isshowcellphone']=array(
            'services' =>'shop.isshowcellphone',
            'param' => array('shop_id'=>$Db_recruit['shop_id']),
        );
        /**↑↑↑↑↑↑↑↑↑↑ 是否显示电话号码 ↑↑↑↑↑↑↑↑↑↑**/

        /**↓↓↓↓↓↓↓↓↓↓↓ 更新点击量 ↓↓↓↓↓↓↓↓↓↓↓**/
        $clickdata['click_count'] = $Db_recruit['click_count']+1;
        $RecruitObj->edit($clickdata,$recruit_id);
        /**↑↑↑↑↑↑↑↑↑↑ 更新点击量 ↑↑↑↑↑↑↑↑↑↑**/

        /**↓↓↓↓↓↓↓↓↓↓↓ 是否过期：过期就要下架 ↓↓↓↓↓↓↓↓↓↓↓**/
        if(!($Db_recruit['recruit_start_time']<$newtime AND $newtime<$Db_recruit['recruit_end_time']))
        {
            $data['buddhastatus'] = 0;

            $RecruitObj->edit($data,$recruit_id);
        }
        /**↑↑↑↑↑↑↑↑↑↑ 是否过期：过期就要下架 ↑↑↑↑↑↑↑↑↑↑**/

        /**↓↓↓↓↓↓↓↓↓↓↓ 分享 ↓↓↓↓↓↓↓↓↓↓↓**/
        $Db_recruit['de_desc'] = '快速发布您的招聘信息，让您的信息快速传播到用户手中，为您挑选合适的人才。万人同时在线，为您排忧解难';
        $Db_recruit['recruit_img'] = '';// 因为招聘没有图片


        if($Db_recruit['recruit_desc'])
        {
            if(mb_strlen($Db_recruit['recruit_desc']) > 20)
            {
                $share_desc = mb_substr(strip_tags($Db_recruit['recruit_desc']),0,20) . '...';
            }else{
                $share_desc = $Db_recruit['recruit_desc'];
            }
        }else{
            $share_desc = '快速发布您的招聘信息，让您的信息快速传播到用户手中，为您挑选合适的人才。万人同时在线，为您排忧解难';
        }

        $sharearr = array(
            'share_title'=>$Db_recruit['recruit_name'],
            'share_desc'=>"招聘：{$Db_recruit['recruit_name']}，薪资待遇：".$Db_recruit['pay'].'。 '.$share_desc,
            'share_link'=> Buddha_Atom_Share::getShareUrl('demand.detail',$recruit_id),
            'share_imgUrl'=> $Db_recruit['recruit_img'],
        );
        $jsondata['sharearr'] = $sharearr;
        /**↑↑↑↑↑↑↑↑↑↑ 分享 ↑↑↑↑↑↑↑↑↑↑**/


        /**↓↓↓↓↓↓↓↓↓↓↓ 店铺是否有转发有赏 ↓↓↓↓↓↓↓↓↓↓↓**/
        $rechargeObj = new Recharge();//充值表
        $rechargeinfo = $rechargeObj->getSingleFiledValues('',"uid={$shopinfo['user_id']} and shop_id='{$shopinfo['shop_id']} and is_open=1'");
        unset($shopinfo['user_id']);
        $is_reward = 0;//是否转发有赏：0否；1是
        $is_reward_img = '';
        $is_reward_url = array();
        if($rechargeinfo && $rechargeinfo['balance'] >= $rechargeinfo['forwarding_money']){
            $is_reward = 1;
            $is_reward_img = $host.'style/images/zhuanfayoushang.png';
            $is_reward_url = array(
                'services' =>'shop.sharingmoney',
                'param' => array('shop_id'=>$Db_recruit['shop_id']),
            );
        }
        $issharearr = array(
            'is_reward'=>$is_reward,        //  是否转发有赏：0否；1是
            'is_reward_img'=>$is_reward_img,//  转发的图标
            'is_reward_url'=>$is_reward_url,//  转发的后访问发的有赏接口
        );
        $jsondata['issharearr'] = $issharearr;
        /**↑↑↑↑↑↑↑↑↑↑ 店铺是否有转发有赏 ↑↑↑↑↑↑↑↑↑↑**/


        $jsondata['recruitinfo'] = $Db_recruit;
        $jsondata['shopinfo'] = $shopinfo;


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '招聘详情');
    }

    /**
     * 需求详情
     * author sys
     */

    public function demandsingle()
    {
        $host = Buddha::$buddha_array['host'];
        $DemandObj = new Demand();
        $MysqlplusObj = new Mysqlplus();
        if (Buddha_Http_Input::checkParameter(array( 'demand_id','lat','lng','b_display')))
        {//判断参数的有效性
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $demand_id= (int)Buddha_Http_Input::getParameter('demand_id');
        $lat = Buddha_Http_Input::getParameter('lat');
        $lng = Buddha_Http_Input::getParameter('lng');
        $b_display = Buddha_Http_Input::getParameter('b_display');
        $order_id = Buddha_Http_Input::getParameter('order_id');
        $UserObj = new User();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $field = array('id as demand_id','user_id','shop_id','name','budget','click_count','demand_start_time','demand_end_time',
            'demand_brief','demand_desc','demand_thumb','demand_img','demand_large','add_time');
        /*
        if($b_display == 2){
            array_push($field,'demand_img as demand_img');
        }else{
            array_push($field,'demand_large as demand_img');
        }*/
        $Db_demand = $DemandObj->getSingleFiledValues($field,"id='{$demand_id}'");
        if(!$Db_demand)
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 30000000, '此id编号不存在');
        }
        $newtime = Buddha::$buddha_array['buddha_timestamp'];
        if(!($Db_demand['demand_start_time']<$newtime AND $newtime<$Db_demand['demand_end_time']))
        {
            $data['buddhastatus'] = 0;
            $DemandObj->edit($data,$demand_id);
        }
        $cat_name = $MysqlplusObj->getCatNameByCatidStr($Db_demand['demandcat_id'],'demandcat');
        $Db_demand['cat_name'] = $cat_name;

        $Db_demand['api_add_timestr'] = date('Y-m-d',$Db_demand['add_time']);

        $CommonObj = new Common();
        $Db_demand['demand_start_timestr'] = $CommonObj->getDateStrOfTime($Db_demand['demand_start_time'],1,1,0);
        $Db_demand['demand_end_timestr'] = $CommonObj->getDateStrOfTime($Db_demand['demand_end_time'],1,1,0);

        if($Db_demand['demand_img'])
        {
            $Db_demand['demand_img'] = $host . $Db_demand['demand_img'];
            $Db_demand['demand_large'] =  $host . $Db_demand['demand_large'];
        }else{
            $Db_demand['demand_img'] = '';
        }

        $ShopObj = new Shop();
        $RegionObj = new Region();
        $OrderObj = new Order();
        $shopinfo = $ShopObj->getSingleFiledValues(array('id as shop_id','user_id','small','name','is_verify','specticloc','lat','lng','mobile','createtimestr','level2','level3','is_verify','user_id'),"id='{$Db_demand['shop_id']}' and isdel=0");

        $shopinfo['small'] = $host . $shopinfo['small'];
        $shopinfo['icon_position'] = $host . "apiindex/menuplus/icon_position.png";//距离图标
        if($shopinfo['level2'] && $shopinfo['level3'])
        {
            $citys = $RegionObj->Region_area2($shopinfo['level2'],$shopinfo['level3']);
            $shopinfo['level2'] = $citys['level2']['name'];
            $shopinfo['level3'] = $citys['level3']['name'];
        }
        $distance = $RegionObj->getDistance($lat,$lng,$shopinfo['lat'],$shopinfo['lng'],2);
        $shopinfo['distance']=$distance;


//
//        /**↓↓↓↓↓↓↓↓↓↓↓ 是否显示电话 ↓↓↓↓↓↓↓↓↓↓↓**/
//        $start = time()-15*60;
//        if($user_id)
//        {
//            $see = $OrderObj->countRecords("user_id='{$user_id}' and good_id='{$demand_id}' and pay_status=1 and createtime>=$start");
//        }else{
//            $see = $OrderObj->countRecords("id='{$order_id}' and good_id='{$demand_id}' and pay_status=1 and createtime>=$start");
//        }
//
//        /**↓↓↓↓↓↓↓↓↓↓↓ 判断用户是否认证：非认证显示7天（is_verify) ↓↓↓↓↓↓↓↓↓↓↓**/
//
//        if($shopinfo['is_verify'])
//        {
//            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_certified.png";
//            $shopinfo['is_showcellphone'] = 1;
//        }else{
//            //$createtime=$goods['add_time'];//免费7天的开始时间
//            $endtime = $shopinfo['createtime'] + 7*86400;//免费7天的结束时间
//            $newtime = Buddha::$buddha_array['buddha_timestamp'];
//            if($newtime< $endtime || $see)
//            {
//                $shopinfo['is_showcellphone']=1;
//            }else{
//                $shopinfo['is_showcellphone']=0;
//            }
//            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_unauthorized.png";
//            $shopinfo['is_showcellphone'] = $shopinfo['is_showcellphone'];
//        }
//        /**↑↑↑↑↑↑↑↑↑↑  判断用户是否认证：非认证显示7天（is_verify) ↑↑↑↑↑↑↑↑↑↑**/
//        /**↑↑↑↑↑↑↑↑↑↑ 是否显示电话 ↑↑↑↑↑↑↑↑↑↑**/
        /**↓↓↓↓↓↓↓↓↓↓↓ 显示电话 ↓↓↓↓↓↓↓↓↓↓↓**/
        if($shopinfo['is_verify'])
        {
            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_certified.png";
        }else{
            $shopinfo['icon_certification'] = $host . "apishop/menuplus/icon_unauthorized.png";
        }
        // 是否显示电话
        $shopinfo['is_showcellphone'] = $ShopObj->isCouldSeeCellphone($Db_demand['shop_id'],$Db_demand['user_id'],$user_id);
        $shopinfo['mobile'] = $ShopObj->showCellphone($demand_id,'demand',$Db_demand['user_id'],$user_id,$Db_demand['shop_id'],$order_id);
        /**↑↑↑↑↑↑↑↑↑↑ 显示电话 ↑↑↑↑↑↑↑↑↑↑**/

        $shopinfo['services'] = "shop.view";
        $shopinfo['param'] = array('shop_id'=>"'{$shopinfo['id']}'",'b_display'=>2,'webface_access_token'=>'buddhaaccesstoken');

        $jsondata['isshowcellphone']=array();

        if(Buddha_Atom_Array::isValidArray($Db_demand))
        {
            /**↓↓↓↓↓↓↓↓↓↓↓ 更新点击量 ↓↓↓↓↓↓↓↓↓↓↓**/
            $clickdata['click_count'] = $Db_demand['click_count']+1;
            $DemandObj->edit($clickdata,$demand_id);
            /**↑↑↑↑↑↑↑↑↑↑ 更新点击量 ↑↑↑↑↑↑↑↑↑↑**/


            /**↓↓↓↓↓↓↓↓↓↓↓ 是否过期：过期就要下架 ↓↓↓↓↓↓↓↓↓↓↓**/
            if(!($Db_demand['demand_start_time']<$newtime AND $newtime<$Db_demand['demand_end_time'])){
                $data['buddhastatus'] = 0;
                $DemandObj->edit($data,$demand_id);
            }
            /**↑↑↑↑↑↑↑↑↑↑ 是否过期：过期就要下架 ↑↑↑↑↑↑↑↑↑↑**/


            $jsondata['recruitinfo'] = $Db_demand;
            $jsondata['shopinfo'] = $shopinfo;

            /**↓↓↓↓↓↓↓↓↓↓↓ 分享 ↓↓↓↓↓↓↓↓↓↓↓**/
            if($Db_demand['demand_desc']){
                if(mb_strlen($Db_demand['demand_desc']) > 20){
                    $share_desc = mb_substr(strip_tags($Db_demand['demand_desc']) ,0,20) . '...';
                }else{
                    $share_desc= $Db_demand['demand_desc'];
                }
            }else{
                $share_desc = '快速发布您的需求，快速解决您的问题，万人同时在线，为您排忧解难';
            }

            $sharearr = array(
                'share_title'=>$Db_demand['name'],
                'share_desc'=>$share_desc,
                'share_link'=> Buddha_Atom_Share::getShareUrl('demand.detail',$demand_id),
                'share_imgUrl'=> $Db_demand['demand_img'],
            );
            $jsondata['sharearr'] = $sharearr;
            /**↑↑↑↑↑↑↑↑↑↑ 分享 ↑↑↑↑↑↑↑↑↑↑**/

            /**↓↓↓↓↓↓↓↓↓↓↓ 店铺是否有转发有赏 ↓↓↓↓↓↓↓↓↓↓↓**/
            $rechargeObj = new Recharge();//充值表
            $rechargeinfo = $rechargeObj->getSingleFiledValues('',"uid={$shopinfo['user_id']} and shop_id='{$shopinfo['shop_id']} and is_open=1'");
            unset($shopinfo['user_id']);
            $is_reward = 0;//是否转发有赏：0否；1是
            $is_reward_img = '';
            $is_reward_url = array();

            if($rechargeinfo && $rechargeinfo['balance'] >= $rechargeinfo['forwarding_money'])
            {
                $is_reward = 1;
                $is_reward_img = $host.'style/images/zhuanfayoushang.png';
                $is_reward_url = array(
                    'services' =>'shop.sharingmoney',
                    'param' => array('shop_id'=>$Db_demand['shop_id']),
                );
            }
            $issharearr = array(
                'is_reward'=>$is_reward,        //  是否转发有赏：0否；1是
                'is_reward_img'=>$is_reward_img,//  转发的图标
                'is_reward_url'=>$is_reward_url,//  转发的后访问发的有赏接口
            );
            $jsondata['issharearr'] = $issharearr;

            /**↑↑↑↑↑↑↑↑↑↑ 店铺是否有转发有赏 ↑↑↑↑↑↑↑↑↑↑**/


            /**↓↓↓↓↓↓↓↓↓↓↓ 是否显示电话号码 ↓↓↓↓↓↓↓↓↓↓↓**/
            $jsondata['isshowcellphone']=array(
                'services' =>'shop.isshowcellphone',
                'param' => array('shop_id'=>$Db_demand['shop_id']),
            );
            /**↑↑↑↑↑↑↑↑↑↑ 是否显示电话号码 ↑↑↑↑↑↑↑↑↑↑**/

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '需求详情');
    }




}