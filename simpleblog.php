<?php
// $Id: simpleblog.php,v 1.1 2006/03/20 16:18:58 mikhail Exp $
if (
    !defined('XOOPS_ROOT_PATH') ||
    !is_file(XOOPS_ROOT_PATH . '/modules/simpleblog/conf.php') ||
    !is_file(XOOPS_ROOT_PATH . '/class/snoopy.php') ||
    !is_file(XOOPS_ROOT_PATH . '/modules/simpleblog/SimpleBlogUtils.php')
) {
    exit();
}
require_once XOOPS_ROOT_PATH . '/modules/simpleblog/conf.php';
require_once XOOPS_ROOT_PATH . '/class/snoopy.php';
require_once XOOPS_ROOT_PATH . '/modules/simpleblog/SimpleBlogUtils.php';

class SimpleBlog
{
    public $VIEW_NUM = 20;

    public $user_list;

    public $blogUid;

    public $targetUser;

    public $userHander;

    public $permission = -1;

    public $title = '';

    public $usersCache = [];

    public $ts;

    public $xoopsDB;

    public $simpleblog_configs = [];

    public function __construct($blogUid = -1)
    {
        global $xoopsDB;

        $this->xoopsDB = &$xoopsDB;

        $this->userHander = new XoopsUserHandler($this->xoopsDB);

        $this->ts = MyTextSanitizer::getInstance();

        $this->user_list = [];

        $this->blogUid = (int)$blogUid;

        if ($this->blogUid > 0) {
            $this->targetUser = $this->userHander->get($this->blogUid);

            if ((!$this->targetUser) || (!$this->targetUser->isActive())) {
                redirect_header(SIMPLEBLOG_DIR, 2, _MD_SIMPLEBLOG_NORIGHTTOACCESS);

                exit();
            }

            $usersCache[$this->blogUid] = $this->targetUser;
        } else {
            redirect_header(XOOPS_URL . '/', 5, _MD_SIMPLEBLOG_INTERNALERROR . '(99.0) [' . $this->blogUid . ']');

            exit();
        }
    }        

    public function getAllApplication()
    {
        global $xoopsDB;

        if (!$qResult = $xoopsDB->query('select uid, title, permission, create_date from ' . SIMPLEBLOG_TABLE_APPL . ' order by create_date')) {
            return false;
        }

        $result = [];

        while (list($uid, $title, $permission, $create_date) = $xoopsDB->fetchRow($qResult)) {
            $result[] = [
                'uid' => $uid,
                'title' => $title,
                'permission' => $permission,
                'create_date' => $create_date,
];
        }

        return $result;
    }

    public function deleteApplication($in_uid)
    {
        global $xoopsDB;

        $uid = (int)$in_uid;

        if ($uid > 0) {
            $xoopsDB->queryF(sprintf('delete from %s where uid = %u', SIMPLEBLOG_TABLE_APPL, $uid));
        }
    }        

    public function getTargetUname()
    {
        return $this->targetUser->uname();
    }

    /**
     * create new blog user
     * @param mixed $permission
     * @param mixed $title
     * @return bool
     * @return bool
     */

    public function createNewBlogUser($permission = 0, $title = '')
    {
        global $xoopsUser;

        $result = $this->xoopsDB->query('select count(*) from ' . SIMPLEBLOG_TABLE_INFO . ' where uid = ' . $this->blogUid);

        [$count] = $this->xoopsDB->fetchRow($result);

        if ($count > 0) {
            return false;
        }  

        $this->xoopsDB->queryF('insert into ' . SIMPLEBLOG_TABLE_INFO . '(uid, blog_permission, last_update, title) values (' . $this->blogUid . ', ' . (int)$permission . ', \'0000-00-00\', \'' . SimpleBlogUtils::convert2sqlString($title) . '\')');

        $result = $this->xoopsDB->query('select uid from ' . SIMPLEBLOG_TABLE_APPL . ' where uid = ' . $this->blogUid);

        if (list($appUid) = $this->xoopsDB->fetchRow($result)) {
            $this->xoopsDB->queryF('delete from ' . SIMPLEBLOG_TABLE_APPL . ' where uid = ' . $this->blogUid);
        }

        return true;
    }

    public function setBlogInfo($permisstion = 0, $title = '')
    {
        $this->xoopsDB->queryF('update ' . SIMPLEBLOG_TABLE_INFO . ' set blog_permission = ' . (int)$permisstion . ', title=\'' . $title . '\' where uid =' . $this->blogUid);
    }

