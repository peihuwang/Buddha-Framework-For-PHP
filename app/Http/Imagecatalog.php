<?php
class Imagecatalog extends  Buddha_App_Model{
    public function __construct(){
        parent::__construct();
        $this->table = strtolower(__CLASS__);
    }

   public function imageIdentification($identify){
       $ider=  array(
            'mobile_home_1'=>'首页上方区域广告位',
            'mobile_home_2'=>'首页中间区域广告位',
            'mobile_home_3'=>'首页底部区域广告位',
        );
       if(array_key_exists($identify,$ider)){
           return 1;
       }else{
           return 0;
       }
   }

    public function getImagecatalogDotIdBy($identify){

       $check=  $this->imageIdentification($identify);
        if($check){
            $num = $this->countRecords("isdel=0 and identify='{$identify}' ");
            if($num==0){
                return 0;
            }
            else{
              $arr =     $this->getSingleFiledValues("","isdel=0 and identify='{$identify}' ");
              return $arr['id'];
            }


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
        $topdata = $this->db->getFiledValues('',  $this->table," (isdel=0 or isdel=10) order by id asc");
        $this->getCateOption ( $topdata,$table,0,$id);
        return $table;
    }

    public function getOptionuser($id=0){
        $table='';
        $topdata = $this->db->getFiledValues('',  $this->table," (isdel=0 or isdel=10) and isopen=1 order by id asc");
        $this->getCateOption ( $topdata,$table,0,$id);
        return $table;
    }

    public function getCateOption($cate, &$table, $startID = 0, $index = 0, $level = 0) {
        foreach ( $cate as $key => $value ) {
            if ($value ['sub'] == $startID) {
                $table .= '<option value="' . $value ['id'] . '"';
                $value ['id'] == $index && $table .= ' selected="select" style="background:#ffffde"';
                $table .= '>' . str_repeat ( '&nbsp;&nbsp;', $level ) . '└ ' . htmlspecialchars ( $value ['name'] ) . '</option>';
                $this->getCateOption ( $cate, $table, $value ['id'], $index, $level + 1 );
            }
        }
    }
    public function getcatlist(){
        $table='';
        $topdata = $this->db->getFiledValues('',$this->table, " (isdel=0 or isdel=10) order by view_order asc");
        $this->getcatTable($topdata,$table,0);
        return $table;

    }



    public function hasChild($sub){
        return $this->db->countRecords($this->table, "sub={$sub}");
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
                if ($v['buddhastatus']==0){
                    $checked = 'checked="checked"';
                    $isopen='是';
                }
                $table .= '<tr  pid="'.$v ['sub'] .'" cid="'. $v ['id'] .'" depath="'.$level .'">
					<td><input type="checkbox"  '.$checked.'  value="' .$v ['id'] .'" name="cate['.$v ['id'] .'][buddhastatus]" class="i-checks" ></td>
					<td><div class="form-group" > ' . $ds . ' ' . $cup . '</div><div class="form-group">' . $v ['name'] . '</div></td>
                  <td>'.$isopen.'</td>
					<td>';
                $table .= '<a title="添加子类" href="index.php?a=add&c=imagecatalog&cid=' . $v ['id'] . '">[添加子类]</a>
			           <a title="编辑" href="index.php?a=edit&c=imagecatalog&id=' . $v ['id'] . '">[编辑]</a>
			           <a onclick="return delnav();" href="index.php?a=del&c=imagecatalog&id=' . $v ['id'] . '" title="删除">[删除]</a>';
                $table .= '</td></tr> ';
                $this->getcatTable ( $cates, $table, $v['id'], $level + 1 );
            }
        }
    }
/*
 * @  select_cat 查询某一个下的类别(默认为本地信息)
 * */
    function select_cat($sub=16){
        $where="isdel=0 and buddhastatus=0 and sub={$sub}";
        $order=' order by id asc ';
        $mobile_local= $this->getFiledValues('',$where.$order);
        return $mobile_local;
    }





}