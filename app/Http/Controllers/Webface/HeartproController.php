<?php

/**
 * Class HeartproController
 */
class HeartproController extends Buddha_App_Action
{
    protected $tablenamestr;
    protected $tablename;

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

        $this->tablenamestr='1分营销';
        $this->tablename='heartpro';
    }


    /**
     * 代表商1分购列表顶部导航
     */
    public function agentnav(){
        $view = (int)Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):0;



        $view0=$view1=$view2=$view3=$view4=0;
        if($view==0){
            $view0=1;
        }elseif($view==2){
            $view1=1;
        }elseif($view==3){
            $view2=1;
        }
        elseif($view==4){
            $view3=1;
        }elseif($view==5){
            $view4=1;
        }else{
            $view0=1;
        }
        $Services ='heartpro.agentmore';
        $header[0] = array( 'select'=>$view0,'name'=>'全部','namevalue'=>0,
            'Services'=>$Services,'param'=>array('view'=>1));
        $header[1] = array( 'select'=>$view1,'name'=>'新加','namevalue'=>2,
            'Services'=>$Services,'param'=>array('view'=>2));
        $header[2] = array( 'select'=>$view2,'name'=>'已审核','namevalue'=>3,
            'Services'=>$Services,'param'=>array('view'=>3));
        $header[3] = array( 'select'=>$view3,'name'=>'未通过','namevalue'=>4,
            'Services'=>$Services,'param'=>array('view'=>4));
        $header[4] = array( 'select'=>$view4,'name'=>'已下架','namevalue'=>5,
            'Services'=>$Services,'param'=>array('view'=>5));

        $jsondata = $header;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "代表商1分购列表顶部导航");

    }


    /**
     * 代理商一分营销列表
     * @author wph 2017-12-21
     */
    public function agentmore()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('b_display','usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj=new Common();
        $UserObj=new User();
        $ShopObj= new Shop();
        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$UserObj->isHasAgentPrivilege($user_id) ){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '你还未申请代理商角色！');
        }


        $keyword = Buddha_Http_Input::getParameter('keyword')?Buddha_Http_Input::getParameter('keyword'):'';
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);
        $view = (int)Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):0;


        $where = " level3='{$Db_User['level3']}' ";

        if(Buddha_Atom_String::isValidString($keyword)){
            $where.= Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($keyword,array('name','number'));
        }

        if($view){
            switch($view){
                case 2;
                    $where.=' and is_sure=0';
                    break;
                case 3;
                    $where.=" and is_sure=1";
                    break;
                case 4;
                    $where.=" and is_sure=4 ";
                    break;
                case 5;
                    $where.=" and isdel=0 and is_sure=1 and buddhastatus=1";
                    break;
            }
        }
        $fileds = 'id AS heartpro_id,name, is_sure, shop_id, name, buddhastatus, keywords, level3, price, level3 ';


        if($b_display==1){
            $fileds.=' , medium AS img ';
        }elseif($b_display==2){
            $fileds.=' , small AS  img ';
        }

        $orderby = " ORDER BY createtime DESC ";


        $sql =" SELECT  {$fileds}
                FROM {$this->prefix}{$this->tablename} WHERE {$where}
                {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        $Db_Activity = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata = array();
       // $jsondata['nav'] = $ShopObj->getPersonalCenterHeaderMenu("{$this->tablename}.agentmore",1,$view);
        $jsondata['page'] =  0;
        $jsondata['pagesize'] =  0;
        $jsondata['totalrecord'] =  0;
        $jsondata['totalpage'] =  0;
        if(Buddha_Atom_Array::isValidArray($Db_Activity)){
            foreach($Db_Activity as $k=>$v){

                if($v['shop_id']!=0){
                    $shop_name= $ShopObj->getSingleFiledValues(array('name'),"id='{$v['shop_id']}' and user_id='{$v['user_id']}'");
                    $name='商家：'.$shop_name['name'];
                }else{
                    $shop_name= $UserObj->getSingleFiledValues(array('name'),"id='{$v['user_id']}'");
                    $name='个人：'.$shop_name['name'];
                }


                $Db_Activity[$k]['status_img']='';
                if($v['is_sure']==0){

                    $Db_Activity[$k]['status_img']=$host.'apistate/menuplus/weishenhe.png';

                    /*活动：审核状态（只有未审核的活动才显示）*/
                    $Db_Activity[$k]['issureServices']=array(
                        'Services' => 'heartpro.beforeverify',
                        'param'=> array('heartpro_id'=>$v['heartpro_id'])
                    );
                    $Db_Activity[$k]['clickbuddon']='审核';

                }elseif($v['is_sure']==4){

                    $Db_Activity[$k]['status_img']=$host.'apistate/menuplus/weitonguo.png';

                }elseif($v['is_sure']==1){

                    $Db_Activity[$k]['status_img']=$host.'apistate/menuplus/yitonguo.png';


                    /*单页信息：上下架（只有正常的单页信息才显示）*/
                    $Db_Activity[$k]['shelfServices']=array(
                        'Services' => 'heartpro.offshelf',
                        'param'=> array('shelf'=>$v['buddhastatus'],'heartpro_id'=>$v['heartpro_id'])
                    );

                    if($v['buddhastatus']==1){

                        $Db_Activity[$k]['clickbuddon']='上 架';

                    }else if($v['buddhastatus']==0){

                        $Db_Activity[$k]['clickbuddon']='下 架';
                    }
                }

                if(Buddha_Atom_String::isValidString( $v['img']))
                {
                    $Db_Heartpro[$k]['img'] = $host .  ltrim($v['img'],'/');
                }else{
                    $Db_Heartpro[$k]['img'] = '';
                }

                $Db_Activity[$k]['img'] = Buddha_Atom_String::getApiFileUrlStr($v['img']);
                $Db_Activity[$k]['shop_name']=$ShopObj->getShopnameFromShopid($v['shop_id']);
                unset( $Db_Activity[$k]['img']);
                unset( $Db_Activity[$k]['level3']);


            }


            $tablewhere=$this->prefix.$this->tablename;

            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);


            $jsondata['page'] =  $temp_Common['page'];
            $jsondata['pagesize'] =  $temp_Common['pagesize'];
            $jsondata['totalrecord'] =  $temp_Common['totalrecord'];
            $jsondata['totalpage'] =  $temp_Common['totalpage'];
            $jsondata['list'] = $Db_Activity;

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "代理商：{$this->tablenamestr}管理列表");


    }


    /**
     * 代理商审核前的页面
     * @author wph 2012-12-21
     */
    public function beforeverify()
    {
        $host= Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('heartpro_id','usertoken','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $ShopObj = new Shop();
        $CommonObj = new Common();
        $RegionObj = new Region();

        $heartpro_id =  (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;
        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id','realname','mobile','banlance','level3','username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $level3 = $Db_User['level3'];

        if(!$UserObj->isHasAgentPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }

        if(!$CommonObj->isOwnerBelongToAgentByLeve3($this->tablename,$heartpro_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }

        if($CommonObj->isIssureByTableid($heartpro_id,$this->tablename)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 50000002	, '已经审核过了，请不要重复审核！');
        }


        $b_display =(int) Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;
        $shop_id =(int) Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):0;


        $fields='id as heartpro_id,shop_id,user_id,shop_id,table_name,table_id,unit_id,price,stock,votecount,level1,level2,level3,is_remote,applystarttimestr,applyendtimestr,onshelftimestr,offshelftimestr,keywords,name,details,is_sure';
        if($b_display==1){

            $fields.=' , medium AS img ';

        }elseif($b_display==2){

            $fields.=' , small AS img ';
        }

        $where=" id ='{$heartpro_id}' ";

        if($shop_id>0){
            $where.=" AND shop_id='{$shop_id}' ";
        }

        $sql =" SELECT {$fields} FROM {$this->prefix}{$this->tablename}  WHERE {$where} ";
        $Db_tablename_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $jsondata=array();
        if(Buddha_Atom_Array::isValidArray($Db_tablename_arr)){

            $Db_Activity = $Db_tablename_arr[0];
            $Db_Activity['img'] = Buddha_Atom_String::getApiFileUrlStr($Db_Activity['img']);
            $Db_Activity['desc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Activity['desc']);
            $Db_Activity['shop_name']=$ShopObj->getShopnameFromShopid($Db_Activity['shop_id']);
            $Db_Activity['desc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Activity['desc']);
            $Db_Table = $this->db->getSingleFiledValues(array('goods_name'),$Db_Activity['table_name'],"id='{$Db_Activity['table_id']}' and user_id='{$Db_Activity['user_id']}'");
            $Db_Activity['supply_name'] = $Db_Table['goods_name'];
            $Db_Table = $this->db->getSingleFiledValues(array('unit'),'supplycat',"id='{$Db_Activity['unit_id']}'");
            $Db_Activity['unit_name'] = $Db_Table['unit'];

            if($Db_Activity['is_remote']==1){
                $Db_Region=$RegionObj->getAllArrayAddressByLever($Db_Activity['level3']);
                $region='';
                foreach($Db_Region as $k=>$v){
                    if($k!=0)
                        $region.=$v['name'].' > ';
                }
                $Db_Activity['region']=Buddha_Atom_String::toDeleteTailCharacter($region);
            }
            unset( $Db_Activity['user_id']);

            /*审核*/
            $Db_Activity['sureServices']=array(
                'Services' => $this->tablename.'.verify',
                'param'=> array('is_sure'=>$Db_Activity['is_sure'],'heartpro_id'=>$Db_Activity['heartpro_id'])
            );

            $jsondata = $Db_Activity;
        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "代理商进行{$this->tablenamestr}之前必须请求的详情页面");

    }


    /**
     * 代理商进行审核
     * @author wph 2017-12-21
     */
    public function verify(){

        if (Buddha_Http_Input::checkParameter(array('heartpro_id','usertoken','is_sure'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $HeartproObj = new Heartpro();
        $CommonObj = new Common();

        $heartpro_id =  (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;
        /*审核状态：1通过审核  ；4未通过审核*/
        $is_sure = (int) Buddha_Http_Input::getParameter('is_sure')?(int) Buddha_Http_Input::getParameter('is_sure'):0;

        /*判断$is_sure审核状态码 是否属于 1,4*/
        if(!$CommonObj->isIdInDataEffectiveById($is_sure,array(1,4)))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }

        $remarks = Buddha_Http_Input::getParameter('remarks')? Buddha_Http_Input::getParameter('remarks'):'';
        /*4未通过审核 必须填写备注*/
        if($is_sure==4 AND !Buddha_Atom_String::isValidString($remarks))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }



        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','banlance','level3',
            'username'
        );
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $level3 = $Db_User['level3'];

        if($UserObj->isHasAgentPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }

        if(!$CommonObj->isOwnerBelongToAgentByLeve3($this->tablename,$heartpro_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }


        $data = array();
        $data['is_sure'] =$is_sure ;
        $data['remarks'] =$remarks ;
        $Db_Heartpro_num = $HeartproObj->edit($data,$heartpro_id);


        $jsondata = array();
        $datas=array();
        if($Db_Heartpro_num){
            $datas['is_ok']=1;
            $datas['is_msg']=$this->tablenamestr.'审核成功！';
        }else{
            $datas['is_ok']=0;
            $datas['is_msg']=$this->tablenamestr.'审核失败！';
        }

        $jsondata['heartpro_id'] = $heartpro_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "代理商{$this->tablenamestr}审核");

    }


    /**
     * 代理商：上下架状态
     */
    public function offshelf()
    {
        if (Buddha_Http_Input::checkParameter(array('heartpro_id','usertoken','isdel'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $CommonObj = new Common();
        $UsercommonObj = new Usercommon();

        $heartpro_id =  (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;

//        /*默认下架  0下架 1=上架*/
        $isdel = (int)Buddha_Http_Input::getParameter('isdel') ? (int)Buddha_Http_Input::getParameter('isdel') : 0;
//        if(!$CommonObj->isIdInDataEffectiveById($shelf)){
//            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
//        }

        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','banlance','level3',
            'username'
        );
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $level3 = $Db_User['level3'];

        if($UserObj->isHasAgentPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }

        if(!$CommonObj->isOwnerBelongToAgentByLeve3($this->tablename,$heartpro_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }

        $data = array();
//        $msg="";
//        if($shelf==0){
//            $data['buddhastatus'] =1 ;
//            $msg="下架";
//        }else{
//            $data['buddhastatus'] =0 ;
//            $msg="上架";
//        }

//        $Db_Heartpro_num = $this->db->updateRecords( $data, $this->tablename,"id ='{$heartpro_id}'" );


        $Db_Usercommon  = $UsercommonObj-> agentsshelf($this->tablename,$heartpro_id,$isdel,$user_id);


        $jsondata = array();
        $jsondata['is_ok'] = $Db_Usercommon['is_ok'];
        $jsondata['is_msg'] = $Db_Usercommon['is_msg'];
        $jsondata['buttonname'] = $Db_Usercommon['buttonname'];
        $jsondata['heartpro_id'] = $heartpro_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '代理商上下架'.$this->tablenamestr);


    }


    /**
     * 商家：上下架状态
     */
    public function businessoffshelf(){

        if (Buddha_Http_Input::checkParameter(array('heartpro_id','usertoken','shelf'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();
        $CommonObj = new Common();

        $heartpro_id =  (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;

        /*默认下架  0下架 1=上架*/
        $shelf = (int)Buddha_Http_Input::getParameter('shelf') ? (int)Buddha_Http_Input::getParameter('shelf') : 0;
        if(!$CommonObj->isIdInDataEffectiveById($shelf)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }

        $usertoken =  Buddha_Http_Input::getParameter('usertoken');
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','logo','groupid','to_group_id',
            'realname','mobile','banlance','level3',
            'username'
        );
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $level3 = $Db_User['level3'];

        if($UserObj->isHasAgentPrivilege($user_id)==0){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000020, '没有代理商权限');
        }

        if(!$CommonObj->isOwnerBelongToAgentByLeve3($this->tablename,$heartpro_id,$level3)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000038, '此信息不属于当前的代理商管理');
        }

        $data = array();
        $msg="";
        if($shelf==0){
            $data['buddhastatus'] =1 ;
            $msg="下架";
        }else{
            $data['buddhastatus'] =0 ;
            $msg="上架";
        }

        $Db_Heartpro_num = $this->db->updateRecords( $data, $this->tablename,"id ='{$heartpro_id}'" );


        $jsondata = array();
        $jsondata['heartpro_id'] = $heartpro_id;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['msg'] = $msg;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.$msg);


    }


    /**
     * 商家1分购列表顶部导航
     */
    public function merchantnav()
    {
        $view = (int)Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):0;

        $view0=$view1=$view2=$view3=0;
        if($view==0){
            $view0=1;
        }elseif($view==2){
            $view1=1;
        }elseif($view==3){
            $view2=1;
        }
        elseif($view==4){
            $view3=1;
        }else{
            $view0=1;
        }
        $Services ='heartpro.merchantmore';
        $header[0] = array( 'select'=>$view0,'name'=>'全部','namevalue'=>0,
            'Services'=>$Services,'param'=>array('view'=>1));
        $header[1] = array( 'select'=>$view1,'name'=>'新加','namevalue'=>2,
            'Services'=>$Services,'param'=>array('view'=>2));
        $header[2] = array( 'select'=>$view2,'name'=>'已审核','namevalue'=>3,
            'Services'=>$Services,'param'=>array('view'=>3));
        $header[3] = array( 'select'=>$view3,'name'=>'未通过','namevalue'=>4,
            'Services'=>$Services,'param'=>array('view'=>4));

        $jsondata = $header;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "商家1分购列表顶部导航");

    }


    /**
     *   商家 1分购列表
     */
    public function merchantmore ()
    {

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('b_display', 'usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $CommonObj = new Common();
        $UserObj = new User();
        $ShopObj = new Shop();

        $usertoken = Buddha_Http_Input::getParameter('usertoken') ? Buddha_Http_Input::getParameter('usertoken') : 0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username', 'level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];



        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        $keyword = Buddha_Http_Input::getParameter('keyword') ? Buddha_Http_Input::getParameter('keyword') : '';

        $b_display = (int)Buddha_Http_Input::getParameter('b_display') ? Buddha_Http_Input::getParameter('b_display') : 2;

        $page = Buddha_Http_Input::getParameter('page') ? Buddha_Http_Input::getParameter('page') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);

        $view = Buddha_Http_Input::getParameter('view') ? Buddha_Http_Input::getParameter('view') : 0;

        $shop_id = Buddha_Http_Input::getParameter('shop_id') ? Buddha_Http_Input::getParameter('shop_id') : 0;


        /*商家：商家只能查看自己的活动信息和没有被删除的活动*/

        $where = " isdel=0 AND user_id='{$user_id}' ";


        if (Buddha_Atom_String::isValidString($keyword))
        {
            $where .= Buddha_Atom_Sql::getSqlByFeildsForVagueSearchstring($keyword, array('name', 'number'));
        }

        if($shop_id>0){
            $where .=" AND shop_id='{$shop_id}'";
        }


        if(!$CommonObj->isIdInDataEffectiveById($view,array(0,2,3,4))){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }


        if ($view) {
            switch($view){
                case 2;
                    $where.=' and is_sure=0 ';
                case 3;
                    $where.=' and is_sure=1 ';
                    break;
                case 4;
                    $where.=' and is_sure=4 ';
                    break;
            }
        }

        $isShowStop=0;

        $fileds = ' id AS heartpro_id, name, buddhastatus,is_sure, number ,price ,is_sure,applystarttime,applyendtime,applystarttimestr,applyendtimestr';

        if ($b_display == 1) {

            $fileds .= ' ,medium AS img ';
        } elseif ($b_display == 2) {

            $fileds .= ' , small AS img ';
        }

        $orderby = " ORDER BY createtime DESC ";

        $sql = " SELECT  {$fileds}
                 FROM {$this->prefix}{$this->tablename} WHERE {$where}
                 {$orderby}  " . Buddha_Tool_Page::sqlLimit($page, $pagesize);

        $Db_Heartpro = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);



        $jsondata = array();
        $jsondata['page'] = 0;
        $jsondata['pagesize'] = 0;
        $jsondata['totalrecord'] = 0;
        $jsondata['totalpage'] = 0;
        $jsondata['list'] = array();
        //$jsondata['nav'] = $ShopObj->getPersonalCenterHeaderMenu("{$this->tablename}.merchantmore",0,$view);
        $jsondata['add'] = array(
            'Services'=>'heartpro.beforeadd',
            'param'=>array(),
        );

        if (Buddha_Atom_Array::isValidArray($Db_Heartpro)) {

            foreach ($Db_Heartpro as $k => $v) {

                if ($v['is_sure'] == 0) {

                    $Db_Heartpro[$k]['status_img'] = $host . 'apistate/menuplus/weishenhe.png';

                } elseif ($v['is_sure'] == 4) {

                    $Db_Heartpro[$k]['status_img'] = $host . 'apistate/menuplus/weitonguo.png';

                } elseif ($v['is_sure'] == 1) {

                    $Db_Heartpro[$k]['status_img'] = $host . 'apistate/menuplus/yitonguo.png';

                }



                $Db_Activity[$k]['img'] = Buddha_Atom_String::getApiFileUrlStr($v['img']);

                if ($v['buddhastatus'] == 1) {

                    $Db_Heartpro[$k]['clickbuddon'] = '上 架';

                } else if ($v['buddhastatus'] == 0) {

                    $Db_Heartpro[$k]['clickbuddon'] = '下 架';

                }
                if(Buddha_Atom_String::isValidString( $v['img']))
                {
                    $Db_Heartpro[$k]['img'] = $host .  ltrim($v['img'],'/');
                }else{
                    $Db_Heartpro[$k]['img'] = '';
                }




                if(!Buddha_Atom_String::isValidString($v['applystarttimestr'])){
                    $Db_Heartpro[$k]['applystarttimestr']=$CommonObj->getDateStrOfTime($v['applystarttime'],1,0,0);
                }

                if(!Buddha_Atom_String::isValidString($v['applyendtimestr'])){
                    $Db_Heartpro[$k]['applyendtimestr']=$CommonObj->getDateStrOfTime($v['applyendtime'],1,0,0);
                }

                $Db_Heartpro[$k]['view'] = array(
                    'Services'=>'heartpro.merchantview',
                    'param'=>array(
                        'heartpro_id'=>$v['heartpro_id'],
                    ),
                );

                $Db_Heartpro[$k]['update'] = array(
                    'Services'=>'heartpro.beforeupdate',
                    'param'=>array(
                        'heartpro_id'=>$v['heartpro_id'],
                    ),
                );

                $Db_Heartpro[$k]['del'] = array(
                    'Services'=>'heartpro.del',
                    'param'=>array(
                        'heartpro_id'=>$v['heartpro_id'],
                    ),
                );

                $Db_Heartpro[$k]['top'] = array(
                    'Services'=>'payment.infotop',
                    'param'=>array(
                        'heartpro_id'=>$v['heartpro_id'],
                        'good_table'=>$this->tablename,
                    ),
                );


                unset($Db_Heartpro[$k]['level3']);
            }


            $tablewhere = $this->prefix . $this->tablename;

            $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);

            $jsondata['page'] = $temp_Common['page'];
            $jsondata['pagesize'] = $temp_Common['pagesize'];
            $jsondata['totalrecord'] = $temp_Common['totalrecord'];
            $jsondata['totalpage'] = $temp_Common['totalpage'];
            $jsondata['list'] = $Db_Heartpro;

        }

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "商家 {$this->tablenamestr}管理列表");


    }
    /**
     *   商家 1分购 添加之前
     */
    public function beforeadd()
    {

        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $ActivityObj=new Activity();
        $UserObj=new User();
        $ShopObj=new Shop();
        $SupplyObj=new Supply();
        $SupplycatObj=new Supplycat();
        $HeartproObj=new Heartpro();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        if(!$ShopObj->IsUserHasShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000042, '你还未创建店铺，快去创建吧！');
        }

        if(!$ShopObj->IsUserHasNormalShop($user_id))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000043, '你名下的所有店铺异常，请处理后一个(要添加到哪个店铺下)或全部店铺后再添加！');
        }

        if(!$SupplyObj->IsUserHasNormalSupply($user_id))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000070, '您还没用发布供应，或者供应还未通过审核，或者已经全部下架！');

        }

        $jsondata = array();


        /*地区*/
        $jsondata['region']=array(
            'Services' => 'ajaxregion.getBelongFromFatherId',
            'param' => array('father'=>1),
        );

        /**正常店铺列表*/
        $jsondata['shoplist'] = $ShopObj->getShoparrByUserid($user_id);

        $jsondata['unit'] = $SupplycatObj->getunit();
        /**标题**/
        $jsondata['headertitle'] = $this->tablenamestr;

        /**正常店铺下的产品接口*/
        $jsondata['belongshop'] = array(
            'Services' => 'heartpro.getSupplyBelongShopbyShopid',
            'param' => array(),
        );


        /**会员参与规则*/
        $jsondata['partake'] = $HeartproObj->userJoinWhere();


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'添加之前的操作接口');
    }


    /**
     *   商家 1分购 添加
     */
    public function add()
    {
        header("Content-Type:text/html;charset=utf-8");

        if (Buddha_Http_Input::checkParameter(array('usertoken','good_name','goods_unit','shop_id','start_date','end_date','coverphoto_arr','is_remote','goods_desc','partake'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj=new User();


        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $ActivityObj=new Activity();
        $RegionObj=new Region();
        $JsonimageObj = new Jsonimage();
        $ShopObj = new Shop();
        $HeartproObj = new Heartpro();
        $MoregalleryObj = new Moregallery();
        $CommonObj = new Common();
        $UsercommonObj = new Usercommon();

        if(!$UserObj->isHasMerchantPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }


        $goods_name = Buddha_Http_Input::getParameter('good_name');//1分购名称

        $shop_id = Buddha_Http_Input::getParameter('shop_id');//发布店铺内码ID

        if(!$CommonObj->isToUserByTablenameAndTableid('shop',$shop_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000009, '店铺内码ID无效！');
        }

        $shopsupply_id=Buddha_Http_Input::getParameter('shopsupply_id');//商品内码ID
        if(!$CommonObj->isToUserByTablenameAndTableid('supply',$shopsupply_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000060, '商品内码ID无效!');
        }

        $goods_unit=Buddha_Http_Input::getParameter('goods_unit');//属性单位内码ID

        if(!$CommonObj->isIdByTablenameAndTableid('supplycat',$goods_unit))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000064, '属性单位内码ID无效!');
        }

        $price = Buddha_Http_Input::getParameter('price');    //  销售价
        $stock = Buddha_Http_Input::getParameter('stock');    //  库存量
        $votecount=Buddha_Http_Input::getParameter('votecount');//投票数量

        $start_date = Buddha_Http_Input::getParameter('start_date');  //报名开始时间
        $end_date = Buddha_Http_Input::getParameter('end_date');      //报名结束时间
//        $shelvesstart_date=Buddha_Http_Input::getParameter('shelvesstart_date');  //上架时间
//        $shelvesend_date=Buddha_Http_Input::getParameter('shelvesend_date');      //下架时间

        $shelvesstart_date = Buddha::$buddha_array['buddha_timestamp'];  //上架时间
//        $shelvesend_date = Buddha::$buddha_array['buddha_timestamp'] + (15*24*60*60);      //下架时间
        $HeartproObj= new Heartpro();
        $shelvesend_date = $HeartproObj->shelvesend();      //下架时间

/////////////////////////
        /***商品异地发布**/
        $is_remote = Buddha_Http_Input::getParameter('is_remote');      //  是否异地发布
        $level1 = Buddha_Http_Input::getParameter('level1');              //  异地发布区域的ID
        $level2 = Buddha_Http_Input::getParameter('level2');              //  异地发布区域的ID
        $level3 = Buddha_Http_Input::getParameter('level3');              //  异地发布区域的ID

        /*判断 $is_remote 的值是否是0,1*/
        if(!$CommonObj->isIdInDataEffectiveById($is_remote))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }
        /*只有在选择了异地发布的时候才验证地区*/
        if($is_remote==1)
        {
            if (!$RegionObj->isProvince($level1)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000001, '国家、省、市、区 中省的地区内码id不正确！');
            }

            if (!$RegionObj->isCity($level2)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中市的地区内码id不正确！');
            }

            if (!$RegionObj->isArea($level3)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中区的地区内码id不正确！');
            }

        }
