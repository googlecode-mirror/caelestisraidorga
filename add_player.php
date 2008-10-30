<?
//////////////////////////////////////////////
// add_player.php
// Script de gestion des utilisateurs (création et mise à jour)
// Appel : maj_player.php
// NE PAS MODIFIER
// Version du 29/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 21/10/2008: Nettoyage du code, première version 
// 29/10/2008: Suppression de la MAJ du mot de passe

//include
include "commun.inc.php";

//On édite un profil
if($_REQUEST['edit'])
{

	//Récupération de l'id du joueur à modifier passé par url puis requête afin de charger ses informations à l'écran
	$id_edit = intval($_REQUEST['edit']);
	$edit_ 	= mysql_query("
							SELECT J.*
							FROM (".prefix('joueurs')." J) 
							WHERE J.id_joueur='$id_edit'
						") or die("Erreur mysql - add_player.php:20 : ".mysql_error());
	$edit	= mysql_fetch_assoc($edit_) or die("Erreur mysql - add_player.php:25 : ".mysql_error());
	
	//Vérification des droits de l'utilisateur, si pas de droits on sort.
	if(($edit['rank']>=$rank) and ($id <> $id_edit)) 
	{
		$edit=array();
		echo "Vous n'avez pas la permission de le faire.";
		exit;
	}
}

// On commence à construire la page
//En-tête
include 'header.php';

//corp de page
?>

<div align=center>
	<big><?=($edit['nom'] ? 'EDITER LE JOUEUR <i>'.$edit['nom'].'</i>' : 'AJOUTER UN JOUEUR')?></big><br>
	
	<table style=width:400px;>
		<form action=maj_player.php method=POST>
			<tr>
				<td>Nom</td>
				<td style=width:60%;><input type=text name=nom maxlenght=150 value='<?=$edit['nom']?>'></td>
			</tr>
			<tr>
				<td>Classe</td>
				<td><select name=classe style=width:90%><?$i=0;foreach($classe as $c){echo "<option value=$i ".($c==$edit['classe'] ?'SELECTED':'').">$c</option>";$i++;}?></select></td>
			</tr>
			<tr>
				<td>Race</td>
				<td><select name=race style=width:90%><?$i=0;foreach($race as $r){echo "<option value=$i ".($r==$edit['race'] ?'SELECTED':'').">$r</option>";$i++;}?></select></td>
			</tr>
			<tr>
				<td>Niveau</td>
				<td><select name=lvl style=width:90%><?for($i=80;$i>0;$i--){echo "<option ".($i==$edit['niveau'] ?'SELECTED':'').">$i</option>";}?></select></td>
			</tr>
			<tr>
				<td>Guilde</td>
				<td><input type=text name=guilde maxlenght=150 value='<?=$edit['guilde']?>'></td>
			</tr>
			<tr>
				<td>Spécialisation</td>
				<td><textarea name=specialisation><?=$edit['specialisation']?></textarea></td>
			</tr>
			<tr>
				<td>Notes</td>
				<td><textarea name=notes></textarea></td>
			</tr>
			
			
			<?
			//Vérification des droits de l'utilisateur, si pas de droits on sort.
			if(!$_REQUEST['edit'])
			{?>
			<tr>
				<td>Mot de passe :
				</td>
				<td><input type=password name=pass maxlenght=20 value=<?=$edit['pass']?>>
				</td>
			</tr>
			<?}?>
			
			<tr>
				<td>Rang :</td>
				<td>
					<select name=rank <?=($rank > $edit['rank'] ? '':'DISABLED')?> style=width:90%>
						<option value=0><?echo $ranks[0];?></option>
						<option value=1 <?=($edit['rank']==1?'SELECTED':'')?>><?echo $ranks[1];?></option>
						<option value=2 <?=($edit['rank']==2?'SELECTED':'')?>><?echo $ranks[2];?></option>
						<option value=3 <?=($edit['rank']==3?'SELECTED':'')?>><?echo $ranks[3];?></option>
						<option value=4 <?=($edit['rank']==4?'SELECTED':'')?>><?echo $ranks[4];?></option>
					</select>
				</td>
			</tr>
			
			<?
			//Vérification des droits de l'utilisateur, si pas de droits on sort.
			if(($edit['rank'] < $rank) and ($rank>=3))
			{?>
			<tr>
				<td>Désactiver
				</td>
				<td><input type=checkbox name=inactive value=1 <?=($edit['inactive'] ? 'CHECKED':'')?>>
				</td>
			</tr>
			<?}?>
			
			<tr>
				<td align=center colspan=2>
					<input type=submit value=<?=($edit['nom'] ? 'Editer' : 'Ajouter')?>>
				</td>
			</tr>
			<input type=hidden name=edit value=<?=$edit['id_joueur']?>>
		</form>
	</table>
</div>
<?
//Pied de page
include 'footer.php';
?>