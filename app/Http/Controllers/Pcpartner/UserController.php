<?php

/**
 * Class UserController
 */
class UserController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $where = " isdel=0 and referral_id='{$uid}' and (groupid='1' or groupid='4') ";
        if($keyword){
            $where.=" and (username like '%$keyword%' or realname like '%$keyword%') ";
        }
        $rcount = $this->db->countRecords( $this->prefix.'user', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
         $pcount = ceil($rcount/$pagesize);
          if($page > $pcount){
              $page=$pcount;
          }

        $orderby = " order by onlineregtime DESC ";
        $fields=array('id','username','realname','mobile','groupid');
        $list = $this->db->getFiledValues ($fields,  $this->prefix.'user', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=user&', $pagesize );
        $this->smarty->assign('rcount',$rcount);
        $this->smarty->assign('pcount',$pcount);
        $this->smarty->assign('page',$page);
        $this->smarty->assign('strPages',$strPages);
        $this->smarty->assign('list',$list);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public  function see (){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $UserObj=new User();
        $RegionObj=new Region();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=shop',2);
        }
        $userifon=$UserObj->fetch($id);
        if(!$userifon){
            Buddha_Http_Head::redirectofmobile('该数据已不存在了','index.php?a=index&c=shop',2);
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