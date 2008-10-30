<?
//////////////////////////////////////////////
// maj_player.php
// Script de mise en base des infos utilisateur
// Appelé par: add_player.php
// NE PAS MODIFIER
// Version du 29/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 21/10/2008: Nettoyage du code, première version 
// 29/10/2008: Suppression de la MAJ du mot de passe

//includes
include "commun.inc.php";

//On a les données
if($_POST['nom'])
{
	
	//Récupération des données passées par formulaire
	$nom = $_POST['nom'];
	$cla = $classe[$_POST['classe']];
	$rac = $race[$_POST['race']];
	$gui = addslashes($_POST['guilde']);
	$spe = addslashes($_POST['specialisation']);
	$not = addslashes($_POST['notes']);
	$lvl = min(80,max(1,intval($_POST['lvl'])));
	$rk	 = min(intval($_POST['rank']),$rank);
	$pass = addslashes($_POST['pass']);
	$ina = intval($_POST['inactive']) ? 1 : 0;
	
	//On crée un nouveau user
	if(!$_POST['edit'])
	{
		mysql_query("
			INSERT INTO ".prefix('joueurs')." (nom,classe,rank,niveau,pass,race,ajoute_par,ajoute_le,guilde,specialisation,notes) 
			VALUES ('".$nom."','".$cla."','".$rk."','".$lvl."','".$pass."','".$rac."','".$id."','".date("YmdHis",time())."','".$gui."','".$spe."','".$not."')"
			) or die ("Erreur mysql - maj_player.php:38 : ".mysql_error());
		
		$msg['message'] = "$nom ajouté !";
	}
	//On edite un user existant
	else
	{
		//On récupère l'id du user à modifier
		$id_edit = intval($_POST['edit']);
		
		//On récupère ses infos en base
		$edit_ 	= mysql_query("
			SELECT J.* 
			FROM (".prefix('joueurs')." J) 
			WHERE J.id_joueur='".$id_edit."'"
			) or die ("Erreur mysql - maj_player.php:53 : ".mysql_error());
		$edit	= mysql_fetch_assoc($edit_) or die ("Erreur mysql - maj_player.php:54 : ".mysql_error());;
		
		mysql_query("
			UPDATE ".prefix('joueurs')."
			SET 
			nom 			= '".$nom."',
			rank			= '".$rk."',
			race 			= '".$rac."',
			niveau			= '".$lvl."',
			classe			= '".$cla."',
			guilde			= '".$gui."',
			specialisation	= '".$spe."',
			notes			= '".$not."',
			inactive		= '".$ina."'
			WHERE id_joueur='".$id_edit."'
			") or die("Erreur mysql - maj_player.php:80 : ".mysql_error());
		
		$msg['message'] = "$nom édité,<a href=info_player.php> retour à la liste</a>.";
	}
}

//On reset les données une fois utilisées
unset($_POST);

//En-tête
include 'header.php';

echo "<big>Action effectuée, merci.</big>";

//Pied de page
include 'footer.php';
?>