    public function deleteAll()
    {
        $this->xoopsDB->queryF('delete from ' . SIMPLEBLOG_TABLE_INFO . ' where uid = ' . $this->blogUid);

        $this->xoopsDB->queryF('delete from ' . SIMPLEBLOG_TABLE_BLOG . ' where uid = ' . $this->blogUid);

        $this->xoopsDB->queryF('delete from ' . SIMPLEBLOG_TABLE_COMMENT . ' where uid = ' . $this->blogUid);

        $this->xoopsDB->queryF('delete from ' . SIMPLEBLOG_TABLE_TRACKBACK . ' where uid = ' . $this->blogUid);
    }

    public function loadBlogInfo()
    {
        global $xoopsUser;

        $sql = 'select blog_permission, title from ' . SIMPLEBLOG_TABLE_INFO . ' where uid=' . $this->targetUser->uid();

        if (!$result = $this->xoopsDB->query($sql)) {
            return false;
        }

        if (list($permission, $title) = $this->xoopsDB->fetchRow($result)) {
            $this->permission = $permission;

            $this->title = $title;

            return true;
        }

        return false;
    }    

    public function canWrite()
    {
        global $xoopsUser;

        if (!$xoopsUser) {
            return false;
        } elseif ($this->permission < 0) {
            if (!$this->loadBlogInfo()) {
                return false;
            }
        }

        if ($xoopsUser->uid() == $this->blogUid) {
            return true;
        }

        return false;
    }

    public function canRead()
    {
        global $xoopsUser;

        if ($xoopsUser) {
            return true;
        }

        if ($this->permission < 0) {
            $this->loadBlogInfo();
        }

        if ((0 == $this->permission) || (1 == $this->permission) || (2 == $this->permission)) {
            return true;
        }

        return false;
    }

    public function canComment()
    {
        global $xoopsUser;

        if ($this->permission < 0) {
            $this->loadBlogInfo();
        }

        if (0 == $this->permission) {
            return true;
        }

        if ($xoopsUser) {
            if ((1 == $this->permission) || (3 == $this->permission)) {
                return true;
            }
        }

        return false;
    }

    public function isPublic()
    {
        if ($this->permission < 0) {
            $this->loadBlogInfo();
        }

        if ((0 == $this->permission) || (1 == $this->permission) || (2 == $this->permission)) {
            return true;
        }

        return false;
    }

    public function useTrackBack()
    {
        if (!array_key_exists('SIMPLEBLOG_TRACKBACK', $this->simpleblog_configs)) {
            $tb = SimpleBlogUtils::getXoopsModuleConfig('SIMPLEBLOG_TRACKBACK');

            $this->simpleblog_configs['SIMPLEBLOG_TRACKBACK'] = (1 == $tb) ? true : false;
        }

        return $this->simpleblog_configs['SIMPLEBLOG_TRACKBACK'];
    }

    public function useUpdatePing()
    {
        if (!array_key_exists('SIMPLEBLOG_UPDATE_PING', $this->simpleblog_configs)) {
            $conf = SimpleBlogUtils::getXoopsModuleConfig('SIMPLEBLOG_UPDATE_PING');

            $this->simpleblog_configs['SIMPLEBLOG_UPDATE_PING'] = (1 == $conf) ? true : false;
        }

        return $this->simpleblog_configs['SIMPLEBLOG_UPDATE_PING'];
    }

    public function getTitle()
    {
        if ($this->permission < 0) {
            $this->loadBlogInfo();
        }

        if ('' != $this->title) {
            return $this->title;
        }
  

        return _MD_SIMPLEBLOG_TITLE_PREFIX . $this->getTargetUname() . _MI_SIMPLEBLOG_TITLE_SUFFIX;
    }    

