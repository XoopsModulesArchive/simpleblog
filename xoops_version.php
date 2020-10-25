<?php
// $Id: xoops_version.php,v 1.1 2006/03/20 16:18:58 mikhail Exp $

$modversion['name'] = _MI_SIMPLEBLOG_NAME;
$modversion['version'] = 0.21;
$modversion['description'] = _MI_SIMPLEBLOG_DESC;
$modversion['credits'] = '';
$modversion['author'] = '<a href="http://xoops-modules.sourceforge.jp/" target="_blank">xoops-modules project</a>';
$modversion['help'] = 'help.html';
$modversion['license'] = 'GPL see LICENSE';
$modversion['official'] = 0;
$modversion['image'] = 'simpleblog.gif';
$modversion['dirname'] = 'simpleblog';

$modversion['sqlfile']['mysql'] = 'sql/mysql.sql';
$modversion['tables'][0] = 'simpleblog_info';
$modversion['tables'][1] = 'simpleblog';
$modversion['tables'][2] = 'simpleblog_comment';
$modversion['tables'][3] = 'simpleblog_application';
$modversion['tables'][4] = 'simpleblog_trackback';

//Admin things
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = 'admin/index.php';
$modversion['adminmenu'] = 'admin/menu.php';

// Menu
$modversion['hasMain'] = 1;
$modversion['sub'][1]['name'] = _MI_SIMPLEBLOG_WRITE;
$modversion['sub'][1]['url'] = 'edit.php?today=on';

// search
$modversion['hasSearch'] = 1;
$modversion['search']['file'] = 'search.inc.php';
$modversion['search']['func'] = 'simpleblog_search';

// config
$modversion['config'][] = [
    'name' => 'blog_description',
    'title' => '_MI_SIMPLEBLOG_CONF_DESC',
    'description' => '_MI_SIMPLEBLOG_CONFIG_RSS_DESC',
    'formtype' => 'textbox',
    'valuetype' => 'text',
    'default' => _MI_SIMPLEBLOG_CONFIG_RSS_DEF,
];
$modversion['config'][] = [
    'name' => 'SIMPLEBLOG_APPL',
    'title' => '_MI_SIMPLEBLOG_APPL_OK',
    'description' => '_MI_SIMPLEBLOG_APPL_DESC',
    'formtype' => 'select',
    'valuetype' => 'int',
    'default' => 0,
    'options' => [_MI_SIMPLEBLOG_APPL_ALLOW => 0, _MI_SIMPLEBLOG_APPL_DENY => 1],
];
$modversion['config'][] = [
    'name' => 'SIMPLEBLOG_REWRITE',
    'title' => '_MI_SIMPLEBLOG_REWRITE_TITLE',
    'description' => '_MI_SIMPLEBLOG_REWRITE_DESC',
    'formtype' => 'select',
    'valuetype' => 'int',
    'default' => 0,
    'options' => [_MI_SIMPLEBLOG_UNUSE_REWRITE => 0, _MI_SIMPLEBLOG_USE_REWRITE => 1],
];

$modversion['config'][] = [
    'name' => 'SIMPLEBLOG_TRACKBACK',
    'title' => '_MI_SIMPLEBLOG_TRACKBACK',
    'description' => '_MI_SIMPLEBLOG_TRACKBACK_DESC',
    'formtype' => 'select',
    'valuetype' => 'int',
    'default' => 0,
    'options' => [_MI_SIMPLEBLOG_UNUSE_TRACKBACK => 0, _MI_SIMPLEBLOG_USE_TRACKBACK => 1],
];

$modversion['config'][] = [
    'name' => 'SIMPLEBLOG_UPDATE_PING',
    'title' => '_MI_SIMPLEBLOG_UPDATE_PING',
    'description' => '_MI_SIMPLEBLOG_UPDATE_PING_DESC',
    'formtype' => 'select',
    'valuetype' => 'int',
    'default' => 1,
    'options' => [_MI_SIMPLEBLOG_UNUSE_UPDATE_PING => 0, _MI_SIMPLEBLOG_USE_UPDATE_PING => 1],
];

// Blocks
$modversion['blocks'][1]['file'] = 'simpleblog_top.php';
$modversion['blocks'][1]['name'] = _MI_SIMPLEBLOG_NAME;
$modversion['blocks'][1]['description'] = _MI_SIMPLEBLOG_DESC;
$modversion['blocks'][1]['show_func'] = 'b_simpleblog_show';
$modversion['blocks'][1]['edit_func'] = 'b_simpleblog_edit';
$modversion['blocks'][1]['options'] = '1';
$modversion['blocks'][1]['template'] = 'simpleblog_block.html';

$modversion['blocks'][2]['file'] = 'simpleblog_top.php';
$modversion['blocks'][2]['name'] = _MI_SIMPLEBLOG_1_LINE;
$modversion['blocks'][2]['description'] = _MI_SIMPLEBLOG_1_LINE_DESC;
$modversion['blocks'][2]['show_func'] = 'b_simpleblog_show';
$modversion['blocks'][2]['edit_func'] = 'b_simpleblog_edit';
$modversion['blocks'][2]['options'] = '1';
$modversion['blocks'][2]['template'] = 'simpleblog_block_1.html';

$modversion['blocks'][3]['file'] = 'simpleblog_top.php';
$modversion['blocks'][3]['name'] = _MI_SIMPLEBLOG_APPL_WAITING_TITLE;
$modversion['blocks'][3]['description'] = _MI_SIMPLEBLOG_APPL_WAITING_TITLE;
$modversion['blocks'][3]['show_func'] = 'b_simpleblog_wait_appl';
$modversion['blocks'][3]['template'] = 'simpleblog_block_wait.html';

$modversion['templates'][] = [
    'file' => 'simpleblog_list.html',
    'description' => 'Blog List',
];
$modversion['templates'][] = [
    'file' => 'simpleblog_view.html',
    'description' => 'ViewBlog',
];

// $modversion['templates'][] = array(
// 	'file'        => 'simpleblog_edit.html',
// 	'description' => 'EditBlog'
// );

$modversion['templates'][] = [
    'file' => 'simpleblog_rss.html',
    'description' => 'SimpleBlog RSS',
];

$modversion['templates'][] = [
    'file' => 'simpleblog_application.html',
    'description' => 'Application for SimpleBlog',
];

$modversion['templates'][] = [
    'file' => 'simpleblog_trackback.html',
    'description' => 'TrackBack for SimpleBlog',
];
