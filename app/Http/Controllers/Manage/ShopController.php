<?php

/**
 * Class ShopController
 */
class ShopController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function more()
    {
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $RegionObj =new Region();
        $ShopObj = new Shop();
        $UserObj = new User();
        $params = array ();
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;
        $p=(int)Buddha_Http_Input::getParameter('p');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        if(Buddha_Http_Input::getParameter('job')){
            $job=Buddha_Http_Input::getParameter('job');
            if(!Buddha_Http_Input::getParameter('ids')){
                Buddha_Http_Head::redirect('您没有选择参数','index.php?a=more&c=shop&view='.$view.'&p='.$p);
            }
            $ids = implode ( ',',Buddha_Http_Input::getParameter('ids'));

            switch($job){
                case 'is_sure':
                    $ShopObj->updateRecords(array('is_sure'=>1,'state'=>0),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=more&c=shop&view='.$view.'&p='.$p);
                    break;
                case 'stop':
                    $ShopObj->updateRecords(array('is_sure'=>0,'state'=>1,'isdel'=>4),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=more&c=shop&view='.$view.'&p='.$p);
                    break;
                case 'sure':
                    $ShopObj->updateRecords(array('is_sure'=>1,'state'=>0,),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=more&c=shop&view='.$view.'&p='.$p);
                    break;
                case 'enable':
                    $ShopObj->updateRecords(array('is_sure'=>1,'state'=>0,'isdel'=>0),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=more&c=shop&view='.$view.'&p='.$p);
                    break;
                case 'is_hot':
                    $ShopObj->updateRecords(array('is_hot'=>1),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=more&c=shop&view='.$view.'&p='.$p);
                    break;
                case 'is_rec':
                    $ShopObj->updateRecords(array('is_rec'=>1),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=more&c=shop&view='.$view.'&p='.$p);
                    break;
                case 'is_promotion':
                    $ShopObj->updateRecords(array('is_promotion'=>1),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=more&c=shop&view='.$view.'&p='.$p);
                    break;
            }
        }

//        $today = date('Y-m-d',time());
        $today_ll = strtotime(date('Y-m-d',time()));//今天0点
        $today_es = strtotime(date('Y-m-d',time()))+ 86400-1;//今天23:59:59
        $todayshopwhere = " {$today_ll} <createtimestr AND  createtimestr<{$today_es}";

        $todayaddtotal = $ShopObj->countRecords($todayshopwhere);
//        $todayaddtotal = $ShopObj->countRecords(" createtimestr like '%{$today}%' ");
        $this->smarty->assign('todayaddtotal',$todayaddtotal);
        //获取有赏店铺
        if($view == 9){
            $rechargeObj = new Recharge();
            $rechargeinfo = $rechargeObj->getFiledValues(array('shop_id'),"shop_id <> 0 AND is_open=1");
            $shopidstr = '';
            if(count($rechargeinfo)>1){
                foreach ($rechargeinfo as $k => $v) {
                    $shopidstr .= $v['shop_id'] . ',';
                }
                $shopidstr = substr($shopidstr,0,-1);
            }else{
                $shopidstr = $rechargeinfo[0]['shop_id'];
            }
        }
        $where = " (isdel=0 or isdel=1  or isdel=4 )";
        if($view) {
            $params['view'] = $view;
            switch ($view) {
                case 2;
                    $where .= ' and is_sure=0';
                    break;
                case 3;
                    $where .= " and is_sure=1";
                    break;
                case 4;
                    $where .= " and is_sure=4 ";
                    break;
                case 5;
                    $where .= " and  state=1";
                    break;

                case 6;
                    $where .= " and is_sure=1 and is_rec=1";
                    break;

                case 7;
                    $where .= " and is_sure=1 and is_hot=1";
                    break;

                case 8;
                    $where .= " and is_sure=1 and is_promotion=1";
                    break;
                case 9;
                    $where .= " and shop_id in ({$shopidstr})";
                    break;
                case 10;
                    $where .= $todayshopwhere;
                    break;
            }
        }
        if($keyword){
            $where.=" and name like '%$keyword%'";
            $params['keyword'] = $keyword;
        }

        $searchType = array (1 => '全部店铺', 2 => '未审核', 3 => '已通过',4 => '未通过', 5=> '已停用',
            6=> '推荐', 7=> '热门', 8=> '促销'
        );
        $this->smarty->assign('searchType',$searchType);
        $this->smarty->assign( 'params', $params );
        $this->smarty->assign( 'keyword', $keyword );
        $rcount= $ShopObj->countRecords($where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by id DESC ";

        $list = $this->db->getFiledValues('', $this->prefix . 'shop', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));
        $realname='';
        $RegionObj = new Region();

        foreach($list as $k=>$v){
           $leve3= $v['level3'];
            $Region_name=$RegionObj->getAllArrayAddressByLever($leve3);
            if($Region_name){
                $regionname='';
                foreach($Region_name as $k1=>$v1){
                    if($k1!=0 and $k1<4){
                        $regionname.=$v1['name'].' > ';
                    }
                }
                $list[$k]['region']=Buddha_Atom_String::toDeleteTailCharacter($regionname);
            }
                if($v['referral_id']>0){
                    $realname=$UserObj->getSingleFiledValues(array('realname'),"id={$v['referral_id']} and groupid=3") ;
                    $list[$k]['referral']=$realname['realname'];
                }elseif($v['addshopuid']>0){
                    $list[$k]['referral']='<span style="color: #0000ee">采集员添加</span>';
                }else{
                    $list[$k]['referral']='<span style="color: #0000ee">商家自己添加</span>';
                }
            $list[$k]['regionale']= $RegionObj->getDetailOfAdrressByRegionIdStr($v['level1'],$v['level2'],$v['level3']);

        }
        $strPages =  Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=shop&'
            .http_build_query($params).'&'
            , $pagesize);
//print_r($list);
        $this->smarty->assign('rcount', $rcount);
        $this->smarty->assign('pcount', $pcount);
        $this->smarty->assign('page', $page);
        $this->smarty->assign('strPages', $strPages);
        $this->smarty->assign('list', $list);
        $this->smarty->assign( 'params', $params );

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }
    //有赏店铺列表
    public function shopbonus(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $RegionObj =new Region();
        $ShopObj = new Shop();
        $UserObj = new User();
        //获取有赏店铺
        $rechargeObj = new Recharge();
        $rechargeinfo = $rechargeObj->getFiledValues(array('shop_id'),"shop_id <> 0 AND is_open=1");
        if(count($rechargeinfo)>1){
            foreach ($rechargeinfo as $k => $v) {
                $shopidstr .= $v['shop_id'] . ',';
            }
            $shopidstr = substr($shopidstr,0,-1);
        }else{
            $shopidstr = $rechargeinfo[0]['shop_id'];
        }
        $where = " (isdel=0 or isdel=4 )";
        $orderby = " order by id DESC ";
        $list = $this->db->getFiledValues('', $this->prefix . 'shop', $where . ' AND id in(' . $shopidstr .')'. $orderby);
        foreach($list as $k=>$v){
           $leve3= $v['level3'];
            $Region_name=$RegionObj->getAllArrayAddressByLever($leve3);
            if($Region_name){
                $regionname='';
                foreach($Region_name as $k1=>$v1){
                    if($k1!=0 and $k1<4){
                        $regionname.=$v1['name'].' > ';
                    }
                }
                $list[$k]['region']=Buddha_Atom_String::toDeleteTailCharacter($regionname);
            }
                if($v['referral_id']>0){
                    $realname=$UserObj->getSingleFiledValues(array('realname'),"id={$v['referral_id']} and groupid=3") ;
                    $list[$k]['referral']=$realname['realname'];
                }elseif($v['addshopuid']>0){
                    $list[$k]['referral']='<span style="color: #0000ee">采集员添加</span>';
                }else{
                    $list[$k]['referral']='<span style="color: #0000ee">商家自己添加</span>';
                }
        }
        $this->smarty->assign( 'list', $list );
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }
    public  function edit(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $p=(int)Buddha_Http_Input::getParameter('p');
        $view=(int)Buddha_Http_Input::getParameter('view');
        $BillObj = new Bill();
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $ShopcatObj=new Shopcat();
        $UserObj=new User();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $partnerrate=(int)Buddha_Http_Input::getParameter('partnerrate');
        $is_sure=Buddha_Http_Input::getParameter('is_sure');
        $remarks=Buddha_Http_Input::getParameter('remarks');
        if(!$id){
            Buddha_Http_Head::redirect('参数错误！',"index.php?a=more&c=shop&p={$p}&view={$view}");
        }
        $shopinfo=$ShopObj->getSingleFiledValues('',"id='{$id}'");
        if(!$shopinfo){
            Buddha_Http_Head::redirect('没有找到您要的信息！',"index.php?a=more&c=shop&p={$p}&view={$view}");
        }
        $getNatureOption=$ShopObj->getNature($shopinfo['storetype']);
        $this->smarty->assign('getNatureOption',$getNatureOption);
        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['partnerrate']=$partnerrate;
            $data['is_sure']=$is_sure;
            if($is_sure==1){
                $data['state']=0;
            }
            $data['remarks']=$remarks;

            $ShopObj->edit($data,$id);
            if($ShopObj){
                if($shopinfo['addshopuid'] && !$shopinfo['remarks'] && $is_sure==1){//给采集员分配采集金额
                    $addshopinfo = $UserObj->getSingleFiledValues(array('id','banlance','lev1'),"id={$shopinfo['addshopuid']}");//获取采集员的详细信息
                    $datae['banlance'] = $addshopinfo['banlance'] + 1;//审核通过采集员余额增加
                    if($UserObj->edit($datae,$addshopinfo['id'])){
                        $bill['user_id'] = $addshopinfo['id'];
                        $bill['is_order'] = 0;
                        $bill['order_type'] = 'collect';
                        $bill['order_desc'] = '信息采集费';
                        $bill['createtime'] = time();
                        $bill['createtimestr'] = date('Y-m-d H:i:s');
                        $bill['orient'] = '+';
                        $bill['billamt'] = 1;
                        $BillObj->add($bill);
                    }
                    
                    if($addshopinfo['lev1']){
                        $leve1 = $UserObj->getSingleFiledValues(array('id','banlance'),"id={$addshopinfo['lev1']}");//一级代理增余额
                        $datas['banlance'] = $leve1['banlance'] + 0.2;
                        if($UserObj->edit($datas,$leve1['id'])){
                            $bill['user_id'] = $leve1['id'];
                            $bill['is_order'] = 0;
                            $bill['order_type'] = 'collect';
                            $bill['order_desc'] = '信息采集费';
                            $bill['createtime'] = time();
                            $bill['createtimestr'] = date('Y-m-d H:i:s');
                            $bill['orient'] = '+';
                            $bill['billamt'] = 0.2;
                            $BillObj->add($bill);
                        }
                    }
                    /*if($addshopinfo['lev2']){//微信公众号不允许
                        $leve2 = $UserObj->getSingleFiledValues(array('id','banlance'),"id={$addshopinfo['lev2']}");//二级代理增余额
                        $datass['banlance'] = $leve2['banlance'] + 0.1;
                        $UserObj->edit($datass,$leve2['id']);
                    }*/
                }
                Buddha_Http_Head::redirect('操作成功！',"index.php?a=more&c=shop&p={$p}&view={$view}");
            }else{
                Buddha_Http_Head::redirect('审核失败！',"index.php?a=more&c=shop&p={$p}&view={$view}");
            }
        }
        $shopcat=$ShopcatObj->goods_thumbgoods_thumb($shopinfo['shopcat_id']);
        if($shopcat){
            $cat='';
            foreach($shopcat as $k=>$v){
                $cat.=$v['cat_name'].' > ';
            }
            $shopinfo['cat_name']=Buddha_Atom_String::toDeleteTailCharacter($cat);
        }
        $Region_name=$RegionObj->getAllArrayAddressByLever($shopinfo['level3']);
        if($Region_name){
            $regionname='';
            foreach($Region_name as $k=>$v){
                if($k!=0 and $k<4){
                    $regionname.=$v['name'].' > ';
                }
            }
            $shopinfo['region']=Buddha_Atom_String::toDeleteTailCharacter($regionname);
        }
        $referral=$UserObj->getSingleFiledValues(array('id','partnerrate','realname'),"id='{$shopinfo['referral_id']}'") ;
        $this->smarty->assign('shopinfo',$shopinfo);
        $this->smarty->assign('referral',$referral);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public  function stop(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $ShopObj=new Shop();
        $id = Buddha_Http_Input::getParameter('id');
        $p = Buddha_Http_Input::getParameter('p');
        $view = Buddha_Http_Input::getParameter('view');
        $Db_Shop = $ShopObj->fetch($id);
        $user_id = $Db_Shop['user_id'];
        $ShopObj->stopShop($id,$user_id);
        Buddha_Http_Head::redirect('店铺停用成功',"index.php?a=more&c=shop&p={$p}&view={$view}");

    }

    public function del(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/
        list($hsk_adminsid, $hsk_adminuser, $hsk_adminpw, $hsk_admintime) = explode("\t",  Buddha_Tool_Password::cookieDecode(Buddha_Http_Cookie::getCookie('buddha_adminsid') , Buddha::$buddha_array['cookie_hash']));
        $uid = $hsk_adminsid;
        $ShopObj=new Shop();
        $id = Buddha_Http_Input::getParameter('id');
        $p = Buddha_Http_Input::getParameter('p');
        $view = Buddha_Http_Input::getParameter('view');
//        $ShopObj->delshop($id);
//        $ShopObj->del($id);
        $UsercommonObj = new Usercommon();
        $Db_Usercommon = $UsercommonObj->manageDelShopAndBelongByShopid($id,$uid);

        if($Db_Usercommon){
//            $ShopObj-> delshop($uid,$id);
            $isok = '删除成功';
        }else{
            $isok = '删除失败';
        }
//echo $isok;

       Buddha_Http_Head::redirect('店铺'.$isok,"index.php?a=more&c=shop&p={$p}&view={$view}");
    }

}