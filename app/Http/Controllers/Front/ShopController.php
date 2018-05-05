<?php

/**
 * Class ShopController
 */
class ShopController extends Buddha_App_Action{
    protected $tablenamestr;
    protected $tablename;
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='店铺';
        $this->tablename='shop';
    }

    public function index()
    {
        $user_id = (int)Buddha_Http_Cookie::getCookie('uid');
        $ShopObj = new Shop();
        $OrderObj = new Order();
        $id = (int)Buddha_Http_Input::getParameter('id');
        $order_id = (int)Buddha_Http_Input::getParameter('order_id');

        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误','index.php?a=index&c=list');
        }
        $shop = $ShopObj->fetch($id);
        if(!$shop){
            Buddha_Http_Head::redirectofmobile('信息不存在','index.php?a=index&c=list');
        }
        if($shop['is_verify'] == 0){
            $shop['shopdesc']=preg_replace("/13[12356789]{1}\d{8}|15[1235689]\d{8}|188\d{8}/", '${1}*****${2}', $shop['shopdesc']);
            $shop['shopdesc']=preg_replace("/\d{3,4}-\d{7,8}/", '${1}*****${2}', $shop['shopdesc']); 
        }

 ///////////////////////////////////   
        //判断用户是否认证：非认证显示7天（is_verify）
        if ($shop['is_verify']==0){
            //$UserObj=new User();
            //$user = $UserObj->getSingleFiledValues(array('onlineregtime'),"id={$shop['user_id']}");
            //$createtime=$user['onlineregtime'];//免费7天的开始时间
            $endtime = strtotime($shop['createtimestr']) + 7*86400;//免费7天的结束时间
            $newtime=time();
            if($newtime< $endtime){
                $shop['verify']=1;
            }else{
                $shop['verify']=0;
            }
        }
        //print_r($shop);
///////////////////////////////////
        $start = time()-15*60; //付费查看电话过期时间
        if($user_id){
            $see= $OrderObj->countRecords("user_id='{$user_id}' and good_id='{$id}' and pay_status=1 and createtime>=$start");
        }else{
            $see= $OrderObj->countRecords("id='{$order_id}' and good_id='{$id}' and pay_status=1 and createtime>=$start");
        }
        $this->smarty->assign('shop',$shop);
        $this->smarty->assign('see',$see);



