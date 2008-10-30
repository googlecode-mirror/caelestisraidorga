<?
	include "config.inc.php";
	include	'function.inc.php';
	
	setlocale(LC_ALL, "fr_FR", 'fr', 'fr_FR@euro');
	
	mysql_select_db($db_name);

	$classe 	= array('Chaman','Chasseur','Démoniste','Druide','Guerrier','Mage','Paladin','Prêtre','Voleur');
	$race		= array('Elfe','Gnome','Humain','Mort-Vivant','Nain','Orc','Tauren','Troll');
	$instance 	= array("Coeur de Magma","Antre d'Onyxia","Seigneur Kazzak","Azuregos","Repaire de l'Aile-Noire","Vallée d'Alterac","Zul'Gurub","Ahn'Qiraj (20)","Ahn'Qiraj (40)");
	
	$id_inst 	= $_GET['id_inst'] ? intval($_GET['id_inst']) : null;

	$id_objet 	= $_GET['id_objet'] ? intval($_GET['id_objet']) : null;

	$player		= intval($_GET['player']);
	
	$view	 	= $_GET['view']=='group' ? 'group' : 'class';
	
//HEAD
?>
		<title>Organisation d'instance : <?=$guild?></title>
		<link rel="stylesheet" type="text/css" media="all" href="<?=$root?>/style.css" />
		<br/>
		<div class=header_menu>
			<big><a href=index.php>Organisation</a> : <?=$guild?> </big><br>
			<big><a href=view.php?db=<?=$db?>>Instances</a> / <a href=view.php?db=<?=$db?>&players>Joueurs</a> / <a href=view.php?db=<?=$db?>&obj>Butins</a></big>
		</div>
		<div align=center>

<?

//FIN HEAD


