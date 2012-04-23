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
  * Class CMS_module_CMS_sitemap
  *
  * Represent the sitemap module.
  *
  * @package Automne
  * @subpackage cms_sitemap
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

//Polymod Codename
define("MOD_CMS_SITEMAP_CODENAME", "cms_sitemap");

class CMS_module_CMS_sitemap extends CMS_module
{
	const MESSAGE_PAGE_SITEMAP = 2;
	const MESSAGE_PAGE_SITEMAP_DESC = 3;
	const MESSAGE_PAGE_SITEMAP_PAGES_DESC = 4;
	const MESSAGE_PAGE_TREE = 50;
	const MESSAGE_PAGE_TREE_DESC = 45;
	const MESSAGE_TASK_REGENERATE_SITEMAP = 51;
	
	const MESSAGE_PARAM_USER_FOR_GENERATION = 38;
	const MESSAGE_PARAM_USER_FOR_GENERATION_DESC = 39;
	const MESSAGE_PARAM_CREATE_INDEX = 40;
	const MESSAGE_PARAM_CREATE_INDEX_DESC = 41;
	const MESSAGE_PARAM_INDEX_NAME = 42;
	const MESSAGE_PARAM_INDEX_NAME_DESC = 43;
	const MESSAGE_PARAM_INDEX_IN_ROBOTS_TXT = 44;
	
	/**
	  * Module autoload handler
	  *
	  * @param string $classname the classname required for loading
	  * @return string : the file to use for required classname
	  * @access public
	  */
	function load($classname) {
		static $classes;
		if (!isset($classes)) {
			$classes = array(
				'cms_sitemap' 		=> PATH_MODULES_FS.'/'.MOD_CMS_SITEMAP_CODENAME.'/sitemap.php',
				'cms_sitemap_page'	=> PATH_MODULES_FS.'/'.MOD_CMS_SITEMAP_CODENAME.'/page.php',
			);
		}
		$file = '';
		if (isset($classes[io::strtolower($classname)])) {
			$file = $classes[io::strtolower($classname)];
		}
		return $file;
	}
	
	/**
	  * Return a list of objects infos to be displayed in module index according to user privileges
	  *
	  * @return string : HTML scripts infos
	  * @access public
	  */
	function getObjectsInfos($user) {
		$objectsInfos = array();
		$cms_language = $user->getLanguage();
		
		$objectsInfos[] = array(
			'label'			=> $cms_language->getMessage(self::MESSAGE_PAGE_SITEMAP, false, MOD_CMS_SITEMAP_CODENAME),
			'adminLabel'	=> $cms_language->getMessage(self::MESSAGE_PAGE_SITEMAP, false, MOD_CMS_SITEMAP_CODENAME),
			'description'	=> $cms_language->getMessage(self::MESSAGE_PAGE_SITEMAP_DESC, false, MOD_CMS_SITEMAP_CODENAME),
			'objectId'		=> 'cms_sitemap',
			'url'			=> PATH_ADMIN_MODULES_WR.'/'.MOD_CMS_SITEMAP_CODENAME.'/index.php',
			'module'		=> 'cms_sitemap',
			'class'			=> 'atm-elements',
		);
		$objectsInfos[] = array(
			'label'			=> $cms_language->getMessage(self::MESSAGE_PAGE_TREE, false, MOD_CMS_SITEMAP_CODENAME),
			'adminLabel'	=> $cms_language->getMessage(self::MESSAGE_PAGE_TREE, false, MOD_CMS_SITEMAP_CODENAME),
			'description'	=> $cms_language->getMessage(self::MESSAGE_PAGE_TREE_DESC, false, MOD_CMS_SITEMAP_CODENAME),
			'objectId'		=> 'tree',
			'url'			=> PATH_ADMIN_MODULES_WR.'/'.MOD_CMS_SITEMAP_CODENAME.'/tree.php',
			'module'		=> 'cms_sitemap',
			'class'			=> 'atm-categories',
		);
		return $objectsInfos;
	}
	
	/**
	  * Return a list of objects infos to be displayed in page properties tabs according to user privileges
	  *
	  * @return string : HTML scripts infos
	  * @access public
	  */
	function getPageTabsProperties($page, $user) {
		$objectsInfos = array();
		$cms_language = $user->getLanguage();
		if ($user->hasModuleClearance($this->getCodename(), CLEARANCE_MODULE_EDIT)) {
			$objectsInfos[] = array(
							'label'			=> $this->getLabel($cms_language),
							'adminLabel'	=> $this->getLabel($cms_language),
							'description'	=> $cms_language->getMessage(self::MESSAGE_PAGE_SITEMAP_PAGES_DESC, false, MOD_CMS_SITEMAP_CODENAME),
							'objectId'		=> MOD_CMS_SITEMAP_CODENAME,
							'url'			=> PATH_ADMIN_MODULES_WR.'/'.MOD_CMS_SITEMAP_CODENAME.'/page-properties.php',
							'module'		=> $this->getCodename(),
							'class'			=> 'atm-elements',
							'page'			=> $page->getID(),
						);
		}
		return $objectsInfos;
	}
	
