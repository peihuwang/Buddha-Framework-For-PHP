<?php

/**
 * Class DemandController
 */
class DemandController extends Buddha_App_Action
{

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
                $where.=" and name like '%$keyword%'";
            }
            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
            $pagesize = Buddha::$buddha_array['page']['pagesize'];
            $orderby = " order by add_time DESC ";
            $list = $this->db->getFiledValues ('',  $this->prefix.'demand', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

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
                   $price="<b>￥<em>".$v['budget']."</em></b>";

                $jsondata[]=array(
                    'id'=>$v['id'],
                    'title'=>$v['name'],
                    'images'=>$v['demand_thumb'],
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
        $DemandObj=new Demand();
        $DemandcatObj=new Demandcat();
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误','index.php?a=index&c=demand',2);
        }
        $demand=$DemandObj->fetch($id);
        if(!$demand){
            Buddha_Http_Head::redirectofmobile('信息不存在','index.php?a=index&c=demand',2);
        }
        $is_sure=Buddha_Http_Input::getParameter('is_sure');
        $remarks=Buddha_Http_Input::getParameter('remarks');
        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['is_sure']=$is_sure;
            $data['remarks']=$remarks;
            if($is_sure==1){
                $data['buddhastatus']=0;
            }
            $DemandObj->edit($data,$id);
            $datas=array();
            if($DemandObj){
                $datas['isok']='true';
                $datas['data']='审核成功';
                $datas['url']='index.php?a=index&c=demand';
            }else{
                $datas['isok']='false';
                $datas['data']='审核失败';
                $datas['url']='index.php?a=index&c=demand';
            }
            Buddha_Http_Output::makeJson($datas);
        }

        $Db_demandcat=$DemandcatObj->goods_thumbgoods_thumb($demand['demandcat_id']);
        if($Db_demandcat){
            $cat_name='';
            foreach($Db_demandcat as $k=>$v){
                $cat_name.=$v['cat_name'].' > ';
            }
            $demand['cat_name']=Buddha_Atom_String::toDeleteTailCharacter($cat_name);
        }
        $Db_shopcat=$ShopObj->getSingleFiledValues(array('name'),"id='{$demand['shop_id']}' and user_id='{$demand['user_id']}'");
        $demand['shop_name']=$Db_shopcat['name'];
        if($demand['is_remote']==1){
        $Db_Region=$RegionObj->getAllArrayAddressByLever($demand['level3']);
            $region='';
           foreach($Db_Region as $k=>$v){
               if($k!=0)
               $region.=$v['name'].' > ';
           }
            $goods['region']=Buddha_Atom_String::toDeleteTailCharacter($region);
        }
        $this->smarty->assign('demand',$demand);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public  function isdel()
    {
        $demand_id=(int)Buddha_Http_Input::getParameter('id');

        $UsercommonObj = new Usercommon();
        $datas = $UsercommonObj->agentsisdel($this->tablename,$demand_id);
        Buddha_Http_Output::makeJson($datas);
    }


}