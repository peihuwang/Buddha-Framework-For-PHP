 <?php

/**
 * Class ShopController
 */
class ShopController extends Buddha_App_Action
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
        $this->tablenamestr='店铺';
        $this->tablename='shop';
    }


    /**
     * 店铺互推送
     */
    public function shoppusheachother(){
        if (Buddha_Http_Input::checkParameter(array('shop_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $ShopObj = new Shop();

        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id');
        $UserObj = new User();

        if(!$ShopObj->isExistShop($shop_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '此店铺不存在');
        }
        /*shop_id=3479*/
        //商家推荐
        $shoptwo = array();
        $Db_Shop = $ShopObj->getSingleFiledValues(array('user_id')," id ='{$shop_id}' ");
        if($Db_Shop['user_id']){
            $recommended = $UserObj->getSingleFiledValues(array('recommended'),"id='{$Db_Shop['user_id']}'");
            if(!empty($recommended['recommended'])){
                $shoptwo = $ShopObj->getFiledValues(array('id as shop_id','name','small as img')," id in ({$recommended['recommended']}) ");

                if(Buddha_Atom_Array::isValidArray($shoptwo)){
                    foreach($shoptwo as $k=>$v){
                        $shoptwo[$k]['img'] = Buddha_Atom_String::getApiFileUrlStr($v['img']);
                    }
                }

            }

        }


        $jsondata = $shoptwo;
        $jsondata['ishasdata'] = count($shoptwo)?1:0;
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'店铺互推（根据shop_id）');

    }
    /**
     *   推荐
     */
   public function pagemore(){

       $host=Buddha::$buddha_array['host'];

       if (Buddha_Http_Input::checkParameter(array('shop_id'))) {
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
       }

       $ShopObj = new Shop();

       $shop_id = (int)Buddha_Http_Input::getParameter('shop_id');


       if(!$ShopObj->isExistShop($shop_id)){
           Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '此店铺不存在');
       }

       /*shop_id=6843*/
       $CommonObj = new Common();
       $recommend = $CommonObj->recommendBelongShop($shop_id,'','');
       $pagemore = array();

      //推荐需求
       $demand = $recommend['demand'];
       $demand['ishasdata'] = count($demand['more'])?1:0;
       $demand['name'] = $demand['headettitle'];
       if(count($demand['more'])){
           $shop_id = $demand['more'][0]['shop_id'];
       }else{
           $shop_id = 0;
       }

       $demand['button'] =array(
           'Services'=>'multilist.demandmore','param'=>array('shop_id'=>$shop_id));
       unset($demand['headettitle']);
       $demand['list'] =$demand['more'];
       if(Buddha_Atom_Array::isValidArray($demand['list'])){
           foreach($demand['list'] as $k=>$v){

               $demand['list'][$k]['type'] = 0;
               $demand['list'][$k]['brief'] = Buddha_Atom_String::getApiValidStr($v['brief']);
               $demand['list'][$k]['main_id'] = $v['demand_id'];

               $demand['list'][$k]['button'] =array(
                   'Services'=>'multisingle.demandsingle','param'=>array('demand_id'=>$v['demand_id']));
               unset($demand['list'][$k]['demand_id'] );
           }
       }
       unset($demand['more']);
       $pagemore[] = $demand;


//供应

       $supply = $recommend['supply'];
       $supply['ishasdata'] = count($supply['more'])?1:0;
       $supply['name'] = $supply['headettitle'];
       if(count($supply['more'])){
           $shop_id = $supply['more'][0]['shop_id'];
       }else{
           $shop_id = 0;
       }

       $supply['button'] =array(
           'Services'=>'multilist.supplymore','param'=>array('shop_id'=>$shop_id));
       unset($supply['headettitle']);
       $supply['list'] =$supply['more'];
       if(Buddha_Atom_Array::isValidArray($supply['list'])){
           foreach($supply['list'] as $k=>$v){

               $supply['list'][$k]['type'] = 0;
               $supply['list'][$k]['brief'] = Buddha_Atom_String::getApiValidStr($v['brief']);
               $supply['list'][$k]['main_id'] = $v['supply_id'];

               $supply['list'][$k]['button'] =array(
                   'Services'=>'multisingle.supplysingle','param'=>array('supply_id'=>$v['supply_id']));
               unset($supply['list'][$k]['supply_id'] );
               unset($supply['list'][$k]['goods_brief'] );


           }
       }
       unset($supply['more']);
       $pagemore[] = $supply;

//活动
       $activity =$recommend['activity'];

       $activity['ishasdata'] = count($activity['more'])?1:0;
       $activity['name'] = $activity['headettitle'];
       if(count($activity['more'])){
           $shop_id = $activity['more'][0]['shop_id'];
       }else{
           $shop_id = 0;
       }

       $activity['button'] =array(
           'Services'=>'activity.more','param'=>array('shop_id'=>$shop_id));
       unset($activity['headettitle']);
       $activity['list'] =$supply['more'];
       if(Buddha_Atom_Array::isValidArray($activity['list'])){
           foreach($activity['list'] as $k=>$v)
           {
               $activity['list'][$k]['brief'] = Buddha_Atom_String::getApiValidStr($v['brief']);
               $activity['list'][$k]['main_id'] = $v['activity_id'];
               $activity['list'][$k]['price'] = $v['number'];
               $activity['list'][$k]['button'] =array(
                   'Services'=>'activity.view','param'=>array('activity_id'=>$v['activity_id']));
               unset($activity['list'][$k]['activity_id'] );

               unset($activity['list'][$k]['number'] );

           }
       }


       unset($activity['more']);
       $pagemore[] = $activity;

//1分购

       $heartpro = $recommend['heartpro'];

       $heartpro['ishasdata'] = count($heartpro['more'])?1:0;
       $heartpro['name'] = $heartpro['headettitle'];

       if(count($heartpro['more'])){
           $shop_id = $heartpro['more'][0]['shop_id'];
       }else{
           $shop_id = 0;
       }

       $heartpro['button'] =array(
           'Services'=>'heartpro.frontlist','param'=>array('shop_id'=>$shop_id));
       unset($heartpro['headettitle']);
       $heartpro['list'] =$heartpro['more'];
       if(Buddha_Atom_Array::isValidArray($heartpro['list'])){
           foreach($heartpro['list'] as $k=>$v){

               $heartpro['list'][$k]['type'] =0;
               $heartpro['list'][$k]['brief'] = Buddha_Atom_String::getApiValidStr($v['brief']);
               $heartpro['list'][$k]['main_id'] = $v['heartpro_id'];
               $heartpro['list'][$k]['price'] = $v['number'];
               $heartpro['list'][$k]['button'] =array(
                   'Services'=>'heartpro.info','param'=>array('heartpro_id'=>$v['heartpro_id']));
               unset($heartpro['list'][$k]['heartpro_id'] );

               unset($heartpro['list'][$k]['number'] );

           }
       }
       unset($heartpro['more']);
       $pagemore[] = $heartpro;

//租赁


       $lease =$recommend['lease'];

       $lease['ishasdata'] = count($lease['more'])?1:0;
       $lease['name'] = $lease['headettitle'];
       if(count($lease['more'])){
           $shop_id = $lease['more'][0]['shop_id'];
       }else{
           $shop_id = 0;
       }

       $lease['button'] =array(
           'Services'=>'multilist.leasearr','param'=>array('shop_id'=>$shop_id));
       unset($lease['headettitle']);
       $lease['list'] =$lease['more'];
       if(Buddha_Atom_Array::isValidArray($lease['list'])){
           foreach($lease['list'] as $k=>$v){

               $lease['list'][$k]['type'] =0;
               $lease['list'][$k]['brief'] = Buddha_Atom_String::getApiValidStr($v['brief']);
               $lease['list'][$k]['main_id'] = $v['lease_id'];
               $lease['list'][$k]['button'] =array(
                   'Services'=>'multisingle.leasesingle','param'=>array('lease_id'=>$v['lease_id']));
               unset($lease['list'][$k]['lease_id'] );



           }
       }
       unset($lease['more']);
       $pagemore[] = $lease;



//传单

       $singleinformation =$recommend['singleinformation'];

       $singleinformation['ishasdata'] = count($singleinformation['more'])?1:0;
       $singleinformation['name'] = $singleinformation['headettitle'];
       if(count($singleinformation['more'])){
           $shop_id = $singleinformation['more'][0]['shop_id'];
       }else{
           $shop_id = 0;
       }

       $singleinformation['button'] =array(
           'Services'=>'singleinformation.more','param'=>array('shop_id'=>$shop_id));
       unset($singleinformation['headettitle']);
       $singleinformation['list'] =$singleinformation['more'];
       if(Buddha_Atom_Array::isValidArray($singleinformation['list'])){
           foreach($singleinformation['list'] as $k=>$v){

               $singleinformation['list'][$k]['type'] =0;
               $singleinformation['list'][$k]['brief'] = Buddha_Atom_String::getApiValidStr($v['brief']);
               $singleinformation['list'][$k]['price'] =Buddha_Atom_String::getApiValidStr($v['number']);
               $singleinformation['list'][$k]['main_id'] = $v['singleinformation_id'];
               $singleinformation['list'][$k]['button'] =array(
                   'singleinformation.view','param'=>array('singleinformation_id'=>$v['singleinformation_id']));
               unset($singleinformation['list'][$k]['singleinformation_id'] );
               unset($singleinformation['list'][$k]['number'] );

           }
       }
       unset($singleinformation['more']);
       $pagemore[] = $singleinformation;


//招聘
       echo "<br><hr>";
       $recruit =$recommend['recruit'];

       $recruit['ishasdata'] = count($recruit['more'])?1:0;
       $recruit['name'] = $recruit['headettitle'];
       if(count($recruit['more'])){
           $shop_id = $recruit['more'][0]['shop_id'];
       }else{
           $shop_id = 0;
       }

       $recruit['button'] =array(
           'Services'=>'multilist.recruitarr','param'=>array('shop_id'=>$shop_id));
       unset($recruit['headettitle']);
       $recruit['list'] =$recruit['more'];
       if(Buddha_Atom_Array::isValidArray($recruit['list'])){
           foreach($recruit['list'] as $k=>$v){

               $recruit['list'][$k]['type'] =0;
               $recruit['list'][$k]['brief'] = Buddha_Atom_String::getApiValidStr($v['brief']);
               $recruit['list'][$k]['main_id'] = $v['recruit_id'];
               $recruit['list'][$k]['button'] =array(
                   'multisingle.recruitsingle','param'=>array('singleinformation_id'=>$v['recruit_id']));
               unset($recruit['list'][$k]['recruit_id'] );


           }
       }
       unset($recruit['more']);
       $pagemore[] = $recruit;



       Buddha_Http_Output::makeWebfaceJson($pagemore,'/webface/?Services='.$_REQUEST['Services'],0,'页面详情之更多列表（根据shop_id）');

   }

    /**
     * 有赏店铺
     */
    public function rewardforwarding(){
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('api_number','b_display')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        /*城市编号*/
        $RegionObj=new Region();
        $ShopObj=new Shop();
        $rechargeObj = new Recharge();

        /**is_indexheader 是否是首页的头部 点击过来的：0否；1是**/
        $is_indexheader = (int)Buddha_Http_Input::getParameter('is_indexheader')?(int)Buddha_Http_Input::getParameter('is_indexheader'):0;
        $api_number = (int)Buddha_Http_Input::getParameter('api_number')?(int)Buddha_Http_Input::getParameter('api_number'):0;
        if(!$RegionObj->isValidRegion($api_number))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444007, '地区编码不对（地区编码对应于腾讯位置adcode）');
        }

        $locdata = $RegionObj->getApiLocationByNumberArr($api_number);

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;
        $page = (int)Buddha_Http_Input::getParameter('page')?(int)Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];

        $pagesize = (int)Buddha_Atom_Secury::getMaxPageSize($pagesize);

        /*当前位置 纬度*/
        $lats = (int)Buddha_Http_Input::getParameter('lat')?Buddha_Http_Input::getParameter('lat'):0;

        /*当前位置 经度*/
        $lngs = (int)Buddha_Http_Input::getParameter('lng')?Buddha_Http_Input::getParameter('lng'):0;


        if($lats==0 and $lngs==0  and $api_number>0 ){
            $lats = $locdata['lat'];
            $lngs = $locdata['lng'];

        }




        $where = " isdel=0 and is_sure=1  and state=0  {$locdata['sql']}";
        $orderby = " order by createtime DESC ";

        $rechargeInfo = $rechargeObj->getFiledValues(array('shop_id'),"balance >= forwarding_money AND is_open=1");


        $shop_ids = Buddha_Atom_Array::getIdInStr($rechargeInfo);


        $where .= " and id IN ({$shop_ids})";


        $orderby = " ORDER BY id DESC ";
        $fields = array('id', 'name', 'brief', 'small','lat','lng','specticloc','storetype','shopcat_id');
        $list = $ShopObj->getFiledValues($fields,$where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ));

        $goodsNws = array();
        foreach($list as $k=>$v){
            $icon = '';
            $distance=$RegionObj->getdistance($locdata['lng'],$locdata['lat'],$v['lng'],$v['lat'],2);//根据经纬度计算距离
            if($rechargeObj->countRecords("shop_id={$v['id']}")){
                $icon = $host."/style/images/icon_reward.png";
            }
            $goodsNws[]=array(
                'shop_id'=>$v['id'],
                'name'=>$v['name'],
                'brief'=>$v['brief'],
                'img'=> Buddha_Atom_String::getApiFileUrlStr($v['small'])  ,
                'shopcat_id'=>$v['shopcat_id'],
                'specticloc'=>$v['specticloc'],
                'distance'=>$distance,
                'icon_shang'=>$icon
            );

        }


        $jsondata = $goodsNws;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "有赏店铺");


    }

    /**
     * 店铺管理：商家 店铺详情（商家只能查看没有被删除的详情）（已完成）
     */
    public function businessesmanageview()
    {
        $host=Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('b_display','usertoken','shop_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj=new Common();
        $UserObj=new User();
        $RegionObj= new Region();
        $ShopObj= new Shop();
        $ShopcatObj= new Shopcat();
        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','tel');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$UserObj->isHasMerchantPrivilege($user_id) ){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家角色！');
        }

        $api_shopid = (int)Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

         if(!$ShopObj->isShopByShopid($api_shopid,$user_id)){
             Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
         }


        /*只能不能查看已经删除的的详情*/
        $where=" id='{$api_shopid}' AND is_sure=1 AND isdel!=1 ";

        $fileds='id AS shop_id, name, realname,mobile,tel,lng,lat,level1,shopcat_id,
                level2,level3,level4,level5,specticloc,brief,opentime, qq,wechatnumber,is_verify,shopdesc ';
        if($b_display == 2){

            $fileds.=' , small AS img';

        }elseif($b_display==1){

            $fileds.=' , medium AS img';

        }

        $sql =" SELECT  {$fileds}  
                FROM {$this->prefix}shop WHERE {$where} ";

        $Db_Shop_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata =array();

        if(Buddha_Atom_Array::isValidArray($Db_Shop_arr)){

            $Db_Shop =  $Db_Shop_arr[0];



            $Db_Shop['img']=$host. $Db_Shop['img'];

            $Db_Shop['api_address']=$RegionObj->getDetailOfAdrressByRegionIdStr($Db_Shop['level1'] ,$Db_Shop['level2'],$Db_Shop['level3'],' ').$Db_Shop['specticloc'];



            $Db_Shop['api_shopcatname']=$ShopcatObj->getShopcatNameToShopcatid($Db_Shop['shopcat_id']);

            $Db_Shop['api_opentimestr']=$CommonObj->getDateStrOfTime($Db_Shop['opentime']);


            $jsondata['header']=array(
                0=>array(
                    'name'=>'促销',
                    'identify'=>'promotion',
                    'img'=>$host.'/apishop/menuplus/cuxiao.png',
                    'Services'=>'merchantcenter.releasesupply',
                    'param'=>array('shop_id'=>$Db_Shop['shop_id']),
                ),
                1=>array(
                    'name'=>'供应',
                    'identify'=>'supply',
                    'img'=>$host.'/apishop/menuplus/gongying.png',
                    'Services'=>'merchantcenter.releasesupply',
                    'param'=>array('shop_id'=>$Db_Shop['shop_id']),
                ),
                2=>array(
                    'name'=>'需求',
                    'identify'=>'demand',
                    'img'=>$host.'/apishop/menuplus/xuqiu.png',
                    'Services'=>'merchantcenter.releasedemand',
                    'param'=>array('shop_id'=>$Db_Shop['shop_id']),
                ),
                3=>array(
                    'name'=>'招聘',
                    'identify'=>'recruit',
                    'img'=>$host.'/apishop/menuplus/zhaopin.png',
                    'Services'=>'merchantcenter.releaserecruit',
                    'param'=>array('shop_id'=>$Db_Shop['shop_id']),
                ),
                4=>array(
                    'name'=>'租赁',
                    'identify'=>'recruit',
                    'img'=>$host.'/apishop/menuplus/zulin.png',
                    'Services'=>'merchantcenter.releaselease',
                    'param'=>array('shop_id'=>$Db_Shop['shop_id']),
                ),

            );


            $jsondata['sliding']=array(
                0=>array(
                    'name'=>'店铺简介',
                    'identify'=>'brief',
                    'content'=>$Db_Shop['brief'],
                    'img'=>$host.$Db_Shop['img'],
                ),
                1=>array(
                    'name'=>'联系我们',
                    'identify'=>'contactus',
                ),

                2=>array(
                    'name'=>'位置导航',
                    'identify'=>'map',
                    'Services'=>'shop.navigation',
                    'param'=>array('shop_id'=>$Db_Shop['shop_id']),
                )
            );
            if(Buddha_Atom_String::isValidString($Db_Shop['opentime'])){
                $Db_Shop['api_opentimestr']=$CommonObj->getDateStrOfTime($Db_Shop['opentime']);
                $Db_Shop['opentime']='';
            }


            if($Db_Shop['is_verify']==0){

                $Db_Shop['api_verifystr']='未认证';
                $Db_Shop['icon_verify']=$host.'apishop/menuplus/icon_unauthorized.png';

            }elseif($Db_Shop['is_verify']==1){

                $Db_Shop['api_verifystr']='已认证';
                $Db_Shop['icon_verify']=$host.'apishop/menuplus/ricon_certified.png';

            }

            $Db_Shop['api_phoneIcon']=$host.'apishop/menuplus/dianhuahuangse.png';


            unset($Db_Shop['level1']);
            unset($Db_Shop['level2']);
            unset($Db_Shop['level3']);
            unset($Db_Shop['level4']);
            unset($Db_Shop['level5']);
            unset($Db_Shop['specticloc']);

            unset($Db_Shop['toptimestr']);
            unset($Db_Shop['toptime']);
            unset($Db_Shop['lat']);
            unset($Db_Shop['lng']);

            $jsondata['list'] = $Db_Shop;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "{$this->tablenamestr}管理:商家{$this->tablenamestr}详情");
    }


    /**
     * 店铺管理：商家 店铺列表（已完成）
     */
    public function businessestmanagemore()
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

        if(!$UserObj->isHasMerchantPrivilege($user_id) ){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家角色！');
        }


        $api_keyword = Buddha_Http_Input::getParameter('api_keyword')?Buddha_Http_Input::getParameter('api_keyword'):'';


        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        $view = Buddha_Http_Input::getParameter('view')?Buddha_Http_Input::getParameter('view'):0;


        $where=" user_id='{$user_id}'";

        if(Buddha_Atom_String::isValidString($api_keyword)){

            $where.= Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($api_keyword,array('name','number'));

        }

        if($view){
            switch($view){
                case 2;
                    $where.=' AND isdel=0 AND is_sure=0 ';
                    break;
                case 3;
                    $where.=' AND isdel=0 AND is_sure=1 ';
                    break;
                case 4;
                    $where.=' AND isdel=0 AND is_sure=4 ';
                    break;
                case 5;
                    $where.=' AND isdel=4 AND state=1 ';
                    break;
            }
        }


        $fileds = ' id AS shop_id, name, number, createtime, is_verify, is_sure, state, user_id ';

        if($b_display==1){
            $fileds.=' , medium AS img ';
        }elseif($b_display==2){
            $fileds.=' , small AS  img ';
        }

        $orderby = " ORDER BY createtime DESC ";


        $sql =" SELECT  {$fileds}  
                FROM {$this->prefix}shop WHERE {$where} 
                {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        $Db_Shop = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);




        $jsondata = array();
        $tablewhere=$this->prefix.'shop';

        $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);


        $jsondata['page'] =  $temp_Common['page'];
        $jsondata['pagesize'] =  $temp_Common['pagesize'];
        $jsondata['totalrecord'] =  $temp_Common['totalrecord'];
        $jsondata['totalpage'] =  $temp_Common['totalpage'];

        $jsondata['list'] = array();



        if(Buddha_Atom_Array::isValidArray($Db_Shop)){


            $jsondata['nav'] = $ShopObj->getPersonalCenterHeaderMenu('shop.businessestmanagemore');

            foreach($Db_Shop as $k=>$v){

                if($v['is_verify']==1){
                    $Db_Shop[$k]['api_authenticatestateimg']=$host.'apistate/menuplus/yirenzheng.png';
                }else{
                    $Db_Shop[$k]['api_authenticatestateimg']=$host.'apistate/menuplus/weirenzheng.png';
                }

                if($v['is_sure']==0){

                    $Db_Shop[$k]['api_auditstateimg']=$host.'apistate/menuplus/weishenhe.png';

                }elseif($v['is_sure']==4){

                    $Db_Shop[$k]['api_auditstateimg']=$host.'apistate/menuplus/weitonguo.png';

                }elseif($v['is_sure']==1){

                    $Db_Shop[$k]['api_auditstateimg']=$host.'apistate/menuplus/yitonguo.png';

                }

                /*在店铺审核过了的情况下，才显示启用停用*/
                if($v['is_sure']==1){
                    if($v['state']==1){

                        $Db_Shop[$k]['api_usestatestr']='启 用';

                    }else{

                        $Db_Shop[$k]['api_usestatestr']='停 用';
                    }
                }

                /*在店铺审核过了的情况下，才显示启用停用*/
                if($v['is_sure']==1)
                {
                    $Db_Shop[$k]['stoporenabled']['Services'] = 'shop.businessEnableDisabledShop';
                    $Db_Shop[$k]['stoporenabled']['param'] = array('shop_id' => $v['shop_id'],'state'=>$v['state']);
                }

                if(Buddha_Atom_String::isValidString($v['img']))
                {

                    $Db_Shop[$k]['api_img']=$host.$v['img'];

                }else{

                    $Db_Shop[$k]['api_img']='';
                }

                unset($Db_Shop[$k]['img']);

                if(Buddha_Atom_String::isValidString($v['createtime'])){
                    $Db_Shop[$k]['api_timestr']=date('Y-m-d',$v['createtime']);
                }

                /*商家店铺详情 */
                $Db_Shop[$k]['shopview']=array(
                    'Services'=>'shop.businessesmanageview',
                    'param'=> array('shop_id' => $v['shop_id']),
                );

                /*商家店铺更新 */
                $Db_Shop[$k]['update']=array(
                    'Services'=>'shop.beforeupdate',
                    'param'=>array('shop_id'=>$v['shop_id']),
                );

            }

            $jsondata['list'] = $Db_Shop;

        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "{$this->tablenamestr}管理：商家{$this->tablenamestr}列表");
    }


    /**
     * 店铺管理：合伙人 店铺列表（合伙人只能查看自己录入的店铺列表并且代理商下架或删除店铺都不能查看）
     */
    public function partnermanageshopmore()
    {
        $host=Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('b_display','usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj=new Common();
        $UserObj=new User();
        $ShopObj= new Shop();
        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$UserObj->isHasPartnerPrivilege($user_id) ){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '你还未申请代理商角色！');
        }


        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        $api_keyword = Buddha_Http_Input::getParameter('$api_keyword')?Buddha_Http_Input::getParameter('$api_keyword'):'';


        $view = Buddha_Http_Input::getParameter('view')?Buddha_Http_Input::getParameter('view'):0;

        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        $where=" (isdel=0 or isdel=4) and referral_id='{$user_id}' ";


        if(Buddha_Atom_String::isValidString($api_keyword)){

            $where.= Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($api_keyword,array('name','number'));

        }

        /**  * type   0：全部 2：新添加 3：已通过 4：未通过 5:已停用?*/

            switch($view){
                case 2;
                    $where.=' AND isdel=0 AND is_sure=0 ';
                    break;
                case 3;
                    $where.=' AND isdel=0 AND is_sure=1 ';
                    break;
                case 4;
                    $where.=' AND isdel=0 AND is_sure=4 ';
                    break;
                case 5;
                    $where.=' AND isdel=4 AND state=1 ';
                    break;
            }



        $fileds = ' id AS shop_id, name, number, createtime, createtimestr, is_verify, is_sure, state  ';

        if($b_display==1){
            $fileds.=' , medium AS img ';
        }elseif($b_display==2){
            $fileds.=' , small AS  img ';
        }

        $orderby = " ORDER BY createtime DESC ";


        $sql =" SELECT  {$fileds}  
                FROM {$this->prefix}shop WHERE {$where} 
                {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        $Db_Shop= $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata['header'] =array();
        $jsondata['list'] =array();



        $tablewhere=$this->prefix.$this->tablename;

        $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);

        $jsondata['page'] = $temp_Common['page'];
        $jsondata['pagesize'] = $temp_Common['pagesize'];
        $jsondata['totalrecord'] = $temp_Common['totalrecord'];
        $jsondata['totalpage'] = $temp_Common['totalpage'];

        $jsondata['header'] = $ShopObj->getPersonalCenterHeaderMenu('shop.partnermanageshopmore');

        $jsondata['add']=array(
            'Services'=>'shop.beforeadd',
            'param'=>array(),
        );


        if(Buddha_Atom_Array::isValidArray($Db_Shop))
        {

            foreach($Db_Shop as $k=>$v ){

                $Db_Shop[$k]['api_img']=$host. $v['img'];
                if($v['is_verify']==1){
                    $Db_Shop[$k]['api_authenticatestateimg']=$host.'apistate/menuplus/yirenzheng.png';
                }else{
                    $Db_Shop[$k]['api_authenticatestateimg']=$host.'apistate/menuplus/weirenzheng.png';
                }

                if($v['is_sure']==0)
                {
                    $Db_Shop[$k]['api_auditstateimg']=$host.'apistate/menuplus/weishenhe.png';

                }elseif($v['is_sure']==4)
                {
                    $Db_Shop[$k]['api_auditstateimg']=$host.'apistate/menuplus/weitonguo.png';
                }elseif($v['is_sure']==1)
                {
                    $Db_Shop[$k]['api_auditstateimg']=$host.'apistate/menuplus/yitonguo.png';
                }
                unset( $Db_Shop[$k]['img']);

                $Db_Shop[$k]['update']=array(
                    'Services'=>'shop.partnerbeforeupdate',
                    'param'=>array('shop_id'=>$v['shop_id']),
                );
            }

            $jsondata['list'] = $Db_Shop;

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "{$this->tablenamestr}管理：合伙人{$this->tablenamestr}列表");


    }


    /**
     * 店铺管理：合伙人 店铺更新之前必须请求的信息:
     * 只能更新他添加的店铺并且代理商删除的店铺不能查看
     */
    public function partnerbeforeupdate()
    {

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','shop_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $ShopObj=new Shop();
        $UserObj=new User();
        $ShopcatObj=new Shopcat();
        $RegionObj=new Region();
        $CommonObj=new Common();
        $AlbumObj=new Album();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        if(!$UserObj->isHasPartnerPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000023, '你还未申请合伙人会员角色！');
        }
        /*判断该店铺是否属于该合伙人录入的*/

        if(!$ShopObj->countRecords("id='{$shop_id}' AND referral_id='$user_id' AND isdel!=1")){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000049, '该店铺不是该合伙人录入！');
        }

        $fields = 'id AS shop_id, name, is_verify, shopcat_id, mobile, 
                    realname, mobile, tel, number, 
                    level0, level1, level2, level3, specticloc, 
                    lng, lat, storetype, property, bushour, 
                    myrange, brief, shopdesc, opentime, qq, 
                    wechatnumber,codeimg ';

        if($b_display==1){
            $fields.=' ,medium AS img ';
        }elseif($b_display==2){
            $fields.=' , small AS img ';
        }

        /*只能更新合伙人添加的店铺  referral_id='$user_id'  并且代理商 删除的店铺不能更改isdel!=1 */
        $where=" id='{$shop_id}' AND referral_id='$user_id' AND isdel!=1";

        $sql ="select {$fields} FROM {$this->prefix}shop WHERE {$where} ";
        $Db_Shop_array = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $table_name='shop';

        $jsondata = array();
        if(Buddha_Atom_Array::isValidArray($Db_Shop_array)){

            $Db_Shop= $Db_Shop_array[0];
            if(!Buddha_Atom_String::isValidString($Db_Shop['codeimg'])){
                /**二维码*/
                //////////////////////
                $shopinfo['id']=$shop_id;
                $CommonObj->codeimg($shopinfo);
                /////////////////////////////
            }
            unset($Db_Shop['codeimg']);

            $nowtime=Buddha::$buddha_array['buddha_timestamp'];

            /**
             * 显示店铺认证的情况
             *  1、店铺未认证$Db_Shop['is_verify']==0
             *  2、店铺已认证  a:  但认证的开始时间小于当前时间
             *               b:   或认当前时间大于认证结束时间
             */
            if(($Db_Shop['is_verify']==0)||($Db_Shop['is_verify']==1&&($nowtime<$Db_Shop['veifytime']||$nowtime>$Db_Shop['veryfyendtime']))){

                /* 是否显示收费标识（即店铺认证）*/
                $hsk_is_shop_needverify =isset(Buddha::$buddha_array['cache']['config']['hsk_is_shop_needverify']) ?Buddha::$buddha_array['cache']['config']['hsk_is_shop_needverify']: 1;
            }elseif($Db_Shop['is_verify']==1){/*已经认证的店铺也不显示店铺认证的*/
                $hsk_is_shop_needverify =0;
            }

            $is_authenticationcode = 0;
            if($hsk_is_shop_needverify==1){
                $is_authenticationcode = 1;
            }

            $Db_Shop['api_opentimestr']= $CommonObj->getDateStrOfTime($Db_Shop['opentime'],1,1);
            $Db_Shop['img']=$host.$Db_Shop['img'];
            $Db_Shop['api_bushourtimestr']= $CommonObj->getDateStrOfTime($Db_Shop['bushour'],1,1);
            $jsondata['is_showsverify'] =$hsk_is_shop_needverify;
            $jsondata['is_authenticationcode'] =$is_authenticationcode;
            $jsondata['realname'] =Buddha_Atom_String::getApiValidStr($Db_User['realname']);
            $jsondata['mobile'] =Buddha_Atom_String::getApiValidStr($Db_User['mobile']);
            $jsondata['storetypelist'] = $ShopObj->getApiNatiureArr($Db_Shop['storetype']);

            $jsondata['api_shopcatname'] = $ShopcatObj->getShopcatNameToShopcatid($Db_Shop['shopcat_id']);

            $jsondata['provincecityname'] = $RegionObj->getDetailOfAdrressByRegionIdStr($Db_Shop['level1'],$Db_Shop['level2'],$Db_Shop['level3'],'>');

            $jsondata['shopclassification']['Services'] = 'shop.shopclassification';
            $jsondata['shopclassification']['param'] = array('api_islocalinfo'=>0);
            $jsondata['region']['Services'] = 'ajaxregion.getBelongFromFatherId';
            $jsondata['region']['param'] = array('father'=>1);
            $jsondata['shoplist'] = $Db_Shop;
            $jsondata['albumlist'] = $ShopObj->getApiShopAlbumArrByshopid($shop_id,$b_display,$table_name);



        }
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "{$this->tablenamestr}管理：合伙人{$this->tablenamestr}编辑之前的数据");
    }


    /**
     * 店铺管理：合伙人 更新(更新自己录入的店铺)
     * 只能更新他添加的店铺并且代理商删除的店铺不能查看
     */

    public function partnerupdate()
    {

        if (Buddha_Http_Input::checkParameter(array('usertoken','name','shopcat','realname','mobile','specticloc','Image','shop_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        $ShopObj=new Shop();
        $ShopcatObj=new Shopcat();
        $CommonObj=new Common();
        $RegionObj=new Region();
        $JsonimageObj = new Jsonimage();

        $shop_id = Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;


        /*验证是否是合伙人*/
        if(!$UserObj->isHasPartnerPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000023, '你还未申请合伙人角色！');

        }


        /*判断该店铺是否已经认证过了*/
        $Db_Shop=$ShopObj->getSingleFiledValues(array('is_verify','veifytime','veryfyendtime'),"id='{$shop_id}' AND isdel=0 OR isdel=5 ");
        $nowtime=Buddha::$buddha_array['buddha_timestamp'];


        /**
         * 显示店铺认证的情况
         *  1、店铺为认证$Db_Shop['is_verify']==0
         *  2、店铺已认证  a:  但认证的开始时间小于当前时间
         *               b:   或认当前时间大于认证结束时间
         *
         * 除去上述两种情况外：都是已经认证的情况：则不需要去判断是否要付店铺认证费  即：$hsk_is_shop_needverify=0
         */
        if(($Db_Shop['is_verify']==0)||($Db_Shop['is_verify']==1 && ($nowtime < $Db_Shop['veifytime'] || $nowtime > $Db_Shop['veryfyendtime']))){
            $hsk_is_shop_needverify =isset(Buddha::$buddha_array['cache']['config']['hsk_is_shop_needverify']) ?Buddha::$buddha_array['cache']['config']['hsk_is_shop_needverify']: 1;//是否收费标识
        }else{
            $hsk_is_shop_needverify=0;
        }

        $mobile=Buddha_Http_Input::getParameter('mobile');
        $storetype=Buddha_Http_Input::getParameter('storetype');
        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');
        $shopcat_id=Buddha_Http_Input::getParameter('shopcat');


        if(!$CommonObj->getMobilephoneiseffectiveBymobile($mobile)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000032, '手机号码不正确！');
        }

        if(!$RegionObj->isProvince($level1)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000001, '国家、省、市、区 中省的地区内码id不正确！');
        }

        if(!$RegionObj->isCity($level2)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中市的地区内码id不正确！');
        }

        if(!$RegionObj->isArea($level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中区的地区内码id不正确！');
        }

        if(!$ShopcatObj->getshopcatidIsEffective($shopcat_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000007, '店铺分类内码ID无效！');
        }

        if(!$ShopcatObj->getStoretypeidIsEffective($storetype)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000008, '店铺性质内码ID无效！');
        }

        $shop_id=Buddha_Http_Input::getParameter('shop_id');

        /*店铺名称*/
        $name=Buddha_Http_Input::getParameter('name');


        /*是否认证：*/
        $is_verify=Buddha_Http_Input::getParameter('is_verify');

        $qq=Buddha_Http_Input::getParameter('qq');
        $wechatnumber=Buddha_Http_Input::getParameter('wechatnumber');
        $rzcodes=Buddha_Http_Input::getParameter('rzcodes');//认证码


        $realname=Buddha_Http_Input::getParameter('realname');

        $tel=Buddha_Http_Input::getParameter('tel');
        $opentime=Buddha_Http_Input::getParameter('opentime');

        $regionstr=Buddha_Http_Input::getParameter('regionstr');
        $specticloc=Buddha_Http_Input::getParameter('specticloc');

        $property=Buddha_Http_Input::getParameter('property');
        $bushour=Buddha_Http_Input::getParameter('bushour');
        $myrange=Buddha_Http_Input::getParameter('myrange');
        $brief=Buddha_Http_Input::getParameter('brief');
        $shopdesc=Buddha_Http_Input::getParameter('shopdesc');

        $lng = Buddha_Http_Input::getParameter('lng');
        $lat = Buddha_Http_Input::getParameter('lat');
        $image_arr=Buddha_Http_Input::getParameter('image_arr');



        /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($image_arr);

        $Db_agentrate = $UserObj->getSingleFiledValues(array('id', 'agentrate'), " isdel=0 and level3='{$level3}'and groupid=2");
        $datas = array();
        $datas['wechatnumber'] = $wechatnumber;
        $datas['qq'] = $qq;
        $datas['name'] = $name;
        $datas['mobile'] = $mobile;
        $datas['tel'] = $tel;
        $datas['opentime'] = strtotime($opentime);;
        $datas['level0'] = 1;
        $datas['level1'] = $level1;
        $datas['level2'] = $level2;
        $datas['level3'] = $level3;
        $datas['regionstr'] = $regionstr;
        $datas['lng'] = $lng;
        $datas['lat'] = $lat;
        $datas['number'] = date("ymdHis") . mt_rand(1000, 9999);
        $datas['specticloc'] = $specticloc;
        $datas['storetype'] = $storetype;
        $datas['property'] = $property;
        $datas['bushour'] = $bushour;
        $datas['myrange'] = $myrange;
        $datas['brief'] = $brief;
        $datas['shopdesc'] = $shopdesc;
        $datas['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $datas['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];


        if($hsk_is_shop_needverify == 1){
            if($is_verify == 1){

                /*店铺添加者选择了付费，但未支付则返回  5 */
                $datas['isdel'] = 5;
            }
        }

        /*合伙人只能更新自己录入的，并且是代理商没有删除和下架的店铺*/

        $this->db->updateRecords( $datas, "shop","id ={$shop_id} AND referral_id='$user_id' AND (isdel!=1 OR isdel!=4");

        $num=$ShopObj->edit($datas,$shop_id);

        if(!$num){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000011, $this->tablenamestr.'更新/编辑失败！');
        }



        $savePath="storage/shop/{$shop_id}/";
        if(Buddha_Atom_Array::isValidArray($image_arr)){
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
            if(Buddha_Atom_Array::isValidArray($MoreImage)){
                $ShopObj->addImageArrToShopAlbum($MoreImage,$shop_id,$savePath);
                $ShopObj->setFirstGalleryImgToShop($shop_id);
            }

        }


        $is_needcreateorder = 0;
        $Services = '';
        $param = array();

        /*只有在显示店铺认证的情况下，才添加订单*/
        if($hsk_is_shop_needverify == 1){

            if($is_verify==1 or $is_verify==2){
                $is_needcreateorder=1;
                $Services = 'payment.shopverify';
                $param = array('is_verify'=>$is_verify,'good_table'=>'shop','good_id'=>$shop_id,'rzcodes'=>$rzcodes);

            }

        }

        $jsondata = array();
        $jsondata['db_isok'] =1;
        $jsondata['db_msg'] =$this->tablenamestr.'更新成功';
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['shop_id'] = $shop_id;
        $jsondata['is_verify'] = $is_verify;
        $jsondata['is_needcreateorder'] = $is_needcreateorder;
        $jsondata['Services'] = $Services;
        $jsondata['param'] = $param;


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "{$this->tablenamestr}管理：合伙人{$this->tablenamestr}更新/编辑");
    }

    /**
     * 店铺管理：合伙人录入商家店铺
     */
    public function partneradd()
    {

        $host = Buddha::$buddha_array['host'];

        /*这里必填groupid的原因是：  因为在添加店铺时要先添加用户，然后才确定店铺拥有者ID ；如果没有无法确定哪一个是店铺拥有者ID*/
        if (Buddha_Http_Input::checkParameter(array('usertoken','name','shopcat','realname','mobile','specticloc','image_arr','level1','level2','level3','specticloc'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
//        $groupid = Buddha_Http_Input::getParameter('groupid')?Buddha_Http_Input::getParameter('groupid'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','partnerrate');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        /*判断该角色是否具有合伙人功能*/
        if(!$UserObj->isHasPartnerPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000023, '没有合伙人用户权限，你还未申请合伙人角色！');
        }

        $ShopObj=new Shop();
        $ShopcatObj=new Shopcat();
        $CommonObj=new Common();
        $RegionObj=new Region();
        $JsonimageObj = new Jsonimage();


        if(!$UserObj->isHasMerchantPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');

        }

        $hsk_is_shop_needverify =isset(Buddha::$buddha_array['cache']['config']['hsk_is_shop_needverify']) ?Buddha::$buddha_array['cache']['config']['hsk_is_shop_needverify']: 1;//是否收费标识

        $mobile=Buddha_Http_Input::getParameter('mobile');
        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');
        $shopcat_id=Buddha_Http_Input::getParameter('shopcat');
        $storetype=(int)Buddha_Http_Input::getParameter('storetype')?(int)Buddha_Http_Input::getParameter('storetype'):0;


        if(!$CommonObj->getMobilephoneiseffectiveBymobile($mobile))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000032, '手机号码不正确！');
        }

        if(!$RegionObj->isProvince($level1))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000001, '国家、省、市、区 中省的地区内码id不正确！');
        }

        if(!$RegionObj->isCity($level2))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中市的地区内码id不正确！');
        }

        if(!$RegionObj->isArea($level3))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中区的地区内码id不正确！');
        }

        if(!$ShopcatObj->getshopcatidIsEffective($shopcat_id))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000007, '店铺分类内码ID无效！');
        }


        if(!$ShopcatObj->getStoretypeidIsEffective($storetype)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000008, $this->tablenamestr.'性质内码ID无效！');
        }


        /*店铺名称*/
        $name=Buddha_Http_Input::getParameter('name');


        /*是否认证：*/
        $is_verify=Buddha_Http_Input::getParameter('is_verify');

        $qq=Buddha_Http_Input::getParameter('qq');
        $wechatnumber=Buddha_Http_Input::getParameter('wechatnumber');
        $rzcodes=Buddha_Http_Input::getParameter('rzcodes');//认证码


        $realname=Buddha_Http_Input::getParameter('realname');

        $tel=Buddha_Http_Input::getParameter('tel');
        $opentime=Buddha_Http_Input::getParameter('opentime');

        $regionstr=Buddha_Http_Input::getParameter('regionstr');
        $specticloc=Buddha_Http_Input::getParameter('specticloc');

        $property=Buddha_Http_Input::getParameter('property');
        $bushour=Buddha_Http_Input::getParameter('bushour');
        $myrange=Buddha_Http_Input::getParameter('myrange');
        $brief=Buddha_Http_Input::getParameter('brief');
        $shopdesc=Buddha_Http_Input::getParameter('shopdesc');
        //地址ID转换转成文字
        $str = $RegionObj->getAddress($level3);
        //获取经纬度
        $lt= $ShopObj->location($str.$specticloc);

        $lng =$lt['lng'];
        $lat =$lt['lat'];

        $image_arr=Buddha_Http_Input::getParameter('image_arr');
        $password=Buddha_Http_Input::getParameter('password')?Buddha_Http_Input::getParameter('password'):'';
        $username=Buddha_Http_Input::getParameter('username')?Buddha_Http_Input::getParameter('username'):'';


        $num=$UserObj->countRecords("mobile='{$mobile}' and isdel=0 ");
        if($num>0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000036, '手机号已经存在！');
        }
        $datas=array();
        $data=array();
        if($username!=''){
            $data['username']=$username;
        }else{
            $data['username']=$mobile;
        }

        if($password){
            $data['password']=Buddha_Tool_Password::md5($password);
            $data['codes']=$password;
        }else{
            /*默认密码：*/
            $password=$UserObj->defaultpassword($mark=1);
            $data['password']=Buddha_Tool_Password::md5($password);
            $data['codes']=$password;
        }
        $data['referral_id'] = $user_id;
        $data['partnerrate'] = (int)$Db_User['partnerrate'];
        $data['mobile'] = $mobile;
        $data['mobile_ide'] = 1;
        /*商家角色*/
        $data['groupid'] = 1;
        /*多角色标识：商家角色*/
        $data['to_group_id'] = 1;
        $data['state'] = 1;
        $data['realname'] = $realname;
        $data['mobile'] = $mobile;
        $data['tel'] = $tel;
        $data['level0'] = 1;
        $data['level1'] = (int)$level1;
        $data['level2'] = (int)$level2;
        $data['level3'] = (int)$level3;
        $data['onlineregtime'] = Buddha::$buddha_array['buddha_timestamp'];

        $User_insertid = $UserObj->add($data);
        if(!$User_insertid){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000035, '用户添加失败！');
        }


        /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($image_arr);

        $Db_agentrate = $UserObj->getSingleFiledValues(array('id', 'agentrate'), " isdel=0 and level3='{$level3}'and groupid=2");
        $datas = array();

        $datas['wechatnumber'] = $wechatnumber;
        $datas['qq'] = $qq;
        $datas['user_id'] = $User_insertid;
        $datas['referral_id'] = (int)$user_id;
        $datas['partnerrate'] = (int)$Db_User['partnerrate'];
        $datas['agent_id'] = (int)$Db_agentrate['id'];
        $datas['agentrate'] = (int)$Db_agentrate['agentrate'];
        $datas['shopcat_id'] = $shopcat_id;
        $datas['realname'] = $realname;
        $datas['name'] = $name;
        $datas['mobile'] = $mobile;
        $datas['tel'] = $tel;

        $datas['opentime'] = strtotime($opentime);;
        $datas['level0'] = 1;
        $datas['level1'] = $level1;
        $datas['level2'] = $level2;
        $datas['level3'] = $level3;
        $datas['regionstr'] = $regionstr;
        $datas['lng'] = $lng;
        $datas['lat'] = $lat;
        $datas['number'] = date("ymdHis") . mt_rand(1000, 9999);
        $datas['specticloc'] = $specticloc;
        $datas['storetype'] = $storetype;
        $datas['property'] = $property;
        $datas['bushour'] = $bushour;
        $datas['myrange'] = $myrange;
        $datas['brief'] = $brief;
        $datas['shopdesc'] = $shopdesc;
        $datas['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $datas['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        if($hsk_is_shop_needverify == 1){
            if($is_verify == 1){

                /*店铺添加者选择了付费，但未支付则返回  5 */
                $datas['isdel'] = 5;
            }
        }
        $shop_id=$ShopObj->add($datas);

        /**二维码*/
        //////////////////////
        $shopinfo['id']=$shop_id;
        $CommonObj->codeimg($shopinfo);
        /////////////////////////////


        if(!$shop_id){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000001, $this->tablenamestr.'添加失败！');
        }

        if(Buddha_Atom_Array::isValidArray($image_arr)){
            $savePath="storage/shop/{$shop_id}/";
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
            if(Buddha_Atom_Array::isValidArray($MoreImage)){
                $ShopObj->addImageArrToShopAlbum($MoreImage,$shop_id,$savePath);
                $ShopObj->setFirstGalleryImgToShop($shop_id);
            }
        }


        $is_needcreateorder = 0;
        $Services = '';
        $param = array();

        /*只有在显示店铺认证的情况下，才添加订单*/
        if($hsk_is_shop_needverify == 1){

            if($is_verify==1 or $is_verify==2){
                $is_needcreateorder=1;
                $Services = 'payment.shopverify';
                $param = array('is_verify'=>$is_verify,'good_table'=>'shop','good_id'=>$shop_id,'rzcodes'=>$rzcodes);

            }

        }

        $jsondata = array();
        $jsondata['db_isok'] =1;
        $jsondata['db_msg'] =$this->tablenamestr.'添加成功';
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['shop_id'] = $shop_id;
        $jsondata['is_verify'] = $is_verify;
        $jsondata['is_needcreateorder'] = $is_needcreateorder;
        $jsondata['Services'] = $Services;
        $jsondata['param'] = $param;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "{$this->tablenamestr}管理：合伙人录入/添加商家{$this->tablenamestr}添加");
    }


    /**
     * 店铺管理：代理商 店铺 审核 详情（代理商只能查看未审核的详情）
     */
    public function agentmanageshopview()
    {
        $host=Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('b_display','usertoken','shop_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj=new Common();
        $UserObj=new User();
        $RegionObj= new Region();
        $ShopObj= new Shop();
        $ShopcatObj= new Shopcat();
        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$UserObj->isHasAgentPrivilege($user_id) ){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '你还未申请代理商角色！');
        }

        $api_shopid = (int)Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;


        if(!$CommonObj->isOwnerBelongToAgentByLeve3($this->tablename,$api_shopid,$Db_User['level3'])){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }


        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;


        /*代理商只能查看未审核的详情*/
        $where=" id='{$api_shopid}' AND is_sure=1 AND (isdel=0 OR isdel=5)";

        $fileds='id AS shop_id,name, shopcat_id, realname,mobile,tel,number,lng,lat,level1,
                level2,level3,level4,level5,endstep, specticloc,storetype,property,bushour,
                myrange,brief,shopdesc,codeimg,is_verify,veifytime,veryfyendtime,veryfyendtimestr,opentime,
                is_sure,state,createtime,createtimestr,remarks,is_hot,is_rec,is_promotion,
                qq,wechatnumber,roadfullname,opentime ';

        if($b_display==2){

            $fileds.=' , small AS img';

        }elseif($b_display==1){

            $fileds.=' , medium AS img';

        }

        $sql =" SELECT  {$fileds}  
                FROM {$this->prefix}shop WHERE {$where} ";

        $Db_Shop_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata =array();

        if(Buddha_Atom_Array::isValidArray($Db_Shop_arr)){

            $Db_Shop =  $Db_Shop_arr[0];

            /*店铺：审核*/
            $Db_Shop['issureServices']=array(
                'Services' => 'shop.verify',
                'param'=> array('is_sure'=>$Db_Shop['is_sure'],'shop_id'=>$Db_Shop['shop_id']),
            );

            $Db_Shop['api_img']=$host. $Db_Shop['img'];
            $Db_Shop['codeimg']=$host. $Db_Shop['codeimg'];

            $Db_Shop['api_address']=$RegionObj->getDetailOfAdrressByRegionIdStr($Db_Shop['level1'] ,$Db_Shop['level2'],$Db_Shop['level3'],' > ');

            $Db_Shop['api_shopcatname']=$ShopcatObj->getShopcatNameToShopcatid($Db_Shop['shopcat_id']);

            if(Buddha_Atom_String::isValidString($Db_Shop['opentime'])){
                $Db_Shop['api_opentimestr']=$CommonObj->getDateStrOfTime($Db_Shop['opentime']);
            }else{
                $Db_Shop['api_opentimestr']='';
                $Db_Shop['opentime']='';
            }
            if(Buddha_Atom_String::isValidString($Db_Shop['veifytime'])){
                $Db_Shop['api_veifytimestr']=$CommonObj->getDateStrOfTime($Db_Shop['veifytime']);
            }else{
                $Db_Shop['api_veifytimestr']='';
                $Db_Shop['opentime']='';
            }


            unset($Db_Shop['level1']);
            unset($Db_Shop['level2']);
            unset($Db_Shop['level3']);
            unset($Db_Shop['img']);

            unset($Db_Shop['toptimestr']);
            unset($Db_Shop['toptime']);
            unset($Db_Shop['lat']);
            unset($Db_Shop['lng']);

            $jsondata = $Db_Shop;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商店铺审核详情');
    }


    /**
     * 店铺管理：代理 商店铺 审核
     */
    public function verify(){

        if (Buddha_Http_Input::checkParameter(array('shop_id','usertoken','is_sure'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $ShopObj = new Shop();
        $CommonObj = new Common();

        $shop_id =  Buddha_Http_Input::getParameter('shop_id');
        /*审核状态：1通过审核  ；4未通过审核*/
        $is_sure = (int) Buddha_Http_Input::getParameter('is_sure')?(int)Buddha_Http_Input::getParameter('is_sure'):0;
        /*判断$is_sure审核状态码 是否属于 1,4*/
        if(!$CommonObj->isIdInDataEffectiveById($is_sure,array(1,4))){
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

        if(!$CommonObj->isOwnerBelongToAgentByLeve3($this->tablename,$shop_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }


        if($CommonObj->isIssureByTableid($shop_id,$this->tablename)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 50000002	, '已经审核过了，请不要重复审核！');
        }


        $data = array();
        $data['is_sure'] =$is_sure ;
        $data['remarks'] =$remarks ;
        $Db_Shop_num=$ShopObj->edit($data,$shop_id);


        $jsondata = array();
        if($Db_Shop_num){
            $jsondata['is_ok']=1;
            $jsondata['is_msg']=$this->tablenamestr.'审核成功！';
        }else{
            $jsondata['is_ok']=0;
            $jsondata['is_msg']=$this->tablenamestr.'审核失败！';
        }

        $jsondata['shop_id'] = $shop_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "{$this->tablenamestr}管理：代理商{$this->tablenamestr}审核");

    }


    /**
     * 代理商：单页 商店 审核之前必须请求详情页面
     */

    public function beforeverify()
    {
        $host= Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('shop_id','usertoken','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $ShopObj = new Shop();
        $CommonObj = new Common();

        $shop_id =  (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id'):0;
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

        if($CommonObj->isOwnerBelongToAgentByLeve3($shop_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }


        if($CommonObj->isIssureByTableid($shop_id,'shop')){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 50000002	, '已经审核过了，请不要重复审核！');
        }

        $b_display =(int) Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;


        $fields = ' id AS shop_id,add_time,click_count, shop_id, name, brief,`desc` ';

        if($b_display==1){

            $fields.=' , small AS img ';

        }elseif($b_display==2){

            $fields.=' , medium AS img ';
        }

        $where=" id ='{shop_id}' ";

        if($shop_id>0){

            $where.=" AND shop_id='{$shop_id}' ";
        }

        $sql =" SELECT {$fields} FROM {$this->prefix}shop  WHERE {$where} ";



        $Db_Shop_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata=array();
        if(Buddha_Atom_Array::isValidArray($Db_Shop_arr)){

            $Db_Shop=$Db_Shop_arr[0];
            if($Db_Shop['img']){
                $Db_Shop['img']=$host.$Db_Shop['img'];
            }else{
                $Db_Shop['img'] = '';
            }

            $Db_Shop['desc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Shop['desc']);
            $Db_Shop['shop_name']=$ShopObj->getShopnameFromShopid($Db_Shop['shop_id']);
            $Db_Shop['shop_img']=$host.$ShopObj->getShopImgFromShopid($Db_Shop['shop_id'],$b_display);
            /*店铺：审核*/
            $Db_Shop['issureServices']=array(
                'Services' => 'shop.verify',
                'param'=> array('is_sure'=>$Db_Shop['is_sure'],'shop_id'=>$Db_Shop['shop_id'])
            );


            $jsondata['list'] = $Db_Shop;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "代理商进行{$this->tablenamestr}之前必须请求的详情页面");

    }




    /**
     * 店铺管理：代理商 店铺 列表
     */
    public function agentmanageshopmore()
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


        $where=" agent_id='{$user_id}' AND level3 = '{$Db_User['level3']}' ";

        if(Buddha_Atom_String::isValidString($api_keyword)){

            $where.= Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($api_keyword,array('name','number'));
        }

        if($view){
            switch($view){
                case 2;
                    $where.=' AND isdel=0 AND is_sure=0 ';
                    break;
                case 3;
                    $where.=' AND isdel=0 AND is_sure=1 ';
                    break;
                case 4;
                    $where.=' AND isdel=0 AND is_sure=4 ';
                    break;
                case 5;
                    $where.=' AND isdel=4 AND state=1 ';
                    break;
            }
        }


        $fileds = ' id AS shop_id, name, number, createtime, is_verify, is_sure, state, user_id ';

        if($b_display==1){
            $fileds.=' , medium AS img ';
        }elseif($b_display==2){
            $fileds.=' , small AS  img ';
        }

        $orderby = " ORDER BY createtime DESC ";


        $sql =" SELECT  {$fileds}  
                FROM {$this->prefix}shop WHERE {$where} 
                {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        $Db_Shop = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);


        $jsondata = array();
        $jsondata['nav'] = $ShopObj->getPersonalCenterHeaderMenu('shop.agentmanageshopmore');
        $tablewhere=$this->prefix.'shop';

        $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);

        $jsondata['page'] =  $temp_Common['page'];
        $jsondata['pagesize'] =  $temp_Common['pagesize'];
        $jsondata['totalrecord'] =  $temp_Common['totalrecord'];
        $jsondata['totalpage'] =  $temp_Common['totalpage'];
        $jsondata['list'] = array();
        if(Buddha_Atom_Array::isValidArray($Db_Shop)){


            foreach($Db_Shop as $k=>$v){

                if($v['is_verify']==1){
                    $Db_Shop[$k]['api_authenticatestateimg']=$host.'apistate/menuplus/yirenzheng.png';
                }else{
                    $Db_Shop[$k]['api_authenticatestateimg']=$host.'apistate/menuplus/weirenzheng.png';

                }

                if($v['is_sure']==0){

                    $Db_Shop[$k]['api_auditstateimg']=$host.'apistate/menuplus/weishenhe.png';
                    /*未审核的才显示：才显示审核*/
                    $Db_Shop[$k]['audit']=array(
                        "Services"=> "shop.beforeverify",
                        "param"=>array('shop_id'=>$v['shop_id']),
                    );

                }elseif($v['is_sure']==4){

                    $Db_Shop[$k]['api_auditstateimg']=$host.'apistate/menuplus/weitonguo.png';
                    /*未通过审核的才显示：才显示审核*/
                    $Db_Shop[$k]['audit']=array(
                        "Services"=> "shop.beforeverify",
                        "param"=>array('shop_id'=>$v['shop_id']),
                    );
                }elseif($v['is_sure']==1){

                    $Db_Shop[$k]['api_auditstateimg']=$host.'apistate/menuplus/yitonguo.png';

                    /*通过审核的才显示：才显示认证否*/
                    if($v['is_verify']==1){
                        $Db_Shop[$k]['api_authenticatestateimg']=$host.'apistate/menuplus/yirenzheng.png';
                    }else{
                        $Db_Shop[$k]['api_authenticatestateimg']=$host.'apistate/menuplus/weirenzheng.png';
                        $Db_Shop[$k]['verify']=array(
                            "Services"=> " payment.shopverify",
                            "param"=>array('shop_id'=>$v['shop_id']),
                        );

                    }
                    /*通过审核的才显示：才显示停用启用否*/
                    $Db_Shop[$k]['api_authenticatestateimg']=$host.'apistate/menuplus/weirenzheng.png';
                    $Db_Shop[$k]['stoporenabled']=array(
                        "Services"=> " shop.agentmanagestoporenabled",
                        "param"=>array('shop_id'=>$v['shop_id'],'isdel'=>$v['isdel']),
                    );

                }

                if($v['state']==1){

                    $Db_Shop[$k]['api_usestatestr']='启 用';

                }else{

                    $Db_Shop[$k]['api_usestatestr']='停 用';
                }
                if(Buddha_Atom_String::isValidString($v['img'])){

                    $Db_Shop[$k]['api_img']=$host.$v['img'];

                }else{

                    $Db_Shop[$k]['api_img']='';
                }

                unset($v['img']);

                if(Buddha_Atom_String::isValidString($v['createtime']))
                {
                    $Db_Shop[$k]['api_timestr']=date('Y-m-d',$v['createtime']);
                }
            }

            $jsondata['list'] = $Db_Shop;
        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商店铺管理列表');
    }


    /**
     * 店铺列表
     */
    public function more()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('api_number','b_display')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        /*城市编号*/
        $RegionObj=new Region();
        /**is_indexheader 是否是首页的头部 点击过来的：0否；1是**/
        $is_indexheader = (int)Buddha_Http_Input::getParameter('is_indexheader')?(int)Buddha_Http_Input::getParameter('is_indexheader'):0;
        $api_number = (int)Buddha_Http_Input::getParameter('api_number')?(int)Buddha_Http_Input::getParameter('api_number'):0;
        if(!$RegionObj->isValidRegion($api_number))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444007, '地区编码不对（地区编码对应于腾讯位置adcode）');
        }

        $locdata = $RegionObj->getApiLocationByNumberArr($api_number);

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;
        $page = (int)Buddha_Http_Input::getParameter('page')?(int)Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];

        $pagesize = (int)Buddha_Atom_Secury::getMaxPageSize($pagesize);

        /*当前位置 纬度*/
        $lats = (int)Buddha_Http_Input::getParameter('lat')?Buddha_Http_Input::getParameter('lat'):0;

        /*当前位置 经度*/
        $lngs = (int)Buddha_Http_Input::getParameter('lng')?Buddha_Http_Input::getParameter('lng'):0;

        $latint = (int)$lats;
        $lngsint = (int)$lngs;


        if($latint==0 or $lngsint==0  or $api_number>0 ){
            $lats = $locdata['lat'];
            $lngs = $locdata['lng'];

        }


        $ShopObj =  new Shop();
        $CommonObj =  new Common();
        $ShopcatObj =  new Shopcat();

         $where = " isdel=0 AND is_sure=1 AND state=0 {$locdata['sql']} ";


                /*店铺属性分类  (0全部<即表示：在首页显示六个>；1沿街商铺；2市场；3商场；4写字楼；5生产制造；storetype */
                $storetype = (int) Buddha_Http_Input::getParameter('storetype')?(int)Buddha_Http_Input::getParameter('storetype'):0;
                /*销售类型： 5 推荐；1最新；2促销；3热门；4最近开业 同type*/
                $api_saletype = (int)Buddha_Http_Input::getParameter('api_saletype')?(int)Buddha_Http_Input::getParameter('api_saletype'):0;

                $api_keyword = Buddha_Http_Input::getParameter('api_keyword')?Buddha_Http_Input::getParameter('api_keyword'):'';

                /*你点击的哪一个物业名称*/
                $shopproperty = Buddha_Http_Input::getParameter('shopproperty')?Buddha_Http_Input::getParameter('shopproperty'):'';

//                /*最近开业的前后时间间距*/
//                $api_timeinterval = (int)Buddha_Http_Input::getParameter('api_timeinterval')?Buddha_Http_Input::getParameter('api_timeinterval'):15;

                /*店铺分类ID  等同于cid*/
                $api_shopclassificationid =(int) Buddha_Http_Input::getParameter('api_shopclassificationid')?(int)Buddha_Http_Input::getParameter('api_shopclassificationid'):0;

                /*是否按照附近显示*/
                $api_isnearby = (int)Buddha_Http_Input::getParameter('api_isnearby')?(int)Buddha_Http_Input::getParameter('api_isnearby'):0;

                /* 按照附近显示的距离默认为1km(如果api_isnearby==1时)*/
                $api_nearbydistance = (int)Buddha_Http_Input::getParameter('api_nearbydistance')?(int)Buddha_Http_Input::getParameter('api_nearbydistance'):1;

                $where_near = '';
                $orderby = " ORDER BY createtime ASC ";
                if($api_isnearby==1){
                    /*在条件中加入附近显示*/

                    $where_near = $RegionObj->whereJoinNearby($api_nearbydistance,$lats,$lngs,$api_number);

                  $latsint=(int)$lats;
                   $lngsint=(int)$lngs;


                  $orderby = " ORDER BY (ABS(lat-{$latsint})+ABS(lng-{$lngsint})) ASC ";

                   //  $orderby = "  ACOS(SIN(($lats * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(($lats * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(($lngs* 3.1415) / 180 - (lng * 3.1415) / 180 ) ) * 6380 asc ";

//

                }else{
                    $orderby = " ORDER BY toptime,createtime DESC ";
                }
                /**如果从首页点击过来的，不需要加入销售条件：***/
                if($is_indexheader==0)
                {
                    /**销售类型： 0 推荐（默认）；1最新；2 促销；3 热门；4 最近开业 同type*/

                    switch ($api_saletype)
                    {
                        case 5:
                            $where.=" AND is_rec = 1 ";

                            break;
                        case 1:

                            $orderby = " ORDER BY toptime,createtime DESC  ";
                            break;
                        case 2:

                            $where.= " AND is_promotion=1 ";
                            break;
                        case 3:

                            $where.= " AND is_hot=1 ";
                            break;
                        case 4:

                            $where.= $ShopObj->openedrecently();
                            break;
                    }
                }


                if($storetype)
                {
                    $where.= " AND storetype='{$storetype}' ";

                    if($storetype == 1 || $storetype == 5){/*店铺属性分类  (0全部；1 沿街商铺；2 市场；3 商场；4 写字楼；5 生产制造；storetype */

                        if(Buddha_Atom_String::isValidString($api_keyword)){
                            $where .= " AND (name LIKE '%$api_keyword%' OR specticloc LIKE '%$api_keyword%')";/*地址或商家名称搜索*/

                        }

                    }elseif($storetype ==2 || $storetype == 3 || $storetype == 4){

                        if(Buddha_Atom_String::isValidString($api_keyword)){
                            $where .= " AND (name LIKE '%$api_keyword%' OR specticloc LIKE '%$api_keyword%' OR  property LIKE '%$api_keyword%')";/**物业名称或道路名称或商家名称搜索*/

                        }
                    }
                }

                if(Buddha_Atom_String::isValidString($api_keyword))
                {
                    $where .= " AND ( name LIKE '%$api_keyword%' OR specticloc LIKE '%$api_keyword%' )";
                }
                if($api_shopclassificationid>0)
                {
                    $where.= " AND shopcat_id ='{$api_shopclassificationid}' ";
                }

                if(Buddha_Atom_String::isValidString($shopproperty))
                {
                    $where.=" AND property='{$shopproperty}' ";
                }


        $fileds = 'id AS shop_id, name, brief, specticloc, storetype as storetypeid , shopcat_id, lng, lat ';


        if($b_display==1)
        {
            $fileds.=', medium AS img ';
        }elseif($b_display==2)
        {
            $fileds.=', small AS img ';
        }

        if($api_isnearby == 1 )
        {
 $where_near2 = " AND lat > {$lats}-1 AND
lat < {$lats}+1 AND
lng > {$lngs}-1 AND
lng < {$lngs}+1 ";
         $orderby2 = " order by ACOS(SIN(({$lats} * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(({$lats} * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(({$lngs}* 3.1415) / 180 - (lng * 3.1415) / 180 ) ) * 6380 asc ";
           $sql = " SELECT {$fileds} FROM {$this->prefix}shop WHERE {$where}{$where_near2} {$orderby2} ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );


     ////  $sql = " SELECT {$fileds} FROM {$this->prefix}shop WHERE {$where}{$where_near} {$orderby} ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );



//          $orderby2 = " order by distance asc ";
//             $sql = " SELECT {$fileds} ,
//SQRT(POW(111.2 * ( lat - {$lats}), 2) + POW(111.2 * ({$lngs} - lng) * COS(lat/ 57.3), 2)) AS distance
// FROM {$this->prefix}shop WHERE {$where}{$where_near2} {$orderby2} ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
//
//
//
//
//
//
//         echo        $sql = " SELECT {$fileds} ,
//6370.996 * 1000*2 * asin(sqrt(pow(sin( $lats * 3.1415926 / 180.0 - lat * 3.1415926 / 180.0/2),2) + cos($lats * 3.1415926 / 180.0) *
//                    cos(lng * 3.1415926 /180.0) * pow(sin($lngs * 3.1415926 / 180.0 - lng * 3.1415926 /180.0/2),2))) AS distance
// FROM {$this->prefix}shop WHERE {$where}{$where_near} {$orderby} ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );


        }else
        {
            $sql = " SELECT {$fileds} FROM {$this->prefix}shop WHERE {$where} {$orderby} ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );

        }

        $Db_Shop = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);


        if($api_isnearby==1 AND !Buddha_Atom_Array::isValidArray($Db_Shop))
        {

            $sql = " SELECT {$fileds} FROM {$this->prefix}shop WHERE {$where} ORDER BY createtime DESC ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );

            $Db_Shop = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }

        $jsondata = array();

//        0 推荐（默认）；1 最新；2 促销 ；3 热门 ；4 最近开业
        $headertitle = array(
            0=>array(
                'api_saletype'=>0,
                'api_saletypename'=>'推荐',
                'Services'=>'shop.more',
                'param'=>array('api_saletype'=>0,'is_indexheader'=>0),
            ),
            1=>array(
                'api_saletype'=>1,
                'api_saletypename'=>'最新',
                'Services'=>'shop.more',
                'param'=>array('api_saletype'=>1,'is_indexheader'=>0),
            ),
            2=>array(
                'api_saletype'=>2,
                'api_saletypename'=>'促销',
                'Services'=>'shop.more',
                'param'=>array('api_saletype'=>2,'is_indexheader'=>0),

            ),
            3=>array(
                'api_saletype'=>3,
                'api_saletypename'=>'热门',
                'Services'=>'shop.more',
                'param'=>array('api_saletype'=>3,'is_indexheader'=>0),
            ),
            4=>array(
                'api_saletype'=>10,
                'api_saletypename'=>'分类',
                'Services'=>'shop.shopclassification',
                'param'=>array(),
            ),
        );
        foreach ($headertitle as $k=>$v){
            if($v['api_saletype']==$api_saletype){
                $headertitle[$k]['select']=1;
            }else{
                $headertitle[$k]['select']=0;
            }
        }

        $jsondata['nav'] = array();
        /** ↓↓↓↓↓↓↓↓ 如果从首页点击过来的，不需要加入头部： ↓↓↓↓↓ ***/
        if($is_indexheader==0)
        {
            $jsondata['nav'] = $headertitle;
        }
        /** ↑↑↑↑↑↑↑↑ 如果从首页点击过来的，不需要加入头部： ↑↑↑↑↑ ***/
        $jsondata['list']=array();
        $jsondata['cat']=array(
            'Services'=>'shop.shopclassification',
            'param'=>array('api_islocalinfo'=>0),
        );
        $jsondata['page'] = 0;
        $jsondata['pagesize'] = 0;
        $jsondata['totalrecord'] = 0;
        $jsondata['totalpage'] = 0;
        $jsondata['storetype'] = $storetype;

        if(Buddha_Atom_Array::isValidArray($Db_Shop))
        {
            foreach($Db_Shop as $k=>$v)
            {

/*echo $lats;
                echo " ";
echo $v['lat'];
                echo "<hr><br>";
                echo $lngs;
                echo " ";
                echo $v['lng'];
                echo "<hr><br>";

             print_r($v);
                echo "<hr><br>";*/

               $distance = $ShopObj->getDistanceBetweenPointsNew( $lats,$lngs,  $v['lat'],$v['lng'],$api_number);//根据经纬度计算距离






                $Db_Shop[$k]['img']=$host.$v['img'];
                $Db_Shop[$k]['distance']=$distance;
                $Db_Shop[$k]['api_shopcatname'] =  Buddha_Atom_String::getApiValidStr($ShopcatObj->getShopcatNameToShopcatid($v['shopcat_id'])) ;
                $Db_Shop[$k]['api_storetypename'] = $ShopcatObj->getStoretypeNameToStoretypeid($v['storetypeid']);
                $Db_Shop[$k]['services'] = 'shop.view';
                $Db_Shop[$k]['param'] = array('shop_id'=>$v['shop_id']);
                unset($Db_Shop[$k]['lng'] );
                unset($Db_Shop[$k]['lat'] );
            }

            $jsondata['list'] = $Db_Shop;
            $tablewhere=$this->prefix.'shop';
            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);

            $jsondata['page'] = $temp_Common['page'];
            $jsondata['pagesize'] = $temp_Common['pagesize'];
            $jsondata['totalrecord'] = $temp_Common['totalrecord'];
            $jsondata['totalpage'] = $temp_Common['totalpage'];

        }
        if(empty($usertoken) and Buddha_Atom_Array::isValidArray($Db_Shop))
        {
            $jsondata['distanceIcon'] = $host.'apiindex/menuplus/icon_position.png';
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '店铺列表');

    }

    /**
     * 店铺详情
     */

    public function view()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('shop_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $SupplyObj = new Supply();


        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        if(strlen($usertoken)){
            $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
            $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','banlance', 'username' );
            $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
            $user_id = $Db_User['id'];
        }

        $user_id = (int)$user_id;


        $b_display =(int) Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;
        $shop_id =(int) Buddha_Http_Input::getParameter('shop_id');

        $fields = 'id AS shop_id, name, is_verify, mobile,lng,lat';

        if($b_display==1){
            $fields.=' , medium AS img ';
        }elseif($b_display==2){
            $fields.=' , small AS img ';
        }

        $where=" id='{$shop_id}' ";

        $sql ="select {$fields} FROM {$this->prefix}shop WHERE {$where} ";
        $Db_Shop = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);


        $ShopObj =new Shop();

        $jsondata = array();

        if(!Buddha_Atom_Array::isValidArray($Db_Shop)){

            $Db_Shop = array();

        }else{

            $Db_Shop = $Db_Shop[0];

        }
        $Db_Shop['img']=$host. $Db_Shop['img'];


