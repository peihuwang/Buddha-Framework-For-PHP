<?php

class Buddha_Tool_Page extends Buddha_Base_Component{
    /**
     * Buddha_Tool_Page Instance
     *
     * @var Buddha_Tool_Page
     */
    protected static $_instance;
    /**
     * 实例化
     *
     * @static
     * @access	public
     * @return	object 返回对象
     */
    public static function getInstance($options)
    {
        if (self::$_instance === null) {
            $createObj=  new self();
            if (is_array($options))
            {
                foreach ($options as $option => $value)
                {
                    $createObj->$option = $value;
                }
            }
            self::$_instance =$createObj;
        }
        return self::$_instance;
    }
    /**
     * 构造
     *
     */
    public function __construct(){

    }


    public static function sqlLimit($page, $pagesize = 30) {
        $page = (int) $page;
        $page <= 0 && $page = 1;
        $pagesize = (int) $pagesize;
        $pagesize <= 0 && $pagesize = 10;
        $GLOBALS['page'] = $page;
        $GLOBALS['pagesize'] = $pagesize;
        return ' LIMIT ' . ($page - 1) * $pagesize . ', ' . $pagesize;
    }

//分页函数
    public static function multLink($currentPage, $totalRecords, $url, $pageSize = 10) {
        $lang_prev = '上一页';
        $lang_next = '下一页';
        if ($totalRecords <= $pageSize)
            return '';
        $mult = '';
        $totalPages = ceil($totalRecords / $pageSize);
        $mult .= '<nav> <ul class="pagination pagination-lg">';
        $currentPage < 1 && $currentPage = 1;
        if ($currentPage > 1) {
            $mult .= '<li><a href="' . $url . 'p=' . ($currentPage - 1) . '">' . $lang_prev . '</a></li>';
        } else {
            $mult .= '<li class="disabled"><a href="#" aria-label="Previous">' . $lang_prev . '</a></li>';
        }
        if ($totalPages < 13) {
            for ($counter = 1; $counter <= $totalPages; $counter++) {
                if ($counter == $currentPage) {
                    $mult .= '<li class="active"><a href="' . $url . 'p=' . $counter . '">' . $counter . ' <span class="sr-only">(current)</span></a></li>';
                } else {
                    $mult .= '<li><a href="' . $url . 'p=' . $counter . '">' . $counter . '</a></li>';
                }
            }
        } elseif ($totalPages > 11) {
            if ($currentPage < 7) {
                for ($counter = 1; $counter < 10; $counter++) {
                    if ($counter == $currentPage) {
                        $mult .= '<li class="active"><a href="#">' . $counter . ' <span class="sr-only">(current)</span></a></li>';
                    } else {
                        $mult .= '<li><a href="' . $url . 'p=' . $counter . '">' . $counter . '</a></li>';
                    }
                }
                $mult .= '<li><span class="pn-break">&#8230;</span></li><li><a href="' . $url . 'p=' . ($totalPages - 1) . '">' . ($totalPages - 1) . '</a></li><li><a href="' . $url . 'p=' . $totalPages . '">' . $totalPages . '</a></li>';

            } elseif ($totalPages - 6 > $currentPage && $currentPage > 6) {
                $mult .= '<li><a href="' . $url . 'p=1">1</a></li><li><a href="' . $url . 'p=2">2</a></li>';
                for ($counter = $currentPage - 3; $counter <= $currentPage + 3; $counter++) {
                    if ($counter == $currentPage) {
                        $mult .= '<li ><a class="curr">' . $counter . '<span class="sr-only">(current)</span></a></li>';
                    } else {
                        $mult .= '<li><a href="' . $url . 'p=' . $counter . '">' . $counter . '</a></li>';
                    }
                }
                $mult .= '<li><span class="pn-break">&#8230;</span></li><li><a href="' . $url . 'p=' . ($totalPages - 1) . '">' . ($totalPages - 1) . '</a></li><li><a href="' . $url . 'p=' . $totalPages . '">' . $totalPages . '</a></li>';
            } else {
                $mult .= '<li><a href="' . $url . 'p=1">1</a></li><li><a href="' . $url . 'p=2">2</a></li><li><span class="pn-break">&#8230;</span></li>';
                for ($counter = $totalPages - 8; $counter <= $totalPages; $counter++) {
                    if ($counter == $currentPage) {
                        $mult .= '<li><a class="curr">' . $counter . '</a></li>';
                    } else {
                        $mult .= '<li><a href="' . $url . 'p=' . $counter . '">' . $counter . '</a></li>';
                    }
                }
            }
        }
        if ($currentPage < $counter - 1) {
            $mult .= '<li><a class="nextprev" href="' . $url . 'p=' . ($currentPage + 1) . '">' . $lang_next . '</a></li>';
        } else {
            $mult .= '<li class="disabled"><a href="#last" data-page="last">' . $lang_next . '</a></li>';
        }
        $mult .= '</ul> ';
        $mult .= '<b style="display: inline;">共'.$totalPages.'页</b></nav>';
        return $mult;
    }

//分页函数
public static function multLinks($currentPage, $totalRecords, $url, $pageSize = 10, $anchor = '') {
        $lang_prev = '上一页';
        $lang_next = '下一页';
        if ($totalRecords <= $pageSize)
            return '';
        $mult = '';
        $totalPages = ceil($totalRecords / $pageSize);
        $mult .= '<div class="pages">';
        $currentPage < 1 && $currentPage = 1;
        if ($currentPage > 1) {
            $mult .= '<a href="' . $url . 'p=' . ($currentPage - 1) . $anchor . '">' . $lang_prev . '</a>';
        } else {
            $mult .= '<b>' . $lang_prev . '</b>';
        }
        if ($totalPages < 13) {
            for ($counter = 1; $counter <= $totalPages; $counter++) {
                if ($counter == $currentPage) {
                    $mult .= '<b>' . $counter . '</b>';
                } else {
                    $mult .= '<a href="' . $url . 'p=' . $counter . $anchor . '">' . $counter . '</a>';
                }
            }
        } elseif ($totalPages > 11) {
            if ($currentPage < 7) {
                for ($counter = 1; $counter < 10; $counter++) {
                    if ($counter == $currentPage) {
                        $mult .= '<b>' . $counter . '</b>';
                    } else {
                        $mult .= '<a href="' . $url . 'p=' . $counter . $anchor . '">' . $counter . '</a>';
                    }
                }
                $mult .= '<span>&#8230;</span><a href="' . $url . 'p=' . ($totalPages - 1) . $anchor . '">' . ($totalPages - 1) . '</a><a href="' . $url . 'p=' . $totalPages . $anchor . '">' . $totalPages . '</a>';
            } elseif ($totalPages - 6 > $currentPage && $currentPage > 6) {
                $mult .= '<a href="' . $url . 'p=1' . $anchor . '">1</a><a href="' . $url . 'p=2' . $anchor . '">2</a><span>&#8230;</span>';
                for ($counter = $currentPage - 3; $counter <= $currentPage + 3; $counter++) {
                    if ($counter == $currentPage) {
                        $mult .= '<b>' . $counter . '</b>';
                    } else {
                        $mult .= '<a href="' . $url . 'p=' . $counter . $anchor . '">' . $counter . '</a>';
                    }
                }
                $mult .= '<span>&#8230;</span><a href="' . $url . 'p=' . ($totalPages - 1) . $anchor . '">' . ($totalPages - 1) . '</a><a href="' . $url . 'p=' . $totalPages . $anchor . '">' . $totalPages . '</a>';
            } else {
                $mult .= '<a href="' . $url . 'p=1' . $anchor . '">1</a><a href="' . $url . 'p=2' . $anchor . '">2</a><span>&#8230;</span>';
                for ($counter = $totalPages - 8; $counter <= $totalPages; $counter++) {
                    if ($counter == $currentPage) {
                        $mult .= '<b>' . $counter . '</b>';
                    } else {
                        $mult .= '<a href="' . $url . 'p=' . $counter . $anchor . '">' . $counter . '</a>';
                    }
                }
            }
        }
        if ($currentPage < $counter - 1) {
            $mult .= '<a href="' . $url . 'p=' . ($currentPage + 1) . $anchor . '" class="nextprev">' . $lang_next . '</a>';
        } else {
            $mult .= '<b>' . $lang_next . '</b>';
        }
        //$mult .= '<div class="fl">记录<strong style="color:red;">'.$totalRecords.'</strong>条&nbsp;&nbsp;共<strong style="color:red;">'.$totalPages.'</strong>页</div>';
        $mult .= '</div>';
        return $mult;
    }



}