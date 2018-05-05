<?php

/**
 * Class Articlecatalog
 */
class Articlecatalog extends Buddha_App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = strtolower(__CLASS__);

    }


    public function getClassPath($cat_id, $parentid)
    {
        $path = array();
        while ($parentid) {
            if ($cat_id && $cat_id == $parentid) {
                return false;
            }
            array_unshift($path, $parentid);
            $row = $this->db->getSingleFiledValues(array('sub', 'cat_path'), $this->table, "id=" . intval($parentid));
            $parentid = $row ['sub'];
        }
        $catpath = implode(",", $path) . ",";
        return $catpath;
    }

    public function updatepath($cat_id, $cat_path)
    {
        $result = $this->db->getFiledValues(array('id', 'cat_path'), $this->table, "cat_path like '" . $cat_id . ",%' or sub=" . intval($cat_id) . "");
        foreach ($result as $k => $v) {
            if ($cat_path == ",") {
                unset ($cat_path);
            }
            $path = $cat_path . substr($v ['cat_path'], strpos($v ['cat_path'], $cat_id . ","), strlen($v ['cat_path']));
            $this->db->updateRecords(array('cat_path' => $path), $this->table, "where id=" . intval($v ['id']));

        }
    }

    public function updatechildcount($id, $cat_id = false)
    {
        if (!$id) {
            return false;
        }
        $child_count = $this->db->countRecords($this->table, "sub=" . intval($id));
        $this->db->updateRecords(array('child_count' => $child_count), $this->table, "id=" . intval($id));

    }


    public function getOption($id = 0)
    {
        $table = '';
        $topdata = $this->db->getFiledValues('', $this->table, " (isdel=0 or isdel=10) order by view_order asc");

        $this->getCateOption($topdata, $table, 0, $id);

        return $table;

    }

    public function getCateOption($cate, &$table, $startID = 0, $index = 0, $level = 0)
    {
        foreach ($cate as $key => $value) {
            if ($value ['sub'] == $startID) {
                $table .= '<option value="' . $value ['id'] . '"';
                $value ['id'] == $index && $table .= ' selected="select" style="background:#ffffde"';
                $table .= '>' . str_repeat('&nbsp;&nbsp;', $level) . '└ ' . htmlspecialchars($value ['name']) . '</option>';
                $this->getCateOption($cate, $table, $value ['id'], $index, $level + 1);
            }
        }
    }

    public function getcatlist()
    {
        $table = '';
        $topdata = $this->db->getFiledValues('', $this->table, " (isdel=0 or isdel=10) order by id asc");
        $this->getcatTable($topdata, $table, 0);
        return $table;

    }

    public function hasChild($sub)
    {
        return $this->db->countRecords($this->table, "sub={$sub}");
    }

    public function getcatTable($cates, &$table, $cid = 0, $level = 0)
    {
        foreach ($cates as $k => $v) {
            if ($v ['sub'] == $cid) {
                $ds = $cup = $link = '';
                if ($v['sub'] == 0 && $v ['child_count']) {
                    $cup = '<i onclick="goods_cateopen(' . $v ['id'] . ')" class="fa fa-chevron-down" id="bt_' . $v['id'] . '"></i>';
                } elseif ($v ['sub'] != 0 && $v ['child_count']) {
                    $cup = '<i onclick="goods_cateopen(' . $v ['id'] . ')" class="fa fa-chevron-down" id="bt_' . $v['id'] . '"></i>';
                }
                $ds = '<i style=" padding:0 5px 0 5px">├─ </i>';
                $ds = str_repeat($ds, $level);
                $checked = '';
                $isopen = '否';
                if ($v['buddhastatus'] == 0) {
                    $checked = 'checked="checked"';
                    $isopen = '是';
                }
                $table .= '<tr  pid="' . $v ['sub'] . '" cid="' . $v ['id'] . '" depath="' . $level . '">
					<td><input type="checkbox"  ' . $checked . '  value="' . $v ['id'] . '" name="cate[' . $v ['id'] . '][buddhastatus]" class="i-checks" ></td>
					<td><div class="form-group" > ' . $ds . ' ' . $cup . '</div><div class="form-group">' . $v ['name'] . '</div></td>
                  <td>' . $isopen . '</td>
					<td>';
                $table .= '<a title="添加子类" href="index.php?a=add&c=articlecatalog&cid=' . $v ['id'] . '">[添加子类]</a>
			           <a title="编辑" href="index.php?a=edit&c=articlecatalog&id=' . $v ['id'] . '">[编辑]</a>
			           <a onclick="return delnav();" href="index.php?a=del&c=articlecatalog&id=' . $v ['id'] . '" title="删除">[删除]</a>';
                $table .= '</td></tr> ';
                $this->getcatTable($cates, $table, $v['id'], $level + 1);
            }
        }
    }

    //系统公告
    public function getSystemNoticeCatid()
    {
        return 19;
    }

    //平台简介
    public function getplatformbrief()
    {
        return 11;
    }

    //商家入门
    public function merchantnowledge()
    {
        return 18;
    }

    //代理商入门
    public function agentknowledge()
    {
        return 16;
    }

    //新手入门
    public function novice()
    {
        return 13;
    }

    //代理商入门
    public function partners()
    {
        return 17;
    }
}