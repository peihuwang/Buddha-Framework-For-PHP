<?php

/**
 * Class HeartproController
 */
class HeartproController extends Buddha_App_Action
{

    protected $tablenamestr;
    protected $tablename;
    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));


        $webface_access_token = Buddha_Http_Input::getParameter('webface_access_token');
        if ($webface_access_token == '') {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444002, 'webface_access_token必填');
        }

        $ApptokenObj = new Apptoken();
        $num = $ApptokenObj->getTokenNum($webface_access_token);
        if ($num == 0) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444003, 'webface_access_token不正确请从新获取');
        }
        $this->tablenamestr='1分营销';
        $this->tablename='heartpro';
    }
//////////////////////  个人中心：商家 /////////////////////////////////////
    /**
     *  个人中心： 商家 1分购添加之前
     */
    public function beforeadd()
    {


        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $ActivityObj=new Activity();
        $UserObj=new User();
        $ShopObj=new Shop();
        $SupplycatObj=new Supplycat();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        if(!$ShopObj->IsUserHasShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000042, '你还未创建店铺，快去创建吧！');
        }

        if(!$ShopObj->IsUserHasNormalShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000043, '你名下的所有店铺异常，请处理后一个(要添加到哪个店铺下)或全部店铺后再添加！');
        }

        $jsondata = array();


        /*地区*/
        $jsondata['region']=array(
            'Services' => 'ajaxregion.getBelongFromFatherId',
            'param' => array('father'=>1),
        );

        /**正常店铺列表*/
        $jsondata['shoplist'] = $ShopObj->getShoparrByUserid($user_id);

        $jsondata['unit'] = $SupplycatObj->getunit();
        /**标题**/
        $jsondata['headertitle'] = $this->tablenamestr;

        /**正常店铺下的产品接口*/
        $jsondata['belongshop']=array(
            'Services' => 'ajaxregion.getBelongShopInformationByShopid',
            'param' => array('table_name'=>'supply'),
        );


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'添加之前的操作接口');
    }
    /**
     *  个人中心： 商家 1分购添加
     */
    public function add()
    {
        header("Content-Type:text/html;charset=utf-8");

        if (Buddha_Http_Input::checkParameter(array('usertoken','typeid','name','start_date','end_date','coverphoto_arr','shop_id','is_remote','buddhastatus','brief','desc'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();
        $CommonObj=new Common();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $ActivityObj=new Activity();
        $RegionObj=new Region();
        $JsonimageObj = new Jsonimage();
        $ShopObj = new Shop();
        $HeartproObj = new Heartpro();
        $MoregalleryObj = new Moregallery();
        $CommonObj = new Common();

        if(!$UserObj->isHasMerchantPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');

        }


//////////////////////////////////////////
        $goods_name=Buddha_Http_Input::getParameter('good_name');//1分购名称

        $shop_id=Buddha_Http_Input::getParameter('shop_id');//发布店铺内码ID
        if(!$CommonObj->isToUserByTablenameAndTableid('shop',$shop_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000009, '店铺内码ID无效！');
        }

        $shopsupply_id=Buddha_Http_Input::getParameter('shopsupply_id');//商品内码ID
        if(!$CommonObj->isToUserByTablenameAndTableid('supply',$shopsupply_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000060, '商品内码ID无效!');
        }

        $goods_unit=Buddha_Http_Input::getParameter('goods_unit');//属性单位内码ID
        if(!$CommonObj->isIdByTablenameAndTableid('supply',$goods_unit)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000064, '属性单位内码ID无效!');
        }

        $price=Buddha_Http_Input::getParameter('price');    //  销售价
        $stock=Buddha_Http_Input::getParameter('stock');    //  库存量
        $votecount=Buddha_Http_Input::getParameter('votecount');//投票数量

        $start_date=Buddha_Http_Input::getParameter('start_date');  //报名开始时间
        $end_date=Buddha_Http_Input::getParameter('end_date');      //报名结束时间
//        $shelvesstart_date=Buddha_Http_Input::getParameter('shelvesstart_date');  //上架时间
//        $shelvesend_date=Buddha_Http_Input::getParameter('shelvesend_date');      //下架时间

        $shelvesstart_date = Buddha::$buddha_array['buddha_timestamp'];  //上架时间
//        $shelvesend_date = Buddha::$buddha_array['buddha_timestamp'] + (15*24*60*60);      //下架时间
        $HeartproObj= new Heartpro();
        $shelvesend_date = $HeartproObj->shelvesend();      //下架时间

/////////////////////////
        /***商品异地发布**/
        $is_remote = Buddha_Http_Input::getParameter('is_remote');      //  是否异地发布
        $level1=Buddha_Http_Input::getParameter('level1');              //  异地发布区域的ID
        $level2=Buddha_Http_Input::getParameter('level2');              //  异地发布区域的ID
        $level3=Buddha_Http_Input::getParameter('level3');              //  异地发布区域的ID

        /*判断 $is_remote 的值是否是0,1*/
        if(!$CommonObj->isIdInDataEffectiveById($is_remote)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }
        /*只有在选择了异地发布的时候才验证地区*/
        if($is_remote==1)
        {
            if (!$RegionObj->isProvince($level1)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000001, '国家、省、市、区 中省的地区内码id不正确！');
            }

            if (!$RegionObj->isCity($level2)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中市的地区内码id不正确！');
            }

            if (!$RegionObj->isArea($level3)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中区的地区内码id不正确！');
            }

        }
//////////////////

        $keywords = Buddha_Http_Input::getParameter('keywords');    //关键词


        //描述、图片
        $goods_desc = Buddha_Http_Input::getParameter('goods_desc');//1分购规则详情


        /*相册*/
        $coverphoto_arr = Buddha_Http_Input::getParameter('coverphoto_arr');

        /*判断图片数组是否是Json*/
        if(Buddha_Atom_String::isJson($coverphoto_arr)){
            $coverphoto_arr = json_decode($coverphoto_arr);
        }

        /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($coverphoto_arr);


        $data['user_id'] = $user_id;
        $data['name'] = $goods_name;//1分购名称
        $data['shop_id'] = $shop_id;//发布店铺内码ID
        $data['table_id'] = $shopsupply_id;//商品内码ID
        $data['table_name'] ='supply';
        $data['unit_id'] = $goods_unit;//属性单位内码ID
        $data['price'] = $price;//销售价
        $data['originalstock'] = $stock;
        $data['stock'] = $stock;//库存量
        $data['votecount'] = $votecount;//投票数量

        $data['applystarttime'] = strtotime($start_date); //报名开始时间
        $data['applystarttimestr'] = $start_date;
        $data['applyendtime'] = strtotime($end_date); //报名结束时间
        $data['applyendtimestr'] =  $end_date;
        $data['onshelftime'] = strtotime($shelvesstart_date); //上架时间
        $data['onshelftimestr'] = $shelvesstart_date;
        $data['offshelftime'] = strtotime($shelvesend_date); //下架时间
        $data['offshelftimestr'] = $shelvesend_date;
        if($is_remote==0){//$activity_id
            $data['is_remote']=0;
            $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$user_id}' and id='{$shop_id}' and isdel=0");
            $data['level0']=$Db_level['level0'];
            $data['level1']=$Db_level['level1'];
            $data['level2']=$Db_level['level2'];
            $data['level3']=$Db_level['level3'];
        }elseif($is_remote==1){//1为异地

            $data['level1']=$level1;
            $data['level2']=$level2;
            $data['level3']=$level3;
        }
        $data['desc'] = $goods_desc;
        $data['keywords'] = $keywords;
        $data['number']=$CommonObj->GeneratingNumber();

        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $Db_Heartpro_id = $HeartproObj->add($data);

        if(!$Db_Heartpro_id){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000070, $this->tablenamestr.'添加失败！');
        }


        /*封面照相册*/
        if(Buddha_Atom_Array::isValidArray($coverphoto_arr)){
            $savePath="storage/{$this->tablename}/{$Db_Heartpro_id}/";
            foreach($coverphoto_arr as $k=>$v){
                $temp_img_arr = explode(',', $v);
                $temp_base64_string = $temp_img_arr[1];
                $output_file = date('Ymdhis',time()). "-{$k}.jpg";
                $filePath =PATH_ROOT.$savePath.$output_file;
                Buddha_Atom_File::base64contentToImg($filePath,$temp_base64_string);
                Buddha_Atom_File::resolveImageForRotate($filePath,NULL);
                $result_img = $savePath.''.$output_file;
                $MoreImage[] = "{$result_img}";
            }
            if(Buddha_Atom_Array::isValidArray($MoreImage)){
                $uploadfield='file';
                $MoregalleryObj->addImageArrToMoregallery($MoreImage,$Db_Heartpro_id,$savePath,$shop_id,$uploadfield);

                /*把封面照设为默认展示图片并把相应的图片路径更新到activity表中*/
                $HeartproObj->setFirstGalleryImgToSupply($Db_Heartpro_id,$this->tablename,'file');
            }
        }
        /*富文本编辑器图片处理*/
        if($goods_desc){
            $MoregalleryObj=new Moregallery();
            $field='desc';
            $saveData_desc = $MoregalleryObj->base_upload($goods_desc,$Db_Heartpro_id,$this->tablename,$field);
            $saveData_desc = str_replace(PATH_ROOT,'/', $saveData_desc);
            $details['desc'] = $saveData_desc;
            $HeartproObj->edit($details,$Db_Heartpro_id);
        }

        /*是否产生订单：0否；1是*/
        $is_needcreateorder = 0;
        $Services ='';
        $param = array();
        /**$remote==1表示发布异地产品添加订单**/
        if($is_remote==1){
            $is_needcreateorder = 1;
            $Services = '';
            $param = array();
        }

        $jsondata = array();
        $jsondata['db_isok'] =1;
        $jsondata['db_msg'] =$this->tablenamestr.'添加成功';
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['heartpro_id'] = $Db_Heartpro_id;
        $jsondata['is_needcreateorder'] = $is_needcreateorder;
        $jsondata['Services'] = $Services;
        $jsondata['param'] = $param;


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'添加');
    }


    /**
     *  个人中心： 商家 1分购编辑 之前
     */
    public function beforeedit()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','heartpro_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $HeartproObj = new Heartpro();
        $UserObj = new User();
        $ShopObj = new Shop();
        $SupplycatObj = new Supplycat();
        $MoregalleryObj = new Moregallery();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        if(!$ShopObj->IsUserHasShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000042, '你还未创建店铺，快去创建吧！');
        }

        if(!$ShopObj->IsUserHasNormalShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000043, '你名下的所有店铺异常，请处理后一个(要添加到哪个店铺下)或全部店铺后再添加！');
        }


        $heartpro_id = (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?(int)Buddha_Http_Input::getParameter('b_display'):2;

        $filed=array('id as heartpro_id,name,price,stock,votecount,onshelftimestr, onshelftime,offshelftime,offshelftimestr,applystarttime,applystarttimestr,applyendtime,applyendtimestr,keywords','table_id','shop_id','level1','level2','level3','table_name','unit_id','is_remote','details');

        $Db_Heartpro = $HeartproObj->getSingleFiledValues($filed,"id='{$heartpro_id}' and user_id='{$user_id}'");

        $jsondata = array();

        if(Buddha_Atom_Array::isValidArray($Db_Heartpro))
        {
            $Supply_name = $this->db->getSingleFiledValues(array('id','goods_name'), $Db_Heartpro['table_name'],"id='{$Db_Heartpro['table_id']}' and user_id='{$user_id}'");
            $Db_Heartpro['supply_name']=$Supply_name['goods_name'];
            $Db_Heartpro['desc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Heartpro['desc']);

            $jsondata = $Db_Heartpro;

            /**↓↓↓↓↓↓↓↓↓↓↓ 产品相册 ↓↓↓↓↓↓↓↓↓↓↓**/
            $gimages = $MoregalleryObj->getGoodsImage($heartpro_id,$this->tablename,'file',$b_display);

            foreach ($gimages as $k=>$v){
                $gimages[$k]['moregallery_id']=$v['id'];
                $gimages[$k]['img']=$host.$v['goods_thumb'];

                $gimages[$k]['Services']='moregallery.deleteimage';
                $gimages[$k]['param']=array('moregallery_id'=>$v['id'],'table_name'=>'moregallery');
                unset($gimages[$k]['id']);
                unset($gimages[$k]['goods_thumb']);
                unset($gimages[$k]['table_name']);
            }
            $jsondata['imgmore'] = $gimages;
            /**↑↑↑↑↑↑↑↑↑↑ 相册 ↑↑↑↑↑↑↑↑↑↑**/

            /**标题**/
            $jsondata['headertitle'] = $this->tablenamestr;

                /*正常店铺列表*/
            $jsondata['shoplist'] = $ShopObj->getShoparrByUserid($user_id,$Db_Heartpro['shop_id']);

            /*单位列表*/
            $jsondata['unit'] = $SupplycatObj->getunit($Db_Heartpro['unit_id']);
            /*地区*/
            $jsondata['region']=array(
                'Services' => 'ajaxregion.getBelongFromFatherId',
                'param' => array('father'=>1),
            );

            /*正常店铺下的产品接口*/
            $jsondata['belongshop']=array(
                'Services' => 'ajaxregion.getBelongShopInformationByShopid',
                'param' => array('table_name'=>$this->tablename),
            );
        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'编辑之前的操作接口');
    }



    /**
     *  个人中心： 商家 1分购编辑
     */
    public function edit()
    {
        header("Content-Type:text/html;charset=utf-8");

        if (Buddha_Http_Input::checkParameter(array('usertoken','typeid','name','start_date','end_date','coverphoto_arr','shop_id','is_remote','buddhastatus','brief','desc','heartpro_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();
        $CommonObj=new Common();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $RegionObj=new Region();
        $JsonimageObj = new Jsonimage();
        $ShopObj = new Shop();
        $HeartproObj = new Heartpro();
        $MoregalleryObj = new Moregallery();
        $CommonObj = new Common();

        if(!$UserObj->isHasMerchantPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');

        }


//////////////////////////////////////////
        $goods_name=Buddha_Http_Input::getParameter('good_name');//1分购名称

        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id'):0;//发布店铺内码ID
        if(!$CommonObj->isToUserByTablenameAndTableid('shop',$shop_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000009, '店铺内码ID无效！');
        }

        $shopsupply_id = (int)Buddha_Http_Input::getParameter('shopsupply_id')?(int)Buddha_Http_Input::getParameter('shopsupply_id'):0;//商品内码ID
        if(!$CommonObj->isToUserByTablenameAndTableid('supply',$shopsupply_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000060, '商品内码ID无效!');
        }

        $goods_unit = (int)Buddha_Http_Input::getParameter('goods_unit')?(int)Buddha_Http_Input::getParameter('goods_unit'):0;//属性单位内码ID
        if(!$CommonObj->isIdByTablenameAndTableid('supply',$goods_unit)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000064, '属性单位内码ID无效!');
        }

        $heartpro_id = (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;//1分购 内码ID
        if(!$CommonObj->isIdByTablenameAndTableid('heartpro',$heartpro_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000071, '1分购内码ID无效!');
        }
        $Db_Heartpro_id = $heartpro_id;


        $price=Buddha_Http_Input::getParameter('price');//销售价
        $stock=Buddha_Http_Input::getParameter('stock');//库存量
        $votecount=Buddha_Http_Input::getParameter('votecount');//投票数量

        $start_date=Buddha_Http_Input::getParameter('start_date');  //报名开始时间
        $end_date=Buddha_Http_Input::getParameter('end_date');      //报名结束时间
//        $shelvesstart_date=Buddha_Http_Input::getParameter('shelvesstart_date');  //上架时间
//        $shelvesend_date=Buddha_Http_Input::getParameter('shelvesend_date');      //下架时间
/////////////////////////
        /***商品异地发布**/
        $is_remote = Buddha_Http_Input::getParameter('is_remote');    //是否异地发布
        $level1=Buddha_Http_Input::getParameter('level1');   //异地发布区域的ID
        $level2=Buddha_Http_Input::getParameter('level2');   //异地发布区域的ID
        $level3=Buddha_Http_Input::getParameter('level3');   //异地发布区域的ID

        /*判断 $is_remote 的值是否是0,1*/
        if(!$CommonObj->isIdInDataEffectiveById($is_remote)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }
        /*只有在选择了异地发布的时候才验证地区*/
        if($is_remote==1) {
            if (!$RegionObj->isProvince($level1)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000001, '国家、省、市、区 中省的地区内码id不正确！');
            }

            if (!$RegionObj->isCity($level2)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中市的地区内码id不正确！');
            }

            if (!$RegionObj->isArea($level3)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中区的地区内码id不正确！');
            }

        }
//////////////////

        $keywords = Buddha_Http_Input::getParameter('keywords');    //关键词


        //描述、图片
        $goods_desc = Buddha_Http_Input::getParameter('goods_desc');//1分购规则详情


        /*相册*/
        $coverphoto_arr = Buddha_Http_Input::getParameter('coverphoto_arr');

        /*判断图片数组是否是Json*/
        if(Buddha_Atom_String::isJson($coverphoto_arr)){
            $coverphoto_arr = json_decode($coverphoto_arr);
        }

        /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($coverphoto_arr);


        $data['user_id'] = $user_id;
        $data['name'] = $goods_name;//1分购名称
        $data['shop_id'] = $shop_id;//发布店铺内码ID
        $data['table_id'] = $shopsupply_id;//商品内码ID
        $data['table_name'] ='supply';
        $data['unit_id'] = $goods_unit;//属性单位内码ID
        $data['price'] = $price;//销售价
        $data['originalstock'] = $stock;
        $data['stock'] = $stock;//库存量
        $data['votecount'] = $votecount;//投票数量
        $data['applystarttime'] = strtotime($start_date); //报名开始时间
        $data['applystarttimestr'] = $start_date;
        $data['applyendtime'] = strtotime($end_date); //报名结束时间
        $data['applyendtimestr'] =  $end_date;
        $data['onshelftime'] = strtotime($shelvesstart_date); //上架时间
        $data['onshelftimestr'] = $shelvesstart_date;
        $data['offshelftime'] = strtotime($shelvesend_date); //下架时间
        $data['offshelftimestr'] = $shelvesend_date;
        if($is_remote==0){//$activity_id
            $data['is_remote']=0;
            $Db_level = $ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$user_id}' and id='{$shop_id}' and isdel=0");
            $data['level0'] = $Db_level['level0'];
            $data['level1'] = $Db_level['level1'];
            $data['level2'] = $Db_level['level2'];
            $data['level3'] = $Db_level['level3'];
        }elseif($is_remote==1){//1为异地

            $data['level1'] = $level1;
            $data['level2'] = $level2;
            $data['level3'] = $level3;
        }
        $data['desc'] = $goods_desc;
        $data['keywords'] = $keywords;
        $data['number'] = $CommonObj->GeneratingNumber();

        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $HeartproObj->edit($data,$heartpro_id);

        /*封面照相册*/
        if(Buddha_Atom_Array::isValidArray($coverphoto_arr)){
            $savePath="storage/{$this->tablename}/{$Db_Heartpro_id}/";
            foreach($coverphoto_arr as $k=>$v){
                $temp_img_arr = explode(',', $v);
                $temp_base64_string = $temp_img_arr[1];
                $output_file = date('Ymdhis',time()). "-{$k}.jpg";
                $filePath = PATH_ROOT.$savePath.$output_file;
                Buddha_Atom_File::base64contentToImg($filePath,$temp_base64_string);
                Buddha_Atom_File::resolveImageForRotate($filePath,NULL);
                $result_img = $savePath.''.$output_file;
                $MoreImage[] = "{$result_img}";
            }
            if(Buddha_Atom_Array::isValidArray($MoreImage)){
                $uploadfield = 'file';
                $MoregalleryObj->addImageArrToMoregallery($MoreImage,$Db_Heartpro_id,$savePath,$shop_id,$uploadfield);

                /*把封面照设为默认展示图片并把相应的图片路径更新到activity表中*/
                $HeartproObj->setFirstGalleryImgToSupply($Db_Heartpro_id,$this->tablename,'file');
            }
        }

        /*富文本编辑器图片处理*/
        if($goods_desc){
            $MoregalleryObj = new Moregallery();
            $field='desc';
            $saveData_desc = $MoregalleryObj->base_upload($goods_desc,$Db_Heartpro_id,$this->tablename,$field);
            $saveData_desc = str_replace(PATH_ROOT,'/', $saveData_desc);
            $details['desc'] = $saveData_desc;
            $HeartproObj->edit($details,$Db_Heartpro_id);
        }

        /*是否产生订单：0否；1是*/
        $is_needcreateorder = 0;
        $Services ='';
        $param = array();
        /**$remote==1表示发布异地产品添加订单**/
        if($is_remote==1){
            $is_needcreateorder = 1;
            $Services = '';
            $param = array();
        }

        $jsondata = array();
        $jsondata['db_isok'] =1;
        $jsondata['db_msg'] =$this->tablenamestr.'编辑成功';
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['heartpro_id'] = $Db_Heartpro_id;
        $jsondata['is_needcreateorder'] = $is_needcreateorder;
        $jsondata['Services'] = $Services;
        $jsondata['param'] = $param;


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'编辑');
    }



    /**
     * 个人中心：商家 1分购删除
     */

    public function del()
    {
        header("Content-Type:text/html;charset=utf-8");

        if (Buddha_Http_Input::checkParameter(array('usertoken','heartpro_id')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();
        $MoregalleryObj=new Moregallery();
        $CommonObj=new Common();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $heartpro_id = Buddha_Http_Input::getParameter('heartpro_id')?Buddha_Http_Input::getParameter('heartpro_id'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if(!$UserObj->isHasMerchantPrivilege($user_id))
        {

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');

        }

        /*判断 1分购 Id是否有效*/
        if(!$CommonObj->isIdByTablenameAndTableid($this->tablename,$heartpro_id))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000071	, '1分购内码ID无效!！');
        }


///////////////////////////////////////////////////////////////////////////////////////////////////
        $where="tablename={$this->tablename} AND goods_id='{$heartpro_id}'";

        $Moregallery_where = $where." AND user_id='{$user_id}'";


        $Db_Moregallery = $MoregalleryObj->getFiledValues(array('id','goods_thumb','goods_img','goods_large','sourcepic'),$Moregallery_where);

        if(Buddha_Atom_Array::isValidArray($Db_Moregallery)){
            $idstr='';
            foreach ($Db_Moregallery as $k=>$v){

                if(Buddha_Atom_String::isValidString($v['goods_thumb'])){
                    @unlink(PATH_ROOT.$v['goods_thumb']);
                }
                if(Buddha_Atom_String::isValidString($v['goods_img'])){
                    @unlink(PATH_ROOT.$v['goods_img']);
                }
                if(Buddha_Atom_String::isValidString($v['goods_large'])){
                    @unlink(PATH_ROOT.$v['goods_large']);
                }
                if(Buddha_Atom_String::isValidString($v['sourcepic'])){
                    @unlink(PATH_ROOT.$v['sourcepic']);
                }
                $idstr.=$v['id'].',';
            }
        }
        $idstr = rtrim($idstr,',');
        $Moregallery_where.=" AND id IN ({$idstr})";
        $Db_Moregallery_num = $this->db->delRecords ('moregallery',$Moregallery_where);

///////////////////////////////////////////////////////////////////////////////////////////////////



        /**活动数据 删除***/
///////////////////////////////////////////////////////////////////////////////////////////////////

        $Db_Heartpro_num = $this->db->delRecords ( $this->tablename, "id='{$heartpro_id}'" );

        $jsondata = array();
        if($Db_Heartpro_num){
            $jsondata['is_ok'] = 1;
            $jsondata['db_msg'] = $this->tablenamestr.'删除成功!';
        }else{
            $jsondata['is_ok'] = 0;
            $jsondata['db_msg'] = $this->tablenamestr.'删除失败!';
        }
///////////////////////////////////////////////////////////////////////////////////////////////////
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['heartpro_id'] = $heartpro_id;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'删除');
    }



    /**
     *  个人中心： 商家 1分购列表
     */
    public function merchantsmanagementmore ()
    {

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('b_display', 'usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj = new Common();
        $UserObj = new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken') ? Buddha_Http_Input::getParameter('usertoken') : 0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username', 'level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];



        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        $api_keyword = Buddha_Http_Input::getParameter('api_keyword') ? Buddha_Http_Input::getParameter('api_keyword') : '';

        $b_display = (int)Buddha_Http_Input::getParameter('b_display') ? Buddha_Http_Input::getParameter('b_display') : 2;

        $page = Buddha_Http_Input::getParameter('page') ? Buddha_Http_Input::getParameter('page') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        $view = Buddha_Http_Input::getParameter('view') ? Buddha_Http_Input::getParameter('view') : 0;

        $shop_id = Buddha_Http_Input::getParameter('shop_id') ? Buddha_Http_Input::getParameter('shop_id') : 0;


        /*商家：商家只能查看自己的活动信息和没有被删除的活动*/

        $where = " isdel=0 AND user_id='{$user_id}' ";


        if (Buddha_Atom_String::isValidString($api_keyword))
        {
            $where .= Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($api_keyword, array('name', 'number'));
        }

        if($shop_id>0){
            $where .=" AND shop_id='{$shop_id}'";
        }


        if(!$CommonObj->isIdInDataEffectiveById($view,array(0,2,3,4))){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }


        if ($view) {
            switch($view){
                case 2;
                    $where.=' and is_sure=0 ';
                case 3;
                    $where.=' and is_sure=1 ';
                    break;
                case 4;
                    $where.=' and is_sure=4 ';
                    break;
            }
        }

        $isShowStop=0;

        $fileds = ' id AS heartpro_id, name, buddhastatus,is_sure, number ,price ,is_sure,applystarttime,applyendtime,applystarttimestr,applyendtimestr';

        if ($b_display == 1) {

            $fileds .= ' ,medium AS api_img ';
        } elseif ($b_display == 2) {

            $fileds .= ' , small AS api_img ';
        }

        $orderby = " ORDER BY createtime DESC ";

        $sql = " SELECT  {$fileds}  
                 FROM {$this->prefix}{$this->tablename} WHERE {$where} 
                 {$orderby}  " . Buddha_Tool_Page::sqlLimit($page, $pagesize);

        $Db_Heartpro = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

////////////////////////////////////////////////////////////////////////////////////////

        $jsondata = array();
        $jsondata['page'] = 0;
        $jsondata['pagesize'] = 0;
        $jsondata['totalrecord'] = 0;
        $jsondata['totalpage'] = 0;
        $jsondata['list'] = array();
////////////////////////////////////////////////////////////////////////////////////////
        $Services='heartpro.merchantsmanagementmore';
        $jsondata['nav']=array(
            0=>array( 'select'=>1,'name'=>'全部','pageflag'=>'','type'=>0,
                'Services'=>$Services,'param'=>array('view'=>0)),
            1=>array( 'select'=>0,'name'=>'新添加','pageflag'=>'','type'=>2,
                'Services'=>$Services,'param'=>array('view'=>2)),

            2=>array( 'select'=>0,'name'=>'已通过','pageflag'=>'','type'=>3,
                'Services'=>$Services,'param'=>array('view'=>3)),

            3=>array( 'select'=>0,'name'=>'未通过','pageflag'=>'','type'=>4,
                'Services'=>$Services,'param'=>array('view'=>4)),
        );


        $jsondata['add'] = array(
            'Services'=>'heartpro.beforeadd',
            'param'=>array(),
        );

        if (Buddha_Atom_Array::isValidArray($Db_Heartpro)) {

            foreach ($Db_Heartpro as $k => $v) {

                if ($v['is_sure'] == 0) {

                    $Db_Heartpro[$k]['api_auditstateimg'] = $host . 'apistate/menuplus/weishenhe.png';

                } elseif ($v['is_sure'] == 4) {

                    $Db_Heartpro[$k]['api_auditstateimg'] = $host . 'apistate/menuplus/weitonguo.png';

                } elseif ($v['is_sure'] == 1) {

                    $Db_Heartpro[$k]['api_auditstateimg'] = $host . 'apistate/menuplus/yitonguo.png';

                }

                if (Buddha_Atom_String::isValidString($v['api_img'])) {

                    $Db_Heartpro[$k]['api_img'] = $host . $v['api_img'];

                } else {

                    $Db_Heartpro[$k]['api_img'] = '';
                }

                if ($v['buddhastatus'] == 1) {

                    $Db_Heartpro[$k]['api_buddhastatus'] = '上 架';

                } else if ($v['buddhastatus'] == 0) {

                    $Db_Heartpro[$k]['api_buddhastatus'] = '下 架';

                }


                if(!Buddha_Atom_String::isValidString($v['applystarttimestr'])){
                    $Db_Heartpro[$k]['applystarttimestr']=$CommonObj->getDateStrOfTime($v['applystarttime'],1,0,0);
                }

                if(!Buddha_Atom_String::isValidString($v['applyendtimestr'])){
                    $Db_Heartpro[$k]['applyendtimestr']=$CommonObj->getDateStrOfTime($v['applyendtime'],1,0,0);
                }

                $Db_Heartpro[$k]['view'] = array(
                    'Services'=>'heartpro.merchantsmanagementview',
                    'param'=>array(
                        'heartpro_id'=>$v['heartpro_id'],
                    ),
                );

                $Db_Heartpro[$k]['update'] = array(
                    'Services'=>'heartpro.beforeupdate',
                    'param'=>array(
                        'heartpro_id'=>$v['heartpro_id'],
                    ),
                );

                $Db_Heartpro[$k]['del'] = array(
                    'Services'=>'heartpro.del',
                    'param'=>array(
                        'heartpro_id'=>$v['heartpro_id'],
                    ),
                );

                $Db_Heartpro[$k]['top'] = array(
                    'Services'=>'payment.infotop',
                    'param'=>array(
                        'heartpro_id'=>$v['heartpro_id'],
                        'good_table'=>$this->tablename,
                    ),
                );


                unset($Db_Heartpro[$k]['level3']);
            }


            $tablewhere = $this->prefix . $this->tablename;

            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);

            $jsondata['page'] = $temp_Common['page'];
            $jsondata['pagesize'] = $temp_Common['pagesize'];
            $jsondata['totalrecord'] = $temp_Common['totalrecord'];
            $jsondata['totalpage'] = $temp_Common['totalpage'];
            $jsondata['list'] = $Db_Heartpro;

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "个人中心： 商家 {$this->tablenamestr}管理列表");


    }
//////////////////////  个人中心：商家 /////////////////////////////////////

//////////////////////  个人中心：代理商    //////////////////////////////////////

    /**
     * @author csh
     *  代理商：1分购  管理列表
     */

    public function agentmanagemore()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('b_display','usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj=new Common();
        $UserObj=new User();
        $ShopObj= new Shop();
        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$UserObj->isHasAgentPrivilege($user_id) ){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '你还未申请代理商角色！');
        }


        $api_keyword = Buddha_Http_Input::getParameter('api_keyword')?Buddha_Http_Input::getParameter('api_keyword'):'';

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        $view = Buddha_Http_Input::getParameter('view')?Buddha_Http_Input::getParameter('view'):0;


        $where = " level3='{$Db_User['level3']}' ";


        if(Buddha_Atom_String::isValidString($api_keyword)){

            $where.= Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($api_keyword,array('name','number'));

        }

        if($view){
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
                case 5;

                    $where.=" and isdel=0 and is_sure=1 and buddhastatus=1";
                    break;
            }
        }
        $fileds = 'id AS heartpro_id,name, is_sure, shop_id, name, buddhastatus, keywords, level3, price, level3 ';


        if($b_display==1){
            $fileds.=' , medium AS img ';
        }elseif($b_display==2){
            $fileds.=' , small AS  img ';
        }

        $orderby = " ORDER BY createtime DESC ";


        $sql =" SELECT  {$fileds}  
                FROM {$this->prefix}{$this->tablename} WHERE {$where} 
                {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        $Db_Activity = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata = array();
        $jsondata['nav'] = $ShopObj->getPersonalCenterHeaderMenu("{$this->tablename}.agentmanagemore");
        $jsondata['page'] =  0;
        $jsondata['pagesize'] =  0;
        $jsondata['totalrecord'] =  0;
        $jsondata['totalpage'] =  0;
        if(Buddha_Atom_Array::isValidArray($Db_Activity)){


            foreach($Db_Activity as $k=>$v){

                if($v['shop_id']!=0){
                    $shop_name= $ShopObj->getSingleFiledValues(array('name'),"id='{$v['shop_id']}' and user_id='{$v['user_id']}'");
                    $name='商家：'.$shop_name['name'];
                }else{
                    $shop_name= $UserObj->getSingleFiledValues(array('name'),"id='{$v['user_id']}'");
                    $name='个人：'.$shop_name['name'];
                }



                if($v['is_sure']==0){

                    $Db_Activity[$k]['api_auditstateimg']=$host.'apistate/menuplus/weishenhe.png';

                    /*活动：审核状态（只有未审核的活动才显示）*/
                    $Db_Activity[$k]['issureServices']=array(
                        'Services' => 'heartpro.beforeverify',
                        'param'=> array('heartpro_id'=>$v['heartpro_id'])
                    );

                }elseif($v['is_sure']==4){

                    $Db_Activity[$k]['api_auditstateimg']=$host.'apistate/menuplus/weitonguo.png';

                }elseif($v['is_sure']==1){

                    $Db_Activity[$k]['api_auditstateimg']=$host.'apistate/menuplus/yitonguo.png';


                    /*单页信息：上下架（只有正常的单页信息才显示）*/
                    $Db_Activity[$k]['buddhastatusServices']=array(
                        'Services' => 'heartpro.offshelf',
                        'param'=> array('shelf'=>$v['buddhastatus'],'heartpro_id'=>$v['heartpro_id'])
                    );

                    if($v['buddhastatus']==1){

                        $Db_Activity[$k]['api_buddhastatus']='上 架';

                    }else if($v['buddhastatus']==0){

                        $Db_Activity[$k]['api_buddhastatus']='下 架';
                    }
                }

                if(Buddha_Atom_String::isValidString($v['img'])){

                    $Db_Activity[$k]['api_img'] = $host.$v['img'];

                }else{

                    $Db_Activity[$k]['api_img']='';
                }


//                if($v['state']==1){
//
//                    $Db_Singleinformation[$k]['api_state']='停 用';
//
//                }else if($v['state']==0){
//
//                    $Db_Singleinformation[$k]['api_state']='启 用';
//
//                }



                $Db_Activity[$k]['shop_name']=$ShopObj->getShopnameFromShopid($v['shop_id']);


                unset( $Db_Activity[$k]['img']);
                unset( $Db_Activity[$k]['level3']);

                $Db_Activity[$k]['view']=array(
                    "Services"=> "{$this->tablename}.verify",
                    "param"=>array('heartpro_id'=>$v['heartpro_id']),
                );
            }


            $tablewhere=$this->prefix.$this->tablename;

            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);


            $jsondata['page'] =  $temp_Common['page'];
            $jsondata['pagesize'] =  $temp_Common['pagesize'];
            $jsondata['totalrecord'] =  $temp_Common['totalrecord'];
            $jsondata['totalpage'] =  $temp_Common['totalpage'];
            $jsondata['list'] = $Db_Activity;

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "代理商：{$this->tablenamestr}管理列表");

    }



    /**
     * 代理商： 1分购 审核之前必须请求详情页面
     */

    public function beforeverify()
    {
        $host= Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('heartpro_id','usertoken','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $ShopObj = new Shop();
        $CommonObj = new Common();
        $RegionObj = new Region();

        $heartpro_id =  (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;
        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','banlance','level3','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $level3 = $Db_User['level3'];

        if(!$UserObj->isHasAgentPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }

        if(!$CommonObj->isOwnerBelongToAgentByLeve3($this->tablename,$heartpro_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }

        if(!$CommonObj->isIssureByTableid($heartpro_id,$this->tablename)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 50000002	, '已经审核过了，请不要重复审核！');
        }


        $b_display =(int) Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        $shop_id =(int) Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;


        $fields='id as heartpro_id,shop_id,user_id,shop_id,table_name,table_id,unit_id,price,stock,votecount,level1,level2,level3,is_remote,applystarttimestr,applyendtimestr,onshelftimestr,offshelftimestr,keywords,name,details,is_sure';
        if($b_display==1){

            $fields.=' , medium AS img ';

        }elseif($b_display==2){

            $fields.=' , small AS img ';
        }

        $where=" id ='{$heartpro_id}' ";

        if($shop_id>0){

            $where.=" AND shop_id='{$shop_id}' ";
        }

        $sql =" SELECT {$fields} FROM {$this->prefix}{$this->tablename}  WHERE {$where} ";


        $Db_tablename_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata=array();
        if(Buddha_Atom_Array::isValidArray($Db_tablename_arr)){

            $Db_Activity = $Db_tablename_arr[0];
            if(Buddha_Atom_String::isValidString($Db_Activity['img'])){
                $Db_Activity['img'] = $host.$Db_Activity['img'];
            }else{
                $Db_Activity['img'] = '';
            }

            $Db_Activity['desc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Activity['desc']);
            $Db_Activity['shop_name']=$ShopObj->getShopnameFromShopid($Db_Activity['shop_id']);
            $Db_Activity['desc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Activity['desc']);


            $Db_Table = $this->db->getSingleFiledValues(array('goods_name'),$Db_Activity['table_name'],"id='{$Db_Activity['table_id']}' and user_id='{$Db_Activity['user_id']}'");
            $Db_Activity['supply_name'] = $Db_Table['goods_name'];
            $Db_Table = $this->db->getSingleFiledValues(array('unit'),'supplycat',"id='{$Db_Activity['unit_id']}'");
            $Db_Activity['unit_name'] = $Db_Table['unit'];

            if($Db_Activity['is_remote']==1){
                $Db_Region=$RegionObj->getAllArrayAddressByLever($Db_Activity['level3']);
                $region='';
                foreach($Db_Region as $k=>$v){
                    if($k!=0)
                        $region.=$v['name'].' > ';
                }
                $Db_Activity['region']=Buddha_Atom_String::toDeleteTailCharacter($region);
            }
            unset( $Db_Activity['user_id']);

            /*审核*/
            $Db_Activity['issureServices']=array(
                'Services' => $this->tablename.'.verify',
                'param'=> array('is_sure'=>$Db_Activity['is_sure'],'heartpro_id'=>$Db_Activity['heartpro_id'])
            );

            $jsondata = $Db_Activity;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "代理商进行{$this->tablenamestr}之前必须请求的详情页面");

    }



    /**
     * 个人中心： 代理商  1分购  审核
     */
    public function verify(){

        if (Buddha_Http_Input::checkParameter(array('heartpro_id','usertoken','is_sure'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $HeartproObj = new Heartpro();
        $CommonObj = new Common();

        $heartpro_id =  (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;
        /*审核状态：1通过审核  ；4未通过审核*/
        $is_sure = (int) Buddha_Http_Input::getParameter('is_sure')?(int) Buddha_Http_Input::getParameter('is_sure'):0;

        /*判断$is_sure审核状态码 是否属于 1,4*/
        if(!$CommonObj->isIdInDataEffectiveById($is_sure,array(1,4)))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }

        $remarks = Buddha_Http_Input::getParameter('remarks')? Buddha_Http_Input::getParameter('remarks'):'';
        /*4未通过审核 必须填写备注*/
        if($is_sure==4 AND !Buddha_Atom_String::isValidString($remarks))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }



        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','banlance','level3',
            'username'
        );
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $level3 = $Db_User['level3'];

        if($UserObj->isHasAgentPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }

        if(!$CommonObj->isOwnerBelongToAgentByLeve3($this->tablename,$heartpro_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }


        $data = array();
        $data['is_sure'] =$is_sure ;
        $data['remarks'] =$remarks ;
        $Db_Heartpro_num = $HeartproObj->edit($data,$heartpro_id);


        $jsondata = array();
        $datas=array();
        if($Db_Heartpro_num){
            $datas['is_ok']=1;
            $datas['is_msg']=$this->tablenamestr.'审核成功！';
        }else{
            $datas['is_ok']=0;
            $datas['is_msg']=$this->tablenamestr.'审核失败！';
        }

        $jsondata['heartpro_id'] = $heartpro_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "代理商{$this->tablenamestr}审核");

    }

    /**
     * 代理商：单页信息上下架状态
     */

    public function offshelf(){

        if (Buddha_Http_Input::checkParameter(array('heartpro_id','usertoken','shelf'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $CommonObj = new Common();

        $heartpro_id =  (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;

        /*默认下架  0下架 1=上架*/
        $shelf = (int)Buddha_Http_Input::getParameter('shelf') ? (int)Buddha_Http_Input::getParameter('shelf') : 0;
        if(!$CommonObj->isIdInDataEffectiveById($shelf)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }

        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','banlance','level3',
            'username'
        );
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $level3 = $Db_User['level3'];

        if($UserObj->isHasAgentPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }

        if(!$CommonObj->isOwnerBelongToAgentByLeve3($this->tablename,$heartpro_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }

        $data = array();
        $msg="";
        if($shelf==0){
            $data['buddhastatus'] =1 ;
            $msg="下架";
        }else{
            $data['buddhastatus'] =0 ;
            $msg="上架";
        }

        $Db_Heartpro_num = $this->db->updateRecords( $data, $this->tablename,"id ='{$heartpro_id}'" );


        $jsondata = array();
        $jsondata['heartpro_id'] = $heartpro_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['msg'] = $msg;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.$msg);


    }

//////////////////////  个人中心：代理商  //////////////////////////////////////




//////////////////////  首页  //////////////////////////////////////


    /**
     *  首页：列表
    */
    public function more()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('api_number','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        /*说明： 如果存在usertoken 表示是从个人中心请求数据：即只请求自己名下的所有单页信息*/

        $usertoken =  Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):'';


        if(Buddha_Atom_String::isValidString($usertoken)){
            $UserObj=new User();
            $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
            $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
            $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
            $user_id = $Db_User['id'];
        }

        /*如果存在usertoken表示是从个人中心请求数据(要加入对应的条件和显示相应的字段)  则不需要*/

        $RegionObj = new Region();
        $CommonObj = new Common();
        $ShopObj = new Shop();

        /*城市编号*/
        $api_number = (int)Buddha_Http_Input::getParameter('api_number');
        $b_display = (int) Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;
        $shop_id = (int) Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;

        $api_keyword = Buddha_Http_Input::getParameter('api_keyword');
        $page = (int)Buddha_Http_Input::getParameter('page')?(int)Buddha_Http_Input::getParameter('page'):1;

        $view = Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;

        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):15;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

//        /*是否按照附近显示*/
//        $api_isnearby = Buddha_Http_Input::getParameter('api_isnearby')?Buddha_Http_Input::getParameter('api_isnearby'):0;
//
//        /* 按照附近显示的距离默认为1km(如果api_isnearby==1时)*/
//        $api_nearbydistance = Buddha_Http_Input::getParameter('api_nearbydistance')?Buddha_Http_Input::getParameter('api_nearbydistance'):1;
//        $lats = (int)Buddha_Http_Input::getParameter('lat')?Buddha_Http_Input::getParameter('lat'):0;
//        $lngs = (int)Buddha_Http_Input::getParameter('lng')?Buddha_Http_Input::getParameter('lng'):0;


        $fields = array('id as heartpro_id', 'shop_id','user_id', 'name','price');


        if($b_display == 1){
            array_push($fields,'medium AS img');
        }elseif($b_display == 2){
            array_push($fields,'small AS img');

        }

        $where=' isdel=0 AND buddhastatus=0 AND is_sure=1';
        /*在条件中加入地区*/
        $where .= $RegionObj->whereJoinRegion($api_number);

        /**传过来店铺ID，就查询该店铺下的*/
        if($shop_id){
            $where.=" AND shop_id='{$shop_id}' ";
        }

        $orderby = " ORDER BY createtime DESC ";

        if ($view) {
            switch ($view) {

                case 2;
                    //  $where .= ' and is_sure=0';
                    $orderby = " ORDER BY createtime DESC";
                    break;
                case 3;
                    $orderby = " ORDER BY click_count DESC";
                    break;
                case 4;
                    $orderby = " group by shop_id order by createtime ASC";
                    break;
            }
        }
// 屏蔽原因：单页信息列表中没有经纬度 无法加入附近显示
//        if($api_isnearby==1){
//            /*在条件中加入附近显示*/
//            $where.= $RegionObj->whereJoinNearby($api_nearbydistance,$lats,$lngs,$api_number);
//        }


        $where .= Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($api_keyword,'name');

        if(Buddha_Atom_String::isValidString($usertoken)){
            $where.=" AND user_id='{$user_id}' ";
        }

//------------------------
        /*先查询：当地有没有过期了但没有下架的1分购：有就下架*/
        $locdata = $RegionObj->getLocationDataFromCookie();
        $CommonObj->UpdateShelvesStatus($this->tablename,'onshelftime','offshelftime',$locdata['sql']);
//---------------------------

       $Db_Table = $this->db->getFiledValues ($fields,  $this->prefix.$this->tablename, $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

        $jsondata = array();
        $jsondata['list'] = array();
        $jsondata['page'] = 0;
        $jsondata['pagesize'] = 0;
        $jsondata['totalrecord'] = 0;
        $jsondata['totalpage'] = 0;

        if(Buddha_Atom_Array::isValidArray($Db_Table))
        {
            foreach ($Db_Table as $k=>$v)
            {
                $Db_Table[$k]['img'] = $host.$v['img'];

//////查询店铺信息区域//////////////////////////////////////////////
                if(Buddha_Atom_String::isValidString($v['shop_id']))
                {
                    $Db_shop = $ShopObj->getSingleFiledValues(array('name','specticloc','lng','lat'),"id='{$v['shop_id']}'");

                    $Db_Table[$k]['shop_name']  = $Db_shop['name'];

                    if($Db_shop['roadfullname']=='0')
                    {
                        $Db_Table[$k]['roadfullname'] = '';
                    }else{
                        $Db_Table[$k]['roadfullname'] = $Db_shop['specticloc'];
                    }
                }else{

                    $Db_user = $UserObj->getSingleFiledValues(array('username','realname','address'),"id='{$v['user_id']}'");

                    if(!Buddha_Atom_String::isValidString($Db_user['address']))
                    {
                        $Db_Table[$k]['roadfullname'] = '' ;
                    }else{
                        $Db_Table[$k]['roadfullname'] = $Db_user['address'];
                    }

                    if(!Buddha_Atom_String::isValidString($Db_user['realname']))
                    {
                        $Db_Table[$k]['username'] = $Db_user['username'];
                    }else
                    {
                        $Db_Table[$k]['username'] = $Db_user['realname'];
                    }
                }
                unset($Db_Table[$k]['user_id']);
                ///////查询店铺信息区域//////////////////////////////////////////////////

                $Db_Table[$k]['services'] = $this->tablename.'.view';
                $Db_Table[$k]['param'] = array($this->tablename.'_id'=>$v[$this->tablename.'_id']);

            }

            $tablewhere = $this->prefix . $this->tablename;
            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);
            $jsondata['page'] = $temp_Common['page'];
            $jsondata['pagesize'] = $temp_Common['pagesize'];
            $jsondata['totalrecord'] = $temp_Common['totalrecord'];
            $jsondata['totalpage'] = $temp_Common['totalpage'];
            $jsondata['list'] = $Db_Table;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'列表');
    }



    /**
     *  首页：1分购 详情
     */
    public function view()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('heartpro_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $SupplyObj = new Supply();          //产品表
        $ShopObj = new Shop();              //
        $CommonObj = new Common();              //
        $SupplycatObj = new Supplycat();              //
        $HeartproObj = new Heartpro();      //1分购
        $HeartplusObj = new Heartplus();    //1分购申请人表对应 投票者表
        $HeartapplyObj = new Heartapply();  //1分购申请人表

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):'';
        $heartpro_id = (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?(int)Buddha_Http_Input::getParameter('b_display'):2;
        if(Buddha_Atom_String::isValidString($usertoken)){
            $UserObj = new User();
            $usertoken = Buddha_Http_Input::getParameter('usertoken');
            $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
            $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
            $user_id = $Db_User['id'];
        }



        $HeartproFiled = array('id as heartpro_id','name','keywords','click_count','table_id','table_name','number','name','shop_id','details'
                                ,'onshelftime','onshelftimestr','offshelftime','offshelftimestr','applystarttime','applystarttimestr'
                                ,'applyendtime','applyendtimestr','is_remote','level1','level2','level3','createtime'
                                ,'createtimestr','shop_id','price','unit_id','stock','votecount');

        if($b_display==2)
        {
            array_push($HeartproFiled,'small as img');
        }else  if($b_display==1)
        {
            array_push($HeartproFiled,'medium as img');
        }

        $Db_Heartpro = $HeartproObj->getSingleFiledValues($HeartproFiled,"id='{$heartpro_id}' and buddhastatus=0");//查询 1分购 数据

        $jsondata = array();

        if(Buddha_Atom_Array::isValidArray($Db_Heartpro))
        {

            $Db_Heartpro['img'] = $host.$Db_Heartpro['img'];
            $newtime = Buddha::$buddha_array['buddha_timestamp'];

            $Db_Heartpro['desc'] = Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Heartpro['desc']);
            $Db_Heartpro['header_title'] = $this->tablenamestr;

            /**↓↓↓↓↓↓↓↓↓↓↓ 单位 ↓↓↓↓↓↓↓↓↓↓↓**/
            $Db_Heartpro['unit_name'] = $SupplycatObj->getSupplycatnameBySupplycatid($Db_Heartpro['unit_id']);
            /**↑↑↑↑↑↑↑↑↑↑ 单位 ↑↑↑↑↑↑↑↑↑↑**/


            /**↓↓↓↓↓↓↓↓↓↓↓ 统计申请 1分购申请人表 的数量 ↓↓↓↓↓↓↓↓↓↓↓**/
            $Heartapplywhere=' heartpro_id='.$heartpro_id;
            $Db_Heartapply_num = $HeartapplyObj->countRecords($Heartapplywhere);
            $Db_Heartpro['heartapply_num'] = $Db_Heartapply_num;
            /**↑↑↑↑↑↑↑↑↑↑ 统计申请 1分购申请人表 的数量 ↑↑↑↑↑↑↑↑↑↑**/



            /**↓↓↓↓↓↓↓↓↓↓↓ 求和：投票的总数 ↓↓↓↓↓↓↓↓↓↓↓**/
            $Heartpluswhere = ' heartpro_id='.$heartpro_id;
            $sql = "SELECT SUM(vote_num) as num FROM {$this->prefix}heartapply WHERE {$Heartpluswhere} ";
            $praise_num = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $Db_Heartpro['vote_sum'] = $praise_num[0]['num'];
            /**↑↑↑↑↑↑↑↑↑↑ 求和：投票的总数 ↑↑↑↑↑↑↑↑↑↑**/


            /**↓↓↓↓↓↓↓↓↓↓↓ 产品 ↓↓↓↓↓↓↓↓↓↓↓**/
            $Db_Supply = $SupplyObj->getSingleFiledValues(array('id as supply_id','market_price','goods_name as supply_name')," id='{$Db_Heartpro['table_id']}'");
            $Db_Heartpro['supply_id'] = $Db_Supply['supply_id'];
            $Db_Heartpro['supply_name'] = $Db_Supply['supply_name'];
            $Db_Heartpro['original_price'] = $Db_Supply['market_price'];
            /**↑↑↑↑↑↑↑↑↑↑ 产品 ↑↑↑↑↑↑↑↑↑↑**/


            /**↓↓↓↓↓↓↓↓↓↓↓ 店铺详情接口 ↓↓↓↓↓↓↓↓↓↓↓**/
            $shopinfo = $ShopObj->getSingleFiledValues(array('id as shop_id','user_id','small','name','is_verify','specticloc','lat','lng','mobile','createtimestr','level2','level3','is_verify','user_id'),"id='{$Db_Heartpro['shop_id']}' and isdel=0");
            $shopinfo['small'] = $host . $shopinfo['small'];
            $Db_Heartpro['shop_name'] = $shopinfo['name'];
            $Db_Heartpro['shop_img'] =  $shopinfo['small'];

            $jsondata = $Db_Heartpro;

            $shopinfo['services'] = "shop.view";
            $shopinfo['param'] = array('shop_id'=>"'{$shopinfo['id']}'");
            /**↑↑↑↑↑↑↑↑↑↑ 店铺详情接口 ↑↑↑↑↑↑↑↑↑↑**/



            /**↓↓↓↓↓↓头部轮播图 查询↓↓↓↓↓↓**/
            $MoregalleryObj = new Moregallery();
            $More = $MoregalleryObj->getFiledValues(array('id','goods_img'),"goods_id={$heartpro_id} and tablename='{$this->tablename}' and webfield='file'");
            $jsondata['bannerimg'] = $More;
            /**↑↑↑↑↑↑↑↑↑↑头部轮播图 查询 ↑↑↑↑↑↑↑↑↑↑**/



            /**↓↓↓↓↓↓↓↓↓↓↓ 更新点击量 ↓↓↓↓↓↓↓↓↓↓↓**/
            $clickdata['click_count'] = $Db_Heartpro['click_count']+1;
            $HeartproObj->edit($clickdata,$heartpro_id);
            /**↑↑↑↑↑↑↑↑↑↑ 更新点击量 ↑↑↑↑↑↑↑↑↑↑**/



            /**↓↓↓↓↓↓↓↓↓↓↓ 是否过期：过期就要下架 ↓↓↓↓↓↓↓↓↓↓↓**/
            if(!($Db_Heartpro['onshelftime']<$newtime AND $newtime<$Db_Heartpro['offshelftime'])){
                $data['buddhastatus'] = 0;
                $HeartproObj->edit($data,$heartpro_id);
            }
            /**↑↑↑↑↑↑↑↑↑↑ 是否过期：过期就要下架 ↑↑↑↑↑↑↑↑↑↑**/


            /**↓↓↓↓↓↓↓↓↓↓↓ 分享 ↓↓↓↓↓↓↓↓↓↓↓**/
            if($Db_Heartpro['keywords'])
            {
                if(mb_strlen($Db_Heartpro['keywords']) > 20)
                {
                    $share_desc = "你的朋友{$Db_User['realname']} @你,他正在参与{$Db_Heartpro['name']} 1分购活动，他邀请你你给他投票!".mb_substr(strip_tags($Db_Heartpro['keywords']) ,0,20) . '...';
                }else{
                    $share_desc = "你的朋友{$Db_User['realname']} @你,他正在参与{$Db_Heartpro['name']} 1分购活动，他邀请你你给他投票!".$Db_Heartpro['keywords'];
                }
            }else{
                $share_desc = "快速发布您的{$this->tablenamestr}，快速解决您的问题，万人同时在线，为您排忧解难";
            }
            if(Buddha_Atom_String::isValidString($Db_Heartpro['img']))
            {
                $share_imgUrl = $Db_Heartpro['img'];
            }else{
                $share_imgUrl = '';
            }
            $sharearr = array(
                'share_title'=>$Db_Heartpro['name'],
                'share_desc'=>$share_desc,
                'share_link'=> Buddha_Atom_Share::getShareUrl('heartpro.detail',$heartpro_id),
                'share_imgUrl'=> $share_imgUrl,
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
                    'param' => array('shop_id'=>$Db_Heartpro['shop_id']),
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
                'param' => array('shop_id'=>$Db_Heartpro['shop_id']),
            );
            /**↑↑↑↑↑↑↑↑↑↑ 是否显示电话号码 ↑↑↑↑↑↑↑↑↑↑**/

            /**↓↓↓↓↓↓↓↓↓↓↓ 按钮 ↓↓↓↓↓↓↓↓↓↓↓**/
            $url = array(
                'prize'=>'index.php?a=vodeprize&c='.$this->tablename.'&id='.$heartpro_id,
                'ranking'=>'index.php?a=voderanking&c='.$this->tablename.'&id='.$heartpro_id,
            );
            $jsondata['button_url'] = $url;
            /**↑↑↑↑↑↑↑↑↑↑ 按钮 ↑↑↑↑↑↑↑↑↑↑**/




            /**↓↓↓↓↓↓↓↓↓↓↓↓ 随机显示20款产品 ↓↓↓↓↓↓↓↓↓↓↓↓**/

            $random_supplyfiled = array('id as supply_id','goods_name','is_promote','market_price','promote_price','goods_brief');

            if($b_display==2)
            {
                array_push($random_supplyfiled,'goods_thumb as img');
            }elseif ($b_display==1){
                array_push($random_supplyfiled,'goods_img as img');
            }

            $Db_Supply_random_id = $SupplyObj->getFiledValues(array('id')," shop_id='{$Db_Heartpro['shop_id']}'");

            $random_id_arr = array();
            foreach ($Db_Supply_random_id as  $k=>$v){
                $random_id_arr[]=$v['id'];
            }

            if(sizeof($random_id_arr)>20)
            {
                $number = 20;
            }else{
                $number = sizeof($random_id_arr);
            }

            $random_keys = array_rand($random_id_arr,$number);


            $random_id_str = '';
            if(sizeof($random_id_arr)>20)
            {
                foreach ($random_keys as $k=>$v)
                {
                    $random_id_str .= $random_id_arr[$v].',';

                }

                $random_id = trim($random_id_str,',');
            }else{

                foreach ($Db_Supply_random_id as $k=>$v)
                {
                    $random_id_str .= $v['id'].',';
                }
                $random_id = trim($random_id_str,',');
            }

            $Db_Supply_random = $SupplyObj->getFiledValues($random_supplyfiled," id IN ({$random_id})");

            foreach ($Db_Supply_random as $k=>$v)
            {
                if($v['is_promote']==1)
                {
                    $Db_Supply_random[$k]['price'] = '¥ '.$v['promote_price'];
                }else{
                    $Db_Supply_random[$k]['price'] = '¥ '.$v['market_price'];
                }
                $Db_Supply_random[$k]['img'] = $host.$v['img'];
                $Db_Supply_random[$k]['goods_brief'] = $CommonObj->intercept_strlen($v['goods_brief'],'10');
                unset( $Db_Supply_random[$k]['promote_price']);
                unset( $Db_Supply_random[$k]['market_price']);
                unset( $Db_Supply_random[$k]['is_promote']);
            }
            $jsondata['supply_random'] = $Db_Supply_random;
            /**↑↑↑↑↑↑↑↑↑↑↑↑ 随机显示20款产品 ↑↑↑↑↑↑↑↑↑↑**/

        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'详情');

    }



    /**
     *  首页：1分购 规则详情
     */
    public function vodeprize()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('heartpro_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $HeartproObj=new Heartpro();
        $CommonObj = new Common();
        $heartpro_id = Buddha_Http_Input::getParameter('heartpro_id')?Buddha_Http_Input::getParameter('heartpro_id'):0;

        $Db_Heartpro = $HeartproObj->getSingleFiledValues(array('name','details','small','votecount','stock'),"id='{$heartpro_id}' AND buddhastatus=0");

        $jsondata = array();
        if(Buddha_Atom_Array::isValidArray($Db_Heartpro))
        {

            $Db_Heartpro['desc'] = Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Heartpro['desc']);

            $Db_Heartpro['desc'] = $Db_Heartpro['desc'].'  最少投票数量：'.$Db_Heartpro['votecount'].';   库存量：'.$Db_Heartpro['stock'];
            $getQECodeImg = $CommonObj->getQRCode('heartpro','vodeprize',$heartpro_id,$Db_Heartpro['small']);
            $Db_Heartpro['codeimg'] =$host.$getQECodeImg;
            $jsondata = $Db_Heartpro;
        }



        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'详情和奖品设置');

    }


    /**
     * 1分购 报名者列表
     */
    public function applicantlist()
    {
        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('heartpro_id','title'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj = New Common();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):'';
        if(Buddha_Atom_String::isValidString($usertoken)){
            $UserObj = new User();
            $usertoken = Buddha_Http_Input::getParameter('usertoken');
            $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
            $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
            $current_uid = $Db_User['id'];//当前用户ID
        }

        $title = Buddha_Http_Input::getParameter('title')?Buddha_Http_Input::getParameter('title'):2;//2人气、3最新
        $heartpro_id = (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;// 1分购 ID
        $heartapply_id = (int)Buddha_Http_Input::getParameter('heartapply_id')?(int)Buddha_Http_Input::getParameter('heartapply_id'):0;// 1分购申请人表ID
        $search = Buddha_Http_Input::getParameter('search')?Buddha_Http_Input::getParameter('search'):'';

        $page = (int)Buddha_Http_Input::getParameter('page')?(int)Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);
        $is_log = 0;
        $vote_num = 0;
        $HeartapplyObj = new Heartapply();

        $limit = Buddha_Tool_Page::sqlLimit ($page, $pagesize);
        $where = ' a.heartpro_id='.$heartpro_id;

        if(Buddha_Atom_String::isValidString($search))
        {
            $where .= " and u.realname like '%{$search}%' or a.number like '%{$search}%'";
        }

        if(Buddha_Atom_String::isValidString($heartapply_id))
        {
            $where .=" a.user_id='{$heartapply_id}'";
        }

        /**查询当前用户的投票数量和是否已经购买过了**/
        if(Buddha_Atom_String::isValidString($current_uid))
        {
            $votewhere = " heartpro_id='{$heartpro_id}' AND user_id='{$current_uid}'";

            $Db_Heartapply = $HeartapplyObj->getSingleFiledValues(array('vote_num','is_buy'),$votewhere);
            $is_log = 1;
            $vote_num = $Db_Heartapply['vote_num'];
        }

        /**在 heartapply 表中要显示的字段有***/
        $filed = "a.id as heartapply_id,a.user_id,a.vote_num,a.number,a.is_buy";

        if($title==2)
        {//2人气、3最新、4 我参与
            $orderby = ' order by a.vote_num desc';
        }elseif($title==3)
        {//2人气、3最新、4 我参与
            $orderby = ' order by a.createtime desc';
        }
//        elseif($title==4)
//        {//2人气、3最新、4 我参与
//            $uid = Buddha_Http_Cookie::getCookie('uid');
//            if(empty($uid))
//            {
//                Buddha_Http_Head::redirectofmobile('还未登录！',"index.php?a=login&c=account",2);
//                exit;
//            }else{
//                $where .=" user_id='{$uid}'";
//            }
//
//            $orderby = ' order by a.createtime desc';
//        }

        $filed .= ',u.logo,u.realname ';
        $table = 'user';
        $as_f = 'u';

        $sql = "select {$filed}
                from {$this->prefix}heartapply as a 
                INNER join {$this->prefix}{$table} as {$as_f} 
                on {$as_f}.id = a.user_id  
                where {$where} {$orderby} {$limit}";

        $Db_heartapply = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata = array();
        $jsondata['current'] = 0;
        $jsondata['is_log'] = 0;
        $jsondata['is_buy'] = 0;
        $jsondata['list']=array();
        $tablewhere = $this->prefix . 'heartapply';

        $temp_Common = $CommonObj->pagination($tablewhere, "id='{$heartpro_id}'", $pagesize, $page);

        $jsondata['page'] = $temp_Common['page'];
        $jsondata['pagesize'] = $temp_Common['pagesize'];
        $jsondata['totalrecord'] = $temp_Common['totalrecord'];
        $jsondata['totalpage'] = $temp_Common['totalpage'];




        if(Buddha_Atom_Array::isValidArray($Db_heartapply))
        {

            foreach($Db_heartapply as $k=>$v)
            {
                if(Buddha_Atom_String::isValidString($v['logo']))
                {
                    $Db_heartapply[$k]['logo'] = $host.$v['logo'];
                }else{
                    $Db_heartapply[$k]['logo'] = $host.'style/images/im.png';
                }

                if($v['is_buy']==1)
                {
                    $Db_heartapply[$k]['icon_buy'] = $host.'style/img_two/successfulbidding.png';
                }else{
                    $Db_heartapply[$k]['icon_buy'] = '';
                }
            }

            $jsondata['current'] = $vote_num;
            $jsondata['is_log'] = $is_log;//是否登录：0否；1是
            $jsondata['list'] = $Db_heartapply;

        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'报名者列表');
    }



    /**
     * 1分购：投票
     * */
    public function vote()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken','heartpro_id','heartapply_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $HeartproObj = new Heartpro();//1分购
        $HeartapplyObj = new Heartapply();//1分购申请人表
        $HeartplusObj = new Heartplus();//1分购申请人表对应投票者表
        $CommonObj = new Common();

        $heartpro_id = (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;// 1分购 ID
        $heartapply_id = (int)Buddha_Http_Input::getParameter('heartapply_id')?(int)Buddha_Http_Input::getParameter('heartapply_id'):0;//  申请表内码  ID
        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):'';

        $UserObj = new User();
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];////当前投票人的ID

        $Db_Heartpro = $HeartproObj->getSingleFiledValues(array('applystarttime','applyendtime')," id='{$heartpro_id}'");

        $jsondata = array();
        $newtime = Buddha::$buddha_array['buddha_timestamp'];
        $jsondata['vote_num'] = 0;
        $jsondata['heartapply_id'] = $heartapply_id;

        if(Buddha_Atom_Array::isValidArray($Db_Heartpro))
        {

            if($Db_Heartpro['applystarttime'] > $newtime)
            {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000065, '竞买时间还未开始，不能投票');
            }elseif($newtime>$Db_Heartpro['applyendtime'])
            {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000065, '竞买时间结束，不能投票');
            }


            if(!Buddha_Atom_String::isValidString($user_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000043, '请登录后再投票(如果没有帐号请注册！)');
            }


            $time = $CommonObj->time_handle('createtime');

            $where = $time['where'];//昨天的0点<当前时间<明天的0点时间

//            $Heartpluswhere = $where." and heartpro_id ={$heartpro_id} and user_id={$uid} and heartapply_id={$heartapply_id}";
            $Heartpluswhere = $where." and heartpro_id ={$heartpro_id} and user_id={$user_id}";


            /***查询用户是否已经存在投票时间*/
            if($HeartplusObj->countRecords($Heartpluswhere))
            {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000066, '你今天已经投过票了，请明天再来吧！)');
            }

            /**↓↓↓↓↓↓↓↓↓↓↓ 更新投票次数 ↓↓↓↓↓↓↓↓↓↓↓**/
            $Db_Heartapply = $HeartapplyObj->getSingleFiledValues(array('id','vote_num')," heartpro_id ={$heartpro_id} and id={$heartapply_id}");//查询1分购申请人的投票次数
            $Db_Heartappl_vote_num = $Db_Heartapply['vote_num']+1;//投票次数加一
            $Heartplus_num = $HeartplusObj->countRecords("heartpro_id='{$heartpro_id}'");

            if($Db_Heartappl_vote_num == $Heartplus_num)
            {
                $data_Heartapply['vote_num'] = $Heartplus_num;
            }else{
                $data_Heartapply['vote_num'] = $Db_Heartappl_vote_num;
            }
            $HeartapplyObj->edit($data_Heartapply,$heartapply_id);
            /**↑↑↑↑↑↑↑↑↑↑ 更新投票次数 ↑↑↑↑↑↑↑↑↑↑**/


            $data['user_id'] = $user_id;
            $data['heartpro_id'] = $heartpro_id;//1分购
            $data['heartapply_id'] = $heartapply_id;//1分购申请人表
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $Heartplus_id = $HeartplusObj->add($data);

            if($Heartplus_id)
            {
                $jsondata['db_isok'] = 'true';
                $jsondata['db_msg'] = $this->tablenamestr.'投票成功';
                $jsondata['vote_num'] = $data_Heartapply['vote_num'];
            }else{
                $jsondata['isok'] = 'false';
                $jsondata['data'] = $this->tablenamestr.'投票失败';
            }
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'投票');
    }



    /**
     * 1分购：投票 排名列表
*/
    public function votelist()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('heartpro_id')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $CommonObj = new Common();
        $HeartproObj= new Heartpro();

        $heartpro_id = (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?(int)Buddha_Http_Input::getParameter('b_display'):2;

        $page = (int)Buddha_Http_Input::getParameter('page')?(int)Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);
        $limit = Buddha_Tool_Page::sqlLimit ($page, $pagesize);
