<?php
// $Id: edit.php,v 1.1 2006/03/20 16:18:58 mikhail Exp $
require __DIR__ . '/header.php';
require_once XOOPS_ROOT_PATH . '/include/xoopscodes.php';

    if (!$xoopsUser || !is_object($xoopsUser)) {
        redirect_header(XOOPS_URL . '/modules/simpleblog/', 2, _MD_SIMPLEBLOG_CAN_WRITE_USER_ONLY);

        exit();
    }
    if (!XoopsSecurity::checkReferer()) {
        redirect_header(XOOPS_URL . '/modules/simpleblog/', 2, 'Referer Check Failed');

        exit();
    }
    $commit = isset($_POST['commit']) ? 'on' : 'off';
    $preview = isset($_POST['preview']) ? 'on' : 'off';

    $blog = new SimpleBlog($xoopsUser->uid());
    if (!$blog->canWrite()) {
        if ((!empty($xoopsModuleConfig['SIMPLEBLOG_APPL'])) && (1 == $xoopsModuleConfig['SIMPLEBLOG_APPL'])) {
            redirect_header(XOOPS_URL . '/', 1, _MD_SIMPLEBLOG_NORIGHTTOACCESS . '(1.2)');

            exit();
        }

        $GLOBALS['xoopsOption']['template_main'] = 'simpleblog_application.html';
    } else {
        $params = SimpleBlogUtils::getDateFromHttpParams();

        $dates = SimpleBlogUtils::complementDate($params);

        if ('on' == $commit) {
            $text = $_POST['text'] ?? '';

            $title = $_POST['title'] ?? '';

            if ($blog->updateBlog($dates, $text, $title)) {
                redirect_header(SimpleBlogUtils::createUrl($xoopsUser->uid()), 2, _MD_SIMPLEBLOG_BLOG_UPDATE);

                exit();
            }  

            redirect_header(SimpleBlogUtils::createUrl($xoopsUser->uid()), 2, _MD_SIMPLEBLOG_INTERNALERROR . '(0.2)');

            exit();
        }  

        if (on == $preview) {
            $ts = MyTextSanitizer::getInstance();

            $formValue['text'] = $_POST['text'];

            $formValue['text'] = $ts->previewTarea($formValue['text'], 0, 1, 1, 1, 1);

            $formValue['text_edit'] = $_POST['text'];

            $formValue['text_edit'] = $ts->htmlSpecialChars($formValue['text_edit']);

            $formValue['title'] = $_POST['title'];

            $formValue['title'] = $ts->htmlSpecialChars($formValue['title']);

            $formValue['trackback'] = $_POST['trackback'];

            $formValue['year'] = $dates['year'];

            $formValue['month'] = $dates['month'];

            $formValue['date'] = $dates['date'];

            echo "<p><table class='outer' cellspacing='1'>\n";

            echo '<tr><th>' . _MD_SIMPLEBLOG_FORM_PREVIEW . "</th></tr>\n";

            echo '<tr class="even"><td><div class="comText">' . $formValue['text'] . '</div></td></tr>';

            echo "</table></p>\n";
        } else {
            $result = $blog->getBlog1($dates);

            $formValue['text'] = array_key_exists('text', $result) ? $result['text'] : '';

            $formValue['text_edit'] = array_key_exists('text_edit', $result) ? $result['text_edit'] : '';

            $formValue['title'] = array_key_exists('title', $result) ? $result['title'] : '';

            $formValue['year'] = array_key_exists('year', $result) ? $result['year'] : '';

            $formValue['month'] = array_key_exists('month', $result) ? $result['month'] : '';

            $formValue['date'] = array_key_exists('date', $result) ? $result['date'] : '';

            $formValue['trackback'] = '';
        }

        $GLOBALS['text'] = $formValue['text_edit'];

        echo "<!-- begin of simpleblog edit form -->\n";

        echo "<table class='outer' cellspacing='1'>\n";

        echo '<form method="post" action="' . XOOPS_URL . '/modules/simpleblog/edit.php">' . "\n";

        echo '<tr><th colspan="2">' . $blog->getTitle() . "</th></tr>\n";

        echo '<tr><td align="left" class="head">' . _MD_SIMPLEBLOG_FORM_DATE . '</td><td align="left" class="even">' . $formValue['year'] . '-' . $formValue['month'] . '-' . $formValue['date'] . '</td></tr>';

        echo "<tr><td align='left' class='head'>" . _MD_SIMPLEBLOG_FORM_TITLE . "</td>\n";

        echo "<td align='left' class='even'><input type='text' name='title' size='70' value='" . $formValue['title'] . "'></td></tr>\n";

        echo "<tr><td align='left' class='head'>Trackback URL</td><td class='even'><input type='text' name='trackback' size='70' value='" . $formValue['trackback'] . "'></td></tr>\n";

        echo '<tr><td align="left" class="head">' . _MD_SIMPLEBLOG_FORM_CONTENS . "</td>\n";

        echo '<td class="even" >';

        echo '<input type="hidden" name="year" value="' . $formValue['year'] . '">' . "\n";

        echo '<input type="hidden" name="month" value="' . $formValue['month'] . '">' . "\n";

        echo '<input type="hidden" name="date" value="' . $formValue['date'] . '">' . "\n";

        echo '<input type="hidden" name="param" value="' . $xoopsUser->uid() . '-' . $formValue['year'] . $formValue['month'] . $formValue['date'] . '">' . "\n";

        xoopsCodeTarea('text');

        xoopsSmilies('text');

        echo '<br><input type="submit" value="' . _MD_SIMPLEBLOG_FORM_PREVIEW . '" name="preview"> &nbsp; <input type="submit" value="' . _MD_SIMPLEBLOG_FORM_SEND . '" name="commit">' . "\n";

        echo "\n";

        echo "<!-- begin of simpleblog edit form -->\n";

        echo "</td></tr></form>\n";

        echo "</table>\n";
    }
require __DIR__ . '/footer.php';
