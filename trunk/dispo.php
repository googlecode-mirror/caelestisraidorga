<?
//////////////////////////////////////////////
// dispo.php
// Script de gestion de ses dispos
// NE PAS MODIFIER
// Version du 21/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 21/10/2008: Nettoyage du code, première version 
// 24/10/2008 - Joyrock : Ajout des logs

//Sécurité
$GLOBALS['allowed'] = 1;

//includes
include "commun.inc.php";

if($_POST['dispo'])
{
	$d = $_POST['dispo'];
	
	// On ne supprime que les entrée des instances encore active, on garde une trace des dispos pour les anciennes instances.
	mysql_query("
		DELETE D.*
		FROM (".prefix('instances')." I)
		LEFT JOIN ".prefix('dispo')." D ON D.id_inst=I.id_inst AND D.id_joueur = '".$id."'
		WHERE date > '".date("YmdHis",time()-3600)."'
		") or die("Erreur mysql - dispo.php:23 : ".mysql_error());
	
	//On  met à jour les dispos en base
	$key = array_keys($d);
	foreach($key as $k)
	{
		if($d[$k])
		{
		mysql_query("
			INSERT INTO ".prefix('dispo')." 
			(`id_joueur`,`id_inst`,`dispo`,`dispo_date`) 
			VALUES
			('$id','".intval($k)."','".intval($d[$k])."','".date("YmdHis",time())."')
			") or die("Erreur mysql - carac_joueur.php:34 : ".mysql_error());
		}
		
		//Si statut ABS, on s'assure que le joueur n'est pas dans la LU du jour
		if($d[$k]==3)
		{
			mysql_query("
				DELETE FROM ".prefix('liens')."
				WHERE (id_inst = '$k' AND id_joueur = '$id')
				") or die("Erreur mysql - carac_joueur.php:42 : ".mysql_error());
		}
	}
	
	//On log
	add_log($user_name,"Dispo","Mise à jour des dispos");
}

//On récupère les infos en base
$d_ = mysql_query("
	SELECT *, I.id_inst id, D.dispo
	FROM (".prefix('instances')." I)
	LEFT JOIN ".prefix('dispo')." D ON D.id_inst=I.id_inst AND D.id_joueur = '$id'
	WHERE date > '".date("YmdHis",time()-3600)."'
	ORDER BY -date
	") or die("Erreur mysql - carac_joueur.php:53 : ".mysql_error());

$instances = array();
while($d = mysql_fetch_assoc($d_))
{
	$instances[] = $d;
}

//En-tête
include "header.php";
?>
<big>Disponibilité, <?=$base['display']?></big><br><br>
<table style=width:500px cellspacing=0>
	<form action=dispo.php method=POST>
		<tr>
			<th style=width:30%>Instance</th>
			<th style=width:43%>Date</th>
			<th>Disponibilité</th>
		</tr>
		<?
		$k=0;
		foreach($instances as $i)
		{
			echo "
				<tr ".($k%2 ? 'class=odd':'').">
					<td>
						".($rank ? $i['inst_nom'] : "Instance du")."
					</td>
					<td>
						le ".aff_date($i['date'])."
					</td>
					<td align=center>
						<select name='dispo[".$i['id']."]' style=width:90%>
					";
				for($j=0;$j<count($dispo);$j++)
				{
					echo "<option value='".$j."' ".($i['dispo']==$j?'SELECTED':'').">".$dispo[$j]."</option>";
				}
			
			echo "
						</select>
					</td>
				<tr>";
			$k++;
		}
	?>
		<tr>
			<td colspan=2>&nbsp;</td>
			<td align=center><input type=submit value=Valider style=width:90%></td>
		</tr>
	</form>
</table>
<?
//Pied de page
include "footer.php";
?>