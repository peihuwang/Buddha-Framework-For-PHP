<?php

/**
 * Class SingleinformationController
 */
class SingleinformationController extends Buddha_App_Action
{
    protected $tablenamestr;
    protected $tablename;
    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='单页信息';
        $this->tablename='singleinformation';
    }

    public function index(){
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if(!$uid){//
            Buddha_Http_Head::redirectofmobile('请到个人中心切换到商家角色后发布活动！',"index.php?a=login&c=account",2);
            exit;
        }
        $this->smarty->assign('c', $this->tablename);
        $this->smarty->assign('title', $this->tablenamestr);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    function ajax_index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $view=Buddha_Http_Input::getParameter('view')?Buddha_Http_Input::getParameter('view'):0;
        $keyword=Buddha_Http_Input::getParameter('keyword')?Buddha_Http_Input::getParameter('keyword'):0;
        $page=Buddha_Http_Input::getParameter('page')?(int)Buddha_Http_Input::getParameter('page'):1;

        $Today=$Tomorrow=$currentdate=0;
        $Today=strtotime(date('Y-m-d'));//今天0点时间戳
        $Tomorrow=strtotime(date('Y-m-d',strtotime('+1 day')));//明天0点时间戳
        $currentdate=time();//当前时间戳
        $where = "isdel=0 and user_id='{$uid}'";
        if(!empty($keyword)){
            $where.=" and (name like '%{$keyword}%' or number like '%{$keyword}%')";
        }


//==================================================================
        if($view){
            switch($view){
                case 2;
                    $where.=' and is_sure=0 ';
                    break;
                case 3;
                    $where.=' and is_sure=1 ';
                    break;
                case 4;
                    $where.=' and is_sure=4 ';
                    break;
            }
        }
//==================================================================

//        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pagesize =18;
        $orderby = " order by id DESC ";
        $filed=array('id','name','buddhastatus','is_sure','singleinformation_thumb','number','brief');
        $SingleinformationObj =new Singleinformation();

        $list = $SingleinformationObj->getFiledValues ($filed, $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );


        $UserObj= new User();
        $sure=$UserObj->getSingleFiledValues(array('groupid'),"id='{$uid}' and isdel=0");


        $CommonObj = new Common();
        $UsercommonObj = new Usercommon();

        foreach($list as $k=>$v)
        {
            $brief=  $CommonObj->intercept_strlen($v['brief']);
            $jsondata['list'][]=array(
                'id'=>$v['id'],
                'name'=>$v['name'],
                'number'=>$v['number'],
                'brief'=>$brief,
                'buddhastatus'=>$v['buddhastatus'],
                'issureimg'=>$UsercommonObj->businessissurestr($v['is_sure']),
                'singleinformation_thumb'=>$CommonObj->handleImgSlashByImgurl($v['singleinformation_thumb']),
                'is_sure'=>$v['is_sure'],
            );
        }
        /*信息置顶的信息*/
        $jsondata['infotop']=array('good_table'=>'singleinformation','order_type'=>'info.top','final_amt'=>'0.2');

        $CommonObj =new Common();
        $jsondata['eurl']=$CommonObj->activity_url('edit');
        $jsondata['durl']=$CommonObj->activity_url('del');
        $jsondata['murl']=$CommonObj->activity_url('mylist');


        $Nws= $CommonObj->page_where($page,$jsondata['list'],$pagesize);
        $datas['info']=$Nws;
        if(count($list)>0){
            $datas['isok']='true';
            $datas['data']=$jsondata;
        }else{
            $datas['isok']='false';
            $datas['data']='没有数据';

        }
        Buddha_Http_Output::makeJson($datas);
    }


    public function add()
    {
        $c=$this->c;
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if($UserInfo['groupid']==1){
            $t='business';
        }elseif($UserInfo['groupid']==2){
            $t='agent';
        }elseif($UserInfo['groupid']==3){
            $t='partner';
        }elseif($UserInfo['groupid']==4){
            $t='user';
        }
        if(empty($uid)){
            Buddha_Http_Head::redirectofmobile('请登录后发布！','index.php?a=login&c=account',2);
            exit;
        }else{
            if($UserInfo['groupid']!=1){//用户注册时的角色
                if($_SESSION['groupid']!=1){//当前身份角色
                    Buddha_Http_Head::redirectofmobile('请到个人中心切换到商家角色后发布活动！',"/{$t}/index.php?a=index&c={$t}",2);
                    exit;
                }
            }
        }
        $ShopObj=new Shop();
        $CommonObj= new Common();
        $num=$ShopObj->countRecords("isdel=0 and state=0 and is_sure=1 and user_id='{$uid}'");
        if($num==0){
            Buddha_Http_Head::redirectofmobile('您还没用创建店铺，或者店铺还未通过审核！','index.php?a=index&c=shop',2);
        }
        $type=Buddha_Http_Input::getParameter('type');
        $shopid=Buddha_Http_Input::getParameter('shop_id');//发布商家Id
        $shopname=Buddha_Http_Input::getParameter('shop_name');//发布商家名称
        $name=Buddha_Http_Input::getParameter('name');

        $buddhastatus=Buddha_Http_Input::getParameter('buddhastatus');
        $desc=Buddha_Http_Input::getParameter('goods_desc');

        $brief=Buddha_Http_Input::getParameter('brief');
        //商品异地发布
        $is_remote=Buddha_Http_Input::getParameter('is_remote');
        $regionstr=Buddha_Http_Input::getParameter('regionstr');

        if(Buddha_Http_Input::isPost()){
            $SingleinformationObj= new Singleinformation();
            $CommonObj= new Common();
            $data=array();
            $data['name']=$name;
            $data['add_time']=time();
            $data['user_id']=$uid;
            $data['number']=$CommonObj->GeneratingNumber();//单页编号

            $data['brief']=$brief;
            if($buddhastatus==''){//上架
                $data['buddhastatus']=1;
            }else{
                $data['buddhastatus']=$buddhastatus;
            }
            $data['shop_id']=$shopid;
            $data['shop_name']=$shopname;
            if($is_remote==''){//0本地
                if(!$shopid){//如果用户没有选择选择店铺则无法获取到用户地址：
                    //1、则把用户当前的地址给它（用户注册的地址）
                    //2、如果用户登录信息里没有地址则把在首页选择的地址给它
                    $data['is_remote']=0;
                    if(!empty($UserInfo['level3'])){//如果用户登录信息里有地址
                        $data['level0']=$UserInfo['level0'];
                        $data['level1']=$UserInfo['level1'];
                        $data['level2']=$UserInfo['level2'];
                        $data['level3']=$UserInfo['level3'];
                    }else{
                        $RegionObj=new Region();
                        $locdata = $RegionObj->RegionCookieSelect();
                        $data['level0']=$locdata['level0'];
                        $data['level1']=$locdata['level1'];
                        $data['level2']=$locdata['level2'];
                        $data['level3']=$locdata['level3'];
                    }
                }else{
                    $data['is_remote']=0;
                    $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$uid}' and id='{$shopid}' and isdel=0");
                    $data['level0']=$Db_level['level0'];
                    $data['level1']=$Db_level['level1'];
                    $data['level2']=$Db_level['level2'];
                    $data['level3']=$Db_level['level3'];
                }
            }elseif($is_remote==1){//1为异地
                $level = explode(",", $regionstr);
                $data['is_remote']=1;
                $data['level0']=1;
                $data['level1']=$level[0];
                $data['level2']=$level[1];
                $data['level3']=$level[2];
            }

            $good_id = $SingleinformationObj->add($data);

            $datas = array();

            if($good_id)
            {
                $table_name=Buddha_Http_Input::getParameter('c');
                $MoreImage= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . 'storage/'.$table_name.'/'.$good_id.'/', array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('file') ->getAllReturnArray();

                $webfield='file';

                if(is_array($MoreImage) and count($MoreImage)>0)
                {
                    $MoregalleryObj =new Moregallery();

                    $moregallery_id=$MoregalleryObj-> pcaddimage($webfield,$MoreImage,$good_id,$table_name,$uid);

                    if(count($moregallery_id)>0)
                    {
                        $num= $SingleinformationObj->setFirstGalleryImgToSupply($good_id,$table_name);
                        if($num==0){
                            $datas['err']=3;
                        }
                    }else{
                        $datas['err']=2;
                    }
                }

                if($desc){  // 富文本编辑器图片处理
                    $MoregalleryObj=new Moregallery();
                    $saveData = $MoregalleryObj->base_upload($desc,$good_id,$table_name);
                    $saveData = str_replace(PATH_ROOT,'/', $saveData);
                    $details['desc'] = $saveData;
                    $SingleinformationObj->edit($details,$good_id);
                }
                //$remote为1表示发布异地产品添加订单
                if($is_remote==1)
                {
                    $OrderObj=new Order();
                    $datas = $OrderObj->Remote_order($shopid,$uid,$good_id,$level,$table_name);
                }else{
                    $datas['isok']='true';
                    $datas['data']='信息添加成功';
                    $datas['url']='index.php?a=index&c='.$c;
                }
            }else{
                $datas['err']=1;
                $datas['isok']='false';
                $datas['data']='信息添加失败!';
                $datas['url']='index.php?a=add&c='.$c;
            }
            //err: 1 表示活动添加失败！；2表示封面照添加失败；3表示封面护照在活动中的字段更新失败;
            Buddha_Http_Output::makeJson($datas);
        }

        $getshoplistOption=$ShopObj->getShoplistOption($uid,0);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('c', $c);
        $this->smarty->assign('title', '信息');
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    public function edit()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=(int)Buddha_Http_Input::getParameter('id');
        $c=$this->c;
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c='.$c,2);
        }
        $title='信息';
        $SingleinformationObj=new Singleinformation();
        $Singleinformation=$SingleinformationObj->getSingleFiledValues('',"id={$id} and user_id={$uid}");
        if(!$Singleinformation){
            Buddha_Http_Head::redirectofmobile('您没有权限查看或'.$title.'已删除！','index.php?a=index&c='.$c,2);
        }

        $this->smarty->assign('title', $title);
        $this->smarty->assign('c', $c);
        $ShopObj=new Shop();
        $getshoplistOption=$ShopObj->getShoplistOption($uid,$Singleinformation['shop_id']);
        $name=Buddha_Http_Input::getParameter('name');

        $buddhastatus=Buddha_Http_Input::getParameter('buddhastatus');
        $desc=Buddha_Http_Input::getParameter('desc');
        $oldimg=Buddha_Http_Input::getParameter('oldimg');
        $brief=Buddha_Http_Input::getParameter('brief');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');//发布商家Id
        $shop_name=Buddha_Http_Input::getParameter('shop_name');//发布商家名称

        //商品异地发布
        $is_remote=Buddha_Http_Input::getParameter('is_remote');
        $regionstr=Buddha_Http_Input::getParameter('regionstr');
