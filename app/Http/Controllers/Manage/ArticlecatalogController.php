<?php

/**
 * Class ArticlecatalogController
 */
class ArticlecatalogController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }


    public function more()
    {
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c . '.' . __FUNCTION__);

        $ArticlecatalogObj = new Articlecatalog();
        $getcatTable = $ArticlecatalogObj->getcatlist();

        $this->smarty->assign('getcatTable', $getcatTable);

        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }


    public function add()
    {
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c . '.' . __FUNCTION__);

        $ArticlecatalogObj = new Articlecatalog();
        $sub = ( int )Buddha_Http_Input::getParameter('sub');
        $name = Buddha_Http_Input::getParameter('name');
        $view_order = Buddha_Http_Input::getParameter('view_order');
        $buddhastatus = Buddha_Http_Input::getParameter('buddhastatus') ? 0 : 1;

        $cat_path = $ArticlecatalogObj->getClassPath(0, $sub);

        if ($_POST) {
            $data = array();
            $data['sub'] = $sub;
            $data ['cat_path'] = $cat_path;
            $data['name'] = trim($name);
            $data['view_order'] = $view_order;
            $data['buddhastatus'] = $buddhastatus;
            $ArticlecatalogObj->add($data);
            $ArticlecatalogObj->updatechildcount($sub);
            if ($ArticlecatalogObj) {
                Buddha_Http_Head::redirect('添加成功', "index.php?a=more&c=articlecatalog");
            }
        }

        $cid = Buddha_Http_Input::getParameter('cid') ? (int)Buddha_Http_Input::getParameter('cid') : 0;
        $optionList = $ArticlecatalogObj->getOption($cid);
        $this->smarty->assign('optionList', $optionList);

        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    public function edit()
    {
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c . '.' . __FUNCTION__);
        /*******************/

        $ArticlecatalogObj = new Articlecatalog();
        $id = (int)Buddha_Http_Input::getParameter('id');
        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
        $this->smarty->assign('page', $page);

        $sub = ( int )Buddha_Http_Input::getParameter('sub');
        $name = Buddha_Http_Input::getParameter('name');
        $view_order = Buddha_Http_Input::getParameter('view_order');
        $buddhastatus = Buddha_Http_Input::getParameter('buddhastatus') ? 0 : 1;

        $cat_path = $ArticlecatalogObj->getClassPath($id, $sub);
        $oldgoodscatalog = $ArticlecatalogObj->fetch($id);
        if ($_POST) {

            $data = array();
            $data['sub'] = $sub;
            $data ['cat_path'] = $cat_path;
            $data['name'] = trim($name);
            $data['view_order'] = $view_order;
            $data['buddhastatus'] = $buddhastatus;

            $ArticlecatalogObj->edit($data, $id);

            $parentid = $sub;
            $cates ['sub'] = $oldgoodscatalog['sub'];
            if ($cates ['sub'] != $parentid) {
                $ArticlecatalogObj->updatepath($id, $cat_path);
                $ArticlecatalogObj->updatechildcount($cates ['sub']);
                $ArticlecatalogObj->updatechildcount($parentid);
            }

            if ($ArticlecatalogObj) {
                Buddha_Http_Head::redirect('编辑成功', "index.php?a=more&c=articlecatalog&p={$page}");
            } else {
                Buddha_Http_Head::redirect('编辑错误', "index.php?a=more&c=articlecatalog&p={$page}");
            }

        }


        $optionList = $ArticlecatalogObj->getOption($oldgoodscatalog['sub']);
        $this->smarty->assign('optionList', $optionList);

        $cat = $ArticlecatalogObj->fetch($id);
        if (!count($cat)) {
            Buddha_Http_Head::redirect('信息不存在', "index.php?a=more&c=articlecatalog&p={$page}");

        }
        $this->smarty->assign('cat', $cat);
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    public function del()
    {
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c . '.' . __FUNCTION__);
        /*******************/
        $ArticlecatalogObj = new Articlecatalog();
        $id = Buddha_Http_Input::getParameter('id');
        $child = $ArticlecatalogObj->fetch($id);
        if ($child['isdel'] == 10) {
            Buddha_Http_Head::redirect('此分类为默认分类不能删除', "index.php?a=more&c=articlecatalog");
        }
        if ($child['child_count'] > 0) {
            Buddha_Http_Head::redirect('删除失败,该分类下存在子类不能移除', "index.php?a=more&c=articlecatalog");
        }
        $goodsdel = $ArticlecatalogObj->del($id);
        if ($goodsdel) {

            Buddha_Http_Head::redirect('删除成功', "index.php?a=more&c=articlecatalog");
        }

        Buddha_Http_Head::redirect('删除失败', "index.php?a=more&c=articlecatalog");
    }

}