<?php
// $Id: comment.php,v 1.1 2006/03/20 16:18:58 mikhail Exp $
require __DIR__ . '/header.php';
    if (!XoopsSecurity::checkReferer()) {
        redirect_header(XOOPS_URL . '/modules/simpleblog/', 2, 'Referer Check Failed');

        exit();
    }
    $params = SimpleBlogUtils::getDateFromHttpParams();

    $comment = $_POST['comment'] ?? '';
    $name = $_POST['name'] ?? '';
    if ($params['uid'] <= 0) {
        redirect_header(XOOPS_URL . '/', 3, _MD_SIMPLEBLOG_NORIGHTTOACCESS . '(0)');

        exit();
    } elseif ('' == $comment) {
        redirect_header(SimpleBlogUtils::createUrl($params['uid']), 3, _MD_SIMPLEBLOG_COMMENT_NO_COMMENT);

        exit();
    }
    if (!$xoopsUser) {
        if ('' == $name) {
            $name = _MD_SIMPLEBLOG_FORM_ANONYMOUS_NAME;
        } elseif (mb_strlen($name) > 200) {
            redirect_header(SimpleBlogUtils::createUrl($params['uid']), 3, _MD_SIMPLEBLOG_COMMENT_NAME_TOO_LONG);

            exit();
        }
    }
    $blog = new SimpleBlog($params['uid']);
    $dates = $params;
    if (!$dates || !SimpleBlogUtils::isCompleteDate($dates)) {
        redirect_header(XOOPS_URL . '/', 3, _MD_SIMPLEBLOG_INVALID_DATE . '(1.0)');

        exit();
    }

    if (!$blog->canComment()) {
        redirect_header(XOOPS_URL . '/', 3, _MD_SIMPLEBLOG_NORIGHTTOACCESS . '(1)');

        exit();
    }

    if ($blog->insertComment($dates, $name, $comment)) {
        redirect_header(SimpleBlogUtils::createUrl($params['uid']), 2, _MD_SIMPLEBLOG_THANKS_COMMENT);

        exit();
    }
require __DIR__ . '/footer.php';