////////分享
        if($shop['brief']){
            if(mb_strlen($shop['brief']) > 15){
                $shop['brief'] = mb_substr($shop['brief'],0,15) . '...';
            }else{
                $shop['brief'] = $shop['brief'];
            }
        }else{
            $shop['brief'] = '点击进入查询本店简介、地址、位置、供应、需求、招聘、促销等各类详情';
        }
        $WechatconfigObj  = new Wechatconfig();
        $sharearr = array(
            'share_title'=>$shop['name'],
            'share_desc'=>$shop['brief'],
            'ahare_link'=>"index.php?a=".__FUNCTION__."&c=".$this->c,
            'share_imgUrl'=>$shop['small'],
        );
        $WechatconfigObj->getJsSign($sharearr['share_title'],$sharearr['share_desc'],$sharearr['ahare_link'],$sharearr['share_imgUrl']);
        ////////分享

        ////////我的店铺头部信息
        $header_Category= $this->header_title();
        $this->smarty->assign('header_category',$header_Category);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function mylist()
    {
        $order_id=(int)Buddha_Http_Input::getParameter('order_id');
        $now_user_id = $user_id = (int)Buddha_Http_Cookie::getCookie('uid');// 为当前查看着ID
        $ShopObj=new Shop();
        $OrderObj=new Order();
        $RegionObj=new Region();
        $UserObj=new User();
        $CommonObj = new  Common();
        $locdata = $RegionObj->getLocationDataFromCookie();
        $view=Buddha_Http_Input::getParameter('view')? Buddha_Http_Input::getParameter('view'):'supply';
        $shop_id = $id=(int)Buddha_Http_Input::getParameter('id');
        $act=Buddha_Http_Input::getParameter('act');
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误','index.php?a=index&c=list');
        }
        $shopinfo = $ShopObj->fetch($id);
        if(!$shopinfo){
            Buddha_Http_Head::redirectofmobile('信息不存在','index.php?a=index&c=list');
        }

        //店铺一码营销
        $ShopObj->createQrcodeForCodeSales($id,$shopinfo['small'],$shopinfo['name'],$event='shop',$eventpage='info');
        $shop_user_id = $shopinfo['user_id']; //店铺拥有者ID


        /**↓↓↓↓↓↓↓↓↓↓↓ 显示电话 ↓↓↓↓↓↓↓↓↓↓↓**/
                // 是否显示电话
       $isshowphone = $ShopObj->isCouldSeeCellphone($shop_id,$shop_user_id,$now_user_id,$order_id);
                //最终显示电话
        $phone = $ShopObj->showCellphone($shop_id,$this->tablename,$shop_user_id,$now_user_id,$shop_id,$order_id);
        $showphone = array('isshowphone'=>$isshowphone,'phone'=>$phone);
        $this->smarty->assign('showphone',$showphone);
        /**↑↑↑↑↑↑↑↑↑↑ 显示电话 ↑↑↑↑↑↑↑↑↑↑**/


        if(strlen($shopinfo['name'])>7){
            $shopinfo['name'] = mb_substr($shopinfo['name'],0,7) . '...';
        }

        if(empty($shopinfo['codeimg'])){
            $CommonObj=new Common();
            $CommonObj->codeimg($shopinfo);
            $shopinfo = $ShopObj->fetch($id);
        }
        
        if ($shopinfo['is_verify'] == 1) {
            $titles = 'e网通';
        }else{
            $titles = '本地商家';
        }
        $names = $ShopObj->getSingleFiledValues(array('name','small'),"id={$id}");
        $contentss = '本地实体商家综合展示平台、本地供求信息综合发布平台（免费入驻）注册就能找到你';
        $WechatconfigObj  = new Wechatconfig();

        if($goods['promote_price'] != '0.00'){
           $goods['jia'] = $goods['promote_price'];
        }else{
            $goods['jia'] = $goods['market_price'];
        }

        //分享开始
        $sharearr = array(
            'share_title'=>$names['name'] .'('.$titles.')',
            'share_desc'=>$contentss,
            'ahare_link'=>"index.php?a=".__FUNCTION__."&c=".$this->c,
            'share_imgUrl'=>'/'.$names['small'],
        );
        $WechatconfigObj->getJsSign($sharearr['share_title'],$sharearr['share_desc'],$sharearr['ahare_link'],$sharearr['share_imgUrl']);
        $this->smarty->assign('user_id',$user_id);
        //////// 分享
        if($act=='list'){
            $view1 = $view;
            $newtime = Buddha::$buddha_array['buddha_timestamp'];
            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
            $api_activitytype = (int)Buddha_Http_Input::getParameter('api_activitytype')?(int)Buddha_Http_Input::getParameter('api_activitytype') : 1;
            $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') : 15;
            //$where = " isdel=0 and is_sure=1 and  buddhastatus=0 and shop_id='{$id}' and is_promote=1 {$locdata['sql']}";
            $orderby = " order by add_time DESC ";

            if($view=='supply')
            {
                $where = " isdel=0 and is_sure=1 and  buddhastatus=0 and shop_id='{$id}' ";
            $fields = array('id', 'shop_id', 'goods_name', 'market_price', 'promote_price', 'is_promote', 'promote_start_date', 'promote_end_date', 'goods_thumb','level1','level2','level3');
            }elseif($view=='demand')
            {
                $where = " isdel=0 and is_sure=1 and  buddhastatus=0 and shop_id='{$id}'";
                $fields = array('id', 'shop_id','user_id', 'name','budget', 'demand_thumb');
            }elseif($view=='recruit')
            {
                $where = " isdel=0 and is_sure=1 and  buddhastatus=0 and shop_id='{$id}'";
                $fields = array('id', 'shop_id','user_id', 'recruit_name','pay');
            }elseif($view=='lease')
            {
                $where = " isdel=0 and is_sure=1 and  buddhastatus=0 and shop_id='{$id}'";
                $fields = array('id','shop_id','user_id', 'lease_name','rent', 'lease_thumb');
            }elseif($view=='promote')
            {
                $where = " isdel=0 and is_sure=1 and  buddhastatus=0 and shop_id='{$id}' and is_promote=1";
                $view1='supply';
                $fields = array('id', 'shop_id', 'goods_name', 'market_price', 'promote_price', 'is_promote', 'promote_start_date', 'promote_end_date', 'goods_thumb');
//            }elseif($view=='activity')
            }elseif($view=='activity_personal' || $view=='activity_unity' || $view=='activity_vote')
            {
                $where = " isdel=0 and is_sure=1 and  buddhastatus=0 and shop_id='{$id}' and $newtime <= end_date";
                if($view=='activity_personal'){
                    $where .=' AND type=1';
                }elseif($view=='activity_unity'){
                    $where .=' AND type=2';
                }elseif($view=='activity_vote'){
                    $where .=' AND type=3';
                }
                $view1 = 'activity';
                $fields = array('id', 'shop_id', 'name', 'start_date', 'end_date','brief', 'activity_thumb','type');
            }elseif($view=='singleinformation')
            {
                $where = " isdel=0 and is_sure=1 and  buddhastatus=0 and shop_id='{$id}'";
                $view1='singleinformation';
                $fields = array('id', 'shop_id', 'name','brief', 'singleinformation_thumb','number');
            }elseif($view=='heartpro')
            {
                $where = " isdel=0 and is_sure=1 and  buddhastatus=0 and shop_id='{$id}' AND ( onshelftime < $newtime AND $newtime < offshelftime )";
                $fields = array('id', 'shop_id', 'name','price', 'small');
                $orderby = " order by createtime DESC ";
            }else{
                $where = " isdel=0 and is_sure=1 and state=0 and id={$id}";
                $view1='shop';
                $fields = '';
                $orderby = " order by createtime DESC ";
            }
//            $where.=''.$locdata['sql'];


            $list = $this->db->getFiledValues($fields,$this->prefix.$view1, $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize));


            if($view=='supply'){//供应
                $nwstiem=time();
                foreach($list as $k=>$v){
                    $shop=$ShopObj->getSingleFiledValues(array('name','specticloc','lng','lat'),"id='{$id}}'");
                     $distance=$RegionObj->getDistance($locdata['lng'],$locdata['lat'],$shop['lng'],$shop['lat'],2);
                    if($shop['specticloc']=='0'){
                        $roadfullname='';
                    }else{
                        $roadfullname=$shop['specticloc'];
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
                    $mylist[]=array(
                        'id'=>$v['id'],
                        'goods_name'=>$v['goods_name'],
                        'is_promote'=>$v['is_promote'],
                        'price'=>$price,
                        'market_price'=>$v['market_price'],
                        'promote_price'=>$v['promote_price'],
                        'shop_name'=>$shop['name'],
                        'distance'=>$distance,
                        'roadfullname'=>$roadfullname,
                        'goods_thumb'=>$v['goods_thumb'],
                    );
                }
            }elseif($view=='demand') {//需求
                foreach ($list as $k => $v) {
                    if ($v['shop_id'] != '0') {
                        $Db_shop = $ShopObj->getSingleFiledValues(array('name', 'specticloc', 'lng', 'lat'), "id='{$v['shop_id']}'");
                        $name = $Db_shop['name'];
                        if ($Db_shop['roadfullname'] == '0') {
                            $roadfullname = '';
                        } else {
                            $roadfullname = $Db_shop['specticloc'];
                        }
                    } else {
                        $Db_user = $UserObj->getSingleFiledValues(array('username', 'realname', 'address'), "id='{$v['user_id']}'");
                        if ($Db_user['address'] == '0') {
                            $roadfullname = '';
                        } else {
                            $roadfullname = $Db_user['address'];
                        }
                        if ($Db_user['realname'] == '0') {
                            $name = $Db_user['username'];
                        } else {
                            $name = $Db_user['realname'];
                        }
                    }
                    $mylist[] = array(
                        'id' => $v['id'],
                        'name' => $v['name'],
                        'price' => $v['budget'],
                        'shop_name' => $name,
                        'lease_name' => $v['lease_name'],
                        'roadfullname' => $roadfullname,
                        'demand_thumb'=>$CommonObj->handleImgSlashByImgurl($v['demand_thumb']),
                    );
                }
            }elseif($view=='heartpro') {//1分购
                foreach ($list as $k => $v) {
                    if ($v['shop_id'] != '0') {
                        $Db_shop = $ShopObj->getSingleFiledValues(array('name', 'specticloc', 'lng', 'lat'), "id='{$v['shop_id']}'");
                        $name = $Db_shop['name'];
                        if ($Db_shop['roadfullname'] == '0') {
                            $roadfullname = '';
                        } else {
                            $roadfullname = $Db_shop['specticloc'];
                        }
                    } else {
                        $Db_user = $UserObj->getSingleFiledValues(array('username', 'realname', 'address'), "id='{$v['user_id']}'");
                        if ($Db_user['address'] == '0') {
                            $roadfullname = '';
                        } else {
                            $roadfullname = $Db_user['address'];
                        }
                        if ($Db_user['realname'] == '0') {
                            $name = $Db_user['username'];
                        } else {
                            $name = $Db_user['realname'];
                        }
                    }
                    $mylist[] = array(
                        'id' => $v['id'],
                        'name' => $v['name'],
                        'price' => $v['price'],
                        'shop_name' => $name,
                        'lease_name' => $v['name'],
                        'roadfullname' => $roadfullname,
                        'demand_thumb' => $v['small']
                    );
                }
            }elseif($view=='recruit'){//招聘
                foreach($list as $k=>$v){
                    $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc','small','lng','lat'),"id='{$v['shop_id']}'");
                    $name=$Db_shop['name'];
                    if($Db_shop['roadfullname']=='0'){
                        $roadfullname='';
                    }else{
                        $roadfullname=$Db_shop['specticloc'];
                    }

                     if($v['pay']==0||$v['pay']==''){
                         $price='<em>面议</em>';
                     }else{
                        $price="<em>￥<i>{$v['pay']}</i></em>";
                     }
                     if($v['small']){
                        $smallImg = $v['small'];
                     }else{
                        $smallImg = $Db_shop['small'];
                     }
                    $mylist[]=array(
                        'id'=>$v['id'],
                        'name'=>$v['recruit_name'],
                        'price'=>$price,
                        'shop_name'=>$name,
                        'recruit_thumb'=>$smallImg,
                        'roadfullname'=>$roadfullname,
                    );
                }
            }elseif($view=='lease'){//租赁
                foreach($list as $k=>$v){
                    if($v['shop_id']!='0'){
                        $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc','small','lng','lat'),"id='{$v['shop_id']}'");
                        $name=$Db_shop['name'];
                        if($Db_shop['roadfullname']=='0'){
                            $roadfullname='';
                        }else{
                            $roadfullname = $Db_shop['specticloc'];
                        }
                    }else{
                        $Db_user=$UserObj->getSingleFiledValues(array('username','realname','address'),"id='{$v['user_id']}'");
                        if($Db_user['address']=='0'){
                            $roadfullname='' ;
                        }else{
                            $roadfullname=$Db_user['address'];
                        }
                        if($Db_user['realname']=='0'){
                            $name=$Db_user['username'];
                        }else{
                            $name=$Db_user['realname'];
                        }
                    }
                    if($v['lease_thumb']){
                        $smallImg = $v['lease_thumb'];
                    }else{
                        $smallImg = $Db_shop['small'];
                    }
                    $mylist[]=array(
                        'id'=>$v['id'],
                        'lease_name'=>$v['lease_name'],
                        'price'=>$v['rent'],
                        'shop_name'=>$name,
                        'roadfullname'=>$roadfullname,
                        'lease_thumb'=>$smallImg,
                    );
                }
            }elseif($view=='promote'){//促销
                foreach($list as $k=>$v){
                    $shop=$ShopObj->getSingleFiledValues(array('name','specticloc','lng','lat'),"id='{$id}}'");
                    $distance=$RegionObj->getDistance($locdata['lng'],$locdata['lat'],$shop['lng'],$shop['lat'],2);
                    if($shop['specticloc']=='0'){
                        $roadfullname='';
                    }else{
                        $roadfullname=$shop['specticloc'];
                    }
                    $mylist[]=array(
                        'id'=>$v['id'],
                        'goods_name'=>$v['goods_name'],
                        'is_promote'=>$v['is_promote'],
                        'price'=>$v['promote_price'],
                        'shop_name'=>$shop['name'],
                        'distance'=>$distance,
                        'roadfullname'=>$roadfullname,
                        'goods_thumb'=>$v['goods_thumb'],
                    );
                }
            }elseif($view=='abstract'){//简介
                if($list[0]['is_verify'] == 0){
                    $list[0]['shopdesc']=preg_replace("/13[12356789]{1}\d{8}|15[1235689]\d{8}|188\d{8}/", '${1}*****${2}',$list[0]['shopdesc']);
                    $list[0]['medium']=preg_replace("/\d{3,4}-\d{7,8}/", '${1}*****${2}', $list[0]['medium']); 
                }

                $mylist[]=array(
                    'shopdesc'=>$list[0]['shopdesc'],
                    'medium'=>$list[0]['medium'],
                );
            }elseif($view=='codesales')
            {//一码营销
                $mylist[]=array(
                    'codeimg'=>$list[0]['codeimg'],
                );
            }else if($view=='card')
            {//名片
                $RegionObj  = new Region();
                $id_string="{$list[0]['level1']},{$list[0]['level2']},{$list[0]['level3']}";
                $area=$RegionObj-> Region_area($id_string);

                /**↓↓↓↓↓↓↓↓↓↓↓ 显示电话 ↓↓↓↓↓↓↓↓↓↓↓**/
                // 是否显示电话
                $isshowphone = $ShopObj->isCouldSeeCellphone($shop_id,$shop_user_id,$now_user_id,$order_id);

                if($isshowphone==1){
                    $mobile = $list[0]['mobile'];
                    $tel = $list[0]['tel'];
                    $qq = $list[0]['qq'];

                    $wechatnumber = $list[0]['wechatnumber'];

                }elseif($isshowphone==0){

                    $mobile = '******';
                    $tel = '******';
                    $qq = '******';
                    $wechatnumber = '******';
                }

                /**↑↑↑↑↑↑↑↑↑↑ 显示电话 ↑↑↑↑↑↑↑↑↑↑**/

                $id=array(
                    'level1'=>$list[0]['level1'],
                    'level2'=>$list[0]['level2'],
                    'level3'=>$list[0]['level3'],
                );
                $diqu = $RegionObj->select_provincialcity($id);
                $Provincialcity= $diqu['level1']['fullname'] .$diqu['level2']['fullname'].$diqu['level3']['fullname'];
                if($list[0]['level4']!=0){
                    $Provincialcity.=$list[0]['level4'];
                }
                if($shopinfo[0]['level5']!=0){
                    $Provincialcity.=$list[0]['level5'];
                }
                if($shopinfo[0]['endstep']!=0){
                    $Provincialcity.=$list[0]['endstep'].'号/弄';
                }

                $mylist[]=array(
                    'name'=>$list[0]['name'],
                    'is_verify'=> $list[0]['is_verify'],
                    'showphone'=>$isshowphone,
                    'realname'=>$list[0]['realname'],
                    'mobile'=>$mobile,
                    'tel'=>$tel,
                    'qq'=>$qq,
                    'wechatnumber'=>$wechatnumber,
                    'fullname'=>$Provincialcity,
                    'specticloc'=>$area.$list[0]['specticloc'],
                    'createtimestr'=>$list[0]['createtimestr'],
                    'codeimg'=>$list[0]['codeimg'],
                );

            }elseif($view=='navigation'){//导航
                $mylist[]=array(
                    'specticloc'=>$list[0]['specticloc'],
                    'lng'=>$list[0]['lng'],
                    'lat'=>$list[0]['lat'],
                );
//            }elseif($view=='activity'){//活动
            }elseif($view=='activity_personal' || $view=='activity_unity' || $view=='activity_vote'){//活动
                $CommonObj=new Common();
                foreach ( $list as $k=>$v) {
                    $name = mb_substr($v['name'],0,$CommonObj->words_number(),'utf-8');
                    $brief = mb_substr($v['brief'],0,$CommonObj->words_number(),'utf-8');
                    if($v['type']==1 || $v['type']==2 ){
                        $a='mylist';
                    }elseif($v['type']==3||$v['type']==4){
                        $a='vodelist';
                    }
                    $mylist[] = array(
                        'id' => $v['id'],
                        'shop_id' => $v['shop_id'],
                        'name' => $name,
                        'start_date' => date('m/d H:i',$v['start_date']),
                        'end_date' => date('m/d H:i',$v['end_date']),
                        'brief' =>$brief,
                        'activity_thumb'=>$v['activity_thumb'],
                        'a'=>$a,
                    );
                }

            }elseif($view=='singleinformation'){//单页信息
                $CommonObj=new Common();
                $a='mylist';
                $c='singleinformation';
                foreach ( $list as $k=>$v) {
                    $name = mb_substr($v['name'],0,$CommonObj->words_number(),'utf-8');
                    $brief = mb_substr($v['brief'],0,$CommonObj->words_number(),'utf-8');

                    $mylist[] = array(
                        'id' => $v['id'],
                        'shop_id' => $v['shop_id'],
                        'name' => $name,
                        'number'=>$v['number'],
                        'brief' =>$brief,
                        'activity_thumb'=>$v['singleinformation_thumb'],
                        'a'=>$a,
                        'c'=>$c,
                    );
                }

            }elseif($view=='house'){//房屋

            }
            //商家推荐
            if($shopinfo['user_id']){
                $recommended = $UserObj->getSingleFiledValues(array('recommended'),"id='{$shopinfo['user_id']}'");
                if(!empty($recommended['recommended'])){
                    $shoptwo = $ShopObj->getFiledValues(array('id','name','small')," id in ({$recommended['recommended']}) ");
                }
                
            }
            $data=array();
            $data['view']=$view;
            /*
             * err =0  没有查询到数据!(用于后面添加提示图片)
             * err =1  查询到了数据,加载完成!
             * err =2  数据加载完成！
             */
            if($page==1 && !$mylist){
                $data['isok']='false';
                $data['err']=0;
                $data['list']='';
                $data['listtwo']=$shoptwo;
                $data['data']='对不起还没有添加数据，快去添加吧！';
            }else if($mylist){
                $data['err']=1;
                $data['isok']='true';
                $data['list']=$mylist;
                $data['listtwo']=$shoptwo;
                $data['data']='加载完成';
            }else{
                $data['err']=2;
                $data['isok']='false';
                $data['list']='';
                $data['listtwo']=$shoptwo;
                $data['data']='你的数据加载完毕，没有更多数据了!';
            }

            Buddha_Http_Output::makeJson($data);
        }


        //店铺是否有转发有赏
        $rechargeObj = new Recharge();
        $rechargeinfo = $rechargeObj->getSingleFiledValues('',"uid={$shopinfo['user_id']}");
        if($rechargeinfo && $rechargeinfo['balance'] >= $rechargeinfo['forwarding_money']){
            $info = '( 转发有赏 )';
            $this->smarty->assign('info',$info);
        }
        ////////我的店铺头部信息
