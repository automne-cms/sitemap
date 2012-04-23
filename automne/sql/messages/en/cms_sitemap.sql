#----------------------------------------------------------------
# Messages content for module cms_sitemap
# Language : en
#----------------------------------------------------------------

DELETE FROM messages WHERE module_mes = 'cms_sitemap' and language_mes = 'en';

INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(1, 'cms_sitemap', 'en', 'Sitemap');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(2, 'cms_sitemap', 'en', 'Manage site maps');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(3, 'cms_sitemap', 'en', 'This tab allows you to view and modify existing sitemaps. Sitemaps are used by external search engines such as Google to index your website.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(4, 'cms_sitemap', 'en', 'This tab allows you to view and modify page sitemaps properties. Sitemaps are used by external search engines such as Google to index your website.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(5, 'cms_sitemap', 'en', 'Priority');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(6, 'cms_sitemap', 'en', 'Auto');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(7, 'cms_sitemap', 'en', 'Page included?');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(8, 'cms_sitemap', 'en', 'Subtree pages included?');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(9, 'cms_sitemap', 'en', 'Update frequency');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(10, 'cms_sitemap', 'en', 'Use the inherited value of the parent pages:');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(11, 'cms_sitemap', 'en', 'Always');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(12, 'cms_sitemap', 'en', 'Hourly');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(13, 'cms_sitemap', 'en', 'Daily');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(14, 'cms_sitemap', 'en', 'Weekly');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(15, 'cms_sitemap', 'en', 'Monthly');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(16, 'cms_sitemap', 'en', 'Yearly');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(17, 'cms_sitemap', 'en', 'Never (archived)');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(18, 'cms_sitemap', 'en', 'If \"Auto\": Priority = 1 - (depth of the page / 10). Minimum: 0.1');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(19, 'cms_sitemap', 'en', 'Estimated update frequency for this page content.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(20, 'cms_sitemap', 'en', 'Included');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(21, 'cms_sitemap', 'en', 'Not included');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(23, 'cms_sitemap', 'en', 'Create / update a sitemap');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(24, 'cms_sitemap', 'en', 'This window allows you to manage your sitemap properties.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(25, 'cms_sitemap', 'en', 'The codename for the sitemap is used to generate the final XML file name. It can contain only alphanumeric characters lowercase letters, numbers and hyphens. Use \"default\" to have \"sitemap.xml\" as generated file name.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(26, 'cms_sitemap', 'en', 'Site selection determine the root page of the tree described by the sitemap.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(27, 'cms_sitemap', 'en', 'Additional namespaces');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(28, 'cms_sitemap', 'en', 'You can add in this field namespaces to complement the additional definition of the XML sitemap. Example: xmlns:image=\"http://www.google.com/schemas/sitemap-image/1.1\"');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(29, 'cms_sitemap', 'en', 'In addition to the description of the pages, here you can add definitions specific to your polymod modules.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(30, 'cms_sitemap', 'en', 'Pending generation...');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(31, 'cms_sitemap', 'en', 'Edit selected sitemap to change properties.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(32, 'cms_sitemap', 'en', 'Create new sitemap.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(33, 'cms_sitemap', 'en', 'Deleted selected sitemap. You cannot cancel this action.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(34, 'cms_sitemap', 'en', 'This tab allows you to view and modify all stored sitemaps. Sitemaps are used by external search engines such as Google to index your website.<br />See <a href=\"http://www.sitemaps.org/en/\" target=\"_blank\">the format documentation to get help</a>.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(35, 'cms_sitemap', 'en', 'Do you confirm deletion of selected sitemap?');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(36, 'cms_sitemap', 'en', 'Generate file');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(37, 'cms_sitemap', 'en', 'Using this option, you will force generation of the sitemap file now. Otherwise, it will be created every nights.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(38, 'cms_sitemap', 'en', 'User rights for generation');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(39, 'cms_sitemap', 'en', 'Specify the id of the user whose access rights will be used to generate sitemaps.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(40, 'cms_sitemap', 'en', 'Create sitemaps index');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(41, 'cms_sitemap', 'en', 'If this option is checked and if the module has more than one sitemap, then an index file will be generated to list sitemaps.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(42, 'cms_sitemap', 'en', 'Index file name');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(43, 'cms_sitemap', 'en', 'This name will be used to index file name of site maps. It must have the extension .xml');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(44, 'cms_sitemap', 'en', 'Specify the name of the sitemap in the robots.txt file');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(45, 'cms_sitemap', 'en', 'You can view the pages included and excluded from sitemaps and their properties.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(46, 'cms_sitemap', 'en', 'Not included because unpublished page.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(47, 'cms_sitemap', 'en', 'Not included because the page has a redirect.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(48, 'cms_sitemap', 'en', 'Not included because the user has no rights to see the page.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(49, 'cms_sitemap', 'en', 'Not included as specifically requested in the properties.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(50, 'cms_sitemap', 'en', 'Pages tree');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(51, 'cms_sitemap', 'en', 'Regenerate sitemap: %s');