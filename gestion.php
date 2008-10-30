<?
include "commun.inc.php";

$id_inst = intval($_REQUEST['id_inst']);

if($_REQUEST['id'] && $rank>=3 && $id!=intval($_REQUEST['id'])){
	$id_ 	= intval($_REQUEST['id']);
	$d 		= mysql_query("SELECT rank,nom FROM ".prefix('joueurs')." WHERE id_joueur='".$id_."'");
	$d 		= mysql_fetch_assoc($d);
	$id 	= $d['rank']>=1 ? $id_ : $id;
	$replace_leader = $d['rank']>=1 ? $d['nom']:null;
}

if($_POST['select']){
	$lead	  	= intval($_POST['lead']) ? intval($_POST['lead']) : $id;
	$players  	= $_POST['player'] ? array_keys($_POST['player']) : array();
	

	if($players[0])
		$players[] 	= $lead;

	$players = array_unique($players);
	
	mysql_query("DELETE FROM ".prefix('liens')." WHERE id_inst='$id_inst' AND id_leader='$id'") or die(mysql_error());
	
	if($players[0]){
		foreach($players as $p){
			@mysql_query("INSERT INTO ".prefix('liens')." (id_inst,id_leader,id_joueur) VALUES ('$id_inst','$id','$p')");// or die(mysql_error());
		}
		//mysql_query("UPDATE ".prefix('liens')." SET is_lead='1' WHERE id_joueur='$lead' AND id_inst='$id_inst'");
	}
}

$inst_ 	= mysql_query("
						SELECT * FROM (".prefix('instances')." I, ".prefix('joueurs')." J)
						WHERE I.inst_orga=J.id_joueur AND I.id_inst='$id_inst' 
						ORDER BY date
					") or die (mysql_error());

$inst  	= mysql_fetch_assoc($inst_);

$sort_ar= array('nom','classe');
$sort_nb= intval($_REQUEST['sort']);
$sort	= $sort_ar[$sort_nb];

$d_ = mysql_query("
					SELECT L.id_inst, D.dispo, D.dispo_date, L.is_lead current_lead,J.rank is_leader,L.id_leader, J.*, B.id_joueur blacklisted
					FROM (".prefix('joueurs')." J)
					LEFT JOIN ".prefix('liens')." L 
					ON L.id_joueur=J.id_joueur AND L.id_inst='$id_inst'
					LEFT JOIN ".prefix('dispo')." D
					ON D.id_joueur=J.id_joueur AND D.id_inst='$id_inst'
					LEFT JOIN ".prefix('blacklist')." B
					ON B.id_joueur=J.id_joueur
					WHERE J.inactive='0'
					ORDER BY $sort
					") or die(mysql_error());
$mygroup = array();
$taken	 = array();
$joueur  = array();

while($d = mysql_fetch_assoc($d_)){

	$joueur[] = $d;
	if($d['id_leader'] == $id)
		$mygroup[$d['id_joueur']] = $d;
		
	$taken[$d['id_joueur']] = $d['id_inst'] || ($d['is_leader'] && !$conf['group_multi_lead']) ? 1:0;
}

include 'header.php';
echo "<big>Gestion du groupe pour l'instance <a href=details.php?id_inst=$id_inst>".strtoupper($inst['inst_nom'])."</a> organisée le ".aff_date($inst['date'])."</big>";
echo ($replace_leader ? "<br><big>Groupe de <font style=color:red>$replace_leader</font></big><br><br>":'<br><br>');
echo "
	<table style=width:400px;margin-left:50px cellspacing=0>
		<tr>
			<th colspan=2>
				Groupe actuel (".count($mygroup)." membres)
			</th>
		</tr>
	";
foreach($mygroup as $m){
	
	echo "
		<tr>
			<td>
				".$m['nom']."			
			</td>
			<td>
				".$m['classe']."
			</td>
		</tr>
		";
}
	
?>
		</table>
		<br><br>
		<table style=width:400px;margin-left:50px cellspacing=0>
			<form action=gestion.php method=POST>
			<?=($rank>=3 && isset($_REQUEST['id']) ? "<input type=hidden name=id value=".$id.">":'')?>
			<input type=hidden name=id_inst value='<?=$id_inst?>'>
			<tr>
				<th>&nbsp;
				</th>
				<th>Nom <a href=gestion.php?id_inst=<?=$id_inst?>&sort=0><img border=0 src=<?=$root?>/images/arrow.gif></a>
				</th>
				<th colspan=2>Classe <a href=gestion.php?id_inst=<?=$id_inst?>&sort=1><img border=0 Src=<?=$root?>/images/arrow.gif></a>
				</th>
				<th>
					Dispo
				</th>
			</tr>
<?
$i=0;
foreach($joueur as $j){
	
	echo "
		<tr class=player ".($mygroup[$j['id_joueur']]['nom'] ? 'style=background-color:#cee8ce':'').">
			<td width=25px;>
				<input type=checkbox name=player[".$j['id_joueur']."] value=1 ".( ($taken[$j['id_joueur']] && !$mygroup[$j['id_joueur']] && $j['id_joueur']!=$id) || $j['blacklisted'] || $j['dispo']==3 ? 'DISABLED' : '')." ".($mygroup[$j['id_joueur']] ? 'CHECKED' : '').">
			</td>
			<td>
				".$j['nom']." ".($j['blacklisted'] ? '(Banni)':'')."
			</td>
			<td style='width:25px;background-color:black;border-bottom:1px solid white'>
				<img  style=width:100%; name=".$j['classe']." src=".$root."/images/classes/".rem_acc(strtolower($j['classe'])).".gif>
			</td>
			<td>
				&nbsp;".$j['classe']."
			</td>
			<td class=dispo_".intval($j['dispo']).">&nbsp;".$dispo[intval($j['dispo'])]."
			</td>
		</tr>
		";
$i++;
}
echo '	
		<tr>
			<td colspan=4 align=center>	
				<input type=submit name=select value=Sélectionner>
			</td>
		</tr>
		</form>
		</table>
	';

include 'footer.php';
?>