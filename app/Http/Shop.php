<?php
class Shop extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    /**
     * @param $shop_id
     * @param $isdel    代理商对店铺的停用启用状态：0启用；4代理商审核关闭；5商家选择了付费但为支付；8代理商将店铺停用
     * @param $user_id  代理商ID：当前登录者ID
     * @return mixed
     * 商家 启、停用 自己的 店铺
     */
    public function businessEnableDisabledShop($shop_id,$user_id)
    {

        $user_id = (int)$user_id;
        $shop_id = (int)$shop_id;

        $UserObj = new User();
        $ShopObj = new Shop();

        $Db_Shop = $ShopObj->getSingleFiledValues(array('state'),"id='{$shop_id}' and user_id='{$user_id}'") ;
        $state = $Db_Shop['state'];

        $isok['title'] = '';

        $is_All = 0;// 代理商是否具备：
        //  启用店铺的时候，也把商家停用的店铺及店铺下的也开启    0否；1是
        //  停用店铺的时候，也把商家停用的店铺及店铺下的也停用    0否；1是

        $is_ok = 0; // 0 失败; 1成功； 9 没有代理商用户权限，你还未申请代理商角色；8 该店铺不属于该代理商;7 商家自己停用，代理商没有权限停用！
                    // 4 代理商审核下架; 5 商家选择了支付但未支付 ; 6 商家自己停用，代理商没有权限开启！；


        //没有代理商用户权限，你还未申请代理商角色
        if(!$UserObj->isHasMerchantPrivilege($user_id) )
        {
            $isok['is_ok'] = 9;
            $isok['is_msg'] = '没有商家用户权限，你还未申请商家角色';
            return $isok;
        }



        $where = " is_sure=1 ";

        $shop_where = $where." AND id='{$shop_id}' AND state='{$state}' ";
        $app_where = $where." AND shop_id='{$shop_id}'";

        //查询店铺状态：is_sure审核状态；state上下架状态；isdel是否正常状态
        $Db_Shop = $ShopObj->getSingleFiledValues(array('is_sure','state','isdel') ,$where." AND id='{$shop_id}'");


        //$state == 8 原来状态为：停用(但是：按钮显示的名称为启用)
        if($state == 1)
        {
            /**将要执行的：启用**/
            $data_shop = array('state' => 0);

            $app_where .= ' AND buddhastatus=1';//更新店铺下对应信息的条件
            $data_app = array('buddhastatus'=>0);

            $title = '停用';
            $isok['title'] = $title;

            if($Db_Shop['isdel'] == 4)
            {
                $isok['is_ok'] = 4;
                $isok['is_msg'] = '代理商审核下架,请修改神审核后再'.$title;
                return $isok;

            }elseif($Db_Shop['isdel'] == 5)
            {
                $isok['is_ok'] = 5;
                $isok['is_msg'] = '该商家选择了支付但未支付,请支付后再'.$title;
                return $isok;
            }
            $buttonname = '停用';//启用成功后将要执行的按钮名称
        }elseif($state == 0) //$state == 0 原来状态为：启用(但是：按钮显示的名称为停用)
        {
            /**将要执行的：停用**/

            $data_shop = array('state' => 1);

            $app_where .= ' AND buddhastatus=0 ';//更新店铺下对应信息的条件
            $data_app = array('buddhastatus'=>1);

            $title = '停 用';
            $isok['title'] = $title;

            if($Db_Shop['isdel'] == 4)
            {
                $isok['is_ok'] = 4;
                $isok['is_msg'] = '代理商审核下架,不能'.$title;
                return $isok;

            }elseif($Db_Shop['isdel'] == 5)
            {
                $isok['is_ok'] = 5;
                $isok['is_msg'] = '该商家选择了支付但未支付,不能'.$title;
                return $isok;
            }

            $buttonname = '启用';//将要执行的按钮名词
        }

        $isok['buttonname'] = $buttonname;

        /*查询店铺 审核状态 和 启用状态*/

        if($Db_Shop['is_sure']==0)
        {
            $isok['is_ok'] = 0;
            $isok['is_msg'] = '店铺还未审核，不能'.$title.'店铺！';
            return $isok;

        }else if($Db_Shop['is_sure']==4)
        {
            $isok['is_ok'] = 0;
            $isok['is_msg'] = '店铺未通过审核，不能'.$title.'店铺！！';
            return $isok;

        }


        if(!$ShopObj->countRecords($shop_where))
        {
            $isok['is_ok'] = 0;
            $isok['is_msg'] = "{$title} 失败,该店铺不存在或店铺已经{$title}请不要重复{$title}！";
            return $isok;
        }



        $Db_Shop_Num =  $ShopObj->updateRecords($data_shop,$shop_where );

        if(!$Db_Shop_Num)
        {
            $isok['is_ok'] = 0;
            $isok['is_msg'] = '商家'.$title.'店铺失败!';
            return $isok;
        }


        $ShopObj->batchupdateShopBelong($app_where,$data_app);//批量更新店铺下的信息

        if($Db_Shop_Num)
        {
            $isok['is_ok'] = 1;
            $isok['is_msg'] = '商家'.$title.'店铺成功!';
            return $isok;
        }

    }


    /**
     * @param $shop_id
     * @param $isdel    代理商对店铺的停用启用状态：0启用；4代理商审核关闭；5商家选择了付费但为支付；8代理商将店铺停用
     * @param $user_id  代理商ID：当前登录者ID
     * @return mixed
     * 代理商 启、停用 所管理商家的 店铺
     */
    public function agentsEnableDisabledShop($shop_id,$isdel,$user_id)
    {
        $user_id = (int)$user_id;
        $shop_id = (int)$shop_id;
        $isdel = (int)$isdel;

        $UserObj = new User();
        $CommonObj = new Common();
        $ShopObj = new Shop();

        $isok['title'] = '';

        $is_All = 0;// 代理商是否具备：
                    //  启用店铺的时候，也把商家停用的店铺及店铺下的也开启    0否；1是
                    //  停用店铺的时候，也把商家停用的店铺及店铺下的也停用    0否；1是

        $is_ok = 0; // 0 失败; 1成功； 9 没有代理商用户权限，你还未申请代理商角色；8 该店铺不属于该代理商;7 商家自己停用，代理商没有权限停用！
                    // 4 代理商审核下架;5 商家选择了支付但未支付 ; 6 商家自己停用，代理商没有权限开启！；


        //没有代理商用户权限，你还未申请代理商角色
        if(!$UserObj->isHasAgentPrivilege($user_id) )
        {
            $isok['is_ok'] = 9;
            $isok['is_msg'] = '没有代理商用户权限，你还未申请代理商角色';
            return $isok;
        }

        //获取该代理商的代理区域
        $Db_User = $UserObj->getSingleFiledValues(array('level3'),"id='{$user_id}'");

        //判断该店铺是否属于该代理商
        if(!$CommonObj->isOwnerBelongToAgentByLeve3('shop',$shop_id,$Db_User['level3']))
        {
            $isok['is_ok'] = 8;
            $isok['is_msg'] = '该店铺不属于该代理商';
            return $isok;
        }

        if($isdel == 4)
        {
            $isok['is_ok'] = 4;
            $isok['is_msg'] = '代理商审核下架';
            return $isok;

        }elseif($isdel == 5)
        {
            $isok['is_ok'] = 5;
            $isok['is_msg'] = '该商家选择了支付但未支付';
            return $isok;
        }


        $where = " is_sure=1 ";

        $shop_where = $where." AND id='{$shop_id}' AND isdel='{$isdel}' ";
        $app_where = $where." AND shop_id='{$shop_id}'";

        //查询店铺状态：is_sure审核状态；state上下架状态；isdel是否正常状态
        $Db_Shop = $ShopObj->getSingleFiledValues(array('is_sure','state','isdel') ,$where." AND id='{$shop_id}'");


        //$isdel == 8 原来状态为：停用
        if($isdel == 8)
        {
            /**将要执行的：启用**/
            $data_shop = array('isdel' => 0);

            $app_where .= ' AND isdel=8';//更新店铺下对应信息的条件
            $data_app = array('isdel'=>0);

            $title = '启 用';


            if($is_All)
            {
                /**将要执行的：启用**/
                array_push($data_shop,array('state'=>0));
                array_push($data_app,array('buddhastatus'=>0));
                $app_where .= ' AND buddhastatus=1';//更新店铺下对应信息的条件

            }else{

                if($Db_Shop['state']==1)
                {
                    $isok['is_ok'] = 6;
                    $isok['is_msg'] = "商家自己停用，代理商没有权限{$title}！";
                    return $isok;
                }
            }

            $buttonname = '停用';//将要执行的按钮名称

        }elseif($isdel == 0) //$isdel == 0 原来状态为：启用
        {
            /**将要执行的：停用**/

            $data_shop = array('isdel' => 8);

            $app_where .= ' AND isdel=0 ';//更新店铺下对应信息的条件
            $data_app = array('isdel'=>8);

            $title = '停 用';
            $isok['title'] = $title;

            if($is_All)
            {
                /**将要执行的：停用**/
                array_push($data_shop,array('state'=>1));
                array_push($data_app,array('buddhastatus'=>1));
                $app_where .= '  AND buddhastatus=1';//更新店铺下对应信息的条件

            }else{
                if($Db_Shop['state']==1)
                {
                    $isok['is_ok'] = 7;
                    $isok['is_msg'] = "商家自己停用，代理商没有权限{$title}！";
                    $isok['title'] = $title;
                    return $isok;
                }
            }
            $buttonname = '启用';//将要执行的按钮名词
        }

        $isok['title'] = $buttonname;

        /*查询店铺 审核状态 和 启用状态*/

        if($Db_Shop['is_sure']==0)
        {
            $isok['is_ok'] = 0;
            $isok['is_msg'] = '店铺还未审核，不能'.$title.'店铺！';
            return $isok;

        }else if($Db_Shop['is_sure']==4)
        {
            $isok['is_ok'] = 0;
            $isok['is_msg'] = '店铺未通过审核，不能'.$title.'店铺！！';
            return $isok;

        }


        if(!$ShopObj->countRecords($shop_where))
        {
            $isok['is_ok'] = 0;
            $isok['is_msg'] = "{$title} 失败,该店铺不存在或店铺已经{$title}请不要重复{$title}！";
            return $isok;
        }



        $Db_Shop_Num =  $ShopObj->updateRecords($data_shop,$shop_where );

        if(!$Db_Shop_Num)
        {
            $isok['is_ok'] = 0;
            $isok['is_msg'] = '代理商'.$title.'店铺失败!';
            return $isok;
        }


        $ShopObj->batchupdateShopBelong($app_where,$data_app);//批量更新店铺下的信息

        if($Db_Shop_Num)
        {
            $isok['is_ok'] = 1;
            $isok['is_msg'] = '代理商'.$title.'店铺成功!';
            return $isok;
        }

    }




    /**
     * @param $is_sure 审核状态：代表字段
     * @return string
     * 代理商店铺 启 用、停 用状态：字符串
     */
    public function agentsenabledisabledstr($isdel)
    {
        $state = '';
        if($isdel==8)
        {
            $state='启 用';
        }else if($isdel==0)
        {
            $state='停 用';
        }else if($isdel==1 or $isdel==4 or $isdel==5)
        {
            $state='店铺异常';
        }

        return $state;
    }




    /**
     * @param $appwhere  更新条件
     * @param $data       更新数据
     * @return mixed
     * 批量更新店铺下的信息
     */

    public function batchupdateShopBelong($appwhere,$data)
    {

        $ShopObj = new Shop();

        $Applicationarray = $ShopObj->getShopstopRelatedTablename();//店铺下具有功能的表名称

        $return_id = array();

        if(Buddha_Atom_Array::isValidArray($Applicationarray))
        {

            foreach($Applicationarray as $k=>$v)
            {

                /*判断该店铺下是否存在供应、需求、租赁、招聘、单页信息.....*/
                if($this->db->countRecords ( $v, $appwhere))
                {
                    $idarr = array();

                    /*获取店铺下存在供应、需求、租赁、招聘、单页信息的Id数组......*/
                    $idarr =  $this->db->getFiledValues(array('id'), $v, $appwhere);
                    $idstr = '';

                    /*组装店铺下存在供应、需求、租赁、招聘、单页信息的ID...... 数组字符串*/
                    foreach($idarr as $kk=>$vv)
                    {
                        $idstr.=$vv['id'].',';
                    }

                    $idstr = rtrim($idstr,',');

                    $return_id[] = $this->db->updateRecords($data, $v, $appwhere." AND id in({$idstr})");
                }
            }
        }

        return $return_id;
    }


    /**
     * @param $SHOP_ID
     * @return mixed
     * 店铺详情的滑动
     */
    public function shopnav($shop_id)
    {
        $host = Buddha::$buddha_array['host'];
        $SupplyObj = new Supply();
        $shopnav = array(


            0=>array( 'select'=>0,'name'=>'简介','pageflag'=>'abstract','type'=>5,
                'Services'=>'shop.shopabstrac','param'=>array(),
                'showstyle'=>'html',
                'icon_promote'=>$host.'apishop/menuplus/jianjie.png','list'=>array(),'is_show'=>1  ),


            1=>array( 'select'=>0,'name'=>'名片','pageflag'=>'card','type'=>6,
                'Services'=>'shop.businesscard','param'=>array(),
                'showstyle'=>'html',
                'icon_promote'=>$host.'apishop/menuplus/mingpian.png','list'=>array(),'is_show'=>1  ),

            2=>array( 'select'=>0,'name'=>'传单','pageflag'=>'singleinformation','type'=>2,
                'Services'=>'singleinformation.more','param'=>array(),
                'showstyle'=>'piclist',
                'icon_promote'=>$host.'apishop/menuplus/danyexin.png','list'=>array(),'is_show'=>1  ),

            3=>array( 'select'=>0,'name'=>'供应','pageflag'=>'supply','type'=>3,
                'Services'=>'multilist.supplymore','param'=>array(),
                'showstyle'=>'piclist',
                'icon_promote'=>$host.'apishop/menuplus/gongying.png','list'=>array(),'is_show'=>1  ),

            4=>array( 'select'=>0,'name'=>'促销','pageflag'=>'promote','type'=>1,
                'Services'=>'multilist.promotionsarr','param'=>array(),
                'showstyle'=>'piclist',
                'icon_promote'=>$host.'apishop/menuplus/cuxiao.png','list'=>array(),'is_show'=>1  ),
            5=>array( 'select'=>0,'name'=>'需求','pageflag'=>'demand','type'=>7,
                'Services'=>'multilist.demandmore','param'=>array(),
                'showstyle'=>'piclist',
                'icon_promote'=>$host.'apishop/menuplus/xuqiu.png','list'=>array(),'is_show'=>1  ),

            6=>array( 'select'=>0,'name'=>'招聘','pageflag'=>'recruit','type'=>8,
                'Services'=>'multilist.recruitarr','param'=>array(),
                'showstyle'=>'nopiclist',
                'icon_promote'=>$host.'apishop/menuplus/zhaopin.png','list'=>array(),'is_show'=>1 ),

            7=>array( 'select'=>0,'name'=>'租赁','pageflag'=>'lease','type'=>9,
                'Services'=>'multilist.leasearr','param'=>array(),
                'showstyle'=>'nopiclist',
                'icon_promote'=>$host.'apishop/menuplus/zulin.png','list'=>array(),'is_show'=>1 ),

            8=>array( 'select'=>0,'name'=>'一分营销','pageflag'=>'heartpro','type'=>10,
                'Services'=>'multilist.heartproarr','param'=>array(),
                'showstyle'=>'nopiclist',
                'icon_promote'=>$host.'apishop/menuplus/heartpro.png','list'=>array(),'is_show'=>1 ),
            9=>array( 'select'=>0,'name'=>'一码营销','pageflag'=>'codesales','type'=>11,
                'Services'=>'multilist.codesalesarr','param'=>array(),
                'showstyle'=>'nopiclist',
                'icon_promote'=>$host.'apishop/menuplus/codesales.png','list'=>array(),'is_show'=>1 ),


            10=>array( 'select'=>0,'name'=>'活动','pageflag'=>'activity','type'=>4,
                'Services'=>'activity.more','param'=>array(),
                'showstyle'=>'piclist',
                'icon_promote'=>$host.'apishop/menuplus/huodong.png','list'=>array(),'is_show'=>1  ),

//            11=>array( 'select'=>0,'name'=>'个体活动','pageflag'=>'activity_personal','type'=>12,
//                'Services'=>'activity.more','param'=>array('api_activitytype'=>1),
//                'showstyle'=>'piclist',
//                'icon_promote'=>$host.'apishop/menuplus/huodong_geti.png','list'=>array()  ,'is_show'=>1 ),
//
//            12=>array( 'select'=>0,'name'=>'联合活动','pageflag'=>'activity_unity','type'=>13,
//                'Services'=>'activity.more','param'=>array('api_activitytype'=>2),
//                'showstyle'=>'piclist',
//                'icon_promote'=>$host.'apishop/menuplus/huodong_lianhe.png','list'=>array() ,'is_show'=>1  ),
//
//            13=>array( 'select'=>0,'name'=>'投票活动','pageflag'=>'activity_vote','type'=>14,
//                'Services'=>'activity.more','param'=>array('api_activitytype'=>3),
//                'showstyle'=>'piclist',
//                'icon_promote'=>$host.'apishop/menuplus/huodong_toupiao.png','list'=>array() ,'is_show'=>1  ),
//
//
//
        );

        $Supply_where = "shop_id='{$shop_id}' AND is_sure=1 AND buddhastatus=0 AND isdel=0";
        if($SupplyObj->countRecords($Supply_where))
        {
            $selectstr = 'supply';
        }else{
            $selectstr = 'card';
        }
        $aa= array();
        foreach ($shopnav as $k=>$v)
        {
            if($v['pageflag'] == $selectstr)
            {
                $shopnav[$k]['select'] = 1;
                $aa[0]=$shopnav[$k];
                unset($shopnav[$k]);
            }else{
                $shopnav[$k]['select']=0;
            }
        }

        foreach ($shopnav as $k=>$v){
            array_push($aa,$v);
        }
        return $aa;
    }


    /**
    * 判断此店铺是否存在
    * @param $shop_id
    * @return int
    * @author wph 2017-12-23
    */
    public function isExistShop($shop_id){

        $num = $this->countRecords("id='{$shop_id}' ");
        if($num){
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * @param string $now_user_id 当前用户ID
     * @param $shop_id
     * @param $order_id
     * @return int
     * 当用户  1、没有认证  2、并且没有支付E网通费用   3、并且不在免费期间时
     * 查看用户是有0.2元付费记录
     */
    public function isPaidRecords($now_user_id,$shop_id,$order_id)
    {

        $OrderObj = new Order();

        $nowtime = Buddha::$buddha_array['buddha_timestamp'];

        $start = $nowtime-15*60; //付费查看电话过期时间(15分钟有效)

        $now_user_id = (int)$now_user_id;

        $where = "good_id='{$shop_id}' and pay_status=1 and createtime>='{$start}' AND order_type='info.see'";

        $see = 0;//是否查找到订单：0否；1是

        if($now_user_id)
        {
            $see = $OrderObj->countRecords($where." AND user_id='{$now_user_id}'");
        }else{
            $see = $OrderObj->countRecords($where." AND id='{$order_id}' ");
        }

        return $see;
    }

    /**
     * @param $shop_id          店铺ID
     * @param $shop_user_id     店铺拥有者ID
     * @return mixed
     * 是否在免费 显示电话 期间
     */
    public function isFreePeriodShopByshopid($shop_id ,$shop_user_id)
    {
        $ShopObj = new Shop();
        $shop_id = (int)$shop_id;
        $shop_user_id = (int)$shop_user_id;
        $where = "user_id='{$shop_user_id}'";
        /**↓↓↓↓↓↓↓↓↓↓↓ 判断该商家是否在 免费时间 ↓↓↓↓↓↓↓↓↓↓↓**/
        $where = $where." AND id='{$shop_id}'";
        $Db_Shop = $ShopObj->getSingleFiledValues(array('createtime'),$where);

        $endtime = $Db_Shop['createtime'] + 30*86400;//免费30天的结束时间
        $nowtime = Buddha::$buddha_array['buddha_timestamp'];

         $isFreePeriod = 0;//是否在免费期间 0否；1是
        /**免费时间：入驻时间 < 当前时间 < 30天结束时间  **/

        if($Db_Shop['createtime'] < $nowtime AND $nowtime < $endtime)
        {
            $isFreePeriod = 1;
        }else{
            $isFreePeriod = 0;
        }
        /**↑↑↑↑↑↑↑↑↑↑ 判断该商家是否在 免费时间  ↑↑↑↑↑↑↑↑↑↑**/

        return $isFreePeriod;
    }


    /**
     * @param $shop_id       店铺ID
     * @param $shop_user_id  店铺拥有者ID
     * @return int
     * 判断店铺是否认证了
     */
    public function isCertification($shop_id, $shop_user_id)
    {
        $ShopObj = new Shop();
        $shop_id = (int)$shop_id;

        $isCertification = 0;//是否显示电话号码 0否；1是

        $where = "user_id='{$shop_user_id}'";
        /**↓↓↓↓↓↓↓↓↓↓↓ 判断该商家是否是否认证商家 ↓↓↓↓↓↓↓↓↓↓↓**/

            //统计该店铺是否认证了（兼容1.0）
        $shop_where = $where." AND id='{$shop_id}' AND is_verify=1";
        $DB_Shop_Num = $ShopObj->countRecords($shop_where);

        if($DB_Shop_Num)
        {
            $isCertification = 1;
        }
        return $isCertification;
    }



    /**
     * @param $shop_id          店铺ID
     * @param $shop_user_id     店铺拥有者ID
     * @param $now_user_id      当前查看着ID
     * @param $order_id         订单ID
     * @return int
     * 如果返回1表示可以显示 如果返回0 表示不能显示电话号码
     */
    public function isCouldSeeCellphone($shop_id,$shop_user_id,$now_user_id,$order_id=0)
    {
        $ShopObj = new Shop();
        $UserfeeObj = new Userfee();
        $shop_id = (int)$shop_id;

        $isShowPhoe = 0;//是否显示电话号码 0否；1是

        //判断该商家是否已经支付了 e网通(360或990)
        $Db_Userfee_num = $UserfeeObj->isPayByShopid($shop_id,$shop_user_id );

        if($Db_Userfee_num == 1){

            $isShowPhoe =1;
        }elseif($Db_Userfee_num == 0){

            //判断该商家是否是否认证商家
            $Db_Shop_Num = $ShopObj->isCertification($shop_id ,$shop_user_id);

            if($Db_Shop_Num ==1){
                $isShowPhoe =1;
            }elseif($Db_Shop_Num ==0){

                //判断该商家是否在 免费时间
                $Db_Shop_Num = $ShopObj->isFreePeriodShopByshopid($shop_id ,$shop_user_id);

                if($Db_Shop_Num == 1){
                    $isShowPhoe =1;
                }else{

                    //查看用户是有0.2元付费记录
                    if($ShopObj->isPaidRecords($now_user_id,$shop_id,$order_id)){

                        $isShowPhoe =1;
                    }
                }
            }
        }

        return $isShowPhoe;
    }

    /**
     * @param $tableid          来源于哪一张表的 ID
     * @param $tablename        来源于哪一张表
     * @param $order_id         订单ID
     * @param $shop_user_id     店铺拥有者ID
     * @param $now_user_id      当前登录者用户ID
     *  @param $shop_id         店铺ID
     * @return string
     * 最终展示的号码为
     */

    public function showCellphone($tableid,$tablename,$shop_user_id,$now_user_id,$shop_id,$order_id=0)
    {
        $UserObj = new User();
        $phone = '查看';

        $Db_table = array();
        if ($tablename=='recruit')
        {
            $RecruitObj = new Recruit();
            $filed = array('tel as phone');
            $Db_table = $Db_Recruit = $RecruitObj->getSingleFiledValues($filed,"id='{$tableid}'");

        }elseif ($tablename=='shop') {

            $ShopObj = new Shop();
            $UserObj = new User();

            $shop_where = "id='{$tableid}'";

            //这里使用的是用户中心的默认展示电话的规则，因为个人中心和店铺使用的是同一个电话
            if($UserObj->countRecords("id='{$shop_user_id}' AND isdefaultphone=1"))// 默认展示手机
            {
                $filed = array('mobile as phone');//默认展示手机
            }else{

                $filed = array('tel as phone');
            }

            $Db_table = $Db_Shop = $ShopObj->getSingleFiledValues($filed,$shop_where);
        }


        //是否显示电话号码
        if($this->isCouldSeeCellphone($shop_id,$shop_user_id,$now_user_id,$order_id))
        {

            if(Buddha_Atom_Array::isValidArray($Db_table))
            {
                $phone = $Db_table['phone'];
            }else{
                $Db_User = $UserObj->getUserDefaultphoneByUserid($shop_user_id);
                if(Buddha_Atom_String::isValidString($Db_User))
                {
                    $phone = $Db_User;
                }
            }
        }

        return $phone;
    }

    /**
     * @param $shopid
     * @param $logo
     * @param $name
     * @param string $event
     * @param string $eventpage
     * 店铺一码营销
     */
    public function createQrcodeForCodeSales($shopid,$logo,$name,$event='shop',$eventpage='info')
    {
        $tt = 'codesales';
        $CommonObj = new Common();
        $name = $CommonObj->intercept_strlen($name,8);

        if(!Buddha_Atom_String::isValidString($logo)){
            $logo = 'style/images/index_sq1.jpg';
        }
        $savefile= PATH_ROOT."storage/{$tt}/qrcode_{$tt}_{$shopid}.jpg";
        @mkdir(PATH_ROOT."storage/{$tt}");
        @chmod(PATH_ROOT."storage/{$tt}",0755);
        if(!file_exists($savefile)){
            //水印透明度
            $alpha = 100;
            //合并水印图片
            $dst_im = imagecreatefromstring(file_get_contents(PATH_ROOT . "/style/images/qrcode_{$tt}.jpg"));

            $qrcodeimg = $CommonObj->getQRCode($event,$eventpage,$shopid,$logo);
            $src_im = imagecreatefromstring(file_get_contents(PATH_ROOT . $qrcodeimg));
            $chuli_src_im = imagecreatetruecolor(550, 550);
            imagecopyresampled($chuli_src_im, $src_im, 0, 0, 0, 0, 550, 550, imagesx($src_im),imagesy($src_im));

            /**↓↓↓↓↓↓↓↓↓↓↓ 添加店招 ↓↓↓↓↓↓↓↓↓↓↓**/
//            $chuli_src_im_three = imagecreatetruecolor(180, 180);
//            $threelogo = imagecreatefromstring(file_get_contents(PATH_ROOT . $logo));
//            imagecopyresampled($chuli_src_im_three, $threelogo, 0, 0, 0, 0, 180, 180, imagesx($threelogo),imagesy($threelogo));
//            imagecopymerge($dst_im,$chuli_src_im_three,180,60,0,0,180,180,100);
            /**↑↑↑↑↑↑↑↑↑↑ 添加店招 ↑↑↑↑↑↑↑↑↑↑**/

            imagecopymerge($dst_im,$chuli_src_im,imagesx($dst_im)-810,imagesy($dst_im)-1057,0,0,550,550,$alpha);

            $ttfroot = PATH_ROOT . 'style/font/simsun.ttc';
            //$font=imagecolorallocate($dst_im,41,163,238);
            $font=imagecolorallocate($dst_im,0,0,0);
            $str = '"'.$name.'"';
            imagettftext($dst_im, 40, 0, 265, 1471, $font, $ttfroot, $str);//使用自定义的字体
            imagejpeg($dst_im, $savefile);
            imagedestroy($dst_im);
            imagedestroy($chuli_src_im);
            imagedestroy($src_im);
            $haibaourl = PATH_ROOT."storage/{$tt}/qrcode_{$tt}_{$shopid}.jpg";

            $data['codeimg']="storage/{$tt}/qrcode_{$tt}_{$shopid}.jpg";
            $ShopObj = new Shop();
            $ShopObj->updateRecords($data,"id='{$shopid}'");
        }
    }

    /**
     * @return string
     * 最近开业时间条件
     */
    public function openedrecently()
    {

        $newtime = Buddha::$buddha_array['buddha_timestamp'];
        $api_recentlystart = $newtime -  ( 15 * 24 * 60 * 60);
        $api_recentlyend   = $newtime +  ( 15 * 24 * 60 * 60);
        $where= " AND ({$api_recentlystart} <= opentime AND opentime <= {$api_recentlyend}) ";


        return $where;
    }


    /**
     * @param $shop_id
     * @param $filed
     * @return int|mixed|string
     * 通过店铺ID获取店铺里面的信息
     */
    public function getshopfollowingbyShopid($shop_id,$filed)
    {

        $shop_id=(int)$shop_id;
        if(!Buddha_Atom_Array::isValidArray($filed))
        {
           return '';
        }
        if($shop_id){
            $Db_shop=$this->getSingleFiledValues($filed," id='{$shop_id}' AND isdel=0");
            if(Buddha_Atom_Array::isValidArray($Db_shop))
            {
                return $Db_shop;
            }else{
                return '';
            }
        }else{
            return '';
        }

    }


    /**
     * @param $shop_id
     * @param $user_id
     * @return int|mixed|string
     */
    public function getShopareaByShopid($shop_id,$user_id)
    {
        $shop_id=(int)$shop_id;
        $user_id=(int)$user_id;
        if($shop_id AND $user_id){
            $Db_shop=$this->getSingleFiledValues(array('level0','level1','level2','level3')," id='{$shop_id}' AND user_id='$user_id'");
            if(Buddha_Atom_Array::isValidArray($Db_shop)){
                return $Db_shop;
            }else{
                return '';
            }
        }else{
            return '';
        }


    }



    /**
     * @param $shop_id
     * @param $user_id
     * @return string
     *  通过店铺ID和yonghu ID 获取店铺名称
     */
    public function getShopNameByShopid($shop_id,$user_id)
    {
        $shop_id=(int)$shop_id;
        $user_id=(int)$user_id;
        if($shop_id AND $user_id){
            $Db_shop=$this->getSingleFiledValues(array('name')," id='{$shop_id}' AND user_id='$user_id'");
            if(Buddha_Atom_Array::isValidArray($Db_shop)){
                return $Db_shop['name'];
            }else{
                return '';
            }
        }else{
            return '';
        }


    }


    /**
     * 判断 用户名下 是否有店铺
     **/
    public function IsUserHasShop($Userid)
    {
        $user_id = (int)$Userid;
        if ($user_id) {
            $where = " user_id='{$user_id}' ";
            $Db_Shop_num = $this->countRecords($where);
            if($Db_Shop_num){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }


    /**
     * 判断用户是否正常( 正常:is_sure=1通过审核 、 state=0启用 、 isdel=0正常 )店铺的个数
     **/
    public function IsUserHasNormalShop($Userid)
    {
        $user_id = (int)$Userid;
        if ($user_id) {
            $where = " is_sure=1 AND state=0 AND isdel=0 AND user_id='{$user_id}'";
            $Db_Shop_num = $this->countRecords($where);
            if($Db_Shop_num){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }
    /**
     * @return object
     * 判断店铺是否有效(正常:is_sure=1通过审核、state=0启用、isdel=0正常)
     */
    public function isShopByShopid($Shopid,$uid=0)
    {
        $Shop_id=(int)$Shopid;

        $user_uid=(int)$uid;

        if($Shop_id && $user_uid){

            $where= " id='$Shop_id' AND is_sure=1 AND state=0 AND isdel=0 ";

            if($uid>0){

                $where.= " AND user_id='$user_uid'";

            }

            $Db_Shop_num = $this->countRecords($where);

            if($Db_Shop_num){
                return 1;
            }else{
                return 0;
            }

        }else{
            return 0;
        }
    }


    /**
     * @param $data
     * @return array
     *   得到代理商或商家停用店铺后 要 停用相关功能的表名称
     */
    public function getShopstopRelatedTablename()
    {
        $ShopstopRelatedTablenameArr = array(
            0=>'activity',
            1=>'demand',
            2=>'lease',
            3=>'recruit',
            4=>'supply',
            5=>'singleinformation',
            6=>'heartpro',
        );

        return $ShopstopRelatedTablenameArr;

    }






    /**
     * @param $Userid
     * @return array
     * @author csh
     * 根据用户ID 查询该用户下正常的店铺
     */
    public function getShoparrByUserid($Userid,$shop_id=0)
    {
        $shop_id=(int)$shop_id;
        $user_id = (int)$Userid;
        if ($user_id)
        {
            $where = $this->shop_public_where();
            $where .= " and user_id='{$user_id}' ";
            $Db_Shop = $this->getFiledValues(array('id as shop_id', 'name as shop_name'), $where . " ORDER BY id DESC ");
            $returnarray = array();
            if (Buddha_Atom_Array::isValidArray($Db_Shop)) {
                foreach($Db_Shop as $k=>$v){
                    if(!Buddha_Atom_String::isValidString($shop_id)){
                        if($k==0){
                            $Db_Shop[$k]['select']=1;
                        }else{
                            $Db_Shop[$k]['select']=0;
                        }
                    }else{

                        if($shop_id>0 AND $v['shop_id']==$shop_id){
                            $Db_Shop[$k]['select']=1;
                        }else{
                            $Db_Shop[$k]['select']=0;
                        }
                    }


                }
                $returnarray = $Db_Shop;
            }
        }
        return $returnarray;
    }


    /**
     * @param $shop_is
     * @return array
     * @ author csh
     * 根据店铺ID获取店铺的相册数组信息
     *
     */

    public function getApiShopAlbumArrByshopid($shop_id,$b_display,$table_name)
    {
        $host=Buddha::$buddha_array['host'];
        $returnarr=array();
        $AlbumObj=new Album();
        $Services = 'album.deleteimage';
        $param=array();
;

        $Albumwhere=" goods_id='{$shop_id}' AND table_name='{$table_name}'";

        $num=$AlbumObj->countRecords($Albumwhere);
        if($num){

            $fileds=' id as album_id ';
            /*手机*/
            if($b_display==2){
                $fileds=' ,goods_thumb as img ';

             /*PC*/
            }else if($b_display==1){
                $fileds=' ,goods_img as img ';
            }
            $sql=" SELECT {$fileds} FROM {$this->prefix}album WHERE {$Albumwhere} ORDER BY id DESC";
            $Db_Album=$this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            foreach ($Db_Album as $k=>$v){
                $returnarr[$k]['album_id']=$v['id'];
                $returnarr[$k]['img']=$host.$v['img'];
                $returnarr[$k]['delete']['Services']=$Services;
                $returnarr[$k]['delete']['param']['album_id']=$v['id'];
                $returnarr[$k]['delete']['param']['table_name']='album';
            }

        }else{

            $fileds=' id as album_id ';
            /*手机*/
            if($b_display==2){
                $fileds.=' ,small as img ';

                /*PC*/
            }else if($b_display==1){
                $fileds.=' ,medium as img ';
            }

            $where=" id='{$shop_id}' AND (isdel=0 OR isdel=5) ";
            $sql=" SELECT {$fileds} FROM {$this->prefix}{$table_name} WHERE {$where} ORDER BY id DESC ";
            $Db_Table=$this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            if(Buddha_Atom_Array::isValidArray($Db_Table)){
                foreach ($Db_Table as $k=>$v){
                    if(Buddha_Atom_String::isValidString($v['img'])){
                        $returnarr[$k]['img']=$host.$v['img'];
                    }else{
                        $returnarr[$k]['img']='';
                    }
                    $returnarr[$k]['album_id']=$v['album_id'];
                    $returnarr[$k]['delete']['Services']=$Services;
                    $returnarr[$k]['delete']['param']['album_id']=$v['album_id'];
                    $returnarr[$k]['delete']['param']['table_name']=$table_name;
                }
            }
        }

        return $returnarr;
    }


    /**
     * @param $Shop_id
     * @return mixed
     * @author csh
     * 判断shop_id 是否有效
     * $user_id >0 表示是个人中心
     */
    public function getShopidIsVerify($Shop_id,$user_id=0)
    {

        if($user_id>0){
            $DB_Shop_num = $this->countRecords("id='{$Shop_id}' AND user_id='$user_id' AND isdel!=1");
        }else{
            $DB_Shop_num= $this->countRecords("id='{$Shop_id}' AND isdel=0");
        }

        return $DB_Shop_num;
    }


    /*获得店铺性质中选中的列表
    * @param int $natiure_id
     * @author  陈绍海
   */
    public function getApiNatiureArr($natiure_id=0){

        if(!($natiure_id>=1 and $natiure_id<=5)){
            $natiure_id = 0;
        }

        $natiure_arr[] = array('name'=>'选择店铺性质','namevalue'=>0,'select'=>0);
        $natiure_arr[] = array('name'=>'沿街商铺','namevalue'=>1,'select'=>0);
        $natiure_arr[] = array('name'=>'市场','namevalue'=>2,'select'=>0);
        $natiure_arr[] = array('name'=>'商场','namevalue'=>3,'select'=>0);
        $natiure_arr[] = array('name'=>'写字楼','namevalue'=>4,'select'=>0);
        $natiure_arr[] = array('name'=>'生产制造','namevalue'=>5,'select'=>0);


        foreach($natiure_arr as $k=>$v){

            $temp_namevalue= $v['namevalue'];
            if($temp_namevalue==$natiure_id){
                $natiure_arr[$k]['select'] =1 ;
            }else{
                $natiure_arr[$k]['select'] =0 ;
            }
        }
        return $natiure_arr;
    }


    public function getShopOfSureToUserTotalInt($shop_id=0,$user_id){

        if($shop_id==0){
            $num = $this->countRecords(" user_id='{$user_id}' AND is_sure=1 ");
            if($num>0){
                return 1;
            }else{
                return 0;
            }
        }

        $num = $this->countRecords(" id='{$shop_id}' AND user_id='{$user_id}' AND is_sure=1 ");
        if($num>0){
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * 判断登录用户名下是否此店铺经过审核
     * @param $shop_id
     * @param $user_id
     * @return int
     */
   public function isShopBelongToUserHasSure($shop_id,$user_id){
       $num = $this->getShopOfSureToUserTotalInt($shop_id,$user_id);
       if($num>0){
           return 1;
       }else{
           return 0;
       }
   }

    /**
     * 店铺是否属于此用户(也就是店铺是否属于当前登录的会员)
     * @param $shop_id
     * @param $user_id
     * @return int
     * @author wph 2017-09-20
     */
   public function isShopBelongToUser($shop_id,$user_id){
       $num = $this->countRecords(" id='{$shop_id}' AND user_id='{$user_id}' ");
       if($num>0){
           return 1;
       }else{
           return 0;
       }



   }

    /**
     *判断店铺是否认证 0“未深证 1:认证
     * @param $shop_id
     * @return int
     */
    public function isShopVerify($shop_id){
        $num = $this->countRecords(" id = '{$shop_id}' ");
        if($num==0){
            return 0;
        }else{

            $Db_Shop = $this->getSingleFiledValues(array('mobile','is_verify','createtime')," id ='{$shop_id}' ");
            if($Db_Shop['is_verify']==1){
                return 1;
            }else{
                return 0;
            }


        }

    }

    /**
     * 判断店铺是不是在免费查看的日期内
     * @param $shop_createtime
     * @return int
     */
    public function isFreeForShowCellphoneDay($shop_id){
        $Db_Shop = $this->getSingleFiledValues(array('createtime')," id = '{$shop_id}' ");
        $shop_createtime = Buddha_Atom_Array::getApiKeyOfValueByArrayString($Db_Shop,'createtime');

        if(strlen($shop_createtime)==0){
            return 0;
        }else{
            $shop_createtime = strtoupper($Db_Shop['createtime']);
        }


        $freetime = 7*24*3600;
        if(strlen($shop_createtime)<10){
            return 0;
        }

        $nowtime = Buddha::$buddha_array['buddha_timestamp'];
        $endtime = $shop_createtime+$freetime;
        if($nowtime>$endtime){
            return 0;
        }else{

            return 1;
        }

    }

    /**
     * 判断店铺付款否
     * @param $shop_id
     * @param $user_id
     * @return int
     */
    public function isShopPayed($shop_id,$user_id){
        if($user_id<1){
            return 0;
        }
        $order_valid_time = 15*60;
        $nowtime = Buddha::$buddha_array['buddha_timestamp'];
        $start = $nowtime-$order_valid_time;
        $OrderObj=new Order();


        $Order_Num= $OrderObj->countRecords("user_id='{$user_id}'  AND pay_status=1 AND createtime>$start ");



        if($Order_Num){

            $Db_Order = $OrderObj->getSingleFiledValues(array('good_id','good_table'),"user_id='{$user_id}'  AND pay_status=1 AND createtime>$start ");

            $flag = 0;
            foreach($Db_Order as $k=>$v){

                $good_table = $v['good_table'];
                $good_id = $v['good_id'];
                if($flag==0 and $good_table=='activity'){
                    $ActivityObj = new Activity();
                    $Activity_Num = $ActivityObj->countRecords("shop_id='{$shop_id}' AND id='{$good_id}' ");
                    if($Activity_Num>0){
                        $flag=1;
                    }

                }

                if($flag==0 and $good_table=='demand'){
                    $DemandObj = new Demand();
                    $Demand_Num = $DemandObj->countRecords("shop_id='{$shop_id}' AND id='{$good_id}' ");
                    if($Demand_Num>0){
                        $flag=1;
                    }
                }

                if($flag==0 and $good_table=='lease'){

                    $LeaseObj = new Lease();
                    $Lease_Num = $LeaseObj->countRecords("shop_id='{$shop_id}' AND id='{$good_id}' ");
                    if($Lease_Num>0){
                        $flag=1;
                    }

                }

                if($flag==0 and $good_table=='recruit'){

                    $RecruitObj = new Recruit();
                    $Recruit_Num = $RecruitObj->countRecords("shop_id='{$shop_id}' AND id='{$good_id}' ");
                    if($Recruit_Num>0){
                        $flag=1;
                    }

                }

                if($flag==0 and $good_table=='shop'){

                    $ShopObj = new Shop();
                    $Shop_Num = $ShopObj->countRecords("id='{$good_id}' ");
                    if($Shop_Num>0){
                        $flag=1;
                    }

                }
                if($flag==0 and $good_table=='supply'){
                    $SupplyObj = new Supply();
                    $Supply_Num = $SupplyObj->countRecords("shop_id='{$shop_id}' AND id='{$good_id}' ");
                    if($Supply_Num>0){
                        $flag=1;
                    }
                }
                if($flag==0 and $good_table=='singleinformation'){
                    $SingleinformationObj = new Singleinformation();
                    $Singleinformation_Num = $SingleinformationObj->countRecords("shop_id='{$shop_id}' AND id='{$good_id}' ");
                    if($Singleinformation_Num>0){
                        $flag=1;
                    }
                }
                if($flag==0 and $good_table=='heartpro'){

                    $HeartproObj = new Heartpro();
                    $Heartpro_Num = $HeartproObj->countRecords("shop_id='{$shop_id}' AND id='{$good_id}' ");
                    if($Heartpro_Num>0){
                        $flag=1;
                    }

                }



            }



            return $flag;
        }else{
            return 0;
        }


    }

    /**
     *判断店铺付款否 付款可以显示
     * 判断店铺是否认证 认证可以显示
     * 判断店铺是否在免费显示的日期内
     * @param $shop_id
     * @param $user_id
     * @return int
     */
    public function isShowCellphone($shop_id,$user_id){

        if($this->isShopPayed($shop_id,$user_id)){

            return 1;

        }elseif ($this->isShopVerify($shop_id)){

            return 1;

        }elseif($this->isFreeForShowCellphoneDay($shop_id)){

            return 1;
        }else{

            return 0;
        }


    }

    /**
     * @param   $Db_Shop
     * @param   $usertoken
     * @return  string
     * @author  陈绍海
     * 是否显示电话号码
     */
    public function isShowPhpone($shopid,$usertoken){

        $ShopObj=new Shop;
        $Db_Shop=$ShopObj->getSingleFiledValues(array('mobile','is_verify','createtime')," id ='{$shopid}' ");

        $mobile='';
        if ($Db_Shop['is_verify']==0){
            $endtime = strtotime($Db_Shop['createtime']) + 7*24*60*60;//免费7天的 结束时间
            $nowtime=Buddha::$buddha_array['buddha_timestamp'];
            /*如果当前时间不在七天免费时间内就不显示电话号码*/
            if( $nowtime > $endtime){

                //付费查看电话过期时间(15分钟有效)
                $start = time()-15*60;
                $OrderObj=new Order();
                if($usertoken){
                    $see= $OrderObj->countRecords("user_id='{$usertoken}' AND good_id='{$Db_Shop['shop_id']}' AND pay_status=1 AND createtime>$start ");
                    if(!$see){
                        $mobile='';
                    }
                }else{
                    $mobile='';
                }
            }
        }else{
            $mobile=$Db_Shop['mobile'];
        }
        return $mobile;
    }

    /**
     * @param $latitude1
     * @param $longitude1
     * @param $latitude2
     * @param $longitude2
     * @return string
     * @author  陈绍海
     * 距离计算
     */


    function getDistance($lon1, $lat1, $log2, $lat2, $unit=2, $decimal=2){
        $EARTH_RADIUS = 6370.996; // 地球半径系数
        $PI = 3.1415926;
        $radLat1 = $lat1 * $PI / 180.0;
        $radLat2 = $lat2 * $PI / 180.0;
        $radLng1 = $lon1 * $PI / 180.0;
        $radLng2 = $log2 * $PI /180.0;
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $distance = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
        $distance = $distance * $EARTH_RADIUS * 1000;
        if($unit==2){
            $distance = $distance / 1000 ;
            return round($distance, $decimal) .'km';
        }else{
            return round($distance, $decimal) .'m';
        }
    }



    function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2,$api_number,$decimal = 2)
    {
        $RegionObj=new Region();
        if($latitude1==0 AND $longitude1==0)
        {
            $Db_Region = $RegionObj->getSingleFiledValues(array('lat','lng')," number ='{$api_number}'");
            $latitude1=$Db_Region['lat'];
            $longitude1=$Db_Region['lng'];
        }

        $lat1=$latitude1;
        $lat2=$latitude2;
        $lon1=$longitude1;
        $log2=$longitude2;


        $EARTH_RADIUS = 6370.996; // 地球半径系数
        $PI = 3.1415926;
        $radLat1 = $lat1 * $PI / 180.0;
        $radLat2 = $lat2 * $PI / 180.0;
        $radLng1 = $lon1 * $PI / 180.0;
        $radLng2 = $log2 * $PI /180.0;
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $distance = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
        $distance = $distance * $EARTH_RADIUS * 1000;
        if($distance>100){
            $distance = $distance / 1000 ;
            return round($distance, $decimal) .'km';
        }else{


            return round($distance, $decimal) .'m';
        }




    }



    /**
     *
     * type   0：全部 2：新添加 3：已通过 4：未通过 5:已停用
     * @param $Services
     * @return array
     *      getPersonalCenterHeaderMenu
     *
     */

    public function getPersonalCenterHeaderMenu($Services,$isShowStop=1,$view=0){
        $header=array();
        $view0 = $view1=$view2=$view3=$view4=0;
        if($view==0){
            $view0=1;
        }elseif($view==2){
            $view1=1;
        }
        elseif($view==3){
            $view2=1;
        }
        elseif($view==4){
            $view3=1;
        }
        elseif($view==5){
            $view4=1;
        }

        $header[0] = array( 'select'=>$view0,'name'=>'全部','pageflag'=>'','type'=>0,
            'Services'=>$Services,'param'=>array('view'=>0));
        $header[1] = array( 'select'=>$view1,'name'=>'新加','pageflag'=>'','type'=>2,
            'Services'=>$Services,'param'=>array('view'=>2));
        $header[2] = array( 'select'=>$view2,'name'=>'已审核','pageflag'=>'','type'=>3,
            'Services'=>$Services,'param'=>array('view'=>3));
        $header[3] = array( 'select'=>$view3,'name'=>'未通过','pageflag'=>'','type'=>4,
            'Services'=>$Services,'param'=>array('view'=>4));



        if($isShowStop==1){
            $header[4] = array( 'select'=>$view4,'name'=>'已下架','pageflag'=>'','type'=>5,
                'Services'=>$Services,'param'=>array('view'=>5));
        }


        return $header;
    }


    /**
     * @param $shop_id
     * @return string
     * 根据店铺ID获取店铺名称
     */


    public function getShopnameFromShopid($shop_id){

        if($shop_id)
        {
            $Db_Shop = $this->getSingleFiledValues(array('name')," id='{$shop_id}' ");
            return $Db_Shop['name'];
        }else{
            return '';
        }

    }

    /**
     * @param $shop_id
     * @return string
     */


    public function getShopImgFromShopid($shop_id,$b_display){


        $shop_id=(int)$shop_id;
        $b_display=(int)$b_display;
        if($b_display==2){
            $filedsarr=array(' small AS shop_img');
        }elseif($b_display==1){
            $filedsarr=array(' medium AS shop_img');
        }

        if($shop_id){

            $Db_Shop = $this->getSingleFiledValues($filedsarr," id='{$shop_id}' ");
            return $Db_Shop['shop_img'];

        }else{
            return '';
        }

    }

    public function addImageArrToShopAlbum($MoreImage,$shop_id,$savePath,$user_id=0){
         $shop_id = (int)$shop_id;

        if(Buddha_Atom_Array::isValidArray($MoreImage) and $shop_id>0){

            foreach($MoreImage as $k=>$v){

                $source_file_location = PATH_ROOT.$v;
                $source_filename  = str_replace($savePath, '', $v);

                Buddha_Tool_File::thumbImageSameWidth($source_file_location, 320, 320, 'S_');
                Buddha_Tool_File::thumbImageSameWidth($source_file_location, 640, 640, 'M_');
                Buddha_Tool_File::thumbImageSameWidth($source_file_location, 1200, 640, 'L_');



                $small_img = $savePath."/S_" . $source_filename;
                $medium_img = $savePath."/M_" . $source_filename;
                $large_img= $savePath."/L_" . $source_filename;


                $data = array();
                $data['goods_id'] = $shop_id;
                $data['table_name'] = 'shop';
                /*小图*/
                $data['goods_thumb'] =$small_img;
                /*中图*/
                $data['goods_img'] = $medium_img;
                /*大图*/
                $data['goods_large'] = $large_img;
                $data['user_id'] = $user_id;

                $this->db->addRecords ( $data, 'album' );
                @unlink($source_file_location);

            }

        }

    }

    public function setFirstGalleryImgToShop($shop_id,$user_id)
    {
        $where=" goods_id='{$shop_id}' AND table_name='shop' AND user_id='{$user_id}' ";
        $num =  $this->db->countRecords ( 'album', $where);

        if($num){

            $defaultgimages= $this->db->getSingleFiledValues('','album',$where." ORDER BY id DESC");

            $this->db->updateRecords(array('isdefault'=>'1'),'album',"id='{$defaultgimages['id']}'  ");
            $dataImg=array();
            $dataImg ['small'] = $defaultgimages['goods_thumb'];
            $dataImg ['medium'] = $defaultgimages['goods_img'];
            $dataImg ['large'] = $defaultgimages['goods_large'];
            $dataImg ['sourcepic'] = $defaultgimages['sourcepic'];
            $this->updateRecords($dataImg,"id='{$shop_id}' AND user_id='{$user_id}'");

        }



    }

    /**
     * @param $table_id
     * @param string $table_name
     * @param int $user_id
     * @param int $Imgmax
     * @param string $tableimg_name
     *   判断当前相册中的相片数量是否大于最大数量，如果大于则要删除
     */
    public function isImGtMax($table_id,$table_name='shop',$user_id=0,$Imgmax=1,$tableimg_name='album')
    {

        $where=" goods_id='{$table_id}' AND table_name='shop' AND user_id='{$user_id}' ";
        $Db_Tableimg_num =  $this->db->countRecords ( $tableimg_name, $where);
        if($table_name == 'shop'){
            $table_name_filedarr=array('small','medium','large','sourcepic');
        }
        if($tableimg_name == 'album'){
            $tableimg_name_filedarr=array('goods_thumb','goods_img','goods_large','sourcepic');
        }

        if($Db_Tableimg_num>=$Imgmax){

            $defaultgimages= $this->db->getFiledValues (array('id','goods_thumb','goods_img','goods_large','sourcepic'),$tableimg_name,$where." ORDER BY id DESC");

            foreach ($defaultgimages as $k=>$v){

                foreach($tableimg_name_filedarr as $kk=>$vv){
                    @unlink(PATH_ROOT . $v [$vv] );
                }

                $this->db->delRecords ( $tableimg_name, "id={$v['id']} AND table_name='shop'" );

            }

            $data = array();
            foreach ($table_name_filedarr as $k=>$v){
                $data[$v]='';
            }

            $this->db->updateRecords( $data, $table_name,"id='{$table_id}'" );
        }

    }






    public function getMoneyArrayFromShop($shop_id,$money){

         $data = array();
         $Shop_Num = $this->countRecords("isdel=0 and id='{$shop_id}' ");
         if($Shop_Num){
             $Db_Shop = $this->getSingleFiledValues(array('agentrate','partnerrate','referral_id','agent_id','is_sure'),"isdel=0 and id='{$shop_id}' ");

             $referral_id = $Db_Shop['referral_id'];
             $partnerrate = $Db_Shop['partnerrate'];
             $agentrate= $Db_Shop['agentrate'];
             $agent_id = $Db_Shop['agent_id'];


             $money_plat=$money*0.2;

             if($referral_id and $partnerrate<100 and $money>0){
                 $money_partner = $money*$partnerrate/100;
             }else{
                 $money_partner=0;
             }

         /*    if($agent_id and $agentrate<100 and $money>0){
                 $money_agent = $money*$agentrate/100;
             }else{
                 $money_agent=0;
             }
             $money_plat = $money-$money_partner-$money_agent;*/

             $money_agent = $money-$money_plat-$money_partner;
             $data['money_agent']=$money_agent;
             $data['money_partner']=$money_partner;
             $data['money_plat']=$money_plat;
             $data['goods_amt']=$money;
             $data['final_amt']=$money;

         }else{
             $data['money_agent']=0;
             $data['money_plat']=0;
             $data['money_partner']=0;
             $data['goods_amt']=$money;
             $data['final_amt']=$money;
         }

        return $data;
    }


    public  function shopDeleteByPartner($shop_id){

        $can_delete = 1;
        //供应
        $SupplyObj = new Supply();
        $goods=$SupplyObj->countRecords(" shop_id='{$shop_id}'");
        if($goods>0) {
            $can_delete =0;
            $msg_can_delete ="有供应内容所以不能删除";
        }
        //需求
        $DemandObj = new Demand();
        $demand=$DemandObj->countRecords("  shop_id='{$shop_id}'");
        if($demand>0) {
            $can_delete =0;
            $msg_can_delete ="有需求内容所以不能删除";
        }
        //招聘
        $RecruitObj = new Recruit();
        $recruit=$RecruitObj->countRecords(" shop_id='{$shop_id}'");
        if($recruit>0) {
            $can_delete =0;
            $msg_can_delete ="有招聘内容所以不能删除";
        }
        //租赁
        $LeaseObj = new Lease();
        $lease=$LeaseObj->countRecords(" shop_id='{$shop_id}'");
        if($lease>0) {
            $can_delete =0;
            $msg_can_delete ="有租赁内容所以不能删除";
        }

        if($can_delete){

            if($this->debug_mode){
                $result =  $this->db->debug()->delete($this->table, array("id" => $shop_id));
            }else{
                $result =    $this->db->delete($this->table, array("id" => $shop_id));
            }

            return  array('can_delete'=>1,'msg_can_delete'=> $result);

        }else{
            return  array('can_delete'=>$can_delete,'msg_can_delete'=> $msg_can_delete);
        }
    }



    public function delshop($shop_id){
        //供应
        $SupplyObj = new Supply();
        $goods=$SupplyObj->countRecords("shop_id='{$shop_id}'");
        if($goods>0) {
            $SupplyObj->del("shop_id='{$shop_id}'");
        }
        //需求
        $DemandObj = new Demand();
        $demand=$DemandObj->countRecords("shop_id='{$shop_id}'");
        if($demand>0) {
            $DemandObj->del("shop_id='{$shop_id}'");
        }
        //招聘
        $RecruitObj = new Recruit();
        $recruit=$RecruitObj->countRecords(" shop_id='{$shop_id}'");
        if($recruit>0) {
            $RecruitObj->del("shop_id='{$shop_id}'");
        }
        //租赁
        $LeaseObj = new Lease();
        $lease=$LeaseObj->countRecords("shop_id='{$shop_id}'");
        if($lease>0) {
            $LeaseObj->del("shop_id='{$shop_id}'");
        }
    }

    //停用店铺，店铺所属信息下架处理
    public function  DisableShop($shop_id,$user_id){
            $this->updateRecords(array('state' => 1), "isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        //供应
        $SupplyObj = new Supply();
        $goods=$SupplyObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($goods>0){
            $SupplyObj->updateRecords(array('buddhastatus' =>1), "isdel=0 and  user_id='{$user_id}' and shop_id='{$shop_id}'");
        }
        //需求
        $DemandObj = new Demand();
        $demand=$DemandObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($demand>0){
            $DemandObj->updateRecords(array('buddhastatus' => 1), "isdel=0 and  user_id='{$user_id}' and shop_id='{$shop_id}'");
        }
        //招聘
        $RecruitObj = new Recruit();
        $recruit=$RecruitObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($recruit>0){
            $RecruitObj->updateRecords(array('buddhastatus' => 1), "isdel=0 and  user_id='{$user_id}' and shop_id='{$shop_id}'");
        }
        //租赁
        $LeaseObj = new Lease();
        $lease=$LeaseObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($lease>0){
            $LeaseObj->updateRecords(array('buddhastatus' => 1), "isdel=0 and  user_id='{$user_id}' and shop_id='{$shop_id}'");
        }
    }

    public function  EnableShop($shop_id,$user_id){
        $this->updateRecords(array('state' => 0), "isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        //供应
        $SupplyObj = new Supply();
        $goods=$SupplyObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($goods>0) {
            $SupplyObj->updateRecords(array('buddhastatus' => 0), "isdel=0 and  user_id='{$user_id}' and shop_id='{$shop_id}'");
        }
        //需求
        $DemandObj = new Demand();
        $demand=$DemandObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($demand>0) {
            $DemandObj->updateRecords(array('buddhastatus' => 0), "isdel=0 and  user_id='{$user_id}' and shop_id='{$shop_id}'");
        }
        //招聘
        $RecruitObj = new Recruit();
        $recruit=$RecruitObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($recruit>0) {
            $RecruitObj->updateRecords(array('buddhastatus' => 0), "isdel=0 and  user_id='{$user_id}' and shop_id='{$shop_id}'");
        }
        //租赁
        $LeaseObj = new Lease();
        $lease=$LeaseObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($lease>0) {
            $LeaseObj->updateRecords(array('buddhastatus' => 0), "isdel=4 an0  user_id='{$user_id}' and shop_id='{$shop_id}'");
        }
    }

//////////////////////end////////////////////


    /**
     * @param $shop_id
     * @param $user_id
     * 店铺   停用
     */
    public function  stopShop($shop_id,$user_id)
    {
        $ShopObj = new Shop();
        $shop   =  $ShopObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($shop>0){
            $ShopObj->updateRecords(array('isdel' => 4, 'state' => 1), "isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        }
        //供应
        $SupplyObj = new Supply();
        $goods=$SupplyObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($goods>0){
         $SupplyObj->updateRecords(array('isdel' => 4), "isdel=0 and  user_id='{$user_id}' and shop_id='{$shop_id}'");
        }
        //需求
        $DemandObj = new Demand();
        $demand=$DemandObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($demand>0){
        $DemandObj->updateRecords(array('isdel' => 4), "isdel=0 and  user_id='{$user_id}' and shop_id='{$shop_id}'");
        }
        //招聘
        $RecruitObj = new Recruit();
        $recruit = $RecruitObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($recruit>0){
        $RecruitObj->updateRecords(array('isdel' => 4), "isdel=0 and  user_id='{$user_id}' and shop_id='{$shop_id}'");
        }
        //租赁
        $LeaseObj = new Lease();
        $lease=$LeaseObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($lease>0){
        $LeaseObj->updateRecords(array('isdel' => 4), "isdel=0 and  user_id='{$user_id}' and shop_id='{$shop_id}'");
        }
    }


    /**
     * @param $shop_id
     * @param $user_id
     * 店铺   启用
     */
    public function  startShop($shop_id,$user_id)
    {
        $ShopObj=new Shop();
        $shop = $ShopObj->countRecords("isdel=4 and  user_id='{$user_id}' and id='{$shop_id}'");

        if($shop>0) {
            $ShopObj->updateRecords(array('isdel' => 0, 'state' => 0), "isdel=4 and  user_id='{$user_id}' and id='{$shop_id}'");
        }
        //供应
        $SupplyObj = new Supply();
        $goods=$SupplyObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($goods) {
            $SupplyObj->updateRecords(array('isdel' => 0), "isdel=4 and  user_id='{$user_id}' and shop_id='{$shop_id}'");
        }
        //需求
        $DemandObj = new Demand();
        $demand=$DemandObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($demand>0) {
            $DemandObj->updateRecords(array('isdel' => 0), "isdel=4 and  user_id='{$user_id}' and shop_id='{$shop_id}'");
        }
        //招聘
        $RecruitObj = new Recruit();
        $recruit=$RecruitObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($recruit>0) {
            $RecruitObj->updateRecords(array('isdel' => 0), "isdel=4 and  user_id='{$user_id}' and shop_id='{$shop_id}'");
        }
        //租赁
        $LeaseObj = new Lease();
        $lease=$LeaseObj->countRecords("isdel=0 and  user_id='{$user_id}' and id='{$shop_id}'");
        if($lease>0) {
            $LeaseObj->updateRecords(array('isdel' => 0), "isdel=4 and  user_id='{$user_id}' and shop_id='{$shop_id}'");
        }
    }

    public function getstoretypeindex(){
        $nature=array('1'=>'沿街商铺','2'=>'市场','3'=>'商场','4'=>'写字楼','5'=>'生产制造');
        return $nature;
    }




    public function getNatureOption($id = 0){
        $table = '';
        $nature=array('1'=>'沿街商铺','2'=>'市场','3'=>'商场','4'=>'写字楼','5'=>'生产制造');
          foreach($nature as $k=>$v){
              $selected='';
              if($k==$id){
                 $selected='selected';
              }
              $table.='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
          }
        return $table;
    }

    public function getNature($id = 0){
        $nature=array('1'=>'沿街商铺','2'=>'市场','3'=>'商场','4'=>'写字楼','5'=>'生产制造');
        foreach($nature as $k=>$v){
            if($k==$id){
                $table=''.$v.'';
            }
        }
        return $table;
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


    public function location($address){
        $key=Buddha::$buddha_array['buddha_tencent_key'];
        $oauurl='http://apis.map.qq.com/ws/geocoder/v1/?address='.$address.'&key='.$key.'';
        $re = $this->curl_file_get_contents($oauurl);
        $rearr = json_decode($re,true);
        return $rearr['result']['location'];

    }
    public function curl_file_get_contents($durl){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $durl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }

    /**
     *  返回用户的店铺列表
     * @param $user_id
     * @param string $shop_id
     * @return mixed
     * @author wph 2017-09-19
     */
    public function getUserShopArr($user_id,$shop_id=0){

        /*if($shop_id==0){
            $temp_select= 1;
        }else{
            $temp_select=0;
        }
        $Return_Shop[] = array('name'=>'选择店铺','namevalue'=>0,'select'=>$temp_select);*/
        $Db_Shop = $this->getFiledValues(array('id','name')," is_sure=1 and state=0 and user_id='{$user_id}' ");
        if(Buddha_Atom_Array::isValidArray($Db_Shop)){
            foreach($Db_Shop as $k=>$v){
                $temp_shop_id = $v['id'];
                if($temp_shop_id==$shop_id){
                    $temp_select= 1;
                }else{
                    $temp_select=0;
                }
                $Return_Shop[] = array('name'=>$v['name'],'namevalue'=>$v['id'],'select'=>$temp_select);
            }
        }else{
            $Return_Shop = '';
        }

        return $Return_Shop;
    }

    /**
     *  返回供应、需求、招聘、租赁等所属店铺
     * @param $user_id
     * @param string $shop_id
     * @return mixed
     * @author wph 2017-09-19
     */
    public function getShopArr($shop_id){

        /*if($shop_id==0){
            $temp_select= 1;
        }else{
            $temp_select=0;
        }
        $Return_Shop[] = array('name'=>'选择店铺','namevalue'=>0,'select'=>$temp_select);*/
        $Db_Shop = $this->getFiledValues(array('id','name')," is_sure=1 and state=0 and id='{$shop_id}' ");
        if(Buddha_Atom_Array::isValidArray($Db_Shop)){
            foreach($Db_Shop as $k=>$v){
                $temp_shop_id = $v['id'];
                if($temp_shop_id==$shop_id){
                    $temp_select= 1;
                }else{
                    $temp_select=0;
                }
                $Return_Shop[] = array('name'=>$v['name'],'namevalue'=>$v['id'],'select'=>$temp_select);
            }
        }

        return $Return_Shop;
    }



    public  function getShoplistOption($user_id,$shop_id=''){
        $table = '';
        $where=$this->shop_public_where();
        $where.=" and user_id='{$user_id}' ";
        $Db_Shoplist= $this->db->getFiledValues(array('id','name'), $this->table, $where);
        foreach($Db_Shoplist as $k=>$v){
            $selected='';
            if($v['id']==$shop_id){
                $selected='selected';
            }
            $table.='<option value="'.$v['id'].'" '.$selected.'>'.$v['name'].'</option>';
        }
        return $table;
    }
/**********
 * @generateCode   生成验证码
 *
 ****************/
    public  function generateCode($urlcontent,$imglogo){
        require_once  './phpqrcode/phpqrcode.php';//验证码类库
        $value=$urlcontent;
        $logo = $imglogo; // 中间的logo
        $QR = "base.png"; // 自定义生成的。结束后可以删除
        $newtime=time();
        $last = $newtime.rand(1000,9999).'.png'; // 最终生成的图片名称  如 last.png
        $errorCorrectionLevel = 'L';//// 纠错级别：L、M、Q、H
        $matrixPointSize = 5;//点的大小：1到10
        QRcode::png($value, $QR, $errorCorrectionLevel, $matrixPointSize, 2);
        if($logo !== FALSE){
            $QR = imagecreatefromstring(file_get_contents($QR));
            $logo = imagecreatefromstring(file_get_contents($logo));
            $QR_width = imagesx($QR);
            $QR_height = imagesy($QR);
            $logo_width = imagesx($logo);
            $logo_height = imagesy($logo);
            $logo_qr_width = $QR_width / 5;
            $scale = $logo_width / $logo_qr_width;
            $logo_qr_height = $logo_height / $scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
        }
                imagepng($QR,$last); // 生成最终的文件
                @unlink ($QR);
        return $last;
    }
/*
 * @shop_url   关于店铺跳转的URL
 * err ==1  表示手机（默认）
 *  err ==2  表示PC（默认）
 * */
   public function shop_url($err=1){
        if($err==1){
            return $url='index.php?a=mylist&c=shop&id=';
        }
    }



    //====================================================================================================
    /*   查询当前用户是否有合伙人
     *      有：则查看店铺有没有合伙人（当前用户有合伙人而店铺没有就要加上）
     */
    public function  referral_id_func(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $referral_id=$UserInfo['referral_id'];
        if($referral_id>0){
        $refe = $this->db->getFiledValues (array('id','referral_id'),  $this->prefix.'shop',"(isdel=0 or isdel=4) and user_id='{$uid}' order by id asc");
            if($refe[0]['referral_id']==0){
                $datas['referral_id'] = $referral_id;
                $id=$refe[0]['id'];
                $num=$this->edit($datas,$id);
            }
        }
        return $num;
    }
    //====================================================================================================

    //查询店铺的公共条件
    //$is_area =0 不加入查询地区条件 =1  加入地区条件
    function shop_public_where($is_area=0){
        $where=' is_sure=1 AND state=0 AND isdel=0 ';
        if($is_area==1){
            $RegionObj=new Region();
            $locdata = $RegionObj->getLocationDataFromCookie();
            $where.=$locdata['sql'];
        }
        return $where;
    }


}