<?php
// $Id: conf.php,v 1.1 2006/03/20 16:18:58 mikhail Exp $
if (
    !defined('XOOPS_ROOT_PATH') ||
    !defined('XOOPS_CACHE_PATH') ||
    !is_dir(XOOPS_CACHE_PATH)
) {
    exit();
}
if (!empty($xoopsConfig)) {
    if (file_exists(XOOPS_ROOT_PATH . '/modules/simpleblog/language/' . $xoopsConfig['language'] . '/main.php')) {
        require_once XOOPS_ROOT_PATH . '/modules/simpleblog/language/' . $xoopsConfig['language'] . '/main.php';
    }
}
simpleblog_init();

function simpleblog_init()
{
    global $xoopsDB;

    define('SIMPLEBLOG_VERSION', '0.2');

    define('SIMPLEBLOG_DIR_NAME', 'simpleblog');

    define('SIMPLEBLOG_DIR', XOOPS_URL . '/modules/' . SIMPLEBLOG_DIR_NAME . '/');

    define('SIMPLEBLOG_BLOCK_LIST_NUM', 10);

    define('SIMPLEBLOG_VIEW_LIST_NUM', 10);

    define('SIMPLEBLOG_MAIN_LIST_NUM', 50);

    define('SIMPLEBLOG_TABLE_BLOG', $xoopsDB->prefix('simpleblog'));

    define('SIMPLEBLOG_TABLE_INFO', $xoopsDB->prefix('simpleblog_info'));

    define('SIMPLEBLOG_TABLE_COMMENT', $xoopsDB->prefix('simpleblog_comment'));

    define('SIMPLEBLOG_TABLE_APPL', $xoopsDB->prefix('simpleblog_application'));

    define('SIMPLEBLOG_TABLE_TRACKBACK', $xoopsDB->prefix('simpleblog_trackback'));

    define('SIMPLEBLOG_DEBUG_OUT', 1);

    // option for cgi user

    define('SIMPLEBLOG_REQUEST_URI_SEP', '/');

    // define('SIMPLEBLOG_REQUEST_URI_SEP', '?param=');
}
