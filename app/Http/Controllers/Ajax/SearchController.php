<?php


class SearchController extends Buddha_App_Action{

 public function __construct(){
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
    }


    public function shop(){
        //http://bendi.com/ajax/?a=shop&c=search&page=1&pagesize=10&number=330421&keyword=付杰装饰
        $RegionObj = new Region();
        $ShopObj = new Shop();
        $keyword = Buddha_Http_Input::getParameter('keyword');
        $number = Buddha_Http_Input::getParameter('number');
        $act = Buddha_Http_Input::getParameter('act');
        $locdata = $RegionObj->getLocationDataFromCookie($number);


        $where = " isdel=0 and is_sure=1  and state=0  ";
        if($locdata['sql']!='' and $number!=''){
        $where.=" {$locdata['sql']} ";
        }
        if ($keyword) {
            $where .= " and (name like '%$keyword%' or specticloc like '%$keyword%')";
        }

        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = (int)Buddha_Http_Input::getParameter('PageSize')?(int)Buddha_Http_Input::getParameter('PageSize') :15;

        $orderby = " order by createtime DESC ";
        $fields = array('id', 'name', 'brief', 'small','lat','lng','specticloc','storetype');
        $list = $this->db->getFiledValues($fields, $this->prefix . 'shop', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));
        foreach($list as $k=>$v){
            $distance=$RegionObj->getDistance($locdata['lng'],$locdata['lat'],$v['lng'],$v['lat'],2);
            $goodsNws[]=array(
                'id'=>$v['id'],
                'name'=>$v['name'],
                'brief'=>$v['brief'],
                'small'=>$v['small'],
                'roadfullname'=>$v['specticloc'],
                'distance'=>$distance,
            );
        }
        if($act=='list'){
        $data=array();
        if(count($goodsNws)>0){
            $data['isok']='true';
            $data['list']=$goodsNws;
            $data['data']='加载完成';
        }else{
            $data['isok']='false';
            $data['list']='';
            $data['data']='没数据了';
        }
        Buddha_Http_Output::makeJson($data);
        }
    }


}