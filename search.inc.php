<?php
// $Id: search.inc.php,v 1.1 2006/03/20 16:18:58 mikhail Exp $
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}
require_once XOOPS_ROOT_PATH . '/modules/simpleblog/simpleblog.php';
require_once XOOPS_ROOT_PATH . '/modules/simpleblog/SimpleBlogUtils.php';

/*
function simpleblog_search_1($queryarray, $andor, $limit, $offset, $userid = -1){
    global $xoopsDB;
    // $sql = 'select uid, title, DATE_FORMAT(blog_date, \'%Y\') year, DATE_FORMAT(blog_date, \'%m\') month, DATE_FORMAT(blog_date, \'%d\') date from '.$xoopsDB->prefix('simpleblog');
    $sql = 'select uid, title, DATE_FORMAT(blog_date, \'%Y\') year, DATE_FORMAT(blog_date, \'%m\') month, DATE_FORMAT(blog_date, \'%d\') date, UNIX_TIMESTAMP(last_update) from '.$xoopsDB->prefix('simpleblog');
    $i = 0;
    if($userid > 0){
        $sql .= " where uid = ".$userid." order by last_update desc ";
    }else{
        foreach ( $queryarray as $ql ) {
            $sql = ($i == 0) ? $sql.' where ' : $sql." $andor ";
            $sql = $sql.' (post_text like '.'\'%'.str_replace('\\"', '"', addslashes($ql)).'%\' or title like '.'\'%'.str_replace('\\"', '"', addslashes($ql)).'%\') ' ;
            $i++;
        }
    }
    $sqlLimit = $limit+$offset;
    $sql = $sql." limit ".$sqlLimit;
    $result = $xoopsDB->query($sql);
    $i = 0;
    $counter = 0;
    $ret = array();
    while( list($uid, $title, $year, $month, $date, $last_update) = $xoopsDB->fetchRow($result) ){

        if($counter >= $offset){
            // $ret[$i]['link'] = 'view.php?&uid='.$uid.'&year='.$year.'&month='.$month.'&date='.$date;
            $ret[$i]['link'] = SimpleBlog::createUrlNoPath($uid, $year, $month, $date);
            $ret[$i]['title'] = $title;
            $ret[$i]['time'] = $last_update;
            $ret[$i]['uid'] = $uid;
            $i++;
        }
        $counter++;
    }
    return $ret;
}
*/

function simpleblog_search($queryarray, $andor, $limit, $offset, $userid = -1)
{
    global $xoopsDB, $xoopsUser;

    $sql = 'select b.uid, b.title, DATE_FORMAT(b.blog_date, \'%Y\') year, DATE_FORMAT(b.blog_date, \'%m\') month, DATE_FORMAT(b.blog_date, \'%d\') date, UNIX_TIMESTAMP(b.last_update) ';

    $sql .= ' from ' . $xoopsDB->prefix('simpleblog') . ' b, ' . $xoopsDB->prefix('simpleblog_info') . ' info ';

    $i = 0;

    if ($userid > 0) {
        $sql .= ' where b.uid = ' . $userid . ' and info.uid = b.uid';

        if (!$xoopsUser) {
            $sql .= ' and info.blog_permission != 3';
        }

        $sql .= ' order by b.last_update desc ';
    } else {
        $sql .= ' where info.uid = b.uid';

        if (!$xoopsUser) {
            $sql .= ' and info.blog_permission != 3';
        }

        foreach ($queryarray as $ql) {
            $sql .= " $andor ";

            $sql .= ' (b.post_text like ' . '\'%' . str_replace('\\"', '"', addslashes($ql)) . '%\' or b.title like ' . '\'%' . str_replace('\\"', '"', addslashes($ql)) . '%\') ';

            $i++;
        }
    }

    // print_r($queryarray);

    $sqlLimit = $limit + $offset;

    $sql .= ' limit ' . $sqlLimit;

    $result = $xoopsDB->query($sql);

    $i = 0;

    $counter = 0;

    $ret = [];

    while (list($uid, $title, $year, $month, $date, $last_update) = $xoopsDB->fetchRow($result)) {
        if ($counter >= $offset) {
            // $ret[$i]['link'] = 'view.php?&uid='.$uid.'&year='.$year.'&month='.$month.'&date='.$date;

            $ret[$i]['link'] = SimpleBlogUtils::createUrlNoPath($uid, $year, $month, $date);

            $ret[$i]['title'] = (empty($title) || (0 == mb_strlen($title))) ? '&lt;empty title&gt;' : $title;

            $ret[$i]['time'] = $last_update;

            $ret[$i]['uid'] = $uid;

            $i++;
        }

        $counter++;
    }

    return $ret;
}
