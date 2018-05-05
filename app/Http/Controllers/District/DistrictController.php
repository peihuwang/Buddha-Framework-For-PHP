<?php

/**
 * Class DistrictController
 */
class DistrictController extends Buddha_App_Action{

    protected $tablenamestr;
    protected $tablename;
    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='生活圈';
        $this->tablename='district';
    }


    /**
     * 商圈申请
     */
    public function petition()
    {
        $DistrictObj = new District();

        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());


        $District_Max = 1;//限制会添加商圈的数量个数
        $District_count = $DistrictObj->countRecords("user_id='{$uid}'");//统计会员拥有商圈的个数

        if($District_count>=$District_Max)
        {
            Buddha_Http_Head::redirectofmobile('你已经创建过生活圈了！','index.php?a=index&c='.$this->tablename,2);

        }


        $regionstr = Buddha_Http_Input::getParameter('regionstr');
        $name = Buddha_Http_Input::getParameter('name');
        $roadfullname = Buddha_Http_Input::getParameter('roadfullname');
        $details = Buddha_Http_Input::getParameter('details');
        $address = Buddha_Http_Input::getParameter('address');

        if(Buddha_Http_Input::isPost())
        {
            $data=array();
            $data['name'] = $name;
            $data['user_id'] = $uid;
            $data['roadfullname'] = $roadfullname;
//            $data['details'] = $details;
            $data['address'] = $address;

            if($regionstr)
            {
                $level = explode(",", $regionstr);
                $data['level0']=1;
                $data['level1']=$level[0];
                $data['level2']=$level[1];
                $data['level3']=$level[2];
            }

            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];

            $demand_id = $DistrictObj->add($data);
            $datas = array();
            if($demand_id)
            {
                $table_name = Buddha_Http_Input::getParameter('c');
                $MoreImage = Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/{$table_name}/{$demand_id}/", array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('Image')->getAllReturnArray();

                $MoregalleryObj = new Moregallery();

                if(is_array($MoreImage) and count($MoreImage)>0)
                {
                    $MoregalleryObj = new Moregallery();

                    $moregallery_id = $MoregalleryObj->pcaddimage('file',$MoreImage, $demand_id,$table_name,$uid);

                    $num = $DistrictObj->setFirstGalleryImgToSupply($demand_id,$table_name,'file');
                }

                if($details){//富文本编辑器图片处理
                    $saveData = $MoregalleryObj->base_upload($details,$demand_id,$this->tablename);
                    $saveData = str_replace(PATH_ROOT,'/', $saveData);
                    $deta_a['details'] = $saveData;
                    $DistrictObj->edit($deta_a,$demand_id);
                }

                $datas['isok']='true';
                $datas['data']=$this->tablenamestr.'添加成功';
                $datas['url']='index.php?a=index&c='.$this->tablename;

            }else{
                $datas['isok']='false';
                $datas['data']=$this->tablenamestr.'添加失败';
                $datas['url']='index.php?a=add&c='.$this->tablename;

            }
            Buddha_Http_Output::makeJson($datas);
        }
        $showlist['title'] = $this->tablenamestr;
        $showlist['buttonname'] = '申 请';
        $this->smarty->assign('list', $showlist);
        $this->smarty->assign('c', $this->tablename);
        $this->smarty->assign('a', 'petition');
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }


    /**
     * 商圈列表
     */
    public function petitionlist()
    {
        $a = 'petitionlist';

        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?(int)Buddha_Http_Input::getParameter('b_display'):2;
        $act = Buddha_Http_Input::getParameter('act');

        $view = Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):0;

        $DistrictObj = new District();
        $UserObj = new User();

        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') :1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];

        if($act=='list')
        {
            $keyword = Buddha_Http_Input::getParameter('keyword');
            $filed = array('id','name','is_sure','roadfullname','buddhastatus');
            $datas = $DistrictObj-> userDistrictList($filed,$uid,$keyword,$view,$page,$pagesize,$b_display,'id',array('small','medium'));

            if(Buddha_Atom_Array::isValidArray($datas['data']))
            {
                foreach ($datas['data'] as $k =>$v){
                    if($v['buddhastatus']==0)
                    {
                        $datas['data'][$k]['buddhastatusstr'] = '下 架';
                    }elseif($v['buddhastatus']==1)
                    {
                        $datas['data'][$k]['buddhastatusstr'] = '上 架';
                    }
                }
            }

            /**↓↓↓↓↓↓↓↓↓↓↓ 置顶参数 ↓↓↓↓↓↓↓↓↓↓↓**/
            $datas['top']['good_table'] = $this->tablename;
            $datas['top']['order_type'] = 'info.top';
            $datas['top']['final_amt']=0.2;
            /**↑↑↑↑↑↑↑↑↑↑ 置顶参数 ↑↑↑↑↑↑↑↑↑↑**/

            Buddha_Http_Output::makeJson($datas);
        }

        $showlist['title'] = $this->tablenamestr.$DistrictObj->getTitleAC($a,$this->tablename);
        $showlist['navlist'] = $DistrictObj->navReorganization($a,$this->tablename);


        $this->smarty->assign('list', $showlist);
        $this->smarty->assign('c', $this->tablename);
        $this->smarty->assign('view', $view);
        $this->smarty->assign('a', $a);
        $this->smarty->assign('return_a', 'index');
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }

    /**
     * 商圈中心
     */
    public function index()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $DistrictObj = new District();
        $UserObj = new User();

        $filarr = array(
            0=>array('filed'=>'xiangqing','view'=>1,),
            1=>array('filed'=>'wuye','view'=>2,),
            2=>array('filed'=>'louceng','view'=>3),
            3=>array('filed'=>'gonggao','view'=>4,),
            4=>array('filed'=>'huiyuan','view'=>5,),
            5=>array('filed'=>'shenqing','view'=>6,),
        );

        $showlist = array();
        $showlist['title'] = $this->tablenamestr.'中心';
        $showlist['navlist'] = $DistrictObj->indexmorenavlist($filarr,$uid);


        $this->smarty->assign('list', $showlist);
        $this->smarty->assign('returnurl','/'.$UserObj->user_role().'/index.php?a=index&c=' .$UserObj->user_role());

        $this->smarty->assign('c', $this->tablename);
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    /**
     *  省市区选择
     */
    public function arear()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $RegionObj = new Region();

        $fid = (int)Buddha_Http_Input::getParameter('fid')? (int)Buddha_Http_Input::getParameter('fid'):1;

        $Db_arear= $RegionObj->getChildlist($fid,$UserInfo['level1'],$UserInfo['level2'],$UserInfo['level3']);

        $datas = array();
        //print_r($Db_arear);

        if($Db_arear){
            $datas['isok']='true';
            $datas['datas']=$Db_arear;
        }else{
            $datas['isok']='false';
            $datas['datas']='';
        }
        Buddha_Http_Output::makeJson($datas);
    }


    /**
     * 详情(修改)
     */
    public function exhaustive()
    {
        $a = 'exhaustive';
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?(int)Buddha_Http_Input::getParameter('b_display'):2;
        $id = (int)Buddha_Http_Input::getParameter('id')? (int)Buddha_Http_Input::getParameter('id'):0;

        $DistrictObj = new District();
        $RegionObj = new Region();

        $where = "user_id='{$uid}' AND isdel=0 AND id='{$id}'";

        $showlist = array();
        $showlist['img'] =  array();
        $filed = array('id','name','roadfullname','details','address','level1','level2','level3');

        $District = $DistrictObj->getSingleFiledValues($filed,$where);
        $District['area'] = $RegionObj->getDetailOfAdrressByRegionIdStr($District['level1'],$District['level2'],$District['level3'],'>');


        $MoregalleryObj = new Moregallery();
        //产品相册
        $gimages = $MoregalleryObj->getEditGoodsImage($this->tablename,$District['id'],$uid);

        $showlist = $District;
        $showlist['img'] = $gimages;
        $showlist['title'] = $this->tablenamestr.'详情修改';
        $showlist['buttonname'] = '修 改';



        $id = Buddha_Http_Input::getParameter('id');
        $regionstr = Buddha_Http_Input::getParameter('regionstr');
        $name = Buddha_Http_Input::getParameter('name');
        $roadfullname = Buddha_Http_Input::getParameter('roadfullname');
        $details = Buddha_Http_Input::getParameter('details');
        $address = Buddha_Http_Input::getParameter('address');

        if(Buddha_Http_Input::isPost())
        {
            $data=array();
            $data['name'] = $name;
            $data['user_id'] = $uid;
            $data['roadfullname'] = $roadfullname;
            $data['address'] = $address;

//            if($regionstr)
//            {
//                $level = explode(",", $regionstr);
//                $data['level0']=1;
//                $data['level1']=$level[0];
//                $data['level2']=$level[1];
//                $data['level3']=$level[2];
//            }

            $demand_id = $DistrictObj->edit($data,$id);





            $MoreImage = Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/{$this->tablename}/{$id}/", array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('Image')->getAllReturnArray();

            $MoregalleryObj = new Moregallery();

            if(Buddha_Atom_Array::isValidArray($MoreImage))
            {

                $moregallery_id = $MoregalleryObj->pcaddimage('file',$MoreImage, $id,$this->tablename,$uid);

                if(count($moregallery_id)>0)
                {
                    $num = $DistrictObj->setFirstGalleryImgToSupply($id,$this->tablename,'file');

                    if($num==0){
                        $datas['err'] = 5;
                    }
                }else{
                    $datas['err'] = 6;
                }
            }

            if($details){//富文本编辑器图片处理
                $dirs = PATH_ROOT."storage/quill/{$this->tablename}/{$id}/";
                if(is_dir($dirs)){
                    if ($dh = opendir($dirs)){
                        while (($file = readdir($dh)) !== false){
                            //$filePath = $dirs.$file;
                            if(!strstr($details,$file) and $file != '.' and $file !='..'){
                                @unlink($dirs.$file);//删除修改后的图片
                                /*echo $file;
                                exit;*/
                            }
                        }
                    }
                }
                $saveData = $MoregalleryObj->base_upload($details,$id);//base64图片上传
                if($saveData){
                    $saveData = str_replace(PATH_ROOT,'/', $saveData);//替换
                    $detail['details'] = $saveData;
                }else{
                    $detail['details'] = $details;
                }
                $DistrictObj->edit($detail,$id);//更新数据
            }


            if(Buddha_Atom_String::isValidString($id))
            {
                $datas['isok']='true';
                $datas['data']=$showlist['title'].'成功';
                $datas['url']='index.php?a=index&c='.$this->tablename;
            }else{
                $datas['isok']='false';
                $datas['data']=$showlist['title'].'失败';
                $datas['url']='index.php?a=edit&c='.$this->tablename;
            }

            Buddha_Http_Output::makeJson($datas);

        }






        $this->smarty->assign('a', $a);
        $this->smarty->assign('list', $showlist);
        $this->smarty->assign('c', $this->tablename);

        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');

    }



    /**
     * 上下架
    */
    public function shelves()
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


    /**
     * 商圈删除
     */
    public function del()
    {
        $id=(int)Buddha_Http_Input::getParameter('id');
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        //相册删除并且信息删除
        $UsercommonObj=new Usercommon();
        $Db_Usercommon = $UsercommonObj->photoalbumDel('moregallery',$this->tablename,$id,$uid);

        $thumimg=array();

        if($Db_Usercommon){
            $thumimg['isok']='true';
            $thumimg['data']='删除成功';
        }else{
            $thumimg['isok']='false';
            $thumimg['data']='删除失败';
        }
        Buddha_Http_Output::makeJson($thumimg);
    }


    /**
     * 相册图片单张删除
     */
    public function delimage()
    {
        $MoregalleryObj = new Moregallery();
        $DemandObj=new Demand();
        $UsercommonObj=new Usercommon();
        $id=(int)Buddha_Http_Input::getParameter('id');
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $thumimg = array();
        if(!$id){
            $thumimg['isok']='false';
            $thumimg['data']='参数错误';
        }

        $Usercommon  = $UsercommonObj->photoalSinglebumDel('moregallery',$this->tablename,$id,$uid);
        if($Usercommon['is_ok']==1){
            $isok = 'true';
        }else{
            $isok = 'false';
        }

        $thumimg['isok'] = $isok;
        $thumimg['data'] = $Usercommon['is_msg'];

        Buddha_Http_Output::makeJson($thumimg);
    }




}