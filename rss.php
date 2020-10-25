<?php
// $Id: rss.php,v 1.1 2006/03/20 16:18:58 mikhail Exp $

require dirname(__DIR__, 2) . '/mainfile.php';
if (
    !defined('XOOPS_ROOT_PATH') ||
    !defined('XOOPS_CACHE_PATH') ||
    !is_file(XOOPS_ROOT_PATH . '/class/template.php') ||
    !is_file(XOOPS_ROOT_PATH . '/modules/simpleblog/simpleblog.php')
) {
    exit();
}
require_once XOOPS_ROOT_PATH . '/class/template.php';
require_once XOOPS_ROOT_PATH . '/modules/simpleblog/simpleblog.php';

if (function_exists('mb_http_output')) {
    mb_http_output('pass');
}

header('Content-Type: text/xml; charset=utf-8');

$params = SimpleBlogUtils::getDateFromHttpParams();

function to_urf8($text)
{
    // xoops_convert_encoding($text);

    return SimpleBlogUtils::convert_encoding($text, _CHARSET, 'utf-8');
}
if ($params['uid'] && $params['uid'] > 0) {
    $blog = new SimpleBlog($params['uid']);

    $r = [];

    if ($blog->canRead()) {
        $r = $blog->getBlogData();
    }

    if (empty($r)) {
        exit();
    }

    $i = 0;

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
	<rdf:RDF 
		xmlns="http://purl.org/rss/1.0/" 
		xmlns:dc="http://purl.org/dc/elements/1.1/" 
		xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
		xmlns:admin="http://webns.net/mvcb/"
		xml:lang="ja">
	  <channel rdf:about="<?php echo SimpleBlogUtils::createRssURL($params['uid']); ?>">
	    <title><?php echo to_urf8(htmlspecialchars($blog->getTitle(), ENT_QUOTES)); ?></title>
	    <link><?php echo SimpleBlogUtils::createUrl($params['uid']); ?></link>
	    <description></description>
		<dc:language><?php echo _LANGCODE; ?></dc:language>
		<admin:generatorAgent rdf:resource="http://xoops-modules.sourceforge.jp/">
	    <items>
	      <rdf:Seq>
	<?php foreach ($r['blog'] as $b) {
        if ($i < 10) {
            ?>
	        <rdf:li rdf:resource="<?php echo to_urf8(htmlspecialchars($b['url'], ENT_QUOTES)); ?>">
	<?php
            $i++;
        }
    }

    $i = 0; ?>
	      </rdf:Seq>
	    </items>
	  </channel>
	<?php foreach ($r['blog'] as $b) {
        if ($i < 10) {
            $text = SimpleBlogUtils::remove_html_tags($b['text']);

            $text = (mb_strlen($text) > 200) ? SimpleBlogUtils::mb_strcut($text, 0, 190) . '...' : $text; ?>
	  <item rdf:about="<?php echo to_urf8(htmlspecialchars($b['url'], ENT_QUOTES)); ?>">
	    <title><?php echo to_urf8(htmlspecialchars($b['title'], ENT_QUOTES)); ?></title>
	    <link><?php echo to_urf8(htmlspecialchars($b['url'], ENT_QUOTES)); ?></link>
	    <description><?php echo to_urf8('<![CDATA[' . $text . ']]>'); ?></description>
	    <dc:date><?php echo to_urf8(htmlspecialchars($b['last_update4rss'], ENT_QUOTES)); ?></dc:date> 
	    <dc:creator><?php echo to_urf8(htmlspecialchars($blog->getTargetUname(), ENT_QUOTES)); ?></dc:creator>
	  </item>
	<?php
            $i++;
        }
    } ?>
	</rdf:RDF>
<?php
} else {
        $tpl = new XoopsTpl();

        $tpl->xoops_setTemplateDir(XOOPS_ROOT_PATH . '/cache');

        $tpl->xoops_setCaching(2);

        $tpl->xoops_setCacheTime(3600);

        if (!$tpl->is_cached('db:simpleblog_rss.html')) {
            $blogList = SimpleBlogUtils::get_blog_list();

            if (is_array($blogList)) {
                $tpl->assign('channel_title', to_urf8(htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES)));

                $tpl->assign('channel_link', XOOPS_URL . '/');

                $tpl->assign('channel_desc', to_urf8(htmlspecialchars($xoopsConfig['slogan'], ENT_QUOTES)));

                $tpl->assign('channel_lastbuild', formatTimestamp(time(), 'rss'));

                $tpl->assign('channel_webmaster', $xoopsConfig['adminmail']);

                $tpl->assign('channel_editor', $xoopsConfig['adminmail']);

                $tpl->assign('channel_category', 'News');

                $tpl->assign('channel_generator', XOOPS_VERSION);

                $tpl->assign('channel_language', _LANGCODE);

                $tpl->assign('image_url', XOOPS_URL . '/images/logo.gif');

                $tpl->assign('channel_desc', to_urf8(htmlspecialchars($xoopsModuleConfig['blog_description'], ENT_QUOTES)));

                foreach ($blogList as $blog) {
                    $tpl->append('items', [
                    'title' => to_urf8(htmlspecialchars($blog['title'], ENT_QUOTES)),
                    // 'link' => XOOPS_URL.'/modules/simpleblog/view.php?uid='.$blog['uid'],
                    'link' => $blog['url'],
                    'date' => htmlspecialchars($blog['last_update4rss'], ENT_QUOTES),
                    'uname' => to_urf8(htmlspecialchars($blog['uname'], ENT_QUOTES)),
                ]);
                }

                $tpl->assign('_MD_SIMPLEBLOG_TITLE_SUFFIX', to_urf8(htmlspecialchars(_MD_SIMPLEBLOG_TITLE_SUFFIX, ENT_QUOTES)));

                $tpl->assign('_MD_SIMPLEBLOG_TITLE_PREFIX', to_urf8(htmlspecialchars(_MI_SIMPLEBLOG_TITLE_PREFIX, ENT_QUOTES)));
            }
        }

        $tpl->display('db:simpleblog_rss.html');
    }

?>

