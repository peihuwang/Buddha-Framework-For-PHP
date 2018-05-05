<?php

/**
 * Class ShopController
 */
class SupplycatController extends Buddha_App_Action{


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

        $SupplycatObj=new Supplycat();
        $getcatTable= $SupplycatObj->getcatlist();

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

        $SupplycatObj=new Supplycat();

        $sub=Buddha_Http_Input::getParameter('sub');
        $cat_name=Buddha_Http_Input::getParameter('cat_name');
        $unit=Buddha_Http_Input::getParameter('unit');
        $sort=Buddha_Http_Input::getParameter('sort');
        $ifopen=Buddha_Http_Input::getParameter('ifopen')?1:0;
        $cat_path = $SupplycatObj->getClassPath(0,$sub);
        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['sub']=$sub;
            $data['cat_path']=$cat_path;
            $data['cat_name']=trim($cat_name);
            $data['unit']=trim($unit);
            $data['view_order']=$sort;
            $data['ifopen']=$ifopen;

            $SupplycatObj->add($data);
            $SupplycatObj->updatechildcount ($sub);
           if($SupplycatObj){
                Buddha_Http_Head::redirect('添加成功','index.php?a=more&c=supplycat');
            }
        }

       $cid=Buddha_Http_Input::getParameter('cid')?(int)Buddha_Http_Input::getParameter('cid'):0;
       $shopoption=$SupplycatObj->getOption($cid);
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

        $SupplycatObj=new Supplycat();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $sub=Buddha_Http_Input::getParameter('sub');
        $cat_name=Buddha_Http_Input::getParameter('cat_name');
        $unit=Buddha_Http_Input::getParameter('unit');
        $sort=Buddha_Http_Input::getParameter('sort');
        $ifopen=Buddha_Http_Input::getParameter('ifopen')?1:0;
        $cat_path = $SupplycatObj->getClassPath($id,$sub);
        $oldgoodscatalog=$SupplycatObj->fetch($id);

        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['sub']=$sub;
            $data['cat_name']=trim($cat_name);
            $data['unit']=trim($unit);
            $data['view_order']=$sort;
            $data['ifopen']=$ifopen;

            $SupplycatObj->edit($data,$id);
            $parentid = $sub;
            $cates ['sub']= $oldgoodscatalog['sub'];
            if ($cates ['sub'] != $parentid) {
                $SupplycatObj->updatepath ($id, $cat_path);
                $SupplycatObj-> updatechildcount ( $cates ['sub'] );
                $SupplycatObj->updatechildcount ( $parentid );
            }

            if($SupplycatObj){
                Buddha_Http_Head::redirect('编辑成功',"index.php?a=more&c=supplycat");
            }else{
                Buddha_Http_Head::redirect('编辑错误',"index.php?a=more&c=supplycat");
            }

        }

        $shopoption = $SupplycatObj ->getOption($oldgoodscatalog['sub']);
        $this->smarty->assign('shopoption',$shopoption);

        $shopcat=$SupplycatObj->fetch($id);
        if(!count($shopcat)){
            Buddha_Http_Head::redirect('信息不存在',"index.php?a=more&c=supplycat");

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

        $SupplycatObj= new Supplycat();
        $id = Buddha_Http_Input::getParameter('id');
        $child=$SupplycatObj->fetch($id);
        if($child['child_count']>0){
            Buddha_Http_Head::redirect('删除失败,该分类下存在子类不能移除',"index.php?a=more&c=Supplycat");
        }
        $SupplycatObj->del($id);
        $SupplycatObj-> updatechildcount ($child ['sub'] );
        if($SupplycatObj){

            Buddha_Http_Head::redirect('删除成功',"index.php?a=more&c=Supplycat");
        }else{
            Buddha_Http_Head::redirect('删除失败',"index.php?a=more&c=Supplycat");
        }
    }

}