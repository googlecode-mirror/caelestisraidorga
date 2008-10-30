<?
//////////////////////////////////////////////
// language.php
// Localisation
// NE PAS MODIFIER
// Version du 21/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
//FICHIER DE LANGUE, on commence doucement
$lang	= array();

//TEMPLATE MAIL
$lang['mail_tpl_valid_core'] 	= "Vous avez été sélectionné(e) pour participer à l'instance %instance du %date.<br><br>Vous serez dans le groupe de %leader avec %group.";
$lang['mail_tpl_invalid_core'] 	= "Votre sélection pour l'instance %instance du %date a été annulée, ne tenez donc plus compte du mail précédent.";
$lang['mail_tpl_valid_title'] 	= "WOWORGA : Sélection pour %instance du %date.";
$lang['mail_tpl_invalid_title'] = "WOWORGA : ANNULATION pour %instance du %date.";


$lang['mail_headers']			= 	'From: '.$mail_contact.'' . "\r\n" .
									'X-Mailer: PHP/' . phpversion().
									'MIME-Version: 1.0' . "\r\n".
				    				'Content-type: text/html; charset=utf-8' . "\r\n";

//POUR L'INDEX
$lang['instance'] = "Instance";
$lang['type'] = "Type";
$lang['date'] = "Date";
$lang['organisateur'] = "Par";
$lang['selected_players'] = "Choisi(s)";
$lang['dkp'] = "DKP";
$lang['new_instance'] = "Nouvelle instance";
$lang['gerer_mon_groupe'] = "Gérer mon groupe";
$lang['finished'] = "Instance terminée"
?>