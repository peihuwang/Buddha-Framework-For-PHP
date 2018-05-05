<?php

/**
 * Class IndexController
 */
class IndexController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index()
    {
        $customer = $hsk_adminsid = $hsk_adminuid = $hsk_adminpw = $hsk_admintime = $adminright = $loginSuccess = '';
        if (Buddha_Http_Cookie::getCookie('buddha_adminsid')) {
            list($hsk_adminsid, $hsk_adminuser, $hsk_adminpw, $hsk_admintime) = explode("\t", Buddha_Tool_Password::cookieDecode(Buddha_Http_Cookie::getCookie('buddha_adminsid'), Buddha::$buddha_array['cookie_hash']));


            if (is_numeric($hsk_adminsid) && (strlen($hsk_adminpw) == 32) && is_numeric($hsk_admintime)) {
                $hsk_adminsid = (int)$hsk_adminsid;
                $hsk_adminpw = addslashes($hsk_adminpw);
                $hsk_admintime = (int)$hsk_admintime;
                $loginSuccess = TRUE;
            }

        }


        if (!$loginSuccess)
            Buddha_Http_Head::redirect('请登录', 'index.php?a=login&c=index');

        $MenuObj = new Menu();
        $MemberObj = new Member();
        list($hsk_adminsid, $hsk_adminuser, $hsk_adminpw, $hsk_admintime) = explode("\t", Buddha_Tool_Password::cookieDecode(Buddha_Http_Cookie::getCookie('buddha_adminsid'), Buddha::$buddha_array['cookie_hash']));


        $member = $MemberObj->fetch($hsk_adminsid);

        $menu_id = rtrim($member['permissions'], ",");
        if ($menu_id == '' and $member['adminid'] == 1) {//adminid==1   表示超级管理员
            $menu = $MenuObj->getFiledValues('', " isopen=1 AND sub=0 and isdisplay =1 ORDER BY sort ASC");
        } else {
            $menu = $MenuObj->getFiledValues('', " isopen=1 AND sub=0 AND isdisplay =1 and id in ({$menu_id})  ORDER BY sort ASC");
        }
        if (is_array($menu)) {
            foreach ($menu as $k => $v) {
                $id = $v['id'];
                $menu[$k]['child'] = $MenuObj->getFiledValues('*', "isopen=1 AND isdisplay =1 AND  sub={$id} ORDER BY sort ASC");
            }
        }

        $MemberObj = new Member();
        $member = $MemberObj->fetch($hsk_adminsid);

        if ($member['permissions']) {
            $permissions = ',' . $member['permissions'];
            foreach ($menu as $k => $v) {
                if (!stripos($permissions, ',' . $v['id'] . ',')) {
                    unset($menu[$k]);
                }
            }
        }

        $this->smarty->assign('menu', $menu);
        $hsk_siteCopyRight = isset(Buddha::$buddha_array['cache']['config']['hsk_siteCopyRight'])
            ? Buddha::$buddha_array['cache']['config']['hsk_siteCopyRight'] : '';

        $this->smarty->assign('hsk_siteCopyRight', $hsk_siteCopyRight);

        $this->smarty->assign('member', $member);
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    public function login()
    {
        $hsk_adminsid = $hsk_adminuid = $hsk_adminpw = $hsk_admintime = $adminright = $loginSuccess = '';

        if (Buddha_Http_Cookie::getCookie('buddha_adminsid')) {
            list($hsk_adminsid, $hsk_adminuser, $hsk_adminpw, $hsk_admintime) = explode("\t", Buddha_Tool_Password::cookieDecode(Buddha_Http_Cookie::getCookie('buddha_adminsid'), Buddha::$buddha_array['cookie_hash']));


            if (is_numeric($hsk_adminsid) && (strlen($hsk_adminpw) == 32) && is_numeric($hsk_admintime)) {
                $hsk_adminsid = (int)$hsk_adminsid;
                $hsk_adminpw = addslashes($hsk_adminpw);
                $hsk_admintime = (int)$hsk_admintime;
                $loginSuccess = TRUE;
            }

        }
        if ($loginSuccess) {

            $MemberObj = new Member();
            $MemberObj->edit(array(
                'regip' => Buddha_Explorer_Network::getIp(),
                'logintime' => time()), $hsk_adminsid);

            Buddha_Http_Head::redirect('登录后台成功', 'index.php?a=index&c=index');
        }


        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    public function dologin()
    {
        $admin_user = Buddha_Http_Input::getParameter('admin_user');
        $admin_pw = Buddha_Http_Input::getParameter('admin_pw');
        $checkcode = Buddha_Http_Input::getParameter('checkcode');


        if (!Buddha_Http_Session::checkCaptcha($checkcode)) {
            $errorstr = Buddha_Locale_Lang::i18n('验证码不正确');
            Buddha_Http_Head::redirect($errorstr, 'index.php?a=login&c=index');
        }


        //验证
        $rules = array(
            '用户' => 'required|min:5',
            '密码' => 'required|min:3',
        );

        $input = array(
            '用户' => $admin_user,
            '密码' => $admin_pw
        );


        if ($errorstr = Buddha_Atom_Validator::getErrorMsg($input, $rules)) {
            Buddha_Http_Head::redirect($errorstr, 'index.php?a=login&c=index');
        }


        $username = $admin_user;
        $password = Buddha_Tool_Password::strongPassword($admin_pw);
        $MemberObj = new Member();
        $customer = $MemberObj->checkLogin((string)$username, $password);

        if ($customer) {
            Buddha_Http_Cookie::setCookie('buddha_adminsid', Buddha_Tool_Password::cookieEncode($customer['id'] . "\t" . $customer['username'] . "\t" . $customer['password'] . "\t" . Buddha::$buddha_array['buddha_timestamp'], Buddha::$buddha_array['cookie_hash'])
            );
            $MemberObj->edit(
                array('loginip' => Buddha_Explorer_Network::getIp(),
                    'logintime' => Buddha::$buddha_array['buddha_timestamp']),
                $customer['id']);

            /***********************
             *   操  作  日  志   *
             ***********************/
            Buddha_Atom_Secury::logWrite($this->c . '.' . __FUNCTION__, $other = Buddha::convertToChinese($this->c) . "::" . Buddha::convertToChinese(__FUNCTION__) . "::" . Buddha::convertToChinese(__FUNCTION__) . "");


            Buddha_Http_Head::redirect('登录后台成功', 'index.php?a=index&c=index');

        } else {
            Buddha_Http_Head::redirect('请登录', 'index.php?a=login&c=index');
        }


    }

    public function main()
    {
        $hsk_adminsid = $hsk_adminuid = $hsk_adminpw = $hsk_admintime = $adminright = $loginSuccess = '';


        if (Buddha_Http_Cookie::getCookie('buddha_adminsid')) {
            list($hsk_adminsid, $hsk_adminuser, $hsk_adminpw, $hsk_admintime) = explode("\t", Buddha_Tool_Password::cookieDecode(Buddha_Http_Cookie::getCookie('buddha_adminsid'), Buddha::$buddha_array['cookie_hash']));
            if (is_numeric($hsk_adminsid) && (strlen($hsk_adminpw) == 32) && is_numeric($hsk_admintime)) {
                $hsk_adminsid = (int)$hsk_adminsid;
                $hsk_adminpw = addslashes($hsk_adminpw);
                $hsk_admintime = (int)$hsk_admintime;
                $loginSuccess = TRUE;
            }
        }


        //操作员登录资料
        $MemberObj = new Member();
        $member = $MemberObj->fetch($hsk_adminsid);
        $this->smarty->assign('member', $member);


        $sys = array();
        $sys['serverSoft'] = $_SERVER['SERVER_SOFTWARE'];
        $sys['serverOS'] = PHP_OS;
        $sys['PHPVersion'] = PHP_VERSION;
        if (ini_get('file_uploads')) {
            $uploadFile = ini_get('upload_max_filesize') . ' / 允许';
        } else {
            $uploadFile = '<span class="r">不支持</span>';
        }
        $sys['uploadFile'] = $uploadFile;
        $sys['domain'] = $_SERVER['SERVER_NAME'];
        $sys['max_execution_time'] = ini_get('max_execution_time') . ' seconds';
        $sys['php_memory_limit'] = ini_get('memory_limit');
        $sys['serverTime'] = date('Y-m-d H:i:s', time());;
        $sys['current_memory'] = function_exists('memory_get_usage') ? Buddha_Tool_File::getRealSize(memory_get_usage()) : '未知';

        $this->smarty->assign('sys', $sys);


        if (!$loginSuccess)
            Buddha_Http_Head::redirect('请登录', 'index.php?a=login&c=index');


        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');

    }

    public function logout()
    {
        list($hsk_adminsid, $hsk_adminuser, $hsk_adminpw, $hsk_admintime) = explode("\t", Buddha_Tool_Password::cookieDecode(Buddha_Http_Cookie::getCookie('buddha_adminsid'), Buddha::$buddha_array['cookie_hash']));
        $MemberObj = new Member();
        $MemberObj->edit(array('lasttime' => time()), $hsk_adminsid);
        Buddha_Http_Cookie::setCookie('buddha_adminsid', '', -1);
        Buddha_Http_Head::redirect('注销成功', 'index.php?a=login&c=index');
    }

}