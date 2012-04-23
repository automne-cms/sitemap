<?php
// +----------------------------------------------------------------------+
// | Automne (TM)														  |
// +----------------------------------------------------------------------+
// | Copyright (c) 2000-2010 WS Interactive								  |
// +----------------------------------------------------------------------+
// | Automne is subject to version 2.0 or above of the GPL license.		  |
// | The license text is bundled with this package in the file			  |
// | LICENSE-GPL, and is available through the world-wide-web at		  |
// | http://www.gnu.org/copyleft/gpl.html.								  |
// +----------------------------------------------------------------------+
// | Author: Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>      |
// +----------------------------------------------------------------------+

/**
  * cms_sitemap controler
  * Used accross an Ajax request. Make actions on cms_sitemap items
  *
  * @package Automne
  * @subpackage cms_sitemap
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

require_once(dirname(__FILE__).'/../../../../cms_rc_admin.php');

define("MESSAGE_ERROR_MODULE_RIGHTS",570);
define("MESSAGE_ERROR_SAVING",1701);

//load interface instance
$view = CMS_view::getInstance();
//set default display mode for this page
$view->setDisplayMode(CMS_view::SHOW_JSON);
//This file is an admin file. Interface must be secure
$view->setSecure();

//Controler vars
$action = sensitiveIO::request('action', array('save', 'delete', 'generate'));
$code = sensitiveIO::request('code');
$oldcode = sensitiveIO::request('oldcode');
$website = sensitiveIO::request('website', 'io::isPositiveInteger');
$definition = sensitiveIO::request('definition');
$namespaces = sensitiveIO::request('namespaces');

//set default content
$content = array('success' => false);

//load module
$codename = MOD_CMS_SITEMAP_CODENAME;
$module = CMS_modulesCatalog::getByCodename($codename);
if (!$module) {
	CMS_grandFather::raiseError('Unknown module or module for codename : '.$codename);
	$view->show();
}
//CHECKS user has module clearance
if (!$cms_user->hasModuleClearance($codename, CLEARANCE_MODULE_EDIT)) {
	CMS_grandFather::raiseError('User has no rights on module : '.$codename);
	$view->setActionMessage($cms_language->getmessage(MESSAGE_ERROR_MODULE_RIGHTS, array($module->getLabel($cms_language))));
	$view->show();
}

if (!$action) {
	$view->setContent($content);
	CMS_grandFather::raiseError('Unknown action ... '.$action);
	$view->show();
}

$cms_message = '';
switch ($action) {
	case 'delete':
		$sitemap = CMS_sitemap::getByCodename($code);
		if (!$sitemap || $sitemap->hasError()) {
			CMS_grandFather::raiseError('Unknown sitemap code : '.$code);
			$view->setContent($content);
			$view->show();
		}
		if ($sitemap->destroy()) {
			//regenerate sitemaps index
			$module->generateIndex();
			$content = array('success' => true);
			$cms_message = $cms_language->getMessage(MESSAGE_ACTION_OPERATION_DONE);
		} else {
			$view->setContent($content);
			$view->show();
		}
	break;
	case 'generate':
		set_time_limit(300);
		$sitemap = CMS_sitemap::getByCodename($code);
		if (!$sitemap || $sitemap->hasError()) {
			CMS_grandFather::raiseError('Unknown sitemap code : '.$code);
			$view->setContent($content);
			$view->show();
		}
		if ($sitemap->generateFile()) {
			//regenerate sitemaps index
			$module->generateIndex();
			$content = array('success' => true);
			$cms_message = $cms_language->getMessage(MESSAGE_ACTION_OPERATION_DONE);
		} else {
			$view->setContent($content);
			$view->show();
		}
	break;
	case 'save':
		//load item messages
		if (!$code) {
			$view->setActionMessage($cms_language->getJSMessage(MESSAGE_ERROR_SAVING));
			$view->setContent($content);
			$view->show();
		}
		if ($oldcode) {
			if ($oldcode != $code) {
				//change codename : check if new code does not already exists
				if (CMS_sitemap::exists($code)) {
					$view->setActionMessage($cms_language->getJSMessage(MESSAGE_ERROR_SAVING));
					$view->setContent($content);
					$view->show();
				}
				$sitemap = CMS_sitemap::getByCodename($oldcode);
				$sitemap->setValue('codename', $code);
			} else {
				$sitemap = CMS_sitemap::getByCodename($code);
			}
			if (!$sitemap || $sitemap->hasError()) {
				CMS_grandFather::raiseError('Unknown sitemap code : '.$code);
				$view->setContent($content);
				$view->show();
			}
		} else {
			//create sitemap : check if code does not already exists
			if (CMS_sitemap::exists($code)) {
				$view->setActionMessage($cms_language->getJSMessage(MESSAGE_ERROR_SAVING));
				$view->setContent($content);
				$view->show();
			}
			$sitemap = new CMS_sitemap();
			$sitemap->setValue('codename', $code);
		}
		
		$sitemap->setValue('site', $website);
		$sitemap->setValue('namespaces', $namespaces);
		//set definition
		$return = $sitemap->setValue('definition', $definition);
		if ($return !== true) {
			$view->setActionMessage($return);
			$view->setContent($content);
			$view->show();
		}
		
		if ($sitemap->writeToPersistence()) {
			$content = array('success' => true);
			$cms_message = $cms_language->getMessage(MESSAGE_ACTION_OPERATION_DONE);
		} else {
			$view->setContent($content);
			$view->show();
		}
	break;
}
//set user message if any
if ($cms_message) {
	$view->setActionMessage($cms_message);
}
//beware, here we add content (not set) because object saving can add his content to (uploaded file infos updated)
$view->addContent($content);
$view->show();
?>