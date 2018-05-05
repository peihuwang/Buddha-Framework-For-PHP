<?php

/**
 * Class PropertyController
 */
class PropertyController extends Buddha_App_Action{

    protected $tablenamestr;
    protected $tablename;
    protected $type;
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr = '物业名称';
        $this->tablename = 'property';
        $this->needtypeId = 2;//访问当前用户页面需要的用户类型id
    }


    /**
     * 列表
     */
    public function index()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $act=Buddha_Http_Input::getParameter('act');

        $UsercommonObj = new Usercommon();
        $Usercommon = $UsercommonObj->isUserJurisdiction($uid,$this->needtypeId);
        if(!$Usercommon['is_ok']!=1)
        {
            Buddha_Http_Head::redirectofmobile('参数错误',"index.php?a=index&c={$this->tablename}",2);
        }

        $view = Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):0;

        if($act=='list')
        {
            $keyword=Buddha_Http_Input::getParameter('keyword');

            $where =" level3='{$UserInfo['level3']}' ";

            if($keyword)
            {
                $where.=" and name like '%{$keyword}%'";
            }

            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') :1;
            $pagesize = Buddha::$buddha_array['page']['pagesize'];


            if($view){
                switch($view)
                {
                    case 1;
                        $where.=' and buddhastatus=1';
                        break;
                    case 2;
                        $where.=" and buddhastatus=0";
                        break;
                }
            }

            $b_display =(int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

            $Filedarr = array('id','name','roadfullname','buddhastatus');

            if($b_display==2)
            {
                array_push($Filedarr,'small as img');

            }elseif ($b_display=1)
            {
                array_push($Filedarr,'medium as img');
            }
            $orderby = ' order by createtime desc';
            $list = $this->db->getFiledValues($Filedarr, $this->prefix.$this->tablename, $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

            $total =  $this->db->countRecords($this->tablename,$where);

            $UsercommonObj = new Usercommon();
            $CommonObj = new Common();

            foreach ($list as $k=>$v)
            {
                $list[$k]['name'] = $CommonObj->isvar($v['name']);

                $list[$k]['roadfullname'] = $CommonObj->isvar($v['roadfullname']);
                $list[$k]['img'] = $CommonObj->handleImgSlashByImgurl($v['img']);
                $list[$k]['buddhastatusstr'] = $UsercommonObj->agentsPropertystr($v['buddhastatus']);
            }

            $CommonObj = new Common();
            $Nws = $CommonObj->page_where($page,$list,$pagesize);
            $datas['info']=$Nws;

            if (is_array($list) and count($list) > 0)
            {
                $datas['isok'] = 'true';
                $datas['list'] = $list;
                $datas['total'] = $total;
            } else {
                $datas['isok'] = 'false';
                $datas['list'] = '没有了';
                $datas['total'] = $total;

            }
            Buddha_Http_Output::makeJson($datas);
        }

        $this->smarty->assign('view', $view);
        $this->smarty->assign('c', $this->tablename);
        $this->smarty->assign('title', $this->tablenamestr);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    /**
     * 添加
     */
    public  function  add()
    {

        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $PropertyObj = new Property();
        $GalleryObj = new Gallery();
        $property = Buddha_Http_Input::getParameter('property')?Buddha_Http_Input::getParameter('property'):'';
        $roadfullname = Buddha_Http_Input::getParameter('roadfullname')?Buddha_Http_Input::getParameter('roadfullname'):'';


        if(Buddha_Http_Input::isPost())
        {
            $datas['level0'] = $UserInfo['level0'];
            $datas['level1'] = $UserInfo['level1'];
            $datas['level2'] = $UserInfo['level2'];
            $datas['level3'] = $UserInfo['level3'];

            $datas['name'] = $property;

            $datas['roadfullname'] = $roadfullname;

            $datas['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $datas['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];

            if($PropertyObj->isExistence($UserInfo['level1'],$UserInfo['level2'],$UserInfo['level3'],$property,$roadfullname))
            {
                $data['isok'] = 'false';
                $data['data'] = '该物业名称已经存在了，请不要重复添加';
                $data['url'] = 'index.php?a=index&c='.$this->tablename;

            }else{

                $property_id = $PropertyObj->add($datas);

                if($property_id)
                {
                    $Image = Buddha_Http_Upload::getInstance()->setUpload(PATH_ROOT . "storage/property/{$property_id}/",
                        array('gif', 'jpg', 'jpeg', 'png'), Buddha::$buddha_array['upload_maxsize'])->run('Image')
                        ->getOneReturnArray();

                    if ($Image)
                    {
                        //解决图像的旋转
                        $GalleryObj->resolveImageForRotate(PATH_ROOT.$Image,NULL);
                        Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 320, 320, 'S_');
                        Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 640, 640, 'M_');
                        Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 1200, 640, 'L_');
                    }


                    $sourcepic = str_replace("storage/{$this->tablename}/{$property_id}/", '', $Image);

//                    $sourcepicarr = explode('/',$sourcepic);
//
//                    $sourcepic = $sourcepicarr['3'];

                    $data = array();

                    if ($Image)
                    {
                        $data['small'] = "storage/{$this->tablename}/{$property_id}/S_" . $sourcepic;
                        $data['medium'] = "storage/{$this->tablename}/{$property_id}/M_" . $sourcepic;
                        $data['large'] = "storage/{$this->tablename}/{$property_id}/L_" . $sourcepic;
                        $data['sourcepic'] = "storage/{$this->tablename}/{$property_id}/" . $sourcepic;

                        $PropertyObj->edit($data,$property_id);
                    }

                    $data=array();
                    $data['isok']='true';
                    $data['data']=$this->tablenamestr.'添加成功';
                    $data['url']='index.php?a=index&c='.$this->tablename;
                }else{
                    $data['isok']='false';
                    $data['data']=$this->tablenamestr.'添加失败';
                    $data['url'] = 'index.php?a=index&c='.$this->tablename;
                }
            }


            Buddha_Http_Output::makeJson($data);
        }


        $this->smarty->assign('title', $this->tablenamestr);
        $this->smarty->assign('c', $this->tablename);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    /**
     * 编辑
     */
    public function edit()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $PropertyObj = new Property();
        $GalleryObj = new Gallery();
        $CommonObj = new Common();
        $UsercommonObj = new Usercommon();

        $property_id = $id=(int)Buddha_Http_Input::getParameter('id');

        if(!$id)
        {
            Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=shop',2);
        }

        $b_display =(int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        $Filedarr = array('id','name','roadfullname','buddhastatus');

        if($b_display==2)
        {
            array_push($Filedarr,'small as img');

        }elseif ($b_display=1)
        {
            array_push($Filedarr,'medium as img');
        }

        $Db_Property = $PropertyObj->getSingleFiledValues($Filedarr,"id='{$id}'");

        if(!$Db_Property){
            Buddha_Http_Head::redirectofmobile('没有找到您要的信息！','index.php?a=index&c='.$this->tablename,2);
        }



        $Db_Property['roadfullname'] = $CommonObj->isvar($Db_Property['roadfullname']);
        $Db_Property['img'] = $CommonObj->handleImgSlashByImgurl($Db_Property['img']);




        $property = Buddha_Http_Input::getParameter('property')?Buddha_Http_Input::getParameter('property'):'';
        $roadfullname = Buddha_Http_Input::getParameter('roadfullname')?Buddha_Http_Input::getParameter('roadfullname'):'';


        if (Buddha_Http_Input::isPost())
        {
            $datas['level0'] = $UserInfo['level0'];
            $datas['level1'] = $UserInfo['level1'];
            $datas['level2'] = $UserInfo['level2'];
            $datas['level3'] = $UserInfo['level3'];

            $datas['name'] = $property;

            $datas['roadfullname'] = $roadfullname;

                $property_num = $PropertyObj->edit($datas,$id);

                if($id)
                {
                    $Image = Buddha_Http_Upload::getInstance()->setUpload(PATH_ROOT . "storage/property/{$property_id}/",
                        array('gif', 'jpg', 'jpeg', 'png'), Buddha::$buddha_array['upload_maxsize'])->run('Image')
                        ->getOneReturnArray();

                    if ($Image)
                    {
                        //解决图像的旋转
                        $GalleryObj->resolveImageForRotate(PATH_ROOT . $Image, NULL);
                        Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 320, 320, 'S_');
                        Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 640, 640, 'M_');
                        Buddha_Tool_File::thumbImageSameWidth(PATH_ROOT . $Image, 1200, 640, 'L_');
                    }


                    $sourcepic = str_replace("storage/{$this->tablename}/{$property_id}/", '', $Image);
//                    var_dump($sourcepic);
//                    echo '<br/>';
//                    $sourcepicarr = explode('/', $sourcepic);
//                    var_dump($sourcepicarr);
//                    $sourcepic = $sourcepicarr['3'];

                    $data = array();

                    if ($Image) {
                        $data['small'] = "storage/{$this->tablename}/{$property_id}/S_" . $sourcepic;
                        $data['medium'] = "storage/{$this->tablename}/{$property_id}/M_" . $sourcepic;
                        $data['large'] = "storage/{$this->tablename}/{$property_id}/L_" . $sourcepic;
                        $data['sourcepic'] = "storage/{$this->tablename}/{$property_id}/" . $sourcepic;

                        $PropertyObj->edit($data, $property_id);
                    }

                    $data['isok'] = 'true';
                    $data['data'] = $this->tablenamestr.'编辑成功';
                    $data['url']='index.php?a=index&c='.$this->tablename;
                } else {
                    $data['isok'] = 'false';
                    $data['data'] = $this->tablenamestr.'编辑失败..';
                    $data['url'] = 'index.php?a=index&c='.$this->tablename;
                }



            Buddha_Http_Output::makeJson($data);
        }
        $this->smarty->assign('shopinfo', $Db_Property);
        $this->smarty->assign('title', $this->tablenamestr);
        $this->smarty->assign('c', $this->tablename);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    /**
     *   判断该物业名称是否存在
     */
    public  function isdel()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $id = (int)Buddha_Http_Input::getParameter('id')?(int)Buddha_Http_Input::getParameter('id'):0;
        $property=Buddha_Http_Input::getParameter('property');
        $roadfullname=(int)Buddha_Http_Input::getParameter('roadfullname');
        $PropertyObj = new Property();

        if($PropertyObj->isExistence($UserInfo['level1'],$UserInfo['level2'],$UserInfo['level3'],$property,$roadfullname,$id))
        {
            $data['isok'] = 'false';
            $data['data'] = '该物业名称已经存在了，请不要重复添加';

        }else{

            $data['isok'] = 'true';
            $data['data'] = '可以使用';
        }



        Buddha_Http_Output::makeJson($data);
    }




    /**
     *   判断该物业名称是否存在
     */
    public  function disableuse()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $id = (int)Buddha_Http_Input::getParameter('id')?(int)Buddha_Http_Input::getParameter('id'):0;

        $PropertyObj = new Property();

        $Db_Property = $PropertyObj->agentdisableuse($id,$uid);

        if($Db_Property['is_ok']==1)
        {
            $isok = 'true';

        }else{

            $isok = 'false';
        }

        $data['isok'] = $isok;
        $data['data'] = $Db_Property['is_msg'];
        $data['id'] = $id;
        $data['state'] = $Db_Property['buttonname'];


        Buddha_Http_Output::makeJson($data);
    }

    /**
     * 合并列表
     */
    public function mergeindex()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $keyword = Buddha_Http_Input::getParameter('keyword');

        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;

        $id = (int)Buddha_Http_Input::getParameter('id')?(int)Buddha_Http_Input::getParameter('id') : 0;
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?(int)Buddha_Http_Input::getParameter('b_display') : 2;

        $UsercommonObj = new Usercommon();
        $Usercommon = $UsercommonObj->isUserJurisdiction($uid,$this->needtypeId);
        if(!$Usercommon['is_ok']!=1)
        {
            Buddha_Http_Head::redirectofmobile('参数错误',"index.php?a=index&c={$this->tablename}",2);
        }

        $pagesize = 100;

        $where =" level3='{$UserInfo['level3']}' ";
        $mergeindex_where  = $where." AND id!='{$id}'";
        $merge_where  = $where." AND id='{$id}'";
        if($keyword)
        {
            $mergeindex_where .= $where." and name like '%{$keyword}%'";
        }

        $orderby = " order by createtime DESC ";


        $Filedarr = array('id','name','roadfullname','buddhastatus');

        if($b_display==2)
        {
            array_push($Filedarr,'small as img');

        }elseif ($b_display=1)
        {
            array_push($Filedarr,'medium as img');
        }

        $mergeindex = $this->db->getFiledValues ($Filedarr, $this->prefix.$this->tablename, $mergeindex_where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

        $merge = $this->db->getSingleFiledValues ($Filedarr, $this->prefix.$this->tablename, $merge_where);
        $CommonObj = new Common();
        $merge['img'] = $CommonObj->handleImgSlashByImgurl($merge['img']);

        //判要合并的对象是否信息是否完整
        if(!Buddha_Atom_String::isValidString($merge['name'])
            AND !Buddha_Atom_String::isValidString($merge['level3'])
            AND !Buddha_Atom_String::isValidString($merge['img']))
        {
            Buddha_Http_Head::redirectofmobile('该物业信息不完整，请完善后再合并吧！',"index.php?a=index&c={$this->tablename}",2);
        }

        $CommonObj = new Common();

        foreach ($mergeindex as $k=>$v)
        {
            $list[$k]['roadfullname'] = $CommonObj->isvar($v['roadfullname']);
        }

        $this->smarty->assign('mergeindex',$mergeindex);
        $this->smarty->assign('merge',$merge);
        $this->smarty->assign('mergeindex',$mergeindex);
        $this->smarty->assign('title',$this->tablenamestr);
        $this->smarty->assign('c',$this->tablename);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    /**
     *   合并
     */
    public  function merge()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $mergeid = Buddha_Http_Input::getParameter('merge');
        $mergeobject = Buddha_Http_Input::getParameter('mergeobject');

        $PropertyObj = new Property();

        $Db_Property = $PropertyObj->PropertyMergeByIdarr($UserInfo['level3'],$mergeid,$mergeobject,$uid,$this->needtypeId);

        if($Db_Property['is_ok']==1)
        {
            $isok = 'true';

        }else{

            $isok = 'false';
        }

        $data['isok'] = $isok;
        $data['data'] = $Db_Property['is_msg'];
        $data['id'] = $mergeid;
        $data['url']='index.php?a=index&c='.$this->tablename;
        Buddha_Http_Output::makeJson($data);

    }




}