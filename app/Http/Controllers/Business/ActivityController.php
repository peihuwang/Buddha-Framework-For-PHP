<?php

/**
 * Class ActivityController
 */
class ActivityController extends Buddha_App_Action
{
    protected $tablenamestr;
    protected $tablename;
    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='活动';
        $this->tablename='activity';
    }
    /*
    *  ajaxadderr       请求区域地址
    */
    public function ajaxadderr(){
        $RegionObj=new Region();
        $fid = Buddha_Http_Input::getParameter('fid');
        $datas= $RegionObj->ajax_adderr($fid);
        Buddha_Http_Output::makeJson($datas);
    }

    public function mylist(){
        $ActivityObj =new Activity();
        $ActivityObj->mylist();
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    public function vodelist(){
        $ActivityObj =new Activity();
        $c=$this->c;
        $ActivityObj->vodelist($c);
        $this->smarty->assign('c', $c);
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    public function vodelist_ajax(){
        $title=Buddha_Http_Input::getParameter('title')?Buddha_Http_Input::getParameter('title'):2;//2人气、3最新
        $id=Buddha_Http_Input::getParameter('id');//活动ID
        $page=Buddha_Http_Input::getParameter('p');
        $search=Buddha_Http_Input::getParameter('search');
        $pagesize=20;
        $ShopObj=new Shop();

        $ActivityObj= new Activity();

        $ActO= $ActivityObj->getSingleFiledValues(array('id','type','vode_type'),"id={$id}");//查询当前活动的活动类型（如果是投票也要查询投票类型）
        $limit=Buddha_Tool_Page::sqlLimit ($page, $pagesize);
        $where=' a.act_id='.$id;
        if(!empty($search)){
            $where.=" and (s.name like '%{$search}%' or s.number like '%{$search}%')";
        }

        //对应商品（supply:goods_thumb 照片）、个人（user：logo照片）、店铺（shop：small）的（cooID、票数 、名称、 在activitycooperation表中）和活动ID


        $filed="a.id,a.shop_id,a.shop_name,a.praise_num";//在 activitycooperation 表中要显示的字段有： 商品、个人、店铺
        //在 activitycooperation 表中要显示的字段有：在activitycooperation中要显示当前 商品、个人、店铺 的所在行的ID、票数、名称
        if($title==2){//2人气、3最新
            $orderby=' order by a.praise_num desc';
        }elseif($title==3){//2人气、3最新
            $orderby=' order by a.add_time desc';
        }
        if(($ActO['type']==3 || $ActO['type']==4) && $ActO['vode_type']==2) {//
            $filed.=',u.logo';
            $table='user';
            $as_f='u';
        }else if(($ActO['type']==3 || $ActO['type']==4) && $ActO['vode_type']==3) {
            $filed.=',s.goods_thumb';
            $table='supply';
            $as_f='s';
        }else{
            $filed.=',s.small';
            $table='shop';
            $as_f='s';
        }
        $sql ="select {$filed}
               from {$this->prefix}activitycooperation as a 
               INNER join {$this->prefix}{$table} as {$as_f} 
               on {$as_f}.id = a.shop_id  
               where {$where} {$orderby} {$limit}";
        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        foreach($list as $k=>$v){
            if(($ActO['type']==3 || $ActO['type']==4) && $ActO['vode_type']==2) {//个人
                if(!empty($v['logo'])){
                    $list[$k]['small']=$v['logo'];
                }else{
                    $list[$k]['small']='style/images/im.png';
                }
            }else if(($ActO['type']==3 || $ActO['type']==4) && $ActO['vode_type']==3) {//产品
                $list[$k]['small']=$v['goods_thumb'];
            }
        }

        $CommonObj=new Common();
        $Nws= $CommonObj->page_where($page,$list,$pagesize);
        if($list){//已经该店铺报名了
            $datas['Nws']=$Nws;
            $datas['isok']='true';
            $datas['data']=$list;
            if(($ActO['type']==3 || $ActO['type']==4) && $ActO['vode_type']==2) {//个人
                $datas['shop_url']='';
            }else if(($ActO['type']==3 || $ActO['type']==4) && $ActO['vode_type']==3) {//产品
                $SupplyObj= new Supply();
                $datas['shop_url']=$SupplyObj->supply_url();
            }else{
                $datas['shop_url']=$ShopObj->shop_url();
            }
        }else{
            $datas['isok']='false';
        }
        Buddha_Http_Output::makeJson($datas);
    }

    public function vodeprize(){
        $ActivityObj =new Activity();
         $ActivityObj->vodeprize();
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    public function vodedetail(){
        $ActivityObj =new Activity();

        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    //报名和问卷调查（屏蔽原因：商家不用自己给自己报名）
    public function ajaxmylist(){
        $ActivityObj=new Activity();
//        $datas=$ActivityObj->ajaxmylist();
        ////////////////////////
//        活动问卷
//        Buddha_Http_Output::makeJson($datas);
    }





    public function index()
    {
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if(!$uid){//
            Buddha_Http_Head::redirectofmobile('请到个人中心切换到商家角色后发布活动！',"index.php?a=login&c=account",2);
            exit;
        }

        $type = Buddha_Http_Input::getParameter('type')?Buddha_Http_Input::getParameter('type'):0;

        $c=$this->c;

        if($type==0)
        {
            $title='全部';
        }else if($type==1){
            $title='个体';
        }else if($type==2){
            $title='联合';
        }else if($type==3){
            $title='投票';
        }else if($type==4){
            $title='点赞';
        }

        $this->smarty->assign('c', $c);
        $this->smarty->assign('title', $title);
        $this->smarty->assign('type', $type);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    /**
     *
     */
    function ajax_index()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
//        $view=Buddha_Http_Input::getParameter('view')?Buddha_Http_Input::getParameter('view'):0;
        $view = Buddha_Http_Input::getParameter('type')?Buddha_Http_Input::getParameter('type'):0;

        $currentclick = Buddha_Http_Input::getParameter('currentclick')?Buddha_Http_Input::getParameter('currentclick'):0;
        $keyword=Buddha_Http_Input::getParameter('keyword')?Buddha_Http_Input::getParameter('keyword'):0;
        $page=Buddha_Http_Input::getParameter('page')?(int)Buddha_Http_Input::getParameter('page'):1;

        $Today = $Tomorrow=$currentdate=0;
        $Today = strtotime(date('Y-m-d'));//今天0点时间戳
        $Tomorrow = strtotime(date('Y-m-d',strtotime('+1 day')));//明天0点时间戳
        $currentdate = time();//当前时间戳
        $where = "isdel=0 and user_id='{$uid}'";
        if(!empty($keyword)){
            $where.=" and (name like '%{$keyword}%' or number like '%{$keyword}%')";
        }

        if($view){
            switch($view)
            {
                case 1;//单商家
                    $where.=' and type=1 ';
                    break;
                case 2;//多商家
                    $where.=' and type=2 ';
                    break;
                case 3;//投票
                    $where.=' and type=3 ';
                    break;
                case 4;//点赞
                    $where.=' and type=4 ';
                    break;
            }
        }

        if($currentclick)
        {
            switch($currentclick)
            {
                case 2;
                    $where.=' and is_sure=0 ';
                    break;
                case 3;
                    $where.=' and is_sure=1 ';
                    break;
                case 4;
                    $where.=' and is_sure=4 ';
            }

        }



        $pagesize =18;
        $orderby = " order by id DESC ";
        $filed=array('id','name','start_date','end_date','buddhastatus','is_sure','activity_thumb','number','type');
        $ActivityObj =new Activity();

        $list = $ActivityObj->getFiledValues ($filed, $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

        $UserObj = new User();
        $sure = $UserObj->getSingleFiledValues(array('groupid'),"id='{$uid}' and isdel=0");

        $CommonObj = new Common();
        $UsercommonObj = new Usercommon();

        foreach($list as $k=>$v)
        {

            $name= mb_substr($v['name'],0,12) . '...';

            if($v['type']==1||$v['type']==2)
            {
                $m_url=$ActivityObj->activity_url('mylist');
            }elseif ($v['type']==3||$v['type']==4)
            {
                $m_url=''.$ActivityObj->activity_url('vodelist');
            }

            $jsondata['list'][]=array(
                'id'=>$v['id'],
                'name'=>$name,
                'end_date'=>date('Y.m.d',$v['end_date']),
                'number'=>$v['number'],
                'start_date'=>date('Y.m.d',$v['start_date']),
                'buddhastatus'=>$v['buddhastatus'],
                'issureimg'=>$UsercommonObj->businessissurestr($v['is_sure']),
                'activity_thumb'=>$CommonObj->handleImgSlashByImgurl($v['activity_thumb']),
                'is_sure'=>$v['is_sure'],
                'm_url'=>$m_url,
            );
        }

        /*信息置顶的信息*/
        $jsondata['infotop']=array('good_table'=>'activity','order_type'=>'info.top','final_amt'=>'0.2');

        $jsondata['eurl']=$ActivityObj->activity_url('edit');
        $jsondata['durl']=$ActivityObj->activity_url('del');

        $jsondata['murl']=$ActivityObj->activity_url('mylist');


        $Nws= $ActivityObj->page_where($page,$jsondata['list'],$pagesize);
        $datas['info']=$Nws;
        /**↓↓↓↓↓↓↓↓↓↓↓ 置顶参数 ↓↓↓↓↓↓↓↓↓↓↓**/
        $datas['top']['good_table']=$this->tablename;
        $datas['top']['order_type']='info.top';
        $datas['top']['final_amt']=0.2;
        /**↑↑↑↑↑↑↑↑↑↑ 置顶参数 ↑↑↑↑↑↑↑↑↑↑**/
        if(count($list)>0){
            $datas['isok']='true';
            $datas['data']=$jsondata;
            $datas['view']=$view;
        }else{
            $datas['isok']='false';
            $datas['data']='没有数据';

        }
        Buddha_Http_Output::makeJson($datas);
    }

    /**
     *
     */
    public function add()
    {
        header("Content-Type: text/html; charset=utf-8");

        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $view = Buddha_Http_Input::getParameter('type')?Buddha_Http_Input::getParameter('type'):0;//活动类型

        if($view==0)
        {
            $title='全部';
        }else if($view==1)
        {
            $title='个体';
        }else if($view==2)
        {
            $title='联合';
        }else if($view==3)
        {
            $title='投票';
        }else if($view==4)
        {
            $title='点赞';
        }

        $this->smarty->assign('title', $title);

        $this->smarty->assign('view', $view);

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
        $typeid = Buddha_Http_Input::getParameter('typeid')?Buddha_Http_Input::getParameter('typeid'):1;
        $c=$this->c;

        $type= Buddha_Http_Input::getParameter('type');              //类型
        $shopid=Buddha_Http_Input::getParameter('shop_id');         //发布商家Id
        $shopname=Buddha_Http_Input::getParameter('shop_name');     //发布商家名称
        $name=Buddha_Http_Input::getParameter('name');              //活动名称
        $sign_start_time=Buddha_Http_Input::getParameter('v_start_date');  //报名开始时间
        $sign_end_time=Buddha_Http_Input::getParameter('v_end_date');      //报名结束时间

        $start_date=Buddha_Http_Input::getParameter('start_date');  //投票开始时间
        $end_date=Buddha_Http_Input::getParameter('end_date');      //投票结束时间

        $buddhastatus=Buddha_Http_Input::getParameter('buddhastatus');//是否上架
        $brief=Buddha_Http_Input::getParameter('brief');            //简述
        $desc=Buddha_Http_Input::getParameter('desc');              //详情
        //商品异地发布
        $is_remote=Buddha_Http_Input::getParameter('is_remote');    //是否异地发布
        $regionstr=Buddha_Http_Input::getParameter('regionstr');    //异地发布区域的ID

        if($type==1||$type==2)
        {
            //表单
            $text=Buddha_Http_Input::getParameter('text');          //多行
            $txt=Buddha_Http_Input::getParameter('txt');            //单行
            $radio=Buddha_Http_Input::getParameter('radio');        //单选的内容
            $radioname=Buddha_Http_Input::getParameter('radioname');//单选的标题
            $checkname=Buddha_Http_Input::getParameter('checkname');//多选的标题
            $checkbox=Buddha_Http_Input::getParameter('checkbox');  //多选的内容
            $form=Buddha_Http_Input::getParameter('form_val');      //多选的内容
            $address=Buddha_Http_Input::getParameter('address');    //详细地址
        }
        if($type==2||$type==3||$type==4){
            $coo_shopid=Buddha_Http_Input::getParameter('cooshopid');//合作商家Id
            $coo_shopname=Buddha_Http_Input::getParameter('cooshopname');//合作商家名称
        }

        if($type==3||$type==4){
            $cooshopname_title=Buddha_Http_Input::getParameter('cooshopname_title');//冠名商家名称
            $cooshopid_title=Buddha_Http_Input::getParameter('cooshopid_title');//冠名商家ID
            $prize=Buddha_Http_Input::getParameter('prize');         //奖品
            $v_type=Buddha_Http_Input::getParameter('v_type');              //投票或的合作对象类型
        }
        if(Buddha_Http_Input::isPost()){
            $ActivityObj= new Activity();
            $data=array();
            if($type==1||$type==2){
                $data['address']=$address;
            }
            $CommonObj= new Common();
            $data['name']=$name;
            $data['type']=$type;
            $data['add_time']=time();
            $data['user_id']=$uid;
            $data['number']=$CommonObj->GeneratingNumber();
            $data['start_date']=strtotime($start_date);
            $data['end_date']=strtotime($end_date);
            $data['sign_start_time']=strtotime($sign_start_time);
            $data['sign_end_time']=strtotime($sign_end_time);
            $data['brief']=$brief;
            if($buddhastatus==''){//上架
                $data['buddhastatus']=1;
            }else{
                $data['buddhastatus']=$buddhastatus;
            }
            $data['shop_id']=$shopid;
            $data['shop_name']=$shopname;
//-----------
            if($type==3||$type==4){
                $data['vode_type']=$v_type;
            }
//-----------

            if($is_remote==''){//0本地
                $data['is_remote']=0;
                $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$uid}' and id='{$shopid}' and isdel=0");
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
            $good_id = $ActivityObj->add($data);


            $datas = array();
            if($good_id){
                $table_name=Buddha_Http_Input::getParameter('c');
//                $table_name='activity';
//-----------
                if($type==1||$type==2){
                    $form_arr=$ActivityObj->if_where($radioname,$checkname,$text,$txt,$checkbox,$radio,$table_name,$good_id);//判断是否符合条件
                    if(!empty($form_arr)){
                        $data_s['form_desc']=serialize($form_arr);
                    }else{
                        $data_s['form_desc']=$form_arr;
                    }
                    $ActivityObj->edit($data_s,$good_id);
                }
//-----------

              if($type==3||$type==4){
//  ↓↓↓↓↓↓↓↓↓↓ 冠名商家广告图片 ↓↓↓↓↓↓↓↓↓↓
//                $table_name=Buddha_Http_Input::getParameter('c');
                $table_name='activity';
                $savePath ='storage/'.$table_name.'/'.$good_id.'/';
                if(!file_exists(PATH_ROOT.$savePath)){
                    mkdir(PATH_ROOT.$savePath, 0777);
                }

                //冠名商家广告图片
                $MoreImage = array();
                $filebanner = Buddha_Http_Input::getParameter('filebanner');

                if(is_array($filebanner) and count($filebanner)){
                    foreach($filebanner as $k=>$perimg){
                        if(!empty($perimg)){
                            $now_pic = $perimg;
                            $imgurl= explode(',',$now_pic);
                            $base64_string = $imgurl[1];
                            if(!Buddha_Atom_File::checkStringIsBase64($base64_string)){//base64前缀检验
                                echo "不是base64格式";
                            }
                            if(!Buddha_Atom_File::checkBase64Img($now_pic)){//base64图片数据检测
                                echo "不是base64格式图片";
                            }
                            $output_file = $uid.'-'.$k.'-'.date('Ymdhis',time()). '.jpg';
                            $filePath =PATH_ROOT.$savePath.$output_file;
                            Buddha_Atom_File::base64contentToImg($filePath,$base64_string);
//临时关闭                   Buddha_Atom_File::resolveImageForRotate($filePath,NULL);//解决图片翻转
                            $MoreImage[] = $savePath.$output_file;
                        }
                    }
                }
                if(is_array($MoreImage) and count($MoreImage)>0){
                    $MoregalleryObj =new Moregallery();
                    $moregallery_id=$MoregalleryObj->pcaddimage('file_title',$MoreImage,$good_id,$table_name,$uid,$cooshopid_title);

                }
              }
// ↑↑↑↑↑↑↑↑↑↑↑↑ 冠名商家广告图片 ↑↑↑↑↑↑↑↑↑↑↑↑

//  ↓↓↓↓↓↓↓↓↓↓ 添加封面照片 ↓↓↓↓↓↓↓↓↓↓
                $MoreImageFile= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . 'storage/'.$table_name.'/'.$good_id.'/', array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('file') ->getAllReturnArray();

                if(is_array($MoreImageFile) and count($MoreImageFile)>0){
                    $MoregalleryObj =new Moregallery();
                    $moregallery_id = $MoregalleryObj->pcaddimage('file',$MoreImageFile, $good_id,$table_name,$uid,$cooshopid_title);

                    if(count($moregallery_id)>0){
                        $num= $ActivityObj->setFirstGalleryImgToSupply($good_id,$table_name,$webfield='file');
                        if($num==0){
                            $datas['err']=5;
                        }
                    }else{
                        $datas['err']=6;
                    }
                }

// ↑↑↑↑↑↑↑↑↑↑↑↑ 添加封面照片 ↑↑↑↑↑↑↑↑↑↑↑↑

                if($desc){  // 富文本编辑器图片处理
                    $MoregalleryObj=new Moregallery();
                    $field='desc';
                    $saveData_desc = $MoregalleryObj->base_upload($desc,$good_id,$table_name,$field);
                    $saveData_desc = str_replace(PATH_ROOT,'/', $saveData_desc);
                    if ($type==3||$type==4){
                        if($prize){  // 富文本编辑器图片处理
                            $prizefield='prize';
                            $saveData_prize = $MoregalleryObj->base_upload($prize,$good_id,$table_name,$prizefield);
                            $saveData_prize = str_replace(PATH_ROOT,'/', $saveData_prize);
                            $details['prize'] = $saveData_prize;
                        }
                    }
                    $details['desc'] = $saveData_desc;
                    $ActivityObj->edit($details,$good_id);
                }
                //合作商家添加
                if(($type==2 ||$type==3||$type==4) && !empty($coo_shopid)){
                    $ActivitycooperationObj=new Activitycooperation();
                    if($type==2||$type==3||$type==4){
//                        $datacoo['u_id']=$uid;
//                        $datacoo['act_id']=$good_id;
//                        $datacoo['add_time']=time();
//                        $datacoo['sore_time']=time();
//                        $datacoo['sore']=1;
                        if(($type==1||$type==2|| ($type==3&& $v_type==1) )){
                            array_push($coo_shopid,$shopid);//先把自己的店铺添加到合作商家中、
                            array_push($coo_shopname,$shopname);//先把自己的店铺添加到合作商家中、
                        }
                        $coonum[]=$ActivitycooperationObj->cooadd($coo_shopid,$good_id,$coo_shopname);
//                        foreach($coo_shopid as $k=>$v){
//                            $datacoo['shop_name']=$coo_shopname[$k];
//                            $datacoo['shop_id']=$v;
//                            if(!empty($v)){
//                                $coonum[]=$ActivitycooperationObj->add($datacoo);
//                            }
//                        }
                    }
                }
                //$remote为1表示发布异地产品添加订单
                if($is_remote==1){
                    $OrderObj=new Order();
                    $datas=$OrderObj->Remote_order($shopid,$uid,$good_id,$level,$table_name);
                }else{
                    $datas['isok']='true';
                    $datas['data']=$title.'添加成功';
                    $datas['url']='index.php?a=index&c='.$c.'&type='.$type;
                }
            }else{
                $datas['err']=1;
                $datas['isok']='false';
                $datas['data']=$title.'添加失败!';
                $datas['url']='index.php?a=index&c=activity&type='.$type;
            }
            //err: 1 表示活动添加失败！；2表示封面照添加失败；3表示封面护照在活动中的字段更新失败;
            Buddha_Http_Output::makeJson($datas);
        }

        $getshoplistOption = $ShopObj->getShoplistOption($uid,0);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('typeid', $typeid);
        $this->smarty->assign('c', $c);
        $this->smarty->assign('contr', 'add');
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }





    public function edit()
    {

        $ActivityObj = new Activity();
        list($uid,$UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        if($UserInfo['groupid']==1){
            $t='business';
        }elseif($UserInfo['groupid']==2){
            $t='agent';
        }elseif($UserInfo['groupid']==3){
            $t='partner';
        }elseif($UserInfo['groupid']==4){
            $t='user';
        }

        $view = Buddha_Http_Input::getParameter('type')?Buddha_Http_Input::getParameter('type'):0;

        if($view==0)
        {
            $title='全部';
        }else if($view==1)
        {
            $title='个体';
        }else if($view==2)
        {
            $title='联合';
        }else if($view==3)
        {
            $title='投票';
        }else if($view==4)
        {
            $title='点赞';
        }

        $this->smarty->assign('title', $title);

        $this->smarty->assign('view', $view);


        if(empty($uid))
        {
            Buddha_Http_Head::redirectofmobile('请登录后再更改！','index.php?a=login&c=account',2);
            exit;
        }else{
            if($UserInfo['groupid']!=1){//用户注册时的角色
                if($_SESSION['groupid']!=1){//当前身份角色
                    Buddha_Http_Head::redirectofmobile('请到个人中心切换到商家角色后发布活动！',"/{$t}/index.php?a=index&c={$t}",2);
                    exit;
                }
            }
        }
        $id=(int)Buddha_Http_Input::getParameter('id');
        $c=$this->c;
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c='.$c,2);
        }
        $activity=$ActivityObj->getSingleFiledValues('',"id={$id} and user_id={$uid}");

        if(!$activity){
            Buddha_Http_Head::redirectofmobile('您没有权限查看或商品已删除！','index.php?a=index&c='.$c,2);
        }
       $ActivitycooperationObj=new Activitycooperation();
       $ActivityapplicationObj=new Activityapplication();
       $app= $ActivityapplicationObj->countRecords("ac_id=$id");//活动报名表
       $coo= $ActivitycooperationObj->countRecords("act_id={$id} and is_sure=1");//活动合作商家(并且通过了商家的审核的)

        if($app>0){
            Buddha_Http_Head::redirectofmobile('对不起该活动已经有人报名了不能再更改了！','index.php?a=index&c='.$c,2);
            exit;
        }

        if($coo>0){
            Buddha_Http_Head::redirectofmobile('对不起该活动已经有商家报名了不能再更改了！','index.php?a=index&c='.$c,2);
            exit;
        }

        $ShopObj = new Shop();
        $getshoplistOption = $ShopObj->getShoplistOption($uid,$activity['shop_id']);
        $activity['form_desc'] = unserialize($activity['form_desc']);
        ///
        $type=Buddha_Http_Input::getParameter('type');                 //类型
        $name=Buddha_Http_Input::getParameter('name');                 //活动名称
        $start_date=Buddha_Http_Input::getParameter('start_date');     //活动开始时间
        $end_date=Buddha_Http_Input::getParameter('end_date');         //活动结束时间


        $sign_start_time=Buddha_Http_Input::getParameter('v_start_date');  //报名开始时间
        $sign_end_time=Buddha_Http_Input::getParameter('v_end_date');      //报名结束时间

        $buddhastatus=Buddha_Http_Input::getParameter('buddhastatus'); //是否上架
        $brief=Buddha_Http_Input::getParameter('brief');               //简述
        $desc=Buddha_Http_Input::getParameter('desc');                 //详情

        $shop_id=Buddha_Http_Input::getParameter('shop_id');//发布商家Id
        $shop_name=Buddha_Http_Input::getParameter('shop_name');//发布商家名称

        //商品异地发布
        $is_remote=Buddha_Http_Input::getParameter('is_remote');
        $regionstr=Buddha_Http_Input::getParameter('regionstr');

        if($type==1||$type==2){
            $text=Buddha_Http_Input::getParameter('text');          //多行
            $txt=Buddha_Http_Input::getParameter('txt');            //单行
            $radio=Buddha_Http_Input::getParameter('radio');        //单选的内容
            $radioname=Buddha_Http_Input::getParameter('radioname');//单选的标题
            $checkname=Buddha_Http_Input::getParameter('checkname');//多选的标题
            $checkbox=Buddha_Http_Input::getParameter('checkbox');  //多选的内容
            $form=Buddha_Http_Input::getParameter('form_val');      //多选的内容
            $address=Buddha_Http_Input::getParameter('address');    //详细地址
        }
        if($type==2 || $type==3 || $type==4){
            $coo_shopid=Buddha_Http_Input::getParameter('cooshopid');//合作商家Id
            $coo_shopname=Buddha_Http_Input::getParameter('cooshopname');//合作商家名称
        }
        if($type==3 || $type==4){
            $cooshopname_title=Buddha_Http_Input::getParameter('cooshopname_title');//冠名商家名称
            $cooshopid_title=Buddha_Http_Input::getParameter('cooshopid_title');//冠名商家ID
            $prize=Buddha_Http_Input::getParameter('prize');         //奖品
            $v_type=Buddha_Http_Input::getParameter('v_type');              //投票或的合作对象类型
        }

        if(Buddha_Http_Input::isPost()){
            $ActivityObj= new Activity();
            $MoregalleryObj =new Moregallery();
            $data=array();
            if($type==1 || $type==2){
                $data['address']=$address;
            }
            $CommonObj= new Common();
            $data['name']=$name;
            $data['type']=$type;
            $data['add_time']=time();
            $data['user_id']=$uid;
            $data['number']=$CommonObj->GeneratingNumber();
            $data['start_date'] = strtotime($start_date);
            $data['end_date'] = strtotime($end_date);
            $data['sign_start_time'] = strtotime($sign_start_time);
            $data['sign_end_time'] = strtotime($sign_end_time);
            $data['brief'] = $brief;
            if($buddhastatus==''){//上架
                $data['buddhastatus']=1;
            }else{
                $data['buddhastatus']=$buddhastatus;
            }
            $data['shop_id']=$shop_id;
            $data['shop_name']=$shop_name;
            if($type==3||$type==4){
                $data['vode_type']=$v_type;
            }
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
            $ActivityObj->edit($data,$id);
            $datas = array();
            if($ActivityObj){
                $table_name=Buddha_Http_Input::getParameter('c');
//                $table_name='activity';

//-----------
                if($type==1||$type==2){
                    $form_arr=$ActivityObj->if_where($radioname,$checkname,$text,$txt,$checkbox,$radio,$table_name,$id);//判断是否符合条件
                    if(!empty($form_arr)){
                        $data_s['form_desc']=serialize($form_arr);
                    }else{
                        $data_s['form_desc']=$form_arr;
                    }
                    $ActivityObj->edit($data_s,$id);
                }
//-----------
//冠名商家广告图片
                $savePath ='storage/'.$table_name.'/'.$id.'/';
                if(!file_exists(PATH_ROOT.$savePath)){
                    mkdir(PATH_ROOT.$savePath, 0777);
                }
                $MoreImage = array();
                $filebanner = Buddha_Http_Input::getParameter('filebanner');
                if(is_array($filebanner) and count($filebanner)){
                    foreach($filebanner as $k=>$perimg){
                        if(!empty($perimg)){
                            $now_pic = $perimg;
                            $imgurl= explode(',',$now_pic);
                            $base64_string = $imgurl[1];
                            if(!Buddha_Atom_File::checkStringIsBase64($base64_string)){//base64前缀检验
                                echo "不是base64格式";
                            }
                            if(!Buddha_Atom_File::checkBase64Img($now_pic)){//base64图片数据检测
                                echo "不是base64格式图片";
                            }
                            $output_file = $uid.'-'.$k.'-'.date('Ymdhis',time()). '.jpg';
                            $filePath =PATH_ROOT.$savePath.$output_file;
                            Buddha_Atom_File::base64contentToImg($filePath,$base64_string);
                            Buddha_Atom_File::resolveImageForRotate($filePath,NULL);//解决图片翻转
                            $MoreImage[] = $savePath.$output_file;
                        }
                    }
                }
                if(is_array($MoreImage) and count($MoreImage)>0){

                    $moregallery_id = $MoregalleryObj->pcaddimage($webfield='file_title',$MoreImage,$id,$table_name,$uid,$cooshopid_title );
                }

//  ↓↓↓↓↓↓↓↓↓↓ 添加封面照片 ↓↓↓↓↓↓↓↓↓↓
                $MoreImageFile= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . 'storage/'.$table_name.'/'.$id.'/', array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('file') ->getAllReturnArray();
                if(is_array($MoreImageFile) and count($MoreImageFile)>0 and $MoreImageFile[0]!='0'){
                    $moregallery_id=$MoregalleryObj->pcaddimage('file',$MoreImageFile, $id,$table_name,$uid);
                    if(count($moregallery_id)>0){
                        $num= $ActivityObj->setFirstGalleryImgToSupply($id,$table_name,$webfield='file');
                        if($num==0){
                            $datas['err']=5;
                        }
                    }else{
                        $datas['err']=6;//为空
                    }
                }
// ↑↑↑↑↑↑↑↑↑↑↑↑ 添加封面照片 ↑↑↑↑↑↑↑↑↑↑↑↑


//  ↓↓↓↓↓↓↓↓↓↓ 奖品和详情 ↓↓↓↓↓↓↓↓↓↓
        //  ↓↓↓↓↓↓↓↓↓↓ 详情 ↓↓↓↓↓↓↓↓↓↓
                if($desc){//富文本编辑器图片处理
                    $dirs=PATH_ROOT."storage/quill/activity/desc/{$id}/";
                    if(is_dir($dirs)){//检查is_dir目录是是否存在
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
                    $field='desc';

//                    $MoregalleryObj->base_upload()
                    $saveData = $MoregalleryObj->base_upload($desc,$id,$table_name,$field);//base64图片上传
                    if($saveData){
                        $saveData = str_replace(PATH_ROOT,'/', $saveData);//替换
                        $desc_details['desc'] = $saveData;
                    }else{
                        $desc_details['desc'] = $desc;
                    }
                    $ActivityObj->edit($desc_details,$id);
                }
        // ↑↑↑↑↑↑↑↑↑↑↑↑ 详情 ↑↑↑↑↑↑↑↑↑↑↑↑
        //   ↓↓↓↓↓↓↓↓↓↓ 奖品↓↓↓↓↓↓↓↓↓↓
                if($prize){//富文本编辑器图片处理
                    $prizedirs=PATH_ROOT."storage/quill/prize/activity/{$id}/";
                    if(is_dir($prizedirs)){//检查is_dir目录是是否存在
                        if ($dh = opendir($prizedirs)){//opendir打开一个目录，读取它的内容，然后关闭：
                            while (($file = readdir($dh)) !== false){// 打开一个目录，读取它的内容，然后关闭：
                                //$filePath = $dirs.$file;
                                if(!strstr($prize,$file) and $file != '.' and $file !='..'){
                                    @unlink($prizedirs.$file);//删除修改后的图片
                                    /*echo $file;
                                    exit;*/
                                }
                            }
                        }
                        //$GalleryObj->deleteDir($dirs);
                    }
                    $field='prize';
                    $prizesaveData = $MoregalleryObj->base_upload($prize,$id,$table_name,$field);//base64图片上传
                    if($prizesaveData){
                        $prizesaveData = str_replace(PATH_ROOT,'/', $prizesaveData);//替换
                        $prize_details['prize'] = $prizesaveData;
                    }else{
                        $prize_details['prize'] = $prize;
                    }
                    $ActivityObj->edit($prize_details,$id);
                }


        // ↑↑↑↑↑↑↑↑↑↑↑↑ 奖品 ↑↑↑↑↑↑↑↑↑↑↑
// ↑↑↑↑↑↑↑↑↑↑↑↑ 奖品和详情 ↑↑↑↑↑↑↑↑↑↑↑↑

//   ↓↓↓↓↓↓↓↓↓↓ 合作商家添加 ↓↓↓↓↓↓↓↓↓↓
                if(($type==2 ||$type==3||$type==4) && !empty($coo_shopid)){
                    $ActivitycooperationObj=new Activitycooperation();
                    if($type==2||$type==3||$type==4){
//                        $datacoo['u_id']=$uid;
//                        $datacoo['act_id']=$id;
//                        $datacoo['add_time']=time();
//                        $datacoo['sore_time']=time();
//                        $datacoo['sore']=1;
                        $coonum[]=$ActivitycooperationObj->cooadd($coo_shopid,$id,$coo_shopname);
//                        foreach($coo_shopid as $k=>$v){
//                            $datacoo['shop_name']=$coo_shopname[$k];
//                            $datacoo['shop_id']=$v;
//                            if(!empty($v)){
//                                $coonum[]=$ActivitycooperationObj->add($datacoo);
//                            }
//                        }
                    }
                }
// ↑↑↑↑↑↑↑↑↑↑↑↑ 合作商家添加 ↑↑↑↑↑↑↑↑↑↑↑↑
                //$remote为1表示发布异地产品添加订单
                if($is_remote==1){
                    $OrderObj =new Order();
                    $datas=$OrderObj->Remote_order($shop_id,$uid,$id,$level,$table_name);
                }else{
                    $datas['isok']='true';
                    $datas['data']=$title.'活动编辑成功！';
                    $datas['url']='index.php?a=index&c='.$c.'&type='.$type;
                }
            }else{
                $datas['err']=1;
                $datas['isok']='false';
                $datas['data']=$title.'活动编辑失败!';
                $datas['url']='index.php?a=index&c=activity&type='.$type;
            }
            //err: 1 表示活动添加失败！；2表示封面照添加失败；3表示封面护照在活动中的字段更新失败;
            Buddha_Http_Output::makeJson($datas);
        }
        $coo =  $ActivitycooperationObj->getFiledValues('',"act_id ={$id} AND u_id='{$uid}'");//查询合作商家
//        print_r($coo);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('act', $activity);
//print_r($activity);
        $sql ="select m.id,m.goods_thumb,m.imgkey,m.shop_id,s.name 
               from {$this->prefix}moregallery as m 
               left join {$this->prefix}shop as s 
               on s.id = m.shop_id 
               where goods_id ={$id} and tablename='{$c}' and webfield='file_title' 
               order by m.imgkey desc";//查询冠名商家
        $More = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $this->smarty->assign('More', $More);
        $this->smarty->assign('coo', $coo);
        $this->smarty->assign('id', $id);
        $this->smarty->assign('c', $c);
        $this->smarty->assign('contr', 'edit');
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    //图片删除
    public function delimage(){
        $ActivityObj=new Activity();
        $MoregalleryObj =new Moregallery();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $table_name=Buddha_Http_Input::getParameter('c');
        $imgWhere="goods_id={$id} and tablename='{$table_name}'";
        $gimages=$MoregalleryObj->getSingleFiledValues(array('id','goods_thumb','goods_img','goods_large','sourcepic'),$imgWhere);
        $thumimg=$MoregalleryObj->del_image($id,$gimages);

        $data['activity_thumb']='';$data['activity_img']='';$data['activity_large']='';$data['sourcepic']='';
        $ActivityObj->edit($data,$id);

        Buddha_Http_Output::makeJson($thumimg);
    }

    //报名查询
    public function signup(){
         $ActivityObj=new Activity();
         $datas=$ActivityObj->signup(1);
         Buddha_Http_Output::makeJson($datas);
     }

    //商家查询报名列表和提交备注
    public function applform(){
        $ActivityObj=new Activity();
        $ActivityapplicationObj=new Activityapplication();
        $source=1;
        $state=Buddha_Http_Input::getParameter('state');
        $id=Buddha_Http_Input::getParameter('id');
        if(Buddha_Http_Input::isPost()) {
            foreach($state as  $k=>$v){
                if(!empty($v)){
                    $data['state']=$v;
                    $good_id[] = $ActivityapplicationObj->edit($data,$k);
                }
            }
            if(count($good_id)>0){
                $datas['err']=1;
                $datas['isok']='true';
                $datas['data']='备注添加成功!';
            }else{
                $datas['err']=1;
                $datas['isok']='true';
                $datas['data']='备注添加失败!';
            }
            Buddha_Http_Output::makeJson($datas);
        }
        $datas=$ActivityObj->signup(1);
        $this->smarty->assign('datas', $datas);
        $this->smarty->assign('id', $id);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
//删除
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
    //合作商家列表：
    public function ajaxshop(){
        $RegionObj=new Region();
        $locdata = $RegionObj->getLocationDataFromCookie();//根据用户当前的地区查询
        $shop=Buddha_Http_Input::getParameter('shop')?Buddha_Http_Input::getParameter('shop'):0;//搜索关键字
        $liid=Buddha_Http_Input::getParameter('liid');//行号（代表第几个合作的）
        $err=Buddha_Http_Input::getParameter('err');//代表是：0合作 ；1冠名商家
        $typeid=Buddha_Http_Input::getParameter('typeid')?Buddha_Http_Input::getParameter('typeid'):0;//活动类型
        $votype=Buddha_Http_Input::getParameter('votype')?Buddha_Http_Input::getParameter('votype'):0;//投票合作对象类型
        $shop_id=Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;//发布店铺ID
        if(($typeid==3||$typeid==4)&&($votype==2||$votype==3)){
            if($votype==2){//合作对象：1 商家 ；2个人；3产品
                $where=" realname like '%{$shop}%' and state=1 and isdel=0 {$locdata['sql']} limit 100";//最大只显示100家（相同输入的搜索信息）
                $UserObj=new User();
                $list=$UserObj->getFiledValues(array('id','mobile','realname','logo'),$where);
                foreach ($list as  $k=>$v){
                    $qs=substr($v['mobile'],0,3);
                    $hs=substr($v['mobile'],-3);
                    $list[$k]['mobile']=$qs.'*****'.$hs;
                    $list[$k]['realname']=mb_substr($v['realname'],0,2).'**';
                    if(empty($v['logo'])){
                        $list[$k]['logo']='style/images/im.png';
                    }
                }
            }elseif($votype==3){//合作对象：1 商家 ；2个人；3产品
                $where=" shop_id={$shop_id} and  goods_name like '%{$shop}%' or goods_sn like '%{$shop}%' and is_sure=1  and isdel=0   limit 100";//最大只显示100家（相同输入的搜索信息）
                $SupplyObj=new Supply();
                $list=$SupplyObj->getFiledValues(array('id','goods_name','goods_sn','goods_thumb','goods_brief'),$where);
                foreach ($list as  $k=>$v){
                    if(!empty($v['goods_brief'])&&$v['goods_brief']!=null){
                        $list[$k]['goods_brief']=substr($v['goods_brief'],0,10) ;
                    }else{
                        $list[$k]['goods_brief']='';
                    }
                    $list[$k]['goods_name']=mb_substr($v['goods_name'],0,18).'...' ;
                }
            }
        }else{
            $where=" name like '%{$shop}%' or number like '%{$shop}%' and is_sure=1 and state=0  and isdel=0  {$locdata['sql']}  limit 100";//最大只显示100家（相同输入的搜索信息）
            $ShopObj=new Shop();
            $list=$ShopObj->getFiledValues(array('id','name','realname','number','specticloc'),$where);
        }
        if(count($list)>0){
            $datas['isok']='true';
            $datas['liid']=$liid;
            $datas['data']=$list;
            $datas['err']=$err;
            $datas['votype']=$votype;//投票合作对象类型
            $datas['typeid']=$typeid;//活动类型
        }else{
            $datas['isok']='false';
            $datas['data']=1;
        }
        Buddha_Http_Output::makeJson($datas);
    }



//合作商家 删除
    public function delcoo(){
        $id=Buddha_Http_Input::getParameter('id');
        $Activitycooperation=new Activitycooperation();
        $num=$Activitycooperation->del($id);
        if(count($num)>0){
            $datas['isok']='true';
            $datas['data']=$num;
        }else{
            $datas['isok']='false';
            $datas['data']=1;
        }
        Buddha_Http_Output::makeJson($datas);
    }

    //合作对象列表
    public function cooshop (){
        $UserObj =new User();
        $UserObj->is_sign();
        $id = (int)Buddha_Http_Input::getParameter('id');
        $ActivitycooperationObj = new Activitycooperation();
        $acoonum = $ActivitycooperationObj->countRecords("act_id={$id}");
//        $ShopObj = new Shop();
//        if ($acoonum) {
//            $acoo = $ActivitycooperationObj->getFiledValues('',"act_id={$id}");
//            foreach ($acoo as $k => $v) {//查询店铺名称和logo
//                $shop = $ShopObj->getSingleFiledValues(array('name', 'small'), "id={$v['shop_id']} and is_sure=1 and state=0");
//                $acoo[$k]['shop_name'] = mb_substr($shop['name'],0,10) . '...';
//                $acoo[$k]['shop_logo'] = $shop['small'];
//            }
//            $aco['aco'] = $acoo;
//            $aco['surl'] = $ShopObj->shop_url();
//            if($aco['sore']==1){//只有 时 商家申请时才能查看
//                $aco['cdurl'] = 'index.php?a=cooshopdetal&c=activity&id=';//合作商家详情()
//            }
//        } else {
//            $aco = '';
//        }

        $ActivitycooperationObj=new Activitycooperation();
        $coo=$ActivitycooperationObj-> vodelist_ajax();

        foreach ($coo as $k => $v) {
            if($v['sore']==1){//只有 时 商家申请时才能查看
                $coo[$k]['cdurl'] = 'index.php?a=cooshopdetal&c=activity&id=';//合作商家详情()
            }
            $coo[$k]['shop_name'] = mb_substr($v['shop_name'],0,5) . '...';
        }
        $aco['aco']=$coo;

//        print_r($aco);

        $this->smarty->assign('aco', $aco);
        $this->smarty->assign('actid', $id);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }

    public function cooshopdetal (){
        $UserObj =new User();
        $UserObj->is_sign();
        $id = (int)Buddha_Http_Input::getParameter('id')?(int)Buddha_Http_Input::getParameter('id'):0;
        $actid = (int)Buddha_Http_Input::getParameter('actid');//活动ID
        $ActivitycooperationObj = new Activitycooperation();
        $acoonum = $ActivitycooperationObj->countRecords("id={$id}");
        $ShopObj = new Shop();
        if ($acoonum) {
            $aco = $ActivitycooperationObj->getSingleFiledValues('',"id={$id}");
            $shop = $ShopObj->getSingleFiledValues(array('name', 'small','realname','mobile'), "id={$aco['shop_id']} and is_sure=1 and state=0");
            if(!$aco['u_name']){ $aco['u_name'] =$shop['realname'];}
            if(!$aco['u_phone']){ $aco['u_phone'] = $shop['mobile'];}
            $aco['shop_name'] = $shop['name'];
            $aco['shop_logo'] = $shop['small'];
            $aco['surl'] = $ShopObj->shop_url();
        }
        $this->smarty->assign('aco', $aco);
        $this->smarty->assign('acid', $actid);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }

    //发起人审核申请商家
    public function auditing(){
        $UserObj=new User();
        $UserObj->is_sign();
        $cid = (int)Buddha_Http_Input::getParameter('cid');//合作商家列表Id
        $sure = (int)Buddha_Http_Input::getParameter('sure');
        $state =Buddha_Http_Input::getParameter('state');
        $ActivitycooperationObj=new Activitycooperation();
        if($sure==0){
            $data['is_sure']=4;
            $data['sure']=4;
        }else{
            $data['is_sure']=$sure;
            $data['sure']=$sure;
        }
        $data['sure_time']=time();
        $data['state']=$state;
        $num= $ActivitycooperationObj->edit($data,$cid);
        if(count($num)>0){
            $datas['isok']='true';
            $datas['data']='审核成功！';
        }else{
            $datas['isok']='false';
            $datas['data']='审核失败！';
        }
        Buddha_Http_Output::makeJson($datas);
    }

    function applformdetails(){
        $id = (int)Buddha_Http_Input::getParameter('id')?(int)Buddha_Http_Input::getParameter('id'):0;//合作商家列表Id
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=activity',2);
        }
       $activityapplicationObj=new Activityapplication();
        $act= $activityapplicationObj->fetch($id);
        $this->smarty->assign('act', $act);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

//编辑时删除合作商家
    function  del_cooshop(){
        $id = (int)Buddha_Http_Input::getParameter('id')?(int)Buddha_Http_Input::getParameter('id'):0;//合作商家列表Id
        $ActivitycooperationObj= new Activitycooperation();
        $num= $ActivitycooperationObj->del($id);
        if($num){
            $datas['isok']='true';
        }else{
            $datas['isok']='false';
        }
        Buddha_Http_Output::makeJson($datas);
    }

//编辑时删除合作商家
    function  del_moreshop(){
        $id = (int)Buddha_Http_Input::getParameter('id')?(int)Buddha_Http_Input::getParameter('id'):0;//合作商家列表Id
        $MoregalleryObj= new Moregallery();
        $CommonObj= new Common();
        $num= $CommonObj->delGalleryimage($id,$MoregalleryObj);
        if($num){
            $datas['isok']='true';
        }else{
            $datas['isok']='false';
        }
        Buddha_Http_Output::makeJson($datas);
    }


//编辑时删除封面
    function  del_file(){
        $id = (int)Buddha_Http_Input::getParameter('id')?(int)Buddha_Http_Input::getParameter('id'):0;//合作商家列表Id
        $sql ="select m.id
                from {$this->prefix}activity as a 
                INNER join {$this->prefix}moregallery as m
                on a.id= m.goods_id 
                where a.id={$id} and webfield='file'";
        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $MoregalleryObj= new Moregallery();
        $CommonObj= new Common();
            $data['activity_thumb']='';
            $data['activity_img']='';
            $data['activity_large']='';
            $data['sourcepic']='';
        $ActivityObj=new Activity();
        $ActivityObj->edit($data,$id);
        $num= $CommonObj->delGalleryimage($list[0]['id'],$MoregalleryObj);
        if($num){
            $datas['isok']='true';
        }else{
            $datas['isok']='false';
        }
        Buddha_Http_Output::makeJson($datas);
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

