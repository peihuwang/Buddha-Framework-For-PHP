<?php

/**
 * Class RecruitController
 */
class RecruitController extends Buddha_App_Action{

    protected $tablenamestr;
    protected $tablename;
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='招聘';
        $this->tablename='recruit';
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
///////////↑↑↑↑↑↑↑////////////////////
                        break;
                }
            }
            if($keyword){
                $where.=" and recruit_name like '%$keyword%'";
            }
            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
            $pagesize = Buddha::$buddha_array['page']['pagesize'];
            $orderby = " order by add_time DESC ";
            $list = $this->db->getFiledValues ('',  $this->prefix.'recruit', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

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

                if($v['pay']!=0){
                   $price="<b>￥<em>".$v['pay']."</em></b>";
                }else{
                    $price="<b>面议</b>";
                }

                $jsondata[]=array(
                    'id'=>$v['id'],
                    'title'=>$v['recruit_name'],
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
        $RecruitObj=new Recruit();
        $RecruitcatObj=new Recruitcat();
        $ShopObj=new Shop();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误','index.php?a=index&c=recruit',2);
        }
        $recruit=$RecruitObj->fetch($id);
        if(!$recruit){
            Buddha_Http_Head::redirectofmobile('信息不存在','index.php?a=index&c=recruit',2);
        }
        $is_sure=Buddha_Http_Input::getParameter('is_sure');
        $remarks=Buddha_Http_Input::getParameter('remarks');
        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['is_sure']=$is_sure;
            $data['remarks']=$remarks;
            $data['buddhastatus']=0;
            $RecruitObj->edit($data,$id);
            $datas=array();
            if($RecruitObj){
                $datas['isok']='true';
                $datas['data']='审核成功';
                $datas['url']='index.php?a=index&c=recruit';
            }else{
                $datas['isok']='false';
                $datas['data']='审核失败';
                $datas['url']='index.php?a=index&c=recruit';
            }
            Buddha_Http_Output::makeJson($datas);
        }

        $Db_recruitcat=$RecruitcatObj->goods_thumbgoods_thumb($recruit['recruit_id']);
        if($Db_recruitcat){
            $cat_name='';
            foreach($Db_recruitcat as $k=>$v){
                $cat_name.=$v['cat_name'].' > ';
            }
            $recruit['cat_name']=Buddha_Atom_String::toDeleteTailCharacter($cat_name);
        }
        $Db_shopcar=$ShopObj->getSingleFiledValues(array('name'),"id='{$recruit['shop_id']}' and user_id='{$recruit['user_id']}'");
        $recruit['shop_name']=$Db_shopcar['name'];
        $recruit['education']=$RecruitObj->getRecruitment_name($recruit['education']);
        $recruit['work']=$RecruitObj->getwork_experience_name($recruit['work']);

        $this->smarty->assign('recruit',$recruit);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

//////////////↓↓↓↓/////////////////
    public  function isdel()
    {
        $recruit_id=(int)Buddha_Http_Input::getParameter('id');
        $UsercommonObj = new Usercommon();
        $datas = $UsercommonObj->agentsisdel($this->tablename,$recruit_id);
        Buddha_Http_Output::makeJson($datas);
    }
///////////↑↑↑↑↑↑↑////////////////////

}