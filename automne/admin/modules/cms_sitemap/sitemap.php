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
  * PHP page : Load sitemap interface
  * Used accross an Ajax request. Render a sitemap for edition
  *
  * @package Automne
  * @subpackage admin
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

require_once(dirname(__FILE__).'/../../../../cms_rc_admin.php');

define("MESSAGE_TOOLBAR_HELP",1073);
define("MESSAGE_ERROR_INCORRECT_VALUES", 1702);
define("MESSAGE_ERROR_SAVING", 1701);
define("MESSAGE_PAGE_SAVE", 952);
define("MESSAGE_PAGE_FIELD_CODENAME", 1675);
define("MESSAGE_PAGE_INFO_FIELD_CODENAME_VTYPE", 1677);
define("MESSAGE_PAGE_FIELD_WEBSITE", 2);
define("MESSAGE_PAGE_SYNTAX_COLOR", 725);
define("MESSAGE_PAGE_ACTION_REINDENT", 726);
define("MESSAGE_PAGE_DEFINITION", 1495);
define("MESSAGE_ACTION_HELP", 1073);

//specific module messages
define("MESSAGE_PAGE_SITEMAP_MANAGEMENT", 1);
define("MESSAGE_PAGE_CREATE_UPDATE", 23);
define("MESSAGE_TOOLBAR_HELP_DESC", 24);
define("MESSAGE_PAGE_FIELD_CODENAME_DESC", 25);
define("MESSAGE_PAGE_FIELD_WEBSITE_DESC", 26);
define("MESSAGE_PAGE_FIELD_NAMESPACES", 27);
define("MESSAGE_PAGE_FIELD_NAMESPACES_DESC", 28);
define("MESSAGE_PAGE_DEFINITION_DESC", 29);

//load interface instance
$view = CMS_view::getInstance();
//set default display mode for this page
$view->setDisplayMode(CMS_view::SHOW_RAW);
//This file is an admin file. Interface must be secure
$view->setSecure();

$winId = sensitiveIO::request('winId');
$code = sensitiveIO::request('code');

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

//load item messages
if ($code) {
	$sitemap = CMS_sitemap::getByCodename($code);
	if (!$sitemap || $sitemap->hasError()) {
		CMS_grandFather::raiseError('Unknown sitemap code : '.$code);
		$view->show();
	}
} else {
	$sitemap = new CMS_sitemap();
}

$winLabel = sensitiveIO::sanitizeJSString($cms_language->getMessage(MESSAGE_PAGE_SITEMAP_MANAGEMENT, false, MOD_CMS_SITEMAP_CODENAME)." :: ".$cms_language->getMessage(MESSAGE_PAGE_CREATE_UPDATE, false, MOD_CMS_SITEMAP_CODENAME));

$code = io::sanitizeJSString($sitemap->getValue('codename'));

$websites = CMS_websitesCatalog::getAll();
$websitesDatas = array();
foreach ($websites as $website) {
	$websitesDatas[] = array($website->getID(), $website->getLabel());
}
$websitesDatas = sensitiveIO::jsonEncode($websitesDatas);
$websiteValue = $sitemap->getValue('site');

$namespaces = io::sanitizeJSString($sitemap->getValue('namespaces'));
$namespacesDesc = io::htmlspecialchars($cms_language->getJSMessage(MESSAGE_PAGE_FIELD_NAMESPACES_DESC, false, MOD_CMS_SITEMAP_CODENAME));

$itemsControlerURL = PATH_ADMIN_MODULES_WR.'/'.MOD_CMS_SITEMAP_CODENAME.'/sitemap-controler.php';

$content = '<textarea id="definition-'.$winId.'" style="display:none;">'.htmlspecialchars($sitemap->getValue('definition')).'</textarea>';
$view->setContent($content);

