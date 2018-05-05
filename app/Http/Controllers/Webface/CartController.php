<?php

/**
 * Class CartController
 */
class CartController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

        //权限
        $webface_access_token = Buddha_Http_Input::getParameter('webface_access_token');
        if ($webface_access_token == '') {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444002, 'webface_access_token必填');
        }

        $ApptokenObj = new Apptoken();
        $num = $ApptokenObj->getTokenNum($webface_access_token);
        if ($num == 0) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444003, 'webface_access_token不正确请从新获取');
        }


    }

    /**
     * 购物车添加
     */
    public function add()
    {

        if (Buddha_Http_Input::checkParameter(array('usertoken','product_id','product_table','goods_number'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $MysqlplusObj = new Mysqlplus();
        $UserObj = new User();
        $CartObj = new Cart();

        $product_table = Buddha_Http_Input::getParameter('product_table');
        $product_id = Buddha_Http_Input::getParameter('product_id');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $goods_number = (int)Buddha_Http_Input::getParameter('goods_number')?Buddha_Http_Input::getParameter('goods_number'):1;
        $rec_type = (int)Buddha_Http_Input::getParameter('rec_type')?Buddha_Http_Input::getParameter('rec_type'):0;


        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken','realname','mobile','username','level1','level2','level3');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        if(!$MysqlplusObj->isValidTable($product_table)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000001, 'product_table不存在');
        }

        $num = $this->db->countRecords($product_table,"id='{$product_id}'");
        if($num<1){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 70000002, 'product_id不正确');
        }

        $Db_Good= array();
        if($product_table=='supply'){
            $Db_Good = $CartObj->getGoodsArr($product_table,$product_id,$user_id,$goods_number);
        }


        if(!$CartObj->isExistGoods($product_table,$product_id,$user_id)){
            $CartObj->add($Db_Good);
        }else{
           $CartObj->addOneGoodsToCart($product_table,$product_id,$user_id,$goods_number);
        }



        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['real_goods_count'] = $CartObj->getCount($user_id);
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '购物车添加成功');

    }


    /**
     * 购物车统计：获取购物车概况-数量,总价格,有无物品
     */
    public function survey()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $CartObj = new Cart();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');


        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $DB_Cart = $CartObj->getCartArr($user_id);
        $Total_Arr = $CartObj->getTotalArr($DB_Cart);


        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['market_total_price'] = $Total_Arr['market_total_price'];
        $jsondata['goods_total_price'] = $Total_Arr['goods_total_price'];
        $jsondata['market_total_price_formated'] =$CartObj->getPriceFormatStr($Total_Arr['market_total_price'],false);
        $jsondata['goods_total_price_formated'] =  $CartObj->getPriceFormatStr($Total_Arr['goods_total_price'],false);


        $jsondata['real_total_count'] = $Total_Arr['real_total_count'];


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '获取购物车概况-数量,总价格,有无物品');
    }


    /**
     * 购物车-产品列表
     */
    public function more()
    {
        $host=Buddha::$buddha_array['host'];
        if (Buddha_Http_Input::checkParameter(array('usertoken','b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $CartObj = new Cart();
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        $b_display = (int)Buddha_Http_Input::getParameter('b_display')?(int)Buddha_Http_Input::getParameter('b_display'):2;


        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $DB_Cart = $CartObj->getCartArr($user_id);

        /*查询购物车对应产品的图片:如果产品被删除图片返回空*/
        foreach ($DB_Cart as $k=>$v){
            if($b_display==2){
                $tableFiled=array('goods_thumb as img');
            }elseif($b_display==1){
                $tableFiled=array('goods_img as img');
            }

            $Db_Goodstable=$this->db->getSingleFiledValues($tableFiled,$v['goods_table'],"id='{$v['goods_id']}'");
            if(Buddha_Atom_Array::isValidArray($Db_Goodstable)){
                $DB_Cart[$k]['api_img']=$host.$Db_Goodstable['img'];
            }else{
                $DB_Cart[$k]['api_img']='';
            }
        }


        $Total_Arr = $CartObj->getTotalArr($DB_Cart);

        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;


        $jsondata['list'] = $CartObj->getGoodsListArr($DB_Cart) ;


        $jsondata['market_total_price'] = $Total_Arr['market_total_price'];
        $jsondata['goods_total_price'] = $Total_Arr['goods_total_price'];
        $jsondata['market_total_price_formated'] =$CartObj->getPriceFormatStr($Total_Arr['market_total_price'],false);
        $jsondata['goods_total_price_formated'] =  $CartObj->getPriceFormatStr($Total_Arr['goods_total_price'],false);

        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '购物车-产品列表');

    }

    /**
     *购物车数量更新
     */
    public function update()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken','cart_id','final_number'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $CartObj = new Cart();

        $cart_id = Buddha_Http_Input::getParameter('cart_id');
        $final_number = Buddha_Http_Input::getParameter('final_number');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');


        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if(!$CartObj->isValidCartGoods($user_id,$cart_id)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000001, '此产品不是当前用户购物车里的里的产品');

        }

        $CartObj->updateUserCartByCartId($user_id,$cart_id,$final_number);

        $DB_Cart = $CartObj->getCartArr($user_id);
        $Total_Arr = $CartObj->getTotalArr($DB_Cart);


        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['cart_id'] = $cart_id;
        $jsondata['goods_number'] = $final_number;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['market_total_price'] = $Total_Arr['market_total_price'];
        $jsondata['goods_total_price'] = $Total_Arr['goods_total_price'];
        $jsondata['market_total_price_formated'] =$CartObj->getPriceFormatStr($Total_Arr['market_total_price'],false);
        $jsondata['goods_total_price_formated'] =  $CartObj->getPriceFormatStr($Total_Arr['goods_total_price'],false);


        $jsondata['real_total_count'] = $Total_Arr['real_total_count'];


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '购物车数量更新');

    }

    /**
     * 购物车产品移除
     */

    public function remove()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken','cart_idarr'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $CartObj = new Cart();

        $cart_idarr = Buddha_Http_Input::getParameter('cart_idarr');
        $usertoken = Buddha_Http_Input::getParameter('usertoken');
        if(Buddha_Atom_String::isJson($cart_idarr)){
            $cart_idarr = json_decode($cart_idarr);
        }
        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];


        if(Buddha_Atom_Array::isValidArray($cart_idarr)){

            foreach($cart_idarr as $k=>$v){

                $cart_id = $v;

                if(!$CartObj->isValidCartGoods($user_id,$cart_id)){
                    Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 90000001, '此产品不是当前用户购物车里的里的产品');
                }
            }

        }



        $CartObj->removeUserCartGoods($user_id,$cart_idarr);

        $DB_Cart = $CartObj->getCartArr($user_id);
        $Total_Arr = $CartObj->getTotalArr($DB_Cart);


        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['market_total_price'] = $Total_Arr['market_total_price'];
        $jsondata['goods_total_price'] = $Total_Arr['goods_total_price'];
        $jsondata['market_total_price_formated'] =$CartObj->getPriceFormatStr($Total_Arr['market_total_price'],false);
        $jsondata['goods_total_price_formated'] =  $CartObj->getPriceFormatStr($Total_Arr['goods_total_price'],false);


        $jsondata['real_total_count'] = $Total_Arr['real_total_count'];


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '购物车产品移除');

    }

    /**
     * 购物车清空
     */
    public function clean()
    {
        if (Buddha_Http_Input::checkParameter(array('usertoken'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $UserObj = new User();
        $CartObj = new Cart();

        $usertoken = Buddha_Http_Input::getParameter('usertoken');


        $UserObj->checkUserToken($usertoken,'/webface/?Services='.$_REQUEST['Services'],20000000,'usertoken不正确请从新获取');
        $fieldsarray= array('id','usertoken');
        $Db_User = $UserObj->getSingleFiledValues($fieldsarray," isdel=0 and usertoken='{$usertoken}' ");
        $user_id = $Db_User['id'];

        $CartObj->cleanUserCartGoods($user_id);

        $DB_Cart = $CartObj->getCartArr($user_id);
        $Total_Arr = $CartObj->getTotalArr($DB_Cart);

        $jsondata = array();
        $jsondata['user_id'] = $user_id;
        $jsondata['usertoken'] = $usertoken;
        $jsondata['market_total_price'] = $Total_Arr['market_total_price'];
        $jsondata['goods_total_price'] = $Total_Arr['goods_total_price'];
        $jsondata['market_total_price_formated'] =$CartObj->getPriceFormatStr($Total_Arr['market_total_price'],false);
        $jsondata['goods_total_price_formated'] =  $CartObj->getPriceFormatStr($Total_Arr['goods_total_price'],false);


        $jsondata['real_total_count'] = $Total_Arr['real_total_count'];


        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '购物车清空');

    }




}