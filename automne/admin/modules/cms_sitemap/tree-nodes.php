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
  * PHP page : Load tree window infos
  * Used accross an Ajax request
  * Return formated tree nodes infos in JSON format
  *
  * @package Automne
  * @subpackage cms_sitemap
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

require_once(dirname(__FILE__).'/../../../../cms_rc_admin.php');

define("MESSAGE_PAGE_REDIRECT", 1625);

//specific messages
define("MESSAGE_PAGE_NOT_PUBLISHED", 46);
define("MESSAGE_PAGE_HAS_REDIRECT", 47);
define("MESSAGE_PAGE_NO_RIGHTS", 48);
define("MESSAGE_PAGE_NOT_INCLUDED", 49);
define("MESSAGE_PAGE_FREQ_ALWAYS", 11);
define("MESSAGE_PAGE_FREQ_HOURLY", 12);
define("MESSAGE_PAGE_FREQ_DAILY", 13);
define("MESSAGE_PAGE_FREQ_WEEKLY", 14);
define("MESSAGE_PAGE_FREQ_MONTHLY", 15);
define("MESSAGE_PAGE_FREQ_YEARLY", 16);
define("MESSAGE_PAGE_FREQ_NEVER", 17);

define("MESSAGE_PAGE_PRIORITY_LABEL", 5);
define("MESSAGE_PAGE_UPDATE_FREQ_LABEL", 9);

//load interface instance
$view = CMS_view::getInstance();
//set default display mode for this page
$view->setDisplayMode(CMS_view::SHOW_JSON);
//This file is an admin file. Interface must be secure
$view->setSecure();

//simple function used to test a value with the string 'false'
function checkFalse($value) {
	return ($value == 'false');
}
function checkNotFalse($value) {
	return ($value !== 'false');
}
$nodeId = (isset($_REQUEST['node']) && io::strpos($_REQUEST['node'], 'page') === 0 && sensitiveIO::isPositiveInteger(io::substr($_REQUEST['node'],4))) ? io::substr($_REQUEST['node'],4) : false;
$rootId = APPLICATION_ROOT_PAGE_ID;
$editable = true;
$onClick = sensitiveIO::request('onClick');
$pageProperty = '';
$currentPage = sensitiveIO::request('currentPage', 'sensitiveIO::isPositiveInteger');
$winId = sensitiveIO::request('winId', '', 'treeWindow');
$showRoot = true;
$maxlevel = 0;

$maxlevelReached = false;
//load node page and siblings
if ($nodeId) {
	$node = CMS_sitemap_page::getPageByID($nodeId);
	if ($node->hasError()) {
		CMS_grandFather::raiseError('Node page has error ...');
		$view->show();
	}
	$siblings = CMS_sitemap_page::getSiblings($node);
} elseif (isset($_REQUEST['node']) && io::strpos($_REQUEST['node'], 'root') === 0) {
	//load website root
	$node = CMS_sitemap_page::getPageByID($rootId);
	//check for users rights
	if ($showRoot && $cms_user->hasPageClearance($node->getID(), CLEARANCE_PAGE_EDIT)) {
		$siblings = array($node);
		unset($node);
	} else {
		$siblings = CMS_sitemap_page::getSiblings($node);
	}
}

//remove unused siblings
foreach ($siblings as $key => $sibling) {
	if (!$cms_user->hasPageClearance($sibling->getID(), CLEARANCE_PAGE_EDIT)) {
		unset($siblings[$key]);
	}
}
//if node is root, then get all orphan tree pages and append them to siblings
if (isset($node) && $node->getID() == $rootId) {
	//get all clearances root pages
	$roots = $cms_user->getEditablePageClearanceRoots();
	foreach ($roots as $pageRootID) {
		if ($pageRootID != APPLICATION_ROOT_PAGE_ID) {
			//get lineage for this clearance root
			$rootLineage = CMS_tree::getLineage(APPLICATION_ROOT_PAGE_ID, $pageRootID, false);
			//go through lineage to check for a break in pages rights
			if (is_array($rootLineage)) {
				$ancestor = array_pop($rootLineage);
				$lastAncestor = '';
				
				while ($rootLineage && $cms_user->hasPageClearance($ancestor, CLEARANCE_PAGE_EDIT)) {
					$lastAncestor = $ancestor;
					$ancestor = array_pop($rootLineage);
				}
				if ($rootLineage && $lastAncestor && !isset($siblings['ancestor'.$lastAncestor])) { //lineage has a break in pages rights so append page to siblings
					$pageRoot = CMS_sitemap_page::getPageByID($lastAncestor);
					if ($pageRoot->hasError()) {
						CMS_grandFather::raiseError('Node page '.$lastAncestor.' has error ...');
					} else {
						$siblings['ancestor'.$lastAncestor] = $pageRoot;
					}
				}
			}
		}
	}
}

//get lineage for current page if any
$currentPageLineage = ($currentPage) ? CMS_tree::getLineage($rootId, $currentPage, false) : array();
if (!is_array($currentPageLineage)) {
	$currentPageLineage =  array();
}


$module = CMS_modulesCatalog::getByCodename(MOD_CMS_SITEMAP_CODENAME);
$userId = $module->getParameters('USER_FOR_GENERATION');
$user = false;
if (io::isPositiveInteger($userId)) {
	$user = CMS_profile_usersCatalog ::getByID($userId);
}
if (!$user) {
	$user = CMS_profile_usersCatalog ::getByID(ANONYMOUS_PROFILEUSER_ID);
}

