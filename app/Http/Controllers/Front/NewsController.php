<?php

/**
 * Class ActivityController
 */
class NewsController extends Buddha_App_Action
{
    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }
    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if(empty($uid)){
            Buddha_Http_Head::redirectofmobile('请登录后发布！','index.php?a=login&c=account',2);
            exit;
        }
        $c=$this->c;
        $UserObj=new User();
        $role= $UserObj->user_role();
        $this->smarty->assign('role',$role);
        $act=Buddha_Http_Input::getParameter('act');
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') :1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $where = "u_id={$uid}";
        $orderby = " order by sure DESC,add_time DESC,id";
        $NewsObj=new News();

        $list = $this->db->getFiledValues('', $this->prefix ."{$c}", $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));

        $ActivityObj=new Activity();
        foreach($list as $k=>$v){
            $list[$k]['addtime']=date("Y-m-d H:i:s",$v['add_time']);
            if($v['is_act']>0){//判断是否是活动
               $count= $ActivityObj->countRecords(" is_sure=1 and buddhastatus=0 and isdel=0 and id={$v['is_act']}");//只显示：已经审核过了的活动信息
                if($count>0){
                    $list[$k]=$v;
                }else{
                    unset($list[$k]);
                }
            }
        }

        if($act=='list'){
            if($list){
                $datas['isok'] = 'true';
                $datas['list'] = $list;
                $data['data']='加载完成';
            }else{
                $datas['isok'] = 'false';
                $datas['list'] = '';
                $datas['data'] = '没有了';
            }
            Buddha_Http_Output::makeJson($datas);
        }
        $where.=' and sure=1 and is_act=0';

        /**
         *   思路：非活动数量+活动数量
         */
        $Facvivitynum=$NewsObj->countRecords($where);/*非活动数量*/
        $sql ="select count(*) as total
	          from {$this->prefix}news as n 
	          left join {$this->prefix}activity as a
              on a.id = n.is_act
              where u_id={$uid}  and  n.sure=1 and a.is_sure=1";
        $Aacvivitynum = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);/*活动数量*/

        $num=$Aacvivitynum[0]['total']+$Facvivitynum;

        $this->smarty->assign('title','我的消息');
        $this->smarty->assign('c',$c);
        $this->smarty->assign('num',$num);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
    public function info(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if(empty($uid)){
            Buddha_Http_Head::redirectofmobile('请登录后发布！','index.php?a=login&c=account',2);
            exit;
        }
        $c=$this->c;
        $NewsObj=new News();
        $ShopObj=new Shop();
        $UserObj=new User();
        $id=Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误！',"index.php?a=index&c={$c}",2);
        }
        $article=$NewsObj->fetch($id);

        if(!$article){
            Buddha_Http_Head::redirectofmobile('信息不存在！',"index.php?a=index&c={$c}",2);
        }
        if($article['soure_id']){//消息发布者ID
            $soure_username=$UserObj->getSingleFiledValues(array('realname'),"id ={$article['u_id']} and isdel=0");
            $article['soure_username']=$soure_username['realname'];
        }
        if($article['shop_id']){//消息发布者的店铺ID
            $shopname=$ShopObj->getSingleFiledValues(array('name'),"id ={$article['shop_id']} and is_sure=1 and state=0 ");
            $article['soure_shopname']=$shopname['name'];
        }
        $article['addtime']=date("Y-m-d H:i:s",$article['add_time']);

        if($article['sure']==1){//消息拥有者是否阅读
            $data['sure']=0;
            $NewsObj->edit($data,$id);
        }
        $ActivitycooperationObj=new Activitycooperation();
        $is_sure= $ActivitycooperationObj->getSingleFiledValues(array('id','is_sure')," u_id={$article['soure_id']} and shop_id={$article['u_shopid']} and act_id={$article['is_act']}");   //查询活动合作商家表的id
        $article['is_sure']=$is_sure['is_sure'];

        $ActivityObj=NEW Activity();

        if($article['is_act']>0){
            $Activity= $ActivityObj->getSingleFiledValues(array('id','type')," id = {$article['is_act']}");
            if($Activity['type']==1||$Activity['type']==2){
                $article['ck_url']='index.php?a=mylist&c=activity&id=';
            }elseif($Activity['type']==3||$Activity['type']==4){
                $article['ck_url']='index.php?a=vodelist&c=activity&id=';
            }
        }
        $this->smarty->assign('c',$c);
        $this->smarty->assign('article',$article);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    function ajaxinfo(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if(empty($uid)){
            Buddha_Http_Head::redirectofmobile('请登录后发布！','index.php?a=login&c=account',2);
            exit;
        }
        $ushopid=Buddha_Http_Input::getParameter('cid');//参与活动的 店铺id
        $err=Buddha_Http_Input::getParameter('err');
        $act=Buddha_Http_Input::getParameter('act');//参与活动的 活动id

        if(!empty($ushopid)&&!empty($act)){
            $data['is_sure']=$err;
            $data['sure']=1;
            $data['sure_time']=time();
            $ActivitycooperationObj=new Activitycooperation();
            $actid= $ActivitycooperationObj->debug()->getSingleFiledValues(array('id'),"shop_id={$ushopid} and act_id={$act}");   //查询活动合作商家表的id
            $num=$ActivitycooperationObj->edit($data,$actid['id']);
            if($num>0){
                $datas['isok']='true';
                $datas['data']=1;
                $ActivityObj=new Activity();
                $Activity= $ActivityObj->getSingleFiledValues(array('id','type')," id = {$act}");
                if($Activity['type']==1||$Activity['type']==2){
                    $datas['ck_url']='index.php?a=mylist&c=activity&id=';
                }elseif($Activity['type']==3||$Activity['type']==4){
                    $datas['ck_url']='index.php?a=vodelist&c=activity&id=';
                }
            }else{
                $datas['isok']='false';
                $datas['data']=0;
            }
        }
        Buddha_Http_Output::makeJson($datas);
    }
}
