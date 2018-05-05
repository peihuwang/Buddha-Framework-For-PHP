<?php

/**
 * Class MenuController
 */
class MenuController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }
    public function more(){
        /******************
         *   权 限 控 制   *
         *********************/
        // Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        Buddha_Atom_Secury::backendPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $MenucatObj = new Menu();


        $menucatTable=$MenucatObj->menu_apply_table();

        $this->smarty ->assign( 'menucatTable',$menucatTable);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }
    public  function add(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        global $_LANG;
        $MenuObj = new Menu();

        $sub=Buddha_Http_Input::getParameter('sub');
        $name=Buddha_Http_Input::getParameter('name');
        $services=Buddha_Http_Input::getParameter('services');
        $operator=Buddha_Http_Input::getParameter('operator');
        $sort=Buddha_Http_Input::getParameter('sort');
        $isopen=Buddha_Http_Input::getParameter('isopen')?1:0;
        $isdisplay=Buddha_Http_Input::getParameter('isdisplay')?1:0;
        $createtime=time();

        if($_POST){
            $data=array();
            $data['sub']=$sub;
            $data['name']=$name;
            $data['services']=$services;
            $data['operator']=$operator;
            $data['isopen']=$isopen;
            $data['isdisplay']=$isdisplay;
            $data['sort']=$sort;
            $data['createtime']=$createtime;
            $MenuObj->add($data);
            if($MenuObj){
                Buddha_Http_Head::redirect('菜单添加成功','index.php?a=more&c=menu');
            }

        }

        $cid =Buddha_Http_Input::getParameter('cid')?(int)Buddha_Http_Input::getParameter('cid'):0;
        $menuoption=$MenuObj->getOption($cid);
        $this->smarty->assign('menuoption',$menuoption);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
    public  function edit(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $MenuObj = new Menu();
        $id=Buddha_Http_Input::getParameter('id');

        $sub=Buddha_Http_Input::getParameter('sub');
        $name=Buddha_Http_Input::getParameter('name');
        $services=Buddha_Http_Input::getParameter('services');
        $operator=Buddha_Http_Input::getParameter('operator');
        $sort=Buddha_Http_Input::getParameter('sort');
        $isopen=Buddha_Http_Input::getParameter('isopen')?1:0;
        $isdisplay=Buddha_Http_Input::getParameter('isdisplay')?1:0;

        $createtime=time();

        if($_POST){
            $data['sub']=$sub;
            $data['name']=$name;
            $data['services']=$services;
            $data['operator']=$operator;
            $data['isopen']=$isopen;
            $data['isdisplay']=$isdisplay;
            $data['sort']=$sort;
            $data['createtime']=$createtime;
            $result= $MenuObj->edit($data,$id);
            if($result){
                Buddha_Http_Head::redirect('菜单编辑成功','index.php?a=more&c=menu');
            }else{
                Buddha_Http_Head::redirect('菜单编辑失败','index.php?a=more&c=menu');

            }
        }

        $meun=$MenuObj->fetch($id);

        $menuoption=$MenuObj->getOption($meun['sub']);
        $this->smarty->assign('menuoption',$menuoption);
        $this->smarty->assign('meun',$meun);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function del(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $MenuObj = new Menu();
        $id=Buddha_Http_Input::getParameter('id');

        $result= $MenuObj->del($id);
        if($result){
            Buddha_Http_Head::redirect('菜单删除成功','index.php?a=more&c=menu');
        }else{
            Buddha_Http_Head::redirect('菜单删除失败','index.php?a=more&c=menu');

        }

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


}