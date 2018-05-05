<?php
class Member extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }


    public function add($data){
        return $this->db->insert($this->table, $data);
    }

    public  function del($id){
        return   $this->db->delete($this->table, array("id" => $id));
    }

    public function edit($data,$id){

       return  $this->db->update($this->table, $data, array(
            "id" => $id
        ));

    }
    function fetch($id){
        $datas = $this->db->select($this->table, '*', array('id'=>$id));
        return  $datas[0];
    }


    function checkLogin($username, $password)
    {

        if (!$username || !$password) return FALSE;
        $datas = $this->db->select($this->table,
           array('id','username','password','pri'),
            array(
                'AND'=>array('username'=>$username,'password'=>$password)
            ),
         array("LIMIT" => array(0, 1))
        );

        if(!count($datas))
            return 0;
        else
            return $datas[0];
    }

    /**
     * 判断用户是否有代理商的权限
     * @param $user_id
     * @return int
     * @author wph 2017-09-13
     */
    public function isHasMemberPrivilege($user_id)
    {
        $Db_Member = $this->countRecords(" id='{$user_id}' AND state=2");
        if($Db_Member)
        {
            return 1;
        }else{
            return 0;
        }
    }




}