<?php
class Userassomoney extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    /**
     * 判断是不是有 有效数据
     * 判断人脉关系分润表是否有有效数据
     * 如果有就返1 没有就返0   如果返1就进行数据更新操作 如果返0就进行数据插入操作.
     * @return int
     * @author wph 2017-11-25
     */
    public function isHasValidRecord(){

        $num = $this->countRecords("buddhastatus=0 AND isdel=0 ");
        if($num>0){
            return 1;
        }else{
            return 0;
        }

    }



    /**
     * 返回分润层级数量
     * @return mixed
     */
    public function getLayerLimitNumber(){

        $arr = $this->getValidRecordArr();
        return $arr['layerlim'];

    }


    /**
     * 返回上1代的利润比例
     * @return mixed
     */
    public function getGenerationOne(){

        $arr = $this->getValidRecordArr();
        return $arr['layer_money1'];

    }

    /**
     * 返回上2代的利润比例
     * @return mixed
     */
    public function getGenerationTwo(){

        $arr = $this->getValidRecordArr();
        return $arr['layer_money2'];

    }

    /**
     * 返回上3代的利润比例
     * @return mixed
     */
    public function getGenerationThree(){

        $arr = $this->getValidRecordArr();
        return $arr['layer_money3'];

    }

    /**
     * 返回上4代的利润比例
     * @return mixed
     */
    public function getGenerationFour(){

        $arr = $this->getValidRecordArr();
        return $arr['layer_money4'];

    }

    /**
     * 返回上5代的利润比例
     * @return mixed
     */
    public function getGenerationFive(){

        $arr = $this->getValidRecordArr();
        return $arr['layer_money5'];

    }

    /**
     * 返回上6代的利润比例
     * @return mixed
     */
    public function getGenerationSix(){

        $arr = $this->getValidRecordArr();
        return $arr['layer_money6'];

    }

    /**
     * 返回上7代的利润比例
     * @return mixed
     */
    public function getGenerationSeven(){

        $arr = $this->getValidRecordArr();
        return $arr['layer_money7'];

    }

    /**
     * 返回上8代的利润比例
     * @return mixed
     */
    public function getGenerationEight(){

        $arr = $this->getValidRecordArr();
        return $arr['layer_money8'];

    }

    /**
     * 返回上9代的利润比例
     * @return mixed
     */
    public function getGenerationNine(){

        $arr = $this->getValidRecordArr();
        return $arr['layer_money9'];

    }

    /**
     * 返回上10代的利润比例
     * @return mixed
     */
    public function getGenerationTen(){

        $arr = $this->getValidRecordArr();
        return $arr['layer_money10'];

    }

    /**
     * 对人脉关系人脉关系分润表进行添加或者更新
     * @param $data
     * @return mixed
     * @author wph 2017-11-25
     */
    public function addOrUpdateUserAssoMoney($data){

        if($this->isHasValidRecord()){
            $Db_Data = $this->getSingleFiledValues('',"buddhastatus=0 AND isdel=0 LIMIT 0,1");
            $id = $Db_Data['id'];
            return $this->edit($data,$id);

        }else{
            return $this->add($data);
        }

    }

    /**
     * 返回人脉具体分润数据
     * @return array
     * @author wph 2017-11-25
     */
    public function getValidRecordArr(){

        $arr = array(
            'layerlim' => 0,
            'layer_money1'=>0,'layer_money2'=>0,'layer_money3'=>0,'layer_money4'=>0,'layer_money5'=>0,
            'layer_money6'=>0,'layer_money7'=>0,'layer_money8'=>0,'layer_money9'=>0,'layer_money10'=>0

        );
        if($this->isHasValidRecord()){

            $Db_Userassomoney = $this->getSingleFiledValues('',"buddhastatus=0 AND isdel=0 LIMIT 0,1");
            $layerlim = $Db_Userassomoney['layerlim'];
            $layer_money1 = $Db_Userassomoney['layer_money1'];
            $layer_money2 = $Db_Userassomoney['layer_money2'];
            $layer_money3 = $Db_Userassomoney['layer_money3'];
            $layer_money4 = $Db_Userassomoney['layer_money4'];
            $layer_money5 = $Db_Userassomoney['layer_money5'];
            $layer_money6 = $Db_Userassomoney['layer_money6'];
            $layer_money7 = $Db_Userassomoney['layer_money7'];
            $layer_money8 = $Db_Userassomoney['layer_money8'];
            $layer_money9 = $Db_Userassomoney['layer_money9'];
            $layer_money10 = $Db_Userassomoney['layer_money10'];

            $total = $layer_money1+$layer_money2+$layer_money3+$layer_money4+$layer_money5+$layer_money6+$layer_money7+$layer_money8+$layer_money9+$layer_money10;

     
              $arr['layerlim'] = $layerlim;
               

            if($total<=1){


                if($layer_money1>=0 AND $layer_money1<=1){
                    $arr['layer_money1'] = $layer_money1;
                }

                if($layer_money2>=0 AND $layer_money2<=1){
                    $arr['layer_money2'] = $layer_money2;
                }

                if($layer_money3>=0 AND $layer_money3<=1){
                    $arr['layer_money3'] = $layer_money3;
                }

                if($layer_money4>=0 AND $layer_money4<=1){
                    $arr['layer_money4'] = $layer_money4;
                }
                if($layer_money5>=0 AND $layer_money5<=1){
                    $arr['layer_money5'] = $layer_money5;
                }

                if($layer_money6>=0 AND $layer_money6<=1){
                    $arr['layer_money6'] = $layer_money6;
                }

                if($layer_money7>=0 AND $layer_money7<=1){
                    $arr['layer_money7'] = $layer_money7;
                }

                if($layer_money8>=0 AND $layer_money8<=1){
                    $arr['layer_money8'] = $layer_money8;
                }

                if($layer_money9>=0 AND $layer_money9<=1){
                    $arr['layer_money9'] = $layer_money9;
                }

                if($layer_money10>=0 AND $layer_money10<=1){
                    $arr['layer_money10'] = $layer_money10;
                }





            }



        }

        return $arr;

    }



}