//        $header_Category= $this->header_title();
        $header_Category = $ShopObj->shopnav($id);

        foreach ($header_Category as $k=>$v){
            if($v['pageflag']=='activity'){
                unset($header_Category[$k]);
            }
        }

//        -------------------------
        $host = Buddha::$buddha_array['host'];
        $shopnav_zhuijia = array(
            11=>array( 'select'=>0,'name'=>'个体活动','pageflag'=>'activity_personal','type'=>12,
                'Services'=>'activity.more','param'=>array('api_activitytype'=>1),
                'showstyle'=>'piclist',
                'icon_promote'=>$host.'apishop/menuplus/huodong_geti.png','list'=>array()  ,'is_show'=>1),

            12=>array( 'select'=>0,'name'=>'联合活动','pageflag'=>'activity_unity','type'=>13,
                'Services'=>'activity.more','param'=>array('api_activitytype'=>2),
                'showstyle'=>'piclist',
                'icon_promote'=>$host.'apishop/menuplus/huodong_lianhe.png','list'=>array() ,'is_show'=>1),

            13=>array( 'select'=>0,'name'=>'投票活动','pageflag'=>'activity_vote','type'=>14,
                'Services'=>'activity.more','param'=>array('api_activitytype'=>3),
                'showstyle'=>'piclist',
                'icon_promote'=>$host.'apishop/menuplus/huodong_toupiao.png','list'=>array() ,'is_show'=>1),
        );


        foreach ($shopnav_zhuijia as $k=>$v)
        {
           array_push($header_Category,$v);
        }
