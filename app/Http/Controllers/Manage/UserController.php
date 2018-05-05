<?php
/**
 * Class UserController
 */
class UserController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function more(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $UserObj = new User();
        $RegionObj=new Region();
        $MemberObj = new Member();
        $params = array ();
        $usertype=$UserObj->usertype();
        $this->smarty->assign('usertype', $usertype);

        list($hsk_adminsid, $hsk_adminuser, $hsk_adminpw, $hsk_admintime) = explode("\t",  Buddha_Tool_Password::cookieDecode(Buddha_Http_Cookie::getCookie('buddha_adminsid') , Buddha::$buddha_array['cookie_hash']));
        $uid= $hsk_adminsid;
        $member =$MemberObj->getSingleFiledValues(array('id','adminid','memberid','permissions','pri'),"id={$uid}");
        if($member['id']  && $member['memberid']==0){
            $this->smarty->assign('utype','1');
        }
        if($member['id'] != 1){
            if($member['memberid']==0 && stripos($member['permissions'],'78')){
                $this->smarty->assign('dels','1');
            }
        }else{
            $this->smarty->assign('dels','1');
        }
        
        $view=Buddha_Http_Input::getParameter('view')?Buddha_Http_Input::getParameter('view'):4;
        $params['view'] = $view;
        $strid='';

        $where = " isdel=0 ";
        $todaytime = strtotime(date("Y-m-d"));
        if($view){
            switch($view) {
                case 1:
                    $where .= " and groupid=1";
                    break;
                case 2;
                    $where.=" and groupid=2";
                    break;
                case 3:
                    $where.=" and groupid=3";
                    break;
                case 4;
                    $where.=" and groupid=4";
                    break;
                case 5;
                    $where.=" and id in({$strid})";
                    break;
                case 6;
                    $where.=" and onlineregtime>{$todaytime} ";
                    break;
            }
        }
        $exportListId = Buddha_Http_Input::getParameter('jb');//列表导出参数
        $option = Buddha_Http_Input::getParameter('option');//检索类型  exportListId
        $params ['option'] = $option;
        $keyword = trim( Buddha_Http_Input::getParameter('keyword'));
        $params ['keyword'] = $keyword;

        $start = Buddha_Http_Input::getParameter('start');
        $end = Buddha_Http_Input::getParameter('end');
        if($start!=''){
            $params ['start'] = $start;
        }
        if($end!=''){
            $params ['end'] = $end;
        }


        if (count($option)) {
            if (count($keyword)) {
                switch ($option) {
                    case '5' : //用户名
                        $where .= " and username LIKE '%{$keyword}%'";
                        break;
                    case '4' ://市
                        $Regionobj = new Region();
                        $regions = $Regionobj->getSingleFiledValues(array('id'),"fullname  LIKE '%{$keyword}%'");
                        $where .= " and level2={$regions['id']}";
                        break;
                    case '3' ://姓名
                        $where .= " and realname LIKE '%{$keyword}%'";
                        break;
                    case '2' ://手机号
                        $where .= " and mobile LIKE '%{$keyword}%'";
                        break;
                    case '1' : //用户名
                        $where .= " and username LIKE '%{$keyword}%'";
                        break;
                }
            }

        }

        $searchType = array (1 => '用户名', 2 => '手机号码', 3 => '真实姓名',4 => '市');
        $this->smarty->assign('searchType',$searchType);
        $this->smarty->assign( 'params', $params );
        $this->smarty->assign( 'keyword', $keyword );
        $this->smarty->assign( 'start', $start );
        $this->smarty->assign( 'end', $end );


        $rcount= $UserObj->countRecords($where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by id DESC ";

        $list = $this->db->getFiledValues('', $this->prefix . 'user', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));
        if($exportListId){
            $array = array('id','groupid','username','mobile','realname','level1','level2','level3','address','onlineregtime');

            $exportList = $this->db->getFiledValues($array, $this->prefix . 'user', $where . $orderby);

                foreach($exportList as $k => $v){

                    $level1=$RegionObj->getSingleFiledValues(array('name'),"id='{$v['level1']}'");
                    $level2=$RegionObj->getSingleFiledValues(array('name'),"id='{$v['level2']}'");
                    $level3=$RegionObj->getSingleFiledValues(array('name'),"id='{$v['level3']}'");
                    $exportList[$k]['level1']=$level1['name'];
                    $exportList[$k]['level2']=$level2['name'];
                    $exportList[$k]['level3']=$level3['name'];
                    if($v['groupid'] == 1){
                       $exportList[$k]['groupid']= '商家会员'; 
                    }
                }
                $str = "编号\t用户名\t会员级别\t姓名\t手机号\t省\t市\t区/县\t详细地址\t注册时间\n";
                $str = iconv('utf-8','gb2312',$str);
                foreach($exportList as $k=>$row){
                    $uid = iconv('utf-8','gb2312',$row['id']);//编号
                    $username= iconv('utf-8','gb2312',$row['username']);//用户名
                    $operateuse= iconv('utf-8','gb2312',$row['groupid']);//会员级别
                    $realname= iconv('utf-8','gb2312',$row['realname']);//姓名
                    $operatedesc =iconv('utf-8','gb2312',$row['mobile']);//手机号
                    $level1=iconv('utf-8','gb2312',$row['level1']);//省
                    $level2=iconv('utf-8','gb2312',$row['level2']);//市
                    $level3=iconv('utf-8','gb2312',$row['level3']);//区
                    $address=iconv('utf-8','gb2312',$row['address']);//详细地址

                    $logdate= iconv('utf-8','gb2312',date('Y-m-d',$row['onlineregtime']));//注册日期
                    $str .= $uid."\t".$username."\t".$operateuse."\t".$realname."\t".$operatedesc."\t".$level1."\t".$level2."\t".$level3."\t".$address."\t".$catName."\t".$logdate."\n"; 
                }

            
            $filename = 'user_'.date('YmdHis').'.xls'; //设置文件名
            echo  Buddha_Tool_File::exportExcel($filename,$str);//导出
            die();
        }
        if($view==2 or $view==3 or $view==1){
        foreach($list as $k=>$v){

            $level1=$RegionObj->getSingleFiledValues(array('name'),"id='{$v['level1']}'");
            $level2=$RegionObj->getSingleFiledValues(array('name'),"id='{$v['level2']}'");
            $level3=$RegionObj->getSingleFiledValues(array('name'),"id='{$v['level3']}'");
            $list[$k]['level1']=$level1['name'];
            $list[$k]['level2']=$level2['name'];
            $list[$k]['level3']=$level3['name'];
        }
        }



        $strPages =  Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=user&'
            .http_build_query($params).'&'
            , $pagesize);
        $this->smarty->assign('rcount', $rcount);
        $this->smarty->assign('pcount', $pcount);
        $this->smarty->assign('page', $page);
        $this->smarty->assign('strPages', $strPages);
        $this->smarty->assign('list', $list);
        $this->smarty->assign( 'params', $params );

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }
    public function add(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $UserObj = new User();
        $RegionObj = new  Region();
        $top_list_1 = $RegionObj->getFiledValues(''," isdel=0 and father=1 ");
        $option_list_1="<option value=''>请选择</option>";
        foreach($top_list_1 as $k=>$v){
            $option_list_1.="<option value='{$v['id']}'>{$v['name']}</option> ";

        }
        $this->smarty->assign('option_list_1', $option_list_1);

        $usertype=$UserObj->usertype();

        $this->smarty->assign('usertype', $usertype);
        //获取数据
         $username=Buddha_Http_Input::getParameter('username');
         $realname=Buddha_Http_Input::getParameter('realname');
         $groupid=Buddha_Http_Input::getParameter('typeid');
         $mobile=Buddha_Http_Input::getParameter('mobile');
         $tel=Buddha_Http_Input::getParameter('tel');
         $email=Buddha_Http_Input::getParameter('email');
         $password=Buddha_Http_Input::getParameter('password');
         $level1=Buddha_Http_Input::getParameter('level1');
         $level2=Buddha_Http_Input::getParameter('level2');
         $level3=Buddha_Http_Input::getParameter('level3');
         $agentrate=Buddha_Http_Input::getParameter('agentrate');
         $partnerrate=Buddha_Http_Input::getParameter('partnerrate');

        if(Buddha_Http_Input::isPost()){
            $step1 = $this->existnickname($username);
            if(!$step1){
                Buddha_Http_Head::redirect('用户名已存在',"index.php?a=more&c=user");
            }
            if($groupid!=2){
            $step2 = $this->existmobile($mobile);
                if(!$step2){
                    Buddha_Http_Head::redirect('手机号已存在',"index.php?a=more&c=user");
                }
            }
            $data=array();
            $data['username']=$username;
            $data['realname']=$realname;
            $data['groupid']=$groupid;
            if($groupid != 2){
                $data['to_group_id']= '4' . ',' . $groupid;
            }else{
                $data['to_group_id']= '';
            }
            $data['mobile']=$mobile;
            $data['tel']=$tel;
            $data['email']=$email;
            $data['password']=Buddha_Tool_Password::md5($password);
            $data['codes']=$password;
            $data['state']=1;
            $data['onlineregtime']=Buddha::$buddha_array['buddha_timestamp'];
            $data['level0']='1';
            $data['level1']=(int)$level1;
            $data['level2']=(int)$level2;
            $data['level3']=(int)$level3;
            if($groupid==2){
            $data['agentrate']=$agentrate;
            }
            if($groupid==3){
            $data['partnerrate']=$partnerrate;
            }

            $adduser=$UserObj->add($data);
            if($adduser){
                Buddha_Http_Head::redirect('添加成功',"index.php?a=more&c=user");
            }else{
                Buddha_Http_Head::redirect('添加失败',"index.php?a=add&c=user");
            }

        }

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }


    public function edit(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $UserObj = new User();
        $Regionobj=new Region();
        $view=Buddha_Http_Input::getParameter('view');
        $id=Buddha_Http_Input::getParameter('id');
        $usertype=$UserObj->usertype();
        $this->smarty->assign('usertype', $usertype);
        $username=Buddha_Http_Input::getParameter('username');
        $realname=Buddha_Http_Input::getParameter('realname');
        $groupid=Buddha_Http_Input::getParameter('typeid');
        $mobile=Buddha_Http_Input::getParameter('mobile');
        $tel=Buddha_Http_Input::getParameter('tel');
        $email=Buddha_Http_Input::getParameter('email');
        $password=Buddha_Http_Input::getParameter('password');
        $level1=Buddha_Http_Input::getParameter('level1');
        $level2=Buddha_Http_Input::getParameter('level2');
        $level3=Buddha_Http_Input::getParameter('level3');
        $agentrate=Buddha_Http_Input::getParameter('agentrate');
        $partnerrate=Buddha_Http_Input::getParameter('partnerrate');

        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['groupid']=$groupid;
            $data['email']=$email;

            if($password){
                $data['password']=Buddha_Tool_Password::md5($password);
                $data['codes']=$password;
            }
            $data['state']=1;
            $data['level0']='1';
            $data['level1']=$level1;
            $data['level2']=$level2;
            $data['level3']=$level3;
            $data['tel']=$tel;
            $data['realname']=$realname;
            $data['onlineregtime']=Buddha::$buddha_array['buddha_timestamp'];
            if($groupid==2){
                $data['agentrate']=$agentrate;
            }
            if($groupid==3){
                $data['partnerrate']=$partnerrate;
            }
            $adduser=$UserObj->edit($data,$id);
            if($adduser){
                Buddha_Http_Head::redirect('编辑成功',"index.php?a=more&c=user&view={$view}");
            }else{
                Buddha_Http_Head::redirect('编辑失败',"index.php?a=edit&c=user&view={$view}");
            }

        }

        $Db_user=$UserObj->fetch($id);
        $RegionObj = new  Region();
        $top_list = $RegionObj->getFiledValues(''," isdel=0 and father=1 ");
        $option_list_1="<option value=''>请选择</option>";

        foreach($top_list as $k=>$v){
            $selected='';
            if($v['id']==$Db_user['level1']){
                $selected = 'selected';
            }
            $option_list_1.="<option value='{$v['id']}' $selected>{$v['name']}</option> ";

        }
        $this->smarty->assign('option_list_1', $option_list_1);
        $this->smarty->assign('userinfo', $Db_user);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }



    public function del(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $UserObj = new User();
        $id = Buddha_Http_Input::getParameter('id');
        $view= Buddha_Http_Input::getParameter('view');
        $page = Buddha_Http_Input::getParameter('p');
        $userInfo = $UserObj->del($id);
        if($userInfo){
            Buddha_Http_Head::redirect('删除成功',"index.php?a=more&c=user&p={$page}&view={$view}");
        }
        Buddha_Http_Head::redirect('删除失败',"index.php?a=more&c=user&p={$page}&view={$view}");
    }


    public function existnickname($param_username=''){

        if($param_username){
            $username = $param_username;
        }else{
            $json = Buddha_Http_Input::getParameter('json');
            $json_arr =Buddha_Atom_Array::jsontoArray($json);
            $username = $json_arr['username'];
        }
        $UserObj = new User();
        $num = $UserObj->getSingleFiledValues(array('id'),"isdel=0 and username='{$username}'");
        if($param_username){
            if($num==0){
                return 1;
            }else{
                return 0;
            }
        }
        $data = array();
        if($num==0){
            $data['isok']='true';
        }else{
            $data['isok']='false';
        }
        Buddha_Http_Output::makeJson($data);
    }

    public function existmobile($param_mobile=''){
        if($param_mobile){
            $Mobile = $param_mobile;
        }else{
            $json = Buddha_Http_Input::getParameter('json');
            $json_arr =Buddha_Atom_Array::jsontoArray($json);
            $Mobile = $json_arr['mobile'];
        }
        $UserObj = new User();
        $num = $UserObj->countRecords("isdel=0 and mobile='{$Mobile}'");
        if($param_mobile){
            if($num==0){
                return 1;
            }else{
                return  0;
            }
        }
        $data = array();
        if($num==0){
            $data['isok']='true';
        }else{
            $data['isok']='false';
        }
        Buddha_Http_Output::makeJson($data);
    }

    public function chregion($param_level3=''){
        if($param_level3){
            $level3 = $param_level3;
        }else{
            $json = Buddha_Http_Input::getParameter('json');
            $json_arr =Buddha_Atom_Array::jsontoArray($json);
            $level3 = $json_arr['level3'];
            $uuser_id=$json_arr['user_id'];
        }
        $UserObj=new User();
        if($uuser_id){
            $num = $UserObj->countRecords("state=1 and groupid=2 and level3='{$level3}' and id!='{$uuser_id}' ");
        }else{
        $num = $UserObj->countRecords("state=1 and groupid=2 and level3='{$level3}'");
        }
        if($param_level3){
            if($num==0){
                return 1;
            }else{
                return  0;
            }
        }
        $data = array();
        if($num==0){
            $data['isok']='true';
        }else{
            $data['isok']='false';
        }
        Buddha_Http_Output::makeJson($data);
    }

    public function apply(){//代理商申请表
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/
        $Regionobj = new Region();
        $ApplyagentObj = new Applyagent();
        $option = Buddha_Http_Input::getParameter('option');//检索类型  exportListId
        $params ['option'] = $option;
        $keyword = trim( Buddha_Http_Input::getParameter('keyword'));
        $params ['keyword'] = $keyword;
        $searchType = array (1 => '省', 2 => '市', 3 => '区县');
        $rcount= $ApplyagentObj->countRecords("ispay=1");
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = 15;//页数
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $where = ' 1=1 ';
        if($option){
            switch ($option) {
                case '1':
                    $regions = $Regionobj->getSingleFiledValues(array('id'),"name  LIKE '%{$keyword}%'");
                    $where .= " and level1={$regions['id']}";
                    break;
                case '2':
                    $regions = $Regionobj->getSingleFiledValues(array('id'),"name  LIKE '%{$keyword}%'");
                    $where .= " and level2={$regions['id']}";
                    break;
                case '3':
                    $regions = $Regionobj->getSingleFiledValues(array('id'),"name  LIKE '%{$keyword}%'");
                    $where .= " and level3={$regions['id']}";
                    break;
            }
        }
        
        $this->smarty->assign('searchType',$searchType);//搜索条件
        $list = $ApplyagentObj->getFiledValues('',$where .' ORDER BY id DESC ' . Buddha_Tool_Page::sqlLimit($page, $pagesize));
        foreach($list as $k => $v){//将省市区显示成对应的名称
            if($v['level1']){
                $pro = $Regionobj->getSingleFiledValues(array('name'),"id={$v['level1']}");
                $list[$k]['level1'] = $pro['name'];
            }
            if($v['level2']){
                $city = $Regionobj->getSingleFiledValues(array('name'),"id={$v['level2']}");
                $list[$k]['level2'] = $city['name'];
            }
            if($v['level3']){
                $area = $Regionobj->getSingleFiledValues(array('name'),"id={$v['level3']}");
                $list[$k]['level3'] = $area['name'];
            }
        }
        $strPages =  Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=user&'
            .http_build_query($params).'&' , $pagesize);//分页
        $this->smarty->assign('list',$list);
        $this->smarty->assign('page', $page);
        $this->smarty->assign( 'params', $params );
        $this->smarty->assign('strPages', $strPages);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
    public function applyedit(){
        $id = Buddha_Http_Input::getParameter('id');
        $p = Buddha_Http_Input::getParameter('p');
        $isok = Buddha_Http_Input::getParameter('isok');
        $Regionobj = new Region();
        $ApplyagentObj = new Applyagent();
        $applyinfo = $ApplyagentObj->getSingleFiledValues('',"id={$id}");
        if($applyinfo['level1']){//省
            $pro = $Regionobj->getSingleFiledValues(array('name'),"id={$applyinfo['level1']}");
            $applyinfo['level1'] = $pro['name'];
        }
        if($applyinfo['level2']){//市
            $city = $Regionobj->getSingleFiledValues(array('name'),"id={$applyinfo['level2']}");
            $applyinfo['level2'] = $city['name'];
        }
        if($applyinfo['level3']){//区县 
            $area = $Regionobj->getSingleFiledValues(array('name'),"id={$applyinfo['level3']}");
            $applyinfo['level3'] = $area['name'];
        }
        if($isok){// 修改审核状态
            $data['isok'] = $isok;
            $ApplyagentObj->edit($data,$id);
            echo "<script>alert('操作成功');location.href='".$_SERVER["HTTP_REFERER"]."';</script>";
        }
        $this->smarty->assign('applyinfo',$applyinfo);
        $this->smarty->assign('p',$p);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function applydel(){
        $Regionobj = new Region();
        $ApplyagentObj = new Applyagent();
        $id = Buddha_Http_Input::getParameter('id');
        $p = Buddha_Http_Input::getParameter('p');
        $re = $ApplyagentObj->del($id);
        if($re){
            $data['isok'] = 'true';
            $data['info'] = '删除成功！';
        }else{
            $data['isok'] = 'false';
            $data['info'] = '服务器忙！'; 
        }
        Buddha_Http_Output::makeJson($data);
    }


    public function enetcom(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $SupplyObj=new Supply();
        $ShopObj=new Shop();
        $UserObj = new User();
        $UserfeeObj = new Userfee();
        $params = array ();
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;
        $p=(int)Buddha_Http_Input::getParameter('p');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $searchType = array (1 => '全部', 2 => '今日新增', 3 => '未审核', 4 => '审核通过',5 => '审核未通过');

        if(Buddha_Http_Input::getParameter('job')){
            $job=Buddha_Http_Input::getParameter('job');
            if(!Buddha_Http_Input::getParameter('ids')){
                Buddha_Http_Head::redirect('您没有选择参数','index.php?a=enetcom&c=user&view='.$view.'&p='.$p);
            }
            $ids = implode ( ',',Buddha_Http_Input::getParameter('ids'));

            switch($job){
                case 'is_sure':
                    $SupplyObj->updateRecords(array('is_sure'=>1,'buddhastatus'=>0),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=enetcom&c=user&view='.$view.'&p='.$p);
                    break;
                case 'stop':
                    $SupplyObj->updateRecords(array('is_sure'=>0,'buddhastatus'=>1,'isdel'=>4),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=enetcom&c=user&view='.$view.'&p='.$p);
                    break;
                case 'sure':
                    $SupplyObj->updateRecords(array('is_sure'=>1,'buddhastatus'=>0,),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=enetcom&c=user&view='.$view.'&p='.$p);
                    break;
                case 'enable':
                    $SupplyObj->updateRecords(array('is_sure'=>1,'buddhastatus'=>0,'isdel'=>0),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=enetcom&c=user&view='.$view.'&p='.$p);
                    break;
                case 'is_hot':
                    $SupplyObj->updateRecords(array('is_hot'=>1),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=enetcom&c=user&view='.$view.'&p='.$p);
                    break;
                case 'is_rec':
                    $SupplyObj->updateRecords(array('is_rec'=>1),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=enetcom&c=user&view='.$view.'&p='.$p);
                    break;
            }
        }

        $where = " 1=1";
        if($view) {
            $params['view'] = $view;
            $times = strtotime(date('Y-m-d'));
            switch ($view) {
                case 2;
                    $where .= ' and createtime > {$times}';
                    break;
                case 3;
                    $where .= " and is_sure=0";
                    break;
                case 4;
                    $where .= " and is_sure=1 ";
                    break;
                case 5;
                    $where .= " and is_sure=4 ";
                    break;
            }
        }
        /*if($keyword){
            $where.=" and goods_name like '%$keyword%'";
            $params['keyword'] = $keyword;
        }*/
        $rcount= $UserfeeObj->countRecords($where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by id DESC ";

        $list = $UserfeeObj->getFiledValues('', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));

        foreach($list as $k=>$v){
            $username = $UserObj->getSingleFiledValues(array('realname','username'),"id='{$v['user_id']}'");
            if($username['realname']){
                $list[$k]['username'] =  $username['realname'];
            }else{
                $list[$k]['username'] =  $username['username'];
            }
        }
        $strPages =  Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=user&' .http_build_query($params).'&', $pagesize);

        $this->smarty->assign('rcount', $rcount);
        $this->smarty->assign('pcount', $pcount);
        $this->smarty->assign('page', $page);
        $this->smarty->assign('strPages', $strPages);
        $this->smarty->assign('list', $list);
        $this->smarty->assign( 'params', $params );


        $this->smarty->assign('searchType',$searchType);
        $this->smarty->assign( 'params', $params );
        $this->smarty->assign( 'keyword', $keyword );
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function enetcomedit(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $ShopdatumObj=new Shopdatum();
        $UserfeeObj=new Userfee();
        $UserObj = new User();
        $ShopObj=new Shop();
        $p=(int)Buddha_Http_Input::getParameter('p');
        $view=(int)Buddha_Http_Input::getParameter('view');
        $id=(int)Buddha_Http_Input::getParameter('id');
        $Userfee = $UserfeeObj->getSingleFiledValues('',"id='{$id}'");
        if(!$id){
            Buddha_Http_Head::redirect('参数错误！',"index.php?a=enetcom&c=user&p={$p}&view={$view}");
        }

        $Shopdatum=$ShopdatumObj->fetch($Userfee['shopdatum_id']);
        $Shopdatum['ispay'] = $Userfee['ispay'];
        if(!$Shopdatum){
            Buddha_Http_Head::redirect('没有找到您要的信息！',"index.php?a=enetcom&c=user&p={$p}&view={$view}");
        }
    
        $is_sure=Buddha_Http_Input::getParameter('is_sure');
        $remarks=Buddha_Http_Input::getParameter('remarks');
        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['is_sure']=$is_sure;
            $data['remarks']=$remarks;
            if($UserfeeObj->edit($data,$id)){
                Buddha_Http_Head::redirect('编辑成功！',"/manage/index.php?a=enetcom&c=user&p={$p}&view={$view}");
            }else{
                Buddha_Http_Head::redirect('编辑失败！',"/manage/index.php?a=enetcom&c=user&p={$p}&view={$view}");
            }
        }

        Buddha_Editor_Set::getInstance()->setEditor(
            array (array ('id' => 'content', 'content' =>$Supply['goods_desc'], 'width' => '100', 'height' => 500 )
            ));

        if($supplycat){
            $cat='';
            foreach($supplycat as $k=>$v){
                $cat.=$v['cat_name'].' > ';
            }
            $Supply['cat_name']=Buddha_Atom_String::toDeleteTailCharacter($cat);
        }
        $shop_name=$ShopObj->getSingleFiledValues(array('name'),"id='{$Supply['shop_id']}'");
        if($shop_name){
        $Supply['shop_name']=  $shop_name['name'];
        }

        $this->smarty->assign('Shopdatum',$Shopdatum);
        $this->smarty->assign('galleryimg',$galleryimg);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function enetcomdel(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $SupplyObj=new Supply();
        $GalleryObj=new Gallery();
        $p=(int)Buddha_Http_Input::getParameter('p');
        $view=(int)Buddha_Http_Input::getParameter('view');
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirect('参数错误！',"index.php?a=milist&c=supply&p={$p}&view={$view}");
        }
        $num=$SupplyObj->countRecords("id='{$id}'");
        if($num==0){
            Buddha_Http_Head::redirect('没有找到您要的信息！',"index.php?a=milist&c=supply&p={$p}&view={$view}");
        }
        $SupplyObj->del($id);
        $GalleryObj->delGelleryimage($id);
        if($SupplyObj){
            Buddha_Http_Head::redirect('删除成功！',"index.php?a=milist&c=supply&p={$p}&view={$view}");
        }else{
            Buddha_Http_Head::redirect('删除失败！',"index.php?a=milist&c=supply&p={$p}&view={$view}");
        }

    }


    public function layerlim(){

        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $UserObj = new User();
        $ShopObj = new Shop();
        $MemberObj = new Member();
        $params = array ();
        $usertype=$UserObj->usertype();
        $this->smarty->assign('usertype', $usertype);

        list($hsk_adminsid, $hsk_adminuser, $hsk_adminpw, $hsk_admintime) = explode("\t",  Buddha_Tool_Password::cookieDecode(Buddha_Http_Cookie::getCookie('buddha_adminsid') , Buddha::$buddha_array['cookie_hash']));
        $uid= $hsk_adminsid;
        $member =$MemberObj->getSingleFiledValues(array('id','adminid','memberid','permissions','pri'),"id={$uid}");
        if($member['id']  && $member['memberid']==0){
            $this->smarty->assign('utype','1');
        }
        if($member['id'] != 1){
            if($member['memberid']==0 && stripos($member['permissions'],'78')){
                $this->smarty->assign('dels','1');
            }
        }else{
            $this->smarty->assign('dels','1');
        }
        $where = " isdel=0 ";
        $exportListId = Buddha_Http_Input::getParameter('jb');//列表导出参数
        $option = Buddha_Http_Input::getParameter('option');//检索类型  exportListId
        $params ['option'] = $option;
        $keyword = trim( Buddha_Http_Input::getParameter('keyword'));
        $params ['keyword'] = $keyword;

        $start = Buddha_Http_Input::getParameter('start');
        $end = Buddha_Http_Input::getParameter('end');
        if($start!=''){
            $params ['start'] = $start;
        }
        if($end!=''){
            $params ['end'] = $end;
        }
        if (count($option)) {
            if (count($keyword)) {
                switch ($option) {
                    case '2' ://手机号
                        $where .= " and mobile LIKE '%{$keyword}%'";
                        break;
                    case '1' : //用户名
                        $where .= " and username LIKE '%{$keyword}%'";
                        break;
                }
            }

        }
        $searchType = array (1 => '用户名', 2 => '手机号码');
        $this->smarty->assign('searchType',$searchType);
        $this->smarty->assign( 'params', $params );
        $this->smarty->assign( 'keyword', $keyword );
        if($keyword){
            $UserassoObj = new Userasso();
            $field = array('id','father_id','username','realname','mobile','logo');
            $list = $this->db->getSingleFiledValues($field, $this->prefix . 'user', $where );
            $sql = $UserassoObj->getSqlFrontByLayerLimitNumberStr('',$list['id']);
            $idarr = $UserassoObj->getFiledValues(array('user_id'),"1=1 {$sql}");
            if(count($idarr)>0){
                foreach ($idarr as $k => $v) {
                    $userInfos[$k] = $UserObj->getSingleFiledValues($field,"id='{$v['user_id']}'");
                }
            }            
        }
        $this->smarty->assign('list', $list);
        $this->smarty->assign('userInfos', $userInfos);
        $this->smarty->assign( 'params', $params );
        $this->smarty->assign( 'groupid', $groupid );

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }


}

