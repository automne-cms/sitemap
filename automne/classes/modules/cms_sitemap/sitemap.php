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
// | Author: S?stien Pauchet <sebastien.pauchet@ws-interactive.fr>      |
// +----------------------------------------------------------------------+

/**
  * Class CMS_sitemap
  *
  * represent a sitemap in the sitemap module
  *
  * @package Automne
  * @subpackage cms_sitemap
  * @author S?stien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

class CMS_sitemap extends CMS_grandFather
{
	const MESSAGE_PAGE_XML_SYNTAX_ERROR = 1296;
	const MESSAGE_PAGE_BLOCK_SYNTAX_ERROR = 1295;
	
	/**
	  * sitemap codename
	  * @var string
	  * @access private
	  */
	protected $_codename;

	/**
	  * Site used for the sitemap
	  * @var integer
	  * @access private
	  */
	protected $_site;

	/**
	  * XML definition
	  * @var string
	  * @access private
	  */
	protected $_definition = '';

	/**
	  * XML namespaces
	  * @var string
	  * @access private
	  */
	protected $_namespaces;

	/**
	  * Constructor.
	  * initializes the sitemap if the codename is given.
	  *
	  * @param string $codename
	  * @return void
	  * @access public
	  */
	function __construct($codename = '')
	{
		if ($codename) {
			$sql = "
				select
					*
				from
					mod_cms_sitemap
				where
					codename_mcs='".io::sanitizeSQLString($codename)."'
			";
			$q = new CMS_query($sql);
			if ($q->getNumRows()) {
				$data = $q->getArray();
				$this->_codename = $codename;
				$this->_site = $data["site_mcs"];
				$this->_definition = $data["definition_mcs"];
				$this->_namespaces = $data["namespaces_mcs"];
			} else {
				$this->raiseError("Unknown codename :".$codename);
			}
		}
	}
	
	/**
	  * Get a value
	  *
	  * @param string $name : value name to get
	  * @return mixed or false if not exists
	  * @access public
	  */
	function getValue($name) {
		$var = '_'.$name;
		if (!in_array($name, array('codename', 'site', 'definition', 'namespaces'))) {
			$this->raiseError("Unknown value to get: ".$name);
			return false;
		}
		if ($name == 'definition') {
			return $this->getDefinition();
		}
		return $this->{$var};
	}
	
	/**
	  * Set a value
	  *
	  * @param string $name : value name to set
	  * @param mixed $value : the value to set
	  * @return boolean
	  * @access public
	  */
	function setValue($name, $value) {
		$var = '_'.$name;
		if (!in_array($name, array('codename', 'site', 'definition', 'namespaces'))) {
			$this->raiseError("Unknown value to set: ".$name);
			return false;
		}
		if ($name == 'codename' && $this->_codename && $this->_codename != $value) {
			//change codename : delete old file and db record
			if (!$this->_deleteFile()) {
				return false;
			}
			$sql = "
				delete
				from
					mod_cms_sitemap
				where
					codename_mcs='".SensitiveIO::sanitizeSQLString($this->_codename)."'
			";
			$q = new CMS_query($sql);
		} elseif ($name == 'definition') {
			return $this->setDefinition($value);
		}
		$this->{$var} = $value;
		return true;
	}
	
	/**
	  * Sets the definition from a string and try to parse it
	  *
	  * @param string $definition The definition
	  * @param boolean $haltOnPolymodParsing Stop setting definition if error on polymod parsing are found (default : true)
	  * @return boolean true on success, false on failure
	  * @access public
	  */
	function setDefinition($definition, $haltOnPolymodParsing = true) {
		global $cms_language;
		
		//because here we can have tags with specific namespaces, we need to add tag with namespace arround declaration
		$definition = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.($this->_namespaces ? ' '.$this->_namespaces : '').'>'."\n".$definition.'</urlset>';
		
		$defXML = new CMS_DOMDocument();
		try {
			$defXML->loadXML($definition, 0, true, false);
		} catch (DOMException $e) {
			return $cms_language->getMessage(self::MESSAGE_PAGE_XML_SYNTAX_ERROR, array($e->getMessage()));
		}
		$blocks = $defXML->getElementsByTagName('block');
		$modules = array();
		foreach ($blocks as $block) {
			if ($block->hasAttribute("module")) {
				$modules[] = $block->getAttribute("module");
			} else {
				return $cms_language->getMessage(self::MESSAGE_PAGE_XML_SYNTAX_ERROR, array($cms_language->getMessage(self::MESSAGE_PAGE_BLOCK_SYNTAX_ERROR)));
			}
		}
		$modules = array_unique($modules);
		//check if rows use a polymod block, if so pass to module for variables conversion
		$rowConverted = false;
		foreach ($modules as $moduleCodename) {
			if (CMS_modulesCatalog::isPolymod($moduleCodename)) {
				$rowConverted = true;
				$module = CMS_modulesCatalog::getByCodename($moduleCodename);
				$definition = $module->convertDefinitionString($definition, false);
			}
		}
		if ($rowConverted) {
			//check definition parsing
			$parsing = new CMS_polymod_definition_parsing($definition, true, CMS_polymod_definition_parsing::CHECK_PARSING_MODE);
			$errors = $parsing->getParsingError();
			if ($errors && $haltOnPolymodParsing) {
				return $cms_language->getMessage(self::MESSAGE_PAGE_XML_SYNTAX_ERROR, array($errors));
			}
		}
		
		//strip urlset tag arround definition
		$definition = str_replace(array(
			'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.($this->_namespaces ? ' '.$this->_namespaces : '').'>'."\n",
			'</urlset>',
		), array('', ''), $definition);
		
		$this->_definition = $definition;
		return true;
	}
	
	/**
	  * Get the definition 
	  *
	  * @return string the definition
	  * @access public
	  */
	function getDefinition() {
		//because here we can have tags with specific namespaces, we need to add tag with namespace arround declaration
		$definition = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.($this->_namespaces ? ' '.$this->_namespaces : '').'>'."\n".$this->_definition.'</urlset>';
		
		$defXML = new CMS_DOMDocument();
		try {
			$defXML->loadXML($definition, 0, true, false);
		} catch (DOMException $e) {
			return $this->_definition;
		}
		$blocks = $defXML->getElementsByTagName('block');
		$modules = array();
		foreach ($blocks as $block) {
			if ($block->hasAttribute("module")) {
				$modules[] = $block->getAttribute("module");
			} else {
				return $this->_definition;
			}
		}
		$modules = array_unique($modules);
		//check if rows use a polymod block, if so pass to module for variables conversion
		$rowConverted = false;
		foreach ($modules as $moduleCodename) {
			if (CMS_modulesCatalog::isPolymod($moduleCodename)) {
				$rowConverted = true;
				$module = CMS_modulesCatalog::getByCodename($moduleCodename);
				$definition = $module->convertDefinitionString($definition, true);
			}
		}
		
		//strip urlset tag arround definition
		$definition = str_replace(array(
			'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.($this->_namespaces ? ' '.$this->_namespaces : '').'>'."\n",
			'</urlset>',
		), array('', ''), $definition);
		
		return $definition;
	}
	
	/**
	  * Writes the page Data into persistence
	  *
	  * @return boolean true on success, false on failure
	  * @access public
	  */
	function writeToPersistence() {
		$sql_fields = "
			codename_mcs='".SensitiveIO::sanitizeSQLString($this->_codename)."',
			site_mcs='".SensitiveIO::sanitizeSQLString($this->_site)."',
			definition_mcs='".SensitiveIO::sanitizeSQLString($this->_definition)."',
			namespaces_mcs='".SensitiveIO::sanitizeSQLString($this->_namespaces)."'
		";
		$sql = "
			replace into
				mod_cms_sitemap
			set
				".$sql_fields."
		";
		$q = new CMS_query($sql);
		if ($q->hasError()) {
			return false;
		}
		return true;
	}
	
	/**
	  * Destroy current sitemap
	  *
	  * @return boolean true on success, false on failure
	  * @access public
	  */
	function destroy() {
		//1- delete sitemap file
		if (!$this->_deleteFile()) {
			return false;
		}
		//2- delete mysql data
		$sql = "
			delete
			from
				mod_cms_sitemap
			where
				codename_mcs='".SensitiveIO::sanitizeSQLString($this->_codename)."'
		";
		$q = new CMS_query($sql);
		
		//3- unset object
		unset($this);
		return true;
	}
	
	/**
	  * Get current sitemap filename
	  *
	  * @return string
	  * @access private
	  */
	function getFileName() {
		if ($this->_codename == 'default') {
			return 'sitemap.xml';
		} else {
			return 'sitemap_'.$this->_codename.'.xml';
		}
	}
	
	/**
	  * Delete sitemap file
	  *
	  * @return boolean true on success, false on failure
	  * @access private
	  */
	private function _deleteFile() {
		$file = new CMS_file(PATH_REALROOT_FS.'/'.$this->getFileName());
		if ($file->exists()) {
			return $file->delete();
		}
		return true;
	}
	
	/**
	  * Generate sitemap file
	  *
	  * @return boolean true on success, false on failure
	  * @access public
	  */
	function generateFile() {
		//delete old file if any
		$file = new CMS_file(PATH_REALROOT_FS.'/'.$this->getFileName());
		if ($file->exists()) {
			$file->delete();
		}
		//generate file
		$website = CMS_websitesCatalog::getByID($this->getValue('site'));
		if (!$website || $website->hasError()) {
			$this->raiseError("Unkown or error in sitemap ".$this->_codename." website.");
			return false;
		}
		$root = $website->getRoot();
		if (!$root || $root->hasError()) {
			$this->raiseError("Unkown or error in sitemap ".$this->_codename." root page.");
			return false;
		}
		
		$codename = MOD_CMS_SITEMAP_CODENAME;
		$module = CMS_modulesCatalog::getByCodename($codename);
		$userId = $module->getParameters('USER_FOR_GENERATION');
		$cms_user = false;
		if (io::isPositiveInteger($userId)) {
			$cms_user = CMS_profile_usersCatalog ::getByID($userId);
		}
		if (!$cms_user) {
			$cms_user = CMS_profile_usersCatalog ::getByID(ANONYMOUS_PROFILEUSER_ID);
		}
		
		$page = CMS_sitemap_page::getPageByID($root->getID(), $cms_user);
		$pagesInfos = $this->_getPagesInfos($page);
		//start XML content
		$content = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
		'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.($this->_namespaces ? ' '.$this->_namespaces : '').'>'."\n";
		//add pages infos
		if ($pagesInfos) {
			foreach ($pagesInfos as $pageInfo) {
				$content .= '<url>'."\n".
				"\t".'<loc>'.$pageInfo['loc'].'</loc>'."\n".
				"\t".'<lastmod>'.$pageInfo['lastmod'].'</lastmod>'."\n".
				"\t".'<changefreq>'.$pageInfo['changefreq'].'</changefreq>'."\n".
				"\t".'<priority>'.$pageInfo['priority'].'</priority>'."\n".
				'</url>'."\n";
			}
		}
		//add compiled definition content if any
		if ($this->_definition) {
			$content .= $this->_compileDefinition($root, $cms_user);
		}
		//end XML content
		$content .= '</urlset>';
		
		//encode file in utf8
		if (strtolower(APPLICATION_DEFAULT_ENCODING) != 'utf-8') {
			$content = utf8_encode($content);
		}
		$file->setContent($content);
		
		return $file->writeToPersistence();
	}
	
	/**
	  * Compile the sitemap definition
	  *
	  * @return string
	  * @access private
	  */
	private function _compileDefinition($root, $cms_user) {
		//because here we can have tags with specific namespaces, we need to add tag with namespace arround declaration
		$definition = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.($this->_namespaces ? ' '.$this->_namespaces : '').'>'."\n".$this->_definition.'</urlset>';
		$compiledDefinition = '';
		$defXML = new CMS_DOMDocument();
		try {
			$defXML->loadXML($definition, 0, true, false);
		} catch (DOMException $e) {
			return '';
		}
		$blocks = $defXML->getElementsByTagName('block');
		foreach ($blocks as $block) {
			$parameters = array();
			$parameters['public'] = true;
			$parameters['pageID'] = $root->getID();
			if ($block->hasAttribute("module")) {
				$parameters['module'] = $block->getAttribute("module");
			}
			if ($block->hasAttribute("language")) {
				$parameters['language'] = $block->getAttribute("language");
			}
			if ($block->hasAttribute("cache")) {
				$parameters['cache'] = $block->getAttribute("cache");
			}
			$definitionParsing = new CMS_polymod_definition_parsing(CMS_DOMDocument::DOMElementToString($block, true), true, CMS_polymod_definition_parsing::PARSE_MODE);
			$compiledDefinition .= $definitionParsing->getContent(CMS_polymod_definition_parsing::OUTPUT_RESULT, $parameters);
		}
		return $compiledDefinition;
	}
	
	/**
	  * Get sitemap pages infos recursively
	  *
	  * @return array of pages infos
	  * @access private
	  */
	private function _getPagesInfos($page, $cms_user) {
		$pages = array();
		if ($page && !$page->hasError()) {
			$redirectlink = $page->getRedirectLink(true);
			if ($page->getPublication() == RESOURCE_PUBLICATION_PUBLIC //if page is public
				&& !$redirectlink->hasValidHREF() //is not a redirection
				&& (!APPLICATION_ENFORCES_ACCESS_CONTROL || $cms_user->hasPageClearance($page->getID(), CLEARANCE_PAGE_VIEW)) //user has rights to view it
				&& $page->getValue('included') == 1 //is included
				) {
				//get last modification date from log
				$lastlog = CMS_log_catalog::getByResourceAction(MOD_STANDARD_CODENAME, $page->getID(), array(CMS_log::LOG_ACTION_RESOURCE_SUBMIT_DRAFT, CMS_log::LOG_ACTION_RESOURCE_DIRECT_VALIDATION), 1);
				if (!$lastlog || !is_object($lastlog[0])) {
					//use publication date instead
					$date = $page->getStatus()->getPublicationDateStart();
				} else {
					$date = $lastlog[0]->getDateTime();
				}
				$pages[$page->getID()] = array(
					'loc'			=> $page->getURL(),
					'lastmod'		=> date('Y-m-d' , $date->getTimestamp()),
					'changefreq'	=> $page->getValue('frequency'),
					'priority'		=> $page->getPriority(),
				);
			}
			$siblings = CMS_tree::getSiblings($page, false, false);
			if ($siblings) {
				foreach ($siblings as $sibling) {
					if (!isset($pages[$sibling])) {
						$page = CMS_sitemap_page::getPageByID($sibling);
						$pages = $pages + $this->_getPagesInfos($page, $cms_user);
					}
				}
			}
		}
		return $pages;
	}
	
	/**
	  * Returns a CMS_sitemap for a given codename
	  * Static function.
	  *
	  * @param string $codename The DB codename of the wanted CMS_sitemap
	  * @return CMS_sitemap or false on failure to find it
	  * @access public
	  */
	static function getByCodename($codename, $reset = false) {
		static $sitemaps;
		if (isset($sitemaps[$codename]) && !$reset) {
			return $sitemaps[$codename];
		}
		$sitemaps[$codename] = new CMS_sitemap($codename);
		if ($sitemaps[$codename]->hasError()) {
			$sitemaps[$codename] = false;
		}
		return $sitemaps[$codename];
	}
	
	/**
	  * Get all the sitemap of module
	  *
	  * @param boolean $returnObject function return array of codename or array of CMS_sitemap (default)
	  * @return array
	  * @access public
	  * @static
	  */
	static function getAll($returnObject = true) {
		$sql = "
			select
				codename_mcs
			from
				mod_cms_sitemap";
		$q = new CMS_query($sql);
		$result = array();
		while ($arr = $q->getArray()) {
			if ($returnObject) {
				$sitemap = CMS_sitemap::getByCodename($arr["codename_mcs"]);
				if ($sitemap && !$sitemap->hasError()) {
					$result[$sitemap->getValue('codename')] = $sitemap;
				}
			} else {
				$result[$arr["codename_mcs"]] = $arr["codename_mcs"];
			}
		}
		return $result;
	}
	
	/**
	  * Does the given codename already exists
	  *
	  * @param string $codename the codename to check
	  * @return boolean
	  * @access public
	  * @static
	  */
	static function exists($codename) {
		$q = new CMS_query("
			select
				1
			from
				mod_cms_sitemap
			where codename_mcs='".io::sanitizeSQLString($codename)."'");
		return ($q->getNumRows() ? true : false);
	}
}
?>