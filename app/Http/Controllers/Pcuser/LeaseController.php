<?php

/**
 * Class LeaseController
 */
class LeaseController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $LeasecatObj=new Leasecat();

        $where = " (isdel=0 or isdel=4) and user_id='{$uid}'";
        $rcount = $this->db->countRecords( $this->prefix.'lease', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by add_time DESC ";
        $fields=array('*');
        $list = $this->db->getFiledValues ($fields, $this->prefix.'lease', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=lease&', $pagesize );
    foreach($list as $k=>$v){
        $Leasecat=$LeasecatObj->goods_thumbgoods_thumb($v['leasecat_id']);
        if($Leasecat) {
            $cat_name = '';
            foreach ($Leasecat as $k1 => $v1) {
                $cat_name .= $v1['cat_name'] . ', ';
            }
        }
        $list[$k]['cat_name'] = Buddha_Atom_String::toDeleteTailCharacter($cat_name);
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
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $LeaseObj=new Lease();
        $LeasecatObj=new Leasecat();
        if($UserInfo['level0']=='0' and $UserInfo['level1']=='0' and $UserInfo['level2']=='0' and $UserInfo['level3']=='0'){
            Buddha_Http_Head::redirect('您的区域信息详细地址为完善,不能发布信息','index.php?a=info&c=user');
        }

        $goods_name=Buddha_Http_Input::getParameter('lease_name');
        $leasecat_id=Buddha_Http_Input::getParameter('leasecat_id');
        $rent=Buddha_Http_Input::getParameter('rent');
        $keywords=Buddha_Http_Input::getParameter('keywords');

        //商品促销
        $lease_start_time=Buddha_Http_Input::getParameter('lease_start_time');
        $lease_end_time=Buddha_Http_Input::getParameter('lease_end_time');

        //描述、图片
        $lease_brief = Buddha_Http_Input::getParameter('lease_brief');
        $lease_desc = Buddha_Http_Input::getParameter('content');


        if(Buddha_Http_Input::isPost()) {
            $data=array();
            $data['lease_name']=$goods_name;
            $data['user_id']=$uid;
            $data['leasecat_id']=$leasecat_id;
            $data['rent']=$rent;
            $data['keywords']=$keywords;
            $data['add_time']=Buddha::$buddha_array['buddha_timestamp'];
            $data['lease_brief']=$lease_brief;
            $data['lease_desc']=$lease_desc;
            $data['lease_start_time']=strtotime($lease_start_time);
            $data['lease_end_time']=strtotime($lease_end_time);

            $lease_id = $LeaseObj->add($data);
            if ($lease_id) {
                $Image = Buddha_Http_Upload::getInstance()->setUpload(PATH_ROOT . "storage/lease/{$lease_id}/",
                    array('gif', 'jpg', 'jpeg', 'png'), Buddha::$buddha_array['upload_maxsize'])->run('Image')
                    ->getOneReturnArray();
                if ($Image) {
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 320, 320, 'S_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 640, 640, 'M_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 800, 800, 'L_');
                }
                $sourcepic = str_replace("storage/lease/{$lease_id}/", '', $Image);
                if ($Image) {
                    $data['lease_thumb'] = "storage/lease/{$lease_id}/S_" . $sourcepic;
                    $data['lease_img'] = "storage/lease/{$lease_id}/M_" . $sourcepic;
                    $data['lease_large'] = "storage/lease/{$lease_id}/L_" . $sourcepic;
                    $data['sourcepic'] = "storage/lease/{$lease_id}/" . $sourcepic;
                }
                $LeaseObj->edit($data,$lease_id);
                Buddha_Http_Output::makeValue(1);
            }else{
                Buddha_Http_Output::makeValue(0);
            }
        }



        Buddha_Editor_Set::getInstance()->setEditor(
            array (array ('id' => 'content', 'content' =>'', 'width' => '100', 'height' => 500 )
            ));
        $getCateOption=$LeasecatObj->getOption();
        $this->smarty->assign('getCateOption', $getCateOption);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $LeaseObj=new Lease();
        $LeasecatObj=new Leasecat();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirect('参数错误','index.php?a=index&c=lease');
        }
        $lease=$LeaseObj->fetch($id);

        if(!$lease){
            Buddha_Http_Head::redirect('信息不存在','index.php?a=index&c=lease');
        }

        $goods_name=Buddha_Http_Input::getParameter('lease_name');
        $leasecat_id=Buddha_Http_Input::getParameter('leasecat_id');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $rent=Buddha_Http_Input::getParameter('rent');
        $keywords=Buddha_Http_Input::getParameter('keywords');

        //商品促销
        $lease_start_time=Buddha_Http_Input::getParameter('lease_start_time');
        $lease_end_time=Buddha_Http_Input::getParameter('lease_end_time');

        //描述、图片
        $lease_brief = Buddha_Http_Input::getParameter('lease_brief');
        $lease_desc = Buddha_Http_Input::getParameter('content');


        if(Buddha_Http_Input::isPost()) {
            $data=array();
            $data['lease_name']=$goods_name;
            $data['user_id']=$uid;
            $data['leasecat_id']=$leasecat_id;
            $data['shop_id']=$shop_id;
            $data['rent']=$rent;
            $data['keywords']=$keywords;
            $data['add_time']=Buddha::$buddha_array['buddha_timestamp'];
            $data['lease_brief']=$lease_brief;
            $data['lease_desc']=$lease_desc;
            $data['lease_start_time']=strtotime($lease_start_time);
            $data['lease_end_time']=strtotime($lease_end_time);

           $LeaseObj->edit($data,$id);
            if ($LeaseObj) {
                $Image = Buddha_Http_Upload::getInstance()->setUpload(PATH_ROOT . "storage/lease/{$id}/",
                    array('gif', 'jpg', 'jpeg', 'png'), Buddha::$buddha_array['upload_maxsize'])->run('Image')
                    ->getOneReturnArray();
                if ($Image) {
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 320, 320, 'S_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 640, 640, 'M_');
                    Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 800, 800, 'L_');
                }
                $sourcepic = str_replace("storage/lease/{$id}/", '', $Image);
                if ($Image) {
                    $data['lease_thumb'] = "storage/lease/{$id}/S_" . $sourcepic;
                    $data['lease_img'] = "storage/lease/{$id}/M_" . $sourcepic;
                    $data['lease_large'] = "storage/lease/{$id}/L_" . $sourcepic;
                    $data['sourcepic'] = "storage/lease/{$id}/" . $sourcepic;
                }
                $LeaseObj->deleteFIleOfPicture($id);
                $LeaseObj->edit($data,$id);
                Buddha_Http_Output::makeValue(1);
            }else{
                Buddha_Http_Output::makeValue(0);
            }
        }

        Buddha_Editor_Set::getInstance()->setEditor(
            array (array ('id' => 'content', 'content' =>$lease['lease_desc'], 'width' => '100', 'height' => 500 )
            ));
        $getCateOption=$LeasecatObj->getOption($lease['leasecat_id']);
        $this->smarty->assign('getCateOption', $getCateOption);
        $this->smarty->assign('lease', $lease);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function del(){
        $DemandObj=new Demand();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $DemandObj->del($id);
        $DemandObj->deleteFIleOfPicture($id);
        if($DemandObj){
            Buddha_Http_Head::redirect('删除成功','index.php?a=index&c=demand');
        }else{
            Buddha_Http_Head::redirect('删除失败','index.php?a=index&c=demand');
        }
    }

}