//////////////////

        $keywords = Buddha_Http_Input::getParameter('keywords');    //关键词


        //描述、图片
        $goods_desc = Buddha_Http_Input::getParameter('goods_desc');//1分购规则详情
        $partake = Buddha_Http_Input::getParameter('partake');//会员参与规则

        /*相册*/
        $coverphoto_arr = Buddha_Http_Input::getParameter('coverphoto_arr');



        $data['user_id'] = $user_id;
        $data['name'] = $goods_name;//1分购名称
        $data['shop_id'] = $shop_id;//发布店铺内码ID
        $data['table_id'] = $shopsupply_id;//商品内码ID
        $data['table_name'] ='supply';
        $data['unit_id'] = $goods_unit;//属性单位内码ID
        $data['price'] = $price;//销售价
        $data['originalstock'] = $stock;//原始库存
        $data['stock'] = $stock;//库存量
        $data['votecount'] = $votecount;//投票数量
        $data['buddhastatus'] = 0;

        $data['applystarttime'] = strtotime($start_date); //报名开始时间
        $data['applystarttimestr'] = $start_date;
        $data['applyendtime'] = strtotime($end_date); //报名结束时间
        $data['applyendtimestr'] =  $end_date;
        $data['onshelftime'] = strtotime($shelvesstart_date); //上架时间
        $data['onshelftimestr'] = $shelvesstart_date;
        $data['offshelftime'] = strtotime($shelvesend_date); //下架时间
        $data['offshelftimestr'] = $shelvesend_date;
        $data['partake'] = $partake;//会员参与规则

        if($is_remote==0)
        {//$activity_id
            $data['is_remote']=0;
            $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$user_id}' and id='{$shop_id}' and isdel=0");
            $data['level0']=$Db_level['level0'];
            $data['level1']=$Db_level['level1'];
            $data['level2']=$Db_level['level2'];
            $data['level3']=$Db_level['level3'];

        }elseif($is_remote==1){//1为异地

            $data['level1']=$level1;
            $data['level2']=$level2;
            $data['level3']=$level3;
        }

        $data['desc'] = $goods_desc;
        $data['keywords'] = $keywords;
        $data['number']=$CommonObj->GeneratingNumber();

        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $Db_Heartpro_id = $HeartproObj->add($data);

        if(!$Db_Heartpro_id)
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000070, $this->tablenamestr.'添加失败！');
        }

        //相册添加
        $UsercommonObj->photoalbumAdd('moregallery',$this->tablename,$Db_Heartpro_id,$coverphoto_arr,$shop_id,$user_id,$webfield='file');


        /*富文本编辑器图片处理*/
        if($goods_desc)
        {
            $MoregalleryObj=new Moregallery();
            $field='desc';
            $saveData_desc = $MoregalleryObj->base_upload($goods_desc,$Db_Heartpro_id,$this->tablename,$field);
            $saveData_desc = str_replace(PATH_ROOT,'/', $saveData_desc);
            $details['desc'] = $saveData_desc;
            $HeartproObj->edit($details,$Db_Heartpro_id);
        }

        /*是否产生订单：0否；1是*/
        $is_needcreateorder = 0;
        $Services ='';
        $param = array();
        /**$remote==1表示发布异地产品添加订单**/
        if($is_remote==1){
            $is_needcreateorder = 1;
            $Services = '';
            $param = array();
        }

        $jsondata = array();
        $jsondata['db_isok'] =1;
        $jsondata['db_msg'] =$this->tablenamestr.'添加成功';
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['heartpro_id'] = $Db_Heartpro_id;
        $jsondata['is_needcreateorder'] = $is_needcreateorder;
        $jsondata['Services'] = $Services;
        $jsondata['param'] = $param;


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'添加');
    }


    /**
     *  个人中心： 商家 1分购编辑 之前
     */
    public function beforeedit()
    {
        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('usertoken','heartpro_id','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $HeartproObj = new Heartpro();
        $UserObj = new User();
        $ShopObj = new Shop();
        $SupplycatObj = new Supplycat();
        $MoregalleryObj = new Moregallery();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        if(!$ShopObj->IsUserHasShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000042, '你还未创建店铺，快去创建吧！');
        }

        if(!$ShopObj->IsUserHasNormalShop($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000043, '你名下的所有店铺异常，请处理后一个(要添加到哪个店铺下)或全部店铺后再添加！');
        }


        $heartpro_id = (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?(int)Buddha_Http_Input::getParameter('b_display'):2;

        $filed=array('id as heartpro_id,name,price,stock,votecount,onshelftimestr, onshelftime,offshelftime,offshelftimestr,applystarttime,applystarttimestr,applyendtime,applyendtimestr,keywords','table_id','shop_id','level1','level2','level3','table_name','unit_id','is_remote','details','partake');

        $Db_Heartpro = $HeartproObj->getSingleFiledValues($filed,"id='{$heartpro_id}' and user_id='{$user_id}'");


        $jsondata = array();
        $jsondata['imgmore'] = array();
        /**正常店铺列表*/
        $jsondata['shoplist'] = $ShopObj->getShoparrByUserid($user_id);
        /**单位列表*/
        $jsondata['unit'] = $SupplycatObj->getunit();

        /**正常店铺下的产品接口*/
        $jsondata['belongshop'] = array(
            'Services' => 'heartpro.getSupplyBelongShopbyShopid',
            'param' => array(),
        );

        /**会员参与规则*/
        $jsondata['partake'] = $HeartproObj->userJoinWhere();

         /**标题**/
        $jsondata['headertitle'] = $this->tablenamestr;

            /**地区*/
        $jsondata['region'] = array(
        'Services' => 'ajaxregion.getBelongFromFatherId',
        'param' => array('father'=>1),
        );

        if(Buddha_Atom_Array::isValidArray($Db_Heartpro))
        {
            $Supply_name = $this->db->getSingleFiledValues(array('id','goods_name'), $Db_Heartpro['table_name'],"id='{$Db_Heartpro['table_id']}' and user_id='{$user_id}'");
            $Db_Heartpro['supply_name']=$Supply_name['goods_name'];
            $Db_Heartpro['desc']=Buddha_Atom_String::getApiContentFromReplaceEditorContent($Db_Heartpro['desc']);

            $jsondata = $Db_Heartpro;

            /**↓↓↓↓↓↓↓↓↓↓↓ 产品相册 ↓↓↓↓↓↓↓↓↓↓↓**/
            $gimages = $MoregalleryObj->getGoodsImage($heartpro_id,$this->tablename,'file',$b_display);
            IF(!Buddha_Atom_Array::isValidArray($gimages))
            {
                $gimages = array();

            }else{

                foreach ($gimages as $k=>$v)
                {
                    $gimages[$k]['moregallery_id']=$v['id'];
                    $gimages[$k]['img']=$host.$v['goods_thumb'];

                    $gimages[$k]['Services']='moregallery.deleteimage';
                    $gimages[$k]['param']=array('moregallery_id'=>$v['id'],'table_name'=>'moregallery');
                    unset($gimages[$k]['id']);
                    unset($gimages[$k]['goods_thumb']);
                    unset($gimages[$k]['table_name']);
                }
            }


            $jsondata['imgmore'] = $gimages;
            /**↑↑↑↑↑↑↑↑↑↑ 相册 ↑↑↑↑↑↑↑↑↑↑**/


            /**正常店铺列表*/
            $jsondata['shoplist'] = $ShopObj->getShoparrByUserid($user_id,$Db_Heartpro['shop_id']);

            /**单位列表*/
            $jsondata['unit'] = $SupplycatObj->getunit($Db_Heartpro['unit_id']);

            /**正常店铺下的产品接口*/
            $jsondata['belongshop'] = array(
                'Services' => 'heartpro.getSupplyBelongShopbyShopid',
                'param' => array('shop_id'=>$Db_Heartpro['shop_id']),
            );


            /**会员参与规则*/
            $jsondata['partake'] = $HeartproObj->userJoinWhere($Db_Heartpro['partake']);
        }


        /**标题**/
        $jsondata['headertitle'] = $this->tablenamestr;

        /**地区*/
        $jsondata['region'] = array(
            'Services' => 'ajaxregion.getBelongFromFatherId',
            'param' => array('father'=>1),
        );

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'编辑之前的操作接口');
    }



    /**
     *  个人中心： 商家 1分购编辑
     */
    public function edit()
    {
        header("Content-Type:text/html;charset=utf-8");

        if (Buddha_Http_Input::checkParameter(array('usertoken','good_name','goods_unit','shop_id','start_date','end_date','coverphoto_arr','is_remote','goods_desc','partake'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();
        $CommonObj=new Common();
        $UsercommonObj =new Usercommon();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $RegionObj=new Region();
        $JsonimageObj = new Jsonimage();
        $ShopObj = new Shop();
        $HeartproObj = new Heartpro();
        $MoregalleryObj = new Moregallery();
        $CommonObj = new Common();

        if(!$UserObj->isHasMerchantPrivilege($user_id)){

            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');

        }


//////////////////////////////////////////
        $goods_name=Buddha_Http_Input::getParameter('good_name');//1分购名称

        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id'):0;//发布店铺内码ID
        if(!$CommonObj->isToUserByTablenameAndTableid('shop',$shop_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000009, '店铺内码ID无效！');
        }

        $shopsupply_id = (int)Buddha_Http_Input::getParameter('shopsupply_id')?(int)Buddha_Http_Input::getParameter('shopsupply_id'):0;//商品内码ID
        if(!$CommonObj->isToUserByTablenameAndTableid('supply',$shopsupply_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000060, '商品内码ID无效!');
        }

        $goods_unit = (int)Buddha_Http_Input::getParameter('goods_unit')?(int)Buddha_Http_Input::getParameter('goods_unit'):0;//属性单位内码ID
        if(!$CommonObj->isIdByTablenameAndTableid('supplycat',$goods_unit))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000064, '属性单位内码ID无效!');
        }

        $heartpro_id = (int)Buddha_Http_Input::getParameter('heartpro_id')?(int)Buddha_Http_Input::getParameter('heartpro_id'):0;//1分购 内码ID
        if(!$CommonObj->isIdByTablenameAndTableid('heartpro',$heartpro_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000071, '1分购内码ID无效!');
        }
        $Db_Heartpro_id = $heartpro_id;


        $price=Buddha_Http_Input::getParameter('price');//销售价
        $stock=Buddha_Http_Input::getParameter('stock');//库存量
        $votecount=Buddha_Http_Input::getParameter('votecount');//投票数量

        $start_date=Buddha_Http_Input::getParameter('start_date');  //报名开始时间
        $end_date=Buddha_Http_Input::getParameter('end_date');      //报名结束时间
//        $shelvesstart_date=Buddha_Http_Input::getParameter('shelvesstart_date');  //上架时间
//        $shelvesend_date=Buddha_Http_Input::getParameter('shelvesend_date');      //下架时间
/////////////////////////
        /***商品异地发布**/
        $is_remote = Buddha_Http_Input::getParameter('is_remote');    //是否异地发布
        $level1=Buddha_Http_Input::getParameter('level1');   //异地发布区域的ID
        $level2=Buddha_Http_Input::getParameter('level2');   //异地发布区域的ID
        $level3=Buddha_Http_Input::getParameter('level3');   //异地发布区域的ID

        /*判断 $is_remote 的值是否是0,1*/
        if(!$CommonObj->isIdInDataEffectiveById($is_remote)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444444, '参数错误！');
        }
        /*只有在选择了异地发布的时候才验证地区*/
        if($is_remote==1) {
            if (!$RegionObj->isProvince($level1)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000001, '国家、省、市、区 中省的地区内码id不正确！');
            }

            if (!$RegionObj->isCity($level2)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000002, '国家、省、市、区 中市的地区内码id不正确！');
            }

            if (!$RegionObj->isArea($level3)) {
                Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 40000003, '国家、省、市、区 中区的地区内码id不正确！');
            }

        }
