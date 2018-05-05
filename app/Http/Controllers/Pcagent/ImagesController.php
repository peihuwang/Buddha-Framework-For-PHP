<?php

/**
 * Class ImagesController
 */
class ImagesController extends Buddha_App_Action{

    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
    }

    public function index(){
        $ImageObj = new Image();
        $ImagecatalogObj = new Imagecatalog();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $where = " isdel=0 and user_id='{$uid}'";
        $rcount = $this->db->countRecords( $this->prefix.'image', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by id DESC ";
        $list = $this->db->getFiledValues ( '*',  $this->prefix.'image', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=images&', $pagesize );
         if(count($list)>0){
             foreach($list as $k=>$v){
                 $cat_nae=$ImagecatalogObj->getSingleFiledValues(array('name'),"id='{$v['cat_id']}'");
                 $list[$k]['catname'] =$cat_nae['name'];
             }
         }
        $this->smarty->assign('rcount',$rcount);
        $this->smarty->assign('pcount',$pcount);
        $this->smarty->assign('page',$page);
        $this->smarty->assign('strPages',$strPages);
        $this->smarty->assign('list',$list);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function add(){
        $ImagecatalogObj = new Imagecatalog();
        $ImageObj = new Image();
        $ShopObj = new Shop();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $cat_id=Buddha_Http_Input::getParameter('cat_id');
 //////////↓↓↓↓↓↓↓↓///////////
        $ShopObj = new Shop();
        $shop_number=Buddha_Http_Input::getParameter('shop_number');
        $shop_name=Buddha_Http_Input::getParameter('shop_name');
        $number=Buddha_Http_Input::getParameter('number');
        $promote_start_date=Buddha_Http_Input::getParameter('promote_start_date');
        $promote_end_date=Buddha_Http_Input::getParameter('promote_end_date');
        $buddhastatus=Buddha_Http_Input::getParameter('budd');

  ////////↑↑↑↑↑↑↑↑↑////////////
        $width=Buddha_Http_Input::getParameter('width');
        $height=Buddha_Http_Input::getParameter('height');

        if(Buddha_Http_Input::isPost()){
/////////////////↓↓↓↓↓↓↓////////////////////////////
            $num=$ShopObj->countRecords("  level3='{$UserInfo['level3']}' and number='{$cat_id}' and isdel=0");
            if($num=0){
                Buddha_Http_Head::redirect('店铺不存在！','index.php?a=add&c=images');
            }
            $shop_name=$ShopObj->getSingleFiledValues(array('id','name'),"number='{$shop_number}' and isdel=0");
//////////////////↑↑↑↑↑↑↑↑↑///////////////////////////
            $imgmax=$ImagecatalogObj->getSingleFiledValues(array('imgmax'),"id='{$cat_id}' and isdel=0");
            if($imgmax){
               $num= $ImageObj->countRecords("isdel=0 and cat_id='{$cat_id}' and user_id='{$uid}'");
                if($num>=$imgmax['imgmax']){
                    Buddha_Http_Output::makeValue(2);
                }
            }
            $data=array();
            $data['cat_id']=$cat_id;
            $data['user_id']=$uid;
            $data['level0']=1;
            $data['level1']=$UserInfo['level1'];
            $data['level2']=$UserInfo['level2'];
            $data['level3']=$UserInfo['level3'];
/////////////////↓↓↓↓↓↓↓////////////////////////////
//            $data['name']=$name;
            $data['name']=$shop_name['name'];//店铺名称
            $data['shop_id']=$shop_name['id'];//店铺ID
            $data['shop_number']=$shop_number;//店铺编号
//            $data['number']=$number;//显示顺序编号
            $data['view_order']=$number;//显示顺序编号
            $data['promote_start_date']=strtotime($promote_start_date);//广告显示开始时间
            $data['promote_end_date']=strtotime($promote_end_date);//广告显示结束时间
            $data['buddhastatus']=$buddhastatus;//广告显示结束时间
///////////////////↑↑↑↑↑↑↑↑↑//////////////////////
            $data['width']=$width;
            $data['height']=$height;
            $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
            $data['buddhastatus']=1;


            $Image= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/image/{$uid}/",
                array ('gif', 'jpg', 'jpeg', 'png' ),  Buddha::$buddha_array['upload_maxsize'] )->run('Image')
                ->getOneReturnArray();
            if($Image){
                Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, $width, $height, 'L_');
                $sourcepic = str_replace("storage/image/{$uid}/",'',$Image);

                $data['large'] = "storage/image/{$uid}/L_" . $sourcepic;
                $data['sourcepic'] = "storage/image/{$uid}/" . $sourcepic;
                }
           $img_id= $ImageObj->add($data);
            if($img_id){
                Buddha_Http_Output::makeValue(1);
            }else{
                Buddha_Http_Output::makeValue(0);
            }
        }

        $optionList = $ImagecatalogObj->getOptionuser();
        $this->smarty->assign('optionList',$optionList);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit(){
        $ImagecatalogObj = new Imagecatalog();
        $ImageObj = new Image();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirect('参数错误','index.php?a=edit&c=images');
        }
        $imga=$ImageObj->fetch($id);
        if(!$imga){
            Buddha_Http_Head::redirect('信息不存在','index.php?a=edit&c=images');
        }

        $cat_id=Buddha_Http_Input::getParameter('cat_id');
        $name=Buddha_Http_Input::getParameter('name');
        $width=Buddha_Http_Input::getParameter('width');
        $height=Buddha_Http_Input::getParameter('height');
//////////↓↓↓↓↓↓↓↓///////////
        $ShopObj = new Shop();
        $shop_number=Buddha_Http_Input::getParameter('shop_number');
        $shop_name=Buddha_Http_Input::getParameter('shop_name');
        $number=Buddha_Http_Input::getParameter('number');
        $promote_start_date=Buddha_Http_Input::getParameter('promote_start_date');
        $promote_end_date=Buddha_Http_Input::getParameter('promote_end_date');
        $buddhastatus=Buddha_Http_Input::getParameter('budd');

////////↑↑↑↑↑↑↑↑↑////////////
        if(Buddha_Http_Input::isPost()){
/////////////////↓↓↓↓↓↓↓////////////////////////////
            $num=$ShopObj->countRecords("  level3='{$UserInfo['level3']}' and number='{$cat_id}' and isdel=0");
            if($num=0){
                Buddha_Http_Head::redirect('店铺不存在！','index.php?a=add&c=images');
            }
            $shop_name=$ShopObj->getSingleFiledValues(array('id','name'),"number='{$shop_number}' and isdel=0");
//////////////////↑↑↑↑↑↑↑↑↑///////////////////////////
            $data=array();
            $data['cat_id']=$cat_id;
            $data['user_id']=$uid;
            $data['level0']=1;
            $data['level1']=$UserInfo['level1'];
            $data['level2']=$UserInfo['level2'];
            $data['level3']=$UserInfo['level3'];
            $data['name']=$name;
            $data['width']=$width;
            $data['height']=$height;
            $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];
            $data['buddhastatus']=1;
/////////////////↓↓↓↓↓↓↓////////////////////////////
//            $data['name']=$name;
            $data['name']=$shop_name['name'];//店铺名称
            $data['shop_id']=$shop_name['id'];//店铺ID
            $data['shop_number']=$shop_number;//店铺编号
//            $data['number']=$number;//显示顺序编号
            $data['view_order']=$number;//显示顺序编号
            $data['promote_start_date']=strtotime($promote_start_date);//广告显示开始时间
            $data['promote_end_date']=strtotime($promote_end_date);//广告显示结束时间
            $data['buddhastatus']=$buddhastatus;//广告显示结束时间

///////////////////↑↑↑↑↑↑↑↑↑//////////////////////
            $Image= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/image/{$uid}/",
                array ('gif', 'jpg', 'jpeg', 'png' ),  Buddha::$buddha_array['upload_maxsize'] )->run('Image')
                ->getOneReturnArray();
            if($Image){
                Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, $width, $height, 'L_');
            }
                $sourcepic = str_replace("storage/image/{$uid}/",'',$Image);
            if($Image){
                $ImageObj->deleteFIleOfPicture($id);
               $data['large'] = "storage/image/{$uid}/L_" . $sourcepic;
                $data['sourcepic'] = "storage/image/{$uid}/" . $sourcepic;
            }
             $ImageObj->edit($data,$id);
            if($ImageObj){
                Buddha_Http_Output::makeValue(1);
            }else{
                Buddha_Http_Output::makeValue(0);
            }
        }

        $optionList = $ImagecatalogObj->getOptionuser($imga['cat_id']);

        $this->smarty->assign('optionList',$optionList);
        $this->smarty->assign('imga',$imga);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function imgsize(){
        $ImagecatalogObj=new Imagecatalog();
        $ImageObj=new Image();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $cat_id=Buddha_Http_Input::getParameter('cat_id');
        $num=$ImagecatalogObj->countRecords("sub='{$cat_id}' and isdel=0");
        if($num!=0){
            Buddha_Http_Output::makeValue(1);
        }
        $image=$ImagecatalogObj->getSingleFiledValues(array('width','height','imgmax'),"isdel=0 and id='{$cat_id}'");
        $num= $ImageObj->countRecords("isdel=0 and cat_id='{$cat_id}' and user_id='{$uid}'");
        if($image){
            $result=array(
                'width'=> $image['width'] ,
                'height'=> $image['height'],
                'imgmax'=> $image['imgmax'],
                'num'=> $num,
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
    public function is_shop(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $cat_id=Buddha_Http_Input::getParameter('cat_id');
        $ShopObj=new Shop();
        $ImageObj=new Image();
        $where=" level3='{$UserInfo['level3']}'";
        $newtime= time();$num='';
        $num=$ImageObj->countRecords($where." and shop_number='{$cat_id}' and isdel=0 and promote_start_date <{$newtime} and {$newtime}<promote_end_date");//查询该店铺是否已经添加过广告,并且广告还没有过期
//        $shop=$ShopObj->getSingleFiledValues(array('id','name'),$where ." and number='{$cat_id}' and isdel=0");//查询店铺是否存在
        $result=array();
        if($num>0){//表示已经存在
            $result=array(
                'status'=> '2',//表示已经存在！
                'shop_name'=>'',
                'message'=> 'err',
            );
        }else{
            $shop=$ShopObj->getSingleFiledValues(array('id','name','is_sure'),$where ." and number='{$cat_id}' and isdel=0");//查询店铺是否存在
            if($shop){
                if($shop['is_sure']==1){
                    $result=array(
                        'status'=> '1',
                        'shop_name'=>$shop['name'],
                        'message'=> 'ok',
                    );
                }else if($shop['is_sure']==0){
                    $result=array(
                        'status'=> '3',//店铺未审核！
                        'shop_name'=>$shop['name'],
                        'message'=> 'ok',
                    );
                }else if($shop['is_sure']==4){
                    $result=array(
                        'status'=> '4',//店铺未通过审核！
                        'shop_name'=>$shop['name'],
                        'message'=> 'ok',
                    );
                }

            }else{
                $result=array(
                    'status'=> '0',
                    'shop_name'=>'',
                    'message'=> 'err',
                );
            }
        }
        Buddha_Http_Output::makeJson($result);
    }
}