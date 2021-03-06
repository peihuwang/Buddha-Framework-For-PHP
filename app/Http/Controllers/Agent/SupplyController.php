<?php

/**
 * Class SupplyController
 */
class SupplyController extends Buddha_App_Action{

    protected $tablenamestr;
    protected $tablename;
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='供应';
        $this->tablename='supply';
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
//////////////↓↓↓↓/////////////////
                        $where.=" and isdel=0 and is_sure=1 and buddhastatus=1";
///////////↑↑↑↑↑↑↑////////////////////
                        break;
                }
            }
            if($keyword){
                $where.=" and goods_name like '%$keyword%'";
            }
            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
            $pagesize = Buddha::$buddha_array['page']['pagesize'];
            $orderby = " order by add_time DESC ";
            $list = $this->db->getFiledValues (array('id','user_id','shop_id','goods_sn','goods_name','is_promote','market_price','promote_price','promote_start_date','promote_end_date','goods_thumb','buddhastatus','is_sure'),  $this->prefix.'supply', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
            $UsercommonObj=new Usercommon();
            foreach($list as $k=>$v)
            {
                $nwstiem=Buddha::$buddha_array['buddha_timestamp'];
                if($v['shop_id']!=0){
                   $shop_name= $ShopObj->getSingleFiledValues(array('name'),"id='{$v['shop_id']}' and user_id='{$v['user_id']}'");
                    $name='商家：'.$shop_name['name'];
                }else{
                    $shop_name= $UserObj->getSingleFiledValues(array('name'),"id='{$v['user_id']}'");
                    $name='个人：'.$shop_name['name'];
                }

                if($v['is_promote']==0)
                {
                    $price="<b>￥<em>".$v['market_price']."</em></b>";
                }else{
                    if($nwstiem<$v['promote_start_date']){
                        $price="<b>￥<em>".$v['market_price']."</em></b>";
                    }elseif($nwstiem>$v['promote_start_date'] and  $nwstiem< $v['promote_end_date']){
                        $price="<b>￥<em>".$v['promote_price']."</em></b> 原价:".$v['market_price']."";
                    }else{
                        $ShopObj->edit(array('promote_price'=>0,'is_promote'=>0,'promote_start_date'=>0,'promote_end_date'=>0),$v['id']);
                    }
                }

                $jsondata[]=array(
                    'id'=>$v['id'],
                    'title'=>$v['goods_name'],
                    'images'=>$v['goods_thumb'],
                    'goods_sn'=>$v['goods_sn'],
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
        $SupplyObj=new Supply();
        $SupplycatObj=new Supplycat();
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误','index.php?a=index&c=supply',2);
        }
        $goods=$SupplyObj->fetch($id);
        if(!$goods){
            Buddha_Http_Head::redirectofmobile('信息不存在','index.php?a=index&c=supply',2);
        }
        $is_sure=Buddha_Http_Input::getParameter('is_sure');
        $remarks=Buddha_Http_Input::getParameter('remarks');
        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['is_sure']=$is_sure;
            $data['remarks']=$remarks;
            $data['buddhastatus']=0;
            $SupplyObj->edit($data,$id);
            $datas=array();
            if($SupplyObj){
                $datas['isok']='true';
                $datas['data']='审核成功';
                $datas['url']='index.php?a=index&c=supply';
            }else{
                $datas['isok']='false';
                $datas['data']='审核失败';
                $datas['url']='index.php?a=index&c=supply';
            }
            Buddha_Http_Output::makeJson($datas);
        }

        $Db_suuplycat=$SupplycatObj->goods_thumbgoods_thumb($goods['supplycat_id']);
        if($Db_suuplycat){
            $cat_name='';
            foreach($Db_suuplycat as $k=>$v){
                $cat_name.=$v['cat_name'].' > ';
            }
            $goods['cat_name']=Buddha_Atom_String::toDeleteTailCharacter($cat_name);
        }
        $Db_shopcar=$ShopObj->getSingleFiledValues(array('name'),"id='{$goods['shop_id']}' and user_id='{$goods['user_id']}'");
        $goods['shop_name']=$Db_shopcar['name'];
        if($goods['is_remote']==1){
        $Db_Region=$RegionObj->getAllArrayAddressByLever($goods['level3']);
            $region='';
           foreach($Db_Region as $k=>$v){
               if($k!=0)
               $region.=$v['name'].' > ';
           }
            $goods['region']=Buddha_Atom_String::toDeleteTailCharacter($region);
        }
        $goods['goods_desc']= str_replace("img","img style='width:80px;height:80px;'",$goods['goods_desc']);

        $this->smarty->assign('goods',$goods);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public  function isdel()
    {
        $supply_id=(int)Buddha_Http_Input::getParameter('id');
        $UsercommonObj = new Usercommon();
        $datas = $UsercommonObj->agentsisdel($this->tablename,$supply_id);
        Buddha_Http_Output::makeJson($datas);
    }


}