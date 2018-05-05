<?php
/**
 * Class GoodsController
 */
class GoodsController extends Buddha_App_Action
{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
    }

    public function lists(){
    	/******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $GoodsObj=new Goods();
        $ShopObj=new Shop();
        $params = array ();
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'): 1;
        $p=(int)Buddha_Http_Input::getParameter('p');
        $keyword=Buddha_Http_Input::getParameter('keyword');
        $searchType = array (1 => '全部',2=>'自营', 3=>'商家入驻',4 => '积分换购', 5 => '审核通过',6 => '审核未通过', 7=> '下架',8=> '推荐',9=> '热门');

        if(Buddha_Http_Input::getParameter('job')){
            $job=Buddha_Http_Input::getParameter('job');
            if(!Buddha_Http_Input::getParameter('ids')){
                Buddha_Http_Head::redirect('您没有选择参数','index.php?a=lists&c=goods&view='.$view.'&p='.$p);
            }
            $ids = implode ( ',',Buddha_Http_Input::getParameter('ids'));

            switch($job){
                case 'is_sure':
                    $GoodsObj->updateRecords(array('is_sure'=>1,'buddhastatus'=>0),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=lists&c=goods&view='.$view.'&p='.$p);
                    break;
                case 'stop':
                    $GoodsObj->updateRecords(array('is_sure'=>0,'buddhastatus'=>1,'isdel'=>4),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=lists&c=goods&view='.$view.'&p='.$p);
                    break;
                case 'sure':
                    $GoodsObj->updateRecords(array('is_sure'=>1,'buddhastatus'=>0,),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=lists&c=goods&view='.$view.'&p='.$p);
                    break;
                case 'enable':
                    $GoodsObj->updateRecords(array('is_sure'=>1,'buddhastatus'=>0,'isdel'=>0),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=lists&c=goods&view='.$view.'&p='.$p);
                    break;
                case 'is_hot':
                    $GoodsObj->updateRecords(array('is_hot'=>1),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=lists&c=goods&view='.$view.'&p='.$p);
                    break;
                case 'is_rec':
                    $GoodsObj->updateRecords(array('is_rec'=>1),"id IN ($ids)");
                    Buddha_Http_Head::redirect('操作成功','index.php?a=lists&c=goods&view='.$view.'&p='.$p);
                    break;
            }
        }

        $where = " (isdel=0 or isdel=4 )";
        if($view) {
            $params['view'] = $view;
            switch ($view) {
            	case 2;
                    $where .= ' and goods_type=1';
                    break;
                case 3;
                    $where .= ' and goods_type=2';
                    break;
                case 4;
                    $where .= ' and goods_type=3';
                    break;
                case 5;
                    $where .= " and is_sure=1";
                    break;
                case 6;
                    $where .= " and is_sure=4 ";
                    break;
                case 7;
                    $where .= " and  buddhastatus=1";
                    break;
                case 8;
                    $where .= " and is_sure=1 and  is_rec=1";
                    break;
                case 9;
                $where .= " and is_sure=1 and   is_hot=1";
                break;

            }
        }

        if($keyword){
            $where.=" and goods_name like '%$keyword%'";
            $params['keyword'] = $keyword;
        }
        $rcount= $GoodsObj->countRecords($where);
        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount/$pagesize);
        if($page > $pcount){
            $page=$pcount;
        }
        $orderby = " order by id DESC ";

        $list = $this->db->getFiledValues('', $this->prefix . 'goods', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));

        foreach($list as $k=>$v){
            $shop_name=$ShopObj->getSingleFiledValues(array('name'),"id='{$v['shop_id']}'");
            $list[$k]['shop_name']=  $shop_name['name'];
        }
        $strPages =  Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=goods&' .http_build_query($params).'&', $pagesize);
        $this->smarty->assign('rcount', $rcount);
        $this->smarty->assign('pcount', $pcount);
        $this->smarty->assign('page', $page);
        $this->smarty->assign('strPages', $strPages);
        $this->smarty->assign('list', $list);
        $this->smarty->assign( 'params', $params );


        $this->smarty->assign('searchType',$searchType);
        $this->smarty->assign( 'params', $params );
        $this->smarty->assign( 'keyword', $keyword );
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');

    }

    public function add(){//商品添加
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/
        $GoodsObj=new Goods();
        $ShopObj=new Shop();
        $GoodscatObj = new Goodscat();
        $SupplycatObj=new Supplycat();
        $OrderObj=new Order();
        $RegionObj = new Region();
        $GalleryObj=new Gallery();
        $GoodsimagesObj=new Goodsimages();
        $GoodspecObj = new Goodspec();
        $GoodsproductObj = new Goodsproduct();

        $goods_name=Buddha_Http_Input::getParameter('goods_name');
        $cat_id1=Buddha_Http_Input::getParameter('cat_id');
        $cat_id2=Buddha_Http_Input::getParameter('cat_id_2');
        $price=Buddha_Http_Input::getParameter('shop_price');
        $market_price=Buddha_Http_Input::getParameter('market_price');
        $keywords=Buddha_Http_Input::getParameter('keywords');
        $por_id=Buddha_Http_Input::getParameter('por_id');
        $city_id=Buddha_Http_Input::getParameter('city_id');
        $area_id=Buddha_Http_Input::getParameter('area_id');
        $is_sure=Buddha_Http_Input::getParameter('is_sure');
        if($is_sure == 3){
            $shopNum=Buddha_Http_Input::getParameter('shopNum');
            $shopInfo = $ShopObj->getSingleFiledValues(array('id','user_id'),"number='{$shopNum}'");
        }
        $is_rec=Buddha_Http_Input::getParameter('is_rec');
         //商品促销
        $is_promote=Buddha_Http_Input::getParameter('is_promote');
        $promote_price=Buddha_Http_Input::getParameter('promote_price');
        $promote_price2=Buddha_Http_Input::getParameter('promote_price2');
        $promote_start_date=Buddha_Http_Input::getParameter('promote_start_date');
        $promote_end_date=Buddha_Http_Input::getParameter('promote_end_date');
        //商品属性值
        $colors_0=Buddha_Http_Input::getParameter('colors_0');//字符串
        $guige_0=Buddha_Http_Input::getParameter('guige_0');//字符串
        $colors=Buddha_Http_Input::getParameter('colors');//颜色
        $guige=Buddha_Http_Input::getParameter('guige');//规格
        $size=Buddha_Http_Input::getParameter('size');//数组
        $spec=Buddha_Http_Input::getParameter('spec');//数组
        $pic=Buddha_Http_Input::getParameter('pic');//数组
        $profit=Buddha_Http_Input::getParameter('profit');//数组
        $stock=Buddha_Http_Input::getParameter('stock');//数组
        
        if(count($colors)>1){
            $colors = join('|',$colors);
        }else{
            $colors = $colors['0'];
        }
        if(count($guige)>1){
            $guige = join('|',$guige);
        }else{
            $guige = $guige['0'];
        }

        //描述、图片
        $goods_brief=Buddha_Http_Input::getParameter('goods_remark');
        $goods_desc=Buddha_Http_Input::getParameter('goods_desc');
        if(Buddha_Http_Input::isPost()){
            $agent_id = $RegionObj->getAgentUsurId($area_id);
            $data=array();
            $data['goods_name']=$goods_name;
            if($shopNum){
                $data['user_id']=$shopInfo['user_id'];
                $data['shop_id']=$shopInfo['id']; 
            }elseif($agent_id){
                $data['user_id'] = $agent_id;
            }
            if($is_rec == 1){
                $data['is_rec']=1;
            }
            $data['goods_sn']=date('ymdmis',time()).rand(10000,99999);
            $data['goodscat_id']=$cat_id1;
            $data['goodscat_id2']=$cat_id2;
            $data['market_price']=$price;
            $data['keywords']=$keywords;
            $data['add_time']=Buddha::$buddha_array['buddha_timestamp'];
            $data['goods_brief']=$goods_brief;
            $data['goods_desc']=$goods_desc;
            $data['goods_type']=$is_sure;
            $data['level1']=$por_id;
            $data['level2']=$city_id;
            $data['level3']=$area_id;
            $data['buddhastatus']=0;
            if($is_sure == 1 || $is_sure == 2){
                $data['is_sure'] = 1;
            }
            if ($is_promote == 1) {
                $data['is_promote'] = $is_promote;
                $data['promote_price'] = $promote_price;
                $data['promote_price2'] = $promote_price2;
                $data['promote_start_date'] = strtotime($promote_start_date);
                $data['promote_end_date'] = strtotime($promote_end_date);
            }
            $good_id = $GoodsObj->add($data);
            $datas = array();
            if($good_id && $colors){
                //添加属性规格
                $data = array();
                $data['user_id'] = $uid;
                $data['good_id'] = $good_id;
                $data['good_table'] = 'supply';
                $data['attrname1'] = $colors_0;
                $data['attrname2'] = $guige_0;
                $data['attrvalue1'] = $colors;
                $data['attrvalue2'] = $guige;
                $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];

                $goodspec_id = $GoodspecObj->add($data);
                if($goodspec_id){
                    for($i=count($size)-1;$i>=0;$i--){
                        $data = array();
                        $data['goods_id'] = $good_id;
                        $data['goodspec_id'] = $goodspec_id;
                        $data['goods_table'] = 'supply';
                        $data['sonattr1'] = $size[$i];
                        $data['sonattr2'] = $spec[$i];
                        $data['cost'] = $pic[$i];
                        $data['profit'] = $profit[$i];
                        $data['stock'] = $stock[$i];
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                        $GoodsproductObj->add($data);
                    }

                }
            }
            if($good_id){
                $MoreImage= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/goods/{$good_id}/",
                    array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('Image')
                    ->getAllReturnArray();
                if(is_array($MoreImage) and count($MoreImage)>0){
                    $GoodsimagesObj->pcaddimage($MoreImage, $good_id);
                    $GoodsObj->setFirstGalleryImgToGoods($good_id);
                }
                if($goods_desc){//富文本编辑器图片处理
                    $saveData = $GalleryObj->base_upload($goods_desc,$good_id,'goods');
                    $saveData = str_replace(PATH_ROOT,'/', $saveData);
                    $details['goods_desc'] = $saveData;
                    $GoodsObj->edit($details,$good_id);
                }
                
                echo 'alert("商品添加成功")';
                sleep(2);
                $url ='/manage/index.php?a=lists&c=goods&view=1';
            }else{
                echo 'alert("商品添加失败")';
                sleep(2);
                $url='/manage/index.php?a=lists&c=goods&view=1';
            }
            Header("Location: $url");
        }
        $proName = $RegionObj->getProName();
        $goods_cat = $GoodscatObj->goods_cat();
        Buddha_Editor_Set::getInstance()->setEditor(
            array (array ('id' => 'content', 'content' =>$Supply['goods_desc'], 'width' => '100', 'height' => 500 )
            ));
        $this->smarty->assign('proName',$proName);
        $this->smarty->assign('goods_cat',$goods_cat);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');  
    }


    public function edit(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $GoodsObj=new Goods();
        $GoodscatObj=new Goodscat();
        $ShopObj=new Shop();
        $RegionObj = new Region();
        $GoodsimagesObj=new Goodsimages();
        $p=(int)Buddha_Http_Input::getParameter('p');
        $view=(int)Buddha_Http_Input::getParameter('view');
        $id=(int)Buddha_Http_Input::getParameter('id');

        if(!$id){
            Buddha_Http_Head::redirect('参数错误！',"index.php?a=lists&c=goods&p={$p}&view={$view}");
        }
        $Goods=$GoodsObj->fetch($id);
        if(!$Goods){
            Buddha_Http_Head::redirect('没有找到您要的信息！',"index.php?a=lists&c=goods&p={$p}&view={$view}");
        }

        $buddhastatus=Buddha_Http_Input::getParameter('buddhastatus');
        $is_hot=Buddha_Http_Input::getParameter('is_hot');
        $is_sure=Buddha_Http_Input::getParameter('is_sure');
        $remarks=Buddha_Http_Input::getParameter('remarks');
        if(Buddha_Http_Input::isPost()){
            $data=array();
            if($buddhastatus){
                $data['buddhastatus']=0;
            }else{
                $data['buddhastatus']=1;
            }
            $data['is_hot']=$is_hot;
            $data['is_sure']=$is_sure;
            $data['remarks']=$remarks;
            if($GoodsObj->updateRecords($data,"id='{$id}'")){
                Buddha_Http_Head::redirect('编辑成功！',"index.php?a=lists&c=goods&p={$p}&view={$view}");
            }else{
                Buddha_Http_Head::redirect('编辑失败！',"index.php?a=lists&c=goods&p={$p}&view={$view}");
            }
        }
        Buddha_Editor_Set::getInstance()->setEditor(
            array (array ('id' => 'content', 'content' =>$Goods['goods_desc'], 'width' => '100', 'height' => 500 )
            ));

        $supplycat=$GoodscatObj->goods_thumbgoods_thumb($Goods['supplycat_id']);
        if($supplycat){
            $cat='';
            foreach($supplycat as $k=>$v){
                $cat.=$v['cat_name'].' > ';
            }
            $Goods['cat_name']=Buddha_Atom_String::toDeleteTailCharacter($cat);
        }
        $shop_name=$ShopObj->getSingleFiledValues(array('name'),"id='{$Goods['shop_id']}'");
        if($shop_name){
        $Goods['shop_name']=  $shop_name['name'];
        }
        $sheng = $RegionObj->getSingleFiledValues(array('name'),"id='{$Goods['level1']}'");
        $shi = $RegionObj->getSingleFiledValues(array('name'),"id='{$Goods['level2']}'");
        $qu = $RegionObj->getSingleFiledValues(array('name'),"id='{$Goods['level3']}'");
        $Goods['addres'] = $sheng['name'] . '<' . $shi['name'] . '<' . $qu['name'];
        $galleryimg=$GoodsimagesObj->getFiledValues('',"goods_id='{$Goods['id']}'");
        $proName = $RegionObj->getProName();
        $this->smarty->assign('Goods',$Goods);
        $this->smarty->assign('galleryimg',$galleryimg);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function por(){
        $RegionObj = new Region();
        $por_id=Buddha_Http_Input::getParameter('por_id');
        $cityName = $RegionObj->getCityName($por_id);
        if($cityName){
            $data['isok'] = 1;
            $data['data'] = $cityName;
            Buddha_Http_Output::makeJson($data);
        }
    }

    public function del(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $GoodsObj=new Goods();
        $GoodsimagesObj=new Goodsimages();
        $p=(int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p'):1;
        $view=(int)Buddha_Http_Input::getParameter('view');
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirect('参数错误！',"index.php?a=lists&c=goods&p={$p}&view={$view}");
        }
        $num=$GoodsObj->countRecords("id='{$id}'");
        if($num==0){
            Buddha_Http_Head::redirect('没有找到您要的信息！',"index.php?a=lists&c=goods&p={$p}&view={$view}");
        }
        $GoodsObj->del($id);
        $GoodsimagesObj->delGelleryimage($id);
        if($GoodsObj){
            Buddha_Http_Head::redirect('删除成功！',"index.php?a=lists&c=goods&p={$p}&view={$view}");
        }else{
            Buddha_Http_Head::redirect('删除失败！',"index.php?a=lists&c=goods&p={$p}&view={$view}");
        }

    }

    public function cat(){
 		/******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $GoodscatObj=new Goodscat();
        $getcatTable= $GoodscatObj->getcatlist();

        $this->smarty ->assign( 'getcatTable',$getcatTable);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');




    }
    public function addcat(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $GoodscatObj=new Goodscat();

        $sub=Buddha_Http_Input::getParameter('sub');
        $cat_name=Buddha_Http_Input::getParameter('cat_name');
        $unit=Buddha_Http_Input::getParameter('unit');
        $sort=Buddha_Http_Input::getParameter('sort');
        $ifopen=Buddha_Http_Input::getParameter('ifopen')?1:0;
        $cat_path = $GoodscatObj->getClassPath(0,$sub);
        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['sub']=$sub;
            $data['cat_path']=$cat_path;
            $data['cat_name']=trim($cat_name);
            $data['unit']=trim($unit);
            $data['view_order']=$sort;
            $data['ifopen']=$ifopen;

            $cart_id = $GoodscatObj->add($data);
            
            if($cart_id){
                $MoreImage= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/goodscat/",
                    array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('Image')
                    ->getAllReturnArray();
                if(is_array($MoreImage) and count($MoreImage)>0){
                    $GoodscatObj->pcaddimage($MoreImage, $cart_id);
                }
                $GoodscatObj->updatechildcount ($sub);
                Buddha_Http_Head::redirect('添加成功','index.php?a=addcat&c=goods');
            }
        }

       $cid=Buddha_Http_Input::getParameter('cid')?(int)Buddha_Http_Input::getParameter('cid'):0;
       $shopoption=$GoodscatObj->getOption($cid);
       $this->smarty->assign('shopoption',$shopoption);


        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public  function editcat(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $GoodscatObj=new Goodscat();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $sub=Buddha_Http_Input::getParameter('sub');
        $cat_name=Buddha_Http_Input::getParameter('cat_name');
        $unit=Buddha_Http_Input::getParameter('unit');
        $sort=Buddha_Http_Input::getParameter('sort');
        $ifopen=Buddha_Http_Input::getParameter('ifopen')?1:0;
        $cat_path = $GoodscatObj->getClassPath($id,$sub);
        $oldgoodscatalog=$GoodscatObj->fetch($id);

        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['sub']=$sub;
            $data['cat_name']=trim($cat_name);
            $data['unit']=trim($unit);
            $data['view_order']=$sort;
            $data['ifopen']=$ifopen;

            $GoodscatObj->edit($data,$id);
            $parentid = $sub;
            $cates ['sub']= $oldgoodscatalog['sub'];
            if ($cates ['sub'] != $parentid) {
                $GoodscatObj->updatepath ($id, $cat_path);
                $GoodscatObj-> updatechildcount ( $cates ['sub'] );
                $GoodscatObj->updatechildcount ( $parentid );
            }
            $MoreImage= Buddha_Http_Upload::getInstance()->setUpload( PATH_ROOT . "storage/goodscat/",
                array ('gif','jpg','jpeg','png'),Buddha::$buddha_array['upload_maxsize'] )->run('Image')
                ->getAllReturnArray();
            if(is_array($MoreImage) and count($MoreImage)>0){
                $GoodscatObj->pcaddimage($MoreImage, $id);
            }
            if($GoodscatObj){
                Buddha_Http_Head::redirect('编辑成功',"index.php?a=cat&c=goods");
            }else{
                Buddha_Http_Head::redirect('编辑错误',"index.php?a=cat&c=goods");
            }
        }
        $shopoption = $GoodscatObj ->getOption($oldgoodscatalog['sub']);
        $this->smarty->assign('shopoption',$shopoption);

        $shopcat=$GoodscatObj->fetch($id);
        if(!count($shopcat)){
            Buddha_Http_Head::redirect('信息不存在',"index.php?a=cat&c=goods");
        }
        $this->smarty->assign('shopcat',$shopcat);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function delimage(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/
        $GoodscatObj= new Goodscat();
        $id = Buddha_Http_Input::getParameter('id');
        $class_img = $GoodscatObj->getSingleFiledValues(array('class_img'),"id='{$id}'");
        $data['class_img'] = '';
        if($GoodscatObj->updateRecords($data,"id='{$id}'")){
            @unlink(PATH_ROOT . $class_img ['class_img'] );
            $thumimg['isok']=1;
            $thumimg['data']='图片删除成功';
        }else{
            $thumimg['isok']=0;
            $thumimg['data']='服务器忙';
        }
        Buddha_Http_Output::makeJson($thumimg);
    }

    public function delcat(){
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Db_Monitor::getInstance()->memberPrivilege($this->c.'.'.__FUNCTION__);
        /*******************/

        $GoodscatObj= new Goodscat();
        $id = Buddha_Http_Input::getParameter('id');
        $child=$GoodscatObj->fetch($id);
        if($child['child_count']>0){
            Buddha_Http_Head::redirect('删除失败,该分类下存在子类不能移除',"index.php?a=cat&c=goods");
        }
        $GoodscatObj->del($id);
        $GoodscatObj-> updatechildcount ($child ['sub'] );
        if($GoodscatObj){

            Buddha_Http_Head::redirect('删除成功',"index.php?a=cat&c=goods");
        }else{
            Buddha_Http_Head::redirect('删除失败',"index.php?a=cat&c=goods");
        }
    }

    public function getCatInfo(){//获取对应的二级分类
        $cat_id = Buddha_Http_Input::getParameter('cat_id');
        $GoodscatObj = new Goodscat();
        $goods_cat = $GoodscatObj->goods_cat($cat_id);
        if(count($goods_cat)>0){
            $data['isok'] = 1;
            $data['data'] = $goods_cat;
        }
        Buddha_Http_Output::makeJson($data);
    }

    function getShopName(){//根据编号获取店铺名称
        $num = Buddha_Http_Input::getParameter('num');
        $ShopObj = new Shop();
        $RegionObj = new Region();
        if($num){
            $shopInfo = $ShopObj->getSingleFiledValues(array('id','name','mobile','level1','level2','level3'),"number='{$num}'");
            if(count($shopInfo)>0){
                $reg = $RegionObj->getDetailOfAdrressByRegionIdStr($shopInfo['level1'],$shopInfo['level2'],$shopInfo['level3'],'<');
                $shopInfo['reg'] = $reg;
                $data['isok'] = 1;
                $data['data'] = $shopInfo;
            }
            Buddha_Http_Output::makeJson($data);
        }
    }

}