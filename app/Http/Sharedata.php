<?php
class Sharedata extends  Buddha_App_Model{
    public function __construct(){
       parent::__construct();
       $this->table = strtolower(__CLASS__);

    }

    public function head(){
        $SupplycatObj=new Supplycat();
        $SupplycatObj=new Shopcat();
        $ImagecatalogObj=new Imagecatalog();
        $ImageObj=new Image();
        $UserObj=new User();
        $uid = Buddha_Http_Cookie::getCookie('uid');
        if($uid){
            $userhead=$UserObj->getSingleFiledValues(array('username'),"isdel=0 and id='{$uid}'");
        }
        $cat_id=$ImagecatalogObj->getSingleFiledValues(array('id'),"isdel=10");
      
        if($cat_id){
           $images= $ImageObj->getFiledValues(array('id','name','large'),"cat_id='{$cat_id['id']}'");
        }
        $RegionObj=new Region();
        $locdata = $RegionObj->getLocationDataFromCookie();

        $agent=$UserObj->getSingleFiledValues(array('*'),"isdel=0 and groupid='2' {$locdata['sql']}");

        $referral=substr($agent['tel'],0,3).'-<i>'.substr($agent['tel'],3,4).'</i>-'.substr($agent['tel'],7,4);

        $this->smarty->assign('referral',$referral);
        $arr =$SupplycatObj->getcategory();
        $tree=$SupplycatObj->tree($arr,0);

        $this->smarty->assign('tree',$tree);
        $this->smarty->assign('uid',$uid);
        $action=$_REQUEST['c'];
        $this->smarty->assign('action',$action);
        $this->smarty->assign('images',$images);
        $this->smarty->assign('userhead',$userhead);

    }
    public function userhead(){
        $UserObj=new User();
        $ImagecatalogObj=new Imagecatalog();
        $ImageObj=new Image();
        $uid = Buddha_Http_Cookie::getCookie('uid');
        $str = str_replace('\\','',$_COOKIE['sName']);
        $jsonarr = json_decode($str,true);

        $jsonarr['nation']=Buddha_Tool_Password::unescape($jsonarr['nation']);
        $jsonarr['province']=Buddha_Tool_Password::unescape($jsonarr['province']);
        $jsonarr['city']=Buddha_Tool_Password::unescape($jsonarr['city']);


        $h=date('G');
        if ($h < 11) {
            $greet = '早上好';
        } else if ($h < 13) {
            $greet = '中午好';
        } else if ($h < 17) {
            $greet = '下午好';
        } else {
            $greet = '晚上好';
        }
        if($uid){
            $userhead=$UserObj->getSingleFiledValues(array('username','realname','	mobile','logo','referral_id','groupid','to_group_id','level3','tel'),"isdel=0 and id='{$uid}'");
			$userhead['to_group_idss'] = $_SESSION['groupid'];
            if($userhead['groupid']==1){
                if($userhead['referral_id']!=0){

                    $referral=$UserObj->getSingleFiledValues(array('username','realname','	mobile','tel'),"isdel=0 and 	groupid='3' and id='{$userhead['referral_id']}'");
                    $userhead['leader']='合伙人';
                    $userhead['daiusername']=$referral['username'];
                    $userhead['dairealname']=$referral['realname'];
                    if($referral['mobile']!=0){
                        $userhead['daimobile']=$referral['mobile'];
                    }else{
                        $userhead['daimobile']=$referral['tel'];
                    }
                }else{
                    $referral=$UserObj->getSingleFiledValues(array('username','realname','	mobile','tel'),"isdel=0 and 	groupid='2' and level3='{$userhead['level3']}'");
                    $userhead['leader']='代理商';
                    $userhead['daiusername']=$referral['username'];
                    $userhead['dairealname']=$referral['realname'];
                    if($referral['mobile']!=0){
                        $userhead['daimobile']=$referral['mobile'];
                    }else{
                        $userhead['daimobile']=$referral['tel'];
                    }

                }
            }else if(($userhead['groupid']==3 or $userhead['groupid']==4) and $userhead['level3']){
                $referral=$UserObj->getSingleFiledValues(array('username','realname','	mobile','tel'),"isdel=0 and groupid='2' and level3='{$userhead['level3']}'");

                $userhead['leader']='代理商';
                $userhead['daiusername']=$referral['username'];
                $userhead['dairealname']=$referral['realname'];
                if($referral['mobile']!=0){
                    $userhead['daimobile']=$referral['mobile'];
                }else{
                    $userhead['daimobile']=$referral['tel'];
                }
            }
        }
        $cat_id=$ImagecatalogObj->getSingleFiledValues(array('id'),"isdel=10");
        if($cat_id){
            $images= $ImageObj->getFiledValues(array('id','name','large'),"cat_id='{$cat_id['id']}'");
        }

        $userhead['greet']=$greet;
        $this->smarty->assign('images',$images);
        $this->smarty->assign('uid',$uid);
        $this->smarty->assign('userhead',$userhead);
        $action=$_REQUEST['c'];
        $this->smarty->assign('action',$action);
        $this->smarty->assign('jsonarr',$jsonarr);
        $this->smarty->assign('urlc',$_REQUEST['c']);
    }

    public function footer(){
       $help= $this->db->getFiledValues(array('id','name'),$this->prefix.'articlecatalog',"sub=1");
        foreach($help as $k=>$v){
            $child= $this->db->getFiledValues(array('id','name'),$this->prefix.'article',"cat_id='{$v['id']}'");
            if($child){
            $help[$k]['child']=$child;
            }
        }
        $this->smarty->assign('help',$help);

    }
}