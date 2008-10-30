<?
//////////////////////////////////////////////
// commun.inc.php
// Fichier appelé par toutes les pages de l'application
// NE PAS MODIFIER
// Version du 21/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 21/10/2008: Nettoyage du code, première version 
// 24/10/2008 - Joyrock : Ajout des logs

//On utilise le charset UTF-8
Header('Content-Type: text/html; charset=utf-8');

//Variables locales en  français
setlocale(LC_ALL, 'fr_FR', 'fr', 'fr_FR@euro');

//Les includes
if (!file_exists('config.inc.php'))
{
	die("<p>Le fichier de configuration config.inc.php ne peut être trouvé.</p>");
}
require('config.inc.php');

include	'function.inc.php';
include	'language.php';

if(!mysql_table_exists("servers_config"))
{
	die("<p>Les tables SQL n'existent pas dans la base ".$db_name."</p><p><a href='create_table.php'>Cliquez ici pour créer les tables.</a></p>");
}


//Compatibilité PHP4 et PHP5pour les librairies PHP de gestion XML
// http://alexandre.alapetite.net/doc-alex/domxml-php4-php5/index.en.html
if (version_compare(PHP_VERSION,'5','>=')) require_once('domxml-php4-to-php5.php');

//Initialisation de la session
$rank 		= null;
$user_name 	= null;
$userguild	= null;
$id 		= null;
$conf		= null;

//Si la session n'est pas initiée alors...
if (!headers_sent() && !$_SESSION)
{

	//On ouvre la session
	session_name('WOWORGA');
	session_start();
	
	//Si on est pas connecté, redirection vers la page de login
	if ((!$_SESSION['rank'] && $GLOBALS['allowed']!==1)||!$_SESSION['isConnected'])
	{
		header("Location: $root/login.php");
		exit;
	}
	
	//Récupération des informations de session en local
	$rank 		= $_SESSION['rank'];
	$user_name 	= $_SESSION['username'];
	$userguild	= $_SESSION['userguild'];
	$id 		= $_SESSION['id'];
	$conf		= $_SESSION['conf'];
	
	//Si la case "se souvenir" est cochée à l'autentification on créé un cookie
	if($_SESSION['remember_me'])
	{
		//setcookie('WOWORGA', $_REQUEST["WOWORGA"], time()+3600*24*7);
	}
	
	//On libère la session
	session_write_close();
}

//Nettoyage
$keys = array_keys($_POST);
foreach($keys as $k) $_POST[$k] = MyStripSlashes($_POST[$k]);
$keys = array_keys($_GET);
foreach($keys as $k) $_GET[$k] = MyStripSlashes($_GET[$k]);
$keys = array_keys($_REQUEST);
foreach($keys as $k) $_REQUEST[$k] = MyStripSlashes($_REQUEST[$k]);


//Si on a configuré une liste d'instance via l'admin de l'application on la récupère, sinon on utilise celle définie dans le fichier de configuration.
if(isset($conf['instances']))
{
	$instance 	= explode(';',$conf['instances']);
}
?>