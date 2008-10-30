<?
//////////////////////////////////////////////
// info_player.php
// Liste des joueurs
// NE PAS MODIFIER
// Version du 29/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 21/10/2008: Nettoyage du code, première version 
// 29/10/2008 - Joyrock : Ajout du numéro de tel au profil, Ajout d'une confirmation pour la suppression

//Sécurité
$GLOBALS['allowed'] = 1;

//includes
include "commun.inc.php";

//Action envoyé par formulaire - supprimer
if($_GET['del'] && $rank>=4)
{
	if($_GET['confirm']==1)
	{
	$delplayer = intval($_GET['del']);
	mysql_query("DELETE FROM ".prefix('blacklist')." WHERE id_joueur='".$delplayer."'") or die("Erreur mysql - info_player.php:20 : ".mysql_error());
	mysql_query("DELETE FROM ".prefix('dispo')." WHERE id_joueur='".$delplayer."'") or die("Erreur mysql - info_player.php:21 : ".mysql_error());
	mysql_query("DELETE FROM ".prefix('drops')." WHERE id_joueur='".$delplayer."'") or die("Erreur mysql - info_player.php:22 : ".mysql_error());
	mysql_query("DELETE FROM ".prefix('joueurs')." WHERE id_joueur='".$delplayer."'") or die("Erreur mysql - info_player.php:23 : ".mysql_error());
	mysql_query("DELETE FROM ".prefix('liens')." WHERE id_joueur='".$delplayer."'") or die("Erreur mysql - info_player.php:24 : ".mysql_error());
	mysql_query("DELETE FROM ".prefix('mails')." WHERE id_joueur='".$delplayer."'") or die("Erreur mysql - info_player.php:25 : ".mysql_error());
	mysql_query("DELETE FROM ".prefix('messages')." WHERE id_joueur='".$delplayer."'") or die("Erreur mysql - info_player.php:26 : ".mysql_error());
	mysql_query("DELETE FROM ".prefix('wishlist')." WHERE id_joueur='".$delplayer."'") or die("Erreur mysql - info_player.php:27 : ".mysql_error());
	}
	else
	{
		//On demande la confirmation
		//En-tête
		include 'header.php';
		
		//Corp de page
		?>
		<big>êtes vous sûr de vouloir supprimer ce joueur ?</big>
		<br>
		<br>
		<a href=info_player.php?del=<?=$_GET['del']?>&confirm=1>OUI</a> / <a href=info_player.php>NON</a>
		<?
		
		//Pied de page
		include 'footer.php';
		exit;
	}	
}

