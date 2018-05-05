<?php

/**
 * Class ImageController
 */
class ImageController extends Buddha_App_Action
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
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $ImagecatalogObj=new Imagecatalog();
        if(Buddha_Http_Input::getParameter('job')){
            $job = Buddha_Http_Input::getParameter ( 'job' );
            if (! Buddha_Http_Input::getParameter ( 'goodsID' )) {
                Buddha_Http_Head::redirect('没有选中',"index.php?a=more&c=image");
            }
            $ids = implode ( ',', Buddha_Http_Input::getParameter ( 'goodsID' ));
            switch($job){
                case 'sort';
                    $sorts = Buddha_Http_Input::getParameter ('view_order');
                    foreach ( $sorts as $k => $v ) {
                        $this->db->updateRecords( array ('view_order' => $v ), 'image','id='.$k);
                    }
                    Buddha_Http_Head::redirect('编辑成功',"index.php?a=more&c=image");
                    break;
                case 'del';
                    $this->db->delRecords('image',"id IN ($ids)");
                    Buddha_Http_Head::redirect('编辑成功',"index.php?a=more&c=image");

                    break;
                case 'open';
                    $this->db->updateRecords(array('buddhastatus' =>0),'image',"id IN ($ids)");
                    Buddha_Http_Head::redirect('编辑成功',"index.php?a=more&c=image");
                    break;
                case 'close';
                    $this->db->updateRecords(array('buddhastatus' =>1 ),'image',"id IN ($ids)");
                    Buddha_Http_Head::redirect('编辑成功',"index.php?a=more&c=image");
                    break;
            }
        }
        $cat_id=Buddha_Http_Input::getParameter('cat_id');

        $where = " isdel=0 ";
        if($cat_id){
            $where .= " and cat_id='{$cat_id}' ";
        }
        $rcount = $this->db->countRecords( $this->prefix.'image', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by id DESC ";
        $list = $this->db->getFiledValues ( '*',  $this->prefix.'image', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        foreach($list as $k=>$v){
            $cat_name=$ImagecatalogObj->getSingleFiledValues(array('name'),"id='{$v['cat_id']}' and isdel=0");
            $list[$k]['cat_name']=$cat_name['name'];
        }
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=image&', $pagesize );
        $imagem=$ImagecatalogObj->getFiledValues(array('id','name')," (sub!=0 and isdel=0) or (sub=0 and isdel=10)");
        $this->smarty->assign('imagem',$imagem);
        $this->smarty->assign('rcount',$rcount);
        $this->smarty->assign('pcount',$pcount);
        $this->smarty->assign('page',$page);
        $this->smarty->assign('strPages',$strPages);
        $this->smarty->assign('list',$list);
        $this->smarty->assign('cat_id',$cat_id);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
    public function add(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $ImageObj = new Image();
        $ImagecatalogObj = new Imagecatalog();
        $cat_id = ( int )Buddha_Http_Input::getParameter('cat_id');
        $name = Buddha_Http_Input::getParameter('name');
        $number = Buddha_Http_Input::getParameter('number');
        $view_order = Buddha_Http_Input::getParameter('view_order');
        $buddhastatus=Buddha_Http_Input::getParameter('buddhastatus')? 1:0;

        if($_POST){
            $num=$ImagecatalogObj->countRecords("sub='{$cat_id}' and isdel=0");
            if($num!=0){
                Buddha_Http_Head::redirect('分类未选择到末级类',"index.php?a=more&c=image");
            }
            $user_id =0;
            $level0=1;
            $level1=Buddha_Http_Input::getParameter('level1');
            $level2=Buddha_Http_Input::getParameter('level2');
            $level3=Buddha_Http_Input::getParameter('level3');
            $createtime = Buddha::$buddha_array['buddha_timestamp'];
            $createtimestr = Buddha::$buddha_array['buddha_timestr'];
            $link=Buddha_Http_Input::getParameter('link');
            $openmethod=Buddha_Http_Input::getParameter('openmethod');
            $width=Buddha_Http_Input::getParameter('width');
            $height=Buddha_Http_Input::getParameter('height');
            $Image= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/image/{$user_id}/",
                array ('gif', 'jpg', 'jpeg', 'png' ),  Buddha::$buddha_array['upload_maxsize'] )->run('Image')
                ->getOneReturnArray();

            if($Image){
                Buddha_Tool_File::thumbImageSameWidth( PATH_ROOT.$Image, $width, $height, 'L_' );
            }
            $sourcepic = str_replace("storage/image/{$user_id}/",'',$Image);


            $data=array();
            $data['cat_id']=$cat_id;
            $data['name']=trim($name);
            $data['number']=trim($number);

            $data['buddhastatus']=$buddhastatus;
            $data['view_order']=$view_order;

            $data['user_id']=$user_id;
            $data['level0']=$level0;
            $data['level1']=$level1;
            $data['level2']=$level2;
            $data['level3']=$level3;
            $data['createtime']=$createtime;
            $data['createtimestr']=$createtimestr;
            $data['link']=$link;
            $data['openmethod']=$openmethod;
            $data['width'] = $width;
            $data['height'] = $height;
            if($Image) {
                $data['large'] = "storage/image/{$user_id}/L_" . $sourcepic;
                $data['sourcepic'] = "storage/image/{$user_id}/" . $sourcepic;
            }
            $goods_id = $ImageObj->add($data);

            if($goods_id){

                Buddha_Http_Head::redirect('添加成功',"index.php?a=more&c=image");
            }
        }
        $optionList = $ImagecatalogObj->getOption();

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

        $ImageObj= new Image();
        $ImagecatalogObj= new Imagecatalog();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $article=$ImageObj->fetch($id);
        if(!count($article)){
            Buddha_Http_Head::redirect('信息不存在',"index.php?a=more&c=image&p={$page}");
        }


        $cat_id = ( int )Buddha_Http_Input::getParameter('cat_id');
        $name = Buddha_Http_Input::getParameter('name');
        $number = Buddha_Http_Input::getParameter('number');
        $view_order = Buddha_Http_Input::getParameter('view_order');
        $buddhastatus=Buddha_Http_Input::getParameter('buddhastatus')?1:0;
        $level0=1;
        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');
        $link=Buddha_Http_Input::getParameter('link');
        $openmethod=Buddha_Http_Input::getParameter('openmethod');
        $width=Buddha_Http_Input::getParameter('width');
        $height=Buddha_Http_Input::getParameter('height');
        if($_POST){
            $num=$ImagecatalogObj->countRecords("sub='{$cat_id}' and isdel=0");
            if($num!=0){
                Buddha_Http_Head::redirect('分类未选择到末级类',"index.php?a=more&c=image&p={$page}");
            }
            $Image= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/image/{$article['user_id']}/",
                array ('gif', 'jpg', 'jpeg', 'png' ),  Buddha::$buddha_array['upload_maxsize'] )->run('Image')
                ->getOneReturnArray();

            if($Image){
                Buddha_Tool_File::thumbImageSameWidth( PATH_ROOT.$Image, $width, $height, 'L_' );
            }
            $sourcepic = str_replace("storage/image/{$article['user_id']}/",'',$Image);


            $data=array();
            $data['cat_id']=$cat_id;
            $data['name']=trim($name);
            $data['number']=trim($number);
            $data['buddhastatus']=$buddhastatus;
            $data['view_order']=$view_order;
            $data['level0']=$level0;
            $data['level1']=$level1;
            $data['level2']=$level2;
            $data['level3']=$level3;
            $data['link']=$link;
            $data['openmethod']=$openmethod;
            $data['width'] = $width;
            $data['height'] = $height;


            if($Image) {
                //删除图片
                $ImageObj->deleteFIleOfPicture($id);
                $data['large'] = "storage/image/{$article['user_id']}/L_" . $sourcepic;
                $data['sourcepic'] = "storage/image/{$article['user_id']}/" . $sourcepic;
            }

            $ImageObj->edit($data,$id);
            if($ImageObj){
                Buddha_Http_Head::redirect('编辑成功',"index.php?a=more&c=image&p={$page}");
            }else{
                Buddha_Http_Head::redirect('编辑失败',"index.php?a=more&c=image&p={$page}");
            }
        }


        $this->smarty->assign('article',$article);
        $this->smarty->assign('page',$page);
        $ImagecatalogObj = new Imagecatalog();
        $optionList = $ImagecatalogObj ->getOption($article['cat_id']);
        $this->smarty->assign('optionList',$optionList);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function  del(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $ImageObj= new Image();
        $id=Buddha_Http_Input::getParameter('id');
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $this->smarty->assign('page',$page);
        $ImageObj->deleteFIleOfPicture($id);
        $result = $ImageObj->del($id);
        if($result){
            Buddha_Http_Head::redirect('删除成功',"index.php?a=more&c=image&p={$page}");
        }else{
            Buddha_Http_Head::redirect('删除失败',"index.php?a=more&c=image&p={$page}");
        }
    }

    public function imgsize(){
        $ImagecatalogObj=new Imagecatalog();
        $cat_id=Buddha_Http_Input::getParameter('cat_id');
        $num=$ImagecatalogObj->countRecords("sub='{$cat_id}' and isdel=0");
        if($num!=0){
            Buddha_Http_Output::makeValue(1);
        }
        $image=$ImagecatalogObj->getSingleFiledValues(array('width','height','imgmax'),"isdel=0 and id='{$cat_id}'");
        if($image){
        $result=array(
          'width'=> $image['width'] ,
          'height'=> $image['height'],
          'imgmax'=> $image['imgmax'],
          'status'=> '0',
          'message'=> 'ok',
        );
        }else{
            $result=array(
                'width'=> $image['width'] ,
                'height'=> $image['height'],
                'imgmax'=> $image['imgmax'],
                'status'=> '1',
                'message'=> 'err',
            );
        }
       Buddha_Http_Output::makeJson($result);
    }
}