    public function getBlogData($year = 0, $month = 0, $date = 0, $limit = 0)
    {
        global $xoopsUser;

        if (0 == $limit) {
            $limit = SIMPLEBLOG_VIEW_LIST_NUM;
        }

        $dateFormat = '%y/%m/%d';

        if (!$this->canRead()) {
            redirect_header(XOOPS_URL . '/', 1, _MD_SIMPLEBLOG_NORIGHTTOACCESS);

            exit();
        }

        $sql_blog = '';

        $tb = $this->useTrackBack();

        if (($year > 1000) && ($month > 0)) {
            if ($date > 0) { // display date blog
                if (checkdate($month, $date, $year)) {
                    $sql_blog = 'select UNIX_TIMESTAMP(last_update) last_update, blog_date, title, post_text from ' . SIMPLEBLOG_TABLE_BLOG . ' where uid = ' . $this->blogUid . ' and blog_date= \'' . $year . '-' . $month . '-' . $date . '\' order by blog_date desc ';
                }
            } else { // display month blog
                $sql_blog = 'select UNIX_TIMESTAMP(last_update) last_update, blog_date, title, post_text from ' . SIMPLEBLOG_TABLE_BLOG . ' where uid = ' . $this->blogUid . ' and DATE_FORMAT(blog_date, \'%Y\')=' . $year . ' and DATE_FORMAT(blog_date, \'%m\') = ' . $month . ' order by blog_date desc ';

                $tb = false;
            }
        }

        if ('' == $sql_blog) { // display current blog
            $sql_blog = 'select UNIX_TIMESTAMP(last_update) last_update, blog_date, title, post_text from ' . SIMPLEBLOG_TABLE_BLOG . ' where uid = ' . $this->blogUid . ' order by blog_date desc limit ' . $limit;

            $tb = false;
        }

        if (!$result_blog = $this->xoopsDB->query($sql_blog)) {
            return false;
        }

        $result = [];

        $i = 0;

        while (list(
                $last_update,
                $result_date,
                $result_title,
                $result_post_text
            ) = $this->xoopsDB->fetchRow($result_blog)
        ) {
            $result['blog'][$i]['year'] = SimpleBlogUtils::mb_strcut($result_date, 0, 4);

            $result['blog'][$i]['month'] = SimpleBlogUtils::mb_strcut($result_date, 5, 2);

            $result['blog'][$i]['date'] = SimpleBlogUtils::mb_strcut($result_date, 8, 2);

            $result['blog'][$i]['date_all'] = $result_date;

            $result['blog'][$i]['title'] = $result_title;

            $result['blog'][$i]['text'] = $this->ts->displayTarea($result_post_text);

            $result['blog'][$i]['text_edit'] = $this->ts->htmlSpecialChars($result_post_text);

            $result['blog'][$i]['comments'] = $this->getComments($result_date);

            $result['blog'][$i]['url'] = SimpleBlogUtils::createUrl($this->blogUid, $result['blog'][$i]['year'], $result['blog'][$i]['month'], $result['blog'][$i]['date']);

            $result['blog'][$i]['last_update_s'] = formatTimestamp($last_update, 's');

            $result['blog'][$i]['last_update_m'] = formatTimestamp($last_update, 'm');

            $result['blog'][$i]['last_update_l'] = formatTimestamp($last_update, 'l');

            $result['blog'][$i]['last_update4rss'] = SimpleBlogUtils::toRssDate($last_update, $this->targetUser->getVar('timezone_offset'));

            if (true === $tb) {
                $result['blog'][$i]['trackback_url'] = SimpleBlogUtils::createUrl($this->blogUid, $result['blog'][$i]['year'], $result['blog'][$i]['month'], $result['blog'][$i]['date'], 'tb');
            }

            $i++;
        }

        $result['blog_num'] = $i;

        $time = time();

        $result['today']['year'] = date('Y', $time);

        $result['today']['month'] = date('m', $time);

        $result['today']['date'] = date('d', $time);

        $result['user'] = $this->targetUser;

        $result['uid'] = $this->blogUid;

        $result['uname'] = $this->targetUser->uname();

        return $result;
    }    

    /*
    function hasBlog($dates){
        $sqlDate = $this->xoopsDB->quoteString($dates['year'].'-'.$dates['month'].'-'.$dates['date']);
        $sql = "select count(*) from ".SIMPLEBLOG_TABLE_BLOG." where uid = ".$this->blogUid.' and  blog_date = '.$sqlDate;
        if(!$result_select = $this->xoopsDB->query($sql)){
            if(list($num) = $this->xoopsDB->fetchRow($result_select)){
                if($num > 0){
                    return true;
                }
            }
        }
        return false;
    }
    */

