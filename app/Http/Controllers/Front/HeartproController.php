<?php

/**
 * Class HeartproController
 */
class HeartproController extends Buddha_App_Action
{

    protected $tablenamestr;
    protected $tablename;
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='1分购';
        $this->tablename='heartpro';
    }


    /**1分购 列表**/

    public function index()
    {
        $RegionObj = new Region();
        $ShopObj = new Shop();
        $UserObj = new User();
        $CommonObj = new Common();

        $locdata = $RegionObj->getLocationDataFromCookie();
        $act = Buddha_Http_Input::getParameter('act');
        $keyword = Buddha_Http_Input::getParameter('keyword');
        $view = Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 2;
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') : 15;
        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id') : 0;

        if($act=='list') {
            $where = " isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']}";
            $orderby = "";
            if (Buddha_Atom_String::isValidString($shop_id)) {
                $where .= "AND shop_id='{$shop_id}'";
            } else {
                $orderby = " group by shop_id ";
            }
            $orderby .= " ORDER BY createtime DESC";
            if ($view) {
                switch ($view) {
                    case 2;
                        //  $where .= ' and is_sure=0';
                        $orderby = " ORDER BY createtime DESC";
                        break;
                    case 3;
                        $orderby = " ORDER BY click_count DESC";
                        break;
                    case 4;
                        $orderby = " group by shop_id order by createtime ASC";
                        break;
                }
            }
            if ($keyword) {
                $where .= " and name like '%{$keyword}%'";
            }

            $fields = array('id', 'shop_id', 'user_id', 'name', 'price', 'small as demand_thumb');
//------------------------
            /*先查询：当地有没有过期了但没有下架的1分购：有就下架*/

            $CommonObj->UpdateShelvesStatus($this->tablename, 'onshelftime', 'offshelftime', $locdata['sql']);

//---------------------------
            if($view==2)
            {
                $CommonindexObj = new Commonindex();

                $list =  $CommonindexObj->newestmore( $this->tablename,$fields,$page,$pagesize,$where,'createtime');

            }else{

                $list = $this->db->getFiledValues($fields, $this->prefix . $this->tablename, $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));
            }

            foreach($list as $k=>$v)
            {
                if($v['shop_id']!='0'){
                    $Db_shop = $ShopObj->getSingleFiledValues(array('name','specticloc','lng','lat'),"id='{$v['shop_id']}'");
                    $name = $Db_shop['name'];
                    if($Db_shop['roadfullname']=='0'){
                        $roadfullname = '';
                    }else{
                        $roadfullname = $Db_shop['specticloc'];
                    }
                }else{
                    $Db_user = $UserObj->getSingleFiledValues(array('username','realname','address'),"id='{$v['user_id']}'");
                    if($Db_user['address']=='0'){
                        $roadfullname = '' ;
                    }else{
                        $roadfullname = $Db_user['address'];
                    }
                    if($Db_user['realname']=='0'){
                        $name = $Db_user['username'];
                    }else{
                        $name = $Db_user['realname'];
                    }
                }

                $lease[] = array(
                    'id'=>$v['id'],
                    'name'=>$v['name'],
                    'price'=>$v['price'],
                    'shop_name'=>$name,
                    'roadfullname'=>$roadfullname,
                    'demand_thumb'=>$v['demand_thumb'],
                );
            }
            $data = array();
            if($lease){
                $data['isok'] = 'true';
                $data['list'] = $lease;
                $data['data'] = '加载完成';
            }else{
                $data['isok'] = 'false';
                $data['list'] = '';
                $data['data'] = '没数据了';
            }

            Buddha_Http_Output::makeJson($data);
        }


        $CommonindexObj = new Commonindex();
        $filarr = array(
            0=>array('filed'=>'zuixin','a'=>'index','view'=>2),
            1=>array('filed'=>'fujin','a'=>'index','view'=>1),
            2=>array('filed'=>'remen','a'=>'index','view'=>3),
            3=>array('filed'=>'shangjia','a'=>'index','view'=>5),
            );

        $Common = $CommonindexObj->indexmorenavlist($this->tablename,$filarr);
        $this->smarty->assign('navlist',$Common);



        $this->smarty->assign('view',$view);
        $this->smarty->assign('title', $this->tablenamestr);
        $this->smarty->assign('c', $this->tablename);
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');

    }



    /**
     * 1分购 详情
     */

    public function info()
    {

        $c = $this->c;
        $SupplyObj = new Supply();//产品表
        $CommonObj = new Common();
        $HeartplusObj = new Heartplus();//1分购申请人表对应 投票者表
        $HeartapplyObj = new Heartapply();//1分购申请人表
        $HeartproObj = new Heartpro();//1分购
        $is_join = $is_log = 0;
        $u_id = Buddha_Http_Cookie::getCookie('uid');

        $id= Buddha_Http_Input::getParameter('id')?Buddha_Http_Input::getParameter('id'):0;
        $url = array(
            'prize'=>'index.php?a=vodeprize&c='.$c.'&id='.$id,
            'ranking'=>'index.php?a=voderanking&c='.$c.'&id='.$id,
            'sign'=>'index.php?a=vodesign&c='.$c.'&id='.$id,
        );


        $Db_Heartpro = $HeartproObj->getSingleFiledValues('',"id='{$id}' and buddhastatus=0");//查询 1分购 数据

// ---------------- ---更新浏览次数
        $data['click_count'] = $Db_Heartpro['click_count']+1;
        $HeartproObj->edit($data,$id);//更新浏览次数
// ------------ --------

        $Db_Supply = $SupplyObj->getSingleFiledValues(array('market_price')," id='{$Db_Heartpro['table_id']}'");


        $Heartapplywhere = ' heartpro_id='.$id;

        $Db_Heartapply_num = $HeartapplyObj->countRecords($Heartapplywhere);//统计申请 1分购申请人表 的数量

//        $Db_Heartapply = $HeartapplyObj->getFiledValues($Heartapplywhere);//查询参加 1分购 的人

        $Heartpluswhere = ' heartpro_id='.$id;

        $Db_Heartplus_num = $HeartplusObj->countRecords($Heartpluswhere);//求和：投票的总数

        $sql = "SELECT SUM(vote_num) as num FROM {$this->prefix}heartapply WHERE {$Heartpluswhere} ";//求和：投票的总数

        $praise_num = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        /**↓↓↓↓↓↓头部轮播图 查询↓↓↓↓↓↓**/
        $MoregalleryObj = new Moregallery();
        $More = $MoregalleryObj->getFiledValues(array('id','goods_img'),"goods_id={$id} and tablename='{$this->tablename}' and webfield='file'");

        /**↑↑↑↑↑↑↑↑↑↑头部轮播图 查询 ↑↑↑↑↑↑↑↑↑↑**/


//////// 分享///////////////////////////////////////////////////////////////////////////////////////////////
        $WechatconfigObj  = new Wechatconfig();
        $uid = Buddha_Http_Cookie::getCookie('uid');

        $count = $HeartapplyObj->countRecords("user_id='{$uid}' AND heartpro_id='{$id}'");
        $share_desc = $Db_Heartpro['keyword'];
        if($uid AND $count>0)
        {
            $u_url='&u_id='.$uid;
            list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
            $content = "你的朋友{$UserInfo['realname']} @你,他正在参与{$Db_Heartpro['name']} 1分购活动，他邀请你你给他投票!";
            $share_desc = $content.$share_desc;
        }

        $sharearr = array(
            'share_title'=>$Db_Heartpro['name'],
            'share_desc'=>$share_desc,
            'ahare_link'=>"index.php?a=".__FUNCTION__."&c=".$this->c.$u_url,
            'share_imgUrl'=>$Db_Heartpro['small'],
        );
        $WechatconfigObj->getJsSign($sharearr['share_title'],$sharearr['share_desc'],$sharearr['ahare_link'],$sharearr['share_imgUrl']);

////////  分享  ///////////////////////////////////////////////////////////////////////////////////////////////


        /**↓↓↓↓↓↓↓↓↓↓↓↓ 推荐 ↓↓↓↓↓↓↓↓↓↓↓↓**/

        $CommonObj = new Common();
        $recommend = $CommonObj->recommendBelongShop($Db_Heartpro['shop_id'],$this->tablename,$id);
        $this->smarty->assign('recommend', $recommend);

        /**↑↑↑↑↑↑↑↑↑↑↑↑ 推荐 ↑↑↑↑↑↑↑↑↑↑**/

//        $ActivityObj = new Activity();
////        $Db_Activity = $ActivityObj->recommendBelongShop($Db_Heartpro['shop_id']);
//        $Db_Activity = $ActivityObj->recommendBelongShop('6695');
//        print_r($Db_Activity);


        /**判断当前用户是否已经参与了活动**/
        if(!empty($u_id))
        {
            $is_join = $HeartapplyObj->countRecords( "heartpro_id='{$id}' AND user_id='{$u_id}'");
            $is_log = 1;
        }

        $this->smarty->assign('is_log', $is_log);//$is_log 是否登录
        $this->smarty->assign('is_join', $is_join);//$is_join 是否参与活动

        $this->smarty->assign('img', $More);// 相册
        $this->smarty->assign('id', $id);//
        $this->smarty->assign('url', $url);//
        $this->smarty->assign('heartplus_num', $Db_Heartplus_num);//投票的总数

        $this->smarty->assign('Act', $Db_Heartpro);//
        $this->smarty->assign('count', $Db_Heartapply_num);//统计申请 1分购申请人表 的数量
        $this->smarty->assign('goods', $Db_Supply);//

        $this->smarty->assign('praise_num', $praise_num[0]['num']);//
        $this->smarty->assign('title', $this->tablenamestr);//
        $this->smarty->assign('c', $this->tablename);//
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }


    /**
     * 1分购 报名者列表
     */

    public function vodelist_ajax()
    {
        $host = Buddha::$buddha_array['host'];
        $title = Buddha_Http_Input::getParameter('title')?Buddha_Http_Input::getParameter('title'):2;// 1 搜索; 2人气  ； 3 最新（表示点击过来的）
        $id = Buddha_Http_Input::getParameter('id');// 1分购 ID
        $u_id = Buddha_Http_Input::getParameter('u_id');// 转发者Id
        $current_uid = Buddha_Http_Cookie::getCookie('uid');//当前用户ID
        $page = Buddha_Http_Input::getParameter('p');
        $search = Buddha_Http_Input::getParameter('search');
        $pagesize = 50;
        $is_log = 0;
        $is_buy = 0;
        $vote_num = 0;

        $is_join = 0;
        $HeartapplyObj = new Heartapply();
        $limit = Buddha_Tool_Page::sqlLimit ($page, $pagesize);
        $where = ' a.heartpro_id='.$id;
        if(!empty($search))
        {
            $where .= " and u.realname like '%{$search}%' or a.number like '%{$search}%'";
        }

        if(!empty($u_id))
        {
            $where .=" a.user_id='{$u_id}'";
        }

        /**查询当前用户的投票数量和是否已经购买过了**/
        if(Buddha_Atom_String::isValidString($current_uid))
        {
            $votewhere = " heartpro_id='{$id}' AND user_id='{$current_uid}'";

            $Db_Heartapply = $HeartapplyObj->getSingleFiledValues(array('vote_num','is_buy'),$votewhere);

            $is_log = 1;


            if(Buddha_Atom_Array::isValidArray($Db_Heartapply)){

                $vote_num = (int)$Db_Heartapply['vote_num'];
            }


            if($Db_Heartapply['is_buy'] == 1)
            {
                $is_buy = 1;
            }

            /**判断当前用户是否已经参与了活动**/
            if(!empty($Db_Heartapply))
            {
                $is_join =1;

            }


        }
        //对应商品（supply:goods_thumb 照片）、个人（user：logo照片）、店铺（shop：small）的（cooID、票数 、名称、 在activitycooperation表中）和活动ID

            //在 heartapply 表中要显示的字段有
        $filed = "a.id,a.user_id,a.vote_num,a.number,a.is_buy";
        if($title==2)
        {//2人气、3最新、4 我参与
            $orderby = ' order by a.vote_num desc';
        }elseif($title==3)
        {//2人气、3最新、4 我参与
            $orderby = ' order by a.createtime desc';
        }
//        elseif($title==4)
//        {//2人气、3最新、4 我参与
//            $uid = Buddha_Http_Cookie::getCookie('uid');
//            if(empty($uid))
//            {
//                Buddha_Http_Head::redirectofmobile('还未登录！',"index.php?a=login&c=account",2);
//                exit;
//            }else{
//                $where .=" user_id='{$uid}'";
//            }
//
//            $orderby = ' order by a.createtime desc';
//        }

        $filed .= ',u.logo,u.realname ';
        $table = 'user';
        $as_f = 'u';

        $sql = "select {$filed}
                from {$this->prefix}heartapply as a 
                INNER join {$this->prefix}{$table} as {$as_f} 
                on {$as_f}.id = a.user_id  
                where {$where} {$orderby} {$limit}";

        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);


        $currentrankings = '暂无';
        foreach($list as $k=>$v)
        {
            if(Buddha_Atom_String::isValidString($v['logo']))
            {
                if(strpos($v['logo'],'http') === false)//不存在
                {
                    $img = $host.$v['logo'];
                }elseif(strpos($v['logo'],'http') >=0){//存在
                    $img = $v['logo'];
                }else{
                    $img = $host.$v['logo'];
                }
            }else{
                $img = $host.'style/images/im.png';
            }

            $list[$k]['img'] = $img;

            if($v['is_buy']==1)
            {
                $list[$k]['icon_buy'] = 'style/img_two/successfulbidding.png';
            }else{
                $list[$k]['icon_buy'] = '';
            }

            if($v['user_id'] == $current_uid){
                $currentrankings = $k+1;
            }
        }

        /**判断当前用户是否已经购买过了:0否；1是*/

        $CommonObj = new Common();
        $Nws = $CommonObj->page_where($page,$list,$pagesize);
        $datas['current'] = 0;


        if($list){//已经该店铺报名了
            $datas['Nws'] = $Nws;
            $datas['isok'] = 'true';
            $datas['current'] = $vote_num;//当前用户目前投票数量
            $datas['is_log'] = $is_log;
            $datas['is_buy'] = $is_buy;
            $datas['is_join'] = $is_join;
            $datas['data'] = $list;
            $datas['p'] = $page;
            $datas['title'] = $title;
            $datas['search'] = $search;
            $datas['currentrankings'] = $currentrankings;//目前的排名
        }else{
            $datas['isok'] = 'false';
        }
        Buddha_Http_Output::makeJson($datas);
    }



    /**
     * 1分购：投票
     * */
    public function ajaxvote()
    {
        $HeartproObj = new Heartpro();//1分购
        $HeartapplyObj = new Heartapply();//1分购申请人表
        $HeartplusObj = new Heartplus();//1分购申请人表对应投票者表

        $heartpro_id= Buddha_Http_Input::getParameter('id')?Buddha_Http_Input::getParameter('id'):0;// 1分购 ID

        $heartapply_id= Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;//  申请表内码  ID

        $Db_Heartpro = $HeartproObj->getSingleFiledValues(array('applystarttime','applyendtime','partake','user_id')," id='{$heartpro_id}'");

        $uid = Buddha_Http_Cookie::getCookie('uid');//当前投票人的ID

        $newtime = Buddha::$buddha_array['buddha_timestamp'];

        if($Db_Heartpro['applystarttime'] > $newtime)
        {
            $datas['isok'] = 'false';
            $datas['data'] = 5;
            $datas['msg'] = '竞买时间还未开始，不能投票';
            $datas['url'] = '';
        }else if($newtime>$Db_Heartpro['applyendtime']){
            $datas['isok'] = 'false';
            $datas['data'] = 6;
            $datas['msg'] = '竞买时间结束，不能投票';
            $datas['url'] = '';
        }else{
            if(empty($uid))
            {
                $datas['isok'] = 'false';
                $datas['data'] = 1;
//                $datas['msg'] = '请登录后再投票(如果没有帐号请注册！)';
                $datas['msg'] = '请您登陆后(如果没有帐号请注册)，再参加竞买或帮助朋友投上您的爱心!';
                $datas['url'] = 'index.php?a=login&c=account';
            }else{

                $CommonObj = new Common();

                $time = $CommonObj->time_handle('createtime');

                $where = $time['where'];//昨天的0点<当前时间<明天的0点时间

//            $Heartpluswhere = $where." and heartpro_id ={$heartpro_id} and user_id={$uid} and heartapply_id={$heartapply_id}";
                $Heartpluswhere = $where." and heartpro_id ={$heartpro_id} and user_id={$uid}";

                /**根据投票规则判断是否能参与**/
                $UsserObj = new User();
                if($Db_Heartpro['partake']==1 AND !($UsserObj->isCouldHeartTicket($uid)))
                {   //只能新会员参与
                    $datas['isok'] = 'false';
                    $datas['data'] = 2;
//                    $datas['msg'] = '你不是新会员或已经投票了，无法投票!';
                    $datas['msg'] = '您的投票权已用完，喊您朋友来吧！';
                    $datas['url'] = '';

                }elseif($Db_Heartpro['partake']==2 AND $HeartplusObj->countRecords($Heartpluswhere))
                {  //新老会员都可以参与    //查询用户是否已经存在投票时间
                    $datas['isok'] = 'false';
                    $datas['data'] = 7;
//                    $datas['msg'] = '你对该商家今天已经投过票了，请选择其它的吧！';
                    $datas['msg'] = '您的投票权已用完，喊您朋友来吧！';
                    $datas['url'] = '';
                }else{

                    $Db_Heartapply = $HeartapplyObj->getSingleFiledValues(array('id','vote_num')," heartpro_id ={$heartpro_id} and id={$heartapply_id}");//查询1分购申请人的投票次数

                    $data['user_id'] = $uid;
                    $data['heartpro_id'] = $heartpro_id;//1分购
                    $data['heartapply_id'] = $heartapply_id;//1分购申请人表
                    $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                    $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                    $Heartplus_id = $HeartplusObj->add($data);

                    $Heartplus_num = $HeartplusObj->countRecords("heartpro_id='{$heartpro_id}'");

                    $Db_Heartappl_vote_num = $Db_Heartapply['vote_num']+1;//投票次数加一

                    if($Db_Heartappl_vote_num == $Heartplus_num)
                    {
                        $data_Heartapply['vote_num'] = $Heartplus_num;
                    }else{
                        $data_Heartapply['vote_num'] = $Db_Heartappl_vote_num;
                    }

                    $HeartapplyObj->edit($data_Heartapply,$heartapply_id);

                    if($Heartplus_id)
                    {
                        $datas['isok'] = 'true';
                        $datas['data'] = 3;
//                        $datas['msg'] = '投票成功!';
                        $datas['msg'] = '感谢您为我投票。您也去参加竞买吧!';
                        $datas['num'] = $data_Heartapply['vote_num'];
                        $datas['shop_id'] = $heartapply_id;
                    }else{
                        $datas['isok'] = 'false';
                        $datas['data'] = 4;
                        $datas['msg'] = '投票失败!';
                        $datas['url'] = '';
                    }
                }
            }
        }
        Buddha_Http_Output::makeJson($datas);
    }


    /**
     *1分购：详情和奖品设置
     * */
    public function vodeprize()
    {
        $host= Buddha::$buddha_array['host'];
//        $CommonObj = new Common();
        $ShopObj = new Shop();
        $HeartproObj = new Heartpro();
        $c = $this->c;
        $id = Buddha_Http_Input::getParameter('id')?Buddha_Http_Input::getParameter('id'):0;
        if(empty($id))
        {
            Buddha_Http_Head::redirectofmobile('参数错误！',"index.php?a=index&c=".$c,2);
            exit;
        }

        $Db_Heartpro = $HeartproObj->getSingleFiledValues(array('details','small','votecount','stock','small','name','shop_id','name'),"id='{$id}' AND buddhastatus=0");

        $shop_where = "id='{$Db_Heartpro['shop_id'] }'";

        $Db_Shop = $ShopObj->getSingleFiledValues(array('small','name'),$shop_where);
//print_r($Db_Shop);
        $Heartpro_codeimg = $HeartproObj->createQrcodeForCodeSales($id,$Db_Shop['small'],$Db_Shop['name'],$event='heartpro',$eventpage='info',$Db_Heartpro['name'],$Db_Heartpro['small']);

        $Db_Heartpro['details'] = $Db_Heartpro['details'].'  <br/> 最少投票数量：'.$Db_Heartpro['votecount'].';  <br/> <br/> 库存量：'.$Db_Heartpro['stock'];
        $Db_Heartpro['codeimg'] = $host.$Heartpro_codeimg;
        $this->smarty->assign('Act', $Db_Heartpro);
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }


    /**
     * 排名(投票)
     */
    public function voderanking()
    {
        $c=$this->c;
        $id = Buddha_Http_Input::getParameter('id')?Buddha_Http_Input::getParameter('id'):0;
        if(empty($id))
        {
            Buddha_Http_Head::redirectofmobile('参数错误！',"index.php?a=index&c=".$c,2);
            exit;
        }

//======查询 1分购申请人表 排名===
        $where = ' y.heartpro_id='.$id;

        $sql ="select y.id,y.user_id,y.vote_num,u.realname 
               from {$this->prefix}heartapply as y 
               left join {$this->prefix}user as u 
               on u.id = y.user_id 
               where {$where} 
               order by y.vote_num desc";

        $heartapply = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);



        $HeartproObj= new Heartpro();

        $Heartpro=$HeartproObj->getSingleFiledValues(array('small'),"id='{$id}'");


        foreach($heartapply as $k=>$v)
        {
            $heartapply[$k]['realname'] = mb_substr($v['realname'],0,15) ;
        }

        $this->smarty->assign('list', $heartapply);
        $this->smarty->assign('activity_img', $Heartpro['small']);
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    /**
     * 1分购：申请 报名(包含了 姓名、留言、电话)
     */

    public function vodesign()
    {
        $c = $this->c;
        $id = Buddha_Http_Input::getParameter('id')?Buddha_Http_Input::getParameter('id'):0;//1分购
        $uid = Buddha_Http_Cookie::getCookie('uid');
        if (empty($uid))
        {   //判断该用户是否存在(是否登录或)
            Buddha_Http_Head::redirectofmobile('请登录后发布！','index.php?a=login&c=account',2);
            exit;
        }

        $name = Buddha_Http_Input::getParameter('name');
        $phone = Buddha_Http_Input::getParameter('phone');
        $massage = Buddha_Http_Input::getParameter('massage');

        if(Buddha_Http_Input::isPost())
        {
            $HeartapplyObj = new Heartapply();
            $count = $HeartapplyObj->countRecords("user_id={$uid} and heartpro_id={$id}");// //判断用户是否已经报名了
            if ($name == '' && $phone == '')
            {
                $uid = Buddha_Http_Cookie::getCookie('uid');
                if (empty($uid))
                {   //判断该用户是否存在(是否登录或)
                    $datas['isok'] = 'false';
                    $datas['type'] = 1;
                    $datas['data'] = '你未登录或未注册，请登录后转换角色为商家再报名!';
                } else {
                    if ($count == 0) {
                        list($uid,$UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
                        //判断用户姓名或联系方式是否为空
                        if(($UserInfo['mobile']==''&&($UserInfo['realname'])||$UserInfo['tel'])){
                            $datas['isok'] = 'false';
                            $datas['type'] = 2;
                            $datas['data'] = '你的用户信息不完整，为了更好为你服务请完整个人信息后再提交或填写姓名和手机号后提交！';
                        }else{
                            $data['u_id'] = $uid;
                            $data['act_id'] = $id;
                            $data['user_name'] = $UserInfo['realname'];
                            $data['message'] = $massage;
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
                                $datas['data'] = '申请成功！';
                                $datas['url'] = 'index.php?a=info&c=heartpro&id='.$id;
                            } else {
                                $datas['isok'] = 'false';
                                $datas['data'] = '申请失败!';
                            }
                        }
                    } else {
                        $datas['isok'] = 'false';
                        $datas['data'] = '您已经申请过了,请不要重复申请！';
                    }
                }
            } else {
                list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
                if ($count == 0)
                {
                    $data['act_id'] = $id;
                    $data['u_id'] = $uid;
                    $data['u_name'] = $UserInfo['realname'];
                    $data['message'] = $massage;
                    $data['add_time'] = time();
                    $data['u_phone'] = $phone;
                    $data['sore'] = 1;
                    $num = $ActivitycooperationObj->add($data);
                    if ($num) {
                        $datas['isok'] = 'true';
                        $datas['data'] = '申请成功！';
                        $datas['url'] = 'index.php?a=info&c=heartpro&id='.$id;
                    } else {
                        $datas['isok'] = 'false';
                        $datas['data'] = '申请失败!';
                    }
                } else {
                    $datas['isok'] = 'false';
                    $datas['data'] = '您已经申请过了,请不要重复申请！';
                }
            }
            Buddha_Http_Output::makeJson($datas);
        }


        $this->smarty->assign('c', $c); $this->smarty->assign('id', $id);
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    /**
     * 1分购：申请 报名(不 包含 姓名、留言、电话)
     * 竞买
     */

    public function vodesign_noma()
    {
        $id = Buddha_Http_Input::getParameter('id')?Buddha_Http_Input::getParameter('id'):0;//1分购

        $uid = Buddha_Http_Cookie::getCookie('uid');
        if(empty($uid))
        {   //判断该用户是否存在(是否登录或)
            $datas['isok'] = 'false';
            $datas['islog'] = 0;
//            $datas['data'] = '请登录后再竞买!';
            $datas['data'] = '请您登陆后(如果没有帐号请注册)，再参加竞买或帮助朋友投上您的爱心!';

        }else{
            //////// 判断 报名时间是否开始了 /////////////////////////////////////////////////////
            $HeartproObj = new Heartpro();
            $HeartapplyObj = new Heartapply();
            $CommonObj = new Common();
            $newtime=Buddha::$buddha_array['buddha_timestamp'];
            $Db_Heartpro = $HeartproObj->getSingleFiledValues(array('applystarttime','applyendtime','stock'),"id='{$id}'");
            $Db_Heartapply = $HeartapplyObj->getSingleFiledValues(array('is_buy'),"heartpro_id='{$id}' AND user_id='{$uid}' ");

            /**判断用户是否已经购买过了**/

//        if($Db_Heartpro_num['applystarttime'] > $newtime)
//        {
//            $datas['isok'] = 'false';
//            $datas['data'] = '报名还未开始，不能报名!';
//        }else
            if($newtime > $Db_Heartpro['applyendtime'] )
            {
                $datas['isok'] = 'false';
                $datas['islog'] = 1;
                $datas['data'] = '报名已结束，不能报名!';
            }else{
                //////// 判断用户是否已经申请了/////////////////////////////////////////////////////
                $HeartapplyObj = new Heartapply();
                $count = $HeartapplyObj->countRecords("user_id='{$uid}' and heartpro_id='{$id}' ");//
                //////////////////////////////////////
                if(!$count)
                {

                    if(!Buddha_Atom_String::isValidString($Db_Heartpro['stock'])){
                        $datas['isok'] = 'false';
                        $datas['islog'] = 1;
                        $datas['data'] = '抱歉拍品数量不足，无法参与，欢迎你的下次光临！';
                    }else{
                        $data['user_id'] = $uid;
                        $data['heartpro_id'] = $id;
                        $data['number']=$CommonObj->GeneratingNumber();
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        $num = $HeartapplyObj->add($data);



                        if ($num){
                            $datas['isok'] = 'true';
//                        $datas['data'] = '申请成功！';
                            $datas['data'] = '恭喜您成功参加竞买，快让您的朋友帮您投票吧！';
                            $datas['islog'] = 1;
                            $datas['url'] = 'index.php?a=info&c=heartpro&id='.$id;
                        } else {
                            $datas['isok'] = 'false';
                            $datas['islog'] = 1;
                            $datas['data'] = '竞买申请失败!';
                        }
                    }
                } else {
                    $datas['isok'] = 'false';
                    $datas['islog'] = 1;
                    $datas['data'] = '您已经申请过了,请不要重复竞买申请！';
                }

            }
/////////////////////////////////////////////////////////////

        }


        Buddha_Http_Output::makeJson($datas);
    }


    /**产品购买***/
    public function shopping()
    {
        //list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
//        $uid = Buddha_Http_Input::getParameter('uid');

////////////////判断用户是否登陆///////////////////////////////////////////////////////////////
        $uid = Buddha_Http_Cookie::getCookie('uid');
        $id = Buddha_Http_Input::getParameter('id');//1分购内码ID
        $money = Buddha_Http_Input::getParameter('money');
//        $number = Buddha_Http_Input::getParameter('number');
        $number = 1;
        if(!$uid)
        {
            $datas['isok']='false';
            $datas['data'] = '请您登陆后(如果没有帐号请注册)，再参加竞买或帮助朋友投上您的爱心!';
            $datas['url'] = "index.php?a=login&c=account";
            Buddha_Http_Output::makeJson($datas);
            exit;
            //$datas['data']='/topay/wxpay/wxpayto.php?order_id='.$order_id.'&backurl='.$backurl;
        }
///////////////////////////////////////////////////////////////////////////////////

//////////////// 是否达到付款条件 ///////////////////////////////////
        $HeartproObj = new Heartpro();
        $merchant_uid = $HeartproObj->getSingleFiledValues(array('user_id','votecount','stock'),"id={$id}");
        $Minvotes= $merchant_uid['votecount'];//最少投票数量

        $HeartapplyObj = new Heartapply();
        $Db_Heartapply = $HeartapplyObj->getSingleFiledValues(array('vote_num','is_buy'),"heartpro_id='{$id}' AND user_id='{$uid}' ");

        $Currentvotes = $Db_Heartapply['vote_num']; // 当前投票数量


        /***先判断该用户是否参与了竞买***/

        if(!Buddha_Atom_Array::isValidArray($Db_Heartapply))
        {
            $datas['isok']='false';
//            $datas['data']='你还未参与竞买，快去参与后，再竞买吧！';
            $datas['data']='您还未参加竞买，请先参加竞买吧！';
        }else{
            /**用户当前投票量：如果当前投票量 小于 最小支付投票量则不能购买**/
            if(!($Currentvotes >= $Minvotes))
            {
                $datas['isok']='false';
//                $datas['data']='你的投票量不足，快去找人给你投票吧';
                $datas['data']='对不起，您的票数还不够，还须加油！喊一下好友前来助力吧！';
            }else{
        /////////////////////////////////////////
                /**检查库存是否正确*/

                if(!Buddha_Atom_String::isValidString($merchant_uid['stock']))
                {
                    $datas['isok']='false';
                    $datas['data']='对不起，你来晚了，现在库存为'.$merchant_uid['stock'].',欢迎你下次光临！';
                }else{

                    /**
                     *   订单查询：查询该用户有没有购买过
                     */
                    $OrderObj = new Order();
                    $Db_Order_count = $OrderObj->countRecords("good_id='{$id}' AND good_table='heartpro' AND user_id='{$uid}' AND pay_status=2");

                    if($Db_Heartapply['is_buy']==1)
                    {
                        $datas['isok'] = 'false';
//                        $datas['data'] = '你已经购买过了，请不用重复购买(一个账户只有一次机会)!';
                        $datas['data'] = '本活动一人只能有一次购买权哦!您已经成功竞买，快去帮好友拉票吧！';
                    }else{
                        if($Db_Order_count)
                        {
                            $datas['isok'] = 'false';
//                            $datas['data'] = '你已经购买过了，请不用重复购买(一个账户只有一次机会)!';
                            $datas['data'] = '本活动一人只能有一次购买权哦!您已经成功竞买，快去帮好友拉票吧！';

                            $HeartapplyObj = new Heartapply();
                            $HeartapplyObj->updateRecords(array('is_buy'=>1),"user_id='{$uid}' AND heartpro_id='{$id}'");

                        }else{

                            $UserObj = new User();
                            $UserInfo = $UserObj->getSingleFiledValues('',"id='{$uid}'");


                            /**↓↓↓↓↓↓↓↓↓↓↓ 订单表 ↓↓↓↓↓↓↓↓↓↓↓**/
                            $OrderObj=new Order();
                            $OrdermerchantObj=new Ordermerchant();

                            $data=array();
                            $order_sn = $OrderObj->birthOrderId($uid);//订单编号
                            $data['good_id'] = $id;//指定产品id
                            $data['user_id'] = $uid;
                            $data['merchant_uid'] = $merchant_uid['user_id'];
                            $data['order_sn'] = $order_sn;
                            $data['good_table'] = $this->tablename;//哪个表
                            $data['pay_type'] = 'third';//third第三方支付，point积分，balance余额
                            $data['order_type'] = 'heartpro';//money.out提现, 店铺认证shop.v,信息置顶info.top ,跨区域信息推广info.market,信息查看info.see,shopping购物,heartpro1分购
                            $data['goods_amt'] = $money * $number;//产品价格
                            $data['final_amt'] = $money * $number;//产品最终价格
                            $data['order_total'] = $number;//件数
                            $data['payname']='微信支付';
                            $data['make_level0'] = $UserInfo['level0'];//国家
                            $data['make_level1'] = $UserInfo['level1'];//省
                            $data['make_level2'] = $UserInfo['level2'];//市
                            $data['make_level3'] = $UserInfo['level3'];//区县
                            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];  //  时间戳
                            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr']; //  时间日期

                            $order_id = $OrderObj->add($data);
                            /**↑↑↑↑↑↑↑↑↑↑ 订单表 ↑↑↑↑↑↑↑↑↑↑**/


                            /**↓↓↓↓↓↓↓↓↓↓↓ 订单子表(订购商品详情表) ↓↓↓↓↓↓↓↓↓↓↓**/
                            $OrderproductObj = new Orderproduct();
                            $SupplyObj = new Supply();

                            $Db_Heartpro = $HeartproObj->getSingleFiledValues(array('table_id','price','small'),"id='{$id}'");
                            //
                            $goodsinfo = $SupplyObj->getSingleFiledValues(array('id','goods_name','goods_thumb','user_id'),"id={$Db_Heartpro['table_id']}");//获取购买商品的详情
                            //
                            $Orderproductdata['product_id'] = $id;//指定产品id
                            $Orderproductdata['product_table'] = $this->tablename;//哪个表
                            $Orderproductdata['product_name'] = $goodsinfo['goods_name'];//产品名称
                            $Orderproductdata['product_img'] = $Db_Heartpro['small'];//商品在线图片
                            $Orderproductdata['product_price'] =$Db_Heartpro['price'];//商品在线价格
                            $Orderproductdata['product_total'] = $number;//产品订购数量
                            $Orderproductdata['order_id'] = $order_id;//订单表内码ID
                            $Orderproductdata['product_amt'] =  $money * $number;//产品小计
                            $Orderproductdata['merchant_id'] =  $goodsinfo['user_id'];//产品所有者ID
                            $Orderproductdata['merchant_amt'] = $money * $number;//此产品最终价格

                            $Orderproductdata['createtime'] = Buddha::$buddha_array['buddha_timestamp'];  //  时间戳
                            $Orderproductdata['createtimestr'] = Buddha::$buddha_array['buddha_timestr']; //  时间日期

                            $Orderproduct_id = $OrderproductObj->add($Orderproductdata);

                            /**↑↑↑↑↑↑↑↑↑↑ 订单子表(订购商品详情表) ↑↑↑↑↑↑↑↑↑↑**/


                            $OrdermerchantObj->getInsertVersion1OrderMerchantInt($order_id,$order_sn,$merchant_uid['user_id'],$money * $number,"heartpro:{$id}");

                            //$urls = substr($_SERVER["REQUEST_URI"],1,stripos($_SERVER["REQUEST_URI"],'?'));
                            $backurl = urlencode('user/index.php?a=index&c=order');
                            $returnurl = urlencode('index.php?a=info&c=heartpro&id='.$id);//不想支付的返回页面

                            if($OrderObj)
                            {
                                $datas['isok']='true';
                                $datas['data'] = '成功';
                                $datas['url'] = 'index.php?a=orderinfo&c=heartpro&goods_id='.$id.'&order_id='.$order_id.'&backurl='.$backurl.'&returnurl='.$returnurl;
                                //$datas['data']='/topay/wxpay/wxpayto.php?order_id='.$order_id.'&backurl='.$backurl;
                            }else{
                                $datas['isok']='false';
                                $datas['data']='服务器忙';
                            }
                        }
                    }
                }
            }
        }
        Buddha_Http_Output::makeJson($datas);
    }


    /** 订单确认 和 地址选择**/
    public function orderinfo()
    {//支付前订单详情
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $Heartpro_id = (int)Buddha_Http_Input::getParameter('goods_id')?(int)Buddha_Http_Input::getParameter('goods_id'):0;//1分购id
        $address_id = (int)Buddha_Http_Input::getParameter('address_id')?(int)Buddha_Http_Input::getParameter('address_id'):0;//收货地址内码id
        $backurl = Buddha_Http_Input::getParameter('backurl');//支付后跳转的url地址
        $returnurl = Buddha_Http_Input::getParameter('returnurl');//不想支付返回的跳转的url地址
//        $backurl = '/index.php?a=info&c=heartpro&id=4';//支付后跳转的url地址
        $backurl = urlencode($backurl);
        $returnurl = urlencode($returnurl);

//        $fanhuiurl = '/index.php?a=info&c=heartpro&id=4';//不想支付返回的跳转的url地址
//        $fanhuiurl = urlencode($fanhuiurl);

        $order_id = (int)Buddha_Http_Input::getParameter('order_id')?(int)Buddha_Http_Input::getParameter('order_id'):0;//订单id
        $OrderObj=new Order();
        $SupplyObj = new Supply();
        $RegionObj = new Region();
        $AddressObj = new Address();
        $HeartproObj = new Heartpro();

        $Db_Heartpro = $HeartproObj->getSingleFiledValues(array('table_id','price','small'),"id='{$Heartpro_id}'");

        $goodsinfo = $SupplyObj->getSingleFiledValues(array('id','goods_name'),"id={$Db_Heartpro['table_id']}");//获取购买商品的详情

        $Db_Heartpro['goods_name'] = $goodsinfo['goods_name'];
        $Db_Heartpro['heartpro_id'] = $Heartpro_id;
        $Db_Heartpro['supply_id'] = $goodsinfo['id'];

        unset($Db_Heartpro['table_id']);

        $orderinfo = $OrderObj->getSingleFiledValues('',"id={$order_id}");//获取订单号

        $addressinfo = $AddressObj->getSingleFiledValues('',"uid={$uid} and isdef = 1");//获取收货地址

        $url = '/topay/wxpay/wxpayto.php?order_id='.$order_id.'&addressid='.$addressinfo['id'].'&backurl='.$backurl.'&returnurl='.$returnurl;//跳转url
        if($addressinfo['pro']){//省
            $pro = $RegionObj->getSingleFiledValues(array('name'),"id={$addressinfo['pro']}");
            $addressinfo['addre'] = $pro['name'].'省';
        }
        if($addressinfo['city']){//市
            $city = $RegionObj->getSingleFiledValues(array('name'),"id={$addressinfo['city']}");
            $addressinfo['addre'] .= ' '.$city['name'].'市';
        }
        if($addressinfo['area']){//区县
            $area = $RegionObj->getSingleFiledValues(array('name'),"id={$addressinfo['area']}");
            $addressinfo['addre'] .= ' '.$area['name'];
        }

//        if(Buddha_Atom_String::isValidString($address_id))
//        {
//            $OrderData['addressid'] = $address_id;
//        }else{
//            $OrderData['addressid'] =  $addressinfo['id'];
//        }
//
//        /****给订单表更新地址******/
//        $OrderObj->updateRecords($OrderData,$order_id);

        $this->smarty->assign('uid',$uid);
        $this->smarty->assign('url',$url);
        $this->smarty->assign('orderinfo',$orderinfo);
        $this->smarty->assign('goodsinfo',$Db_Heartpro);
        $this->smarty->assign('addressinfo',$addressinfo);

        $TPL_URL = $this->c.'.'.__FUNCTION__; echo 11;
        $this->smarty -> display($TPL_URL.'.html');

    }


    /**支付**/
    public function updateaddress()
    {//给订单表添加收货地址id号
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $AddressObj = new Address();
        $addressid = Buddha_Http_Input::getParameter('addressinfo');//收货地址id
        if(!Buddha_Atom_String::isValidString($addressid)){
            $addressinfo = $AddressObj->getSingleFiledValues(array('id'),"uid={$uid} and isdef = 1");//获取收货地址
            $addressid = $addressinfo['id'];
        }

        $orderid = Buddha_Http_Input::getParameter('orderid');//订单id
        $OrderObj=new Order();
        $data['addressid'] = $addressid;
        $Db_Order_num = $OrderObj->updateRecords($data,"id='{$orderid}'");

        if($Db_Order_num)
        {//编辑order表
            $datas['isok']='true';
            $datas['data'] = '即将跳转到支付页面';
//            $datas['data']='/topay/wxpay/wxpayto.php?order_id='.$order_id.'&backurl='.$backurl;
        }else{
            $datas['isok']='false';
            $datas['data']='服务器忙';
        }

        Buddha_Http_Output::makeJson($datas);
    }





}

