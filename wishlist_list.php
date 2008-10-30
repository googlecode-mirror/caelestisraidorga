<?
$GLOBALS['allowed'] = 1;
include "commun.inc.php";

if(!$conf['lock_wishlist'] && $rank<0)
	exit;

$d_ = mysql_query("
			SELECT J.nom, J.id_joueur, J.classe, W.*, D.id_drop as got
			FROM (".prefix('joueurs')." J, ".prefix('wishlist')." W)
			LEFT JOIN ".prefix('drops')." D ON D.id_objet=W.id_objet AND D.id_joueur=J.id_joueur
			WHERE J.inactive='0' AND W.id_joueur=J.id_joueur
			ORDER BY J.classe, J.nom, W.poids
			")
			or die(mysql_error());
//			LEFT JOIN ".prefix('objets')." O ON O.id_objet=W.id_objet
$wish = array();
while($d = mysql_fetch_assoc($d_)){
	
	
	$wish[$d['classe']][$d['id_joueur']]['data'] = $d;
	$wish[$d['classe']][$d['id_joueur']]['objets'][$d['id_objet']] = $d;
	
}

//OBJET
$d_ = mysql_query("
					SELECT objet_nom,id_objet,img
					FROM ".prefix('objets')."
					ORDER BY objet_nom
				") 
				or die (mysql_error());

$objet = array();

while($d = mysql_fetch_assoc($d_)){
	
	$objet[$d['id_objet']] = $d;
	
}

include "header.php";
?>
<big><a href=wishlist.php>Wish List</a> / Listes du Raid</big><br><br>
<table style=width:90%;border:0px class=wishlist cellspacing=0>
	<tr>
	<?
		foreach($wish as $classe=>$persos){
			echo "
					<td style=vertical-align:top;width:12%><big>$classe</big>
			";
			
			foreach($persos as $p=>$wish){
				echo "
						<table style=font-size:10px cellspacing=0>
							<tr>
								<th colspan=2><a href=info_player.php?player=".$wish['data']['id_joueur'].">".$wish['data']['nom']."</a>
								</th>
							</tr>
					";
					$i=1;
					foreach($wish['objets'] as $w){
						if($i > $conf['limit_wishlist'])
							break;
						echo "<tr ".($w['got'] ? 'style=background-color:#ff8080':'')."><td><a href=drops.php?action=obj&id_objet=".$w['id_objet'].">".($objet[$w['id_objet']]['img'] ? "<img width=20px src=".$objet[$w['id_objet']]['img'].">":"???")."</a></td><td>".$objet[$w['id_objet']]['objet_nom']."</td></tr>							</tr>";
						$i++;
					}
				echo "
						</table>
						<br><br>
					";
						
			}
			
			echo "
					</td>
			";
		}
	?>
	</tr>
</table>
<?
include "footer.php";
?>