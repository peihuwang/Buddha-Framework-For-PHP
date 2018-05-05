<?php

/**
 * Class PermissionsController
 */
class PermissionsController extends Buddha_App_Action{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function edit(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $ManegerObj = new Manager();

        if($_POST){
            $action_code=Buddha_Http_Input::getParameter('action_code');//获取子级id
            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
            $id=(int)Buddha_Http_Input::getParameter('id');
            $permissions='';
            $pri='';

            foreach($action_code as $k=> $v){

                if(is_array($v) and (count($v))){
                    $v[$k]=$k;
                }
                if(is_array($v) and (count($v))){


                    foreach($v as $kk=>$vv){
                        $permissions.= $vv.',';
                        $temp_menu=$this->db->getSingleFiledValues('', $this->prefix.'menu', "id={$vv} and isopen=1");

                        $temp_services = $temp_menu['services'];
                        $temp_operator = $temp_menu['operator'];

                        if($temp_operator==''){
                            $tempvv=$temp_services;
                        }else{
                            $tempvv=$temp_services.'.'.$temp_operator;
                        }
                        $pri.= $tempvv.',';
                        unset($temp_menu);
                        unset($tempvv);
                    }
                }
            }

            $data['pri']=$pri;
            $data['permissions']=$permissions;
            $result = $ManegerObj->edit($data,$id);

            if ($result) {
                Buddha_Http_Head::redirect('编辑成功',"index.php?a=more&c=manager&p={$page}");

            }else{
                Buddha_Http_Head::redirect('编辑失败',"index.php?a=more&c=manager&p={$page}");

            }
        }

        /*******************
         * 获取全部的权限  *
         ******************/
        $list = $this->db->getFiledValues('', $this->prefix . 'menu', "isopen=1 and sub=0  order by id asc");
        foreach ($list as $k=>$v) {
            $id=$v['id'];
            $list[$k]['child'] = $this->db->getFiledValues('', $this->prefix . 'menu', "isopen=1 and sub={$id}  order by id asc");
            $keya='';
            foreach($list[$k]['child'] as $k1=>$v1){
                $keya .=$v1['id'].',';
            }
            $list[$k]['abc']=$keya;
        }

        $this->smarty->assign('list',$list);
        /*************************
         * 获取用户已经有的权限  *
         *************************/

        $id=(int)Buddha_Http_Input::getParameter('id');
        $this->smarty->assign('id',$id);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $this->smarty->assign('page',$page);

        $permissions =$ManegerObj->fetch($id);
        $menuids=$permissions['permissions'];

        $menuids_arr = explode(',',$menuids);

        $state_arr = array();
        if(is_array($menuids_arr) and count($menuids_arr)){
            foreach($menuids_arr as $k=>$v){
                if($v){
                    $state_arr[$v]  =$v;
                }
            }
        }

        foreach($list as $k=>$v){
            if((array_key_exists($v['id'],$state_arr)))
                $list[$k]['state']=1;
            else
                $list[$k]['state']=0;
            if(is_array($v['child']) and count($v['child'])){
                foreach($v['child'] as $k1 =>$v1){
                    if((array_key_exists($v1['id'],$state_arr)))
                        $list[$k]['child'][$k1]['state']=1;
                    else
                        $list[$k]['child'][$k1]['state']=0;
                }}
        }

        $this->smarty->assign('list',$list);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
    

}