//        $isShowCellphone = $ShopObj->isShowCellphone($shop_id,$user_id);
//
//        if($isShowCellphone==1){
//
//            $mobile = Buddha_Atom_Array::getApiKeyOfValueByArrayString($Db_Shop,'mobile');
//
//        }else{
//
//            $mobile = '查看';
//
//        }
//
//
//
//            $is_verify = 1;
//            $icon_verify = $host.'apishop/menuplus/renzheng.png';
//            $verifystr = '已认证';
//
//        }else{
//
//            $is_verify = 0;
//            $icon_verify = $host.'apishop/menuplus/renzheng.png';
//            $verifystr = '未认证';
//        }

//        if($ShopObj->isShopVerify($shop_id)){
//            $is_verify = 1;
//        }else{
//            $is_verify = 0;
//        }

        /**↓↓↓↓↓↓↓↓↓↓↓ 显示电话 ↓↓↓↓↓↓↓↓↓↓↓**/
        if($Db_Shop['is_verify'])
        {
            $icon_verify = $host . "apishop/menuplus/icon_certified.png";
            $verifystr = '已认证';
            $is_verify = 1;
        }else{
            $icon_verify = $host . "apishop/menuplus/icon_unauthorized.png";
            $verifystr = '未认证';
            $is_verify = 1;
        }


        // 是否显示电话
       $is_showcellphone = $ShopObj->isCouldSeeCellphone($shop_id,$Db_Shop['user_id'],$user_id);


        $mobile = $ShopObj->showCellphone($shop_id,'shop',$Db_Shop['user_id'],$user_id,$shop_id);;
        /**↑↑↑↑↑↑↑↑↑↑ 显示电话 ↑↑↑↑↑↑↑↑↑↑**/



        $jsondata['header'] = array('shop_name'=>Buddha_Atom_Array::getApiKeyOfValueByArrayString($Db_Shop,'name'),
            'isShowCellphone'=>$is_showcellphone,
            'shop_id'=>$Db_Shop['shop_id'],
            'mobile'=>$mobile,
            'icon_cellphone'=>$host.'apishop/menuplus/dianhua.png',
            'icon_map'=>$host.'apishop/menuplus/ditu.png',
            'shop_img'=>$host.$Db_Shop['img'],
            'is_verify'=>$is_verify,
            'icon_verify'=>$icon_verify,
            'verifystr'=>$verifystr,
            'lng'=>$Db_Shop['lng'],
            'lat'=>$Db_Shop['lat'],
        );

        $jsondata['shop']=array(
            'Services'=>'shop.view',
            'param'=>array('shop_id'=>$Db_Shop['shop_id']),
        );


       $jsondata['body'] = array(
            0=>array( 'select'=>0,'name'=>'促销','pageflag'=>'promote','type'=>1,
                                            'Services'=>'multilist.promotionsarr','param'=>array(),
                                            'showstyle'=>'piclist',
                                            'icon_promote'=>$host.'apishop/menuplus/cuxiao.png','list'=>array()  ),

            1=>array( 'select'=>0,'name'=>'传单','pageflag'=>'singleinformation','type'=>2,
                                            'Services'=>'singleinformation.more','param'=>array(),
                                            'showstyle'=>'piclist',
                                            'icon_promote'=>$host.'apishop/menuplus/danyexin.png','list'=>array()  ),

            2=>array( 'select'=>0,'name'=>'供应','pageflag'=>'supply','type'=>3,
                                            'Services'=>'multilist.supplymore','param'=>array(),
                                            'showstyle'=>'piclist',
                                            'icon_promote'=>$host.'apishop/menuplus/gongying.png','list'=>array()  ),

            3=>array( 'select'=>0,'name'=>'活动','pageflag'=>'activity','type'=>4,
                                            'Services'=>'activity.more','param'=>array(),
                                            'showstyle'=>'piclist',
                                            'icon_promote'=>$host.'apishop/menuplus/huodong.png','list'=>array()  ),

            4=>array( 'select'=>0,'name'=>'简介','pageflag'=>'abstract','type'=>5,
                                            'Services'=>'shop.shopabstrac','param'=>array(),
                                            'showstyle'=>'html',
                                            'icon_promote'=>$host.'apishop/menuplus/jianjie.png','list'=>array()  ),

            5=>array( 'select'=>0,'name'=>'名片','pageflag'=>'card','type'=>6,
                                            'Services'=>'shop.businesscard','param'=>array(),
                                            'showstyle'=>'html',
                                            'icon_promote'=>$host.'apishop/menuplus/mingpian.png','list'=>array()  ),

            6=>array( 'select'=>0,'name'=>'需求','pageflag'=>'demand','type'=>7,
                                            'Services'=>'multilist.demandmore','param'=>array(),
                                            'showstyle'=>'piclist',
                                            'icon_promote'=>$host.'apishop/menuplus/xuqiu.png','list'=>array()  ),

            7=>array( 'select'=>0,'name'=>'招聘','pageflag'=>'recruit','type'=>8,
                                            'Services'=>'multilist.recruitarr','param'=>array(),
                                            'showstyle'=>'nopiclist',
                                            'icon_promote'=>$host.'apishop/menuplus/zhaopin.png','list'=>array() ),

            8=>array( 'select'=>0,'name'=>'租赁','pageflag'=>'lease','type'=>9,
                                            'Services'=>'multilist.leasearr','param'=>array(),
                                            'showstyle'=>'nopiclist',
                                            'icon_promote'=>$host.'apishop/menuplus/zulin.png','list'=>array() ),
            9=>array( 'select'=>0,'name'=>'一分营销','pageflag'=>'heartpro','type'=>10,
                                            'Services'=>'multilist.heartproarr','param'=>array(),
                                            'showstyle'=>'nopiclist',
                                            'icon_promote'=>$host.'apishop/menuplus/heartpro.png','list'=>array() ),
            10=>array( 'select'=>0,'name'=>'一码营销','pageflag'=>'codesales','type'=>11,
                                            'Services'=>'multilist.codesalesarr','param'=>array(),
                                            'showstyle'=>'nopiclist',
                                            'icon_promote'=>$host.'apishop/menuplus/codesales.png','list'=>array() ),
        );


        $Supply_where = "shop_id='{$shop_id}' AND is_sure=1 AND buddhastatus=0 AND isdel=0";
        if($SupplyObj->countRecords($Supply_where))
        {
            $selectstr = 'supply';
        }else{
            $selectstr = 'card';
        }

        foreach ($jsondata['body'] as $k=>$v)
        {
            if($v['pageflag'] == $selectstr)
            {
                $jsondata['body'][$k]['select']=1;
            }else{
                $jsondata['body'][$k]['select']=0;
            }
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '店铺详情');
    }

    /**
     *  isShowCellphone  是否显示电话
    **/
    public function isshowcellphone()
    {

        if (Buddha_Http_Input::checkParameter(array('shop_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $shop_id =(int) Buddha_Http_Input::getParameter('shop_id');

        $fields = 'id AS shop_id, name, is_verify, mobile';
        $UserObj = new User();


        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        if(strlen($usertoken)){
            $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
            $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','banlance', 'username' );
            $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
            $user_id = $Db_User['id'];

            $user_id = (int)$user_id;

            $where=" id='{$shop_id}' ";

            $sql ="select {$fields} FROM {$this->prefix}shop WHERE {$where} ";
            $Db_Shop = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            $ShopObj = new Shop();

            $isShowCellphone = $ShopObj->isShowCellphone($shop_id,$user_id);

        }else{

            $isShowCellphone=0;

        }


        if($isShowCellphone==1){

            $mobile = Buddha_Atom_Array::getApiKeyOfValueByArrayString($Db_Shop,'mobile');

        }else{

            $mobile = '查看';

        }

        $jsondata = array();
        $jsondata['mobile'] = $mobile;
        $jsondata['isShowCellphone'] = $isShowCellphone;



        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '是否显示电话号码');
    }


    /**
     * 店铺分类列表
     */
    public function shopclassification()
    {

        if (Buddha_Http_Input::checkParameter(array('api_number','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $ShopObj=new Shop();

        /* 是否是本地信息，默认为0否,1是 */
        $api_islocalinfo = Buddha_Http_Input::getParameter('api_islocalinfo')?Buddha_Http_Input::getParameter('api_islocalinfo'):0;
        $b_display = Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        $api_keyword = Buddha_Http_Input::getParameter('api_keyword')?Buddha_Http_Input::getParameter('api_keyword'):'';/* 搜搜关键字 */

        /*城市编号*/
        $RegionObj=new Region();

        $api_number = (int)Buddha_Http_Input::getParameter('api_number')?Buddha_Http_Input::getParameter('api_number'):0;
        if(!$RegionObj->isValidRegion($api_number)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444007, '地区编码不对（地区编码对应于腾讯位置adcode）');
        }

        $locdata=$RegionObj->getApiLocationByNumberArr($api_number);


        $where = ' sc.ifopen=0 AND ic.isopen=1 AND sc.isdel=0 AND ic.isdel=0  ';
        if(Buddha_Atom_String::isValidString($api_keyword)){
            $where .= " AND sc.cat_name LIKE '%$api_keyword%' ";
        }

        $fileds='sc.id AS shopcat_id,sc.sub,sc.cat_name,sc.child_count';

        if($api_islocalinfo==1){
            /*当为本地信息时要显示广告位:将广告位信息加入到店铺分类列表中*/
            $fileds.=' ,sc.ad_id AS api_imagecatalogid,sc.ad_name';

            /*表示是手机版的本地信息*/
            if($b_display==2){

                /*手机本地信息为sub=16*/
                $where .=' AND ic.sub=16 ';
                $fileds.=' ,ic.name AS api_imagecatalogname,ic.identify AS api_imagecatalogidentify, ic.view_order api_imagecatalogvieworder,ic.sub AS api_imagecatalogsub';
            }
            
        }

        $orderby = ' ORDER BY sc.view_order ASC';


        $sql =" SELECT  {$fileds}  
                FROM {$this->prefix}shopcat AS sc 
                LEFT JOIN {$this->prefix}imagecatalog AS ic 
                ON ic.id = sc.ad_id 
                WHERE {$where} 
                {$orderby} ";

        $Db_Shopcat = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);


        $jsondata=array();

        if(Buddha_Atom_Array::isValidArray($Db_Shopcat)){
            /*判断是否为本地信息: 1 为是；0为否   ： 当为1 时，要上请求店铺的接口和对应的参数*/
            /*判断是否为本地信息: 1 为是；0为否   ： 当为1 时，要统计①本地该类店铺的数目；返回值要返回当前选择的哪一个类目*/
            if($api_islocalinfo==1){

                $shopwhere = ' isdel=0 ';

                $shopwhere .= $RegionObj->whereJoinRegion($api_number);

            }
            if($api_islocalinfo==1){
                foreach($Db_Shopcat as $k=>$v){

                    if($k==0){

                        $Db_Shopcat[$k]['select']=1;
                    }else{

                        $Db_Shopcat[$k]['select']=0;
                    }
                    /*统计当前区县该类下的总数量*/
                    $Db_Shopcat[$k]['total']=$ShopObj->countRecords($shopwhere." AND shopcat_id='{$v['shopcat_id']}' {$locdata['sql']}");
                    /*店铺列表*/
                    $Db_Shopcat[$k]['shopmore']['Services']='shop.more';
                    $Db_Shopcat[$k]['shopmore']['param']=array('api_shopclassificationid'=>$v['api_imagecatalogid']);

                    /*当前区县当前分类下的广告图列表*/
                    $Db_Shopcat[$k]['admore']['Services']='shop.advertising';
                    $Db_Shopcat[$k]['admore']['param']=array('api_imagecatalogid'=>$v['api_imagecatalogid']);
                }
            }
            $jsondata=$Db_Shopcat;
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '店铺分类列表');
    }


    /**
     * 店铺物业列表
     */
    public function shopproperty ()
    {
        if (Buddha_Http_Input::checkParameter(array('api_number','storetype'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $RegionObj = new Region();
        $ShopObj  = new Shop();
        $CommonObj = new Common();

        $api_keyword = Buddha_Http_Input::getParameter('api_keyword');

        /*店铺属性分类  (0全部；1沿街商铺；2市场；3商场；4写字楼；5生产制造；storetype */
        $storetype =(int) Buddha_Http_Input::getParameter('storetype')?Buddha_Http_Input::getParameter('storetype'):2;

        /*城市编号*/
        $api_number = (int)Buddha_Http_Input::getParameter('api_number')?Buddha_Http_Input::getParameter('api_number'):'';
        if(!$RegionObj->isValidRegion($api_number)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444007, '地区编码不对（地区编码对应于腾讯位置adcode）');
        }
        $locdata=$RegionObj->getApiLocationByNumberArr($api_number);

        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        $where = " isdel=0 AND is_sure=1  AND state=0 {$locdata['sql']}";
        $title='';
        if($storetype){
            switch($storetype){
                case 1;
                    $where.=" AND storetype=1 ";
                    $title='';
                    break;
                case 2;
                    $where.=" AND storetype=2 ";
                    $title='市场';
                    break;
                case 3;
                    $where.=" AND storetype=3 ";
                    $title='商场';
                    break;
                case 4;
                    $where.=" AND storetype=4 ";
                    $title='写字楼';
                    break;
                case 5;
                    $where.=" AND storetype=5 ";
                    $title='';
                    break;
            };
        };

        if ($api_keyword) {
            $where .= " AND (property LIKE '%$api_keyword%' OR specticloc LIKE '%$api_keyword%')";
        };

        $orderby = " ORDER BY createtime DESC ";

        $sql = " select DISTINCT property,  storetype   from {$this->prefix}shop  where {$where} {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );



        $Db_Shop = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata = array();
        $jsondata['list']= array();
        $jsondata['page'] = 0;
        $jsondata['pagesize'] = $pagesize;
        $jsondata['totalrecord'] = 0;
        $jsondata['totalpage'] = 0;
        $jsondata['title'] = $title;
        if(Buddha_Atom_Array::isValidArray($Db_Shop))
        {
            foreach( $Db_Shop as $k=>$v){
                $property = $v['property'];
                $total = $ShopObj->countRecords("isdel=0 and is_sure=1 and state=0 and property='{$property}' {$locdata['sql']} ");
                $Db_Shop[$k]['total'] = $total;
            };

            $jsondata['list']=array();
            if(Buddha_Atom_Array::isValidArray($Db_Shop))
            {
                $Db_Shop_array=array();

                foreach($Db_Shop as $k=>$v){
                    $Db_Shop[$k]['Services']='shop.more';
                    $Db_Shop[$k]['param']=array('storetype'=>$storetype,'shopproperty'=>$v['property']);
//                    $Db_Shop[$k]['storetype ']=$storetype ;
                }

                $jsondata['list'] = array_slice($Db_Shop,($page-1),$pagesize);

                $rcount = count($Db_Shop);
                $pcount = ceil($rcount / $pagesize);
                if ($page > $pcount)
                {
                    $page = $pcount;
                }
                $temp_Common = array();
                /*当前页*/
                $temp_Common['page'] = $page;
                /*每页数量*/
                $temp_Common['pagesize'] = $pagesize;
                /*总条数*/
                $temp_Common['totalrecord'] = $rcount;
                /*总页数*/
                $temp_Common['totalpage'] = $pcount;

                $jsondata['page'] = $temp_Common['page'];
                $jsondata['pagesize'] = $temp_Common['pagesize'];
                $jsondata['totalrecord'] = $temp_Common['totalrecord'];
                $jsondata['totalpage'] = $temp_Common['totalpage'];
                $jsondata['title'] = $title;
            }
        }


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $title.'店铺物业列表');

    }

    /**
     *  简介
     */
    public function shopabstract(){
        $host=  Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('shop_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $ShopObj=new Shop();

        $shop_id =(int) Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;
        $b_display =(int) Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        $where = " isdel=0 AND is_sure=1 AND state=0 AND id='{$shop_id}' ";

        $fields = ' shopdesc, is_verify  ';
        if($b_display==2){
            $fields .=' , medium AS img';
        }elseif($b_display==1){
            $fields .=' , large AS img ';
        }

        $sql = " SELECT {$fields} FROM {$this->prefix}shop WHERE {$where} ";

        $Db_Shop = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata=array();
        if(Buddha_Atom_Array::isValidArray($Db_Shop))
        {
            $isShopVerify = $ShopObj->isShopVerify($shop_id);
            if($isShopVerify==0)
            {
                $Db_Shop[0]['shopdesc']=preg_replace("/13[12356789]{1}\d{8}|15[1235689]\d{8}|188\d{8}/", '${1}*****${2}',$Db_Shop[0]['shopdesc']);

                $Db_Shop[0]['shopdesc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Shop[0]['shopdesc']);

                $Db_Shop[0]['img']=preg_replace("/\d{3,4}-\d{7,8}/", '${1}*****${2}', $Db_Shop[0]['img']);
            }
            $Db_Shop[0]['img']=$host.$Db_Shop[0]['img'];
            $jsondata=$Db_Shop;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '店铺简介');

    }

    /**
     *  名片
     */
    public function businesscard()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('shop_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $ShopObj=new Shop();
        $UserObj=new User();
        $RegionObj  = new Region();


        $usertoken = Buddha_Http_Input::getParameter('usertoken');

        if(strlen($usertoken)){
            $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
            $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
                'realname','mobile','banlance',
                'username'
            );
            $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
            $user_id = $Db_User['id'];
        }

        $user_id = (int)$user_id;


        $shop_id =(int) Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;
        $b_display =(int) Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        $where = " isdel=0 AND is_sure=1 AND state=0 AND id='{$shop_id}' ";

        $fields = ' id AS shop_id, name as shop_name,level1, level2, level3, is_verify, level4, level5, endstep, realname, mobile, tel, qq, wechatnumber, roadfullname, specticloc, createtimestr, codeimg ';

        if($b_display==2){
            $fields .=' , small AS img';
        }elseif($b_display==1){
            $fields .=' , small AS img ';
        }

        $sql = " SELECT {$fields} FROM {$this->prefix}shop WHERE {$where} ";
        $Db_Shop = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata=array();

        if(Buddha_Atom_Array::isValidArray($Db_Shop)){

            $id_string="{$Db_Shop[0]['level1']},{$Db_Shop[0]['level2']},{$Db_Shop[0]['level3']}";
            $area=$RegionObj-> Region_area($id_string);

            $isShowCellphone = $ShopObj->isShopVerify($shop_id);
            if($isShowCellphone==0){
                $Db_Shop[0]['mobile']=preg_replace("/13[12356789]{1}\d{8}|15[1235689]\d{8}|188\d{8}/", '${1}*****${2}',$Db_Shop[0]['mobile']);
                $Db_Shop[0]['tel']=preg_replace("/13[12356789]{1}\d{8}|15[1235689]\d{8}|188\d{8}/", '${1}*****${2}',$Db_Shop[0]['tel']);
                $Db_Shop[0]['realname']=preg_replace("/13[12356789]{1}\d{8}|15[1235689]\d{8}|188\d{8}/", '${1}*****${2}',$Db_Shop[0]['realname']);
            }

            $id=array(
                'level1'=>$Db_Shop[0]['level1'],
                'level2'=>$Db_Shop[0]['level2'],
                'level3'=>$Db_Shop[0]['level3'],
            );

            $diqu=$RegionObj->select_provincialcity($id);
            $Provincialcity= $diqu['level1']['fullname'] .$diqu['level2']['fullname'].$diqu['level3']['fullname'];
            if($Db_Shop[0]['level4']){
                $Provincialcity.=$Db_Shop[0]['level4'].'街道  ';
            }
            if($Db_Shop[0]['level5']){
                $Provincialcity.=$Db_Shop[0]['level5'].'路   ';
            }

            if($Db_Shop[0]['endstep']){
                $Provincialcity.=$Db_Shop[0]['endstep'].'号/弄  ';
            }

            if($Db_Shop[0]['roadfullname']){
                $Provincialcity.=$Db_Shop[0]['roadfullname'].'  ';
            }

            if($Db_Shop[0]['specticloc']){
                $Provincialcity.=','.$Db_Shop[0]['specticloc'].'  ';
            }

            if(Buddha_Atom_String::isValidString($Db_Shop[0]['qq'])==0){
                $Db_Shop[0]['qq']='';
            }
            $Db_Shop[0]['qqimg']=$host.'apishop/menuplus/qq.png';
            $Db_Shop[0]['mobileimg']=$host.'apishop/menuplus/shouji.png';
            $Db_Shop[0]['telimg']=$host.'apishop/menuplus/zuoji.png';
            $Db_Shop[0]['wechatnumberimg']=$host.'apishop/menuplus/weixin.png';
            if(Buddha_Atom_String::isValidString($Db_Shop[0]['wechatnumber'])==0){
                $Db_Shop[0]['wechatnumber']='';
            }
//            $Db_Shop[0]['specticloc']=$area.$Db_Shop[0]['specticloc'];

            $Db_Shop[0]['fullname']=$Provincialcity;
            $Db_Shop[0]['img']=$host.$Db_Shop[0]['img'];
            $Db_Shop[0]['codeimg']=$host.$Db_Shop[0]['codeimg'];
            unset($Db_Shop[0]['level1']);
            unset($Db_Shop[0]['level2']);
            unset($Db_Shop[0]['level3']);
            unset($Db_Shop[0]['level4']);
            unset($Db_Shop[0]['level5']);
            unset($Db_Shop[0]['level6']);
            unset($Db_Shop[0]['endstep']);
            unset($Db_Shop[0]['specticloc']);
            unset($Db_Shop[0]['roadfullname']);

            $jsondata= $Db_Shop[0];

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '名片');
    }


    /**
     * 店铺：本地信息的广告图
     */
    public function advertising()
    {
        $host=Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('api_number','api_imagecatalogid','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $RegionObj=new Region();
        $api_number = Buddha_Http_Input::getParameter('api_number');/*  */

        //
        $api_adid = Buddha_Http_Input::getParameter('api_imagecatalogid');/*  */


        $nowtime= Buddha::$buddha_array['buddha_timestamp'];

        $where = " cat_id='$api_adid' AND buddhastatus=1 AND isdel=0 AND (promote_start_date<=$nowtime  AND $nowtime < promote_end_date )";
        $where .= $RegionObj->whereJoinRegion($api_number);

        $fileds=' id AS image_id, shop_id, name, large AS img ';
        $orderby=' ORDER BY view_order ASC';

        $page=1;$pagesize=1;
        $sql =" SELECT {$fileds} 
                FROM {$this->prefix}image 
                WHERE {$where} 
                {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );

        $Db_Image_array = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata=array();
        if(!Buddha_Atom_Array::isValidArray($Db_Image_array)){
         /*当该地区的给类别下没有地区广告时显示平台广告*/

            $temp_where = " cat_id='$api_adid' AND buddhastatus=1 AND isdel=0 AND (promote_start_date=0  AND promote_end_date = 0 ) AND shop_id =0  AND level3=0 ";
            $sql =" SELECT  {$fileds}  
                FROM {$this->prefix}image 
                WHERE {$temp_where} 
                {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );

            $Db_Image_array = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }
        if(Buddha_Atom_Array::isValidArray($Db_Image_array)){

            foreach ($Db_Image_array as $k=>$v) {
                $Db_Image_array[$k]['img']=$host.$v['img'];
                $Db_Image_array[$k]['Services']='shop.view';
                $Db_Image_array[$k]['param']=array('shop_id'=>$v['shop_id']);
            }
            $jsondata =$Db_Image_array ;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '本地信息广告');
    }


    /**
     * 店铺管理：商家店铺添加之前必须请求的信息
     */

    public function beforeadd()
    {

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $ShopObj=new Shop();
        $UserObj=new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }
        /* 是否显示收费标识（即店铺认证）*/
        $hsk_is_shop_needverify =isset(Buddha::$buddha_array['cache']['config']['hsk_is_shop_needverify']) ?Buddha::$buddha_array['cache']['config']['hsk_is_shop_needverify']: 1;
        $is_authenticationcode = 0;
        if($hsk_is_shop_needverify==1){
            $is_authenticationcode = 1;
        }

        $jsondata = array();
        $jsondata['is_showsverify'] =$hsk_is_shop_needverify;
        $jsondata['is_authenticationcode'] =$is_authenticationcode;
        $jsondata['realname'] =Buddha_Atom_String::getApiValidStr($Db_User['realname']);
        $jsondata['mobile'] =Buddha_Atom_String::getApiValidStr($Db_User['mobile']);
        $jsondata['storetypelist'] = $ShopObj->getApiNatiureArr(0);
        $jsondata['shopclassification']['Services'] = 'shop.shopclassification';
        $jsondata['shopclassification']['param'] = array('api_islocalinfo'=>0);
        $jsondata['region']['Services'] = 'ajaxregion.getBelongFromFatherId';
        $jsondata['region']['param'] = array('father'=>1);


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "{$this->tablenamestr}管理：{$this->tablenamestr}添加之前的操作接口");
    }

    /**
     * 店铺管理：商家 店铺更新之前必须请求的信息
     */
    public function beforeupdate()
    {

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','shop_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $ShopObj=new Shop();
        $UserObj=new User();
        $ShopcatObj=new Shopcat();
        $RegionObj=new Region();
        $CommonObj=new Common();
        $AlbumObj=new Album();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        if(!$ShopObj->getShopidIsVerify($shop_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000009, '店铺内码ID无效！');
        }

        $fields = 'id AS shop_id, name, is_verify, shopcat_id, mobile, 
                    realname, mobile, tel, 
                    level0, level1, level2, level3, specticloc, 
                    lng, lat, storetype, property, bushour, 
                    myrange, brief, shopdesc, opentime, qq, 
                    wechatnumber, veifytime, veryfyendtime, codeimg';

        /*isdel=0 表示正常；isdel=5 表示选择了店铺认证单未支付的店铺*/
        $where=" id='{$shop_id}' AND user_id='$user_id' AND (isdel=0 or isdel=5)";


        $sql ="select {$fields} FROM {$this->prefix}shop WHERE {$where} ";

        $Db_Shop_array = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata = array();
        if(Buddha_Atom_Array::isValidArray($Db_Shop_array)){

            $Db_Shop= $Db_Shop_array[0];
            $nowtime=Buddha::$buddha_array['buddha_timestamp'];


            /**显示店铺认证的情况：
             *  1：店铺已经认证，并且认证的时间为，认证开始时间小于当前时间并且当前时间小于认证结束时间
             *  2：其余的都不显示
            */
            /*是否显示：店铺认证 的 认证码填写：0否；1是 */
            $is_authenticationcode = 0;
            if($Db_Shop['is_verify']==1 AND ($Db_Shop['veifytime']<=$nowtime AND $nowtime<=$Db_Shop['veryfyendtime'])){

                $hsk_is_shop_needverify =0;

            }else{
                $hsk_is_shop_needverify =isset(Buddha::$buddha_array['cache']['config']['hsk_is_shop_needverify']) ?Buddha::$buddha_array['cache']['config']['hsk_is_shop_needverify']: 1;

                /*是否显示：店铺认证 的 认证码填写：0否；1是 */
                if($hsk_is_shop_needverify==1){

                    $is_authenticationcode = 1;

                }
            }
            if(!Buddha_Atom_String::isValidString($Db_Shop['codeimg'])){
                /**二维码*/
                //////////////////////
                $shopinfo['id']=$shop_id;
                $CommonObj->codeimg($shopinfo);
                /////////////////////////////
            }




            unset($Db_Shop['codeimg']);
            unset($Db_Shop['veifytime']);
            unset($Db_Shop['veryfyendtime']);
            $Db_Shop['api_opentimestr']= $CommonObj->getDateStrOfTime($Db_Shop['opentime'],1,1);
//            $Db_Shop['img']=$host.$Db_Shop['img'];

            $jsondata['is_showsverify'] =$hsk_is_shop_needverify;
            $jsondata['is_authenticationcode'] =$is_authenticationcode;
            $Db_Shop['realname'] =Buddha_Atom_String::getApiValidStr($Db_User['realname']);
            $Db_Shop['mobile'] =Buddha_Atom_String::getApiValidStr($Db_User['mobile']);
            $jsondata['storetypelist'] = $ShopObj->getApiNatiureArr($Db_Shop['storetype']);

            $jsondata['api_shopcatname'] = $ShopcatObj->getShopcatNameToShopcatid($Db_Shop['shopcat_id']);

            $jsondata['provincecityname'] = $RegionObj->getDetailOfAdrressByRegionIdStr($Db_Shop['level1'],$Db_Shop['level2'],$Db_Shop['level3'],'>');
            $jsondata['shopclassification']['Services'] = 'shop.shopclassification';
            $jsondata['shopclassification']['param'] = array('api_islocalinfo'=>0);
            $jsondata['region']['Services'] = 'ajaxregion.getBelongFromFatherId';
            $jsondata['region']['param'] = array('father'=>1);
            $jsondata['shoplist'] = $Db_Shop;



            /*相册*/

            $Album_filed=' id as album_id';
            if($b_display==2){

                $Album_filed.=' , goods_thumb as img';
            }elseif ($b_display==1){

                $Album_filed.=' , goods_img as img';
            }
            $Album_where="table_name='{$this->tablename}' AND user_id='{$user_id}' AND goods_id='$shop_id'";
            $Album_orderby=" ORDER BY id DESC ";

            $Album_sql =" SELECT  {$Album_filed}  
                          FROM {$this->prefix}album 
                          WHERE {$Album_where} {$Album_orderby}  ";
            $Db_Album = $this->db->query($Album_sql)->fetchAll(PDO::FETCH_ASSOC);

            /*判断Album中有没有该图片，没有则要查询店铺里边的图片*/
            if(!Buddha_Atom_Array::isValidArray($Db_Album)){
                $Shop_fields='id as album_id';
                if($b_display==1){
                    $Shop_fields.=' ,medium AS img ';
                }elseif($b_display==2){
                    $Shop_fields.=' , small AS img ';
                }

                $Shop_sql =" SELECT  {$Shop_fields}  
                          FROM {$this->prefix}{$this->tablename} 
                          WHERE {$where} ";
                $Db_Album = $this->db->query($Shop_sql)->fetchAll(PDO::FETCH_ASSOC);
            }

            if(Buddha_Atom_Array::isValidArray($Db_Album)){
                foreach ($Db_Album as $k=>$v){
                    if(Buddha_Atom_String::isValidString($v['img'])) {

                        $Db_Album[$k]['img'] = $host . $v['img'];
                        $Db_Album[$k]['imgdel'] =array(
                            'Servers'=>'album.deleteimage',
                            'param'=>array(
                                'album_id'=>$v['album_id'],
                                'table_name'=>$this->tablename,
                            ),
                        ) ;

                    }else{
                        $Db_Album[$k]['img'] = '';
                        $Db_Album[$k]['album_id'] = '';
                    }
                }
            }


            if(Buddha_Atom_Array::isValidArray($Db_Album)){
                $jsondata['album']=$Db_Album;

            }else{


                $jsondata['tablename']=$this->tablename;
            }




        }
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '店铺编辑之前的操作接口');
    }

    /**
     * 店铺管理：商家 店铺添加
     */

    public function add()
  {

      /*这里必填 groupid 的原因是：  因为在添加店铺时要先添加用户，然后才确定店铺拥有者ID ；如果没有无法确定哪一个是店铺拥有者ID*/
      if (Buddha_Http_Input::checkParameter(array('usertoken','name','shopcat','realname','mobile','specticloc','image_arr'))) {
          Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
      }
      $UserObj = new User();

      $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
      $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

      $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','groupid','to_group_id');
      $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
      $user_id = $Db_User['id'];

      /*判断该角色ID 是否正确 拥有商家角色*/
      if(!strpos($Db_User['to_group_id'], '1')) {
          Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
      }



      $ShopObj=new Shop();
      $ShopcatObj=new Shopcat();
      $CommonObj=new Common();
      $RegionObj=new Region();
      $JsonimageObj = new Jsonimage();


      if(!$UserObj->isHasMerchantPrivilege($user_id)){

          Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');

      }

      $hsk_is_shop_needverify =isset(Buddha::$buddha_array['cache']['config']['hsk_is_shop_needverify']) ?Buddha::$buddha_array['cache']['config']['hsk_is_shop_needverify']: 1;//是否收费标识

      $mobile=Buddha_Http_Input::getParameter('mobile');
      $storetype=Buddha_Http_Input::getParameter('storetype');
      $level1=Buddha_Http_Input::getParameter('level1');
      $level2=Buddha_Http_Input::getParameter('level2');
      $level3=Buddha_Http_Input::getParameter('level3');
      $shopcat_id=Buddha_Http_Input::getParameter('shopcat');


      if(!$CommonObj->getMobilephoneiseffectiveBymobile($mobile)){
          Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000032, '手机号码不正确！');
      }

      if(!$RegionObj->isProvince($level1)){
          Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000001, '国家、省、市、区 中省的地区内码id不正确！');
      }

      if(!$RegionObj->isCity($level2)){
          Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中市的地区内码id不正确！');
      }

      if(!$RegionObj->isArea($level3)){
          Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中区的地区内码id不正确！');
      }

      if(!$ShopcatObj->getshopcatidIsEffective($shopcat_id)){
        Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000007, '店铺分类内码ID无效！');
      }


      if(!$ShopcatObj->getStoretypeidIsEffective($storetype)){
          Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000008, $this->tablenamestr.'性质内码ID无效！');
      }

      /*店铺名称*/
      $name=Buddha_Http_Input::getParameter('name');


      /*是否认证：*/
      $is_verify=Buddha_Http_Input::getParameter('is_verify');
      /*判断 $is_verify 的值是否是0,1*/
      if(!$CommonObj->isIdInDataEffectiveById($is_verify)){
          Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
      }



      $qq=Buddha_Http_Input::getParameter('qq');
      $wechatnumber=Buddha_Http_Input::getParameter('wechatnumber');
      $rzcodes=Buddha_Http_Input::getParameter('rzcodes');//认证码


      $realname=Buddha_Http_Input::getParameter('realname');

      $tel=Buddha_Http_Input::getParameter('tel');
      $opentime=Buddha_Http_Input::getParameter('opentime');

      $regionstr=Buddha_Http_Input::getParameter('regionstr');
      $specticloc=Buddha_Http_Input::getParameter('specticloc');

      $property=Buddha_Http_Input::getParameter('property');
      $bushour=Buddha_Http_Input::getParameter('bushour');
      $myrange=Buddha_Http_Input::getParameter('myrange');
      $brief=Buddha_Http_Input::getParameter('brief');
      $shopdesc=Buddha_Http_Input::getParameter('shopdesc');
      //地址ID转换转成文字
      $str = $RegionObj->getAddress($level3);
      //获取经纬度
      $lt= $ShopObj->location($str.$specticloc);

      $lng =$lt['lng'];
      $lat =$lt['lat'];

