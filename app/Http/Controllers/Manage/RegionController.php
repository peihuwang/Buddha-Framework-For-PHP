<?php

/**
 * Class RegionController
 */
class RegionController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));
    }

    public function more()
    {
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c . '.' . __FUNCTION__);

        $RegionObj = new Region();


        $names_arr = Buddha_Http_Input::getParameter('names');
        $fullname_arr = Buddha_Http_Input::getParameter('fullname');
        $pinyin_arr = Buddha_Http_Input::getParameter('pinyin');
        $lat_arr = Buddha_Http_Input::getParameter('lat');
        $lng_arr = Buddha_Http_Input::getParameter('lng');
        $father = Buddha_Http_Input::getParameter('father');
        $areas_arr = Buddha_Http_Input::getParameter('areas');

        if (Buddha_Http_Input::isPost()) {
            if (count($areas_arr) and is_array($areas_arr)) {
                foreach ($areas_arr as $key => $value) {
                    $area = array();
                    $area['id'] = $value['id'];
                    $area['name'] = $value['name'];

                    $area['fullname'] = $value['fullname'];
                    $area['pinyin'] = $value['pinyin'];
                    $area['lat'] = $value['lat'];
                    $area['lng'] = $value['lng'];
                    $area['father'] = $father;
                    $RegionObj->edit($area, $key);
                }
            }

            if (count($names_arr) and is_array($names_arr)) {
                foreach ($names_arr as $k => $v) {
                    if ($v) {
                        //add
                        $data = array();
                        $data['name'] = $v;
                        if (isset($fullname_arr[$k])) {
                            $fullname = $fullname_arr[$k];
                        } else {
                            $fullname = $v;
                        }
                        $data['fullname'] = $fullname;
                        $data['pinyin'] = $pinyin_arr[$k];
                        $data['lat'] = $lat_arr[$k];
                        $data['lng'] = $lng_arr[$k];

                        $data['father'] = $father;
                        $data['createtime'] = Buddha::$buddha_array['buddha_timestamp'];
                        $data['createtimestr'] = Buddha::$buddha_array['buddha_timestr'];

                        if ($father == 0) {
                            $data['level'] = 0;
                            $RegionObj->add($data);

                        } else {
                            $Db_father_region = $RegionObj->fetch($father);
                            $father_level = $Db_father_region['level'];
                            $data['level'] = $father_level + 1;
                            $RegionObj->add($data);
                            $Db_father_immchildnum = $RegionObj->countRecords(" father='{$father}' and isdel=0 ");
                            $RegionObj->edit(array('immchildnum' => $Db_father_immchildnum), $father);
                        }

                    }
                }
            }
            Buddha_Http_Head::redirect('添加成功', 'index.php?a=more&c=region');
        }

        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }


    public function del()
    {
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c . '.' . __FUNCTION__);

        $RegionObj = new Region();
        $id = Buddha_Http_Input::getParameter('id');
        $father = Buddha_Http_Input::getParameter('father');
        $childnum = $RegionObj->countRecords(" father='{$id}' and isdel=0 ");
        if ($childnum == 0) {
            $RegionObj->del($id);
            $Db_father_immchildnum = $RegionObj->countRecords(" father='{$father}' and isdel=0 ");
            $RegionObj->edit(array('immchildnum' => $Db_father_immchildnum), $father);
            Buddha_Http_Head::redirect('删除成功', 'index.php?a=more&c=region');
        } else {
            Buddha_Http_Head::redirect('当前类下含有子类：删除失败！', 'index.php?a=more&c=region');
        }
        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }

    public function ajax()
    {
        $RegionObj = new Region();
        $json = Buddha_Http_Input::getParameter('json');
        $json_arr = Buddha_Atom_Array::jsontoArray($json);
        $Db_Region = $RegionObj->getFiledValues(array('id', 'name', 'father', 'fullname', 'pinyin', 'lat', 'lng', 'level'), "father='{$json_arr['father']}' and isdel=0");
        $json_rsp ['isok'] = 'true';
        $json_rsp ['data'] = $Db_Region;
        Buddha_Http_Output::makeJson($json_rsp);
    }

    public function ajaxdel()
    {
        $RegionObj = new Region();
        $id = Buddha_Http_Input::getParameter('id');
        $father = Buddha_Http_Input::getParameter('father');
        $childnum = $RegionObj->countRecords(" father='{$id}' and isdel=0 ");
        $json = array();
        if ($childnum == 0) {
            $RegionObj->del($id);
            $Db_father_immchildnum = $RegionObj->countRecords(" father='{$father}' and isdel=0 ");
            $RegionObj->edit(array('immchildnum' => $Db_father_immchildnum), $father);
            $json ['isok'] = 'true';
            $json['msg'] = '删除成功';

        } else {
            $json ['isok'] = 'false';
            $json['msg'] = '当前类下含有子类：删除失败';

        }

        Buddha_Http_Output::makeJson($json);
    }

}