<?
//////////////////////////////////////////////
// login.php
// Page d'identification
// NE PAS MODIFIER
// Version du 28/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 21/10/2008: Nettoyage du code, première version 
// 24/10/2008 - Joyrock : Ajout des logs
// 28/10/2008 - Joyrock : Correction d'un bug ie
// 30/10/2008 - Joyrock : Modification suite à script d'installation.

//On utilise le charset UTF-8
Header('Content-Type: text/html; charset=utf-8');

//includes
include	'config.inc.php';
include 'function.inc.php';

//On récupère len base le paramétrage de l'application
$conf = mysql_query("SELECT * FROM servers_config WHERE prefix='".$db_prefix."'") or die("Erreur mysql - login.php:34 : ".mysql_error());
$conf = mysql_fetch_assoc($conf);

$test_install = mysql_query("SELECT max(id_joueur) as IdMax FROM ".prefix('joueurs',$db_name)) or die("Erreur mysql - login.php:21 : ".mysql_error());
$test_install = mysql_fetch_assoc($test_install);
if($test_install['IdMax']==1)
{
$erreur = "Vous venez d'installer le gestionnaire de raid.<br>Votre login: Admin (attention à la majuscule), pas de mot de passe.";
}

//On a cliqué sur "Login"
if($_REQUEST['login'])
{
   	
	//On nettoye
	$name = addslashes($_REQUEST['login']);
	$pass = addslashes($_REQUEST['pass']);
	
	//On récupère les informations du user en base
	$d = mysql_query("SELECT id_joueur, rank, nom, guilde, inactive FROM ".prefix('joueurs',$db_name)." WHERE nom='$name' AND pass='$pass'") or die("Erreur mysql - login.php:23 : ".mysql_error());
	$d = mysql_fetch_assoc($d);
	
	//Si le user est inactif on renvoie vers la page de login
	if($d['inactive'])
	{
		header("Location: $root/login.php");
		exit;
	}

	//On récupère len base le paramétrage de l'application
	//$conf = mysql_query("SELECT * FROM servers_config WHERE prefix='".$db_prefix."'") or die("Erreur mysql - login.php:34 : ".mysql_error());
	//$conf = mysql_fetch_assoc($conf);
	
	//Si notre user a les droits suffisant et que l'on récupère correctement le paramétrage
	if($d['rank'] && $conf['id_server'])
	{
		//On ouvre la session
		session_name('WOWORGA');
		session_start();
		
		//Si on est déjà connecté, redirection vers l'index
		if($_SESSION['isConnected']){
			header("Location: $root/index.php");
			exit;			
		}
		
		//Initialisation de la session		
		session_unset();
		$_SESSION['isConnected']	= true;
		$_SESSION['rank']			= $d['rank'];
		$_SESSION['id']				= $d['id_joueur'];
		$_SESSION['username']		= $d['nom'];
		$_SESSION['userguild']		= $d['guilde'];
		$_SESSION['conf']			= $conf;
		$_SESSION['remember_me']	= $_POST['rememberme']?true:false;
		
		//On libère la session
		session_write_close();
		
		//On log
		add_log($d['nom'],"Login","Connection");
		
		//On renvoie sur l'index
		header("Location: $root/index.php");
		exit;
	}
	//Si notre user a le rank = 0 mais que l'on récupère correctement le paramétrage
	elseif($d['id_joueur'] && $conf['id_server'])
	{
		//On ouvre la session
		session_name('WOWORGA');
		session_start();
		
		//Si déjà connecté, on redirige vers les dispos
		if($_SESSION['isConnected'])
		{
			header("Location: $root/dispo.php");
			exit;			
		}
			
		session_unset();
		$_SESSION['isConnected']	= true;
		$_SESSION['rank']			= 0;
		$_SESSION['id']				= $d['id_joueur'];
		$_SESSION['username']		= $d['nom'];
		$_SESSION['userguild']		= $d['guilde'];
		$_SESSION['conf']			= $conf;
		$_SESSION['remember_me']	= $_POST['rememberme']?true:false;

		//On libère la session
		session_write_close();
		
		//On log
		add_log($d['nom'],"Login","Connection");
		
		//On redirige
		if($pass)
		{
			header("Location: $root/dispo.php");
		}
		else
		{
			header("Location: $root/options.php");
		}
	}
	//Login et pass incorrects
	else
	{
		//On log
		add_log($name,"Login","Erreur de connection");
		
		$erreur = "Vous ne pouvez pas vous logguer sur la section <b>".$guild."</b>. Vérifiez que : <br>- Vous avez bien tapé votre mot de passe.<br>- Le login est votre nom de JOUEUR.<br>- Si c'est votre 1er passage, laissez le mot de passe vide.<br><br>Si le problème persiste, contactez votre leader.";
	}
}
elseif(isset($_REQUEST['disco']))
{
	//On ouvre la session
	session_name('WOWORGA');
	session_start();
	
	//On log
	add_log($_SESSION['username'],"Login","Déconnection");
	
	//On déconnecte
	session_unregister("isConnected");
	session_unregister("rank");
	session_unregister("id");
	session_unregister("username");
	session_unregister("userguild");
	session_unregister("conf");
	session_unregister("remenber_me");
	session_destroy();
	
	//On redirige
	header("Location: $root/login.php");
}

//On affiche la page de login
?>
<html>
<head>
	<title>Organisation d'instance, <?=$guild?></title>
	<link rel="stylesheet" type="text/css" media="all" href="<?=$root?>/style.css" />
</head>
<body>

<table width=100% style=border:0px>
	<tr>
		<td width=100% align=center>
			<img style=border:0px src="images/banniere2.jpg">
		</td>
	</tr>
	<tr>
		<td>
			<div align=center>
				<table class=login style='width:300px;margin:-2 0 0 0;' cellspacing=0 cellpadding=0>
					<form action=login.php method=POST>
						<tr>
							<th colspan=2 style=text-align:center>ACCES DES MEMBRES</th>
						</tr>
						<tr>
							<th width=40%>Nom</th>
							<td><input type=text name=login value="<?=$name?>" style=width:150px></td>
						</tr>
						<tr>
							<th>Mot de passe</th>
							<td><input type=password name=pass value="<?=$pass?>" style=width:150px></td>
						</tr>
						<tr style=font-style:italic;font-size:10px>
							<td style=text-align:right><input type=checkbox name=rememberme value=1 style=width:15px></td>
							<td style=text-align:left;vertical-align:middle>Se souvenir</td>
						</tr>
						<tr>
							<td colspan=2 align=center>
								<input type=submit value=Login>
								<br><br>
								<a style=background-color:white href="<?=$conf['forum_link']?>">Me contacter (via le forum de la guilde <?=$guild?>)</a>
							</td>
						</tr>
					</form>
					<?
					if($erreur)
					{
					?>
						<tr>
							<td style='font-size:10px;color:red;border: 2px solid red;text-align:left;' colspan=2>
								<?
								echo $erreur;
								?>
							</td>
						</tr>
					<?
					}
					?>
				</table>
			</div>
		</td>
	</tr>
</table>
</body>
</html>