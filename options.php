<?
//////////////////////////////////////////////
// options.php
// Script de mise à jour du paramétrage de l'application et de mise à jour des infos utilisateur
// NE PAS MODIFIER
// Version du 29/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 21/10/2008: Nettoyage du code, première version 
// 24/10/2008 - Joyrock : Ajout des logs
// 27/10/2008 - Joyrock : Affichage des logs par jour
// 29/10/2008 - Joyrock: Ajout du champ téléphone au profil

//Sécuritée
$GLOBALS['allowed'] = 1;

//includes
include "commun.inc.php";

//Initialisation de la session
$load = null;

//Si l'utilisateur est de niveau 3 ou 4 on met à jour la config serveur
if($rank>=3 && $_POST['edit_options'])
{
	//On nettoye les données passées par formulaire
	$inst = addslashes($_POST['instances']);
	$link = addslashes($_POST['forum_link']);
	
	//Infos liées à la wish liste - désactivé pour le moment
	/*
	$wish = abs(intval($_POST['limit_wishlist']));
	$addo = abs(intval($_POST['limit_obj_add']));
	$lock = $_POST['lock_wishlist'] ? 1:0;
	*/

	//On met à jour
	mysql_query("
		UPDATE servers_config SET 
		instances 	= '".$inst."',
		forum_link	= '".$link."' 
		WHERE id_server='".intval($conf['id_server'])."'
		") or die ("Erreur mysql - options.php:44 : ".mysql_error());
	
	//On log
	add_log($user_name,"Options","Mise à jour des options du Raid Orga");
	
	//Message à afficher et page de redirection
	$msg['message'] = "Configuration mise à jour.";
	$load = "options.php";
}

//Mise à jour des infos utilisateur
if($_POST['edit_user'])
{
	//chargement  des informations du perso
	$edit_ 	= mysql_query("SELECT J.* FROM (".prefix('joueurs')." J) WHERE J.id_joueur='$id'") or die ("Erreur mysql - options.php:50 : ".mysql_error());
	$edit	= mysql_fetch_assoc($edit_) or die ("Erreur mysql - options.php:51 : ".mysql_error());
	
	//On nettoye les données passées par formulaire
	$nom = !$rank || !$_POST['nom'] ? addslashes($edit['nom']):addslashes($_POST['nom']);
	$cla = !$rank ? $edit['classe']:$classe[$_POST['classe']];
	$rac = !$rank ? $edit['race']:$race[$_POST['race']];
	$gui = addslashes($_POST['guilde']);
	$spe = addslashes($_POST['specialisation']);
	$mai = addslashes($_POST['mail']);
	$not = addslashes($_POST['notes']);
	$tel = addslashes($_POST['tel']);
	$pa1 = addslashes($_POST['pass']);
	$pa2 = addslashes($_POST['pass_conf']);
	$vac = $_POST['vacance'] ? 1:0;
	$lvl = $_POST['lvl'];
	
	//Si on change le mot de passe et que la confirmation est ok
	if($pa1===$pa2 && $pa1)
	{
		$pass = $pa1;
		$msg['message'] = "Mot de passe changé avec succés!";
	}
	//Sinon
	else
	{
		if($pa2 && $pa2!='')
			$msg['erreur'] = 'Mots de passe différents';
		$pass = 'pass';
	}
	
	//Si le mail change on envois un mail de confirmation
	if($mai != $edit['mail'] && $mai)
	{
		//envoi d'un mail de test
		$msg['message'] = "Un email de test vous a été envoyé.<br>Si vous ne le recevez pas c'est sans doute que vous vous êtes trompé d'adresse, ou que votre hébergeur refuse nos mails.<br>";
		$subject = 'WOWORGA - Message de test pour votre compte "'.$edit['nom'].'"';
		$headers = 	'From: '.$mail_contact. "\r\n" .
					'X-Mailer: PHP/' . phpversion().
					'MIME-Version: 1.0' . "\r\n".
    				'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    				
		$message = "
					Ceci est un message de test envoyé par le <a href=".$root.">site d'organisation de raid</a> de la guilde ".$guild.".
					<br><br>
					Si vous le recevez, c'est que cette adresse est valide pour votre compte <i>".$edit['nom']."</i>.<br><br>
					Si il s'agit d'une erreur, veuillez nous en excuser.<br><br>
					Il est inutile de répondre à ce mail.<br><br>
					Pour information, votre adresse IP lors de l'envoi : ".$_SERVER['REMOTE_ADDR'].".
					";
			
		$message = preg_replace("#(?<!\r)\n#si", "\r\n", $message);
	
	     mail($mai, $subject, $message, $headers);
	}
	
	//On met à jour les infos en base
	mysql_query("
		UPDATE ".prefix('joueurs')."
		SET 
		nom 			= '$nom',
		race 			= '$rac',
		classe			= '$cla',
		niveau			= '$lvl',
		mail			= '$mai',
		guilde			= '$gui',
		specialisation	= '$spe',
		notes			= '$not',
		vacance			= '$vac',
		pass			= '$pass',
		telephone		= '$tel' 
		WHERE id_joueur = '$id'
		") or die("Erreur mysql - options.php:121 : ".mysql_error());		
		
	//On log
	add_log($user_name,"Options","Mise à jour des infos utilisateur");
}

//rechargement du perso avec les données actuelles
$edit_ 	= mysql_query("SELECT J.* FROM (".prefix('joueurs')." J) WHERE J.id_joueur='$id'") or die("Erreur mysql - options.php:124 : ".mysql_error());	
$edit	= mysql_fetch_assoc($edit_) or die("Erreur mysql - options.php:125 : ".mysql_error());	

//rechargement des options avec les données actuelles
$conf_ 	= mysql_query("SELECT * FROM servers_config WHERE prefix='".$db_prefix."'") or die("Erreur mysql - options.php:128 : ".mysql_error());	
$conf	= mysql_fetch_assoc($conf_) or die("Erreur mysql - options.php:129 : ".mysql_error());	

//On met à jour la session
session_name('WOWORGA');
session_start();
$_SESSION['conf'] = $conf;
session_write_close();

//Si on a une adresse de redirection
if($load)
{
	include 'header.php';
		echo '<big>ACTION EFFECTUEE</big><br><meta http-equiv="Refresh" content="2; url='.$load.'" /><a href='.$load.'>Si la page ne se recharge pas cliquer sur ce lien.</a>';
	include 'footer.php';
	exit;
}

//Si l'utilisateur a un mot de passe vide
if(!$edit['pass']){
	$msg['erreur'] = "Vous n'avez pas encore spécifié votre mot de passe, vous devriez le faire maintenant.";
}

//En-tête
include 'header.php';
//Corp de page
?>
<table style=width:90%;border:0px>
	<tr>
	<?
	if($rank>=3)
	{
	?>
		<td width=50%>
			<div align=center>
				<big>OPTIONS DE LA SECTION <?=$guild?></big><br>
				<table style=width:500px;>
					<form action=options.php method=POST>
						<tr>
							<th style=font-size:10px;width:50%>Adresse vers votre Forum externe<br><i>Laisser vide si vous ne voulez pas d'icone.</i></th>
							<td><input type=text name=forum_link value="<?=$conf['forum_link']?>"></td>
						</tr>
						<tr>
							<th style=font-size:10px>Liste des instances<br><i>Séparées par des ";"<br>L'ordre compte.</i></th>
							<td><textarea name=instances rows=8><?=$conf['instances']?></textarea></td>
						</tr>
						<?
						//Désactivation de la wishliste
						/*
						<tr>
							<th style=font-size:10px>Nombre de voeux de la wishlist<br><i><b>0</b> pour la désactiver.</i></th>
							<td><input type=text name=limit_wishlist value="<?=$conf['limit_wishlist']?>"></td>
						</tr>
						<tr>
							<th style=font-size:10px>Vérouiller la wishlist<br><i>Les utilisateurs non-lead auront alors accès à la liste totale, comme les leaders.</i></th>
							<td><input type=checkbox name=lock_wishlist value=1 <?=($conf['lock_wishlist']?'CHECKED':'')?>"></td>
						</tr>
						<tr>
							<th style=font-size:10px>Limite d'objets que peuvent ajouter les membres non-lead</th>
							<td><input type=text name=limit_obj_add value="<?=$conf['limit_obj_add']?>"></td>
						</tr>
						*/
						?>
						<tr>
							<th style=font-size:10px>Langue</th>
							<td>
								<select name=langue style=width:90%>
									<option value='fr'>Français</option>
								</select>
							</td>
						<tr>
							<td colspan=2 align=center><input type=submit name=edit_options value="Valider"></td>
						</tr>
					</form>
				</table>
			</div>
		</td>
	<?
	}
	?>
		<td>
			<div align=center>
				<big>OPTIONS DU COMPTE <i><?=$edit['nom']?></i></big><br>
				<table style=width:500px;>
					<form action=options.php method=POST>
						<tr>
							<td>En vacance<br><i style=font-size:10px>Si vous partez en vacance cette option vous met automatiquement Absent</i></td>
							<td><input type=checkbox name=vacance value=1 <?=($edit['vacance'] ? 'CHECKED':'')?>></td>
						</tr>
						<tr>
							<td>Nom</td>
							<td style=width:60%><input type=text name=nom maxlenght=150 value='<?=$edit['nom']?>' <?=($rank ? '':'DISABLED')?>></td>
						</tr>
						<tr>
							<td>Classe</td>
							<td>
								<select name=classe style=width:90% <?=($rank ? '':'DISABLED')?>>
								<?
								$i=0;
								foreach($classe as $c)
								{
									echo "<option value=$i ".($c==$edit['classe'] ?'SELECTED':'').">$c</option>";
									$i++;
								}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td>Race</td>
							<td><select name=race style=width:90% <?=($rank ? '':'DISABLED')?>><?$i=0;foreach($race as $r){echo "<option value=$i ".($r==$edit['race'] ?'SELECTED':'').">$r</option>";$i++;}?></select></td>
						</tr>
						<tr>
							<td>Niveau</td>
							<td><select name=lvl style=width:90%><?for($i=80;$i>0;$i--){echo "<option ".($i==$edit['niveau'] ?'SELECTED':'').">$i</option>";}?></select></td>
						</tr>
						<tr>
							<td>Guilde</td>
							<td><input type=text name=guilde maxlenght=150 value='<?=$edit['guilde']?>'></td>
						</tr>
						<tr>
							<td>Spécialisation</td>
							<td><textarea name=specialisation><?=$edit['specialisation']?></textarea></td>
						</tr>
						<tr>
							<td>Notes</td>
							<td><textarea name=notes><?=$edit['notes']?></textarea></td>
						</tr>
						<tr>
							<td>Telephone<br><i style=font-size:10px>Uniquement visible par les officiers</i></td>
							<td><input type=text name=tel maxlenght=20 value='<?=$edit['telephone']?>'></td>
						</tr>
						<tr>
							<td>Mot de passe :</td>
							<td><input type=password name=pass maxlenght=20></td>
						</tr>
						<tr>
							<td>Confirmation :</td>
							<td><input type=password name=pass_conf maxlenght=20></td>
						</tr>
						<tr>
							<td>Mail<br><i style=font-size:10px>Si vous souhaitez être averti de votre sélection<br>(pas encore fonctionnel)</i></td>
							<td><input type=text name=mail value="<?=$edit['mail']?>"></td>
						</tr>
						<tr>
							<td align=center colspan=2>
								<input type=submit name=edit_user value="Editer">
							</td>
						</tr>
						<input type=hidden name=edit value=<?=$edit['id_joueur']?>>
					</form>
				</table>
			</div>
		</td>
	</tr>
<?
if($rank>=3)
{
	//rechargement des logs avec les données actuelles pour les 4 derniers jours
	if($_REQUEST['filtre_user'])
	{
	$conditions_filtre=$conditions_filtre." AND log_utilisateur='".$_REQUEST['filtre_user']."'";
	}
	
	if($_REQUEST['filtre_section'])
	{
	$conditions_filtre=$conditions_filtre." AND log_page='".$_REQUEST['filtre_section']."'";
	}
	
	$log_ 	= mysql_query("
		SELECT * FROM ".prefix('log')."  
		WHERE TO_DAYS(NOW()) - TO_DAYS(log_date) <= 4 ".$conditions_filtre."
		ORDER BY log_date DESC
		") or die("Erreur mysql - options.php:140 : ".mysql_error());	
	
	echo "
	<tr><td colspan=2></td></tr>
	<tr>
		<td colspan=2>
			<table style=width:100%;>
				<tr>
					<th>Date</th>
					<th>Utilisateur</th>
					<th>Section</th>
					<th>Message</th>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>";
					if($_REQUEST['filtre_user']) echo "<a href='options.php?filtre_section=".$_REQUEST['filtre_section']."&filtre_user='>Reset</a>";
					
	echo "			</td>
					<td>";
					if($_REQUEST['filtre_section']) echo "<a href='options.php?filtre_section=&filtre_user=".$_REQUEST['filtre_user']."'>Reset</a>";

	echo "			</td>
					<td>&nbsp;</td>
				</tr>";
				
	$ancien_jour="";
	while($log = mysql_fetch_assoc($log_))
	{
		if($ancien_jour!=substr($log['log_date'], 0, -9))
		{
			$mois=substr($log['log_date'],5,2);
			$jour=substr($log['log_date'],8,2);
			$annee=substr($log['log_date'],0,4);
			echo "<tr><th colspan=4 style=font-size:12px><center>".utf8_encode(strftime("%A %d %B",mktime(0,0,0,$mois,$jour,$annee)))."</center></th></tr>";
			$ancien_jour=substr($log['log_date'], 0, -9);
		}
	
		echo "
				<tr>
					<td>".$log['log_date']."</td>
					<td><a href='options.php?filtre_section=".$_REQUEST['filtre_section']."&filtre_user=".$log['log_utilisateur']."'>".$log['log_utilisateur']."</a></td>
					<td><a href='options.php?filtre_section=".$log['log_page']."&filtre_user=".$_REQUEST['filtre_user']."'>".$log['log_page']."</a></td>
					<td>".$log['log_message']."</td>
				</tr>";
	}
	
	echo "
			</table>
		</td>
	</tr>";
}
?>

</table>
<?
//Pied de page
include 'footer.php';
?>