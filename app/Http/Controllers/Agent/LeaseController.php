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
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $UserObj=new User();
        $ShopObj=new Shop();
        $act=Buddha_Http_Input::getParameter('act');
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):1;
        $keyword=Buddha_Http_Input::getParameter('keyword');

        if($act=='list'){
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
//                        $where.=" and isdel=4 and buddhastatus=1";
//////////////↓↓↓↓/////////////////
                        $where.=" and isdel=0 and is_sure=1 and buddhastatus=1";
///////////↑↑↑↑↑↑↑////////////////////                        break;
                }
            }
            if($keyword){
                $where.=" and lease_name like '%$keyword%'";
            }
            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
            $pagesize = Buddha::$buddha_array['page']['pagesize'];
            $orderby = " order by add_time DESC ";
            $list = $this->db->getFiledValues ('',  $this->prefix.'lease', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

            $UsercommonObj=new Usercommon();

            foreach($list as $k=>$v)
            {
                if($v['shop_id']!=0){
                   $shop_name= $ShopObj->getSingleFiledValues(array('name'),"id='{$v['shop_id']}' and user_id='{$v['user_id']}'");
                    $name='商家：'.$shop_name['name'];
                }else{
                    $shop_name= $UserObj->getSingleFiledValues(array('name'),"id='{$v['user_id']}'");
                    $name='个人：'.$shop_name['name'];
                }
                $price="<b>￥<em>".$v['rent']."</em></b>";
                $jsondata[]=array(
                    'id'=>$v['id'],
                    'title'=>$v['lease_name'],
                    'images'=>$v['lease_thumb'],
                    'user_id'=>$v['user_id'],
                    'is_sure'=>$UsercommonObj->agentsissure($v['is_sure']),
                    'state'=>$UsercommonObj->agentsshelfstr($v['isdel']),
                    'name'=>$name,
                    'price'=>$price,
                );
            }
            if($list){
                $data['isok']='true';
                $data['data']=$jsondata;
            }else{
                $data['isok']='false';
                $data['data']='没有数据!';
            }
            Buddha_Http_Output::makeJson($data);
        }

        $this->smarty->assign('view',$view);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit(){
        $LeaseObj=new Lease();
        $LeasecatObj=new Leasecat();
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误','index.php?a=index&c=lease',2);
        }
        $lease=$LeaseObj->fetch($id);
        if(!$lease){
            Buddha_Http_Head::redirectofmobile('信息不存在','index.php?a=index&c=lease',2);
        }
        $is_sure=Buddha_Http_Input::getParameter('is_sure');
        $remarks=Buddha_Http_Input::getParameter('remarks');
        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['is_sure']=$is_sure;
            $data['remarks']=$remarks;
            $data['buddhastatus']=0;
            $LeaseObj->edit($data,$id);
            $datas=array();
            if($LeaseObj){
                $datas['isok']='true';
                $datas['data']='审核成功';
                $datas['url']='index.php?a=index&c=lease';
            }else{
                $datas['isok']='false';
                $datas['data']='审核失败';
                $datas['url']='index.php?a=index&c=lease';
            }
            Buddha_Http_Output::makeJson($datas);
        }
        $Db_leasecat=$LeasecatObj->goods_thumbgoods_thumb($lease['leasecat_id']);
        if($Db_leasecat){
            $cat_name='';
            foreach($Db_leasecat as $k=>$v){
                $cat_name.=$v['cat_name'].' > ';
            }
            $lease['cat_name']=Buddha_Atom_String::toDeleteTailCharacter($cat_name);
        }
        if($lease['is_remote']==1){
            $Db_Region=$RegionObj->getAllArrayAddressByLever($lease['level3']);
            $region='';
            foreach($Db_Region as $k=>$v){
                if($k!=0)
                    $region.=$v['name'].' > ';
            }
            $lease['region']=Buddha_Atom_String::toDeleteTailCharacter($region);
        }
        $Db_shopcar=$ShopObj->getSingleFiledValues(array('name'),"id='{$lease['shop_id']}' and user_id='{$lease['user_id']}'");
        $lease['shop_name']=$Db_shopcar['name'];
        $this->smarty->assign('lease',$lease);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
//////////////↓↓↓↓/////////////////
    public  function isdel()
    {

        $lease_id=(int)Buddha_Http_Input::getParameter('id');
        $UsercommonObj = new Usercommon();
        $datas = $UsercommonObj->agentsisdel($this->tablename,$lease_id);
        Buddha_Http_Output::makeJson($datas);
    }
///////////↑↑↑↑↑↑↑////////////////////


}