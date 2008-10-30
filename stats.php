<?
//////////////////////////////////////////////
// stats.php
// Statistiques des joueurs
// NE PAS MODIFIER
// Version du 27/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 21/10/2008: Nettoyage du code, première version 
// 27/10/2008 - Joyrock : Ajout du filtre date	

//sécurité
$GLOBALS['allowed'] = 1;

//includes
include "commun.inc.php";


if(!$_GET['player'])
{
	$ord 	= array('nom','classe','race','guilde','dispo','reserv','nc','abs','runs','ni','rank');
	$order 	= $_GET['order'] ? $ord[intval($_GET['order'])] : 'nom';
	$sort	= $_GET['sort']=='SORT_DESC' ? SORT_DESC:SORT_ASC;
	$tros	= $_GET['sort']=='SORT_DESC' ? 'SORT_ASC':'SORT_DESC';
	$ina	= $_REQUEST['ina'] ? 1:0;
	$inactivity = $ina ? '':"AND inactive='0'";
	
	if($_REQUEST['date_filtre'])
	{
		if($_REQUEST['date_filtre']=="")
		{
			$select_date_dispo="";
			$select_date_instance="";
			$select_date_joueur="";
		}
		else
		{
			$jour=substr($_REQUEST['date_filtre'],0,2);
			$mois=substr($_REQUEST['date_filtre'],3,2);
			$annee=substr($_REQUEST['date_filtre'],6,4);
		
			$filtre_date=$annee.$mois.$jour."000000";
			$select_date_dispo="AND dispo_date>=".$filtre_date;
			$select_date_instance="AND date>=".$filtre_date;
			$select_date_joueur="AND ajoute_le<=".$filtre_date;
		}
	}
	else
	{
		$select_date_dispo="";
		$select_date_instance="";
	}
	 
	//Requête liste des joueurs
	$d_ = mysql_query("
		SELECT J.*, J.rank is_leader , B.id_joueur blacked
		FROM(".prefix('joueurs')." J) 
		LEFT JOIN ".prefix('blacklist')." B ON B.id_joueur=J.id_joueur
		WHERE 1 ".$inactivity." ".$select_date_joueur) or die ("Erreur mysql - stats.php:27 : ".mysql_error());
		
	//On initialise le tableau de joueur
	while($d = mysql_fetch_assoc($d_))
	{
		$d['ni']= 0;
		$d['dispo']= 0;
		$d['abs']= 0;
		$d['reserv']= 0;
		$d['nc']= 0;
		$d['runs']= 0;
		$d['nb_instances']=0;
		$joueur[$d['id_joueur']] = $d;
	}
	
	//On compte pour chaque joueur le nombre de statut "NC"
	$d_ = mysql_query("
		SELECT id_joueur,count(*) as nc 
		FROM ".prefix('dispo')."
		WHERE dispo = '1' ".$select_date_dispo." GROUP BY id_joueur
		") or die ("Erreur mysql - stats.php:47 : ".mysql_error());
	
	while($d = mysql_fetch_assoc($d_))
	{
		if($joueur[$d['id_joueur']]) $joueur[$d['id_joueur']]['nc'] = $d['nc'];
	}
		
	//On compte pour chaque joueur le nombre de statut "Dispo".
	$d_ = mysql_query("
		SELECT id_joueur,count(*) as dispo
		FROM ".prefix('dispo')."
		WHERE dispo = '2' ".$select_date_dispo." GROUP BY id_joueur
		") or die ("Erreur mysql - stats.php:60 : ".mysql_error());
	
	while($d = mysql_fetch_assoc($d_))
	{
		if($joueur[$d['id_joueur']]) $joueur[$d['id_joueur']]['dispo'] = $d['dispo'];
	}

	//On compte pour chaque joueur le nombre de statut "Abs".
	$d_ = mysql_query("
		SELECT id_joueur,count(*) as abs 
		FROM ".prefix('dispo')."
		WHERE dispo = '3' ".$select_date_dispo." GROUP BY id_joueur
		") or die ("Erreur mysql - stats.php:73 : ".mysql_error());
		
	while($d = mysql_fetch_assoc($d_))
	{
		if($joueur[$d['id_joueur']]) $joueur[$d['id_joueur']]['abs'] = $d['abs'];
	}
		
	//On compte pour chaque joueur le nombre de statut "reserviste".
	$d_ = mysql_query("
		SELECT id_joueur,count(*) as res
		FROM ".prefix('dispo')."
		WHERE dispo = '4' ".$select_date_dispo." GROUP BY id_joueur
		") or die ("Erreur mysql - stats.php:86 : ".mysql_error());
	
	while($d = mysql_fetch_assoc($d_))
	{
		if($joueur[$d['id_joueur']]) $joueur[$d['id_joueur']]['reserv'] = $d['res'];
	}

	//NB instances depuis inscriptions
	foreach($joueur as $j)
	{
		$d_ = mysql_query("
			SELECT count(id_inst) as nbinst 
			FROM ".prefix('instances')." 
			WHERE date >= ".$j['ajoute_le']." ".$select_date_instance) or die ("Erreur mysql - stats.php:101 : ".mysql_error());
			
		while($d = mysql_fetch_assoc($d_))
		{
			$joueur[$j['id_joueur']]['nb_instances'] = $d['nbinst'];
		}
		
		$joueur[$j['id_joueur']]['ni'] = $joueur[$j['id_joueur']]['nb_instances']-$joueur[$j['id_joueur']]['nc']-$joueur[$j['id_joueur']]['abs']-$joueur[$j['id_joueur']]['dispo']-$joueur[$j['id_joueur']]['reserv'];
		
		//Debug si ni négatif
		/*if($joueur[$j['id_joueur']]['ni']<0)
			{
			echo "Nb instances :".$joueur[$j['id_joueur']]['nb_instances']."<br>";
			echo "N/C :".$joueur[$j['id_joueur']]['nc']."<br>";
			echo "ABS :".$joueur[$j['id_joueur']]['abs']."<br>";
			echo "Dispo :".$joueur[$j['id_joueur']]['dispo']."<br>";
			echo "Reserviste :".$joueur[$j['id_joueur']]['reserv']."<br>";
			echo "Non inscrit:".$joueur[$j['id_joueur']]['ni']."<br>";
			}*/
	}
	
	//NB INSTANCES EFFECTUEES
	$d_ = mysql_query("
		SELECT id_joueur,count(*) as inst 
		FROM (".prefix('liens')." L, ".prefix('instances')." I)
		WHERE I.id_inst=L.id_inst AND date < '".date("YmdHis",time())."' ".$select_date_instance." GROUP BY L.id_joueur
		") or die ("Erreur mysql - stats.php:115 : ".mysql_error());
	
	while($d = mysql_fetch_assoc($d_))
	{
		if($joueur[$d['id_joueur']]) $joueur[$d['id_joueur']]['runs'] = $d['inst'];
	}
		
	// On calcul le pourcentage pour chaque colonne
	foreach($joueur as $j)
	{
		if($j['nb_instances']>0) {$joueur[$j['id_joueur']]['dispo'] = pourcentage($j['dispo'],$j['nb_instances'],100);}else{$joueur[$j['id_joueur']]['dispo'] = 0;}
		if($j['nb_instances']>0) {$joueur[$j['id_joueur']]['reserv'] =pourcentage($j['reserv'],$j['nb_instances'],100);}else{$joueur[$j['id_joueur']]['reserv'] = 0;}
		if($j['nb_instances']>0) {$joueur[$j['id_joueur']]['nc'] =pourcentage($j['nc'],$j['nb_instances'],100);}else{$joueur[$j['id_joueur']]['nc'] = 0;}
		if($j['nb_instances']>0) {$joueur[$j['id_joueur']]['abs'] =pourcentage($j['abs'],$j['nb_instances'],100);}else{$joueur[$j['id_joueur']]['abs'] = 0;}
		if($j['nb_instances']>0) {$joueur[$j['id_joueur']]['runs'] =pourcentage($j['runs'],$j['nb_instances'],100);}else{$joueur[$j['id_joueur']]['runs'] = 0;}
		if($j['nb_instances']>0) {$joueur[$j['id_joueur']]['ni'] =pourcentage($j['ni'],$j['nb_instances'],100);}else{$joueur[$j['id_joueur']]['ni'] = 0;}
	}
		
	// Obtient une liste de colonnes
	foreach ($joueur as $key => $row) 
	{
		$nom[$key]  = $row['nom'];
		$sorting[$key] = $row[$order];
	}

	// Tri les données par volume décroissant, edition croissant
	// Ajoute $data en tant que premier paramêtre, pour trier par la clé commune
	array_multisort($sorting, $sort, $nom, SORT_ASC, $joueur);
	
	//En tête de page
	include 'header.php';
	?>
	<div align=center>
		<table cellspacing=0 style=width:1024px>
			<tr>
				<form name=view action=stats.php?date_filtre=<?=$_REQUEST['date_filtre']?>&order=<?=$_GET['order'];?>&sort=<?=$_GET['sort'];?> method=POST>
					<td>
						<input type=checkbox name=ina value=1 <?=($_REQUEST['ina']?'CHECKED':'')?>  onchange="if (this.value != 'NULL') document.view.submit();">
					</td>
					<td colspan=2 style=vertical-align:middle align=left>
						<i style=font-size:10px>Afficher les inactifs</i>
					</td>
				</form>
				<form name=view_date action=stats.php?date_filtre=<?=$_REQUEST['date_filtre']?>&order=<?=$_GET['order'];?>&sort=<?=$_GET['sort'];?>  method=POST>
					<td colspan=2>
						<input type=submit value='Filtrer'>	
					</td>
					<td colspan=2>
						<input type=text name=date_filtre value="<?echo $_REQUEST['date_filtre'];?>">
					</td>
					<td colspan=6 style=vertical-align:middle align=left>
						<i style=font-size:10px>Laissez vide pour les stats depuis l'inscription, sinon jj/mm/aaaa</i>
					</td>
				</form>
			</tr>
			<tr>
				<th width=1%>&nbsp;</th>
				<th>Nom 		<a href=stats.php?date_filtre=<?=$_REQUEST['date_filtre']?>&ina=<?=$ina?>&order=0&sort=<?=($_GET['order']==0 ? $tros:'SORT_ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Classe 		<a href=stats.php?date_filtre=<?=$_REQUEST['date_filtre']?>&ina=<?=$ina?>&order=1&sort=<?=($_GET['order']==1 ? $tros:'SORT_ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Race 		<a href=stats.php?date_filtre=<?=$_REQUEST['date_filtre']?>&ina=<?=$ina?>&order=2&sort=<?=($_GET['order']==2 ? $tros:'SORT_ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Guilde 		<a href=stats.php?date_filtre=<?=$_REQUEST['date_filtre']?>&ina=<?=$ina?>&order=3&sort=<?=($_GET['order']==3 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Dispo		<a href=stats.php?date_filtre=<?=$_REQUEST['date_filtre']?>&ina=<?=$ina?>&order=4&sort=<?=($_GET['order']==4 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Res.		<a href=stats.php?date_filtre=<?=$_REQUEST['date_filtre']?>&ina=<?=$ina?>&order=5&sort=<?=($_GET['order']==5 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>N.C			<a href=stats.php?date_filtre=<?=$_REQUEST['date_filtre']?>&ina=<?=$ina?>&order=6&sort=<?=($_GET['order']==6 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Abs			<a href=stats.php?date_filtre=<?=$_REQUEST['date_filtre']?>&ina=<?=$ina?>&order=7&sort=<?=($_GET['order']==7 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Runs		<a href=stats.php?date_filtre=<?=$_REQUEST['date_filtre']?>&ina=<?=$ina?>&order=8&sort=<?=($_GET['order']==8 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>N/A			<a href=stats.php?date_filtre=<?=$_REQUEST['date_filtre']?>&ina=<?=$ina?>&order=9&sort=<?=($_GET['order']==9 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Etat 		<a href=stats.php?date_filtre=<?=$_REQUEST['date_filtre']?>&ina=<?=$ina?>&order=10&sort=<?=($_GET['order']==10 ? $tros:'SORT_ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>&nbsp;</th>
			</tr>
			<?
			$i=1;
			foreach($joueur as $j)
			{
				echo "
				<tr ".($i%2 ? '':'style=background-color:#cee8ce').">
					<td align=right style=font-size:10px>$i</td>
					<td style=height:22px;>&nbsp;<b><a href=stats.php?player=".$j['id_joueur'].">".$j['nom']."</a></b></td>
					<td>&nbsp;".$j['classe']."</td>
					<td>&nbsp;".$j['race']."</td>
					<td>&nbsp;".$j['guilde']."</td>
					<td>&nbsp;<b>".$j['dispo']."%</b></td>
					<td>&nbsp;<b>".$j['reserv']."%</b></td>
					<td>&nbsp;<b>".$j['nc']."%</b></td>
					<td>&nbsp;<b>".$j['abs']."%</b></td>
					<td>&nbsp;<b>".$j['runs']."%</b></td>
					<td>&nbsp;<b>".$j['ni']."%</b></td>
					<td ".($j['blacked']?'class=blacked':'').">&nbsp;".($j['blacked']?'Banni':$ranks[$j['rank']])."</td>
				</tr>";
				$i++;
			}
			?>
		</table>
	</div>
	<?
	//Pied de page
	include 'footer.php';
}
else
{
	// Include avec toutes mes fonctions qui vont bien...
	include('artichow/statistiques_include.php');
		
	//SELECTION DES DONNEES DU PERSO
	$player = intval($_GET['player']);

	$d_ = mysql_query("
		SELECT J.*, J.rank is_leader , B.id_joueur blacked
		FROM(".prefix('joueurs')." J) 
		LEFT JOIN ".prefix('blacklist')." B ON B.id_joueur=J.id_joueur
		WHERE J.id_joueur='".$player."'
		") or die ("Erreur mysql - stats.php:215 : ".mysql_error());
		
	//On initialise le tableau de joueur
	while($d = mysql_fetch_assoc($d_))
	{
		$joueur = $d;
	}
		
	//On compte pour chaque joueur le nombre de statut "Dispo".
	$d_ = mysql_query("
		SELECT count(*) as dispo
		FROM ".prefix('dispo')."
		WHERE dispo = '2'
		AND id_joueur='".$player."'
		") or die ("Erreur mysql - stats.php:229 : ".mysql_error());
	
	while($d = mysql_fetch_assoc($d_))
	{
		$nbdispo = $d['dispo'];
	}
		
	//On compte pour chaque joueur le nombre de statut "NC"
	$d_ = mysql_query("
		SELECT count(*) as nc 
		FROM ".prefix('dispo')."
		WHERE dispo = '1'
		AND id_joueur='".$player."'
		") or die ("Erreur mysql - stats.php:2242 : ".mysql_error());
	
	while($d = mysql_fetch_assoc($d_))
	{
		$nbnc = $d['nc'];
	}

	//On compte pour chaque joueur le nombre de statut "Abs".
	$d_ = mysql_query("
		SELECT count(*) as abs 
		FROM ".prefix('dispo')."
		WHERE dispo = '3'
		AND id_joueur='".$player."'
		") or die ("Erreur mysql - stats.php:255 : ".mysql_error());
		
	while($d = mysql_fetch_assoc($d_))
	{
		$nbabs = $d['abs'];
	}
		
	//On compte pour chaque joueur le nombre de statut "reserviste".
	$d_ = mysql_query("
		SELECT count(*) as res
		FROM ".prefix('dispo')."
		WHERE dispo = '4'
		AND id_joueur='".$player."'
		") or die ("Erreur mysql - stats.php:268 : ".mysql_error());
		
	while($d = mysql_fetch_assoc($d_))
	{
		$nbres = $d['res'];
	}

	//NB instances depuis inscriptions
	$d_ = mysql_query("
		SELECT count(*) as nbinst 
		FROM ".prefix('instances')." 
		WHERE date >= ".$joueur['ajoute_le']) or die ("Erreur mysql - stats.php:281 : ".mysql_error());
	
	while($d = mysql_fetch_assoc($d_))
	{
		$nbinstance = $d['nbinst'];
	}
		
	//NB INSTANCES EFFECTUEES
	$d_ = mysql_query("
		SELECT count(*) as inst 
		FROM (".prefix('liens')." L, ".prefix('instances')." I)
		WHERE I.id_inst=L.id_inst AND date < '".date("YmdHis",time())."'
		AND L.id_joueur='".$player."'
		") or die ("Erreur mysql - stats.php:292 : ".mysql_error());
	
	while($d = mysql_fetch_assoc($d_))
	{
		$nbruns = $d['inst'];
	}
		
	//Nombre de "Non inscrit"
	$nbni = $nbinstance-$nbdispo-$nbabs-$nbnc-$nbres;
		
	//On prépare le graph 1
	$indextab=0;
	if($nbinstance>0) 
	{ 
		$pctdispo=pourcentage($nbdispo,$nbinstance,100);
		$pctres=pourcentage($nbres,$nbinstance,100);
		$pctnc=pourcentage($nbnc,$nbinstance,100);
		$pctabs=pourcentage($nbabs,$nbinstance,100);
		$pctni=pourcentage($nbni,$nbinstance,100);
			
		if($pctdispo>0)
		{
			$legend1[$indextab] = 'Dispo';
			$data1[$indextab] = $pctdispo;
			$indextab++;
		}
			
		if($pctres>0)
		{
			$legend1[$indextab] = 'Reserviste';
			$data1[$indextab] = $pctres;
			$indextab++;
		}

		if($pctnc>0)
		{
			$legend1[$indextab] = 'Non confirme';
			$data1[$indextab] = $pctnc;
			$indextab++;
		}		
		
		if($pctabs>0)
		{
			$legend1[$indextab] = 'Absent';
			$data1[$indextab] = $pctabs;
			$indextab++;
		}	
		
		if($pctni>0)
		{
			$legend1[$indextab] = 'Non inscrit';
			$data1[$indextab] = $pctni;
			$indextab++;
		}			
	}
	
	$indextab2=0;
	if($nbinstance>0) 
	{
		$pctruns=pourcentage($nbruns,$nbinstance,100);
		$pctnoruns=100-$pctruns;
	
		if($pctruns>0)
		{
			$legend2[$indextab2] = 'Pris en raid';
			$data2[$indextab2] = $pctruns;
			$indextab2++;
		}
			
		if($pctnoruns>0)
		{
			$legend2[$indextab2] = 'Non pris en raid';
			$data2[$indextab2] = $pctnoruns;
			$indextab2++;
		}	
	}
		
	// Tabeau des couleurs
	$color = array('#92DDF3','#5C69AA','#A35E9E','#DF6C6C');

	// Largeur du camembert
	$width = 500;

	// Hauteur du camembert
	$height = 350;
	
	//En-tête de la page
	include 'header.php';
	?>
	<big>Statistiques : <?=$joueur['nom']?></big>
	<table style=width:90%;border:0px;>
		<tr>
			<td style=width:30%>
				<table style=width:100%;font-size:10px; cellspacing=0 cellpadding=0>
					<tr>
						<th colspan=3></th>
					</tr>
					<tr>
						<td>
							<?
							// Titre du camembert
							 $title2 = "Taux de participation";
								
				 			// On crée l'image
							 insertStatImage(1,$width,$height,$title2,$legend2,$data2,$color,'',FALSE);
								
							echo'</br></br>'; 
							?>
						</td>
					<tr>
				</table>
			</td>
			<td style=width:30%></td>
			<td style=width:30%>
				<table style=width:100%;font-size:10px; cellspacing=0 cellpadding=0>
					<tr>
						<th colspan=3></th>
					</tr>
					<tr>
						<td>
							<?
							// Titre du camembert
							$title1 = "Mise à jour du Raid Orga";
																 
							// On crée l'image
							insertStatImage(1,$width,$height,$title1,$legend1,$data1,$color,'',FALSE);
								
							echo'</br></br>'; 
							?>
						</td>
					</tr>
				</table>							
			</td>
		</tr>
		<tr>
			<td></td>
		</tr>
	</table>
	<?
	//Pied de page
	include 'footer.php';
}
?>
