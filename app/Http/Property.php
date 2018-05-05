<?php
class Property extends  Buddha_App_Model
{

    protected $tablenamestr;
    protected $tablename;
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
        $this->tablenamestr='物业名称';
        $this->tablename='property';
    }

    public function setFirstGalleryImgToSupply($goods_id,$tablename)
    {
        $defaultgimages= $this->db->getSingleFiledValues('','moregallery',"goods_id={$goods_id} and tablename='{$tablename}' and isdel=0 order by isdefault,id ASC");
        $this->db->updateRecords(array('isdefault'=>'1'),'moregallery',"id='{$defaultgimages['id']}'");
        $dataImg=array();
        $dataImg ['small'] = $defaultgimages['goods_thumb'];
        $dataImg ['medium'] = $defaultgimages['goods_img'];
        $dataImg ['large'] =  $defaultgimages['goods_large'];
        $dataImg ['sourcepic'] =  $defaultgimages['sourcepic'];
        $num =$this->updateRecords($dataImg,"id={$goods_id}");
        return $num;
    }


    /**
     * @param $shop_id
     * @return mixed
     * 物业名称添加
     * 思路： 首先要排除 店铺性质为 1（沿街店铺）和5（生产制造） 的
     *       如果 $property_id
     *             1、存在，表示是选择下拉的 只需要添加物业名称店铺表
     *             2、不存在，表示是添加店铺者新加的
     *                  2.1、首先还是要检查 该新添加的物业名称 $property 是否存在
     *                           2.1.1、不存在  直接添加，并更新店铺列表的物业名称ID
     *                           2.1.2、存在  只需要添加物业名称店铺表
     */
    public function Propertyadd($shop_id)
    {
        $Db_Property_id = 0;
        $ShopObj = new Shop();
        $PropertyshopObj = new Propertyshop();//物业名称 店铺 列表
        $PropertyObj = new Property();//物业名称 列表
        //storetype   店铺性质; property_id  物业名称ID; property 物业名称

        $Db_Shop = $ShopObj->getSingleFiledValues(array('level0','level1','level2','level3','property','storetype','property_id'),"id='{$shop_id}'");

        if(Buddha_Atom_Array::isValidArray($Db_Shop))
        {
            $creatime = Buddha::$buddha_array['buddha_timestamp'];
            $creatimestr = Buddha::$buddha_array['buddha_timestr'];

            $Propertydata['createtime'] = $creatime;
            $Propertydata['createtimestr'] = $creatimestr;

            $Propertyshop['createtime'] = $creatime;
            $Propertyshop['createtimestr'] = $creatimestr;


            //$property_id存在
            if($Db_Shop['property_id']>0)
            {
                $Propertydata['name'] = $Db_Shop['property'];
                $Propertydata['level0'] = $Db_Shop['level0'];
                $Propertydata['level1'] = $Db_Shop['level1'];
                $Propertydata['level2'] = $Db_Shop['level2'];
                $Propertydata['level3'] = $Db_Shop['level3'];
                $Db_Property_id = $PropertyObj->add($Propertydata);//物业名称 店铺 列表

            }else{//$property_id 不存在

                if($Db_Shop['level3']>0  AND ($Db_Shop['storetype']!=1 AND $Db_Shop['storetype']!=5))
                {
                    $where = "name='{$Db_Shop['property']}' AND level3='{$Db_Shop['level3']}'";
                    $Db_Property_Num = $PropertyObj->countRecords($where);

                    if(!$Db_Property_Num)
                    {
                        $Propertydata['name'] = $Db_Shop['property'];
                        $Propertydata['level0'] = $Db_Shop['level0'];
                        $Propertydata['level1'] = $Db_Shop['level1'];
                        $Propertydata['level2'] = $Db_Shop['level2'];
                        $Propertydata['level3'] = $Db_Shop['level3'];

                        if(!Buddha_Atom_String::isValidString($Db_Shop['property']))
                        {
                            $Propertydata['buddhastatus'] = 1;
                        }else{
                            $Propertydata['buddhastatus'] = 0;
                        }

                        $Db_Property_id = $PropertyObj->add($Propertydata);

                    }else{

                        $Db_Property = $PropertyObj->getSingleFiledValues(array('id'),$where);
                        $Db_Property_id = $Db_Property['id'];
                    }

                    $Propertyshop['shop_id'] = $Db_Shop['id'];
                    $Propertyshop['property_id'] = $Db_Property_id;
                    $Db_Propertyshop_id = $PropertyshopObj->add($Propertyshop);

                    $Shop['property_id'] = $Db_Property_id;
                    $ShopObj->updateRecords($Shop,"id='{$shop_id}'");
                }else{

                    $Shop['property_id'] = 0;
                    $ShopObj->updateRecords($Shop,"id='{$shop_id}'");
                }
            }
        }


        return $Db_Property_id;
    }


    /**
     * @param $level3               该代理商的代理地区
     * @param $mergeid              合并ID
     * @param $mergeObjectsIdarr    被合并对象的ID数组
     * @param $user_id              //当前用户ID
     * @param $NeedTypeId           //访问当前用户页面需要的用户类型
     * @param $UserTale             //用户表名称
     * @return string
     * 代理商合并物业名称
     */
    public function PropertyMergeByIdarr($level3,$mergeid,$mergeObjectsIdarr,$user_id,$needTypeId,$UserTale='user')
    {
        if(!Buddha_Atom_String::isValidString($level3)
            OR !Buddha_Atom_String::isValidString($mergeid)
            OR !Buddha_Atom_String::isValidString($user_id)
            OR !Buddha_Atom_String::isValidString($needTypeId)
            OR !Buddha_Atom_Array::isValidArray($mergeObjectsIdarr))
        {
            $data['is_ok'] = 5;
            $data['is_msg'] = '参数错误！';
            return $data;
        }


        $ShopObj = new Shop();
        $CommonObj = new Common();
        $PropertyObj = new Property();//物业名称 列表
        $PropertyshopObj = new Propertyshop();//物业名称 店铺 列表
        $UsercommonObj = new Usercommon();

        if(!$UsercommonObj->isUserJurisdiction($user_id,$needTypeId,$UserTale='user')){
            $data['is_ok'] = 6;
            $data['is_msg'] = '非法进入！！';
            return $data;
        }

        $Db_Property = $PropertyObj->getSingleFiledValues(array('name')," level3='{$level3}' AND id='{$mergeid}'"); //查找合并的物业名称

        $mergeObjectsId_str = $CommonObj->getIdstrByIdarr($mergeObjectsIdarr);//将合并物业名称Id数组变成字符串

        $Propertyshop_where = " property_id in({$mergeObjectsId_str})";

        $Db_Propertyshop_Count = $PropertyshopObj->countRecords($Propertyshop_where); //查询要合并的店铺ID   根据  物业名称店铺列表 中的店铺ID  和 区县ID

        if( Buddha_Atom_String::isValidString($Db_Propertyshop_Count))
        {
            $Db_Propertyshop = $PropertyshopObj->getFiledValues(array('shop_id','id'),$Propertyshop_where); //查询要合并的店铺ID   根据  物业名称店铺列表 中的店铺ID  和 区县ID
            $Db_Propertyshop_Shopidstr = $CommonObj->getIdstrByIdarr($Db_Propertyshop,'id');//将物业名称店铺ID数组变成字符串

            $Db_shop_Shopidstr = $CommonObj->getIdstrByIdarr($Db_Propertyshop,'shop_id');//将物业名称店铺的店铺ID数组变成字符串

            $Propertyshop_data['property_id'] = $mergeid;// 组装 物业名称店铺 列表更新数据

            // 组装 店铺 的物业名称列表 更新数据
            $shop_data['property_id'] = $mergeid;
            $shop_data['property'] = $Db_Property['name'];

            //更新物业名称 店铺 列表
            $Db_Propertyshop_Num = $PropertyshopObj->updateRecords($Propertyshop_data,"id in($Db_Propertyshop_Shopidstr)");

            if(!Buddha_Atom_String::isValidString($Db_Propertyshop_Num))
            {
                $data['is_ok'] = 4;
                $data['is_msg'] = '合并失败！';
                return $data;
            }

            $Db_Shop_Num = $ShopObj->updateRecords($shop_data,"id in($Db_shop_Shopidstr)");


            if(!Buddha_Atom_String::isValidString($Db_Shop_Num))
            {
                $data['is_ok'] = 3;
                $data['is_msg'] = '合并失败！';
                return $data;
            }
        }

        $Property_where = " level3='{$level3}'";

        foreach ($mergeObjectsIdarr as $k=>$v)
        {
            $UsercommonObj->photoalSinglebumDel($this->tablename,$this->tablename,$v,$user_id);
        }


        $data['is_ok'] = 1;
        $data['is_msg'] = '合并成功！';
        return $data;


    }

    /**
     * @param $level1
     * @param $level2
     * @param $level3
     * @param $propertynmae    物业名称
     * @param $roadfullname    详细地址
     * @param $id    物业名称的ID  如果存在表示是编辑要排除本身
     * @return int
     * 根据$level3,$propertynmae,$roadfullname  判断物业名称是否已经存在了
     */
     public function isExistence($level1,$level2,$level3,$propertynmae,$roadfullname='',$propertyid=0)
     {

        $PropertyObj = new Property();

        if(!Buddha_Atom_String::isValidString($level3)
            or !Buddha_Atom_String::isValidString($propertynmae)
            or !Buddha_Atom_String::isValidString($roadfullname))
        {
            return 0;
        }

        $where = "name='{$propertynmae}' AND roadfullname='{$roadfullname}' AND level3='{$level3}' AND level2='{$level2}' AND level1='{$level1}'";

        if(Buddha_Atom_String::isValidString($propertyid))
        {
            $where .=" AND id!='{$propertyid}' ";
        }
        $Db_Property_num = $PropertyObj->debug()->countRecords($where);

         return $Db_Property_num;
     }


    /**
     * @param $propertyid
     * @param $user_id
     * @return int
     * 这个只有代理商权限的人才能操作(为了安全)
     */
    public function agentdisableuse($propertyid,$user_id)
    {
        $data['buttonname'] = '';
        $data['id'] = $propertyid;

        if(!Buddha_Atom_String::isValidString($propertyid) or !Buddha_Atom_String::isValidString($user_id))
        {
            $data['is_ok'] = 5;
            $data['is_msg'] = '参数错误';
            return $data;
        }

        $UserObj = new User();

        if(!$UserObj->countRecords("id='{$user_id}' AND groupid=2 AND isdel=0"))
        {
            $data['is_ok'] = 4;
            $data['is_msg'] = '你没有该权限';
            return $data;
        }

        $PropertyObj = new Property();

        $Db_Property = $PropertyObj->getSingleFiledValues(array('buddhastatus'),"id='{$propertyid}'");

        if(!Buddha_Atom_Array::isValidArray($Db_Property))
        {
            $data['is_ok'] = 3;
            $data['is_msg'] = '该数据不存在！';
            return $data;
        }

        if($Db_Property['buddhastatus']==0)//当前状态为:启用
        {
            //将:禁用
            $data['buttonname'] = '启 用';
            $u_data['buddhastatus']=1;
            $title = '禁用';
        }elseif($Db_Property['buddhastatus']==1)//当前状态为:禁用
        {
            //将:启用
            $data['buttonname'] = '禁 用';
            $u_data['buddhastatus']=0;
            $title = '启用';
        }

        $Db_Property_num  = $PropertyObj->edit($u_data,$propertyid);

        if($Db_Property_num)
        {
            $data['is_ok'] = 1;
            $data['is_msg'] = $title.'成功！';
        }else{
            $data['is_ok'] = 0;
            $data['is_msg'] = $title.'失败！';

        }

        return $data;
    }


    /**
     * @param string $level1
     * @param $level2
     * @param $level3
     * @param $id   哪一个默认选中
     *
     * @return mixed
     * 物业名称列表
     */
    public function propertylist($level1,$level2,$level3,$id=0)
    {
        if(!Buddha_Atom_String::isValidString($level1)
            OR !Buddha_Atom_String::isValidString($level2)
            OR !Buddha_Atom_String::isValidString($level3))
        {
            return array();
        }

        $PropertyObj = new Property();

        $where = " level1='{$level1}' AND level2='{$level2}' AND level3='{$level3}' AND buddhastatus=0";
        $order = ' ORDER BY id DESC';
        $Db_Property = $PropertyObj->getFiledValues(array('id','name'),$where.$order);

        if(!Buddha_Atom_Array::isValidArray($Db_Property))
        {
            return array();
        }

        foreach ($Db_Property as $k=>$v)
        {
            if(Buddha_Atom_String::isValidString($id))
            {
                if($v['id'] == $id){
                    $Db_Property[$k]['select'] = 1;
                }else{
                    $Db_Property[$k]['select'] = 0;
                }
            }else{
//                if($k == 0)
//                {
//                    $Db_Property[$k]['select'] = 1;
//                }else{
//                    $Db_Property[$k]['select'] = 0;
//                }
            }

        }

        return $Db_Property;
    }

    /**
     * @param int $property_id
     * @param string $property_name
     * @param $user_id
     * @param $shop_id
     * @param $level1   店铺选择的省市区id
     * @param $level2   店铺选择的省市区id
     * @param $level3   店铺选择的省市区id
     * @return int|mixed
     * 商家物业名称和ID返回
     */
    public function businesspropertyadd($property_id=0,$property_name='',$user_id,$shop_id,$level1,$level2,$level3)
    {
        if(!Buddha_Atom_String::isValidString($user_id)
            or (!Buddha_Atom_String::isValidString($property_id) AND !Buddha_Atom_String::isValidString($property_name) )
        )
        {
            return 0;
        }

        $creatime = Buddha::$buddha_array['buddha_timestamp'];
        $creatimestr = Buddha::$buddha_array['buddha_timestr'];


        if(!Buddha_Atom_String::isValidString($property_id) AND Buddha_Atom_String::isValidString($property_name))
        {
            //物业名称表
            $PropertyObj = new Property();
            if($PropertyObj->isExistence($level1,$level2,$level3,$property_name,'',$property_id))
            {
                $where = "name='{$property_name}'  AND level3='{$level3}' AND level2='{$level2}' AND level1='{$level1}'";
                $Db_Property_id = $PropertyObj->getSingleFiledValues(array('id'),$where);
                $property_id = $Db_Property_id['id'];

            }else{
                $PropertyObj = new Property();
                $data_Property['name'] = $property_name;
                $data_Property['level1'] = $level1;
                $data_Property['level2'] = $level2;
                $data_Property['level3'] = $level3;
                $data_Property['createtime'] = $creatime;
                $data_Property['createtimestr'] = $creatimestr;
                $property_id = $PropertyObj->add($data_Property);
            }
        }


        /**$property_id 存在表示是下拉选择**/
        if(Buddha_Atom_String::isValidString($property_id))
        {
            $PropertyObj = new Property();
            $Db_Property = $PropertyObj->getSingleFiledValues(array('name'),"id='{$property_id}'");
            $property_name = $Db_Property['name'];
        }


        //更新店铺物业名称
        $ShopObj = new Shop();
        $data_shop['property'] = $property_name;
        $data_shop['property_id'] = $property_id;
        $ShopObj->edit($data_shop,$shop_id);

        //店铺物业名称关联表
        $PropertyshopObj = new Propertyshop();
        $data_Propertyshop['shop_id'] = $shop_id;
        $data_Propertyshop['property_id'] = $property_id;
        $data_Propertyshop['createtime'] = $creatime;
        $data_Propertyshop['createtimestr'] = $creatimestr;
        $Db_Propertyshop = $PropertyshopObj->add($data_Propertyshop);

        return $Db_Propertyshop;
    }


}