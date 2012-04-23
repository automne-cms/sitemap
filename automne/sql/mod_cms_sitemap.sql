##
## Contains declaration for module installation : 
## All table creation (mandatory) : inject 1/2
##
# --------------------------------------------------------

INSERT INTO `modules` (`id_mod`, `label_mod`, `codename_mod`, `administrationFrontend_mod`, `hasParameters_mod`, `isPolymod_mod`) VALUES 
('', 1, 'cms_sitemap', 'index.php', 1, 0);


DROP TABLE IF EXISTS `mod_cms_sitemap`;
CREATE TABLE `mod_cms_sitemap` (
  `codename_mcs` varchar(50) NOT NULL,
  `site_mcs` int(11) unsigned NOT NULL,
  `definition_mcs` mediumtext NOT NULL,
  `namespaces_mcs` text NOT NULL,
  UNIQUE KEY `codename_mcs` (`codename_mcs`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mod_cms_sitemap_pages`;
CREATE TABLE `mod_cms_sitemap_pages` (
  `page_msp` int(11) NOT NULL,
  `priority_msp` float NOT NULL DEFAULT '0',
  `included_msp` tinyint(1) NOT NULL,
  `subtree_msp` tinyint(1) NOT NULL,
  `frequency_msp` varchar(20) NOT NULL,
  UNIQUE KEY `page_msp` (`page_msp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `mod_cms_sitemap_pages` (`page_msp`, `priority_msp`, `included_msp`, `subtree_msp`, `frequency_msp`) VALUES(1, 1, 1, 1, 'monthly');
