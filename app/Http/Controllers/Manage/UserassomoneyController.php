<?php

/**
 * Class UserassomoneyController
 */
class UserassomoneyController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }


    public function edit(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/
        $UserassomoneyObj = new Userassomoney();


        //获取数据
        $layerlim=Buddha_Http_Input::getParameter('layerlim');
        $layer_money1=Buddha_Http_Input::getParameter('layer_money1');
        $layer_money2=Buddha_Http_Input::getParameter('layer_money2');
        $layer_money3=Buddha_Http_Input::getParameter('layer_money3');
        $layer_money4=Buddha_Http_Input::getParameter('layer_money4');
        $layer_money5=Buddha_Http_Input::getParameter('layer_money5');
        $layer_money6=Buddha_Http_Input::getParameter('layer_money6');
        $layer_money7=Buddha_Http_Input::getParameter('layer_money7');
        $layer_money8=Buddha_Http_Input::getParameter('layer_money8');
        $layer_money9=Buddha_Http_Input::getParameter('layer_money9');
        $layer_money10=Buddha_Http_Input::getParameter('layer_money10');






        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['layerlim']=$layerlim;
            $data['layer_money1']=$layer_money1;
            $data['layer_money2']=$layer_money2;
            $data['layer_money3']=$layer_money3;
            $data['layer_money4']=$layer_money4;
            $data['layer_money5']=$layer_money5;
            $data['layer_money6']=$layer_money6;
            $data['layer_money7']=$layer_money7;
            $data['layer_money8']=$layer_money8;
            $data['layer_money9']=$layer_money9;
            $data['layer_money10']=$layer_money10;






                $UserassomoneyObj->addOrUpdateUserAssoMoney($data);

                Buddha_Http_Head::redirect('编辑成功',"index.php?a=edit&c=userassomoney");

        }

        $Db_Userassomoney=$UserassomoneyObj->getValidRecordArr();
        $this->smarty->assign('Db_Userassomoney', $Db_Userassomoney);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }



}