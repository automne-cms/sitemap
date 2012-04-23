#----------------------------------------------------------------
# Messages content for module cms_sitemap
# Language : fr
#----------------------------------------------------------------

DELETE FROM messages WHERE module_mes = 'cms_sitemap' and language_mes = 'fr';

INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(1, 'cms_sitemap', 'fr', 'Plans des Sites');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(2, 'cms_sitemap', 'fr', 'Gérer les plans de sites');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(3, 'cms_sitemap', 'fr', 'Cet onglet vous permet de consulter et modifier les plans de sites existants. Les plans de sites sont employés par les moteurs de recherche externes tels que Google pour indexer votre site.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(4, 'cms_sitemap', 'fr', 'Cet onglet vous permet de consulter et modifier les propriétés de plan de site pour cette page. Les plans de sites sont employés par les moteurs de recherche externes tels que Google pour indexer votre site.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(5, 'cms_sitemap', 'fr', 'Priorité');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(6, 'cms_sitemap', 'fr', 'Auto');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(7, 'cms_sitemap', 'fr', 'Page incluse ?');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(8, 'cms_sitemap', 'fr', 'Sous arborescence incluse ?');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(9, 'cms_sitemap', 'fr', 'Fréquence de mise à jour');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(10, 'cms_sitemap', 'fr', 'Employer la valeur héritée des pages parentes : ');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(11, 'cms_sitemap', 'fr', 'Toujours');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(12, 'cms_sitemap', 'fr', 'Horaire');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(13, 'cms_sitemap', 'fr', 'Quotidienne');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(14, 'cms_sitemap', 'fr', 'Hebdomadaire');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(15, 'cms_sitemap', 'fr', 'Mensuelle');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(16, 'cms_sitemap', 'fr', 'Annuelle');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(17, 'cms_sitemap', 'fr', 'Jamais (archivée)');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(18, 'cms_sitemap', 'fr', 'Si \"Auto\" : Priorité = 1 - (profondeur de la page / 10). Minimum : 0.1');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(19, 'cms_sitemap', 'fr', 'Fréquence de mise à jour estimée pour le contenu de cette page.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(20, 'cms_sitemap', 'fr', 'Inclue');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(21, 'cms_sitemap', 'fr', 'Non inclue');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(23, 'cms_sitemap', 'fr', 'Création / modification d\'un plan de site');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(24, 'cms_sitemap', 'fr', 'Cette page vous permet de modifier les propriétés de votre plan de site.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(25, 'cms_sitemap', 'fr', 'Le nom de code du plan de site sert à générer le nom du fichier XML final. Il ne peut contenir que des caractères alphanumériques minuscules, des chiffres et des tirets. Utilisez \"default\" pour avoir \"sitemap.xml\" comme nom de fichier généré.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(26, 'cms_sitemap', 'fr', 'Le choix du site permet de déterminer la page racine de l\'arborescence décrite par le plan de site.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(27, 'cms_sitemap', 'fr', 'Espaces de nom complémentaires (namespaces)');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(28, 'cms_sitemap', 'fr', 'Vous pouvez ajouter dans ce champ des espaces de nom (namespaces) supplémentaires pour complémenter la définition XML du plan de site. Exemple : xmlns:image=\"http://www.google.com/schemas/sitemap-image/1.1\"');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(29, 'cms_sitemap', 'fr', 'En plus de la description des pages du site, vous pouvez ajouter ici des définitions spécifiques à vos modules polymod.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(30, 'cms_sitemap', 'fr', 'En attente de génération ...');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(31, 'cms_sitemap', 'fr', 'Editer le plan de site sélectionné pour en changer les propriétés.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(32, 'cms_sitemap', 'fr', 'Créer un nouveau plan de site.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(33, 'cms_sitemap', 'fr', 'Supprimer le plan de site sélectionné. Attention, il n\'est pas possible d\'annuler cette action.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(34, 'cms_sitemap', 'fr', 'Cet onglet vous permet de consulter et modifier les propriétés de tous les plans de sites. Les plans de sites sont employés par les moteurs de recherche externes tels que Google pour indexer votre site.<br />Consultez <a href=\"http://www.sitemaps.org/fr/\" target=\"_blank\">la documentation du format pour obtenir de l\'aide</a>.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(35, 'cms_sitemap', 'fr', 'Confirmez-vous la suppression définitive du plan de site sélectionné ?');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(36, 'cms_sitemap', 'fr', 'Générer le fichier');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(37, 'cms_sitemap', 'fr', 'Utiliser cette option permet de forcer la génération du fichier de plan de site maintenant. Il sera aussi généré toutes les nuits.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(38, 'cms_sitemap', 'fr', 'Droits utilisateur de génération');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(39, 'cms_sitemap', 'fr', 'Spécifiez ici l\'identifiant de l\'utilisateur dont les droits d\'accès seront employés pour générer les plans de sites.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(40, 'cms_sitemap', 'fr', 'Créer un index des plans de sites');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(41, 'cms_sitemap', 'fr', 'Si cette option est cochée et que le module comporte plus d\'un plan de site, alors un fichier index sera générer pour lister ces plans de sites.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(42, 'cms_sitemap', 'fr', 'Nom du fichier index');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(43, 'cms_sitemap', 'fr', 'Ce nom sera employé pour nom de fichier d\'index des plans de sites. Il doit comporter l\'extension .xml');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(44, 'cms_sitemap', 'fr', 'Spécifier le nom du plan de site dans le fichier robots.txt');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(45, 'cms_sitemap', 'fr', 'Vous pouvez consulter les pages incluses et exclues des plans de sites ainsi que leurs propriétés.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(46, 'cms_sitemap', 'fr', 'Non inclue car page non publiée.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(47, 'cms_sitemap', 'fr', 'Non inclue car la page possède une redirection.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(48, 'cms_sitemap', 'fr', 'Non inclue car l\'utilisateur ne peut pas voir la page.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(49, 'cms_sitemap', 'fr', 'Non inclue car spécifiquement demandé dans les propriétés.');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(50, 'cms_sitemap', 'fr', 'Arborescence des pages');
INSERT INTO `messages` (`id_mes`, `module_mes`, `language_mes`, `message_mes`) VALUES(51, 'cms_sitemap', 'fr', 'Génération du plan de site : %s');
