<?
//////////////////////////////////////////////
// add_instance.php
// Script de création de raid
// NE PAS MODIFIER
// Version du 21/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:

//includes
include "commun.inc.php";

//Initialisation des variables
$annees  	= range(date('Y',time()),date('Y',time())+1);
$mois		= range(1,12);
$jours		= range(1,31);
$heures		= range(0,23);
$minutes	= array(0,15,30,45);

//On trie le tableau des instances récupérées dans commun.inc.php (config de l'application)
asort($instance);

//Si on a une instance sélectionnée et envoyée par formulaire
if(isset($_POST['instance']))
{
	//On formate la date
	$date = date("YmdHis",mktime($heures[$_POST['heures']],$minutes[$_POST['minutes']],0,$mois[$_POST['mois']],$jours[$_POST['jours']],$annees[$_POST['annees']]));
	
	//Type de l'instance (raid 10, 25...)
	$i_type = max(0,min(count($inst_type)-1,intval($_POST['inst_type'])));
	
	//On met en base
	mysql_query("
		INSERT INTO ".prefix('instances')." (inst_orga,inst_type,date,inst_nom) 
		VALUES ('$id','$i_type','$date','".addslashes($instance[$_POST['instance']])."')
		") or die("Erreur mysql - add_instance.php:36 : ".mysql_error());
	$msg['message'] = "Instance Créee avec Succès ! <a href=index.php>RETOUR</a>";
}

//En tête de la page
include 'header.php';

//Corp de page
?>
<div align=center>
	<big>ORGANISATION D'UNE INSTANCE</big><br><br>
	<table style=width:500px;>
		<form action=add_instance.php method=POST>
			<tr>
				<td>Instance</td>
				<td><select name=instance><?foreach($instance as $i=>$n){echo "<option value=$i>$n</option>";}?></select></td>
			</tr>
			<tr>
				<td>En</td>
				<td><select name=inst_type><?foreach($inst_type as $i=>$n){echo "<option value=$i>$n</option>";}?></select></td>
			</tr>
			<tr>
				<td>Date</td>
				<td>
					Le <select name=jours><?$i=0;foreach($jours as $n){echo "<option value=$i ".(date('d',time())==$n ? 'SELECTED' :'').">".sprintf('%02s',$n)."</option>";$i++;}?></select> /
					<select name=mois><?$i=0;foreach($mois as $n){echo "<option value=$i ".(date('m',time())==$n ? 'SELECTED' :'').">".sprintf('%02s',$n)."</option>";$i++;}?></select> /
					<select name=annees><?$i=0;foreach($annees as $n){echo "<option value=$i ".(date('Y',time())==$n ? 'SELECTED' :'').">$n</option>";$i++;}?></select> à 
					<select name=heures><?$i=0;foreach($heures as $n){echo "<option value=$i ".(20==$n ? 'SELECTED' :'').">".sprintf('%02s',$n)."</option>";$i++;}?></select>H
					<select name=minutes><?$i=0;foreach($minutes as $n){echo "<option value=$i>".sprintf('%02s',$n)."</option>";$i++;}?></select>
				</td>
			</tr>
			<tr>
				<td colspan=2 align=center>
					<input type=submit value=Créer>
				</td>
			</tr>
		</form>
	</table>
</div>
<?

//Pied de page
include 'footer.php';
?>