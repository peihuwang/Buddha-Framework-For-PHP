<?php
class Cart extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    /**
     * 获取产品的缩略图
     * @param $product_table
     * @param $product_id
     * @return string
     */
    public function getProductImg($product_table,$product_id){
        $productimg = '';
        if($product_table=='supply'){

            $num = $this->db->countRecords("{$product_table}","id='{$product_id}'");
            if($num){
                $Db_Goods = $this->db->getSingleFiledValues (array('goods_thumb'), $product_table, "id='{$product_id}' " );
                $productimg = $Db_Goods['goods_thumb'];

            }

        }elseif($product_table=='goodsproduct'){
            $num = $this->db->countRecords("{$product_table}","id='{$product_id}'");
            if($num){
                $goods = $this->db->getSingleFiledValues(array('goods_id'),$product_table,"id='{$product_id}'");
                $Db_Goods = $this->db->getSingleFiledValues (array('goods_thumb'),"goods", "id='{$goods['goods_id']}' " );
                $productimg = $Db_Goods['goods_thumb'];

            }
        }

        return $productimg;

    }

    /**
     * 清空用户的购物车全部产品
     * @param $user_id
     */
    public function cleanUserCartGoods($user_id){
        $this->delRecords(" user_id='{$user_id}'");
    }
    /**
     * 批量删除用户购物车里的选中删除的产品
     * @param $user_id
     * @param $cart_idarr
     */

    public function removeUserCartGoods($user_id,$cart_idarr){
        $ids = implode ( ',', $cart_idarr);
        $this->delRecords(" user_id='{$user_id}' AND  id IN ($ids)");

    }
    /**
     * 更新用户购物车里的产品
     * @param $user_id
     * @param $cart_id
     * @param $goods_number
     */
    public function updateUserCartByCartId($user_id,$cart_id,$goods_number){
        $goods_number = (int)$goods_number?$goods_number:1;
        $this->updateRecords(array('goods_number'=>$goods_number),"user_id='{$user_id}' AND id='{$cart_id}' ");

    }
    /**
     * 判断是不是此用户的购物车产品
     * @param $user_id
     * @param $cart_id
     * @return int
     */
    public function isValidCartGoods($user_id,$cart_id){

        $num = $this->countRecords("user_id='{$user_id}' AND id='{$cart_id}' ");
        if($num){
            return 1;
        }else{
            return 0;
        }

    }

    /**
     * 获取钱的格式化字符串
     * @param $price
     * @param bool|true $change_price
     * @return string
     */
    public function getPriceFormatStr($price, $change_price = true){
        $currency_format = "￥%s元";
        $price_format = 4;
        if($change_price){
            switch ($price_format)
            {
                case 0:
                    $price = number_format($price, 2, '.', '');
                    break;
                case 1: // 保留不为 0 的尾数
                    $price = preg_replace('/(.*)(\\.)([0-9]*?)0+$/', '\1\2\3', number_format($price, 2, '.', ''));

                    if (substr($price, -1) == '.')
                    {
                        $price = substr($price, 0, -1);
                    }
                    break;
                case 2: // 不四舍五入，保留1位
                    $price = substr(number_format($price, 2, '.', ''), 0, -1);
                    break;
                case 3: // 直接取整
                    $price = intval($price);
                    break;
                case 4: // 四舍五入，保留 1 位
                    $price = number_format($price, 1, '.', '');
                    break;
                case 5: // 先四舍五入，不保留小数
                    $price = round($price);
                    break;
            }
        }else{
            $price = number_format($price, 2, '.', '');
        }

        return sprintf($currency_format, $price);

   }

    /**
     * 得道购物车概况 real_goods_count goods_price market_price
     * @param $cararr
     * @return array
     */
    public function getTotalArr($cararr){
        $totalarr = array();
        $totalarr['real_total_count']=0;
        $totalarr['goods_total_price']=0;
        $totalarr['market_total_price']=0;



        if(Buddha_Atom_Array::isValidArray($cararr)){
            /* 商品总价 */
            foreach ($cararr AS $val)
            {
                /* 统计实体商品的个数 */
                if ($val['is_real'])
                {
                    $totalarr['real_total_count']++;
                }

                $totalarr['goods_total_price']  += $val['goods_price'] * $val['goods_number'];
                $totalarr['market_total_price'] += $val['market_price'] * $val['goods_number'];

            }
        }

        return $totalarr;
    }
    /**
     * 获取购物车里的数据
     * @param $user_id
     * @return mixed
     */
    public function getCartArr($user_id,$table='',$paging=''){
        if($table == 'goods'){
            $where = "user_id='{$user_id}' AND goods_table='goods'" . $paging;
        }else{
            $where = "user_id='{$user_id}' ";
        }
        $fieldsarr = array('id as cart_id','user_id','merchant_id','is_single','session_id','goods_id',
            'goods_table','goods_sn','product_id','product_table','goods_name',
            'market_price','goods_price','goods_number','goods_attr','is_real',
            'extension_code','parent_id','rec_type','is_gift','is_shipping','can_handsel','goods_attr_id'

        );
         return $this->getFiledValues($fieldsarr,$where);
    }


    public function getGoodsListArr($cartarr){

        $return_arr = array();
        if(Buddha_Atom_Array::isValidArray($cartarr)){

            foreach($cartarr as $k=>$v){

                $createtime = Buddha::$buddha_array['buddha_timestamp'];
                $createtimestr = Buddha::$buddha_array['buddha_timestr'];
                if(Buddha_Atom_String::isValidString($v['createtime'])){
                    $createtime = $v['createtime'] ;
                    $createtimestr = $v['createtimestr'] ;
                }
                $return_arr[] = array('cart_id'=>$v['cart_id'],'product_id'=>$v['product_id'],
                    'product_table'=>$v['product_table'],'goods_name'=>$v['goods_name'],
                    'goods_number'=>$v['goods_number'],'goods_price'=>$v['goods_price'],
                    'market_price'=>$v['market_price'],'amout'=>$v['goods_number']*$v['goods_price']
                    ,'api_img'=>$v['api_img'],
                    'createtime'=>$createtime,'createtimestr'=>$createtimestr


                );
            }

        }

            return $return_arr;


    }


    /**
     * 判断购物车此物品是存存在 1存在 0不存在
     * @param $product_table
     * @param $product_id
     * @param $user_id
     * @return int
     */
    public function isExistGoods($product_table,$product_id,$user_id){

        $num = $this->countRecords("product_table='{$product_table}' AND product_id='{$product_id}' AND user_id='{$user_id}' ");
        if($num){
            return 1;
        }else{
            return 0;
        }

    }

    public function getCount($user_id){
        $sql = "SELECT SUM(goods_number) AS total  FROM {$this->prefix}cart  WHERE   user_id='{$user_id}' AND is_gift=0 AND is_real=1 ";
        $Db_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        if(Buddha_Atom_Array::isValidArray($Db_arr)){
            return $Db_arr[0]['total'];
        }else{
            return 0;
        }

    }

    /**
     * 把此产品加入购物车,为实现订购数量+1
     * @param $product_table
     * @param $product_id
     * @param $user_id
     */
    public function addOneGoodsToCart($product_table,$product_id,$user_id,$goods_number){

        $goods_number = (int)$goods_number;
        if($goods_number<1)
            $goods_number =1;

        $sql = "UPDATE {$this->prefix}cart SET goods_number=goods_number+{$goods_number} WHERE  product_table='{$product_table}' AND product_id='{$product_id}' AND user_id='{$user_id}' ";
        $this->db->query($sql);


    }
    /**
     * 判断两个时间戳之内是不是在促销日期之内
     * @param $promote_start_date
     * @param $promote_end_date
     * @return int
     */
    public function isPromote($promote_start_date,$promote_end_date){
        $nowtime = Buddha::$buddha_array['buddha_timestamp'];
        if(strlen($promote_start_date)<10 or strlen($promote_end_date)<10){
            return 0;
        }
        if($nowtime>=$promote_start_date and $promote_start_date<$promote_end_date){
            return 1;
        }else{
            return 0;
        }



    }
    /**返回要加入购物车产品的数组
     * @param $product_table
     * @param $product_id
     * @return mixed
     */
      public function getGoodsArr($product_table,$product_id,$user_id,$goods_number,$goods_table='',$goods_id=''){
          $Db_Goods = array();
          if($product_table=='supply'){
              $Db_Goods = $this->db->getSingleFiledValues (array('market_price','shop_id','promote_start_date','promote_end_date','promote_price','goods_sn','goods_name'), $product_table, "id='{$product_id}' " );
              $market_price = $Db_Goods['market_price'];
              $promote_price = $Db_Goods['promote_price'];
              $goods_sn = $Db_Goods['goods_sn'];
              $final_price = $market_price;
              $goods_name =  $Db_Goods['goods_name'];
              $rec_type=0;
              if($this->isPromote( $Db_Goods['promote_start_date'], $Db_Goods['promote_end_date'])){
                  if($promote_price<$market_price){
                      $final_price=  $promote_price;
                      $rec_type=  1;
                  }

              }
              $Db_Shop = $this->db->getSingleFiledValues (array('user_id'), 'shop', "id='{$Db_Goods['shop_id']}' " );
              $merchant_id = (int)$Db_Shop['user_id'];
            return   array('is_single'=>1,'is_real'=>1,'product_table'=>$product_table,'product_id'=>$product_id,'goods_number'=>$goods_number,'goods_name'=>$goods_name,
                'merchant_id'=>$merchant_id,'market_price'=>$market_price,'goods_price'=>$final_price,'rec_type'=>$rec_type,'goods_sn'=>$goods_sn,'goods_id'=>$product_id,'user_id'=>$user_id,
                'createtime'=>Buddha::$buddha_array['buddha_timestamp'],'createtimestr'=>Buddha::$buddha_array['buddha_timestr'],);

          }elseif($product_table=='goods'){

              $Db_Goods = $this->db->getSingleFiledValues (array('market_price','shop_id','promote_start_date','promote_end_date','promote_price','goods_sn','goods_name'), $product_table, "id='{$product_id}' " );
              $market_price = $Db_Goods['market_price'];
              $promote_price = $Db_Goods['promote_price'];
              $goods_sn = $Db_Goods['goods_sn'];
              $final_price = $market_price;
              $goods_name =  $Db_Goods['goods_name'];
              $rec_type=0;
              if($this->isPromote( $Db_Goods['promote_start_date'], $Db_Goods['promote_end_date'])){
                  if($promote_price<$market_price){
                      $final_price=  $promote_price;
                      $rec_type=  1;
                  }

              }
              $goods_product = $this->db->getSingleFiledValues (array('cost','profit'), 'goodsproduct', "id='{$goods_id}' " );
              if(count($goods_product)>0){
                  $final_price = $goods_product['cost'] + $goods_product['profit'];
              }
              $Db_Shop = $this->db->getSingleFiledValues (array('user_id'), 'shop', "id='{$Db_Goods['shop_id']}' " );
              $merchant_id = (int)$Db_Shop['user_id'];
              return   array('is_single'=>1,'is_real'=>1,'goods_table'=>$product_table,'goods_id'=>$product_id,'goods_number'=>$goods_number,'goods_name'=>$goods_name,'product_table'=>$goods_table,'product_id'=>$goods_id,
                  'merchant_id'=>$merchant_id,'market_price'=>$market_price,'goods_price'=>$final_price,'rec_type'=>$rec_type,'goods_sn'=>$goods_sn,'goods_id'=>$product_id,'user_id'=>$user_id,
                  'createtime'=>Buddha::$buddha_array['buddha_timestamp'],'createtimestr'=>Buddha::$buddha_array['buddha_timestr'],);
          }

          return $Db_Goods;
      }
    /**改变购物车里购买啊的商品的数量
     * @param $type
     * @param $cart_id
     * 
     */
    public function goodsCartNumberPlusMinus($cart_id,$type){
        $GoodsproductObj = new Goodsproduct();
        $uid = (int)Buddha_Http_Cookie::getCookie('uid');
        if($type){
            $cartInfo = $this->getSingleFiledValues(array('product_id','goods_number'),"id='{$cart_id}' AND user_id='{$uid}'");
            $stock = $GoodsproductObj->getSingleFiledValues(array('stock'),"id='{$cartInfo['product_id']}'");
            $data = array();
            if($cart_id){
                $goods_number = $cartInfo['goods_number'] + 1;
                if($type == 'plus'){
                    if($stock['stock'] >= $goods_number){
                        $data['goods_number'] = $cartInfo['goods_number'] + 1;
                    }else{
                        return 1;
                    }
                }
                if($type == 'minus'){
                    if($cartInfo['goods_number'] > 1){
                        $data['goods_number'] = $cartInfo['goods_number'] - 1;
                    }else{
                        return 0;
                    } 
                }
                
                $this->updateRecords($data,"id='{$cart_id}' AND user_id='{$uid}'");
            }
            
        }






    }













}