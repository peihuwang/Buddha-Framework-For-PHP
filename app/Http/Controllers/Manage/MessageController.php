<?php

/**
 * Class MessageController
 */
class MessageController extends Buddha_App_Action
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
        /*******************/


        $job = Buddha_Http_Input::getParameter('job');
        $ids = Buddha_Http_Input::getParameter('ids');
        if (strlen($job)) {

            $ids = implode(',', $ids);
            switch ($job) {

                case 'del';
                    $this->db->delRecords('message', "id IN ($ids)");

                    Buddha_Http_Head::redirect('批量删除成功', "index.php?a=more&c=message");

                    break;
                case 'is_view';
                    $this->db->updateRecords(array('is_view' => 1), 'message', "id IN ($ids)");
                    Buddha_Http_Head::redirect('批量设置已读', "index.php?a=more&c=message");

                    break;

            }

        }


        $view = Buddha_Http_Input::getParameter('view') ? (int)Buddha_Http_Input::getParameter('view') : 1;
        $p = (int)Buddha_Http_Input::getParameter('p');
        $keyword = Buddha_Http_Input::getParameter('keyword');
        $searchType = array(1 => '留言咨询', 2 => '投诉与建议');

        $where = " o.isdel=0 ";
        if ($view) {
            $params['view'] = $view;
            switch ($view) {

                case 1:
                    $where .= " and  o.type='1' ";
                    break;

                case 2:
                    $where .= " and  o.type='2' ";
                    break;


            }
        }

        if ($keyword) {
            $where .= " and (o.title like '%$keyword%' or  u.mobile like '%$keyword%')   ";
            $params['keyword'] = $keyword;
        }

        $sql = "select count(*) as total  from {$this->prefix}message as o
           left join {$this->prefix}user as u on o.user_id = u.id
         where {$where} ";

        $count_arr = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $rcount = $count_arr[0]['total'];

        $page = (int)Buddha_Http_Input::getParameter('p') ? (int)Buddha_Http_Input::getParameter('p') : 1;
        $pagesize = Buddha::$buddha_array['page']['pagesize'];
        $pcount = ceil($rcount / $pagesize);
        if ($page > $pcount) {
            $page = $pcount;
        }
        $orderby = " order by o.is_view asc ,o.id DESC ";
        $limit = Buddha_Tool_Page::sqlLimit($page, $pagesize);

        $sql = "select *

	 from {$this->prefix}message as o  left join {$this->prefix}user as u on o.user_id = u.id
    where {$where} {$orderby}  {$limit}
    ";


        $list = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);


        $strPages = Buddha_Tool_Page::multLink($page, $rcount, 'index.php?a=' . __FUNCTION__ . '&c=' . $this->c . '&' . http_build_query($params) . '&', $pagesize);

        $this->smarty->assign('rcount', $rcount);
        $this->smarty->assign('pcount', $pcount);
        $this->smarty->assign('page', $page);
        $this->smarty->assign('strPages', $strPages);
        $this->smarty->assign('list', $list);
        $this->smarty->assign('params', $params);
        $this->smarty->assign('view', $view);

        $this->smarty->assign('searchType', $searchType);
        $this->smarty->assign('params', $params);
        $this->smarty->assign('keyword', $keyword);


        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }


    public function edit()
    {
        /******************
         *   权 限 控 制   *
         *********************/
        Buddha_Atom_Secury::backendPrivilege($this->c . '.' . __FUNCTION__);
        /*******************/


        $MessageObj = new Message();
        $view = Buddha_Http_Input::getParameter('view') ? (int)Buddha_Http_Input::getParameter('view') : 1;
        $page = (int)Buddha_Http_Input::getParameter('p');
        $id = (int)Buddha_Http_Input::getParameter('id');
        $pay_status = (int)Buddha_Http_Input::getParameter('pay_status');
        $Db_Message = $MessageObj->fetch($id);

        $is_view = $Db_Message['is_view'];
        if ($is_view == 0) {
            $MessageObj->edit(array('is_view' => 1), $id);
        }


        $this->smarty->assign('c', $Db_Message);
        $this->smarty->assign('view', $view);
        $this->smarty->assign('page', $page);


        $TPL_URL = $this->c . '.' . __FUNCTION__;
        $this->smarty->display($TPL_URL . '.html');
    }


}