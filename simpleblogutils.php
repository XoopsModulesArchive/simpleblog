<?php
// $Id: simpleblogutils.php,v 1.1 2006/03/20 16:18:58 mikhail Exp $
if (
    !defined('XOOPS_ROOT_PATH') ||
    !defined('XOOPS_CACHE_PATH') ||
    !is_file(XOOPS_ROOT_PATH . '/modules/simpleblog/conf.php') ||
    !is_file(XOOPS_ROOT_PATH . '/modules/simpleblog/class/SimpleBlogPing2.php')
) {
    exit();
}
require_once XOOPS_ROOT_PATH . '/modules/simpleblog/conf.php';
require_once XOOPS_ROOT_PATH . '/modules/simpleblog/class/SimpleBlogPing2.php';

class SimpleBlogUtils
{
    public function log($str)
    {
        if ( /* defined(SIMPLEBLOG_DEBUG_OUT)  && */ (SIMPLEBLOG_DEBUG_OUT == 1)) {
            $log = XOOPS_ROOT_PATH . '/cache/simpleblog.log';

            $fp = fopen($log, 'ab');

            fwrite($fp, $str . "\n");

            fclose($fp);
        }
    }

    public function getDateFromHttpParams()
    {
        global $_POST, $_GET, $_SERVER;

        $param = isset($_POST['param']) ? ($_POST['param']) : 0;

        if (0 == $param) {
            $param = isset($_GET['param']) ? ($_GET['param']) : 0;
        }

        if (0 == $param) {
            $tmp = explode('/', $_SERVER['REQUEST_URI']);

            $param = ($tmp[count($tmp) - 1]);
        }

        $param = trim($param);

        if (0 == $param) {
            return false;
        }

        $result = [];

        $result['params'] = $param;

        if (preg_match('/^([0-9]+)-([0-9]{4})([0-9]{2})([0-9]{2})-([a-zA-Z0-9]*)/', $param, $m)) {
            $result['uid'] = self::checkUid($m[1]);

            $result['year'] = self::checkYeak($m[2]);

            $result['month'] = self::checkMonth($m[3]);

            $result['date'] = self::checkDate($m[2], $m[3], $m[4]);

            $result['command'] = trim($m[5]);
        } elseif (preg_match('/^([0-9]+)-([0-9]{4})([0-9]{2})([0-9]{2})/', $param, $m)) {
            $result['uid'] = self::checkUid($m[1]);

            $result['year'] = self::checkYeak($m[2]);

            $result['month'] = self::checkMonth($m[3]);

            $result['date'] = self::checkDate($m[2], $m[3], $m[4]);
        } elseif (preg_match('/^([0-9]+)-([0-9]{4})([0-9]{2})/', $param, $m)) {
            $result['uid'] = self::checkUid($m[1]);

            $result['year'] = self::checkYeak($m[2]);

            $result['month'] = self::checkMonth($m[3]);
        } elseif (preg_match('/^([0-9]+)/', $param, $m)) {
            $result['uid'] = self::checkUid($m[1]);
        } else {
            redirect_header(XOOPS_URL . '/', 1, _MD_SIMPLEBLOG_INVALID_DATE . '(INVALID PARAM)');

            exit();
        }

        return $result;
    }

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

    public function weblogUpdatesPing($rss, $url, $blog_name = null, $title = null, $excerpt = null)
    {
        $ping = new SimpleBlogPing2($rss, $url, $blog_name, $title, $excerpt);

        $ping->send();

        /* debug log
        ob_start();
        print_r($ping);
        $log = ob_get_contents();
        ob_end_clean();
        SimpleBlogUtils::log($log);
        */
    }

