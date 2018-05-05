<?php
class Activity extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);
    }


    /**
     * @param int $id
     * @return string
     * 推荐属于该店铺下的 活动
     */
    public function recommendBelongShop($shop_id,$id=0,$b_display=2)
    {
        $host = Buddha::$buddha_array['host'];

        $ActivityObj = new Activity();
        $CommonObj = new Common();
        $shownum = 6; //最大显示数量
        /**
         *  思路：
         *      1、先统计该店铺下的 活动 有多少个(审核通过+上架)；
         *      2、如果该店铺下的 活动 数量少于$shownum 数量则直接查询即可（前提是当前传过来的id=0；如果大于0，则要去除该ID）
         *      3、如果该店铺下的 活动 数量多余$shownum 数量则直接查询后随机筛选出$shownum即可
         */

        /**↓↓↓↓↓↓↓↓↓↓↓↓ 随机显示该店铺下的 6 款 活动  ↓↓↓↓↓↓↓↓↓↓↓↓**/
        $random_filed = array('id as activity_id','name','number','`brief`','type');
        if($b_display==2)
        {
            array_push($random_filed,'activity_thumb as img');
        }elseif($b_display==1){
            array_push($random_filed,'activity_img as img');
        }

        $where = " shop_id='{$shop_id}' AND is_sure=1 AND buddhastatus=0 AND isdel=0";

        if($id>0)
        {
            $where .= " id!='{$id}'";
        }

        $order = ' ORDER BY id DESC';
        /***1、先统计该店铺下 1活动 的有多少个(审核通过+上架)；****/

        $Db_Demand_count = $ActivityObj->countRecords($where);

        if($Db_Demand_count==0)
        {
            $Db_Activity = array();
        }elseif(0<=$Db_Demand_count AND $Db_Demand_count<=$shownum)//1.1如果在没有其他条件的情况下（推荐和热门）的总数量 小于等于 $shownum最大显示数量 则直接查询所有的
        {
            $Db_Activity = $ActivityObj->getFiledValues($random_filed,$where.$order);
        }else{//1.1如果在没有其他条件的情况下（推荐和热门）的总数量 大于 $shownum最大显示数量 则直接查询所有的

            $Db_random_id = $ActivityObj->getFiledValues(array('id'),$where.' AND (is_hot=1 OR is_rec=1) '.$order);
            if(!Buddha_Atom_Array::isValidArray($Db_random_id)){
                $Db_random_id = $ActivityObj->getFiledValues(array('id'),$where.$order);
            }
            $random_id_arr = array();
            //ID二维数组 变为 ID一维数组
            foreach ($Db_random_id as $k=>$v)
            {
                $random_id_arr[]=$v['id'];
            }
            //如果总数量 大于 $shownum最大显示数量 则随机获取数组 的个数等于 $shownum最大显示数量
            if(sizeof($random_id_arr)>$shownum)
            {
                $number = $shownum;
            }else{//如果总数量 小于等于 $shownum最大显示数量 则随机获取数组 的个数等于 数组的数量
                $number = sizeof($random_id_arr);
            }

            //随机获取$number个数据
            $random_keys = array_rand($random_id_arr,$number);

            $random_id_str = '';
            //如果总数量 大于 $shownum最大显示数量
            if(sizeof($random_id_arr)>$shownum)
            {
                foreach ($random_keys as $k=>$v)
                {
                    $random_id_str .= $random_id_arr[$v].',';
                }
                $random_id = trim($random_id_str,',');
            }else{//如果总数量 小于等于 $shownum最大显示数量
                foreach ($Db_random_id as $k=>$v)
                {
                    $random_id_str .= $v['id'].',';
                }
                $random_id = trim($random_id_str,',');
            }

            $Db_Activity = $ActivityObj->getFiledValues($random_filed," id IN ({$random_id})");
        }

        foreach ($Db_Activity as $k=>$v)
        {
            $Db_Activity[$k]['brief'] = $CommonObj->intercept_strlen($v['brief'],'10');

            if(Buddha_Atom_String::isValidString($v['img']))
            {
                $Db_Activity[$k]['img'] = $host.Buddha_Atom_Dir::getformatDbStorageDir($v['img']);
            }else{
                $Db_Activity[$k]['img'] = '';
            }
        }
        /**↑↑↑↑↑↑↑↑↑↑↑↑ 随机显示 6款 1活动 ↑↑↑↑↑↑↑↑↑↑**/

        $list['headettitle'] = '活动';
        $list['more'] = $Db_Activity;
        if($Db_Activity['type'] == 2){
            $url = 'mylist';
        }elseif ($Db_Activity['type'] == 3){
            $url ='vodelist';
        }

        $list['url'] = "index.php?a={$url}&c={$this->table}&shop_id=".$shop_id;
        return $list;
    }




    /**
     * 相册删除及信息删除
     */
    public function  toCleanTrash($activity_id,$user_id)
    {
        $where = "goods_id='{$activity_id}' AND (user_id=0 or user_id='{$user_id}') AND tablename='activity' ";
        $MoregalleryObj = new Moregallery();
        $num = $MoregalleryObj->countRecords($where);
        if($num){
            /*删除图片 同时删除信息*/
            $Db_Moregallery = $MoregalleryObj->getFiledValues(array('id','goods_thumb','goods_img','goods_large','sourcepic'),$where);

            if(Buddha_Atom_Array::isValidArray($Db_Moregallery))
            {
                $idstr='';
                foreach($Db_Moregallery as $k=>$v)
                {
                    $temp_id = $v['id'];
                    $MoregalleryObj->delGelleryimage($temp_id);
                    $idstr .=$v['id'].',';
                }
                $idstr = Buddha_Atom_String::toDeleteTailCharacter($idstr,1);

                $MoregalleryObj->delRecords("id IN ({$idstr}) ");
            }
        }

        $Db_Activity_num =  $this->delRecords("id='{$activity_id}' AND user_id='{$user_id}'");
        return $Db_Activity_num;
    }

    /**
     * 更新活动问题的被点击数量
    */
    public function update_customclick_num($table_id,$checkname,$radioname,$table_name='activity')
    {
        $aa=1;
        $where="t_id='{$table_id}' AND t_name='{$table_name}'";
        if(Buddha_Atom_Array::isValidArray($checkname)){
            foreach ($checkname as $k=>$v){

                if($k>0){
                    $Db_custom=$this->db->getSingleFiledValues(array('click_num'),'custom',$where." AND id='{$v}'");
                    $data['click_num']=$Db_custom['click_num']+1;
                    $num=$this->db->updateRecords( $data, "custom",$where." AND　id='{$v}'" );
                    $aa=$num;
                }else{
                    $Db_custom=$this->db->getSingleFiledValues(array('click_num'),'custom',$where." AND arrkey='{$v}'");
                    $data['click_num']=$Db_custom['click_num']+1;
                    $this->db->updateRecords( $data, "custom",$where." AND arrkey='{$v}'" );
                    $aa=$num;
                }

            }
        }
        if(Buddha_Atom_Array::isValidArray($radioname)){
            foreach ($radioname as $k=>$v){

                if($k>0){
                    $Db_custom=$this->db->getSingleFiledValues(array('click_num'),$where."id='{$v}'");
                    $data['click_num']=$Db_custom['click_num']+1;
                    $num=$this->db->updateRecords( $data, "custom",$where." AND　id='{$v}'" );
                    $aa=$num;
                }else{
                    $Db_custom=$this->db->getSingleFiledValues(array('click_num'),$where." AND arrkey='{$v}'");
                    $data['click_num']=$Db_custom['click_num']+1;
                    $this->db->updateRecords( $data, "custom",$where." AND arrkey='{$v}'" );
                    $aa=$num;
                }

            }
        }

        return $aa;
    }


    /**
     * @param $Db_Activity
     * @return mixed
     *  获取当前活动的状态
     */

    public function getActivityState($Db_Activity)
    {

        $newtime = time();
        /*默认情况下：活动未开始可以报名*/
        $cc = 0;
        if ($newtime < $Db_Activity['start_date']) {//如果当前时间小于开始时间则是距离开始时间
            $aa = 1;//距离开始
            $bb = 0;//活动未开始
        }elseif ($Db_Activity['start_date'] <= $newtime AND $newtime <= $Db_Activity['end_date']) {//如果当前时间小于等于开始时间则是距离结束时间
            $aa = 2;//距离结束距离结束
            $bb = 2;//活动进行中
        }elseif($newtime > $Db_Activity['end_date']){
            $aa = 0;//活动已经结束
            $bb = 1;//活动已结束
        }
        /*报名开始和报名结束时间都不为空*/
        if(!empty($Db_Activity['sign_start_time']) AND !empty($Db_Activity['sign_end_time'])){
            if ($newtime < $Db_Activity['sign_start_time']) {//如果当前时间小于报名开始时间则是距离开始时间
                $cc = 0;//活动报名未开始可以报名
            }elseif ($Db_Activity['sign_start_time'] <= $newtime && $newtime <= $Db_Activity['sign_end_time']) {//如果当前时间小于等于开始时间则是距离结束时间
                $cc = 0;//活动处于报名期间可以报名
            }elseif($newtime > $Db_Activity['end_date']){
                $cc = 1;//活动报名已结束
            }
        /*报名开始和报名结束时间都为空*/
        }elseif(empty($Db_Activity['sign_start_time']) AND empty($Db_Activity['sign_end_time'])){
            if ($newtime < $Db_Activity['start_date']) {//如果当前时间小于开始时间则是距离开始时间
                $cc = 0;//活动未开始可以报名
            }elseif ($Db_Activity['start_date'] <= $newtime AND $newtime <= $Db_Activity['end_date']) {//如果当前时间小于等于开始时间则是距离结束时间
                $cc = 0;//活动已开始可以报名
            }elseif($newtime > $Db_Activity['end_date']){
                $cc = 1;//活动已结束不可以报名
            }
        //如果报名开始时间不为空，
        }else if(!empty($Db_Activity['sign_start_time'])){
            if(empty($Db_Activity['sign_end_time'])){//如果报名结束时间为空，则以活动结束时间为报名结束时间
                if($newtime < $Db_Activity['sign_start_time']){//当前时间小于活动报名时间（报名未开始）
                    $cc = 3;//报名未开始（不可报名）
                }else if($newtime <= $Db_Activity['sign_start_time'] AND $newtime <= $Db_Activity['end_time']){//当前时间小于报名开始时间（活动报名未开始）
                    $cc = 0;//活动报名开始
                }else if($newtime>$Db_Activity['end_time']){
                    $cc = 4;//活动已结束（不可报名）
                }
            }else if(!empty($Db_Activity['sign_end_time'])){
                if($newtime < $Db_Activity['sign_start_time']){//当前时间小于活动报名时间（报名未开始）
                    $cc = 3;//报名未开始（不可报名）
                }else if($newtime <= $Db_Activity['sign_start_time'] AND $newtime <= $Db_Activity['sign_end_time']){//当前时间小于报名开始时间（活动报名未开始）
                    $cc = 0;//活动报名开始
                }else if($newtime>$Db_Activity['end_time']){
                    $cc = 4;//活动已结束（不可报名）
                }
            }
        }else if(empty($Db_Activity['sign_end_time'])){//如果报名结束时间为空，
            if(empty($Db_Activity['sign_start_time'])) {//如果报名开始时间为空，则以活动审核通过时间为报名开始时间（）
                if($newtime < $Db_Activity['end_time']) {//当前时间小于活动报名时间（报名未开始）
                    $cc = 0;//报名开始（可报名）
                } else if($newtime <= $Db_Activity['start_time'] AND $newtime <= $Db_Activity['end_time']){//当前时间小于报名开始时间（活动报名未开始）
                    $cc = 0;//活动报名开始
                }else if($newtime>$Db_Activity['end_time']){
                    $cc = 4;//活动已结束（不可报名）
                }
            }elseif (!empty($Db_Activity['sign_start_time'])){//如果报名结束时间不为空，
                if($newtime < $Db_Activity['sign_start_time']){//当前时间小于活动报名时间（报名未开始）
                    $cc = 3;//报名未开始（不可报名）
                }else if($newtime <= $Db_Activity['sign_start_time'] AND $newtime <= $Db_Activity['end_time']){//当前时间小于报名开始时间（活动报名未开始）
                    $cc = 0;//活动报名开始
                }else if($newtime>$Db_Activity['end_time']){
                    $cc = 4;//活动已结束（不可报名）
                }
            }
        }

        /*活动状态标题：0活动已经结束; 1距离开始; 2 距离结束距离结束*/
        $arr['activitystatetitle']=$aa;
        /*报名状态：*/
        $arr['signstate']=$cc; // 0 可以报名  1活动已结束不可以报名 3 报名未开始（不可报名）; 4活动已结束（不可报名）( 活动开始了就不能报名了；0可以报名，1 不可以报名)
        $arr['time_e']=$bb; // 0活动未开始; 1活动已结束; 2活动进行中


        return $arr;
    }


    /**
     * @param $activityid
     * @return int
     * 判断活动ID是否属于当前用户
     */
    public function isActivityidAndUidValid($activityid,$user_id)
    {
        $activityid=(int)$activityid;

        $Db_Activity_Num=$this->db->countRecords('activity',"id='{$activityid}' AND user_id='{$user_id}'");
        if($Db_Activity_Num){
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * @param $activityid
     * @return int
     * 判断活动ID是否有效
     */
    public function isActivityidValid($activityid)
    {
        $activityid=(int)$activityid;

        $Db_Activity_Num=$this->db->countRecords('activity',"id='{$activityid}'");
        if($Db_Activity_Num){
            return 1;
        }else{
            return 0;
        }
    }


    /**
     * 设置多图中的第一张图片会默认展示图片
     * @param $lease_id
     * @author csh
     */

    public function setFirstMoreImageImgToActivity($shop_id){


        $table_name='activity';
        $num =  $this->db->countRecords ( 'moregallery', " goods_id='{$shop_id}' AND table_name='{$table_name}' " );
        if($num){

            $defaultgimages= $this->db->getSingleFiledValues('','moregallery',"goods_id='{$shop_id}' AND table_name='{$table_name}' order by id ASC");
            $this->db->updateRecords(array('isdefault'=>'1'),'album',"id='{$defaultgimages['id']}'  ");
            $dataImg=array();
            $dataImg ['small'] = $defaultgimages['goods_thumb'];
            $dataImg ['medium'] = $defaultgimages['goods_img'];
            $dataImg ['large'] = $defaultgimages['goods_large'];
            $dataImg ['sourcepic'] = $defaultgimages['sourcepic'];
            $this->updateRecords($dataImg,"id='{$shop_id}'   ");

        }

    }
    /**
     * @param $Db_Activity
     * @return mixed
     * 在报名开始时间或报名结束时间有一个为空的判断，并返回时间报名开始和结束时间（）
     */


    public function getRegistrationtime($Db_Activity){
        /*思路描述：1、如果报名开始和报名结束都不存在：就以活动添加时间为报名开始时间，活动结束时间为报名结束时间*/
        /*思路描述：2、如果报名开始时间存在，报名结束时间不存在；就以报名开始时间为报名开始时间，活动结束时间为报名结束时间*/
        /*思路描述：3、如果报名开始时间不存在，报名结束时间存在；就以活动添加时间为报名开始时间，报名结束时间为报名结束时间*/

        if((int)$Db_Activity['sign_start_time']==0 && (int)$Db_Activity['sign_end_time']==0){

            $api_signstarttime=(int)$Db_Activity['add_time']
            ;
            $api_signendtime=(int)$Db_Activity['end_date'];

        }else if((int)$Db_Activity['sign_start_time']!=0 && (int)$Db_Activity['sign_end_time']!=0){

            $api_signstarttime=(int)$Db_Activity['sign_start_time'];

            $api_signendtime=(int)$Db_Activity['sign_end_time'];

        }else if((int)$Db_Activity['sign_start_time']!=0 && (int)$Db_Activity['sign_end_time']==0){


            $api_signstarttime=(int)$Db_Activity['sign_start_time'];

            $api_signendtime=(int)$Db_Activity['end_date'];

        }else if((int)$Db_Activity['sign_start_time']==0 && (int)$Db_Activity['sign_end_time']!=0){

            $api_signstarttime=(int)$Db_Activity['add_time'];

            $api_signendtime=(int)$Db_Activity['sign_end_time'];

        }
        $CommonObj=new Common();

        $signtime['api_signstarttime']=$CommonObj->getDateStrOfTime($api_signstarttime,0,1,1);

        $signtime['api_signendtime']=$CommonObj->getDateStrOfTime($api_signendtime,0,1,1);

        return  $signtime;

    }


    /**
     * @param $singleinfomation_id
     * @param $level3
     * @return int
     * @author wph 2017-09-14
     * 判断该活动是否属于该代理商
     */
    public function isOwnerBelongToAgentByLeve3($activity_id,$level3){

        if($level3<1 or $activity_id<1){
            return 0;
        }

        $num = $this->countRecords(" id='{$activity_id}' AND  level3='{$level3}' ");
        if($num){
            return 1;
        }else{
            return 0;
        }

    }

    /**
     * @param $activitytypeid
     * @return mixed
     * 获取活动类型名称，通过活动类型ID
     */
    public function getactivitytypenameforactivitytyid($activitytypeid)
    {

//        return $this->getTypeNameByTypeId($activitytypeid);

        if($activitytypeid>0)
        {
            $activitytypearray = $this->activitytype();

            foreach($activitytypearray as $k=>$v)
            {
                if($v['activitytypeid'] == $activitytypeid)
                {
                    return $v['activitytypename'];
                }
            }
        }else{
            return '';
        }
    }



    public  function getTypeNameByTypeId($activitytypeid=0){
        $activitytypeid =  (int)$activitytypeid;

        $arr = $this->getTypeArr();

        if($activitytypeid>=1 and $activitytypeid<=3){

              return $arr[$activitytypeid];

        }else{
            return '';
        }

    }


    public function getTypeArr(){
        $arr = array('1'=>'单家','2'=>'多家联合','3'=>'投票');
        return $arr;
    }



    public function activityMytType($activitytypeid=0){
        $activitytypearray=array();

        $arr = $this->getTypeArr();
        $activitytypearray = array();

        $step = 0;
        foreach($arr as $k=>$v){

            $activitytypearray[$step++]   = array('activitytypeid'=>$k,'activitytypename'=>$v);
        }


        foreach ($activitytypearray as $k=>$v){
            if(Buddha_Atom_String::isValidString($activitytypeid)){
                if($v['activitytypeid']==$activitytypeid){
                    $activitytypearray[$k]['select']=1;
                }else{
                    $activitytypearray[$k]['select']=0;
                }
            }else{
                if($v['activitytypeid']==1){
                    $activitytypearray[$k]['select']=1;
                }else{
                    $activitytypearray[$k]['select']=0;
                }
            }
        }

        return $activitytypearray;
    }


    /**
     * @return array
     * 活动类型
     */

    public function activitytype($activitytypeid=0){
        $activitytypearray=array();


        $activitytypearray=array(
            0=>array('activitytypeid'=>1,'activitytypename'=>'单家'),
            1=>array('activitytypeid'=>2,'activitytypename'=>'多家联合'),
            2=>array('activitytypeid'=>3,'activitytypename'=>'投票'),


//                3=>array('activitytypeid'=>4,'activitytypename'=>'点赞'),
        );

       foreach ($activitytypearray as $k=>$v){
           if(Buddha_Atom_String::isValidString($activitytypeid)){
               if($v['activitytypeid']==$activitytypeid){
                   $activitytypearray[$k]['select']=1;
               }else{
                   $activitytypearray[$k]['select']=0;
               }
           }else{
               if($v['activitytypeid']==1){
                   $activitytypearray[$k]['select']=1;
               }else{
                   $activitytypearray[$k]['select']=0;
               }
           }
       }

        return $activitytypearray;
    }


    /**
     * @return array
     * 活动类型默认选中
     */

    public function activitytypeselect($activitytypeid=0){
        $activitytypearray=$this->activitytype();
        if(Buddha_Atom_Array::isValidArray($activitytypearray)){
            foreach ($activitytypearray as $k=>$v){
                if($activitytypeid>0 AND $v['activitytypeid']==$activitytypeid){
                    $activitytypearray[$k]['select']=1;
                }else{
                    $activitytypearray[$k]['select']=0;
                }
            }
        }

        return $activitytypearray;
    }





    /**
     * @return bool
     * 通过活动类型ID 判断活类型ID是否有效
     */
    public function isActivitytypeEffectiveByActivitytypeid($activitytypeid)
    {
        $activitytypeid=(int)$activitytypeid;

        $activitytype=$this->activitytype();

        if(0<$activitytypeid AND $activitytypeid<4 AND Buddha_Atom_Array::isValidArray($activitytype)){

            foreach ($activitytype as $k=>$v){
                if($v['activitytypeid']==$activitytypeid){
                    return 1;
                }
            }

        }else{
            return 0;
        }


        return $this->debug_mode;
    }




    /**
     * @param $activitytypeid
     * @return mixed
     * 通过活动类型ID获取活动的名称
     *
     */

    public function getActivitytypenameByActivitytypeid($activitytypeid){

        if((int)$activitytypeid>0){
            $activitytypearray=$this->activitytype();

            foreach ($activitytypearray as $k=>$v)

                if($v['activitytypeid']==$activitytypeid){

                    return $v['activitytypename'];

                }
        }else{

            return '';
        }
    }


    /**
     * @param $activityvodetypeid
     * @return mixed
     * 通过活动投票类型ID获取活动投票类型的名称
     */
    public function getActivityvodetypenameByActivityvodetypeid($activityvodetypeid){

        if((int)$activityvodetypeid>0){

            $activityvodetypearray=$this->activityvodetype();

            foreach ($activityvodetypearray as $k=>$v)

                if($v['activityvodetypeid']==$activityvodetypeid){

                    return $v['activityvodetypename'];

                }
        }else{

            return '';
        }
    }


    /**
     * @return array
     *  activityvodetype
     *   活动投票类型
     */
    public function activityvodetype($activitytypeid=0,$activityvodetypeid=0){

        $activityvodetypearray=array();

        $activityvodetypearray=array(
            0=>array('activityvodetypeid'=>1,'activityvodetypename'=>'商家'),
            1=>array('activityvodetypeid'=>2,'activityvodetypename'=>'个人'),
            2=>array('activityvodetypeid'=>3,'activityvodetypename'=>'产品'),
        );
        foreach($activityvodetypearray as $k=>$v){
            if(Buddha_Atom_String::isValidString($activitytypeid) AND $activitytypeid==3 AND Buddha_Atom_String::isValidString($activityvodetypeid) AND $v['activityvodetypeid']==$activityvodetypeid){
                $activityvodetypearray[$k]['select']=1;
            }else{
                $activityvodetypearray[$k]['select']=0;
            }
        }

        return  $activityvodetypearray;
    }






    /**
     * @return array
     *  activityvodetype
     *   活动投票类型
     */
    public function activityvodetypeselect($activityvodetypeid=0){

        $activityvodetypearray=$this->activityvodetype();

        if(Buddha_Atom_Array::isValidArray($activityvodetypearray)){
            foreach ($activityvodetypearray as $k=>$v){
                if($activityvodetypeid>0 AND $v['id']==$activityvodetypeid){
                    $activityvodetypearray[$k]['select']=1;
                }else{
                    $activityvodetypearray[$k]['select']=0;
                }
            }
        }

        return  $activityvodetypearray;
    }







    /**
     * 1=未开始 2=进行中 3=已结束 0=系统出错
     * @param $starttime
     * @param $endstart
     * @return int
     */
    public function getActiveStatusInt($starttime,$endtime){
        if(strlen($starttime)<10 or strlen($endtime)<10 or $starttime>$endtime){

            return 0;
        }

        $nowtime = Buddha::$buddha_array['buddha_timestamp'];
        if($nowtime<$starttime){
            return 1;
        }

        elseif($starttime<=$nowtime and $nowtime<$endtime){
            return 2;
        }else{
            return 3;
        }


    }



    /**
     * 0 ：不能报名   1：可以报名
     * @param $activity_start
     * @param $actiity_end
     * @param $enrol_stat
     * @param $enrol_end
     * @return int
     */
    public  function isActivityEnrole($activity_start,$actiity_end,$enrol_stat,$enrol_end){
        $nowtime = Buddha::$buddha_array['buddha_timestamp'];
        $activestatus = $this->getActiveStatusInt($activity_start,$actiity_end);
        if(strlen($enrol_end)>=10 and strlen($enrol_stat)>=10  and $enrol_end>=$enrol_stat)
        {

            if( ($activestatus==1 or $activestatus==2) and ($nowtime>=$enrol_stat and $nowtime<$enrol_end) )
            {
                return 1;
            }else{
                return 0;
            }


        }elseif(strlen($enrol_end)>=10 and strlen($enrol_stat)<10 )
        {

            if( ($activestatus==1 or $activestatus==2) and  $nowtime<$enrol_end ) {
                return 1;
            }else{
                return 0;
            }


        }else{

            if( ($activestatus==1 or $activestatus==2) )
            {
                return 1;
            }else{
                return 0;
            }

        }




    }

     //$webfield添加进来对应页面的字段名称：主要针对一个页面有多个相册或有多个单图
    public function setFirstGalleryImgToSupply($goods_id,$tablename,$webfield){
        $defaultgimages= $this->db->getSingleFiledValues('',$this->prefix.'moregallery',"goods_id={$goods_id} and tablename='{$tablename}' and isdel=0 and webfield='{$webfield}' order by isdefault,id ASC");
        $this->db->updateRecords(array('isdefault'=>'1'),$this->prefix.'moregallery',"id='{$defaultgimages['id']}'");
        $dataImg=array();
        $dataImg ['activity_thumb '] = $defaultgimages['goods_thumb'];
        $dataImg ['activity_img'] = $defaultgimages['goods_img'];
        $dataImg ['activity_large'] =  $defaultgimages['goods_large'];
        $dataImg ['sourcepic'] =  $defaultgimages['sourcepic'];
        $num =$this->updateRecords($dataImg,"id='{$goods_id}'");
        return $num;
    }

    public function GeneratingNumber(){
        $time=date(YmdHis);
        $random =rand(11111111,99999999);
        $num=$time.$random;
        return $num;
    }

    /*
     *  @ if_where  判断是否符合条件
     *  @$arr  条件
     */


    public function if_where($radioname,$checkname,$text,$txt,$checkbox,$radio,$table_name,$good_id)
    {


        $CustomObj=new Custom();
        if(!empty($txt)){
            $arr['type']=1;//1 单行;'2 多行；3单选；4多选
            $arr['list']=$txt;
            $CustomObj->Custom_add($arr,$table_name,$good_id);
        }
        if(!empty($text)){
            $arr['type']=2;//1 单行;'2 多行；3单选；4多选
            $arr['list']= $text;
            $CustomObj->Custom_add($arr,$table_name,$good_id);
        }
        if(!empty($radio)){
            $arr['type']=5;//1 单行;'2 多行；3单选；4多选;5单选内容；6多选内容
            $arr['list']= $radio;
            $CustomObj->Custom_add($arr,$table_name,$good_id);
        }
        if(!empty($radioname)){
            $arr['type']=3;//1 单行;'2 多行；3单选；4多选
            $arr['list']= $radioname;
            $CustomObj->Custom_add($arr,$table_name,$good_id);
        }
        if(!empty($checkname)){
            $arr['type']=4;//1 单行;'2 多行；3单选；4多选
            $arr['list']= $checkname;
            $CustomObj->Custom_add($arr,$table_name,$good_id);
        }

        if(!empty($checkbox)){
            $arr['type']=6;//1 单行;'2 多行；3单选；4多选;5单选内容；6多选内容
            $arr['list']= $checkbox;
            $CustomObj->Custom_add($arr,$table_name,$good_id);
        }
        if(!empty($txt)){
            foreach($txt as $k=>$v){
                $aa=explode(',',$k);
                $sub0=$aa[0];
                $sub1=$aa[1];
                $sub2=$aa[2];
                $txt[$k]=array(
                    'sub0'=>$sub0,
                    'sub1'=>$sub1,
                    'sub2'=>$sub2,
                    'val'=>$v,
                );
            }
        }
        if(!empty($text)) {
            foreach ($text as $k => $v) {
                $aa = explode(',', $k);
                $sub0 = $aa[0];
                $sub1 = $aa[1];
                $sub2 = $aa[2];
                $text[$k] = array(
                    'sub0' => $sub0,
                    'sub1' => $sub1,
                    'sub2' => $sub2,
                    'val' => $v,
                );
            }
        }

        if(!empty($checkbox)){
            foreach ($checkbox as $k => $v) {
                $sub0=$sub1=$sub2=$sub3='';
                $aa = explode(',', $k);
                $sub0 = $aa[0];
                $sub1 = $aa[1];
                $sub2 = $aa[2];
                $sub3 = $aa[3];
                $checkbox_arr[$k] = array(
                    'sub0' => $sub0,
                    'sub1' => $sub1,
                    'sub2' => $sub2,
                    'sub3' => $sub3,
                    'val' => $v,
                );
            }
        }

        if(!empty($checkname)){
            foreach ($checkname as $k => $v) {
                $subt0=$subt1=$subt2='';
                $aa_0 = explode(',', $k);
                $subt0 = $aa_0[0];
                $subt1 = $aa_0[1];
                $subt2 = $aa_0[2];
                $checkname[$k] = array(
                    'sub0' => $subt0,
                    'sub1' => $subt1,
                    'sub2' => $subt2,
                    'val' => $v,
                );
                foreach ($checkbox as $kk => $vv) {
                    $sub1=$sub2=$sub3='';
                    $aa_1 = explode(',', $kk);
                    $sub0 = $aa_1[0];
                    $sub1 = $aa_1[1];
                    $sub2 = $aa_1[2];
                    $sub3 = $aa_1[3];
                    if($subt2==$sub2){
                        $checkname[$k]['son'][$kk]= array(
                            'sub0' => $sub0,
                            'sub1' => $sub1,
                            'sub2' => $sub2,
                            'sub3' => $sub3,
                            'val' => $vv,
                        );
                    }
                }
            }
        }
        if(!empty($radioname)){
            foreach ($radioname as $k => $v) {
                $subt0=$subt1=$subt2='';
                $aa_0 = explode(',', $k);
                $subt0 = $aa_0[0];
                $subt1 = $aa_0[1];
                $subt2 = $aa_0[2];
                $radioname[$k] = array(
                    'sub0' => $subt0,
                    'sub1' => $subt1,
                    'sub2' => $subt2,
                    'val' => $v,
                );
                foreach ($radio as $kk => $vv) {
                    $sub1=$sub2=$sub3='';
                    $aa_1 = explode(',', $kk);
                    $sub0 = $aa_1[0];
                    $sub1 = $aa_1[1];
                    $sub2 = $aa_1[2];
                    $sub3 = $aa_1[3];
                    if($subt2==$sub2){
                        $radioname[$k]['son'][$kk]= array(
                            'sub0' => $sub0,
                            'sub1' => $sub1,
                            'sub2' => $sub2,
                            'sub3' => $sub3,
                            'val' => $vv,
                        );
                    }
                }
            }
        }
        $cc=array(
            1=>$radioname,
            2=>$checkname,
            3=>$text,
            4=>$txt
        );

        $ac=array();
        foreach($cc as $k=>$v){
            if($v){
                $ac= array_merge($v,$ac);
            }
        }
        $am=$ac;
        $wai_index_max=0;
//       $am= array_merge($radioname,$checkname,$text,$txt);
        foreach($am as $k=>$v){
            $aa=explode(',',$k);
            if($aa[2]>$wai_index_max){
                $wai_index_max=$aa[2];
            }
        }
        $arr=array();
        if(!empty($radioname)||!empty($checkname)||!empty($text)||!empty($txt)){
            $arr['w_indexmax']=$wai_index_max;
        }
        if(!empty($radioname)){
            $arr['desc']['radioname']=$radioname;
        }
        if(!empty($text)){
            $arr['desc']['text']=$text;
        }
        if(!empty($txt)){
            $arr['desc']['txt']=$txt;
        }
        if(!empty($checkname)){
            $arr['desc']['checkname']=$checkname;
        }
        return $arr;
    }
    /*
     * @shop_url   关于店铺跳转的URL
     * err ==1  表示手机（默认）
     *  err ==2  表示PC（默认）
     *  type  类型：index 为列表，add为添加，edit为编辑，del为删除，top为置顶，mylist为详情页面
     * */
    public function activity_url($type='index',$err=1){
        $c_0=explode('&',$_SERVER["QUERY_STRING"]);
        $c_1=explode('=',$c_0[1]);
        $c=$c_1[1];
        if($err==1){
            return $url="index.php?a={$type}&c={$c}&id=";
        }
    }

    /*
     *  @page_where  根据情况给定动态加载信息！
     *  @$p  当前页数
     *  @$list   当前根据页数查询出的数据
 *      @$pagesize   每页显示条数
     * */

    public function page_where($p,$list,$pagesize){
        $Nws=array();
        if($p==1){
            if(count($list)==0){
                $goods['length']=0;
                $Nws='对不起，你查询的数据不存在，请看看别的吧';
            }elseif(count($list)>=0 && count($list)<$pagesize){
                $Nws='你的数据加载完毕！';
            }elseif(count($list)==$pagesize){
                $Nws='向上拉加载更多！';
            }
        }elseif($p>1){
            if(count($list)==0){
                $Nws='你的数据加载完毕';
                $goods['length']=0;
            }elseif(count($list)>=0 && count($list)<$pagesize){
                $Nws='你的数据加载完毕！';
                $goods['length']=0;
            }elseif(count($list)==$pagesize){
                $Nws='向上拉加载更多！';
            }
        }
        return $Nws;
    }
    public function maxIndex($text){
        $max=0;
        $tmaxs=array();
        foreach($text as $k => $v){
            $tmaxs=explode(',',$k);
            if($tmaxs[2]>$max){
                $max=$tmaxs[2];
            }
        }
        return $max;
    }
    /**
     * 图片删除
    **/
    public function del_image($id){
        $ActivityObj =new Activity();
        $thumimg=array();
        if(!$id){
            $thumimg['isok']='false';
            $thumimg['data']='参数错误';
        }
        $gimages=$ActivityObj->getSingleFiledValues($id);
//        $aa= $ActivityObj->del($id);
        @unlink(PATH_ROOT . $gimages ['activity_thumb'] );
        @unlink(PATH_ROOT . $gimages ['activity_img']);
        @unlink(PATH_ROOT . $gimages ['activity_large']);
        @unlink(PATH_ROOT . $gimages ['sourcepic'] );
        $thumimg['isok']='true';
        $thumimg['data']='图片删除成功';
        return $thumimg;
    }


   /**
    * 活动查询共用
    * $type  来源  0表示个人中心点击出来的需要用户验证； 1  为从首页点击出来的不需要加入个人信息并且要查询是否过期
   **/
    public function mylist($type=0)
    {

        date_default_timezone_set('PRC'); //设置中国时区
        $ActivityObj = new Activity();
        $id = (int)Buddha_Http_Input::getParameter('id');
        if (!$id) {
            Buddha_Http_Head::redirectofmobile('参数错误！', 'index.php?a=index&c=activity', 2);
        }

        if($id==1){
            $where="id={$id} ";
        }else{
            if($type==0){
                $uid = Buddha_Http_Cookie::getCookie('uid');
                $where="id={$id} and user_id={$uid}";
            }else{
                $time=time();
                $where="id={$id} and {$time}<=end_date and buddhastatus=0 and isdel=0 and is_sure=1";
            }
        }

        $activity = $ActivityObj->getSingleFiledValues('', $where);
        if (!$activity) {
            if($id==1){//因为没有默认显示id=1的信息所以：id=1排除
                $time=time();
                $where="id={$id}  and buddhastatus=0 and isdel=0 and is_sure=1";
                $activity = $ActivityObj->getSingleFiledValues('', $where);
            }else{
                Buddha_Http_Head::redirectofmobile('您没有权限查看或商品已删除！', 'index.php?a=index&c=activity', 2);
            }
        }
         ////////分享
        $WechatconfigObj  = new Wechatconfig();
        $sharearr = array(
            'share_title'=>$activity['name'],
            'share_desc'=>$activity['brief'],
            'ahare_link'=>"index.php?a=".__FUNCTION__."&c=".$this->c,
            'share_imgUrl'=>$activity['activity_thumb'],
        );
        $WechatconfigObj->getJsSign($sharearr['share_title'],$sharearr['share_desc'],$sharearr['ahare_link'],$sharearr['share_imgUrl']);
        ////////分享
        $newtime = time();


        $cc=0;//活动未开始可以报名
        if ($newtime < $activity['start_date']) {//如果当前时间小于开始时间则是距离开始时间
            $aa = 1;//距离开始
            $bb=0;//活动未开始
        }elseif ($activity['start_date'] <= $newtime && $newtime <= $activity['end_date']) {//如果当前时间小于等于开始时间则是距离结束时间
            $aa = 2;//距离结束距离结束
            $bb=2;//活动进行中
        }elseif($newtime > $activity['end_date']){
            $aa = 0;//活动已经结束
            $bb=1;//活动已结束
        }
        if(!empty($activity['sign_start_time']) && !empty($activity['sign_end_time'])){
            if ($newtime < $activity['sign_start_time']) {//如果当前时间小于报名开始时间则是距离开始时间
                $cc = 0;//活动报名未开始不可以报名
            }elseif ($activity['sign_start_time'] <= $newtime && $newtime <= $activity['sign_end_time']) {//如果当前时间小于等于开始时间则是距离结束时间
                $cc = 0;//活动处于报名期间可以报名
            }elseif($newtime > $activity['end_date']){
                $cc = 1;//活动报名已结束
            }
        }elseif(empty($activity['sign_start_time']) && empty($activity['sign_end_time'])){
            if ($newtime < $activity['start_date']) {//如果当前时间小于开始时间则是距离开始时间
                $cc = 0;//活动未开始可以报名
            }elseif ($activity['start_date'] <= $newtime && $newtime <= $activity['end_date']) {//如果当前时间小于等于开始时间则是距离结束时间
                $cc = 0;//活动已开始不可以报名
            }elseif($newtime > $activity['end_date']){
                $cc = 1;//活动已结束不可以报名
            }
        }else if(!empty($activity['sign_start_time'])){//如果报名开始时间不为空，
            if(empty($activity['sign_end_time'])){//如果报名结束时间为空，则以活动结束时间为报名结束时间
                if($newtime < $activity['sign_start_time']){//当前时间小于活动报名时间（报名未开始）
                    $cc = 3;//报名未开始（不可报名）
                }else if($newtime <= $activity['sign_start_time'] && $newtime <= $activity['end_time']){//当前时间小于报名开始时间（活动报名未开始）
                    $cc = 0;//活动报名开始
                }else if($newtime>$activity['end_time']){
                    $cc = 4;//活动已结束（不可报名）
                }
            }else if(!empty($activity['sign_end_time'])){
                if($newtime < $activity['sign_start_time']){//当前时间小于活动报名时间（报名未开始）
                    $cc = 3;//报名未开始（不可报名）
                }else if($newtime <= $activity['sign_start_time'] && $newtime <= $activity['sign_end_time']){//当前时间小于报名开始时间（活动报名未开始）
                    $cc = 0;//活动报名开始
                }else if($newtime>$activity['end_time']){
                    $cc = 4;//活动已结束（不可报名）
                }
            }
        }else if(empty($activity['sign_end_time'])){//如果报名结束时间为空，
            if(empty($activity['sign_start_time'])) {//如果报名开始时间为空，则以活动审核通过时间为报名开始时间（）
                if($newtime < $activity['end_time']) {//当前时间小于活动报名时间（报名未开始）
                    $cc = 0;//报名开始（可报名）
                } else if($newtime <= $activity['start_time'] && $newtime <= $activity['end_time']){//当前时间小于报名开始时间（活动报名未开始）
                    $cc = 0;//活动报名开始
                }else if($newtime>$activity['end_time']){
                    $cc = 4;//活动已结束（不可报名）
                }
            }elseif (!empty($activity['sign_start_time'])){//如果报名结束时间不为空，
                if($newtime < $activity['sign_start_time']){//当前时间小于活动报名时间（报名未开始）
                    $cc = 3;//报名未开始（不可报名）
                }else if($newtime <= $activity['sign_start_time'] && $newtime <= $activity['end_time']){//当前时间小于报名开始时间（活动报名未开始）
                    $cc = 0;//活动报名开始
                }else if($newtime>$activity['end_time']){
                    $cc = 4;//活动已结束（不可报名）
                }
            }
        }

        $activity['start_date_ns']=date('m-d H:i',$activity['start_date']);
        $activity['end_date_ns']=date('m-d H:i',$activity['end_date']);
        $activity['start_date_s']=date('Y-m-d H:i:s',$activity['start_date']);
        $activity['end_date_s']=date('Y-m-d H:i:s',$activity['end_date']);
        $activity['sign_start_time']=date('m-d H:i',$activity['sign_start_time']);
        $activity['sign_end_time']=date('m-d H:i',$activity['sign_end_time']);
        $activity['time_b'] = $aa;  //  距离开始还是结束或者活已经结束了cc
        $activity['time_c'] = $cc;  //  活动开始了就不能报名了；0可以报名，1 不可以报名
        $activity['time_e'] = $bb;

        if(!empty($activity['form_desc'])){
            $activity['form_desc'] = unserialize($activity['form_desc']);
        }
        $this->smarty->assign('act', $activity);

//浏览 次数
        /**↓↓↓↓↓↓↓↓↓↓↓↓ 推荐 ↓↓↓↓↓↓↓↓↓↓↓↓**/
        $CommonObj = new Common();
        $recommend = $CommonObj->recommendBelongShop($activity['shop_id'],$this->tablename,$id);
        $this->smarty->assign('recommend', $recommend);
//print_r($recommend);
        /**↑↑↑↑↑↑↑↑↑↑↑↑ 推荐 ↑↑↑↑↑↑↑↑↑↑**/

        $data['click_count'] = $activity['click_count'] + 1;
        $ActivityObj->edit($data, $id);

        if($activity['type']==2){//合作商家(多商家)
            $ActivitycooperationObj = new Activitycooperation();
            $coopwhere="act_id={$id} and is_sure=1 and sure=1";
            $acoonum = $ActivitycooperationObj->countRecords($coopwhere);
            $ShopObj = new Shop();
            if ($acoonum) {
                $acoo = $ActivitycooperationObj->getFiledValues('',$coopwhere);
                foreach ($acoo as $k => $v) {//查询店铺名称和logo
                    $shop = $ShopObj->getSingleFiledValues(array('name', 'small'), "id={$v['shop_id']} and is_sure=1 and state=0");
                    $acoo[$k]['shop_name'] = mb_substr($shop['name'],0,6) ;
                    $acoo[$k]['shop_logo'] = $shop['small'];
                }
                $aco['aco'] = $acoo;
                $aco['surl'] = $ShopObj->shop_url();
            } else {
                $aco = '';
            }
            $this->smarty->assign('aco', $aco);
        }
    }
    //个人商家共用报名和问卷调查
    // $soure  来源  0表示个人中心点击出来的需要用户验证;   1为从首页点击出来的不需要加入个人信息
    /**
     * @param int $soure
     * @return mixed
     * @$c  来源于哪一张表
     */
    public function ajaxmylist($soure=1)
    {
        $ActivityObj = new Activity();
        $uid = Buddha_Http_Cookie::getCookie('uid');
        $id = (int)Buddha_Http_Input::getParameter('id');
        $user = Buddha_Http_Input::getParameter('user');
        $message = Buddha_Http_Input::getParameter('message');
        $phone = Buddha_Http_Input::getParameter('phone');
        $txt = Buddha_Http_Input::getParameter('txt');//单行
        $text = Buddha_Http_Input::getParameter('text');//多行
        $radioname = Buddha_Http_Input::getParameter('radioname');//单选
        $checkname = Buddha_Http_Input::getParameter('checkname');//多选


        $this-> update_number($id,$checkname,$radioname);//更新活动中单选多选的点击量

        $ActivityquestionnaireObj = new Activityquestionnaire();
        $ActivityapplicationObj = new Activityapplication();
        if($soure==1){
            $where="id={$id} and buddhastatus=0 and is_sure=1";
        }else{
            $where="id={$id} and user_id={$uid}";
        }
        $c='activity';
        if (!$id) {
            Buddha_Http_Head::redirectofmobile('参数错误！', "index.php?a=index&c={$c}", 2);
        }else
            $activity = $ActivityObj->getSingleFiledValues('', $where);
        if (!$activity) {
            Buddha_Http_Head::redirectofmobile('您没有权限查看或商品已删除！', "index.php?a=index&c={$c}", 2);
        }
        $data_q['table'] = 'activityapplication';
        if ($user == '' && $phone == '') {
            $uid = Buddha_Http_Cookie::getCookie('uid');
                if (empty($uid)) {   //判断该用户是否存在(是否登录或)
                    $datas['isok'] = 'false';
                    $datas['type'] = 1;
                    $datas['data'] = '你未登录或未注册，请登录或填写姓名和手机号后再报名!';
                } else {
                    $count = $ActivityapplicationObj->countRecords("u_id={$uid} and ac_id={$id}");// //判断用户是否已经报名了
                    if ($count == 0) {
                        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
                        //判断用户姓名或联系方式是否为空
                        if($UserInfo['mobile']=='' || $UserInfo['realname']==''){
                            $datas['isok'] = 'false';
                            $datas['type'] = 2;
                            $datas['data'] = '你的用户信息不完整，为了更好为你服务请完整后再提交或填写姓名和手机号后提交！';
                        }else{
                            $data['ac_id'] = $id;
                            $data['u_id'] = $uid;
                            $data['username'] = $UserInfo['realname'];
                            if($UserInfo['mobile']=='' || $UserInfo['tel']==''){
                                $datas['isok'] = 'false';
                                $datas['type'] = 2;
                                $datas['data'] = '你的用户信息不完整，为了更好为你服务请完整后再提交或填写姓名和手机号后提交！';
                            }elseif(!$UserInfo['mobile']){
                                $data['phone'] = $UserInfo['tel'];
                            }elseif($UserInfo['mobile']){
                                $data['phone'] = $UserInfo['mobile'];
                            }
                            $data['addtime'] = time();
                            $data['message'] = $message;
							$data['tablename'] = $this->c;
                            $num = $ActivityapplicationObj->add($data);
                            if ($num) {
                                $aqtcount = $ActivityquestionnaireObj->countRecords("ap_id={$uid} and act_id={$id}"); //判断用户是否已经填过问卷了
                                if($aqtcount){
                                    $datas['isok'] = 'false';
                                    $datas['type'] = 2;
                                    $datas['data'] = '问卷你已经填写过了，请不要重复提交！';
                                }else{
                                    $ActivityquestionnaireObj->countRecords();
                                    $data_q['act_id'] = $id;
                                    $data_q['ap_id'] = $num;//报名表中的ID
                                    if($txt){$coo_arr['txt']=$txt;}
                                    if($text){$coo_arr['text']=$text;}
                                    if($radioname){$coo_arr['radioname']=$radioname;}
                                    if($checkname){$coo_arr['checkname']=$checkname;}
                                    if($coo_arr){
                                        $data_q['coo_arr'] =serialize($coo_arr);
                                    }else{
                                        $data_q['coo_arr'] ='';
                                    }
                                    $aqtid = $ActivityquestionnaireObj->add($data_q);
                                    $ActivityObj=new Activity();
                                    $ActivityObj-> update_number($id,$checkname,$radioname);//更新活动中单选多选的点击量
                                    if ($aqtid) {
                                        $datas['isok'] = 'true';
                                        $datas['type'] = 2;
                                        $datas['data'] = '报名成功！';
                                    } else {
                                        $datas['isok'] = 'false';
                                        $datas['type'] = 2;
                                        $datas['data'] = '问卷添加失败！';
                                    }
                                }
                            } else {
                                $datas['isok'] = 'false';
                                $datas['type'] = 1;
                                $datas['data'] = '报名失败!';
                            }
                        }
                    } else {
                        $datas['isok'] = 'false';
                        $datas['type'] = 1;
                        $datas['data'] = '您已经报过名了,请不要重复报名！';
                    }
                }
        } else {
//判断用户是否已经报名了
            $count = $ActivityapplicationObj->countRecords("phone={$phone} and ac_id={$id}");
            if ($count == 0) {
                $data['ac_id'] = $id;
                $data['u_id'] = 0;
                $data['username'] = $user;
                $data['phone'] = $phone;
                $data['addtime'] = time();
                $data['message'] = $message;
				$data['tablename'] = $this->c;
                $num = $ActivityapplicationObj->add($data);
                if ($num) {
                    $aqtcount = $ActivityquestionnaireObj->countRecords("ap_id={$num} and act_id={$id}"); //判断用户是否已经填过问卷了
                    if($aqtcount){
                        $datas['isok'] = 'false';
                        $datas['type'] = 2;
                        $datas['data'] = '问卷你已经填写过了，请不要重复提交！';
                    }else{
                        $ActivityquestionnaireObj->countRecords();
                        $data_q['act_id'] = $id;
                        $data_q['ap_id'] = $num;//报名表中的ID
                        if($txt){$coo_arr['txt']=$txt;}
                        if($text){$coo_arr['text']=$text;}
                        if($radioname){$coo_arr['radioname']=$radioname;}
                        if($checkname){$coo_arr['checkname']=$checkname;}
                        if($coo_arr){
                            $data_q['coo_arr'] =serialize($coo_arr);
                        }else{
                            $data_q['coo_arr'] ='';
                        }
                        $aqtid = $ActivityquestionnaireObj->add($data_q);
                        $ActivityObj=new Activity();
                        $ActivityObj-> update_number($id,$checkname,$radioname);//更新活动中单选多选的点击量
                        if ($aqtid) {
                            $datas['isok'] = 'true';
                            $datas['type'] = 2;
                            $datas['data'] = '报名成功！';
                        } else {
                            $datas['isok'] = 'false';
                            $datas['type'] = 2;
                            $datas['data'] = '问卷添加失败！';
                        }
                    }
                } else {
                    $datas['isok'] = 'false';
                    $datas['type'] = 1;
                    $datas['data'] = '报名失败!';
                }
            } else {
                $datas['isok'] = 'false';
                $datas['type'] = 1;
                $datas['data'] = '您已经报过名了,请不要重复报名！';
            }
        }
        return $datas;
    }

    //个人商家报名查询   $source来源 0 为首页 1为人人中心
   public  function signup($source=0){
       if($source==1){
           $uid = Buddha_Http_Cookie::getCookie('uid');
       }
        $id=(int)Buddha_Http_Input::getParameter('id');
        $ActivityapplicationObj =new Activityapplication();
        $ActivityObj=new Activity();
       if($source==0){
           $Actwhere="id={$id}";//来源于个人中心就不用判断是否上架
       }else{
           $Actwhere="id={$id} and buddhastatus=0 and is_sure=1";
       }
        $Actnum=$ActivityObj->countRecords($Actwhere);//判断活动是否存在
        if($Actnum){
            $user_act= $ActivityapplicationObj->getFiledValues('',"ac_id={$id} order by id desc");
            if(!empty($user_act)){
                $UserObj=new User();
                foreach($user_act as $k=>$v){
                    if($v['u_id']>0){//注册用户
                        $userlogo= $UserObj->getSingleFiledValues(array('logo'),"id={$v['u_id']}");
                        if($userlogo['logo']){
                            $user_act[$k]['logo']=$userlogo['logo'];
                        }else{
                            $user_act[$k]['logo']='style/images/im.png';//没有头像给默认头像
                        }
                    }else{
                        $user_act[$k]['logo']='style/images/im.png';//非注册用户给默认头像
                    }
                    if($source==0){
                        $user_act[$k]['username']=mb_substr($v['username'],0,1).'**';
                    }
                }
                $datas['isok']='true';
                $datas['data']=$user_act;
            }else{
                $datas['isok']='false';//屏蔽原因在首页点击进入的不用提示
                $datas['data']=0;

            }
        }else{
            $datas['isok']='false';
            $datas['data']='活动不存在!';
        }
        return $datas;
    }

//查询活动的公共条件
//$is_area =0 不加入查询地区条件 =1  加入地区条件
//$is_time =0 不加入查询时间 =1  加入时间条件
    function act_public_where($is_area=0){
        $where=' state=0 and is_sure=1 and isdel=0 ';
        if($is_area==1){
            $RegionObj=new Region();
            $locdata = $RegionObj->getLocationDataFromCookie();
            $where.=$locdata['sql'];
        }
        return $where;
    }



    //更新活动问题的被点击数量
    //$id 活动ID 、
    function update_number($id,$checkname,$radioname){
        //==========================================================
        //更新活动表单问题点击量
        $ActivityObj=new Activity();
        $acti=$ActivityObj->getSingleFiledValues(array('id','form_desc'),"id ={$id}");

        if($acti['form_desc']){
            $form_desc=unserialize( $acti['form_desc']);
            foreach($form_desc['desc']['radioname'] as $k=>$v){
                foreach($radioname as $krk=>$vrk){
                    if($k==$krk){
                        foreach($v['son'] as $kk=>$vv){
                            if($vv['sub3']==$vrk){
                                if(array_key_exists('number', $vv)){//判断某个键是否存在
                                    $form_desc['desc']['radioname'][$k]['son'][$kk]['number']= $vv['number']+1;
                                }else{
                                    $form_desc['desc']['radioname'][$k]['son'][$kk]['number']= 1;
                                }
                            }
                        }
                    }
                }
            }
            $checknamekey=array_keys($checkname);
            foreach($checknamekey as $k=>$v){
                $index=strripos($v,',');
                $subindex=substr($v,0,$index);
                foreach($checkname as $kn=>$vn){
                    if($v==$kn){
                        $checknamekey[$k]=array(
                            0=>$subindex,
                            1=>$kn,
                            2=>$vn
                        );
                    }
                }
            }
            foreach($form_desc['desc']['checkname'] as $k=>$v){
                foreach($checknamekey as $kn=>$vn){
                    if($k==$vn[0]){
                        foreach($v['son'] as $kk=>$vv){
                            if($kk==$vn[1]&&$vv['sub3']==$vn[2]){
                                if(array_key_exists('number', $vv)){//判断某个键是否存在
                                    $form_desc['desc']['checkname'][$k]['son'][$kk]['number']= $vv['number']+1;
                                }else{
                                    $form_desc['desc']['checkname'][$k]['son'][$kk]['number']= 1;
                                }
                            }
                        }
                    }
                }
            }
        }
        if($form_desc){
            $dat_f['form_desc']=serialize($form_desc);
        }else{
            $dat_f['form_desc']=$form_desc;
        }

        $num= $ActivityObj->edit($dat_f,$id);
        return $num;
    }
    //查询活动详情和奖品设置
    public function vodeprize(){
        $ActivityObj =new Activity();
        $c=$this->c;
        $id= Buddha_Http_Input::getParameter('id')?Buddha_Http_Input::getParameter('id'):0;
        if(empty($id)){
            Buddha_Http_Head::redirectofmobile('参数错误！',"index.php?a=index&c=".$c,2);
            exit;
        }
        $Act=  $ActivityObj->fetch($id);
        $this->smarty->assign('Act', $Act);
    }

    public function vodelist($c){
        $ActivityObj =new Activity();
        $ActivitycooperationObj =new Activitycooperation();
        $id= Buddha_Http_Input::getParameter('id')?Buddha_Http_Input::getParameter('id'):0;
        $url=array(
            'prize'=>'index.php?a=vodeprize&c='.$c.'&id='.$id,
            'ranking'=>'index.php?a=voderanking&c='.$c.'&id='.$id,
            'sign'=>'index.php?a=vodesign&c='.$c.'&id='.$id,
            'all'=>'index.php?a=cooshop&c='.$c.'&id='.$id,
        );

        $Act= $ActivityObj->fetch($id);//查询活动数据
//        ---更新浏览次数
        $data['click_count']=$Act['click_count']+1;
        $ActivityObj->edit($data,$id);//更新浏览次数
//        --------


        $coowhere=' act_id='.$id;
        $count=$ActivitycooperationObj->countRecords($coowhere);//统计参加活动的商家数量
        $coo=$ActivitycooperationObj->getFiledValues($coowhere);//查询参加活动的商家

        $sql ="select sum(praise_num) as num from {$this->prefix}activitycooperation where {$coowhere} ";//求和：投票的总数
        $praise_num = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    //     ↓↓↓↓↓↓冠名商家查询↓↓↓↓↓↓
            $MoregalleryObj=new Moregallery();
            $More= $MoregalleryObj->getFiledValues(array('id','goods_img','shop_id'),"goods_id={$id} and tablename='{$c}' and webfield='file_title'");
    //     ↑↑↑↑↑↑↑↑↑↑冠名商家查询 ↑↑↑↑↑↑↑↑↑↑
    //     ↓↓↓↓↓↓ 头部轮播图组装 ↓↓↓↓↓↓

            $carousel=array();
            $carousel[0]['goods_img']=$Act['activity_img'];
            $carousel[0]['shop_id']=0;
            foreach($More as $k=>$v){
                array_push($carousel,$v);
            }
            $ShopObj=new Shop();
            $car['list']=$carousel;
            $car['shop_url']=$ShopObj->shop_url();
    //     ↑↑↑↑↑↑↑↑↑↑头部轮播图组装 ↑↑↑↑↑↑↑↑↑↑

//        print_r($car);
        ////////分享
        $WechatconfigObj  = new Wechatconfig();
        $sharearr = array(
            'share_title'=>$Act['name'],
            'share_desc'=>$Act['brief'],
            'ahare_link'=>"index.php?a=".__FUNCTION__."&c=".$this->c,
            'share_imgUrl'=>$Act['activity_thumb'],
        );
        $WechatconfigObj->getJsSign($sharearr['share_title'],$sharearr['share_desc'],$sharearr['ahare_link'],$sharearr['share_imgUrl']);
        ////////分享

        $this->smarty->assign('car', $car);
        $this->smarty->assign('id', $id);
        $this->smarty->assign('url', $url);
        $this->smarty->assign('Act', $Act);
        $this->smarty->assign('count', $count);

        $this->smarty->assign('praise_num', $praise_num[0]['num']);
    }

    //比赛排名
    public function voderanking($c)
    {
        $id= Buddha_Http_Input::getParameter('id')?Buddha_Http_Input::getParameter('id'):0;
        if(empty($id)){
            Buddha_Http_Head::redirectofmobile('参数错误！',"index.php?a=index&c=".$c,2);
            exit;
        }

//======查询商家排名===
        $orderby=' order by praise_num desc';
        $filed=array('id','shop_id','shop_name','praise_num');
        $ActivitycooperationObj=new Activitycooperation();
        $where=' act_id='.$id;
        $list = $ActivitycooperationObj->getFiledValues ($filed, $where . $orderby);//查询商家排名

        foreach($list as $k=>$v){
            $list[$k]['shop_name']=mb_substr($v['shop_name'],0,15) ;
        }
        $this->smarty->assign('list', $list);
        $ShopObj=new Shop();
        $this->smarty->assign('shop_url', $ShopObj->shop_url());
//=========

        $ActivityObj =new Activity();
        $Act=$ActivityObj->getSingleFiledValues(array('activity_img'),'id='.$id);//查询活动的图片
        $this->smarty->assign('id', $id);
        $this->smarty->assign('activity_img', $Act['activity_img']);
    }

    /**
     * @param $api_number
     * @param string $num
     * @param string $b_display 1pc 2mobile
     * @return mixed
     */
    public function getActivityArr($api_number,$num='',$b_display=''){
        $host = Buddha::$buddha_array['host'];
        $ActivityObj = new Activity();
        $RegionObj = new Region();
        if(!$RegionObj->isValidRegion($api_number)){
            Buddha_Http_Output::makeWebfaceJson(null, '/webface/?Services=' . $_REQUEST['Services'], 4444007, '地区编码不对（地区编码对应于腾讯位置adcode）');
        }
        $regnum = $RegionObj->getApiLocationByNumberArr($api_number);
        $where = "";
        $page = Buddha_Http_Input::getParameter('page')?Buddha_Http_Input::getParameter('page'):1;
        $pagesize = Buddha_Http_Input::getParameter('pagesize')?Buddha_Http_Input::getParameter('pagesize'):15;
        $time=time();
        $where .= " isdel=0 and is_sure=1 and buddhastatus=0  and {$time}<=end_date {$regnum['sql']} order by  add_time DESC ";
        if(!$num){
            $sql = "select count(*) as total from {$this->prefix}activity where {$where}";
            $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $rcount = $count_arr[0]['total'];
            $pcount = ceil($rcount / $pagesize);
            if ($page > $pcount) {
                $page = $pcount;
            }
            $where .=  Buddha_Tool_Page::sqlLimit ( $page, $pagesize );
        }elseif(!$b_display){
            $where .= " LIMIT 0,6 ";
        }
        $filed=array('id as act_id','name','activity_thumb','brief','address','type');
        $Db_activity_arr = $ActivityObj->getFiledValues($filed, $where);
        if(count($Db_activity_arr)<1){
            $Db_activity_arr = $ActivityObj->getFiledValues($filed, "id in(47,50,85,89) order by  add_time DESC");//没有数据显示默认
        }
        //print_r($ShopPro);
        foreach ($Db_activity_arr as $k => $v) {
            if($v['activity_thumb']){
                $Db_activity_arr[$k]['activity_thumb'] = $host.substr($v['activity_thumb'],1);
            }
            if(mb_strlen($v['brief']) > 35){
                $v['brief'] = mb_substr($v['brief'],0,35) . '...';
            }
            if(mb_strlen($v['name']) > 18){
                $v['name'] = mb_substr($v['name'],0,18) . '...';
            }
        }
        if($rcount){
            $jsondata = array();
            $jsondata['page'] = $page;
            $jsondata['pagesize'] = $pagesize;
            $jsondata['totalrecord'] = $rcount;
            $jsondata['totalpage'] = $pcount;
            $jsondata['list'] = $Db_activity_arr;
        }else{
            $jsondata =  $Db_activity_arr;
        }
        return $jsondata;
    }








}