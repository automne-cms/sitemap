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
  * PHP page : Load module sitemap window.
  * Used accross an Ajax request.
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

$winId = sensitiveIO::request('winId');
$fatherId = sensitiveIO::request('fatherId');
$pageId = sensitiveIO::request('page', 'io::isPositiveInteger');
$isPage = sensitiveIO::request('isPage') ? '1' : '0';

//Standard messages
define("MESSAGE_PAGE_SAVE", 952);

//Specific module message
define("MESSAGE_PAGE_SITEMAP_PAGES_DESC", 4);
define("MESSAGE_PAGE_PRIORITY_LABEL", 5);
define("MESSAGE_PAGE_PRIORITY_AUTO", 6);
define("MESSAGE_PAGE_PAGE_INCLUDED_LABEL", 7);
define("MESSAGE_PAGE_SUBTREE_INCLUDED_LABEL", 8);
define("MESSAGE_PAGE_UPDATE_FREQ_LABEL", 9);
define("MESSAGE_PAGE_VALUE_FROM_FATHER", 10);
define("MESSAGE_PAGE_FREQ_ALWAYS", 11);
define("MESSAGE_PAGE_FREQ_HOURLY", 12);
define("MESSAGE_PAGE_FREQ_DAILY", 13);
define("MESSAGE_PAGE_FREQ_WEEKLY", 14);
define("MESSAGE_PAGE_FREQ_MONTHLY", 15);
define("MESSAGE_PAGE_FREQ_YEARLY", 16);
define("MESSAGE_PAGE_FREQ_NEVER", 17);
define("MESSAGE_PAGE_PRIORITY_DESC", 18);
define("MESSAGE_PAGE_UPDATE_FREQ_DESC", 19);
define("MESSAGE_PAGE_INCLUDED", 20);
define("MESSAGE_PAGE_NOT_INCLUDED", 21);

if (!$winId) {
	CMS_grandFather::raiseError('Unknown window Id ...');
	$view->show();
}
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

$moduleLabel = sensitiveIO::sanitizeJSString(io::htmlspecialchars($module->getLabel($cms_language)));

$itemControler = PATH_ADMIN_MODULES_WR.'/'.MOD_CMS_SITEMAP_CODENAME.'/page-controler.php';

//load sitemap page object
$page = CMS_sitemap_page::getPageByID($pageId);

$disableFathers = $pageId == APPLICATION_ROOT_PAGE_ID ? 'disabled:1,' : '';
if (!$disableFathers) {
	//load father page sitemap object
	$father = CMS_sitemap_page::getPageByID(CMS_tree::getFather($page, false, false));
}

$priorityDesc = io::htmlspecialchars($cms_language->getJSMessage(MESSAGE_PAGE_PRIORITY_DESC, false, MOD_CMS_SITEMAP_CODENAME));
$prioritiesDatas = sensitiveIO::jsonEncode(array(
	array('0'	, $cms_language->getJSMessage(MESSAGE_PAGE_PRIORITY_AUTO, false, MOD_CMS_SITEMAP_CODENAME)),
	array('1'	, 1),
	array('0.9'	, 0.9),
	array('0.8'	, 0.8),
	array('0.7'	, 0.7),
	array('0.6'	, 0.6),
	array('0.5'	, 0.5),
	array('0.4'	, 0.4),
	array('0.3'	, 0.3),
	array('0.2'	, 0.2),
	array('0.1'	, 0.1),
));
$priorityValue = $page->getValue('priority', false) ? $page->getValue('priority') : '0';

$freqDesc = io::htmlspecialchars($cms_language->getJSMessage(MESSAGE_PAGE_UPDATE_FREQ_DESC, false, MOD_CMS_SITEMAP_CODENAME));
$frequenciesDatas = sensitiveIO::jsonEncode(array(
	array(''		, ' '),
	array('always'	, $cms_language->getJSMessage(MESSAGE_PAGE_FREQ_ALWAYS, false, MOD_CMS_SITEMAP_CODENAME)),
	array('hourly'	, $cms_language->getJSMessage(MESSAGE_PAGE_FREQ_HOURLY, false, MOD_CMS_SITEMAP_CODENAME)),
	array('daily'	, $cms_language->getJSMessage(MESSAGE_PAGE_FREQ_DAILY, false, MOD_CMS_SITEMAP_CODENAME)),
	array('weekly'	, $cms_language->getJSMessage(MESSAGE_PAGE_FREQ_WEEKLY, false, MOD_CMS_SITEMAP_CODENAME)),
	array('monthly'	, $cms_language->getJSMessage(MESSAGE_PAGE_FREQ_MONTHLY, false, MOD_CMS_SITEMAP_CODENAME)),
	array('yearly'	, $cms_language->getJSMessage(MESSAGE_PAGE_FREQ_YEARLY, false, MOD_CMS_SITEMAP_CODENAME)),
	array('never'	, $cms_language->getJSMessage(MESSAGE_PAGE_FREQ_NEVER, false, MOD_CMS_SITEMAP_CODENAME)),
));

