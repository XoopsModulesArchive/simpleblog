<?php
// $Id: application.php,v 1.1 2006/03/20 16:18:58 mikhail Exp $
require __DIR__ . '/header.php';

    if (!$xoopsUser) {
        redirect_header(XOOPS_URL . '/', 1, _MD_SIMPLEBLOG_NORIGHTTOACCESS . '(1.1)');

        exit();
    }
    if (!XoopsSecurity::checkReferer()) {
        redirect_header(XOOPS_URL . '/modules/simpleblog/', 2, 'Referer Check Failed');

        exit();
    }
    if ((!empty($xoopsModuleConfig['SIMPLEBLOG_APPL'])) && (1 == $xoopsModuleConfig['SIMPLEBLOG_APPL'])) {
        redirect_header(XOOPS_URL . '/', 1, _MD_SIMPLEBLOG_NORIGHTTOACCESS . '(1.2)');

        exit();
    }

    $blog = new SimpleBlog($xoopsUser->uid());
    $result = SimpleBlogUtils::newApplication($_POST['title'], ($_POST['permission']));

    if ('' == $result) {
        redirect_header(XOOPS_URL . '/', 1, _MD_SIMPLEBLOG_APPLICATION_APPLIED);

        exit();
    }  
        redirect_header(XOOPS_URL . '/', 2, $result);
        exit();

require __DIR__ . '/footer.php';
