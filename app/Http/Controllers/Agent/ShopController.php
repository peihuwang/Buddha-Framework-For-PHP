<?php

/**
 * Class ShopController
 */
class ShopController extends Buddha_App_Action{

    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
    }

    /**
     * 列表
     */
    public function index()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $act=Buddha_Http_Input::getParameter('act');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;

        if($act=='list'){
            $where = " (agent_id='{$uid}' or level3='{$UserInfo['level3']}') ";
            if($view){
                switch($view){
                    case 2;
                        $where.=' and isdel=0 and is_sure=0';
                        break;
                    case 3;
                        $where.=" and isdel=0 and is_sure=1";
                        break;
                    case 4;
                        $where.=" and isdel=0 and is_sure=4 ";
                        break;
                    case 5;
                        $where.=" and isdel=4 and state=1";
                        break;
                }
            }
            if($keyword){
                $where.=" and name like '%$keyword%'";
            }
            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
            $pagesize = Buddha::$buddha_array['page']['pagesize'];
            $orderby = " order by createtime DESC ";
            $list = $this->db->getFiledValues (array('id','name','number','small','createtime','is_verify','is_sure','state','user_id'),  $this->prefix.'shop', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );

            $UsercommonObj=new Usercommon();
            $ShopObj=new Shop();

            foreach($list as $k=>$v)
            {
                if($v['is_verify']==1){
                    $is_verify='已认证<em>V1</em>';
                }else{
                    $is_verify='店铺未认证';
                }

                $jsondata[]=array(
                    'id'=>$v['id'],
                    'name'=>$v['name'],
                    'images'=>$v['small'],
                    'number'=>$v['number'],
                    'user_id'=>$v['user_id'],
                    'is_sure'=>$UsercommonObj->agentsissure($v['is_sure']),
                    'is_verify'=>$is_verify,
                    'state'=>$ShopObj->agentsenabledisabledstr($v['isdel']),
                    'createtime'=>date('Y-m-d',$v['createtime']),
                );
            }
            if($list){
                $data['isok']='true';
                $data['data']=$jsondata;
            }else{
                $data['isok']='false';
                $data['data']='没有数据!';
            }
            Buddha_Http_Output::makeJson($data);
        }
        $order_id = Buddha_Http_Input::getParameter('order_id');
        if($order_id){
            $ShopObj=new Shop();
            $OrderObj=new Order();
            $orderInfo = $OrderObj->getSingleFiledValues(array('good_id'),"id={$order_id} and good_table='shop' and pay_status=1");
            if($orderInfo){
                $data['is_verify'] = 1;
                $data['veifytime'] = time();
                $data['veryfyendtime'] = time() + 31536000;
                $data['veryfyendtimestr'] = date('Y-m-d H:i:s',time() + 31536000);
                $ShopObj->updateRecords($data,"id={$orderInfo['good_id']}");
            }
        }
        $this->smarty->assign('view',$view);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    /**
     * 审核
     */
    public  function edit()
    {
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $ShopcatObj=new Shopcat();
        $UserObj=new User();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $partnerrate=(int)Buddha_Http_Input::getParameter('partnerrate');
        $is_sure=Buddha_Http_Input::getParameter('is_sure');
        $remarks=Buddha_Http_Input::getParameter('remarks');
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=shop',2);
        }
        $shopinfo=$ShopObj->getSingleFiledValues('',"id='{$id}'");
        if(!$shopinfo){
            Buddha_Http_Head::redirectofmobile('没有找到您要的信息！','index.php?a=index&c=shop',2);
        }

        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['partnerrate']=$partnerrate;
            $data['is_sure']=$is_sure;
            if($is_sure==1){
                $data['state']=0;
            }
            $data['remarks']=$remarks;
            $ShopObj->edit($data,$id);
            $datas=array();
            if($ShopObj){
                $datas['isok']='true';
                $datas['data']='审核完成!';
                $datas['url']='index.php?a=index&c=shop';
            }else{
                $datas['isok']='true';
                $datas['data']='审核失败!';
                $datas['url']='index.php?a=index&c=shop';
            }
            Buddha_Http_Output::makeJson($datas);
        }
        $shopcat=$ShopcatObj->goods_thumbgoods_thumb($shopinfo['shopcat_id']);
        if($shopcat){
            $cat='';
            foreach($shopcat as $k=>$v){
                $cat.=$v['cat_name'].' > ';
            }
            $shopinfo['cat_name']=Buddha_Atom_String::toDeleteTailCharacter($cat);
        }
        $Region_name=$RegionObj->getAllArrayAddressByLever($shopinfo['level3']);
        if($Region_name){
            $regionname='';
            foreach($Region_name as $k=>$v){
                if($k!=0 and $k<4){
                    $regionname.=$v['name'].' > ';
                }elseif($k==4){
                    $shopinfo['road']=$v['name'];
                }elseif($k==5){
                    $shopinfo['endstep']=$v['name'].$shopinfo['endstep'];
                }
            }
            $shopinfo['region']=Buddha_Atom_String::toDeleteTailCharacter($regionname);
        }
        $shoptype=$ShopObj->getNature($shopinfo['storetype']);
        $shopinfo['storetype']=$shoptype;

        $referral=$UserObj->getSingleFiledValues(array('id','realname','partnerrate'),"id='{$shopinfo['referral_id']}'");

        $this->smarty->assign('shopinfo',$shopinfo);
        $this->smarty->assign('referral',$referral);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }


    /**
     * 启 / 停 用
     */
    public  function isdel()
    {
        $ShopObj = new Shop();
        $shop_id = (int)Buddha_Http_Input::getParameter('id');
        $user_id = (int)Buddha_Http_Input::getParameter('user_id');
        $Db_Shop = $ShopObj->fetch($shop_id);
        $shop_user_id = $Db_Shop['user_id'];

        if($shop_user_id != $user_id)
        {
            $datas['isok']='false';
            $datas['data']='致命错误,不能修改.请联系管理员.';
        }


        $DB_Shop = $ShopObj->agentsEnableDisabledShop($shop_id,$Db_Shop['isdel'],$user_id);

        $jsondata = array();
        $jsondata['db_isok'] = $DB_Shop['is_ok'];

        if($DB_Shop['is_ok']==1)
        {
            $isok = 'true';
        }else{
            $isok = 'false';
        }

        $jsondata['db_msg'] =  $DB_Shop['is_msg'];
        $jsondata['db_msg'] =  $DB_Shop['title'];


        $state = array('id'=>$shop_id,'state'=>$DB_Shop['title']);
        $datas['isok']=$isok;
        $datas['data']=$state;



        Buddha_Http_Output::makeJson($datas);
    }


    /**
     * 店铺认证:    现金店证
     */
    public  function  verifya(){//店铺认证
        $urls = substr($_SERVER["REQUEST_URI"],1,stripos($_SERVER["REQUEST_URI"],'?')+1);
        $url = urlencode($urls.'a=index&c=shop');
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $ShopObj=new Shop();
        $OrderObj=new Order();
        $id=(int)Buddha_Http_Input::getParameter('shop_id');
        $shopinfo= $ShopObj->fetch($id);
        $getMoneyArrayFromShop = $ShopObj->getMoneyArrayFromShop($id,990);
        $data=array();
        $data['good_id']=$id;//指定产品id
        $data['user_id']=$uid;
        $data['order_sn']= $OrderObj->birthOrderId($uid);//订单编号
        $data['good_table']='shop';//哪个表
        $data['referral_id']=$shopinfo['referral_id'];//业务员id
        $data['partnerrate']=0;//合伙人提成比例
        $data['agent_id']=$uid;//代理商id
        $data['agentrate']=0;//代理商提成比例
        $data['pay_type']='third';//third第三方支付，point积分，balance余额
        $data['order_type']='shop.a';//money.out提现, 商家自行店铺认证shop.v,代理商进行店铺认证shop.a,信息置顶info.top ,跨区域信息推广info.market,信息查看info.see
        $data['goods_amt']='198';//产品价格
        $data['final_amt']='198';//产品最终价格
        $data['money_agent']=0;//代理商分润金额
        $data['money_plat']=0;//平台分润金额
        $data['money_partner']=0;//合伙人分润金额
        $data['payname']='微信支付';
        $data['make_level0']=$shopinfo['level0'];//国家
        $data['make_level1']=$shopinfo['level1'];//省
        $data['make_level2']=$shopinfo['level2'];//市
        $data['make_level3']=$shopinfo['level3'];//区县
        $data['make_level4']=$shopinfo['level4'];//乡镇
        $data['make_level5']=$shopinfo['level5'];
        $data['createtime']=Buddha::$buddha_array['buddha_timestamp'];//时间戳
        $data['createtimestr']=Buddha::$buddha_array['buddha_timestr'];//时间日期
        $order_id=$OrderObj->add($data);
        if($OrderObj){
            $datas['isok']='true';
            $datas['data']='/topay/wxpay/wxpayto.php?order_id='.$order_id.'&backurl='.$url;
        }else{
            $datas['isok']='false';
            $datas['data']='认证失败';
        }
        Buddha_Http_Output::makeJson($datas);
    }


    /**
     *店铺认证:     认证码认证
     */
    function authentication_codes(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $shop_id=(int)Buddha_Http_Input::getParameter('shop_id');
        $codes = Buddha_Http_Input::getParameter('codes');
        $certifObj = new Certification();
        $ShopObj = new Shop();
        $OrderObj = new Order();
        $time = time();
        $certifinfo = $certifObj->getSingleFiledValues('',"code='{$codes}' and is_use=0 and overdue_time>{$time}");
        
        if(!$certifinfo){
            $data['isok'] = 2;
            Buddha_Http_Output::makeJson($data);
        }

        $Db_Shop = $ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"id='{$shop_id}'");


        $data = array();
        $data['good_id'] = $shop_id;
        $data['user_id'] = $uid;
        $data['order_sn'] = $OrderObj->birthOrderId($uid);
        $data['good_table'] = 'shop';
        $data['pay_status'] =1;
        $data['pay_type'] = 'certification';
        $data['order_type'] = 'shop.v';
        $data['payname'] = $codes;
//        $data['make_level0'] = $level0;
//        $data['make_level1'] = $level[0];
//        $data['make_level2'] = $level[1];
//        $data['make_level3'] = $level[2];
        $data['make_level0'] = $Db_Shop['level0'];
        $data['make_level1'] = $Db_Shop['level1'];
        $data['make_level2'] = $Db_Shop['level2'];
        $data['make_level3'] = $Db_Shop['level3'];
        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
        $order_id=$OrderObj->add($data);
        if($order_id){
            $datas = array();
            $datas['is_verify'] = 1;//改变店铺状态
            $datas['isdel'] = 0;//改变店铺状态
            $ShopObj->edit($datas,$shop_id);
            $datass = array();
            $datass['shop_id'] = $shop_id;
            $datass['user_id'] = $uid;
            $datass['usetime'] = time();
            $datass['is_use'] = 1;//改变认证码状态
            $certifObj->edit($datass,$certifinfo['id']);
            $data['isok'] = 1;
        }else{
            $data['isok'] = 2;
        }
        Buddha_Http_Output::makeJson($data);
    }


}