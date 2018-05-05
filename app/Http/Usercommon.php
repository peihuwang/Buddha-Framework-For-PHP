<?php
class Usercommon extends  Buddha_App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = strtolower(__CLASS__);
    }


    /**
     * @param $current_user_id            //当前用户ID
     * @param $NeedTypeId                 //访问当前用户页面需要的用户类型
     * @param $UserTale                   //用户表名称
     * @return mixed
     *
     */
    public function isUserJurisdiction($current_user_id,$needTypeId,$UserTale='user')
    {
        if(!Buddha_Atom_String::isValidString($current_user_id)
            or !Buddha_Atom_String::isValidString($needTypeId) ){
            $data['is_ok'] = 5;
            $data['is_msg'] = '参数错误！';
            return $data;
        }

        if($UserTale=='user')
        {
            $UserObj = new User();

            if($needTypeId==1)
            {
                $User = $UserObj->isHasAgentPrivilege($current_user_id);
            }elseif($needTypeId==2)
            {
                $User = $UserObj->isHasMerchantPrivilege($current_user_id);
            }elseif($needTypeId==3)
            {
                $User = $UserObj->isHasPartnerPrivilege($current_user_id);
            }elseif($needTypeId==4)
            {
                $User = $UserObj->isHasUserPrivilege($current_user_id);
            }

            if($User)
            {
                $data['is_ok'] = 1;
                $data['is_msg'] = '你拥有该权限!';
            }else{
                $data['is_ok'] = 0;
                $data['is_msg'] = '你没有该权限!';
            }
        }

        return $data;
    }


    /**
     * @param $photoalbumname       往哪一个相册中添加相册
     * @param $table_name           来源于哪一张相册名称的添加
     * @param $table_id             来源于哪一张相册ID的添加
     * @param $user_id              添加相册人的用户ID
     * @param $deltype              删除类型：0  是商家 ； 1 manage(总后台)
     * @return array|int|void
     * 删除相册里边对应$table_name,$table_id,的所有图片
     */
    public function photoalbumDel($photoalbumname,$table_name,$table_id,$user_id=0,$deltype=0)
    {
        $Db_Moregallery_Num = 0;

        if(!Buddha_Atom_String::isValidString($photoalbumname)){
            return 0;
        }

        if(!Buddha_Atom_String::isValidString($table_name)){
            return 0;
        }

        if(!Buddha_Atom_String::isValidString($table_id)){
            return 0;
        }

        if($photoalbumname == 'moregallery')
        {
            $MoregalleryObj = new Moregallery();
            $Db_Moregallery_Num = $MoregalleryObj->Moregallerydel($table_name,$table_id,$user_id,$deltype);

        }else if($photoalbumname == 'gallery')
        {
            $GalleryObj = new Gallery();
            $Db_Moregallery_Num = $GalleryObj->Gellerydel($table_name='supply',$table_id,$user_id,$deltype);
        }

        return $Db_Moregallery_Num;
    }



    /**
     * @param $photoalbumname       往哪一个相册中添加相册
     * @param $table_name           来源于哪一张相册名称的添加
     * @param $photoal_id           相册ID的
     * @param $user_id              添加相册人的用户ID
     * @return array|int|void
     * 删除相册里边对应$table_name,$photoal_id,的图片
     */
    public function photoalSinglebumDel($photoalbumname,$table_name,$photoal_id,$user_id=0)
    {

        if(!Buddha_Atom_String::isValidString($photoalbumname)
            OR !Buddha_Atom_String::isValidString($table_name)
            OR !Buddha_Atom_String::isValidString($photoal_id))
        {
            $data['is_ok'] = 5;
            $data['is_msg'] = '参数错误！';
            return $data;
        }

        $where = "id='{$photoal_id}' AND user_id='{$user_id}'";
        $filedarr = array('goods_thumb','goods_img','goods_large','sourcepic');

        if($photoalbumname == 'moregallery')
        {
            $where .= " AND tablename='{$table_name}'";

        }else if($photoalbumname == 'gallery')
        {

        }else if($photoalbumname == 'shop')
        {
            $filedarr = array('small','medium','large','sourcepic');

        }else if($photoalbumname == 'shop' or $photoalbumname == 'property')
        {
            $filedarr = array('small','medium','large','sourcepic');
            $where = "id='{$photoal_id}'";
        }

        $Db_Table = $this->db->getSingleFiledValues($filedarr,$photoalbumname,$where);

        if(!Buddha_Atom_Array::isValidArray($Db_Table))
        {
            $data['is_ok'] = 4;
            $data['is_msg'] = 'id不存在！';
            return $data;
        }

        if($photoalbumname == 'moregallery' OR $photoalbumname == 'gallery')
        {
            if (Buddha_Atom_String::isValidString($Db_Table['goods_thumb']))
            {
                @unlink(PATH_ROOT . $Db_Table['goods_thumb']);
            }

            if (Buddha_Atom_String::isValidString($Db_Table['goods_img']))
            {
                @unlink(PATH_ROOT . $Db_Table['goods_img']);
            }

            if (Buddha_Atom_String::isValidString($Db_Table['goods_large']))
            {
                @unlink(PATH_ROOT . $Db_Table['goods_large']);
            }

        }else if($photoalbumname == 'shop' or $photoalbumname == 'property')
        {

            if (Buddha_Atom_String::isValidString($Db_Table['small']))
            {
                @unlink(PATH_ROOT . $Db_Table['small']);
            }

            if (Buddha_Atom_String::isValidString($Db_Table['medium']))
            {
                @unlink(PATH_ROOT . $Db_Table['medium']);
            }
            if (Buddha_Atom_String::isValidString($Db_Table['large']))
            {
                @unlink(PATH_ROOT . $Db_Table['large']);
            }
        }

        if (Buddha_Atom_String::isValidString($Db_Table['sourcepic']))
        {
            @unlink(PATH_ROOT . $Db_Table['sourcepic']);
        }


        $Db_Table_NUm = $this->db->delRecords($photoalbumname,$where);

        if(!Buddha_Atom_String::isValidString($Db_Table_NUm))
        {
            $data['is_ok'] = 0;
            $data['is_msg'] = '图片删除失败！';
            return $data;
        }else
        {
            $data['is_ok'] = 1;
            $data['is_msg'] = '图片删除成功！';
            return $data;
        }

    }




    /**
     * @param $photoalbumname       往哪一个相册中添加相册
     * @param $table_name           来源于哪一张相册名称的添加
     * @param $table_id             来源于哪一张相册ID的添加
     * @param $img                  相册数组
     * @param $shop_id              属于哪一个店铺下的店铺ID
     * @param $user_id              添加相册人的用户ID
     * @param string $webfield      该图片的代表字段默认为 file
     * @return array|int|void
     * 往相册里边添加图片
     */
    public function photoalbumAdd($photoalbumname,$table_name,$table_id,$img,$shop_id,$user_id=0,$webfield='file')
    {

        if(!Buddha_Atom_String::isValidString($photoalbumname)){
            return 0;
        }

        if(!Buddha_Atom_String::isValidString($table_name)){
            return 0;
        }

        if(!Buddha_Atom_String::isValidString($table_id)){
            return 0;
        }

        if(!isset($img)){//检测变量是否设置，并且不是 NULL。(因为它有可能是图片数组或base64字符串)
            return 0;
        }

        if(!Buddha_Atom_String::isValidString($shop_id)){
            return 0;
        }

        $Db_Moregallery_idarr = array();


        $JsonimageObj = new Jsonimage();
        /*判断图片数组是否是Json*/
        if(Buddha_Atom_String::isJson($img)){
            $img = json_decode($img);
        }

        /* 判断图片是不是格式正确 应该图片传数组 遍历图片数组 确保每个图片格式都正确*/
        $JsonimageObj->errorDieImageFromUpload($img);

        if(!Buddha_Atom_Array::isValidArray($img)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 20000047, '相册不能为空！');

        }

        if($photoalbumname == 'moregallery')
        {
            $MoregalleryObj = new Moregallery();
            $Db_Moregallery_idarr = $MoregalleryObj->Moregalleryadd($img,$table_name,$table_id,$shop_id,$user_id,$webfield);
        }

        return $Db_Moregallery_idarr;
    }



    /**
     * @param $table_name          来源于哪一张表
     * @param $table_id            来源于哪一张表ID
     * @param $buddhastatus        上下架状态
     * @return int
     * 商家：  上、下架  信息
     */
    public function businessshelf($table_name,$table_id,$user_id)
    {


        $CommonObj = new Common();
        $MysqlplusObj = new Mysqlplus();

        $returndata['buttonname'] = '';

        if(!Buddha_Atom_String::isValidString($table_name)
            AND !Buddha_Atom_String::isValidString($table_id)
            AND !Buddha_Atom_String::isValidString($user_id)
        )
        {
            $returndata['is_ok'] = 9;
            $returndata['is_msg'] = '参数错误！';
            return $returndata;
        }

        $UserObj = new User();
        if(!$UserObj->isHasMerchantPrivilege($user_id)){//不是商家
            $returndata['is_ok'] = 10;
            $returndata['is_msg'] = '非法入侵！';
        }



        if(!$MysqlplusObj->isValidTable($table_name))
        {
            $returndata['is_ok'] = 9;
            $returndata['is_msg'] = '参数错误！';
            return $returndata;
        }




        $Db_Table = $this->db->getSingleFiledValues(array('buddhastatus'),$table_name,"id='{$table_id}'");
        $shelf = $Db_Table['buddhastatus'];

        if($shelf == 0)  //当前状态$shelf == 0为上架
        {
            //将要下架
            $data['buddhastatus'] =1 ;
            $returndata['buttonname'] = '上架';//下架成功后按钮的名称
            $title = '下架';//当前即将要处理的动作
        }else if($shelf == 1)//当前状态$shelf == 1为 下架
        {
            //将要 上架
            $data['buddhastatus'] = 0 ;
            $returndata['buttonname'] = '下架';//上架成功后按钮的名称
            $title = '上架';//当前即将要处理的动作
        }

        $where = "id='{$table_id}' AND user_id='{$user_id}'";
        $Db_Table = $this->db->getSingleFiledValues(array('isdel','is_sure','remarks'),$table_name,$where);

        if($Db_Table['is_sure']==0)
        {
            $returndata['is_ok'] = 4;
            $returndata['is_msg'] = '尊敬的用户：该信息正在审核中，不能！'.$title;
            return $returndata;
        }elseif($Db_Table['is_sure']==4)
        {
            $returndata['is_ok'] = 3;
            $returndata['is_msg'] = "尊敬的用户：由于你发布的信息{$Db_Table['remarks']},请及时更改提交审核后再{$title}吧！";
            return $returndata;
        }


        if($Db_Table['isdel']==1)
        {
            $returndata['is_ok'] = 8;
            $returndata['is_msg'] = '已经被删除了！';
            return $returndata;
        }else if($Db_Table['isdel']==4)
        {
            $returndata['is_ok'] = 7;
            $returndata['is_msg'] = "尊敬的用户：由于你发布的信息严重违反了平台的守则,该信息已被代理商下架，请及时更改提交审核后再{$title}吧！";
            return $returndata;
        }elseif($Db_Table['isdel']==7)
        {
            $returndata['is_ok'] = 6;
            $returndata['is_msg'] = "尊敬的用户：由于你自己已经把该信息发布的店铺给停用了,所以无法{$title}，请开启该店铺后再{$title}吧！";;
            return $returndata;
        }elseif($Db_Table['isdel']==8)
        {
            $returndata['is_ok'] = 5;
            $returndata['is_msg'] = "尊敬的用户：由于你发布的信息严重违反了平台的守则,该店铺已被代理商下架，请及时更改提交审核后再{$title}吧！";
            return $returndata;
        }

        $Db_Table_Num = $this->db->updateRecords($data,$table_name,$where);

        if($Db_Table_Num)
        {
            $returndata['is_ok'] = 1;
            $returndata['is_msg'] = "{$title}成功！";
            return $returndata;
        }else{
            $returndata['is_ok'] = 0;
            $returndata['is_msg'] = "{$title}失败！";
            return $returndata;
        }
    }




    /**
     * @param $shop_id              该信息所属店铺ID
     * @param $Agents_user_id       代理商ID
     * @return int
     *  判断：当前信息是否属于当前代理商
     */
    public function isOwnerBelongToAgentByLeve3($shop_id,$Agents_user_id)
    {

        if($shop_id<1 ){
            return 0;
        }
        $UserObj = new User();
        $ShopObj = new Shop();

        $Db_Shop = $ShopObj->getSingleFiledValues(array('level3'),"id='{$shop_id}'");//原因是：因为存在异地发布

        $Db_user_Num = $UserObj->countRecords("id='{$Agents_user_id}' AND level3='{$Db_Shop['level3']}'");//原因是：因为存在异地发布

        if($Db_user_Num)
        {
            return 1;
        }else{
            return 0;
        }

    }



    /**
     * @param $table_name       来源于哪一张表
     * @param $table_id         来源于哪一张表ID
     * @param $isdel            上下架状态
     * @param $user_id          代理商ID
     * @return mixed
     * 代理商：  上、下架  信息
     */
    public function agentsshelf($table_name,$table_id,$isdel,$agents_user_id)
    {
        $UsercommonObj = new Usercommon();
        $MysqlplusObj = new Mysqlplus();

        $UserObj = new User();
        if(!$UserObj->isHasAgentPrivilege($agents_user_id)){//不是代理商
            $returndata['is_ok'] = 10;
            $returndata['is_msg'] = '非法入侵！';
        }

        $returndata['buttonname'] = '';

        if(!Buddha_Atom_String::isValidString($table_name)
            AND !Buddha_Atom_String::isValidString($table_id)
            AND !Buddha_Atom_String::isValidString($agents_user_id)
            AND !Buddha_Atom_String::isValidString($isdel))
        {
            $returndata['is_ok'] = 9;
            $returndata['is_msg'] = '参数错误！';
            return $returndata;
        }

        if(!$MysqlplusObj->isValidTable($table_name))
        {
            $returndata['is_ok'] = 9;
            $returndata['is_msg'] = '参数错误！';
            return $returndata;
        }



        if($isdel == 0)  //当前状态$shelf == 0为上架
        {
            //将要下架
            $data['isdel'] =4 ;
            $returndata['buttonname'] = '上架';//下架成功后按钮的名称
            $title = '下架';//当前即将要处理的动作名称
        }else if($isdel == 4)//当前状态$shelf == 1为 下架
        {
            //将要 上架
            $data['isdel'] = 0 ;
            $returndata['buttonname'] = '下架';//上架成功后按钮的名称
            $title = '上架';//当前即将要处理的动作名称
        }

        $where = "id='{$table_id}'";
        $Db_Table = $this->db->getSingleFiledValues(array('isdel','is_sure','remarks','shop_id'),$table_name,$where);

        if(!$UsercommonObj->isOwnerBelongToAgentByLeve3($Db_Table['shop_id'],$agents_user_id))
        {
            $returndata['is_ok'] = 3;
            $returndata['is_msg'] = '该信息不属于当前代理商，不能！'.$title;
            return $returndata;
        }

        if($Db_Table['is_sure']==0)
        {
            $returndata['is_ok'] = 4;
            $returndata['is_msg'] = "尊敬的用户：该信息还未审核，不能{$title}!";
            return $returndata;
        }elseif($Db_Table['is_sure']==4)
        {
            $returndata['is_ok'] = 3;
            $returndata['is_msg'] = "尊敬的用户：该信息未通过审核,不能{$title}！";
            return $returndata;
        }

        if($Db_Table['isdel']==1)
        {
            $returndata['is_ok'] = 8;
            $returndata['is_msg'] = '已经被删除了！';
            return $returndata;
        }elseif($Db_Table['isdel']==7)
        {
            $returndata['is_ok'] = 6;
            $returndata['is_msg'] = "商家自己停用店铺,所以无法{$title}";;
            return $returndata;
        }elseif($Db_Table['isdel']==8)
        {
            $returndata['is_ok'] = 5;
            $returndata['is_msg'] = "该店铺已被代理商下架，所以无法{$title}！";
            return $returndata;
        }

        $Db_Table_Num = $this->db->updateRecords($data,$table_name,$where);

        if($Db_Table_Num)
        {
            $returndata['is_ok'] = 1;
            $returndata['is_msg'] = "{$title}成功！";
            return $returndata;
        }else{
            $returndata['is_ok'] = 0;
            $returndata['is_msg'] = "{$title}失败！";
            return $returndata;
        }
    }

    /**
     * @param $isdel  上架下架：代表字段
     * @return string
     * 代理商物业名称： 停 用、启 用：字符串
     */
    public function agentsPropertystr($buddhastatus)
    {

        if($buddhastatus==0){
            $state='禁 用';
        }elseif($buddhastatus==1){
            $state='启 用';
        }else{
            $state='';
        }
        return $state;
    }



    /**
     * @param $isdel  上架下架：代表字段
     * @return string
     * 代理商： 上架、下架：字符串
     */
    public function agentsshelfstr($isdel)
    {

        if($isdel==4)
        {
            $state='上 架';
        }else if($isdel==0)
        {
            $state='下 架 ';
        }else{
            $state='异常';
        }
        return $state;
    }



    /**
     * @param $is_sure 审核状态：代表字段
     * @return string
     * 代理商： 审核状态：字符串
     */
    public function agentsissure($is_sure)
    {
        $sure = '';
        if($is_sure==0){
            $sure='not';
        }elseif($is_sure==4){
            $sure='no';
        }elseif($is_sure==1){
            $sure='yes';
        }else{
            $sure='';
        }

        return $sure;
    }



    /**
     * @param $is_sure 审核状态：代表字段
     * @return string
     * 代理商： 审核状态
     */
    public function agentsisdel($table_name,$table_id)
    {
        $UsercommonObj = new Usercommon();

        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());

        $Db_Table = $this->db->getSingleFiledValues(array('isdel'),$table_name,"id='{$table_id}'");

        $Db_Usercommon = $UsercommonObj->agentsshelf($table_name,$table_id,$Db_Table['isdel'],$uid);

        if($Db_Usercommon['is_ok']==1)
        {
            $is_ok='true';
        }else{
            $is_ok='false';
        }

        $state = array('id'=>$table_id,'state'=>$Db_Usercommon['buttonname']);
        $datas['isok'] = $is_ok;
        $datas['is_msg'] = $Db_Usercommon['is_msg'];
        $datas['data'] = $state;

        return $datas;
    }



    /**
     * @param $isdel  上架下架：代表字段
     * @return string
     * 商家： 上架、下架：字符串
     */
    public function businessissurestr($issure)
    {

        if($issure==0)
        {
            $issureimg='checked';//审核中
        }elseif($issure==4)
        {
            $issureimg='fail';//未通过
        }elseif($issure==1)
        {
            $issureimg='pass';//已通过
        }else{
            $issureimg='';
        }

        return $issureimg;
    }



    /**
     * @param $isdel  上架下架：代表字段
     * @return string
     * 商家： 停 用、启 用：字符串
     */
    public function businessstatestr($state)
    {

        if($state==0){
            $state='停 用';
        }elseif($state==1){
            $state='启 用';
        }else{
            $state='';
        }
        return $state;
    }


    /**
     * @param $shop_id
     * @param $user_id
     * @return int
     * 商家：删除店铺及店铺下的信息
     */
    public function businessDelShopAndBelongByShopid($shop_id,$user_id)
    {

        $shop_id = (int)$shop_id;
        $user_id = (int)$user_id;

        $CommonObj = new Common();
        $UserObj = new User();

        if(!$UserObj->isHasMerchantPrivilege($user_id)){//不是商家
            return 0;
        }

        if(!$CommonObj->isToUserByTablenameAndTableid('shop',$shop_id,$user_id)){
            return 0;
        }

        $audittable = $CommonObj->audittable();

        if(!Buddha_Atom_Array::isValidArray($audittable))
        {
            return 0;
        }

        //删除店铺的
        foreach ($audittable as $k=>$v)
        {
            if($v['name']=='shop' ){
                unset($audittable[$k]);
            }
            if(!Buddha_Atom_String::isValidString($v['name'] or !Buddha_Atom_String::isValidString($v['chinesename']))){
                unset($audittable[$k]);
            }
        }

        $Table_where = "shop_id='{$shop_id}' AND user_id='{$user_id}'";

        $UsercommonObj = new Usercommon();

        foreach ($audittable as $kk=>$vv)
        {
            $Db_Table_num = $this->db->countRecords($vv['name'],$Table_where);

            if(Buddha_Atom_String::isValidString($Db_Table_num))
            {
                $Db_Table = $this->db->getFiledValues(array('id'),$vv['name'],$Table_where);
                $idstr = '';
                foreach ($Db_Table as $k=>$v)
                {
                    if($v['name']=='supply')
                    {
                    $photoalbumname = 'gallery';
                    }else{
                        $photoalbumname = 'moregallery';
                    }
//                    $idstr .= $v['id'] . ',';
                    $UsercommonObj->photoalbumDel($photoalbumname,$vv['name'],$v['id'],$user_id);
                }
//                $idstr = rtrim($idstr, ',');
//                $Table_where .= " AND id IN ({$idstr})";
//                $Db_Table_delnum[] =  $this->db->delRecords($v['name'], $Table_where);
            }
        }

        $Db_Shop_num =  $this->db->delRecords('shop', "id='{$shop_id}' AND user_id='{$user_id}'");

        return $Db_Shop_num;
    }




    /**
     * @param $shop_id
     * @param $user_id
     * @return int
     * 平台：删除店铺及店铺下的信息
     */
    public function manageDelShopAndBelongByShopid($shop_id,$manage_user_id)
    {

        $shop_id = (int)$shop_id;
        $manage_user_id = (int)$manage_user_id;

        $CommonObj = new Common();
        $UserObj = new User();
        $ShopObj = new Shop();

        if(!$this->db->countRecords('member',"id='{$manage_user_id}' AND state=2")){//不是平台
            return 0;
        }

        if(!$CommonObj->isIdByTablenameAndTableid('shop',$shop_id)){
            return 0;
        }

        $audittable = $CommonObj->audittable();

        if(!Buddha_Atom_Array::isValidArray($audittable))
        {
            return 0;
        }

        $Db_shop = $ShopObj->getSingleFiledValues(array('user_id'),"id='{$shop_id}'");
        $shop_user_id = $Db_shop['user_id'];
        //删除店铺的
        foreach ($audittable as $k=>$v)
        {
            if($v['name']=='shop' ){
                unset($audittable[$k]);
            }
            if(!Buddha_Atom_String::isValidString($v['name'] or !Buddha_Atom_String::isValidString($v['chinesename']))){
                unset($audittable[$k]);
            }
        }

        $Table_where = "shop_id='{$shop_id}'";

        $UsercommonObj = new Usercommon();

        foreach ($audittable as $kk=>$vv)
        {
            $Db_Table_num = $this->db->countRecords($vv['name'],$Table_where);
            if(Buddha_Atom_String::isValidString($Db_Table_num))
            {
                $Db_Table = $this->db->getFiledValues(array('id'),$vv['name'],$Table_where);
                $idstr = '';
                foreach ($Db_Table as $k=>$v)
                {
                    if($vv['name']=='supply')
                    {
                        $photoalbumname = 'gallery';
                    }else{
                        $photoalbumname = 'moregallery';
                    }

//                    $idstr .= $v['id'] . ',';
                    $UsercommonObj->photoalbumDel($photoalbumname,$vv['name'],$v['id'],$shop_user_id);

                }
//                $idstr = rtrim($idstr, ',');
//                $Table_where .= " AND id IN ({$idstr})";
//                $Db_Table_delnum[] =  $this->db->delRecords($v['name'], $Table_where);
            }
        }

        $Db_Shop_num =  $this->db->delRecords('shop', "id='{$shop_id}'");

        return $Db_Shop_num;
    }



    /**
     * @param  $table_name           来源于哪一张相册名称的添加
     * @param  $table_id             来源于哪一张相册ID的添加
     * @param  $user_id              添加相册人的用户ID
     * @return int|void
     * 往Moregallery相册中  添加图片
     *  特别说明 setFirstGalleryImgToSupply 这个方法需要每一个都要创建
     */

    public function Moregallerydel($table_name,$table_id,$user_id=0)
    {
        if (!Buddha_Atom_String::isValidString($table_name)) {
            return 0;
        }

        if (!Buddha_Atom_String::isValidString($table_id)) {
            return 0;
        }

        $MoregalleryObj = new Moregallery();
        $CommonObj = new Common();

        $where = "tablename='{$table_name}' AND goods_id='{$table_id}'";

        $Moregallery_where = $where . " AND (user_id='{$user_id}' OR user_id=0)";

        $Db_Moregallery = $MoregalleryObj->getFiledValues(array('id', 'goods_thumb', 'goods_img', 'goods_large', 'sourcepic'), $Moregallery_where);



        if(Buddha_Atom_Array::isValidArray($Db_Moregallery))
        {
            $idstr = '';

            foreach ($Db_Moregallery as $k => $v)
            {
                if (Buddha_Atom_String::isValidString($v['goods_thumb']))
                {
                    @unlink(PATH_ROOT . $v['goods_thumb']);
                }

                if (Buddha_Atom_String::isValidString($v['goods_img']))
                {
                    @unlink(PATH_ROOT . $v['goods_img']);
                }

                if (Buddha_Atom_String::isValidString($v['goods_large']))
                {
                    @unlink(PATH_ROOT . $v['goods_large']);
                }

                if (Buddha_Atom_String::isValidString($v['sourcepic']))
                {
                    @unlink(PATH_ROOT . $v['sourcepic']);
                }

                $idstr .= $v['id'] . ',';
            }

            @unlink(PATH_ROOT."{$CommonObj->photoalDirectory}{$table_name}/{$table_id}");

            $idstr = rtrim($idstr, ',');

            $Moregallery_where .= " AND id IN ({$idstr})";

            $Db_Moregallery_Num =  $this->db->delRecords('moregallery', $Moregallery_where);

        }

        $Db_Table_Num = $this->db->delRecords($table_name, "id='{$table_id}' AND user_id='{$user_id}'");

        return $Db_Table_Num;

    }




}