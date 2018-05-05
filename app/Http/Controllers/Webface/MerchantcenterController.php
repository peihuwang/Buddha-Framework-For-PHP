<?php
/**
 * 商家个人中心
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/14
 * Time: 17:02
 * author sys
 */
class MerchantcenterController extends Buddha_App_Action
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
     * 供应
     * author sys
     */
    public function releasesupply()
    {
        $host = Buddha::$buddha_array['host'];//https安全连接
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $SupplyObj = new Supply();
        $CommonObj = new Common();
        $UserObj = new User();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $keyword = Buddha_Http_Input::getParameter('keyword');
        $center = Buddha_Http_Input::getParameter('center');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $is_promote = Buddha_Http_Input::getParameter('is_promote');
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):1;
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):15;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $Today=$Tomorrow=$currentdate=0;
        $Today=strtotime(date('Y-m-d'));//今天0点时间戳
        $Tomorrow=strtotime(date('Y-m-d',strtotime('+1 day')));//明天0点时间戳
        $currentdate=time();//当前时间戳
        if($keyword){
            $like = " AND goods_name like '%{$keyword}%' OR goods_sn='{$keyword}' ";
        }

        $where = " user_id='{$user_id}' AND isdel=0 ";
        if($shop_id){
            $where .= " AND shop_id='{$shop_id}' ";
        }
        if($center != 1){
            $where .= " AND is_sure=1  AND buddhastatus=0 ";
        }
        if($is_promote){
            $where .= " AND is_promote='{$is_promote}' ";
        }
        $orderby = " ORDER BY  add_time DESC ";
        /**
         * view=2 新加  view=3 已审核   view=4 未通过 view=5 促销
         */
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
            }
            if($view == 5){
                if($center == 1){
                    $where.=" and isdel=0 and is_sure=1 and promote_price > 0 ";
                }else{
                    $where.=" and isdel=0 and is_sure=1 and promote_price > 0 and {$Today } <  {$currentdate} and {$currentdate} < {$Tomorrow}";
                }
            }
        }
        $sql = "select count(*) as total from {$this->prefix}supply where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }
        $limit =  Buddha_Tool_Page::sqlLimit ( $page,$pagesize );
        $Db_supply_promote_arr=$SupplyObj->getFiledValues(array('id as supply_id','shop_id','goods_sn','goods_name','market_price','promote_price','is_promote','promote_start_date','promote_end_date','goods_thumb','is_sure'),$where . $like . $orderby . $limit);



        $jsondata =array();
        $jsondata['list'] =array();


        $tablewhere=$this->prefix.'supply';
        $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);
        $jsondata['page'] = $temp_Common['page'];
        $jsondata['pagesize'] = $temp_Common['pagesize'];
        $jsondata['totalrecord'] = $temp_Common['totalrecord'];
        $jsondata['totalpage'] = $temp_Common['totalpage'];

        if(Buddha_Atom_Array::isValidArray($Db_supply_promote_arr)){

            foreach($Db_supply_promote_arr as $k => $v)
            {
                if(Buddha_Atom_String::isValidString($v['goods_thumb'])){
                    $Db_supply_promote_arr[$k]['goods_thumb'] = $host.$v['goods_thumb'];
                }else{
                    $Db_supply_promote_arr[$k]['goods_thumb'] = '';
                }

                $Db_supply_promote_arr[$k]['icon_number'] = $host . "style/img_two/icon_number.png";
                if($v['is_sure'] == 0){
                    $Db_supply_promote_arr[$k]['icon_audit'] = $host . "apiuser/menuplus/weishenhe.png";
                }elseif($v['is_sure'] == 1){
                    $Db_supply_promote_arr[$k]['icon_audit'] = $host . "apiuser/menuplus/yishenhe.png";
                }elseif($v['is_sure'] == 4){
                    $Db_supply_promote_arr[$k]['icon_audit'] = $host . "apiuser/menuplus/weitongguo.png";
                }
            }


        //        if($rcount){
        //            $jsondata['totalrecord'] = $rcount;
        //        }else{
        //            $jsondata['totalrecord'] = 0;
        //        }
        //        if($pcount){
        //            $jsondata['totalpage'] = $pcount;
        //        }else{
        //            $jsondata['totalpage'] = 0;
        //        }
        //        if(Buddha_Atom_Array::isValidArray($Db_supply_promote_arr)){
        //            $jsondata['list'] = $Db_supply_promote_arr;
        //        }else{
        //            $jsondata['list'] = array();
        //        }

            $jsondata['list'] = $Db_supply_promote_arr;
        }



        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'供应列表');
    }

    /**
     * 需求
     * author sys
     */
    public function releasedemand()
    {
        $host = Buddha::$buddha_array['host'];//https安全连接
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $DemandObj = new Demand();
        $CommonObj = new Common();
        $UserObj = new User();
        $DemandcatObj = new Demandcat();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $keyword = Buddha_Http_Input::getParameter('keyword');
        $center = Buddha_Http_Input::getParameter('center');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):1;
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):15;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        if($keyword){
            $like = " AND name like '%{$keyword}%' ";
        }
        $where = " user_id='{$user_id}' AND isdel=0 ";
        if($shop_id){
            $where .= " AND shop_id='{$shop_id}' ";
        }
        if($center != 1){
            $where .= " AND is_sure=1  AND buddhastatus=0 ";
        }
        $orderby = " ORDER BY  add_time DESC ";
        /**
         * view=2 新加  view=3 已审核   view=4 未通过
         */
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
            }
        }
        $sql = "select count(*) as total from {$this->prefix}demand where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }
        $limit =  Buddha_Tool_Page::sqlLimit ( $page,$pagesize );
        $Db_demand_promote_arr=$DemandObj->getFiledValues(array('id as demand_id','shop_id','name','demand_thumb','budget',
            'demandcat_id','is_sure'),$where . $like . $orderby  . $limit);



        $jsondata =array();
        $jsondata['list'] =array();


        $tablewhere=$this->prefix.'demand';
        $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);
        $jsondata['page'] = $temp_Common['page'];
        $jsondata['pagesize'] = $temp_Common['pagesize'];
        $jsondata['totalrecord'] = $temp_Common['totalrecord'];
        $jsondata['totalpage'] = $temp_Common['totalpage'];


        if(Buddha_Atom_Array::isValidArray($Db_demand_promote_arr)){
            foreach($Db_demand_promote_arr as $k => $v)
            {
                $Db_demand_promote_arr[$k]['demandcat_name'] = $DemandcatObj->getDemandcatNameByDemandcatid($v['demandcat_id']);
                if(Buddha_Atom_String::isValidString($v['demand_thumb']))
                {
                    $Db_demand_promote_arr[$k]['demand_thumb'] = $host.$v['demand_thumb'];
                }else{
                    $Db_demand_promote_arr[$k]['demand_thumb'] = '';
                }

                $Db_demand_promote_arr[$k]['icon_price'] = $host . "style/images/Price.png";
                $Db_demand_promote_arr[$k]['icon_class'] = $host ."style/images/shopgray.png";
                if($v['is_sure'] == 0)
                {
                    $Db_demand_promote_arr[$k]['icon_audit'] = $host . "apiuser/menuplus/weishenhe.png";
                }elseif($v['is_sure'] == 1){
                    $Db_demand_promote_arr[$k]['icon_audit'] = $host . "apiuser/menuplus/yishenhe.png";
                }elseif($v['is_sure'] == 4){
                    $Db_demand_promote_arr[$k]['icon_audit'] = $host . "apiuser/menuplus/weitongguo.png";
                }
            }
//            $jsondata = array();
//            $jsondata['page'] = $page;
//            $jsondata['pagesize'] = $pagesize;
//            if($rcount){
//                $jsondata['totalrecord'] = $rcount;
//            }else{
//                $jsondata['totalrecord'] = 0;
//            }
//            if($pcount){
//                $jsondata['totalpage'] = $pcount;
//            }else{
//                $jsondata['totalpage'] = 0;
//            }
//            if(Buddha_Atom_Array::isValidArray($Db_demand_promote_arr)){
//
//            }else{
//                $jsondata['list'] = array();
//            }
            $jsondata['list'] = $Db_demand_promote_arr;
        }



        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'需求列表');
    }

    /**
     * 招聘
     * author sys
     */
    public function releaserecruit()
    {
        $host = Buddha::$buddha_array['host'];//https安全连接
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $RecruitObj = new Recruit();
        $UserObj = new User();
        $CommonObj = new Common();
        $RecruitcatObj = new Recruitcat();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $keyword = Buddha_Http_Input::getParameter('keyword');
        $center = Buddha_Http_Input::getParameter('center');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):1;
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):15;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        if($keyword){
            $like = " AND recruit_name like '%{$keyword}%' ";
        }
        $where = " user_id='{$user_id}' AND isdel=0 ";
        if($shop_id){
            $where .= " AND shop_id='{$shop_id}' ";
        }
        if($center != 1){
            $where .= " AND is_sure=1  AND buddhastatus=0 ";
        }
        $orderby = " ORDER BY  add_time DESC ";
        /**
         * view=2 新加  view=3 已审核   view=4 未通过
         */
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
            }
        }
        $sql = "select count(*) as total from {$this->prefix}recruit where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }
        $limit =  Buddha_Tool_Page::sqlLimit ( $page,$pagesize );
        $Db_recruit_promote_arr=$RecruitObj->getFiledValues(array('id as rec_id','recruit_id','shop_id','recruit_name',
            'pay','recruit_desc','is_sure'),$where . $like . $orderby . $limit );


        $jsondata =array();
        $jsondata['list'] =array();

        $tablewhere=$this->prefix.'recruit';
        $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);
        $jsondata['page'] = $temp_Common['page'];
        $jsondata['pagesize'] = $temp_Common['pagesize'];
        $jsondata['totalrecord'] = $temp_Common['totalrecord'];
        $jsondata['totalpage'] = $temp_Common['totalpage'];


        if(Buddha_Atom_Array::isValidArray($Db_recruit_promote_arr)){
            foreach($Db_recruit_promote_arr as $k => $v){
                $Db_recruit_promote_arr[$k]['recruitcat_name'] = $RecruitcatObj->getRecruitcatNameByRecruitcatid($v['recruit_id']);
                $Db_recruit_promote_arr[$k]['icon_price'] = $host . "style/images/Price.png";
                $Db_recruit_promote_arr[$k]['icon_class'] = $host ."tyle/images/shopgray.png";
            }

            $jsondata['list'] = $Db_recruit_promote_arr;

        }



        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'招聘列表');
    }


    /**
     * 租赁
     * author sys
     */
    public function releaselease()
    {
        $host = Buddha::$buddha_array['host'];//https安全连接
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $LeaseObj = new Lease();
        $CommonObj = new Common();
        $UserObj = new User();
        $LeasecatObj = new Leasecat();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $keyword = Buddha_Http_Input::getParameter('keyword');
        $center = Buddha_Http_Input::getParameter('center');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):1;
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):15;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        if($keyword){
            $like = " AND lease_name like '%{$keyword}%' ";
        }
        $where = " user_id='{$user_id}' AND isdel=0 ";
        if($shop_id){
            $where .= " AND shop_id='{$shop_id}' ";
        }
        if($center != 1){
            $where .= " AND is_sure=1  AND buddhastatus=0 ";
        }
        $orderby = " ORDER BY  add_time DESC ";
        /**
         * view=2 新加  view=3 已审核   view=4 未通过
         */
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
            }
        }
        $sql = "select count(*) as total from {$this->prefix}lease where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }
        $limit =  Buddha_Tool_Page::sqlLimit ( $page,$pagesize );
        $Db_lease_promote_arr=$LeaseObj->getFiledValues(array('id as lease_id','user_id','shop_id','lease_name','rent','lease_thumb','is_sure','leasecat_id'),$where . $like . $orderby . $limit);



        $jsondata =array();
        $jsondata['list'] =array();

        $tablewhere=$this->prefix.'lease';
        $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);
        $jsondata['page'] = $temp_Common['page'];
        $jsondata['pagesize'] = $temp_Common['pagesize'];
        $jsondata['totalrecord'] = $temp_Common['totalrecord'];
        $jsondata['totalpage'] = $temp_Common['totalpage'];

        if(Buddha_Atom_Array::isValidArray($Db_lease_promote_arr))
        {
            foreach($Db_lease_promote_arr as $k => $v){
                if($v['lease_thumb']){
                    $Db_lease_promote_arr[$k]['lease_thumb'] = $host . $v['lease_thumb'];
                }else{
                    $Db_lease_promote_arr[$k]['lease_thumb'] = "";
                }

                $Db_lease_promote_arr[$k]['icon_price'] = $host . "style/images/Price.png";
                $Db_lease_promote_arr[$k]['icon_class'] = $host ."style/images/shopgray.png";
                if($v['is_sure'] == 0){
                    $Db_lease_promote_arr[$k]['icon_audit'] = $host . "apiuser/menuplus/weishenhe.png";
                }elseif($v['is_sure'] == 1){
                    $Db_lease_promote_arr[$k]['icon_audit'] = $host . "apiuser/menuplus/yishenhe.png";
                }elseif($v['is_sure'] == 4){
                    $Db_lease_promote_arr[$k]['icon_audit'] = $host . "apiuser/menuplus/weitongguo.png";
                }

                $Db_lease_promote_arr[$k]['api_releasecatname'] = $LeasecatObj->getReleasecatNamebyReleasecatid($v['leasecat_id']);
            }

            $jsondata['list'] = $Db_lease_promote_arr;

        }


        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'租赁列表');
    }


    /**
     * 供应添加
     */
    public function supplyadd()
    {
        $host = Buddha::$buddha_array['host'];//https安全连接
        $savePath ='storage/supply/';
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken','goods_name','shopcat_id','shop_id','market_price'
       ,'image_arr'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $UserObj = new User();
        $ShopObj = new Shop();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        if($UserObj->isHasMerchantPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],20000022,'没有商家用户权限，你还未申请商家角色');
        }

        if($ShopObj->getShopOfSureToUserTotalInt(0,$user_id) == 0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000028, '您还没用创建店铺，或者店铺还未通过审核！');
        }

        $OrderObj=new Order();
        $ShopObj=new Shop();
        $SupplyObj=new Supply();
        $GalleryObj=new Gallery();
        $JsonimageObj = new Jsonimage();
        $RegionObj=new Region();
        $goods_name=Buddha_Http_Input::getParameter('goods_name');
        $supplycat_id=Buddha_Http_Input::getParameter('shopcat_id');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $goods_unit=Buddha_Http_Input::getParameter('goods_unit');
        $market_price=Buddha_Http_Input::getParameter('market_price');
        $price2=Buddha_Http_Input::getParameter('price2');
        $keywords=Buddha_Http_Input::getParameter('keyword');
        $image_arr=Buddha_Http_Input::getParameter('image_arr');
        if(Buddha_Atom_String::isJson($image_arr)){
            $image_arr = json_decode($image_arr);
        }

        if(!$ShopObj->isShopBelongToUser($shop_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000029, '此店铺不属于当前用户');
        }
        /*判断图片是不是格式正确 应该图片传数组*/
        /* 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($image_arr);


        //商品促销
        $is_promote=Buddha_Http_Input::getParameter('is_promote');
        $promote_price=Buddha_Http_Input::getParameter('promote_price');
        $promote_start_date=Buddha_Http_Input::getParameter('promote_start_date');
        $promote_end_date=Buddha_Http_Input::getParameter('promote_end_date');

        //商品异地发布
        $is_remote=Buddha_Http_Input::getParameter('is_remote');
        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');

        //描述、图片
        $goods_brief=Buddha_Http_Input::getParameter('goods_brief');
        $goods_desc=Buddha_Http_Input::getParameter('goods_desc');
        $data = array();
        $data['goods_name'] = $goods_name;
        $data['user_id'] = $user_id;
        $data['goods_sn'] = date('ymdmis', time()) . rand(10000, 99999);
        $data['supplycat_id'] = $supplycat_id;
        $data['shop_id'] = $shop_id;
        $data['goods_unit'] = $goods_unit;
        $data['market_price'] = $market_price;
        $data['market_price2'] = $price2;
        $data['keywords'] = $keywords;
        $data['add_time'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['goods_brief'] = $goods_brief;
        $data['goods_desc'] = $goods_desc;


        if ($is_promote == 1) {
            $data['is_promote'] = $is_promote;
            $data['promote_price'] = $promote_price;
            $data['promote_start_date'] = strtotime($promote_start_date);
            $data['promote_end_date'] = strtotime($promote_end_date);
        }

        if ($is_remote) {
            if(!$RegionObj->isProvince($level1)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000001, '国家、省、市、区 中省的地区内码id不正确！');
            }

            if(!$RegionObj->isCity($level2)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中市的地区内码id不正确！');
            }

            if(!$RegionObj->isArea($level3)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中区的地区内码id不正确！');
            }
            $data['is_remote'] = $is_remote;
            $data['level0'] = 1;
            $data['level1'] = $level1;
            $data['level2'] = $level2;
            $data['level3'] = $level3;
        } else {
            $data['is_remote'] = 0;
            $Db_level = $ShopObj->getSingleFiledValues(array('level0', 'level1', 'level2', 'level3'), "user_id='{$user_id}' and id='{$shop_id}' and isdel=0");
            $data['level0'] = $Db_level['level0'];
            $data['level1'] = $Db_level['level1'];
            $data['level2'] = $Db_level['level2'];
            $data['level3'] = $Db_level['level3'];
        }
        $good_id = $SupplyObj->add($data);
        if(!$good_id){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000003, '供应添加失败');
        }

        $datas = array();
        if($good_id){
            $savePath.="{$good_id}/";
            if(!file_exists(PATH_ROOT.$savePath)){
                @mkdir(PATH_ROOT.$savePath, 0777);
            }
            $MoreImage = array();
            foreach($image_arr as $k=>$v){
                $temp_img_arr = explode(',', $v);
                $temp_base64_string = $temp_img_arr[1];
                $output_file = date('Ymdhis',time()). "-{$k}.jpg";
                $filePath =PATH_ROOT.$savePath.$output_file;
                Buddha_Atom_File::base64contentToImg($filePath,$temp_base64_string);
                Buddha_Atom_File::resolveImageForRotate($filePath,NULL);
                $result_img = $savePath.''.$output_file;
                $MoreImage[] = "{$result_img}";

            }
            if(is_array($MoreImage) and count($MoreImage)>0){
                $re = $GalleryObj->pcaddimage($MoreImage, $good_id);
                /*if(!$re){
                    Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000112, '图片');
                }*/
                $SupplyObj->setFirstGalleryImgToSupply($good_id);
            }
            if($goods_desc){//富文本编辑器图片处理
                if(stripos($goods_desc,"data:image\/jpeg;base64")){
                    $saveData = $GalleryObj->base_upload($goods_desc,$good_id,"supply");
                    $saveData = str_replace(PATH_ROOT,'/', $saveData);
                    $details['goods_desc'] = $saveData;
                    if(!$SupplyObj->edit($details,$good_id)){
                        Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444006, '富文本添加失败');
                    }
                }
            }

            //$remote为1表示发布异地产品添加订单
            if($is_remote==1){
                $payment_code = Buddha_Http_Input::getParameter('payment_code');
                $payname = Buddha_Http_Input::getParameter('payname');
                $Db_referral=$ShopObj->getSingleFiledValues(array('referral_id','partnerrate','agent_id','agentrate','level0','level1','level2','level3'),"id='{$shop_id}' and user_id='{$user_id}' and isdel=0");
                $getMoneyArrayFromShop = $ShopObj->getMoneyArrayFromShop($shop_id,0.2);
                $data=array();
                $data['good_id']=$good_id;
                $data['user_id']=$user_id;
                $data['order_sn']= $OrderObj->birthOrderId($user_id);
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
                $data['payname']= $payname;
                $data['make_level0']=$Db_referral['level0'];
                $data['make_level1']=$Db_referral['level1'];
                $data['make_level2']=$Db_referral['level2'];
                $data['make_level3']=$Db_referral['level3'];
                $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
                $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
                $order_id=$OrderObj->add($data);
                $jsondata['db_isok']='1';
                $jsondata['db_msg']='商品添加成功,去支付。';
                $jsondata['url']= "{$host}appsdk/{$payment_code}/beforepay.php?order_id={$order_id}";
            }else{
                $jsondata['db_isok'] = '1';
                $jsondata['db_msg'] = '添加成功';
            }
        }else{
            $jsondata['db_isok'] = '0';
            $jsondata['db_msg'] = '添加失败';
        }
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['recurit_id'] = $good_id;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'添加供应');
    }

    /**
     * 供应编辑前页面
     */
    public function beforeupdatesupply(){
        $host = Buddha::$buddha_array['host'];//https安全连接
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken','supply_id'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $SupplyObj = new Supply();
        $CommonObj = new Common();
        $UserObj = new User();
        $ShopObj = new Shop();
        $RegionObj = new Region();
        $GalleryObj = new Gallery();
        $SupplycatObj = new Supplycat();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $supply_id = Buddha_Http_Input::getParameter('supply_id');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
       /*if($UserObj->isHasMerchantPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],20000022,'没有商家用户权限，你还未申请商家角色');
        }
        if(!$SupplyObj->isSupplyBelongToUser($supply_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000031, '供应信息的主人不是目前的用户');
        }*/

        $Db_Supply = $SupplyObj->getSingleFiledValues('' ,"id= '{$supply_id}' ");


        $jsondata = array();
        if(Buddha_Atom_Array::isValidArray($Db_Supply)){
            $areastr = $RegionObj->getDetailOfAdrressByRegionIdStr($Db_Supply['level1'],$Db_Supply['level2'],$Db_Supply['level3'],'>');//汉字区域
            $goods_desc = Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Supply['goods_desc']);//详情图片加上绝对路径
            $Db_img_arr = $GalleryObj->getGoodsImage($supply_id);

            $Db_GettableOption = $SupplycatObj->getSupplycatunit($Db_Supply['goods_unit']);//单位
            $Db_Supplycat = $SupplycatObj->getcatist();//分类
            $Db_Supplycat_name = $SupplycatObj->getSingleCatName($Db_Supply['supplycat_id']);//分类
            $jsondata = array();
            $jsondata['user_id'] = $user_id;
            $jsondata['usertoken'] = $usertoken;
//            $jsondata['GettableOption'] = $Db_GettableOption;
            $jsondata['user_id'] = $user_id;
            $jsondata['usertoken'] = $usertoken;
            $jsondata['supply_id'] = $supply_id;
            $jsondata['supplycat_id'] = $Db_Supply['supplycat_id'];
            $jsondata['supplycat_name'] = $Db_Supplycat_name;
            $jsondata['shop_id'] = $Db_Supply['shop_id'];
            $jsondata['is_promote'] = $Db_Supply['is_promote'];
            $jsondata['goods_name'] = $Db_Supply['goods_name'];
            $jsondata['goods_unit'] = $Db_Supply['goods_unit'];
            $jsondata['unit'] = $Db_Supply['goods_unit'];
            $jsondata['market_price'] = $Db_Supply['market_price'];
            $jsondata['promote_price'] = $Db_Supply['promote_price'];
            if(Buddha_Atom_String::isValidString($Db_Supply['promote_start_date'])){
                $jsondata['promote_start_date'] =$CommonObj->getDateStrOfTime($Db_Supply['promote_start_date']);
            }else{
                $jsondata['promote_start_date'] = '';
            }
            if(Buddha_Atom_String::isValidString($Db_Supply['promote_end_date'])){
                $jsondata['promote_end_date'] = $CommonObj->getDateStrOfTime($Db_Supply['promote_end_date']);
            }else{
                $jsondata['promote_end_date'] = '';
            }

            $jsondata['keywords'] = $Db_Supply['keywords'];
            $jsondata['goods_brief'] = $Db_Supply['goods_brief'];
            $jsondata['goods_desc'] = $goods_desc;
            $jsondata['is_remote'] = $Db_Supply['is_remote'];
            $jsondata['level1'] = $Db_Supply['level1'];
            $jsondata['level2'] = $Db_Supply['level2'];
            $jsondata['level3'] = $Db_Supply['level3'];
            $jsondata['areastr'] = $areastr;
            $jsondata['albumlist']=$SupplyObj->getApiLeaseGalleryArr($supply_id);


            //是否促销
            if($Db_Supply['is_promote'] == 1){
                $promote_start_date = date('Y-m-d',$Db_Supply['promote_start_date']);
                $promote_end_date = date('Y-m-d',$Db_Supply['promote_end_date']);
                $jsondata['promote_price'] = $Db_Supply['promote_price'];
                $jsondata['promote_start_date'] = $promote_start_date;
                $jsondata['promote_end_date'] = $promote_end_date;
            }
            $shop_id_list=$ShopObj->getUserShopArr($user_id,$Db_Supply['shop_id']);
            if(Buddha_Atom_Array::isValidArray($shop_id_list)){
                $jsondata['shop_id_list'] = $shop_id_list;
            }else{
                $shop_id_list = $ShopObj->getFiledValues(array('name','id as namevalue'),"id='{$Db_Supply['shop_id']}'");
                $jsondata['shop_id_list'] = $shop_id_list;
            }
            $jsondata['Supplycat'] = $Db_Supplycat;
            $jsondata['unit_list'] = $Db_GettableOption;
            $shop_id_list=$ShopObj->getUserShopArr($user_id,0);
            $jsondata['area']=array(
                'Services'=>'ajaxregion.getBelongFromFatherId',
                'param'=>array('father'=>1),
            );
        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '编辑供应之前的展示页面');


    }
    /**
     * 供应编辑
     */
    public function supplyedit(){
        $host = Buddha::$buddha_array['host'];//https安全连接
        $savePath ='storage/supply/';
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken','supply_id','goods_name','shopcat_id','shop_id','market_price'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $UserObj = new User();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        if($UserObj->isHasMerchantPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],20000022,'没有商家用户权限，你还未申请商家角色');
        }

        $OrderObj=new Order();
        $ShopObj=new Shop();
        $SupplyObj=new Supply();
        $GalleryObj=new Gallery();
        $RegionObj = new Region();
        $JsonimageObj = new Jsonimage();
        $supply_id=Buddha_Http_Input::getParameter('supply_id');
        $goods_name=Buddha_Http_Input::getParameter('goods_name');
        $supplycat_id=Buddha_Http_Input::getParameter('shopcat_id');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $goods_unit=Buddha_Http_Input::getParameter('goods_unit');
        $market_price=Buddha_Http_Input::getParameter('market_price');
        $price2=Buddha_Http_Input::getParameter('price2');
        $keywords=Buddha_Http_Input::getParameter('keyword');
        $image_arr=Buddha_Http_Input::getParameter('image_arr');
        if(Buddha_Atom_String::isJson($image_arr)){
            $image_arr = json_decode($image_arr);
        }
        if(!$ShopObj->isShopBelongToUser($shop_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000029, '此店铺不属于当前用户');
        }

        /*判断图片是不是格式正确 应该图片传数组*/
        /* 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($image_arr);



        //商品促销
        $is_promote=Buddha_Http_Input::getParameter('is_promote');
        $promote_price=Buddha_Http_Input::getParameter('promote_price');
        $promote_start_date=Buddha_Http_Input::getParameter('promote_start_date');
        $promote_end_date=Buddha_Http_Input::getParameter('promote_end_date');

        //商品异地发布
        $is_remote=Buddha_Http_Input::getParameter('is_remote');
        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');


        //描述、图片
        $goods_brief=Buddha_Http_Input::getParameter('goods_brief');
        $goods_desc=Buddha_Http_Input::getParameter('goods_desc');
        $data = array();
        $data['goods_name'] = $goods_name;
        $data['user_id'] = $user_id;
        $data['goods_sn'] = date('ymdmis', time()) . rand(10000, 99999);
        $data['supplycat_id'] = $supplycat_id;
        $data['shop_id'] = $shop_id;
        $data['goods_unit'] = $goods_unit;
        $data['market_price'] = $market_price;
        $data['market_price2'] = $price2;
        $data['keywords'] = $keywords;
        $data['add_time'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['goods_brief'] = $goods_brief;
        //$data['goods_desc'] = $goods_desc;

        if ($is_promote == 1) {
            $data['is_promote'] = $is_promote;
            $data['promote_price'] = $promote_price;
            $data['promote_start_date'] = strtotime($promote_start_date);
            $data['promote_end_date'] = strtotime($promote_end_date);
        }

        if ($is_remote) {
            if(!$RegionObj->isProvince($level1)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000001, '国家、省、市、区 中省的地区内码id不正确！');
            }

            if(!$RegionObj->isCity($level2)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中市的地区内码id不正确！');
            }

            if(!$RegionObj->isArea($level3)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中区的地区内码id不正确！');
            }
            $data['is_remote'] = $is_remote;
            $data['level0'] = 1;
            $data['level1'] = $level1;
            $data['level2'] = $level2;
            $data['level3'] = $level3;
        } else {
            $data['is_remote'] = 0;
            $Db_level = $ShopObj->getSingleFiledValues(array('level0', 'level1', 'level2', 'level3'), "user_id='{$user_id}' and id='{$shop_id}' and isdel=0");
            $data['level0'] = $Db_level['level0'];
            $data['level1'] = $Db_level['level1'];
            $data['level2'] = $Db_level['level2'];
            $data['level3'] = $Db_level['level3'];
        }
        if(!$SupplyObj->updateRecords($data,"id='{$supply_id}'")){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000003, '供应编辑失败');
        }

        $datas = array();
        if($supply_id){
            $savePath.="{$supply_id}/";
            if(!file_exists(PATH_ROOT.$savePath)){
                @mkdir(PATH_ROOT.$savePath, 0777);
            }
            $MoreImage = array();
            foreach($image_arr as $k=>$v){
                $temp_img_arr = explode(',', $v);
                $temp_base64_string = $temp_img_arr[1];
                $output_file = date('Ymdhis',time()). "-{$k}.jpg";
                $filePath =PATH_ROOT.$savePath.$output_file;
                Buddha_Atom_File::base64contentToImg($filePath,$temp_base64_string);
                Buddha_Atom_File::resolveImageForRotate($filePath,NULL);
                $result_img = $savePath.''.$output_file;
                $MoreImage[] = "{$result_img}";

            }
            if(is_array($MoreImage) and count($MoreImage)>0){
                $re = $GalleryObj->pcaddimage($MoreImage, $supply_id);
                /*if(!$re){
                    Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000112, '图片');
                }*/
                $SupplyObj->setFirstGalleryImgToSupply($supply_id);
            }
            if($goods_desc){//富文本编辑器图片处理
                if(stripos($goods_desc,"data:image\/jpeg;base64")){
                    $saveData = $GalleryObj->base_upload($goods_desc,$supply_id,"supply");
                    $saveData = str_replace(PATH_ROOT,'/', $saveData);
                    $details['goods_desc'] = $saveData;
                    if(!$SupplyObj->edit($details,$supply_id)){
                        Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444006, '富文本添加失败');
                    }
                }
            }

            //$remote为1表示发布异地产品添加订单
            if($is_remote==1){
                $payment_code = Buddha_Http_Input::getParameter('payment_code');
                $payname = Buddha_Http_Input::getParameter('payname');
                $Db_referral=$ShopObj->getSingleFiledValues(array('referral_id','partnerrate','agent_id','agentrate','level0','level1','level2','level3'),"id='{$shop_id}' and user_id='{$user_id}' and isdel=0");
                $getMoneyArrayFromShop = $ShopObj->getMoneyArrayFromShop($shop_id,0.2);
                $data=array();
                $data['good_id']=$supply_id;
                $data['user_id']=$user_id;
                $data['order_sn']= $OrderObj->birthOrderId($user_id);
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
                $data['payname']= $payname;
                $data['make_level0']=$Db_referral['level0'];
                $data['make_level1']=$Db_referral['level1'];
                $data['make_level2']=$Db_referral['level2'];
                $data['make_level3']=$Db_referral['level3'];
                $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
                $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
                $order_id=$OrderObj->add($data);
                $jsondata['db_isok']='1';
                $jsondata['db_msg']='商品添加成功,去支付。';
                $jsondata['url']= "{$host}appsdk/{$payment_code}/beforepay.php?order_id={$order_id}";
            }else{
                $jsondata['db_isok'] = '1';
                $jsondata['db_msg'] = '添加成功';
            }
        }else{
            $jsondata['db_isok'] = '0';
            $jsondata['db_msg'] = '添加失败';
        }
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['supply_id'] = $supply_id;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'编辑供应');
    }

    /**
     * 供应添加前页面
     */
    public function beforeaddsupply(){
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $UserObj = new User();
        $ShopObj = new Shop();
        $SupplycatObj = new Supplycat();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','mobile','realname');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        if(!$ShopObj->getShopOfSureToUserTotalInt(0,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000028, '您还没用创建店铺，或者店铺还未通过审核！');
        }
        $Db_GettableOption=$SupplycatObj->getSupplycatunit();//单位
        $Db_Supplycat_name = $SupplycatObj->getcatist();//分类
        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['GettableOption'] = $Db_GettableOption;
        $jsondata['Supplycat'] = $Db_Supplycat_name;
        $shop_id_list=$ShopObj->getUserShopArr($user_id,0);
        $jsondata['shop_id_list'] = $shop_id_list;
        $jsondata['area']=array(
            'Services'=>'ajaxregion.getBelongFromFatherId',
            'param'=>array('father'=>1),
        );
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '添加供应之前的展示页面');
    }

    /**
     * 供应删除
     */
    public function delsupply(){
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken','supply_id'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $GalleryObj=new Gallery();
        $SupplyObj=new Supply();
        $UserObj = new User();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $supply_id=(int)Buddha_Http_Input::getParameter('supply_id');
        if(!$SupplyObj->isSupplyBelongToUser($supply_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000031, '供应信息的主人不是目前的用户');
        }

        $SupplyObj->del($supply_id);
        $GalleryObj->delGelleryimage($supply_id);
        $jsondata=array();
        if($SupplyObj){
            $jsondata['isok']='true';
            $jsondata['data']='删除成功';
            $jsondata['supply_id']=$supply_id;
            $jsondata['album']=array(
                'Server'=>'album.deleteSupplyImage',
                'param'=>array('album_id'=>$supply_id,'table_name'=>'supply'),
            );

        }else{
            $jsondata['isok']='false';
            $jsondata['data']='服务器忙';
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'删除供应');
    }

    /**
     * 需求添加前页面
     */
    public function beforeadddemand(){
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $UserObj = new User();
        $ShopObj = new Shop();
        $DemandcatObj = new Demandcat();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','mobile','realname');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $Db_Demandcat_name = $DemandcatObj->getcatist();//分类
        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['Supplycat'] = $Db_Demandcat_name;
        $shop_id_list=$ShopObj->getUserShopArr($user_id);
        $jsondata['shop_id_list'] = $shop_id_list;
        $jsondata['area']=array(
            'Services'=>'ajaxregion.getBelongFromFatherId',
            'param'=>array('father'=>1),
        );
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '添加需求之前的展示页面');
    }

    /**
     * 需求删除
     */
    public function deldemand(){
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken','demand_id'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $DemandObj = new Demand();
        $UserObj = new User();
        $AlbumObj = new Album();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $demand_id = Buddha_Http_Input::getParameter('demand_id');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        if($UserObj->isHasMerchantPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有商家用户权限，你还未申请商家角色');
        }

        if(!$DemandObj->isSupplyBelongToUser($demand_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000031, '需求信息的主人不是目前的用户');
        }
        if(!$AlbumObj->deletePhotos($demand_id,"demand")){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000005, '相册删除失败');
        }
        $DemandObj=new Demand();
        $DemandObj->del($demand_id);
        $DemandObj->deleteFIleOfPicture($demand_id);

        if($DemandObj){
            $jsondata['isok']='true';
            $jsondata['data']='删除成功';
            $jsondata['demand_id']=$demand_id;
            $jsondata['album']=array(
                'Server'=>'album.deleteimage',
                'param'=>array('album_id'=>$demand_id,'table_name'=>'demand'),
            );
        }else{
            $jsondata['isok']='false';
            $jsondata['data']='服务器忙';
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'删除需求');
    }


    /**
     * 需求添加
     */
    public function demandadd(){

        $host = Buddha::$buddha_array['host'];//https安全连接

        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken','demand_name','demandcat_id','budget'
        ,'demand_start_time','demand_end_time'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }

        $ShopObj=new Shop();
        $DemandObj=new Demand();
        $JsonimageObj=new Jsonimage();
        $UserObj = new User();
        $RegionObj = new Region();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $demand_name = Buddha_Http_Input::getParameter('demand_name');
        $demandcat_id = Buddha_Http_Input::getParameter('demandcat_id');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $budget = Buddha_Http_Input::getParameter('budget');
        $demand_start_time = Buddha_Http_Input::getParameter('demand_start_time');
        $demand_end_time = Buddha_Http_Input::getParameter('demand_end_time');
        $keywords = Buddha_Http_Input::getParameter('keyword');
        $image_arr=Buddha_Http_Input::getParameter('image_arr');

        if(Buddha_Atom_String::isJson($image_arr)){
            $image_arr = json_decode($image_arr);
        }
        /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($image_arr);
        //需求异地发布
        $is_remote = Buddha_Http_Input::getParameter('is_remote');
        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');

        //描述、图片
        $demand_brief = Buddha_Http_Input::getParameter('demand_brief');
        $demand_desc = Buddha_Http_Input::getParameter('demand_desc');

            $data=array();
            $data['name']=$demand_name;
            $data['user_id']=$user_id;
            $data['demandcat_id']=$demandcat_id;
            $data['shop_id']=$shop_id;
            $data['budget']=$budget;
            $data['keywords']=$keywords;
            $data['add_time']=Buddha::$buddha_array['buddha_timestamp'];
            $data['demand_brief']=$demand_brief;
            $data['demand_desc']=$demand_desc;
            $data['demand_start_time']=strtotime($demand_start_time);
            $data['demand_end_time']=strtotime($demand_end_time);
            $data['is_remote']=$is_remote;
            if($is_remote){
                if(!$RegionObj->isProvince($level1)){
                    Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000001, '国家、省、市、区 中省的地区内码id不正确！');
                }

                if(!$RegionObj->isCity($level2)){
                    Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中市的地区内码id不正确！');
                }

                if(!$RegionObj->isArea($level3)){
                    Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中区的地区内码id不正确！');
                }
                $data['level0']=1;
                $data['level1']=$level1;
                $data['level2']=$level2;
                $data['level3']=$level3;
            }else{
                $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$user_id}' and id='{$shop_id}' and isdel=0");
                $data['level0']=$Db_level['level0'];
                $data['level1']=$Db_level['level1'];
                $data['level2']=$Db_level['level2'];
                $data['level3']=$Db_level['level3'];
            }
            $demand_id = $DemandObj->add($data);
            if(!$demand_id){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000004, '需求信息添加失败');
            }

            $savePath ="storage/demand/{$demand_id}/";

            if(!file_exists(PATH_ROOT.$savePath)){
                @mkdir(PATH_ROOT.$savePath, 0777);
            }

            if(Buddha_Atom_Array::isValidArray($image_arr)) {
                foreach ($image_arr as $k => $v) {
                    $temp_img_arr = explode(',', $v);
                    $temp_base64_string =  $temp_img_arr[1];

                    $output_file = date('Ymdhis', time()) . "-{$k}.jpg";
                    $filePath = PATH_ROOT . $savePath . $output_file;

                    Buddha_Atom_File::base64contentToImg($filePath, $temp_base64_string);

                    Buddha_Atom_File::resolveImageForRotate($filePath, NULL);
                    $result_img = $savePath . '' . $output_file;
                    $MoreImage[$k] = "{$result_img}";
                }
            }

            if(Buddha_Atom_Array::isValidArray($MoreImage)){
                $DemandObj->addImageArrToLeaseAlbum($MoreImage,$demand_id,$savePath,$user_id);
                $DemandObj->setFirstGalleryImgToDemand($demand_id);
            }


            $is_needcreateorder = 0;
            $Services = '';
            $param = array();
            //$remote为1表示发布异地产品添加订单
            if ($is_remote == 1) {
                $is_needcreateorder = 1;
                $Services = 'payment.remoteinfo';
                $param = array('good_id' => $demand_id, 'good_table' => 'demand');
            }
            $jsondata = array();
            $jsondata['user_id'] = $user_id;
            $jsondata['usertoken'] = $usertoken;
            $jsondata['demand_id'] = $demand_id;

            $jsondata['is_needcreateorder'] = $is_needcreateorder;
            $jsondata['Services'] = $Services;
            $jsondata['param'] = $param;


            $jsondata['db_isok'] = '1';
            $jsondata['db_msg'] = '需求添加成功';
            Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '添加需求');

    }

    /**
     * 需求编辑前页面
     */
    public function beforeupdatedemand(){
        $host = Buddha::$buddha_array['host'];//https安全连接
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken','demand_id'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $DemandObj = new Demand();
        $UserObj = new User();
        $ShopObj = new Shop();
        $RegionObj = new Region();
        //$GalleryObj = new Gallery();
        $DemandcatObj = new Demandcat();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $demand_id = Buddha_Http_Input::getParameter('demand_id');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        /*if($UserObj->isHasMerchantPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],20000022,'没有商家用户权限，你还未申请商家角色');
        }
        if(!$DemandObj->isSupplyBelongToUser($demand_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000031, '需求信息的主人不是目前的用户');
        }*/

        $Db_Demand = $DemandObj->getSingleFiledValues('' ,"id= '{$demand_id}' ");
        $Db_Demandcat_name = $DemandcatObj->getSingleCatName($Db_Demand['demandcat_id']);//分类
        $areastr = $RegionObj->getDetailOfAdrressByRegionIdStr($Db_Demand['level1'],$Db_Demand['level2'],$Db_Demand['level3'],'>');//汉字区域
        $goods_desc = Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Demand['demand_desc']);//详情图片加上绝对路径
        $AlbumObj = new Album();
        $Db_img_arr = $AlbumObj->getImage($demand_id,'demand');
        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['demand_id'] = $demand_id;
        $jsondata['demandcat_id'] = $Db_Demand['demandcat_id'];
        $jsondata['shop_id'] = $Db_Demand['shop_id'];
        $jsondata['demandcat_name'] = $Db_Demandcat_name;
        $jsondata['name'] = $Db_Demand['name'];
        $jsondata['budget'] = $Db_Demand['budget'];
        $jsondata['keywords'] = $Db_Demand['keywords'];
        $jsondata['demand_brief'] = $Db_Demand['demand_brief'];
        $jsondata['demand_desc'] = $goods_desc;
        $jsondata['is_remote'] = $Db_Demand['is_remote'];
        $jsondata['level1'] = $Db_Demand['level1'];
        $jsondata['level2'] = $Db_Demand['level2'];
        $jsondata['level3'] = $Db_Demand['level3'];
        $jsondata['areastr'] = $areastr;
        $jsondata['albumlist']=$DemandObj->getApiLeaseAlbumArr($demand_id);

        if(Buddha_Atom_Array::isValidArray($Db_img_arr) && count($Db_img_arr)>1){
            foreach($Db_img_arr as $k => $v){
                $jsondata['demand_thumb'][$k] = $host . $v['goods_thumb'];
            }
        }
        $demand_start_time = date('Y-m-d',$Db_Demand['demand_start_time']);
        $demand_end_time = date('Y-m-d',$Db_Demand['demand_end_time']);
        $jsondata['demand_start_date'] = $demand_start_time;
        $jsondata['demand_end_date'] = $demand_end_time;
        $shop_id_list=$ShopObj->getUserShopArr($user_id,$Db_Demand['shop_id']);

        if(Buddha_Atom_Array::isValidArray($shop_id_list)){
            $jsondata['shop_id_list'] = $shop_id_list;
        }else{
            $shop_id_list = $ShopObj->getFiledValues(array('name','id as namevalue'),"id='{$Db_Demand['shop_id']}'");
            $jsondata['shop_id_list'] = $shop_id_list;
        }
        $jsondata['area']=array(
            'Services'=>'ajaxregion.getBelongFromFatherId',
            'param'=>array(),
        );
         $jsondata['class']=array(
            'Services'=>'multilist.demandclassmore',
            'param'=>array('father'=>1),
        );
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '需求编辑前展示页面');
    }

    /**
     * 需求编辑
     */
    public function demandedit(){
        $host = Buddha::$buddha_array['host'];//https安全连接
        //$savePath ='storage/demand/';
        if(Buddha_Http_Input::checkParameter(array('b_display','demand_id','usertoken','demandcat_id','demand_name','budget'
        ,'demand_start_time','demand_end_time','image_arr'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $UserObj = new User();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $OrderObj=new Order();
        $ShopObj=new Shop();
        $DemandObj=new Demand();
        $GalleryObj=new Gallery();
        $JsonimageObj = new Jsonimage();
        $RegionObj = new Region();
        $name = Buddha_Http_Input::getParameter('demand_name');
        $demand_id = Buddha_Http_Input::getParameter('demand_id');
        $demandcat_id = Buddha_Http_Input::getParameter('demandcat_id');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $budget = Buddha_Http_Input::getParameter('budget');
        $demand_start_time = Buddha_Http_Input::getParameter('demand_start_time');
        $demand_end_time = Buddha_Http_Input::getParameter('demand_end_time');
        $keywords = Buddha_Http_Input::getParameter('keyword');
        $image_arr=Buddha_Http_Input::getParameter('image_arr');
        //需求异地发布
        $is_remote = Buddha_Http_Input::getParameter('is_remote');
        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');

        //描述、图片
        $demand_brief = Buddha_Http_Input::getParameter('demand_brief');
        $demand_desc = Buddha_Http_Input::getParameter('demand_desc');
        /*if($shop_id){
            if(!$ShopObj->getShopOfSureToUserTotalInt($shop_id,$user_id)==0){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000028, '您还没用创建店铺，或者店铺还未通过审核！');
            }
            if(!$ShopObj->isShopBelongToUser($shop_id,$user_id)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000029, '此店铺不属于当前用户');
            }
            if($UserObj->isHasMerchantPrivilege($user_id)==0){
                Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],20000022,'没有商家用户权限，你还未申请商家角色');
            }
        }*/
        if(Buddha_Atom_String::isJson($image_arr)){
            $image_arr = json_decode($image_arr);
        }
        /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($image_arr);
        /*判断图片是不是格式正确 应该图片传数组*/
        /* 遍历图片数组 确保每个图片格式都正确*/
        //$JsonimageObj->errorDieImageFromUpload($image_arr);
        $data = array();
        $data['name'] = $name;
        $data['user_id'] = $user_id;
        $data['demandcat_id'] = $demandcat_id;
        $data['shop_id'] = $shop_id;
        $data['budget'] = $budget;
        $data['keywords'] = $keywords;
        $data['add_time'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['demand_brief'] = $demand_brief;
        $data['demand_desc'] = $demand_desc;
        $data['is_remote'] = $is_remote;
        $data['demand_start_time'] = strtotime($demand_start_time);
        $data['demand_end_time'] = strtotime($demand_end_time);
        if ($is_remote) {
            if(!$RegionObj->isProvince($level1)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000001, '国家、省、市、区 中省的地区内码id不正确！');
            }

            if(!$RegionObj->isCity($level2)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中市的地区内码id不正确！');
            }

            if(!$RegionObj->isArea($level3)){
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中区的地区内码id不正确！');
            }
            $data['level1'] = $level1;
            $data['level2'] = $level2;
            $data['level3'] = $level3;
        } else {
            $Db_level = $ShopObj->getSingleFiledValues(array('level0', 'level1', 'level2', 'level3'), "user_id='{$user_id}' and id='{$shop_id}' and isdel=0");
            $data['level0'] = $Db_level['level0'];
            $data['level1'] = $Db_level['level1'];
            $data['level2'] = $Db_level['level2'];
            $data['level3'] = $Db_level['level3'];
        }
        //$demand_id = $DemandObj->edit($data,$demand_id);
        if(!$DemandObj->edit($data,$demand_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000003, '需求编辑失败');
        }
        $datas = array();
        $savePath ="storage/demand/{$demand_id}/";
        if(!file_exists(PATH_ROOT.$savePath)){
            @mkdir(PATH_ROOT.$savePath, 0777);
        }
        if(Buddha_Atom_Array::isValidArray($image_arr)) {
            foreach ($image_arr as $k => $v) {
                $temp_img_arr = explode(',', $v);
                $temp_base64_string = $temp_img_arr[1];

                $output_file = date('Ymdhis', time()) . "-{$k}.jpg";
                $filePath = PATH_ROOT . $savePath . $output_file;
                Buddha_Atom_File::base64contentToImg($filePath, $temp_base64_string);

                Buddha_Atom_File::resolveImageForRotate($filePath, NULL);
                $result_img = $savePath . '' . $output_file;
                $MoreImage[$k] = "{$result_img}";
            }
        }
        file_put_contents("isotest.txt",var_export($MoreImage,true));
        if(Buddha_Atom_Array::isValidArray($MoreImage)){
            $DemandObj->addImageArrToLeaseAlbum($MoreImage,$demand_id,$savePath,$user_id);
            $DemandObj->setFirstGalleryImgToDemand($demand_id);
        }
        //$remote为1表示发布异地产品添加订单
        if($is_remote==1){
            $payment_code = Buddha_Http_Input::getParameter('payment_code');
            $payname = Buddha_Http_Input::getParameter('payname');
            $Db_referral=$ShopObj->getSingleFiledValues(array('referral_id','partnerrate','agent_id','agentrate','level0','level1','level2','level3'),"id='{$shop_id}' and user_id='{$user_id}' and isdel=0");
            $getMoneyArrayFromShop = $ShopObj->getMoneyArrayFromShop($shop_id,0.2);
            $data=array();
            $data['demand_id']=$demand_id;
            $data['user_id']=$user_id;
            $data['order_sn']= $OrderObj->birthOrderId($user_id);
            $data['good_table']='demand';
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
            $data['payname']= $payname;
            $data['make_level0']=$Db_referral['level0'];
            $data['make_level1']=$Db_referral['level1'];
            $data['make_level2']=$Db_referral['level2'];
            $data['make_level3']=$Db_referral['level3'];
            $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
            $order_id=$OrderObj->add($data);
            $jsondata['db_isok']='1';
            $jsondata['db_msg']='需求编辑成功,去支付。';
            $jsondata['url']= "{$host}appsdk/{$payment_code}/beforepay.php?order_id={$order_id}";
        }else{
            $jsondata['db_isok'] = '1';
            $jsondata['db_msg'] = '编辑成功';
        }
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['demand_id'] = $demand_id;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'编辑需求');
    }

    /**
     * 代理商申请
     */
    public function applyagent(){
        $host = Buddha::$buddha_array['host'];//https安全连接
        $savePath ='storage/demand/';
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken','payment_code','payname','Party_b','pross','citys'
        ,'area','signature','id_card','mobile','address','email','referees','dates','notes'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $UserObj = new User();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $payment_code = Buddha_Http_Input::getParameter('payment_code');
        $payname = Buddha_Http_Input::getParameter('payname');
        $RegionObj = new Region();
        $OrderObj = new Order();
        $ApplyagentObj = new Applyagent();

        $province = $RegionObj->getFiledValues(array('id','name'),"level=1");
        $party_b=Buddha_Http_Input::getParameter('Party_b');//公司名称
        $pross=Buddha_Http_Input::getParameter('pross');//省
        $citys=Buddha_Http_Input::getParameter('citys');//市
        $area=Buddha_Http_Input::getParameter('area');//区县
        $signature=Buddha_Http_Input::getParameter('signature');//署名
        $id_card=Buddha_Http_Input::getParameter('id_card');//身份证号
        $mobile=Buddha_Http_Input::getParameter('mobile');//手机号
        $address=Buddha_Http_Input::getParameter('address');//详细地址
        $email=Buddha_Http_Input::getParameter('email');//邮箱
        $referees=Buddha_Http_Input::getParameter('referees');//推荐人
        $dates=Buddha_Http_Input::getParameter('dates');//电子合同填写日期
        $notes=Buddha_Http_Input::getParameter('notes');//备注
        if(!$RegionObj->isProvince($pross)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000001, '国家、省、市、区中的省的ID不存在');
        }

        if(!$RegionObj->isCity($citys)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000002, '国家、省、市、区中的市的ID不存在');
        }

        if(!$RegionObj->isArea($area)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000003, '国家、省、市、区中的区的ID不存在');
        }
        //判断代理区域是否空白
        if($citys && !$area){//市代
            $cityNum = $ApplyagentObj->countRecords("level2={$citys} and level3='' and isok=1 and ispay=1");
            if($cityNum){
                $jsondata['isok']='false';
                $jsondata['data']='对不起，您所选市已有代理';
                Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'对不起，您所选市已有代理!');
            }
        }
        if($area){//区县代理
            $areaNum = $ApplyagentObj->countRecords("level3={$area} and isok=1 and ispay=1");
            if($areaNum){
                $jsondata = array();
                $jsondata['info']='对不起，所选区域已有代理';
                $jsondata['url']='';
                Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'对不起，所选区域已有代理!');
            }
        }
        $datase = array();
        $datase['party_b'] = $party_b;
        $datase['level1'] = $pross;
        $datase['level2'] = $citys;
        $datase['level3'] = $area;
        $datase['signature'] = $signature;
        $datase['id_card'] = $id_card;
        $datase['mobile'] = $mobile;
        $datase['address'] = $address;
        $datase['email'] = $email;
        $datase['referees'] = $referees;
        $datase['dates'] = $dates;
        $datase['notes'] = $notes;
        $datase['createtime'] = time();
        $datase['isok'] = 0;
        $insert_id = $ApplyagentObj->add($datase);
        if($insert_id){
            /*$datas=array();
            $datas['good_id']=$insert_id;//指定产品id
            $datas['user_id']=$user_id;
            $datas['order_sn']= $OrderObj->birthOrderId($user_id);//订单编号
            $datas['good_table']='applyagent';//哪个表
            $datas['pay_type']='third';//third第三方支付，point积分，balance余额
            $datas['order_type']='applyagent';//money.out提现, 店铺认证shop.v,信息置顶info.top ,跨区域信息推广info.market,信息查看info.see，applyagent代理商申请押金
            $datas['goods_amt']=3000.00;//产品价格
            $datas['final_amt']=3000.00;//产品最终价格

            $datas['payname'] = $payname;
            $datas['createtime']=Buddha::$buddha_array['buddha_timestamp'];
            $datas['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
            $order_id=$OrderObj->add($datas);*/
            $jsondata=array();
            $jsondata['info']='添加成功，调起选择支付方式接口';
            $jsondata['Services'] = "payment.choice";
            $jsondata['param'] = array();
            $jsondata['applyagent_id'] = $insert_id;

            //$jsondata['url']= "{$host}appsdk/{$payment_code}/beforepay.php?order_id={$order_id}";
        }else{
            $jsondata['info']='服务器忙';
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'申请成功!');
    }
    /**
     * 招聘删除
     */
    public function delrecruit(){
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken','recruit_id'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $UserObj = new User();
        $RecruitObj = new Recruit();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $recruit_id = Buddha_Http_Input::getParameter('recruit_id');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        if($UserObj->isHasMerchantPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有商家用户权限，你还未申请商家角色');
        }

        if(!$RecruitObj->isRecruitBelongToUser($recruit_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000031, '招聘信息的主人不是目前的用户');
        }
        //$DemandObj->deleteFIleOfPicture($recruit_id);

        if($RecruitObj->del($recruit_id)){
            $jsondata['isok']='true';
            $jsondata['data']='删除成功';
            $jsondata['recruit_id']=$recruit_id;
        }else{
            $jsondata['isok']='false';
            $jsondata['data']='服务器忙';
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'删除招聘');
    }

    /**
     * 租赁删除
     */
    public function dellease(){
        if(Buddha_Http_Input::checkParameter(array('b_display','usertoken','lease_id'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $LeaseObj = new Lease();
        $UserObj = new User();
        $AlbumObj = new Album();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $lease_id = Buddha_Http_Input::getParameter('lease_id');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        if($UserObj->isHasMerchantPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有商家用户权限，你还未申请商家角色');
        }

        if(!$LeaseObj->isLeaseBelongToUser($lease_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000031, '租赁信息不存在或主人不是目前的用户');
        }
        if(!$AlbumObj->deletePhotos($lease_id,"lease")){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000005, '相册删除失败');
        }
        if($LeaseObj->del($lease_id)){
            $jsondata['isok']='true';
            $jsondata['data']='删除成功';
            $jsondata['lease_id']=$lease_id;
            $jsondata['album']=array(
                'Server'=>'album.deleteimage',
                'param'=>array('album_id'=>$lease_id,'table_name'=>'lease'),
            );
        }else{
            $jsondata['isok']='false';
            $jsondata['data']='服务器忙';
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'删除租赁');
    }
}