    public function newApplication($in_title, $in_permission)
    {
        global $xoopsUser, $xoopsDB;

        $title = '';

        $permission = -1;

        if (!empty($in_title)) {
            $title = self::convert2sqlString($in_title);
        }

        if ((0 == $in_permission) || (1 == $in_permission) || (2 == $in_permission) || (3 == $in_permission)) {
            $permission = (int)$in_permission;
        }

        if ($permission < 0) {
            return _MD_SIMPLEBLOG_ERR_INVALID_PERMISSION;
        }

        if (!$result = $xoopsDB->query('select uid from ' . SIMPLEBLOG_TABLE_APPL . ' where uid = ' . $xoopsUser->uid())) {
            return 'select error';
        }

        if (list($tmpUid) = $xoopsDB->fetchRow($result)) {
            return _MD_SIMPLEBLOG_ERR_APPLICATION_ALREADY_APPLIED;
        }

        if (!$result = $xoopsDB->query('select uid from ' . SIMPLEBLOG_TABLE_INFO . ' where uid = ' . $xoopsUser->uid())) {
            return 'select error';
        }

        if (list($tmpUid) = $xoopsDB->fetchRow($result)) {
            return _MD_SIMPLEBLOG_ERR_ALREADY_WRITABLE;
        }

        $sql = sprintf(
            "insert into %s (uid, title, permission, create_date) values(%u, '%s', %u, CURRENT_TIMESTAMP())",
            SIMPLEBLOG_TABLE_APPL,
            $xoopsUser->uid(),
            $title,
            $permission
        );

        if (!$result = $xoopsDB->query($sql)) {
            return 'insert error';
        }

        return '';
    }

    public function getXoopsModuleConfig($key)
    {
        global $xoopsDB;

        $mid = -1;

        $sql = 'SELECT mid FROM ' . $xoopsDB->prefix('modules') . " WHERE dirname = 'simpleblog'";

        if (!$result = $xoopsDB->query($sql)) {
            return false;
        }

        $numrows = $xoopsDB->getRowsNum($result);

        if (1 == $numrows) {
            [$l_mid] = $xoopsDB->fetchRow($result);

            $mid = $l_mid;
        } else {
            return false;
        }

        $sql = 'select conf_value from ' . $xoopsDB->prefix('config') . ' where conf_modid = ' . $mid . " and conf_name = '" . trim($key) . "'";

        if (!$result = $xoopsDB->query($sql)) {
            return false;
        }

        $numrows = $xoopsDB->getRowsNum($result);

        if (1 == $numrows) {
            [$value] = $xoopsDB->fetchRow($result);

            return (int)$value;
        }
  

        return false;
    }

    public function get_blog_list($start = 0)
    {
        global $xoopsUser, $xoopsDB;

        $useRerite = self::getXoopsModuleConfig('SIMPLEBLOG_REWRITE');

        $block_list_num = 10;

        $permission = 2;

        $dateFormat = '%m/%d %k:%i';

        if ($xoopsUser) {
            $permission = 4;
        }

        $selectMax = $start + SIMPLEBLOG_BLOCK_LIST_NUM;

        $sql_select = sprintf(
            'select uid, UNIX_TIMESTAMP(last_update) last_update,  title FROM %s WHERE blog_permission <= %u and last_update != \'0000-00-00\' ORDER BY last_update desc limit %u',
            SIMPLEBLOG_TABLE_INFO,
            $permission,
            $selectMax
        );

        if (!$result_select = $xoopsDB->query($sql_select)) {
            return false;
        }

        $tmp = [];

        $i = 0;

        while (list(
                $result_uid,
                $result_last_update,
                $title
            ) = $xoopsDB->fetchRow($result_select)
        ) {
            if ($i >= $start) {
                $res = [];

                $res['uid'] = $result_uid;

                $res['last_update'] = $result_last_update;

                $res['last_update_s'] = formatTimestamp($result_last_update, 's');

                $res['last_update_m'] = formatTimestamp($result_last_update, 'm');

                $res['last_update_l'] = formatTimestamp($result_last_update, 'l');

                $res['title'] = $title;

                $res['url'] = self::createUrl($result_uid);

                $tmp[$i] = $res;
            }

            $i++;
        }

        $block = [];

        $userHander = new XoopsUserHandler($xoopsDB);

        $i = 0;

        foreach ($tmp as $target) {
            $tUser = $userHander->get($target['uid']);

            if (is_object($tUser)) {
                $target['uname'] = $tUser->uname();

                $target['last_update4rss'] = self::toRssDate($target['last_update'], $tUser->getVar('timezone_offset'));

                if (empty($target['title'])) {
                    $target['title'] = _MD_SIMPLEBLOG_TITLE_PREFIX . $target['uname'] . _MD_SIMPLEBLOG_TITLE_SUFFIX;
                }
            } else {
                $target['uname'] = 'deleted';

                if (empty($target['title'])) {
                    $target['title'] = _MD_SIMPLEBLOG_TITLE_PREFIX . ' deleted ' . _MD_SIMPLEBLOG_TITLE_SUFFIX;
                }
            }

            $block[$i] = $target;

            $i++;
        }

        return $block;
    }

