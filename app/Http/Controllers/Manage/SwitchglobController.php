<?php

/**
 * Class SwitchglobController
 */
class SwitchglobController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }


    public function edit()
    {
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c . '.' . __FUNCTION__);
        ($this->c . '.' . __FUNCTION__);
        /*******************/
        $SwitchglobObj = new Switchglob();

        //获取数据
        $is_openworldchat = (int)Buddha_Http_Input::getParameter('is_openworldchat');


        if (Buddha_Http_Input::isPost()) {
            $data = array();
            $data['is_openworldchat'] = $is_openworldchat;

            $SwitchglobObj->addOrUpdateSwitchGlob($data);

            Buddha_Http_Head::redirect('编辑成功', "index.php?a=edit&c=switchglob");

        }

        $Db_Data = $SwitchglobObj->getValidRecordArr();
        $this->smarty->assign('Db_Data', $Db_Data);


        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }


}