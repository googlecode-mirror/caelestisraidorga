<?
	include 'config.inc.php';
	include 'commun.inc.php';
	
	if(isset($database[$_GET['db']]) && $rank>=3){
		
		$db = intval($_GET['db']);
		
		$sql = array();
		$sql[] = "
					CREATE TABLE `".prefix('blacklist',$db)."` (
					  `id_joueur` mediumint(9) NOT NULL default '0',
					  `raison` text NOT NULL,
					  `blacklisted_by` smallint(6) NOT NULL default '0',
					  PRIMARY KEY  (`id_joueur`)
					) TYPE=MyISAM;
				";
		$sql[] = "
					CREATE TABLE `".prefix('instances',$db)."` (
					  `id_inst` smallint(6) NOT NULL auto_increment,
					  `inst_type` tinyint(1) NOT NULL default '0',
					  `date` bigint(14) NOT NULL default '0',
					  `inst_nom` varchar(150) NOT NULL default '',
					  `inst_orga` smallint(6) NOT NULL default '0',
					  `dkp` float NOT NULL default '0',
					  PRIMARY KEY  (`id_inst`),
					  KEY `inst_orga` (`inst_orga`)
					) TYPE=MyISAM AUTO_INCREMENT=10 ;
				";
		$sql[] = "
					CREATE TABLE `".prefix('joueurs',$db)."` (
					  `id_joueur` smallint(6) NOT NULL auto_increment,
					  `nom` varchar(150) NOT NULL default '',
					  `rank` tinyint(1) NOT NULL default '0',
					  `pass` varchar(20) binary NOT NULL default '',
					  `mail` varchar(100) NOT NULL default '',
					  `classe` varchar(20) NOT NULL default '',
					  `race` varchar(20) NOT NULL default '',
					  `niveau` tinyint(2) NOT NULL default '60',
					  `guilde` varchar(100) NOT NULL default '',
					  `specialisation` tinytext NOT NULL,
					  `notes` text NOT NULL,
					  `ajoute_par` smallint(6) NOT NULL default '0',
					  `ajoute_le` bigint(14) NOT NULL default '0',
					  `harmonise` set('0','1') NOT NULL default '0',
					  `onyxia` set('0','1') NOT NULL default '0',
					  `blackwing` set('0','1') NOT NULL default '0',
					  `inactive` set('0','1') NOT NULL default '0',
					  PRIMARY KEY  (`id_joueur`),
					  UNIQUE KEY `nom` (`nom`)
					) TYPE=MyISAM AUTO_INCREMENT=39 ;
				";
				
		$sql[] = "
					INSERT INTO `".prefix('joueurs',$db)."` ( `id_joueur` , `nom` , `rank`, `pass`, `classe` , `race` , `niveau` , `guilde` , `specialisation` , `notes` , `ajoute_par` , `ajoute_le` , `harmonise` , `onyxia` )
					VALUES (
					'1', 'Admin', '2', 'admin', '', '', '60', '', '', '', '0', '0', '0', '0'
					);
				";

		$sql[] = "	
					CREATE TABLE `".prefix('liens',$db)."` (
					  `id_inst` smallint(6) NOT NULL default '0',
					  `id_joueur` smallint(6) NOT NULL default '0',
					  `id_leader` smallint(6) NOT NULL default '0',
					  `is_lead` set('0','1') NOT NULL default '0',
					  PRIMARY KEY  (`id_inst`,`id_joueur`)
					) TYPE=MyISAM;
				";
		$sql[] = "
					CREATE TABLE `".prefix('messages',$db)."` (
					  `id_mess` mediumint(9) NOT NULL auto_increment,
					  `id_joueur` mediumint(9) NOT NULL default '0',
					  `id_inst` smallint(6) NOT NULL default '0',
					  `mess_titre` varchar(200) NOT NULL default '',
					  `mess_data` text NOT NULL,
					  `mess_date` bigint(14) NOT NULL default '0',
					  `edited` bigint(14) NOT NULL default '0',
					PRIMARY KEY ( `id_mess` ) ,
					INDEX ( `id_joueur` ),
					KEY `id_inst` (`id_inst`)
					) TYPE=MyISAM;
				";

		$sql[] = "
				CREATE TABLE `".prefix('forum',$db)."` (
				`id_mess` mediumint( 9 ) NOT NULL AUTO_INCREMENT ,
				`id_file` mediumint( 9 ) NOT NULL default '0',
				`titre` varchar( 200 ) NOT NULL default '',
				`data` text NOT NULL ,
				`date` bigint( 14 ) NOT NULL default '0',
				`poster` mediumint( 9 ) NOT NULL default '0',
				`last_reply_id` mediumint( 9 ) NOT NULL default '0',
				`edited` bigint( 14 ) NOT NULL default '0',
				`read_by` varchar( 255 ) NOT NULL default '',
				PRIMARY KEY ( `id_mess` ) ,
				KEY `id_file` ( `id_file` )
				) TYPE = MYISAM AUTO_INCREMENT =17
				";
		$sql[] = "
				CREATE TABLE `".prefix('drops',$db)."` (
				  `id_drop` mediumint(9) NOT NULL auto_increment,
				  `id_objet` mediumint(9) NOT NULL ,
				  `id_joueur` mediumint(9) NOT NULL default '0',
				  `id_inst` mediumint(9) NOT NULL default '0',
				  `drop_dkp` float NOT NULL default '0',
				  PRIMARY KEY  (`id_drop`),
				  INDEX ( `id_objet` ),
				  KEY `id_joueur` (`id_joueur`,`id_inst`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=38 ;
				";
				
		$sql[] = "
				CREATE TABLE `".prefix('dkps_raisons',$db)."` (
				  `id_raison` mediumint(9) NOT NULL auto_increment,
				  `raison` varchar(255) NOT NULL default '',
				  PRIMARY KEY  (`id_raison`)
				) TYPE=MyISAM AUTO_INCREMENT=4 ;
				";
	
		$sql[] = "
				CREATE TABLE `".prefix('dkps',$db)."` (
				  `id_ajust` mediumint(9) NOT NULL auto_increment,
				  `id_joueur` mediumint(9) NOT NULL default '0',
				  `id_inst` mediumint(9) NOT NULL default '0',
				  `id_raison` mediumint(9) NOT NULL default '0',
				  `ajustement` float NOT NULL default '0',
				  `id_leader` smallint(6) NOT NULL default '0',
				  `date_ajust` bigint(14) NOT NULL default '0',
				  PRIMARY KEY  (`id_ajust`)
				) TYPE=MyISAM AUTO_INCREMENT=4 ;
				";
				
		$sql[] = "	
			CREATE TABLE `".prefix('objets',$db)."` (
				`id_objet` MEDIUMINT NOT NULL AUTO_INCREMENT ,
				`objet_nom` VARCHAR( 255 ) NOT NULL ,
				`objet_dkp` FLOAT NOT NULL ,
				`img` VARCHAR( 255 ) NOT NULL ,
				`car` MEDIUMTEXT NOT NULL ,
				`remarque` MEDIUMTEXT NOT NULL ,
				`thottbot` varchar(255) NOT NULL default '',
				`wowdbu` varchar(255) NOT NULL default '',
				`allakhazam` varchar(255) NOT NULL default '',
				`added_by` MEDIUMINT NOT NULL ,
				`dropped_by` TINYINT NOT NULL ,
				PRIMARY KEY ( `id_objet` ) ,
				UNIQUE (
				`objet_nom`
				)
				);
				";	
			
			$sql[] = "		
				CREATE TABLE `".prefix('dispo',$db)."` (
				  `id_joueur` mediumint(9) NOT NULL default '0',
				  `id_inst` mediumint(9) NOT NULL default '0',
				  `dispo` tinyint(1) NOT NULL default '0',
				  `dispo_date` bigint(14) NOT NULL default '0',
				  PRIMARY KEY  (`id_joueur`,`id_inst`)
				) TYPE=MyISAM;
			";
			
			$sql[] = "
				CREATE TABLE `".prefix('wishlist',$db)."` (
				  `id_joueur` mediumint(9) NOT NULL default '0',
				  `id_objet` mediumint(9) NOT NULL default '0',
				  `poids` tinyint(4) NOT NULL default '0',
				  `wish_date` bigint(14) NOT NULL default '0',
				  PRIMARY KEY  (`id_joueur`,`id_objet`)
				) TYPE=MyISAM;
			";
			
			$sql[] = "
				CREATE TABLE `".prefix('boss',$db)."` (
				  `id_boss` tinyint(4) NOT NULL auto_increment,
				  `boss_nom` varchar(100) NOT NULL default '',
				  `boss_inst` tinyint(4) NOT NULL default '0',
				  PRIMARY KEY  (`id_boss`),
				  UNIQUE KEY `boss_nom` (`boss_nom`)
				) TYPE=MyISAM ;
			";
			
			$sql[] = "
				CREATE TABLE `".prefix('mails',$db)."` (
				  `id_joueur` mediumint(9) NOT NULL default '0',
				  `id_inst` smallint(6) NOT NULL default '0',
				  `id_lead` mediumint(9) NOT NULL default '0',
				  `mail_date` bigint(14) NOT NULL default '0',
				  PRIMARY KEY  (`id_joueur`,`id_inst`),
				  KEY `id_lead` (`id_lead`)
				) TYPE=MyISAM;
			";
			
			$sql[] = "
								INSERT INTO `servers_config` ( `id_server` , `prefix` , `forum_link` , `instances` , `langue` )
								VALUES (
								'', '".$database[$db]['prefix']."', 'forum/', 'Coeur de Magma;Antre d\'Onyxia;Seigneur Kazzak;Azuregos;Repaire de l\'Aile-Noire;Vallée d\'Alterac;', 'fr'
								);
								";
								
								
				foreach($sql as $s)					
					mysql_query($s) or die ("ERROR :<br>$s<br><br>".mysql_error());
					
			
			}
?>

DB créee