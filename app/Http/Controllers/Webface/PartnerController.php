<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/10
 * Time: 9:46
 * author sys
 */
class PartnerController extends Buddha_App_Action
{
    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

        //权限
        $webface_access_token = Buddha_Http_Input::getParameter('webface_access_token');
        if ($webface_access_token == '') {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444002, 'webface_access_token必填');
        }

        $ApptokenObj = new Apptoken();
        $num = $ApptokenObj->getTokenNum($webface_access_token);
        if ($num == 0) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444003, 'webface_access_token不正确请从新获取');
        }
    }

    /**
     * 商家会员
     */
    public function membermore(){
        if (Buddha_Http_Input::checkParameter(array('b_display','usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj = new User();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        if($UserObj->isHasPartnerPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '没有合伙人权限，你还未申请商家角色');
        }
        $where = " isdel=0 and referral_id='{$user_id}'";
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        if($keyword){
            $where.="and (username LIKE '%{$keyword}%' or mobile LIKE '%{$keyword}%' or realname LIKE '%{$keyword}%')";
        }

        $sql = "select count(*) as total from {$this->prefix}user  where {$where}";
        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount = $count_arr[0]['total'];
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }


        $orderby = " order by onlineregtime DESC ";
        $find=array('id','username','mobile','onlineregtime','groupid','state','username','realname');
        $list = $this->db->getFiledValues ($find,  $this->prefix.'user', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        foreach($list as $k=>$v){
            if($v['state']==0){
                $list[$k]['state']='未激活';
            }elseif($v['state']==1){
                $list[$k]['state']='激活';
            }else{
                $list[$k]['state']='注销';
            }
            if($v['groupid']==1){
                $list[$k]['groupid']='商家';
            }
            if($v['realname']=='' or $v['realname']=='0'){
                $list[$k]['realname']=$v['username'];
            }else{
                $list[$k]['realname']=$v['realname'];
            }

            $list[$k]['onlineregtime']=date('Y-m-d',$v['onlineregtime']);
        }
        $jsondata = array();
        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        if($rcount){
            $jsondata['totalrecord'] = $rcount;
        }else{
            $jsondata['totalrecord'] = 0;
        }
        if($pcount){
            $jsondata['totalpage'] = $pcount;
        }else{
            $jsondata['totalpage'] = 0;
        }
        if($list){
            $jsondata['list']=$list;
        }else{
            $jsondata['list']=array();
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '商家会员');

    }














}