<?
//////////////////////////////////////////////
// fiche_objet.php
// Affichage des caractéristiques d'un objet
// NE PAS MODIFIER
// Version du 27/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 21/10/2008: Nettoyage du code, première version 
// 27/10/2008 : Joyrock - Ajout des logs et correction de la modification

// Selection des infos concernant l'objet
$objet = mysql_query(
	"SELECT O.*, B.boss_nom FROM (".prefix('objets')." O) 
	LEFT JOIN ".prefix('boss')." B ON O.dropped_by = B.real_id_boss
	WHERE real_id='".$id_objet."'
	") or die("Erreur mysql - fiche_objet.php:13 : ".mysql_error());
$objet = mysql_fetch_assoc($objet);

//Sélections des infos cincernant les drops de cet objet
$d_ = mysql_query(
	"SELECT D.*, J.nom, I.inst_nom, I.id_inst, I.date
	FROM (".prefix('drops')." D)
	LEFT JOIN ".prefix('instances')." I ON I.id_inst=D.id_inst
	LEFT JOIN ".prefix('joueurs')." J ON J.id_joueur = D.id_joueur
	WHERE D.id_objet='".$id_objet."'
	ORDER BY -date
	") or die("Erreur mysql - fiche_objet.php:20 : ".mysql_error());
$butin = array();
	
while($d = mysql_fetch_assoc($d_))
{
	$butin[] = $d;
}
	
if(isset($rank))
{
	$info_player='info_player.php?';
	$details='details.php?';
}
else
{
	$info_player='view.php?db='.$db.'&';
	$details='view.php?db='.$db.'&';	
}

$wishlist=array();
if( ($rank && $conf['limit_wishlist']>0) || $conf['lock_wishlist'])
{
	$d_ = mysql_query(
		"SELECT poids, nom, J.id_joueur, wish_date, D.id_drop as got_it 
		FROM (".prefix('wishlist')." W, ".prefix('joueurs')." J)
		LEFT JOIN ".prefix('drops')." D ON J.id_joueur=D.id_joueur AND W.id_objet=D.id_objet
		WHERE W.id_objet='$id_objet' AND J.id_joueur=W.id_joueur 
		ORDER BY poids,nom
		") or die("Erreur mysql - fiche_objet.php:50 : ".mysql_error());

	while($d = mysql_fetch_assoc($d_))
	{
		$wishlist[] = $d;
	}
}
?>
<big><?=$objet['objet_nom']?></big>
<table style=width:500px>
	<?
	if($objet['obj_img'] && $objet['obj_carac'])
	{
	?>
		<tr>
			<td align=center colspan=2>
				<div id="wrapper">
					<div id="main">
						<div class="text">				
							<?=$objet['obj_carac']?>
							<script type="text/javascript">
							ge('ic<?=$objet['real_id']?>').appendChild(Icon.create('<?=$objet['obj_img']?>', 2, 0, 0, 1));
							Tooltip.fixSafe(ge('tt<?=$objet['real_id']?>'), 1, 1);
							</script>
						</div>
					</div>
				</div>
			</td>
		</tr>
	<?
	}
	?>
	<tr>
		<th>Tombe sur</th>
		<td><?echo $objet['boss_nom'];?></td>
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
	<?
	if($wishlist[0])
	{
	?>
		<tr>
			<td colspan=2 align=center>
				<a href=wishlist.php?add_objet=<?=$objet['real_id']?>>Ajouter à la Wishlist</a>
			</td>
		</tr>
	<?
	}
	
	if($rank>=3)
	{
	?>
		<tr>
			<form action=drops.php?action=objedit method=POST>
				<td colspan=2 align=center>
					<input type=submit value=Editer>
				</td>
				<input type=hidden name=id_objet value='<?=$objet['real_id']?>'>
			</form>
		</tr>
	<?
	}
	?>
</table>
		
<?
if($butin[0])
{
?>
<br>
<big>Drops</big>
<table style=width:500px; cellspacing=0>
	<tr class=forum>
		<th>Joueur</th>
		<th>Instance</th>
		<?
		if($rank)
		{
			echo "<th>&nbsp;</th>";
		}
		?>
	</tr>
	<?
	$i=0;
	foreach($butin as $b)
	{
		echo "
			<tr ".($i%2 ? 'class=odd':'').">
				<td><a href=".$info_player."player=".$b['id_joueur'].">".$b['nom']."</a></td>
				<td><a href=".$details."id_inst=".$b['id_inst'].">".$b['inst_nom']."</a> le ".aff_date($b['date'])."</td>
				<td align=center>";
				if($rank>=3)
				{
					echo "<a href=drops.php?edit=".$b['id_drop']."&action=add><img src=".$root."/images/edit.gif></a>";
				}
		echo "
				</td>
			</tr>";
		$i++;
	}
	?>
</table>
<?
}

if($wishlist[0])
{
?>
	<br>
	<big>Dans la wishlist de</big>
	<table style=width:500px; cellspacing=0>
	<?
	$i=0;
	foreach($wishlist as $w)
	{
		echo "
			<tr ".($i%2 ? 'class=odd':'')." ".($w['got_it'] ? 'style=background-color:#ff8080':'').">
				<td>".($w['poids']+1)."</td>
				<td><a href=".$info_player."player=".$w['id_joueur'].">".$w['nom']."</a></td>
				<td>depuis le ".aff_date($w['wish_date'],5)."</td>
			</tr>";
		$i++;
	}
	echo "</table>";
}
?>