$jscontent = <<<END
	var window = Ext.getCmp('{$winId}');
	//set window title
	window.setTitle('{$winLabel}');
	//set help button on top of page
	window.tools['help'].show();
	//add a tooltip on button
	var propertiesTip = new Ext.ToolTip({
		target:		 window.tools['help'],
		title:			 '{$cms_language->getJsMessage(MESSAGE_TOOLBAR_HELP)}',
		html:			 '{$cms_language->getJSMessage(MESSAGE_TOOLBAR_HELP_DESC, false, MOD_CMS_SITEMAP_CODENAME)}',
		dismissDelay:	0
	});
	window.saved = false;
	window.code = '{$code}';
	
	var submitItem = function (action) {
		if (editor) {
			editor.save();
		}
		var form = Ext.getCmp('{$winId}-form').getForm();
		var values = form.getValues();
		
		form.submit({
			params:{
				oldcode:	window.code,
				action:		action
			},
			success:function(form, action){
				if (!action.result || action.result.success == false) {
					Automne.message.show('{$cms_language->getJSMessage(MESSAGE_ERROR_SAVING)}', '', window);
				} else {
					window.saved = true;
				}
			},
			failure:function(form, action){
				Automne.message.show('{$cms_language->getJSMessage(MESSAGE_ERROR_SAVING)}', '', window);
			},
			scope:this
		});
		
	}
	//editor var
	var editor;
	
	//create center panel
	var center = new Ext.Panel({
		region:				'center',
		border:				false,
		autoScroll:			true,
		buttonAlign:		'center',
		items: [{
			id:				'{$winId}-form',
			layout: 		'form',
			bodyStyle: 		'padding:10px',
			border:			false,
			autoWidth:		true,
			autoHeight:		true,
			xtype:			'atmForm',
			url:			'{$itemsControlerURL}',
			labelAlign:		'right',
			defaults: {
				xtype:			'textfield',
				anchor:			'97%',
				allowBlank:		true
			},
			items:[{
				fieldLabel:		'<span ext:qtip=\"{$cms_language->getJSMessage(MESSAGE_PAGE_FIELD_CODENAME_DESC, false, MOD_CMS_SITEMAP_CODENAME)}\" class=\"atm-help\"><span class="atm-red">*</span> {$cms_language->getJSMessage(MESSAGE_PAGE_FIELD_CODENAME)}</span>',
				xtype:			'textfield',
				name:			'code',
				maxLength:		50,
				vtype:			'codename',
				allowBlank:		false,
				vtypeText:		'{$cms_language->getJSMessage(MESSAGE_PAGE_INFO_FIELD_CODENAME_VTYPE)}',
				value:			'{$code}'
			},{
				fieldLabel:		'<span ext:qtip=\"{$cms_language->getJSMessage(MESSAGE_PAGE_FIELD_WEBSITE_DESC, false, MOD_CMS_SITEMAP_CODENAME)}\" class=\"atm-help\"><span class="atm-red">*</span> {$cms_language->getJSMessage(MESSAGE_PAGE_FIELD_WEBSITE)}</span>',
				name:			'website',
				hiddenName:		'website',
				xtype:			'combo',
				forceSelection:	true,
				mode:			'local',
				valueField:		'id',
				displayField:	'name',
				value:			'{$websiteValue}',
				triggerAction: 	'all',
				store:			new Ext.data.SimpleStore({
				    fields: 		['id', 'name'],
				    data : 			{$websitesDatas}
				}),
				allowBlank: 		false,
				selectOnFocus:		true,
				editable:			false,
				anchor:				false
			},{
				xtype:			'checkbox',
				boxLabel:		'{$cms_language->getJSMessage(MESSAGE_PAGE_SYNTAX_COLOR)}',
				labelSeparator:	'',
				listeners:		{'check':function(field, checked) {
					if (checked) {
						var textarea = Ext.get('defText-{$winId}');
						var width = textarea.getWidth();
						var height = textarea.getHeight();
						var foldFunc = CodeMirror.newFoldFunction(CodeMirror.braceRangeFinder);
						editor = CodeMirror.fromTextArea(document.getElementById('defText-{$winId}'), {
					        lineNumbers: true,
					        matchBrackets: true,
					        mode: "application/x-httpd-php",
							indentWithTabs: true,
					        enterMode: "keep",
					        tabMode: "shift",
							tabSize: 2,
							onGutterClick: foldFunc,
							extraKeys: {
								"Ctrl-Q": function(cm){
									foldFunc(cm, cm.getCursor().line);
								},
								"Ctrl-S": function() {
									Ext.getCmp('save-{$winId}').handler();
								}
							}
					    });
						Ext.select('.CodeMirror-scroll').setHeight((height - 6));
						Ext.select('.CodeMirror-scroll').setWidth(width);
						
						field.disable();
						Ext.getCmp('reindent-{$winId}').show();
					}
				}, scope:this}
			},{
				id:				'defText-{$winId}',
				xtype:			'textarea',
				name:			'definition',
				cls:			'atm-code',
				height:			400,
				enableKeyEvents:true,
				fieldLabel:		'<span ext:qtip=\"{$cms_language->getJSMessage(MESSAGE_PAGE_DEFINITION_DESC, false, MOD_CMS_SITEMAP_CODENAME)}\" class=\"atm-help\">{$cms_language->getJSMessage(MESSAGE_PAGE_DEFINITION)}</span>',
				value:			Ext.get('definition-{$winId}').dom.value,
				listeners:{'keypress': function(field, e){
					var k = e.getKey();
					//manage TAB press
					if(k == e.TAB) {
						e.stopEvent();
						var myValue = '\t';
						var myField = field.el.dom;
						if (document.selection) {//IE support
							myField.focus();
							sel = document.selection.createRange();
							sel.text = myValue;
							myField.focus();
						} else if (myField.selectionStart || myField.selectionStart == '0') {
							var startPos = myField.selectionStart;
							var endPos = myField.selectionEnd;
							var scrollTop = myField.scrollTop;
							myField.value = myField.value.substring(0, startPos)
							              + myValue 
					                      + myField.value.substring(endPos, myField.value.length);
							myField.focus();
							myField.selectionStart = startPos + myValue.length;
							myField.selectionEnd = startPos + myValue.length;
							myField.scrollTop = scrollTop;
						}
					}
				}, 'resize': function(field, width, height){
					if (editor) { //resize editor according to textarea size
						if (height) Ext.select('.CodeMirror-scroll').setHeight((height - 6));
						if (width) Ext.select('.CodeMirror-scroll').setWidth(width);
					}
				},
				scope:this}
			},{
				fieldLabel:		'<span ext:qtip=\"{$namespacesDesc}\" class=\"atm-help\">{$cms_language->getJSMessage(MESSAGE_PAGE_FIELD_NAMESPACES, false, MOD_CMS_SITEMAP_CODENAME)}</span>',
				xtype:			'textfield',
				name:			'namespaces',
				allowBlank:		true,
				value:			'{$namespaces}'
			}]
		}],
		buttons:[{
			text:			'{$cms_language->getJSMessage(MESSAGE_ACTION_HELP)}',
			iconCls:		'atm-pic-question',
			anchor:			'',
			scope:			this,
			handler:		function(button) {
				var windowId = 'rowHelpWindow';
				if (Ext.WindowMgr.get(windowId)) {
					Ext.WindowMgr.bringToFront(windowId);
				} else {
					//create window element
					var win = new Automne.Window({
						id:				windowId,
						modal:			false,
						father:			window.father,
						popupable:		true,
						autoLoad:		{
							url:			'row-help.php',
							params:			{
								winId:			windowId
							},
							nocache:		true,
							scope:			this
						}
					});
					//display window
					win.show(button.getEl());
				}
			}
		},{
			id:				'reindent-{$winId}',
			text:			'{$cms_language->getJSMessage(MESSAGE_PAGE_ACTION_REINDENT)}',
			anchor:			'',
			hidden:			true,
			listeners:		{'click':function(button) {
				editor.reindent();
			}, scope:this}
		},{
			text:			'{$cms_language->getJSMessage(MESSAGE_PAGE_SAVE)}',
			iconCls:		'atm-pic-validate',
			xtype:			'button',
			name:			'submitAdmin',
			handler:		submitItem.createDelegate(this, ['save']),
			scope:			this
		}]
	});
	window.add(center);
	setTimeout(function(){
		//redo windows layout
		window.doLayout();
		if (Ext.isIE7) {
			center.syncSize();
		}
	}, 100);
END;
$view->addJavascript($jscontent);
$view->show();
?>