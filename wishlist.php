<?
$GLOBALS['allowed'] = 1;
include "commun.inc.php";

$adding_limit = $conf['limit_obj_add'];

//UP&DOWN
if( (isset($_GET['up']) || isset($_GET['down'])) && !$conf['lock_wishlist']){
	
	$id_objet 	= intval($_GET['id_objet']);
	$mod 		= isset($_GET['up']) ? -1:1;
	
	$poids = mysql_query("SELECT poids,id_objet FROM ".prefix('wishlist')." WHERE id_objet='$id_objet' AND id_joueur='$id'");
	$poids = mysql_fetch_assoc($poids);

	if($poids['id_objet']){
		$poids = $poids['poids'];
		
		mysql_query("
						UPDATE ".prefix('wishlist')." 
						SET poids='$poids' 		
						WHERE id_joueur='$id' AND poids='".($poids+$mod)."'
					") or die(mysql_error());
	
		if(mysql_affected_rows())
			mysql_query("
							UPDATE ".prefix('wishlist')." 
							SET poids='".max(0,$poids+$mod)."' 		
							WHERE id_joueur='$id' AND id_objet='$id_objet'
						") or die(mysql_error());
	}

	
}

if($_POST['obj_nom']){
	
	$count = 0;
	if(!$rank){
		
		$count = mysql_query("SELECT count(*) as count FROM ".prefix('objets')." WHERE added_by='$id'");
		$count = mysql_fetch_assoc($count);
		$count = $count['count'];
		
	}
	
	if($rank || $count <= $adding_limit){
		$nom = ucwords(strip_tags(addslashes($_POST['obj_nom'])));
		$tho = strip_tags(addslashes($_POST['thottbot']));
		$bos = intval($_POST['id_boss']);
		
		mysql_query("INSERT INTO ".prefix('objets')." (`objet_nom`,`thottbot`,`added_by`,`dropped_by`) VALUES ('$nom','$tho','$id','$bos')");
		
		$load = $root."/drops.php?action=obj&id_objet=".mysql_insert_id();
	}
	else
		$msg['erreur'] = "Vous ne pouvez dépasser la limite de $adding_limit objets ajoutés.";
}

if($_GET['del']){
	
	$del = intval($_GET['del']);
	mysql_query("DELETE FROM ".prefix('wishlist')." WHERE id_objet='$del' AND id_joueur='$id'");
	
}


//WISHLIST
$d_ = mysql_query("
					SELECT *
					FROM (".prefix('wishlist')." W)
					WHERE id_joueur = '$id'
					ORDER BY poids
				") 
				or die (mysql_error());

$wish = array();
while($d = mysql_fetch_assoc($d_)){
	
	$wish[] = $d;
	
}


//AJOUT D'OBJET
if($_REQUEST['add_objet'] && !$conf['lock_wishlist']){
	
	$id_objet 	= intval($_REQUEST['add_objet']);
	$poids	  	= count($wish);
	$date		= date("YmdHis",time());
	if($poids < $conf['limit_wishlist']){
		mysql_query("
						INSERT INTO ".prefix('wishlist')." 
						(`id_joueur`,`id_objet`,`poids`,`wish_date`) 
						VALUES 
						('$id','$id_objet','$poids','$date')
					");
					
		
		if(mysql_errno() == '1062')
			$msg['erreur'] = "Cet Objet fait déja partie de votre liste.";
		else{
			
			$wish[] = array(
								'poids'		=> $poids,
								'id_objet'	=> $id_objet,
								'wish_date'	=> $date,
							);
			
		}
	}
	else{
		$msg['erreur'] = "Vous ne pouvez avoir plus de <b>".$conf['limit_wishlist']."</b> objets dans votre liste de souhaits.";
	}
}


//OBJET
$d_ = mysql_query("
					SELECT objet_nom,id_objet,img
					FROM ".prefix('objets')."
					ORDER BY objet_nom
				") 
				or die (mysql_error());

$objet = array();
$objet_list = array();
while($d = mysql_fetch_assoc($d_)){
	
	$objet[$d['id_objet']] = $d;
	
	$objet_list[] = $d;
}

//BOSSES
$boss = array();
$d_ = mysql_query("SELECT * FROM ".prefix('boss')." ORDER BY boss_nom");
while($d = mysql_fetch_assoc($d_))
	$boss[] = $d;

if($load){
	include 'header.php';
		echo '<big>ACTION EFFECTUEE</big><br><meta http-equiv="Refresh" content="2; url='.$load.'" /><a href='.$load.'>Si la page ne se recharge pas cliquer sur ce lien.</a>';
	include 'footer.php';
	exit;
}

include "header.php";
?>
<big>Wish List (<?=(count($wish)."/".$conf['limit_wishlist'])?>)<?=($rank || $conf['lock_wishlist'] ? " / <a href=wishlist_list.php>Listes du Raid</a>":'')?></big><br><br>
<table style=width:500px class=wishlist cellspacing=0>
	<form action=wishlist.php method=POST>
	<tr>
		<th colspan=2>&nbsp;
		</th>
		<th width=80% colspan=2>Objet
		</th>
		<?if(!$conf['lock_wishlist']){?>
		<th colspan=2>&nbsp;
		</th>
		<?}?>
	</tr>
	<?
		$k=1;
		foreach($wish as $w){
			$o = $objet[$w['id_objet']];
			echo "
				<tr ".($k%2 ? '':'class=odd')." style=height:40px>
					<td style=font-size:10px>".($w['poids']+1)."
					</td>
					<td>
						".($o['img'] ? "<img src=".$o['img']." width=30px> ":'&nbsp;')."
					</td>
					<td>
						<a href=drops.php?action=obj&id_objet=".$o['id_objet'].">".$o['objet_nom']."</a>
					</td>
					<td>
						depuis le ".aff_date($w['wish_date'],5)."
					</td>
					".(!$conf['lock_wishlist'] ? "
					<td align=center>
						<a href=wishlist.php?id_objet=".$o['id_objet']."&up><img src=images/up.gif></a><br><a href=wishlist.php?id_objet=".$o['id_objet']."&down><img src=images/down.gif></a>
					</td>
					<td>
						<a href=wishlist.php?del=".$o['id_objet']."><img src=images/del.gif></a>
					</td>
					":'')."
				<tr>
			";
			$k++;
		}
	?>
	<?if(!$conf['lock_wishlist']){?>
	<tr>
		<td colspan=4 align=right>
			<select name=add_objet>
				<option value=0>Choisir l'objet souhaité</option>
			<?
				foreach($objet_list as $o){
					echo "<option value=".$o['id_objet'].">".$o['objet_nom']."</option>";
				}
			?>
			</select>
		</td>
		<td align=center colspan=2><input type=submit value=Ajouter style=width:90%></td>
	</tr>
	<?}?>
	</form>
</table>

<!--<i style=font-size:11px>Si l'objet que vous souhaitez n'est pas dans la liste, contactez un lead pour qu'il l'ajoute.</i>-->
<br>
<br>
<table style=width:500px>
	<form action=wishlist.php method=POST>
	<tr align=center>
		<th>Nom<br><i style=font-size:10px>En français pour que cela reste clair</i>
		</th>
		<td><input type=text name=obj_nom>
		</td>
	</tr>
	<tr align=center>
		<th>Lien Thottbot<br><i style=font-size:10px>Ex : http://www.thottbot.com/?i=28233</i>
		</th>
		<td><input type=text name=thottbot>
		</td>
	</tr>
	<tr align=center>
		<th width=50%>Tombe sur
		</th>
		<td width=50%>
			<select name=id_boss style=width:90%>
				<option value='0'>Inconnu</option>
				<?
					foreach($boss as $b){
						echo "<option value='".$b['id_boss']."'>".$b['boss_nom']."</option>";
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan=2 align=center>
			<input type=submit value=Ajouter>
		</td>
	</tr>
	</form>
</table>
<i style=font-size:11px>A moins d'être leader, vous ne pouvez ajouter plus de <b><?=$adding_limit?></b> objets.</i>
<?
include "footer.php";
?>