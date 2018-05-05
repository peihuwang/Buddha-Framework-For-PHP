<?php

/**
 * Class OrderController
 */
class RegionController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));


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
     * 随机得到一个市 或者  区的 编码
     */
    public function navrandsingle(){
        if (Buddha_Http_Input::checkParameter(array('b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }
        $b_display = (int)Buddha_Http_Input::getParameter('b_display');
        $RegionObj = new Region();
        $where='';
        if($b_display==2){
            $where=" isdel=0  AND level=3 ";
        }else {
            $where=" isdel=0  AND level=2 ";
        }
        $where =" isdel=0 AND level=3 AND number='330421' ";
        $orderby = " ORDER BY RAND()  DESC ";



        $jsondata=$RegionObj->getSingleFiledValues(array('id as region_id','name','number','pinyin','lat','lng'),$where.$orderby);
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '默认市区县导航');
    }


    public function more(){
        $RegionObj = new Region();
        $where=" isdel=0  AND level>2 ";
        $orderby = " ORDER BY pinyin ASC ";
        $Db_region=$RegionObj->getFiledValues(array('id as region_id','name','number','pinyin','lat','lng'),$where.$orderby);
        Buddha_Http_Output::makeWebfaceJson($Db_region, '/webface/?Services=' . $_REQUEST['Services'], 0, '区县json导航');
    }

    /**
     * ios转用的 cityList.plist
     */
    public function iosplist(){
        $RegionObj = new Region();
        $where=" isdel=0  AND level>2 ";
        $orderby = " ORDER BY pinyin ASC ";
        $Db_region=$RegionObj->getFiledValues('',$where.$orderby);

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<!DOCTYPE plist PUBLIC \"-//Apple//DTD PLIST 1.0//EN\" \"http://www.apple.com/DTDs/PropertyList-1.0.dtd\">
<plist version=\"1.0\">
<dict>
	<key>citys</key>
	<array>";

        foreach($Db_region as $k=>$v){
               $xml.= "
		<dict>
			<key>city</key>
			<string>{$v['name']}</string>
			<key>id</key>
			<integer>{$v['number']}</integer>
			<key>pinyin</key>
			<string>{$v['pinyin']}</string>
			<key>lat</key>
			<int>{$v['lat']}</int>
		    <key>lng</key>
			<int>{$v['lng']}</int>
		</dict>";
        }

        $xml.= "
	</array>
    </dict>
     </plist>";
        echo $xml;
    }
    /**
     *  区县导航
     */
    public function navigation(){

        if (Buddha_Http_Input::checkParameter(array('b_display'))) {
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444001, '必填信息没填写');
        }

        $RegionObj = new Region();

        $b_display = (int)Buddha_Http_Input::getParameter('b_display');
        $api_keyword = Buddha_Http_Input::getParameter('api_keyword');
        $where='';
        if($b_display==1){
            $where=" isdel=0  AND level>1 ";
        }else if($b_display==2){
            $where=" isdel=0  AND level>2 ";
        }

        if($api_keyword){
            $where.=" AND name LIKE '%$api_keyword%'";
        }

        $orderby = " ORDER BY createtime DESC ";

        $Db_region=$RegionObj->getFiledValues('',$where.$orderby);

        if(count($Db_region)>0){
            $myregion =  array();
            foreach($Db_region as $k=>$v){
                $first = strtoupper(substr( $v['pinyin'], 0, 1 )) ;
                $myregion[$first]['first'] =$first;
                $myregion[$first][]=array(
                    'region_id'=>$v['id'],
                    'api_number'=>$v['number'],
                    'pinyin'=>$v['pinyin'],
                    'lat'=>$v['lat'],
                    'lng'=>$v['lng'],
                );
            }
            $myzero = $myregion;
            ksort($myzero);
            $myzeoregion = array();
            foreach ($myzero as $k => $v) {
                $myzeoregion[] = $v;
            }
        }else{
            $myzeoregion=0;
        }

        $jsondata['list'] = $myzeoregion;
        Buddha_Http_Output::makeWebfaceJson($jsondata, '/webface/?Services=' . $_REQUEST['Services'], 0, '区县导航');

    }

}