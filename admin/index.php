<?php
// $Id: index.php,v 1.1 2006/03/20 16:18:57 mikhail Exp $
require dirname(__DIR__, 3) . '/include/cp_header.php';
if (
    (!defined('XOOPS_ROOT_PATH')) ||
    (!is_object($xoopsUser)) ||
    (!$xoopsUser->isAdmin())) {
    exit();
}
require_once dirname(__DIR__) . '/conf.php';
require_once XOOPS_ROOT_PATH . '/modules/simpleblog/simpleblog.php';
xoops_cp_header();

// updater check
/*
$updates = SimpleBlog::check_updater();
if($updates !== false){
    if( !isset($updates['response']) ||
        (empty($updates['response']))  ||
        ($updates['response'] == $updates['local'])
    ){
    }else{
//		echo " local_version='".$updates['local']."' new_version='".$updates['response']."'";
        echo "<center><h2><a href='http://sourceforge.jp/projects/xoops-modules/' target='_blank'>"._AM_SIMPLEBLOG_ADMIN_NEW_VERSION.'('.$updates['response'].")</a></h2></center><br>\n";
    }
}
*/
?>
<table width='100%' border='0' cellspacing='1' class='outer'>
<form action="index.php" method="post">
	    <tr><td colspan="2"><h4><?php echo _AM_SIMPLEBLOG_ADMIN_CREATE; ?></h4></td></tr>
	    <tr class="even">
			<td><?php echo _AM_SIMPLEBLOG_ADMIN_UID; ?>(<a href="userList.php" target="_blank"><?php echo _AM_SIMPLEBLOG_USER_LIST; ?>)</td>
			<td><input type="text" name="uid">
		</tr>
		<tr class="odd">
			<td><?php echo _AM_SIMPLEBLOG_ADMIN_BLOG_TITLE; ?></td>
			<td><input type='text' name='title'>
		</tr>
		<tr class="even">
			<td><?php echo _AM_SIMPLEBLOG_ADMIN_PERMISSION ?></td>
			<td><SELECT name=permission>;
				<option value=3><?php echo _AM_SIMPLEBLOG_ADMIN_PERMISSION3; ?></option>
				<option value=2><?php echo _AM_SIMPLEBLOG_ADMIN_PERMISSION2; ?></option>
				<option value=1><?php echo _AM_SIMPLEBLOG_ADMIN_PERMISSION1; ?></option>
				<option value=0><?php echo _AM_SIMPLEBLOG_ADMIN_PERMISSION0; ?></option>
				</select>
			</td>
		</tr>
		<tr  class="odd">
			<td colspan="2" align="center">
				<input type=submit name=create value=create></form><br>
			
<?php
if (isset($_POST['edit']) ||
    isset($_POST['create']) ||
    isset($_POST['delete']) ||
    isset($_POST['reject'])) {
    if (!XoopsSecurity::checkReferer()) {
        redirect_header(XOOPS_URL . '/modules/simpleblog/', 2, 'Referer Check Failed');

        exit();
    }
}

if (isset($_POST['edit'])) {
    $targetUid = isset($_POST['uid']) ? (int)$_POST['uid'] : 0;

    $permission = isset($_POST['permission']) ? (int)$_POST['permission'] : -1;

    $title = isset($_POST['title']) ? ($_POST['title']) : '';

    if ($targetUid > 0) {
        if ($permission >= 0) {
            $blog = new SimpleBlog($targetUid);

            $blog->setBlogInfo($permission, $title);
        }
    }
} elseif (isset($_POST['create'])) {
    $targetUid = isset($_POST['uid']) ? (int)$_POST['uid'] : 0;

    $permission = isset($_POST['permission']) ? (int)$_POST['permission'] : -1;

    $title = isset($_POST['title']) ? ($_POST['title']) : '';

    if ($targetUid > 0) {
        if ($permission >= 0) {
            $blog = new SimpleBlog($targetUid);

            if ($blog->createNewBlogUser($permission, $title)) {
                echo '<font color="red">success: create blog uid=' . $targetUid . '</font>';
            } else {
                echo '<font color="red">failed: create blog uid=' . $targetUid . '</font>';
            }
        }
    }
} elseif (isset($_POST['delete'])) {
    $targetUid = isset($_POST['uid']) ? (int)$_POST['uid'] : 0;

    if ($targetUid > 0) {
        $blog = new SimpleBlog($targetUid);

        $blog->deleteAll();
    }
} elseif (isset($_POST['reject'])) {
    $targetUid = isset($_POST['uid']) ? (int)$_POST['uid'] : 0;

    if ($targetUid > 0) {
        $blog = new SimpleBlog($targetUid);

        $blog->deleteApplication($targetUid);
    }
}
?>
</td></tr></table><br>

