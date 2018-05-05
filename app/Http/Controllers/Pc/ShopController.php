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

    public function index(){
        $ShopcatObj=new Shopcat();
        $RegionObj=new Region();
        $params = array ();
        $locdata = $RegionObj->getLocationDataFromCookie();

        $sort=Buddha_Http_Input::getParameter('sort')?Buddha_Http_Input::getParameter('sort'):'default';
        $pattern=Buddha_Http_Input::getParameter('pattern')?Buddha_Http_Input::getParameter('pattern'):'grid';
        $number=(int)Buddha_Http_Input::getParameter('number')?Buddha_Http_Input::getParameter('number'):''.$locdata['number'].'';
        $cid =(int)Buddha_Http_Input::getParameter('cid')?Buddha_Http_Input::getParameter('cid'):'0';
        $storetype=Buddha_Http_Input::getParameter('storetype');
        $property=Buddha_Http_Input::getParameter('property');

        $subid=$ShopcatObj->getSingleFiledValues(array('id','sub','cat_name'),"isdel=0 and id='{$cid}'");
        $catlist=$ShopcatObj->getcatist($cid);

        $choice=$RegionObj->getSingleFiledValues(array('id','number','name','immchildnum'),"number='{$number}'  order by  id  desc ");
        $father=$RegionObj->getSingleFiledValues(array('id','number','name','immchildnum'),"number='{$locdata['number']}'  order by  id  desc ");

        $region=$RegionObj->getFiledValues(array('name','number'),"father='{$father['id']}'");

        $params['sort'] = $sort;
        $params['pattern'] = $pattern;
        $params['cid'] = $cid;
        $params['number'] = $number;
        $params['storetype'] = $storetype;
        if($cid){
            $getcategory =$ShopcatObj->getcategory();
            $insql = $ShopcatObj->getInSqlByID($getcategory,$cid);
        }
        $where =" isdel=0 and is_sure=1 and state=0 {$locdata['sql']}";
        if($storetype){
            switch ($storetype){
                case '1':
                    $where.="and storetype=1";
                    break;
                case '5':
                    $where.="and storetype=5";
                    break;
            }
        }
        if($sort=='desc'){
            $orderby=" order by click_count DESC ";
        }elseif($sort=='asc'){
            $orderby=" order by click_count ASC ";
        }elseif($sort=='verify'){
            $orderby=" order by is_verify DESC ";
        }else{
            $orderby=" order by createtime DESC ";
        }
       if($cid){
            $where.=" and  shopcat_id IN {$insql}";
        }
        if($storetype!='' and $property!=''){

            $where.=" and storetype='{$storetype}' and property='{$property}'";
        }

        $rcount = $this->db->countRecords( $this->prefix.'shop', $where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize')?(int)Buddha_Http_Input::getParameter('pagesize') :30;
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }

        $fields =array('*');
         $list = $this->db->getFiledValues ($fields,  $this->prefix.'shop', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
         if($cid>0){
             $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&cid='.$cid.'&c=shop&pattern='.$pattern.'&', $pagesize );
         }else{
             $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=shop&pattern='.$pattern.'&', $pagesize );
         }

        //推荐店铺
        $listhot = $this->db->getFiledValues ($fields,  $this->prefix.'shop',"isdel=0 and is_sure=1 and state=0 and is_rec=1 {$locdata['sql']}  order by click_count DESC  limit 0,10");

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


    public function shop(){
        $ShopcatObj=new Shopcat();
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $params = array ();
        $locdata = $RegionObj->getLocationDataFromCookie();
        $sort=Buddha_Http_Input::getParameter('sort')?Buddha_Http_Input::getParameter('sort'):'default';
        $pattern=Buddha_Http_Input::getParameter('pattern')?Buddha_Http_Input::getParameter('pattern'):'list';
        $number=(int)Buddha_Http_Input::getParameter('number')?Buddha_Http_Input::getParameter('number'):''.$locdata['number'].'';
        $cid =(int)Buddha_Http_Input::getParameter('cid')?Buddha_Http_Input::getParameter('cid'):'0';
        $storetype=Buddha_Http_Input::getParameter('storetype');

        $subid=$ShopcatObj->getSingleFiledValues(array('id','sub','cat_name'),"isdel=0 and id='{$cid}'");
        $catlist=$ShopcatObj->getcatist($cid);

        $choice=$RegionObj->getSingleFiledValues(array('id','number','name','immchildnum'),"number='{$number}'  order by  id  desc ");
        $father=$RegionObj->getSingleFiledValues(array('id','number','name','immchildnum'),"number='{$locdata['number']}'  order by  id  desc ");

        $region=$RegionObj->getFiledValues(array('name','number'),"father='{$father['id']}'");

        $params['sort'] = $sort;
        $params['pattern'] = $pattern;
        $params['cid'] = $cid;
        $params['number'] = $number;
        $params['storetype'] = $storetype;
        if($cid){
            $getcategory =$ShopcatObj->getcategory();
            $insql = $ShopcatObj->getInSqlByID($getcategory,$cid);
        }
        $where =" isdel=0 and is_sure=1 and state=0 and storetype='{$storetype}' {$locdata['sql']}";
        if($sort=='desc'){
            $orderby=" order by click_count DESC ";
        }elseif($sort=='asc'){
            $orderby=" order by click_count ASC ";
        }elseif($sort=='verify'){
            $orderby=" order by is_verify DESC ";
        }

        if($cid){
            $where.=" and  shopcat_id IN {$insql}";
        }

        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('pagesize')?(int)Buddha_Http_Input::getParameter('pagesize') :30;

        $sql ="select DISTINCT property,  storetype   from {$this->prefix}shop  where {$where} {$orderby}  ".Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        $getAtt = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $rcount= count($getAtt);
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $fields =array('*');
        foreach( $getAtt as $k=>$v){
            $property = $v['property'];
            $total = $ShopObj->countRecords("isdel=0 and is_sure=1 and state=0 and property='{$property}' {$locdata['sql']}");
            $getAtt[$k]['total'] = $total;
        };
        $strPages = Buddha_Tool_Page::multLink ( $page, $rcount, 'index.php?a='.__FUNCTION__.'&c=shop&pattern='.$pattern.'&', $pagesize );




        //推荐店铺
        $listhot = $this->db->getFiledValues ($fields,  $this->prefix.'shop',"isdel=0 and is_sure=1 and state=0 and is_rec=1 {$locdata['sql']}  order by click_count DESC  limit 0,10");

        $this->smarty->assign('rcount',$rcount);
        $this->smarty->assign('pcount',$pcount);
        $this->smarty->assign('page',$page);
        $this->smarty->assign('strPages',$strPages);
        $this->smarty->assign('getAtt',$getAtt);
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
        $ShopcatObj=new Shopcat();
        $ShopObj=new Shop();
        $RegionObj=new Region();
        $locdata = $RegionObj->getLocationDataFromCookie();
        $id=(int)Buddha_Http_Input::getParameter('id');

        if(!$id){
            Buddha_Http_Head::redirect('参数错误','index.php?a=index&c=recruit');
        }
        $shop=$ShopObj->fetch($id);
        if(!$shop){
            Buddha_Http_Head::redirect('信息不存在','index.php?a=index&c=recruit');
        }

        $fields =array('*');
        $listhot = $this->db->getFiledValues ($fields,  $this->prefix.'shop',"isdel=0 and is_sure=1 and state=0 and is_rec=1 {$locdata['sql']}    order by is_rec DESC  limit 0,6");
        $cat=$ShopcatObj->goods_thumbgoods_thumb($shop['shopcat_id']);
        if($cat){
            $header='';
            foreach($cat as $k=>$v){
                $header.=' > <a href="index.php?a=index&c='.$this->c.'&cid='.$v['id'].'">'.$v['cat_name'].'</a>';
            }
        }
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
//        $Db_orderunm= $OrderObj->countRecords("user_id='{$user_id}' and good_id='{$id}' and good_table='{$c}' and pay_status=1 and createtime>$start and createtime<=$end order by createtime DESC" );
/////////////////////////////////////////////////////////////
        $order_id=(int)Buddha_Http_Input::getParameter('order_id');
        $start = time()-30*60;//付费查看电话过期时间
        if($user_id){
            $see= $OrderObj->countRecords("user_id='{$user_id}' and good_id='{$id}' and pay_status=1 and createtime>=$start");
        }else{
            $see= $OrderObj->countRecords("id='{$order_id}' and good_id='{$id}' and pay_status=1 and createtime>=$start");
        }
        if($shop['is_verify']==1){//认证
             $resstr=$shop['mobile'];
            $shop['verify']=0;
        }else if($shop['is_verify']==0){//非认证
            if($see){//付费
                $resstr=$shop['mobile'];
                $shop['verify']=0;
            }else{
//                $resstr=substr_replace($shop['mobile'],'****',3,4);
                $createtime=$shop['createtime'];//免费15天的开始时间
                $endtime = strtotime('+15 Day',strtotime(date('Y-m-d H:i:s',$createtime)));//免费15天的结束时间
                $newtime=time();
                if($createtime<$newtime  and $newtime< $endtime){
                    $resstr=$shop['mobile'];
                    $shop['verify']=1;
                }else{
                    $shop['verify']=0;
                    $resstr=substr_replace($shop['mobile'],'****',3,4);
                }
            }
        }
/////////////////////////////////////////////////////////////
        $shop['lng']=$shop['lng'];
        $shop['lat']=$shop['lat'];
        $shop['is_verify']=$shop['is_verify'];
        $shop['shop_name']=$shop['name'];
        $shop['mobile']=$resstr;
        $shop['address']=$area.$shop['specticloc'];




        $data=array();
        $data['click_count']=$shop['click_count']+1;
        $ShopObj->edit($data,$id);
        $this->smarty->assign('listhot',$listhot);
        $this->smarty->assign('shop',$shop);
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

        $erwma=array(
          'http'=>"http://www.bendishangjia.com/pc/index.php?a=detailed&c=shop&id={$shop['id']}",
            'logo'=>"<img src='http://www.bendishangjia.com/{$shop['small']}' style='position:absolute; width:30px; height: 30px; left: 50%; top: 50%; margin: -15px 0 0 -15px;'>",
        );

        $this->smarty->assign('erwma',$erwma);
      //   $this->smarty->assing('erwm',$erwm);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    //查询订单是否成功
    public function infosee(){
        $OrderObj=new Order();
        $ShopObj=new Shop();
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
           $Db_shop=$ShopObj->getSingleFiledValues(array('mobile'),"id='{$good_id}' and isdel=0");
           $jsondata = array();
           $jsondata['url'] = 'index.php?a=detailed&c='.$good_table;
           $jsondata['errcode'] = 0;
           $jsondata['mobile'] = $Db_shop['mobile'];
           $jsondata['errmsg'] ='ok';
           Buddha_Http_Output::makeJson($jsondata);
       }
    }
}