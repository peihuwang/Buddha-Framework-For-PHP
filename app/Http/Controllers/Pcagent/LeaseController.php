<?php

/**
 * Class LeaseController
 */
class LeaseController extends Buddha_App_Action{
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $UserObj=new User();
        $ShopObj=new Shop();
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;

        $where =" level3='{$UserInfo['level3']}' ";
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
                    $where.=" and isdel=4 and buddhastatus=0";
                    break;
                case 6;
                    $where.=" and isdel=0 and buddhastatus=1";
                    break;
            }
        }
        if($keyword){
            $where.=" and lease_name like '%$keyword%'";
        }
      $rcount = $this->db->countRecords( $this->prefix.'lease', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by add_time DESC ";
        $fields =array('*');
        $list = $this->db->getFiledValues ($fields,  $this->prefix.'lease', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=recruit&', $pagesize );
        foreach($list as $k=>$v){
            if ($v['shop_id'] != 0) {
                $shop_name = $ShopObj->getSingleFiledValues(array('name'), "id='{$v['shop_id']}' and user_id='{$v['user_id']}'");
                $list[$k]['name'] = $shop_name['name'];
            } else{
                $shop_name = $UserObj->getSingleFiledValues(array('name'), "id='{$v['user_id']}'");
                $list[$k]['name'] =$shop_name['name'];
            }
        }
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
        $LeaseObj=new Lease();
        $RLeasecatObj=new Leasecat();
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirect('参数错误','index.php?a=index&c=lease');
        }
        $lease=$LeaseObj->fetch($id);
        if(!$lease){
            Buddha_Http_Head::redirect('信息不存在','index.php?a=index&c=lease');
        }
        $is_sure=Buddha_Http_Input::getParameter('is_sure');
        $remarks=Buddha_Http_Input::getParameter('remarks');
        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['is_sure']=$is_sure;
            $data['remarks']=$remarks;
            $data['buddhastatus']=0;
            $LeaseObj->edit($data,$id);
            if($LeaseObj){
                Buddha_Http_Head::redirect('编辑成功','index.php?a=index&c=lease');
            }else{
                Buddha_Http_Head::redirect('编辑失败','index.php?a=index&c=lease');
            }
        }

        $Db_suuplycat=$RLeasecatObj->goods_thumbgoods_thumb($lease['leasecat_id']);
        if($Db_suuplycat){
            $cat_name='';
            foreach($Db_suuplycat as $k=>$v){
                $cat_name.=$v['cat_name'].' > ';
            }
            $lease['cat_name']=Buddha_Atom_String::toDeleteTailCharacter($cat_name);
        }
        $Db_shopcar=$ShopObj->getSingleFiledValues(array('name'),"id='{$lease['shop_id']}' and user_id='{$lease['user_id']}'");
        $lease['shop_name']=$Db_shopcar['name'];
        if($lease['is_remote']==1){
            $Db_Region=$RegionObj->getAllArrayAddressByLever($lease['level3']);
            $region='';
            foreach($Db_Region as $k=>$v){
                if($k!=0)
                    $region.=$v['name'].' > ';
            }
            $lease['region']=Buddha_Atom_String::toDeleteTailCharacter($region);
        }
        $this->smarty->assign('lease',$lease);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public  function isdel(){
///////////////////////////////////////////////
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 0;
        $user_id=(int)Buddha_Http_Input::getParameter('user_id');
        $s_state='租赁';
        $s_URL='lease';
///////////////////////////////////////////////
        $LeaseObj=new Lease();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $user_id=(int)Buddha_Http_Input::getParameter('user_id');
        $Db_Supply = $LeaseObj->fetch($id);
        $shop_user_id = $Db_Supply['user_id'];
        if($shop_user_id!=$user_id){
            $datas['isok']='false';
            $datas['data']='致命错误,不能修改.请联系管理员.';
        }
///////////////////////////////////////////////
        if($view==0){
            if($Db_Supply['isdel']==0){
                $LeaseObj->updateRecords(array('isdel' => 4), " isdel=0  and  user_id='{$user_id}' and id='{$id}'");
                Buddha_Http_Head::redirect("你将商家的{$s_state}下架成功!","index.php?a=index&c={$s_URL}");
            }else if($Db_Supply['isdel']==4){
                $LeaseObj->updateRecords(array('isdel' =>0), "isdel=4 and  user_id='{$user_id}' and id='{$id}'");
                Buddha_Http_Head::redirect("你将商家的{$s_state}上架成功!","index.php?a=index&c={$s_URL}");
            }
        }elseif ($view==1){//推荐
            if($Db_Supply['is_rec']==1){
                $LeaseObj->updateRecords(array('is_rec' => 0), " isdel=0 and  user_id='{$user_id}' and id='{$id}'");
                Buddha_Http_Head::redirect("你将商家{$s_state}取消推荐停用成功！","index.php?a=index&c={$s_URL}");
            }else if($Db_Supply['is_rec']==0){
                $LeaseObj->updateRecords(array('is_rec' => 1), " isdel=0 and  user_id='{$user_id}' and id='{$id}'");
                Buddha_Http_Head::redirect("你将商家{$s_state}设为推荐成功！","index.php?a=index&c={$s_URL}");
            }
        }elseif ($view==2){//热门
            if($Db_Supply['is_hot']==1){
                $LeaseObj->updateRecords(array('is_hot' => 0), " isdel=0 and  user_id='{$user_id}' and id='{$id}'");
                Buddha_Http_Head::redirect("你将商家{$s_state}取消热门成功！","index.php?a=index&c={$s_URL}");
            }else if($Db_Supply['is_hot']==0){
                $LeaseObj->updateRecords(array('is_hot' => 1), " isdel=0 and  user_id='{$user_id}' and id='{$id}'");
                Buddha_Http_Head::redirect("你将商家{$s_state}设为热门成功！","index.php?a=index&c={$s_URL}");
            }
        }
///////////////////////////////////////////////
    }

}