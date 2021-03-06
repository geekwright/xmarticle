<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * xmarticle module
 *
 * @copyright       XOOPS Project (http://xoops.org)
 * @license         GNU GPL 2 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @author          Mage Gregory (AKA Mage)
 */
use \Xmf\Request;

include_once __DIR__ . '/header.php';
$GLOBALS['xoopsOption']['template_main'] = 'xmarticle_viewarticle.tpl';
include_once XOOPS_ROOT_PATH.'/header.php';

$xoTheme->addStylesheet( XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/assets/css/styles.css', null );

$category_id = Request::getInt('category_id', 0);
$article_id = Request::getInt('article_id', 0);

if ($category_id == 0) {
    redirect_header('index.php', 2, _MA_XMARTICLE_ERROR_NOCATEGORY);
    exit();
}
// permission to view
$permHelper->checkPermissionRedirect('xmarticle_view', $category_id, 'index.php', 2, _NOPERM);

if ($article_id == 0) {
    redirect_header('index.php', 2, _MA_XMARTICLE_ERROR_NOARTICLE);
    exit();
}

$category = $categoryHandler->get($category_id);
$article = $articleHandler->get($article_id);

if (count($category) == 0) {
    redirect_header('index.php', 2, _MA_XMARTICLE_ERROR_NOCATEGORY);
    exit();
}

if ($category->getVar('category_status') == 0 || $article->getVar('article_status') == 0) {
    redirect_header('index.php', 2, _MA_XMARTICLE_ERROR_NACTIVE);
    exit();
}

if (count($article_id) == 0) {
    redirect_header('index.php', 2, _MA_XMARTICLE_ERROR_NOARTICLE);
    exit();
}
// Category
$xoopsTpl->assign('category_name', $category->getVar('category_name'));
$xoopsTpl->assign('category_reference', $category->getVar('category_reference'));
$xoopsTpl->assign('category_id', $category_id);

// Article
$xoopsTpl->assign('article_id', $article_id);
$xoopsTpl->assign('name', $article->getVar('article_name'));
$xoopsTpl->assign('description', $article->getVar('article_description'));
$xoopsTpl->assign('reference', $article->getVar('article_reference'));
$xoopsTpl->assign('date', formatTimestamp($article->getVar('article_date'), 's'));
if ($article->getVar('article_mdate') != 0){
	$xoopsTpl->assign('mdate', formatTimestamp($article->getVar('article_mdate'), 's'));
}
$xoopsTpl->assign('author', XoopsUser::getUnameFromId($article->getVar('article_userid')));
$article_img = $article->getVar('article_logo') ?: 'blank.gif';
$xoopsTpl->assign('logo', $url_logo_article .  $article_img);

// Field
$field_arr = XmarticleUtility::getArticleFields($category->getVar('category_fields'), $article_id);
$field_count = count($field_arr);
if ($field_count >0 ){
	$count = 1;
	$count_row = 1;
	foreach (array_keys($field_arr) as $i) {
		$field['name']            = $field_arr[$i][0];
		$field['description']     = $field_arr[$i][1];
		$field['value']           = $field_arr[$i][2];
		$field['count']           = $count;
		if ($count_row == $count) {
			$field['row'] = true;
			$count_row = $count_row + 2;
		} else {
			$field['row'] = false;
		}
		if ($count == $field_count) {
			$field['end'] = true;
		} else {
			$field['end'] = false;
		}
		$xoopsTpl->append_by_ref('field', $field);
		unset($field);
		$count++;
	}
}
//SEO
// pagetitle
$xoopsTpl->assign('xoops_pagetitle', \Xmf\Metagen::generateSeoTitle($article->getVar('article_name') . '-' . $xoopsModule->name()));
//description   
$xoTheme->addMeta('meta', 'description', \Xmf\Metagen::generateDescription($article->getVar('article_description'), 30));
//keywords
$keywords = \Xmf\Metagen::generateKeywords($article->getVar('article_description'), 10);    
$xoTheme->addMeta('meta', 'keywords', implode(', ', $keywords));

include XOOPS_ROOT_PATH.'/footer.php';