	/**
	  * Process the daily routine: regenerate all sitemaps and index if any
	  *
	  * @return void
	  * @access public
	  */
	function processDailyRoutine() {
		$sitemaps = CMS_sitemap::getAll();
		foreach ($sitemaps as $sitemap) {
			//regenerate all sitemaps and index if any
			CMS_scriptsManager::addScript(MOD_CMS_SITEMAP_CODENAME, array('task' => 'regenerate', 'sitemap' => $sitemap->getValue('codename')));
		}
		//start scripts
		CMS_scriptsManager::startScript();
		return true;
	}
	
	/**
	  * Module script task : regenerate one sitemap
	  *
	  * @param array $parameters the task parameters
	  * @return Boolean true/false
	  * @access public
	  */
	function scriptTask($parameters) {
		if ($parameters['task'] == 'regenerate' && isset($parameters['sitemap'])) {
			$sitemap = CMS_sitemap::getByCodename($parameters['sitemap']);
			if (!$sitemap || $sitemap->hasError()) {
				CMS_grandFather::raiseError('Unknown sitemap code to regenerate: '.$parameters['sitemap']);
				return false;
			}
			//generate sitemap
			if ($sitemap->generateFile()) {
				//regenerate sitemaps index if needed
				$module->generateIndex();
				return true;
			}
		}
		return false;
	}
	
	/**
	  * Module script info : get infos for a given script parameters
	  *
	  * @param array $parameters the task parameters
	  * @return string : HTML scripts infos
	  * @access public
	  */
	function scriptInfo($parameters) {
		global $cms_language;
		if (!is_object($cms_language)) {
			return parent::scriptInfo($parameters);
		}
		return $cms_language->getMessage(self::MESSAGE_TASK_REGENERATE_SITEMAP, array($parameters['sitemap']), MOD_CMS_SITEMAP_CODENAME);
	}
	
	/**
	  * Generate index of all sitemap files (only if more than one sitemap exists)
	  *
	  * @return boolean
	  * @access public
	  */
	function generateIndex() {
		$indexName = $this->getParameters('INDEX_NAME');
		if (!$indexName || substr($indexName, -4) != '.xml') {
			$indexName = 'sitemap_index.xml';
		}
		$file = new CMS_file(PATH_REALROOT_FS.'/'.$indexName);
		if ($file->exists()) {
			$file->delete();
		}
		$sitemaps = CMS_sitemap::getAll();
		if (sizeof($sitemaps) < 2) {
			//update robots.txt
			if (!$sitemaps) {
				$this->updateRobotsFile('');
			} else {
				$sitemap = array_shift($sitemaps);
				$website = CMS_websitesCatalog::getByID($sitemap->getValue('site'));
				$this->updateRobotsFile(($website ? $website->getURL() : CMS_websitesCatalog::getMainURL()).PATH_REALROOT_WR.'/'.$sitemap->getFileName());
			}
			return true;
		}
		if (!$this->getParameters('CREATE_INDEX')) {
			return true;
		}
		$content = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
		'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
		$hasContent = false;
		foreach ($sitemaps as $sitemap) {
			if (file_exists(PATH_REALROOT_FS.'/'.$sitemap->getFileName())) {
				$website = CMS_websitesCatalog::getByID($sitemap->getValue('site'));
				$content .= '<sitemap>'."\n".
				"\t".'<loc>'.(($website ? $website->getURL() : CMS_websitesCatalog::getMainURL()).PATH_REALROOT_WR.'/'.$sitemap->getFileName()).'</loc>'."\n".
				'</sitemap>'."\n";
				$hasContent = true;
			}
		}
		$content .= '</sitemapindex>';
		if (!$hasContent) {
			return true;
		}
		//update robots.txt
		$this->updateRobotsFile(CMS_websitesCatalog::getMainURL().PATH_REALROOT_WR.'/'.$indexName);
		
		//encode file in utf8
		if (strtolower(APPLICATION_DEFAULT_ENCODING) != 'utf-8') {
			$content = utf8_encode($content);
		}
		
		$file->setContent($content);
		return $file->writeToPersistence();
	}
	
	/**
	  * List sitemap in robots.txt file (if needed)
	  *
	  * @return boolean
	  * @access public
	  */
	function updateRobotsFile($sitemap) {
		if ($sitemap && $this->getParameters('INDEX_IN_ROBOTS_TXT')) {
			$robotsFile = new CMS_file(PATH_REALROOT_FS.'/robots.txt');
			if ($robotsFile->exists()) {
				$lines = $robotsFile->readContent('array', '');
				$found = false;
				foreach ($lines as $key => $line) {
					if (substr($line, 0, 8) == 'Sitemap:') {
						$lines[$key] = 'Sitemap: '.$sitemap."\n";
						$found = true;
					}
				}
				if (!$found) {
					$lines[] = "\n".'Sitemap: '.$sitemap."\n";
				}
				$robotsFile->setContent(implode('', $lines), false);
			} else {
				$robotsFile->setContent('Sitemap: '.$sitemap."\n", false);
			}
			$robotsFile->writeToPersistence();
		} else {
			$robotsFile = new CMS_file(PATH_REALROOT_FS.'/robots.txt');
			if ($robotsFile->exists()) {
				$lines = $robotsFile->readContent('array', '');
				foreach ($lines as $key => $line) {
					if (substr($line, 0, 8) == 'Sitemap:') {
						unset($lines[$key]);
					}
				}
				$robotsFile->setContent(implode('', $lines), false);
			}
			$robotsFile->writeToPersistence();
		}
		return true;
	}
}
?>