<?php
// $Id: trackback.php,v 1.1 2006/03/20 16:18:58 mikhail Exp $

require dirname(__DIR__, 2) . '/mainfile.php';
require_once XOOPS_ROOT_PATH . '/modules/simpleblog/simpleblog.php';
require_once XOOPS_ROOT_PATH . '/modules/simpleblog/SimpleBlogUtils.php';

$ok = '<?xml version="1.0" encoding="iso-8859-1"?>' . "\n    <response><error>0</error></response>";
$failed = '<?xml version="1.0" encoding="iso-8859-1"?>' . "\n<response><error>1</error><message>Ping Failed.</message></response>";
$failed1 = '<?xml version="1.0" encoding="iso-8859-1"?>' . "\n<response><error>1</error><message>";
$failed2 = '</message></response>';
$result = '';
$errmes = '';

$params = SimpleBlogUtils::getDateFromHttpParams();
if (SimpleBlogUtils::isCompleteDate($params)) {
    $blog = new SimpleBlog($params['uid']);

    if (!$blog->useTrackBack()) {
        $errmes = 'This blog cannot recieve trackback';

        $result = $failed1 . $errmes . $failed2;
    } elseif (!array_key_exists('title', $_POST) || empty($_POST['title'])) {
        $errmes = 'need Title';

        $result = $failed1 . $errmes . $failed2;
    } else {
        $errmes .= $blog->recieve_trackback_ping($params);

        $result = $ok;
    }
} else {
    $errmes .= 'Invalid link(Param is not complete)';

    $result = $failed1 . $errmes . $failed2;
}
$log = formatTimestamp(mktime(), 'm');
$log .= ' start trackback from ' . $_SERVER['REMOTE_ADDR'] . "=========================\n";
ob_start();
print_r($_SERVER);
print_r($_GET);
print_r($_POST);
$log .= ob_get_contents();
ob_end_clean();
$log .= "========================================\n";
$log .= $result . "\n";
$log .= 'end   trackback from ' . $_SERVER['REMOTE_ADDR'] . "=========================\n";
SimpleBlogUtils::log($log);
// log
/*
ob_start();
print_r($_SERVER);
print_r($_POST);
print_r($params);
print($errmes."\n");
$cnt = ob_get_contents();
ob_end_clean();
$fp = fopen( XOOPS_ROOT_PATH."/cache/php.log", "a");
fwrite($fp, $cnt."--\n".$HTTP_RAW_POST_DATA."\n".$result."\n==============================\n");
fclose($fp);
*/

// header('Content-Type:text/xml; charset=utf-8');
header('Content-Type: text/xml');
header('Content-Length: ' . mb_strlen($result));

echo($result);
