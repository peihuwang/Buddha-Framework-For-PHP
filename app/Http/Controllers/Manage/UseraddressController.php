<?php

/**
 * Class UseraddressController
 */
class UseraddressController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public  function more(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $user_id = Buddha_Http_Input::getParameter('user_id');
        $where = " isdel=0 and user_id={$user_id} ";
        $rcount = $this->db->countRecords( $this->prefix.'useraddress', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }

        $orderby = " order by id DESC ";
        $list = $this->db->getFiledValues ( '*',  $this->prefix.'useraddress', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

        $UseraddressObj = new Useraddress($this->db);
        $list=$UseraddressObj-> useraddressRegion($list);

        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=useraddress&', $pagesize );

        $this->smarty->assign('rcount',$rcount);
        $this->smarty->assign('pcount',$pcount);
        $this->smarty->assign('page',$page);
        $this->smarty->assign('strPages',$strPages);
        $this->smarty->assign('list',$list);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty->assign('user_id',$user_id);
        $this->smarty -> display($TPL_URL.'.html');
    }

    public  function add(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $this->smarty->assign('page',$page);
        $user_id = Buddha_Http_Input::getParameter('user_id');
        $name=Buddha_Http_Input::getParameter('name');
        $mobile=Buddha_Http_Input::getParameter('mobile');
        $zip=Buddha_Http_Input::getParameter('zip');
        $province=Buddha_Http_Input::getParameter('province');
        $city=Buddha_Http_Input::getParameter('city');
        $area=Buddha_Http_Input::getParameter('area');
        $addr=Buddha_Http_Input::getParameter('addr');
        $def_addr=Buddha_Http_Input::getParameter('def_addr')?1:0;
        $isdel=Buddha_Http_Input::getParameter('isdel')?0:1;

        if($_POST){
            $data= array();
            $data['user_id']=$user_id;
            $data['name']=$name;
            $data['mobile']=$mobile;
            $data['zip']=$zip;
            $data['province']=$province;
            $data['city']=$city;
            $data['area']=$area;
            $data['addr']=$addr;
            $data['def_addr']=$def_addr;
            $data['isdel']=$isdel;

            $useraddressobj = new Useraddress($this->db);
            $addid=$useraddressobj->add($data);
            if($useraddressobj){
                if($def_addr==1){
                    $useraddressobj->editSilent(array('def_addr'=>'0'),$user_id);
                    $useraddressobj->edit(array('def_addr'=>'1'),$addid);
                }

                Buddha_Http_Head::redirect('添加成功',"index.php?a=more&c=useraddress&user_id={$user_id}&p={$page}");
            }

        }

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty->assign('user_id',$user_id);
        $this->smarty -> display($TPL_URL.'.html');
    }


    public  function edit(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $this->smarty->assign('page',$page);
        $user_id = Buddha_Http_Input::getParameter('user_id');
        $id = Buddha_Http_Input::getParameter('id');


        $name=Buddha_Http_Input::getParameter('name');
        $mobile=Buddha_Http_Input::getParameter('mobile');
        $zip=Buddha_Http_Input::getParameter('zip');
        $province=Buddha_Http_Input::getParameter('province');
        $city=Buddha_Http_Input::getParameter('city');
        $area=Buddha_Http_Input::getParameter('area');
        $addr=Buddha_Http_Input::getParameter('addr');
        $def_addr=Buddha_Http_Input::getParameter('def_addr')?1:0;
        $isdel=Buddha_Http_Input::getParameter('isdel')?0:1;
        $UseraddressObj = new Useraddress();

        if($_POST){
            $data= array();
            $data['name']=$name;
            $data['mobile']=$mobile;
            $data['zip']=$zip;
            $data['province']=$province;
            $data['city']=$city;
            $data['area']=$area;
            $data['addr']=$addr;
            $data['def_addr']=$def_addr;
            $data['isdel']=$isdel;

            $useraddress = $UseraddressObj->edit($data,$id);
            if($useraddress){
                if($def_addr==1){
                    $UseraddressObj->editSilent(array('def_addr'=>'0'),$user_id);
                    $UseraddressObj->edit(array('def_addr'=>'1'),$id);
                }
                Buddha_Http_Head::redirect('编辑成功',"index.php?a=more&c=useraddress&user_id={$user_id}&p={$page}");

            }else{

                Buddha_Http_Head::redirect('编辑失败',"index.php?a=more&c=useraddress&user_id={$user_id}&p={$page}");
            }

        }

        $useraddress = $UseraddressObj->fetch($id);
        if(count($useraddress)==0){
            Buddha_Http_Head::redirect('编辑失败',"index.php?a=more&c=useraddress&user_id={$user_id}&p={$page}");
        }
        $this->smarty->assign('useraddress',$useraddress);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty->assign('id',$id);
        $this->smarty->assign('user_id',$user_id);
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function del(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $UseraddressObj = new Useraddress();
        $id = Buddha_Http_Input::getParameter('id');
        $page = Buddha_Http_Input::getParameter('p');
        $user_id=Buddha_Http_Input::getParameter('user_id');
        $useraddressdel = $UseraddressObj->del($id);
        if($useraddressdel){
            Buddha_Http_Head::redirect('删除成功',"index.php?a=more&c=useraddress&user_id={$user_id}&p={$page}");

        }

        Buddha_Http_Head::redirect('删除失败',"index.php?a=more&c=useraddress&user_id={$user_id}&p={$page}");
    }
}
