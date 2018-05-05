<?php

/**
 * Class ShopController
 */
class LeasecatController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function more(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $LeasecatObj=new Leasecat();
        $getcatTable= $LeasecatObj->getcatlist();

        $this->smarty ->assign( 'getcatTable',$getcatTable);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function add(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $LeasecatObj=new Leasecat();

        $sub=Buddha_Http_Input::getParameter('sub');
        $cat_name=Buddha_Http_Input::getParameter('cat_name');
        $sort=Buddha_Http_Input::getParameter('sort');
        $ifopen=Buddha_Http_Input::getParameter('ifopen')?1:0;
        $cat_path = $LeasecatObj->getClassPath(0,$sub);
        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['sub']=$sub;
            $data['cat_path']=$cat_path;
            $data['cat_name']=trim($cat_name);
            $data['view_order']=$sort;
            $data['ifopen']=$ifopen;

            $LeasecatObj->add($data);
            $LeasecatObj->updatechildcount ($sub);
           if($LeasecatObj){
                Buddha_Http_Head::redirect('添加成功','index.php?a=more&c=leasecat');
            }
        }

       $cid=Buddha_Http_Input::getParameter('cid')?(int)Buddha_Http_Input::getParameter('cid'):0;
       $shopoption=$LeasecatObj->getOption($cid);
       $this->smarty->assign('shopoption',$shopoption);


        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public  function edit(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $LeasecatObj=new Leasecat();
        $id=(int)Buddha_Http_Input::getParameter('id');

        $sub=Buddha_Http_Input::getParameter('sub');
        $cat_name=Buddha_Http_Input::getParameter('cat_name');
        $sort=Buddha_Http_Input::getParameter('sort');
        $ifopen=Buddha_Http_Input::getParameter('ifopen')?1:0;
        $cat_path = $LeasecatObj->getClassPath($id,$sub);
        $oldgoodscatalog=$LeasecatObj->fetch($id);

        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['sub']=$sub;
            $data['cat_name']=trim($cat_name);
            $data['view_order']=$sort;
            $data['ifopen']=$ifopen;

            $LeasecatObj->edit($data,$id);
            $parentid = $sub;
            $cates ['sub']= $oldgoodscatalog['sub'];
            if ($cates ['sub'] != $parentid) {
                $LeasecatObj->updatepath ($id, $cat_path);
                $LeasecatObj-> updatechildcount ( $cates ['sub'] );
                $LeasecatObj->updatechildcount ( $parentid );
            }

            if($LeasecatObj){
                Buddha_Http_Head::redirect('编辑成功',"index.php?a=more&c=leasecat");
            }else{
                Buddha_Http_Head::redirect('编辑错误',"index.php?a=more&c=leasecat");
            }

        }

        $shopoption = $LeasecatObj ->getOption($oldgoodscatalog['sub']);
        $this->smarty->assign('shopoption',$shopoption);

        $shopcat=$LeasecatObj->fetch($id);
        if(!count($shopcat)){
            Buddha_Http_Head::redirect('信息不存在',"index.php?a=more&c=leasecat");

        }

        $this->smarty->assign('shopcat',$shopcat);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function del(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $LeasecatObj=new Leasecat();
        $id = Buddha_Http_Input::getParameter('id');
        $child=$LeasecatObj->fetch($id);
        if($child['child_count']>0){
            Buddha_Http_Head::redirect('删除失败,该分类下存在子类不能移除',"index.php?a=more&c=leasecat");
        }
     $LeasecatObj->del($id);
        $LeasecatObj-> updatechildcount ($child ['sub'] );
        if($LeasecatObj){

            Buddha_Http_Head::redirect('删除成功',"index.php?a=more&c=leasecat");
        }else{
            Buddha_Http_Head::redirect('删除失败',"index.php?a=more&c=leasecat");
        }
    }

}