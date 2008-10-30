<?
//////////////////////////////////////////////
// add_message.php
// Script d'ajout de message dans un raid
// NE PAS MODIFIER
// Version du 21/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:

//Sécurité
$GLOBALS['allowed'] = 1;

//includes
include "commun.inc.php";

//On récupère et nettoie les informations transmises par formulaire et url si elles existent
$id_inst = intval($_GET['id_inst']);
$titre 	= addslashes(strip_tags($_POST['titre']));
$mess	= addslashes(strip_tags($_POST['message']));
$action = $_REQUEST['action'];

//En fonction de l'action (edtion, creation...)
switch($action)
{
	//On edite un message existant
	case 'edit':
		//identifiant du message à editer
		$id_mess = intval($_REQUEST['mess']);
		
		//Récupération des informations liées à l'instance en base pour rediriger vers la page
		$inst_ 	= mysql_query("
			SELECT I.* FROM (".prefix('instances')." I, ".prefix('messages')." M) 
			WHERE id_mess='".$id_mess."' 
			AND M.id_inst=I.id_inst
			") or die("Erreur mysql - add_message.php:31 : ".mysql_error());
		$inst 	= mysql_fetch_assoc($inst_) or die("Erreur mysql - add_message.php:48 : ".mysql_error());
		$id_inst=$inst['id_inst'];
			
		if($_POST['envoyer'] && $mess)
		{
			//On met à jour le message edité en base
			mysql_query("
				UPDATE ".prefix('messages')." 
				SET 
				mess_data  	= '".$mess."',
				mess_titre 	= '".$titre."',
				edited	   	= '".date("YmdHis",time())."'
				WHERE id_mess='".$id_mess."' AND id_joueur='".$id."'
				") or die("Erreur mysql - add_message.php:33 : ".mysql_error());
		
			$load = $root."/details.php?id_inst=".$id_inst;	
		}
		
		//On  la dernière version du message en base
		$edit = mysql_query("
			SELECT * FROM ".prefix('messages')." 
			WHERE id_mess='".$id_mess."'
			") or die("Erreur mysql - add_message.php:51 : ".mysql_error());
		$edit = mysql_fetch_assoc($edit) or die("Erreur mysql - add_message.php:59 : ".mysql_error());
	
	break;
	
	//Cas de suppression
	case 'del':
		//identifiant du message à supprimer
		$id_mess = intval($_REQUEST['mess']);
			
		//Récupération des informations liées à l'instance en base pour rediriger vers la page
		$inst_ 	= mysql_query("
			SELECT I.* FROM (".prefix('instances')." I, ".prefix('messages')." M) 
			WHERE id_mess='".$id_mess."' 
			AND M.id_inst=I.id_inst") or die("Erreur mysql - add_message.php:69 : ".mysql_error());
		$inst = mysql_fetch_assoc($inst_) or die("Erreur mysql - add_message.php:73 : ".mysql_error());
		$id_inst=$inst['id_inst'];
		
		//Suppression en base du message
		mysql_query("
			DELETE FROM ".prefix('messages')." 
			WHERE id_mess='".$id_mess."' 
			AND id_joueur='".$id."'") or die("Erreur mysql - add_message.php:77 : ".mysql_error());
			
		$load = $root."/details.php?id_inst=".$id_inst;	
		
	break;
	
	default:
		//chargement de l'instance
		$inst_ = mysql_query("
								SELECT I.* FROM (".prefix('instances')." I)
								WHERE I.id_inst='".$id_inst."' 
							"
							) or die("Erreur mysql - add_message.php:88 : ".mysql_error());
							
		$inst  = mysql_fetch_assoc($inst_) or die("Erreur mysql - add_message.php:94 : ".mysql_error());
		
		if($_POST['envoyer'] && $mess)
		{
			mysql_query("
				INSERT INTO ".prefix('messages')." (`id_joueur`,`id_inst`,`mess_titre`,`mess_data`,`mess_date`)
				VALUES ('".$id."','".$id_inst."','".$titre."','".$mess."','".date("YmdHis",time())."')
				") or die("Erreur mysql - add_message.php:98 : ".mysql_error());
						
			$load = $root."/details.php?id_inst=".$id_inst;
		}		
	break;	
}

//Si mode de prévisualisation
if($_POST['previsu'])
{	
	$edit['mess_titre'] = $titre;
	$edit['mess_data'] 	= $mess;
}

//Si url de redirection - page de redirection et exit
if($load)
{
	include 'header.php';
	echo '<big>ACTION EFFECTUEE</big><br><meta http-equiv="Refresh" content="2; url='.$load.'" /><a href='.$load.'>Si la page ne se recharge pas cliquer sur ce lien.</a>';
	include 'footer.php';
	exit;
}

//En-tête de page
include 'header.php'

//Corp de page
?>
<big><?=($action=='edit'?'Edition du ':'')?>Message relatif à l'instance <a href=details.php?id_inst=<?=$id_inst?>><?=strtoupper($inst['inst_nom'])?></a> organisée le <?=aff_date($inst['date'])?></big><br><br>
<table style=width:600px;>
	<form action=add_message.php?id_inst=<?=$id_inst?> method=POST>
		<tr>
			<th style=width:20%>Titre</th>
			<td align=center><input name=titre type=text value="<?=$edit['mess_titre']?>"></td>
		</tr>
		<tr>
			<th>Message</th>
			<td align=center><textarea name=message rows=7><?=$edit['mess_data']?></textarea></td>
		</tr>
		<tr>
			<td colspan=2 align=center>
				<input type=submit name=envoyer style=width:40% value="Envoyer"> 
				<input type=submit name=previsu	style=width:40% value="Prévisualiser">
			</td>
		</tr>
		<?=($action == 'edit'?"<input type=hidden name=mess value=$id_mess><input type=hidden name=action value=edit>":'')?>
	</form>
</table>
<?
//Pied de page
include 'footer.php';
?>