if($id_inst){
	$d_ = mysql_query("
				SELECT DISTINCT J.*, L.id_leader, L.is_lead current_lead, j.nom leader_nom
				FROM (".prefix('joueurs')." J, ".prefix('liens')." L)
				LEFT JOIN ".prefix('joueurs')." j ON L.id_leader=j.id_joueur
				WHERE L.id_inst='$id_inst' AND L.id_joueur=J.id_joueur
				ORDER BY nom
			"
			) or die(mysql_error());

	$group	= array();
	$joueur = array();
	$count_classe = array();
				
	while($d = mysql_fetch_assoc($d_)){
	
		if(!$d['current_lead'])
			$group[$d['id_leader']]['players'][]= $d['id_joueur'];
		else
			$group[$d['id_leader']]['leader']	= $d['id_joueur'];
			
		$group[$d['id_leader']]['real_leader_name'] = $d['leader_nom'];
			
		$joueur[$d['id_joueur']] = $d;
		$count_classe[$d['classe']]++;
		$classes[$d['classe']][] = $d['id_joueur'];
			
	}
	
	$inst_ = mysql_query("
							SELECT * FROM (".prefix('instances')." I, ".prefix('joueurs')." J)
							WHERE I.inst_orga=J.id_joueur AND I.id_inst='$id_inst' 
							ORDER BY date
						"
						) or die (mysql_error());
						
	$inst  = mysql_fetch_assoc($inst_);
	
	//BUTIN
	$d_ = mysql_query("
						SELECT D.*, J.nom, O.*
						FROM (".prefix('drops')." D, ".prefix('objets')." O)
						LEFT JOIN ".prefix('joueurs')." J ON J.id_joueur = D.id_joueur
						WHERE D.id_inst='$id_inst' AND D.id_objet=O.id_objet
						ORDER BY objet_nom
					");
	$butin = array();
	$dkp = 0;
	while($d = mysql_fetch_assoc($d_)){
		
		$butin[] = $d;
		$dkp+=$d['drop_dkp'];
	}		
	
	//GROUPS
	?>
	
	<big><?=$inst['inst_nom']?><br>Le <?=aff_date($inst['date'])?><br><b><?=intval($inst['dkp'])?></b> DKP gagnés</big><br/><br/>
	
	<?if($butin[0]){?>
		<div align=right>
			<table style=width:300px;font-size:10px cellspacing=0>
			<tr class=forum>
				<th>Objet
				</th>
				<th>Joueur
				</th>
				<th>DKP
				</th>
				<?if($rank){?>
					<th>&nbsp;
					</th>
				<?}?>
			</tr>
			<?
				$i=0;
				foreach($butin as $b){
					echo "
						<tr ".($i%2 ? 'class=odd':'').">
							<td><a href=view.php?db=".$db."&id_objet=".$b['id_objet'].">".$b['objet_nom']."</a>
							</td>
							<td><a href=view.php?db=".$db."&player=".$b['id_joueur'].">".$b['nom']."</a>
							</td>
							<td>".$b['drop_dkp']."
						</tr>
					";
					$i++;
				}
			?>
			</table>
			<b><?=count($butin)?></b> Butins amassés. <b><?=$dkp?></b> DKP dépensés.
		</div>
		<br>
	<?}?>
	
	<br>
	<table style=width:250px;border:0px;height:15px; cellspacing=0 cellpadding=0>
		<form name=classement action=view.php method=GET>
		<input type=hidden name=db value=<?=$db?>>
		<input type=hidden name=id_inst value=<?=$id_inst?>>
		<tr>
			<td>
				Visualisation par <select name=view onchange="if (this.value != 'NULL') document.classement.submit();">
						<option value=class <?=($view!='group' ? 'SELECTED':'')?>>Classe</option>
						<option value=group <?=($view=='group' ? 'SELECTED':'')?>>Groupe</option>
					</select>
			</td>
		</tr>
		</form>
	</table>
	<br>
	<table cellspacing=0 class=small style=border:0px;>
		<tr>
	<?
	switch($view){
		case 'group':
			
			$i=1;
			$g_key = array_keys($group);
			foreach($group as $g){
				
				echo "
						<td style=width:120px;>
							Groupe de <b>".$g['real_leader_name']."</b>
							<table style=width:130px;margin-left:7px cellspacing=0>
								".($joueur[$g['leader']]['nom'] ? "
								<tr class=leader>
									<td style=background-color:black;width:38px>
										<img style=width:100% src=".$root."/images/classes/".rem_acc(strtolower($joueur[$g['leader']]['classe'])).".gif>
									</td>
									<td>
										".$joueur[$g['leader']]['nom']."
									</td>
								</tr>
								":'')."
					";
				if($g['players'][0])
				foreach($g['players'] as $p){
					echo "
								<tr>
									<td style=background-color:black;>
										<img src=".$root."/images/classes/".rem_acc(strtolower($joueur[$p]['classe'])).".gif>
									</td>
									<td class=player>
										".$joueur[$p]['nom']."
									</td>
								</tr>
						";
				}
			
				echo "
							</table>
						</td>
					";
				$i++;
			}
		break;
		default:
			$c_key = array_keys($classe);
			foreach($classe as $c){
				if($classes[$c]){
					echo "
							<td style=width:120px;>
								<table style=width:130px; cellspacing=0>
									<tr class=leader>
										<td style=background-color:black;width:38px><img style=width:100% src=".$root."/images/classes/".rem_acc(strtolower($c)).".gif>
										</td>
										<td>
											".$c."s<br>(".$count_classe[$c].")
										</td>
									</tr>
						";
					foreach($classes[$c] as $p){
						echo "
									<tr class=player>
										<td colspan=2>
											".$joueur[$p]['nom']."
										</td>
									</tr>
							";
					}
				
					echo "
								</table>
							</td>
						";
				}
			}
		break;
	}
	echo "<td>&nbsp;</td></tr></table>";
}
elseif($player){
	
	$player = intval($_GET['player']);

	$sql = "
			SELECT J.*, L2.id_joueur leader_id, B.id_joueur blacked, L.*
			FROM (".prefix('joueurs')." J)
			LEFT JOIN ".prefix('blacklist')." B ON B.id_joueur=J.id_joueur
			LEFT JOIN ".prefix('liens')." L 	ON L.id_joueur=J.id_joueur
			LEFT JOIN ".prefix('liens')." L2 	ON L2.id_inst=L.id_inst AND L2.is_lead='1' AND L2.id_leader=L.id_leader
			WHERE J.id_joueur = '$player'
		";
	
	//SELECTION DES DONNEES DU PERSO
	$instances 	= array();
	$leader		= array();
	$d_ = mysql_query($sql) or die(mysql_error());
	while($d = mysql_fetch_assoc($d_)){
		
		$instances[] = $d['id_inst'];
		$leader[]	= $d['leader_id'];
		$links[$d['id_inst']] = $d['leader_id'];
		$j = $d;
		$j['dkp'] = 0;
		
	}
	
	//SELECTION DES DONNEES DES INSTANCES POUR HISTORIQUE
	$passif = array();
	if($instances[0]){
		$d_ = mysql_query("SELECT * FROM ".prefix('instances')." WHERE id_inst IN (".implode(',',$instances).") ORDER BY -date") or die(mysql_error());
		while($d = mysql_fetch_assoc($d_)){
			
			$passif[] = $d;
			$j['dkp']+= $d['dkp'];
				
		}
	}
	//SELECTION DES DONNEES DES LEADERS POUR HISTORIQUE
	$chef = array();
	if($leader[0]){
		$d_ = mysql_query("SELECT J.* FROM ".prefix('joueurs')." J WHERE id_joueur IN (".implode(',',$leader).")") or die(mysql_error());
		while($d = mysql_fetch_assoc($d_)){
			
			$chef[$d['id_joueur']] = $d;
				
		}
	}
	
	//SELECTION DES DONNEES DU BUTIN
	$butin = array();
	$d_ = mysql_query("
				SELECT D.*, O.*, I.inst_nom, I.id_inst, I.date
				FROM (".prefix('drops')." D, ".prefix('objets')." O)
				LEFT JOIN ".prefix('instances')." I ON I.id_inst=D.id_inst
				WHERE D.id_joueur='$player' AND D.id_objet=O.id_objet
				ORDER BY -date
				"
				) or die(mysql_error());
	while($d = mysql_fetch_assoc($d_)){
		
		$butin[] = $d;
		$j['dkp']-= $d['drop_dkp'];
			
	}
	
	//AJUSTEMENT DKPs
	$d_ = mysql_query("
						SELECT D.*, J.nom, I.inst_nom, I.date, R.raison
						FROM (".prefix('dkps')." D, ".prefix('joueurs')." J, ".prefix('dkps_raisons')." R, ".prefix('instances')." I)
						WHERE D.id_joueur = J.id_joueur AND D.id_raison=R.id_raison AND D.id_inst=I.id_inst AND D.id_joueur='$player'
						ORDER BY -date,-date_ajust
					"
					) or die(mysql_error());
		
		$ajust = array();
		
		while($d = mysql_fetch_assoc($d_)){
		
			$ajust[] = $d;	
			$j['dkp']+=$d['ajustement'];
			
		}
		
		
	/*
	echo '<pre>';
	print_r($links);
	print_r($passif);
	print_r($chef);
	//*/

	?>
		<big>Fiche de <?=$j['nom']?></big>
		<table style=width:400px;>
			<tr>
				<th style=width:50%>Classe
				</th>
				<td><?=$j['classe']?>
				</td>
			</tr>
			<tr>
				<th>Race
				</th>
				<td><?=$j['race']?>
				</td>
			</tr>
			<tr>
				<th>Guilde
				</th>
				<td><?=$j['guilde']?>
				</td>
			</tr>
			<tr>
				<th>Spécialisation
				</th>
				<td><?=$j['specialisation']?>
				</td>
			</tr>
			<tr>
				<th>Notes
				</th>
				<td><?=$j['notes']?>
				</td>
			</tr>
			<tr>
				<th>Harmonisé
				</th>
				<td><?=($j['harmonise']?'Oui':'Non')?>
				</td>
			</tr>
			<tr>
				<th>Sceau d'Onyxia
				</th>
				<td><?=($j['onyxia']?'Oui':'Non')?>
				</td>
			</tr>
			<tr>
				<th>Antre de l'Aile-Noire
				</th>
				<td><?=($j['blackwing']?'Oui':'Non')?>
				</td>
			</tr>
			<tr>
				<th>DKP
				</th>
				<td><?=$j['dkp']?>
				</td>
			</tr>
		</table><br>
		<big>Historique (<?=count($passif)?> instances)</big>
		<table style=width:700px; cellspacing=0>
		<?
			$i=0;
			foreach($passif as $p){
				echo "
					<tr ".($i%2 ? 'class=odd':'').">
						<td>".($p['date']<date("YmdHis",time())?'A participé au':'Prévu pour')."
						</td>
						<td><a href=view.php?id_inst=".$p['id_inst']."&db=".$db.">".$p['inst_nom']."</a>
						</td>
						<td>du ".aff_date($p['date'])."
						</td>
						<td>sous les ordres de <a href=view.php?player=".$chef[$links[$p['id_inst']]]['id_joueur']."&db=".$db.">".$chef[$links[$p['id_inst']]]['nom']."</a>
						</td>
					</tr>
				";
				$i++;
			}
		?>
		</table>
		
		<br>
		<?if($butin[0]){?>
			<big>Butin</big>
			<table style=width:700px; cellspacing=0>
			<tr class=forum>
				<th>Objet
				</th>
				<th>Instance
				</th>
				<th>Liens
				</th>
				<th>DKP
				</th>
			</tr>
			<?
				$i=0;
				foreach($butin as $b){
					echo "
						<tr ".($i%2 ? 'class=odd':'').">
							<td><a href=view.php?id_objet=".$b['id_objet'].">".$b['objet_nom']."</a>
							</td>
							<td><a href=view.php?id_inst=".$b['id_inst']."&db=".$db.">".$b['inst_nom']."</a> le ".aff_date($b['date'])."
							</td>
							<td>
								".($b['thottbot']?		"<a href=".$b['thottbot']." target=_blank>T</a> | "	:'T | ')."
								".($b['wowdbu']?		"<a href=".$b['wowdbu']." target=_blank>W</a> | "	:'W | ')."
								".($b['allakhazam']?	"<a href=".$b['allakhazam']." target=_blank>A</a>"	:'A')."
							</td>
							<td>".$b['drop_dkp']."								
							</td>
						</tr>
					";
					$i++;
				}
			?>
			</table>
		<?}?>
		<?if($ajust[0]){?>
		<br>
		<big> Ajustements DKPs</big>
				<table style=width:700px; cellspacing=0>
					<tr class=forum>
						<th>Raison
						</th>
						<th>Instance
						</th>
						<th>Ajustement
						</th>
						<th>Ajusté le
						</th>
					</tr>
					
					<?
						foreach($ajust as $k){
							
							echo "
								<tr>
									<td>".$k['raison']."
									</td>
									<td><a href=details.php?id_inst=".$k['id_inst'].">".$k['inst_nom']."</a> le ".aff_date($k['date'])."
									</td>
									<td>".$k['ajustement']."
									</td>
									<td>".aff_date($k['date_ajust'],1)."
									</td>
								</tr>
								";
							
						}
					?>
					
				</table>
		<?}?>
	<?
	
}
elseif($id_objet){
	
	include 'fiche_objet.php';
	
}
elseif(isset($_GET['obj'])){
	
	$id_objet = intval($_REQUEST['id_objet']);		
			
	$d_ = mysql_query("SELECT * FROM (".prefix('objets')." O) ORDER BY objet_nom");
	
	$obj = array();
	while($d = mysql_fetch_assoc($d_)){
		
		$obj[$d['id_objet']] = $d;
		
	}
	
	$d_ = mysql_query("SELECT count(*) as fall,id_objet FROM (".prefix('drops')." O) GROUP BY id_objet");
	while($d = mysql_fetch_assoc($d_)){
		$obj[$d['id_objet']]['fall'] = $d['fall'];
	}
	
	$d_ = mysql_query("SELECT avg(drop_dkp) as `avg`,id_objet FROM (".prefix('drops')." O) GROUP BY id_objet");
	while($d = mysql_fetch_assoc($d_)){
		$obj[$d['id_objet']]['avg'] = $d['avg'];
	}
	
	
	?>
		<big>Liste des Objets</big>
		<table style=width:600px;>
			<tr>
				<th>Objet
				</th>
				<th>Tombé
				</th>
				<th>DKP de Base
				</th>
				<th>DKP Moyen
				</th>
				<?
					$i=0;
					foreach($obj as $o){
						
						echo "
							<tr  ".($i%2?'class=odd':'').">
								<td><a href=view.php?id_objet=".$o['id_objet']."&db=".$db.">".$o['objet_nom']."</a>
								</td>
								<td>".intval($o['fall'])." fois
								</td>
								<td>".$o['objet_dkp']."
								</td>
								<td>".$o['avg']."
								</td>
							</tr>
						";
						$i++;
					}
				?>
		</table>
	<?
	
}
elseif(isset($_GET['players'])){
	
	$ord 	= array('nom','classe','race','guilde','id_leader','harmonise','onyxia','specialisation','notes','blackwing','loot','runs','dkp');
	
	$order 	= $_GET['order'] ? $ord[intval($_GET['order'])] : 'nom';
	
	$sort	= $_GET['sort']=='SORT_DESC' ? SORT_DESC:SORT_ASC;
	$tros	= $_GET['sort']=='SORT_DESC' ? 'SORT_ASC':'SORT_DESC';
	
	$sql = "
			SELECT J.*, J.rank is_leader , B.id_joueur blacked
			FROM (".prefix('joueurs')." J)
			LEFT JOIN ".prefix('blacklist')." B ON B.id_joueur=J.id_joueur
			WHERE  inactive='0'
			
		";
	//ORDER BY ".$order." $sort,nom
	
	$d_ = mysql_query($sql) or die(mysql_error());
	
	while($d = mysql_fetch_assoc($d_)){
		$d['dkp'] = 0;
		$d['loot']= 0;
		$d['runs']= 0;
		$joueur[$d['id_joueur']] = $d;
			
	}
	
	//LOOT
	$d_ = mysql_query("SELECT id_joueur,count(*) as loot FROM ".prefix('drops')." GROUP BY id_joueur");
	while($d = mysql_fetch_assoc($d_)){
		
		if($joueur[$d['id_joueur']])
			$joueur[$d['id_joueur']]['loot'] = $d['loot'];
		
	}
	
	//NB INSTANCES EFFECTUEES
	$d_ = mysql_query("
						SELECT id_joueur,count(*) as inst 
						FROM (".prefix('liens')." L, ".prefix('instances')." I)
						WHERE I.id_inst=L.id_inst AND date < '".date("YmdHis",time())."'
						GROUP BY L.id_joueur
					");
	while($d = mysql_fetch_assoc($d_)){
		
		if($joueur[$d['id_joueur']])
			$joueur[$d['id_joueur']]['runs'] = $d['inst'];
		
	}
	
	//NB DKP GAGNES PAR INSTANCES
	$d_ = mysql_query("
							SELECT id_joueur,dkp  
							FROM (".prefix('instances')." I, ".prefix('liens')." L)
							WHERE L.id_inst=I.id_inst
						");
	while($d = mysql_fetch_assoc($d_)){
		
		if($joueur[$d['id_joueur']])
			$joueur[$d['id_joueur']]['dkp']+= $d['dkp'];
		
	}
	
	//NB DKP "PERDUS" SUR BUTIN
	$d_ = mysql_query("
							SELECT id_joueur,drop_dkp  
							FROM ".prefix('drops')."
						");
	while($d = mysql_fetch_assoc($d_)){
		
		if($joueur[$d['id_joueur']])
			$joueur[$d['id_joueur']]['dkp']-= $d['drop_dkp'];
		
	}

	//NB DKP AJUSTEMENT
	$d_ = mysql_query("
							SELECT *
							FROM ".prefix('dkps')."
						");
	while($d = mysql_fetch_assoc($d_)){
		
		if($joueur[$d['id_joueur']])
			$joueur[$d['id_joueur']]['dkp']+= $d['ajustement'];
		
	}
	
	
	// Obtient une liste de colonnes
	
	foreach ($joueur as $key => $row) {
	    $nom[$key]  = $row['nom'];
	    $sorting[$key] = $row[$order];
	}
	
	// Tri les données par volume décroissant, edition croissant
	// Ajoute $data en tant que premier paramètre, pour trier par la clé commune
	array_multisort($sorting, $sort, $nom, SORT_ASC, $joueur);
		
	?>
		<big><b><?=count($joueur)?></b> Joueurs :</big>
	
		<table cellspacing=0 style=width:1000px>
			<tr>
				<th>Nom 		<a href=view.php?players&db=<?=$db?>&order=0&sort=<?=($_GET['order']==0 ? $tros:'SORT_ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a>
				</th>
				<th>Classe 		<a href=view.php?players&db=<?=$db?>&order=1&sort=<?=($_GET['order']==1 ? $tros:'SORT_ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a>
				</th>
				<th>Race 		<a href=view.php?players&db=<?=$db?>&order=2&sort=<?=($_GET['order']==2 ? $tros:'SORT_ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a>
				</th>
				<th>Guilde 		<a href=view.php?players&db=<?=$db?>&order=3&sort=<?=($_GET['order']==3 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a>
				</th>
				<th>MC			<a href=view.php?players&db=<?=$db?>&order=5&sort=<?=($_GET['order']==5 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a>
				</th>
				<th>Ony 		<a href=view.php?players&db=<?=$db?>&order=6&sort=<?=($_GET['order']==6 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a>
				</th>
				<th>BWL 		<a href=view.php?players&db=<?=$db?>&order=9&sort=<?=($_GET['order']==9 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a>
				</th>
				<th>Loot		<a href=view.php?players&db=<?=$db?>&order=10&sort=<?=($_GET['order']==10 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a>
				</th>
				<th>Runs		<a href=view.php?players&db=<?=$db?>&order=11&sort=<?=($_GET['order']==11 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a>
				</th>
				<th>DKP			<a href=view.php?players&db=<?=$db?>&order=12&sort=<?=($_GET['order']==12 ? $tros:'SORT_DESC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a>
				</th>
			</tr>
		<?
		$i=0;
		foreach($joueur as $j){
			
			echo "
			<tr ".($i%2 ? 'style=background-color:#cee8ce':'').">
				<td style=height:22px;>&nbsp;<b><a href=view.php?db=".$db."&player=".$j['id_joueur'].">".$j['nom']."</a></b>
				</td>
				<td>&nbsp;".$j['classe']."
				</td>
				<td>&nbsp;".$j['race']."
				</td>
				<td>&nbsp;".$j['guilde']."
				</td>
				<td>&nbsp;".($j['harmonise']?"<img src=$root/images/valid.gif>":"<img src=$root/images/invalid.gif>")."
				</td>
				<td>&nbsp;".($j['onyxia']?"<img src=$root/images/valid.gif>":"<img src=$root/images/invalid.gif>")."
				</td>
				<td>&nbsp;".($j['blackwing']?"<img src=$root/images/valid.gif>":"<img src=$root/images/invalid.gif>")."
				</td>
				<td>&nbsp;<b>".$j['loot']."</b>
				</td>
				<td>&nbsp;<b>".$j['runs']."</b>
				</td>
				<td>&nbsp;<b>".$j['dkp']."</b>
				</td>
			</tr>
			";
			$i++;
		}
		?>
		</table>	
	<?
}
else{
	
	$d_ = mysql_query("SELECT * FROM (".prefix('instances')." I, ".prefix('joueurs')." J) WHERE I.inst_orga=J.id_joueur ORDER BY -date") or die (mysql_error());

	$instances = array();
	while($d = mysql_fetch_assoc($d_)){
		
		$tmp = mysql_fetch_row(mysql_query("SELECT count(*) FROM ".prefix('liens')." WHERE id_inst='".$d['id_inst']."'"));
		$d['count'] = $tmp[0];
		$instances[] = $d;
		
	}
	
//INSTANCES DE LA ZONE
	echo "
	<div align=center>
	
	<big><b>".count($instances)."</b> Instances :</big>
	
	<table cellspacing=0 style=width:1000px>
		<tr>
			<th>
				Instance
			</th>
			<th>
				Date
			</th>
			<th>
				Organisateur
			</th>
			<th style=text-align:right>
				Joueurs confirmés
			</th>
			<th style=text-align:right>
				DKP
			</th>
		</tr>
	";
	
	$i=0;
	foreach ($instances as $inst){
	
		echo "
			<tr ".($inst['date']<date("YmdHis",time()) ? 'class=finished': ($i%2 ? 'class=odd':'')).">
				<td>
					<a href=view.php?id_inst=".$inst['id_inst']."&db=$db>".$inst['inst_nom']."</a>
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
					".$inst['dkp']."
				</td>
			</tr>
		";
	$i++;	
	}
	echo "</table>";
?>
<?
}

?>
<?include 'footer.php';?>