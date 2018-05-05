<?php

/**
 * Class ArticleController
 */
class ArticleController extends Buddha_App_Action
{


    public function __construct()
    {
        parent::__construct();
        $this->classname = __CLASS__;
        $this->c = strtolower(preg_replace('/Controller/', '', __CLASS__));

    }

    public function mylist(){
        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $UserObj=new User;
        $ArticlecatalogObj=new Articlecatalog();

        $cat_id=Buddha_Http_Input::getParameter('cat_id');


        $articlect=$ArticlecatalogObj->fetch('7');
        if(!$articlect){
            Buddha_Http_Head::redirectofmobile('分类不存在','index.php?a=index&c=partner',2);
        }

        if($articlect['child_count']>0){
            /*
 *   目前为止：公告里只显示：公告(id=19)和操作视频(id=12)
 *              操作视频：每个操作视频下只显示相应的操作视频（id=18 商家操作视频）
 * */
            $list= $ArticlecatalogObj->getFiledValues('',"isdel=0 and sub='{$articlect['id']}' and id in (19,12) ");

            foreach($list  as  $k=>$v){
                if($v ['child_count']>0){
                    $list[$k]['sub']= $ArticlecatalogObj->getFiledValues('',"isdel=0 and sub='{$v['id']}' and id=18");
                }
            }
        }



        $this->smarty->assign('list',$list);
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
    public function index(){

        list($uid,$UserInfo)=each(Buddha_Db_Monitor::getInstance()->userPrivilege());
        $act=Buddha_Http_Input::getParameter('act');
        $cat_id=Buddha_Http_Input::getParameter('cat_id');

        $page = (int)Buddha_Http_Input::getParameter('p')?(int)Buddha_Http_Input::getParameter('p') :1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $where = "isdel=0 and cat_id='{$cat_id}' OR cat_id=13 ";
//        $where = "isdel=0 and cat_id='7'";
        $orderby = " order by id DESC ";

        $list = $this->db->getFiledValues('', $this->prefix . 'article', $where . $orderby . Buddha_Tool_Page::sqlLimit($page, $pagesize));

        if($act=='list'){
            if($list){
                $datas['isok'] = 'true';
                $datas['list'] = $list;
                $data['data']='加载完成';
            }else{
                $datas['isok'] = 'false';
                $datas['list'] = '';
                $datas['data'] = '没有了';
            }
            Buddha_Http_Output::makeJson($datas);
        }

        if($cat_id==19){
            $this->smarty->assign('title','系统公告');
        }elseif($cat_id==18){
            $this->smarty->assign('title','商家视频');
        }elseif($cat_id==13){
            $this->smarty->assign('title','新手入门');
        }elseif($cat_id==11){
            $this->smarty->assign('title','平台简介');
        }
        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty->assign('cat_id',$cat_id);
        $this->smarty -> display($TPL_URL.'.html');
    }

    public function info(){
        $ArticleObj=new Article();
        $id=Buddha_Http_Input::getParameter('id');
        if(!$id){
            Buddha_Http_Head::redirectofmobile('参数错误！','index.php?a=index&c=article',2);
        }
        $article=$ArticleObj->fetch($id);
        if(!$article){
            Buddha_Http_Head::redirectofmobile('信息不存在！','index.php?a=index&c=article',2);
        }

        $this->smarty->assign('article',$article);

        $TPL_URL = $this->c.'.'.__FUNCTION__;
        $this->smarty -> display($TPL_URL.'.html');
    }
}