<?php
$appList = SimpleBlog::getAllApplication();
if (count($appList) > 0) {
    ?>
<table width='100%' border='0' cellspacing='1' class='outer'>
<tr><d cospan="5"><h4><?php echo _AM_SIMPLEBLOG_APPLICATED_USER; ?></h4></td></tr>
		<tr class="head">
			<th>uid</th>
			<th>title</th>
			<th>permission</th>
			<th>create date</th>
			<th>edit</th>
		</tr>
<?php foreach ($appList as $app) {
        $opt[0] = $opt[1] = $opt[2] = $opt[3] = '';

        $opt[$app['permission']] = ' SELECTED'; ?>
		<tr><form method="post" action="index.php">
			<td><?php echo $app['uid']; ?></td>
			<td><input type="text" name="title" value="<?php echo $app['title']; ?>"></td>
			<td>
				<select name=permission>';
					<option value='3'<?php echo $opt[3]; ?>><?php echo _AM_SIMPLEBLOG_ADMIN_PERMISSION3; ?></option>
					<option value='2'<?php echo $opt[2]; ?>><?php echo _AM_SIMPLEBLOG_ADMIN_PERMISSION2; ?></option>
					<option value='1'<?php echo $opt[1]; ?>><?php echo _AM_SIMPLEBLOG_ADMIN_PERMISSION1; ?></option>
					<option value='0'<?php echo $opt[0]; ?>><?php echo _AM_SIMPLEBLOG_ADMIN_PERMISSION0; ?></option>
				</select></td>
			<td><?php echo $app['create_date']; ?></td>
			<td>
				
					<input type="submit" name="create" value="<?php echo _AM_SIMPLEBLOG_ALLOW_APPLICATION; ?>"> 
					<input type="submit" name="reject" value="<?php echo _AM_SIMPLEBLOG_REJECT_APPLICATION; ?>">
					<input type=hidden name=uid value='<?php echo $app['uid'] ?>'>
					<input type=hidden name=application value='application'>
			</td>
		</form></tr>
<?php
    } ?>
	</table><br><?php
} ?>

<table width='100%' border='0' cellspacing='1' class='outer'>
	<tr><td class="odd" colspan="6"><h4><?php echo _AM_SIMPLEBLOG_ADMIN_EDIT; ?></h4></td></tr>
	<tr class='bg1' align="left">
		<th><?php echo _AM_SIMPLEBLOG_ADMIN_UID; ?></th>
		<th><?php echo _AM_SIMPLEBLOG_ADMIN_NAME; ?></th>
		<th><?php echo _AM_SIMPLEBLOG_ADMIN_BLOG_TITLE; ?></th>
		<th><?php echo _AM_SIMPLEBLOG_ADMIN_PERMISSION; ?></th>
		<th><?php echo _AM_SIMPLEBLOG_ADMIN_LASTUPDATE; ?></th>
		<th><?php echo _AM_SIMPLEBLOG_ADMIN_OPERATION; ?></th>
	</tr>
<?php
    $result = $xoopsDB->query('SELECT uid, blog_permission, DATE_FORMAT(last_update, \'%Y-%m-%d\') last_update, title FROM ' . SIMPLEBLOG_TABLE_INFO);
    $i = 0;
    $users = [];
    while (list($uid, $permission, $lastUpdate, $blogTitle) = $xoopsDB->fetchRow($result)) {
        $users[$i]['uid'] = $uid;

        $users[$i]['permission'] = $permission;

        $users[$i]['lastUpdate'] = $lastUpdate;

        $users[$i]['title'] = $blogTitle;

        $i++;
    }
    if (count($users) > 0) {
        $userHander = new XoopsUserHandler($xoopsDB);

        foreach ($users as $user) {
            $u = $userHander->get($user['uid']);

            if (is_object($u)) {
                $user['uname'] = $u->uname();
            } else {
                $user['uname'] = 'deleted';
            }

            $opt[0] = $opt[1] = $opt[2] = $opt[3] = '';

            $opt[$user['permission']] = ' SELECTED'; ?>
		<tr><form method="post" action="index.php">
			<td><?php echo $user['uid']; ?><input type=hidden name=uid value='<?php echo $user['uid'] ?>'></td>
			<td><?php echo htmlspecialchars($user['uname'], ENT_QUOTES | ENT_HTML5); ?></td>
			<td><input type="text" name="title" value="<?php echo htmlspecialchars($user['title'], ENT_QUOTES | ENT_HTML5); ?>"></td>
			<td>
				<select name=permission>';
					<option value='3'<?php echo $opt[3]; ?>><?php echo _AM_SIMPLEBLOG_ADMIN_PERMISSION3; ?></option>
					<option value='2'<?php echo $opt[2]; ?>><?php echo _AM_SIMPLEBLOG_ADMIN_PERMISSION2; ?></option>
					<option value='1'<?php echo $opt[1]; ?>><?php echo _AM_SIMPLEBLOG_ADMIN_PERMISSION1; ?></option>
					<option value='0'<?php echo $opt[0]; ?>><?php echo _AM_SIMPLEBLOG_ADMIN_PERMISSION0; ?></option>
				</select>
			</td>
			<td><?php echo $user['lastUpdate']; ?></td>
			<td><input type=submit name=edit value=edit> <input type=submit name=delete value=delete></td>
			</form>
		</tr><?php
        }
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
