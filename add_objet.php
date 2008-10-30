<?
//////////////////////////////////////////////
// add_objet.php
// Script d'ajout d'objet (loot) à la base
// Appel d'une fonction (wowhead();)
// NE PAS MODIFIER
// Version du 21/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 21/10/2008: Nettoyage du code, première version 

//sécurité
$GLOBALS['allowed'] = 1;

//includes
include "commun.inc.php";

//Initialisation des variables
$load 	= null;

//Si des données sont envoyées par le formulaire
if(isset($_REQUEST['action']))
{	
	//identifiant de l'objet à ajouter envoyé par formulaire
	$id_obj = intval($_POST['idobj']);
	
	//appel de la fonction de parsing des pages wowhead (fr)
	$result=wowhead($id_obj);
	
	//redirection
	$load = $root."/add_objet.php";
}
	
//Si redirection
if($load)
{
	include 'header.php';
		echo '<big>'.$result.'</big><br><meta http-equiv="Refresh" content="2; url='.$load.'" /><a href='.$load.'>Si la page ne se recharge pas cliquer sur ce lien.</a>';
	include 'footer.php';
	exit;
}

//En tête de page
include 'header.php';

//Corp de page
?>
<big>AJOUT DE BUTIN</big>
<table style=width:600px;>
	<form name=add_obj_form action=add_objet.php method=POST>
		<input type=hidden name=action value=add>
			<tr>
				<th style=width:40% colspan=2 >Id de l'objet</th>
				<td align=center>
					<input type=text name=idobj style=width:90% value="<?=$edit['objet_nom']?>">
				</td>
			</tr>
			<tr>
				<td colspan=2 align=center>
					<input type=submit value=Ajouter>
				</td>
			</tr>
	</form>
</table>
<br>
<br>
<?
//Pied de page
include 'footer.php';
?>