if (!$page->getValue('frequency', false)) { //from father ?
	$frequencyValue = $page->getValue('frequency');
	$frequencyFatherValue = 'true';
} else {
	$frequencyValue = $page->getValue('frequency', false);
	$frequencyFatherValue = 'false';
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
$frequencyFatherText = !$disableFathers ? '<strong>'.$frequencies[$father->getValue('frequency')].'</strong>' : '--';

$inclusions = array(
	1	=> $cms_language->getJSMessage(MESSAGE_PAGE_INCLUDED, false, MOD_CMS_SITEMAP_CODENAME),
	-1	=> $cms_language->getJSMessage(MESSAGE_PAGE_NOT_INCLUDED, false, MOD_CMS_SITEMAP_CODENAME),
);

if (!$disableFathers && $page->getValue('included', false) == 0) { //from father ?
	$includedValue = $page->getValue('included');
	$includedFatherValue = 'true';
} else {
	$includedValue = $page->getValue('included', false) == 1 ? '1' : '0';
	$includedFatherValue = 'false';
}
$includedFatherText = !$disableFathers ? '<strong>'.$inclusions[$father->getValue('included')].'</strong>' : '--';

if (!$disableFathers && $page->getValue('subtree', false) == 0) { //from father ?
	$subtreeValue = $page->getValue('subtree');
	$subtreeFatherValue = 'true';
} else {
	$subtreeValue = $page->getValue('subtree', false) == 1 ? '1' : '0';
	$subtreeFatherValue = 'false';
}
$subtreeFatherText = !$disableFathers ? '<strong>'.$inclusions[$father->getValue('subtree')].'</strong>' : '--';

$pageTitle = io::sanitizeJSString($page->getTitle().' ('.$page->getID().')');

$jscontent = <<<END
	var window = Ext.getCmp('{$winId}');
	
	if ('{$isPage}' && window) {
		//if we are in a window context set window title
		window.setTitle('{$pageTitle}');
	}
	
	var submitItem = function () {
		var form = Ext.getCmp('{$winId}-form').getForm();
		form.submit({
			params:{
				module:		'{$codename}',
				page:		'{$pageId}'
			},
			scope:this
		});
	}
	
	var parentdisable = function(fieldName) {
		var form = Ext.getCmp('{$winId}-form').getForm();
		var field = form.findField(fieldName);
		var check = form.findField(fieldName + 'Father');
		if (field && check) {
			field.setDisabled(check.getValue());
		}
	}
	
	//create center panel
	var center = new Ext.Panel({
		region:				'center',
		border:				false,
		autoScroll:			true,
		buttonAlign:		'center',
		title:				'{$cms_language->getJSMessage(MESSAGE_PAGE_SITEMAP_PAGES_DESC, false, MOD_CMS_SITEMAP_CODENAME)}',
		items: [{
			id:				'{$winId}-form',
			layout: 		'form',
			bodyStyle: 		'padding:10px',
			border:			false,
			autoWidth:		true,
			autoHeight:		true,
			xtype:			'atmForm',
			url:			'{$itemControler}',
			labelAlign:		'right',
			defaults: {
				anchor:			'97%',
				allowBlank:		false
			},
			items:[{
				layout:			'column',
				xtype:			'panel',
				border:			false,
				anchor:			'-20px',
				items:[{
					columnWidth:	.4,
					layout: 		'form',
					border:			false,
					items: [{
						labelSeparator:	'',
						name:			'included',
						inputValue:		'1',
						xtype:			'checkbox',
						checked:		{$includedValue},
						boxLabel:		'{$cms_language->getJSMessage(MESSAGE_PAGE_PAGE_INCLUDED_LABEL, false, MOD_CMS_SITEMAP_CODENAME)}'
					}]
				},{
					columnWidth:	.6,
					layout: 		'form',
					border:			false,
					items: [{
						{$disableFathers}
						hideLabel:		true,
						labelSeparator:	'',
						name:			'includedFather',
						inputValue:		'1',
						xtype:			'checkbox',
						checked:		{$includedFatherValue},
						boxLabel:		'{$cms_language->getJSMessage(MESSAGE_PAGE_VALUE_FROM_FATHER, false, MOD_CMS_SITEMAP_CODENAME)} {$includedFatherText}',
						listeners:{
							'check': parentdisable.createDelegate(this, ['included']),
							'afterrender': parentdisable.createDelegate(this, ['included'])
						}
					}]
				}]
			},{
				layout:			'column',
				xtype:			'panel',
				border:			false,
				anchor:			'-20px',
				items:[{
					columnWidth:	.4,
					layout: 		'form',
					border:			false,
					items: [{
						labelSeparator:	'',
						name:			'subtree',
						inputValue:		'1',
						xtype:			'checkbox',
						checked:		{$subtreeValue},
						boxLabel:		'{$cms_language->getJSMessage(MESSAGE_PAGE_SUBTREE_INCLUDED_LABEL, false, MOD_CMS_SITEMAP_CODENAME)}'
					}]
				},{
					columnWidth:	.6,
					layout: 		'form',
					border:			false,
					items: [{
						{$disableFathers}
						hideLabel:		true,
						labelSeparator:	'',
						name:			'subtreeFather',
						inputValue:		'1',
						xtype:			'checkbox',
						checked:		{$subtreeFatherValue},
						boxLabel:		'{$cms_language->getJSMessage(MESSAGE_PAGE_VALUE_FROM_FATHER, false, MOD_CMS_SITEMAP_CODENAME)} {$subtreeFatherText}',
						listeners:{
							'check': parentdisable.createDelegate(this, ['subtree']),
							'afterrender': parentdisable.createDelegate(this, ['subtree'])
						}
					}]
				}]
			},{
				fieldLabel:		'<span class="atm-help" ext:qtip="{$priorityDesc}">{$cms_language->getJSMessage(MESSAGE_PAGE_PRIORITY_LABEL, false, MOD_CMS_SITEMAP_CODENAME)}</span>',
				name:			'priority',
				hiddenName:		'priority',
				xtype:			'combo',
				forceSelection:	true,
				mode:			'local',
				valueField:		'id',
				displayField:	'name',
				value:			'{$priorityValue}',
				triggerAction: 	'all',
				store:			new Ext.data.SimpleStore({
				    fields: 		['id', 'name'],
				    data : 			{$prioritiesDatas}
				}),
				allowBlank: 		true,
				selectOnFocus:		true,
				editable:			false,
				anchor:				false
			},{
				layout:			'column',
				xtype:			'panel',
				border:			false,
				anchor:			'-20px',
				items:[{
					columnWidth:	.4,
					layout: 		'form',
					border:			false,
					items: [{
						fieldLabel:		'<span class="atm-help" ext:qtip="{$freqDesc}">{$cms_language->getJSMessage(MESSAGE_PAGE_UPDATE_FREQ_LABEL, false, MOD_CMS_SITEMAP_CODENAME)}</span>',
						name:			'frequency',
						hiddenName:		'frequency',
						xtype:			'combo',
						forceSelection:	true,
						mode:			'local',
						valueField:		'id',
						displayField:	'name',
						value:			'{$frequencyValue}',
						triggerAction: 	'all',
						store:			new Ext.data.SimpleStore({
						    fields: 		['id', 'name'],
						    data : 			{$frequenciesDatas}
						}),
						allowBlank: 		true,
						selectOnFocus:		true,
						editable:			false,
						anchor:				false
					}]
				},{
					columnWidth:	.6,
					layout: 		'form',
					border:			false,
					items: [{
						{$disableFathers}
						hideLabel:		true,
						labelSeparator:	'',
						name:			'frequencyFather',
						inputValue:		'1',
						xtype:			'checkbox',
						checked:		{$frequencyFatherValue},
						boxLabel:		'{$cms_language->getJSMessage(MESSAGE_PAGE_VALUE_FROM_FATHER, false, MOD_CMS_SITEMAP_CODENAME)} {$frequencyFatherText}',
						listeners:{
							'check': parentdisable.createDelegate(this, ['frequency']),
							'afterrender': parentdisable.createDelegate(this, ['frequency'])
						}
					}]
				}]
			}]
		}],
		buttons:[{
			id:				'{$winId}-save',
			text:			'{$cms_language->getJSMessage(MESSAGE_PAGE_SAVE)}',
			xtype:			'button',
			name:			'submitAdmin',
			handler:		submitItem,
			scope:			this
		}]
	});
	window.add(center);
	window.doLayout();
END;
$view->addJavascript($jscontent);
$view->show();
?>