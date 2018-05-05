<?php

/**
 * Class LogController
 */
class LogController extends Buddha_App_Action
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

        $where = "1=1 ";
        $job = Buddha_Http_Input::getParameter('job');
        $jb = Buddha_Http_Input::getParameter('jb');
        $keys = Buddha_Http_Input::getParameter('keys');
        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        if ($jb) {
            $ids = Buddha_Http_Input::getParameter('ids');
            $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
            if (!is_array($ids)) {
                Buddha_Http_Head::redirect('导出失败', "index.php?a=more&c=log&p={$page}");

            }

            $id = implode(',', Buddha_Http_Input::getParameter('ids'));
            $where = " isdel=0 ";
            $list = $this->db->getFiledValues('*', $this->prefix . 'log', " id IN ($id)
		 order by id DESC");

            $str = "操作员标号\t操作员名称\t操作功能\t操作内容\t原内容\t登录日期\t登录IP\n";

            //$str = "类型\t用户名\t姓名\t卡号\t性别\t邮件\t电话\t身份证\t积分\t生日\n";
            $str = iconv('utf-8', 'gb2312', $str);
            foreach ($list as $k => $row) {
                $uid = iconv('utf-8', 'gb2312', $row['uid']);//操作员标号
                $username = iconv('utf-8', 'gb2312', $row['username']);//操作员名称
                $operateuse = iconv('utf-8', 'gb2312', $row['operateuse']);//操作功能
                $operatedesc = iconv('utf-8', 'gb2312', $row['operatedesc']);//操作内容
                $operateolddesc = iconv('utf-8', 'gb2312', $row['operateolddesc']);//原内容
                $logdate = '`' . iconv('utf-8', 'gb2312', date('Y-m-d', $row['logdate'])) . '`';//登录日期
                $ip = iconv('utf-8', 'gb2312', $row['ip']);//登录IP

                $str .= $uid . "\t" . $username . "\t" . $operateuse . "\t" . $operatedesc . "\t" . $operateolddesc . "\t" . $logdate . "\t" . $ip . "\n";
            }
            $filename = 'log.more.logsexport_' . date('YmdHis') . '.xls'; //设置文件名
            echo Buddha_Tool_File::exportExcel($filename, $str);//导出
            die();
        }
//======↑↑↑↑↑==========


        switch ($job) {
            case'admin';
                $where .= " and username LIKE '%{$keys}%'";
                break;
        }
        $rcount = $this->db->countRecords($this->prefix . 'log', $where);

        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }

        $orderby = " order by logdate DESC ";
        $list = $this->db->getFiledValues('', $this->prefix . 'log', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));
        $strPages = Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=log&', $pagesize);


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
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c . '.' . __FUNCTION__);
        /*******************/

        $LogObj = new Log();
        $uid = Buddha_Http_Input::getParameter('uid');
        $username = Buddha_Http_Input::getParameter('username');
        $operateuse = Buddha_Http_Input::getParameter('operateuse');
        $operatedesc = Buddha_Http_Input::getParameter('operatedesc');
        $operateolddesc = Buddha_Http_Input::getParameter('operateolddesc');


        if ($_POST) {
            $data = array();
            $data['uid'] = $uid;
            $data['username'] = $username;
            $data['operateuse'] = $operateuse;
            $data['operatedesc'] = $operatedesc;
            $data['operateolddesc'] = $operateolddesc;

            $result = $LogObj->add($data);
            if ($result) {
                Buddha_Http_Head::redirect('添加成功', "index.php?a=more&c=log");
            }

        }

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

        $LogObj = new Log();
        $id = Buddha_Http_Input::getParameter('id');
        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
        $this->smarty->assign('page', $page);

        $uid = Buddha_Http_Input::getParameter('uid');
        $username = Buddha_Http_Input::getParameter('username');
        $operateuse = Buddha_Http_Input::getParameter('operateuse');
        $operatedesc = Buddha_Http_Input::getParameter('operatedesc');
        $operateolddesc = Buddha_Http_Input::getParameter('operateolddesc');

        if ($_POST) {
            $data = array();
            $data['uid'] = $uid;
            $data['username'] = $username;
            $data['operateuse'] = $operateuse;
            $data['operatedesc'] = $operatedesc;
            $data['operateolddesc'] = $operateolddesc;

            $result = $LogObj->edit($data, $id);
            if ($result) {
                Buddha_Http_Head::redirect('编辑成功', "index.php?a=more&c=log");
            } else {
                Buddha_Http_Head::redirect('编辑失败', "index.php?a=more&c=log");
            }
        }
        $logedit = $LogObj->fetch($id);
        if (!count($logedit)) {
            Buddha_Http_Head::redirect('编辑失败', "index.php?a=more&c=log&p={$page}");
        }
        $this->smarty->assign('logedit', $logedit);

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

        $LogObj = new Log();
        $id = Buddha_Http_Input::getParameter('id');
        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
        $this->smarty->assign('page', $page);

        $result = $LogObj->del($id);
        if ($result) {
            Buddha_Http_Head::redirect('删除成功', "index.php?a=more&c=log&p={$page}");
        } else {
            Buddha_Http_Head::redirect('删除失败', "index.php?a=more&c=log&p={$page}");
        }

        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

}