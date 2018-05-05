<?php

/**
 * Class HeartproController
 */
class HeartproController extends Buddha_App_Action{


    protected $tablenamestr;
    protected $tablename;
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='1分营销';
        $this->tablename='heartpro';
    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $UserObj=new User();
        $ShopObj=new Shop();
        $act=Buddha_Http_Input::getParameter('act');
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):1;
        $keyword=Buddha_Http_Input::getParameter('keyword');

        if($act=='list'){
            $where =" level3='{$UserInfo['level3']}' and isdel=0 ";
            if($view){
                switch($view){
                    case 2;
                        $where.=' and is_sure=0';
                        break;
                    case 3;
                        $where.=" and is_sure=1";
                        break;
                    case 4;
                        $where.=" and is_sure=4 ";
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


            $filed=array('id','name','keywords as details','small','user_id','is_sure','buddhastatus','shop_id','price');

            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
            $pagesize = Buddha::$buddha_array['page']['pagesize'];
            $orderby = " order by createtime DESC ";

            $list = $this->db->getFiledValues ($filed,  $this->prefix.$this->tablename, $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

            $UsercommonObj=new Usercommon();

            $CommonObj=new Common();
            foreach($list as $k=>$v)
            {
                if($v['shop_id']!=0){
                   $shop_name= $ShopObj->getSingleFiledValues(array('name'),"id='{$v['shop_id']}' and user_id='{$v['user_id']}'");
                    $name='商家：'.$shop_name['name'];
                }else{
                    $shop_name= $UserObj->getSingleFiledValues(array('name'),"id='{$v['user_id']}'");
                    $name='个人：'.$shop_name['name'];
                }

                $price="<b>￥<em>".$v['price']."</em></b>";

                $jsondata[]=array(
                    'id'=>$v['id'],
                    'title'=>$v['name'],
                    'brief'=>$CommonObj->intercept_strlen($v['brief'],18),
                    'images'=>$v['small'],
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
        $this->smarty->assign('c', $c= $this->c);
        $this->smarty->assign('title', $this->tablenamestr);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit()
    {
        $ShopObj = new Shop();
        $RegionObj = new Region();
        $id = (int)Buddha_Http_Input::getParameter('id');
        if(!$id)
        {
            Buddha_Http_Head::redirectofmobile('参数错误',"index.php?a=index&c={$this->tablename}",2);
        }

        $filed=array('id','shop_id','user_id','shop_id','table_name','table_id','unit_id','price','stock','votecount','level1','level2','level3','is_remote','applystarttimestr','applyendtimestr','onshelftimestr','offshelftimestr','keywords','small','name','remarks','details','is_sure');
        $demand = $this->db->getSingleFiledValues($filed,$this->tablename,"id='{$id}'");

        if(!$demand)
        {
            Buddha_Http_Head::redirectofmobile('信息不存在',"index.php?a=index&c={$this->tablename}",2);
        }

        $is_sure = Buddha_Http_Input::getParameter('is_sure');

        $remarks = Buddha_Http_Input::getParameter('remarks');
        if(Buddha_Http_Input::isPost()){
            $data = array();
            $data['is_sure'] = $is_sure;
            $data['remarks'] = $remarks;
            if($is_sure==1){
                $data['buddhastatus'] = 0;
            }
           $num = $this->db->updateRecords($data, $this->tablename,"id = '{$id}'");
            $datas = array();
            if($num)
            {
                $datas['isok'] = 'true';
                $datas['data'] = $this->tablenamestr.'审核成功';
                $datas['url'] = "index.php?a=index&c={$this->tablename}";
            }else{
                $datas['isok'] = 'false';
                $datas['data'] = $this->tablenamestr.'审核失败';
                $datas['url'] = "index.php?a=index&c={$this->tablename}";
            }
            Buddha_Http_Output::makeJson($datas);
        }

        $Db_shopcat = $ShopObj->getSingleFiledValues(array('name'),"id='{$demand['shop_id']}' and user_id='{$demand['user_id']}'");
        $demand['shop_name'] = $Db_shopcat['name'];
        $Db_Table = $this->db->getSingleFiledValues(array('goods_name'),$demand['table_name'],"id='{$demand['table_id']}' and user_id='{$demand['user_id']}'");
        $demand['supply_name'] = $Db_Table['goods_name'];
        $Db_Table = $this->db->getSingleFiledValues(array('unit'),'supplycat',"id='{$demand['unit_id']}'");
        $demand['unit_name'] = $Db_Table['unit'];

        if($demand['is_remote']==1)
        {
            $Db_Region=$RegionObj->getAllArrayAddressByLever($demand['level3']);
            $region='';
            foreach($Db_Region as $k=>$v){
                if($k!=0)
                    $region.=$v['name'].' > ';
            }
            $demand['region']=Buddha_Atom_String::toDeleteTailCharacter($region);
        }


        $demand['desc']= str_replace("img","img style='width:80px;height:80px;'",$demand['desc']);

        unset( $demand['user_id']);

        $this->smarty->assign('goods',$demand);
        $this->smarty->assign('title', $this->tablenamestr);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
}



//////////////↓↓↓↓上/下 架/////////////////
    public  function isdel()
    {
        $demand_id=(int)Buddha_Http_Input::getParameter('id');
        $UsercommonObj = new Usercommon();
        $datas = $UsercommonObj->agentsisdel($this->tablename,$demand_id);
        Buddha_Http_Output::makeJson($datas);
    }
///////////↑↑↑↑↑↑↑////////////////////

}