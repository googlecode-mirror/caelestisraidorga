<?
//////////////////////////////////////////////
// help.php
// Aide de l'application
// NE PAS MODIFIER
// Version du 21/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:

//sécurité
$GLOBALS['allowed'] = 1;

//includes
include 'commun.inc.php';
include 'header.php';
?>
<!--
<img src=images/icones/index.jpg>
<img src=images/icones/player.jpg>
<img src=images/icones/butin.jpg>
<img src=images/icones/dkp.jpg>
<img src=images/icones/orga.jpg>
<br>
<br>
-->

<?if ($rank<3){?>
<table style=width:90%;border:0px>
	<tr>
		<td width=60px><img src=images/icones/index.jpg><br>
		</td>
		<td align=justify>
			<big>Instances</big><br><br>
			Cette page référence toutes les instances passées, présentes et futures qui ont été créees.<br>
			A partir de là, en cliquant sur le nom de l'instance, vous pouvez atteindre la page récapitulative de l'instance.<br><br>
			<u>Vous avez sur ce tableau :</u><br>
			- La composition de chaque classe.<br>
			- A gauche, la liste des joueurs qui sont passés sur le site pour signaler leur disponibilité, et n'ont pas encore été sélectionnés.<br>
			- A droite, la liste des butins accumulés lors de la sortie.<br>
			<br>
			<br>
		</td>
	</tr>
	<tr>
		<td width=60px><img src=images/icones/butin.jpg>
		</td>
		<td align=justify>
			<big>Butin</big><br><br>
			Cette page référence la liste des objets gagnés sur les instances. Les objets apparaitront autant de fois qu'ils ont été gagnés. 
			Si vous souhaitez voir une liste axée plutôt sur les objets que sur l'aspect "Gain", cliquez sur "<i>Liste des Objets</i>" en haut.<br>
			<br>
			<br>
			<br>
		</td>
	</tr>
	<tr>
		<td width=60px><img src=images/icones/orga.jpg>
		</td>
		<td align=justify>
			<big>Disponibilité</big><br><i>Tous</i><br><br>
			Cette page, commune aux leaders comme aux simples joueurs, permet de donner ses disponibilités sur les instances à venir.<br>
			Les disponibilités sur les instances passées ne sont pas conservées.
			<br>
			<br>
			<br>
		</td>
	</tr>
	<tr>
		<td width=60px><img src=images/icones/wishlist.jpg>
		</td>
		<td align=justify>
			<big>WishList (Liste de Souhaits)</big><br><i>Tous</i><br><br>
			Petit plus, que vous pouvez utiliser ou non.<br>
			La wishlist permet à tout les joueurs de spécifier quels objets ils aimeraient VRAIMENT avoir, par ordre de préférence.
			Cela peut permettre d'organiser un peu à l'avance la répartition des butins, et c'est donc une alternative intéressante au SK.<br><br>
			Cette page permet également aux joueurs d'ajouter un objet qui ne serait pas présent dans la liste de votre raid.<br><br>
			La wishlist d'un joueur n'est visible que par les officiers/sages/admin (via la fiche du joueur) et lui-même.
			<br>
			<br>
			<br>
		</td>
	</tr>
</table>
<?}
else {
?>
<center><big><b><u>Officiers</u></b></big></center>
<table style=width:90%;border:0px>
	<tr>
		<td width=60px><img src=images/icones/index.jpg><br>
		</td>
		<td align=justify>
			<big>Instances</big><br><br>
			Cette page référence toutes les instances passées, présentes et futures qui ont été créees.<br>
			A partir de là, en cliquant sur le nom de l'instance, vous pouvez atteindre la page récapitulative de l'instance.<br><br>
			<u>Vous avez sur ce tableau :</u><br>
			- La composition de chaque classe.<br>
			- A gauche, la liste des joueurs qui sont passés sur le site pour signaler leur disponibilité, et n'ont pas encore été sélectionnés.<br>
			- A droite, la liste des butins accumulés lors de la sortie.<br>
			<br>
			<br>
		</td>
	</tr>
	<tr>
		<td width=60px><img src=images/icones/butin.jpg>
		</td>
		<td align=justify>
			<big>Butin</big><br><br>
			Cette page référence la liste des objets gagnés sur les instances. Les objets apparaitront autant de fois qu'ils ont été gagnés.
			Si vous souhaitez voir une liste axée plutôt sur les objets que sur l'aspect "Gain", cliquez sur "<i>Liste des Objets</i>" en haut.<br>
			<br>
			<br>
			<br>
		</td>
	</tr>
	<tr>
		<td width=60px><img src=images/icones/dkp.jpg>
		</td>
		<td align=justify>
			<big>Statistiques</big><br><br>
			Il s'agit d'une manière de visualiser l'assiduité des vos membres à la participation aux raid et à la mise à jour de leurs dispo sur le Raid Orga.
			<br>
			<br>
			<br>
		</td>
	</tr>
	<tr>
		<td width=60px><img src=images/icones/orga.jpg>
		</td>
		<td align=justify>
			<big>Disponibilité</big><br><i>Tous</i><br><br>
			Cette page, commune aux leaders comme aux simples joueurs, permet de donner ses disponibilités sur les instances à venir.<br>
			Les disponibilités sur les instances passées ne sont pas conservées.
			<br>
			<br>
			<br>
		</td>
	</tr>
	<tr>
		<td width=60px><img src=images/icones/wishlist.jpg>
		</td>
		<td align=justify>
			<big>WishList (Liste de Souhaits)</big><br><i>Tous</i><br><br>
			Petit plus, que vous pouvez utiliser ou non.<br>
			La wishlist permet à tout les joueurs de spécifier quels objets ils aimeraient VRAIMENT avoir, par ordre de préférence.
			Cela peut permettre d'organiser un peu à l'avance la répartition des butins, et c'est donc une alternative intéressante au SK.<br><br>
			Cette page permet également aux joueurs d'ajouter un objet qui ne serait pas présent dans la liste de votre raid.<br><br>
			La wishlist d'un joueur n'est visible que par les officiers/sages/admin (via la fiche du joueur) et lui-même.
			<br>
			<br>
			<br>
		</td>
	</tr>
</table>
<?
}
?>

<?
include 'footer.php';
?>