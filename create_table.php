<?
//////////////////////////////////////////////
// create_table.php
// Creation des tables
// NE PAS MODIFIER
// Version du 30/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 30/10/2008: Première version 

//Les includes
if (!file_exists('config.inc.php'))
{
	die("<p>Le fichier de configuration config.inc.php ne peut être trouvé.</p>");
}
require('config.inc.php');
include	'function.inc.php';

// On passe la base en UTF-8
$query[] ="ALTER DATABASE `".$db_name."` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin";

// Structure de la table `".$db_prefix."blacklist`
$query[] = "CREATE TABLE `".$db_prefix."blacklist` (
  `id_joueur` mediumint(9) NOT NULL default '0',
  `raison` text collate utf8_bin NOT NULL,
  `blacklisted_by` smallint(6) NOT NULL default '0',
  KEY `blacklisted_by` (`blacklisted_by`),
  KEY `id_joueur` (`id_joueur`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";


// Structure de la table `".$db_prefix."boss`
$query[] = "CREATE TABLE `".$db_prefix."boss` (
  `id_boss` int(4) NOT NULL auto_increment,
  `real_id_boss` int(10) NOT NULL,
  `boss_nom` varchar(100) character set latin1 NOT NULL default '',
  PRIMARY KEY  (`id_boss`),
  UNIQUE KEY `id_real_boss` (`real_id_boss`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

// Structure de la table `".$db_prefix."dispo`
$query[] = "CREATE TABLE `".$db_prefix."dispo` (
  `id_joueur` mediumint(9) NOT NULL default '0',
  `id_inst` mediumint(9) NOT NULL default '0',
  `dispo` tinyint(1) NOT NULL default '0',
  `dispo_date` bigint(14) NOT NULL default '0',
  PRIMARY KEY  (`id_joueur`,`id_inst`),
  KEY `dispo` (`dispo`),
  KEY `dispo_date` (`dispo_date`),
  KEY `id_inst` (`id_inst`),
  KEY `id_joueur` (`id_joueur`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";


//  Structure de la table `".$db_prefix."drops`
$query[] = "CREATE TABLE `".$db_prefix."drops` (
  `id_drop` mediumint(9) NOT NULL auto_increment,
  `id_objet` mediumint(9) NOT NULL default '0',
  `id_joueur` mediumint(9) NOT NULL default '0',
  `id_inst` mediumint(9) NOT NULL default '0',
  `drop_dkp` float NOT NULL default '0',
  PRIMARY KEY  (`id_drop`),
  KEY `id_objet` (`id_objet`),
  KEY `id_joueur` (`id_joueur`,`id_inst`),
  KEY `drop_dkp` (`drop_dkp`),
  KEY `id_drop` (`id_drop`),
  KEY `id_inst` (`id_inst`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

//  Structure de la table `".$db_prefix."instances`
$query[] = "CREATE TABLE `".$db_prefix."instances` (
  `id_inst` smallint(6) NOT NULL auto_increment,
  `inst_type` tinyint(1) NOT NULL default '0',
  `date` bigint(14) NOT NULL default '0',
  `inst_nom` varchar(150) collate utf8_bin NOT NULL,
  `inst_orga` smallint(6) NOT NULL default '0',
  `dkp` float NOT NULL default '0',
  PRIMARY KEY  (`id_inst`),
  KEY `inst_orga` (`inst_orga`),
  KEY `date` (`date`),
  KEY `dkp` (`dkp`),
  KEY `id_inst` (`id_inst`),
  KEY `inst_nom` (`inst_nom`),
  KEY `inst_type` (`inst_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

//  Structure de la table `".$db_prefix."joueurs`
$query[] = "CREATE TABLE `".$db_prefix."joueurs` (
  `id_joueur` smallint(6) NOT NULL auto_increment,
  `nom` varchar(150) collate utf8_bin NOT NULL,
  `rank` tinyint(1) NOT NULL default '0',
  `pass` varchar(20) collate utf8_bin NOT NULL,
  `mail` varchar(100) collate utf8_bin NOT NULL,
  `classe` varchar(20) collate utf8_bin NOT NULL,
  `race` varchar(20) collate utf8_bin NOT NULL,
  `niveau` tinyint(2) NOT NULL default '0',
  `guilde` varchar(100) collate utf8_bin NOT NULL,
  `specialisation` tinytext collate utf8_bin NOT NULL,
  `notes` text collate utf8_bin NOT NULL,
  `telephone` varchar(20) collate utf8_bin NOT NULL default '06XXXXXXXX',
  `ajoute_par` smallint(6) NOT NULL default '0',
  `ajoute_le` bigint(14) NOT NULL default '0',
  `vacance` set('0','1') collate utf8_bin NOT NULL default '0',
  `inactive` set('0','1') collate utf8_bin NOT NULL default '0',
  `xml_data` text collate utf8_bin NOT NULL,
  `xml_update` bigint(14) NOT NULL,
  PRIMARY KEY  (`id_joueur`),
  KEY `nom` (`nom`),
  KEY `ajoute_le` (`ajoute_le`),
  KEY `ajoute_par` (`ajoute_par`),
  KEY `classe` (`classe`),
  KEY `guilde` (`guilde`),
  KEY `harmonise` (`vacance`),
  KEY `id_joueur` (`id_joueur`),
  KEY `inactive` (`inactive`),
  KEY `mail` (`mail`),
  KEY `niveau` (`niveau`),
  KEY `pass` (`pass`),
  KEY `race` (`race`),
  KEY `rank` (`rank`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

//  Structure de la table `".$db_prefix."liens`
$query[] = "CREATE TABLE `".$db_prefix."liens` (
  `id_inst` smallint(6) NOT NULL default '0',
  `id_joueur` smallint(6) NOT NULL default '0',
  `id_leader` smallint(6) NOT NULL default '0',
  `is_lead` set('0','1') collate utf8_bin NOT NULL default '0',
  `type_raid` set('10','25') collate utf8_bin NOT NULL,
  PRIMARY KEY  (`id_inst`,`id_joueur`),
  KEY `id_inst` (`id_inst`),
  KEY `id_joueur` (`id_joueur`),
  KEY `id_leader` (`id_leader`),
  KEY `is_lead` (`is_lead`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

//  Structure de la table `".$db_prefix."log`
$query[] = "CREATE TABLE `".$db_prefix."log` (
  `log_id` int(11) NOT NULL auto_increment,
  `log_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `log_utilisateur` varchar(50) collate utf8_bin NOT NULL,
  `log_page` varchar(255) collate utf8_bin NOT NULL,
  `log_message` varchar(255) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

//  Structure de la table `".$db_prefix."mails`
$query[] = "CREATE TABLE `".$db_prefix."mails` (
  `id_joueur` mediumint(9) NOT NULL default '0',
  `id_inst` smallint(6) NOT NULL default '0',
  `id_lead` mediumint(9) NOT NULL default '0',
  `mail_date` bigint(14) NOT NULL default '0',
  PRIMARY KEY  (`id_joueur`,`id_inst`),
  KEY `id_lead` (`id_lead`),
  KEY `id_inst` (`id_inst`),
  KEY `id_joueur` (`id_joueur`),
  KEY `mail_date` (`mail_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

//  Structure de la table `".$db_prefix."messages`
$query[] = "CREATE TABLE `".$db_prefix."messages` (
  `id_mess` mediumint(9) NOT NULL auto_increment,
  `id_joueur` mediumint(9) NOT NULL default '0',
  `id_inst` smallint(6) NOT NULL default '0',
  `mess_titre` varchar(200) collate utf8_bin NOT NULL,
  `mess_data` text collate utf8_bin NOT NULL,
  `mess_date` bigint(14) NOT NULL default '0',
  `edited` bigint(14) NOT NULL default '0',
  PRIMARY KEY  (`id_mess`),
  KEY `id_joueur` (`id_joueur`),
  KEY `id_inst` (`id_inst`),
  KEY `edited` (`edited`),
  KEY `id_mess` (`id_mess`),
  KEY `mess_date` (`mess_date`),
  KEY `mess_titre` (`mess_titre`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

//  Structure de la table `".$db_prefix."objets`
$query[] = "CREATE TABLE `".$db_prefix."objets` (
  `id_objet` int(9) NOT NULL auto_increment,
  `real_id` int(10) NOT NULL,
  `obj_nom` varchar(255) collate utf8_bin NOT NULL,
  `obj_img` varchar(255) collate utf8_bin NOT NULL,
  `obj_carac` mediumtext collate utf8_bin NOT NULL,
  `dropped_by` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id_objet`),
  KEY `real_id` (`real_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

//  Structure de la table `".$db_prefix."wishlist`
$query[] = "CREATE TABLE `".$db_prefix."wishlist` (
  `id_joueur` mediumint(9) NOT NULL default '0',
  `id_objet` mediumint(9) NOT NULL default '0',
  `poids` tinyint(4) NOT NULL default '0',
  `wish_date` bigint(14) NOT NULL default '0',
  PRIMARY KEY  (`id_joueur`,`id_objet`),
  KEY `id_joueur` (`id_joueur`),
  KEY `id_objet` (`id_objet`),
  KEY `poids` (`poids`),
  KEY `wish_date` (`wish_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

//  Structure de la table `servers_config`
$query[] = "CREATE TABLE `servers_config` (
  `id_server` tinyint(4) NOT NULL auto_increment,
  `prefix` varchar(10) collate utf8_bin NOT NULL,
  `forum_link` varchar(150) collate utf8_bin NOT NULL default 'forum/',
  `instances` mediumtext collate utf8_bin NOT NULL,
  `langue` set('fr') collate utf8_bin NOT NULL default 'fr',
  `limit_wishlist` tinyint(4) NOT NULL default '5',
  `lock_wishlist` set('0','1') collate utf8_bin NOT NULL default '0',
  `limit_obj_add` tinyint(4) NOT NULL default '3',
  `group_multi_lead` set('0','1') collate utf8_bin NOT NULL default '0',
  PRIMARY KEY  (`id_server`),
  UNIQUE KEY `prefix` (`prefix`),
  KEY `forum_link` (`forum_link`),
  KEY `group_multi_lead` (`group_multi_lead`),
  KEY `id_server` (`id_server`),
  KEY `langue` (`langue`),
  KEY `limit_obj_add` (`limit_obj_add`),
  KEY `limit_wishlist` (`limit_wishlist`),
  KEY `lock_wishlist` (`lock_wishlist`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin";

//On remplis la table de config
$query[] = "INSERT INTO `servers_config` (`prefix`, `instances`, `langue`, `limit_wishlist`, `lock_wishlist`, `limit_obj_add`, `group_multi_lead`) VALUES
('".$db_prefix."', 0x526169643b4576656e74204775696c64653b54656d706c65204e6f69723b48796a616c3b53756e77656c6c3b4e61787872616d6173, 'fr', 0, '0', 10, '1')";

//On créé l'admin
$query[] = "INSERT INTO orga_joueurs (nom, rank) VALUES ('Admin', 4)";


//On lance la création
while ($each_query = each($query))
{
    $resultat = mysql_query($each_query[1]);
    if (!$resultat)
    {
    print("erreurs lors de la création des tables.  Error: ".mysql_error())."<p>";
    }
}

Header("Location: ".$_SERVER['HTTP_REFERER']);

?>