    public function getBlog1($dates)
    {
        global $xoopsUser;

        $sqlDate = $dates['year'] . '-' . $dates['month'] . '-' . $dates['date'];

        $sql = 'select title, post_text FROM ' . SIMPLEBLOG_TABLE_BLOG . ' WHERE uid = ' . $this->blogUid . ' and  blog_date = \'' . $sqlDate . '\'';

        if (!$result_select = $this->xoopsDB->query($sql)) {
            return false;
        }

        $result = [];

        $result['year'] = $dates['year'];

        $result['month'] = $dates['month'];

        $result['date'] = $dates['date'];

        if (list($title, $text) = $this->xoopsDB->fetchRow($result_select)) {
            $result['date_all'] = $sqlDate;

            $result['title'] = $title;

            $result['text'] = $this->ts->displayTarea($text);

            $result['text_edit'] = $this->ts->htmlSpecialChars($text);
        }

        return $result;
    }    

    public function updateBlog($dates, $text, $title = '')
    {
        global $xoopsUser, $_POST;

        $sqlDate = $dates['year'] . '-' . $dates['month'] . '-' . $dates['date'];

        $sqlText = SimpleBlogUtils::convert2sqlString($text);

        $sqlTitle = SimpleBlogUtils::convert2sqlString($title);

        $uid = $xoopsUser->uid();

        if (empty($text)) {
            $sql = sprintf("delete from %s where uid=%u and blog_date='%s'", SIMPLEBLOG_TABLE_BLOG, $uid, $sqlDate);
        } else {
            $sql = sprintf("select uid from %s where uid = %u and blog_date = '%s'", SIMPLEBLOG_TABLE_BLOG, $uid, $sqlDate);

            if (!$result_select = $this->xoopsDB->query($sql)) {
                return false;
            }

            if (0 == $this->xoopsDB->getRowsNum($result_select)) {
                $sql = sprintf("insert into %s(uid, blog_date, title, post_text) values(%u, '%s', '%s', '%s')", SIMPLEBLOG_TABLE_BLOG, $uid, $sqlDate, $sqlTitle, $sqlText);
            } else {
                $sql = sprintf("update %s set title = '%s', post_text = '%s' where uid = %u and blog_date ='%s'", SIMPLEBLOG_TABLE_BLOG, $sqlTitle, $sqlText, $uid, $sqlDate);
            }
        }

        SimpleBlogUtils::log($sql);

        $this->xoopsDB->queryF($sql);

        $this->update();

        if (!empty($text) && $this->isPublic()) {
            if ($this->useUpdatePing()) {
                SimpleBlogUtils::weblogUpdatesPing(
                    SimpleBlogUtils::createRssURL($uid),
                    SimpleBlogUtils::createUrl($uid),
                    $this->getTitle(),
                    $title
                );
            }

            if ((array_key_exists('trackback', $_POST)) &&
                (!empty($_POST['trackback']))
            ) {
                $tb_text = $this->ts->displayTarea($sqlText);

                SimpleBlogUtils::send_trackback_ping(
                    trim($_POST['trackback']),
                    SimpleBlogUtils::createUrl($uid, $dates['year'], $dates['month'], $dates['date']),
                    $title,
                    $this->getTitle(),
                    (mb_strlen($tb_text) > 251) ? mb_strcut($tb_text, 0, 251) . '...' : $tb_text
                );
            }
        }

        return true;
    }        

    public function update()
    {
        $sql = 'update ' . SIMPLEBLOG_TABLE_INFO . ' set last_update = CURRENT_TIMESTAMP() where uid = ' . $this->blogUid;

        $this->xoopsDB->queryF($sql);
    }    

    public function insertComment($dates, $name, $comment)
    {
        global $xoopsUser;

        $uid = 0;

        if ($xoopsUser) {
            $uid = $xoopsUser->uid();
        }

        $sqlDate = $dates['year'] . '-' . $dates['month'] . '-' . $dates['date'];

        $sqlName = SimpleBlogUtils::convert2sqlString($name);

        $sqlComment = SimpleBlogUtils::convert2sqlString($comment);

        $sql = sprintf("select count(*) from %s where uid = %u and blog_date = '%s'", SIMPLEBLOG_TABLE_BLOG, $this->blogUid, $sqlDate);

        if (!$result_select = $this->xoopsDB->query($sql)) {
            return false;
        }

        if (1 == $this->xoopsDB->getRowsNum($result_select)) {
            $sql_base = "insert into %s (uid, blog_date, comment_id, comment_uid, comment_name, post_text, create_date) values(%u, '%s', null, %u, '%s', '%s', CURRENT_TIMESTAMP())";

            $sql = sprintf($sql_base, SIMPLEBLOG_TABLE_COMMENT, $this->blogUid, $sqlDate, $uid, $sqlName, $sqlComment);

            $result = $this->xoopsDB->queryF($sql);

            $this->update();

            return true;
        }

        return false;
    }

