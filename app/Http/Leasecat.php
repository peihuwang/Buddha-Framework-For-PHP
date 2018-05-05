<?php
class Leasecat extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    /**
     * @param $recruitcat_id
     * @return int
     */

    public function getReleasecatNamebyReleasecatid($recruitcat_id){

        $Db_Releasecat = $this->getSingleFiledValues(array('id','cat_name'),"id='{$recruitcat_id}' AND  ifopen=0 AND isdel=0");
        if(Buddha_Atom_Array::isValidArray($Db_Releasecat)){
            return $Db_Releasecat['cat_name'];
        }else{
            return '';
        }

    }







    /**
     * 是否存在此招聘分类
     * @param $recruitcat_id
     * @return int
     * @author wph 2017-09-20
     */
    public function isHasRecord($recruitcat_id){

        $num = $this->countRecords("id='{$recruitcat_id}' ");
        if($num){
            return 1;
        }else{
            return 0;
        }

    }

    public function getClassPath($cat_id, $parentid) {
        $path = array ();
        while ( $parentid ) {
            if ($cat_id && $cat_id == $parentid) {
                return false;
            }
            array_unshift ( $path, $parentid );
            $row =$this->db->getSingleFiledValues(array('sub','cat_path'),$this->table, "id=".intval ( $parentid ));
            $parentid = $row ['sub'];
        }
        $catpath = implode ( ",", $path ) . ",";
        return $catpath;
    }

    public function updatepath($cat_id, $cat_path) {
        $result =$this->db->getFiledValues ( array ('id', 'cat_path' ),$this->table, "cat_path like '" . $cat_id . ",%' or sub=" . intval ( $cat_id ) . "" );
        foreach ( $result as $k => $v ) {
            if ($cat_path == ",") {
                unset ( $cat_path );
            }
            $path = $cat_path . substr ( $v ['cat_path'], strpos ( $v ['cat_path'], $cat_id . "," ), strlen ( $v ['cat_path']));
            $this->db->updateRecords (array('cat_path'=>$path),$this->table, "where id=" . intval ( $v ['id'] ) );

        }
    }

    public function updatechildcount($id, $cat_id = false) {
        if (!$id) {
            return false;
        }
        $child_count =$this->db->countRecords ( $this->table, "sub=" . intval ( $id ) );
        $this->db->updateRecords ( array ('child_count' => $child_count ),$this->table, "id=" . intval ( $id ) );

    }

    public function getOption($id=0){
        $table='';
        $topdata = $this->db->getFiledValues('',  $this->table," isdel=0 order by view_order asc");

        $this->getCateOption ( $topdata,$table,0,$id);
        return $table;

    }

    public function getCateOption($cate, &$table, $startID = 0, $index = 0, $level = 0) {
        foreach ( $cate as $key => $value ) {
            if ($value ['sub'] == $startID) {
                $table .= '<option value="' . $value ['id'] . '"';
                $value ['id'] == $index && $table .= ' selected="select" style="background:#ffffde"';
                $table .= '>' . str_repeat ( '&nbsp;&nbsp;', $level ) . '└ ' . htmlspecialchars ( $value ['cat_name'] ) . '</option>';
                $this->getCateOption ( $cate, $table, $value ['id'], $index, $level + 1 );
            }
        }
    }

    public function getcatlist(){
        $table='';
        $topdata = $this->db->getFiledValues('',$this->table, " isdel=0 order by  view_order ASC");
        $this->getcatTable($topdata,$table,0);
        return $table;

    }

    public function getcatTable($cates, &$table, $cid = 0, $level = 0) {
        foreach ( $cates as $k => $v ) {
            if ($v ['sub'] == $cid) {
                $ds = $cup = $link = '';
                if ($v['sub']== 0 && $v ['child_count']) {
                    $cup = '<i onclick="goods_cateopen('.$v ['id'] .')" class="fa fa-chevron-down" id="bt_'. $v['id'] .'"></i>';
                } elseif($v ['sub'] !=0 && $v ['child_count']){
                    $cup = '<i onclick="goods_cateopen('.$v ['id'] .')" class="fa fa-chevron-down" id="bt_' .$v['id'] .'"></i>';
                }
                $ds = '<i style=" padding:0 5px 0 5px">├─ </i>';
                $ds = str_repeat ( $ds, $level );
                $checked='';
                $isopen='否';
                if ($v['ifopen']==0){
                    $checked = 'checked="checked"';
                    $isopen='是';
                }
                $table .= '<tr  pid="'.$v ['sub'] .'" cid="'. $v ['id'] .'" depath="'.$level .'">
					<td><input type="checkbox"  '.$checked.'  value="' .$v ['ifopen'] .'" name="cate['.$v ['id'] .'][ifopen]" class="i-checks" ></td>
					<td><div class="form-group" > ' . $ds . ' ' . $cup . '</div><div class="form-group">' . $v ['cat_name'] . '</div></td>

                  <td>'.$isopen.'</td>
					<td>';
                $table .= '<a title="添加子类" href="index.php?a=add&c=leasecat&cid=' . $v ['id'] . '">[添加子类]</a>
			           <a title="编辑" href="index.php?a=edit&c=leasecat&id=' . $v ['id'] . '">[编辑]</a>
			           <a onclick="return delnav();" href="index.php?a=del&c=leasecat&id=' . $v ['id'] . '" title="删除">[删除]</a>';
                $table .= '</td></tr> ';
                $this->getcatTable ( $cates, $table, $v['id'], $level + 1 );
            }
        }
    }

    public function getLeasecatlist($sub){
        $Db_Demand= $this->db->getFiledValues(array('id','sub','cat_name','child_count'), $this->table, "sub='{$sub}' and isdel=0");
        return $Db_Demand;
    }

    public function goods_thumbgoods_thumb($shopcat_id){
        if($shopcat_id){
            $num = $this->countRecords(" isdel=0 and id='{$shopcat_id}'");
            if($num==0)
                return 0;
            $Db_region = $this->fetch($shopcat_id);
            $sub = $Db_region['sub'];
            $row[]= array('id'=>$Db_region['id'],'cat_name'=>$Db_region['cat_name'],'sub'=>$sub);
            $this->getArrayTr($row);
            $sort_arr = Buddha_Atom_Array::sort($row,'id');
            return $sort_arr;
        }else{
            return 0;
        }

    }

    public function getArrayTr( &$row) {
        $region_id =$row[count($row)-1]['id'];
        $sub =$row[count($row)-1]['sub'];
        if($sub) {
            $Db_region = $this->fetch($sub);
            $sub = $Db_region['sub'];
            $row[]= array('id'=>$Db_region['id'],'cat_name'=>$Db_region['cat_name'],'sub'=>$sub);
            $this->getArrayTr($row) ;
        }
    }


    public function getcategory(){
        $category = $this->getFiledValues(array('id', 'cat_name', 'sub'), "isdel=0 and ifopen=0 order  by id DESC");

        return $category;
    }

    public function getcatist($cid=0){
        $Db_Shopcat= $this->db->getFiledValues(array('id','sub','cat_name'), $this->table, "sub='{$cid}' and isdel=0");
        return $Db_Shopcat;
    }

    public function getDivRelation($cates, &$table, $cid = 0, $number = '') {
        $table .= '<ul>';
        foreach ( $cates as $k => $v ) {
            if ($v ['sub'] == $cid) {
                $haschild = 0;$name="<li data-href='index.php?a=index&c=lease&cid={$v['id']}'>{$v['cat_name']}</li";
                foreach ( $cates as $k1 => $v1 ) {
                    if($v['id']==$v1['sub']){
                        $haschild = 1;
                        $name="<li><span data-href='index.php?a=index&c=lease&cid={$v['id']}'>{$v['cat_name']}</span>";
                    }
                }
                $table .="{$name}";
                if($haschild)
                    $this->getDivRelation ( $cates, $table, $v['id'], $number );
                $table .="</li>";
            }
        }
        $table .= '</ul>';

    }


    //获取某个分类的所有子分类  
    public function getSubs($cates,$sub=0,$level=0){
        $subs = array();
        foreach($cates as $item){
            if($item['sub']==$sub){
                $item['level'] = $level;
                $subs[]=$item;

                $subs = array_merge($subs,$this->getSubs($cates,$item['id'],$level+1));
            }
        }
        return $subs;
    }

    public function getInSqlByID($cates,$sub){
        $retur_arr = $this->getSubs($cates,$sub,0);
        $temp_arr = array();
        if(count($retur_arr)){

            foreach($retur_arr as $k=>$v){
                $temp_arr[]=$v['id'];
            }
        }
        $temp_arr[]=$sub;
        return "(".implode(',',$temp_arr).")";


    }



}