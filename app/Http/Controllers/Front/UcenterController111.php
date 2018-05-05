<?php

/**
 * Class UcenterController
 */
class UcenterController extends Buddha_App_Action{
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
    }
    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if(!$uid){
            Buddha_Http_Head::jump('/index.php?a=login&c=account');
            //Header("Location: index.php?a=login&c=account");
        }
        $_SESSION['groupid'] = '';
        $j = $_GET['j'];
        if($j){
            $to_group_id = explode(',',$UserInfo['to_group_id']);
            if(in_array($j,$to_group_id)){
                if($j==1){
                    $_SESSION['groupid'] = 1;
                    $url='/business/index.php?a=index&c=business';
                }elseif($j==2){
                    $_SESSION['groupid'] = 2;
                    $url='/agent/index.php?a=index&c=agent';
                }elseif($j==3){
                    $_SESSION['groupid'] = 3;
                    $url='/partner/index.php?a=index&c=partner';
                }elseif($j==4){
                    $_SESSION['groupid'] = 4;
                    /*print_r($_COOKIE);
                    echo '<br/>';
                    print_r($UserInfo);
                    exit;*/
                    $url='/user/index.php?a=index&c=user';
                }
                Buddha_Http_Head::jump($url);
                exit;
            }else{
                echo '<script>alert("您还没有申请此角色,请在个人中心提交您的申请！")</script>';
                echo"<script>history.go(-1);</script>";
                exit; 
            }
        }
        if($UserInfo['groupid']==1){
            $url='/business/index.php?a=index&c=business';
        }elseif($UserInfo['groupid']==2){
            $url='/agent/index.php?a=index&c=agent';
        }elseif($UserInfo['groupid']==3){
             $url='/partner/index.php?a=index&c=partner';
        }else{
             $url='/user/index.php?a=index&c=user';
        }

        Buddha_Http_Head::jump($url);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

     public function partnre_apply(){//判断合伙人资料是否完整
        $j = $_GET['j'];
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $userObj = new User();
        if(stripos($UserInfo['to_group_id'],$j)){
            $datas['status'] = 0;
        }else{
            if($UserInfo['realname'] && $UserInfo['level1']){//判断真实姓名和地区是否存在
                if(stripos($UserInfo['to_group_id'],'1')){
                    $data['to_group_id'] = $UserInfo['to_group_id'] . ',' . $j;
                }else{
                    $data['to_group_id'] = $UserInfo['to_group_id'] . ',' . '1' . ',' . $j; 
                }
                
                $re = $userObj -> updateRecords($data,"id = {$uid}");
                if($re){
                    $datas['status'] = 1;;
                    
                }else{
                    $datas['status'] = 2;
                }
            }else{
                $datas['status'] = 3;
            }
        }
        Buddha_Http_Output::makeJson($datas);
    }

    public function role_changing(){//添加角色
        $j = $_GET['j'];
        $uid = Buddha_Http_Cookie::getCookie('uid');
        $userObj = new User();
        $UserInfo=$userObj->getSingleFiledValues(array('groupid','to_group_id'),"id={$uid}");
        if(stripos($UserInfo['to_group_id'],$j) == false && $UserInfo['gropuid'] == $j){
            $data['to_group_id'] = $UserInfo['to_group_id'] . ',' . $j;
            $re = $userObj -> updateRecords($data,"id = {$uid}");
            echo '<script>alert("您已存在此角色！")</script>';
            echo"<script>history.go(-1);</script>";
            exit;
        }
        if(stripos($UserInfo['to_group_id'],$j)){
            echo '<script>alert("您已存在此角色！")</script>';
            echo"<script>history.go(-1);</script>";
            exit;
        }else{
            $data['to_group_id'] = $UserInfo['to_group_id'] . ',' . $j;
            $re = $userObj -> updateRecords($data,"id = {$uid}");
            if($re){

                echo '<script>alert("角色添加成功！")</script>';
                echo"<script>history.go(-1);</script>";
            }else{
                echo '<script>alert("服务器忙！")</script>';
                echo"<script>history.go(-1);</script>";
            }
        }
    }

}