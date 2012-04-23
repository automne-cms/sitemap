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
  * PHP page : Load cms_sitemap list window.
  * Used accross an Ajax request.
  * 
  * @package Automne
  * @subpackage cms_sitemap
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

require_once(dirname(__FILE__).'/../../../../cms_rc_admin.php');

//Standard messages
define("MESSAGE_PAGE_NEW", 262);
define("MESSAGE_PAGE_MODIFY", 938);
define("MESSAGE_PAGE_DELETE", 252);
define("MESSAGE_PAGE_LOADING", 1321);
define("MESSAGE_PAGE_LABEL", 814);
define("MESSAGE_PAGE_CODE", 1675);
define("MESSAGE_PAGE_WEBSITE", 2);
define("MESSAGE_PAGE_FILE", 191);

//Specific message
define("MESSAGE_PAGE_EDIT_SELECTED", 31);
define("MESSAGE_PAGE_CREATE_NEW_SITEMAP", 32);
define("MESSAGE_PAGE_DELETE_SELECTED", 33);
define("MESSAGE_PAGE_TITLE", 34);
define("MESSAGE_PAGE_ACTION_DELETE_CONFIRM", 35);
define("MESSAGE_PAGE_GENERATE", 36);
define("MESSAGE_PAGE_GENERATE_SELECTED", 37);

//load interface instance
$view = CMS_view::getInstance();
//set default display mode for this page
$view->setDisplayMode(CMS_view::SHOW_RAW);
//This file is an admin file. Interface must be secure
$view->setSecure();

$winId = sensitiveIO::request('winId');
$codename = sensitiveIO::request('module');

//load module
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

//usefull vars
$searchURL = PATH_ADMIN_MODULES_WR.'/'.MOD_CMS_SITEMAP_CODENAME.'/sitemap-datas.php';
$editURL = PATH_ADMIN_MODULES_WR.'/'.MOD_CMS_SITEMAP_CODENAME.'/sitemap.php';
$itemsControlerURL = PATH_ADMIN_MODULES_WR.'/'.MOD_CMS_SITEMAP_CODENAME.'/sitemap-controler.php';

