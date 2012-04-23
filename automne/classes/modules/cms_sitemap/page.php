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
  * Class CMS_sitemap_page
  *
  * Manage sitemap pages
  *
  * @package Automne
  * @subpackage cms_sitemap
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

class CMS_sitemap_page extends CMS_page
{
	/**
	  * Priority
	  *
	  * @var float
	  * @access private
	  */
	protected $_priority;
	
	/**
	  * Is page included ?
	  *
	  * @var integer
	  * @access private
	  */
	protected $_included;
	
	/**
	  * Is subtree included ?
	  *
	  * @var integer
	  * @access private
	  */
	protected $_subtree;
	
	/**
	  * Update frequency
	  *
	  * @var string
	  * @access private
	  */
	protected $_frequency;
	
	/**
	  * Constructor.
	  *
	  * @param integer $id id of page in DB
	  * @return  void
	  * @access public
	  */
    function __construct($id = false)
    {
        //Load page sitemap datas
		if (SensitiveIO::isPositiveInteger($id)) {
			$sql = "
				select
					*
				from
					mod_cms_sitemap_pages
				where
					page_msp='$id'
			";
			$q = new CMS_query($sql);
			if ($q->getNumRows()) {
				$data = $q->getArray();
				
				$this->_priority	= $data["priority_msp"];
				$this->_included	= $data["included_msp"];
				$this->_subtree		= $data["subtree_msp"];
				$this->_frequency	= $data["frequency_msp"];
			}
		}
		parent::__construct($id);
    }
	
	/**
	  * Get a value
	  *
	  * @param string $name : value name to get
	  * @param boolean $fromParent : if no value set : get it from the closest parent in tree
	  * @return mixed or false if not exists
	  * @access public
	  */
	function getValue($name, $fromParent = true) {
		$var = '_'.$name;
		if (!in_array($name, array('priority', 'included', 'subtree', 'frequency'))) {
			$this->raiseError("Unknown value to get: ".$name);
			return false;
		}
		if ($fromParent && !$this->{$var}) {
			return $this->getParentValue($name);
		}
		return $this->{$var};
	}
	
	/**
	  * Get a parent value
	  *
	  * @param string $name : value name to get
	  * @return mixed or false if not exists
	  * @access public
	  */
	function getParentValue($name) {
		$var = '_'.$name;
		if (!in_array($name, array('priority', 'included', 'subtree', 'frequency'))) {
			$this->raiseError("Unknown value to get: ".$name);
			return false;
		}
		if ($name == 'priority') {
			return 0; //Priority is always Auto from parent
		}
		if ($name == 'included') {
			$name = 'subtree';
		}
		$fatherId = CMS_tree::getFather($this->_pageID, false, false);
		if (!$fatherId) {
			return false;
		}
		$father = CMS_sitemap_page::getPageByID($fatherId);
		return $father->getValue($name);
	}
	
	/**
	  * Get page priority accordingly to parents (compute auto value)
	  *
	  * @return float or false if not exists
	  * @access public
	  */
	function getPriority() {
		if ($this->_priority != 0) {
			return $this->_priority;
		}
		$lineage = CMS_tree::getLineage(APPLICATION_ROOT_PAGE_ID, $this->_pageID, false, false);
		$lineage = array_reverse($lineage);
		$count = 0;
		foreach ($lineage as $pageId) {
			$page = CMS_sitemap_page::getPageByID($pageId);
			if ($page->getValue('priority') == 0) {
				$count++;
			} else {
				break;
			}
		}
		$priority = $page->getValue('priority') - ($count / 10);
		if ($priority <= 0.1) {
			return 0.1;
		}
		return $priority;
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
		if (!in_array($name, array('priority', 'included', 'subtree', 'frequency'))) {
			$this->raiseError("Unknown value to set: ".$name);
			return false;
		}
		$this->{$var} = $value;
		return true;
	}
	
	/**
	  * Writes the page Data into persistence
	  *
	  * @return boolean true on success, false on failure
	  * @access public
	  */
	function writeToPersistence() {
		$return = parent::writeToPersistence();
		
		$sql_fields = "
			page_msp='".SensitiveIO::sanitizeSQLString($this->_pageID)."',
			priority_msp='".SensitiveIO::sanitizeSQLString($this->_priority)."',
			included_msp='".SensitiveIO::sanitizeSQLString($this->_included)."',
			subtree_msp='".SensitiveIO::sanitizeSQLString($this->_subtree)."',
			frequency_msp='".SensitiveIO::sanitizeSQLString($this->_frequency)."'
		";
		$sql = "
			replace into
				mod_cms_sitemap_pages
			set
				".$sql_fields."
		";
		$q = new CMS_query($sql);
		if ($q->hasError()) {
			return false;
		}
		return $return;
	}
	
	/**
	  * Returns a CMS_sitemap_page when given an ID
	  * Static function.
	  *
	  * @param integer $id The DB ID of the wanted CMS_sitemap_page
	  * @return CMS_sitemap_page or false on failure to find it
	  * @access public
	  */
	static function getPageByID($id, $reset = false) {
		if (!SensitiveIO::isPositiveInteger($id)) {
			CMS_grandFather::raiseError("Id must be positive integer : ".$id);
			return false;
		}
		static $pages;
		if (isset($pages[$id]) && !$reset) {
			return $pages[$id];
		}
		$pages[$id] = new CMS_sitemap_page($id);
		if ($pages[$id]->hasError()) {
			$pages[$id] = false;
		}
		return $pages[$id];
	}
	
	/**
	  * Returns all the siblings pages, sorted by sibling order.
	  * Static function.
	  *
	  * @param CMS_page $page The page we want he siblings of (can accept the page ID instead of CMS_page)
	  * @param boolean $publicTree Do we want to fetch the public tree or the edited one ?
	  * @param boolean $getPages if false, return only an array of sibling ID, else, return an array of sibling CMS_sitemap_page
	  * @return array(CMS_sitemap_page) The siblings ordered
	  * @access public
	  */
	static function getSiblings(&$page, $publicTree = false, $getPages=true)
	{
		if (sensitiveIO::isPositiveInteger($page)) {
			$pageID = $page;
		} elseif(is_object($page)) {
			$pageID = $page->getID();
		} else {
			CMS_grandFather::raiseError('page must be a valid CMS_page or a page Id : '.$page);
			return array();
		}
		$table = ($publicTree) ? "linx_tree_public" : "linx_tree_edited";
		$sql = "
			select
				sibling_ltr
			from
				".$table."
			where
				father_ltr='".$pageID."'
			order by
				order_ltr
		";
		$q = new CMS_query($sql);
		$pages = array();
		while ($id = $q->getValue("sibling_ltr")) {
			if ($getPages) {
				$pg = CMS_sitemap_page::getPageByID($id);
				if (!$pg->hasError()) {
					$pages[] = $pg;
				}
			} else {
				$pages[]=$id;
			}
		}
		return $pages;
	}
}
?>