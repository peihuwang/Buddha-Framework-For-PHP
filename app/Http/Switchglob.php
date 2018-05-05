<?php

class Switchglob extends Buddha_App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = strtolower(__CLASS__);

    }

    /**
     * 判断是不是有 有效数据
     * 如果有就返1 没有就返0   如果返1就进行数据更新操作 如果返0就进行数据插入操作.
     * @return int
     * @author wph 2017-12-04
     */
    public function isHasValidRecord()
    {

        $num = $this->countRecords("buddhastatus=0 AND isdel=0 ");
        if ($num > 0) {
            return 1;
        } else {
            return 0;
        }

    }


    /**
     * 对开关全局表进行添加或者更新
     * @param $data
     * @return mixed
     * @author wph 2017-12-04
     */
    public function addOrUpdateSwitchGlob($data)
    {

        if ($this->isHasValidRecord()) {
            $Db_Userassomoney = $this->getSingleFiledValues('', "buddhastatus=0 AND isdel=0 LIMIT 0,1");
            $id = $Db_Userassomoney['id'];
            return $this->edit($data, $id);

        } else {
            return $this->add($data);
        }

    }


    public function getSwitchGlobArr()
    {

        $arr = $this->getSingleFiledValues('', "buddhastatus=0 AND isdel=0");
        return $arr;

    }

    /**
     * 返回全局控制表中的是否开启商信功能的状态 1=开启 0=未开启
     * @return int
     * @author wph 2017-12-04
     */
    public function getIsOpenWorldChatInt()
    {
        $arr = $this->getValidRecordArr();
        return $arr['is_openworldchat'];

    }


    /**
     * 返回有效数据
     * @return array
     * @author wph 2017-11-25
     */
    public function getValidRecordArr()
    {

        $arr = array(
            'is_openworldchat' => 0

        );
        if ($this->isHasValidRecord()) {

            $Db_Data = $this->getSingleFiledValues('', "buddhastatus=0 AND isdel=0 LIMIT 0,1");
            $is_openworldchat = $Db_Data['is_openworldchat'];

            $arr['is_openworldchat'] = $is_openworldchat;


        }

        return $arr;

    }


}