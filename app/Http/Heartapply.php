<?php
class Heartapply extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }

    /**
     *判断用户是否投票  如果返回1就代表投过票了 0代表没有投票
     * @param $heartpro_id
     * @param $user_id
     * @return int
     * @author wph 2017-12-22
     */
    public function isHadVote($heartpro_id,$user_id){

        $num = $this->countRecords("user_id='{$user_id}' and heartpro_id='{$heartpro_id}' ");
        if($num){
            return 1;
        }else{
            return 0;
        }

    }

    /**
     * 判断用户是否购买此1分购 返回1代表购买 返回0代表未购买
     * @param $heartpro_id
     * @param $user_id
     * @return int
     * @author wph 2017-12-22
     */
    public function isHadBuy($heartpro_id,$user_id){

        $num = $this->countRecords("is_buy=1 AND user_id='{$user_id}' and heartpro_id='{$heartpro_id}' ");
        if($num){
            return 1;
        }else{
            return 0;
        }
    }


}