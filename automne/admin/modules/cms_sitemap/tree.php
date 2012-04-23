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
  * PHP page : Load tree window infos. Presents a portion of the pages tree. 
  * Used accross an Ajax request render sitemap tree
  * 
  * @package Automne
  * @subpackage cms_sitemap
  * @author Sébastien Pauchet <sebastien.pauchet@ws-interactive.fr>
  */

require_once(dirname(__FILE__).'/../../../../cms_rc_admin.php');

define("MESSAGE_WINDOW_TITLE", 1031);
define("MESSAGE_TOOLBAR_SEARCH_PAGE", 1091);
define("MESSAGE_SEARCH_LOADING", 1321);
define("MESSAGE_TOOLBAR_HELP",1073);
define("MESSAGE_TOOLBAR_HELP_FILTER", 323);
define("MESSAGE_TOOLBAR_HELP_SEARCH", 324);
define("MESSAGE_WINDOW_HELP", 325);

//specific messages
define("MESSAGE_PAGE_HEADING", 45);

//load interface instance
$view = CMS_view::getInstance();
//set default display mode for this page
$view->setDisplayMode(CMS_view::SHOW_RAW);
//This file is an admin file. Interface must be secure
$view->setSecure();

//simple function used to test a value with the string 'false'
function checkFalse($value) {
	return ($value == 'false');
}
function checkNotFalse($value) {
	return ($value !== 'false');
}
$rootId = APPLICATION_ROOT_PAGE_ID;
$showRoot = true;
$maxlevel = 0;
$editable = true;
$pageProperty = '';
$heading = $cms_language->getMessage(MESSAGE_PAGE_HEADING, false, MOD_CMS_SITEMAP_CODENAME);
$hideMenu = false;
$window = sensitiveIO::request('window', '', true);
$winId = sensitiveIO::request('winId', '', 'treeWindow');
$currentPage = false;

//THE USER SECTIONS, Check if user has module administration, else hide Modules Frame
$hasSectionsRoots = $cms_user->hasEditablePages();

if (!$hasSectionsRoots) {
	CMS_grandFather::raiseError('No sections root found ...');
	$view->show();
}

//load root page
$root = CMS_tree::getPageByID($rootId);
if (!is_object($root) || $root->hasError()) {
	CMS_grandFather::raiseError('Root page has error ...');
	$view->show();
}

$onClick = sensitiveIO::sanitizeJSString('Ext.getCmp(\''.$winId.'\').editPage(%s, this);');

$rootnode = array(
	'id'		=>	'root'.$rootId, 
	'leaf'		=>	false, 
	'expanded'	=>	true,
);

//encode nodes array in json
$rootnode = sensitiveIO::jsonEncode($rootnode);

$rootvisible = ($cms_user->hasPageClearance($root->getID(), CLEARANCE_PAGE_VIEW)) ? 'true' : 'false';

$scriptRoot = dirname($_SERVER['SCRIPT_NAME']);
$adminRoot = PATH_ADMIN_WR;

$heading = $heading ? '\''.sensitiveIO::sanitizeJSString($heading).'\'' : 'null';

$imgPath = PATH_ADMIN_IMAGES_WR;
if ($hideMenu) {
	$tbar = "''";
} else {
	$tbar = "new Ext.Toolbar({
			id:				'treeToolbar',
			items:			[";
			$tbar .= "
				new Ext.Toolbar.Fill(),
				new Automne.ComboBox({
					id: 	'searchBox',
					store: new Ext.data.Store({
						proxy: new Ext.data.HttpProxy({
							url: 				'{$adminRoot}/search-pages.php',
							disableCaching:		true
						}),
						reader: new Automne.JsonReader({
							root: 				'pages',
							totalProperty: 		'totalCount',
							id: 				'pageId'
						}, [
							{name: 'title', 	mapping: 'title'},
							{name: 'status',	mapping: 'status'}
						])
					}),
					listeners: {'specialkey':function(field, e) {
							if (Ext.EventObject.getKey() == Ext.EventObject.ENTER) {
								field.doQuery(field.getValue());
							}
						},
						scope:this
					},
					displayField:		'title',
					autoLoad:			false,
					typeAhead: 			false,
					width: 				320,
					minListWidth:		320,
					resizable: 			true,
					loadingText:		'{$cms_language->getJsMessage(MESSAGE_SEARCH_LOADING)}',
					minChars:			3,
					maxHeight:			400,
					queryDelay:			350,
					pageSize:			10,
					hideTrigger:		true,
					emptyText:			'{$cms_language->getJsMessage(MESSAGE_TOOLBAR_SEARCH_PAGE)}',
					tpl: new Ext.XTemplate(
						'<tpl for=\".\"><div class=\"search-item atm-search-item\">',
							'<h3>{status}&nbsp;{title}</h3>',
						'</div></tpl>'
					),
					itemSelector: 		'div.atm-search-item'
				}),
				new Ext.Toolbar.Spacer(),
				{
					icon:  		'{$imgPath}/help.gif',
					cls: 		'x-btn-icon',
					tooltip: 	{
						title:			'{$cms_language->getJsMessage(MESSAGE_TOOLBAR_HELP)}',
						text:			'{$cms_language->getJsMessage(MESSAGE_TOOLBAR_HELP_SEARCH)}',
						dismissDelay:	30000
					}
			    }
			]
		})
	";
}

$editURL = PATH_ADMIN_MODULES_WR.'/'.MOD_CMS_SITEMAP_CODENAME.'/page-properties.php';

$jscontent = <<<END
	var treeWindow = Ext.getCmp('{$winId}');
	var fatherWindow = Ext.getCmp('modulecms_sitemapWindow');
	var rootconfig = {$rootnode};
	
	var objectsWindows = [];
	treeWindow.editPage = function(pageId, el) {
		var windowId = 'sitemapEditWindow'+pageId;
		if (objectsWindows[windowId]) {
			Ext.WindowMgr.bringToFront(objectsWindows[windowId]);
		} else {
			//create window element
			objectsWindows[windowId] = new Automne.Window({
				id:				windowId,
				page:			pageId,
				autoLoad:		{
					url:			'{$editURL}',
					params:			{
						winId:			windowId,
						fatherId:		'{$winId}',
						page:			pageId,
						isPage:			1
					},
					nocache:		true,
					scope:			this
				},
				modal:			false,
				father:			fatherWindow,
				width:			750,
				height:			580,
				listeners:{'close':function(win){
					//reload parent node
					el.node.parentNode.reload();
					//delete window from list
					delete objectsWindows[win.id];
				}}
			});
			//display window
			objectsWindows[windowId].show();
		}
	}
	
	var tree = new Automne.treePanel({
		id:					'treePanel{$winId}',
		title:				{$heading},
		autoScroll:			true,
		animate:			true,
		region:				'center',
		border:				false,
		rootVisible:		false,
		containerScroll:	true,
		loader: 			new Automne.treeLoader({
								dataUrl:		'{$scriptRoot}/tree-nodes.php',
								baseParams:		{
													onClick:		'{$onClick}',
													winId:			'{$winId}',
													currentPage:	'{$currentPage}',
													root:			'{$rootId}'
												},
								uiProviders:{
									'page': Automne.treeNode
								}
							}),
		root:				new Ext.tree.AsyncTreeNode(rootconfig),
		tbar:				{$tbar}
	});
	
	treeWindow.add(tree);
	
	//redo windows layout
	treeWindow.doLayout();
END;
$view->addJavascript($jscontent);
$view->show();
?>