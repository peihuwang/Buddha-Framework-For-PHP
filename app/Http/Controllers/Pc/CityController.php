<?php

/**
 * Class CityController
 */
class CityController extends Buddha_App_Action{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        $RegionObj=new Region();
        $region=$RegionObj->getFiledValues(array('id','name','number','father'),"father=1 and isdel=0");
        foreach($region as $k=>$v){
            $region1=$RegionObj->getFiledValues(array('id','name','number','father'),"father='{$v['id']}' and isdel=0");
            $region[$k]['child']=$region1;
            foreach($region[$k]['child'] as $k1=>$v1){
                $region2=$RegionObj->getFiledValues(array('id','name','number','father'),"father='{$v1['id']}' and isdel=0");
                $region[$k]['child'][$k1]['child']=$region2;
            }
        }


        $this->smarty->assign('region',$region);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function getnumber (){
        $RegionObj=new Region();
       $number= Buddha_Http_Input::getParameter('number');
        $region=$RegionObj->getSingleFiledValues('*',"isdel=0 and number='{$number}'");
        $re=array();
        if($region){
            $province=$RegionObj->getSingleFiledValues('*',"isdel=0 and id='{$region['father']}'");
            $re['nation']='中国';
            $re['province']=$province['name'];
            $re['city']=$region['name'];
            $re['adcode']=$region['number'];
            $re['lat']=$region['lat'];
            $re['lng']=$region['lng'];
            $re['errcode']='0';
            $re['errmsg'] = "完成";
        }else{
            $re['errcode']='1';
            $re['errmsg'] = "不明错误";
        }

        Buddha_Http_Output::makeJson($re);
    }

}