$frequencies = array(
	'0'			=> '--',
	'always'	=> $cms_language->getJSMessage(MESSAGE_PAGE_FREQ_ALWAYS, false, MOD_CMS_SITEMAP_CODENAME),
	'hourly'	=> $cms_language->getJSMessage(MESSAGE_PAGE_FREQ_HOURLY, false, MOD_CMS_SITEMAP_CODENAME),
	'daily'		=> $cms_language->getJSMessage(MESSAGE_PAGE_FREQ_DAILY, false, MOD_CMS_SITEMAP_CODENAME),
	'weekly'	=> $cms_language->getJSMessage(MESSAGE_PAGE_FREQ_WEEKLY, false, MOD_CMS_SITEMAP_CODENAME),
	'monthly'	=> $cms_language->getJSMessage(MESSAGE_PAGE_FREQ_MONTHLY, false, MOD_CMS_SITEMAP_CODENAME),
	'yearly'	=> $cms_language->getJSMessage(MESSAGE_PAGE_FREQ_YEARLY, false, MOD_CMS_SITEMAP_CODENAME),
	'never'		=> $cms_language->getJSMessage(MESSAGE_PAGE_FREQ_NEVER, false, MOD_CMS_SITEMAP_CODENAME),
);

$nodes = array();
foreach ($siblings as $sibling) {
	if ($cms_user->hasPageClearance($sibling->getID(), CLEARANCE_PAGE_EDIT)) {
		
		$property = '('.$sibling->getID().')';
		$redirectlink = $sibling->getRedirectLink(true);
		if ($redirectlink->hasValidHREF()) {
			if ($redirectlink->getLinkType() == RESOURCE_LINK_TYPE_INTERNAL) {
				$redirectPage = CMS_sitemap_page::getPageByID($redirectlink->getInternalLink());
				if (!$redirectPage->hasError()) {
					$property .= '<small class="atm-help" ext:qtip="'.$cms_language->getMessage(MESSAGE_PAGE_REDIRECT, array('\''.$redirectPage->getTitle(true).'\' ('.$redirectPage->getID().')')).'"> &rArr; '.$redirectPage->getID().'</small>';
				}
			} else {
				$label = $redirectlink->getExternalLink();
				$property .= '<small class="atm-help" ext:qtip="'.$cms_language->getMessage(MESSAGE_PAGE_REDIRECT, array(io::ellipsis($label, '80'))).'"> &rArr; '.io::ellipsis($label, '50').'</small>';
			}
		}
		
		$included = true;
		$qtip = '';
		if ($sibling->getPublication() != RESOURCE_PUBLICATION_PUBLIC) {
			$included = false;
			$qtip = $cms_language->getMessage(MESSAGE_PAGE_NOT_PUBLISHED, false, MOD_CMS_SITEMAP_CODENAME);
		}
		if ($redirectlink->hasValidHREF()) {
			$included = false;
			$qtip = $cms_language->getMessage(MESSAGE_PAGE_HAS_REDIRECT, false, MOD_CMS_SITEMAP_CODENAME);
		}
		if (APPLICATION_ENFORCES_ACCESS_CONTROL && !$user->hasPageClearance($sibling->getID(), CLEARANCE_PAGE_VIEW)) {
			$included = false;
			$qtip = $cms_language->getMessage(MESSAGE_PAGE_NO_RIGHTS, false, MOD_CMS_SITEMAP_CODENAME);
		}
		if ($sibling->getValue('included') != 1) {
			$included = false;
			$qtip = $cms_language->getMessage(MESSAGE_PAGE_NOT_INCLUDED, false, MOD_CMS_SITEMAP_CODENAME);
		}
		$pageTitle = (PAGE_LINK_NAME_IN_TREE) ? $sibling->getLinkTitle() : $sibling->getTitle();
		$text = io::htmlspecialchars($pageTitle).' '.$property;
		
		if ($included) {
			$text = '<span style="color:green;">'.$text.'</span>';
		} else {
			$text = '<span style="color:red;">'.$text.'</span>';
		}
		
		$qtip = $qtip ? $qtip.'<br />' : '';
		$qtip .= $cms_language->getMessage(MESSAGE_PAGE_PRIORITY_LABEL, false, MOD_CMS_SITEMAP_CODENAME).' : '.$sibling->getPriority().'<br />';
		$qtip .= $cms_language->getMessage(MESSAGE_PAGE_UPDATE_FREQ_LABEL, false, MOD_CMS_SITEMAP_CODENAME).' : '.$frequencies[$sibling->getValue('frequency')];
		
		$hasSiblings = CMS_tree::hasSiblings($sibling) ? true : false;
		$editableSibling = $cms_user->hasPageClearance($sibling->getId(), CLEARANCE_PAGE_EDIT);
		
		$nodes[] = array(
			'id'		=>	'page'.$sibling->getID(), 
			'onClick'	=>	($editableSibling ? sprintf($onClick, $sibling->getID()) : ''),
			'text'		=>	$text,
			'status'	=>	$sibling->getStatus()->getHTML(true, $cms_user, MOD_STANDARD_CODENAME, $sibling->getID()),
			'leaf'		=>	!$hasSiblings,
			'qtip'		=>	$qtip,
			'uiProvider'=>	'page',
			'selected'	=>	($sibling->getID() == $currentPage),
			'expanded'	=>	in_array($sibling->getID(), $currentPageLineage),
		);
	}
}

$view->setContent($nodes);
$view->show();
?>