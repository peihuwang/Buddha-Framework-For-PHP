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

    public function more()
    {
        $ArticlecatalogObj = new Articlecatalog();
        if (Buddha_Http_Input::getParameter('job')) {
            $job = Buddha_Http_Input::getParameter('job');
            if (!Buddha_Http_Input::getParameter('goodsID')) {
                Buddha_Http_Head::redirect('没有选中', "index.php?a=more&c=article");
            }
            $ids = implode(',', Buddha_Http_Input::getParameter('goodsID'));
            switch ($job) {
                case 'sort';

                    $sorts = Buddha_Http_Input::getParameter('view_order');
                    foreach ($sorts as $k => $v) {
                        $this->db->updateRecords(array('view_order' => $v), 'article', 'id=' . $k);
                    }
                    Buddha_Http_Head::redirect('编辑成功', "index.php?a=more&c=article");
                    break;
                case 'del';
                    $this->db->delRecords('article', "id IN ($ids)");
                    Buddha_Http_Head::redirect('编辑成功', "index.php?a=more&c=article");

                    break;
                case 'open';
                    $this->db->updateRecords(array('buddhastatus' => 0), 'article', "id IN ($ids)");
                    Buddha_Http_Head::redirect('编辑成功', "index.php?a=more&c=article");
                    break;
                case 'close';
                    $this->db->updateRecords(array('buddhastatus' => 1), 'article', "id IN ($ids)");
                    Buddha_Http_Head::redirect('编辑成功', "index.php?a=more&c=article");
                    break;
            }
        }
        $where = " isdel=0 ";
        $rcount = $this->db->countRecords($this->prefix . 'article', $where);
        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }
        $orderby = " order by  view_order ASC ,id DESC ";
        $list = $this->db->getFiledValues('*', $this->prefix . 'article', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));
        foreach ($list as $k => $v) {
            $cat_name = $ArticlecatalogObj->getSingleFiledValues(array('name'), "id='{$v['cat_id']}' and (isdel=0 or isdel=10)");
            $list[$k]['cat_name'] = $cat_name['name'];
        }
        $strPages = Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=article&', $pagesize);

        $this->smarty->assign('rcount', $rcount);
        $this->smarty->assign('pcount', $pcount);
        $this->smarty->assign('page', $page);
        $this->smarty->assign('strPages', $strPages);
        $this->smarty->assign('list', $list);

        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }


    public function add()
    {
        $ArticleObj = new Article();

        $cat_id = ( int )Buddha_Http_Input::getParameter('cat_id');
        $name = Buddha_Http_Input::getParameter('name');
        $number = Buddha_Http_Input::getParameter('number');
        $buddhastatus = Buddha_Http_Input::getParameter('buddhastatus') ? 0 : 1;
        $brief = Buddha_Http_Input::getParameter('brief');
        $content = Buddha_Http_Input::getParameter('content');
        if ($_POST) {
            $data = array();
            $data['cat_id'] = $cat_id;
            $data['name'] = trim($name);
            $data['number'] = trim($number);

            $data['buddhastatus'] = $buddhastatus;
            $data['brief'] = $brief;
            $data['content'] = $content;
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $data['content'] = $content;

            $goods_id = $ArticleObj->add($data);


            if ($goods_id) {

                Buddha_Http_Head::redirect('添加成功', "index.php?a=more&c=article");
            }
        }
        $ArticlecatalogObj = new Articlecatalog();
        $optionList = $ArticlecatalogObj->getOption();


        $this->smarty->assign('optionList', $optionList);

        Buddha_Editor_Set::getInstance()->setEditor(
            array(array('id' => 'content', 'content' => '', 'width' => '100', 'height' => 500)
            ));


        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    public function edit()
    {

        $id = (int)Buddha_Http_Input::getParameter('id');
        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
        $this->smarty->assign('page', $page);

        $ArticleObj = new Article();


        $cat_id = ( int )Buddha_Http_Input::getParameter('cat_id');
        $name = Buddha_Http_Input::getParameter('name');
        $number = Buddha_Http_Input::getParameter('number');
        $buddhastatus = Buddha_Http_Input::getParameter('buddhastatus') ? 0 : 1;
        $brief = Buddha_Http_Input::getParameter('brief');
        $content = Buddha_Http_Input::getParameter('content');

        if ($_POST) {
            $data = array();
            $data['cat_id'] = $cat_id;
            $data['name'] = trim($name);
            $data['number'] = trim($number);
            $data['buddhastatus'] = $buddhastatus;
            $data['brief'] = $brief;
            $data['content'] = $content;

            $result = $ArticleObj->edit($data, $id);


            if ($result) {

                Buddha_Http_Head::redirect('编辑成功', "index.php?a=more&c=article&p={$page}");
            } else {

                Buddha_Http_Head::redirect('编辑失败', "index.php?a=more&c=article&p={$page}");
            }
        }


        $article = $ArticleObj->fetch($id);
        if (!count($article)) {
            Buddha_Http_Head::redirect('编辑失败', "index.php?a=more&c=article&p={$page}");
        }
        $this->smarty->assign('article', $article);


        $ArticlecatalogObj = new Articlecatalog();
        $optionList = $ArticlecatalogObj->getOption($article['cat_id']);
        $this->smarty->assign('optionList', $optionList);


        Buddha_Editor_Set::getInstance()->setEditor(
            array(array('id' => 'content', 'content' => '' . $article['content'] . '', 'width' => '100', 'height' => 500)
            ));
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    public function  del()
    {
        $ArticleObj = new Article();
        $id = Buddha_Http_Input::getParameter('id');
        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
        $this->smarty->assign('page', $page);

        $result = $ArticleObj->del($id);
        if ($result) {
            Buddha_Http_Head::redirect('删除成功', "index.php?a=more&c=article&p={$page}");
        } else {
            Buddha_Http_Head::redirect('删除失败', "index.php?a=more&c=article&p={$page}");
        }
    }

    public function message()
    {//留言列表
        $UserchatObj = new Userchat();//实例化数据库操作类
        $where = " isdel=0 ";//正常显示的
        $rcount = $this->db->countRecords($this->prefix . 'userchat', $where);// 计算条数用来分页
        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;//获取当前的页数
        $pagesize = Buddha::$buddha_array['page']['pagesize'];//获取每页显示的条数
        $pcount = ceil($rcount / $pagesize);//计算总共多少页
        if ($page > $pcount) {//限定页数
            $page = $pcount;
        }
        $orderby = " order by id DESC ";//排序
        $list = $this->db->getFiledValues('*', $this->prefix . 'userchat', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));
        $strPages = Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=article&', $pagesize);//上一页1,2,3下一页（通过类库获得）
        foreach ($list as $k => $v) {//截取显示留言过长的
            if (mb_strlen($v['question']) > 20) {
                $list[$k]['question'] = mb_substr($v['question'], 0, 20) . '...';
            }
        }
        $this->smarty->assign('rcount', $rcount);
        $this->smarty->assign('pcount', $pcount);
        $this->smarty->assign('page', $page);
        $this->smarty->assign('strPages', $strPages);
        $this->smarty->assign('list', $list);

        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    function to_view()
    {
        $userObj = new User();
        $id = (int)Buddha_Http_Input::getParameter('id');//获取数据
        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;//得到页数
        $this->smarty->assign('page', $page);
        $UserchatObj = new Userchat();
        $messageInfo = $UserchatObj->getSingleFiledValues('*', "id={$id}");
        $mobile = $userObj->getSingleFiledValues(array('mobile'), "id={$messageInfo['user_id']}");
        $messageInfo['mobile'] = $mobile['mobile'];
        $this->smarty->assign('messageInfo', $messageInfo);
        /*print_r($messageInfo);
        exit;*/
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    function mesdel()
    {
        $UserchatObj = new Userchat();
        $id = Buddha_Http_Input::getParameter('id');
        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
        $this->smarty->assign('page', $page);

        $result = $UserchatObj->del($id);
        if ($result) {
            Buddha_Http_Head::redirect('删除成功', "index.php?a=message&c=article&p={$page}");
        } else {
            Buddha_Http_Head::redirect('删除失败', "index.php?a=message&c=article&p={$page}");
        }
    }
}