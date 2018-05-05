<?php
class Image extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }


    public  function deleteFIleOfPicture($id){
        $Db_Image =$this->fetch($id);
        $sourcepic = $Db_Image['sourcepic'];
        $small = $Db_Image['small'];
        $medium = $Db_Image['medium'];
        $large = $Db_Image['large'];

        @unlink(PATH_ROOT . $sourcepic);
        @unlink(PATH_ROOT . $small);
        @unlink(PATH_ROOT . $medium);
        @unlink(PATH_ROOT . $large);
    }
    /***
     *  DB_img :查询区域图片 轮播图区域
     *  $cat_id  图片大类ID
     *  $pic_user_id  代理商ＩＤ
     *  $type_id 为 店铺类别的ID
     **/
    public  function DB_img($p,$type_id){
        $ImageObj=new Image();
        $RegionObj= new Region();
        $UserObj = new User();
        $ImagecatalogObj=new Imagecatalog();
        $locdata = $RegionObj->getLocationDataFromCookie();//区域信息
        if(count($locdata)==0){
            Buddha_Http_Head::redirectofmobile('定位失败，请手动选择区县后再浏览！','index.php?a=index&c=index',2);
        }
        $lock_area = 0;//默认锁定不到区域
        //默认图片用户是平台
        $pic_user_id =0;

        if($locdata['region_id']=='' || !$locdata['region_id']){
            Buddha_Http_Head::redirect('你还没有选择地区呢，快去首页头部选择吧！','index.php?a=index&c=index');
        }

        $regionleve3num = $RegionObj->countRecords("isdel=0 and id={$locdata['region_id']} and level=3 ");//判断该编号有没有地区


        if($regionleve3num){
            //可以锁定到区域
            $region_level3_id = $locdata['region_id'];
            //有没有这个区域的代理    //判断该地区有没有代理商
            $agent_area_num =$UserObj->countRecords("isdel=0 and level3='{$region_level3_id}' and ( groupid=2 or  to_group_id like '%2%')");
            if($agent_area_num){
                //如果存在代理商 取出代理商id  查图片广告
                $Db_User = $UserObj->getSingleFiledValues(array('id'),"isdel=0  and level3='{$region_level3_id}' and ( groupid=2 or  to_group_id like '%2%')");// to_group_id   多角色标识
                $pic_user_id = $Db_User['id'];
            }
        }
        //查找图片类别
        $ShopcatObj= new Shopcat();
        $shopcat=$ShopcatObj->getSingleFiledValues('',"isdel=0 and id={$type_id} order by id DESC, view_order ASC");
        $identify='mobile_local_'.$p;
        $Db_Imagecatalog= $ImagecatalogObj->getSingleFiledValues('',"isdel=0 and identify = '{$identify}'and id= {$shopcat['ad_id']}");

        if(count($Db_Imagecatalog)){
            $cat_id = $Db_Imagecatalog['id'];//分类ID
            $the_identify = $Db_Imagecatalog['identify'];//分类标识名称
//            判断该标识有没有上传图片
            /*
             *  isdel=0  　　是否删除：0为启用
             *  buddhastatus=0     是否上架：0上架
             *  cat_id      图片类别ID
             *  user_id     区域代理ID　：　> 0  有代理商区域图片；    =0 为 平台上传图片
            */
            $order_where=' order by view_order asc limit 1';
            $imagewhere="isdel=0 and buddhastatus=1  and cat_id={$cat_id}";
            $imagefind=array('id','large', 'name', 'link', 'openmethod','shop_id','promote_start_date','promote_end_date');
            $imagenum = $ImageObj->countRecords($imagewhere."  and user_id={$pic_user_id}  ");//判断该地区有没有上传地区图片（条件：）、

//            var_dump($imagenum);

            if ($imagenum>0) {
                //判断该代理商有没有上传图片：有则显示代理商上传的图片
                $Db_Image = $ImageObj->getFiledValues($imagefind,$imagewhere. " and user_id={$pic_user_id} and shop_id>0 order by view_order limit 5");
                $newtime=time();
                foreach($Db_Image as  $k=>$v){//取出在   开始时间<=当前时间<=结束时间的图片  ；大于结束时间得就要删除
                    if( $v['promote_start_date']!='0' and  (int)$v['promote_start_date']<= $newtime  and  $newtime <= (int)$v['promote_end_date']){//如果 开始时间<=当前时间<=结束时间
                        $Image[$k] = $v;
                    }else if( (int)$v['promote_end_date'] < $newtime ){//如果 当前时间　>　结束时间  则下架
                        $data['buddhastatus'] = 0;
                        if($v['shop_id']>0){
                            $ImageObj->edit($data,$v['id']);
                        }
                    }
                }
                if($Image){//判断有没有 在 开始时间<=当前时间<=结束时间 的图片
                    $Db_Image = $Image;
                }else{
                    //判断该代理商有没有上传图片：没有有则显示平台上传的图片
                    $Db_Image = $ImageObj->getFiledValues($imagefind, $imagewhere." and user_id=0 {$order_where}" );//large大图   link开始   openmethod  跳转
                }
            } else {
                //判断该代理商有没有上传图片：没有有则显示平台上传的图片
                $Db_Image = $ImageObj->getFiledValues($imagefind, $imagewhere." and user_id=0  {$order_where}" );//large大图   link开始   openmethod  跳转
            }
        }


        $img=array();
        $img=array(
            'Db_Image'=>$Db_Image,
            'cat_id'=>$cat_id,
        );
        return $img;
    }
    public function getIndexBannerArr($cat_id,$api_number,$num,$services,$b_display=2){
        $host = Buddha::$buddha_array['host'];//https安全连接
        $Db_bannerimg = array();
        $RegionObj = new Region();
        if(!$RegionObj->isValidRegion($api_number)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444007, '地区编码不对（地区编码对应于腾讯位置adcode）');
        }
        $regnum = $RegionObj->getApiLocationByNumberArr($api_number);
        $max_num = 5;
        if($num>$max_num or $num<1){
            $num = $max_num;
        }
        $where = "" ;

        if($b_display ==2){
            $where .= $regnum['sql'];
            $whereTwo = $regnum['sql'];
        }else{
            $where .= $regnum['sql'];
            $whereTwo = $regnum['sql'];
        }
        $time = time();
        $fieldsarray = array('user_id','cat_id','shop_id','name','number','large','width','height');
        $Db_banner = $this->getFiledValues(array('promote_end_date'),"cat_id='{$cat_id}' {$where} AND promote_start_date<{$time}  AND promote_end_date>{$time}  AND buddhastatus=1 AND isdel=0 ORDER BY createtime");
        $Db_num = count($Db_banner);
        if($Db_num>=$num){
            $Db_bannerimg = $this->getFiledValues($fieldsarray,"cat_id='{$cat_id}' {$where} AND promote_start_date<{$time}  AND promote_end_date>{$time}  AND buddhastatus=1 AND isdel=0 ORDER BY createtime limit 0,{$num}");
        }elseif($Db_num<$num && $Db_num>0){
            $Db_bannerimg = $this->getFiledValues($fieldsarray,"cat_id='{$cat_id}' {$where} AND promote_start_date<{$time}  AND promote_end_date>{$time}  AND buddhastatus=1 AND isdel=0 ORDER BY createtime limit 0,{$Db_num}");
            for($i=$Db_num;$i<=$num;$i++){
                $num_two = $num-$Db_num;
                $Db_bannerimg[$i] = $this->getSingleFiledValues($fieldsarray,"cat_id='{$cat_id}'{$whereTwo} AND buddhastatus=1 AND isdel=0 ORDER BY createtime limit 0,{$num_two}");
            }
        }else{
            $Db_bannerimg = $this->getFiledValues($fieldsarray,"cat_id='{$cat_id}' {$whereTwo} AND promote_start_date<{$time}  AND promote_end_date>{$time}  AND buddhastatus=1 AND isdel=0 ORDER BY createtime limit 0,{$num}");
        }
        $Db_bannerimg = array_filter($Db_bannerimg);
        foreach($Db_bannerimg as $k => $v){
            if($v['large']){
                $Db_bannerimg[$k]['large']  = $host . $v['large'];
            }
            if($v['shop_id']){
                $Db_bannerimg[$k]['url']['services'] = $services;
                $Db_bannerimg[$k]['url']['shop_id'] = $v['shop_id'];
            }else{
                $Db_bannerimg[$k]['url']['services'] = "index.bannerAdd";
            }
        }
        return $Db_bannerimg;
    }
}