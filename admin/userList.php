<?php
// $Id: userList.php,v 1.1 2006/03/20 16:18:57 mikhail Exp $
require dirname(__DIR__, 3) . '/include/cp_header.php';
if (
    (!defined('XOOPS_ROOT_PATH')) ||
    (!is_object($xoopsUser)) ||
    (!$xoopsUser->isAdmin())) {
    exit();
}
if (!XoopsSecurity::checkReferer()) {
    redirect_header(XOOPS_URL . '/modules/simpleblog/', 2, 'Referer Check Failed');

    exit();
}

require_once dirname(__DIR__) . '/conf.php';
require_once dirname(__DIR__) . '/simpleblog.php';
xoops_cp_header();

$sql = 'select uid, uname from ' . $xoopsDB->prefix('users') . ' order by uid';
$result = $xoopsDB->query($sql);
$i = 0;
?>
<table width='100%' border='0' cellspacing='1' class='outer'>
	<tr><th><?php echo _AM_SIMPLEBLOG_ADMIN_UID; ?></th><th><?php echo _AM_SIMPLEBLOG_ADMIN_NAME; ?></th></tr>
<?php
while (list($uid, $uname) = $xoopsDB->fetchRow($result)) {
    if (0 == $i % 2) {
        echo "<tr class=\"even\">\n";
    } else {
        echo "<tr class=\"odd\">\n";
    }

    echo '<td><b>' . $uid . '</b></td><td>' . $uname . '</td>';

    echo "</tr>\n";

    $i++;
}
?>
</table>


<br><center>
<a href="http://sourceforge.jp/">
	<img src="http://sourceforge.jp/sflogo.php?group_id=757" width="96" height="31" border="0" alt="SourceForge.jp" target="_blank">
</a> 
<a href="http://feeds.archive.org/validator/check?url=<?php echo XOOPS_URL; ?>/modules/simpleblog/backend.php" target="_blank">
	<img src="<?php echo XOOPS_URL; ?>/modules/simpleblog/rss-valid.gif" border="0">
</a><br>
Created by <a href="http://xoops-modules.sourceforge.jp/" target="_blank">xoops-modules project</a>
</center>
<?php
xoops_cp_footer();
?>
