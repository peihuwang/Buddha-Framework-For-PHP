<?php

/**
 * Class ApptokenController
 */
class ApptokenController extends Buddha_App_Action
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

        $where = "isdel=0 ";
        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];


        $rcount = $this->db->countRecords($this->prefix . 'apptoken', $where);

        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }

        $orderby = " order by id ASC ";
        $list = $this->db->getFiledValues('', $this->prefix . 'apptoken', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));
        $strPages = Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=apptoken&', $pagesize);


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

        $ApptokenObj = new Apptoken();
        $appname = Buddha_Http_Input::getParameter('appname');
        $appvalue = Buddha_Http_Input::getParameter('appvalue');
        $key = Buddha_Http_Input::getParameter('key');
        $starttime = Buddha_Http_Input::getParameter('starttime');
        $endtime = Buddha_Http_Input::getParameter('endtime');
        $allowip = Buddha_Http_Input::getParameter('allowip');
        $duetime = Buddha_Http_Input::getParameter('duetime');
        $static = Buddha_Http_Input::getParameter('static');
        $buddhastatus = 0;


        if ($_POST) {
            $data = array();


            $data['appname'] = $appname;
            $data['appvalue'] = $appvalue;
            $data['key'] = $key;
            $data['starttime'] = $starttime;
            $data['endtime'] = $endtime;
            $data['allowip'] = $allowip;
            $data['duetime'] = $duetime;
            $data['static'] = $static;
            $data['buddhastatus'] = $buddhastatus;


            $result = $ApptokenObj->add($data);
            if ($result) {
                Buddha_Http_Head::redirect('添加成功', "index.php?a=more&c=apptoken");
            }

        }

        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    public function edit()
    {
        $ApptokenObj = new Apptoken();
        $id = Buddha_Http_Input::getParameter('id');
        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
        $this->smarty->assign('page', $page);

        $appname = Buddha_Http_Input::getParameter('appname');
        $appvalue = Buddha_Http_Input::getParameter('appvalue');
        $key = Buddha_Http_Input::getParameter('key');
        $starttime = Buddha_Http_Input::getParameter('starttime');
        $endtime = Buddha_Http_Input::getParameter('endtime');
        $allowip = Buddha_Http_Input::getParameter('allowip');
        $duetime = Buddha_Http_Input::getParameter('duetime');
        $static = Buddha_Http_Input::getParameter('static');
        $buddhastatus = Buddha_Http_Input::getParameter('buddhastatus');

        if ($_POST) {
            $data = array();
            $data['appname'] = $appname;
            $data['appvalue'] = $appvalue;
            $data['key'] = $key;
            $data['starttime'] = $starttime;
            $data['endtime'] = $endtime;
            $data['allowip'] = $allowip;
            $data['duetime'] = $duetime;
            $data['static'] = $static;
            $data['buddhastatus'] = $buddhastatus;

            $result = $ApptokenObj->edit($data, $id);
            if ($result) {
                Buddha_Http_Head::redirect('编辑成功', "index.php?a=more&c=apptoken");
            } else {
                Buddha_Http_Head::redirect('编辑失败', "index.php?a=more&c=apptoken");
            }
        }
        $apptokeninfo = $ApptokenObj->fetch($id);
        if (!count($apptokeninfo)) {
            Buddha_Http_Head::redirect('编辑失败', "index.php?a=more&c=apptoken&p={$page}");
        }
        $this->smarty->assign('apptokeninfo', $apptokeninfo);

        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');

    }

    public function del()
    {
        $ApptokenObj = new Apptoken();
        $id = Buddha_Http_Input::getParameter('id');
        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
        $this->smarty->assign('page', $page);

        $result = $ApptokenObj->del($id);
        if ($result) {
            Buddha_Http_Head::redirect('删除成功', "index.php?a=more&c=apptoken&p={$page}");
        } else {
            Buddha_Http_Head::redirect('删除失败', "index.php?a=more&c=apptoken&p={$page}");
        }

        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

}