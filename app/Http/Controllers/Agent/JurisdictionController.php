<?php

/**
 * Class JurisdictionController
 */
class JurisdictionController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        $RegionObj = new Region();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $act = Buddha_Http_Input::getParameter('act');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        if($act=='list'){
        $where = "isdel=0 and user_id='$uid'";
        if($keyword){
            $where.=" and roadname like '%{$keyword}%'";
        }
        $rcount = $this->db->countRecords( $this->prefix.'agentroad', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') :1;
        if($page==1){
            $RegionObj->manageRoad($UserInfo['level3']);
        }
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
            /*  $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
               $page=$pcount;
           }*/
        $orderby = " order by id DESC ";
        $list = $this->db->getFiledValues ( '*',  $this->prefix.'agentroad', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

            if(is_array($list) and count($list)>0){
                $datas['isok']='true';
                $datas['data']=$list;
            }else{
                $datas['isok']='false';
                $datas['data']='没有了';
            }
        Buddha_Http_Output::makeJson($datas);
        }
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function add(){
        $RegionObj=new Region();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $act=Buddha_Http_Input::getParameter('act');
        $id=Buddha_Http_Input::getParameter('id');
        $level4=Buddha_Http_Input::getParameter('level4');
        $level5=Buddha_Http_Input::getParameter('level5');

        if($act=='adderr'){
            $r=$RegionObj->addChilds($id,$level4,$level5);
            $datas=array();
            if($r){
                $datas['isok']='true';
            }else{
                $datas['isok']='false';
            }
            Buddha_Http_Output::makeJson($datas);
        }

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function eidt(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
       $RegionObj=new Region();
        $id=Buddha_Http_Input::getParameter('id');
        $adderr=$RegionObj->getAllArrayAddressByLever($id);
        $level45=Buddha_Http_Input::getParameter('level45');

        if($level45){
        foreach($level45 as $k=>$v){
          $RegionObj->modifyRoad($k,$v);
        }
            Buddha_Http_Output::makeJson(1);
        }

        $this->smarty->assign('adderr', $adderr);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function arear(){
        $json = Buddha_Http_Input::getParameter('json');
        $json_arr =Buddha_Atom_Array::jsontoArray($json);
        $father = $json_arr['father'];
        $RegionObj=new Region();
        $Db_arear= $RegionObj->getFiledValues(array('id','immchildnum','name','father','level'),"father='{$father}' and isdel=0");
        $datas = array();
        if($Db_arear){
            $datas['isok']='true';
            $datas['data']=$Db_arear;
        }else{
            $datas['isok']='false';
            $datas['data']='';
        }
        Buddha_Http_Output::makeJson($datas);
    }


}