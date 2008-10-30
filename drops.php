<?
//////////////////////////////////////////////
// drops.php
// Script des loots
// NE PAS MODIFIER
// Version du 27/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 21/10/2008: Nettoyage du code, première version 
// 27/10/2008 : Joyrock - Ajout des logs et correction de la modification

//Sécurité
$GLOBALS['allowed'] = 1;

//includes
include "commun.inc.php";

//Init des variables
$load 	= null;

//On récupère un paramétre transmis par formulaire
$action = $_REQUEST['action'];
	
switch($action)
{
	case 'add':
		$edit = array();
				
		if($_REQUEST['edit'])
		{
			$id_drop = intval($_REQUEST['edit']);
			$edit = mysql_query("
				SELECT * FROM (".prefix('drops')." D, ".prefix('objets')." O) 
				LEFT JOIN ".prefix('boss')." B ON O.dropped_by=B.real_id_boss
				WHERE id_drop='".$id_drop."' AND D.id_objet = O.real_id
				") or die("Erreur mysql - drops.php:30 : ".mysql_error());
			$edit = mysql_fetch_assoc($edit);
		}
				
		if($_POST['id_boss'])
		{
			$id_inst = intval($_POST['id_inst']);
			$id_joueur = intval($_POST['id_joueur']);
			$id_boss = intval($_POST['id_boss']);
			$id_objet = intval($_POST['id_objet'.$id_boss]);
					
			if(!$id_drop && $id_objet)
			{
				mysql_query("
					INSERT INTO ".prefix('drops')." (`id_inst`,`id_joueur`,`id_objet`)
					VALUES
					('".$id_inst."','".$id_joueur."','".$id_objet."')
					") or die("Erreur mysql - drops.php:47 : ".mysql_error());
				
				//Nom du joueur
				$temp_ = mysql_query("
					SELECT nom
					FROM ".prefix('joueurs')."
					WHERE id_joueur='".$id_joueur."'
					") or die("Erreur mysql - drops.php:56 : ".mysql_error());
				$temp = mysql_fetch_assoc($temp_);
				
				//On log
				add_log($user_name,"Butin","Ajout d\'un loot pour <b>".addslashes($temp['nom'])."</b>");	
				
				$load = $root."/drops.php";
				
			}
			else
			{
				mysql_query("
					UPDATE ".prefix('drops')." SET
					id_joueur 	= '".$id_joueur."',
					id_objet	= '".$id_objet."',
					id_inst		= '".$id_inst."'
					WHERE
					id_drop		= '".$id_drop."'
					") or die("Erreur mysql - drops.php:57 : ".mysql_error());
				
				//Nom du joueur
				$temp_ = mysql_query("
					SELECT nom
					FROM ".prefix('joueurs')."
					WHERE id_joueur='".$id_joueur."'
					") or die("Erreur mysql - drops.php:56 : ".mysql_error());
				$temp = mysql_fetch_assoc($temp_);
				
				//On log
				add_log($user_name,"Butin","Modification d\'un loot de <b>".addslashes($temp['nom'])."</b>");	
				$load = $root."/drops.php";
			}	
		}
		else
		{
			include 'header.php';
					
			$inst = array();
			$d_ = mysql_query("
				SELECT id_inst,inst_nom,date 
				FROM ".prefix('instances')." 
				ORDER BY -date
				") or die("Erreur mysql - drops.php:74 : ".mysql_error());
			
			while($d = mysql_fetch_assoc($d_))
			{
				$inst[] = $d;
			}
					
			$joueur = array();
			$d_ = mysql_query("
				SELECT id_joueur,nom 
				FROM ".prefix('joueurs')." 
				ORDER BY nom
				") or die("Erreur mysql - drops.php:86 : ".mysql_error());
				
			while($d = mysql_fetch_assoc($d_))
			{
				$joueur[] = $d;
			}
			
			//BOSSES
			$boss = array();
			$d_ = mysql_query("
				SELECT * FROM ".prefix('boss')." 
				ORDER BY boss_nom
				") or die("Erreur mysql - drops.php:99 : ".mysql_error());
				
			while($d = mysql_fetch_assoc($d_))
			{
				$boss[] = $d;
			}
			
			if(!isset($edit['real_id']))
			{
				$oldid=0;
			}
			else
			{
				$oldid=$edit['real_id_boss'];
			}
			
			echo "<script>
					var oldId = \"div".$oldid."\";
					
					function display(_value){
					var _id = \"div\"+_value;
					document.getElementById(oldId).style.visibility=\"hidden\";
					document.getElementById(_id).style.visibility=\"visible\";
					oldId = _id;
					}
				</script>";
			
			?>
			<big><?=($_REQUEST['edit'] ? 'EDITION':'AJOUT')?> DE BUTIN</big>
				<table style=width:600px;>
					<form name=drop_form action=drops.php method=POST>
						<input type=hidden name=action value=add>
						<tr>
							<td width=20%>Boss</td>
							<td align=center width=80%>
								<select name=id_boss style=width:90% onchange="display(this.value)">
									<option></option>
									<?
									foreach($boss as $b)
									{
										echo "<option value='".$b['real_id_boss']."' ".($b['real_id_boss']==$edit['real_id_boss'] ? 'SELECTED':'').">".$b['boss_nom']."</option>";
									}
									?>
								</select> 
							</td>
						</tr>
						<tr>
							<td width=20%>Nom de l'objet</td>
							<td align=center width=80%>
								<div style="position:relative;top:0;width:100%">
									<?
									if(!isset($edit['real_id']))
									{
										echo "
											<div id=\"div0\" name=\"boss\" style=\"visibility:visible;position:absolute;top:0;left:0;width:100%\">
												<select style=width:90%>
													<option value=1>&nbsp;</option>
												</select>
											</div>";
									}
								
									foreach($boss as $b)
									{
										$drop = array();
										$d_ = mysql_query("
											SELECT obj_nom, real_id 
											FROM ".prefix('objets')." 
											WHERE dropped_by=".$b['real_id_boss']." 
											ORDER BY obj_nom
											") or die("Erreur mysql - drops.php:166 : ".mysql_error());
											
										while($d = mysql_fetch_assoc($d_))
										{
											$drop[] = $d;
										}
										
										echo "<div id=\"div".$b['real_id_boss']."\" name=\"".$b['boss_nom']."\" style=\"visibility:".($b['real_id_boss']==$edit['real_id_boss'] ? 'visible':'hidden').";position:absolute;top:0;left:0;width:100%\">
												<select name=id_objet".$b['real_id_boss']." style=width:90%>";
									
										foreach($drop as $d)
										{
											echo "<option value='".$d['real_id']."' ".($d['real_id']==$edit['real_id'] ? 'SELECTED':'').">".$d['obj_nom']."</option>";
										}
										
										echo "
												</select>
											</div>";
									}
									?>
								</div>
							</td>
						</tr>
						<tr>
							<td width=20%>Instance</td>
							<td align=center width=80%>
								<select name=id_inst style=width:90%>
									<option></option>
									<?
									foreach($inst as $i)
									{
										echo "<option value=".$i['id_inst']." ".($i['id_inst']==$edit['id_inst']?'SELECTED':(substr($i['date'],0,8)==date("Ymd",time())?'SELECTED':'')).">".$i['inst_nom']." le ".aff_date($i['date'])."</option>";
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td width=20%>Joueur</td>
							<td align=center width=80%>
								<select name=id_joueur style=width:90%>
									<option></option>
									<?
									foreach($joueur as $j)
									{
										echo "<option value=".$j['id_joueur']." ".($j['id_joueur']==$edit['id_joueur']?'SELECTED':'').">".$j['nom']."</option>";
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan=3 align=center><input type=submit value="<?=($_REQUEST['edit'] ? 'Modifier':'Ajouter')?>"></td>
						</tr>
						<input type=hidden name=edit value="<?=$edit['id_drop']?>">
					</form>
					<tr>
					<?
					if($rank>=3)
					{
					?>
						<td colspan=3 align=center>
							<form name=ajout_objet action=add_objet.php method=POST>
								<input type=submit value="Ajouter Nouvel objet">
							</form>
						</td>
					<?
					}
					?>
					</tr>
				</table>
				<br>
				<br>
		<?
		//Pied de page
		include 'footer.php';
		}
	break;
	
	case 'del':
	
		$delid = intval($_REQUEST['del']);
		$verif = $_GET['verif'] ? true:false;
	        
		if($verif)
		{
			$temp_ = mysql_query("
				SELECT * FROM (".prefix('objets')." O,".prefix('drops')." D) 
				LEFT JOIN ".prefix('joueurs')." J ON D.id_joueur=J.id_joueur
				WHERE id_drop='".$delid."' AND D.id_objet = O.real_id
				") or die("Erreur mysql - drops.php:281 : ".mysql_error());
			$temp = mysql_fetch_assoc($temp_);
			
			mysql_query("
				DELETE FROM ".prefix('drops')." 
				WHERE id_drop='".$delid."'
				") or die("Erreur mysql - drops.php:288 : ".mysql_error());
			
			//log
			add_log($user_name,"Butin","Suppression du loot <b>".addslashes($temp['obj_nom'])."</b> assigné à <b>".addslashes($temp['nom'])."</b>");

			$load = $root.'/drops.php';
		}
		else
		{
			include 'header.php';
			?>
			<big>Etes vous sûr ?</big>
			<br>
			<br>
			<a href=drops.php?del=<?=$delid?>&action=del&verif=1>OUI</a> / <a href=drops.php>NON</a>
			<?
			include 'footer.php';
		}
		
	break;
	
	case 'obj':
				
		$id_objet = intval($_REQUEST['id_objet']);		
			
		if($id_objet)
		{
			include 'header.php';
			include 'fiche_objet.php';
			include 'footer.php';
		}
		else
		{
			if($_GET['del'] && $rank>=3)
			{
				$del = intval($_GET['del']);
				$d = mysql_query("
					SELECT count(*) as fall 
					FROM ".prefix('drops')." 
					WHERE id_objet='".$del."'
					") or die("Erreur mysql - drops.php:293 : ".mysql_error());
				$d = mysql_fetch_assoc($d);
				
				if(!$d['fall'])
				{
					$temp_ = mysql_query(
						"SELECT * FROM ".prefix('objets')." 
						WHERE real_id='".$del."'
					") or die("Erreur mysql - drops.php:336 : ".mysql_error());
					$temp = mysql_fetch_assoc($temp_);
					
					mysql_query("
						DELETE FROM ".prefix('objets')." 
						WHERE real_id='".$del."' LIMIT 1
						") or die("Erreur mysql - drops.php:342 : ".mysql_error());
						
					mysql_query("
						DELETE FROM ".prefix('wishlist')." 
						WHERE id_objet='".$del."'
						") or die("Erreur mysql - drops.php:347 : ".mysql_error());
			
					//log
					add_log($user_name,"Butin","Suppression dans la base de l\'objet <b>".addslashes($temp['obj_nom'])."</b>");
					
					
					$msg['message'] = "Objet effacé.";
				}
				else
				{
					$msg['erreur'] = "Objet droppé, impossible à effacer.";
				}
			}
				
			$ord 	= array('obj_nom','fall','boss_nom');
			$order 	= $_GET['order'] ? $ord[intval($_GET['order'])] : 'obj_nom';
			$sort	= $_GET['sort']=='SORT_DESC' ? SORT_DESC:SORT_ASC;
			$tros	= $_GET['sort']=='SORT_DESC' ? 'SORT_ASC':'SORT_DESC';
			
			$d_ = mysql_query("
				SELECT O.*, boss_nom
				FROM (".prefix('objets')." O) 
				LEFT JOIN ".prefix('boss')." B ON O.dropped_by=B.real_id_boss
				ORDER BY obj_nom
				") or die("Erreur mysql - drops.php:325 : ".mysql_error());
				
			$obj = array();
			while($d = mysql_fetch_assoc($d_))
			{
				$obj[$d['real_id']] = $d;
			}
				
			$d_ = mysql_query("
				SELECT id_objet,poids 
				FROM ".prefix('wishlist')." 
				ORDER BY id_objet
				") or die("Erreur mysql - drops.php:338 : ".mysql_error());
				
			while($d = mysql_fetch_assoc($d_))
			{
				$obj[$d['real_id']]['appeal']+= 1 / ($d['poids']+1);
			}				
				
			$d_ = mysql_query("
				SELECT count(*) as fall,id_objet 
				FROM (".prefix('drops')." O) GROUP BY id_objet
				") or die("Erreur mysql - drops.php:350 : ".mysql_error());
			
			while($d = mysql_fetch_assoc($d_))
			{
				$obj[$d['id_objet']]['fall'] = $d['fall'];
			}
				
			// Obtient une liste de colonnes
			foreach ($obj as $key => $row) 
			{
				$nom[$key]  = $row['obj_nom'];
				$sorting[$key] = $row[$order];
			}
				
			// Tri les données par volume décroissant, edition croissant
			// Ajoute $data en tant que premier paramètre, pour trier par la clé commune
			array_multisort($sorting, $sort, $nom, SORT_ASC, $obj);
				
			include 'header.php';
			?>
			<big> <a href=drops.php>Butin des Raids</a> / Liste des Objets</big>
			<table cellspacing=0>
				<tr>
					<th>Objet <a href=drops.php?action=obj&order=0&sort=<?=($_GET['order']==0 ? $tros:'SORT_ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
					<th>Tombé <a href=drops.php?action=obj&order=1&sort=<?=($_GET['order']==1 ? $tros:'SORT_ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
					<th>Sur <a href=drops.php?action=obj&order=2&sort=<?=($_GET['order']==2 ? $tros:'SORT_ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				</tr>
				<?
				$i=0;
				foreach($obj as $o)
				{
					echo "
					<tr  ".($i%2?'class=odd':'').">
						<td><a href=drops.php?action=obj&id_objet=".$o['real_id'].">".($o['obj_nom']?$o['obj_nom']:'?')."</a> </td>
						<td>".intval($o['fall'])." fois ".( $o['fall'] || $rank<3 ? '':"<a href=drops.php?action=obj&del=".$o['real_id']."><img src=images/del.gif></a>")."</td>
						<td>".($rank>=3 ? ($o['boss_nom'] ? $o['boss_nom']:"<a href=drops.php?action=objedit&id_objet=".$o['real_id'].">?</a>"):($o['dropped_by'] ? $o['boss_nom']:'?'))."</td>
					</tr>";
					$i++;
				}
				?>
					<tr>
					<?
					if($rank>=3)
					{
					?>
						<td colspan=3>
							<form name=ajout_objet action=add_objet.php method=POST>
								<input type=submit value="Ajouter Nouvel objet">
							</form>
						</td>
					<?
					}
					?>
					</tr>
			</table>
		<?
		include 'footer.php';
		}
			
	break;

	case 'objedit':
	
		$id_objet= intval($_REQUEST['id_objet']);
		
		if($_POST['id_boss'])
		{
			$id_boss = intval($_POST['id_boss']);
			$img = '';
			$car = '';
				
			mysql_query("
				UPDATE ".prefix('objets')." 
				SET dropped_by='".$id_boss."' 
				WHERE real_id='".$id_objet."'
				") or die("Erreur mysql - drops.php:466 : ".mysql_error());
			
			$temp_ = mysql_query("
			SELECT * FROM ".prefix('objets')." 
			WHERE real_id='".$id_objet."'
			") or die("Erreur mysql - drops.php:473 : ".mysql_error());
			$temp = mysql_fetch_assoc($temp_);
			
			//log
			add_log($user_name,"Butin","Modification de l\'objet <b>".addslashes($temp['obj_nom'])."</b>");
			
			$load = $root."/drops.php?action=obj&id_objet=".$id_objet;
			break;
		}
		
		$objet = mysql_query("
			SELECT * FROM ".prefix('objets')." 
			WHERE real_id='".$id_objet."'
			") or die("Erreur mysql - drops.php:416 : ".mysql_error());
		$objet = mysql_fetch_assoc($objet);
		
		//BOSSES
		$boss = array();
		$d_ = mysql_query("
			SELECT * 
			FROM ".prefix('boss')." 
			ORDER BY boss_nom
			") or die("Erreur mysql - drops.php:424 : ".mysql_error());
		
		while($d = mysql_fetch_assoc($d_)) $boss[] = $d;
		
		//En-tête
		include 'header.php';
		?>
		<table style=width:400px>
			<form name=drop_form action=drops.php?action=objedit&id_objet=<?=$id_objet?> method=POST>
				<tr>
					<th>Nom</th>
					<td><input type=text name=obj_nom value="<?=$objet['obj_nom']?>" DISABLED></td>
				</tr>
				<tr>
					<th style=width:40%>Tombe sur</th>
					<td>
						<select name=id_boss style=width:44% onchange="if (this.value == '-1') document.drop_form.new_boss.disabled=0; else document.drop_form.new_boss.disabled=1;">
							<?
							foreach($boss as $b)
							{
								echo "<option value='".$b['real_id_boss']."' ".($b['real_id_boss']==$objet['dropped_by'] ? 'SELECTED':'').">".$b['boss_nom']."</option>";
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th>Liens</th>
					<td>
						<?
						echo "<a href=\"http://thottbot.com/i".$objet['real_id']."\" target=_blank>T</a> |
						<a href=\"http://fr.wowhead.com/?item=".$objet['real_id']."\" target=_blank>W</a> |
						<a href=\"http://wow.allakhazam.com/db/item.html?witem=".$objet['real_id'].";source=live;locale=frFR\" target=_blank>A</a>";
						?>
					</td>
				</tr>
				<tr>
					<td colspan=2 align=center>
						<input type=submit value=Editer>
					</td>
				</tr>
			</form>
		</table>
		<?
		//Pied de page
		include 'footer.php';
	break;

	default:
		$ord = array('-I.date','J.nom','obj_nom');
		$order 	= $ord[intval($_GET['order'])];
		$sort	= $_GET['sort']=='DESC' ? 'DESC':'ASC';
		$tros	= $_GET['sort']=='DESC' ? 'ASC':'DESC';
				
		$d_ = mysql_query("
			SELECT D.*, J.nom, I.inst_nom, I.date, O.*
			FROM (".prefix('drops')." D, ".prefix('objets')." O)
			LEFT JOIN ".prefix('instances')." I ON I.id_inst=D.id_inst
			LEFT JOIN ".prefix('joueurs')." J	ON J.id_joueur=D.id_joueur
			WHERE D.id_objet = O.real_id
			ORDER BY ".$order." ".$sort.",nom
			") or die("Erreur mysql - drops.php:527 : ".mysql_error());
			
		$drop = array();
		while($d = mysql_fetch_assoc($d_))
		{
			$drop[] = $d;
		}
			
		//En-tête
		include 'header.php';
		?>
		<big>Butin des Raids / <a href=drops.php?action=obj>Liste des Objets</a></big>
		<table>
			<tr>
				<td>
					<form action=drops.php method=POST>
						<input type=submit value=Ajouter>
						<input type=hidden name=action value=add>
					</form>
				</td> 
			</tr>
			<tr>
				<th>Instance <a href=drops.php?order=0&sort=<?=($_GET['order']==0 ? $tros:'ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Possesseur <a href=drops.php?order=1&sort=<?=($_GET['order']==1 ? $tros:'ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Objet <a href=drops.php?order=2&sort=<?=($_GET['order']==2 ? $tros:'ASC')?>><img border=0 src=<?=$root?>/images/arrow.gif></a></th>
				<th>Liens</th>
				<?
				if ($rank>=3)
				{
				echo "<th>&nbsp;</th>";
				}
				?>
			</tr>
			<? 
			$i=0;
			foreach ($drop as $d)
			{
				echo "
					<tr ".($i%2?'class=odd':'').">
						<td><a href=details.php?id_inst=".$d['id_inst'].">".$d['inst_nom']."</a> le ".aff_date($d['date'])."</td>
						<td>".($rank ? "<a href=info_player.php?player=".$d['id_joueur'].">":'')."".$d['nom']."".($rank ? "</a>":'')."</td>
						<td><a href=drops.php?action=obj&id_objet=".$d['real_id'].">".$d['obj_nom']."</a></td>
						<td>
							<a href=\"http://thottbot.com/i".$d['real_id']."\" target=_blank>T</a> |
							<a href=\"http://fr.wowhead.com/?item=".$d['real_id']."\" target=_blank>W</a> |
							<a href=\"http://wow.allakhazam.com/db/item.html?witem=".$d['real_id'].";source=live;locale=frFR\" target=_blank>A</a>
						</td>
						".($rank>=3 ? "<td align=center><a href=drops.php?edit=".$d['id_drop']."&action=add><img src=".$root."/images/edit.gif></a> <a href=drops.php?del=".$d['id_drop']."&action=del><img src=".$root."/images/del.gif></td>":'')."
					</tr>";
				$i++;
			}
			?>
			<tr>
				<td>
					<form action=drops.php method=POST>
						<input type=submit value=Ajouter>
						<input type=hidden name=action value=add>
					</form>
				</td>
			</tr>
		</table>
		<?
		//Pied de page
		include 'footer.php';
		
	break;
}

if($load)
{
	include 'header.php';
	echo '<big>ACTION EFFECTUEE</big><br><meta http-equiv="Refresh" content="2; url='.$load.'" /><a href='.$load.'>Si la page ne se recharge pas cliquer sur ce lien.</a>';
	include 'footer.php';
	exit;
}	
?>
