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

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;

        $where = " (agent_id='{$uid}' or level3='{$UserInfo['level3']}') ";
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
                case 5;
                    $where.=" and isdel=4 and is_sure=1  and state=1";
                    break;
                case 6;
                    $where.=" and isdel=0 and is_sure=1 and state=1";
                    break;
            }
        }
        if($keyword){
            $where.=" and name like '%$keyword%'";
        }
        $rcount = $this->db->countRecords( $this->prefix.'shop', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by createtime DESC ";
        $fields =array('id','name','number','small','createtime','is_verify','is_sure','state','user_id','remarks','isdel','is_rec','is_hot');
        $list = $this->db->getFiledValues ($fields,  $this->prefix.'shop', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ));
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=shop&', $pagesize );
        $this->smarty->assign('rcount',$rcount);
        $this->smarty->assign('pcount',$pcount);
        $this->smarty->assign('page',$page);
        $this->smarty->assign('strPages',$strPages);
        $this->smarty->assign('list',$list);
        $this->smarty->assign('view',$view);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $ShopcatObj=new Shopcat();
        $UserObj=new User();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $partnerrate=(int)Buddha_Http_Input::getParameter('partnerrate');
        $is_sure=Buddha_Http_Input::getParameter('is_sure');
        $remarks=Buddha_Http_Input::getParameter('remarks');

        if(!$id){
            Buddha_Http_Head::redirect('参数错误！','index.php?a=index&c=shop');
        }
        $shopinfo=$ShopObj->fetch($id);
        if(!$shopinfo){
            Buddha_Http_Head::redirect('没有找到您要的信息！','index.php?a=index&c=shop');
        }

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
                Buddha_Http_Head::redirect('审核成功','index.php?a=index&c=shop');

            }else{
                Buddha_Http_Head::redirect('审核失败','index.php?a=index&c=shop');
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
        $Region_name=$RegionObj->getAllArrayAddressByLever($shopinfo['level5']);
        if($Region_name){
            $regionname='';
            foreach($Region_name as $k=>$v){
                if($k!=0 and $k<4){
                    $regionname.=$v['name'].' > ';
                }
            }
            $shopinfo['region']=Buddha_Atom_String::toDeleteTailCharacter($regionname);
        }
        $shoptype=$ShopObj->getNature($shopinfo['storetype']);
        $shopinfo['storetype']=$shoptype;

        $referral=$UserObj->getSingleFiledValues(array('id','realname','partnerrate'),"id='{$shopinfo['referral_id']}'");
        if($shopinfo['level1']){//获取区域名称
            $shen=$RegionObj->getSingleFiledValues(array('name'),"id={$shopinfo['level1']}");
            $shi=$RegionObj->getSingleFiledValues(array('name'),"id={$shopinfo['level2']}");
            $qu=$RegionObj->getSingleFiledValues(array('name'),"id={$shopinfo['level3']}");
            $shopinfo['diqu'] = $shen['name'] . '>' . $shi['name'] . '>' . $qu['name'];
        }
        $this->smarty->assign('shopinfo',$shopinfo);
        $this->smarty->assign('referral',$referral);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public  function isdel(){
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 0;

        $ShopObj=new Shop();
        $shop_id=(int)Buddha_Http_Input::getParameter('id');
        $user_id=(int)Buddha_Http_Input::getParameter('user_id');
        $Db_Shop = $ShopObj->fetch($shop_id);
        $shop_user_id = $Db_Shop['user_id'];
        if($shop_user_id!=$user_id){
            $datas['isok']='false';
            $datas['data']='致命错误,不能修改.请联系管理员.';
        }
        if($view==0){
            if($Db_Shop['isdel']==0){
                $ShopObj->stopShop($shop_id,$user_id);
                Buddha_Http_Head::redirect('你将商家店铺停用成功！','index.php?a=index&c=shop');
            }else if($Db_Shop['isdel']==4){
                $ShopObj->startShop($shop_id,$user_id);
                Buddha_Http_Head::redirect('你将商家店铺启用成功！','index.php?a=index&c=shop');
            }
        }elseif ($view==1){//推荐
            if($Db_Shop['is_rec']==1){
                $ShopObj->updateRecords(array('is_rec' => 0), " isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
                Buddha_Http_Head::redirect('你将商家店铺取消推荐停用成功！','index.php?a=index&c=shop');
            }else if($Db_Shop['is_rec']==0){
                $ShopObj->updateRecords(array('is_rec' => 1), " isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
                Buddha_Http_Head::redirect('你将商家店铺设为推荐成功！','index.php?a=index&c=shop');
            }
        }elseif ($view==2){//热门
            if($Db_Shop['is_hot']==1){
                $ShopObj->updateRecords(array('is_hot' => 0), " isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
                Buddha_Http_Head::redirect('你将商家店铺取消热门成功！','index.php?a=index&c=shop');
            }else if($Db_Shop['is_hot']==0){
                $ShopObj->updateRecords(array('is_hot' => 1), " isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
                Buddha_Http_Head::redirect('你将商家店铺设为热门成功！','index.php?a=index&c=shop');
            }
        }
    }

}