<?
	$database = array();
	$i=0;

/* REQUIEM */

	$database[$i]['db'] 		= 'web1_db3'; 									//Nom de la base sur laquelle est stocké la section
	$database[$i]['display'] 	= 'Caelestis Concilium';	//Ce qu'on affiche comme nom pour cette section
	$database[$i]['prefix'] 	= 'ORGA_';									//Prefixe pour cette section dans base de donnée
	$i++;

	function prefix($table,$db=null){
		global $database;
		$db = $db===null ? (isset($_SESSION['db'])?$_SESSION['db']:$_GET['db']) : $db;
		return $database[$db]['prefix'].$table;
	}
?>