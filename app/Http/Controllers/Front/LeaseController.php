<?php

/**
 * Class LeaseController
 */
class LeaseController extends Buddha_App_Action{
    protected $tablenamestr;
    protected $tablename;
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='租赁';
        $this->tablename='lease';
    }

    public function index(){
        $RegionObj=new Region();
        $ShopObj=new Shop();
        $UserObj=new User();
        $LeasecatObj = new Leasecat();
        $cid = Buddha_Http_Input::getParameter('cid');
        $getcategory =$LeasecatObj->getcategory();
        if($cid){
            $insql = $LeasecatObj->getInSqlByID($getcategory,$cid);
        }
        $CommonindexObj = new Commonindex();
        $locdata = $RegionObj->getLocationDataFromCookie();
        $act=Buddha_Http_Input::getParameter('act');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 2;
        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id') : 0;
        if($act=='list') {
            $where = " isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']}";
            $orderby = "";
            if(Buddha_Atom_String::isValidString($shop_id))
            {
                $where .= "AND shop_id='{$shop_id}'";
            }else{
                $orderby = " group by shop_id ";
            }
            $orderby .= " order by rent DESC ";
            if ($view) {
                switch ($view) {
                    case 2;
                      //  $where .= ' and is_sure=0';
                        break;
                    case 3;
                        $where .= " and is_hot=1";
                        break;
                    case 4;
                        $orderby = " group by shop_id order by rent ASC";
                        break;
                    case 5;
                        $where .= " and shop_id!=0";
                        break;
                }
            }
            if ($keyword) {
                $where .= " and lease_name like '%$keyword%'";
            }
            if($cid){
                $where.=" and  leasecat_id IN {$insql}";
            }

            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
            $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') : 15;

            $fields = array('id', 'shop_id','user_id', 'lease_name','rent', 'lease_thumb');


            if($view==2)
            {
                $CommonindexObj = new Commonindex();

                $list =  $CommonindexObj->newestmore( $this->tablename,$fields,$page,$pagesize,$where);

            }else {

                $list = $this->db->getFiledValues($fields, $this->prefix . 'lease', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));
            }

            $CommonObj = new Common();

            foreach($list as $k=>$v)
            {
                if($v['shop_id']!='0'){
                $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc','lng','lat','small'),"id='{$v['shop_id']}'");
                    $name=$Db_shop['name'];
                    if($Db_shop['roadfullname']=='0'){
                        $roadfullname='';
                    }else{
                        $roadfullname=$Db_shop['specticloc'];
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

                if(Buddha_Atom_String::isValidString($v['lease_thumb']))
                {
                    $img = $v['lease_thumb'];
                }else{
                    if(Buddha_Atom_String::isValidString($Db_shop['small']))
                    {
                        $img = $Db_shop['small'];
                    }else{
                        $img = '';
                    }
                }

                $lease[]=array(
                    'id'=>$v['id'],
                    'lease_name'=>$v['lease_name'],
                    'price'=>$v['rent'],
                    'shop_name'=>$name,
                    'roadfullname'=>$roadfullname,
                    'lease_thumb'=>$img,
                    'lease_thumb'=>$CommonObj->handleImgSlashByImgurl($img),

                );
            }
            $data=array();
            if($lease){
                $data['isok']='true';
                $data['list']=$lease;
                $data['data']='加载完成';

            }else{
                $data['isok']='false';
                $data['list']='';
                $data['data']='没数据了';
            }
            Buddha_Http_Output::makeJson($data);
        }
        $this->smarty->assign('view',$view);

        $CommonindexObj = new Commonindex();
        $filarr = array(
            0=>array('filed'=>'zuixin','a'=>'index','view'=>2),
            1=>array('filed'=>'tuijian','a'=>'index','view'=>1),
            2=>array('filed'=>'remen','a'=>'index','view'=>3),
            3=>array('filed'=>'shangjia','a'=>'index','view'=>5),
            4=>array('filed'=>'fenlei','a'=>'category','view'=>6));
        $Common = $CommonindexObj->indexmorenavlist($this->tablename,$filarr);
        $this->smarty->assign('navlist',$Common);


        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }



    public function category(){
        $LeasecatObj=new Leasecat();

        $arr =$LeasecatObj->getcategory();
        $table ='';
        $LeasecatObj->getDivRelation($arr,$table);
        $this->smarty->assign('category',$table);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }



    public function info()
    {

        $RegionObj=new Region();
        $LeaseObj=new Lease();
        $ShopObj=new Shop();
        $OrderObj=new Order();
        $now_user_id = $user_id = (int)Buddha_Http_Cookie::getCookie('uid');
        $order_id=(int)Buddha_Http_Input::getParameter('order_id');
        $locdata = $RegionObj->getLocationDataFromCookie();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirectofmobile('链接参数错误！','index.php?a=index&c=lease',2);
        }
        $goods= $LeaseObj->fetch($id);
        if(!$goods){
            Buddha_Http_Head::redirectofmobile('信息不存在或已删除！','index.php?a=index&c=lease',2);
        }

        $shopinfo= $ShopObj->getSingleFiledValues(array('user_id','small','name','is_verify','specticloc','lat','lng','mobile','level2','level3'),"id='{$goods['shop_id']}'");
        $citys = $RegionObj->getSingleFiledValues('name',"id={$shopinfo['level2']}");
        $xian = $RegionObj->getSingleFiledValues('name',"id={$shopinfo['level3']}");
        $shopinfo['level2'] = $citys['name'];
        $shopinfo['level3'] = $xian['name'];
        $lat1=$locdata['lat'];
        $lng1=$locdata['lng'];
        $distance=$RegionObj->getDistance($lat1,$lng1,$shopinfo['lat'],$shopinfo['lng'],2);
        $shopinfo['distance']=$distance;
        $data=array();
        $data['click_count']=$goods['click_count']+1;
        $LeaseObj->edit($data,$id);

//        $start = time()-30*60;
//        if($user_id){
//            $see= $OrderObj->countRecords("user_id='{$user_id}' and good_id='{$shopinfo['id']}' and pay_status=1 and createtime>=$start");
//        }else{
//            $see= $OrderObj->countRecords("id='{$order_id}' and good_id='{$shopinfo['id']}' and pay_status=1 and createtime>=$start");
//        }
/////////////////////////////////////
//        if($shopinfo['is_verify']==1){//判断用户是否认证：非认证显示15天（is_verify）
//            $shopinfo['verify']=0;
//        }else if ($shopinfo['is_verify']==0){
//            $createtime=$goods['add_time'];//免费15天的开始时间
//            $endtime = strtotime('+15 Day',strtotime(date('Y-m-d H:i:s',$createtime)));//免费15天的结束时间
//            $newtime=time();
//            if($createtime < $newtime  and $newtime < $endtime){
//                $shopinfo['verify']=1;
//            }else{
//                $shopinfo['verify']=0;
//            }
//        }


        /**↓↓↓↓↓↓↓↓↓↓↓ 显示电话 ↓↓↓↓↓↓↓↓↓↓↓**/
        // 是否显示电话
        $isshowphone = $ShopObj->isCouldSeeCellphone($goods['shop_id'],$goods['user_id'],$now_user_id,$order_id);
        //最终显示电话
        $phone = $ShopObj->showCellphone($id,$this->tablename,$goods['user_id'],$now_user_id,$goods['shop_id'],$order_id);
        $showphone = array('isshowphone'=>$isshowphone,'phone'=>$phone);
        $this->smarty->assign('showphone',$showphone);
        /**↑↑↑↑↑↑↑↑↑↑ 显示电话 ↑↑↑↑↑↑↑↑↑↑**/






        //店铺是否有转发有赏
        $rechargeObj = new Recharge();
        $rechargeinfo = $rechargeObj->getSingleFiledValues('',"uid={$shopinfo['user_id']} and shop_id='{$shopinfo['id']} and is_open=1'");
        if($rechargeinfo && $rechargeinfo['balance'] >= $rechargeinfo['forwarding_money']){
            $info = 1;
            $this->smarty->assign('info',$info);
        }
///////////////////////////////////
        $this->smarty->assign('see',$see);
        $this->smarty->assign('uid',$user_id);
        $this->smarty->assign('goods',$goods);
        $this->smarty->assign('shopinfo',$shopinfo);
////////分享
        if($goods['lease_desc']){
            if(mb_strlen($goods['lease_desc']) > 20){
                $goods['de_desc'] = mb_substr($goods['lease_desc'],0,20) . '...';
            }else{
                $goods['de_desc'] = $goods['lease_desc'];
            }
        }else{
            $goods['de_desc'] = '快速发布您的租赁，让您的信息快速传播到用户手中，万人同时在线，为您排忧解难';
        }
        $WechatconfigObj  = new Wechatconfig();
        $sharearr = array(
            'share_title'=>$goods['lease_name'],
            'share_desc'=>$goods['de_desc'],
            'ahare_link'=>"index.php?a=".__FUNCTION__."&c=".$this->c,
            'share_imgUrl'=>$goods['lease_thumb'],
        );
        $WechatconfigObj->getJsSign($sharearr['share_title'],$sharearr['share_desc'],$sharearr['ahare_link'],$sharearr['share_imgUrl']);
        ////////分享

        $shopObj=new Shop();
        $this->smarty->assign('shop_url',$shopObj->shop_url());


        /**↓↓↓↓↓↓↓↓↓↓↓↓ 推荐 ↓↓↓↓↓↓↓↓↓↓↓↓**/
        $CommonObj = new Common();
        $recommend = $CommonObj->recommendBelongShop($goods['shop_id'],$this->tablename,$id);
        $this->smarty->assign('recommend', $recommend);
//        print_r($recommend);
        /**↑↑↑↑↑↑↑↑↑↑↑↑ 推荐 ↑↑↑↑↑↑↑↑↑↑**/



        /**↓↓↓↓↓↓↓↓↓↓↓↓ 相册 ↓↓↓↓↓↓↓↓↓↓↓↓**/
        $CommonObj = new Common();
        $Db_Gallery = $CommonObj->getGalleryByTableidComm($id,$this->tablename);
        $this->smarty->assign('gallery', $Db_Gallery);
        /**↑↑↑↑↑↑↑↑↑↑↑↑ 相册 ↑↑↑↑↑↑↑↑↑↑**/

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


}