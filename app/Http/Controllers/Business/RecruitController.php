<?php

/**
 * Class RecruitController
 */
class RecruitController extends Buddha_App_Action
{
    protected $tablenamestr;
    protected $tablename;
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='招聘';
        $this->tablename='recruit';
    }

    public function index()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $RecruitcatObj=new Recruitcat();
        $act=Buddha_Http_Input::getParameter('act');
        $uid = Buddha_Http_Cookie::getCookie('uid');

        $view = Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):0;
        if($act=='list'){
            $keyword=Buddha_Http_Input::getParameter('keyword');
                $where = "isdel=0 and user_id='{$uid}'";
                if($keyword){
                    $where.=" and recruit_name like '%{$keyword}%' ";
                }
               // $rcount = $this->db->countRecords( $this->prefix.'recruit', $where);
                $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') :1;
                $pagesize = Buddha::$buddha_array['page']['pagesize'];
            if($view){
                switch($view){
                    case 2;
                        $where.=' and isdel=0 and is_sure=0';
                        break;
                    case 3;
                        $where.=" and isdel=0 and is_sure=1";
                        break;
                    case 4;
                        $where.=" and isdel=0 and is_sure=4 ";
                        break;
                }
            }

            $orderby = " order by id DESC ";
                $list = $this->db->getFiledValues ('*',  $this->prefix.'recruit', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ));
            $UserObj= new User();
            $sure = $UserObj->getSingleFiledValues(array('groupid'),"id='{$uid}' and isdel=0");
            $UsercommonObj =  new Usercommon();
            $CommonObj =  new Common();

            foreach ($list as $k=>$v)
            {
                $cat_id=$v['recruit_id'];
                $Db_Lease=$RecruitcatObj->goods_thumbgoods_thumb($cat_id);
                if($Db_Lease){
                    $cat_name='';
                    foreach($Db_Lease as $k1=>$v1){
                        $cat_name.=$v1['cat_name'].' > ';
                    }
                    $list[$k]['cat_name']=Buddha_Atom_String::toDeleteTailCharacter($cat_name);
                }
                $list[$k]['issureimg'] = $UsercommonObj->businessissurestr($v['is_sure']);

                $list[$k]['small'] =$CommonObj->handleImgSlashByImgurl($v['small']);


                if(Buddha_Atom_String::isValidString($v['pay'])){
                    $list[$k]['pay']='￥'.trim($v['pay']);
                }else{
                    $list[$k]['pay']='面议';
                }

                $list[$k]['small'] = Buddha_Atom_Dir::getformatDbStorageDir($v['small']);

                if(!Buddha_Atom_String::isValidString($v['small'])){
                    $ShopObj = new Shop();
                    $Db_shop = $ShopObj->getSingleFiledValues(array('small'),"id='{$v['shop_id']}'");
                    $list[$k]['small'] = $Db_shop['small'];
                }
            }
            $CommonObj = new Common();
            $Nws= $CommonObj->page_where($page,$list,$pagesize);
            $datas['info'] = $Nws;

            /**↓↓↓↓↓↓↓↓↓↓↓ 置顶参数 ↓↓↓↓↓↓↓↓↓↓↓**/
            $datas['top']['good_table']=$this->tablename;
            $datas['top']['order_type']='info.top';
            $datas['top']['final_amt']=0.2;
            /**↑↑↑↑↑↑↑↑↑↑ 置顶参数 ↑↑↑↑↑↑↑↑↑↑**/

            if(is_array($list) and count($list)>0)
            {
                $datas['isok']='true';
                 $datas['data']=$list;
            }else{
                    $datas['isok']='false';
                    $datas['data']='没有了';
            }
            Buddha_Http_Output::makeJson($datas);
        }
        $this->smarty->assign('view', $view);
        $this->smarty->assign('c', $this->tablename);
        $this->smarty->assign('title', $this->tablenamestr);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function add()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $RecruitObj=new Recruit();
        $ShopObj=new Shop();
        $num=$ShopObj->countRecords("isdel=0 and state=0 and is_sure=1 and user_id='{$uid}'");
        if($num==0){
            Buddha_Http_Head::redirectofmobile('您还没用创建店铺，或者店铺还未通过审核！','index.php?a=index&c=shop',2);
        }
			$title='招聘';
            $recruit_name = Buddha_Http_Input::getParameter('recruit_name');
            $recruit_id = Buddha_Http_Input::getParameter('recruit_id');
            $pay = Buddha_Http_Input::getParameter('pay');
            $education = Buddha_Http_Input::getParameter('education');
            $work = Buddha_Http_Input::getParameter('work');
            $recruit_start_time = Buddha_Http_Input::getParameter('recruit_start_time');
            $recruit_end_time = Buddha_Http_Input::getParameter('recruit_end_time');
            $shop_id = Buddha_Http_Input::getParameter('shop_id');
            $treatment = Buddha_Http_Input::getParameter('treatment');
            $number = Buddha_Http_Input::getParameter('number');
            $contacts = Buddha_Http_Input::getParameter('contacts');
            $tel = Buddha_Http_Input::getParameter('tel');
            $recruit_desc = Buddha_Http_Input::getParameter('recruit_desc');
            $recruit_brief = Buddha_Http_Input::getParameter('recruit_brief');

            if(Buddha_Http_Input::isPost()){
                $Db_level = $ShopObj->getSingleFiledValues(array('level0', 'level1', 'level2', 'level3'), "user_id='{$uid}' and id='{$shop_id}' and isdel=0");
                $data = array();
                $data['recruit_name'] = $recruit_name;
                $data['user_id'] = $uid;
                $data['recruit_id'] = $recruit_id;
                $data['shop_id'] = $shop_id;
                $data['pay'] = $pay;
                $data['education'] = $education;
                $data['work'] = $work;
                $data['treatment'] = $treatment;
                $data['number'] = $number;
                $data['contacts'] = $contacts;
                $data['tel'] = $tel;
                $data['level0'] = $Db_level['level0'];
                $data['level1'] = $Db_level['level1'];
                $data['level2'] = $Db_level['level2'];
                $data['level3'] = $Db_level['level3'];
                $data['recruit_start_time'] = strtotime($recruit_start_time);
                $data['recruit_end_time'] = strtotime($recruit_end_time);
//                $data['recruit_desc'] = $recruit_desc;
                $data['recruit_brief'] = $recruit_brief;
                $data['add_time'] = Buddha::$buddha_array['buddha_timestamp'];

                $Recruit_id = $RecruitObj->add($data);
                if($Recruit_id){
                    $GalleryObj = new Gallery();
                    $table_name = Buddha_Http_Input::getParameter('c');
                    $MoreImage= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/{$table_name}/{$Recruit_id}/", array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('Image')->getAllReturnArray();

                    $MoregalleryObj = new Moregallery();
                    if(is_array($MoreImage) and count($MoreImage)>0){
                        $moregallery_id = $MoregalleryObj->pcaddimage('file',$MoreImage, $Recruit_id,$table_name,$uid);
                        $num = $RecruitObj->setFirstGalleryImgToSupply($Recruit_id,$table_name,'file');
                    }
                    if($recruit_desc){//富文本编辑器图片处理

                        $saveData = $MoregalleryObj->base_upload($recruit_desc,$Recruit_id,$this->tablename);
                        $saveData = str_replace(PATH_ROOT,'/', $saveData);
                        $details['recruit_desc'] = $saveData;
                        $RecruitObj->edit($details,$Recruit_id);
                    }
                }
                $datas = array();
                if ($Recruit_id) {
                    $datas['isok'] = 'true';
                    $datas['data'] = '添加成功';
                    $datas['url'] = 'index.php?a=index&c=recruit';
                } else {
                    $datas['isok'] = 'false';
                    $datas['data'] = '添加失败';
                    $datas['url'] = 'index.php?a=add&c=recruit';
                }
                Buddha_Http_Output::makeJson($datas);
        }

        $gettableOption=$RecruitObj->Recruitment_Qualifications();
        $gettableOption1=$RecruitObj->work_experience();
        $getshoplistOption=$ShopObj->getShoplistOption($uid,0);
        $this->smarty->assign('gettableOption', $gettableOption);
        $this->smarty->assign('gettableOption1', $gettableOption1);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('title', $title);
        $this->smarty->assign('UserInfo', $UserInfo);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function edit()
    {

        $MoregalleryObj = new Moregallery();
        $RecruitObj=new Recruit();
        $RecruitcatObj=new Recruitcat();
        $ShopObj=new Shop();
        $OrderObj=new Order();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
          Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=shop',2);
        }
        $recruit=$RecruitObj->getSingleFiledValues('',"id='{$id}' and user_id='{$uid}'");
        if(!$recruit){
            Buddha_Http_Head::redirectofmobile('您没有权限查看或商品已删除！','index.php?a=index&c=shop',2);
        }
		$title='招聘';
        $recruit_name=Buddha_Http_Input::getParameter('recruit_name');
        $recruit_id=Buddha_Http_Input::getParameter('recruit_id');
        $pay=Buddha_Http_Input::getParameter('pay');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $treatment=Buddha_Http_Input::getParameter('treatment');
        $number=Buddha_Http_Input::getParameter('number');
        $education=Buddha_Http_Input::getParameter('education');
        $contacts=Buddha_Http_Input::getParameter('contacts');
        $tel=Buddha_Http_Input::getParameter('tel');
        $work=Buddha_Http_Input::getParameter('work');
        $recruit_start_time=Buddha_Http_Input::getParameter('recruit_start_time');
        $recruit_end_time=Buddha_Http_Input::getParameter('recruit_end_time');
        $recruit_desc = Buddha_Http_Input::getParameter('recruit_desc');
        $recruit_brief = Buddha_Http_Input::getParameter('recruit_brief');

        if(Buddha_Http_Input::isPost()){
                $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$uid}' and id='{$shop_id}' and isdel=0");
                $data=array();
                $data['recruit_name']=$recruit_name;
                $data['user_id']=$uid;
                $data['recruit_id']=$recruit_id;
                $data['shop_id']=$shop_id;
                $data['pay']=$pay;
                $data['education']=$education;
                $data['work']=$work;
                $data['treatment'] = $treatment;
                $data['number']=$number;
                $data['contacts']=$contacts;
                $data['tel']=$tel;
                $data['recruit_start_time']=strtotime($recruit_start_time);
                $data['recruit_end_time']=strtotime($recruit_end_time);
                $data['recruit_desc']=$recruit_desc;
                $data['recruit_brief']=$recruit_brief;
                $data['level0']=$Db_level['level0'];
                $data['level1']=$Db_level['level1'];
                $data['level2']=$Db_level['level2'];
                $data['level3']=$Db_level['level3'];
                $data['add_time']=Buddha::$buddha_array['buddha_timestamp'];

                $Recruitnum = $RecruitObj->edit($data,$id);

            if($id){

                $table_name = Buddha_Http_Input::getParameter('c');
                $MoreImage= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/{$table_name}/{$id}/", array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('Image')->getAllReturnArray();

                $MoregalleryObj = new Moregallery();

                if(is_array($MoreImage) and count($MoreImage)>0){
                    $moregallery_id = $MoregalleryObj->pcaddimage('file',$MoreImage, $id,$this->tablename,$uid);

                    if(count($moregallery_id)>0){
                        $num = $RecruitObj->setFirstGalleryImgToSupply($id,$this->tablename,'file');

                        if($num==0){
                            $datas['err'] = 5;
                        }
                    }else{
                        $datas['err'] = 6;
                    }
                }
                if($recruit_desc){//富文本编辑器图片处理
                    $dirs = PATH_ROOT."storage/quill/{$this->tablename}/{$id}/";
                    if(is_dir($dirs)){
                        if ($dh = opendir($dirs)){
                            while (($file = readdir($dh)) !== false){
                                //$filePath = $dirs.$file;
                                if(!strstr($recruit_desc,$file) and $file != '.' and $file !='..'){
                                    @unlink($dirs.$file);//删除修改后的图片
                                    /*echo $file;
                                    exit;*/
                                }
                            }
                        }
                    }
                    $saveData = $MoregalleryObj->base_upload($recruit_desc,$id);//base64图片上传
                    if($saveData){
                        $saveData = str_replace(PATH_ROOT,'/', $saveData);//替换
                        $details['recruit_desc'] = $saveData;
                    }else{
                        $details['recruit_desc'] = $recruit_desc;
                    }
                    $RecruitObj->edit($details,$id);//更新数据
                }

            }

                $datas = array();
                if($Recruitnum){
                    $datas['isok']='true';
                    $datas['data']='编辑成功';
                    $datas['url']='index.php?a=index&c=recruit';
                }else{
                    $datas['isok']='false';
                    $datas['data']='编辑失败';
                    $datas['url']='index.php?a=edit&c=recruit';
                }
                Buddha_Http_Output::makeJson($datas);
            }


        $getshoplistOption=$ShopObj->getShoplistOption($recruit['user_id'],$recruit['shop_id']);
        $Recruit=$RecruitcatObj->goods_thumbgoods_thumb($recruit['recruit_id']);
        if($Recruit){
            $cat_name='';
            foreach ($Recruit as $k=>$v){
            $cat_name.=$v['cat_name'].' > ';
            }
            $recruit['cat_name']=Buddha_Atom_String::toDeleteTailCharacter($cat_name);
        }

        $gettableOption=$RecruitObj->Recruitment_Qualifications($recruit['education']);
        $gettableOption1=$RecruitObj->work_experience($recruit['work']);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('gettableOption', $gettableOption);
        $this->smarty->assign('gettableOption1', $gettableOption1);
        $this->smarty->assign('recruit', $recruit);

        $Top=$OrderObj->getFiledValues(array('final_amt','createtime'),"isdel=0 and good_id='{$id}' and order_type='info.top' and pay_status=1 and user_id='{$uid}'");
        if(count($Top)>0){
            foreach ($Top as $k=>$v){
                $Top[$k]['name']=$recruit['recruit_name'];
            }
        }
        $this->smarty->assign('Top', $Top);

        //产品相册
        $gimages = $MoregalleryObj->getEditGoodsImage($this->tablename,$id,$uid);


        $this->smarty->assign('gimages', $gimages);

		$infotop=array('id'=>$id,'good_table'=>'recruit','order_type'=>'info.top','final_amt'=>'0.2');

        $this->smarty->assign('infotop', $infotop);
		$this->smarty->assign('title', $title);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function del()
    {
        $id=(int)Buddha_Http_Input::getParameter('id');
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        //相册删除并且信息删除
        $UsercommonObj=new Usercommon();
        $Db_Usercommon = $UsercommonObj->photoalbumDel('moregallery',$this->tablename,$id,$uid);

        $thumimg = array();

        if($Db_Usercommon){
            $thumimg['isok']='true';
            $thumimg['data']='删除成功';
        }else{
            $thumimg['isok']='false';
            $thumimg['data']='删除失败';
        }
        Buddha_Http_Output::makeJson($thumimg);
    }



    public function recruitcat(){
       $RecruitcatObj=new Recruitcat();
        $fid = Buddha_Http_Input::getParameter('fid');
        $Db_recruit= $RecruitcatObj->getRecruitcatlist($fid);
        $datas = array();
        if($Db_recruit){
            $datas['isok']='true';
            $datas['data']=$Db_recruit;
        }else{
            $datas['isok']='false';
            $datas['data']='';
        }
        Buddha_Http_Output::makeJson($datas);
    }



    public function fail(){
        $RecruitObj=new Recruit();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=(int)Buddha_Http_Input::getParameter('id');
        $Db_shop=$RecruitObj->getSingleFiledValues(array('remarks'),"isdel=0 and user_id='{$uid}' and id='{$id}'");
        $failinfo=array();
        if($Db_shop){
            $failinfo['isok']=0;
            $failinfo['remarks']=$Db_shop['remarks'];
        }else{
            $failinfo['isok']=1;
            $failinfo['data']='错误';
            $failinfo['remarks']='';
        }
        Buddha_Http_Output::makeJson($failinfo);
    }


    //相册图片删除
    public  function delimage()
    {
        $GalleryObj=new Gallery();
        $RecruitObj=new Recruit();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $thumimg=array();
        if(!$id){
            $thumimg['isok']='false';
            $thumimg['data']='参数错误';
        }
        $gimages = $GalleryObj->fetch($id);
        if ($gimages and $gimages['isdefault']==0)
        {
            $GalleryObj->del($id);
            @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
            @unlink(PATH_ROOT . $gimages ['goods_img']);
            @unlink(PATH_ROOT . $gimages ['goods_large']);
            @unlink(PATH_ROOT . $gimages ['sourcepic'] );
            $thumimg['isok']='true';
            $thumimg['data']='图片删除成功';
        }else{
            $GalleryObj->del($id);
            @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
            @unlink(PATH_ROOT . $gimages ['goods_img']);
            @unlink(PATH_ROOT . $gimages ['goods_large']);
            @unlink(PATH_ROOT . $gimages ['sourcepic'] );
            $RecruitObj->setFirstGalleryImgToSupply($gimages['goods_id']);
            $thumimg['isok']='true';
            $thumimg['data']='图片删除成功';
        }

        Buddha_Http_Output::makeJson($thumimg);
    }



//上下架
    public  function shelves()
    {
        $id = (int)Buddha_Http_Input::getParameter('id');
        $thumimg = array();
        $UsercommonObj = new Usercommon();
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $Db_Usercommon = $UsercommonObj->businessshelf($this->tablename,$id,$uid);
        if($Db_Usercommon['is_ok']==1)
        {
            $isok = 'true';
        }else{
            $isok = 'false';
        }

        $thumimg['id'] = $id;
        $thumimg['isok'] = $isok;
        $thumimg['data'] = $Db_Usercommon['is_msg'];
        $thumimg['buttonname'] = $Db_Usercommon['buttonname'];

        Buddha_Http_Output::makeJson($thumimg);
    }



}