if(!$_GET['player'])
{
	$ord 	= array('nom','classe','race','niveau','guilde','loot','runs','specialisation','notes','rank');
	$order 	= $_GET['order'] ? $ord[intval($_GET['order'])] : 'nom';
	$sort	= $_GET['sort']=='SORT_DESC' ? SORT_DESC:SORT_ASC;
	$tros	= $_GET['sort']=='SORT_DESC' ? 'SORT_ASC':'SORT_DESC';
	$ina	= $_REQUEST['ina'] ? 1:0;
	$inactivity = $ina ? '':"AND inactive='0'";
	
	//ORDER BY ".$order." $sort,nom
	$d_ = mysql_query("
		SELECT J.*, J.rank is_leader , B.id_joueur blacked
		FROM(".prefix('joueurs')." J) 
		LEFT JOIN ".prefix('blacklist')." B ON B.id_joueur=J.id_joueur
		WHERE 1 ".$inactivity) or die("Erreur mysql - info_player.php:40 : ".mysql_error());
	
	while($d = mysql_fetch_assoc($d_))
	{
		$d['loot']= 0;
		$d['runs']= 0;
		$joueur[$d['id_joueur']] = $d;
	}
	
	//Nombre de loot
	$d_ = mysql_query("
		SELECT id_joueur,count(*) as loot 
		FROM ".prefix('drops')." 
		GROUP BY id_joueur
		") or die("Erreur mysql - info_player.php:54 : ".mysql_error());
	
	while($d = mysql_fetch_assoc($d_))
	{
		if($joueur[$d['id_joueur']]) $joueur[$d['id_joueur']]['loot'] = $d['loot'];
	}
	
	//Nombre d'instance
	$d_ = mysql_query("
						SELECT id_joueur,count(*) as inst 
						FROM (".prefix('liens')." L, ".prefix('instances')." I)
						WHERE I.id_inst=L.id_inst AND date < '".date("YmdHis",time())."'
						GROUP BY L.id_joueur
					") or die("Erreur mysql - info_player.php:66 : ".mysql_error());
					
	while($d = mysql_fetch_assoc($d_))
	{
		if($joueur[$d['id_joueur']]) $joueur[$d['id_joueur']]['runs'] = $d['inst'];
	}

	// Obtient une liste de colonnes
	foreach ($joueur as $key => $row) 
	{
	    $nom[$key]  = $row['nom'];
	    $sorting[$key] = $row[$order];
	}
	
	// Tri les données par volume décroissant, edition croissant
	// Ajoute $data en tant que premier paramètre, pour trier par la clé commune
	array_multisort($sorting, $sort, $nom, SORT_ASC, $joueur);

	//En-tête
	include 'header.php';
	?>
	<div align=center>
		<table cellspacing=0 style=width:1024px>
			<tr>
				<?
				if ($rank>=3)
				{
				?>
				<form action=add_player.php method=POST>
					<td colspan=3>
						<input type=submit value='AJOUTER UN JOUEUR'>
					</td>
				</form>
				<form action=blacklist.php method=POST>
					<td colspan=2>
						<input type=submit value='BLACKLIST'>
					</td>
				</form>
				<?
				}
				?>
				<form name=view action=info_player.php method=POST>
					<td>
						<input type=checkbox name=ina value=1 <?=($_REQUEST['ina']?'CHECKED':'')?>  onchange="if (this.value != 'NULL') document.view.submit();">
					</td>
					<td colspan=3 style=vertical-align:middle align=left>
						<i style=font-size:10px>Afficher les inactifs</i>
					</td>
				</form>
			</tr>
			<tr>
				<th width=1%>&nbsp;</th>
				<th>Nom <a href=info_player.php?ina=<?=$ina?>&order=0&sort=<?=($_GET['order']==0 ? $tros:'SORT_ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Classe <a href=info_player.php?ina=<?=$ina?>&order=1&sort=<?=($_GET['order']==1 ? $tros:'SORT_ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Race <a href=info_player.php?ina=<?=$ina?>&order=2&sort=<?=($_GET['order']==2 ? $tros:'SORT_ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Lvl <a href=info_player.php?ina=<?=$ina?>&order=3&sort=<?=($_GET['order']==3 ? $tros:'SORT_ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Guilde <a href=info_player.php?ina=<?=$ina?>&order=4&sort=<?=($_GET['order']==4 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Loot <a href=info_player.php?ina=<?=$ina?>&order=5&sort=<?=($_GET['order']==5 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Runs <a href=info_player.php?ina=<?=$ina?>&order=6&sort=<?=($_GET['order']==6 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Spé <a href=info_player.php?ina=<?=$ina?>&order=7&sort=<?=($_GET['order']==7 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Info <a href=info_player.php?ina=<?=$ina?>&order=8&sort=<?=($_GET['order']==8 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Etat <a href=info_player.php?ina=<?=$ina?>&order=9&sort=<?=($_GET['order']==9 ? $tros:'SORT_ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			</tr>
			<?
			$i=1;
			foreach($joueur as $j)
			{
				echo "
				<tr ".($i%2 ? '':'style=background-color:#cee8ce').">
					<td align=right style=font-size:10px>$i</td>
					<td style=height:22px;>&nbsp;<b><a href=info_player.php?player=".$j['id_joueur'].">".$j['nom']."</a></b></td>
					<td>&nbsp;".$j['classe']."</td>
					<td>&nbsp;".$j['race']."</td>
					<td>&nbsp;".$j['niveau']."</td>
					<td>&nbsp;".$j['guilde']."</td>
					<td>&nbsp;<b>".$j['loot']."</b></td>
					<td>&nbsp;<b>".$j['runs']."</b></td>
					<td>&nbsp;".($j['specialisation']?"<a href=info_player.php?player=".$j['id_joueur']."><img src=$root/images/spe.gif></a>":'')."</td>
					<td>&nbsp;".($j['notes']?"<a href=info_player.php?player=".$j['id_joueur']."><img src=$root/images/spe.gif></a>":'')."</td>
					<td ".($j['blacked']?'class=blacked':'').">&nbsp;".($j['blacked']?'Banni':$ranks[$j['rank']])."</td>";
					
				if ($rank>=3)
				{
					echo "
						<form action=add_player.php?edit=".$j['id_joueur']." method=POST>
							<td style=width:50px;>
								".($j['rank']<$rank? '<input type=submit value=Edit style=width:50px;>':'&nbsp;')."
							</td>
						</form>";
				}
					
				if ($rank>=4)
				{
					echo "
					<form action=info_player.php?del=".$j['id_joueur']."&confirm=0 method=POST>
						<td style=width:50px;>
							".($j['rank']<$rank? '<input type=submit value=Del style=width:50px;>':'&nbsp;')."
						</td>
					</form>";
				}
					
				echo "</tr>";
				$i++;
			}
			
			if ($rank>=3)
			{
			?>
				<tr>
					<form action=add_player.php method=POST>
						<td colspan=3>
							<input type=submit value='AJOUTER UN JOUEUR'>
						</td>
					</form>
					<form action=blacklist.php method=POST>
						<td colspan=2>
							<input type=submit value='BLACKLIST'>
						</td>
					</form>
				</tr>
			<?
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

	$player = intval($_GET['player']);
	
	$instances 	= array();
	$leader		= array();
	
	//Sélection des données du joueur
	$d_ = mysql_query("
		SELECT J.*, L2.id_joueur leader_id, B.id_joueur blacked, L.*
		FROM (".prefix('joueurs')." J) 
		LEFT JOIN ".prefix('blacklist')." B ON B.id_joueur=J.id_joueur
		LEFT JOIN ".prefix('liens')." L 	ON L.id_joueur=J.id_joueur
		LEFT JOIN ".prefix('liens')." L2 	ON L2.id_inst=L.id_inst AND L2.is_lead='1' AND L2.id_leader=L.id_leader
		WHERE J.id_joueur = '".$player."'
		") or die("Erreur mysql - info_player.php:211 : ".mysql_error());
		
	while($d = mysql_fetch_assoc($d_))
	{
		$instances[] = $d['id_inst'];
		$leader[] = intval($d['leader_id']);
		$links[$d['id_inst']] = $d['leader_id'];
		$j = $d;
	}
	
	//Récupération de l'historique des instances
	$passif = array();
	if($instances[0])
	{
		$d_ = mysql_query("
			SELECT * FROM ".prefix('instances')." 
			WHERE id_inst IN (".implode(',',$instances).") 
			ORDER BY -date") or die("Erreur mysql - info_player.php:232 : ".mysql_error());
			
		while($d = mysql_fetch_assoc($d_))
		{
			$passif[] = $d;
			$j['dkp']+= $d['dkp'];
		}
	}
	
	//Récupération des informations sur les leader d'instance
	$chef = array();
	if($leader[0])
	{
		$d_ = mysql_query("
			SELECT J.* FROM (".prefix('joueurs')." J) 
			WHERE id_joueur IN (".implode(',',$leader).")
			") or die("Erreur mysql - info_player.php:248 : ".mysql_error());
			
		while($d = mysql_fetch_assoc($d_))
		{
			$chef[$d['id_joueur']] = $d;
		}
	}
	
	//On récupère les loots du joueur
	$butin = array();
	$d_ = mysql_query("
				SELECT D.*, O.*, I.inst_nom, I.id_inst, I.date, W.wish_date as wished
				FROM (".prefix('drops')." D, ".prefix('objets')." O)
				LEFT JOIN ".prefix('instances')." I ON I.id_inst=D.id_inst
				LEFT JOIN ".prefix('wishlist')." W ON D.id_objet=W.id_objet AND W.id_joueur='".$player."'
				WHERE D.id_joueur='".$player."' AND O.real_id=D.id_objet
				ORDER BY -date
				"
				) or die("Erreur mysql - info_player.php:261 : ".mysql_error());
	
	while($d = mysql_fetch_assoc($d_))
	{
		$butin[] = $d;
	}
	
	//La wishlist
	$d_ = mysql_query("
		SELECT O.*, D.id_drop as got
		FROM (".prefix('wishlist')." W, ".prefix('objets')." O)
		LEFT JOIN ".prefix('drops')." D ON D.id_objet=W.id_objet AND D.id_joueur='".$player."'
		WHERE W.id_joueur = '".$player."' AND O.real_id=W.id_objet
		ORDER BY poids
		") or die("Erreur mysql - info_player.php:277 : ".mysql_error());
	
	$wish = array();
	while($d = mysql_fetch_assoc($d_))
	{
		$wish[] = $d;
	}
	
	//Les dispo
	$d_ = mysql_query("
		SELECT  I.*, I.id_inst id, D.dispo, L.id_leader as taken
		FROM (".prefix('instances')." I, ".prefix('joueurs')." J)
		LEFT JOIN ".prefix('dispo')." D ON D.id_inst=I.id_inst AND D.id_joueur = '$player'
		LEFT JOIN ".prefix('liens')." L ON L.id_joueur = J.id_joueur AND I.id_inst=L.id_inst
		WHERE J.id_joueur='$player' AND date > '".date("YmdHis",time()-3600*24)."'
		ORDER BY -date
		") or die("Erreur mysql - info_player.php:292 : ".mysql_error());
	
	$disp = array();
	while($d = mysql_fetch_assoc($d_))
	{
		$disp[] = $d;
	}
	
	//En-tête
	include 'header.php';
	?>
	
	<big>Fiche de <?=$j['nom']?></big>
	<table style=width:90%;border:0px;>
		<tr>
			<td style=width:20%>
				<?
				if($dispo[0])
				{
				?>
					<table style=width:100%;font-size:10px; cellspacing=0 cellpadding=0>
						<tr>
							<th colspan=3>Disponibilité</th>
						</tr>
						<?
						$k=1;
						foreach($disp as $d)
						{
							echo "
								<tr ".($k%2 ? '':'class=odd').">
									<td>
										<a href=details.php?id_inst=".$d['id_inst'].">".$d['inst_nom']."</a>
									</td>
									<td width=35%>
										".aff_date($d['date'],4)."
									</td>
									<td>
										".$dispo_short[intval($d['dispo'])]."
										".($d['taken'] ? '<br><i>Prévu</i>':'')."
									</td>
								<tr>";
							$k++;
						}
						?>
					</table>
				<?
				}
				else
				{
				echo "Pas de dispos.";
				}
				?>
			</td>
			<td style=width:60% align=center>
				<table style=width:400px;>
					<tr>
						<th style=width:50%>Classe</th>
						<td><?=$j['classe']?></td>
					</tr>
					<tr>
						<th>Race</th>
						<td><?=$j['race']?></td>
					</tr>
					<tr>
						<th>Guilde</th>
						<td><?=$j['guilde']?></td>
					</tr>
					<tr>
						<th>Spécialisation</th>
						<td><?=$j['specialisation']?></td>
					</tr>
					<tr>
						<th>Notes</th>
						<td><?=$j['notes']?></td>
					</tr>
					<?
					if($rank>=3)
					{
					?>
					<tr>
						<th>Telephone</th>
						<td><?=$j['telephone']?></td>
					</tr>
						<tr>
							<form action=add_player.php?edit=<?=$player?> method=POST>
								<td colspan=2 align=center>
									<input type=submit value=EDITER>
								</td>
							</form>
						</tr>
					<?
					}
					?>
				</table>
				<br>
			<?
			if($butin[0])
			{
			?>
			<big>Butin</big>
			<table style=width:700px; cellspacing=0>
				<tr class=forum>
					<th>Objet</th>
					<th>Instance</th>
					<th>Liens</th>
				</tr>
				<?
				$i=0;
				foreach($butin as $b)
				{
					echo "
						<tr ".($i%2 ? 'class=odd':'')."  ".($b['wished'] ? 'style=background-color:#ff8080':'').">
							<td><a href=drops.php?action=obj&id_objet=".$b['real_id'].">".$b['obj_nom']."</aS></td>
							<td><a href=details.php?id_inst=".$b['id_inst'].">".$b['inst_nom']."</a> le ".aff_date($b['date'])."</td>
							<td>
								<a href=\"http://thottbot.com/i".$b['real_id']."\" target=_blank>T</a> |
								<a href=\"http://fr.wowhead.com/?item=".$b['real_id']."\" target=_blank>W</a> |
								<a href=\"http://wow.allakhazam.com/db/item.html?witem=".$b['real_id'].";source=live;locale=frFR\" target=_blank>A</a>
							</td>
						</tr>";
					$i++;
				}
				?>
			</table>
			<?
			}
			?>
			<br>
			<big>Historique</big>
			<table style=width:700px; cellspacing=0>
				<?
				$i=0;
				foreach($passif as $p)
				{
					echo "
						<tr ".($i%2 ? 'class=odd':'').">
							<td>".($p['date']<date("YmdHis",time())?'A participé au':'Prévu pour')."</td>
							<td><a href=details.php?id_inst=".$p['id_inst'].">".$p['inst_nom']."</a></td>
							<td>du ".aff_date($p['date'])."</td>
							<td>sous les ordres de <a href=info_player.php?player=".$chef[$links[$p['id_inst']]]['id_joueur'].">".$chef[$links[$p['id_inst']]]['nom']."</a></td>
						</tr>";
					$i++;
				}
				?>
			</table>
			<br>
		</td>
		<td  style=width:20%;>
			<?
			if($wish[0] && $conf['limit_wishlist'])
			{
			?>
			<table style=width:100%;font-size:10px; class=wishlist cellspacing=0 cellpadding=0>
				<tr>
					<th colspan=2>Wishlist</th>
				</tr>
				<?
				$k=1;
				foreach($wish as $w)
				{
					if($k > $conf['limit_wishlist']) break;
					echo "
						<tr ".($k%2 ? '':'class=odd')." ".($w['got'] ? 'style=background-color:#ff8080;height:40px':' style=height:40px').">
							<td>
								".($w['img'] ? "<img src=".$w['img']." width=30px> ":'&nbsp;')."
							</td>
							<td>
								<a href=drops.php?action=obj&id_objet=".$w['id_objet'].">".$w['objet_nom']."</a>
							</td>
						<tr>";
					$k++;
				}
				?>
			</table>
			<?
			}
			else
			{
			echo "&nbsp";
			}
			?>
		</td>
	</tr>
</table>
<?
//Pied de page
include 'footer.php';
}
?>
