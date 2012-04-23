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
// | Author: Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>	  |
// +----------------------------------------------------------------------+

/**
  * PHP controler : Receive actions on sitemap pages
  * Used accross an Ajax request
  * 
  * @package Automne
  * @subpackage cms_sitemap
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

require_once(dirname(__FILE__).'/../../../../cms_rc_admin.php');

//load interface instance
$view = CMS_view::getInstance();
//set default display mode for this page
$view->setDisplayMode(CMS_view::SHOW_RAW);
//This file is an admin file. Interface must be secure
$view->setSecure();

//Controler vars
$pageId = sensitiveIO::request('page', 'io::isPositiveInteger');
$includedFather = sensitiveIO::request('includedFather') ? true : false;
$subtreeFather = sensitiveIO::request('subtreeFather') ? true : false;
$priorityFather = sensitiveIO::request('priorityFather') ? true : false;
$frequencyFather = sensitiveIO::request('frequencyFather') ? true : false;

$included = sensitiveIO::request('included') ? 1 : -1;
$subtree = sensitiveIO::request('subtree') ? 1 : -1;
$priority = sensitiveIO::request('priority');
$frequency = sensitiveIO::request('frequency');

//Specific module message


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

//load sitemap page object
$page = CMS_sitemap_page::getPageByID($pageId);

$page->setValue('included', ($includedFather ? 0 : $included));
$page->setValue('subtree', ($subtreeFather ? 0 : $subtree));
$page->setValue('priority', ($priorityFather ? 0 : $priority));
$page->setValue('frequency', ($frequencyFather ? '' : $frequency));

$page->writeToPersistence();

$cms_message = $cms_language->getMessage(MESSAGE_ACTION_OPERATION_DONE);

//set user message if any
if ($cms_message) {
	$view->setActionMessage($cms_message);
}
$view->show();
?>