//////////////////

        $keywords = Buddha_Http_Input::getParameter('keywords');    //关键词


        //描述、图片
        $goods_desc = Buddha_Http_Input::getParameter('goods_desc');//1分购规则详情


        /*相册*/
        $coverphoto_arr = Buddha_Http_Input::getParameter('coverphoto_arr');
        $partake = Buddha_Http_Input::getParameter('partake');//会员参与规则




        $data['user_id'] = $user_id;
        $data['name'] = $goods_name;//1分购名称
        $data['shop_id'] = $shop_id;//发布店铺内码ID
        $data['table_id'] = $shopsupply_id;//商品内码ID
        $data['table_name'] ='supply';
        $data['unit_id'] = $goods_unit;//属性单位内码ID
        $data['price'] = $price;//销售价
        $data['originalstock'] = $stock;
        $data['stock'] = $stock;//库存量
        $data['votecount'] = $votecount;//投票数量
        $data['applystarttime'] = strtotime($start_date); //报名开始时间
        $data['applystarttimestr'] = $start_date;
        $data['applyendtime'] = strtotime($end_date); //报名结束时间
        $data['applyendtimestr'] =  $end_date;
      //  $data['onshelftime'] = strtotime($shelvesstart_date); //上架时间
       // $data['onshelftimestr'] = $shelvesstart_date;
        //$data['offshelftime'] = strtotime($shelvesend_date); //下架时间
       // $data['offshelftimestr'] = $shelvesend_date;
        if($is_remote==0)
        {//$activity_id
            $data['is_remote']=0;
            $Db_level = $ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$user_id}' and id='{$shop_id}' and isdel=0");
            $data['level0'] = $Db_level['level0'];
            $data['level1'] = $Db_level['level1'];
            $data['level2'] = $Db_level['level2'];
            $data['level3'] = $Db_level['level3'];

        }elseif($is_remote==1){//1为异地

            $data['level1'] = $level1;
            $data['level2'] = $level2;
            $data['level3'] = $level3;
        }
        $data['desc'] = $goods_desc;
        $data['keywords'] = $keywords;
        $data['number'] = $CommonObj->GeneratingNumber();
        $data['partake'] = $partake;//会员参与规则

        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $HeartproObj->edit($data,$heartpro_id);

        /*相册添加*/
        $UsercommonObj->photoalbumAdd('moregallery',$this->tablename,$Db_Heartpro_id,$coverphoto_arr,$shop_id,$user_id,$webfield='file');

        /*富文本编辑器图片处理*/
        if($goods_desc){
            $MoregalleryObj = new Moregallery();
            $field='desc';
            $saveData_desc = $MoregalleryObj->base_upload($goods_desc,$Db_Heartpro_id,$this->tablename,$field);
            $saveData_desc = str_replace(PATH_ROOT,'/', $saveData_desc);
            $details['desc'] = $saveData_desc;
            $HeartproObj->edit($details,$Db_Heartpro_id);
        }

        /*是否产生订单：0否；1是*/
        $is_needcreateorder = 0;
        $Services ='';
        $param = array();
        /**$remote==1表示发布异地产品添加订单**/
        if($is_remote==1){
            $is_needcreateorder = 1;
            $Services = '';
            $param = array();
        }

        $jsondata = array();
        $jsondata['db_isok'] =1;
        $jsondata['db_msg'] =$this->tablenamestr.'编辑成功';
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['heartpro_id'] = $Db_Heartpro_id;
        $jsondata['is_needcreateorder'] = $is_needcreateorder;
        $jsondata['Services'] = $Services;
        $jsondata['param'] = $param;


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'编辑');
    }


    /**
     * 个人中心：商家 1分购删除
     */

    public function del()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken','heartpro_id')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $UserObj=new User();
        $CommonObj=new Common();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $heartpro_id = Buddha_Http_Input::getParameter('heartpro_id')?Buddha_Http_Input::getParameter('heartpro_id'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if(!$UserObj->isHasMerchantPrivilege($user_id))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');

        }

        /*判断 1分购 Id是否有效*/
        if(!$CommonObj->isIdByTablenameAndTableid($this->tablename,$heartpro_id))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000071	, '1分购内码ID无效!！');
        }

        $UsercommonObj=new Usercommon();

        //相册删除并且信息删除
        $Db_Heartpro_num = $UsercommonObj->photoalbumDel('moregallery',$this->tablename,$heartpro_id,$user_id);

        $jsondata = array();

        if($Db_Heartpro_num)
        {
            $jsondata['is_ok'] = 1;
            $jsondata['db_msg'] = $this->tablenamestr.'删除成功!';
        }else{

            $jsondata['is_ok'] = 0;
            $jsondata['db_msg'] = $this->tablenamestr.'删除失败!';
        }

        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['heartpro_id'] = $heartpro_id;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'删除');
    }

    /**
     *  首页1分营销 列表头部
     */
    public function frontlistnav(){


        $view = (int)Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):1;



        $view0=$view1=$view2=$view3=0;
        if($view==1){
            $view0=1;
        }elseif($view==2){
            $view1=1;
        }elseif($view==3){
            $view2=1;
        }
        elseif($view==5){
            $view3=1;
        }else{
            $view0=1;
        }
        $Services ='heartpro.frontlist';
        $header[0] = array( 'select'=>$view0,'name'=>'附近','namevalue'=>1,
            'Services'=>$Services,'param'=>array('view'=>1));
        $header[1] = array( 'select'=>$view1,'name'=>'最新','namevalue'=>2,
            'Services'=>$Services,'param'=>array('view'=>2));
        $header[2] = array( 'select'=>$view2,'name'=>'热门','namevalue'=>3,
            'Services'=>$Services,'param'=>array('view'=>3));
        $header[3] = array( 'select'=>$view3,'name'=>'商家','namevalue'=>5,
            'Services'=>$Services,'param'=>array('view'=>5));

        $jsondata = $header;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "1分购列表顶部导航");


    }

    /**
     *   首页 1分购 列表
     */
    public function frontlist(){

        $host = Buddha::$buddha_array['host'];

        if (Buddha_Http_Input::checkParameter(array('api_number','b_display')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        /*城市编号*/
        $RegionObj=new Region();
        $ShopObj = new Shop();
        $UserObj = new User();
        $CommonObj = new Common();

        $api_number = (int)Buddha_Http_Input::getParameter('api_number')?(int)Buddha_Http_Input::getParameter('api_number'):0;
        if(!$RegionObj->isValidRegion($api_number))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444007, '地区编码不对（地区编码对应于腾讯位置adcode）');
        }

        $locdata = $RegionObj->getApiLocationByNumberArr($api_number);

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;
        $page = (int)Buddha_Http_Input::getParameter('page')?(int)Buddha_Http_Input::getParameter('page'):1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize') ? (int)Buddha_Http_Input::getParameter('pagesize') : Buddha::$buddha_array['page']['pagesize'];

        $pagesize = (int)Buddha_Atom_Secury::getMaxPageSize($pagesize);

        /*当前位置 纬度*/
        $lats = (int)Buddha_Http_Input::getParameter('lat')?Buddha_Http_Input::getParameter('lat'):0;

        /*当前位置 经度*/
        $lngs = (int)Buddha_Http_Input::getParameter('lng')?Buddha_Http_Input::getParameter('lng'):0;


        if($lats==0 and $lngs==0  and $api_number>0 ){
            $lats = $locdata['lat'];
            $lngs = $locdata['lng'];

        }

        $b_display = (int)Buddha_Http_Input::getParameter('b_display') ? Buddha_Http_Input::getParameter('b_display') : 2;
        $keyword = Buddha_Http_Input::getParameter('keyword');
        $view = Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') : 15;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);
        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id') : 0;
        $orderby = " ORDER BY createtime DESC";


        $where = " isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']}";


        $orderby = "";
        if(Buddha_Atom_String::isValidString($shop_id))
        {
            $where .= "AND shop_id='{$shop_id}'";
        }else{
            $orderby = " group by shop_id ";
        }
        $orderby .= " ORDER BY createtime DESC";


        if ($view) {
            switch ($view)
            {
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

        $fields = array('id', 'shop_id','user_id', 'name','price', 'small as demand_thumb');

        /*先查询：当地有没有过期了但没有下架的1分购：有就下架*/

        $CommonObj->UpdateShelvesStatus($this->tablename,'onshelftime','offshelftime',$locdata['sql']);



        $list = $this->db->getFiledValues ($fields,  $this->prefix.$this->tablename, $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );




        $jsondata = array();

        $jsondata['page'] = 0;
        $jsondata['pagesize'] = 0;
        $jsondata['totalrecord'] = 0;
        $jsondata['totalpage'] = 0;
        $lease = array();
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
                'heartpro_id'=>$v['id'],
                'name'=>$v['name'],
                'price'=>$v['price'],
                'shop_name'=>$name,

                'icon_price'=>Buddha_Atom_String::getApiFileUrlStr('apishop/menuplus/icon_price.png'),
                'icon_shop'=>Buddha_Atom_String::getApiFileUrlStr('apishop/menuplus/icon_shop.png'),

                'roadfullname'=>$roadfullname,
                'img'=> Buddha_Atom_String::getApiFileUrlStr($v['demand_thumb'])
            );
        }


        $tablewhere = $this->prefix . $this->tablename;
        $temp_Common = $CommonObj->pagination($tablewhere, $where, $pagesize, $page);

        $jsondata['page'] = $temp_Common['page'];
        $jsondata['pagesize'] = $temp_Common['pagesize'];
        $jsondata['totalrecord'] = $temp_Common['totalrecord'];
        $jsondata['totalpage'] = $temp_Common['totalpage'];

        $jsondata['list'] = $lease;

        $jsondata['page_title'] = '1分营销';
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "1分营销");




    }

    /**
     * 1分购详情
     */
    public function info(){

        if (Buddha_Http_Input::checkParameter(array('heartpro_id','b_display')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $SupplyObj = new Supply();
        $UserObj = new User();
        $CommonObj = new Common();
        $HeartplusObj = new Heartplus();
        $HeartapplyObj = new Heartapply();
        $HeartproObj = new Heartpro();
        $is_join = $is_log = 0;

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $heartpro_id = Buddha_Http_Input::getParameter('heartpro_id')?Buddha_Http_Input::getParameter('heartpro_id'):0;
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;

        if(Buddha_Atom_String::isValidString($usertoken)){
            $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        }


        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];
        $jsondata = array();
        /*查询 1分购 数据*/


        $where = "id='{$heartpro_id}' and buddhastatus=0";
        $sql = " SELECT  id as heartpro_id,medium as img,name,price,table_id,click_count,votecount,stock,
                          keywords,details,price,applyendtime,applyendtimestr
                 FROM {$this->prefix}{$this->tablename} WHERE {$where}
                 " ;

        $Db_Heartpro = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $Db_Heartpro = $Db_Heartpro[0];




        if(Buddha_Atom_Array::isValidArray($Db_Heartpro)){

            $table_id=$Db_Heartpro['table_id'];
            $Db_Supply = $SupplyObj->getSingleFiledValues(array('shop_id'),"id='{$table_id}' ");
            $Db_Heartpro['shop_id'] = Buddha_Atom_String::getApiValidStr($Db_Supply['shop_id']);

            $Db_Heartpro['img'] = Buddha_Atom_String::getApiFileUrlStr($Db_Heartpro['shop_id']);
            $Db_Heartpro['page_time1'] ='1分购竞买倒计时：';
            $Db_Heartpro['page_rule1'] ='1分购活动规则：';
            $Db_Heartpro['page_rule2'] ='竞买人邀请好友协助投票，投票数达到"单品投票数"即可0.01元购得拍品,数量有限,先"到"先得！';
            $Db_Heartpro['page_input1'] ='请输入名称或者编号：';



            $Db_Heartpro['button1_click'] = array('name'=>'活动二维码','Services'=>'heartpro.voteprize','param'=>array('heartpro_id'=>'?'));
            $Db_Heartpro['button2_click'] = array('name'=>'活动详情','Services'=>'heartpro.voteprize','param'=>array('heartpro_id'=>'?'));
            $Db_Heartpro['button3_click'] = array('name'=>'搜索','Services'=>'heartpro.votelist','param'=>array('type'=>2,'heartpro_id'=>'?','keyword'=>'?'));
            $Db_Heartpro['button4_click'] = array('name'=>'人气排序','Services'=>'heartpro.votelist','param'=>array('type'=>2,'heartpro_id'=>'?'));
            $Db_Heartpro['button5_click'] = array('name'=>'我要竞买','Services'=>'heartpro.votelist','param'=>array('type'=>3,'heartpro_id'=>'?'));

            $Db_Heartpro['button'] ='活动详情：';



        }



        /*更新浏览次数*/
        $data['click_count'] = $Db_Heartpro['click_count']+1;
        $HeartproObj->edit($data,$heartpro_id);
        $Db_Supply = $SupplyObj->getSingleFiledValues(array('market_price')," id='{$Db_Heartpro['table_id']}'");
        $jsondata =$Db_Heartpro;
        $jsondata['market_price']=$Db_Supply['market_price'];
        $jsondata['click_count']= $data['click_count'] ;

        $Heartapplywhere = ' heartpro_id='.$heartpro_id;

        $jsondata['applycount'] = $HeartapplyObj->countRecords($Heartapplywhere);//统计申请 1分购申请人表 的数量



        $Heartpluswhere = ' heartpro_id='.$heartpro_id;

        $jsondata['ticketcount'] = $HeartplusObj->countRecords($Heartpluswhere);//求和：投票的总数

        $sql = "SELECT SUM(vote_num) as num FROM {$this->prefix}heartapply WHERE {$Heartpluswhere} ";//求和：投票的总数

        $praise_num = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        /**↓↓↓↓↓↓头部轮播图 查询↓↓↓↓↓↓**/
        $MoregalleryObj = new Moregallery();
        $More = $MoregalleryObj->getFiledValues(array('id as img_id','goods_img as img'),"goods_id={$heartpro_id} and tablename='{$this->tablename}' and webfield='file'");

        if(Buddha_Atom_Array::isValidArray($More)){
            foreach($More as $k=>$v){
                $More[$k]['img'] =Buddha_Atom_String::getApiFileUrlStr($v['img']);
            }
        }


        /**分享**/
        $share_desc = Buddha_Atom_Html::tripHtmlTag($Db_Heartpro['desc']);
        if(Buddha_Atom_String::isValidString($Db_Heartpro['keyword'])){
            $share_desc=$Db_Heartpro['keyword'];
        }



        $share_imgUrl = Buddha_Atom_String::getApiFileUrlStr($Db_Heartpro['img']);
        $sharearr = array(
            'share_title'=>$Db_Heartpro['name'],
            'share_desc'=>$share_desc,
            'share_link'=> Buddha_Atom_Share::getShareUrl('heartpro.detail',$heartpro_id),
            'share_imgUrl'=>$share_imgUrl,
        );
        $jsondata['sharearr'] = $sharearr;
        /**分享**/

        $jsondata['banner'] = $More;
        $jsondata['nowtime'] = Buddha::$buddha_array['buddha_timestamp'];


        $jsondata['nowtimestr'] = Buddha::$buddha_array['buddha_timestr'];

        $jsondata['votecount'] = $Db_Heartpro['votecount'];
        $jsondata['stock'] = $Db_Heartpro['stock'];



        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "1分购详情");

    }

    /**
     * 1分购  投票列表
     */
    public function votelist(){
        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('heartpro_id','b_display')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $HeartapplyObj = new Heartapply();
        $CommonObj = new Common();

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;
        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $heartpro_id = (int)Buddha_Http_Input::getParameter('heartpro_id')?Buddha_Http_Input::getParameter('heartpro_id'):2;

        if(Buddha_Atom_String::isValidString($usertoken)){
            $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        }


        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        $type = Buddha_Http_Input::getParameter('type')?Buddha_Http_Input::getParameter('type'):2;// 1 搜索; 2人气  ； 3 最新（表示点击过来的）







        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') : 50;
        $pagesize = Buddha_Atom_Secury::getMaxPageSize($pagesize);
        $keyword = Buddha_Http_Input::getParameter('keyword');


        $is_log = 0;
        $is_buy = 0;
        $myvote = 0;

        $is_join = 0;

        $limit = Buddha_Tool_Page::sqlLimit ($page, $pagesize);
        $where = ' a.heartpro_id='.$heartpro_id;
        if(!empty($keyword))
        {
            $where .= " and u.realname like '%{$keyword}%' or a.number like '%{$keyword}%'";
        }



        /**查询当前用户的投票数量和是否已经购买过了**/
        if(Buddha_Atom_String::isValidString($user_id))
        {
            $votewhere = " heartpro_id='{$heartpro_id}' AND user_id='{$user_id}'";

            $Db_Heartapply = $HeartapplyObj->getSingleFiledValues(array('vote_num','is_buy'),$votewhere);
            $is_log = 1;

            if(Buddha_Atom_Array::isValidArray($Db_Heartapply)){
                $myvote = (int)$Db_Heartapply['vote_num'];
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

        if($type==2)
        {//2人气、3最新、4 我参与
            $orderby = ' order by a.vote_num desc';
        }elseif($type==3)
        {//2人气、3最新、4 我参与
            $orderby = ' order by a.createtime desc';
        }




        $sql = "select a.id as heartapply_id,a.user_id,a.vote_num,a.number,a.is_buy,u.logo,u.realname,u.logo,u.realname
                from {$this->prefix}heartapply as a
                INNER join {$this->prefix}user as u
                on u.id = a.user_id
                where {$where} {$orderby} {$limit}";

        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);


        $currentrankings = '暂无';
        foreach($list as $k=>$v)
        {

            $list[$k]['logo'] = Buddha_Atom_String::getUserLogo($v['logo'],'style/images/im.png') ;



            if($v['is_buy']==1)
            {
                $list[$k]['icon_buy'] = $host.'style/img_two/successfulbidding.png';
            }else{
                $list[$k]['icon_buy'] = '';
            }

            if($v['user_id'] == $user_id){
                $currentrankings = $k+1;
            }
        }

        /**判断当前用户是否已经购买过了:0否；1是*/




        $jsondata = array();
        $jsondata['page'] = 0;
        $jsondata['pagesize'] = 0;
        $jsondata['totalrecord'] = 0;
        $jsondata['totalpage'] = 0;
        $jsondata['myvote'] = 0;


        /****查总数****/
        $temp_sql =" SELECT count(*) AS total
                from {$this->prefix}heartapply as a
                INNER join {$this->prefix}user as u
                on u.id = a.user_id
                where {$where} ";


        $count_arr = $this->db->query($temp_sql)->fetchAll(PDO::FETCH_ASSOC);

        $rcount = $pcount = 0;

        if(Buddha_Atom_Array::isValidArray($count_arr))
        {
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
        }
        /****查总数****/

        $jsondata['page'] = $page;
        $jsondata['pagesize'] = $pagesize;
        $jsondata['totalrecord'] = $rcount;
        $jsondata['totalpage'] = $pcount;

        $jsondata['is_log'] = $is_log;
        $jsondata['is_buy'] = $is_buy;
        $jsondata['is_join'] = $is_join;
        $jsondata['type'] = $type;
        $jsondata['keyword'] = $keyword;
        $jsondata['currentrankings'] = $currentrankings;
        $jsondata['myvote'] = $myvote;
        $jsondata['list'] = $list;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "1分购投票列表");


    }

    /**
     * 1分购竞买申请
     */
    public function apply(){
        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('usertoken','heartpro_id','b_display')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $HeartproObj = new Heartpro();
        $HeartapplyObj = new Heartapply();
        $CommonObj = new Common();

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;
        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $heartpro_id = (int)Buddha_Http_Input::getParameter('heartpro_id')?Buddha_Http_Input::getParameter('heartpro_id'):2;
        $nowtime=Buddha::$buddha_array['buddha_timestamp'];

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if(!$HeartproObj->isHasValidRecord($heartpro_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '1分购主表内码id不存在');
        }


        if($HeartapplyObj->isHadBuy($heartpro_id,$user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '当前登录会员已经购买过1分购');
        }





        if($HeartproObj->isExpire($heartpro_id))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '报名已结束，不能报名!');
        }

        if($HeartapplyObj->isHadVote($heartpro_id,$user_id) )
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '您已经申请过了,请不要重复竞买申请！');
        }

        if(!$HeartproObj->isValidStock($heartpro_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '抱歉拍品数量不足，无法参与，欢迎你的下次光临！');
        }


        $data['user_id'] = $user_id;
        $data['heartpro_id'] = $heartpro_id;
        $data['number']=$CommonObj->GeneratingNumber();
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $heartapply_id = $HeartapplyObj->add($data);


        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['heartapply_id'] = $heartapply_id;


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "1分购竞买申请");
    }

    /**
     * 投票添加
     */
    public function vote(){
        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('usertoken','heartpro_id','heartapply_id','b_display')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $HeartproObj = new Heartpro();
        $HeartapplyObj = new Heartapply();
        $HeartplusObj = new Heartplus();
        $CommonObj = new Common();

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;
        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $heartapply_id = (int)Buddha_Http_Input::getParameter('heartpro_id')?Buddha_Http_Input::getParameter('heartapply_id'):2;
        $heartpro_id = (int)Buddha_Http_Input::getParameter('heartpro_id')?Buddha_Http_Input::getParameter('heartpro_id'):2;


        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$HeartproObj->isStart($heartpro_id))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '竞买时间还未开始，不能投票');
        }

        if($HeartproObj->isExpire($heartpro_id))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '报名已结束，不能报名!');
        }


        $Db_Heartpro = $HeartproObj->getSingleFiledValues(array('applystarttime','applyendtime','partake','user_id')," id='{$heartpro_id}'");
        $time = $CommonObj->time_handle('createtime');
        $where = $time['where'];//昨天的0点<当前时间<明天的0点时间
        $Heartpluswhere = $where." and heartpro_id ='{$heartpro_id}' and user_id='{$user_id}' ";


        if($Db_Heartpro['partake']==1 AND !($UserObj->isCouldHeartTicket($user_id)))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '您的投票权已用完，喊您朋友来吧！');

        }
        if($Db_Heartpro['partake']==2 AND $HeartplusObj->countRecords($Heartpluswhere))
        {  //新老会员都可以参与    //查询用户是否已经存在投票时间
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 444444, '您的投票权已用完，喊您朋友来吧！');
        }

       //查询1分购申请人的投票次数
        $Db_Heartapply = $HeartapplyObj->getSingleFiledValues(array('id','vote_num')," heartpro_id ='{$heartpro_id}' and id='{$heartapply_id}' ");

        $data['user_id'] = $user_id;
        $data['heartpro_id'] = $heartpro_id;//1分购
        $data['heartapply_id'] = $heartapply_id;//1分购申请人表
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $heartplus_id = $HeartplusObj->add($data);

        $Heartplus_num = $HeartplusObj->countRecords("heartpro_id='{$heartpro_id}'");

        $Db_Heartappl_vote_num = $Db_Heartapply['vote_num']+1;//投票次数加一

        if($Db_Heartappl_vote_num == $Heartplus_num)
        {
            $data_Heartapply['vote_num'] = $Heartplus_num;
        }else{
            $data_Heartapply['vote_num'] = $Db_Heartappl_vote_num;
        }

        $HeartapplyObj->edit($data_Heartapply,$heartapply_id);

        $msg = '感谢您为我投票。您也去参加竞买吧!';
        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['heartplus_id'] = $heartplus_id;
        $jsondata['msg'] = $msg;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "投票添加");


    }


    /**
     * 1分购奖品和详情
     */
    public function voteprize(){
        $host = Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('heartpro_id','b_display')))
        {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $HeartproObj = new Heartpro();
        $ShopObj = new Shop();
        $HeartapplyObj = new Heartapply();
        $HeartplusObj = new Heartplus();
        $CommonObj = new Common();

        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?Buddha_Http_Input::getParameter('b_display'):2;
        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $heartpro_id = (int)Buddha_Http_Input::getParameter('heartpro_id')?Buddha_Http_Input::getParameter('heartpro_id'):2;

        if(Buddha_Atom_String::isValidString($usertoken)){
            $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        }


        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        $Db_Heartpro = $HeartproObj->getSingleFiledValues(array('details','small','votecount','stock','small','name','shop_id','name'),"id='{$heartpro_id}' AND buddhastatus=0");
        $shop_where = "id='{$Db_Heartpro['shop_id'] }'";
        $Db_Shop = $ShopObj->getSingleFiledValues(array('small','name'),$shop_where);
        $Heartpro_codeimg = $HeartproObj->createQrcodeForCodeSales($heartpro_id,$Db_Shop['small'],$Db_Shop['name'],$event='heartpro',$eventpage='info',$Db_Heartpro['name'],$Db_Heartpro['small']);

        $Db_Heartpro['desc'] = $Db_Heartpro['desc'].'  <br/> 最少投票数量：'.$Db_Heartpro['votecount'].';  <br/> <br/> 库存量：'.$Db_Heartpro['stock'];
        $Db_Heartpro['codeimg'] = $host.$Heartpro_codeimg;

        $Db_Heartpro['img'] = Buddha_Atom_String::getApiFileUrlStr($Db_Heartpro['small']);
        unset(  $Db_Heartpro['small']);

        $jsondata = array();
        $jsondata =$Db_Heartpro;
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;




        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, "1分购奖品和详情");
    }


    /**
     *  获取店铺下正常的产品
     */
    public function getSupplyBelongShopbyShopid()
    {
      if (Buddha_Http_Input::checkParameter(array('usertoken','shop_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $SupplyObj = new Supply();

        $usertoken = Buddha_Http_Input::getParameter('usertoken')?Buddha_Http_Input::getParameter('usertoken'):0;
        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');
        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id'):0;
        $keyword = Buddha_Http_Input::getParameter('keyword')?Buddha_Http_Input::getParameter('keyword'):'';



        if(!$UserObj->isHasMerchantPrivilege($user_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000022, '你还未申请商家会员角色！');
        }

        $Db_Supply = $SupplyObj-> getSupplyBelongShopbyShopid($shop_id,$user_id,$keyword);

        $jsondata = array();

        $jsondata = $Db_Supply;

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, $this->tablenamestr.'获取店铺下正常的产品');
    }


}