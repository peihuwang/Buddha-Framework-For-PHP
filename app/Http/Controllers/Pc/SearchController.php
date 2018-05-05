<?php

/**
 * Class SearchController
 */
class SearchController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        $RegionObj=new Region();
        $locdata = $RegionObj->getLocationDataFromCookie();
        $keyword=Buddha_Http_Input::getParameter('keyword');

        $where =" isdel=0 and is_sure=1  and state=0  {$locdata['sql']}";
        if($keyword){
           $where.=" and (name like '%$keyword%' or specticloc like '%$keyword%') ";
        }

        $orderby = " order by createtime DESC ";
        $rcount = $this->db->countRecords( $this->prefix.'shop', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize')?(int)Buddha_Http_Input::getParameter('pagesize') :30;
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }

        $fields =array('*');
        $list = $this->db->getFiledValues ($fields,  $this->prefix.'shop', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=search&keyword='.$keyword.'&', $pagesize );


        $listhot = $this->db->getFiledValues ($fields,  $this->prefix.'shop',"isdel=0 and is_sure=1 and state=0 and is_rec=1 {$locdata['sql']}  order by click_count DESC  limit 0,10");

        $this->smarty->assign('rcount',$rcount);
        $this->smarty->assign('pcount',$pcount);
        $this->smarty->assign('page',$page);
        $this->smarty->assign('strPages',$strPages);
        $this->smarty->assign('list',$list);
        $this->smarty->assign('listhot',$listhot);
        $this->smarty->assign('keyword',$keyword);


        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
    

}