//      $lng = Buddha_Http_Input::getParameter('lng');
//      $lat = Buddha_Http_Input::getParameter('lat');
      $image_arr=Buddha_Http_Input::getParameter('image_arr');

      if($image_arr==''){
          Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000047, '相册不能为空！');

      }

      /*判断图片数组是否是Json*/
      if(Buddha_Atom_String::isJson($image_arr)){
          $image_arr = json_decode($image_arr);
      }


      /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
      $JsonimageObj->errorDieImageFromUpload($image_arr);


      $Db_agentrate = $UserObj->getSingleFiledValues(array('id', 'agentrate'), " isdel=0 and level3='{$level3}'and groupid=2");

      $datas = array();
      $datas['wechatnumber'] = $wechatnumber;
      $datas['qq'] = $qq;
      $datas['user_id'] = $user_id;
      $datas['agent_id'] = $Db_agentrate['id'];
      $datas['agentrate'] = $Db_agentrate['agentrate'];
      $datas['referral_id'] = 0;
      $datas['partnerrate'] = 0;
      $datas['shopcat_id'] = $shopcat_id;
      $datas['realname'] = $realname;
      $datas['name'] = $name;
      $datas['mobile'] = $mobile;
      $datas['tel'] = $tel;
      $datas['opentime'] = strtotime($opentime);;
      $datas['level0'] = 1;
      $datas['level1'] = $level1;
      $datas['level2'] = $level2;
      $datas['level3'] = $level3;
      $datas['regionstr'] = $regionstr;
      $datas['lng'] = $lng;
      $datas['lat'] = $lat;
      $datas['number'] = date("ymdHis") . mt_rand(1000, 9999);
      $datas['specticloc'] = $specticloc;
      $datas['storetype'] = $storetype;
      $datas['property'] = $property;
      $datas['bushour'] = $bushour;
      $datas['myrange'] = $myrange;
      $datas['brief'] = $brief;
      $datas['shopdesc'] = $shopdesc;
      $datas['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
      $datas['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
      if($hsk_is_shop_needverify == 1){
          if($is_verify == 1){

              /*店铺添加者选择了付费，但未支付则返回  5 */
              $datas['isdel'] = 5;
          }
      }

      $shop_id = $ShopObj->add($datas);

      if(!$shop_id){
          Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000001, $this->tablenamestr.'添加失败！');
      }


      $MoreImage = array();
     if(Buddha_Atom_Array::isValidArray($image_arr)){
         $savePath="storage/{$this->tablename}/{$shop_id}/";
         if(!file_exists(PATH_ROOT.$savePath)){
             mkdir(PATH_ROOT.$savePath, 0777);
         }

          foreach($image_arr as $k=>$v)
          {
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
              $ShopObj->addImageArrToShopAlbum($MoreImage,$shop_id,$savePath,$user_id);

              $ShopObj->setFirstGalleryImgToShop($shop_id,$user_id);
          }
     }

     $is_needcreateorder = 0;
     $Services = '';
     $param = array();

      /*只有在显示店铺认证的情况下，才添加订单*/
      if($hsk_is_shop_needverify == 1){

          if($is_verify==1 or $is_verify==2){
              $is_needcreateorder=1;
              $Services = 'payment.shopverify';
              $param = array('is_verify'=>$is_verify,'good_table'=>'shop','good_id'=>$shop_id,'rzcodes'=>$rzcodes);

          }

      }
        /**二维码*/
        //////////////////////
         $shop_where = "id='{$shop_id}' and user_id='{$user_id}'";
        $shopinfo = $ShopObj->getSingleFiledValues(array('small','name'),$shop_where);

        $ShopObj->createQrcodeForCodeSales($shop_id,$shopinfo['small'],$shopinfo['name'],$event='shop',$eventpage='info');

        /////////////////////////////
      $jsondata = array();
      $jsondata['db_isok'] =1;
      $jsondata['db_msg'] =$this->tablenamestr.'添加成功';
      $jsondata['user_id'] = $user_id;
      $jsondata['usertoken'] = $usertoken;
      $jsondata['shop_id'] = $shop_id;
      $jsondata['is_verify'] = $is_verify;
      $jsondata['is_needcreateorder'] = $is_needcreateorder;
      $jsondata['Services'] = $Services;
      $jsondata['param'] = $param;

      Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'添加');
  }


    /**
     * 店铺管理：商家 店铺更新
     */

    public function update()
    {

        if (Buddha_Http_Input::checkParameter(array('usertoken','name','shopcat','realname','mobile','specticloc','shop_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $ShopObj=new Shop();
        $ShopcatObj=new Shopcat();
        $CommonObj=new Common();
        $RegionObj=new Region();
        $JsonimageObj = new Jsonimage();

        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id'):0;


        if(!$UserObj->isHasMerchantPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');

        }

        /*判断店铺是否存在（并且是否属于当前门户）*/

        if(!$CommonObj->isToUserByTablenameAndTableid($this->tablename,$shop_id,$user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000009	, $this->tablename.'内码ID无效！');
        }

        /*判断该店铺是否已经认证过了*/
        $Db_Shop = $ShopObj->getSingleFiledValues(array('is_verify','veifytime','veryfyendtime'),"id='{$shop_id}' AND isdel=0 OR isdel=5 ");
        $nowtime = Buddha::$buddha_array['buddha_timestamp'];


        /**
         * 显示店铺认证的情况
         *  1、店铺已认证$Db_Shop['is_verify']== 1   个并且当前时间在认证开始和结束时间之间
         *  2、除去 1 都按照后台实际情况来
         * 除去上述两种情况外：都是已经认证的情况：则不需要去判断是否要付店铺认证费  即：$hsk_is_shop_needverify=0
         */


        if($Db_Shop['is_verify']==1 AND ( $Db_Shop['veifytime'] <=$nowtime AND $nowtime <=$Db_Shop['veryfyendtime']))
        {
            $hsk_is_shop_needverify=0;
        }else{

            $hsk_is_shop_needverify =isset(Buddha::$buddha_array['cache']['config']['hsk_is_shop_needverify']) ?Buddha::$buddha_array['cache']['config']['hsk_is_shop_needverify']: 1;//是否收费标识
        }


        $mobile=Buddha_Http_Input::getParameter('mobile');
        $storetype=Buddha_Http_Input::getParameter('storetype');
        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');
        $shopcat_id=Buddha_Http_Input::getParameter('shopcat');



        if(!$CommonObj->getMobilephoneiseffectiveBymobile($mobile)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000032, '手机号码不正确！');
        }

        if(!$RegionObj->isProvince($level1)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000001, '国家、省、市、区 中省的地区内码id不正确！');
        }

        if(!$RegionObj->isCity($level2)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中市的地区内码id不正确！');
        }

        if(!$RegionObj->isArea($level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中区的地区内码id不正确！');
        }


        /* 判断店铺性质ID 是否有效*/
        if(!$ShopcatObj->getStoretypeidIsEffective($storetype)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000008, '店铺性质内码ID无效！');
        }

        $shop_id=Buddha_Http_Input::getParameter('shop_id');

        /*店铺名称*/
        $name=Buddha_Http_Input::getParameter('name');


        /*是否认证：*/
        $is_verify=Buddha_Http_Input::getParameter('is_verify');
        /*判断 $is_verify 的值是否是0,1*/
        if(!$CommonObj->isIdInDataEffectiveById($is_verify)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }


        $qq=Buddha_Http_Input::getParameter('qq');
        $wechatnumber=Buddha_Http_Input::getParameter('wechatnumber');
        $rzcodes=Buddha_Http_Input::getParameter('rzcodes');//认证码


        $realname=Buddha_Http_Input::getParameter('realname');

        $tel=Buddha_Http_Input::getParameter('tel');
        $opentime=Buddha_Http_Input::getParameter('opentime');

        $regionstr=Buddha_Http_Input::getParameter('regionstr');
        $specticloc=Buddha_Http_Input::getParameter('specticloc');

        $property=Buddha_Http_Input::getParameter('property');
        $bushour=Buddha_Http_Input::getParameter('bushour');
        $myrange=Buddha_Http_Input::getParameter('myrange');
        $brief=Buddha_Http_Input::getParameter('brief');


        $shopdesc=Buddha_Http_Input::getParameter('shopdesc');
        //地址ID转换转成文字
        $str = $RegionObj->getAddress($level3);
        //获取经纬度
        $lt= $ShopObj->location($str.$specticloc);

        $lng =$lt['lng'];
        $lat =$lt['lat'];
//        $lng = Buddha_Http_Input::getParameter('lng');
//        $lat = Buddha_Http_Input::getParameter('lat');
        $image_arr=Buddha_Http_Input::getParameter('image_arr');

        /*判断图片数组是否是Json*/
        if(Buddha_Atom_String::isJson($image_arr)){
            $image_arr = json_decode($image_arr);
        }
        /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($image_arr);

//        $Db_agentrate = $UserObj->getSingleFiledValues(array('id', 'agentrate'), " isdel=0 and level3='{$level3}'and groupid=2");
        $datas = array();
        $datas['wechatnumber'] = $wechatnumber;
        $datas['qq'] = $qq;
//        $datas['user_id'] = $user_id;
//        $datas['agent_id'] = $Db_agentrate['id'];
//        $datas['agentrate'] = $Db_agentrate['agentrate'];
//        $datas['referral_id'] = 0;
//        $datas['partnerrate'] = 0;
        $datas['shopcat_id'] = $shopcat_id;
        $datas['realname'] = $realname;
        $datas['name'] = $name;
        $datas['mobile'] = $mobile;
        $datas['tel'] = $tel;
        $datas['opentime'] = strtotime($opentime);
        $datas['level0'] = 1;
        $datas['level1'] = $level1;
        $datas['level2'] = $level2;
        $datas['level3'] = $level3;
        $datas['regionstr'] = $regionstr;
        $datas['lng'] = $lng;
        $datas['lat'] = $lat;
        $datas['specticloc'] = $specticloc;
        $datas['storetype'] = $storetype;
        $datas['property'] = $property;
        $datas['bushour'] = $bushour;
        $datas['myrange'] = $myrange;
        $datas['brief'] = $brief;
        $datas['shopdesc'] = $shopdesc;

       $Db_Shop_num= $ShopObj->edit($datas,$shop_id);
        /*
         *说明： 这里不适用 $Db_Shop_num 判断的原因是：如果只是修改了图片，而其他信息没有修改 $Db_Shop_num 则这里返回的为0 后面就不会继续执行了(如果只更改了图片，后面将不再执行，所以这里没用$Db_Shop_num)
         * */


        if(!$shop_id){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000011, '店铺更新/编辑失败！');
        }



        if(Buddha_Atom_Array::isValidArray($image_arr)){

            /*
             *     在更新相册之前先判断用户最大相册数量是否符合要求，如果不符合需要删除
             */
            $ShopObj->isImGtMax($shop_id,$table_name='shop',$user_id,$Imgmax=1,$tableimg_name='album');

            $savePath="storage/{$this->tablename}/{$shop_id}/";
            if(!file_exists(PATH_ROOT.$savePath)){
                mkdir(PATH_ROOT.$savePath, 0777);
            }
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


            if(Buddha_Atom_Array::isValidArray($MoreImage)){
                $ShopObj->addImageArrToShopAlbum($MoreImage,$shop_id,$savePath,$user_id);
                $ShopObj->setFirstGalleryImgToShop($shop_id,$user_id);
            }
        }




        $is_needcreateorder = 0;
        $Services = '';
        $param = array();

        /*只有在显示店铺认证的情况下，才添加订单*/
        if($hsk_is_shop_needverify == 1){

            if($is_verify==1 or $is_verify==2){
                $is_needcreateorder=1;
                $Services = 'payment.shopverify';
                $param = array('is_verify'=>$is_verify,'good_table'=>'shop','good_id'=>$shop_id,'rzcodes'=>$rzcodes);

            }
        }
//        if($hsk_is_shop_needverify == 1){
//            if($is_verify == 1){
//
//                /*店铺添加者选择了付费，但未支付则返回  5 */
//                $datas['isdel'] = 5;
//            }
//        }
        $jsondata = array();
        $jsondata['db_isok'] =1;
        $jsondata['db_msg'] =$this->tablenamestr.'更新成功';
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['shop_id'] = $shop_id;
        $jsondata['is_verify'] = $is_verify;
        $jsondata['is_needcreateorder'] = $is_needcreateorder;
        $jsondata['Services'] = $Services;
        $jsondata['param'] = $param;


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'更新/编辑');
    }

    /**
     *  店铺管理: 商家 店铺的启用和停用
     */
    public function businessEnableDisabledShop()
    {

        if (Buddha_Http_Input::checkParameter(array('usertoken','shop_id','state'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $ShopObj= new Shop();
        $CommonObj= new Common();
        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id'):0;
        $state = (int)Buddha_Http_Input::getParameter('state')?(int)Buddha_Http_Input::getParameter('state'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        /*商家停用功能*/
        if(!$UserObj->isHasMerchantPrivilege($user_id) ){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有商家用户权限，你还未申请商家角色！');
        }

        /*检测店铺id和用户Id是否匹配*/


        if(!$CommonObj->isToUserByTablenameAndTableid($this->tablename,$shop_id,$user_id))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        };

        $Db_Shop = $ShopObj->businessEnableDisabledShop($shop_id,$user_id);

        $jsondata = array();
        $jsondata['db_isok'] = $Db_Shop['is_ok'];
        $jsondata['db_msg'] =  $Db_Shop['is_msg'];
        $jsondata['buttonname'] =  $Db_Shop['buttonname'];
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['shop_id'] = $shop_id;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "商家启用/停用自己店铺");

    }



    /**
     * 店铺管理：代理商 店铺的启用和停用
     */
    public function agentmanagestoporenabled()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken','shop_id','isdel')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $ShopObj= new Shop();
        $CommonObj= new Common();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):'';
        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $isdel = (int)Buddha_Http_Input::getParameter('isdel')?(int)Buddha_Http_Input::getParameter('isdel'):0;

        if(!$CommonObj->isOwnerBelongToAgentByLeve3($this->tablename,$shop_id,$Db_User['level3']))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }


        if(!$UserObj->isHasAgentPrivilege($user_id) ){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商用户权限，你还未申请代理商角色！');
        }


        $DB_Shop = $ShopObj->agentsEnableDisabledShop($shop_id,$isdel,$user_id);

        $jsondata = array();
        $jsondata['db_isok'] = $DB_Shop['is_ok'];
        $jsondata['db_msg'] =  $DB_Shop['is_msg'];
        $jsondata['buttonname'] =  $DB_Shop['buttonname'];
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['shop_id'] = $shop_id;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, " 代理商启用/停用商家店铺");

    }


    /**
     * 导航
     */
    public function navigation()
    {
        if (Buddha_Http_Input::checkParameter(array('shop_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $ShopObj=new Shop();
        $shop_id = Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;
        if(!$ShopObj->isShopByShopid($shop_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }

        $jsondata='';
        $Db_shop= $ShopObj->getSingleFiledValues(array('specticloc','lng','lat'),"id='{$shop_id}'");

        if(Buddha_Atom_Array::getApiKeyOfValueByArrayString($Db_shop)){
            $url="http://apis.map.qq.com/tools/routeplan/eword={$Db_shop['specticloc']}&epointx={$Db_shop['lng']}&epointy={$Db_shop['lat']}&policy=1?referer=myapp&key=HM5BZ-ICHKU-VDNVI-27RMJ-YZRCQ-P5BTP";
            $jsondata =$url;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '导航');

    }

    /**
     * 手机号验证
     * 普通会员不具备该功能
     */
    public function mobilenumberverification()
    {
        if (Buddha_Http_Input::checkParameter(array('mobile','usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','partnerrate');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        $mobile = Buddha_Http_Input::getParameter('mobile')?Buddha_Http_Input::getParameter('mobile'):'';

        if(!Buddha_Atom_String::isValidString($mobile)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误');
        }

        $num=$UserObj->countRecords("mobile='{$mobile}' and isdel=0 ");

        $data=array();
        if(!$num){
            $data['is_ok']=1;
            $data['msg']='手机号可用';
        }else{
            $data['is_ok']=0;
            $data['msg']='手机号已被使用';
        }
        $jsondata=$data;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '手机号验证');

    }



    /**
     * 账号验证
     * 普通会员不具备该功能
     */
    public function usernameverification()
    {

        if (Buddha_Http_Input::checkParameter(array('username','usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','partnerrate');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];





        $username = Buddha_Http_Input::getParameter('username');

        if(!Buddha_Atom_String::isValidString($username)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误');
        }
        $num=$UserObj->countRecords("username='{$username}' and isdel=0 ");

        $data=array();
        if(!$num){
            $data['is_ok']=1;
            $data['msg']='用户名可用';
        }else{
            $data['is_ok']=0;
            $data['msg']='用户名已被使用';
        }
        $jsondata=$data;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '用户名验证');

    }


    /**
     * 店铺认证：验证 认证码（暂时不用了：使用 payment.shopverify.php）
     *普通会员不具备该功能
     */
    public function verifycode(){

        if (Buddha_Http_Input::checkParameter(array('rzcodes','usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','partnerrate');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        $certifObj = new Certification();
        $orderObj = new Order();
        $rzcodes=Buddha_Http_Input::getParameter('rzcodes');//获取用户所填写的认证码
        if(!Buddha_Atom_String::isValidString($rzcodes)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误');
        }

        $time = time();
        $certifinfo = $certifObj->countRecords("code='{$rzcodes}' and is_use=0 and overdue_time>{$time}");
        $orderinfo = $orderObj->countRecords("pay_status = 1 and payname = '{$rzcodes}'");
        $jsondata=array();
        if($certifinfo && !$orderinfo){
            $data['isok'] = 1;
            $data['info'] = '认证码验证通过';
        }else{
            $data['isok'] =0;
            $data['info'] = '您输入的认证码已使用，请联系客服';
        }

        $jsondata=$data;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '认证码验证');

    }




    /**
     * 转发有赏
     */

    public function sharingmoney()
    {
        $host = Buddha::$buddha_array['host'];

        //usertoken 必填; shop_id 必填 店铺内码ID;types 必填 0 好友(hao;1为朋友圈(quan);

        if (Buddha_Http_Input::checkParameter(array('usertoken','shop_id','types')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $ShopObj=new Shop();
        $rechargeObj = new Recharge();
        $UserObj = new User();
        $billObj = new Bill();
        $orderObj = new Order();
        $sharingObj = new Sharing();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','groupid');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id'):0;
        $types = (int)Buddha_Http_Input::getParameter('types')?(int)Buddha_Http_Input::getParameter('types'):0;

        $shopinfo = $ShopObj->getSingleFiledValues('',"id='{$shop_id}'");

        $rechargeinfo = $rechargeObj->getSingleFiledValues(array('balance','forwarding_money','is_open','time_period','forwarding_money','hao_forwarding_money','hao_forwarding_money','hao_forwarding_money'),"uid={$shopinfo['user_id']}");

        ////////分享有赏
        if($rechargeinfo && $rechargeinfo['balance'] >= $rechargeinfo['forwarding_money'] && $rechargeinfo['is_open'] == 1)
        {

            $sharinginfo = $sharingObj ->getSingleFiledValues('',"uid={$user_id} and shop_id={$shopinfo['id']}");//该用户有没有分享过次店铺

            if($sharinginfo)
            {
                if($rechargeinfo['time_period'])
                {
                    $set_time = explode($rechargeinfo['time_period'],'-');//转发有赏起始时间段
                    $starttime = strtotime(date('Y-m-d').' '.$set_time[0].':00:00');
                    $endtime = strtotime(date('Y-m-d').' '.$set_time[1].':00:00');
                }
                /***同家店铺分享每天分享第一次在此分享才有赏金，转发有时间段限制***/
                if((time() - $sharinginfo['createtime']) >= 86400 && $starttime<=time() && $endtime>=time()){
                    $times['createtime'] = strtotime(date('Ymd'));
                    //$re = $sharingObj->edit($times,$sharinginfo['id']);//更新分享时间
                    $sql = "UPDATE {$this->prefix}sharing SET createtime='{$times['createtime']}' WHERE id='{$sharinginfo['id']}'";
                    if($this->db->query($sql))
                    {
                        /**↓↓↓↓↓↓↓↓↓↓↓↓↓ 更新账户余额 ↓↓↓↓↓↓↓↓↓↓↓↓↓**/
                        $banlance = $UserObj->getSingleFiledValues(array('id','banlance'),"id={$user_id}");
                         //                        if($types == 'quan'){
                        if($types == 1)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['forwarding_money'];//增加账户余额
                         //                        }elseif($types == 'hao'){
                        }elseif($types == 0){// 0 好友(hao)；1为朋友圈(quan);
                            $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['hao_forwarding_money'];//增加账户余额
                        }
                        $UserObj->edit($dataes,$user_id);//更新账户余额
                        /*** ↑↑↑↑↑↑↑↑↑ 更新账户余额 ↑↑↑↑↑↑↑↑↑****/


                        /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 生成订单和账单明细 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/
                        $data = array();
                        $data['good_id'] = $shopinfo['id'];
                        $data['user_id'] = $user_id;
                        $data['order_sn'] = $orderObj->birthOrderId($user_id);
                        $data['good_table'] = 'shop';
                         //                      if($types == 'quan'){
                        if($types == 1)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $data['goods_amt'] = $rechargeinfo['forwarding_money'];
                            $data['final_amt'] = $rechargeinfo['forwarding_money'];
                         //                        }elseif($types == 'hao'){
                        }elseif($types == 0)
                        {// 0 好友(hao)；1为朋友圈(quan);

                            $data['goods_amt'] = $rechargeinfo['hao_forwarding_money'];
                            $data['final_amt'] = $rechargeinfo['hao_forwarding_money'];
                        }
                        $data['pay_status'] =1;
                        $data['pay_type'] = 'balance';
                        $data['order_type'] = 'forwarding_money';
                        $data['payname'] = '余额支付转发有赏';
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        $order_id=$orderObj->add($data);
                        /*** ↑↑↑↑↑↑↑↑↑ 生成订单和账单明细 ↑↑↑↑↑↑↑↑↑****/


                        /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 转发赏金 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/
                        $order_sn = $orderObj->getSingleFiledValues(array('order_sn'),"id={$order_id}");
                        $data = array();
                        $data['user_id'] = $user_id;
                        $data['order_sn'] = $order_sn['order_sn'];
                        $data['order_id'] = $order_id;
                        $data['is_order'] = 1;
                        $data['order_type'] = 'forwarding.money';
                        $data['order_desc']  ='转发赏金';
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                         //                        if($types == 'quan'){
                        if($types == 1)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $data['billamt'] = $rechargeinfo['forwarding_money'];
                         //                        }elseif($types == 'hao'){
                        }elseif($types == 0)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $data['billamt'] = $rechargeinfo['hao_forwarding_money'];
                        }
                        $billObj->add($data);
                        /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 转发赏金 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/


                        /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 商家转发后资金减少的记录 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/
                        $data = array();//
                        $data['user_id'] = $shopinfo['user_id'];
                        $data['is_order'] = 0;
                        $data['order_type'] = 'forwarding.money';
                        $data['order_desc']  ='扣除转发赏金';
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                         //                        if($types == 'quan'){
                        if($types == 1)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $data['billamt'] = '-' . $rechargeinfo['forwarding_money'];
                         //                        }elseif($types == 'hao'){
                        }elseif($types == 0)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $data['billamt'] = '-' . $rechargeinfo['hao_forwarding_money'];
                        }
                        $billObj->add($data);
                        /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 商家转发后资金减少的记录 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/

                        /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 改变对应的充值表余额 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/
                         //                        if($types == 'quan'){
                        if($types == 1)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['forwarding_money'];
                            $bounty = $rechargeinfo['forwarding_money'];
                         //                        }elseif($types == 'hao'){
                        }elseif($types == 0)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['hao_forwarding_money'];
                            $bounty = $rechargeinfo['hao_forwarding_money'];
                        }
                        $rechargeObj->edit($rech,$rechargeinfo['id']);
                        /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 改变对应的充值表余额 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/

                        $data = array();
                        $data['is_ok'] = 'true';
                        $data['is_msg'] = '赏金已充入余额';
                        $data['order_sn'] = $order_sn['order_sn'];
                        $data['bounty'] = $bounty;

                    }

                }elseif((time() - $sharinginfo['createtime']) >= 86400)
                { /***转发没有时间段限制**/

                    $times['createtime'] = strtotime(date('Ymd'));
                    //$re = $sharingObj->edit($times,$sharinginfo['id']);//更新分享时间
                    //$sql = "select count(*) as total from {$this->prefix}lease where {$where} {$like}";
                    $sql = "UPDATE {$this->prefix}sharing SET createtime='{$times['createtime']}' WHERE id='{$sharinginfo['id']}'";
                    if($this->db->query($sql))
                    {
                        /**↓↓↓↓↓↓↓↓↓↓↓↓↓ 更新账户余额 ↓↓↓↓↓↓↓↓↓↓↓↓↓**/
                        $banlance = $UserObj->getSingleFiledValues(array('id','banlance'),"id={$user_id}");
                        //   if($types == 'quan'){
                        if($types == 1)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['forwarding_money'];//增加账户余额
                        //   }elseif($types == 'hao'){
                        }elseif($types == 0)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['hao_forwarding_money'];//增加账户余额
                        }
                        $UserObj->edit($dataes,$user_id);//
                        /*** ↑↑↑↑↑↑↑↑↑ 更新账户余额 ↑↑↑↑↑↑↑↑↑****/

                        /**↓↓↓↓↓↓↓↓↓↓↓↓↓ 生成订单和账单明细 ↓↓↓↓↓↓↓↓↓↓↓↓↓**/
                        $data = array();
                        $data['good_id'] = $shopinfo['id'];
                        $data['user_id'] = $user_id;
                        $data['order_sn'] = $orderObj->birthOrderId($user_id);
                        $data['good_table'] = 'shop';
                        // if($types == 'quan'){
                        if($types == 1)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $data['goods_amt'] = $rechargeinfo['forwarding_money'];
                            $data['final_amt'] = $rechargeinfo['forwarding_money'];
                        // }elseif($types == 'hao'){
                        }elseif($types == 0)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $data['goods_amt'] = $rechargeinfo['hao_forwarding_money'];
                            $data['final_amt'] = $rechargeinfo['hao_forwarding_money'];
                        }

                        $data['pay_status'] =1;
                        $data['pay_type'] = 'balance';
                        $data['order_type'] = 'forwarding_money';
                        $data['payname'] = '余额支付转发有赏';
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        $order_id=$orderObj->add($data);
                        /*** ↑↑↑↑↑↑↑↑↑ 生成订单和账单明细 ↑↑↑↑↑↑↑↑↑****/

                        /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 转发赏金 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/

                        $order_sn = $orderObj->getSingleFiledValues(array('order_sn'),"id={$order_id}");
                        $data = array();
                        $data['user_id'] = $user_id;
                        $data['order_sn'] = $order_sn['order_sn'];
                        $data['order_id'] = $order_id;
                        $data['is_order'] = 1;
                        $data['order_type'] = 'forwarding.money';
                        $data['order_desc']  ='转发赏金';
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                         //                        if($types == 'quan'){
                        if($types == 1)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $data['billamt'] = $rechargeinfo['forwarding_money'];
                         //                        }elseif($types == 'hao'){
                        }elseif($types == 0)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $data['billamt'] = $rechargeinfo['hao_forwarding_money'];
                        }
                        $billObj->add($data);
                        /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 转发赏金 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/


                        /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 商家转发后资金减少的记录 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/
                        $data = array();
                        $data['user_id'] = $shopinfo['user_id'];
                        $data['is_order'] = 0;
                        $data['order_type'] = 'forwarding.money';
                        $data['order_desc']  ='扣除转发赏金';
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        //                        if($types == 'quan'){
                        if($types == 1)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $data['billamt'] = '-' . $rechargeinfo['forwarding_money'];
                        //                        }elseif($types == 'hao'){
                        }elseif($types == 0)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $data['billamt'] = '-' . $rechargeinfo['hao_forwarding_money'];
                        }
                        $billObj->add($data);
                        /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 商家转发后资金减少的记录 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/


                        /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 改变对应的充值表余额 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/
                        //                        if($types == 'quan'){
                        if($types == 1)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['forwarding_money'];
                            $bounty = $rechargeinfo['forwarding_money'];
                        //                        }elseif($types == 'hao'){
                        }elseif($types == 0)
                        {// 0 好友(hao)；1为朋友圈(quan);
                            $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['hao_forwarding_money'];
                            $bounty = $rechargeinfo['hao_forwarding_money'];
                        }
                        $rechargeObj->edit($rech,$rechargeinfo['id']);
                        /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 改变对应的充值表余额 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/

                        $data = array();

                        $data['is_ok'] = 'true';
                        $data['is_msg'] = '赏金已充入余额';
                        $data['order_sn'] = $order_sn['order_sn'];
                        $data['bounty'] = $bounty;


                    }
                }
            }else{
                $datass['uid'] = $user_id;
                $datass['shop_id'] = $shopinfo['id'];
                $datass['createtime'] = strtotime(date('Ymd'));
                /**添加记录*/
                if($sharingObj->add($datass))
                {
                    /**↓↓↓↓↓↓↓↓↓↓↓↓↓ 更新账户余额 ↓↓↓↓↓↓↓↓↓↓↓↓↓**/
                    $banlance = $UserObj->getSingleFiledValues(array('id','banlance'),"id={$user_id}");
                    //    if($types == 'quan'){
                    if($types == 1)
                    {// 0 好友(hao)；1为朋友圈(quan);
                        $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['forwarding_money'];//增加账户余额
                    //      }elseif($types == 'hao'){
                    }elseif($types == 0)
                    {// 0 好友(hao)；1为朋友圈(quan);
                        $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['hao_forwarding_money'];//增加账户余额
                    }
                    $UserObj->edit($dataes,$user_id);//更新账户余额
                    /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 更新账户余额 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/


                    /**↓↓↓↓↓↓↓↓↓↓↓↓↓ 生成订单和账单明细 ↓↓↓↓↓↓↓↓↓↓↓↓↓**/
                    $data = array();
                    $data['good_id'] = $shopinfo['id'];
                    $data['user_id'] = $user_id;
                    $data['order_sn'] = $orderObj->birthOrderId($user_id);
                    $data['good_table'] = 'shop';
                    //                    if($types == 'quan'){
                    if($types == 1)
                    {// 0 好友(hao)；1为朋友圈(quan);
                        $data['goods_amt'] = $rechargeinfo['forwarding_money'];
                        $data['final_amt'] = $rechargeinfo['forwarding_money'];
                    //                    }elseif($types == 'hao'){
                    }elseif($types == 0)
                    {// 0 好友(hao)；1为朋友圈(quan);
                        $data['goods_amt'] = $rechargeinfo['hao_forwarding_money'];
                        $data['final_amt'] = $rechargeinfo['hao_forwarding_money'];
                    }
                    $data['pay_status'] =1;
                    $data['pay_type'] = 'balance';
                    $data['order_type'] = 'forwarding_money';
                    $data['payname'] = '余额支付转发有赏';
                    $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                    $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                    $order_id=$orderObj->add($data);
                    /*** ↑↑↑↑↑↑↑↑↑ 生成订单和账单明细 ↑↑↑↑↑↑↑↑↑****/

                    /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 转发赏金 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/
                    $order_sn = $orderObj->getSingleFiledValues(array('order_sn'),"id={$order_id}");
                    $data = array();
                    $data['user_id'] = $user_id;
                    $data['order_sn'] = $order_sn['order_sn'];
                    $data['order_id'] = $order_id;
                    $data['is_order'] = 1;
                    $data['order_type'] = 'forwarding.money';
                    $data['order_desc']  ='转发赏金';
                    $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                    $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                    //                    if($types == 'quan'){
                    if($types == 1)
                    {// 0 好友(hao)；1为朋友圈(quan);
                        $data['billamt'] = $rechargeinfo['forwarding_money'];
                    //                    }elseif($types == 'hao'){
                    }elseif($types == 0)
                    {// 0 好友(hao)；1为朋友圈(quan);
                        $data['billamt'] = $rechargeinfo['hao_forwarding_money'];
                    }
                    $billObj->add($data);
                    /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 转发赏金 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/


                    /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 商家转发后资金减少的记录 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/
                    $data = array();
                    $data['user_id'] = $shopinfo['user_id'];
                    $data['is_order'] = 0;
                    $data['order_type'] = 'forwarding.money';
                    $data['order_desc']  ='扣除转发赏金';
                    $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                    $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                    //                    if($types == 'quan'){
                    if($types == 1)
                    {// 0 好友(hao)；1为朋友圈(quan);
                        $data['billamt'] = '-' . $rechargeinfo['forwarding_money'];
                    //                    }elseif($types == 'hao'){
                    }elseif($types == 0)
                    {// 0 好友(hao)；1为朋友圈(quan);
                        $data['billamt'] = '-' . $rechargeinfo['hao_forwarding_money'];
                    }
                    $billObj->add($data);
                    /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 商家转发后资金减少的记录 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/


                    /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 改变对应的充值表余额 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/
                   //                    if($types == 'quan'){
                    if($types == 1)
                    {// 0 好友(hao)；1为朋友圈(quan);
                        $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['forwarding_money'];
                        $bounty = $rechargeinfo['forwarding_money'];
                    //                    }elseif($types == 'hao'){
                    }elseif($types == 0)
                    {// 0 好友(hao)；1为朋友圈(quan);
                        $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['hao_forwarding_money'];
                        $bounty = $rechargeinfo['hao_forwarding_money'];
                    }
                    $rechargeObj->edit($rech,$rechargeinfo['id']);
                    /*** ↓↓↓↓↓↓↓↓↓↓↓↓↓ 改变对应的充值表余额 ↓↓↓↓↓↓↓↓↓↓↓↓↓****/

                    $data = array();
                    $data['is_ok'] = 'true';
                    $data['is_msg'] = '赏金已充入余额';
                    $data['order_sn'] = $order_sn['order_sn'];
                    $data['bounty'] = $bounty;

                }
            }
        }else{
            $data = array();
            $data['is_ok'] = 'false';
            $data['info'] = '';
        }
        $jsondata=$data;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '转发有赏');

    }





    /**
     * @param int $id
     * @return string
     * 推荐属于该店铺下的产品
     */
    public function recommendBelongShop()
    {
        $CommonObj = new Common();
        $MysqlplusObj = new Mysqlplus();

        if (Buddha_Http_Input::checkParameter(array('shop_id','table_name','table_id','b_display')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }


        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id'):0;
        $table_id = (int)Buddha_Http_Input::getParameter('table_id')?(int)Buddha_Http_Input::getParameter('table_id'):0;
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?(int)Buddha_Http_Input::getParameter('b_display'):2;
        $table_name = Buddha_Http_Input::getParameter('table_name')?Buddha_Http_Input::getParameter('table_name'):'';


        if(!$MysqlplusObj->isValidTable($table_name))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误');
        }

        if(!$CommonObj->isIdByTablenameAndTableid($this->tablename,$shop_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000009, '店铺内码ID无效！');
        }
        if(!$CommonObj->isIdByTablenameAndTableid($table_name,$table_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 30000002, '内码id无效！');
        }
        $jsondata = array();
        $jsondata['activity'] = array();
        $jsondata['demand'] = array();
        $jsondata['heartpro'] = array();
        $jsondata['lease'] = array();
        $jsondata['recruit'] = array();
        $jsondata['singleinformation'] = array();
        $jsondata['supply'] = array();

        if(Buddha_Atom_String::isValidString($shop_id))
        {
            $Activity_id = $Demand_id = $Heartpro_id = $Lease_id = $Recruit_id = $Singleinformation_id = $Supply_id = 0;

            if($table_name=='Activity')
            {
                $Activity_id = $table_id;
            }elseif($table_name=='Demand')
            {
                $Demand_id = $table_id;
            }elseif($table_name=='Heartpro')
            {
                $Heartpro_id = $table_id;
            }elseif($table_name=='Lease')
            {
                $Heartpro_id = $table_id;
            }elseif($table_name=='Recruit')
            {
                $Recruit_id = $table_id;
            }elseif($table_name=='Singleinformation')
            {
                $Singleinformation_id = $table_id;
            }elseif($table_name=='Supply')
            {
                $Supply_id = $table_id;
            }

            $ActivityObj = new Activity();
            $DemandObj = new Demand();
            $HeartproObj = new Heartpro($shop_id,$Heartpro_id);
            $LeaseObj = new Lease($shop_id,$Lease_id);
            $RecruitObj = new Recruit($shop_id,$Recruit_id);
            $SingleinformationObj = new Singleinformation($shop_id,$Singleinformation_id);
            $SupplyObj = new Supply($shop_id,$Supply_id);


            $Db_Activity  = $ActivityObj->recommendBelongShop($shop_id,$Activity_id,$b_display);
            $Db_Demand = $DemandObj->recommendBelongShop($shop_id,$Demand_id,$b_display);
            $Db_Heartpro = $HeartproObj->recommendBelongShop($shop_id,$Heartpro_id,$b_display);
            $Db_Lease = $LeaseObj->recommendBelongShop($shop_id,$Lease_id,$b_display);
            $Db_Recruit = $RecruitObj->recommendBelongShop($shop_id,$Recruit_id,$b_display);
            $Db_Singleinformation = $SingleinformationObj->recommendBelongShop($shop_id,$Singleinformation_id,$b_display);
            $Db_Supply = $SupplyObj->recommendBelongShop($shop_id,$Supply_id,$b_display);

            $jsondata['activity'] = $Db_Activity;
            $jsondata['demand'] = $Db_Demand;
            $jsondata['heartpro'] = $Db_Heartpro;
            $jsondata['lease'] = $Db_Lease;
            $jsondata['recruit'] = $Db_Recruit;
            $jsondata['singleinformation'] = $Db_Singleinformation;
            $jsondata['supply'] = $Db_Supply;

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '店铺下所有的推荐');

    }









}