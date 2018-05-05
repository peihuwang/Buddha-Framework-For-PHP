<?php

/**
 * Class PaymentController
 */
class PaymentController extends Buddha_App_Action
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


        $PaymentObj = new Payment();

        $payments = $PaymentObj->get_builtin();

        $white_list = $PaymentObj->get_white_list();

        $setPayment = $PaymentObj->getSetPayment();


        foreach ($payments as $key => $value) {
            $payments [$key] ['system_enabled'] = in_array($key, $white_list);
            $payments [$key] ['installed'] = in_array($key, $setPayment);
            if (in_array($key, $setPayment)) {
                $payments [$key] ['payment_id'] = array_search($key, $setPayment);
            }
        }


        $this->smarty->assign('payments', $payments);
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

        $code = trim(Buddha_Http_Input::getParameter('code'));
        $PaymentObj = new Payment();
        $payInfo = $PaymentObj->get_builtin_info($code);

        if (Buddha_Http_Input::isPost()) {
            $data = array();
            $data['is_online'] = $payInfo['is_online'];
            $data['payment_code'] = $payInfo['code'];
            $data['payment_name'] = $payInfo['name'];
            $data['payment_icon'] = $payInfo['payment_icon'];
            $data['payment_desc'] = Buddha_Atom_Html::toConvert(Buddha_Http_Input::getParameter('desc'));
            $data['config'] = Buddha_Http_Input::getParameter('config') ? serialize(Buddha_Http_Input::getParameter('config')) : '';
            $data['ifopen'] = (int)Buddha_Http_Input::getParameter('ifopen');
            $num = $PaymentObj->countRecords(" payment_code='{$code}'");
            if ($num) {
                $PaymentObj->updateRecords($data, "payment_code='{$code}'");
            } else {
                $PaymentObj->add($data);
            }
            Buddha_Http_Head::redirect('配置成功', "index.php?a=more&c=payment");
        }

        $info = $PaymentObj->getSingleFiledValues('', "payment_code='{$code}'");
        if ($info['config']) {
            $config = unserialize($info['config']);
            $info = array_merge($info, $config);
        }


        Buddha_Editor_Set::getInstance()->setEditor(
            array(array('id' => 'content', 'content' => $info['payment_desc'], 'width' => '99', 'height' => 300)
            ));


        Buddha_Atom_Html::radioChecked('ifopen_', $info['ifopen']);
        $ifopen_0 = $GLOBALS['ifopen_0'];
        $ifopen_1 = $GLOBALS['ifopen_1'];
        $this->smarty->assign('ifopen_0', $ifopen_0);
        $this->smarty->assign('ifopen_1', $ifopen_1);
        $this->smarty->assign('info', $info);
        $this->smarty->assign('payInfo', $payInfo);

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

        $code = trim(Buddha_Http_Input::getParameter('code'));
        $PaymentObj = new Payment();
        $payInfo = $PaymentObj->get_builtin_info($code);

        if (Buddha_Http_Input::isPost()) {
            $data = array();
            $data['is_online'] = $payInfo['is_online'];
            $data['payment_code'] = $payInfo['code'];
            $data['payment_name'] = $payInfo['name'];
            $data['payment_icon'] = $payInfo['payment_icon'];
            $data['payment_desc'] = Buddha_Atom_Html::toConvert(Buddha_Http_Input::getParameter('desc'));
            $data['config'] = Buddha_Http_Input::getParameter('config') ? serialize(Buddha_Http_Input::getParameter('config')) : '';
            $data['ifopen'] = (int)Buddha_Http_Input::getParameter('ifopen');

            $num = $PaymentObj->countRecords(" payment_code='{$code}'");
            if ($num) {
                $PaymentObj->updateRecords($data, "payment_code='{$code}'");
            } else {
                $PaymentObj->add($data);
            }
            Buddha_Http_Head::redirect('安装成功', "index.php?a=more&c=payment");

        }

        Buddha_Atom_Html::radioChecked('ifopen_', 1);
        $ifopen_0 = $GLOBALS['ifopen_0'];
        $ifopen_1 = $GLOBALS['ifopen_1'];
        $this->smarty->assign('ifopen_0', $ifopen_0);
        $this->smarty->assign('ifopen_1', $ifopen_1);


        $this->smarty->assign('payInfo', $payInfo);


        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }


}