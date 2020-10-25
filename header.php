<?php
// $Id: header.php,v 1.1 2006/03/20 16:18:58 mikhail Exp $
require dirname(__DIR__, 2) . '/mainfile.php';
if (
    !defined('XOOPS_ROOT_PATH') ||
    !is_file(XOOPS_ROOT_PATH . '/header.php') ||
    !is_file(XOOPS_ROOT_PATH . '/modules/simpleblog/simpleblog.php') ||
    !defined('XOOPS_CACHE_PATH') ||
    !is_dir(XOOPS_CACHE_PATH)
) {
    exit();
}
require XOOPS_ROOT_PATH . '/header.php';
require_once XOOPS_ROOT_PATH . '/modules/simpleblog/simpleblog.php';
if ($xoopsTpl) {
    SimpleBlogUtils::assign_message($xoopsTpl);
}
$xoopsTpl->assign('xoops_module_header', '<link rel="alternate" type="application/rss+xml" title="RSS" href="' . XOOPS_URL . '/modules/simpleblog/rss.php">');
