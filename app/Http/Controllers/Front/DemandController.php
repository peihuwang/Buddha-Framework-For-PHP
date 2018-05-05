<?php

/**
 * Class DemandController
 */
class DemandController extends Buddha_App_Action{

    protected $tablenamestr;
    protected $tablename;
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='需求';
        $this->tablename='demand';
    }

    public function index()
    {
        $RegionObj=new Region();
        $ShopObj=new Shop();
        $UserObj=new User();
        $LeasecatObj = new Leasecat();
        $cid = Buddha_Http_Input::getParameter('cid');
        $getcategory =$LeasecatObj->getcategory();
        if($cid)
        {
            $insql = $LeasecatObj->getInSqlByID($getcategory,$cid);
        }

        $locdata = $RegionObj->getLocationDataFromCookie();
        $act=Buddha_Http_Input::getParameter('act');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 2;
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') : 15;
        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id') : 0;
        if($act=='list')
        {
            $where = " isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']}";
            $orderby = "";

            if(Buddha_Atom_String::isValidString($shop_id))
            {
                $where .= "AND shop_id='{$shop_id}'";
            }else{
                $orderby = " group by shop_id ";
            }

            $orderby .= " order by budget DESC ";

            if ($view)
            {
                switch ($view)
                {
                    case 2;
                        $orderby = " group by shop_id order by add_time DESC ";
                        break;
                    case 3;
                        $where .= " and is_hot=1";
                        break;
                    /*case 4;
                        $orderby = "group by shop_id  order by budget ASC";
                        break;*/
                    case 5;
                        $where .= " and shop_id!=0";
                        break;
                }
            }

            if ($keyword)
            {
                $where .= " and name like '%{$keyword}%'";
            }

            if($cid)
            {
                $where.=" and  demandcat_id IN {$insql}";
            }
            $fields = array('id', 'shop_id','user_id', '`name`','budget', 'demand_thumb');


            if($view==2)
            {
                $CommonindexObj = new Commonindex();

                $list =  $CommonindexObj->newestmore( $this->tablename,$fields,$page,$pagesize,$where);

            }else{

                $list = $this->db->getFiledValues ($fields,  $this->prefix.'demand', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ));

            }


            $CommonObj = new Common();

            foreach($list as $k=>$v)
            {
                if($v['shop_id']!='0'){
                    $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc','lng','lat'),"id='{$v['shop_id']}'");
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

                $lease[]=array(
                    'id'=>$v['id'],
                    'name'=>$v['name'],
                    'price'=>$v['budget'],
                    'shop_name'=>$name,
                    'roadfullname'=>$roadfullname,
                    'demand_thumb'=>$CommonObj->handleImgSlashByImgurl($v['demand_thumb']),
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

        $CommonindexObj = new Commonindex();
        $filarr = array(
            0=>array('filed'=>'zuixin','a'=>'index','view'=>2),
            1=>array('filed'=>'tuijian','a'=>'index','view'=>1),
            2=>array('filed'=>'remen','a'=>'index','view'=>3),
            3=>array('filed'=>'shangjia','a'=>'index','view'=>5),
            4=>array('filed'=>'fenlei','a'=>'category','view'=>6));

        $Common = $CommonindexObj->indexmorenavlist($this->tablename,$filarr);
        $this->smarty->assign('navlist',$Common);



        $this->smarty->assign('view',$view);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }



    public function category()
    {
        $DemandcatObj=new Demandcat();

        $arr =$DemandcatObj->getcategory();
        $table ='';
        $DemandcatObj->getDivRelation($arr,$table);
        $this->smarty->assign('category',$table);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function info()
    {
        $RegionObj=new Region();
        $DemandpObj=new Demand();
        $ShopObj=new Shop();
        $OrderObj=new Order();
        $now_user_id = $user_id = (int)Buddha_Http_Cookie::getCookie('uid');
        $order_id=(int)Buddha_Http_Input::getParameter('order_id');
        $locdata = $RegionObj->getLocationDataFromCookie();
        $id=(int)Buddha_Http_Input::getParameter('id');

        if(!$id){
            Buddha_Http_Head::redirectofmobile('链接参数错误！','index.php?a=index&c=demand',2);
        }
        $goods= $DemandpObj->fetch($id);
        if(!$goods){
            Buddha_Http_Head::redirectofmobile('信息不存在或已删除！','index.php?a=index&c=demand',2);
        }

        $shopinfo= $ShopObj->getSingleFiledValues(array('user_id','small','name','is_verify','specticloc','lat','lng','mobile','createtimestr','level2','level3'),"id='{$goods['shop_id']}'");
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
        $DemandpObj->edit($data,$id);



        /**↓↓↓↓↓↓↓↓↓↓↓ 显示电话 ↓↓↓↓↓↓↓↓↓↓↓**/
        // 是否显示电话
        $isshowphone = $ShopObj->isCouldSeeCellphone($goods['shop_id'],$goods['user_id'],$now_user_id,$order_id);
        //最终显示电话
        $phone = $ShopObj->showCellphone($id,$this->tablename,$goods['user_id'],$now_user_id,$goods['shop_id'],$order_id);
        $showphone = array('isshowphone'=>$isshowphone,'phone'=>$phone);
        $this->smarty->assign('showphone',$showphone);
        /**↑↑↑↑↑↑↑↑↑↑ 显示电话 ↑↑↑↑↑↑↑↑↑↑**/



        /**↓↓↓↓↓↓↓↓↓↓↓ 店铺是否有转发有赏 ↓↓↓↓↓↓↓↓↓↓↓**/

        $rechargeObj = new Recharge();
        $rechargeinfo = $rechargeObj->getSingleFiledValues('',"uid={$shopinfo['user_id']} and shop_id='{$shopinfo['id']} and is_open=1'");
        if($rechargeinfo && $rechargeinfo['balance'] >= $rechargeinfo['forwarding_money']){
            $info = 1;
            $this->smarty->assign('info',$info);
        }
        /**↑↑↑↑↑↑↑↑↑↑ 店铺是否有转发有赏 ↑↑↑↑↑↑↑↑↑↑**/
///////////////////////////////////
//        $this->smarty->assign('see',$see);
        $this->smarty->assign('goods',$goods);
        $this->smarty->assign('uid',$user_id);
        $this->smarty->assign('shopinfo',$shopinfo);
        if($goods['demand_desc']){
            if(mb_strlen($goods['demand_desc']) > 20){
                $goods['de_desc'] = mb_substr($goods['demand_desc'],0,20) . '...';
            }else{
                $goods['de_desc'] = $goods['demand_desc'];
            }
        }else{
            $goods['de_desc'] = '快速发布您的需求，快速解决您的问题，万人同时在线，为您排忧解难';
        }
        ////////分享
        $WechatconfigObj  = new Wechatconfig();
        $sharearr = array(
            'share_title'=>$goods['name'],
            'share_desc'=>$goods['de_desc'],
            'ahare_link'=>"index.php?a=".__FUNCTION__."&c=".$this->c,
            'share_imgUrl'=>$goods['demand_thumb'],
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
        $this->smarty->assign('gallery', $Db_Gallery,'','');
        /**↑↑↑↑↑↑↑↑↑↑↑↑ 相册 ↑↑↑↑↑↑↑↑↑↑**/


        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }




}