    public function createRssURL($uid)
    {
        $useRerite = self::getXoopsModuleConfig('SIMPLEBLOG_REWRITE');

        if ((empty($useRerite)) || (0 == $useRerite)) {
            return SIMPLEBLOG_DIR . 'rss.php' . SIMPLEBLOG_REQUEST_URI_SEP . $uid;
        }
  

        return SIMPLEBLOG_DIR . 'rss/' . $uid . '.xml';
    }

    public function createUrl($uid, $year = 0, $month = 0, $date = 0, $command = null)
    {
        return XOOPS_URL . '/modules/simpleblog/' . self::createUrlNoPath($uid, $year, $month, $date, $command);
    }

    public function createUrlNoPath($uid, $year = 0, $month = 0, $date = 0, $command = null)
    {
        $useRerite = self::getXoopsModuleConfig('SIMPLEBLOG_REWRITE');

        $result = '';

        if ((empty($useRerite)) || (0 == $useRerite)) {
            $result .= 'index.php' . SIMPLEBLOG_REQUEST_URI_SEP . self::makeParams($uid, $year, $month, $date, $command);
        } else {
            $result .= 'view/' . self::makeParams($uid, $year, $month, $date, $command) . '.html';
        }

        return $result;
    }

    public function mb_strcut($text, $start, $end)
    {
        if (function_exists('mb_strcut')) {
            // return mb_strcut($text, $start, $end);

            return mb_substr($text, $start, $end);
        }
  

        return mb_substr($text, $start, $end);
        // return strcut($text, $start, $end);
    }

    public function toRssDate($time, $timezone = null)
    {
        if (!empty($timezone)) {
            $time = xoops_getUserTimestamp($time, $timezone);
        }

        $res = date('Y-m-d\\TH:i:sO', $time);

        // mmmm

        $result = mb_substr($res, 0, -2) . ':' . mb_substr($res, -2);

        return $result;
    }

    public function checkUid($iuid)
    {
        $uid = (int)$iuid;

        if ($uid > 0) {
            return $uid;
        }
    }

    public function checkYeak($iyear)
    {
        $year = (int)$iyear;

        if (($year > 1000) && ($year < 3000)) {
            return $iyear;
        }

        redirect_header(XOOPS_URL . '/', 1, _MD_SIMPLEBLOG_INVALID_DATE . '(YEAR)' . $iyear);

        exit();
    }

    public function checkMonth($imonth)
    {
        $month = (int)$imonth;

        if (($month > 0) && ($month < 13)) {
            return $imonth;
        }

        redirect_header(XOOPS_URL . '/', 1, _MD_SIMPLEBLOG_INVALID_DATE . '(MONTH)');

        exit();
    }

    public function checkDate($year, $month, $date)
    {
        if (checkdate((int)$month, (int)$date, (int)$year)) {
            return $date;
        }

        redirect_header(XOOPS_URL . '/', 1, _MD_SIMPLEBLOG_INVALID_DATE . '(ALL DATE) ' . (int)$year . '-' . (int)$month . '-' . (int)$date);

        exit();
    }

