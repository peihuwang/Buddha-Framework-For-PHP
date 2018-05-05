<?php

/**
 * Class AjaxregionController
 */
class AjaxregionController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

        //权限
        $webface_access_token = Buddha_Http_Input::getParameter('webface_access_token');
        if($webface_access_token==''){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444002,'webface_access_token必填');
        }

        $ApptokenObj = new Apptoken();
        $num =$ApptokenObj->getTokenNum($webface_access_token);
        if($num==0){
            Buddha_Http_Output::makeWebfaceJson(null,'/webface/?Services='.$_REQUEST['Services'],4444003,'webface_access_token不正确请从新获取');
        }

    }
    /**AJAX操作-地区省市区筛选***/
    public function getBelongFromFatherId(){
        $father = (int)Buddha_Http_Input::getParameter('father')?(int)Buddha_Http_Input::getParameter('father'):1;
        $sql = "SELECT id AS regin_id,name,father,fullname,pinyin,lat,lng,level FROM {$this->prefix}region WHERE father='{$father}' and isdel=0";
        $Db_Region = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $jsondata= array();
        $jsondata['regionall'] = $Db_Region;
        $jsondata['regionhot'] = array();
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'省市区列表');
    }

    /**AJAX操作-根据店铺获取店铺下的数据***/
    public function getBelongShopInformationByShopid()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken','table_name','shop_id'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $CommonObj = new Common();

        $usertoken = Buddha_Http_Input::getParameter('usertoken') ? Buddha_Http_Input::getParameter('usertoken') : 0;

        $UserObj->checkUserToken($usertoken, '/webface/?Services=' . $_REQUEST['Services'], 20000000, 'usertoken不正确请从新获取');

        $fieldsarray = array('id', 'usertoken', 'logo', 'realname', 'mobile', 'username', 'level3','tel');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray, " isdel=0 AND usertoken='{$usertoken}' ");
        if(!Buddha_Atom_Array::isValidArray($Db_User)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000051, '你还未登陆，请登陆后再操作!');
        }
        $user_id = $Db_User['id'];

        $table_name = Buddha_Http_Input::getParameter('table_name') ? Buddha_Http_Input::getParameter('table_name') : '';
        $api_keyword = Buddha_Http_Input::getParameter('api_keyword') ? Buddha_Http_Input::getParameter('api_keyword') : '';
        $shop_id = (int)Buddha_Http_Input::getParameter('shop_id')?(int)Buddha_Http_Input::getParameter('shop_id'):0;

        if(!$CommonObj->isIdByTablenameAndTableid('shop',$shop_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 80000009, '店铺内码ID无效！');
        }

         $where="shop_id={$shop_id} AND isdel=0 AND user_id='{$user_id}'";

        /*注意：这里只是做了产品的，其余的根据需求自己添加*/
        if($table_name=='demand' OR $table_name=='singleinformation' OR $table_name=='activity'){//需求、单页信息
            $filed=array('id','name');
            if(Buddha_Atom_String::isValidString($api_keyword)){
                $where.=" name LIKE %{$api_keyword}%";
            }


        }elseif($table_name=='supply'){//产品表"
            $filed=array('id','goods_name as name');
            if(Buddha_Atom_String::isValidString($api_keyword)){
                $where.=" goods_name LIKE %{$api_keyword}%";
            }

        }else{
            $name=$table_name.'_name';
            $filed=array('id',"'{$name}' as name");
            if(Buddha_Atom_String::isValidString($api_keyword)){
                $where.=" {$name} LIKE %{$api_keyword}%";
            }

        }

        $Db_Tabe=$this->db->getFiledValues($filed, $table_name,$where);
        $jsondata=array();
        if(Buddha_Atom_Array::isValidArray($Db_Tabe)){
            foreach ($Db_Tabe as $k=>$v){
                if($k==0){
                    $Db_Tabe[$k]['select']=1;
                }else{
                    $Db_Tabe[$k]['select']=0;
                }
            }
            $jsondata = $Db_Tabe;
        }
        Buddha_Http_Output::makeWebfaceJson($jsondata,'/webface/?Services='.$_REQUEST['Services'],0,'AJAX操作-根据店铺获取店铺下的数据');
    }

}