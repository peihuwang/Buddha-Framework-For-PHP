<?php

/**
 * Class RecruitController
 */
class RecruitController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }


    public function index(){
        $RecruitcatObj=new Recruitcat();
        $RegionObj=new Region();
        $params = array ();
        $locdata = $RegionObj->getLocationDataFromCookie();

        $sort=Buddha_Http_Input::getParameter('sort')?Buddha_Http_Input::getParameter('sort'):'default';
        $pattern=Buddha_Http_Input::getParameter('pattern')?Buddha_Http_Input::getParameter('pattern'):'grid';
        $number=(int)Buddha_Http_Input::getParameter('number')?Buddha_Http_Input::getParameter('number'):''.$locdata['number'].'';
        $cid =(int)Buddha_Http_Input::getParameter('cid')?Buddha_Http_Input::getParameter('cid'):'0';
        /**************************/
        $shop_id =(int)Buddha_Http_Input::getParameter('shop_id')?Buddha_Http_Input::getParameter('shop_id'):'0';
        /**************************/
        $subid=$RecruitcatObj->getSingleFiledValues(array('id','sub','cat_name'),"isdel=0 and id='{$cid}'");
        $catlist=$RecruitcatObj->getcatist($cid);

        $choice=$RegionObj->getSingleFiledValues(array('id','number','name','immchildnum'),"number='{$number}'  order by  id  desc ");
        $father=$RegionObj->getSingleFiledValues(array('id','number','name','father','level'),"number='{$locdata['number']}'  order by  id  desc ");

        if($father['level']==2){
            $region=$RegionObj->getFiledValues(array('name','number'),"father='{$father['id']}'");
        }else{
            $region=$RegionObj->getFiledValues(array('name','number'),"father='{$father['father']}'");
        }

        $link='';
        $params['sort'] = $sort;
        $params['pattern'] = $pattern;
        $params['cid'] = $cid;
        $params['number'] = $number;
        if($sort){
            $link.='&sort='.$sort;
        }
        if($pattern){
            $link.='&pattern='.$pattern;
        }
        if($cid){
            $link.='&cid='.$cid;
        }
        if($number){
            $link.='&number='.$number;
        }

        if($cid){
            $getcategory =$RecruitcatObj->getcategory();
            $insql = $RecruitcatObj->getInSqlByID($getcategory,$cid);
        }

        $where =" isdel=0 and is_sure=1 and buddhastatus=0 {$locdata['sql']}";
        /**************************/
        if($shop_id != 0){
            $where .= " and shop_id={$shop_id}";
        }
        /**************************/

        $orderby = " order by add_time DESC ";
        if($sort=='desc'){
            $orderby=" order by click_count DESC ";
        }elseif($sort=='asc'){
            $orderby=" order by click_count ASC ";
        }elseif($sort=='verify'){
            $orderby=" order by is_verify DESC ";
        }
        if($cid){
            $where.=" and  recruit_id IN {$insql}";
        }

        $rcount = $this->db->countRecords( $this->prefix.'recruit', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize')?(int)Buddha_Http_Input::getParameter('pagesize') :30;

        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }

        $fields =array('*');
        $list = $this->db->getFiledValues ($fields,  $this->prefix.'recruit', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=recruit'.$link.'&', $pagesize );

        //推荐店铺
        $listhot = $this->db->getFiledValues ($fields,  $this->prefix.'recruit',"isdel=0 and is_sure=1 and buddhastatus=0 and is_rec=1 {$locdata['sql']}  order by click_count DESC  limit 0,20");


        $this->smarty->assign('rcount',$rcount);
        $this->smarty->assign('pcount',$pcount);
        $this->smarty->assign('page',$page);
        $this->smarty->assign('strPages',$strPages);
        $this->smarty->assign('list',$list);
        $this->smarty->assign('params',$params);
        $this->smarty->assign('catlist',$catlist);
        $this->smarty->assign('region',$region);
        $this->smarty->assign('subid',$subid);//已选择条件
        $this->smarty->assign('choice',$choice);//已选择条件
        $this->smarty->assign('father',$father);//已选择条件
        $this->smarty->assign('listhot',$listhot);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function detailed(){
        $user_id = (int)Buddha_Http_Cookie::getCookie('uid');
        $OrderObj=new Order();
        $RecruitObj=new Recruit();
        $RecruitcatObj=new Recruitcat();
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $locdata = $RegionObj->getLocationDataFromCookie();
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirect('参数错误','index.php?a=index&c=recruit');
        }
        $recruit=$RecruitObj->fetch($id);
        if(!$recruit){
            Buddha_Http_Head::redirect('信息不存在','index.php?a=index&c=recruit');
        }

        $fields =array('*');
        $listhot = $this->db->getFiledValues ($fields,  $this->prefix.'recruit',"isdel=0 and is_sure=1 and buddhastatus=0 and is_rec=1 {$locdata['sql']}   order by click_count DESC  limit 0,20");
        $cat=$RecruitcatObj->goods_thumbgoods_thumb($recruit['recruit_id']);
        if($cat){
            $header='';
            foreach($cat as $k=>$v){
                $header.=' > <a href="index.php?a=index&c='.$this->c.'&cid='.$v['id'].'">'.$v['cat_name'].'</a>';
            }
        }
        $shop=$ShopObj->getSingleFiledValues('',"id='{$recruit['shop_id']}'");
        $region=$RegionObj->getAllArrayAddressByLever($shop['level3']);
        if(count($region)>0){
            $area='';
            foreach($region as $k=>$v){
                if($v['id']!=1){
                    $area.=$v['name'].' - ';
                }
            }
        }
        $startstr = date('Y-m-d',time());
        $start= strtotime($startstr);
        $end=time();
        $c= $this->c;
////////////////////////////////////////////////////////////////////////////////////////
        $order_id=(int)Buddha_Http_Input::getParameter('order_id');
        $start = time()-30*60;//付费查看电话过期时间
        if($user_id){
            $see= $OrderObj->countRecords("user_id='{$user_id}' and good_id='{$id}' and pay_status=1 and createtime>=$start");
        }else{
            $see= $OrderObj->countRecords("id='{$order_id}' and good_id='{$id}' and pay_status=1 and createtime>=$start");
        }
        if($shop['is_verify']==1){//认证
            $resstr=$shop['mobile'];
            $demand['verify']=0;
        }else if($shop['is_verify']==0){//非认证
            if($see){//付费
                $resstr=$shop['mobile'];
                $recruit['verify']=0;
            }else{
                $createtime=$shop['createtime'];//免费15天的开始时间
                $endtime = strtotime('+15 Day',strtotime(date('Y-m-d H:i:s',$createtime)));//免费15天的结束时间
                $newtime=time();
                if($createtime<$newtime  and $newtime< $endtime){
                    $resstr=$shop['mobile'];
                    $recruit['verify']=1;
                }else{
                    $recruit['verify']=0;
                    $resstr=substr_replace($shop['mobile'],'****',3,4);
                }
            }
        }
////////////////////////////////////////////////////////////////////////////////////////
        $recruit['lng']=$shop['lng'];
        $recruit['lat']=$shop['lat'];
        $recruit['is_verify']=$shop['is_verify'];
        $recruit['shop_name']=$shop['name'];
        $recruit['mobile']=$resstr;
        $recruit['address']=$area.$shop['specticloc'];
        $data=array();
        $data['click_count']=$recruit['click_count']+1;
        $RecruitObj->edit($data,$id);
        $this->smarty->assign('listhot',$listhot);
        $this->smarty->assign('recruit',$recruit);
        $this->smarty->assign('header',$header);

        //支付函数
        $pay=array(
            'good_id'=>$shop['id'],
            'good_table'=>'shop',
            'order_type'=>'info.see',
            'pc'=>'1',
            'final_amt'=>'0.2',
        );
        $this->smarty->assign('pay',$pay);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    //查询订单是否成功
    public function infosee(){
        $OrderObj=new Order();
        $RecruitObj=new Recruit();
        $user_id = (int)Buddha_Http_Cookie::getCookie('uid');
        $good_id=Buddha_Http_Input::getParameter('id');
        $good_table=Buddha_Http_Input::getParameter('good_table');
        if(!$user_id){
            $jsondata = array();
            $jsondata['url'] = 'index.php?a=login&c=account';
            $jsondata['errcode'] = 1;
            $jsondata['errmsg'] = "请登陆";
            Buddha_Http_Output::makeJson($jsondata);
        }
        $startstr = date('Y-m-d',time());
        $start= strtotime($startstr);
        $end=time()-100;
        $Db_orderunm= $OrderObj->countRecords("user_id='{$user_id}' and good_id='{$good_id}' and good_table='{$good_table}' and pay_status=1 and createtime>$start and createtime<=$end order by createtime DESC" );

        if($Db_orderunm){
            $Db_shop=$RecruitObj->getSingleFiledValues(array('mobile'),"id='{$good_id}' and isdel=0");
            $jsondata = array();
            $jsondata['url'] = 'index.php?a=detailed&c='.$good_table;
            $jsondata['errcode'] = 0;
            $jsondata['mobile'] = $Db_shop['mobile'];
            $jsondata['errmsg'] ='ok';
            Buddha_Http_Output::makeJson($jsondata);
        }
    }
}