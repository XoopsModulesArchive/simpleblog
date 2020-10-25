<?php
// $Id: index.php,v 1.1 2006/03/20 16:18:58 mikhail Exp $

require __DIR__ . '/header.php';

/*
$param = isset($_GET['param']) ? intval($_GET['param']) : 0;
if($param == 0){
    redirect_header(XOOPS_URL.'/',1,_MD_SIMPLEBLOG_INTERNALERROR);
    exit();
}
*/
$params = SimpleBlogUtils::getDateFromHttpParams();
// print_r($params );
if ($params) {
    $blog = new SimpleBlog($params['uid']);

    if (SimpleBlogUtils::isCompleteDate($params) && array_key_exists('command', $params) && ('tb' == $params['command'])) {
        $tb = $blog->useTrackBack();

        if (false === $tb) {
            redirect_header(XOOPS_URL . '/', 1, _MD_SIMPLEBLOG_INTERNALERROR);

            exit();
        }

        $result = $blog->getBlogData($params['year'], $params['month'], $params['date']);

        $GLOBALS['xoopsOption']['template_main'] = 'simpleblog_trackback.html';

        $xoopsTpl->assign('url', SimpleBlogUtils::createUrl($params['uid'], $params['year'], $params['month'], $params['date']));

        $xoopsTpl->assign('mt_tb_url', SimpleBlogUtils::makeTrackBackURL($params['uid'], $params['year'], $params['month'], $params['date']));

        $xoopsTpl->assign('params', $params);

        $xoopsTpl->assign('xoops_module_header', '<link rel="alternate" type="application/rss+xml" title="RSS" href="' . SimpleBlogUtils::createRssURL($params['uid']) . '">');
    } else {
        // init

        $xoopsTpl->assign('simpleblog_editable', false);

        $xoopsTpl->assign('simpleblog_commentable', false);

        $result = [];

        $result_max_num = (
            SimpleBlogUtils::isCompleteDate($params) && array_key_exists('month', $params)
        ) ? 31 : SIMPLEBLOG_VIEW_LIST_NUM;

        $result = $blog->getBlogData($params['year'], $params['month'], $params['date'], $result_max_num);

        $xoopsTpl->assign('simpleblog_blogdata', $result['blog']);

        $xoopsTpl->assign('simpleblog', $result);

        $xoopsTpl->assign('blog_title', $blog->getTitle());

        $xoopsTpl->assign('blog_uname', $blog->getTargetUname());

        $xoopsTpl->assign('simpleblog_targetUid', $params['uid']);

        $xoopsTpl->assign('simpleblog_index', $blog->getBlogIndex());

        $xoopsTpl->assign('simpleblog_today', $result['today']);

        if ($blog->isPublic()) {
            $xoopsTpl->assign('simpleblog_user_rss', SimpleBlogUtils::createRssURL($params['uid']));
        }

        if ($blog->canWrite()) {
            $xoopsTpl->assign('simpleblog_editable', true);
        }

        if ($blog->canComment()) {
            $xoopsTpl->assign('simpleblog_commentable', true);

            if ($xoopsUser) {
                $xoopsTpl->assign('simpleblog_uname', $xoopsUser->uname());
            }
        }

        $blog->recieve_trackback_ping($params);

        if (SimpleBlogUtils::isCompleteDate($params)) {
            $xoopsTpl->assign('trackbacks', $blog->getTrackBack($params));
        }

        $GLOBALS['xoopsOption']['template_main'] = 'simpleblog_view.html';

        $mh = '';

        $mh .= '<link rel="alternate" type="application/rss+xml" title="RSS" href="' . SimpleBlogUtils::createRssURL($params['uid']) . '">' . "\n";

        $mh .= '<link rel="start" href="' . SimpleBlogUtils::createUrl($params['uid']) . '" title="Home">' . "\n";

        $xoopsTpl->assign('xoops_module_header', $mh);

        // $xoopsTpl->assign('xoops_module_header', '<link rel="alternate" type="application/rss+xml" title="RSS" href="'.SimpleBlogUtils::createRssURL($params['uid']).'">');
        // <link rel="start" href="http://el30.sub.jp/" title="Home">
        // $xoopsTpl->assign('simpleblog_home_url', SimpleBlogUtils::createUrl($params['uid']));
    }
} else {
    $xoopsTpl->assign('simpleblog', SimpleBlogUtils::get_blog_list());

    $rssUrl = XOOPS_URL . '/modules/simpleblog/rss.php';

    $xoopsTpl->assign('simpleblog_rss_url', $rssUrl);

    $GLOBALS['xoopsOption']['template_main'] = 'simpleblog_list.html';

    $xoopsTpl->assign('xoops_module_header', '<link rel="alternate" type="application/rss+xml" title="RSS" href="' . $rssUrl . '">');
}

require __DIR__ . '/footer.php';