$jscontent = <<<END
	var moduleObjectWindow = Ext.getCmp('{$winId}');
	var fatherWindow = Ext.getCmp('modulecms_sitemapWindow');
	
	//define search function into window (to be accessible by parent window)
	moduleObjectWindow.search = function() {
		if (!moduleObjectWindow.ok) {
			return;
		}
		if (resultsPanel.getEl()) {
			resultsPanel.getEl().mask('{$cms_language->getJSMessage(MESSAGE_PAGE_LOADING)}');
		}
		store.load({
			callback:		function() {
				if (resultsPanel.getEl()) {
					resultsPanel.getEl().unmask();
				}
			},
			scope:			this
		});
		return;
	}
	
	var objectsWindows = [];
	
	// Results store
	var store = new Automne.JsonStore({
		autoDestroy: 	true,
		root:			'results',
		totalProperty:	'total',
		url:			'{$searchURL}',
		id:				'code',
		remoteSort:		true,
		fields:			['website', 'code', 'file']
	});
	
	var editItem = function(code, button) {
		if (!code) {
			code = '';
		}
		var windowId = 'languageEditWindow'+code;
		if (objectsWindows[windowId]) {
			Ext.WindowMgr.bringToFront(objectsWindows[windowId]);
		} else {
			//create window element
			objectsWindows[windowId] = new Automne.Window({
				id:				windowId,
				code:			code,
				autoLoad:		{
					url:			'{$editURL}',
					params:			{
						winId:			windowId,
						code:			code
					},
					nocache:		true,
					scope:			this
				},
				modal:			false,
				father:			fatherWindow,
				width:			750,
				height:			580,
				animateTarget:	button,
				listeners:{'close':function(win){
					//enable button to allow creation of a other items
					if (!win.code) {
						Ext.getCmp('{$winId}createItem').enable();
					}
					//reload search
					moduleObjectWindow.search();
					//delete window from list
					delete objectsWindows[win.id];
				}}
			});
			//display window
			objectsWindows[windowId].show(button.getEl());
			if (code == '') {
				Ext.getCmp('{$winId}createItem').disable();
			}
		}
	}
	
	var resultsPanel = new Ext.grid.GridPanel({
        title:				'{$cms_language->getJsMessage(MESSAGE_PAGE_TITLE, false, MOD_CMS_SITEMAP_CODENAME)}',
		id:					'{$winId}resultsPanel',
		cls:				'atm-results',
		collapsible:		false,
		region:				'center',
		border:				false,
		store: 				store,
        autoExpandColumn:	'label',
		colModel: new Ext.grid.ColumnModel([
			{header: "{$cms_language->getJsMessage(MESSAGE_PAGE_WEBSITE)}", width: 80, 	dataIndex: 'website', 	sortable: false},
			{header: "{$cms_language->getJsMessage(MESSAGE_PAGE_CODE)}", 	width: 80, 	dataIndex: 'code', 		sortable: false},
			{header: "{$cms_language->getJsMessage(MESSAGE_PAGE_FILE)}",	width: 80, 	dataIndex: 'file', 		sortable: false}
		]),
		selModel: new Ext.grid.RowSelectionModel({singleSelect:true}),
		anchor:				'100%',
		viewConfig: 		{
			forceFit:			true
		},
        loadMask: true,
		tbar: new Ext.Toolbar({
            id: 			'{$winId}toolbar',
            enableOverflow: true,
            items: [{
				id:			'{$winId}editItem',
				iconCls:	'atm-pic-modify',
				xtype:		'button',
				text:		'{$cms_language->getJSMessage(MESSAGE_PAGE_MODIFY)}',
				handler:	function(button) {
					var row = resultsPanel.getSelectionModel().getSelected();
					editItem(row.id, button);
				},
				scope:		this,
				disabled:	true
			},{
				id:			'{$winId}deleteItem',
				iconCls:	'atm-pic-deletion',
				xtype:		'button',
				text:		'{$cms_language->getJSMessage(MESSAGE_PAGE_DELETE)}',
				handler:	function(button) {
					var row = resultsPanel.getSelectionModel().getSelected();
					Automne.message.popup({
						msg: 				'{$cms_language->getJSMessage(MESSAGE_PAGE_ACTION_DELETE_CONFIRM, false, MOD_CMS_SITEMAP_CODENAME)}',
						buttons: 			Ext.MessageBox.OKCANCEL,
						closable: 			false,
						icon: 				Ext.MessageBox.WARNING,
						fn:					function(button) {
							if (button == 'ok') {
								Automne.server.call({
									url:			'{$itemsControlerURL}',
									params:			{
										action:		'delete',
										code:		row.id
									},
									scope:			this,
									fcnCallback:	function(response, options, jsonResponse){
										//reload search
										moduleObjectWindow.search();
									}
								});
							}
						}
					});
				},
				scope:		this,
				disabled:	true
			},{
				id:			'{$winId}generateItem',
				xtype:		'button',
				text:		'{$cms_language->getJSMessage(MESSAGE_PAGE_GENERATE, false, MOD_CMS_SITEMAP_CODENAME)}',
				handler:	function(button) {
					var row = resultsPanel.getSelectionModel().getSelected();
					Automne.server.call({
						url:			'{$itemsControlerURL}',
						params:			{
							action:		'generate',
							code:		row.id
						},
						scope:			this,
						fcnCallback:	function(response, options, jsonResponse){
							//reload search
							moduleObjectWindow.search();
						}
					});
				},
				scope:		this,
				disabled:	true
			}, '->', {
				id:			'{$winId}createItem',
				iconCls:	'atm-pic-add',
				xtype:		'button',
				text:		'{$cms_language->getJSMessage(MESSAGE_PAGE_NEW)}',
				handler:	function(button) {
					editItem(0, button);
				},
				scope:		resultsPanel
			}]
		})
    });
	
	moduleObjectWindow.add(resultsPanel);
	
	//redo windows layout
	moduleObjectWindow.doLayout();
	
	//this flag is needed, because form construction, launch multiple search queries before complete page construct so we check in moduleObjectWindow.search if construction is ok
	moduleObjectWindow.ok = true;
	
	//launch search
	moduleObjectWindow.search();
	
	//add selection events to selection model
	var qtips = [];
	setTimeout(function(){
		qtips['edit'] = new Ext.ToolTip({
			target: 		Ext.getCmp('{$winId}editItem').getEl(),
			html: 			'{$cms_language->getJsMessage(MESSAGE_PAGE_EDIT_SELECTED, false, MOD_CMS_SITEMAP_CODENAME)}',
			disabled:		true
		});
		qtips['create'] = new Ext.ToolTip({
			target: 		Ext.getCmp('{$winId}createItem').getEl(),
			html: 			'{$cms_language->getJsMessage(MESSAGE_PAGE_CREATE_NEW_SITEMAP, false, MOD_CMS_SITEMAP_CODENAME)}'
		});
		qtips['delete'] = new Ext.ToolTip({
			target: 		Ext.getCmp('{$winId}deleteItem').getEl(),
			html: 			'{$cms_language->getJsMessage(MESSAGE_PAGE_DELETE_SELECTED, false, MOD_CMS_SITEMAP_CODENAME)}',
			disabled:		true
		});
		qtips['generate'] = new Ext.ToolTip({
			target: 		Ext.getCmp('{$winId}generateItem').getEl(),
			html: 			'{$cms_language->getJsMessage(MESSAGE_PAGE_GENERATE_SELECTED, false, MOD_CMS_SITEMAP_CODENAME)}',
			disabled:		true
		});
	}, 500);
	resultsPanel.getSelectionModel().on('selectionchange', function(sm){
		if (!sm.getCount()) {
			qtips['edit'].disable();
			qtips['delete'].disable();
			qtips['generate'].disable();
			Ext.getCmp('{$winId}editItem').disable();
			Ext.getCmp('{$winId}deleteItem').disable();
			Ext.getCmp('{$winId}generateItem').disable();
		} else { //enable / disable buttons allowed by selection
			qtips['edit'].enable();
			qtips['delete'].enable();
			qtips['generate'].enable();
			Ext.getCmp('{$winId}editItem').enable();
			Ext.getCmp('{$winId}deleteItem').enable();
			Ext.getCmp('{$winId}generateItem').enable();
		}
		if (Ext.getCmp('{$winId}toolbar')) {
			Ext.getCmp('{$winId}toolbar').syncSize();
		}
	}, this);
END;
$view->addJavascript($jscontent);
$view->show();
?>