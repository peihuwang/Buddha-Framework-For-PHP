<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/9
 * Time: 16:42
 * 所有列表接口
 */
class MultilistController extends Buddha_App_Action
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
     * 需求
     */
    public function demandmore(){
        $host = Buddha::$buddha_array['host'];//https安全连接
        if(Buddha_Http_Input::checkParameter(array('b_display','api_number'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $DemandObj = new Demand();
        $ShopObj = new Shop();
        $RegionObj = new Region();
        $DemandcatObj = new Demandcat();
        $api_number = Buddha_Http_Input::getParameter('api_number');
        $b_display = Buddha_Http_Input::getParameter('b_display');
        $view = Buddha_Http_Input::getParameter('view');
        $cid = Buddha_Http_Input::getParameter('cid');
        $lat = Buddha_Http_Input::getParameter('lat');
        $lng = Buddha_Http_Input::getParameter('lng');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $keyword = Buddha_Http_Input::getParameter('keyword');
        $locdata = $RegionObj->getApiLocationByNumberArr($api_number);
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):6;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);
        if($shop_id){
            $where = " shop_id='{$shop_id}' AND isdel=0 AND is_sure=1 AND buddhastatus=0 ORDER BY  add_time DESC ";

            $sql = "select count(*) as total from {$this->prefix}demand where {$where}";
            $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
        }else{
            if($keyword){
                $like = " AND name like '%{$keyword}%' OR keywords like '%{$keyword}%' ";
            }
            $where = "  isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']} ";
            $orderby = " group by shop_id  order by budget DESC ";
            /**
             * view=2 最新  view=3 热门   view=5 商家
             */
            if ($view) {
                switch ($view) {
                    case 2;
                        $orderby = " group by shop_id order by toptime,add_time DESC ";
                        break;
                    case 3;
                        $where .= " and is_hot=1 ";
                        break;
                    case 4;
                        $where .= " and shop_id!=0 ";
                        break;
                }
            }
            if($cid){
                /*$getcategory =$DemandcatObj->getcategory();
                $insql = $DemandcatObj->getInSqlByID($getcategory,$cid);*/
                $where .=" AND demandcat_id='{$cid}'";
            }
            $sql = "SELECT count(*) as total from (SELECT shop_id FROM {$this->prefix}demand WHERE {$where}  {$like}  {$orderby}) as t";
            $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
            $limit =  Buddha_Tool_Page::sqlLimit ( $page,$pagesize );
        }


        $Db_demand_arr=$DemandObj->getFiledValues(array('id as demand_id','demandcat_id','name','shop_id','demand_brief','budget','demand_thumb',
        'demand_start_time','demand_end_time'),$where . $like . $orderby . $limit);
        $MysqlplusObj = new Mysqlplus();
        $newtime=time();
        foreach($Db_demand_arr as $k => $v){
            $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc'),"id='{$v['shop_id']}'");
            $jingwei = $ShopObj->getSingleFiledValues(array('lat','lng','specticloc'),"id={$v['shop_id']}");
            $distance=$RegionObj->getDistance($lng,$lat,$jingwei['lng'],$jingwei['lat'],2);
            $Db_demand_arr[$k]['shopname'] = $Db_shop['name'];
            $cat_name = $MysqlplusObj->getCatNameByCatidStr($v['demandcat_id'],'demandcat');
            $Db_demand_arr[$k]['cat_name'] = $cat_name;
            $Db_demand_arr[$k]['distance'] = $distance;
            if(Buddha_Atom_String::isValidString($v['demand_thumb'])){
                $Db_demand_arr[$k]['demand_thumb'] = $host . $v['demand_thumb'];
            }else{
                $Db_demand_arr[$k]['demand_thumb'] = '';
            }

            if(!($v['demand_start_time']<$newtime AND $newtime<$v['demand_end_time'])){
                $data['buddhastatus']=0;
                $DemandObj->edit($data,$v['demand_id']);
            }

            unset($v['demand_start_time']);
            unset($v['demand_end_time']);

            $Db_demand_arr[$k]['specticloc'] = $Db_shop['specticloc'];
            $Db_demand_arr[$k]['icon_pay'] = $host . "style/images/Price.png";
            $Db_demand_arr[$k]['icon_shop'] = $host . "style/images/shopgray.png";
            $Db_demand_arr[$k]['icon_position'] = $host . "apiindex/menuplus/icon_position.png";
            $Db_demand_arr[$k]['services'] =  "multisingle.demandsingles";
            $Db_demand_arr[$k]['param'] = array('demand_id'=>$v['demand_id']);
            $Db_demand_arr[$k]['services'] =  "multisingle.demandsingles";
            $Db_demand_arr[$k]['para'] = array('b_display'=>$b_display,'webface_access_token'=>'buddhaaccesstoken',
                                               'demand_id'=>$v['demand_id'],'lat'=>$lat,'lng'=>$lng);

        }

        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        if($rcount){
            $jsondata['totalrecord'] = $rcount;
        }else{
            $jsondata['totalrecord'] = 0;
        }
        if($pcount){
            $jsondata['totalpage'] = $pcount;
        }else{
            $jsondata['totalpage'] = 0;
        }
        if(Buddha_Atom_Array::isValidArray($Db_demand_arr)){
            $jsondata['list'] = $Db_demand_arr;
        }else{
            $jsondata['list'] = array();
        }

        $jsondata['cat']=array(
            'services'=>'multilist.demandclassmore',
            'param'=>array(),
        );



        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'需求列表');

    }


    /**
     * 最新招聘
     */
    public function recruitarr(){
        $host = Buddha::$buddha_array['host'];//https安全连接
        if(Buddha_Http_Input::checkParameter(array('b_display','api_number'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $RecruitObj = new Recruit();
        $ShopObj = new Shop();
        $RegionObj = new Region();
        $CommonObj = new Common();
        $RecruitcatObj = new Recruitcat();
        $api_number = Buddha_Http_Input::getParameter('api_number');
        $b_display = Buddha_Http_Input::getParameter('b_display');
        $view = Buddha_Http_Input::getParameter('view');
        $cid = Buddha_Http_Input::getParameter('cid');
        $lat = Buddha_Http_Input::getParameter('lat');
        $lng = Buddha_Http_Input::getParameter('lng');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $keyword = Buddha_Http_Input::getParameter('keyword');
        $locdata = $RegionObj->getApiLocationByNumberArr($api_number);
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):6;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);
        if($shop_id){
            $where = " shop_id='{$shop_id}' AND isdel=0 AND is_sure=1 AND buddhastatus=0 ORDER BY  add_time DESC ";
            $sql = "select count(*) as total from {$this->prefix}recruit where {$where}";
            $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
        }else{
            if($keyword){
                $like = " AND recruit_name like '%{$keyword}%' OR treatment like '%{$keyword}%' ";
            }
            $where = " isdel=0 AND is_sure=1 AND buddhastatus=0 {$locdata['sql']}  ";
            $orderby = " group by shop_id  order by add_time DESC";
            /**
             * view=2 最新  view=3 热门   view=4 薪酬
             */

            if ($view) {
                switch ($view) {
                    case 2;
                        $orderby = ' ORDER BY toptime,add_time DESC';
                        break;
                    case 3;
                        $orderby = ' ORDER BY click_count DESC ';
                        break;
                    case 4;
                        $orderby = ' GROUP BY shop_id order BY pay ASC';
                        break;
                    case 5;
                        $where .= ' AND shop_id!=0';
                        break;
                }
            }
            /*if($view==4){
                $orderby =$RecruitObj->getPayConditionStr();
            }elseif($view==3){
                $orderby =$RecruitObj->getClickCountConditionStr();
            }elseif($view==2){
                $orderby = $RecruitObj->getAddTimeConditionStr();
            }*/
            if($cid){
                /*$getcategory =$RecruitcatObj->getcategory();
                $insql = $RecruitcatObj->getInSqlByID($getcategory,$cid);*/
                $where .=" AND recruit_id='{$cid}'";
            }
            $sql = "SELECT count(*) as total from (SELECT shop_id FROM {$this->prefix}recruit WHERE {$where}  {$like}  {$orderby}) as t";
            $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
            $limit =  Buddha_Tool_Page::sqlLimit ( $page,$pagesize );
        }


        $Db_recruit_arr=$RecruitObj->getFiledValues(array('id as recruit_id','recruit_id as cat_id','treatment','number',
            'shop_id','recruit_name','pay','contacts','tel','recruit_start_time','recruit_end_time'),
            $where . $like . $orderby . $limit);
        $MysqlplusObj = new Mysqlplus();
        foreach($Db_recruit_arr as $k => $v){
            $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc'),"id='{$v['shop_id']}'");
            $jingwei = $ShopObj->getSingleFiledValues(array('lat','lng','specticloc'),"id={$v['shop_id']}");
            $distance=$RegionObj->getDistance($lng,$lat,$jingwei['lng'],$jingwei['lat'],2);
            $Db_recruit_arr[$k]['shopname'] = $Db_shop['name'];
            $cat_name = $MysqlplusObj->getCatNameByCatidStr($v['cat_id'],'recruitcat');
            $Db_recruit_arr[$k]['cat_name'] = $cat_name;
            $Db_recruit_arr[$k]['distance'] = $distance;
            $Db_recruit_arr[$k]['specticloc'] = $Db_shop['specticloc'];
            $Db_recruit_arr[$k]['api_starttime'] = $CommonObj->getDateStrOfTime($v['recruit_start_time'],0,1) ;
            $Db_recruit_arr[$k]['api_endtime'] = $CommonObj->getDateStrOfTime($v['recruit_end_time'],0,1) ;
            $Db_recruit_arr[$k]['icon_pay'] = $host . "style/images/Price.png";
            $Db_recruit_arr[$k]['icon_shop'] = $host . "style/images/shopgray.png";
            $Db_recruit_arr[$k]['icon_position'] = $host . "apiindex/menuplus/icon_position.png";
            $Db_recruit_arr[$k]['services'] =  "multisingle.recruitsingles";
            $Db_recruit_arr[$k]['param'] = array('recruit_id'=>$v['recruit_id']);




            /*是否过期：过期就要下架*/
            $newtime=time();
            if(!($v['recruit_start_time']<$newtime AND $newtime<$v['recruit_end_time'])){
                $data['buddhastatus']=1;
                $RecruitObj->edit($data,$v['recruit_id']);
            }

            unset($v['start_date']);
            unset($v['end_date']);

        }

        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        if($rcount){
            $jsondata['totalrecord'] = $rcount;
        }else{
            $jsondata['totalrecord'] = 0;
        }
        if($pcount){
            $jsondata['totalpage'] = $pcount;
        }else{
            $jsondata['totalpage'] = 0;
        }
        if(Buddha_Atom_Array::isValidArray($Db_recruit_arr)){
            $jsondata['list'] = $Db_recruit_arr;
        }else{
            $jsondata['list'] = array();
        }

        $jsondata['cat']=array(
            'services'=>'multilist.recruitclassmore',
            'param'=>array(),
        );

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'招聘列表');

    }

    /**
     * 供应
     * author sys
     */
    public function supplymore(){
        $host = Buddha::$buddha_array['host'];//https安全连接
        if(Buddha_Http_Input::checkParameter(array('b_display','api_number'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $SupplyObj = new Supply();
        $ShopObj = new Shop();
        $RegionObj = new Region();
        $SupplycatObj = new Supplycat();
        $CommonObj = new Common();

        $api_number = Buddha_Http_Input::getParameter('api_number');
        $b_display = Buddha_Http_Input::getParameter('b_display');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $view = Buddha_Http_Input::getParameter('view');
        $cid = Buddha_Http_Input::getParameter('cid');
        $lat = Buddha_Http_Input::getParameter('lat');
        $lng = Buddha_Http_Input::getParameter('lng');
        $keyword = Buddha_Http_Input::getParameter('keyword');
        $locdata = $RegionObj->getApiLocationByNumberArr($api_number);
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):6;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);
        if($shop_id){
            $where = " shop_id='{$shop_id}' AND isdel=0 AND is_sure=1 AND buddhastatus=0 AND is_promote='0' ORDER BY add_time DESC ";
            $sql = "select count(*) as total from {$this->prefix}supply where {$where}";
            $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
        }else{
            if($keyword){
                $like = " AND goods_name like '%{$keyword}%' OR keywords like '%{$keyword}%' OR goods_brief like '%{$keyword}%' ";
            }
            $where = " isdel=0 AND is_sure=1 AND buddhastatus=0 {$locdata['sql']} ";
            $orderby = " group by shop_id  order by toptime,add_time DESC ";
            /**
             * view=3 最新  view=3 热门   view=4 促销
             */
            if($view==4){
                $where .= " and is_promote=1";//促销
            }elseif($view==3){
                $where .= " and is_hot=1";
            }elseif($view==2){
                //$orderby = ;
            }
            //select count(*) as total from b2b_supply where isdel=0 AND is_sure=1 AND buddhastatus=0 AND level3='962' AND is_promote='0'
            if($cid){
                /*$getcategory =$SupplycatObj->getcategory();
                $insql = $SupplycatObj->getInSqlByID($getcategory,$cid);*/
                $where .=" AND supplycat_id='{$cid}'";
            }
            $sql = "SELECT count(*) as total from (SELECT shop_id FROM {$this->prefix}supply WHERE {$where}  {$like}  {$orderby}) as t";
            $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
            $limit =  Buddha_Tool_Page::sqlLimit ( $page,$pagesize );
        }


        $filedarr=array('id as supply_id','supplycat_id','shop_id','goods_name','market_price','promote_price','is_promote','promote_start_date','promote_end_date');

            if($b_display==2){
                array_push($filedarr,'goods_thumb as goods_thumb');
            }elseif($b_display==1){
                array_push($filedarr,'goods_img as goods_thumb');
            }

        $Db_supply_promote_arr=$SupplyObj->getFiledValues($filedarr,$where . $like . $orderby . $limit);
        $MysqlplusObj = new Mysqlplus();
        foreach($Db_supply_promote_arr as $k => $v){
            $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc'),"id='{$v['shop_id']}'");
            $jingwei = $ShopObj->getSingleFiledValues(array('lat','lng','specticloc'),"id={$v['shop_id']}");
            $distance=$RegionObj->getDistance($lng,$lat,$jingwei['lng'],$jingwei['lat'],2);
            $Db_supply_promote_arr[$k]['shopname'] = $Db_shop['name'];
            $cat_name = $MysqlplusObj->getCatNameByCatidStr($v['supplycat_id'],'supplycat');
            $Db_supply_promote_arr[$k]['cat_name'] = $cat_name;
            if($v['goods_thumb']){
                $Db_supply_promote_arr[$k]['goods_thumb'] = $host.$v['goods_thumb'];
            }else{
                $Db_supply_promote_arr[$k]['goods_thumb'] = '';
            }

            /*是否过期：过期就要非促销*/
            $newtime=time();
            if(!($v['promote_start_date']<$newtime AND $newtime<$v['promote_end_date'])){
                $data['is_promote']=0;
                $SupplyObj->edit($data,$v['supply_id']);
            }


            $Db_supply_promote_arr[$k]['distance'] = $distance;
            $Db_supply_promote_arr[$k]['api_promotestartdate'] = $CommonObj->getDateStrOfTime($v['promote_start_date'],0,1) ;
            $Db_supply_promote_arr[$k]['api_promoteenddate'] =$CommonObj->getDateStrOfTime($v['promote_end_date'],0,1);

            $Db_supply_promote_arr[$k]['specticloc'] = $Db_shop['specticloc'];;
            $Db_supply_promote_arr[$k]['icon_position'] = $host . "apiindex/menuplus/icon_position.png";
            $Db_supply_promote_arr[$k]['icon_price'] = $host.'style/images/Price.png';
            $Db_supply_promote_arr[$k]['icon_shop'] = $host.'style/images/shopgray.png';

            $Db_supply_promote_arr[$k]['services'] =  "multisingle.supplysingle";
            $Db_supply_promote_arr[$k]['param'] = array(
                'supply_id'=>$v['supply_id']);
        }

        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        if($rcount){
            $jsondata['totalrecord'] = $rcount;
        }else{
            $jsondata['totalrecord'] = 0;
        }
        if($pcount){
            $jsondata['totalpage'] = $pcount;
        }else{
            $jsondata['totalpage'] = 0;
        }
        if(Buddha_Atom_Array::isValidArray($Db_supply_promote_arr)){
            $jsondata['list'] = $Db_supply_promote_arr;
        }else{
            $jsondata['list'] = array();
        }
        $jsondata['cat'] = array(
            'Services'=>'multilist.supplyclassmore',
            'param'=>array(),
        );

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'供应列表');
    }

    /**
     * 促销
     */
    public function promotionsarr(){
        $host = Buddha::$buddha_array['host'];//https安全连接
        if(Buddha_Http_Input::checkParameter(array('b_display'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $SupplyObj = new Supply();
        $ShopObj = new Shop();
        $RegionObj = new Region();
        $CommonObj = new Common();

        $api_number = Buddha_Http_Input::getParameter('api_number');
        $b_display = Buddha_Http_Input::getParameter('b_display');
        $cid = Buddha_Http_Input::getParameter('cid');
        $lat = Buddha_Http_Input::getParameter('lat');
        $lng = Buddha_Http_Input::getParameter('lng');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $keyword = Buddha_Http_Input::getParameter('keyword');
        $locdata = $RegionObj->getApiLocationByNumberArr($api_number);
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):6;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);
        if($shop_id){
            $where = " shop_id='{$shop_id}' AND isdel=0 AND is_sure=1 AND buddhastatus=0 AND is_promote = 1  ORDER BY  toptime,add_time DESC ";
            $sql = '';
            $sql = "select count(*) as total from {$this->prefix}supply where {$where}";
            $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
        }else{
            if($keyword){
                $like = " AND (goods_name like '%{$keyword}%' OR keywords like '%{$keyword}%') ";
            }

            $where = " isdel=0 AND is_sure=1 AND buddhastatus=0 {$locdata['sql']} AND is_promote = 1 ";
            if($cid){
                $where .=" AND supplycat_id='{$cid}'";
            }
            $orderby = " GROUP BY shop_id ORDER BY  toptime,add_time DESC ";
            $sql = " SELECT count(*) as total from (SELECT shop_id FROM {$this->prefix}supply WHERE {$where}  {$like}  {$orderby}) as t" ;
            $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
            $limit =  Buddha_Tool_Page::sqlLimit ( $page,$pagesize );
        }

        $filedarr=array('id as promote_id','supplycat_id','goods_brief','shop_id','goods_name','market_price','promote_price','is_promote','promote_start_date','promote_end_date');

        if($b_display==2)
        {
            array_push($filedarr,'goods_thumb as goods_thumb');
        }elseif($b_display==1){
            array_push($filedarr,'goods_img as goods_thumb');
        }

        $Db_supply_promote_arr = $SupplyObj->getFiledValues($filedarr,$where . $like . $orderby . $limit);
        $MysqlplusObj = new Mysqlplus();
        foreach($Db_supply_promote_arr as $k => $v){
            if(mb_strlen($v['goods_brief']) > 35){
                $v['goods_brief'] = mb_substr($v['goods_brief'],0,35) . '...';
            }
            if(mb_strlen($v['goods_name']) > 18){
                $v['goods_name'] = mb_substr($v['goods_name'],0,18) . '...';
            }
            $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc'),"id='{$v['shop_id']}'");
            $jingwei = $ShopObj->getSingleFiledValues(array('lat','lng','specticloc'),"id={$v['shop_id']}");
            $distance=$RegionObj->getDistance($lng,$lat,$jingwei['lng'],$jingwei['lat'],2);
            $Db_supply_promote_arr[$k]['shopname'] = $Db_shop['name'];
            $Db_supply_promote_arr[$k]['specticloc'] = $Db_shop['specticloc'];
            $cat_name = $MysqlplusObj->getCatNameByCatidStr($v['supplycat_id'],'supplycat');
            $Db_supply_promote_arr[$k]['cat_name'] = $cat_name;
            $Db_supply_promote_arr[$k]['goods_thumb'] = $host.$v['goods_thumb'];
            $Db_supply_promote_arr[$k]['distance'] = $distance;
            $Db_supply_promote_arr[$k]['goods_name'] = $v['goods_name'];
            $Db_supply_promote_arr[$k]['icon_pay'] = $host . "style/images/Price.png";
            $Db_supply_promote_arr[$k]['icon_position'] = $host . "apiindex/menuplus/icon_position.png";
            $Db_supply_promote_arr[$k]['services'] =  "multisingle.promotionsingle";
            $Db_supply_promote_arr[$k]['param'] = array('promote_id'=>$v['promote_id']);

            /*是否过期：过期就要非促销*/
            $newtime=time();
            if(!($v['promote_start_date']<$newtime AND $newtime<$v['promote_end_date']))
            {
                $data['is_promote']=0;
                $SupplyObj->edit($data,$v['supply_id']);
            }

        }
        /*$jsondata = array();
        $tablewhere = $this->prefix . 'supply';
        $temp_Common = $CommonObj->pagination($tablewhere, $where.$like, $pagesize, $page);
        $jsondata['page'] = $temp_Common['page'];
        $jsondata['pagesize'] = $temp_Common['pagesize'];
        $jsondata['totalrecord'] = $temp_Common['totalrecord'];
        $jsondata['totalpage'] = $temp_Common['totalpage'];*/



        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;

        if($rcount){
            $jsondata['totalrecord'] = $rcount;
        }else{
            $jsondata['totalrecord'] = 0;
        }
        if($pcount){
            $jsondata['totalpage'] = $pcount;
        }else{
            $jsondata['totalpage'] = 0;
        }

        if(Buddha_Atom_Array::isValidArray($Db_supply_promote_arr)){
            $jsondata['list'] = $Db_supply_promote_arr;
        }else{
            $jsondata['list'] = array();
        }


        $jsondata['cat'] = array(
            'Services'=>'multilist.supplyclassmore',
            'param'=>array(),
        );
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'促销列表');
    }

    /**
     * 店铺之一分营销
     * @author wph 2017-12-25
     */
    public function heartproarr(){
        $host = Buddha::$buddha_array['host'];//https安全连接
        if(Buddha_Http_Input::checkParameter(array('b_display'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }

        $UserObj = new User();
        $ShopObj = new Shop();

        $shop_id = $id=(int)Buddha_Http_Input::getParameter('shop_id');
        $newtime = Buddha::$buddha_array['buddha_timestamp'];
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize')?(int)Buddha_Http_Input::getParameter('pagesize') : 15;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        $where = " isdel=0 and is_sure=1 and  buddhastatus=0 and shop_id='{$shop_id}' AND ( onshelftime < $newtime AND $newtime < offshelftime )";

        $fields = array('id', 'shop_id','user_id', 'name','price', 'small as demand_thumb');
        $orderby = " order by toptime,createtime DESC ";



            $sql = "select count(*) as total from {$this->prefix}heartpro where {$where}";
            $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }


        $list = $this->db->getFiledValues($fields,$this->prefix.'heartpro', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize));

        $lease = array();
        foreach($list as $k=>$v)
        {
            if($v['shop_id']!='0'){
                $Db_shop = $ShopObj->getSingleFiledValues(array('name','specticloc','lng','lat'),"id='{$v['shop_id']}'");
                $name = $Db_shop['name'];
                if($Db_shop['roadfullname']=='0'){
                    $roadfullname = '';
                }else{
                    $roadfullname = $Db_shop['specticloc'];
                }
            }else{
                $Db_user = $UserObj->getSingleFiledValues(array('username','realname','address'),"id='{$v['user_id']}'");
                if($Db_user['address']=='0'){
                    $roadfullname = '' ;
                }else{
                    $roadfullname = $Db_user['address'];
                }
                if($Db_user['realname']=='0'){
                    $name = $Db_user['username'];
                }else{
                    $name = $Db_user['realname'];
                }
            }

            $lease[] = array(
                'heartpro_id'=>$v['id'],
                'name'=>$v['name'],
                'price'=>$v['price'],
                'shop_name'=>$name,

                'icon_price'=>Buddha_Atom_String::getApiFileUrlStr('apishop/menuplus/icon_price.png'),
                'icon_shop'=>Buddha_Atom_String::getApiFileUrlStr('apishop/menuplus/icon_shop.png'),

                'roadfullname'=>$roadfullname,
                'img'=> Buddha_Atom_String::getApiFileUrlStr($v['demand_thumb'])
            );
        }


        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        if($rcount){
            $jsondata['totalrecord'] = $rcount;
        }else{
            $jsondata['totalrecord'] = 0;
        }
        if($pcount){
            $jsondata['totalpage'] = $pcount;
        }else{
            $jsondata['totalpage'] = 0;
        }
        if(Buddha_Atom_Array::isValidArray($lease)){
            $jsondata['list'] = $lease;
        }else{
            $jsondata['list'] = array();
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'店铺之分营销列表');
    }

    public  function codesalesarr(){
        $host = Buddha::$buddha_array['host'];//https安全连接
        if(Buddha_Http_Input::checkParameter(array('b_display'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }

        $UserObj = new User();
        $ShopObj = new Shop();

        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id');

        $shopinfo = $ShopObj->fetch($shop_id);

        //店铺一码营销
        $ShopObj->createQrcodeForCodeSales($shop_id,$shopinfo['small'],$shopinfo['name'],$event='shop',$eventpage='info');
        $shop_user_id = $shopinfo['user_id']; //店铺拥有者ID

        $where = " isdel=0 and is_sure=1 and state=0 and id={$shop_id}";
        $view1='shop';
        $fields = '';
        $orderby = " order by createtime DESC ";

        $Db_Shop = $ShopObj->getSingleFiledValues(array('codeimg'),$where);

        $jsondata = array();
        $jsondata['codeimg'] = Buddha_Atom_String::getApiFileUrlStr($Db_Shop['codeimg']);
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'店铺之一码营销');
    }
    /**
     * 租赁
     */
    public function leasearr(){
        $host = Buddha::$buddha_array['host'];//https安全连接
        if(Buddha_Http_Input::checkParameter(array('b_display'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $LeaseObj = new Lease();
        $ShopObj = new Shop();
        $RegionObj = new Region();

        $api_number = Buddha_Http_Input::getParameter('api_number');
        $b_display = Buddha_Http_Input::getParameter('b_display');
        $cid = Buddha_Http_Input::getParameter('cid');
        $lat = Buddha_Http_Input::getParameter('lat');
        $lng = Buddha_Http_Input::getParameter('lng');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');
        $keyword = Buddha_Http_Input::getParameter('keyword');
        $view = Buddha_Http_Input::getParameter('view');
        $locdata = $RegionObj->getApiLocationByNumberArr($api_number);
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):6;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);
        if($shop_id){
            $where = " shop_id='{$shop_id}' AND isdel=0 AND is_sure=1 AND buddhastatus=0 ORDER BY  add_time DESC ";
            $sql = "select count(*) as total from {$this->prefix}lease where {$where}";
            $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
        }else{
            if($keyword){
                $like = " AND lease_name like '%{$keyword}%' OR keywords like '%{$keyword}%' ";
            }
            $where = " isdel=0 AND is_sure=1 AND buddhastatus=0 {$locdata['sql']} ";
            $orderby = "  group by shop_id  ORDER BY  toptime,add_time DESC ";
            /**
             * view=2 最新  view=3 预算   view=4 商家
             */
            if($cid){
                $where .=" AND leasecat_id='{$cid}'";
            }
            if ($view) {
                switch ($view) {
                    case 2;
                        $orderby = " ORDER BY  toptime,add_time DESC ";
                        break;
                    case 3;
                        $orderby = "order by rent ASC";
                        break;
                    case 4;
                        $where .= " and shop_id!=0";
                        break;
                }
            }
            $sql = "SELECT count(*) as total from (SELECT shop_id FROM {$this->prefix}lease WHERE {$where}  {$like}  {$orderby}) as t";
            $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
            $limit =  Buddha_Tool_Page::sqlLimit ( $page,$pagesize );
        }

        $filedarr=(array('id as lease_id','leasecat_id','shop_id','lease_name','rent'));
        $imgfiled='lease';
        if($b_display==2){
            array_push($filedarr,$imgfiled.'_thumb as lease_thumb');
        }elseif($b_display==1){
            array_push($filedarr,$imgfiled.'_img as lease_thumb');
        }
        $Db_lease_arr=$LeaseObj->getFiledValues($filedarr,$where . $like . $orderby . $limit);
        $MysqlplusObj = new Mysqlplus();
        foreach($Db_lease_arr as $k => $v){
            $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc'),"id='{$v['shop_id']}'");
            $jingwei = $ShopObj->getSingleFiledValues(array('lat','lng','specticloc'),"id={$v['shop_id']}");
            $distance=$RegionObj->getDistance($lng,$lat,$jingwei['lng'],$jingwei['lat'],2);
            $Db_lease_arr[$k]['shopname'] = $Db_shop['name'];
            $cat_name = $MysqlplusObj->getCatNameByCatidStr($v['leasecat_id'],'leasecat');
            $Db_lease_arr[$k]['cat_name'] = $cat_name;
            $Db_lease_arr[$k]['lease_thumb'] = $host.$v['lease_thumb'];
            $Db_lease_arr[$k]['distance'] = $distance;
            $Db_lease_arr[$k]['icon_pay'] = $host . "style/images/Price.png";
            $Db_lease_arr[$k]['icon_shop'] = $host . "style/images/shopgray.png";
            $Db_lease_arr[$k]['services'] =  "multisingle.leasesingle";
            $Db_lease_arr[$k]['param'] = array('lease_id'=>$v['lease_id']);
        }

        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        if($rcount){
            $jsondata['totalrecord'] = $rcount;
        }else{
            $jsondata['totalrecord'] = 0;
        }
        if($pcount){
            $jsondata['totalpage'] = $pcount;
        }else{
            $jsondata['totalpage'] = 0;
        }
        if(Buddha_Atom_Array::isValidArray($Db_lease_arr)){
            $jsondata['list'] = $Db_lease_arr;
        }else{
            $jsondata['list'] = array();
        }
        $jsondata['cat'] = array(
            'Services'=>'multilist.leaseclassmore',
            'param'=>array(),
        );


        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'租赁列表');
    }

    /**
     * 需求分类
     */
    public function demandclassmore(){
        if(Buddha_Http_Input::checkParameter(array('webface_access_token'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $DemandcatObj=new Demandcat();
        $fields = array('id as demandcat_id','cat_name');
        $Db_demandcatlist = $DemandcatObj->getFiledValues($fields," ifopen='0' AND isdel='0' ");
        foreach($Db_demandcatlist as $k => $v){
            $Db_demandcatlist[$k]['services'] = "multilist.demandmore";
            $Db_demandcatlist[$k]['param'] = "cat_id={$v['demandcat_id']}";
        }
        $jsondata = array();
        $jsondata = $Db_demandcatlist;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'需求分类');
    }
    /**
     * 招聘分类
     */
    public function recruitclassmore(){
        if(Buddha_Http_Input::checkParameter(array('webface_access_token'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $RecruitcatObj=new Recruitcat();
        $fields = array('id as recruitcat_id','cat_name');
        $Db_recruitcatlist = $RecruitcatObj->getFiledValues($fields," ifopen='0' AND isdel='0' ");
        foreach($Db_recruitcatlist as $k => $v){
            $Db_recruitcatlist[$k]['services'] = "multilist.recruitarr";
            $Db_recruitcatlist[$k]['param'] = "cat_id={$v['recruitcat_id']}";
        }
        $jsondata = array();
        $jsondata = $Db_recruitcatlist;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'招聘分类');
    }
    /**
     * 促销分类
     */
    public function promotionsclassmore(){
        if(Buddha_Http_Input::checkParameter(array('webface_access_token'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $SupplycatObj=new Supplycat();
        $fields = array('id as promotionscat_id','cat_name');
        $Db_promotionscatlist = $SupplycatObj->getFiledValues($fields," ifopen='0' AND isdel='0' ");
        foreach($Db_promotionscatlist as $k => $v){
            $Db_promotionscatlist[$k]['services'] = "multilist.promotionsarr";
            $Db_promotionscatlist[$k]['param'] = "cat_id={$v['promotionscat_id']}";
        }
        $jsondata = array();
        $jsondata = $Db_promotionscatlist;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'促销分类');
    }

    /**
     * 供应分类
     */
    public function supplyclassmore(){
        if(Buddha_Http_Input::checkParameter(array('webface_access_token'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $SupplycatObj=new Supplycat();
        $fields = array('id as supply_id','cat_name');
        $Db_supplycatlist = $SupplycatObj->getFiledValues($fields," ifopen='0' AND isdel='0' ");
        foreach($Db_supplycatlist as $k => $v){
            $Db_supplycatlist[$k]['services'] = "multilist.supplymore";
            $Db_supplycatlist[$k]['param'] = "cat_id={$v['supply_id']}";
        }
        $jsondata = array();
        $jsondata = $Db_supplycatlist;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'供应分类');
    }
    /**
     * 租赁分类
     */
    public function leaseclassmore(){
        if(Buddha_Http_Input::checkParameter(array('webface_access_token'))){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444001,'必填信息没填写');
        }
        $LeasecatObj=new Leasecat();
        $fields = array('id as lease_id','cat_name');
        $Db_leasecatlist = $LeasecatObj->getFiledValues($fields," ifopen='0' AND isdel='0' ");
        foreach($Db_leasecatlist as $k => $v){
            $Db_leasecatlist[$k]['services'] = "multilist.leasearr";
            $Db_leasecatlist[$k]['param'] = "cat_id={$v['lease_id']}";
        }
        $jsondata = array();
        $jsondata = $Db_leasecatlist;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'租赁列表');
    }

}