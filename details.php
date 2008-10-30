<?
//////////////////////////////////////////////
// details.php
// Script de gestion des Line UP
// NE PAS MODIFIER
// Version du 24/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 21/10/2008: Nettoyage du code, première version 
// 24/10/2008 - Joyrock : Ajout des logs

//Sécurité
$GLOBALS['allowed'] = 1;

//includes
include "commun.inc.php";
include 'bbcode.php';

//Variables
$abs = array();
$group25		= array();
$joueur25 		= array();
$count_classe25 = array();
$count_raid25	= 0;
$group10 		= array();
$joueur10 		= array();
$count_classe10 = array();
$count_raid10	= 0;
$mess = array();
$butin = array();
$disp = array();
$abs = array();
$mail = array();

//On récupère l'id du raid
$id_inst = intval($_GET['id_inst']);

//Mode de visualisation des groupes (groupe ou classe)
$view	 = $_REQUEST['view'];

//MAJ du status des joueurs en vacance
if($rank>=3)
{
	$d_ = mysql_query("
		SELECT *
		FROM ".prefix('joueurs')."
		WHERE vacance='1'
		") or die("Erreur mysql - details.php:29 : ".mysql_error());
		
	//Pour chaque joueur en vacance
	while($d = mysql_fetch_assoc($d_))
	{
		//On supprime les dispos et sélections du joueur
		mysql_query("
			DELETE FROM ".prefix('dispo')." 
			WHERE id_joueur = '".$d[id_joueur]."' 
			AND id_inst='".$id_inst."'
			") or die("Erreur mysql - details.php:39 : ".mysql_error());
			
		mysql_query("
			DELETE FROM ".prefix('liens')."
			WHERE id_joueur = '".$d[id_joueur]."'
			AND id_inst = '".$id_inst."'
			") or die("Erreur mysql - details.php:45 : ".mysql_error());
			
		//On met à jour son status sur ABS
		mysql_query("
			INSERT INTO ".prefix('dispo')." (`id_joueur`,`id_inst`,`dispo`,`dispo_date`) 
			VALUES ('".$d[id_joueur]."','".$id_inst."','3','".date("YmdHis",time())."')
			") or die("Erreur mysql - details.php:52 : ".mysql_error());
	}
}

//Récupération en base de toutes les informations de l'instance en cours
$inst_ = mysql_query("	
		SELECT * FROM (".prefix('instances')." I, ".prefix('joueurs')." J)
		WHERE I.inst_orga=J.id_joueur AND I.id_inst='".$id_inst."'
		ORDER BY date
		") or die("Erreur mysql - details.php:131 : ".mysql_error());
$inst  = mysql_fetch_assoc($inst_);

//On ajoute un user à la Line up du raid 25
if($_GET['add25'] && $rank>=3)
{
	//Recupération de l'id du joueur à rajouter
	$add25 = intval($_GET['add25']);
	
	//Si on s'ajoute soit même, on est leader du groupe
	$is_lead = $id==$add25 ? 1:0;
	
	//On insère en base
	mysql_query("
		INSERT INTO ".prefix('liens')." (id_inst,id_leader,id_joueur,is_lead,type_raid) 
		VALUES ('".$id_inst."','".$id."','".$add25."','".$is_lead."','25')
		") or die("Erreur mysql - details.php:68 : ".mysql_error());
	
	//Nom du joueur
	$temp_ = mysql_query("
		SELECT nom
		FROM ".prefix('joueurs')."
		WHERE id_joueur='".$add25."'
		") or die("Erreur mysql - details.php:90 : ".mysql_error());
	$temp = mysql_fetch_assoc($temp_);
	
	//On log
	add_log($user_name,"Line Up","Ajout de <b>".$temp['nom']."</b> à la Line up 25 du raid du <b>".aff_date($inst['date'])."</b>");
	
	//Si j'ai ajouté un leader je vire l'ancien leader
	if($is_lead)
	{
		mysql_query("
			UPDATE ".prefix('liens')." 
			SET is_lead='0' 
			WHERE id_inst='".$id_inst."' 
			AND id_joueur!='".$id."' 
			AND id_leader='".$id."'
			AND type_raid='25'
			") or die("Erreur mysql - details.php:76 : ".mysql_error());
	}
}

//On ajoute un user à la Line up du raid 10
if($_GET['add10'] && $rank>=3)
{
	//Recupération de l'id du joueur à rajouter
	$add10 = intval($_GET['add10']);
	
	//Si on s'ajoute soit même, on est leader du groupe
	$is_lead = $id==$add10 ? 1:0;
	
	//On insère en base
	mysql_query("
		INSERT INTO ".prefix('liens')." (id_inst,id_leader,id_joueur,is_lead,type_raid) 
		VALUES ('".$id_inst."','".$id."','".$add10."','".$is_lead."','10')
		") or die("Erreur mysql - details.php:97 : ".mysql_error());
		
	//Nom du joueur
	$temp_ = mysql_query("
		SELECT nom
		FROM ".prefix('joueurs')."
		WHERE id_joueur='".$add10."'
		") or die("Erreur mysql - details.php:90 : ".mysql_error());
	$temp = mysql_fetch_assoc($temp_);
	add_log($user_name,"Line Up","Ajout de <b>".$temp['nom']."</b> à la Line up 10 du raid du <b>".aff_date($inst['date'])."</b>");

	//Si j'ai ajouté un leader je vire l'ancien leader
	if($is_lead)
	{
		mysql_query("
			UPDATE ".prefix('liens')." 
			SET is_lead='0' 
			WHERE id_inst='".$id_inst."' 
			AND id_joueur!='".$id."' 
			AND id_leader='".$id."'
			AND type_raid='10'
			") or die("Erreur mysql - details.php:105 : ".mysql_error());
	}	
}

//On supprime de la Line UP
if($_GET['delraid'] && $rank>=3)
{
	//Recupération de l'id du joueur à enlever
	$delraid = intval($_GET['delraid']);
	
	//On l'enlève de la base
	mysql_query("
		DELETE FROM ".prefix('liens')." 
		WHERE id_inst='".$id_inst."' 
		AND id_joueur='".$delraid."'
		") or die("Erreur mysql - details.php:123 : ".mysql_error());
		
	//Nom du joueur
	$temp_ = mysql_query("
		SELECT nom
		FROM ".prefix('joueurs')."
		WHERE id_joueur='".$delraid."'
		") or die("Erreur mysql - details.php:172 : ".mysql_error());
	$temp = mysql_fetch_assoc($temp_);
	add_log($user_name,"Line Up","Suppression de <b>".$temp['nom']."</b> de la Line up du raid du <b>".aff_date($inst['date'])."</b>");
}

//JOUEURS Raid 25
$d_ = mysql_query("
		SELECT DISTINCT J.*, L.id_leader, L.is_lead current_lead, jj.nom leader_nom, jj.id_joueur leader_id_joueur
		FROM (".prefix('joueurs')." J, ".prefix('liens')." L)
		LEFT JOIN ".prefix('joueurs')." jj ON L.id_leader=jj.id_joueur
		WHERE L.id_inst='".$id_inst."' AND L.id_joueur=J.id_joueur AND L.type_raid='25'
		ORDER BY nom
		") or die("Erreur mysql - details.php:139 : ".mysql_error());

while($d = mysql_fetch_assoc($d_))
{
	if(!$d['current_lead'])
	{
		$group25[$d['id_leader']]['players'][]= $d['id_joueur'];
	}
	else
	{
		$group25[$d['id_leader']]['leader']	= $d['id_joueur'];
	}
		
	$group25[$d['id_leader']]['real_leader_name'] = $d['leader_nom'];
	$group25[$d['id_leader']]['real_leader_id'] = $d['leader_id_joueur'];
	
	$joueur25[$d['id_joueur']] = $d;
	$count_classe25[$d['classe']]++;
	$classes25[$d['classe']][] = $d['id_joueur'];
	$count_raid25++;
}

//JOUEURS Raid 10
$d_ = mysql_query("
	SELECT DISTINCT J.*, L.id_leader, L.is_lead current_lead, jj.nom leader_nom, jj.id_joueur leader_id_joueur
	FROM (".prefix('joueurs')." J, ".prefix('liens')." L)
	LEFT JOIN ".prefix('joueurs')." jj ON L.id_leader=jj.id_joueur
	WHERE L.id_inst='".$id_inst."' AND L.id_joueur=J.id_joueur AND L.type_raid='10'
	ORDER BY nom
	") or die("Erreur mysql - details.php:174 : ".mysql_error());
			
while($d = mysql_fetch_assoc($d_))
{
	if(!$d['current_lead'])
	{
		$group10[$d['id_leader']]['players'][]= $d['id_joueur'];
	}
	else
	{
		$group10[$d['id_leader']]['leader']	= $d['id_joueur'];
	}
		
	$group10[$d['id_leader']]['real_leader_name'] = $d['leader_nom'];
	$group10[$d['id_leader']]['real_leader_id'] = $d['leader_id_joueur'];
	
	$joueur10[$d['id_joueur']] = $d;
	$count_classe10[$d['classe']]++;
	$classes10[$d['classe']][] = $d['id_joueur'];
	$count_raid10++;
}

//Liste des messages liés à cette instance
$d_ = mysql_query("
	SELECT M.*,J.nom
	FROM (".prefix('messages')." M)
	LEFT JOIN ".prefix('joueurs')." J ON J.id_joueur=M.id_joueur 
	WHERE M.id_inst = '".$id_inst."'
	ORDER BY mess_date
	") or die("Erreur mysql - details.php:209 : ".mysql_error());

while($d = mysql_fetch_assoc($d_))
{
	$mess[] = $d;
}

//Liste des loots récupérés durant l'instance
$d_ = mysql_query("
	SELECT D.*, J.nom, O.*
	FROM (".prefix('drops')." D, ".prefix('objets')." O)
	LEFT JOIN ".prefix('joueurs')." J ON J.id_joueur = D.id_joueur
	WHERE D.id_inst='".$id_inst."' AND D.id_objet=O.real_id
	ORDER BY obj_nom
") or die("Erreur mysql - details.php:226 : ".mysql_error());

while($d = mysql_fetch_assoc($d_))
{
	$butin[] = $d;
}		

//Liste des joueurs disponibles mais pas encore selectionnés dans la Line Up
$d_ = mysql_query("
	SELECT J.*, L.id_inst as taken, D.dispo, D.dispo_date
	FROM (".prefix('joueurs')." J, ".prefix('dispo')." D)
	LEFT JOIN ".prefix('liens')." L ON (J.id_joueur = L.id_joueur AND L.id_inst='".$id_inst."')
	WHERE J.id_joueur=D.id_joueur AND D.id_inst='".$id_inst."'
	ORDER BY dispo,classe, dispo_date,nom
	") or die("Erreur mysql - details.php:242 : ".mysql_error());

while($d = mysql_fetch_assoc($d_))
{
	if(($d['dispo']==2 or $d['dispo']==1 or $d['dispo']==4) && !$d['taken'])
	{
		$disp[] = $d;
	}
}

//Liste des joueurs ABS
$d_ = mysql_query("
	SELECT J.*, L.id_inst as taken, D.dispo, D.dispo_date
	FROM (".prefix('joueurs')." J, ".prefix('dispo')." D)
	LEFT JOIN ".prefix('liens')." L ON (J.id_joueur = L.id_joueur AND L.id_inst='".$id_inst."')
	WHERE J.id_joueur=D.id_joueur AND D.id_inst='".$id_inst."'
	ORDER BY dispo,classe, dispo_date,nom")  or die("Erreur mysql - details.php:262 : ".mysql_error());

while($d = mysql_fetch_assoc($d_))
{
	if($d['dispo']==3)
	{
		$abs[] = $d;
	}	
}		

//Suppression de l'instance
if(isset($_GET['del']) && $rank >= 3)
{
	//On a confirmé la suppression
	if($_GET['del'])
	{	
		echo "Effacement...<br>";
		//L'instance
		mysql_query("
			DELETE FROM ".prefix('instances')." 
			WHERE id_inst='".$id_inst."'
			") or die("Erreur mysql - details.php:278 : ".mysql_error());
		$eff['instances'] = mysql_affected_rows();
		//Ses messages
		mysql_query("
			DELETE FROM ".prefix('messages')." 
			WHERE id_inst='".$id_inst."'
			") or die("Erreur mysql - details.php:284 : ".mysql_error());
		$eff['messages'] = mysql_affected_rows();
		//Ses sélectionnés
		mysql_query("
			DELETE FROM ".prefix('liens')." 
			WHERE id_inst='".$id_inst."'
			") or die("Erreur mysql - details.php:290 : ".mysql_error());
		$eff['participants'] = mysql_affected_rows();
		//Ses loots
		mysql_query("
			DELETE FROM ".prefix('drops')." 
			WHERE id_inst='".$id_inst."'
			") or die("Erreur mysql - details.php:296 : ".mysql_error());
		$eff['butins'] = mysql_affected_rows();
		
		$k = array_keys($eff);
		
		//En tête
		include 'header.php';
		
		//Corp de page
		for($i=0;$i<count($k);$i++)
		{			
			echo "<b>".$eff[$k[$i]]."</b> ".ucfirst($k[$i])."<br>";
		}
		echo "<br><br><a href=index.php>TERMINE</a><br><br>";
		
		//Pied de page
		include 'footer.php';
		exit;
	}
	else
	{
		//On demande la confirmation
		//En-tête
		include 'header.php';
		
		//Corp de page
		?>
		<big>êtes vous sûr ?</big>
		<br>
		<br>
		<a href=details.php?id_inst=<?=$inst['id_inst']?>&del=1>OUI</a> / <a href=details.php?id_inst=<?=$inst['id_inst']?>>NON</a>
		<?
		
		//Pied de page
		include 'footer.php';
		exit;
	}	
}

//En-tête
include 'header.php';

//Corp de page
?>
<div align=center>
	<big>
		<?
		if($rank>=3)
		{
			echo $inst['inst_nom'];
		}
		else
		{
			echo "Instance surprise";
		}
		echo " le ".aff_date($inst['date']);
		?>
	</big>
	<table style='width:98%;border:0px solid'>
		<tr>
			<!--Bloc de gauche-->
			<td style=width:35%;>
				<?
				if($disp[0]) 
				{
				//Liste des joueurs dispos (gauche)
				?>
				<table style=width:95% cellspacing=0>
					<tr class=forum>
						<th colspan=3>Joueurs Libres : Disponibles, Non confirmés ou Réservistes.</th>
						<th>10</th>
						<th>25</th>
					</tr>
					<?
					$i=0;
					foreach($disp as $d)
					{
						echo "
						<tr ".($i%2 ? 'class=odd':'').">
							<td><a href=info_player.php?player=".$d['id_joueur'].">".$d['nom']."</a></td>
							<td>".$dispo_short[intval($d['dispo'])]."
								<br>".($rank ? aff_date($d['dispo_date'],1) :'')."
							</td>
							<td>".$d['classe']."</td>
							<td>
						";
						if($rank>=3)
						{
							echo "<a href=details.php?id_inst=".$id_inst."&add10=".$d['id_joueur']."><img alt='Ajouter ce joueur au raid 10' src=images/spe10.gif></a>";
						};
						
						echo "</td><td>";
						
						if($rank>=3)
						{
							echo "<a href=details.php?id_inst=".$id_inst."&add25=".$d['id_joueur']."><img alt='Ajouter ce joueur au raid 25' src=images/spe.gif></a>";
						};
						?>
							</td>
					</tr>
					<?
						$i++;
					}
					?>
				</table>
				
				<!-- Liste des joueurs absents (gauche)-->
				<table style=width:95% cellspacing=0>
					<tr class=forum>
						<th colspan=5>Joueurs Absents.</th>
					</tr>
					<tr class=odd>
						<td>
							<?
							$i=0;
							foreach($abs as $d)
							{
								echo "<a href=info_player.php?player=".$d['id_joueur'].">".$d['nom']."</a> - ";
								$i++;
							}
							?>
						</td>
					</tr>
				</table>
				<?
				}
				else
				{
					echo "&nbsp;";
				}
				?>
			</td>
			
			<!--Bloc des messages (central) -->
			<td style=width:45%>
				<table style=width:100% cellspacing=4>
					<tr>
						<td valign=top>
							<?
							if($rank>=3)
							{
								echo "<b>".$inst['inst_nom']."</b><br>";
							}
								else
							{	
								echo "<b>Instance surprise</b><br>";
							}
							echo "<b>".$inst_type[$inst['inst_type']]."</b><br>	Le ".aff_date($inst['date'])."<br>";
							?>
						</td>
						<td>
							<?
							if($rank >= 3)
							{
							?>
							<form action=details.php?id_inst=<?echo $inst['id_inst'];?>&del=0 method=POST>
								<input type=submit value="Effacer l'instance" style=width:200px;background-color:red;color:white>
							</form>
							<form action=mod_instance.php?id_inst=<?=$inst['id_inst']?> method=POST>
								<input type=submit value="Changer l'instance" style=width:200px;background-color:red;color:white>
							</form>
							<?
							}
							?>
						</td>
						<td rowspan=3>
							<table cellspacing=0 style=border:0px;>
							<?
							foreach($classe as $c)
							{
								$img_classe=rem_acc($c);
								$img_classe=strtolower($img_classe);
								echo"<tr class=player><td><img src=$root/images/classes/".$img_classe.".gif></td><td>$c</td><td>".intval($count_classe25[$c]+$count_classe10[$c])."</td></tr>";
							}
							?>
							</table>
							<center><b>Raid 25 : <font style=color:blue><? echo $count_raid25;?></font></b> joueur(s).</center>
							<center><b>Raid 10 : <font style=color:blue><? echo $count_raid10;?></font></b> joueur(s).</center>
						</td>
					</tr>
					<tr>
						<td align=center colspan=2>
							<form action=add_message.php?id_inst=<?=$id_inst?> method=POST>
								<input type=submit value="Nouveau Message">
							</form>
						</td>
					</tr>
					<tr>
						<td class=mess style=height:300px;width:450px colspan=2>	
							<div>
								<table cellspacing=0>
								<?
								$i=0;
								foreach($mess as $m)
								{
									echo "
									<tr ".($i%2 ? 'class=odd':'').">
										<th rowspan=2 style=width:15%;>
											<a href=info_player.php?player=".$m['id_joueur'].">".$m['nom']."</a>
											".($m['id_joueur'] == $id ? "
											<br>
											<a href=add_message.php?mess=".$m['id_mess']."&action=edit><img src=".$root."/images/edit.gif></a>
											<a href=add_message.php?mess=".$m['id_mess']."&action=del><img src=".$root."/images/del.gif></a>
											":'')."
										</th>
										<th style=width:30%>&nbsp;".$m['mess_titre']."</th>
										<th style=text-align:right>".aff_date($m['mess_date'])."</th>
									</tr>
									<tr>
										<td colspan=2 align=justify>".bb(nl2br(strip_tags($m['mess_data'])))."</td>
									</tr>";
									$i++;
								}
								?>
								</table>
							</div>
						</td>
					</tr>
					<?
					if($rank>=3)
					{
					?>
					<tr>
						<th style=text-align:center;vertical-align:middle;font-size:10px>
						<?="<a href=details.php?view=mail&id_inst=".$inst['id_inst']."&grp=0><img style=width:10px src=images/mail.gif> Alerte Mail Pour Tous</a>"?>
						</th>
					</tr>
					<?}?>
				</table>
			</td>
			<!--Bloc des loots (droite) -->
			<td style=width:20% align=center>
				<?
				if($butin[0])
				{
				?>
					<table style=width:95%;font-size:10px cellspacing=0>
						<tr class=forum>
							<th>Objet</th>
							<th>Joueur</th>
							<th>&nbsp;</th>
						</tr>
						<?
						$i=0;
						foreach($butin as $b)
						{
							echo "<tr ".($i%2 ? 'class=odd':'').">
									<td><a href=drops.php?action=obj&id_objet=".$b['real_id'].">".$b['obj_nom']."</a></td>
									<td><a href=info_player.php?player=".$b['id_joueur'].">".$b['nom']."</a></td>
									<td align=center>
										".($rank>=3 ? "<a href=drops.php?edit=".$b['id_drop']."&action=add25><img src=".$root."/images/edit.gif></a>
										":'')."
									</td>
								</tr>
								";
							$i++;
						}
						?>
					</table>
					<b><?=count($butin)?></b> Butins amassés. <b>
				<?
				}
				else
				{
					echo "&nbsp;";
				}
				?>
			</td>
		</tr>
	</table>
</div>
<br>
<!--Liste des Line up-->
<?
//Choix du mode de visualisation des groupes
if(!$view || $view=='class' || $view=='group')
{
?>
<table style=width:250px;border:0px;height:15px; cellspacing=0 cellpadding=0>
	<form name=classement action=details.php?id_inst=<?=$id_inst?> method=POST>
		<tr>
			<td>
				Visualisation par 
				<select name=view onchange="if (this.value != 'NULL') document.classement.submit();">
					<option value=class <?=($view!='group' ? 'SELECTED':'')?>>Classe</option>
					<option value=group <?=($view=='group' ? 'SELECTED':'')?>>Groupe</option>
				</select>
			</td>
		</tr>
	</form>
</table>
<br>
<?
}
//Line up pour raid 25
?>
<table style=width:250px;border:0px;height:15px; cellspacing=0 cellpadding=0>
	<tr>
		<td>
			<big>Line UP 25</big>
		</td>
	</tr>
</table>
<table cellspacing=0 class=small style=border:0px;>
	<tr>
	<?
	switch($view)
	{
	//Affichage par "groupe"
	case 'group' :
		//$c_key = array_keys($group25);	
		foreach($group25 as $g_key=> $g)
		{
			$img_classe=rem_acc($joueur25[$g['leader']]['classe']);
			$img_classe=strtolower($img_classe);	
			echo "
			<td style=width:120px;>Groupe de <b>".$g['real_leader_name']."</b>
				<table style=width:130px;margin-left:7px cellspacing=0>";
					if($joueur25[$g['leader']]['nom'])
					{
					//Si un leader existe, on le place en haut de la liste
					echo "
					<tr class=leader>
						<td style=background-color:black;width:38px>
							<img style=width:100% src=".$root."/images/classes/".$img_classe.".gif>
						</td>
						<td>
							<a href=info_player.php?player=".$joueur25[$g['leader']]['id_joueur'].">".$joueur25[$g['leader']]['nom']."</a>
						</td>
						<td>
							<a href=details.php?id_inst=".$id_inst."&delraid=".$joueur25[$g['leader']]['id_joueur']."><img alt='Supprimer ce joueur du raid' src=images/del.gif height=15 width=15></a>
						</td>
					</tr>";
					}
					//Liste des joueurs du groupe
					if($g['players'][0])
					{
						foreach($g['players'] as $p)
						{
							$img_classe=rem_acc($joueur25[$p]['classe']);
							$img_classe=strtolower($img_classe);	
							echo "
								<tr>
									<td style=background-color:black;>
										<img src=".$root."/images/classes/".$img_classe.".gif>
									</td>
									<td class=player>
										<a href=info_player.php?player=".$joueur25[$p]['id_joueur'].">".$joueur25[$p]['nom']."</a>
									</td>
									<td>
										<a href=details.php?id_inst=".$id_inst."&delraid=".$joueur25[$p]['id_joueur']."><img alt='Supprimer ce joueur du raid' src=images/del.gif height=15 width=15></a>
									</td>
								</tr>";
						}
					}
			
					if($rank && ($rank>=3 || $id==$g['real_leader_id']))
					{
						echo "
							<tr>
								<th colspan=3 style=font-size:10px;text-align:center><a href=details.php?view=mail&id_inst=".$inst['id_inst']."&grp=".$g['real_leader_id']."><img style=width:10px src=images/mail.gif> Alerte Mail</a>
								</th>
							</tr>";
					}
					?>
				</table>
			</td>
		<?
		}
	break;
		
	case 'mail':
		$grp = intval($_REQUEST['grp']);
		if($rank && ($rank>=3 || $id==$grp) && $inst['date'] > date("YmdHis",time()-3600*24))
		{
			if($grp)
			{
				$d_ = mysql_query("
					SELECT M.mail_date as sent, J.nom, J.id_joueur, J.mail, L.id_leader as playing
					FROM (".prefix('joueurs')." J) 
					LEFT JOIN ".prefix('liens')." L ON L.id_leader = '".$grp."' AND J.id_joueur=L.id_joueur AND L.id_inst='".$id_inst."'
					LEFT JOIN ".prefix('mails')." M ON J.id_joueur=M.id_joueur AND M.id_lead='".$grp."' AND M.id_inst='".$id_inst."'
					WHERE inactive='0' AND mail!=''
					ORDER BY J.nom
				")  or die("Erreur mysql - details.php:675 : ".mysql_error());
			}
			else
			{
				$d_ = mysql_query("
					SELECT M.mail_date as sent, J.nom, J.id_joueur, J.mail, L.id_leader as playing
					FROM (".prefix('joueurs')." J) 
					LEFT JOIN ".prefix('liens')." L ON J.id_joueur=L.id_joueur AND L.id_inst='".$id_inst."'
					LEFT JOIN ".prefix('mails')." M ON J.id_joueur=M.id_joueur AND M.id_inst='".$id_inst."'
					WHERE inactive='0' AND mail!=''
					ORDER BY J.nom
				")  or die("Erreur mysql - details.php:686 : ".mysql_error());
			}
			
		 	while($d = mysql_fetch_assoc($d_))
			{
				$gg = array();
				if($d['playing'])
				{
					if($group25[$d['playing']]['leader'])
					{
						$gg[] = '<b>'.$joueur25[$group25[$d['playing']]['leader']]['nom'].'</b>';
					}
					foreach($group25[$d['playing']]['players'] as $g)
					{
						$gg[] = $joueur25[$g]['nom'];
					}
				}
				
				$str = array(
					'%leader',
					'%instance',
					'%date',
					'%group',
					);
					
				$replace = array(
					$group25[$d['playing']]['real_leader_name'],
					$inst['inst_nom'],
					aff_date($inst['date']),
					implode(', ',$gg),
					);
				
				if($d['sent'] || $d['playing'])
				{
					if($d['sent'] && !$d['playing'])
					{
						//Envoie mail d'annulation
						$d['status']= 'Mail d\'annulation envoyé';
						$d['img']	= 'invalid.gif';
						
						$message = str_replace($str,$replace,$lang['mail_tpl_invalid_core']);
						$message = preg_replace("#(?<!\r)\n#si", "\r\n",$message);
						$title = str_replace($str,$replace,$lang['mail_tpl_invalid_title']);
						mail($d['mail'], $title, $message, $lang['mail_headers']);
						
						mysql_query("DELETE FROM ".prefix('mails')." WHERE id_joueur='".$d['id_joueur']."' AND id_inst='".$id_inst."'") or die("Erreur mysql - details.php:735 : ".mysql_error());
					}
					elseif(!$d['sent'] && $d['playing'])
					{
						//Envoie mail de selection
						$d['status']= 'Mail de sélection en court d\'envoi';
						$d['img']	= 'unread.gif';
						
						$message = str_replace($str,$replace,$lang['mail_tpl_valid_core']);
						$message = preg_replace("#(?<!\r)\n#si", "\r\n",$message);
						$title = str_replace($str,$replace,$lang['mail_tpl_valid_title']);
						mail($d['mail'], $title, $message, $lang['mail_headers']);
						
						mysql_query("
							INSERT INTO ".prefix('mails')." (`id_joueur`,`id_inst`,`id_lead`,`mail_date`) 
							VALUES ('".$d['id_joueur']."','".$id_inst."','".$d['playing']."','".date("YmdHis",time())."')
							") or die("Erreur mysql - details.php:748 : ".mysql_error());
					}
					else
					{
						//n'envoie rien
						$d['status']= 'Mail déja envoyé';
						$d['img']	= 'valid.gif';
					}
			 		$mail[] = $d;
		 		}
		 	}
			?>
			<big>Envoi de Mails</big>
			<table style=width:400px>
			<?
		 	foreach($mail as $m)
			{
		 		echo "
		 		<tr>
		 			<td>".$m['nom']."</td>
		 			<td><img src=images/".$m['img']."></td>
		 			<td>".$m['status']."</td>
		 		</tr>";
		 	}
			?>
			</table>
			<?
		}
		elseif($rank && ($rank>=3 || $id==$grp))
		{
		 	$msg['erreur'] = "Instance trop ancienne pour envoyer les mails.";
		}
	break;
	//Par défaut affichage par classe
	default:		
		foreach($classe as $c)
		{
			if($classes25[$c])
			{
				$img_classe=rem_acc($c);
				$img_classe=strtolower($img_classe);
				//Image de la classe et nombre de membre selectionné dans cette classe
				echo "
						<td style=width:120px;>
							<table style=width:130px; cellspacing=0>
								<tr class=leader>
									<td style=background-color:black;width:38px>
										<img style=width:100% src=".$root."/images/classes/".$img_classe.".gif>
									</td>
									<td colspan=2>
										".$c."s<br>(".$count_classe25[$c].")
									</td>
								</tr>";
				//Liste des joueurs 				
				foreach($classes25[$c] as $p)
				{
					echo "
								<tr class=player>
									<td>
										".($rank ? "<input type=checkbox>" : '')." 
									</td>
									<td>
										<a href=info_player.php?player=".$joueur25[$p]['id_joueur'].">".$joueur25[$p]['nom']."</a>
									</td>
									<td>
										<a href=details.php?id_inst=".$id_inst."&delraid=".$joueur25[$p]['id_joueur']."><img alt='Supprimer ce joueur du raid' src=images/del.gif height=15 width=15></a>
									</td>
								</tr>";
				}
				
				echo "
							</table>
						</td>";
			}
		}
	break;
}

echo "
	<td width=100%>&nbsp;</td>
	</tr>
</table>";

//Line up pour raid 10
?>
<table style=width:250px;border:0px;height:15px; cellspacing=0 cellpadding=0>
	<tr>
		<td>
			<big>Line UP 10</big>
		</td>
	</tr>
</table>
<table cellspacing=0 class=small style=border:0px;>
	<tr>
	<?
	switch($view)
	{
	//Affichage par "groupe"
	case 'group' :
		//$c_key = array_keys($group10);	
		foreach($group10 as $g_key=> $g)
		{
			$img_classe=rem_acc($joueur10[$g['leader']]['classe']);
			$img_classe=strtolower($img_classe);	
			echo "
			<td style=width:120px;>Groupe de <b>".$g['real_leader_name']."</b>
				<table style=width:130px;margin-left:7px cellspacing=0>";
					if($joueur10[$g['leader']]['nom'])
					{
					//Si un leader existe, on le place en haut de la liste
					echo "
					<tr class=leader>
						<td style=background-color:black;width:38px>
							<img style=width:100% src=".$root."/images/classes/".$img_classe.".gif>
						</td>
						<td>
							<a href=info_player.php?player=".$joueur10[$g['leader']]['id_joueur'].">".$joueur10[$g['leader']]['nom']."</a>
						</td>
						<td>
							<a href=details.php?id_inst=".$id_inst."&delraid=".$joueur10[$g['leader']]['id_joueur']."><img alt='Supprimer ce joueur du raid' src=images/del.gif height=15 width=15></a>
						</td>
					</tr>";
					}
					//Liste des joueurs du groupe
					if($g['players'][0])
					{
						foreach($g['players'] as $p)
						{
							$img_classe=rem_acc($joueur10[$p]['classe']);
							$img_classe=strtolower($img_classe);	
							echo "
								<tr>
									<td style=background-color:black;>
										<img src=".$root."/images/classes/".$img_classe.".gif>
									</td>
									<td class=player>
										<a href=info_player.php?player=".$joueur10[$p]['id_joueur'].">".$joueur10[$p]['nom']."</a>
									</td>
									<td>
										<a href=details.php?id_inst=".$id_inst."&delraid=".$joueur10[$p]['id_joueur']."><img alt='Supprimer ce joueur du raid' src=images/del.gif height=15 width=15></a>
									</td>
								</tr>";
						}
					}
			
					if($rank && ($rank>=3 || $id==$g['real_leader_id']))
					{
						echo "
							<tr>
								<th colspan=3 style=font-size:10px;text-align:center><a href=details.php?view=mail&id_inst=".$inst['id_inst']."&grp=".$g['real_leader_id']."><img style=width:10px src=images/mail.gif> Alerte Mail</a>
								</th>
							</tr>";
					}
					?>
				</table>
			</td>
		<?
		}
	break;
		
	case 'mail':
		$grp = intval($_REQUEST['grp']);
		if($rank && ($rank>=3 || $id==$grp) && $inst['date'] > date("YmdHis",time()-3600*24))
		{
			if($grp)
			{
				$d_ = mysql_query("
					SELECT M.mail_date as sent, J.nom, J.id_joueur, J.mail, L.id_leader as playing
					FROM (".prefix('joueurs')." J) 
					LEFT JOIN ".prefix('liens')." L ON L.id_leader = '".$grp."' AND J.id_joueur=L.id_joueur AND L.id_inst='".$id_inst."'
					LEFT JOIN ".prefix('mails')." M ON J.id_joueur=M.id_joueur AND M.id_lead='".$grp."' AND M.id_inst='".$id_inst."'
					WHERE inactive='0' AND mail!=''
					ORDER BY J.nom
				")  or die("Erreur mysql - details.php:675 : ".mysql_error());
			}
			else
			{
				$d_ = mysql_query("
					SELECT M.mail_date as sent, J.nom, J.id_joueur, J.mail, L.id_leader as playing
					FROM (".prefix('joueurs')." J) 
					LEFT JOIN ".prefix('liens')." L ON J.id_joueur=L.id_joueur AND L.id_inst='".$id_inst."'
					LEFT JOIN ".prefix('mails')." M ON J.id_joueur=M.id_joueur AND M.id_inst='".$id_inst."'
					WHERE inactive='0' AND mail!=''
					ORDER BY J.nom
				")  or die("Erreur mysql - details.php:686 : ".mysql_error());
			}
			
		 	while($d = mysql_fetch_assoc($d_))
			{
				$gg = array();
				if($d['playing'])
				{
					if($group10[$d['playing']]['leader'])
					{
						$gg[] = '<b>'.$joueur10[$group10[$d['playing']]['leader']]['nom'].'</b>';
					}
					foreach($group10[$d['playing']]['players'] as $g)
					{
						$gg[] = $joueur10[$g]['nom'];
					}
				}
				
				$str = array(
					'%leader',
					'%instance',
					'%date',
					'%group',
					);
					
				$replace = array(
					$group10[$d['playing']]['real_leader_name'],
					$inst['inst_nom'],
					aff_date($inst['date']),
					implode(', ',$gg),
					);
				
				if($d['sent'] || $d['playing'])
				{
					if($d['sent'] && !$d['playing'])
					{
						//Envoie mail d'annulation
						$d['status']= 'Mail d\'annulation envoyé';
						$d['img']	= 'invalid.gif';
						
						$message = str_replace($str,$replace,$lang['mail_tpl_invalid_core']);
						$message = preg_replace("#(?<!\r)\n#si", "\r\n",$message);
						$title = str_replace($str,$replace,$lang['mail_tpl_invalid_title']);
						mail($d['mail'], $title, $message, $lang['mail_headers']);
						
						mysql_query("DELETE FROM ".prefix('mails')." WHERE id_joueur='".$d['id_joueur']."' AND id_inst='".$id_inst."'") or die("Erreur mysql - details.php:735 : ".mysql_error());
					}
					elseif(!$d['sent'] && $d['playing'])
					{
						//Envoie mail de selection
						$d['status']= 'Mail de sélection en court d\'envoi';
						$d['img']	= 'unread.gif';
						
						$message = str_replace($str,$replace,$lang['mail_tpl_valid_core']);
						$message = preg_replace("#(?<!\r)\n#si", "\r\n",$message);
						$title = str_replace($str,$replace,$lang['mail_tpl_valid_title']);
						mail($d['mail'], $title, $message, $lang['mail_headers']);
						
						mysql_query("
							INSERT INTO ".prefix('mails')." (`id_joueur`,`id_inst`,`id_lead`,`mail_date`) 
							VALUES ('".$d['id_joueur']."','".$id_inst."','".$d['playing']."','".date("YmdHis",time())."')
							") or die("Erreur mysql - details.php:748 : ".mysql_error());
					}
					else
					{
						//n'envoie rien
						$d['status']= 'Mail déja envoyé';
						$d['img']	= 'valid.gif';
					}
			 		$mail[] = $d;
		 		}
		 	}
			?>
			<big>Envoi de Mails</big>
			<table style=width:400px>
			<?
		 	foreach($mail as $m)
			{
		 		echo "
		 		<tr>
		 			<td>".$m['nom']."</td>
		 			<td><img src=images/".$m['img']."></td>
		 			<td>".$m['status']."</td>
		 		</tr>";
		 	}
			?>
			</table>
			<?
		}
		elseif($rank && ($rank>=3 || $id==$grp))
		{
		 	$msg['erreur'] = "Instance trop ancienne pour envoyer les mails.";
		}
	break;
	//Par défaut affichage par classe
	default:		
		foreach($classe as $c)
		{
			if($classes10[$c])
			{
				$img_classe=rem_acc($c);
				$img_classe=strtolower($img_classe);
				//Image de la classe et nombre de membre selectionné dans cette classe
				echo "
						<td style=width:120px;>
							<table style=width:130px; cellspacing=0>
								<tr class=leader>
									<td style=background-color:black;width:38px>
										<img style=width:100% src=".$root."/images/classes/".$img_classe.".gif>
									</td>
									<td colspan=2>
										".$c."s<br>(".$count_classe10[$c].")
									</td>
								</tr>";
				//Liste des joueurs 				
				foreach($classes10[$c] as $p)
				{
					echo "
								<tr class=player>
									<td>
										".($rank ? "<input type=checkbox>" : '')." 
									</td>
									<td>
										<a href=info_player.php?player=".$joueur10[$p]['id_joueur'].">".$joueur10[$p]['nom']."</a>
									</td>
									<td>
										<a href=details.php?id_inst=".$id_inst."&delraid=".$joueur10[$p]['id_joueur']."><img alt='Supprimer ce joueur du raid' src=images/del.gif height=15 width=15></a>
									</td>
								</tr>";
				}
				
				echo "
							</table>
						</td>";
			}
		}
	break;
}

echo "
	<td width=100%>&nbsp;</td>
	</tr>
</table>";

include 'footer.php';
?>	