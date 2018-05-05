<?php

/**
 * Class ArticleController
 */
class ArticleController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }
    public function mylist(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ArticlecatalogObj=new Articlecatalog();
        $articlect=$ArticlecatalogObj->fetch('7');
        if(!$articlect){
            Buddha_Http_Head::redirectofmobile('分类不存在','index.php?a=index&c=partner',2);
        }
////////////////////////////////////////////////////////////
        if($articlect['child_count']>0){
            $list= $ArticlecatalogObj->getFiledValues('',"isdel=0 and sub='{$articlect['id']}'");
            foreach($list  as  $k=>$v){
                if($v ['child_count']>0){
                    $list[$k]['sub']= $ArticlecatalogObj->getFiledValues('',"isdel=0 and sub='{$v['id']}'");
                }
            }
        }
////////////////////////////////////////////////////////////
        $this->smarty->assign('list',$list);
   
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $cat_id=(int)Buddha_Http_Input::getParameter('cat_id');

        $where = "isdel=0 and cat_id='{$cat_id}'";
        $rcount = $this->db->countRecords( $this->prefix.'article', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by id DESC ";
        $fields=array('*');
        $list = $this->db->getFiledValues ($fields, $this->prefix.'article', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=article&', $pagesize );

        $this->smarty->assign('rcount',$rcount);
        $this->smarty->assign('pcount',$pcount);
        $this->smarty->assign('page',$page);
        $this->smarty->assign('strPages',$strPages);
        $this->smarty->assign('list',$list);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function info(){
        $ArticleObj=new Article();
        $id=Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=article',2);
        }
        $article=$ArticleObj->fetch($id);
        if(!$article){
            Buddha_Http_Head::redirectofmobile('信息不存在！','index.php?a=index&c=article',2);
        }

        $this->smarty->assign('article',$article);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
}