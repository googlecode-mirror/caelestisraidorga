<?
//////////////////////////////////////////////
// blacklist.php
// Script de gestion de la blacklist
// NE PAS MODIFIER
// Version du 21/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:

//includes
include "commun.inc.php";

//On ban
if($_POST['to_black'])
{
	//Récupération des info du formulaire et nettoyage
	$raison 	= addslashes(strip_tags($_POST['raison']));
	$to_black 	= intval($_POST['to_black']);
	
	//S'il manque la raison du ban on met un message d'erreur
	if(!$raison)
	{
		$msg['erreur'] = 'Vous devez donner une raison pour blacklister un joueur.';
	}
	else
	{
	//Sinon on ajoute le ban à la base
		mysql_query("
			INSERT INTO ".prefix('blacklist')." (id_joueur,raison,blacklisted_by) 
			VALUES ('$to_black','$raison','$id')
			") or die("Erreur mysql - blacklist.php:27 : ".mysql_error());
	}
}

//On deban
if($_GET['unban'])
{
	//Récupération des info du formulaire 
	$unban = intval($_GET['unban']);
	
	//On supprime le ban de la base
	mysql_query("
		DELETE FROM ".prefix('blacklist')." 
		WHERE id_joueur='$unban'
		") or die("Erreur mysql - blacklist.php:42 : ".mysql_error());
}

//On récupère la liste des bannis
$d_ = mysql_query("
			SELECT J.*, B.id_joueur blacked, B.raison, B.blacklisted_by
			FROM (".prefix('joueurs')." J) 
			LEFT JOIN ".prefix('blacklist')." B ON B.id_joueur=J.id_joueur
			ORDER BY nom;
		") or die("Erreur mysql - blacklist.php:49 : ".mysql_error());

//On initialise les listes
$joueur		= array(); // Liste des joueurs
$blacklist 	= array(); // Liste des bannis
$liste		= array(); //Liste des non bannis

//Pour chaque bannis
while($d = mysql_fetch_assoc($d_))
{	
	//On stocke le joueur dans la liste des joueurs
	$joueur[$d['id_joueur']] = $d;
	
	//On ne peux ban que des users de rang inférieur
	if($d['rank']<$rank)
	{
		//Il  est bannis -> dans la liste des bannis
		if($d['blacked'])
		{
			$blacklist[] = $d['id_joueur'];
		}
		//Sinon dans la liste des users selectionnable
		else
		{
			$liste[$d['id_joueur']] = $d;
		}
	}
}

//En tête de la page
include 'header.php';

//Corp de la page
?>
<table cellspacing=0 style=width:700px;>
	<tr>
		<th style=width:150px>Nom</th>
		<th>Raison</th>
		<th>Banni par</th>
		<th>&nbsp;</th>
	</tr>
<?
$i=0;
foreach($blacklist as $b)
{
	$raison=$joueur[$b]['raison'];
	echo "
	<tr ".($i%2 ? 'class=odd':'').">
		<td><b>".$joueur[$b]['nom']."</b></td>
		<td>".$raison."</td>
		<td>".$joueur[$joueur[$b]['blacklisted_by']]['nom']."</td>
		<form action=blacklist.php?unban=".$joueur[$b]['id_joueur']." method=POST>
			<td style=width:100px>
				<input type=submit value=Débannir style=width:100px>
			</td>
		</form>
	</tr>";
$i++;
}
?>
	<tr>
		<form action=blacklist.php method=POST>
			<td colspan=4 align=center>
				<br><br>Ajouter 
				<select name=to_black >
					<option>Choisir un nom</option>
					<?
					foreach($liste as $j) echo"<option value=".$j['id_joueur'].">".$j['nom']."</option>";
					?>
				</select> 
				à la blacklist pour le motif suivant : <br>
				<textarea name=raison rows=8></textarea>
				<input type=submit value=Bannir>
			</td>
		</form>
	</tr>
</table>
<?

//Pied de page
include 'footer.php';
?>