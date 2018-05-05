<?php
class Userfee extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }

    /**
     * @param $shop_id          店铺ID
     * @param $shop_user_id     店铺拥有者ID
     * @return int
     * 通过 店铺ID 和 用户ID 判断该店铺是否已经付费了
     */
    public function isPayByShopid($shop_id,$shop_user_id )
    {
        $UserfeeObj = new Userfee();
        $shop_id = (int)$shop_id;
        $shop_user_id = (int)$shop_user_id;

        $isPay = 0;//该店铺是否付费了 0否；1是

        $where = "user_id='{$shop_user_id}'";

        /**↓↓↓↓↓↓↓↓↓↓↓ 判断该商家是否已经支付了 e网通(360或990) ↓↓↓↓↓↓↓↓↓↓↓**/

        $Where = $where." AND (fee_type=1 or fee_type=2) AND (shop_id>0 AND shop_id='{$shop_id}')";
        $Db_Userfee_num = $UserfeeObj->countRecords($Where);

            //如果shop_id>0 并且 shop_id=$shop_id 存在；则表示用户有多家店铺只为这一家店铺付费了
        if(Buddha_Atom_String::isValidString($Db_Userfee_num))
        {
            $isPay = 1;
        }else{

                //如果shop_id>0 并且 shop_id=$shop_id 不存在；则表示用户只有一家店铺只为这一家付费了
            $Where = $where." AND (fee_type=1 or fee_type=2) AND (shop_id=0 AND shop_id='{$shop_id}')";
            $Db_Userfee_num = $UserfeeObj->countRecords($Where);
            if(Buddha_Atom_String::isValidString($Db_Userfee_num))
            {
                $isPay = 1;
            }
        }
        return $isPay;
    }

}