    public function makeParams($uid, $year = 0, $month = 0, $date = 0, $command = null)
    {
        $result = '';

        $c = '';

        if (!empty($command)) {
            $c = '-' . $command;
        }

        if (0 == $year) {
            $result = $uid;
        } elseif (0 == $date) {
            $result = sprintf('%s-%04u%02u%s', '' . $uid, $year, $month, $c);
        } else {
            $result = sprintf('%s-%04u%02u%02u%s', '' . $uid, $year, $month, $date, $c);
        }

        return $result;
    }

    public function makeTrackBackURL($uid, $year = 0, $month = 0, $date = 0)
    {
        return XOOPS_URL . '/modules/simpleblog/trackback.php' . SIMPLEBLOG_REQUEST_URI_SEP . self::makeParams($uid, $year, $month, $date);
    }

    public function isCompleteDate($d)
    {
        if (checkdate((int)$d['month'], (int)$d['date'], (int)$d['year'])) {
            return true;
        }

        return false;
    }

    public function complementDate($d)
    {
        if (!checkdate((int)$d['month'], (int)$d['date'], (int)$d['year'])) {
            $time = time();

            $d['year'] = date('Y', $time);

            $d['month'] = sprintf('%02u', date('m', $time));

            $d['date'] = sprintf('%02u', date('d', $time));
        }

        return $d;
    }

    public function convert_encoding($text, $from, $to)
    {
        if (function_exists('mb_http_output')) {
            mb_http_output('pass');
        }

        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($text, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $text);
        }
  

        return $text;
    }

    public function assign_message(&$tpl)
    {
        $all_constants_ = get_defined_constants();

        foreach ($all_constants_ as $key => $val) {
            if (preg_match('/^_(MB|MD|AM|MI)_SIMPLEBLOG_(.)*$/', $key) || preg_match('/^SIMPLEBLOG_(.)*$/', $key)) {
                if (is_array($tpl)) {
                    $tpl[$key] = $val;
                } elseif (is_object($tpl)) {
                    $tpl->assign($key, $val);
                }
            }
        }
    }

    /*
    function get_recent_trackback($date){
        global $xoopsDB;
        $sql = 'select title, url from '.SIMPLEBLOG_TABLE_TRACKBACK.' where uid = '.$date['uid'].' order by t_date desc';
        if(!$db_result = $this->xoopsDB->query($sql)){
            return false;
        }
        $i = 0;

        $result['html'] = '<div>';
        while(list($title, $url) = $this->xoopsDB->fetchRow($db_result)){
            $result[data][] = new array(){ 'title' => $title, 'url' => $url};
            $i++;
            $result['html'] .= '<a href="'.$url.'" target="_blank">'.$title.'</a><br>';
        }
        $result['html'] .= '</div>';

        return $result;
    }
    */

    public function send_trackback_ping($trackback_url, $url, $title, $blog_name, $excerpt = null)
    {
        SimpleBlogPing2::send_trackback_ping($trackback_url, $url, $title, $blog_name, $excerpt);
    }    

    public function remove_html_tags($t)
    {
        return preg_replace_callback(
            "/(<[a-zA-Z0-9\"\'\=\s\/\-\~\_;\:\.\n\r\t\?\&\+\%\&]*?>|\n|\r)/ms",
            /* "/(<[*]*?>|\n|\r)/ms", */
            'simpleblog_remove_html_tags_callback',
            $t
        );
    }    

    public function convert2sqlString($text)
    {
        $ts = MyTextSanitizer::getInstance();

        if (!is_object($ts)) {
            exit();
        }

        $res = $ts->stripSlashesGPC($text);

        $res = $ts->censorString($res);

        $res = addslashes($res);

        return $res;
    }
}
function simpleblog_remove_html_tags_callback($t)
{
    return '';
}