    public function escapeHtml($text)
    {
        $result = $text;

        // $result = preg_replace('&', '&amp;', $text);

        $result = preg_replace('<', '&lt;', $result);

        $result = preg_replace('>', '&gt;', $result);

        // $result = preg_replace('\'', '&apos;', $result);

        $result = preg_replace('"', '&quot;', $result);

        //$result = preg_replace('\r\n', '\n', $result);

        //$result = preg_replace('\r', '\n', $result);

        //$result = preg_replace('\n', '<br>', $result);

        return $result;
    }

    public function getBlogIndex()
    {
        global $xoopsUser;

        $sql = 'select distinct DATE_FORMAT(blog_date, \'%Y\') year, DATE_FORMAT(blog_date, \'%m\') month from ' . SIMPLEBLOG_TABLE_BLOG . ' where uid = ' . $this->blogUid . ' and blog_date != \'0000-00-00\' order by year desc, month';

        if (!$result_select = $this->xoopsDB->query($sql)) {
            return false;
        }

        $result = [];

        while (list($year, $month) = $this->xoopsDB->fetchRow($result_select)) {
            // $result[$year][$month] = $month;

            $result[$year][$month]['month'] = $month;

            $result[$year][$month]['url'] = SimpleBlogUtils::createUrl($this->blogUid, $year, $month);
        }

        return $result;
    }

    public function getComments($blogDate)
    {
        global $xoopsUser;

        $sql = 'select comment_id, comment_uid,comment_name, post_text, UNIX_TIMESTAMP(create_date) create_date from ' . SIMPLEBLOG_TABLE_COMMENT . ' where uid = ' . $this->blogUid . ' and blog_date = ' . $this->xoopsDB->quoteString($blogDate) . ' order by comment_id ';

        if (!$result_select = $this->xoopsDB->query($sql)) {
            return false;
        }

        $i = 0;

        $comments = [];

        while (
            list($comment_id, $comment_uid, $comment_name, $post_text, $create_date) = $this->xoopsDB->fetchRow($result_select)
        ) {
            $comments[$i]['id'] = $comment_id;

            $comments[$i]['uid'] = $comment_uid;

            $comments[$i]['create_date'] = $create_date;

            $comments[$i]['create_date_s'] = (0 == $create_date) ? '<unknown>' : formatTimestamp($create_date, 's');

            $comments[$i]['create_date_m'] = (0 == $create_date) ? '<unknown>' : formatTimestamp($create_date, 'm');

            $comments[$i]['create_date_l'] = (0 == $create_date) ? '<unknown>' : formatTimestamp($create_date, 'l');

            if ($comment_uid > 0) {
                if (in_array($comment_uid, $this->usersCache, true)) {
                    $comments[$i]['name'] = $this->usersCache[$comment_uid]->uname();
                } else {
                    $tmpUser = $this->userHander->get($comment_uid);

                    if (is_object($tmpUser)) {
                        $this->usersCache[$comment_uid] = $tmpUser;

                        $comments[$i]['name'] = $this->usersCache[$comment_uid]->uname();
                    } else {
                        $comments[$i]['name'] = $comment_name . '@' . _MD_SIMPLEBLOG_FORM_GUEST;
                    }
                }
            } else {
                $comments[$i]['name'] = $comment_name . '@' . _MD_SIMPLEBLOG_FORM_GUEST;
            }

            $comments[$i]['comment'] = $post_text;

            $i++;
        }

        return $comments;
    }

    public function convert_trackback_encoding($dates, $text)
    {
        if (empty($dates) || !array_key_exists('command', $dates)) {
            return $text;
        } elseif ('eucjp' == $dates['command']) {
            return SimpleBlogUtils::convert_encoding($text, 'euc-jp', _CHARSET);
        } elseif ('sjis' == $dates['command']) {
            return SimpleBlogUtils::convert_encoding($text, 'sjis', _CHARSET);
        } elseif ('utf8' == $dates['command']) {
            return SimpleBlogUtils::convert_encoding($text, 'utf-8', _CHARSET);
        }

        return $text;
    }