//        -------------------------

        $this->smarty->assign('header_category',$header_Category);
        $this->smarty->assign('view',$view);
        $this->smarty->assign('shopinfo',$shopinfo);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function indexiframe(){

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function mylistiframe(){

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
    public function header_title(){
        ////////我的店铺头部信息///////////
        $header_Category=array(
            0=>array('id'=>1,'name'=>'供应','err'=>'supply','img'=>'supply'),
            1=>array('id'=>2,'name'=>'需求','err'=>'demand','img'=>'need'),
            2=>array('id'=>3,'name'=>'招聘','err'=>'recruit','img'=>'recruit'),
            3=>array('id'=>4,'name'=>'租赁','err'=>'lease','img'=>'lease'),
            4=>array('id'=>5,'name'=>'简介','err'=>'abstract','img'=>'intro'),
            5=>array('id'=>6,'name'=>'名片','err'=>'card','img'=>'about'),
            //6=>array('id'=>7,'name'=>'一码','err'=>'','img'=>'yimayingxiao'),
            7=>array('id'=>8,'name'=>'活动','err'=>'activity','img'=>'campaign'),
//            8=>array('id'=>9,'name'=>'房屋','err'=>'house','img'=>'building'),
            9=>array('id'=>10,'name'=>'促销','err'=>'promote','img'=>'sales'),
            10=>array('id'=>11,'name'=>'传单','err'=>'singleinformation','img'=>'info'),
            11=>array('id'=>12,'name'=>'1分购','err'=>'heartpro','img'=>'heartpro'),
            12=>array('id'=>13,'name'=>'一码营销','err'=>'codesales','img'=>'codesales'),
        );
        return $header_Category;
    }
    public function sharingmoney(){//转发有赏
        $ShopObj = new Shop();
        $UserObj = new User();
        $s_id=(int)Buddha_Http_Input::getParameter('s_id');//店铺内码ID
        $uid = (int)Buddha_Http_Cookie::getCookie('uid');
        $uinfo = $UserObj->getSingleFiledValues('',"id='{$uid}'");
        if(!$uinfo['mobile'] && !$uid){
            header("Location:http://{$_SERVER['HTTP_HOST']}/index.php?a=login&c=account");
        }
        $types = Buddha_Http_Input::getParameter('types');

        $shopinfo = $ShopObj->getSingleFiledValues('',"id='{$s_id}'");
        $rechargeObj = new Recharge();
        $rechargeinfo = $rechargeObj->getSingleFiledValues('',"uid={$shopinfo['user_id']}");

       // file_put_contents("test{$s_id}-".time().".txt",var_export($_REQUEST,true));


        ////////分享有赏
        if($rechargeinfo && $rechargeinfo['balance'] >= $rechargeinfo['forwarding_money'] && $rechargeinfo['is_open'] == 1){
            //$user_id = (int)Buddha_Http_Cookie::getCookie('uid');
            $userObj = new User();
            $billObj = new Bill();
            $orderObj = new Order();
            $sharingObj = new Sharing();
            $sharinginfo = $sharingObj ->getSingleFiledValues('',"uid={$uid} and shop_id={$shopinfo['id']}");//该用户有没有分享过次店铺

            if($sharinginfo){
                if($rechargeinfo['time_period']){
                    $set_time = explode($rechargeinfo['time_period'],'-');//转发有赏起始时间段
                    $starttime = strtotime(date('Y-m-d').' '.$set_time[0].':00:00');
                    $endtime = strtotime(date('Y-m-d').' '.$set_time[1].':00:00');
                }
                if((time() - $sharinginfo['createtime']) >= 86400 && $starttime<=time() && $endtime>=time()){//  同家店铺分享每天分享第一次在此分享才有赏金，转发有时间段限制
                    $times['createtime'] = strtotime(date('Ymd'));
                    //$re = $sharingObj->edit($times,$sharinginfo['id']);//更新分享时间
                    $sql = "UPDATE {$this->prefix}sharing SET createtime='{$times['createtime']}' WHERE id='{$sharinginfo['id']}'";
                    if($this->db->query($sql)){
                        $banlance = $userObj->getSingleFiledValues(array('id','banlance'),"id={$uid}");
                        if($types == 'quan'){
                            $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['forwarding_money'];//增加账户余额
                        }elseif($types == 'hao'){
                            $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['hao_forwarding_money'];//增加账户余额
                        }
                        $userObj->edit($dataes,$uid);//更新账户余额
                        //生成订单和账单明细
                        $data = array();
                        $data['good_id'] = $shopinfo['id'];
                        $data['user_id'] = $uid;
                        $data['order_sn'] = $orderObj->birthOrderId($uid);
                        $data['good_table'] = 'shop';
                        if($types == 'quan'){
                            $data['goods_amt'] = $rechargeinfo['forwarding_money'];
                            $data['final_amt'] = $rechargeinfo['forwarding_money'];
                        }elseif($types == 'hao'){
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
                        $order_sn = $orderObj->getSingleFiledValues(array('order_sn'),"id={$order_id}");
                        $data = array();
                        $data['user_id'] = $uid;
                        $data['order_sn'] = $order_sn['order_sn'];
                        $data['order_id'] = $order_id;
                        $data['is_order'] = 1;
                        $data['order_type'] = 'forwarding.money';
                        $data['order_desc']  ='转发赏金';
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        if($types == 'quan'){
                            $data['billamt'] = $rechargeinfo['forwarding_money']; 
                        }elseif($types == 'hao'){
                            $data['billamt'] = $rechargeinfo['hao_forwarding_money']; 
                        }
                        $billObj->add($data);
                        $data = array();//商家转发后资金减少的记录
                        $data['user_id'] = $shopinfo['user_id'];
                        $data['is_order'] = 0;
                        $data['order_type'] = 'forwarding.money';
                        $data['order_desc']  ='扣除转发赏金';
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        if($types == 'quan'){
                            $data['billamt'] = '-' . $rechargeinfo['forwarding_money']; 
                        }elseif($types == 'hao'){
                            $data['billamt'] = '-' . $rechargeinfo['hao_forwarding_money']; 
                        }
                        $billObj->add($data);
                        //改变对应的充值表余额
                        if($types == 'quan'){
                            $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['forwarding_money'];
                        }elseif($types == 'hao'){
                            $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['hao_forwarding_money'];
                        }
                        $rechargeObj->edit($rech,$rechargeinfo['id']);


                        $data = array();
                        $data['isok'] = 'true';
                        $data['info'] = '赏金已充入余额';
                    }
                }elseif((time() - $sharinginfo['createtime']) >= 86400){//转发没有时间段限制

                    $times['createtime'] = strtotime(date('Ymd'));
                    //$re = $sharingObj->edit($times,$sharinginfo['id']);//更新分享时间
                    //$sql = "select count(*) as total from {$this->prefix}lease where {$where} {$like}";
                    $sql = "UPDATE {$this->prefix}sharing SET createtime='{$times['createtime']}' WHERE id='{$sharinginfo['id']}'";
                    if($this->db->query($sql)){
                        $banlance = $userObj->getSingleFiledValues(array('id','banlance'),"id={$uid}");
                        if($types == 'quan'){
                            $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['forwarding_money'];//增加账户余额
                        }elseif($types == 'hao'){
                            $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['hao_forwarding_money'];//增加账户余额
                        }
                        $userObj->edit($dataes,$uid);//更新账户余额
                        //生成订单和账单明细
                        $data = array();
                        $data['good_id'] = $shopinfo['id'];
                        $data['user_id'] = $uid;
                        $data['order_sn'] = $orderObj->birthOrderId($uid);
                        $data['good_table'] = 'shop';
                        if($types == 'quan'){
                            $data['goods_amt'] = $rechargeinfo['forwarding_money'];
                            $data['final_amt'] = $rechargeinfo['forwarding_money'];
                        }elseif($types == 'hao'){
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
                        $order_sn = $orderObj->getSingleFiledValues(array('order_sn'),"id={$order_id}");
                        $data = array();
                        $data['user_id'] = $uid;
                        $data['order_sn'] = $order_sn['order_sn'];
                        $data['order_id'] = $order_id;
                        $data['is_order'] = 1;
                        $data['order_type'] = 'forwarding.money';
                        $data['order_desc']  ='转发赏金';
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        if($types == 'quan'){
                            $data['billamt'] = $rechargeinfo['forwarding_money']; 
                        }elseif($types == 'hao'){
                            $data['billamt'] = $rechargeinfo['hao_forwarding_money']; 
                        }
                        $billObj->add($data);
                        $data = array();//商家转发后资金减少的记录
                        $data['user_id'] = $shopinfo['user_id'];
                        $data['is_order'] = 0;
                        $data['order_type'] = 'forwarding.money';
                        $data['order_desc']  ='扣除转发赏金';
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        if($types == 'quan'){
                            $data['billamt'] = '-' . $rechargeinfo['forwarding_money']; 
                        }elseif($types == 'hao'){
                            $data['billamt'] = '-' . $rechargeinfo['hao_forwarding_money']; 
                        }
                        $billObj->add($data);
                        //改变对应的充值表余额
                        if($types == 'quan'){
                            $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['forwarding_money'];
                        }elseif($types == 'hao'){
                            $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['hao_forwarding_money'];
                        }

                        $rechargeObj->edit($rech,$rechargeinfo['id']);
                        $data = array();
                        $data['isok'] = 'true';
                        $data['info'] = '赏金已充入余额';
                    }
                }
            }else{
                $datass['uid'] = $uid;
                $datass['shop_id'] = $shopinfo['id'];
                $datass['createtime'] = strtotime(date('Ymd'));
                //添加记录
                if($sharingObj->add($datass)){
                    $banlance = $userObj->getSingleFiledValues(array('id','banlance'),"id={$uid}");
                   if($types == 'quan'){
                        $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['forwarding_money'];//增加账户余额
                    }elseif($types == 'hao'){
                        $dataes['banlance'] = $banlance['banlance'] + $rechargeinfo['hao_forwarding_money'];//增加账户余额
                    }
                    $userObj->edit($dataes,$uid);//更新账户余额
                    //生成订单和账单明细
                    $data = array();
                    $data['good_id'] = $shopinfo['id'];
                    $data['user_id'] = $uid;
                    $data['order_sn'] = $orderObj->birthOrderId($uid);
                    $data['good_table'] = 'shop';
                    if($types == 'quan'){
                        $data['goods_amt'] = $rechargeinfo['forwarding_money'];
                        $data['final_amt'] = $rechargeinfo['forwarding_money'];
                    }elseif($types == 'hao'){
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
                    $order_sn = $orderObj->getSingleFiledValues(array('order_sn'),"id={$order_id}");
                    $data = array();
                    $data['user_id'] = $uid;
                    $data['order_sn'] = $order_sn['order_sn'];
                    $data['order_id'] = $order_id;
                    $data['is_order'] = 1;
                    $data['order_type'] = 'forwarding.money';
                    $data['order_desc']  ='转发赏金';
                    $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                    $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                    if($types == 'quan'){
                        $data['billamt'] = $rechargeinfo['forwarding_money']; 
                    }elseif($types == 'hao'){
                        $data['billamt'] = $rechargeinfo['hao_forwarding_money']; 
                    }
                    $billObj->add($data);

                    //商家转发后资金减少的记录
                    $data = array();
                    $data['user_id'] = $shopinfo['user_id'];
                    $data['is_order'] = 0;
                    $data['order_type'] = 'forwarding.money';
                    $data['order_desc']  ='扣除转发赏金';
                    $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                    $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                    if($types == 'quan'){
                        $data['billamt'] = '-' . $rechargeinfo['forwarding_money']; 
                    }elseif($types == 'hao'){
                        $data['billamt'] = '-' . $rechargeinfo['hao_forwarding_money']; 
                    }
                    $billObj->add($data);
                    //改变对应的充值表余额
                    if($types == 'quan'){
                            $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['forwarding_money'];
                        }elseif($types == 'hao'){
                            $rech['balance'] = $rechargeinfo['balance'] - $rechargeinfo['hao_forwarding_money'];
                        }
                    $rechargeObj->edit($rech,$rechargeinfo['id']);

                    $data = array();
                    $data['isok'] = 'true';
                    $data['info'] = '赏金已充入余额';
                }
            }
        }else{
            $data = array();
            $data['isok'] = 'false';
            $data['info'] = '';
        }


        Buddha_Http_Output::makeJson($data);
    }
    

}