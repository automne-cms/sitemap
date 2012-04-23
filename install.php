<?php
/**
  * Install or update module
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

require_once(dirname(__FILE__).'/../../cms_rc_admin.php');

//check if module is already installed (if so, it is an update)
$sql = "show tables";
$q = new CMS_query($sql);
$installed = false;
while ($table = $q->getValue(0)) {
	if ($table == 'mod_cms_sitemap') {
		$q = new CMS_query("select * from modules where codename_mod='cms_sitemap'");
		if ($q->getNumRows()) {
			$installed = true;
		}
	}
}
if (!$installed) {
	echo "Sitemap installation : Not installed : Launch installation ...<br />";
	if (CMS_patch::executeSqlScript(PATH_MAIN_FS.'/sql/mod_cms_sitemap.sql',true)) {
		CMS_patch::executeSqlScript(PATH_MAIN_FS.'/sql/mod_cms_sitemap.sql',false);
		//copy module parameters file
		if (CMS_file::copyTo(PATH_TMP_FS.'/automne/classes/modules/cms_sitemap_rc.xml', PATH_PACKAGES_FS.'/modules/cms_sitemap_rc.xml')) {
			CMS_file::chmodFile(FILES_CHMOD, PATH_PACKAGES_FS.'/modules/cms_sitemap_rc.xml');
			echo "Sitemap installation : Installation done.<br /><br />";
		} else {
			echo "Sitemap installation : INSTALLATION ERROR ! Can not copy parameters file ...<br />";
		}
	} else {
		echo "Sitemap installation : INSTALLATION ERROR ! Problem in SQL syntax (SQL tables file) ...<br />";
	}
} else {
	echo "Sitemap installation : Already installed : Launch update ...<br />";
	
	//check modules parameters
	if (!file_exists(PATH_PACKAGES_FS.'/modules/cms_sitemap_rc.xml')) {
		//copy missing parameters file
		CMS_file::copyTo(PATH_TMP_FS.'/automne/tmp/modules/cms_sitemap_rc.xml', PATH_PACKAGES_FS.'/modules/cms_sitemap_rc.xml');
		CMS_file::chmodFile(FILES_CHMOD, PATH_PACKAGES_FS.'/modules/cms_sitemap_rc.xml');
		echo "Sitemap installation : Update done.<br /><br />";
	} else {
		//load destination module parameters
		$module = CMS_modulesCatalog::getByCodename('cms_sitemap');
		$moduleParameters = $module->getParameters(false,true);
		if (!is_array($moduleParameters)) {
			$moduleParameters = array();
		}
		//load the XML data of the source the files
		$sourceXML = new CMS_file(PATH_TMP_FS.PATH_PACKAGES_WR.'/modules/cms_sitemap_rc.xml');
		$domdocument = new CMS_DOMDocument();
		try {
			$domdocument->loadXML($sourceXML->readContent("string"));
		} catch (DOMException $e) {}
		$paramsTags = $domdocument->getElementsByTagName('param');
		$sourceParameters = array();
		foreach ($paramsTags as $aTag) {
			$name = ($aTag->hasAttribute('name')) ? $aTag->getAttribute('name') : '';
			$type = ($aTag->hasAttribute('type')) ? $aTag->getAttribute('type') : '';
			$sourceParameters[$name] = array(CMS_DOMDocument::DOMElementToString($aTag, true),$type);
		}
		//merge the two tables of parameters
		$resultParameters = array_merge($sourceParameters,$moduleParameters);
		//set new parameters to the module
		if ($module->setAndWriteParameters($resultParameters)) {
			echo 'Modules parameters successfully merged<br />';
			echo "Sitemap installation : Update done.<br /><br />";
		} else {
			echo "Sitemap installation : UPDATE ERROR ! Problem for merging modules parameters ...<br /><br />";
		}
	}
}
?>