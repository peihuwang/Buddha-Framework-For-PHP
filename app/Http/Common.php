<?php
class Common extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }


    /**
     * @param $var
     * @return mixed
     * 判断变量是否存在
     */
    public function isvar($var)
    {

        if(is_array($var))//判断变量是否是数组
        {

            if(!Buddha_Atom_Array::isValidArray($var))
            {
                return '';
            }else{
                return $var;
            }

        }else{
            if(!Buddha_Atom_String::isValidString($var))
            {
                return '';
            }else{

                return $var;
            }
        }

    }


    /**
     * @param $imgurl
     * @return string
     * 处理img的URL双线或第一个有线但有的没有要加线的问题
     */
    public function handleImgSlashByImgurl($imgurl)
    {

        $host = Buddha::$buddha_array['host'];

        if(!Buddha_Atom_String::isValidString($imgurl))
        {
            return '';
        }

        $num = strpos($imgurl,'/');

        if($num===FALSE)
        {
            return '';

        }elseif ($num==0)
        {

             $imgurl = ltrim($imgurl,'/');
//            $imgurl = '/'.$imgurl;
        }

        Buddha_Atom_String::getAfterReplaceStr('//','',$imgurl);


        return $host.$imgurl;
    }


    /**
     * @return mixed
     *储存图片的根目录
     */
    public function photoalDirectory()
    {
        return 'storage/';
    }



    /**
     * @param $Idarr
     * @return string
     * @return $filed     组装对象的字段名称
     * 将数Id组转化为Id字符串
     */
    public function getIdstrByIdarr($Idarr,$filed='')
    {

        $Idstr = '';

        if(!Buddha_Atom_Array::isValidArray($Idarr))
        {
            return '';
        }

        if(!Buddha_Atom_String::isValidString($filed))
        {
            foreach ($Idarr as $k=>$v)//组装合并对象ID为字符串(即物业名称 列表)
            {
                $Idstr .=$v.',';
            }

        }else{
            foreach ($Idarr as $k=>$v)//组装合并对象ID为字符串(即物业名称 列表)
            {
                $Idstr .=$v[$filed].',';
            }
        }


        $Idstr = rtrim($Idstr,',');

        return $Idstr;
    }


    /**
     * @param $Selectdata
     * @param $id
     * @return mixed
     * 根据数据ID选择数据中的默认选择项
     */
    public function defaultSelectById ($Selectdata,$id=0)
    {
        $id = (int)$id;
        if(Buddha_Atom_String::isValidString($id))
        {
            foreach ($Selectdata as $k=>$v)
            {
                if(Buddha_Atom_String::isValidString($id))
                {
                    if($id == $v['id'])
                    {
                        $Selectdata[$k]['select']= 1;

                    }else{

                        $Selectdata[$k]['select']= 0;
                    }

                }else{

                    if($k == 0)
                    {
                        $Selectdata[$k]['select']= 1;

                    }else{

                        $Selectdata[$k]['select']= 0;
                    }
                }



            }
        }
        return $Selectdata;
    }


    /**
     * @param $table_id
     * @param $table_id
     * @param $table_name
     * @param string $bigpicture 该表格中大图的字段：  如果该字段为空，则表示表格名称拼接默认字段；如果不为空就显示传过来的字段
     * @param string $smallpicture
     * @param string $imgtablenamae   图片表的名称  多个相册Album《混合相册》、Moregallery《混合此相册》
     * @param int $b_display   手机还是Pc
     * @return array
     * 通过 tableid 获取相册  只适合于  Album《混合相册》和Moregallery《混合此相册》
     *
     * 思路：
     *       先判断是否在这两个相册存在图片（因为涉及到多个相册Album《混合相册》、Moregallery《混合此相册》
     *          如果 $imgtablenamae  1、如果为空：则需要一个一个的查找
     *                              2、如果不为空：直接查询
     *                                  A 有值：返回值
     *                                  B 没有值：查询当前数据是否存在图片
     *                                       C、 有值：返回值
     *                                       D、没有值：当前数据的店铺图片是否存在
     *                                           E、有值：返回值
     *                                           F、没有值：返回空
     */

    public function getGalleryByTableidComm($table_id,$table_name,$bigpicture='_large',$smallpicture='_medium',$imgtablenamae='',$b_display=2,$filed='file')
    {
        $MoregalleryObj = new Moregallery();
        $AlbumObj = new Album();
        $b_display = (int)$b_display;

        if($imgtablenamae == 'moregallery')
        {
            $ImgMore = $MoregalleryObj->getMoregalleryByTableid($table_id,$table_name,$b_display,$filed);
        }elseif($imgtablenamae == 'album')
        {
            $ImgMore = $AlbumObj->getAlbumByTableid($table_id,$table_name,$b_display=2);
        }else{
            $ImgMore = $MoregalleryObj->getMoregalleryByTableid($table_id,$table_name,$b_display,$filed);
            if(!Buddha_Atom_Array::isValidArray($ImgMore)){
                $ImgMore = $AlbumObj->getAlbumByTableid($table_id,$table_name,$b_display=2);
            }
        }

        $ImgMore = array();

        if(!Buddha_Atom_Array::isValidArray($ImgMore))
        {
            /***
             * 当 moregallery 和 Album 都为空时 获取当前信息的图片
             */
            $table_filedarr = array('shop_id');

            if( $bigpicture!='_large' AND $smallpicture!='_medium')
            {
                if($b_display==2)
                {
                    array_push($table_filedarr,$smallpicture.' as img');
                }elseif($b_display==1){
                    array_push($table_filedarr,$bigpicture.' as img');
                }
            }else{
                if($b_display==2)
                {
                    array_push($table_filedarr,$table_name.'_img as img');
                }elseif($b_display==1){
                    array_push($table_filedarr,$table_name.'_large as img');
                }
            }

            $Db_Table = $this->db->getSingleFiledValues($table_filedarr,$table_name,"id='{$table_id}'");

            if(Buddha_Atom_String::isValidString($Db_Table['img']))
            {
                $ImgMore[0]['img'] = $Db_Table['img'];
            }else{
                /***
                 * 当  moregallery 、 Album  和 店铺获取当前信息的图片  都为空时 查询 店铺 的
                 */
                $shop_filedarr = array();
                if($b_display==2)
                {
                    array_push($shop_filedarr,'medium as img');
                }elseif($b_display==1){
                    array_push($shop_filedarr,'large as img');
                }

                $ShopObj = new Shop();

                $Db_Shop = $ShopObj->getSingleFiledValues($shop_filedarr,"id='{$Db_Table['shop_id']}'");

                if(Buddha_Atom_Array::isValidArray($Db_Shop))
                {
                    $ImgMore[0]['img'] = $Db_Shop['img'];
                }else{
                    $ImgMore = array();
                }
            }
        }

        return $ImgMore ;
    }
    /**
     * @param $data
     * @return array
     * 首页轮播的 店铺性质 和 类型
     * nature   性质
     * type     类型
     * p_url    该条的URL是否要拼接参数(如：地区编码)
     * is_show  是否显示：0否(显示弹窗)；1是（显示链接）
     */
    public function IndexNav()
    {
        $host = Buddha::$buddha_array['host'];
        $number = 5;
        $url = $host.'index.php?';
        $img = $host.'style/images/';
        $nav = array(
            'nature'=>array(
                0=>array('img'=>$img.'001.png','name'=>'商家导航','url'=>$url.'a=infonew&c=local','filed'=>'yanjie','p_url'=>0,'is_show'=>1),
                1=>array('img'=>$img.'23.png','name'=>'商场','url'=>$url.'a=index&c=list&storetype=3','filed'=>'shangchang','p_url'=>0,'is_show'=>1),
                2=>array('img'=>$img.'22.png','name'=>'市场','url'=>$url.'a=index&c=list&storetype=2','filed'=>'shichang','p_url'=>0,'is_show'=>1),
                3=>array('img'=>$img.'5.png','name'=>'写字楼','url'=>$url.'a=index&c=list&storetype=4','filed'=>'xiezilou','p_url'=>0,'is_show'=>1),
                4=>array('img'=>$img.'21.png','name'=>'沿街商铺','url'=>$url.'a=shop&c=list&storetype=1','filed'=>'yanjie','p_url'=>0,'is_show'=>1),
                5=>array('img'=>$img.'6.png','name'=>'生产制造','url'=>$url.'a=index&c=list&storetype=5','filed'=>'zhizao','p_url'=>0,'is_show'=>1),
                6=>array('img'=>$img.'117.png','name'=>'本地生活','url'=>$url.'','filed'=>'zhizao','p_url'=>0,'is_show'=>0),
            ),
            'type'=>array(
                0=>array('img'=>$img.'111.png','name'=>'本地需求','url'=>$url.'a=index&c=demand','filed'=>'bendi','p_url'=>0,'is_show'=>1),
                1=>array('img'=>$img.'112.png','name'=>'本地招聘','url'=>$url.'a=index&c=recruit','filed'=>'bendi','p_url'=>0,'is_show'=>1),
                2=>array('img'=>$img.'index_zhuanfa.png','name'=>'转发有赏','url'=>$url.'a=reccharges&c=list','filed'=>'zhuanfa','p_url'=>1,'is_show'=>1),
                3=>array('img'=>$img.'13.png','name'=>'1分购','url'=>$url.'a=index&c=heartpro','filed'=>'fengou','p_url'=>1,'is_show'=>1),
                4=>array('img'=>$img.'24.png','name'=>'本地商城','url'=>$url.'a=index&c=shoppingmall','filed'=>'bendi','p_url'=>0,'is_show'=>1),
                
                5=>array('img'=>$img.'11.png','name'=>'本地活动','url'=>$url.'a=index&c=activity','filed'=>'huodong','p_url'=>1,'is_show'=>0),
                12=>array('img'=>$img.'geti.png','name'=>'个体活动','url'=>$url.'a=index&c=activity&view=1',''=>'huodong','p_url'=>1,'is_show'=>1),
                13=>array('img'=>$img.'lianhe.png','name'=>'联合活动','url'=>$url.'a=index&c=activity&view=2',''=>'huodong','p_url'=>1,'is_show'=>1),
                14=>array('img'=>$img.'toupiao.png','name'=>'投票活动','url'=>$url.'a=index&c=activity&view=4','filed'=>'huodong','p_url'=>1,'is_show'=>1),

                6=>array('img'=>$img.'118.png','name'=>'本地广告','url'=>$url.'a=infonew&c=local','filed'=>'guanggao','p_url'=>1,'is_show'=>1),
                7=>array('img'=>$img.'123.png','name'=>'本地传单','url'=>$url.'a=index&c=singleinformation','filed'=>'chuandan','p_url'=>0,'is_show'=>1),
                8=>array('img'=>$img.'116.png','name'=>'本地促销','url'=>$url.'a=shop&c=list&type=is_promotion','filed'=>'cuxiao','p_url'=>1,'is_show'=>1),
                9=>array('img'=>$img.'114.png','name'=>'本地租赁','url'=>$url.'a=index&c=lease','filed'=>'zulin','p_url'=>1,'is_show'=>1),
                10=>array('img'=>$img.'12.png','name'=>'本地团购','url'=>'','filed'=>'tuangou','p_url'=>1,'is_show'=>0),
                11=>array('img'=>$img.'115.png','name'=>'本地供应','url'=>$url.'a=index&c=supply','filed'=>'gongying','p_url'=>1,'is_show'=>1),
                
            ),
        );
        $nav['nature'] = array_chunk($nav['nature'],$number);
        $nav['type'] = array_chunk($nav['type'],$number);
        return $nav;
    }


    /**
     * 获取当前页面完整URL地址
     */
    function hsk_wx_get_url() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }

    public function getWeChatUserInformation(){
        $appid = 'wxfc875d9388d83b78';
        $appsecret = '591f59368f5e53b471aa2df4c79ff12b';

        $openid = $_COOKIE['openid'];
        if(!$openid && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
            if (!isset($_GET['code'])){
                $backurl = $this->hsk_wx_get_url();
                $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($backurl)."&response_type=code&scope=snsapi_base&state=123#wechat_redirect";
                Header("Location: $url");
            }else{
                //获取code码，以获取openid
                $code = $_GET['code'];
                $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
                $re = file_get_contents($url);
                $rearr = json_decode($re,true);
                $setopenid = $rearr['openid'];
                $access_token = $rearr['access_token'];
                $WechatconfigObj = new Wechatconfig();
                $rearrs = $WechatconfigObj->getWechatUserInfo($setopenid);
            }
        }
        return $rearrs;
    }

    /**
     * @param int $id
     * @return string
     * 推荐属于该店铺下的产品
     */
    public function recommendBelongShop($shop_id,$table_name,$table_id)
    {
        $shop_id = (int)$shop_id;
        $table_id = (int)$table_id;

        $list = array();
        $list['activity'] = array();
        $list['demand'] = array();
        $list['heartpro'] = array();
        $list['lease'] = array();
        $list['recruit'] = array();
        $list['singleinformation'] = array();
        $list['supply'] = array();

        if(Buddha_Atom_String::isValidString($shop_id))
        {
            $Activity_id = $Demand_id = $Heartpro_id = $Lease_id = $Recruit_id = $Singleinformation_id = $Supply_id = 0;

            if($table_name=='Activity')
            {
                $Activity_id = $table_id;
            }elseif($table_name=='Demand')
            {
                $Demand_id = $table_id;
            }elseif($table_name=='Heartpro')
            {
                $Heartpro_id = $table_id;
            }elseif($table_name=='Lease')
            {
                $Heartpro_id = $table_id;
            }elseif($table_name=='Recruit')
            {
                $Recruit_id = $table_id;
            }elseif($table_name=='Singleinformation')
            {
                $Singleinformation_id = $table_id;
            }elseif($table_name=='Supply')
            {
                $Supply_id = $table_id;
            }

            $ActivityObj = new Activity();
            $DemandObj = new Demand();
            $HeartproObj = new Heartpro($shop_id,$Heartpro_id);
            $LeaseObj = new Lease($shop_id,$Lease_id);
            $RecruitObj = new Recruit($shop_id,$Recruit_id);
            $SingleinformationObj = new Singleinformation($shop_id,$Singleinformation_id);
            $SupplyObj = new Supply($shop_id,$Supply_id);

            $Db_Activity  = $ActivityObj->recommendBelongShop($shop_id,$Activity_id);

            $Db_Demand = $DemandObj->recommendBelongShop($shop_id,$Demand_id);

            $Db_Heartpro = $HeartproObj->recommendBelongShop($shop_id,$Heartpro_id);

            $Db_Lease = $LeaseObj->recommendBelongShop($shop_id,$Lease_id);

            $Db_Recruit = $RecruitObj->recommendBelongShop($shop_id,$Recruit_id);

            $Db_Singleinformation = $SingleinformationObj->recommendBelongShop($shop_id,$Singleinformation_id);

            $Db_Supply = $SupplyObj->recommendBelongShop($shop_id,$Supply_id);

            $list['activity'] = $Db_Activity;
            $list['demand'] = $Db_Demand;
            $list['heartpro'] = $Db_Heartpro;
            $list['lease'] = $Db_Lease;
            $list['recruit'] = $Db_Recruit;
            $list['singleinformation'] = $Db_Singleinformation;
            $list['supply'] = $Db_Supply;


//            $list = array(
//                0=>$Db_Activity,
//                1=>$Db_Demand,
//                2=>$Db_Heartpro,
//                3=>$Db_Lease,
//                4=>$Db_Recruit,
//                5=>$Db_Singleinformation,
//                6=>$Db_Supply,
//            );

        }

        return $list;
    }



    /**
     * 用户分润
     *@param $user_id 用户id
     *@param $money 金额
     */

    public function getGlobalProfitDistribution($user_id,$money,$order_sn,$order_id){//全局利润分配
        $UserObj = new User();
        $BillObj = new Bill();
        $BullObj = new Bill();
        $UserassoObj = new Userasso();
        $UserassomoneyObj = new Userassomoney();
        $ShareDetails = $UserassomoneyObj->getSingleFiledValues('',"1=1");
        $UserassoInfo = $UserassoObj->getSingleFiledValues('',"user_id='{$user_id}'");
        if(Buddha_Atom_Array::isValidArray($ShareDetails)){
            $n = $ShareDetails['layerlim'];
            for($i=1;$i<=$n;$i++){
                $fieldOne = "layer" . $i;
                $fieldTwo = "layer_money" . $i;
                $layer[] = $UserassoInfo[$fieldOne];
                $layer_money[] = $ShareDetails[$fieldTwo];
            }            
        }
        foreach ($layer as $k => $v) {
            if($v){
                $userInfo = $UserObj->getSingleFiledValues(array('id','banlance'),"id='{$v}'");
                //$data['banlance'] = $userInfo['banlance'] + ($money * $layer_money[$k]);
                $price = $money * $layer_money[$k];
                $this->db->update('user',
                   array("banlance[+]" => $price),
                   array("id" =>$userInfo['id'])
               ); 
            }
            
            $num = $BillObj->countRecords("user_id='{$v}' AND order_id='$order_id' AND order_type='commission' ");
            if(!$num && $v){
                $data = array();
                $data['user_id'] = $v;
                $data['order_sn'] = $order_sn;
                $data['order_id'] = $order_id;
                $data['order_type'] = 'commission';//分润
                $data['order_desc'] = '分润';
                $data['is_order'] = 0;
                $data['orient'] = '+';
                $data['billamt'] = '+' . $price;
                $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];
                $insert_id = $BillObj->add($data);
            }   
        }
    }

    /**
     *  http://bdsj.com/index.php?a=info&c=heartpro&id=1
     * @param $event      = heartpro   哪一个控制器（有可能是表名）
     * @param $eventpage  = info   自定义的变量名称：如info代表的是heartpro 页面
     * @param $varid      = 变量 如：这里的ID的值
     * @param bool $logo  图标
     * @return string
     */
    public function getQRCode($event,$eventpage,$varid,$logo=FALSE)
    {
        if($logo!==FALSE)
        {

           $logolocation = PATH_ROOT .$logo;//准备好的logo图片

            if(!file_exists($logolocation))
            {
                $logo = FALSE;
            }else{
                /**图片路径中多余的'/'***/
                $logo = PATH_ROOT.Buddha_Atom_Dir::getformatDbStorageDir($logo);
            }
        }

        /**↓↓↓↓↓↓↓↓↓↓↓ 1分购：二维码 ↓↓↓↓↓↓↓↓↓↓↓**/
        if($event=='heartpro' and $eventpage=='info')
        {
            /**$keyvalue:组装域名后参数*/
            $keyvalue = "a=info&c=heartpro&id={$varid}";
            $qrcodefile = $event.'_'.$eventpage.'_'.$varid;
            $encode_keyvalue = Buddha_Tool_Password::encrypt($keyvalue,'E','hacker');
        }
        /**↑↑↑↑↑↑↑↑↑↑ 一1分购：二维码  ↑↑↑↑↑↑↑↑↑↑**/


        /**↓↓↓↓↓↓↓↓↓↓↓ 一码营销：即店铺二维码 ↓↓↓↓↓↓↓↓↓↓↓**/
        if($event=='shop' and $eventpage=='info')
        {
            /**$keyvalue:组装域名后参数*/
            $keyvalue = "a=mylist&c=shop&id={$varid}";
            $qrcodefile = $event.'_'.$eventpage.'_'.$varid;
            $encode_keyvalue = Buddha_Tool_Password::encrypt($keyvalue,'E','hacker');
        }
        /**↑↑↑↑↑↑↑↑↑↑ 一码营销：即店铺二维码  ↑↑↑↑↑↑↑↑↑↑**/


        /**↓↓↓↓↓↓↓↓↓↓↓ 登录：二维码 ↓↓↓↓↓↓↓↓↓↓↓**/
        if($event=='user' and $eventpage=='register')
        {
            /**$keyvalue:组装域名后参数*/
            $keyvalue = "a=register&c=account&origin_id={$varid}";
            $qrcodefile = $event.'_'.$eventpage.'_'.$varid;
            $encode_keyvalue = Buddha_Tool_Password::encrypt($keyvalue,'E','hacker');
        }
        /**↑↑↑↑↑↑↑↑↑↑ 登录：二维码  ↑↑↑↑↑↑↑↑↑↑**/


        /**↓↓↓↓↓↓↓↓↓↓↓ 1分购详情：二维码 ↓↓↓↓↓↓↓↓↓↓↓**/
        if($event=='heartpro' and $eventpage=='info')
        {
            /**$keyvalue:组装域名后参数*/
             $keyvalue = "a=info&c=heartpro&id={$varid}";
            $qrcodefile = $event.'_'.$eventpage.'_'.$varid;
            $encode_keyvalue = Buddha_Tool_Password::encrypt($keyvalue,'E','hacker');
        }
        /**↑↑↑↑↑↑↑↑↑↑ 分购详情：二维码 ↑↑↑↑↑↑↑↑↑↑**/


        /**引入phpqrcode类库***/
        include PATH_ROOT . 'phpqrcode/phpqrcode.php';
        $value=Buddha::$buddha_array['qrcodedomain']."index.php?c=jump&keyvalud={$encode_keyvalue}";


        $filename="storage/temp/{$event}/".$qrcodefile.'.png';
        if(file_exists(PATH_ROOT.'/'.$filename))
        {
           return $filename;
        }


        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 5;//生成图片大小
        $temporary = PATH_ROOT .'storage/temporary';//临时文件位置
        $n = $this->creade_path($temporary);//创建文件夹

        if($n!=0)
        {
            $nlog=$temporary.'/nlog'.$qrcodefile.''.'.png';//原始二维码图的路径+名称(不带logo的)

            QRcode::png($value, $nlog, $errorCorrectionLevel, $matrixPointSize, 2);//生成二维码图片

            $QR = $nlog;//已经生成的原始二维码图(原始二维码图的路径+名称(不带logo的))
            if ($logo !== FALSE) {

                $QR = imagecreatefromstring(file_get_contents($QR));

                $logo = imagecreatefromstring(file_get_contents($logo));

                $QR_width = imagesx($QR);//二维码图片宽度
                $QR_height = imagesy($QR);//二维码图片高度
                $logo_width = imagesx($logo);//logo图片宽度
                $logo_height = imagesy($logo);//logo图片高度
                if($logo_width>80){
                    $logo_width = 80;
                }
                if($logo_height>80){
                    $logo_height = 80;
                }
                $logo_qr_width = $QR_width / 5;
                $scale = $logo_width/$logo_qr_width;
                $logo_qr_height = $logo_height/$scale;
                $from_width = ($QR_width - $logo_qr_width) / 2;
                //重新组合图片并调整大小
                imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
                    $logo_qr_height, $logo_width, $logo_height);
            }


            if($logo==FALSE)
            {
                $oldfile =  $nlog;
            }else{
                //输出图片
                $ylog=$temporary.'/ylog'.$qrcodefile.''. '.png';//带Logo二维码的文件路径+名称

                imagepng($QR, $ylog);//带Logo二维码的文件名

                $oldfile= $ylog; //旧目录（即：带logo的二维码图片路径）
            }

            $newFile= PATH_ROOT ."storage/temp/{$event}"; //新目录

            $nus= $this->creade_path($newFile);
            if($nus!=0){
                $filename="storage/temp/{$event}/".$qrcodefile.'.png';
                $newFile_mv= PATH_ROOT.$filename; //新目录
                rename($oldfile,$newFile_mv);
              //  unlink($nlog);//删除原始二维码图（不带logo的二维码）
                return $filename;

            }
        }
        return $filename;

    }

    /**
     * @param $img 图片路径
     * @return string
     * 等比例缩放图片
     */
    public function proportionalscaling($img)
    {
        /****因为PHP只能对资源进行操作，所以要对需要进行缩放的图片进行拷贝，创建为新的资源***/
        $src = imagecreatefromjpeg($img);

        /*****↓↓↓↓↓↓ 取得源图片的宽度和高度 ↓↓↓↓↓***/
        $size_src = getimagesize($img);
        $w = $size_src['0'];
        $h = $size_src['1'];
        /*****↑↑↑↑↑↑ 取得源图片的宽度和高度 ↑↑↑↑***/

        /**↓↓↓↓↓↓指定缩放出来的最大的宽度（也有可能是高度）**/
        $max = 100;


        /***↓↓↓↓↓↓ 根据最大值为100，算出另一个边的长度，得到缩放后的图片宽度和高度 ↓↓↓↓↓↓***/
        if($w > $h){
            $w = $max;
            $h = $h*($max/$size_src['0']);
        }else{
            $h = $max;
            $w = $w*($max/$size_src['1']);
        }
        /***↑↑↑↑↑↑ 根据最大值为100，算出另一个边的长度，得到缩放后的图片宽度和高度 ↑↑↑↑↑↑***/

        /***↓↓↓↓↓↓ 声明一个$w宽，$h高的真彩图片资源 ↓↓↓↓↓↓***/
        $image=imagecreatetruecolor($w, $h);
        /***↑↑↑↑↑↑ 声明一个$w宽，$h高的真彩图片资源 ↑↑↑↑↑↑***/




        /****关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）***/
        imagecopyresampled($image, $src, 0, 0, 0, 0, $w, $h, $size_src['0'], $size_src['1']);

//        //告诉浏览器以图片形式解析
//        header('content-type:image/png');
//        imagepng($image);

        //销毁资源
        imagedestroy($image);

        return $image;
    }

    /**
     * @param $tablename       表    名称
     * @param $filedstarttime  开始  字符串名称
     * @param $filedendtime    结束  字符串名称
     * @param $local           本地  信息
     * @return mixed
     * 更新没有本应下架而没有下架的
     */
    public function UpdateShelvesStatus($tablename,$filedstarttime,$filedendtime,$local='')
    {
//------------------------
        /*先查询：当地有没有过期了但没有下架的1分购：有就下架*/

        $newtime = Buddha::$buddha_array['buddha_timestamp'];
        $countwhere = " isdel=0 and is_sure=1 and buddhastatus=0 AND ({$filedstarttime} > $newtime or $newtime > $filedendtime) {$local}";
        $count = $this->db->countRecords($this->prefix.$tablename, $countwhere);

        if($count>0)
        {
            $idstr = '';
            $shelftime = $this->db->getFiledValues(array('id'),$this->prefix.$tablename, $countwhere.' limit 50');
            foreach ($shelftime as  $k=>$v)
            {
                $idstr.=$v['id'].',';
            }

            $shelftimeid = trim($idstr,',');

            $shelftimedata['buddhastatus'] =1;

            $num = $this->db->updateRecords( $shelftimedata, $this->prefix.$tablename,"id IN ($shelftimeid)" );

        }
//---------------------------
        /*先查询：当地有没有 本没有  过期了但 已下架的1分购：有就 上架*/
        //屏蔽原因：首页没有上架的权利
//        $count_where = " isdel=0 and is_sure=1 and buddhastatus=1 AND ({$filedstarttime} < $newtime AND $newtime < $filedendtime) {$local}";
//
//        $count_1 = $this->db->countRecords($this->prefix.$tablename, $count_where);
//
//        if($count_1>0)
//        {
//            $idstr='';
//            $shelftime = $this->db->getFiledValues(array('id'),$this->prefix.$tablename, $count_where.' limit 50');
//            foreach ($shelftime as  $k=>$v)
//            {
//                $idstr.=$v['id'].',';
//            }
//            $shelf_timeid = trim($idstr,',');
//
//            $shelftime_data['buddhastatus'] =0;
//
//            $num = $this->db->updateRecords( $shelftime_data, $this->prefix.$tablename,"id IN ($shelf_timeid)" );
//        }

        return $num;
    }


    /**
     * @param $singleinfomation_id
     * @param $level3
     * @return int
     * @author wph 2017-09-14
     * 判断该 $tableid 通过 $tablename 和 $tableid 是否属于该代理商
     */

    public function isOwnerBelongToAgentByLeve3($table_name,$table_id,$level3)
    {
        $table_id=(int)$table_id;
        if($level3<1 or $table_id<1)
        {
            return 0;
        }

        if($this->isaudittableEffectivenessBytablename($table_name))
        {
            $num = $this->db->countRecords($table_name," id='{$table_id}' AND level3='{$level3}' ");
            if($num){
                return 1;
            }else{
                return 0;
            }
        }else{

            return 0;
        }

    }


    /**
     * @param $table_name
     * @param $table_id
     * @return int
     * 判断该 $tableid 通过 $tablename 、 $tableid 和 $uid 是否属于该用户
     */

    public function isToUserByTablenameAndTableid($table_name,$table_id,$user_id)
    {

        $table_id=(int)$table_id;
        $user_id=(int)$user_id;
        if($table_id<1 OR $user_id<1)
        {
            return 0;
        }

        $MysqlplusObj=new Mysqlplus;

        if(Buddha_Atom_String::isValidString($table_name) AND  $MysqlplusObj->isValidTable($table_name)){

//            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000001, 'good_table不存在');
//        }
//        if($this->isaudittableEffectivenessBytablename($table_name)){

            $num = $this->db->countRecords($table_name," id='{$table_id}' AND user_id='{$user_id}' AND isdel!=1");

            if($num){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 0;
        }

    }


    /**
     * @param $table_id
     * @param $table_name
     * @return int
     *  根据 $table_id 和 $table_name 判断 $table_id 是否已经审核过了
     * 0
     */

    public function isIssureByTableid($table_id,$table_name)
    {

        $table_id=(int)$table_id;

        if($table_id>0 And Buddha_Atom_String::isValidString($table_name)){

            $num=$this->db->countRecords($table_name," id='{$table_id}' AND is_sure!=0 ");

            if($num){
                return 1;
            }else
                return 0;
        }
        return 1;
    }


    /**
     * @param $tabl_ename
     * @param $table_id
     * @return int
     * 通过 $table_name 中的$table_id 判断 $table_id 是否存在
     */

    public function isIdByTablenameAndTableid($table_name,$table_id){
       $table_id=(int)$table_id;
       if($table_id AND Buddha_Atom_String::isValidString($table_name)){
           $Db_Table_num= $this->db->countRecords($table_name,"id ='$table_id'");
           if($Db_Table_num){
               return 1;
           }else{
               return 0;
           }
       }else{
           return 0;
       }

    }

    /**
     * @param $id
     * @param array $data
     * @return int
     * 判断$id是否在$data内
     * 0表示不存在，1表示存在
     */
    public function isIdInDataEffectiveById($id,$data=array(0,1))
    {
        $id=(int)$id;
        $aa=0;
        foreach ($data as $k=>$v){
            if($v==$id){
                $aa=1;
            }
        }
        return $aa;
    }


    /**
     * @param $time
     * @return int
     * 通过 $time 判断时间戳格式是否正确
     */
    public function isTimeEffectiveByTime($time)
    {
        $time=(int)$time;
        $newtimelen=strlen(time());
        if(strlen($time)>=$newtimelen ){
            return 1;
        }else {
            return 0;
        }

    }


    /**
     * @param $data
     * @return array
     * 审核状态
     */
    public function AuditStatusCodes()
    {
        $Status=array();
        $Status=array(
            0=>array('stateid'=>0,'name'=>'未审核'),
            1=>array('stateid'=>1,'name'=>'已通过审核'),
            2=>array('stateid'=>4,'name'=>'未通过审核'),
        );

        return $Status;
    }

    /**
     * @param $stateid
     * @return int
     * 判断状态码支付正确
     */
    public function isAuditStatusCodesBy($stateid){
        $state=$this->AuditStatusCodes();
        if($stateid==0 ||$stateid==1||$stateid==4 ){
            foreach ($state as $k=>$v){
                if($v['stateid']==$stateid){
                    return 1;
                }
            }
        }else{
            return 0;
        }
    }


    /**需要处理的所有表备注**/
    public function getalltableBytablename($tablename)
    {
        if(Buddha_Atom_String::isValidString($tablename)){
            $audittable=array();
            $audittable=$this->audittable();
            foreach($audittable as $k=>$v){
                if($v['name'] == $tablename){
                    return $v['chinesename'];
                }
            }
        }else{
            return '';
        }

    }

    /**
     * @param $data
     * @return array
     *  返回需要处理的所有表名称和备注
     */
    public function alltable()
    {
        $audittable=array();
        $audittable=array(
            0=>array(
                'name'=>'activity',
                'chinesename'=>'活动',
            ),
            1=>array(
                'name'=>'demand',
                'chinesename'=>'需求',
            ),
            2=>array(
                'name'=>'lease',
                'chinesename'=>'租赁',
            ),
            3=>array(
                'name'=>'recruit',
                'chinesename'=>'招聘',
            ),
            4=>array(
                'name'=>'shop',
                'chinesename'=>'店铺',
            ),
            5=>array(
                'name'=>'supply',
                'chinesename'=>'供应',
            ),
            6=>array(
                'name'=>'supply',
                'chinesename'=>'供应',
            ),
        );
        return $audittable;
    }

    /**
     * @param $data
     * @return array
     *  返回需要审核的表名称
     */
    public function audittable()
    {
        $audittable=array();
        $audittable=array(
            0=>array(
                'name'=>'activity',
                'chinesename'=>'活动',
            ),
            1=>array(
                'name'=>'demand',
                'chinesename'=>'需求',
            ),
            2=>array(
                'name'=>'lease',
                'chinesename'=>'租赁',
            ),
            3=>array(
                'name'=>'recruit',
                'chinesename'=>'招聘',
            ),
            4=>array(
                'name'=>'shop',
                'chinesename'=>'店铺',
            ),
            5=>array(
                'name'=>'supply',
                'chinesename'=>'供应',
            ),
            6=>array(
                'name'=>'singleinformation',
                'chinesename'=>'单页信息',
            ),
            7=>array(
                'name'=>'heartpro',
                'chinesename'=>'1分购',
            ),
        );
        return $audittable;
    }


    /*判断需要审核的表名称的有效性*/
    public function isaudittableEffectivenessBytablename($tablename)
    {

        if(Buddha_Atom_String::isValidString($tablename)){

            $audittable=array();
            $audittable=$this->audittable();
            foreach($audittable as $k=>$v){
                if($v['name']==$tablename){
                    return 1;
                }
            }
        }else{
            return 0;
        }

    }

    /*得到需要审核的表名称*/
    public function getaudittableEffectivenessBytablename($tablename)
    {
        if(Buddha_Atom_String::isValidString($tablename)){
            $audittable=array();
            $audittable=$this->audittable();
            foreach($audittable as $k=>$v){
                if($v['name']==$tablename){
                    return $v['chinesename'];
                }
            }
        }else{
            return '';
        }

    }


    /*判断需要审核的表id 的有效性*/
    public function isaudittableEffectivenessBytablename_id($tablename,$tablename_id)
    {
        $tablename_id=(int)$tablename_id;

        /*判断$tablename 的有效性（是否是在审核表的范围内）*/

        if($this->isaudittableEffectivenessBytablename($tablename)){

            /*判断 $tablename_id 为数字的有效性*/
            if($tablename_id>0){
                $Db_table_num= $this->db->countRecords($tablename,"id ='$tablename_id'");

                /*判断 $tablename_id 在表中的有效性*/
                if($Db_table_num){
                    return 1;
                }else{
                    return 0;
                }
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }


    /*需要审核 当table_name！=shop 时获取对应 shop_id*/
    public function getShopidBytablenameAndtableid($table_name,$table_id)
    {
        if(Buddha_Atom_String::isValidString($table_name) AND $table_name!='shop'){
           $Db_table_shop_id= $this->db->getSingleFiledValues ( array('shop_id'), $table_name, "id='{$table_id}'" );
           if($Db_table_shop_id){
               return $Db_table_shop_id;
           }else{
               return 0;
           }
        }else{
            return 0;
        }
    }

    /*需要审核：判断当前审核是否属于该代理商*/
    public function isOwnedAuditBytablename_id($table_name,$table_id,$level3)
    {
        if($table_name!='shop'){
            /*先当table_name！=shop 时获取对应 shop_id*/
            $shop_id=$this->getShopidBytablenameAndtableid($table_name,$table_id);
        }else{
            $shop_id=$table_id;
        }

        $Db_table_num=$this->db->countRecords('shop'," id='{$shop_id}' AND level3='{$level3}'");
        if($Db_table_num){
            return 1;
        }else{
            return 0;
        }
    }


    /*判断需要审核的表id 的有效性*/
    public function isaudittableEffectivenessBytablename_idanduid($tablename,$tablename_id,$uid)
    {
        $tablename_id=(int)$tablename_id;
        $uid=(int)$uid;
        /*判断 $tablename_id 为数字的有效性*/
        if($tablename_id>0){
            $Db_table_num= $this->db->countRecords($tablename,"id ='$tablename_id' ");

            /*判断 $tablename_id 在表中的有效性*/
            if($Db_table_num){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }

    /*判断 需要审核的表 是否已经审核过了*/
    public function isTableAuditBytablename_id($tablename,$tablename_id)
    {
        $tablename_id=(int)$tablename_id;
        /*判断$tablename 为字符串的有效性*/
        if($tablename_id>0){
            $Db_table_num= $this->db->countRecords($tablename,"id ='$tablename_id' AND is_sure=0");
            /*判断 $tablename_id 在表中的有效性*/
            if($Db_table_num){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 0;
        }

    }


    /**
     * @param array $field
     * @param string $condition
     * @return int
     * 通过手机号码正则判断手机号码是否有效
     */
    public function getMobilephoneiseffectiveBymobile($mobile)
    {
        if(preg_match("/^1[34578]\d{9}$/", $mobile)){
            return  1;
        }else{
            return  0;
        }
    }





    /**
     * @param $where
     * @return int
     * @ author 陈绍海
     */
    //pagination 分页
    //$tablewhere 要加入前缀
    //说明这里的where 指的是统计分页统计总条数的sql语句中 FROM 以后包括 FROM在内的所有条件
      public function pagination($tablewhere,$where,$pagesize,$page){
          $temp_sql ="SELECT count(*) AS total
                      FROM {$tablewhere}
                      WHERE {$where} ";
    

          $count_arr = $this->db->query($temp_sql)->fetchAll(PDO::FETCH_ASSOC);

          $rcount = $pcount = 0;

          if(Buddha_Atom_Array::isValidArray($count_arr))
          {
              $rcount = $count_arr[0]['total'];
              $pcount = ceil($rcount / $pagesize);
              if ($page > $pcount) {
                  $page = $pcount;
              }
          }

          $temp_Common = array();
          /*当前页*/
          $temp_Common['page'] = $page;
          /*每页数量*/
          $temp_Common['pagesize'] = $pagesize;
          /*总条数*/
          $temp_Common['totalrecord'] = $rcount;
          /*总页数*/
          $temp_Common['totalpage'] = $pcount;
    
          return $temp_Common;
      }




    /**
     * @param $time
     * @param int $isHIS
     * @return false|string
     * $isHIS 是否显示时分秒1 是 ，0否
     * $isS 是否显示秒1 是 ，0否
     * $returntimetype 返回类型  0 字符串（string类型）  1为0(int类型)（当为空时的返回值）
     */
    public function getDateStrOfTime($time,$isHIS=1,$isS=0,$returntimetype=0){


        if(Buddha_Atom_String::isValidString($time)){
            if($isS==1) {

                return date("Y-m-d H:i", $time);

            }else{

                return  date("Y-m-d H:i:s",$time);

            }

        }else{

            if($returntimetype==0){
                return  '';
            }elseif($returntimetype==1){
                return  0;
            }
        }

    }






//@GeneratingNumber编号
    public function GeneratingNumber(){
        $time=date(YmdHis);
        $random =rand(11111111,99999999);
        $num=$time.$random;
        return $num;
    }

    /*
     * @shop_url   关于店铺跳转的URL
     * err ==1  表示手机（默认）
     *  err ==2  表示PC（默认）
     *  type  类型：index 为列表，add为添加，edit为编辑，del为删除，top为置顶，mylist为详情页面
     * */
    public function activity_url($type='index',$err=1){
        $c_0=explode('&',$_SERVER["QUERY_STRING"]);
        $c_1=explode('=',$c_0[1]);
        $c=$c_1[1];
        if($err==1){
            return $url="index.php?a={$type}&c={$c}&id=";
        }
    }

    /*
     *  @page_where  根据情况给定动态加载信息！
     *  @$p  当前页数
     *  @$list   当前根据页数查询出的数据
 *      @$pagesize   每页显示条数
     * */

    public function page_where($p,$list,$pagesize){

        $Nws=array();
        if($p==1){
            if(count($list)==0){
                $goods['length']=0;
                $Nws='对不起，你查询的数据不存在，请看看别的吧';
            }elseif(count($list)>=0 && count($list)<$pagesize){
                $Nws='你的数据加载完毕';
            }elseif(count($list)==$pagesize){
                $Nws='向上拉加载更多';
            }
        }elseif($p>1){
            if(count($list)==0){
                $Nws='你的数据加载完毕';
                $goods['length']=0;
            }elseif(count($list)>=0 && count($list)<$pagesize){
                $Nws='你的数据加载完毕';
                $goods['length']=0;
            }elseif(count($list)==$pagesize){
                $Nws='向上拉加载更多';
            }
        }
        return $Nws;
    }

    /*
     * deviceType判断当前设备的类型
     */
    public  function deviceType(){
        $type_nmber=0;
        $agent=strtolower(strtolower($_SERVER['HTTP_USER_AGENT']));//全部转为小写
        $is_pc=(strpos($agent,'window nt'))?true:false;
        $is_iphone=(strpos($agent,'iphone'))?true:false;
        $is_ipad=(strpos($agent,'ipad'))?true:false;
        $is_android=(strpos($agent,'android'))?true:false;
        if($is_pc){
            $type_nmber=1;
        }else if($is_iphone){
            $type_nmber=2;
        }else if($is_ipad){
            $type_nmber=3;
        }else if($is_android){
            $type_nmber=4;
        }
        return $type_nmber;
    }


    /*
       * @ words_number 根据屏幕大小显示显示的字数
       * @$lines  显示几行 默认显示一行
       * @  默认显示三个点占用一个位置所以减去1
       *   $fontsize 字体大小
       */
    public function words_number($lines=1,$fontsize=13){
        $screenwidth = $_COOKIE['screenwidth'];
        if(!isset($screenwidth)){
            $screenwidth=320;
        }
        if($fontsize==14){
            if(320<=$screenwidth && $screenwidth<375){
                $number=14;
            }elseif(375<=$screenwidth && $screenwidth<414){
                $number=17;
            }elseif(414<=$screenwidth && $screenwidth<768){
                $number=19;
            }elseif($screenwidth=768){
                $number=42;
            }
        }else if($fontsize==13){
            if(320<=$screenwidth && $screenwidth<375){
                $number=17;
            }elseif(375<=$screenwidth && $screenwidth<414){
                $number=22;
            }elseif(414<=$screenwidth && $screenwidth<768){
                $number=25;
            }elseif($screenwidth=768){
                $number=42;
            }
        }
        $size=$number*$lines-3;
        return $size;
    }
    /*
     * @ footer
     * 每页共用底部
     */
    public function urlfooter(){
        $footer_arr=array();
        $footer_arr['url']=array(
            0=>array('url'=>'/index.php?a=index&c=index','namech'=>'首页','a'=>'index','c'=>'index'),
//            1=>array('url'=>'','namech'=>'商城','a'=>'','c'=>''),//屏蔽原因还未完成
            2=>array('url'=>'/index.php?a=infonew&c=local','namech'=>'本地信息','a'=>'infonew','c'=>'local'),
            3=>array('url'=>'/index.php?a=shop&c=list','namech'=>'附近商家','a'=>'shop','c'=>'list'),
            4=>array('url'=>'../index.php?a=index&c=ucenter','namech'=>'我的','a'=>'index','c'=>'ucenter'),
        );
        $footer_arr['class']='w'.(100/(count($footer_arr['url'])));
        return $footer_arr;
    }


    public function navigation()
    {
        $arr=array();
        $nature=array(
                1=>array('name'=>'沿街商铺','img'=>'index_icon1','url'=>'index.php?a=shop&c=list&storetype=1'),
                2=>array('name'=>'市场','img'=>'index_icon2','url'=>'href="index.php?a=index&c=list&storetype=2'),
                3=>array('name'=>'商场','img'=>'index_icon3','url'=>'index.php?a=index&c=list&storetype=3'),
                4=>array('name'=>'写字楼','img'=>'index_icon4','url'=>'index.php?a=index&c=list&storetype=4'),
                5=>array('name'=>'生产制造','img'=>'index_icon5','url'=>'index.php?a=shop&c=list&storetype=5'),
        );

        $function=array(
            1=>array('name'=>'需求','img'=>'index_icon_6','url'=>'index.php?a=index&c=demand'),
            8=>array('name'=>'供应','img'=>'index_icon_1','url'=>'index.php?a=index&c=supply'),
            2=>array('name'=>'促销','img'=>'index_icon_2','url'=>'index.php?a=promote&c=supply'),
            5=>array('name'=>'招聘','img'=>'index_icon_5','url'=>'index.php?a=index&c=recruit'),
            3=>array('name'=>'租赁','img'=>'index_icon_3','url'=>'index.php?a=index&c=lease'),

//            4=>array('name'=>'活动','img'=>'index_icon_4','url'=>''),

//            6=>array('name'=>'详情','img'=>'index_icon_6','url'=>''),
//            7=>array('name'=>'二手','img'=>'index_icon_7','url'=>''),

        );
        $size=8;//每组显示数目
        $fc=  array_chunk($function,$size);
        $arr=array('nature'=>$nature,'function'=>$fc);
        return $arr;
    }

    /**
     * @param $brief$brief  截取内容
     * @param $len   最大显示数量
     * @return mixed|string
     */
    function intercept_strlen($brief,$len=0)
    {
        $brief = Buddha_Atom_Html::tripHtmlTag($brief);
        $brief_re = '';
        if(Buddha_Atom_String::isValidString($brief))
        {
            if(Buddha_Atom_String::isValidString($len)){
                $max = $len;
            }else{
                $max = $this-> words_number($lines=1,$fontsize=13);//根据屏幕判断显示数量
            }
            if(mb_strlen($brief) > $max){
                $brief_re= mb_substr($brief,0,$max) . '...';
            }else{
                $brief_re = $brief;
            }
        }
        return $brief_re;
    }


    /*
     * @shop_url   关于店铺跳转的URL
     * err ==1  表示手机（默认）
     *  err ==2  表示PC（默认）
     *  type  类型：index 为列表，add为添加，edit为编辑，del为删除，top为置顶，mylist为详情页面
     * */
    public function jump_url($type='index',$err=1){
        $c_0=explode('&',$_SERVER["QUERY_STRING"]);
        $c_1=explode('=',$c_0[1]);
        $c=$c_1[1];
        if($err==1){
            return $url="index.php?a={$type}&c={$c}&id=";
        }
    }

//@codeimg   生成二维码图片
    public function codeimg($shopinfo)
    {
        include PATH_ROOT . 'phpqrcode/phpqrcode.php'; // 引入phpqrcode类库
                //生成中间带logo的二维码
        $value='http://'.$_SERVER['HTTP_HOST'].'/index.php?a=mylist&c=shop&id='.$shopinfo['id'];
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 5;//生成图片大小
        $temporary= PATH_ROOT .'storage/temporary';//临时文件位置
        $n= $this->creade_path($temporary);//创建文件夹
        if($n!=0){
            $nlog=$temporary.'/nlog'.$shopinfo['id'].'_'.time(). '.png';//原始二维码图的路径+名称(不带logo的)
            QRcode::png($value, $nlog, $errorCorrectionLevel, $matrixPointSize, 2);//生成二维码图片
            $logo = PATH_ROOT .$shopinfo['small'];//准备好的logo图片
            $QR = $nlog;//已经生成的原始二维码图(原始二维码图的路径+名称(不带logo的))
            if ($logo !== FALSE) {
                $QR = imagecreatefromstring(file_get_contents($QR));
                $logo = imagecreatefromstring(file_get_contents($logo));
                $QR_width = imagesx($QR);//二维码图片宽度
                $QR_height = imagesy($QR);//二维码图片高度
                $logo_width = imagesx($logo);//logo图片宽度
                $logo_height = imagesy($logo);//logo图片高度
                $logo_qr_width = $QR_width / 5;
                $scale = $logo_width/$logo_qr_width;
                $logo_qr_height = $logo_height/$scale;
                $from_width = ($QR_width - $logo_qr_width) / 2;
                //重新组合图片并调整大小
                imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
                    $logo_qr_height, $logo_width, $logo_height);
            }
            unlink($nlog);//删除原始二维码图（不带logo的二维码）
                //输出图片
            $ylog=$temporary.'/ylog'.$shopinfo['id'].'_'.time(). '.png';//带Logo二维码的文件路径+名称
//            imagepng($QR, 'helloweixin.png');//带Logo二维码的文件名
            imagepng($QR, $ylog);//带Logo二维码的文件名
//        echo '<img src="helloweixin.png">';//输出图片
            $oldfile= $ylog; //旧目录（即：带logo的二维码图片路径）
            $newFile= PATH_ROOT .'storage/shopcode/'; //新目录
            //var_dump($newFile);
            $nus= $this->creade_path($newFile);
            if($nus!=0){
                $filename='storage/shopcode/'.$shopinfo['id'].'_'.time().'.png';
                $newFile_mv= PATH_ROOT.$filename; //新目录
                rename($oldfile,$newFile_mv);

                $data['codeimg']=$filename;
                $ShopObj=new Shop();
                $num= $ShopObj->edit($data,$shopinfo['id']);
                return $num;
            }
        }
    }

//@creade_path  创建的多级目录
    public  function creade_path($path){
        //$path要创建的多级目录
        if (is_dir($path)){ //判断目录存在否，存在给出提示，不存在则创建目录
            $nus=2;//已存在
        }else{
            //第三个参数是“true”表示能创建多级目录，iconv防止中文目录乱码
            $res=mkdir( $path,0777,true);
            if ($res){
                $nus=1;//创建成功
            }else{
                $nus=0;//创建失败
            }
        }
        return $nus;
    }
    /**
     *  投票时间处理
     * （一天不能投票投票同一家）
     *
     */
    public function time_handle($filed = 'v_time')
    {
        $Today = time();//当前
        $Yesterday = strtotime(date("Y-m-d",$Today));//今天0点
        $Tomorrow = strtotime(date("Y-m-d",strtotime("+1 day")));//明天0点
        $time['Yesterday'] = $Yesterday;
        $time['Today'] = $Today;
        $time['Tomorrow'] = $Tomorrow;
        $time['where'] = " ({$time['Yesterday']} < {$filed} and {$filed} < {$time['Tomorrow']})";
        return $time;
    }



//图片删除
    public function delGalleryimage($goods_id,$ImgObj)
    {
        $goodimg= $ImgObj->getFiledValues('',"id='{$goods_id}'");
        if(is_array($goodimg) and count($goodimg)){
            foreach($goodimg as $k=>$v){
                $num[]=$ImgObj->del($v['id']);
                @unlink(PATH_ROOT . $v ['goods_thumb'] );
                @unlink(PATH_ROOT . $v ['goods_img']);
                @unlink(PATH_ROOT . $v ['goods_large']);
                @unlink(PATH_ROOT . $v ['sourcepic'] );
            }
        }
        return $num;
    }



    /*手机*/
    function checkMobile($str)
    {
        $pattern = '/^(13|14|15|17|18)d{9}$/';
        if (preg_match($pattern,$str))
        {
            Return true;
        }else{
            Return false;
        }
    }
    /*邮箱*/
    function checkEmail($str)
    {
        $preg_email='/^[a-zA-Z0-9]+([-_.][a-zA-Z0-9]+)*@([a-zA-Z0-9]+[-.])+([a-z]{2,5})$/ims';
        if(preg_match($preg_email,$str)){
            Return 1;
        }else{
            Return 0;
        }
    }

    /*固话*/
    function checkCall($str)
    {
        $preg_call='/^(0\d{2,3})?(\d{7,8})$/ims';
        if(preg_match($preg_call,$str)){
            Return 1;
        }else{
            Return 0;
        }
    }


    /*验证只包含中英文的名字*/
    function checkName($str)
    {
        $preg_name='/^[\x{4e00}-\x{9fa5}]{2,10}$|^[a-zA-Z\s]*[a-zA-Z\s]{2,20}$/isu';
        if(preg_match($preg_name,$str)){
            Return 1;
        }else{
            Return 0;
        }
    }



    /*验证 验证身份证号码*/
    function checkCard($str)
    {
        $preg_card='/^\d{15}$)|(^\d{17}([0-9]|X)$/isu';
        if(preg_match($preg_card,$str)){
            Return 1;
        }else{
            Return 0;
        }
    }



    /*验证 验证银行卡号*/
    function checkBankCard($str)
    {
        $preg_card='/^\d{15}$)|(^\d{17}([0-9]|X)$/isu';
        if(preg_match($preg_card,$str)){
            Return 1;
        }else{
            Return 0;
        }
    }




    /*验证 验证QQ号码*/
    function checkQq($str)
    {
        $preg_QQ='/^\d{5,12}$/isu';
        if(preg_match($preg_QQ,$str)){
            Return 1;
        }else{
            Return 0;
        }
    }




    /*验证 微信号*/
    function checkWechat($str)
    {
        $preg_wechat='/^[_a-zA-Z0-9]{5,19}+$/isu';
        if(preg_match($preg_wechat,$str)){
            Return 1;
        }else{
            Return 0;
        }
    }


    /*验证 验证特殊符号(如需要验证其他字符，自行转义 "\X" 添加)*/
    function checkSpacial($str)
    {
        $preg_spacial="/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\/|\;|\\' | \`|\-|\=|\\\|\|/isu";
        if(preg_match($preg_spacial,$str)){
            Return 1;
        }else{
            Return 0;
        }
    }
     /***检查是否为正整数****/
    function isunsignedinteger($str)
    {
        $preg_spacial="/^d+$/";
        if(preg_match($preg_spacial,$str)){
            Return 1;
        }else{
            Return 0;
        }
    }
}