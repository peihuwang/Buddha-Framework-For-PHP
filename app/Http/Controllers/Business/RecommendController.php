<?php
/**
 * Class RecommendController
 */
class RecommendController extends Buddha_App_Action{

    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    /**
     * 商家互推列表
     */
    public function index(){
    	$ShopObj=new Shop();
    	$UserObj = new User();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        if($UserInfo['recommended']){
        	$where = " id in ({$UserInfo['recommended']}) ";
	        $rcount = $this->db->countRecords( $this->prefix.'shop', $where);
	        $orderby = " ORDER BY id DESC ";
	        $shoplists = $ShopObj->getFiledValues(array('id','user_id','name','realname','number','roadfullname','small','brief','createtimestr'),$where . $orderby);
	        $this->smarty->assign('shoplists',$shoplists);
        }
        $act = Buddha_Http_Input::getParameter('act');
        $keyword = Buddha_Http_Input::getParameter('keyword');
        if($act == 'list' && $keyword){
        	$page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
	        $pagesize = Buddha::$buddha_array['page']['pagesize'];
	        $businessId = $UserObj->getSingleFiledValues(array('id'),"mobile='{$keyword}'");
	        $where = "is_sure=1 AND isdel=0 AND user_id='{$businessId['id']}' ";
	        $rcount = $this->db->countRecords( $this->prefix.'shop', $where);
	        $orderby = " ORDER BY id DESC ";
	        $shoplist = $ShopObj->getFiledValues(array('id','user_id','name','realname','number','roadfullname','small','brief','createtimestr'),$where . $orderby);
	        if($shoplist){
	        	$data['isok'] = 'true';
	        	$data['data'] = $shoplist;
	        }else{
	        	$data['isok'] = 'false';
	        	$data['data'] = '暂时还没有可推荐的店铺';
	        }

	        Buddha_Http_Output::makeJson($data);
        }

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

	}
	/*function sql(){
		$UserObj = new User();
		$user = $UserObj->getFiledValues(array('id','to_group_id'),"groupid=1");
		foreach ($user as $k => $v) {
			if(!stripos($v['to_group_id'],'3')){
				$data['to_group_id'] = '1,3,4';
				$UserObj->updateRecords($data,"id={$v['id']}");
			}
		}
	}*/
	function setup(){
    	$UserObj = new User();
    	$ShopObj = new Shop();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $shop_id = Buddha_Http_Input::getParameter('id');
        $shopinfo = $ShopObj->getSingleFiledValues(array('id,user_id'),"id='{$shop_id}'");
        /*if($shopinfo['id']){
        	$datas['isok'] = 0;
			$datas['info'] = "不能推荐您现在操作的店铺！";
			Buddha_Http_Output::makeJson($datas);
        }*/
        if($UserInfo['recommended']){
        	if(stripos($UserInfo['recommended'],',')){
        		$arr = explode(',', $UserInfo['recommended']);
        		if(in_array($shop_id,$arr)){
        			$datas['isok'] = 0;
        			$datas['info'] = "您已推荐过此店铺！";
        			Buddha_Http_Output::makeJson($datas);
        		}
        		if(count($arr)>10){
        			$datas['isok'] = 0;
        			$datas['info'] = "推荐店铺不能超过十个！";
        			Buddha_Http_Output::makeJson($datas);
        		}
        	}elseif($UserInfo['recommended'] == $shop_id){
        		$datas['isok'] = 0;
        		$datas['info'] = "您已推荐过此店铺！";
        		Buddha_Http_Output::makeJson($datas);
        	}
        	$data['recommended'] = $UserInfo['recommended'] . ',' . $shop_id;
        }else{
        	$data['recommended'] = $shop_id;
        }

        if($UserObj->edit($data,$UserInfo['id'])){
        	$datas['isok'] = 1;
    		$datas['info'] = "推荐成功！";
    		Buddha_Http_Output::makeJson($datas);
        }

	}

    /**
     *删除商家互推店铺
    **/
    function dele(){
        $UserObj = new User();
        $ShopObj = new Shop();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $shop_id = Buddha_Http_Input::getParameter('id');
        if($UserInfo['recommended']){
            if(stripos($UserInfo['recommended'],',')){
                $recommended = explode(',',$UserInfo['recommended']);
                if(in_array($shop_id,$recommended)){
                    foreach ($recommended as $key=>$value){
                        if ($value === $shop_id){
                            unset($recommended[$key]);
                        }
                        
                    }
                    $data['recommended'] = join(',',$recommended);
                    $UserObj->updateRecords($data,"id='{$uid}'");
                }
            }elseif($UserInfo['recommended'] == $shop_id){
                $data['recommended'] = '';
                $UserObj->updateRecords($data,"id='{$uid}'");
            }
            $datas['isok'] = 1;
            $datas['info'] = '操作成功';
        }else{
            $datas['isok'] = 1;
            $datas['info'] = '服务器忙';
        }
        
        Buddha_Http_Output::makeJson($datas);


    }

}



