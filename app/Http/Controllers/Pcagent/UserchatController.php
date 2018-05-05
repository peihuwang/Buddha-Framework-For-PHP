<?php

/**
 * Class UserchatController
 */
class UserchatController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $UserchatObj=new Userchat();
        $act=Buddha_Http_Input::getParameter('act');
        if($act=='list'){
        $where = "isdel=0 and user_id='{$uid}'";
        $rcount = $UserchatObj->countRecords($where);

        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('PageSize') ? (int)Buddha_Http_Input::getParameter('PageSize') :15;
        //$pcount = ceil($rcount / $pagesize);
        $orderby = " order by id DESC ";

            $list = $UserchatObj->getFiledValues('', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));
            $jsondata=array();
            if (count($list)) {
                $arrUsers = $list;
                $sort = array(
                    'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
                    'id' => 'id',       //排序字段
                );
                $arrSort = array();
                foreach ($arrUsers AS $uniqid => $row) {
                    foreach ($row AS $key => $value) {
                        $arrSort[$key][$uniqid] = $value;
                    }
                }
                if ($sort['direction']) {
                    array_multisort($arrSort[$sort['id']], constant($sort['direction']), $arrUsers);
                }
                foreach ($arrUsers as $k => $v) {
                    $jsondata[]= array(
                        'id' => $v['id'],
                        'question' => $v['question'],
                        'logo' => $UserInfo['logo'],
                        'answer' => $v['answer'],
                    );
                }
            }
                Buddha_Http_Output::makeWebfaceJson($jsondata, '', 0, '加载完成');

        }

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

     public function subMess(){
        $data['user_id'] = Buddha_Http_Cookie::getCookie('uid');
        //获取表单数据
        $data['username'] = Buddha_Http_Input::getParameter('uName');
        $data['question'] = Buddha_Http_Input::getParameter('messageContent');
        $data['type'] = Buddha_Http_Input::getParameter('selectionType');
        $data['ip'] = Buddha_Explorer_Network::getIp();
        $data['createtime'] = time();
        $data['createtimestr'] = date('Y-m-d H:i:s',time());
        $Userchat = new Userchat();
        $re = $Userchat -> add($data);
        if($re){
            echo '<script>alert("留言成功！");history.go(-2);</script>';
        }else{
            echo '<script>alert("服务器忙！");history.go(-2);</script>';
        }
    }

}