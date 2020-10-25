<?php
// $Id: simpleblog_top.php,v 1.1 2006/03/20 16:18:59 mikhail Exp $
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}
require_once XOOPS_ROOT_PATH . '/modules/simpleblog/SimpleBlogUtils.php';

function b_simpleblog_wait_appl($options)
{
    global $xoopsUser;

    $result = [];

    SimpleBlogUtils::assign_message($result);

    if ($xoopsUser && ($xoopsUser->isAdmin())) {
        $result['simpleblog_applicationNum'] = SimpleBlogUtils::getApplicationNum();
    }

    return $result;
}

function b_simpleblog_show($options)
{
    global $xoopsUser;

    $result = [];

    SimpleBlogUtils::assign_message($result);

    $result['simpleblog'] = SimpleBlogUtils::get_blog_list();

    $result['show_rss'] = (1 == $options[0]) ? 1 : 0;

    /*
    $result['blogTitle'] = _MB_SIMPLEBLOG_BLOG_TITLE;
    $result['unameTitle'] = _MB_SIMPLEBLOG_BLOGGER_NAME;
    $result['lastUpdateTitle'] = _MB_SIMPLEBLOG_UPDATE_DATE;
    */

    return $result;
}

function b_simpleblog_edit($options)
{
    $checked = [];

    $checked[0] = (1 == $options[0]) ? ' selected' : '';

    $checked[1] = ('' == $checked[0]) ? ' selected' : '';

    $form = '';

    $form .= _MB_SIMPLEBLOG_SHOW_RSS_LINK . ' :';

    $form .= "<select name='options[0]'>\n";

    $form .= "<option value='1'" . $checked[0] . '>' . _MB_SIMPLEBLOG_YES . "</option>\n";

    $form .= "<option value='0'" . $checked[1] . '>' . _MB_SIMPLEBLOG_NO . "</option>\n";

    $form .= "</select>\n";

    return $form;
}
