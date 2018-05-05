<?php
class Activitycooperation extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }

    //合作商家添加
    public function cooadd($cooshopid,$act_id,$shopnamearr)
    {
        header("Content-type: text/html; charset=utf-8");
        list($uid, $UserInfo) = each(Buddha_Db_Monitor::getInstance()->userPrivilege());
         $datacoo['act_id']=$act_id;
        $datacoo['u_id']=$uid;

        $datacoo['add_time']=time();
        $datacoo['sore_time']=time();
        $datacoo['sore']=2;
        $ActivityObj=new Activity();
        $Act=$ActivityObj->getSingleFiledValues(array('id','name','start_date','end_date','shop_name','shop_id')," id ={$act_id}");
        $ShopObj=new Shop();
        if(count($cooshopid)>0){
            foreach($cooshopid as $k=>$v){
                $datacoo['shop_name']=$shopnamearr[$k];
                $datacoo['shop_id']=$v;
                $coo_id= $this->add($datacoo);
                $numarr['coo'][]=$coo_id;
                //            =======================
                $datanew['soure_id']=$uid;//发布消息的用户
                $datanew['shop_id']=$Act['shop_id'];//发布消息的店铺ID
                $datanew['shop_name']=$Act['shop_name'];////发布消息的店铺
                $datanew['name']=$Act['name'].'活动邀请函';//消息名称
                $datanew['add_time']=time();
                $datanew['is_act']=$act_id;//是否为活动
                $NewsObj=new News();
                foreach($shopnamearr as $k=>$v){
                    $datanew['content']="我店({$Act['shop_name']})从".date('Y-m-d H:i',$Act['start_date']).'—'.date('Y-m-d H:i',$Act['end_date'])."起在". $Act['address']."发起“{$Act['name']}”本地联合活动，已经对你(店)发出参与邀请，如果同意，请通过，谢谢!";
                    $shop_uid=$ShopObj->getSingleFiledValues(array('id','user_id')," id =($cooshopid[$k])");//查询所有店铺的拥有者ID
                    $datanew['u_id']=$shop_uid['user_id'];//店铺拥有者ID即消息接受者ID
                    $datanew['u_shopid']=$cooshopid[$k];//被邀请参与活动的店铺ID
                    $datanew['coo_id']=$coo_id;
//                print_r($datanew);/
                    $numarr['news'][]= $NewsObj->add($datanew);
                }
//            ===============









            }

        }
       return  $numarr;
    }

//查询活动的公共条件
//$is_area =0 不加入查询地区条件 =1  加入地区条件
//$is_time =0 不加入查询时间 =1  加入时间条件
    function act_public_where($is_area=0){
        $where=' is_sure=1 and sure=1 ';
        if($is_area==1){
            $RegionObj=new Region();
            $locdata = $RegionObj->getLocationDataFromCookie();
            $where.=$locdata['sql'];
        }
        return $where;
    }


    public function vodelist_ajax(){
        $title=Buddha_Http_Input::getParameter('title')?Buddha_Http_Input::getParameter('title'):2;//2人气、3最新
        $id=Buddha_Http_Input::getParameter('id');//活动ID
        $page=Buddha_Http_Input::getParameter('p');
        $search=Buddha_Http_Input::getParameter('search');
        $pagesize=20;
        $ShopObj=new Shop();

        $ActivityObj= new Activity();

        $ActO= $ActivityObj->getSingleFiledValues(array('id','type','vode_type'),"id={$id}");//查询当前活动的活动类型（如果是投票也要查询投票类型）
        $limit=Buddha_Tool_Page::sqlLimit ($page, $pagesize);
        $where=' a.act_id='.$id;
        if(!empty($search)){
            $where.=" and (s.name like '%{$search}%' or s.number like '%{$search}%')";
        }

        //对应商品（supply:goods_thumb 照片）、个人（user：logo照片）、店铺（shop：small）的（cooID、票数 、名称、 在activitycooperation表中）和活动ID


        $filed="a.id,a.shop_id,a.shop_name,a.praise_num,a.sure,a.is_sure,a.sore";//在 activitycooperation 表中要显示的字段有： 商品、个人、店铺
        //在 activitycooperation 表中要显示的字段有：在activitycooperation中要显示当前 商品、个人、店铺 的所在行的ID、票数、名称
        if($title==2){//2人气、3最新
            $orderby=' order by a.praise_num desc';
        }elseif($title==3){//2人气、3最新
            $orderby=' order by a.add_time desc';
        }
        if(($ActO['type']==3 || $ActO['type']==4) && $ActO['vode_type']==2) {//
            $filed.=',u.logo';
            $table='user';
            $as_f='u';
        }else if(($ActO['type']==3 || $ActO['type']==4) && $ActO['vode_type']==3) {
            $filed.=',s.goods_thumb';
            $table='supply';
            $as_f='s';
        }else{
            $filed.=',s.small';
            $table='shop';
            $as_f='s';
        }
        $sql ="select {$filed}
               from {$this->prefix}activitycooperation as a 
               INNER join {$this->prefix}{$table} as {$as_f} 
               on {$as_f}.id = a.shop_id  
               where {$where} {$orderby} {$limit}";
        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        foreach($list as $k=>$v){
            if(($ActO['type']==3 || $ActO['type']==4) && $ActO['vode_type']==2) {//个人
                if(!empty($v['logo'])){
                    $list[$k]['small']=$v['logo'];
                }else{
                    $list[$k]['small']='style/images/im.png';
                }
            }else if(($ActO['type']==3 || $ActO['type']==4) && $ActO['vode_type']==3) {//产品
                $list[$k]['small']=$v['goods_thumb'];
            }
        }
        return $list;
    }








}