//======查询 1分购申请人表 排名===
        $where = ' y.heartpro_id='.$heartpro_id;

        $sql ="select y.id as heartapply_id,y.vote_num,u.realname 
               from {$this->prefix}heartapply as y 
               left join {$this->prefix}user as u 
               on u.id = y.user_id 
               where {$where} 
               order by y.vote_num desc ".$limit;
        $Db_Heartapply = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);



        $filed = array();

        if($b_display==2){
            $filed= array('small as img');
        }elseif ($b_display==1){
            $filed= array('medium as img');
        }

        $Heartpro = $HeartproObj->getSingleFiledValues($filed,"id='{$heartpro_id}'");

        $jsondata = array();

        $jsondata['headerimg'] = $host.$Heartpro['img'];

        $tablewhere = $this->prefix . 'heartapply';

        $temp_Common = $CommonObj->pagination($tablewhere, "id='{$heartpro_id}'", $pagesize, $page);

        $jsondata['page'] = $temp_Common['page'];
        $jsondata['pagesize'] = $temp_Common['pagesize'];
        $jsondata['totalrecord'] = $temp_Common['totalrecord'];
        $jsondata['totalpage'] = $temp_Common['totalpage'];

        if(Buddha_Atom_Array::isValidArray($Db_Heartapply)){
            foreach($Db_Heartapply as $k=>$v)
            {
                $Db_Heartapply[$k]['realname'] = mb_substr($v['realname'],0,15) ;
            }
            $jsondata['list'] = $Db_Heartapply;
        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'投票排名列表');
    }



    /**
     * 1分购：申请 报名(不 包含 姓名、留言、电话)
     */

    public function applicantregistration()
    {

        if (Buddha_Http_Input::checkParameter(array('usertoken','heartpro_id')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $HeartproObj = new Heartpro();
        $HeartapplyObj = new Heartapply();
        $CommonObj = new Common();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):'';
        $UserObj = new User();
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];//当前用户ID


        $heartpro_id = (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;//1分购


        if(!Buddha_Atom_String::isValidString($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000067, '请登录后再竞买(如果没有帐号请注册)！');
        }

        $newtime = Buddha::$buddha_array['buddha_timestamp'];
        $Db_Heartpro_num = $HeartproObj->getSingleFiledValues(array('applystarttime','applyendtime'),"id='{$heartpro_id}'");
//        $Db_Heartapply = $HeartapplyObj->getSingleFiledValues(array('is_buy'),"heartpro_id='{$heartpro_id}' AND user_id='{$user_id}' ");

//        if($Db_Heartpro_num['applystarttime'] > $newtime)
//        {
//            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000068, '报名还未开始，不能报名!');

//        }else
        if($newtime > $Db_Heartpro_num['applyendtime'] )
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000069, '报名已结束，不能报名！');
        }


        /**↓↓↓↓↓↓↓↓↓↓↓ 判断用户是否已经申请了 ↓↓↓↓↓↓↓↓↓↓↓**/
        $Heartapply_count = $HeartapplyObj->countRecords("user_id={$user_id} and heartpro_id={$heartpro_id} ");//
        /**↑↑↑↑↑↑↑↑↑↑ 判断用户是否已经申请了 ↑↑↑↑↑↑↑↑↑↑**/


        if($Heartapply_count)
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000072, '您已经申请过了,请不要重复申请！');
        }

        $data['user_id'] = $user_id;
        $data['heartpro_id'] = $heartpro_id;
        $data['number'] = $CommonObj->GeneratingNumber();
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $Heartapply_id = $HeartapplyObj->add($data);
        $jsondata = array();
        $jsondata['heartapply_id'] = $Heartapply_id;
        if ($Heartapply_id)
        {
            $jsondata['db_isok'] = 'true';
            $jsondata['db_msg'] = $this->tablenamestr.'申请成功！';
            $jsondata['heartapply_id'] = $Heartapply_id;
        } else {
            $jsondata['isok'] = 'false';
            $jsondata['data'] = $this->tablenamestr.'申请失败!';

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'申请报名');


    }



    /**产品购买***/
    public function shopping()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken','heartpro_id','heartapply_id')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $HeartproObj = new Heartpro();//1分购
        $HeartapplyObj = new Heartapply();//1分购申请人表
        $HeartplusObj = new Heartplus();//1分购申请人表对应投票者表
        $CommonObj = new Common();
        $OrderObj=new Order();
        $OrdermerchantObj=new Ordermerchant();


        $heartpro_id = (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;// 1分购 ID
        $heartapply_id = (int)Buddha_Http_Input::getParameter('heartapply_id')?(int)Buddha_Http_Input::getParameter('heartapply_id'):0;//  申请表内码  ID

        $money = Buddha_Http_Input::getParameter('money');
//      $number = Buddha_Http_Input::getParameter('number');
        $number = 1;


        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):'';

        $UserObj = new User();
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username','level0','level1','level2','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];////当前投票人的ID


        /**↓↓↓↓↓↓↓↓↓↓↓ 是否达到付款条件 ↓↓↓↓↓↓↓↓↓↓↓**/
        $merchant_uid = $HeartproObj->getSingleFiledValues(array('user_id','votecount','stock'),"id={$heartpro_id}");
        $Minvotes= $merchant_uid['votecount'];//最少投票数量

        $Db_Heartapply = $HeartapplyObj->getSingleFiledValues(array('vote_num','is_buy'),"heartpro_id='{$heartpro_id}' AND user_id='{$user_id}' ");

        $Currentvotes = $Db_Heartapply['vote_num']; // 当前投票数量

        if(!($Currentvotes >= $Minvotes))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 30000001, '非法操作！');
        }
        /**↑↑↑↑↑↑↑↑↑↑ 是否达到付款条件 ↑↑↑↑↑↑↑↑↑↑**/



        /**↓↓↓↓↓↓↓↓↓↓↓ 检查库存是否正确 ↓↓↓↓↓↓↓↓↓↓↓**/
        if(!Buddha_Atom_String::isValidString($merchant_uid['stock']))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000074, '对不起客官，库存没有了!');
        }
        /**↑↑↑↑↑↑↑↑↑↑ 检查库存是否正确 ↑↑↑↑↑↑↑↑↑↑**/



        /**↓↓↓↓↓↓↓↓↓↓↓ 检查 购买数量 是否正确 ↓↓↓↓↓↓↓↓↓↓↓**/
        if(!Buddha_Atom_String::isValidString($number))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 30000001, '非法操作!');
        }
        /**↑↑↑↑↑↑↑↑↑↑ 检查 购买数量 是否正确  ↑↑↑↑↑↑↑↑↑↑**/



        /**↓↓↓↓↓↓↓↓↓↓↓ 检查 该用户是否已经购买过了  ↓↓↓↓↓↓↓↓↓↓↓**/
        if($Db_Heartapply['is_buy']==1)
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000075, '你已经购买过了，请不用重复购买(一个账户只有一次机会)!!');
        }
        /**↑↑↑↑↑↑↑↑↑↑ 检查 该用户是否已经购买过了  ↑↑↑↑↑↑↑↑↑↑**/



        $data=array();
        $order_sn = $OrderObj->birthOrderId($user_id);//订单编号
        $data['good_id']=$heartpro_id;//指定产品id
        $data['user_id']=$user_id;
        $data['merchant_uid'] = $merchant_uid['user_id'];
        $data['order_sn']= $order_sn;
        $data['good_table']=$this->tablename;//哪个表
        $data['pay_type']='third';//third第三方支付，point积分，balance余额
        $data['order_type']='heartpro';//money.out提现, 店铺认证shop.v,信息置顶info.top ,跨区域信息推广info.market,信息查看info.see,shopping购物,heartpro1分购
        $data['goods_amt'] = $money * $number;//产品价格
        $data['final_amt'] = $money * $number;//产品最终价格
        $data['order_total'] = $number;//件数
        $data['payname'] = '微信支付';
        $data['make_level0']=$Db_User['level0'];//国家
        $data['make_level1']=$Db_User['level1'];//省
        $data['make_level2']=$Db_User['level2'];//市
        $data['make_level3']=$Db_User['level3'];//区县
        $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];  //  时间戳
        $data['createtimestr']=Buddha::$buddha_array['buddha_timestr']; //  时间日期
        $order_id=$OrderObj->add($data);

        $OrdermerchantObj->getInsertVersion1OrderMerchantInt($order_id,$order_sn,$merchant_uid['user_id'],$money * $number,"heartpro:{$heartpro_id}");

        //$urls = substr($_SERVER["REQUEST_URI"],1,stripos($_SERVER["REQUEST_URI"],'?'));

        $jsondata = array();
        $backurl = urlencode('user/index.php?a=index&c=order');

        if($OrderObj)
        {
            $jsondata['isok'] = 'true';
            $jsondata['data'] = '成功';
            $jsondata['url'] = 'index.php?a=orderinfo&c=heartpro&goods_id='.$heartpro_id.'&order_id='.$order_id.'&backurl='.$backurl;
            //$datas['data']='/topay/wxpay/wxpayto.php?order_id='.$order_id.'&backurl='.$backurl;
        }else{
            $jsondata['isok']='false';
            $jsondata['data']='服务器忙';
        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'产品购买');
    }








//////////////////////  首页  //////////////////////////////////////











}
