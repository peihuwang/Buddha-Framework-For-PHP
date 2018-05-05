<?php

/**
 * Class ShopController
 */
class ShopcatController extends Buddha_App_Action{


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

        $ShopcatObj=new Shopcat();
        $getcatTable= $ShopcatObj->getcatlist();
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

        $ShopcatObj=new Shopcat();

        $sub=Buddha_Http_Input::getParameter('sub');
        $cat_name=Buddha_Http_Input::getParameter('cat_name');
        $sort=Buddha_Http_Input::getParameter('sort');
        $ad_id=Buddha_Http_Input::getParameter('ad_id');
        $ad_name=Buddha_Http_Input::getParameter('ad_name');
        $ifopen=Buddha_Http_Input::getParameter('ifopen')?1:0;
        $cat_path = $ShopcatObj->getClassPath(0,$sub);
        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['sub']=$sub;
            $data['cat_name']=trim($cat_name);
            $data['cat_path']=$cat_path;
            if($sub==0){
                $data['ad_id']=$ad_id;
                $data['ad_name']=$ad_name;
            }
            $data['view_order']=$sort;
            $data['ifopen']=$ifopen;
            $ShopcatObj->add($data);
            $ShopcatObj->updatechildcount ($sub);

           if($ShopcatObj){
               Buddha_Http_Head::redirect('添加成功','index.php?a=more&c=shopcat');
            }
        }

       $cid=Buddha_Http_Input::getParameter('cid')?(int)Buddha_Http_Input::getParameter('cid'):0;

       $shopoption=$ShopcatObj->getOption($cid);
       $this->smarty->assign('shopoption',$shopoption);

        $ImagecatalogObj= new Imagecatalog();
        $mobile_local=$ImagecatalogObj->select_cat();
        $this->smarty->assign('mobile_local',$mobile_local);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public  function edit(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $ShopcatObj=new Shopcat();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $sub=Buddha_Http_Input::getParameter('shopcat_id');
        $cat_name=Buddha_Http_Input::getParameter('cat_name');
        $ad_id=Buddha_Http_Input::getParameter('ad_id');
        $ad_name=Buddha_Http_Input::getParameter('ad_name');
        $sort=Buddha_Http_Input::getParameter('sort');
        $ifopen=Buddha_Http_Input::getParameter('ifopen')?1:0;
        $cat_path = $ShopcatObj->getClassPath($id,$sub);
        $oldgoodscatalog=$ShopcatObj->fetch($id);
        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['sub']=$sub;
            $data['cat_name']=trim($cat_name);
            $data['view_order']=$sort;
            $data['ad_id']=$ad_id;
            $data['ad_name']=$ad_name;
            $data['ifopen']=$ifopen;

            $ShopcatObj->edit($data,$id);
            $parentid = $sub;
            $cates ['sub']= $oldgoodscatalog['sub'];
            if ($cates ['sub'] != $parentid) {
                $ShopcatObj->updatepath ($id, $cat_path);
                $ShopcatObj-> updatechildcount ( $cates ['sub'] );
                $ShopcatObj->updatechildcount ( $parentid );
            }

            if($ShopcatObj){
                Buddha_Http_Head::redirect('编辑成功',"index.php?a=more&c=shopcat");
            }else{
                Buddha_Http_Head::redirect('编辑错误',"index.php?a=more&c=shopcat");
            }
        }

        $shop_cat= $ShopcatObj ->shop_cat();
        $this->smarty->assign('shop_cat',$shop_cat);


        $shopcat=$ShopcatObj->fetch($id);
        if(!count($shopcat)){
            Buddha_Http_Head::redirect('信息不存在',"index.php?a=more&c=shopcat");
        }

        $ImagecatalogObj= new Imagecatalog();
        $mobile_local=$ImagecatalogObj->select_cat();
        $this->smarty->assign('mobile_local',$mobile_local);
        $this->smarty->assign('shopcat',$shopcat);
        $this->smarty->assign('id',$id);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function del(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $ShopcatObj= new Shopcat();
        $id = Buddha_Http_Input::getParameter('id');
        $child=$ShopcatObj->fetch($id);

        if($child['child_count']>0){
            Buddha_Http_Head::redirect('删除失败,该分类下存在子类不能移除',"index.php?a=more&c=shopcat");
        }
        $ShopcatObj->del($id);
        $ShopcatObj-> updatechildcount ($child ['sub'] );
        if($ShopcatObj){
            Buddha_Http_Head::redirect('删除成功',"index.php?a=more&c=shopcat");
        }else{
            Buddha_Http_Head::redirect('删除失败',"index.php?a=more&c=shopcat");
        }
    }

    /*
 * @ajax_shopcat  判断当前有没已经添加过了广告位
 */
    function ajax_shopcat(){
        $id=(int)Buddha_Http_Input::getParameter('id');
        $ShopcatObj= new Shopcat();
        $data=$ShopcatObj->counct_shop_cat($id);
        Buddha_Http_Output::makeJson($data);
    }
}