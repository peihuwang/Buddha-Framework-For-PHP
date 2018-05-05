<?php

/**
 * Class GoodsController
 */
class GoodsController extends Buddha_App_Action
{

    protected $tablenamestr;
    protected $tablename;
    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
        $this->tablenamestr='商品';
        $this->tablename='goods';
    }

    public function index(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $act=Buddha_Http_Input::getParameter('act');
        $uid = Buddha_Http_Cookie::getCookie('uid');
        $view=Buddha_Http_Input::getParameter('view')?(int)Buddha_Http_Input::getParameter('view'):1;
        $Today=$Tomorrow=$currentdate=0;
        $Today=strtotime(date('Y-m-d'));//今天0点时间戳
        $Tomorrow=strtotime(date('Y-m-d',strtotime('+1 day')));//明天0点时间戳
        $currentdate=time();//当前时间戳
        $GoodsObj = new Goods();
        $num =  $GoodsObj->countRecords ("user_id='{$uid}' AND is_promote=1 AND promote_end_date<{$currentdate}" );
        if($num){
            $Promotion = $GoodsObj->getFiledValues('',"user_id='{$uid}' AND is_promote=1 AND promote_end_date<{$currentdate}");
            foreach ($Promotion as $k => $v) {
                if($v['promote_end_date']<$currentdate){
                    $data['is_promote'] = 0;
                    $data['promote_price'] = 0.00;
                    $GoodsObj->updateRecords($data,"id='{$v['id']}'");
                }
            }
        }

        if($act=='list'){
            $keyword=Buddha_Http_Input::getParameter('keyword');
            $where = "isdel=0 and user_id='{$uid}'";
            if($keyword){
                $where.=" and (goods_name like '%{$keyword}%' or goods_sn like '%{$keyword}%') ";
            }
            $times = time() - 604800;//七天前时间戳
            if($view){
                switch($view){
                    case 2;
                        $where.=" and isdel=0 and is_sure=1  AND add_time>'{$times}'";
                        break;
                    case 3;
                        $where.=" and isdel=0 and is_sure=0 ";
                        break;
                    case 4;
                        $where.=" and isdel=0 and is_sure=4 ";
                        break;
                    case 5;
                $where.=" and isdel=0 and is_sure=1 and promote_price > 0 and {$Today } <  {$currentdate} and {$currentdate} < {$Tomorrow}";
                        break;
                }
            }
          //  $rcount = $this->db->countRecords( $this->prefix.'supply', $where);
            $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') :1;
            $pagesize = Buddha::$buddha_array['page']['pagesize'];

            $orderby = " order by id DESC ";
            $list = $this->db->getFiledValues (array('id','user_id','goods_thumb','goods_name','market_price','market_price2','goods_sn','is_sure','promote_price','is_promote','buddhastatus'),  $this->prefix.'goods', $where . $orderby . Buddha_Tool_Page::sqlLimit ( $page, $pagesize ) );
            $UserObj= new User();
            $sure=$UserObj->getSingleFiledValues(array('groupid'),"id='{$uid}' and isdel=0");

            foreach($list as $k=>$v)
            {
                if($v['is_sure']==0){
                    $issureimg='checked';//审核中
                }elseif($v['is_sure']==4){
                    $issureimg='fail';//未通过
                }elseif($v['is_sure']==1){
                    $issureimg='pass';//已通过
                }

                if($v['market_price2']!='' && $v['market_price2']!=0.00 && $v['market_price2']!= NULL){
                    $market_price=$v['market_price'].'—'.$v['market_price2'];
                }else{
                    $market_price=$v['market_price'];
                }
                if($v['promote_price'] == 0.00){
                   $promote_price = 0;
                }else{
                   $promote_price = $v['promote_price'];
                }
                $jsondata[]=array(
                    'id'=>$v['id'],
                    'title'=>$v['goods_name'],
                    'images'=>$v['goods_thumb'],
                    'goods_sn'=>$v['goods_sn'],
                    'user_id'=>$v['user_id'],
                    'is_sure'=>$v['is_sure'],
                    'issureimg'=>$issureimg,
                    'buddhastatus'=>$v['buddhastatus'],
                    'goods_name'=>$v['goods_name'],
                    'market_price'=>$market_price,
                    'promote_price'=>$promote_price,
                    'goods_thumb'=>$v['goods_thumb'],
                    'is_promote'=>$v['is_promote'],
                );
            }
//            $infotop=array('id'=>$id,'good_table'=>'supply','order_type'=>'info.top','final_amt'=>'0.2');

            /**↓↓↓↓↓↓↓↓↓↓↓ 置顶参数 ↓↓↓↓↓↓↓↓↓↓↓**/
            $datas['top']['good_table']=$this->tablename;
            $datas['top']['order_type']='info.top';
            $datas['top']['final_amt']=0.2;
            /**↑↑↑↑↑↑↑↑↑↑ 置顶参数 ↑↑↑↑↑↑↑↑↑↑**/

            $CommonObj = new Common();
            $Nws= $CommonObj->page_where($page,$list,$pagesize);
            $datas['info'] = $Nws;

            if(is_array($list) and count($list)>0){
                $datas['isok']='true';
                $datas['data']=$jsondata;
            }else{
                    $datas['isok']='false';
                    $datas['data']='没有数据';
            }

            Buddha_Http_Output::makeJson($datas);
        }
        $this->smarty->assign('view', $view);
        $this->smarty->assign('c', $this->tablename);
        $this->smarty->assign('title', $this->tablenamestr);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function add(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $GoodscatObj=new Goodscat();
        $OrderObj=new Order();
        $ShopObj=new Shop();
        $GoodsObj=new Goods();
        $GalleryObj=new Gallery();
        $GoodspecObj = new Goodspec();
        $GoodsproductObj = new Goodsproduct();
        $GoodsimagesObj = new Goodsimages();
        $RegionObj = new Region();
        $area = $RegionObj->getSingleFiledValues(array('name'),"id='{$UserInfo['level3']}'");
        $title='商品';
        $goods_name=Buddha_Http_Input::getParameter('good_name');
        $supplycat_id=Buddha_Http_Input::getParameter('shopcat_id');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $goods_unit=Buddha_Http_Input::getParameter('goods_unit');
        $price=Buddha_Http_Input::getParameter('price');
        $price2=Buddha_Http_Input::getParameter('price2');
        $keywords=Buddha_Http_Input::getParameter('keywords');

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
        //商品图片
        $Image=Buddha_Http_Input::getParameter('Image');//数组

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
        $goods_brief=Buddha_Http_Input::getParameter('goods_brief');
        $goods_desc=Buddha_Http_Input::getParameter('goods_desc');

        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['goods_name']=$goods_name;
            $data['user_id']=$uid;
            $data['goods_sn']=date('ymdmis',time()).rand(10000,99999);
            $data['shop_id']=$shop_id;
            $data['goods_unit']=$goods_unit;
            $data['market_price']=$price;
            $data['market_price2']=$price2;
            $data['keywords']=$keywords;
            $data['add_time']=Buddha::$buddha_array['buddha_timestamp'];
            $data['goods_brief']=$goods_brief;
            $data['goods_desc']=$goods_desc;
            $data['goods_type']=1;
            $data['is_sure']=1;
            if(stripos($supplycat_id,',')){
                $goodscat = explode(',',$supplycat_id);
                $data['goodscat_id']=$goodscat[0];
                $data['goodscat_id2']=$goodscat[1];
            }else{
               $data['goodscat_id']=$supplycat_id; 
            }
            $data['is_remote']=0;
            $data['level0']=$UserInfo['level0'];
            $data['level1']=$UserInfo['level1'];
            $data['level2']=$UserInfo['level2'];
            $data['level3']=$UserInfo['level3'];
            $good_id = $GoodsObj->add($data);
            $datas = array();
            if($good_id){
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
                $img = $GoodsimagesObj->goods_base64_upload($Image,$good_id);
                $imgs['goods_thumb'] = $img;
                $imgs['goods_img'] = $img;
                $imgs['goods_large'] = $img;
                $GoodsObj->updateRecords($imgs,"id='{$good_id}'");
                if($goods_desc){//富文本编辑器图片处理
                    $saveData = $GalleryObj->base_upload($goods_desc,$good_id);
                    $saveData = str_replace(PATH_ROOT,'/', $saveData);
                    $details['goods_desc'] = $saveData;
                    $GoodsObj->edit($details,$good_id);
                }
                $datas['isok']='true';
                $datas['data']='商品添加成功';
                $datas['url']='index.php?a=index&c=goods';
            }else{
                $datas['isok']='false';
                $datas['data']='商品添加失败';
                $datas['url']='index.php?a=add&c=goods';

            }
            Buddha_Http_Output::makeJson($datas);
        }


        $getshoplistOption=$ShopObj->getShoplistOption($uid,0);
        $gettableOption=$GoodscatObj->getunitOption();
        $this->smarty->assign('gettableOption', $gettableOption);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('title', $title);
        $this->smarty->assign('area',$area);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function edit(){
        $GoodsObj=new Goods();
        $GoodscatObj=new Goodscat();
        $OrderObj=new Order();
        $ShopObj=new Shop();
        $GalleryObj = new Gallery();
        $GoodsimagesObj=new Goodsimages();
        $RegionObj=new Region();
        $GoodspecObj = new Goodspec();
        $GoodsproductObj = new Goodsproduct();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $area = $RegionObj->getSingleFiledValues(array('name'),"id='{$UserInfo['level3']}'");
        if(!$uid)
        {
            Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=goods',2);
        }
        $id=(int)Buddha_Http_Input::getParameter('id');
        if(!$id)
        {
          Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=goods',2);
        }

        $goods=$GoodsObj->getSingleFiledValues('',"id='{$id}' and user_id='{$uid}'");

        if(!$goods)
        {
            Buddha_Http_Head::redirectofmobile('没有找到您要的信息！','index.php?a=index&c=goods',2);
        }

        if($goods['user_id'] != $uid){
            Buddha_Http_Head::redirectofmobile('您没有权限进行此操作！','index.php?a=index&c=goods',2);
        }
        $title='商品';
        //产品属性
        $GoodspecObj = new Goodspec();
        $GoodsproductObj = new Goodsproduct();


        $attr = $GoodspecObj->getSingleFiledValues('',"user_id='{$uid}' AND good_id='{$id}' AND good_table='supply'");

        if(Buddha_Atom_Array::isValidArray($attr)){
            if(stripos($attr['attrvalue1'],'|')){
                $attr['attrvalue1'] = explode('|', $attr['attrvalue1']);
            }
            if(stripos($attr['attrvalue2'],'|')){
                $attr['attrvalue2'] = explode('|', $attr['attrvalue2']);
            }
        }

        //商品子表
        $goodsson = $GoodsproductObj->getFiledValues('',"goods_id='{$id}' AND goodspec_id='{$attr['id']}'");
        $goods_cat = $GoodscatObj->getSingleFiledValues(array('id','sub','child_count','cat_name'),"id='{$goods['goodscat_id']}'");
        if($goods['goodscat_id2']){
            $goods_cat2 = $GoodscatObj->getSingleFiledValues(array('id','sub','child_count','cat_name'),"id='{$goods['goodscat_id2']}'");
        }
        $goodscat=array();
        $goodscat[0]=$goods_cat;
        if($goods_cat2){
            $goodscat[1]=$goods_cat2;
        }
        if($goodscat){
            $cat_name='';
            foreach ($goodscat as $k=>$v){
                $cat_name.=$v['cat_name'].' > ';
            }
            $cat_name=trim($cat_name,' > ');

            $goods['cat_name']=$cat_name;
        }
        $act=Buddha_Http_Input::getParameter('act');
        $goods_name=Buddha_Http_Input::getParameter('good_name');
        $supplycat_id=Buddha_Http_Input::getParameter('shopcat_id');
        $shop_id=Buddha_Http_Input::getParameter('shop_id');
        $goods_unit=Buddha_Http_Input::getParameter('goods_unit');
        $price=Buddha_Http_Input::getParameter('price');
        $price2=Buddha_Http_Input::getParameter('price2');
        $keywords=Buddha_Http_Input::getParameter('keywords');
        //描述、图片
        $goods_brief=Buddha_Http_Input::getParameter('goods_brief');
        $goods_desc=Buddha_Http_Input::getParameter('goods_desc');

        //商品属性值
        $colors_0=Buddha_Http_Input::getParameter('colors_0');//字符串
        $guige_0=Buddha_Http_Input::getParameter('guige_0');//字符串
        $colors=Buddha_Http_Input::getParameter('colors');//数组
        $guige=Buddha_Http_Input::getParameter('guige');//数组
        $size=Buddha_Http_Input::getParameter('size');//数组
        $spec=Buddha_Http_Input::getParameter('spec');//数组
        $pic=Buddha_Http_Input::getParameter('pic');//数组
        $profit=Buddha_Http_Input::getParameter('profit');//数组
        $stock=Buddha_Http_Input::getParameter('stock');//数组
        //商品图片
        $Image=Buddha_Http_Input::getParameter('Image');//数组
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

        if(Buddha_Http_Input::isPost()){
            $data=array();
            $data['goods_name']=$goods_name;
            $data['user_id']=$uid;
            $data['supplycat_id']=$supplycat_id;
            $data['shop_id']=$shop_id;
            $data['goods_unit']=$goods_unit;
            $data['market_price']=$price;
            $data['market_price2']=$price2;
            $data['keywords']=$keywords;
            $data['goods_brief']=$goods_brief;
            //$data['goods_desc']=$goods_desc;
            if($promote_price){
                $data['is_promote']=$is_promote;
                $data['promote_price']=$promote_price;
                $data['promote_price2']=$promote_price2;
                $data['promote_start_date']=strtotime($promote_start_date);
                $data['promote_end_date']=strtotime($promote_end_date);
            }else{
                $data['is_promote']=0;
                $data['promote_price']='';
                $data['promote_start_date']='';
                $data['promote_end_date']='';
            }
            if($regionstr){
                $level = explode(",", $regionstr);
                $data['is_remote']=$is_remote;
                $data['level0']=1;
                $data['level1']=$level[0];
                $data['level2']=$level[1];
                $data['level3']=$level[2];
            }else{
                $data['is_remote']=0;
                $Db_level=$ShopObj->getSingleFiledValues(array('level0','level1','level2','level3'),"user_id='{$uid}' and id='{$shop_id}' and isdel=0");
                $data['level0']=$Db_level['level0'];
                $data['level1']=$Db_level['level1'];
                $data['level2']=$Db_level['level2'];
                $data['level3']=$Db_level['level3'];
            }
            $GoodsObj->edit($data,$id);

            //添加属性规格
            $data = array();
            $data['attrname1'] = $colors_0;
            $data['attrname2'] = $guige_0;
            $data['attrvalue1'] = $colors;
            $data['attrvalue2'] = $guige;
            $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
            $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
            $GoodspecObj->updateRecords($data,"id='{$attr['id']}'");
            $GoodsproductObj->delRecords("goods_id='{$id}' AND goodspec_id='{$attr['id']}' AND goods_table='supply'");
            for($i=count($size)-1;$i>=0;$i--){
                $data = array();
                $data['goods_id'] = $id;
                $data['goodspec_id'] = $attr['id'];
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
            $datas = array();
            if($GoodsObj){
                if($Image){
                    $num = $GoodsimagesObj->countRecords("goods_id='{$id}'");
                    $img = $GoodsimagesObj->goods_base64_upload($Image,$id);
                    if(!$num){
                        $imgs['goods_thumb'] = $img;
                        $imgs['goods_img'] = $img;
                        $imgs['goods_large'] = $img;
                        $GoodsObj->updateRecords($imgs,"id='{$id}'"); 
                    }
                }
                if($goods_desc){//富文本编辑器图片处理
                    $dirs = PATH_ROOT."storage/quill/{$id}/";
                    if(is_dir($dirs)){
                        if ($dh = opendir($dirs)){
                            while (($file = readdir($dh)) !== false){
                                //$filePath = $dirs.$file;
                                if(!strstr($goods_desc,$file) and $file != '.' and $file !='..'){
                                    @unlink($dirs.$file);//删除修改后的图片
                                    /*echo $file;
                                    exit;*/
                                }
                            }
                        }
                        //$GalleryObj->deleteDir($dirs);
                    }
                    $saveData = $GalleryObj->base_upload($goods_desc,$id);//base64图片上传
                    if($saveData){
                       $saveData = str_replace(PATH_ROOT,'/', $saveData);//替换
                       $details['goods_desc'] = $saveData;
                    }else{
                        $details['goods_desc'] = $goods_desc;
                    }
                    $GoodsObj->edit($details,$id);//更新数据
                }
                $datas['isok']='true';
                $datas['data']='商品编辑成功';
                $datas['url']='index.php?a=index&c=goods';
            }else{
                $datas['isok']='false';
                $datas['data']='商品编辑失败';
                $datas['url']='index.php?a=edit&c=goods';
            }
            Buddha_Http_Output::makeJson($datas);
        }

        //产品相册
        $gimages = $GoodsimagesObj->getGoodsImage($id);
        $getshoplistOption=$ShopObj->getShoplistOption($uid,$goods['shop_id']);
        $Goodscat=$GoodscatObj->goods_thumbgoods_thumb($goods['goodscat_id']);
      //区域名称拼接
        $Region_name=$RegionObj->getAllArrayAddressByLever($goods['level3']);
        if($Region_name){
        $regionname='';
        foreach($Region_name as $k=>$v){
            if($k!=0){
                $regionname.=$v['name'].' > ';
            }
        }
        $goods['region_name']=Buddha_Atom_String::toDeleteTailCharacter($regionname);
        }
        $gettableOption=$GoodscatObj->getunitOption($goods['goods_unit']);
        $this->smarty->assign('getshoplistOption', $getshoplistOption);
        $this->smarty->assign('gettableOption', $gettableOption);
        $this->smarty->assign('gimages', $gimages);
        $this->smarty->assign('goods', $goods);
        $this->smarty->assign('attr', $attr);
        $this->smarty->assign('area',$area);
        $this->smarty->assign('goodsson', $goodsson);

       //消息置顶
        $Top=$OrderObj->getFiledValues(array('final_amt','createtime'),"isdel=0 and good_id='{$id}' and order_type='info.top' and pay_status=1 and user_id='{$uid}'");
        if(count($Top)>0){
        foreach ($Top as $k=>$v){
            $Top[$k]['name']=$goods['goods_name'];
        }
        }
        $this->smarty->assign('Top', $Top);
        $infotop=array('id'=>$id,'good_table'=>'supply','order_type'=>'info.top','final_amt'=>'0.2');

        $this->smarty->assign('infotop', $infotop);
        $this->smarty->assign('title', $title);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function del(){
        $GoodsimagesObj=new Goodsimages();
        $GoodsObj=new Goods();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $GoodsObj->del($id);
        $GoodsimagesObj->delGelleryimage($id);
        $thumimg=array();
        if($GoodsObj){
            $thumimg['isok']='true';
            $thumimg['data']='删除成功';
        }else{
            $thumimg['isok']='false';
            $thumimg['data']='删除失败';
        }
        Buddha_Http_Output::makeJson($thumimg);
    }

    //相册图片删除
    public  function delimage(){
        $GoodsObj = new Goods();
        $GoodsimagesObj=new Goodsimages();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $thumimg=array();
        if(!$id){
            $thumimg['isok']='false';
            $thumimg['data']='参数错误';
        }
        $gimages=$GoodsimagesObj->fetch($id);
        if ($gimages){
            if($gimages['isdefault']==0){
                $GoodsimagesObj->del($id);
                @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
                @unlink(PATH_ROOT . $gimages ['goods_img']);
                @unlink(PATH_ROOT . $gimages ['goods_large']);
                @unlink(PATH_ROOT . $gimages ['sourcepic'] );
            }else{
                $img = $GoodsimagesObj->getSingleFiledValues(array('id','goods_thumb'),"goods_id='{$gimages['goods_id']}' ORDER BY id DESC");
                if($img){
                    $data['isdefault'] = 1;
                    $GoodsimagesObj->updateRecords($data,"id='{$img['id']}'");
                    $datas['goods_thumb'] = $img['goods_thumb'];
                    $datas['goods_img'] = $img['goods_thumb'];
                    $datas['goods_large'] = $img['goods_thumb'];
                    $GoodsObj->updateRecords($datas,"id='{$gimages['goods_id']}'");
                }
                $GoodsimagesObj->del($id);
                @unlink(PATH_ROOT . $gimages ['goods_thumb'] );
                @unlink(PATH_ROOT . $gimages ['goods_img']);
                @unlink(PATH_ROOT . $gimages ['goods_large']);
                @unlink(PATH_ROOT . $gimages ['sourcepic'] );
            }
            
            $thumimg['isok']='true';
            $thumimg['data']='图片删除成功';
        }else{
            $thumimg['isok']='false';
            $thumimg['data']='服务器忙'; 
        }

        Buddha_Http_Output::makeJson($thumimg);
    }

    public function goodscat(){
       $GoodscatObj = new Goodscat();
        $fid = Buddha_Http_Input::getParameter('fid');
        $Db_Shopcat = $GoodscatObj->getSupplycatlist($fid);

        $datas = array();
        if($Db_Shopcat){
            $datas['isok']='true';
            $datas['data']=$Db_Shopcat;
        }else{
            $datas['isok']='false';
            $datas['data']='';
        }
        Buddha_Http_Output::makeJson($datas);
    }

    public function ajaxadderr(){
         $RegionObj=new Region();
         $fid = Buddha_Http_Input::getParameter('fid');
         if($fid==''){
             $fid=1;
         }
         $Db_Region= $RegionObj->getFiledValues(array('id','immchildnum','name','father','level'),"father='{$fid}' and isdel=0");
         $datas = array();
         if($Db_Region){
             $datas['isok']='true';
             $datas['data']=$Db_Region;
         }else{
             $datas['isok']='false';
             $datas['data']='';
         }
         Buddha_Http_Output::makeJson($datas);
     }

    public function fail(){
        $Demandbj=new Demand();
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $id=(int)Buddha_Http_Input::getParameter('id');
        $Db_shop=$Demandbj->getSingleFiledValues(array('remarks'),"isdel=0 and user_id='{$uid}' and id='{$id}'");
        $failinfo=array();
        if($Db_shop){
            $failinfo['isok']=0;
            $failinfo['remarks']=$Db_shop['remarks'];
        }else{
            $failinfo['isok']=1;
            $failinfo['data']='错误';
            $failinfo['remarks']='';
        }
        Buddha_Http_Output::makeJson($failinfo);
    }


//上下架
    public  function shelves()
    {
        $GoodsObj=new Goods();
        $id=(int)Buddha_Http_Input::getParameter('id');
        $shelves=(int)Buddha_Http_Input::getParameter('shelves');
        $thumimg=array();
        if(!$id or !$shelves)
        {
            $thumimg['isok']='false';
            $thumimg['data']='参数错误';
        }

        if($shelves==0)
        {
            $title = '下架';
            $data['buddhastatus']=1;
            $buddhastatus = 1;
        }elseif($shelves==1){
            $title = '上架';
            $data['buddhastatus']=0;
            $buddhastatus = 0;
        }
        $db_number =  $GoodsObj->updateRecords($data,"id='{$id}'");
        if ($db_number)
        {
            $thumimg['isok']='true';
            $thumimg['data']=$title.'成功';
            $thumimg['buddhastatus']=$buddhastatus;
        }else{

            $thumimg['isok']='false';
            $thumimg['data']=$title.'失败';
        }
        $thumimg['id']=$id;
        Buddha_Http_Output::makeJson($thumimg);
    }








}