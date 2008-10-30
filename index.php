<?
//////////////////////////////////////////////
// index.php
// Page d'accueil
// NE PAS MODIFIER
// Version du 27/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 21/10/2008: Nettoyage du code, première version 
// 27/10/2008 : Joyrock - modification de la gestion de groupe

//Sécuritée
$GLOBALS['allowed'] = 1;

//includes
include "commun.inc.php";

//On récupère le mode d'affichage
$ref=$_GET['ref'];

//Initialisation de la liste des instances
$instances = array();

switch($ref)
{
	////////////////////
	//INSTANCES FUTURES
	////////////////////
	default:
	case 'futur':
		
	//Récupération de la liste des instances à venir
	$d_ = mysql_query("
		SELECT * FROM ".prefix('instances')." I, ".prefix('joueurs')." J
		WHERE I.inst_orga=J.id_joueur AND I.date>'".date('YmdHis',time()-3600*24)."'
		ORDER BY -date
	") or die ("Erreur mysql - index.php:35 : ".mysql_error());
	
	//Pour chaque instance récupérée
	while($d = mysql_fetch_assoc($d_))
	{
		//Nombre de joueurs séléctionnés
		$tmp = mysql_fetch_row(mysql_query("SELECT count(*) FROM ".prefix('liens')." WHERE id_inst='".$d['id_inst']."'"));
	
		//recherche dispo
		$tmp2_ = mysql_query("
			SELECT count(*)
			FROM ".prefix('dispo')." D
			LEFT JOIN ".prefix('liens')." L ON D.id_inst=L.id_inst AND D.id_joueur=L.id_joueur
			WHERE D.id_inst='".$d['id_inst']."' AND (dispo='2') AND id_leader IS NULL
			") or die("Erreur mysql - index.php:49 : ".mysql_error());
		$tmp2 = mysql_fetch_row($tmp2_);

		//Nombre de joueurs NC
		$tmp3_ = mysql_query("
			SELECT count(*)
			FROM ".prefix('dispo')." D
			LEFT JOIN ".prefix('liens')." L ON D.id_inst=L.id_inst AND D.id_joueur=L.id_joueur
			WHERE D.id_inst='".$d['id_inst']."' AND (dispo='1') AND id_leader IS NULL
			") or die("Erreur mysql - index.php:58 : ".mysql_error());
		$tmp3 = mysql_fetch_row($tmp3_);

		//Nombre de joueurs réservistes
		$tmp4_ = mysql_query("
			SELECT count(*)
			FROM ".prefix('dispo')." D
			LEFT JOIN ".prefix('liens')." L ON D.id_inst=L.id_inst AND D.id_joueur=L.id_joueur
			WHERE D.id_inst='".$d['id_inst']."' AND (dispo='4') AND id_leader IS NULL
			") or die("Erreur mysql - index.php:67 : ".mysql_error());
		$tmp4 = mysql_fetch_row($tmp4_);
		
		//On stock dans la liste des instances
		$d['count'] = $tmp[0];
		$d['count_dispo'] = intval($tmp2[0]);
		$d['count_nc'] = intval($tmp3[0]);
		$d['count_res'] = intval ($tmp4[0]);
		$instances[] = $d;	
	}

	//En tête de la page
	include 'header.php';

	//Corp de page
	echo "
	<big>Liste des Instances prévues, ".$guild."</big> - <small><a href=index.php?ref=passe>(Historique de la guilde)</a></small>
	<table cellspacing=0 style=margin-left:5% >
		<tr>
			<th>
				".$lang['instance']."
			</th>
			<th>
				".$lang['type']."
			</th>		
			<th>
				".$lang['date']."
			</th>
			<th>
				".$lang['organisateur']."
			</th>
			<th style=text-align:right>
				".$lang['selected_players']."
			</th>
			<th style=text-align:right>
				Dispo(s)
			</th>
			";
	if($rank>=3) 
		{
		echo "
			<form action=add_instance.php>
			<th>
				<input type=submit value='".$lang['new_instance']."' style=margin-left:25>
			</th>
			</form>";
		}
	else
		{
		echo "<th></th>";
		}
	echo "
		</tr>";
	
	$i=0;
	foreach ($instances as $inst)
	{
		echo "
		<tr ".($inst['date']<date("YmdHis",time()) ? 'class=finished': ($i%2 ? 'class=odd':''))." >
			<td>
				".($rank ? "<a href=details.php?id_inst=".$inst['id_inst'].">".$inst['inst_nom']."</a>":"<a href=details.php?id_inst=".$inst['id_inst'].">Surprise :)</a>")."
			</td>
			<td>
				".$inst_type[$inst['inst_type']]."
			</td>
			<td>
				".aff_date($inst['date'])."
			</td>
			<td>
				".$inst['nom']."
			</td>
			<td align=right>
				".$inst['count']."
			</td>
			<td align=right>
				<font style=color:green>".$inst['count_dispo']."</font> <font style=color:orange>+ ".$inst['count_nc']." NC</font> <font style=color:blue>+ ".$inst['count_res']." Rés</font>
			</td>";
		if($rank>=3) 
		{
			echo "
			<form action=details.php?id_inst=".$inst['id_inst']." method=POST>
			<td align=right>
				<input type=submit value='".$lang['gerer_mon_groupe']."'>
			</td>
			</form>
			";
		}
		else
		{
			echo "<td></td>";
		}
		echo "</tr>";
		$i++;	
	}
	echo "</table>";
	?>

	<table style=border:0px;width=150px;margin-left:5%>
		<tr>
	<?
	if($rank>=3)
	{
	?>
		<form action=add_instance.php>
			<td colspan=2>
				<input type=submit value='<?=$lang['new_instance']?>' style=width:150px>
			</td>
		</form>
	<?
	}
	?>
		</tr>
		<tr>
			<td  class=finished style='border:1px solid black;width:50px'>&nbsp;</td>
			<td align=left><?=$lang['finished']?></td>
		</tr>
	</table>

	<?
	//Pied de page
	include 'footer.php';
	break;

	////////////////////
	//INSTANCES PASSEES
	////////////////////

	case 'passe':

	$d_ = mysql_query("
		SELECT * FROM ".prefix('instances')." I, ".prefix('joueurs')." J 
		WHERE I.inst_orga=J.id_joueur AND I.date<'".date('YmdHis',time()-7200)."' AND I.date>'20060427030000'
		ORDER BY -date
	") or die ("Erreur mysql - index.php:201 : ".mysql_error());

	while($d = mysql_fetch_assoc($d_))
	{
		//Nombre de joueurs séléctionnés
		$tmp = mysql_fetch_row(mysql_query("SELECT count(*) FROM ".prefix('liens')." WHERE id_inst='".$d['id_inst']."'"));
	
		//recherche dispo
		$tmp2_ = mysql_query("
			SELECT count(*)
			FROM ".prefix('dispo')." D
			LEFT JOIN ".prefix('liens')." L ON D.id_inst=L.id_inst AND D.id_joueur=L.id_joueur
			WHERE D.id_inst='".$d['id_inst']."' AND (dispo='2') AND id_leader IS NULL
			") or die("Erreur mysql - index.php:214 : ".mysql_error());
			$tmp2 = mysql_fetch_row($tmp2_);

		//recherche NC
		$tmp3_ = mysql_query("
			SELECT count(*)
			FROM ".prefix('dispo')." D
			LEFT JOIN ".prefix('liens')." L ON D.id_inst=L.id_inst AND D.id_joueur=L.id_joueur
			WHERE D.id_inst='".$d['id_inst']."' AND (dispo='1') AND id_leader IS NULL
			") or die("Erreur mysql - index.php:223 : ".mysql_error());
		$tmp3 = mysql_fetch_row($tmp3_);

		//recherche réservistes
		$tmp4_ = mysql_query("
			SELECT count(*)
			FROM ".prefix('dispo')." D
			LEFT JOIN ".prefix('liens')." L ON D.id_inst=L.id_inst AND D.id_joueur=L.id_joueur
			WHERE D.id_inst='".$d['id_inst']."' AND (dispo='4') AND id_leader IS NULL
			") or die("Erreur mysql - index.php:232 : ".mysql_error());
		$tmp4 = mysql_fetch_row($tmp4_);
		
		//On stock dans la liste des instances
		$d['count'] = $tmp[0];
		$d['count_dispo'] = intval($tmp2[0]);
		$d['count_nc'] = intval($tmp3[0]);
		$d['count_res'] = intval ($tmp4[0]);
		$instances[] = $d;
	}
	
	//En tête de la page
	include 'header.php';
	
	//Corp de la page
	echo "
	<big>Historique des Instances faites par la guilde, ".$base['display']."</big> - <small><a href=index.php?ref=futur>(voir la liste des Instances prévues)</a></small>
	<table cellspacing=0 style=margin-left:5%>
		<tr>
			<th>
				".$lang['instance']."
			</th>
			<th>
				".$lang['type']."
			</th>		
			<th>
				".$lang['date']."
			</th>
			<th>
				".$lang['organisateur']."
			</th>
			<th style=text-align:right>
				".$lang['selected_players']."
			</th>
			<th style=text-align:right>
				Dispo(s) (+ N.C. + Rés.)
			</th>
			".($rank ? "
				<form action=add_instance.php>
					<th>
						<input type=submit value='".$lang['new_instance']."' style=margin-left:25>
					</th>
				</form>
			":'')."
		</tr>
	";
	
	$i=0;
	foreach ($instances as $inst)
	{
		echo "
		<tr ".($inst['date']<date("YmdHis",time()) ? 'class=finished': ($i%2 ? 'class=odd':'')).">
			<td>
				<a href=details.php?id_inst=".$inst['id_inst'].">".$inst['inst_nom']."</a>
			</td>
			<td>
				".$inst_type[$inst['inst_type']]."
			</td>
			<td>
				".aff_date($inst['date'])."
			</td>
			<td>
				".$inst['nom']."
			</td>
			<td align=right>
				".$inst['count']."
			</td>
			<td align=right>
				<font style=color:green>".$inst['count_dispo']."</font> <font style=color:orange>(+ ".$inst['count_nc'].")</font> <font style=color:blue>(+ ".$inst['count_res'].")</font>
			</td>
			<td></td>
		</tr>
	";
	$i++;	
	}
	echo "</table>";
	?>

	<table style=border:0px;width=150px;margin-left:5%>
		<tr>
	<?
	if($rank>=3)
	{
	?>
		<form action=add_instance.php>
			<td colspan=2>
				<input type=submit value='<?=$lang['new_instance']?>' style=width:150px>
			</td>
		</form>	
	<?
	}
	?>
		</tr>
		<tr>
			<td  class=finished style='border:1px solid black;width:50px'>&nbsp;</td>
			<td align=left><?=$lang['finished']?></td>
		</tr>
	</table>
	
	<?
	//Pied de page
	include 'footer.php';
	break;
}
?>