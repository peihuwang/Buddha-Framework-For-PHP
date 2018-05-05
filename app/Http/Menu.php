<?php
class Menu extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }
    public function getOption($id=0){
        $table='';
        $_sql ="SELECT * FROM {$this->prefix}menu  WHERE  1=1 AND sub=0 ORDER  BY id ASC ";
        $topdata = $this->db->query($_sql)->fetchAll();
        $this->getCateOption ( $topdata,$table,0,$id);
        return $table;
    }

    public function getCateOption($cate, &$table, $startID = 0, $index = 0, $level = 0) {
        foreach ( $cate as $key => $value ) {
            if ($value ['sub'] == $startID) {
                $table .= '<option value="' . $value ['id'] . '"';
                $value ['id'] == $index && $table .= ' selected="select" style="background:#ffffde"';
                $table .= '> ' . htmlspecialchars ( $value ['name'] ) . '</option>';
                $this->getCateOption ( $cate, $table, $value ['id'], $index, $level + 1 );
            }
        }
    }
    function menu_apply_table() {
        $table='';
        $cates = $this->db->getFiledValues('',$this->prefix.'menu',"1=1 order by sort asc");
        $this->get_menu_table ($cates,$table,0);
        return $table;

    }
    public  function get_menu_table($cates,&$table, $cid = 0, $level = 0){
        foreach ($cates as $k => $v) {
            if ($v['sub'] == $cid) {
                $ds = $cup = $link = $biao= '';
                if ($v['sub'] != 0) {
                    $ds = '<i style=" padding:0 5px 0 5px">├─ </i>';
                    $link = 'index.php?a=' . $v['operat
                $checked = \'\';
                if ($v[\'isopen\']) {
                    $checked = \'checked=""\';
                }or'] . '&c=' . $v['services'] . '';
                    $biao='' . $v['operator'] .'>>'.$v['services'].'';
                } else {
                    $biao=''.$v['services'].'';
                    $cup = '<i onclick="goods_cateopen(' . $v ['id'] . ')" class="fa fa-chevron-down" id="bt_' . $v ['id'] . '">展开</i>';
                }
                $checked = '';
                if ($v['isopen']) {
                    $checked = 'checked=""';
                }
                $table .= '<tr  pid="' . $v ['sub'] . '" cid="' . $v ['id'] . '" depath="' . $level . '">
					<td><input type="checkbox"  ' . $checked . '  value="' . $v ['id'] . '" name="cate[' . $v ['id'] . ']" class="i-checks" ></td>
					<td><div class="form-group" > ' . $ds . ' ' . $cup . '</div><div class="form-group">' . $v ['name'] . '</div></td>
                   <td>' .$biao. '</td>
                  <td><a href="'.$link.'" target="_blank">'.$link.'</a></td>
					<td>';
                $table .= '
			           <a title="编辑" href="index.php?a=edit&c=menu&id=' . $v ['id'] . '">[编辑]</a>
			           <a onclick="return delnav();" href="index.php?a=del&c=menu&id=' . $v ['id'] . '" title="删除">[删除]</a>';
                $table .= '</td></tr> ';
                $this->get_menu_table($cates, $table, $v ['id'], $level + 1);
            }
        }
    }
}