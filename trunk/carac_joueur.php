<?
//////////////////////////////////////////////
// carac_joueur.php
// Script d'affichage de la fiche du perso
// NE PAS MODIFIER
// Version du 21/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:

//sécurité
$GLOBALS['allowed'] = 1;

//includes
include "commun.inc.php";

$player = intval($_GET['player']);

//Sélection des données du perso
$instances 	= array();
$leader		= array();
$d_ = mysql_query("
		SELECT J.*, L2.id_joueur leader_id, B.id_joueur blacked, L.*
		FROM (".prefix('joueurs')." J) 
		LEFT JOIN ".prefix('blacklist')." B ON B.id_joueur=J.id_joueur
		LEFT JOIN ".prefix('liens')." L 	ON L.id_joueur=J.id_joueur
		LEFT JOIN ".prefix('liens')." L2 	ON L2.id_inst=L.id_inst AND L2.is_lead='1' AND L2.id_leader=L.id_leader
		WHERE J.id_joueur = '".$player."'
		") or die("Erreur mysql - carac_joueur.php:25 : ".mysql_error());
			
while($d = mysql_fetch_assoc($d_))
{
	$instances[] 	= $d['id_inst'];
	$leader[]		= intval($d['leader_id']);
	$links[$d['id_inst']] = $d['leader_id'];
	$j = $d;	
}
	
//MAJ du XML Armory en base si les données date de plus de 24h
//if($j['xml_update']< date('YmdHis',time()-3600*24))
//{
//	$content=setxmlcache($j['id_joueur']);
//}
	
//Historique des instances faite par le joueur
$passif = array();
if($instances[0])
{
	$d_ = mysql_query("
		SELECT * FROM ".prefix('instances')." 
		WHERE id_inst IN (".implode(',',$instances).") 
		ORDER BY -date
		") or die("Erreur mysql - carac_joueur.php:50 : ".mysql_error());
			
	while($d = mysql_fetch_assoc($d_))
	{			
		$passif[] = $d;
		$j['dkp']+= $d['dkp'];
	}
}
	
//Récupération des informations sur les leaders de ces instances
$chef = array();
if($leader[0])
{
	$d_ = mysql_query("
		SELECT J.* FROM (".prefix('joueurs')." J) 
		WHERE id_joueur IN (".implode(',',$leader).")
		") or die("Erreur mysql - carac_joueur.php:67 : ".mysql_error());
			
	while($d = mysql_fetch_assoc($d_))
	{		
		$chef[$d['id_joueur']] = $d;
	}
}
	
//Récupération des loots du joueur
$butin = array();
$d_ = mysql_query("
		SELECT D.*, O.*, I.inst_nom, I.id_inst, I.date, W.wish_date as wished
		FROM (".prefix('drops')." D, ".prefix('objets')." O)
		LEFT JOIN ".prefix('instances')." I ON I.id_inst=D.id_inst
		LEFT JOIN ".prefix('wishlist')." W ON D.id_objet=W.id_objet AND W.id_joueur='".$player."'
		WHERE D.id_joueur='".$player."' AND O.real_id=D.id_objet
		ORDER BY -date
		") or die("Erreur mysql - carac_joueur.php:80 : ".mysql_error());
				
while($d = mysql_fetch_assoc($d_))
{
	$butin[] = $d;
}
	
//Informations de la wishlist
$d_ = mysql_query("
		SELECT O.*, D.id_drop as got
		FROM (".prefix('wishlist')." W, ".prefix('objets')." O)
		LEFT JOIN ".prefix('drops')." D ON D.id_objet=W.id_objet AND D.id_joueur='".$player."'
		WHERE W.id_joueur = '".$player."' AND O.real_id=W.id_objet
		ORDER BY poids
		") or die("Erreur mysql - carac_joueur.php:93 : ".mysql_error());
	
$wish = array();
while($d = mysql_fetch_assoc($d_))
{
	$wish[] = $d;	
}

//Les dispos du joueurs
$d_ = mysql_query("
		SELECT  I.*, I.id_inst id, D.dispo, L.id_leader as taken
		FROM (".prefix('instances')." I, ".prefix('joueurs')." J)
		LEFT JOIN ".prefix('dispo')." D ON D.id_inst=I.id_inst AND D.id_joueur = '".$player."'
		LEFT JOIN ".prefix('liens')." L ON L.id_joueur = J.id_joueur AND I.id_inst=L.id_inst
		WHERE J.id_joueur='".$player."' AND date > '".date("YmdHis",time()-3600*24)."'
		ORDER BY -date") or die("Erreur mysql - carac_joueur.php:108 : ".mysql_error());
	
$disp = array();
while($d = mysql_fetch_assoc($d_))
{		
	$disp[] = $d;
}

//En tête de la page	
include 'header.php';
?>
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
					<td width=35%>".aff_date($d['date'],4)."</td>
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
			echo "Pas de dispo";
		}
		?>
		</td>
		<td style=width:60% align=center>
			<?
			//On récupère le XML s'il n'a pas été mis à jour.
			if(!isset($content)) $content=getxmlcache($j['id_joueur']);
					
			//On ouvre le DOM
			if (($xml = domxml_open_mem($content)) == false)
			{
				echo "[-] Erreur : domxml_open_mem()<br>";
				exit;
			}
					
			// Récupération des infos du perso
			$character = $xml->get_elements_by_tagname('character');
			if (empty($character))
			{
				echo "[-] Erreur : infos perso non dispos.<br>";
				exit;
			}
			
			foreach ($character as $carac)
			{
				if($carac->get_attribute('classId')) $info_joueur['classeid']=$carac->get_attribute('classId');
				if($carac->get_attribute('name')) $info_joueur['nom']=$carac->get_attribute('name');
				if($carac->get_attribute('raceId')) $info_joueur['raceid']=$carac->get_attribute('raceId');
				if($carac->get_attribute('suffix')) $info_joueur['suffix']=$carac->get_attribute('suffix');
				if($carac->get_attribute('level')) $info_joueur['level']=$carac->get_attribute('level');
					
				switch($info_joueur['classeid'])
				{
					case '1':
					case '2':
					case '3':
					case '4':
					case '5':
					case '6':
					case '7':
					case '8':
					case '8':
					case '9':
					case '10':
					case '11':					
					$info_joueur['classe']="Druide";
					break;
					default:
					$info_joueur['classe']="Classe inconnue";
					break;
				}
					
					switch($info_joueur['raceid'])
				{
					case '1':
					case '2':
					case '3':
					case '4':
					$info_joueur['race']="Elfe de la nuit";
					break;
					case '5':
					case '6':
					case '7':
					case '8':
					case '9':
					case '10':
					default:
					$info_joueur['race']="Race inconnue";
					break;
				}
			}
					
			// Récupération de la vie
			$health = $xml->get_elements_by_tagname('health');
			if (empty($health))
			{
				echo "[-] Erreur : barre de vie non dispo.<br>";
				exit;
			}
			
			foreach ($health as $barrevie)
			{
				$vie=$barrevie->get_attribute('effective');
			}
					
			// Récupération de la mana
			$secondBar = $xml->get_elements_by_tagname('secondBar');
			if (empty($secondBar))
			{
				echo "[-] Erreur : seconde barre (mana/rage/energie) non dispo.<br>";
				exit;
			}
			foreach ($secondBar as $barremana)
			{
				$mana['type']=$barremana->get_attribute('type');
				$mana['pool']=$barremana->get_attribute('effective');
				$mana['regen_casting']=$barremana->get_attribute('casting');
				$mana['regen_notcasting']=$barremana->get_attribute('notCasting');
			}
							
			// Récupération des items
			$items = $xml->get_elements_by_tagname('item');
			if (empty($items))
			{
				echo "[-] Erreur : aucun item dispo.<br>";
				exit;
			}
					
			$objet_xml=array();
					
			foreach ($items as $item)
			{
				$tmpslot=$item->get_attribute('slot');
				$tmpid=$item->get_attribute('id');
				$tmpicon=$item->get_attribute('icon');
					
				$objet_xml[$tmpslot]['real_id']=$tmpid;
				$objet_xml[$tmpslot]['obj_img']=$tmpicon;
					
				/*$obj_ = mysql_query("
					SELECT  *
					FROM (".prefix('objets')." 
					WHERE real_id='".$elem->get_attribute('id')."'") or die (mysql_error());
				$obj = mysql_fetch_assoc($obj_);
								
				$item[$elem->get_attribute('slot')]['obj_img']=$obj['obj_img'];*/
			}
			?>
					
			<!--<table style=width:405px;>
			<tr>-->
			<!-- Affichage de la fiche perso-->
			<div id="overDiv" style="position: absolute; visibility: hidden; z-index: 1000; opacity: 0.8;"></div>
			<script>
				var tabs = new Array()
				var tab_count = 0
							
				function addTab( name ) {
					tabs[tab_count] = name;
					tab_count++;
				}
					
				function doTab( div ) {
					for( i=0 ; i<tab_count ; i++ ) {
						obj = document.getElementById( tabs[i] );
						fontobj = document.getElementById( "tabfont"+tabs[i] );
						if( tabs[i] == div ) {
							obj.style.display="block";
							fontobj.style.color="#ffffff";
						} else {
							obj.style.display="none";
							fontobj.style.color="#aa9900";
						}
					}
				}
					
				addTab('page1')
				addTab('page3')
				addTab('page4')
			</script>
					
			<div class="char" id="char">
				<div class="main">
					<div class="page1" id="page1">
						<div class="top" id="top">
							<span class="nomcarac"><?echo $j['nom']." (".$info_joueur['level'].")";?></span><br>
							<span class="classecarac"><? echo $info_joueur['classe']." - ".$info_joueur['race'];?></span>
						</div>
						<div class="left">
							<div class="equip"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[0]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[0]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[0]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 		<!-- Heaume-->
							<div class="equip"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[1]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[1]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[1]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 		<!-- Collier-->
							<div class="equip"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[2]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[2]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[2]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 		<!-- Epaulettes-->
							<div class="equip"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[14]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[14]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[14]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div>		<!-- Cape-->
							<div class="equip"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[4]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[4]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[4]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 		<!-- Plastron -->
							<div class="equip"><div class="item"><a href="<?//echo $item[5]['real_id'];?>" onmouseover="return overlib('<?//echo $item[5]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?//echo $item[5]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 								<!-- Chemise-->
							<div class="equip"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[18]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[18]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[18]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 		<!-- Tabard-->
							<div class="equip"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[8]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[8]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[8]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 		<!-- Bracelets-->
						</div> <!-- left -->
						<div class="middle">
							<div class="portrait">
								<div class="resistances">
									<ul class="stats">
										<li><a class="noform" href="#"><span class="green">8</span></a></li>
										<li><a class="noform" href="#"><span class="green">18</span></a></li>
										<li><a class="noform" href="#"><span class="green">8</span></a></li>
										<li><a class="noform" href="#"><span class="green">18</span></a></li>
										<li><a class="noform" href="#"><span class="green">13</span></a></li>
									</ul>
								</div>
								<br><br>
								<span class="grey">
									Coups Critiques: 11%<br>
									Coups Esquivés: 7.002%<br>
									Coups Parés: 5%<br>
									Coups Bloqués: 10%<br>
								</span><br>
								<span class="green">Points de Vie: <?echo $vie;?></span><br>
								<span class="yellow">
									<?
									switch($mana['type'])
									{
										case 'm':
										echo "Points de Mana: ".$mana['pool'];
										break;
										
										case 'e':
										echo "Points d'énergie: ".$mana['pool'];
										break;
					
										case 'r':
										echo "Points de Rage: ".$mana['pool'];
										break;
										
										default:
										echo "Points de Mana/Energie/Rage: ".$mana['pool'];
										break;
									}
									?>
								</span>
								<br><br><br>
								<span class="blue">Regen en combat: <?echo $mana['regen_casting']." mp5.";?></span><br>
								<span class="blue">Regen hors combat: <?echo $mana['regen_notcasting']." mp5.";?></span><br><br>
							</div>
							<div class="bottom">
								<div class="padding">
									<ul class="stats">
										<li>Force: <a class="noform" href="#" onmouseover="return overlib('<table border=\'0\' cellspacing=\'0\' cellpadding=\'0\' width=300><tr><td class=black background=\'images/profiler/tip/topleft.png\' width=\'6\' height=\'6\'></td><td class=black background=\'images/profiler/tip/top.png\' height=\'6\'></td><td class=black background=\'images/profiler/tip/topright.png\' width=\'6\' height=\'6\'></td></tr><tr><td background=\'images/profiler/tip/left.png\' width=\'6\'></td><td class=black background=\'images/profiler/tip/middle.png\' heigh=500><span class=tooltipheader style=color:#FFFFFF;z-index:0 >Force 133 (90<span class=greenb style=z-index:0 >+43</span>)</span><span class=tooltipline style=color:#DFB801;z-index:0>Augmente votre puissance d\'attaque</span><span class=tooltipline style=color:#DFB801;z-index:0>avec les armes de mélée</span></td><td class=black background=\'images/profiler/tip/right.png\' width=\'6\'></td></tr><tr><td class=black background=\'images/profiler/tip/bottomleft.png\' width=\'6\' height=\'6\'></td><td class=black background=\'images/profiler/tip/bottom.png\' height=\'6\'></td><td class=black background=\'images/profiler/tip/bottomright.png\' width=\'6\' height=\'6\'></td></tr></table>')" onmouseout="return nd()"><span class="green">133</span></a></li>
										<li>Agilité: <a class="noform" href="#" onmouseover="return overlib('<table border=\'0\' cellspacing=\'0\' cellpadding=\'0\' width=300><tr><td class=black background=\'images/profiler/tip/topleft.png\' width=\'6\' height=\'6\'></td><td class=black background=\'images/profiler/tip/top.png\' height=\'6\'></td><td class=black background=\'images/profiler/tip/topright.png\' width=\'6\' height=\'6\'></td></tr><tr><td background=\'images/profiler/tip/left.png\' width=\'6\'></td><td class=black background=\'images/profiler/tip/middle.png\' heigh=500><span class=tooltipheader style=color:#FFFFFF;z-index:0 >Agilité 65 (50<span class=greenb style=z-index:0 >+15</span>)</span><span class=tooltipline style=color:#DFB801;z-index:0>Augmente votre puissance d\'attaque</span><span class=tooltipline style=color:#DFB801;z-index:0>avec les armes de jet.</span><span class=tooltipline style=color:#DFB801;z-index:0>Améliore les chances de placer un</span><span class=tooltipline style=color:#DFB801;z-index:0>coup critique avec toutes les armes.</span><span class=tooltipline style=color:#DFB801;z-index:0>Améliore votre armure et vos chances</span><span class=tooltipline style=color:#DFB801;z-index:0>d\'éviter les attaques</span></td><td class=black background=\'images/profiler/tip/right.png\' width=\'6\'></td></tr><tr><td class=black background=\'images/profiler/tip/bottomleft.png\' width=\'6\' height=\'6\'></td><td class=black background=\'images/profiler/tip/bottom.png\' height=\'6\'></td><td class=black background=\'images/profiler/tip/bottomright.png\' width=\'6\' height=\'6\'></td></tr></table>')" onmouseout="return nd()"><span class="green">65</span></a></li>
										<li>Endurance: <a class="noform" href="#" onmouseover="return overlib('<table border=\'0\' cellspacing=\'0\' cellpadding=\'0\' width=300><tr><td class=black background=\'images/profiler/tip/topleft.png\' width=\'6\' height=\'6\'></td><td class=black background=\'images/profiler/tip/top.png\' height=\'6\'></td><td class=black background=\'images/profiler/tip/topright.png\' width=\'6\' height=\'6\'></td></tr><tr><td background=\'images/profiler/tip/left.png\' width=\'6\'></td><td class=black background=\'images/profiler/tip/middle.png\' heigh=500><span class=tooltipheader style=color:#FFFFFF;z-index:0 >Endurance 215 (97<span class=greenb style=z-index:0 >+118</span>)</span><span class=tooltipline style=color:#DFB801;z-index:0>Augmente vos points de vie</span></td><td class=black background=\'images/profiler/tip/right.png\' width=\'6\'></td></tr><tr><td class=black background=\'images/profiler/tip/bottomleft.png\' width=\'6\' height=\'6\'></td><td class=black background=\'images/profiler/tip/bottom.png\' height=\'6\'></td><td class=black background=\'images/profiler/tip/bottomright.png\' width=\'6\' height=\'6\'></td></tr></table>')" onmouseout="return nd()"><span class="green">215</span></a></li>
										<li>Intelligence: <a class="noform" href="#" onmouseover="return overlib('<table border=\'0\' cellspacing=\'0\' cellpadding=\'0\' width=300><tr><td class=black background=\'images/profiler/tip/topleft.png\' width=\'6\' height=\'6\'></td><td class=black background=\'images/profiler/tip/top.png\' height=\'6\'></td><td class=black background=\'images/profiler/tip/topright.png\' width=\'6\' height=\'6\'></td></tr><tr><td background=\'images/profiler/tip/left.png\' width=\'6\'></td><td class=black background=\'images/profiler/tip/middle.png\' heigh=500><span class=tooltipheader style=color:#FFFFFF;z-index:0 >Intelligence 220 (85<span class=greenb style=z-index:0 >+135</span>)</span><span class=tooltipline style=color:#DFB801;z-index:0>Augmente vos points de mana et vos</span><span class=tooltipline style=color:#DFB801;z-index:0>chances de placer un coup critique</span><span class=tooltipline style=color:#DFB801;z-index:0>avec les sorts.</span><span class=tooltipline style=color:#DFB801;z-index:0>Améliore la vitesse à laquelle vous</span><span class=tooltipline style=color:#DFB801;z-index:0>augmentez vos compétences</span><span class=tooltipline style=color:#DFB801;z-index:0>d\'armes</span></td><td class=black background=\'images/profiler/tip/right.png\' width=\'6\'></td></tr><tr><td class=black background=\'images/profiler/tip/bottomleft.png\' width=\'6\' height=\'6\'></td><td class=black background=\'images/profiler/tip/bottom.png\' height=\'6\'></td><td class=black background=\'images/profiler/tip/bottomright.png\' width=\'6\' height=\'6\'></td></tr></table>')" onmouseout="return nd()"><span class="green">220</span></a></li>
										<li>Esprit: <a class="noform" href="#" onmouseover="return overlib('<table border=\'0\' cellspacing=\'0\' cellpadding=\'0\' width=300><tr><td class=black background=\'images/profiler/tip/topleft.png\' width=\'6\' height=\'6\'></td><td class=black background=\'images/profiler/tip/top.png\' height=\'6\'></td><td class=black background=\'images/profiler/tip/topright.png\' width=\'6\' height=\'6\'></td></tr><tr><td background=\'images/profiler/tip/left.png\' width=\'6\'></td><td class=black background=\'images/profiler/tip/middle.png\' heigh=500><span class=tooltipheader style=color:#FFFFFF;z-index:0 >Esprit 271 (102<span class=greenb style=z-index:0 >+169</span>)</span><span class=tooltipline style=color:#DFB801;z-index:0>Améliore vos taux de régénération</span><span class=tooltipline style=color:#DFB801;z-index:0>de santé et de mana</span></td><td class=black background=\'images/profiler/tip/right.png\' width=\'6\'></td></tr><tr><td class=black background=\'images/profiler/tip/bottomleft.png\' width=\'6\' height=\'6\'></td><td class=black background=\'images/profiler/tip/bottom.png\' height=\'6\'></td><td class=black background=\'images/profiler/tip/bottomright.png\' width=\'6\' height=\'6\'></td></tr></table>')" onmouseout="return nd()"><span class="green">271</span></a></li>
										<li>Armure: <a class="noform" href="#" onmouseover="return overlib('<table border=\'0\' cellspacing=\'0\' cellpadding=\'0\' width=300><tr><td class=black background=\'images/profiler/tip/topleft.png\' width=\'6\' height=\'6\'></td><td class=black background=\'images/profiler/tip/top.png\' height=\'6\'></td><td class=black background=\'images/profiler/tip/topright.png\' width=\'6\' height=\'6\'></td></tr><tr><td background=\'images/profiler/tip/left.png\' width=\'6\'></td><td class=black background=\'images/profiler/tip/middle.png\' heigh=500><span class=tooltipheader style=color:#FFFFFF;z-index:0 >Armure 5120 (4690<span class=greenb style=z-index:0 >+430</span>)</span><span class=tooltipline style=color:#DFB801;z-index:0>Fait chuter le nombre de points de</span><span class=tooltipline style=color:#DFB801;z-index:0>dégàts que vous subissez des</span><span class=tooltipline style=color:#DFB801;z-index:0>attaques physiques. La réduction</span><span class=tooltipline style=color:#DFB801;z-index:0>dépend du niveau de votre attaquant.</span><span class=tooltipline style=color:#DFB801;z-index:0>Réduction des dégàts contre un</span><span class=tooltipline style=color:#DFB801;z-index:0>attaquant de niveau 60: 48.21%</span></td><td class=black background=\'images/profiler/tip/right.png\' width=\'6\'></td></tr><tr><td class=black background=\'images/profiler/tip/bottomleft.png\' width=\'6\' height=\'6\'></td><td class=black background=\'images/profiler/tip/bottom.png\' height=\'6\'></td><td class=black background=\'images/profiler/tip/bottomright.png\' width=\'6\' height=\'6\'></td></tr></table>')" onmouseout="return nd()"><span class="green">5120</span></a></li>
									</ul>
									<ul class="stats">
										<li>Atq de mélée<span class="white">300</span>
											<ul>
												<li>Puissance: <span class="white"><a href="#" onmouseover="return overlib('<table border=\'0\' cellspacing=\'0\' cellpadding=\'0\' width=250><tr><td background=\'images/profiler/tip/topleft.png\' width=\'6\' height=\'6\'></td><td background=\'images/profiler/tip/top.png\' height=\'6\'></td><td background=\'images/profiler/tip/topright.png\' width=\'6\' height=\'6\'></td></tr><tr><td background=\'images/profiler/tip/left.png\' width=\'6\'></td><td background=\'images/profiler/tip/middle.png\' heigh=500><span class=tooltipheader style=color:#;z-index:0>Puissance d\'attaque en mélée 366</span><span class=tooltipline style=color:#ffffff;z-index:0>Augmente les points de dégàts infligés avec des armes de mélée de 27,3 points de dégàts par seconde.</span><span class=tooltipline style=color:#ffffff;z-index:0>/</span></td><td background=\'images/profiler/tip/right.png\' width=\'6\'></td></tr><tr><td background=\'images/profiler/tip/bottomleft.png\' width=\'6\' height=\'6\'></td><td background=\'images/profiler/tip/bottom.png\' height=\'6\'></td><td background=\'images/profiler/tip/bottomright.png\' width=\'6\' height=\'6\'></td></tr></table>')" onmouseout="return nd()" class="noform">366</a></span></li>
												<li>Dégàts: <span class="white"><a href="#" onmouseover="return overlib('<table border=\'0\' cellspacing=\'0\' cellpadding=\'0\' width=250><tr><td background=\'images/profiler/tip/topleft.png\' width=\'6\' height=\'6\'></td><td background=\'images/profiler/tip/top.png\' height=\'6\'></td><td background=\'images/profiler/tip/topright.png\' width=\'6\' height=\'6\'></td></tr><tr><td background=\'images/profiler/tip/left.png\' width=\'6\'></td><td background=\'images/profiler/tip/middle.png\' heigh=500><span class=tooltipheader style=color:#;z-index:0>Main droite</span><span class=tooltipline style=color:#ffffff;z-index:0>Vitesse d\'attaque\(secondes) :	1,80</span><span class=tooltipline style=color:#ffffff;z-index:0>Dégéts/:	108 - 161</span><span class=tooltipline style=color:#ffffff;z-index:0>Dégàts par seconde :	74,5</span><span class=tooltipline style=color:#ffffff;z-index:0>/</span></td><td background=\'images/profiler/tip/right.png\' width=\'6\'></td></tr><tr><td background=\'images/profiler/tip/bottomleft.png\' width=\'6\' height=\'6\'></td><td background=\'images/profiler/tip/bottom.png\' height=\'6\'></td><td background=\'images/profiler/tip/bottomright.png\' width=\'6\' height=\'6\'></td></tr></table>')" onmouseout="return nd()" class="noform">108:161</a></span></li>
											</ul>
										</li>
										<li>Atq à distance<span class="white">0</span>
											<ul>
												<li>Puissance: <span class="white"><a href="#" onmouseover="return overlib('<table border=\'0\' cellspacing=\'0\' cellpadding=\'0\' width=250><tr><td background=\'images/profiler/tip/topleft.png\' width=\'6\' height=\'6\'></td><td background=\'images/profiler/tip/top.png\' height=\'6\'></td><td background=\'images/profiler/tip/topright.png\' width=\'6\' height=\'6\'></td></tr><tr><td background=\'images/profiler/tip/left.png\' width=\'6\'></td><td background=\'images/profiler/tip/middle.png\' heigh=500><span class=tooltipheader style=color:#;z-index:0>Puissance d\'attaque à distance 110</span><span class=tooltipline style=color:#ffffff;z-index:0>Augmente les points de dégàts infligés avec des armes à distance de 7,9 points de dégàts par seconde.</span><span class=tooltipline style=color:#ffffff;z-index:0></span></td><td background=\'images/profiler/tip/right.png\' width=\'6\'></td></tr><tr><td background=\'images/profiler/tip/bottomleft.png\' width=\'6\' height=\'6\'></td><td background=\'images/profiler/tip/bottom.png\' height=\'6\'></td><td background=\'images/profiler/tip/bottomright.png\' width=\'6\' height=\'6\'></td></tr></table>')" onmouseout="return nd()" class="noform">110</a></span></li>
												<li>Dégats: <span class="white"><a href="#" onmouseover="return overlib('<table border=\'0\' cellspacing=\'0\' cellpadding=\'0\' width=250><tr><td background=\'images/profiler/tip/topleft.png\' width=\'6\' height=\'6\'></td><td background=\'images/profiler/tip/top.png\' height=\'6\'></td><td background=\'images/profiler/tip/topright.png\' width=\'6\' height=\'6\'></td></tr><tr><td background=\'images/profiler/tip/left.png\' width=\'6\'></td><td background=\'images/profiler/tip/middle.png\' heigh=500><span class=tooltipheader style=color:#;z-index:0>à distance</span><span class=tooltipline style=color:#ffffff;z-index:0>Vitesse d\'attaque/(secondes) :	0,00</span><span class=tooltipline style=color:#ffffff;z-index:0>Dégétsé:	17 - 18</span><span class=tooltipline style=color:#ffffff;z-index:0>Dégàts par seconde :	1.$</span><span class=tooltipline style=color:#ffffff;z-index:0></span></td><td background=\'images/profiler/tip/right.png\' width=\'6\'></td></tr><tr><td background=\'images/profiler/tip/bottomleft.png\' width=\'6\' height=\'6\'></td><td background=\'images/profiler/tip/bottom.png\' height=\'6\'></td><td background=\'images/profiler/tip/bottomright.png\' width=\'6\' height=\'6\'></td></tr></table>')" onmouseout="return nd()" class="noform">17:18</a></span></li>
											</ul>
										</li>
									</ul>
								</div> <!-- padding -->
								<div class="hands">
									<div class="weapon0"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[15]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[15]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[15]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 	<!-- Arme 1-->
									<div class="weapon1"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[17]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[17]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[17]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 	<!-- Arme 2-->
									<div class="weapon2"><div class="item">&nbsp;</div></div>
								</div><!-- hands -->
							</div> <!-- bottom -->
						</div> <!-- middle -->
						<div class="right">
							<div class="equip"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[9]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[9]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[9]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 		<!-- Gants-->
							<div class="equip"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[5]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[5]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[5]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 		<!-- Ceinture-->
							<div class="equip"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[6]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[6]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[6]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 		<!-- Jambières-->
							<div class="equip"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[7]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[7]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[7]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 		<!-- Bottes -->
							<div class="equip"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[10]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[10]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[10]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 		<!-- Anneau 1 -->
							<div class="equip"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[11]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[11]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[11]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 		<!-- Anneau 2 -->
							<div class="equip"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[12]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[12]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[12]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 		<!-- Trinket 1 -->
							<div class="equip"><div class="item"><a href="http://fr.wowhead.com/?item=<?echo $objet_xml[13]['real_id'];?>" onmouseover="return overlib('<?echo $objet_xml[13]['real_id'];?>')" onmouseout="return nd()"><img src="images/objets/medium/<?echo $objet_xml[13]['obj_img'];?>.jpg" class="icon" alt=""></a></div></div> 		<!-- Trinket 2 -->
						</div> <!-- right -->	
					</div><!-- page1 -->
					
					<!-- ################ page compétences ################### -->
					<div class="page2" id="page4">
						<div class="top" id="top">
							<h1 class="top"><?=$j['nom']?></h1>
							<h2>Niveau 60 Tauren Chaman</h2>
							<h2>Esprit de Esprits Nomades</h2>
						</div>
						<div class="left"></div>
						<div class="skills">
							<div class="skilltype">Compétences de classe </div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barGrey.gif">
									<span class="name">Amélioration</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barGrey.gif">
									<span class="name">Restauration</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barGrey.gif">
									<span class="name">Combat élémentaire</span>
								</div>
							</div>
							<div class="skilltype">Métiers </div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barBit.gif" alt="" class="bit" width="354">
									<span class="name">Dépeçage</span><span class="level">300 / 300</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
								   <img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barBit.gif" alt="" class="bit" width="354">
								   <span class="name">Travail du cuir</span><span class="level">300 / 300</span>
								</div>
							</div>
							<div class="skilltype">Compétences secondaires </div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barBit.gif" alt="" class="bit" width="354">
									<span class="name">Secourisme</span><span class="level">300 / 300</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barBit.gif" alt="" class="bit" width="354">
									<span class="name">Monte</span><span class="level">150 / 150</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barBit.gif" alt="" class="bit" width="354">
									<span class="name">Pêche</span><span class="level">300 / 300</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barBit.gif" alt="" class="bit" width="354">
									<span class="name">Cuisine</span><span class="level">300 / 300</span>
								</div>
							</div>
							<div class="skilltype">Compétences d'armes </div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barBit.gif" alt="" class="bit" width="354">
									<span class="name">Masse</span><span class="level">300 / 300</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barBit.gif" alt="" class="bit" width="354">
									<span class="name">Défense</span><span class="level">300 / 300</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barBit.gif" alt="" class="bit" width="354">
									<span class="name">Masses à deux mains</span><span class="level">300 / 300</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barBit.gif" alt="" class="bit" width="354">
									<span class="name">Dagues</span><span class="level">300 / 300</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barBit.gif" alt="" class="bit" width="354">
									<span class="name">Haches à deux mains</span><span class="level">300 / 300</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barBit.gif" alt="" class="bit" width="354">
									<span class="name">Bâtons</span><span class="level">300 / 300</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barBit.gif" alt="" class="bit" width="354">
									<span class="name">Haches</span><span class="level">300 / 300</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barBit.gif" alt="" class="bit" width="350">
									<span class="name">Mains nues</span><span class="level">297 / 300</span>
								</div>
							</div>
							<div class="skilltype">Armures utilisables </div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barGrey.gif">
									<span class="name">Cuir</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barGrey.gif">
									<span class="name">Mailles</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barGrey.gif">
									<span class="name">Tissu</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barGrey.gif">
									<span class="name">Bouclier</span>
								</div>
							</div>
							<div class="skilltype">Langues </div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barBit.gif" alt="" class="bit" width="354">
									<span class="name">Langue : taurahe</span><span class="level">300 / 300</span>
								</div>
							</div>
							<div class="skill">
								<div class="skillbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barBit.gif" alt="" class="bit" width="354">
									<span class="name">Langue : orc</span><span class="level">300 / 300</span>
								</div>
							</div>
						</div>		
						<div class="right"></div>
					</div>
						
					<!-- ################ page réputations ################### -->								
					<div class="page2" id="page3">
						<div class="top" id="top">
							<h1 class="top"><?=$j['nom']?></h1>
							<h2>Niveau 60 Tauren Chaman</h2>
							<h2>Esprit de Esprits Nomades</h2>
						</div>
						<div class="left"></div>
						<div class="skills">
							<div class="reptype">Horde </div>
							<div class="skill">
								<div class="namerep">Orgrimmar</div>
								<div class="repbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barGreen.gif" alt="" class="bit" width="158"><a href="#" class="noform" onmouseover="changetext('repOrgrimmar','999/1000')" onmouseout="changetext('repOrgrimmar','Exalté')"><span class="level" id="repOrgrimmar">Exalté</span></a>
								</div>
							</div>
							<div class="skill">
								<div class="namerep">Thunder Bluff</div>
								<div class="repbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barGreen.gif" alt="" class="bit" width="20"><a href="#" class="noform" onmouseover="changetext('repThunder Bluff','2719/21000')" onmouseout="changetext('repThunder Bluff','Révéré')"><span class="level" id="repThunder Bluff">Révéré</span></a>
								</div>
							</div>
							<div class="skill">
								<div class="namerep">Trolls Darkspear</div>
								<div class="repbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barGreen.gif" alt="" class="bit" width="16"><a href="#" class="noform" onmouseover="changetext('repTrolls Darkspear','2231/21000')" onmouseout="changetext('repTrolls Darkspear','Révéré')"><span class="level" id="repTrolls Darkspear">Révéré</span></a>
								</div>
							</div>
							<div class="skill">
								<div class="namerep">Undercity</div>
								<div class="repbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barGreen.gif" alt="" class="bit" width="79"><a href="#" class="noform" onmouseover="changetext('repUndercity','10492/21000')" onmouseout="changetext('repUndercity','Révéré')"><span class="level" id="repUndercity">Révéré</span></a>
								</div>
							</div>
							<div class="reptype">Cartel Gentepression</div>
							<div class="skill">
								<div class="namerep">Baie-du-Butin</div>
								<div class="repbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barGreen.gif" alt="" class="bit" width="36"><a href="#" class="noform" onmouseover="changetext('repBaie-du-Butin','2732/12000')" onmouseout="changetext('repBaie-du-Butin','Honoré')"><span class="level" id="repBaie-du-Butin">Honoré</span></a>
								</div>
							</div>
							<div class="skill">
								<div class="namerep">Tribu Zandalar</div>
								<div class="repbox">
									<img class="bg" alt="" src="images/profiler/barEmpty.gif"><img src="images/profiler/barYellow.gif" alt="" class="bit" width="53"><a href="#" class="noform" onmouseover="changetext('repTribu Zandalar','1007/3000')" onmouseout="changetext('repTribu Zandalar','Neutre')"><span class="level" id="repTribu Zandalar">Neutre</span></a>
								</div>
							</div>
						</div>
						<div class="right"></div>
					</div>								
				</div><!-- main -->
				<div class="bottomBorder"></div>
				<div class="tabs">
					<div class="tab">
						<div class="left"></div>
						<div class="middle"><font id="tabfontpage1" class="white"><span onclick="doTab( 'page1' )">Personnage</span></font></div>
						<div class="right"></div>
					</div>
					<div class="tab">
						<div class="left"></div>
						<div class="middle"><font id="tabfontpage3" class="yellow"><span onclick="doTab( 'page3' )">Rep.</span></font></div>
						<div class="right"></div>
					</div>
					<div class="tab">
						<div class="left"></div>
						<div class="middle"><font id="tabfontpage4" class="yellow"><span onclick="doTab( 'page4' )">Comp.</span></font></div>
						<div class="right"></div>
					</div>
				</div>
			</div> <!-- char -->
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
					<td><a href=\"http://thottbot.com/i".$b['real_id']."\" target=_blank>T</a> |
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
							<td>".($w['img'] ? "<img src=".$w['img']." width=30px> ":'&nbsp;')."</td>
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
		echo "Pas de wishlist";
		}
		?>
		</td>
	</tr>
</table>
<?	
//Pied de page
include 'footer.php';
?>