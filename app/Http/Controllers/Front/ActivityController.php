<?php

/**
 * Class ActivityController
 */
class ActivityController extends Buddha_App_Action
{
    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
    }
    public function mylist()
    {
        header("Content-type: text/html; charset=utf-8");
        $ActivityObj =new Activity();
        $ActivityObj->mylist(1);

        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }
    //单商家报名和问卷调查
    public function ajaxmylist(){
        $ActivityObj=new Activity();
        $c=$this->c;
        $datas=$ActivityObj->ajaxmylist(1);
        Buddha_Http_Output::makeJson($datas);
    }

    //报名查询
     function signup(){
         $ActivityObj=new Activity();
         $datas=$ActivityObj->signup();
         Buddha_Http_Output::makeJson($datas);
     }
////活动列表
    function index()
    {
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $UserObj=new User();
        $ActivityObj=new Activity();
        $locdata = $RegionObj->getLocationDataFromCookie();
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') : 15;
        $act=Buddha_Http_Input::getParameter('act');
        $view = Buddha_Http_Input::getParameter('view')?Buddha_Http_Input::getParameter('view'):0;//0全部，1单家，2，多家，3信息；4投票
        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id') : 0;

        $keyword = Buddha_Http_Input::getParameter('keyword');

        if($view==1)
        {//0全部，1单家，2，多家，3信息
            $title = '个体';
        }elseif($view==2)
        {//0全部，1单家，2，多家，3信息
            $title = '联合';
        }elseif($view==4)
        {//0全部，1单家，2，多家，3信息
            $title = '投票';
        }


        $this->smarty->assign('title', $title);
        $this->smarty->assign('view', $view);



        if($act=='list')
        {
            $time=time();

            $where = " isdel=0 and is_sure=1 and buddhastatus=0  and {$time}<=`end_date`  {$locdata['sql']}";

            $orderby = "";
            if(Buddha_Atom_String::isValidString($shop_id))
            {
                $where .= "AND shop_id='{$shop_id}'";
            }else{
                $orderby = " group by shop_id ";
            }
            $orderby .= " order by add_time DESC ";
            if ($keyword) {
                $where .= " and (name like '%$keyword%' or number like '%$keyword%') ";
            }


            if($view==1){//0全部，1单家，2，多家，3信息
                $where .= ' and type=1 ';
            }elseif($view==2){//0全部，1单家，2，多家，3信息
                $where .= ' and type=2 ';
            }elseif($view==4){//0全部，1单家，2，多家，3信息
                $where .= ' and type=3 ';
            }


            $RegionObj = new Region();
            $locdata = $RegionObj->getLocationDataFromCookie();

            $where.= " {$locdata['sql']}  ";
            $fields = array('id', 'shop_id', 'user_id', 'name', 'activity_thumb','address','brief','shop_id','type');

            $Express=0;//表示没有第一页

            if($view==3){
                $fields = array('id', 'shop_id', 'user_id', 'name', 'singleinformation_thumb','address','brief','shop_id');
                $SingleinformationObj=new Singleinformation();
                $where=$SingleinformationObj->act_public_where(1);
                $fields = array('id', 'shop_id', 'user_id', 'name', 'singleinformation_thumb','brief','shop_id');
                $list = $this->db->getFiledValues($fields, $this->prefix . 'singleinformation', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));
                $Express=1;//表示第一页
            }else{
                $list = $this->db->getFiledValues($fields, $this->prefix . 'activity', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));
                $Express=1;//表示表示第一页
            }
            if(empty($keyword)){
                if(count($list)==0&&($view==0)&&$Express==0){//并且是view=0 点击得是全部  并且是不是搜索  并且表示没有第一页
                    $list = $ActivityObj->getFiledValues($fields, "id in(1) order by  add_time DESC");//没有数据显示默认
                }
            }
            foreach ($list as $k => $v) {
                if ($v['shop_id'] != '0') {
                    $Db_shop = $ShopObj->getSingleFiledValues(array('name', 'specticloc', 'lng', 'lat'), "id='{$v['shop_id']}'");
                    $name = $Db_shop['name'];
                    if ($v['address']) {
                        $roadfullname = $v['address'];
                    } else {
                        $roadfullname = $Db_shop['address'];
                    }
                }
                $brief=mb_substr($v['brief'],0,30,'utf-8').'...';
                $vname=mb_substr($v['name'],0,10,'utf-8');
                $name=mb_substr($name,0,5,'utf-8');
                $roadfullname=mb_substr($roadfullname,0,5,'utf-8');

                if($view==3)
                {
                    $ac='singleinformation';
                    $demand_thumb = $v['singleinformation_thumb'];
                }else{
                    $ac=$this->c;
                    $demand_thumb = $v['activity_thumb'];
                }
                if($v['type']==3)
                {
                    $a='vodelist';
                }else if($v['type']==1||$v['type']==2)
                {
                    $a='mylist';
                }
                if($view==3){
                    $a='mylist';
                }
                $lease[] = array(
                    'id' => $v['id'],
                    'name' => $vname,
                    'brief' => $brief,
                    'shop_name' => $name,
                    'roadfullname' => $roadfullname,
                    'demand_thumb' => $demand_thumb,
                    'c' => $ac,
                    'a' => $a,
                );
            }

            $CommonObj=new Common();
            $masg= $CommonObj-> page_where($page,$list,$pagesize);

            $data = array();
            if ($lease) {
                $data['isok'] = 'true';
                $data['list'] = $lease;
                $data['data'] = $masg;
            } else {
                $data['isok'] = 'false';
                $data['list'] = '';
                $data['data'] = $masg;
            }
            Buddha_Http_Output::makeJson($data);
        }

        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }
    //申请加入活动的店铺查询
    public function ajaxcoo(){
        $uid = Buddha_Http_Cookie::getCookie('uid');
        $ShopObj=new Shop();
        $filed=array('id','name','number','realname','specticloc');
        $where=$ShopObj->shop_public_where();//需要加入地区
        if (empty($uid)) {//如果未登录：请登陆后或请输入活动名称或编号再操作
            $datas['isok'] = 'false';
            $datas['data'] = '请登陆并确认角色为商家后，再操作！';
        }else{
            list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
            //判断是否是商家角色
            $where .= " and user_id ={$uid} ";
            if ($_SESSION['groupid'] == 1) { //为商家
                $shoplist = $ShopObj->getFiledValues($filed, $where);
                if($shoplist){
                    $datas['isok'] = 'true';
                    $datas['data'] = $shoplist;
                }else{
                    $datas['isok'] = 'false';
                    $datas['data'] = '你还没有创建店铺快去创建吧！';
                }
            } else {
                if ($UserInfo['groupid'] == 1) {//为商家
                    $shoplist = $ShopObj->getFiledValues($filed, $where);
                    if($shoplist){
                        $datas['isok'] = 'true';
                        $datas['data'] = $shoplist;
                    }else{
                        $datas['isok'] = 'false';
                        $datas['data'] = '你还没有创建店铺快去创建吧！';
                    }
                } else {
                    $datas['isok'] = 'false';
                    $datas['data'] = '请切换角色为商家后再操作！！';
                }
            }
        }
        Buddha_Http_Output::makeJson($datas);
    }
    //申请加入活动的店铺的添加
    public function ajaxcoo_add(){
        $uid = Buddha_Http_Cookie::getCookie('uid');
        $sid=Buddha_Http_Input::getParameter('id')?(int)Buddha_Http_Input::getParameter('id'):0;
        $aid=Buddha_Http_Input::getParameter('aid')?(int)Buddha_Http_Input::getParameter('aid'):0;
        $shopname=Buddha_Http_Input::getParameter('name');
        if(!empty($sid)){
            // 判断该店铺是否已经参加了该活动（发动发起人已经确认和未确认）
            $ActivitycooperationObj=new Activitycooperation();
            $actwhere_coo=" `act_id`={$aid} and `shop_id`={$sid}";
            $count=$ActivitycooperationObj->countRecords($actwhere_coo);
            if($count>0){
                    $datas['isok'] = 'false';
                    $datas['data'] = '你已经申请该活动,请不要重复申请！';
            }else{
                $data['u_id']=$uid;
                $data['act_id']=$aid;
                $data['shop_id']=$sid;
                $data['shop_name']=$shopname;
                $data['sore']=1;
                $data['sore_time']=time();
                $data['add_time']=time();
                $num=$ActivitycooperationObj->add($data);
                if($num){
                    $ActivityObj=new Activity();
                    $NewsObj=new News();
                    $actwhere=$ActivityObj->act_public_where();
                    $actwhere.= " and id={$aid} ";
                    $Act=$ActivityObj->getSingleFiledValues(array('id','user_id','name','start_date','end_date','address'),$actwhere);
                    $datanew['u_id']=$Act['user_id'];
                    $datanew['soure_id']=$uid;
                    $datanew['shop_id']=$sid;
                    $datanew['shop_name']=$shopname;
                    $datanew['name']=$Act['name'].'活动参加申请';
                    $datanew['add_time']=time();
                    $datanew['is_act']=$aid;
                    $datanew['content']="我店({$shopname})申请参加从".date('Y-m-d H:i',$Act['start_date']).'—'.date('Y-m-d H:i',$Act['end_date'])."起在". $Act['address']."发起“{$Act['name']}”本地联合活动，如果同意，请通过，谢谢!";
                    $NewsObj->add($datanew);
                    $datas['isok'] = 'true';
                    $datas['data'] = '申请参加活动成功！';
                    $datas['cooid'] = $num;
                }else{
                    $datas['isok'] = 'false';
                    $datas['data'] = '申请参加活动失败！';
                }
            }
        }
        Buddha_Http_Output::makeJson($datas);
    }
    //多商家申请报名和问卷调查
    public function ajaxmylist_coo(){
        $ActivityObj=new Activity();
        $datas=$ActivityObj->ajaxmylist(1);
        Buddha_Http_Output::makeJson($datas);
    }
    /**
     *查询活动详情和奖品设置
     * */
    public function vodeprize(){
        $ActivityObj =new Activity();
        $ActivityObj->vodeprize();
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }
    /***活动投票详情******/
    public function vodelist(){
        $ActivityObj =new Activity();
        $c=$this->c;
        $ActivityObj->vodelist($c);
        $this->smarty->assign('c', $c);
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    /**
     *  首页活动投票：合作对象列表
    */
    public function vodelist_ajax()
    {
        $ActivitycooperationObj = new Activitycooperation();
        $list = $ActivitycooperationObj-> vodelist_ajax();

        $page = Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p'):0;
        $id = Buddha_Http_Input::getParameter('id')?(int)Buddha_Http_Input::getParameter('id'):0;
        $pagesize = 20;
        $ShopObj = new Shop();
        $CommonObj = new Common();
        $Nws = $CommonObj->page_where($page,$list,$pagesize);
        $ActivityObj = new Activity();
        $ActO = $ActivityObj->getSingleFiledValues(array('type','vode_type'),"id={$id}");
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

    public function voderanking()//排名(投票)
    {
        $ActivityObj =new Activity();
        $c=$this->c;
        $ActivityObj->voderanking($c);
        $this->smarty->assign('c', $c);
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    public function vodesign(){//报名(投票)
        $c=$this->c;
        $id= Buddha_Http_Input::getParameter('id')?Buddha_Http_Input::getParameter('id'):0;
        $uid = Buddha_Http_Cookie::getCookie('uid');
        if (empty($uid)) {   //判断该用户是否存在(是否登录或)
            Buddha_Http_Head::redirectofmobile('请登录后发布！','index.php?a=login&c=account',2);
            exit;
        }

        $name = Buddha_Http_Input::getParameter('name');
        $phone = Buddha_Http_Input::getParameter('phone');
        $massage = Buddha_Http_Input::getParameter('massage');
        $shop_name = Buddha_Http_Input::getParameter('shop_name');
        $shop_id = Buddha_Http_Input::getParameter('shop_id');

        if(Buddha_Http_Input::isPost()){
            $ActivitycooperationObj=new Activitycooperation();
            $count = $ActivitycooperationObj->countRecords("shop_id={$shop_id} and act_id={$id}");// //判断商家是否已经报名了
            if ($name == '' && $phone == '') {
                $uid = Buddha_Http_Cookie::getCookie('uid');
                if (empty($uid)) {   //判断该用户是否存在(是否登录或)
                    $datas['isok'] = 'false';
                    $datas['type'] = 1;
                    $datas['data'] = '你未登录或未注册，请登录后转换角色为商家再报名!';
                } else {
                    if ($count == 0) {
                        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
                        //判断用户姓名或联系方式是否为空
                        if(($UserInfo['mobile']==''&&($UserInfo['realname'])||$UserInfo['tel'])){
                            $datas['isok'] = 'false';
                            $datas['type'] = 2;
                            $datas['data'] = '你的用户信息不完整，为了更好为你服务请完整个人信息后再提交或填写姓名和手机号后提交！';
                        }else{
                            $data['u_id'] = $uid;
                            $data['act_id'] = $id;
                            $data['u_name'] = $UserInfo['realname'];
                            $data['message'] = $massage;
                            $data['shop_name'] = $shop_name;
                            $data['shop_id'] = $shop_id;
                            $data['add_time'] = time();
                            $data['sore'] = 1;
                            if($UserInfo['mobile']==''|| $UserInfo['tel']==''){
                                $datas['isok'] = 'false';
                                $datas['type'] = 2;
                                $datas['data'] = '你的用户信息不完整，为了更好为你服务请完整后再提交或填写姓名和手机号后提交！';
                            }elseif(!$UserInfo['mobile']){
                                $data['u_phone'] = $UserInfo['tel'];
                            }elseif($UserInfo['mobile']){
                                $data['u_phone'] = $UserInfo['mobile'];
                            }
                            $num = $ActivitycooperationObj->add($data);
                            if ($num) {
                                $datas['isok'] = 'true';
                                $datas['data'] = '报名成功！';
                                $datas['url'] = 'index.php?a=vodelist&c=activity&id='.$id;
                            } else {
                                $datas['isok'] = 'false';
                                $datas['data'] = '报名失败!';
                            }
                        }
                    } else {
                        $datas['isok'] = 'false';
                        $datas['data'] = '您已经报过名了,请不要重复报名！';
                    }
                }
            } else {
                list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
                if ($count == 0) {
                    $data['act_id'] = $id;
                    $data['u_id'] = $uid;
                    $data['u_name'] = $UserInfo['realname'];
                    $data['message'] = $massage;
                    $data['shop_name'] = $shop_name;
                    $data['shop_id'] = $shop_id;
                    $data['add_time'] = time();
                    $data['u_phone'] = $phone;
                    $data['sore'] = 1;
                    $num = $ActivitycooperationObj->add($data);
                    if ($num) {
                        $datas['isok'] = 'true';
                        $datas['data'] = '报名成功！';
                        $datas['url'] = 'index.php?a=vodelist&c=activity&id='.$id;
                    } else {
                        $datas['isok'] = 'false';
                        $datas['data'] = '报名失败!';
                    }
                } else {
                    $datas['isok'] = 'false';
                    $datas['data'] = '您已经报过名了,请不要重复报名！';
                }
            }
            Buddha_Http_Output::makeJson($datas);
        }

        $ShopObj=new Shop();
        $getshoplistOption=$ShopObj->getShoplistOption($uid,0);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);

        $this->smarty->assign('c', $c); $this->smarty->assign('id', $id);
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    /**
     * 申请成为合作商家:店铺是否已经申请成为了合作商家了
     */
   public function  vodeajax_shop(){
       $id= Buddha_Http_Input::getParameter('id')?Buddha_Http_Input::getParameter('id'):0;
       $shop_id= Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;
       $where="act_id={$id} and shop_id={$shop_id}";
       $ActivitycooperationObj=new Activitycooperation();

       $count= $ActivitycooperationObj->countRecords($where);
        if($count){//已经该店铺报名了
            $datas['isok'] = 'false';
        }else{
            $datas['isok'] = 'true';
        }
       Buddha_Http_Output::makeJson($datas);
   }

    /**
    * 首页活动投票：投票
    * */
    public function ajaxvote(){
        $id= Buddha_Http_Input::getParameter('id')?Buddha_Http_Input::getParameter('id'):0;//活动ID

        $shop_id= Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;//店铺ID
        $ActivitycooperationObj=new Activitycooperation();

        $VodetimeObj=new Vodetime();
        $uid = Buddha_Http_Cookie::getCookie('uid');
        if(empty($uid)){
            $datas['isok'] = 'false';
            $datas['data'] = 1;//请登录后再投票(如果没有帐号请注册！)
            $datas['url'] = 'index.php?a=login&c=account';
        }else{
            $CommonObj=new Common();
            $time= $CommonObj->time_handle();

            $table='activitycooperation';
            $where=$time['where'];//昨天的0点<当前时间<明天的0点时间
            $vodewhere=$where." and act_id ={$id} and whichtable='{$table}' and u_id={$uid} and shop_id={$shop_id}";
            $count= $VodetimeObj->getSingleFiledValues(array('id','shop_id','v_time'),$vodewhere);          //查询用户是否已经存在投票时间

            if($count){
                $datas['isok'] = 'false';
                $datas['data'] = 2;//你对该商家今天已经投过票了，请选择其它的吧！
                $datas['url'] = '';
            }else{
                $Act_id=  $ActivitycooperationObj->getSingleFiledValues(array('id','praise_num')," act_id={$id} and shop_id={$shop_id}");//查询合作商家表中的ID//查询该商家的投票次数
                $data['u_id']=$uid;
                $data['whichtable']=$table;
                $data['table_id']=$Act_id['id'];
                $data['act_id']=$id;
                $data['v_time']=time();
                $data['shop_id']=$shop_id;
                $id=$VodetimeObj->add($data);
                $data_coo['praise_num']=$Act_id['praise_num']+1;//该商家的投票次数加一
                $ActivitycooperationObj->edit($data_coo,$Act_id['id']);
                if($id){
                    $datas['isok'] = 'true';
                    $datas['data'] = 3;//投票成功
                    $datas['num'] = $data_coo['praise_num'];
                    $datas['shop_id'] = $shop_id;
                }else{
                    $datas['isok'] = 'false';
                    $datas['data'] = 4;//投票失败
                    $datas['url'] = '';
                }
            }
        }
        Buddha_Http_Output::makeJson($datas);
    }










}