//var_dump($desc);
//exit;

        if(Buddha_Http_Input::isPost()){
            $SingleinformationObj= new Singleinformation();
            $CommonObjs= new Common();
            $data=array();
            $data['name']=$name;
            $data['add_time']=time();
            $data['user_id']=$uid;
            $data['number']=$CommonObjs->GeneratingNumber();
            $data['brief']=$brief;
            if($buddhastatus==''){//上架
                $data['buddhastatus']=1;
            }else{
                $data['buddhastatus']=$buddhastatus;
            }
            $data['shop_id']=$shop_id;
            $data['shop_name']=$shop_name;
            $data['brief']=$brief;
            if($is_remote==''){//0本地
                $data['is_remote']=0;
                $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$uid}' and id='{$shop_id}' and isdel=0");
                $data['level0']=$Db_level['level0'];
                $data['level1']=$Db_level['level1'];
                $data['level2']=$Db_level['level2'];
                $data['level3']=$Db_level['level3'];
            }elseif($is_remote==1){//1为异地
                $level = explode(",", $regionstr);
                $data['is_remote']=1;
                $data['level0']=1;
                $data['level1']=$level[0];
                $data['level2']=$level[1];
                $data['level3']=$level[2];
            }
            $SingleinformationObj->edit($data,$id);
            $datas = array();
            if($SingleinformationObj){
                $table_name=Buddha_Http_Input::getParameter('c');
                $MoregalleryObj =new Moregallery();

                //如果没有更换照片就不用图片处理
                $MoreImage= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . 'storage/'.$table_name.'/'.$id.'/', array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('file') ->getAllReturnArray();
                $webfield='file';
            if($MoreImage[0]!=$oldimg){//表示更新图片
                $imgWhere="goods_id={$id} and tablename='{$table_name}'";
                $gimages=$MoregalleryObj->getSingleFiledValues(array('id','goods_thumb','goods_img','goods_large','sourcepic'),$imgWhere);
                $thumimg=$MoregalleryObj->del_image($id,$gimages);

                $data['singleinformation_thumb']='';$data['singleinformation_img']='';$data['singleinformation_large']='';$data['sourcepic']='';
                $SingleinformationObj->edit($data,$id);
                if(is_array($MoreImage) and count($MoreImage)>0){
                    $moregallery_id=$MoregalleryObj->pcaddimage($webfield,$MoreImage,$id,$table_name,$uid);
                    if(count($moregallery_id)>0){
                        $num= $SingleinformationObj->setFirstGalleryImgToSupply($id,$table_name);
                        if($num==0){
                            $datas['err']=3;
                        }
                    }else{
                        $datas['err']=2;
                    }
                }
            }

                if($desc){//富文本编辑器图片处理
                    $dirs=PATH_ROOT."storage/quill/{$c}/{$id}/";
                    if(is_dir($dirs)){
                        if ($dh = opendir($dirs)){
                            while (($file = readdir($dh)) !== false){
                                //$filePath = $dirs.$file;
                                if(!strstr($desc,$file) and $file != '.' and $file !='..'){
                                    @unlink($dirs.$file);//删除修改后的图片
                                    /*echo $file;
                                    exit;*/
                                }
                            }
                        }
                        //$GalleryObj->deleteDir($dirs);
                    }
                }
                $saveData = $MoregalleryObj->base_upload($desc,$id,$table_name);//base64图片上传
                if($saveData){
                    $saveData = str_replace(PATH_ROOT,'/', $saveData);//替换
                    $details['desc'] = $saveData;
                }else{
                    $details['desc'] = $desc;
                }
                $SingleinformationObj->edit($details,$id);//更新数据
                //$remote为1表示发布异地产品添加订单
                if($is_remote==1){
                    $OrderObj =new Order();
                    $datas=$OrderObj->Remote_order($shop_id,$uid,$id,$level,$table_name);
                }else{
                    $datas['isok']='true';
                    $datas['data']='活动编辑成功！';
                    $datas['url']='index.php?a=index&c='.$c;
                }
            }else{
                $datas['err']=1;
                $datas['isok']='false';
                $datas['data']='活动编辑失败!';
                $datas['url']='index.php?a=add&c='.$c;
            }
            //err: 1 表示活动添加失败！；2表示封面照添加失败；3表示封面护照在活动中的字段更新失败;
            Buddha_Http_Output::makeJson($datas);
        }
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('act', $Singleinformation);

        $this->smarty->assign('c', $this->c);
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    public function mylist(){
        $ActivityObj =new Activity();
        $ActivityObj->mylist();
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }
/*编辑时删除封面*/
    function  del_file(){
        $c=$this->c;
        $id = (int)Buddha_Http_Input::getParameter('id')?(int)Buddha_Http_Input::getParameter('id'):0;//合作商家列表Id
        $sql ="select m.id
                from {$this->prefix}$c as s
                INNER join {$this->prefix}moregallery as m
                on s.id= m.goods_id 
                where s.id={$id} and webfield='file'";
        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $MoregalleryObj= new Moregallery();
        $CommonObj= new Common();
        $data['singleinformation_thumb']='';
        $data['singleinformation_img']='';
        $data['singleinformation_large']='';
        $data['sourcepic']='';
        $SingleinformationObj=new Singleinformation();
        $SingleinformationObj->edit($data,$id);
        $num= $CommonObj->delGalleryimage($list[0]['id'],$MoregalleryObj);
        if($num){
            $datas['isok']='true';
        }else{
            $datas['isok']='false';
        }
        Buddha_Http_Output::makeJson($datas);
    }
    public function del()
    {
        $id = (int)Buddha_Http_Input::getParameter('id');
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

