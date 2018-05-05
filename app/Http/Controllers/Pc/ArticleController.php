<?php

/**
 * Class ArticleController
 */
class ArticleController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function help(){
        $ArticlecatalogObj=new Articlecatalog();
        $Articlecat=$ArticlecatalogObj->getFiledValues(array('id'),"sub='1'");
        if($Articlecat){
            $arr_id=array();
            foreach($Articlecat as $k=>$v){
                $arr_id[]=$v['id'];
            }
           $arr_id= implode(',',$arr_id);
        }

        if($arr_id){
            $where =" isdel=0 and cat_id IN ($arr_id) ";
         $rcount = $this->db->countRecords( $this->prefix.'article', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize')?(int)Buddha_Http_Input::getParameter('pagesize') :30;
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }

        $fields =array('*');
        $orderby = "order by id DESC ";

        $list = $this->db->getFiledValues ($fields,  $this->prefix.'article', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=article&', $pagesize );

        }
        $this->smarty->assign('rcount',$rcount);
        $this->smarty->assign('pcount',$pcount);
        $this->smarty->assign('page',$page);
        $this->smarty->assign('strPages',$strPages);
        $this->smarty->assign('list',$list);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }


    public function details(){
        $ArticleObj=new Article();
      $id=(int)Buddha_Http_Input::getParameter('id');
       if(!$id){
           Buddha_Http_Head::redirect('参数错误','/pc');
       }
        $article=$ArticleObj->fetch($id);
       if(!$article){
           Buddha_Http_Head::redirect('信息不存在','/pc');
       }
        $this->smarty->assign('article',$article);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }


}