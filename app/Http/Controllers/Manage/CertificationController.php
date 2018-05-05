<?php

/**店铺认证码
 * Class CertificationController
 */   
class CertificationController extends Buddha_App_Action{
    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function codes(){
    	/******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/       

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function lists(){//认证码列表
    	/******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/
        $ShopObj = new Shop();
        $UserObj = new User();
        $CertifObj = new Certification();
        $where = " 1=1 ";
        $rcount= $CertifObj->countRecords($where);//总条数
        $synum= $CertifObj->countRecords("is_use = 1");//已使用条数
        $wnum= $CertifObj->countRecords("is_use = 2");//过期条数
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;//页数
        $pagesize = 15;
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by id DESC ";
        $list = $this->db->getFiledValues('', $this->prefix . 'certification', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));//获取列表
        $strPages =  Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=certification&',$pagesize);//分页
        foreach($list as $k=>$v){
            if($v['shop_id']){//店铺名称
                $shopname = $ShopObj->getSingleFiledValues(array('name'),"id='{$v['shop_id']}'");
                $list[$k]['shopname'] = $shopname['name'];
            }
            if($v['user_id']){//操作者姓名
                $username = $UserObj->getSingleFiledValues(array('realname','username'),"id='{$v['user_id']}'");
                if($username['realname']){
                    $list[$k]['operator'] = $username['realname'];
                }else{
                    $list[$k]['operator'] = $username['username'];
                }
            }
        }
        $this->smarty->assign('list',$list);
        $this->smarty->assign('page', $page);
        $this->smarty->assign('rcount', $rcount);
        $this->smarty->assign('synum', $synum);
        $this->smarty->assign('wnum', $wnum);
        $this->smarty->assign('strPages', $strPages);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');


    }


    public function generation(){//生成认证码
    	$certifObj = new Certification();
    	$numbers = Buddha_Http_Input::getParameter('numbers');//生成条数
    	$dates = Buddha_Http_Input::getParameter('dates');//有效期
        $remarks = Buddha_Http_Input::getParameter('remarks');//备注
    	$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    	$string = str_shuffle($str);
    	list($msec, $sec) = explode(' ', microtime());
 		$msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    	for($i=0;$i<$numbers;$i++){
    		$codes[$i] = str_shuffle(substr($string,0,15) . $msectime);
    	}
    	if($codes){
    		foreach ($codes as $v) {
    			$data['code'] = $v;
    			$data['createtime'] = time();
    			$data['valids'] = $dates;
                $data['remarks'] = $remarks;
    			$data['overdue_time'] = strtotime("{$dates} month");
    			$data['is_use'] = 0;
    			$certifObj->add($data);
    		}
    		$datas['isok'] = 'true';
    		$datas['info'] = '操作成功';
    		$datas['data'] = $codes;
    	}else{
    		$datas['isok'] = 'false';
    		$datas['info'] = '服务器忙';
    		$datas['data'] = '';
    	}
    	Buddha_Http_Output::makeJson($datas);
    }

}
