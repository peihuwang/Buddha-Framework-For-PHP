<?php

/**
 * Class ManagerController
 */
class ManagerController extends Buddha_App_Action
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
        /*******************/

        if (Buddha_Http_Input::getParameter('job')) {
            $job = Buddha_Http_Input::getParameter('job');
            if (!Buddha_Http_Input::getParameter('ids')) {
                Buddha_Http_Head::redirect('没有选中', 'index.php?a=more&c=manager');
            }
            $ids = implode(',', Buddha_Http_Input::getParameter('ids'));
            switch ($job) {
                case 'del';
                    $this->db->delRecords('member', "id IN ($ids) and id!=1");
                    break;
            }
        }
        $where = "1=1";
        $rcount = $this->db->countRecords($this->prefix . 'member', $where);
        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }

        $orderby = " order by id DESC ";
        $filed = " id,memberid,username,nickname,utype,mobile,email,state,regtime ";
        $list = $this->db->getFiledValues('', $this->prefix . 'member', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));
        $strPages = Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=manager&', $pagesize);
        $utypearr = array(
            array("id" => '0', 'name' => 'CEO办公室'),
            array('id' => '1', 'name' => '总经理办公室'),
            array('id' => '2', 'name' => '经理部办公室'),
            array("id" => '3', 'name' => '后勤部办公室'),
            array('id' => '4', 'name' => '市场部办公室'),
            array('id' => '5', 'name' => '营销部办公室'),
            array('id' => '6', 'name' => '生产部办公室'),
            array('id' => '7', 'name' => '运输部办公室')
        );
        $this->smarty->assign('rcount', $rcount);
        $this->smarty->assign('pcount', $pcount);
        $this->smarty->assign('page', $page);
        $this->smarty->assign('strPages', $strPages);
        $this->smarty->assign('list', $list);
        $this->smarty->assign('utypearr', $utypearr);

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

        $memberid = Buddha_Http_Input::getParameter('typeid');
        $username = Buddha_Http_Input::getParameter('username');
        $nickname = Buddha_Http_Input::getParameter('name');
        $email = Buddha_Http_Input::getParameter('email');
        $mobile = Buddha_Http_Input::getParameter('mobile');
        $password = Buddha_Http_Input::getParameter('password');
        $state = Buddha_Http_Input::getParameter('state');
        $utype = Buddha_Http_Input::getParameter('typeid');
        $regip = $_SERVER["REMOTE_ADDR"];
        $regtime = time();
        if ($_POST) {
            $data = array();
            $data['memberid'] = $memberid;
            $data['username'] = $username;
            $data['nickname'] = $nickname;
            $data['mobile'] = $mobile;
            $data['email'] = $email;
            $data['password'] = Buddha_Tool_Password::strongPassword($password);
            $data['state'] = $state;
            $data['utype'] = $utype;
            $data['regip'] = $regip;
            $data['regtime'] = $regtime;
            $ManagerObj = new Manager();
            $manager = $ManagerObj->add($data);
            if ($manager) {
                Buddha_Http_Head::redirect('添加成功', 'index.php?a=more&c=manager');
            }

        }
        $utypearr = array(
            array("id" => '0', 'name' => 'CEO办公室'),
            array('id' => '1', 'name' => '总经理办公室'),
            array('id' => '2', 'name' => '经理部办公室'),
            array("id" => '3', 'name' => '后勤部办公室'),
            array('id' => '4', 'name' => '市场部办公室'),
            array('id' => '5', 'name' => '营销部办公室'),
            array('id' => '6', 'name' => '生产部办公室'),
            array('id' => '7', 'name' => '运输部办公室')
        );
        $this->smarty->assign('utypearr', $utypearr);

        $statearr = array(
            array("id" => '0', 'name' => '未审核'),
            array("id" => '1', 'name' => '未激活'),
            array('id' => '2', 'name' => '正常'),
            array('id' => '3', 'name' => '停用'),

        );
        $this->smarty->assign('statearr', $statearr);

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


        $id = (int)Buddha_Http_Input::getParameter('id');
        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
        $this->smarty->assign('page', $page);
        $ManagerObj = new Manager();


        $memberid = Buddha_Http_Input::getParameter('typeid');
        $username = Buddha_Http_Input::getParameter('username');
        $nickname = Buddha_Http_Input::getParameter('name');
        $email = Buddha_Http_Input::getParameter('email');
        $mobile = Buddha_Http_Input::getParameter('mobile');

        $state = Buddha_Http_Input::getParameter('state');
        $utype = Buddha_Http_Input::getParameter('typeid');
        $regip = $_SERVER["REMOTE_ADDR"];
        $logintime = time();

        if ($_POST) {
            $data = array();
            $data['memberid'] = $memberid;
            $data['username'] = $username;
            $data['nickname'] = $nickname;
            $data['mobile'] = $mobile;
            $data['email'] = $email;


            if (Buddha_Http_Input::getParameter('password') != '') {
                $password = Buddha_Http_Input::getParameter('password');
                $data['password'] = Buddha_Tool_Password::strongPassword($password);
            }


            $data['state'] = $state;
            $data['utype'] = $utype;
            $data['regip'] = $regip;
            $data['logintime'] = $logintime;

            $manager = $ManagerObj->edit($data, $id);
            if ($manager) {
                Buddha_Http_Head::redirect('编辑成功', 'index.php?a=more&c=manager');
            }


        }

        $optionList = $ManagerObj->fetch($id);

        $this->smarty->assign('optionList', $optionList);
        $utypearr = array(
            array("id" => '0', 'name' => 'CEO办公室'),
            array('id' => '1', 'name' => '总经理办公室'),
            array('id' => '2', 'name' => '经理部办公室'),
            array("id" => '3', 'name' => '后勤部办公室'),
            array('id' => '4', 'name' => '市场部办公室'),
            array('id' => '5', 'name' => '营销部办公室'),
            array('id' => '6', 'name' => '生产部办公室'),
            array('id' => '7', 'name' => '运输部办公室')
        );
        $this->smarty->assign('utypearr', $utypearr);

        $statearr = array(
            array("id" => '0', 'name' => '未审核'),
            array("id" => '1', 'name' => '未激活'),
            array('id' => '2', 'name' => '正常'),
            array('id' => '3', 'name' => '停用'),

        );
        $this->smarty->assign('statearr', $statearr);
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

        $theaterdobj = new Manager();
        $page = Buddha_Http_Input::getParameter('p');
        $id = Buddha_Http_Input::getParameter('id');
        $theaterdel = $theaterdobj->del($id);
        if ($theaterdel) {

            if ($theaterdel) {
                Buddha_Http_Head::redirect('删除成功', "index.php?a=more&c=manager&p={$page}");
            }
            Buddha_Http_Head::redirect('删除失败', "index.php?a=more&c=manager&p={$page}");

        }
    }


}