﻿<?php
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

if (!defined('XOOPS_ROOT_PATH')) {
    die('XOOPS root path not defined');
}

/**
 * Class xmarticle_category
 */
class xmarticle_category extends XoopsObject
{
    // constructor
    /**
     * xmarticle_category constructor.
     */
    public function __construct()
    {
        $this->initVar('category_id', XOBJ_DTYPE_INT, null, false, 11);
        $this->initVar('category_name', XOBJ_DTYPE_TXTBOX, null, false);
        $this->initVar('category_reference', XOBJ_DTYPE_TXTBOX, null, false, 11);
        $this->initVar('category_description', XOBJ_DTYPE_TXTAREA, null, false);
        // use html
        $this->initVar('dohtml', XOBJ_DTYPE_INT, 1, false);        
        $this->initVar('category_logo', XOBJ_DTYPE_TXTBOX, null, false);
        $this->initVar('category_weight', XOBJ_DTYPE_INT, null, false, 11);
        $this->initVar('category_status', XOBJ_DTYPE_INT, null, false, 1);
    }

    /**
     * @return mixed
     */
    public function get_new_enreg()
    {
        global $xoopsDB;
        $new_enreg = $xoopsDB->getInsertId();
        return $new_enreg;
    }
    
    /**
     * @return mixed
     */
    public function saveCategory($categoryHandler, $action = false)
    {
        if ($action === false) {
            $action = $_SERVER['REQUEST_URI'];
        }
        $error_message = '';        
        // test error
        if ((int)$_REQUEST['category_weight'] == 0 && $_REQUEST['category_weight'] != '0') {
            $error_message .= _MA_XMARTICLE_ERROR_WEIGHT . '<br>';
            $this->setVar('category_weight', 0);
        }        
        //logo
        $uploadirectory = '/xmarticle/images/category';
        if ($_FILES['category_logo']['error'] != UPLOAD_ERR_NO_FILE) {
            include_once XOOPS_ROOT_PATH . '/class/uploader.php';
            $uploader_category_img = new XoopsMediaUploader(XOOPS_UPLOAD_PATH . $uploadirectory, array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png', 'image/png'), $upload_size, null, null);
            if ($uploader_category_img->fetchMedia('category_logo')) {
                $uploader_category_img->setPrefix('category_');
                if (!$uploader_category_img->upload()) {
                    $error_message .= $uploader_category_img->getErrors() . '<br />';
                } else {
                    $this->setVar('category_logo', $uploader_category_img->getSavedFileName());
                }
            } else {
                $error_message .= $uploader_category_img->getErrors();
            }
        } else {
            $this->setVar('category_logo', Xmf\Request::getString('category_logo', ''));
        }
        $this->setVar('category_name', Xmf\Request::getString('category_name', ''));
        $this->setVar('category_reference',  Xmf\Request::getString('category_reference', ''));
        $this->setVar('category_description',  Xmf\Request::getText('category_description', ''));        
        $this->setVar('category_status', Xmf\Request::getInt('category_status', 1));
        if ($error_message == '') {
            $this->setVar('category_weight', Xmf\Request::getInt('category_weight', 0));
            if ($categoryHandler->insert($this)) {
                redirect_header($action, 2, _MA_XMARTICLE);
            } else {
                $error_message =  $this->getHtmlErrors();
            }
        }
        return $error_message;
    }

    /**
     * @param bool $action
     * @return XoopsThemeForm
     */
    public function getForm($action = false)
    {
        $upload_size = 500000;
        //global $xoopsModuleConfig;
        $helper = \Xmf\Module\Helper::getHelper('xmarticle');
        if ($action === false) {
            $action = $_SERVER['REQUEST_URI'];
        }
        include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
        
        //form title
        $title = $this->isNew() ? sprintf(_MA_XMARTICLE_ADD) : sprintf(_MA_XMARTICLE_EDIT);
        
        $form = new XoopsThemeForm($title, 'form', $action, 'post', true);
        $form->setExtra('enctype="multipart/form-data"');
        
        if (!$this->isNew()) {
            $form->addElement(new XoopsFormHidden('category_id', $this->getVar('category_id')));
            $status = $this->getVar('category_status');
            $weight = $this->getVar('category_weight');
        } else {
            $status = 1;
            $weight = 0;
        }

        // title
        $form->addElement(new XoopsFormText(_MA_XMARTICLE_CATEGORY_NAME, 'category_name', 50, 255, $this->getVar('category_name')), true);
        
        // reference
        $form->addElement(new XoopsFormText(_MA_XMARTICLE_CATEGORY_REFERENCE, 'category_reference', 20, 255, $this->getVar('category_reference')), true);

        // description
        $editor_configs           =array();
        $editor_configs['name']   = 'category_description';
        $editor_configs['value']  = $this->getVar('category_description', 'e');
        $editor_configs['rows']   = 20;
        $editor_configs['cols']   = 160;
        $editor_configs['width']  = '100%';
        $editor_configs['height'] = '400px';
        $editor_configs['editor'] = $helper->getConfig('admin_editor', 'Plain Text');
        $form->addElement(new XoopsFormEditor(_MA_XMARTICLE_CATEGORY_DESC, 'category_description', $editor_configs), false);
        // logo
        $blank_img = $this->getVar('category_logo') ? $this->getVar('category_logo') : 'blank.gif';
        $uploadirectory='/uploads/xmarticle/images/category';
        $imgtray_img     = new XoopsFormElementTray(_MA_XMARTICLE_CATEGORY_LOGOFILE  . '<br /><br />' . sprintf(_MA_XMARTICLE_CATEGORY_UPLOADSIZE, $upload_size/1000), '<br />');
        $imgpath_img     = sprintf(_MA_XMARTICLE_CATEGORY_FORMPATH, $uploadirectory);
        $imageselect_img = new XoopsFormSelect($imgpath_img, 'category_logo', $blank_img);
        $image_array_img = XoopsLists::getImgListAsArray(XOOPS_ROOT_PATH . $uploadirectory);
        $imageselect_img->addOption("$blank_img", $blank_img);
        foreach ($image_array_img as $image_img) {
            $imageselect_img->addOption("$image_img", $image_img);
        }
        $imageselect_img->setExtra("onchange='showImgSelected(\"image_img2\", \"category_logo\", \"" . $uploadirectory . "\", \"\", \"" . XOOPS_URL . "\")'");
        $imgtray_img->addElement($imageselect_img, false);
        $imgtray_img->addElement(new XoopsFormLabel('', "<br /><img src='" . XOOPS_URL . '/' . $uploadirectory . '/' . $blank_img . "' name='image_img2' id='image_img2' alt='' />"));
        $fileseltray_img = new XoopsFormElementTray('<br />', '<br /><br />');
        $fileseltray_img->addElement(new XoopsFormFile(_MA_XMARTICLE_CATEGORY_UPLOAD, 'category_logo', $upload_size), false);
        $fileseltray_img->addElement(new XoopsFormLabel(''), false);
        $imgtray_img->addElement($fileseltray_img);
        $form->addElement($imgtray_img);
        // weight
        $form->addElement(new XoopsFormText(_MA_XMARTICLE_CATEGORY_WEIGHT, 'category_weight', 5, 5, $weight), true);

        // status
        $form_status = new XoopsFormRadio(_MA_XMARTICLE_STATUS, 'category_status', $status);
        $options = array(1 => _MA_XMARTICLE_STATUS_A, 0 =>_MA_XMARTICLE_STATUS_NA,);
        $form_status->addOptionArray($options);
        $form->addElement($form_status);

        $form->addElement(new XoopsFormHidden('op', 'save'));
        // submitt
        $form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));

        return $form;
    }
}

/**
 * Class xmarticlexmarticle_categoryHandler
 */
class xmarticlexmarticle_categoryHandler extends XoopsPersistableObjectHandler
{
    /**
     * xmarticlexmarticle_categoryHandler constructor.
     * @param null|XoopsDatabase $db
     */
    public function __construct(&$db)
    {
        parent::__construct($db, 'xmarticle_category', 'xmarticle_category', 'category_id', 'category_name');
    }
}