<?php

/**
 * Class BillController
 */
class BillController extends Buddha_App_Action{

    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $where = " isdel=0 and user_id='$uid' ";
        $rcount = $this->db->countRecords( $this->prefix.'bill', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }

        $orderby = " order by createtime DESC ";
        $list = $this->db->getFiledValues ( '*',  $this->prefix.'bill', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=bill&', $pagesize );


        $this->smarty->assign('rcount',$rcount);
        $this->smarty->assign('pcount',$pcount);
        $this->smarty->assign('page',$page);
        $this->smarty->assign('strPages',$strPages);
        $this->smarty->assign('list',$list);


        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }

    public function getout(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        ///////////////////////////////////////////////
        $get_id = (int)Buddha_Http_Input::getParameter('mid')?(int)Buddha_Http_Input::getParameter('mid') : 0;
        if($get_id>0){
            $UserObj=new User();
            $uid= Buddha_Http_Cookie::getCookie('uid');
            $DB_User = $UserObj->getSingleFiledValues(array('banlance')," id='{$uid}' and state=1");
            $totle_money=$DB_User['banlance'];
            $difference=$totle_money-$get_id;
            if($difference>=50){
                $state=array('state'=>'提现申请成功！');
                $datas['isok']='true';
                $datas['data']=$state;
            }else if($difference < 50){
                $state=array('state'=>'提现后余额不足,请重新输入！');
                $datas['isok']='false';
                $datas['data']=$state;
            }
            Buddha_Http_Output::makeJson($datas);
        }

//////////////////////////////////////////////////////
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

}