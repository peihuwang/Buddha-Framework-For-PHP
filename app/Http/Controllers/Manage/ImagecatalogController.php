<?php

/**
 * Class ImagecatalogController
 */
class ImagecatalogController extends Buddha_App_Action{


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
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $ImagecatalogObj = new Imagecatalog();
        $getcatTable=$ImagecatalogObj->getcatlist();


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

        $ImagecatalogObj = new Imagecatalog();
        $sub = ( int )Buddha_Http_Input::getParameter('sub');
        $name = Buddha_Http_Input::getParameter('name');
        $view_order = Buddha_Http_Input::getParameter('view_order');
        $buddhastatus = Buddha_Http_Input::getParameter('buddhastatus')?0:1;

        $width = Buddha_Http_Input::getParameter('width');
        $height = Buddha_Http_Input::getParameter('height');
        $imgmax = Buddha_Http_Input::getParameter('imgmax');
        $identify = Buddha_Http_Input::getParameter('identify');
        $isopen = Buddha_Http_Input::getParameter('isopen');
        $cat_path = $ImagecatalogObj->getClassPath(0,$sub);

        if($_POST){
            $data=array();
            $data['sub']=$sub;
            $data ['cat_path'] = $cat_path;
            $data['name']=trim($name);
            $data['view_order']=$view_order;
            $data['buddhastatus']=$buddhastatus;
            $data['identify']=$identify;
            $data['width']=$width;
            $data['height']=$height;
            $data['imgmax']=$imgmax;
            $data['isopen']=$isopen;
            $ImagecatalogObj->add($data);
            $ImagecatalogObj->updatechildcount ($sub);
            if($ImagecatalogObj){
                Buddha_Http_Head::redirect('添加成功',"index.php?a=more&c=imagecatalog");
            }
        }

        $cid =Buddha_Http_Input::getParameter('cid')?(int)Buddha_Http_Input::getParameter('cid'):0;
        $optionList = $ImagecatalogObj ->getOption($cid);
        $this->smarty->assign('optionList',$optionList);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $ImagecatalogObj= new Imagecatalog();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $this->smarty->assign('page',$page);

        $sub = ( int )Buddha_Http_Input::getParameter('sub');
        $name = Buddha_Http_Input::getParameter('name');
        $view_order = Buddha_Http_Input::getParameter('view_order');
        $buddhastatus = Buddha_Http_Input::getParameter('buddhastatus')?0:1;
        $width = Buddha_Http_Input::getParameter('width');
        $height = Buddha_Http_Input::getParameter('height');
        $imgmax = Buddha_Http_Input::getParameter('imgmax');
        $identify = Buddha_Http_Input::getParameter('identify');
        $isopen = Buddha_Http_Input::getParameter('isopen');
        $cat_path = $ImagecatalogObj->getClassPath($id,$sub);
        $oldgoodscatalog=$ImagecatalogObj->fetch($id);
        if($_POST){

            $data=array();
            $data['sub']=$sub;
            $data ['cat_path'] = $cat_path;
            $data['name']=trim($name);
            $data['view_order']=$view_order;
            $data['buddhastatus']=$buddhastatus;
            $data['identify']=$identify;
            $data['width']=$width;
            $data['height']=$height;
            $data['imgmax']=$imgmax;
            $data['isopen']=$isopen;
            $ImagecatalogObj->edit($data,$id);

            $parentid = $sub;
            $cates ['sub']= $oldgoodscatalog['sub'];
            if ($cates ['sub'] != $parentid) {
                $ImagecatalogObj->updatepath ($id, $cat_path);
                $ImagecatalogObj-> updatechildcount ( $cates ['sub'] );
                $ImagecatalogObj->updatechildcount ( $parentid );
            }

            if($ImagecatalogObj){
                Buddha_Http_Head::redirect('编辑成功',"index.php?a=more&c=imagecatalog&p={$page}");
            }else{
                Buddha_Http_Head::redirect('编辑错误',"index.php?a=more&c=imagecatalog&p={$page}");
            }

        }


        $optionList = $ImagecatalogObj ->getOption($oldgoodscatalog['sub']);
        $this->smarty->assign('optionList',$optionList);

        $cat=$ImagecatalogObj->fetch($id);
        if(!count($cat)){
            Buddha_Http_Head::redirect('信息不存在',"index.php?a=more&c=imagecatalog&p={$page}");

        }
        $this->smarty->assign('cat',$cat);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function del(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $ImagecatalogObj= new Imagecatalog();
        $id = Buddha_Http_Input::getParameter('id');
        $child=$ImagecatalogObj->fetch($id);
        if($child['isdel']==10){
            Buddha_Http_Head::redirect('此分类为默认分类不能删除',"index.php?a=more&c=imagecatalog");
        }
        if($child['child_count']>0){
            Buddha_Http_Head::redirect('删除失败,该分类下存在子类不能移除',"index.php?a=more&c=imagecatalog");
        }
        $ImagecatalogObj->del($id);
        if($ImagecatalogObj){

            Buddha_Http_Head::redirect('删除成功',"index.php?a=more&c=imagecatalog");
        }else{
            Buddha_Http_Head::redirect('删除失败',"index.php?a=more&c=imagecatalog");
        }


    }

}