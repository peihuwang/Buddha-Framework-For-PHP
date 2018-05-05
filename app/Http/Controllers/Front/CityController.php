<?php

/**
 * Class CityController
 */
class CityController extends Buddha_App_Action{


    public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function index(){
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function ajax(){
        $CommonObj=new Common();
        $type_number=$CommonObj->deviceType();
        $RegionObj=new Region();
        $keyword=Buddha_Http_Input::getParameter('keyword');
        if($type_number==1){
            $where="isdel=0  and level>1  ";   //只显示区县不显示省市(PC要显示市，因为PC定位为IP)
        }else{
            $where="isdel=0  and level>2  ";   //只显示区县不显示省市（手机定位精确，只显示区县）
        }
        if($keyword){  // 搜索时不用 ，搜索出来省市只搜索区县
            $where.="and name like '%$keyword%'";
        }
        $orderby = " order by createtime DESC ";
        $region=$RegionObj->getFiledValues('',$where.$orderby);
        if(count($region)>0){
            $myregion =  array();
            foreach($region as $k=>$v){
                $first = strtoupper(substr( $v['pinyin'], 0, 1 )) ;
                $myregion['list'][$first]['first'] =$first;
                $myregion['list'][$first][]=array(
                    'id'=>$v['id'],
                    'number'=>$v['number'],
                    'namer'=>$v['name'],

                );
            }

            $myzero = $myregion['list'];
            ksort($myzero);
            $myzeoregion = array();
            foreach ($myzero as $k => $v) {
                $myzeoregion['list'][] = $v;
            }
        }else{
            $myzeoregion=0;
        }
        Buddha_Http_Output::makeJson($myzeoregion);
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

    public function info(){
        $firsta=Buddha_Http_Input::getParameter('first');

        $RegionObj=new Region();
        $region=$RegionObj->getFiledValues('',"isdel=0 and level=3");
        $myregion =  array();
        foreach($region as $k=>$v){
            $first = strtoupper(substr( $v['pinyin'], 0, 1 )) ;
            $myregion[$first]['first'] =$first;
            $myregion[$first][]=array(
                'id'=>$v['id'],
                'number'=>$v['number'],
                'namer'=>$v['name'],

            );
        }

        ksort($myregion);
        $myzeoregion = array();
        foreach ($myregion as $k => $v) {
            if ($k == $firsta) {
               // $myzeoregion['first']=$v['first'];
                $myzeoregion = $v;
            }
        }

        $this->smarty->assign('myzeoregion',$myzeoregion);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }

}