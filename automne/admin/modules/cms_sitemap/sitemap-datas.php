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
//
// $Id: search.php,v 1.8 2010/03/08 16:42:07 sebastien Exp $

/**
  * PHP page : Load cms_i18n items datas
  * Used accross an Ajax request.
  * Return formated items infos in JSON format
  *
  * @package Automne
  * @subpackage admin
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

require_once(dirname(__FILE__).'/../../../../cms_rc_admin.php');

//load interface instance
$view = CMS_view::getInstance();
//set default display mode for this page
$view->setDisplayMode(CMS_view::SHOW_JSON);
//This file is an admin file. Interface must be secure
$view->setSecure();

//Specific messages
define('MESSAGE_PAGE_IN_PROGRESS', 30);

//get search vars
$dir = sensitiveIO::request('dir');

$itemsDatas = array();
$itemsDatas['results'] = array();

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

//get messages
$resultCount = 0;
$sitemaps = CMS_sitemap::getAll();

// Vars for lists output purpose and pages display, see further
$itemsDatas['total'] = sizeof($sitemaps);

//loop on results items
foreach($sitemaps as $sitemap) {
	$website = CMS_websitesCatalog::getByID($sitemap->getValue('site'));
	$filename = $sitemap->getFileName();
	$file = !file_exists(PATH_REALROOT_FS.'/'.$filename) ? $cms_language->getMessage(MESSAGE_PAGE_IN_PROGRESS, false, MOD_CMS_SITEMAP_CODENAME) : '<a href="'.PATH_REALROOT_WR.'/'.$filename.'" target="_blank">'.PATH_REALROOT_WR.'/'.$filename.'</a>';
	
	$itemsDatas['results'][] = array(
		'code'			=> $sitemap->getValue('codename'),
		'website'		=> ($website ? $website->getLabel() : 'error ...'),
		'file'			=> $file,
	);
}

$view->setContent($itemsDatas);
$view->show();
?>