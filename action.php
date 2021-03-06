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
$GLOBALS['xoopsOption']['template_main'] = 'xmarticle_action.tpl';
include_once XOOPS_ROOT_PATH.'/header.php';

$xoTheme->addStylesheet( XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/assets/css/styles.css', null );

$op = Request::getCmd('op', '');

if ($op == 'clone' || $op == 'edit' || $op == 'del' || $op == 'add' || $op == 'loadarticle' || $op == 'save' ){
    switch ($op) {        
        // Add
        case 'add':      
            // Form
            // permission to submitt
            $permHelper->checkPermissionRedirect('xmarticle_other', 4, 'index.php', 2, _NOPERM);
            $obj  = $articleHandler->create();
            $form = $obj->getFormCategory();
            $xoopsTpl->assign('form', $form->render());
            break;

        // Loadtype
        case 'loadarticle': 
            // permission to submitt
            $permHelper->checkPermissionRedirect('xmarticle_other', 4, 'index.php', 2, _NOPERM);
            $article_cid = Request::getInt('article_cid', 0);
            if ($article_cid == 0) {
                $xoopsTpl->assign('error_message', _MA_XMARTICLE_ERROR_NOCATEGORY);
            } else {
                $obj  = $articleHandler->create();
                $form = $obj->getForm($article_cid);
                $xoopsTpl->assign('form', $form->render());
            }
            break;
            
        // Edit
        case 'edit':     
            // Form
            $article_id = Request::getInt('article_id', 0);
            if ($article_id == 0) {
                $xoopsTpl->assign('error_message', _MA_XMARTICLE_ERROR_NOARTICLE);
            } else {
                $obj = $articleHandler->get($article_id);
                $form = $obj->getForm();
                $xoopsTpl->assign('form', $form->render()); 
            }

            break;
        
        // Clone
        case 'clone':
            $article_id = Request::getInt('article_id', 0);
            if ($article_id == 0) {
                $xoopsTpl->assign('error_message', _MA_XMARTICLE_ERROR_NOARTICLE);
            } else {
                $cloneobj = XmarticleUtility::cloneArticle($article_id);
                $form = $cloneobj->getForm($cloneobj->getVar('article_cid'), $article_id, 'action.php');
                $xoopsTpl->assign('form', $form->render());
            }
            break;
            
        // Save
        case 'save':
            // permission to submitt
            $permHelper->checkPermissionRedirect('xmarticle_other', 4, 'index.php', 2, _NOPERM);
            if (!$GLOBALS['xoopsSecurity']->check()) {
                redirect_header('index.php', 3, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
            }
            $article_id = Request::getInt('article_id', 0);
            if ($article_id == 0) {
                $obj = $articleHandler->create();
            } else {
                $obj = $articleHandler->get($article_id);
            }
            $error_message = $obj->savearticle($articleHandler, 'viewarticle.php');
            if ($error_message != ''){
                $xoopsTpl->assign('error_message', $error_message);
                $form = $obj->getForm();
                $xoopsTpl->assign('form', $form->render());
            }            
            break;
            
        // del
        case 'del':
            // permission to del
            $permHelper->checkPermissionRedirect('xmarticle_other', 16, 'index.php', 2, _NOPERM);
            $article_id = Request::getInt('article_id', 0);
            if ($article_id == 0) {
                $xoopsTpl->assign('error_message', _MA_XMARTICLE_ERROR_NOARTICLE);
            } else {
                $surdel = Request::getBool('surdel', false);
                $obj  = $articleHandler->get($article_id);
                if ($surdel === true) {
                    if (!$GLOBALS['xoopsSecurity']->check()) {
                        redirect_header('index.php', 3, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
                    }
                    if ($articleHandler->delete($obj)) {
                        //Del logo
                        if ($obj->getVar('article_logo') != 'blank.gif') {
                            $urlfile = $path_logo_article . $obj->getVar('article_logo');
                            if (is_file($urlfile)) {
                                chmod($urlfile, 0777);
                                unlink($urlfile);
                            }
                        }
                        //Del fielddata
                        XmarticleUtility::delFilddataArticle($article_id);
                        redirect_header('index.php', 2, _MA_XMARTICLE_REDIRECT_SAVE);
                    } else {
                        $xoopsTpl->assign('error_message', $obj->getHtmlErrors());
                    }
                } else {
                    $article_img = $obj->getVar('article_logo') ?: 'blank.gif';
                    xoops_confirm(array('surdel' => true, 'article_id' => $article_id, 'op' => 'del'), $_SERVER['REQUEST_URI'], 
                                        sprintf(_MA_XMARTICLE_ARTICLE_SUREDEL, $obj->getVar('article_name')) . '<br \>
                                        <img src="' . $url_logo_article . $article_img . '" title="' . 
                                        $obj->getVar('article_name') . '" /><br \>');
                }
            }
            break;
    }
} else {
    redirect_header('index.php', 2, _NOPERM);
    exit();
}
include XOOPS_ROOT_PATH.'/footer.php';