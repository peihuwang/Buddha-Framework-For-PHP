<?php

/**
 * Class UserController
 */
class UserController extends Buddha_App_Action{

    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
           $act=Buddha_Http_Input::getParameter('act');
           $keyword=Buddha_Http_Input::getParameter('keyword');
        if($act=='list'){
            $where = " isdel=0 and level3='{$UserInfo['level3']}' and (groupid='1' or groupid='4') ";
            if($keyword){
                $where.=" and (username like '%$keyword%' or realname like '%$keyword%') ";
            }
            $rcount = $this->db->countRecords( $this->prefix.'user', $where);
            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
            $pagesize = Buddha::$buddha_array['page']['pagesize'];
          /*  $pcount = ceil($rcount/$pagesize);
            if($page > $pcount){
                $page=$pcount;
            }*/

            $orderby = " order by onlineregtime DESC ";
            $list = $this->db->getFiledValues (array('id','username','realname','mobile','groupid'),  $this->prefix.'user', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
            foreach($list as $k=>$v){
                if($v['realname']){
                    $name=$v['realname'];
                }else{
                    $name=$v['username'];
                }
                if($v['groupid']==1){
                    $groupid='商家';
                }elseif($v['groupid']==4){
                    $groupid='个人';
                }
                $jsondata[]=array(
                    'id'=>$v['id'],
                    'name'=>$name,
                    'mobile'=>$v['mobile'],
                    'groupid'=>$groupid,
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

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function see(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $UserObj=new User();
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=shop',2);
        }
       $userifon=$UserObj->fetch($id);
        if(!$userifon){
            Buddha_Http_Head::redirectofmobile('该数据已不存在了','index.php?a=index&c=shop',2);
        }
        if($userifon['groupid'] == 1){
            $Db_shop=$ShopObj->getSingleFiledValues(array('name'),"user_id='{$userifon['id']}'");
            $userifon['shop_name']=$Db_shop['name'];
        }
        $agent_area=$RegionObj->getAllArrayAddressByLever($userifon['level3']);
        if($agent_area){
            $areadder='';
            foreach($agent_area as $k=> $v) {
                if ($k != 0)
                    $areadder.=$v['name'].' > ';
            }
            $userifon['areadder']=Buddha_Atom_String::toDeleteTailCharacter($areadder);
        }

        $this->smarty->assign('userifon',$userifon);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

}