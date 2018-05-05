<?php

/**
 * Class SingleinformationController
 */
class SingleinformationController extends Buddha_App_Action
{
    protected $tablenamestr;
    protected $tablename;
    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='传单';
        $this->tablename='singleinformation';
    }


    ////单页信息列表
    function index()
    {
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $UserObj=new User();
        $locdata = $RegionObj->getLocationDataFromCookie();
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') : 15;
        $act=Buddha_Http_Input::getParameter('act');
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 2;
        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id') : 0;

        $keyword = Buddha_Http_Input::getParameter('keyword');


        if($act=='list') {
            if($act=='list') {
                $where = " isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']}";

                $orderby = "";
                if(Buddha_Atom_String::isValidString($shop_id))
                {
                    $where .= "AND shop_id='{$shop_id}'";
                }else{
                    $orderby = " group by shop_id ";
                }
                $orderby .= "  order by id DESC ";
                if ($view) {
                    switch ($view) {
                        case 2;
                            $orderby = " group by shop_id order by add_time DESC ";
                            break;
                        case 3;
                            $where .= " and is_hot=1";
                            break;
                        /*case 4;
                            $orderby = "group by shop_id  order by budget ASC";
                            break;*/
                        case 5;
                            $where .= " and shop_id!=0";
                            break;
                    }
                }
                if ($keyword) {
                    $where .= " and name like '%{$keyword}%'";
                }

                $fields = array('id', 'shop_id','user_id', 'name', 'singleinformation_thumb','number');

                if($view==2)
                {
                    $CommonindexObj = new Commonindex();

                    $list =  $CommonindexObj->newestmore( $this->tablename,$fields,$page,$pagesize,$where);

                }else {

                $list = $this->db->getFiledValues ($fields,  $this->prefix.$this->tablename, $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
                }

                foreach($list as $k=>$v){
                    if($v['shop_id']!='0'){
                        $Db_shop=$ShopObj->getSingleFiledValues(array('name','specticloc','lng','lat'),"id='{$v['shop_id']}'");
                        $name=$Db_shop['name'];
                        if($Db_shop['roadfullname']=='0'){
                            $roadfullname='';
                        }else{
                            $roadfullname=$Db_shop['specticloc'];
                        }
                    }else{
                        $Db_user=$UserObj->getSingleFiledValues(array('username','realname','address'),"id='{$v['user_id']}'");
                        if($Db_user['address']=='0'){
                            $roadfullname='' ;
                        }else{
                            $roadfullname=$Db_user['address'];
                        }
                        if($Db_user['realname']=='0'){
                            $name=$Db_user['username'];
                        }else{
                            $name=$Db_user['realname'];
                        }
                    }

                    $lease[]=array(
                        'id'=>$v['id'],
                        'name'=>$v['name'],
                        'number'=>$v['number'],
                        'singleinformation_thumb'=>$v['singleinformation_thumb'],
                        'shop_name'=>$name,
                        'roadfullname'=>$roadfullname,
                        'demand_thumb'=>$v['demand_thumb'],
                    );
                }
                $data=array();
                if($lease){
                    $data['isok']='true';
                    $data['list']=$lease;
                    $data['data']='加载完成';
                }else{
                    $data['isok']='false';
                    $data['list']='';
                    $data['data']='没数据了';
                }
                Buddha_Http_Output::makeJson($data);
            }

        }
        $this->smarty->assign('view',$view);

        $CommonindexObj = new Commonindex();
        $filarr = array(
            0=>array('filed'=>'zuixin','a'=>'index','view'=>2),
            1=>array('filed'=>'fujin','a'=>'index','view'=>1),
            2=>array('filed'=>'remen','a'=>'index','view'=>3),
            );
        $Common = $CommonindexObj->indexmorenavlist($this->tablename,$filarr);
        $this->smarty->assign('navlist',$Common);


        $this->smarty->assign('c', $this->tablename);
        $this->smarty->assign('title', $this->tablenamestr);
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }







    public function mylist()
    {
        $id = (int)Buddha_Http_Input::getParameter('id')?(int)Buddha_Http_Input::getParameter('id') : 0;
        if (!$id) {
            Buddha_Http_Head::redirectofmobile('参数错误！', "index.php?a=index&c=activity", 2);
        }
       $SingleinformationObj=new Singleinformation();
       $ShopObj=new Shop();
        $title='信息';
        $singleinformation= $SingleinformationObj->fetch($id);
        $Db_Shop= $ShopObj->getSingleFiledValues(array('id','name','small','is_verify','brief'),"id='{$singleinformation['shop_id']}'");
        if(empty($singleinformation['shop_name'])){
            $singleinformation['shop_name']=$Db_Shop['name'];
        }

        $singleinformation['shop_small']=$Db_Shop['small'];
        $singleinformation['shop_is_verify']=$Db_Shop['is_verify'];
        if(mb_strlen($Db_Shop['brief']) > 10)
        {
            $singleinformation['shop_brief'] = mb_substr($Db_Shop['shop_brief'],0,10) . '...';
        }else{
            $singleinformation['shop_brief'] = $Db_Shop['brief'];
        }

        $singleinformation['shop_url']=$ShopObj->shop_url();




        $data['click_count']= $singleinformation['click_count']+1;
        $SingleinformationObj->edit($data,$id);
        ////////分享
        $WechatconfigObj  = new Wechatconfig();
        $sharearr = array(
            'share_title'=>$singleinformation['name'],
            'share_desc'=>$singleinformation['brief'],
            'ahare_link'=>"index.php?a=".__FUNCTION__."&c=".$this->c,
            'share_imgUrl'=>$singleinformation['singleinformation_thumb'],
        );
        $WechatconfigObj->getJsSign($sharearr['share_title'],$sharearr['share_desc'],$sharearr['ahare_link'],$sharearr['share_imgUrl']);
        ////////分享
        /**↓↓↓↓↓↓↓↓↓↓↓↓ 推荐 ↓↓↓↓↓↓↓↓↓↓↓↓**/
        $CommonObj = new Common();
        $recommend = $CommonObj->recommendBelongShop($singleinformation['shop_id'],$this->tablename,$id);
        $this->smarty->assign('recommend', $recommend);
//        print_r($recommend);
        /**↑↑↑↑↑↑↑↑↑↑↑↑ 推荐 ↑↑↑↑↑↑↑↑↑↑**/        $this->smarty->assign('Db_Shop', $Db_Shop);
        $this->smarty->assign('act', $singleinformation);
        $this->smarty->assign('title', $title);
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }
}

