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
            Buddha_Http_Head::jump('pc/index.php?a=login&c=account');
        }
        $_SESSION['groupid'] = '';
        $j = $_GET['j'];
        if($j){
            $to_group_id = explode(',',$UserInfo['to_group_id']);
            if(in_array($j,$to_group_id)){
                if($j==1){
                    $_SESSION['groupid'] = 1;
                    $url='/pcbusiness/index.php?a=index&c=business';
                }elseif($j==2){
                    $_SESSION['groupid'] = 2;
                    $url='/pcagent/index.php?a=index&c=agent';
                }elseif($j==3){
                    $_SESSION['groupid'] = 3;
                    $url='/pcpartner/index.php?a=index&c=partner';
                }elseif($j==4){
                    $_SESSION['groupid'] = 4;
                    /*print_r($_COOKIE);
                    echo '<br/>';
                    print_r($UserInfo);
                    exit;*/
                    $url='/pcuser/index.php?a=index&c=user';
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
            $url='/pcbusiness/index.php?a=index&c=business';
        }elseif($UserInfo['groupid']==2){
            $url='/pcagent/index.php?a=index&c=agent';
        }elseif($UserInfo['groupid']==3){
            $url='/pcpartner/index.php?a=index&c=partner';
        }elseif($UserInfo['groupid']==4){
            $url='/pcuser/index.php?a=index&c=user';
        }
        Buddha_Http_Head::jump($url);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
    public function role_changing(){
        $j = $_GET['j'];
        $uid = Buddha_Http_Cookie::getCookie('uid');
        $userObj = new user();
        $UserInfo = $userObj -> getSingleFiledValues(array('to_group_id'),"id={$uid}");
        if(stripos($UserInfo['to_group_id'],$j)){
            echo '<script>alert("您已存在此角色！")</script>';
            echo"<script>history.go(-1);</script>";
            exit;
        }else{
            /*if($j == 3){
                echo  "<script>if(confirm( '成为合伙人后可以获取更多的收益，移步 系统公告 了解详细内容。是否继续开通合伙人功能？？')){}else{return;};</script>";
            }*/
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