    public function recieve_trackback_ping($dates = null)
    {
        global $_POST, $_GET;

        $referer = null;

        $title = null;

        $tb = $this->useTrackBack();

        if ((true === $tb) && array_key_exists('url', $_POST) && !empty($_POST['url'])) {
            $referer = trim($_POST['url']);

            $title = array_key_exists('title', $_POST) ? $this->convert_trackback_encoding($dates, trim($_POST['title'])) : null;
        } elseif ((true === $tb) && array_key_exists('url', $_GET) && !empty($_GET['url'])) {
            $referer = trim($_GET['url']);

            $title = array_key_exists('blog_name', $_GET) ? $this->convert_trackback_encoding($dates, trim($_GET['blog_name'])) . '&nbsp;/&nbsp;' : null;

            $title .= array_key_exists('title', $_GET) ? $this->convert_trackback_encoding($dates, trim($_GET['title'])) : null;
        } elseif (array_key_exists('HTTP_REFERER', $_SERVER)) {
            $referer = trim(\Xmf\Request::getString('HTTP_REFERER', '', 'SERVER'));

            if ((empty($referer)) || (preg_match('/^' . preg_replace('/', '\\/', XOOPS_URL) . '*/', $referer))) {
                return 'same site';
            }
        } else {
            return 'no args';
        }

        if (SimpleBlogUtils::isCompleteDate($dates)) {
            $targetDate = $dates['year'] . '-' . $dates['month'] . '-' . $dates['date'];
        }

        // get current date

        $sql = 'select blog_date from ' . SIMPLEBLOG_TABLE_BLOG . ' where uid = ' . $this->blogUid . ' order by blog_date desc limit 1';

        if (!$result_select = $this->xoopsDB->query($sql)) {
            return 'sql error';
        }

        [$current_date] = $this->xoopsDB->fetchRow($result_select);

        if (!empty($current_date)) {
            $this->incrementTrackBack($current_date, $referer, $title);

            if ((!empty($targetDate)) && ($current_date != $targetDate)) {
                $this->incrementTrackBack($targetDate, $referer, $title);
            }
        }

        return 'ok';
    }

    public function incrementTrackBack($date, $url, $title)
    {
        $t = empty($title) ? 'null' : $this->xoopsDB->quoteString($title);

        $u = $this->xoopsDB->quoteString($url);

        $d = $this->xoopsDB->quoteString($date);

        $update = 'update ' . SIMPLEBLOG_TABLE_TRACKBACK . ' set count = count+1, title = %s where uid = %u and t_date = %s and url = %s';

        $this->xoopsDB->queryF(sprintf($update, $t, $this->blogUid, $d, $u));

        if (0 == $this->xoopsDB->getAffectedRows()) {
            $insert = 'insert into ' . SIMPLEBLOG_TABLE_TRACKBACK . '(uid, t_date, count, title, url) VALUES(%u, %s, 1, %s, %s)';

            $this->xoopsDB->queryF(sprintf($insert, $this->blogUid, $d, $t, $u));
        }
    }

    public function getTrackBack($date)
    {
        $sqlDate = $this->xoopsDB->quoteString($date['year'] . '-' . $date['month'] . '-' . $date['date']);

        $sql = 'select count, t_date, title, url from ' . SIMPLEBLOG_TABLE_TRACKBACK . ' where uid = ' . $this->blogUid . ' and t_date = ' . $sqlDate . '  order by count desc';

        if (!$result_select = $this->xoopsDB->query($sql)) {
            return false;
        }

        $result = [];

        while (list($count, $date, $title, $url) = $this->xoopsDB->fetchRow($result_select)) {
            $t = [];

            $t['count'] = $count;

            $t['date'] = $date;

            $t['url'] = htmlspecialchars($url, ENT_QUOTES | ENT_HTML5);

            $t['title'] = (empty($title)) ? htmlspecialchars($url, ENT_QUOTES | ENT_HTML5) : '<b>[TrackBack]' . htmlspecialchars($title, ENT_QUOTES | ENT_HTML5) . '</b>';

            $result[] = $t;
        }

        return $result;
    }

    // deprecated method

    public function getApplicationNum()
    {
        global $xoopsDB;

        if (!$dbResult = $xoopsDB->query('select count(*) num from ' . SIMPLEBLOG_TABLE_APPL)) {
            return 0;
        }

        if (list($num) = $xoopsDB->fetchRow($dbResult)) {
            return $num;
        }

        return 0;
    }
}
