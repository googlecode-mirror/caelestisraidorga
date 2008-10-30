<?
//////////////////////////////////////////////
// header.php
// En-tête de toutes les pages, sauf login.
// NE PAS MODIFIER
// Version du 21/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 21/10/2008: Nettoyage du code, première version 
// 
?>
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
	
	<meta http-equiv="pragma" content="no-cache" />
	<META http-equiv="cache-control" content="no-cache">
	
	<title>Organisation d'instance : <?=$guild?></title>
	
	<link rel="stylesheet" type="text/css" media="all" href="<?=$root?>/style.css" />
	<link rel="stylesheet" type="text/css" media="all" href="<?=$root?>/global.css" />
	<link rel="stylesheet" type="text/css" href="default.css">
	
	<script src="global.js" type="text/javascript"></script>
	<script type="text/javascript" src="overlib.js"></script><!-- overLIB (c) Erik Bosrup -->

	
<script language="javascript">
	function display_txt(given_txt,fading){
		a = document.getElementById("displaytext");
		a.innerHTML = given_txt;
	}
</script>
<script language="JavaScript">

function changetext(champ,message) {
    document.getElementById(champ).innerHTML = message;
	  }
</script>
<script type="text/javascript">
	function majTextQuest(text)
	{
		document.getElementById("affichage").innerHTML=text;
	}
</script>
</head>
<body>
<div class=header_menu>
	<table style='width:100%;border:0px;margin:0 0 0 0;'>
		<tr>
				<td align=left width=580px>
					<a href=index.php><img onmouseover=display_txt(this.alt); onmouseout=display_txt(""); alt="Organisation" src=images/icones/index.jpg></a>
					<?if($conf['forum_link']){?>
						<a href="<?=$conf['forum_link']?>"><img onmouseover=display_txt(this.alt); onmouseout=display_txt(""); alt="Forum" src=images/icones/forum.jpg></a>
					<?}?>
					<a href=drops.php><img onmouseover=display_txt(this.alt); onmouseout=display_txt(""); alt="Butin" src=images/icones/butin.jpg></a>
					<?if($rank>=0){?>
						<a href=info_player.php><img onmouseover=display_txt(this.alt); onmouseout=display_txt(""); alt="Joueurs" src=images/icones/player.jpg></a>
					<?}?>
					<?if($rank>=3){?>
						<a href=stats.php><img onmouseover=display_txt(this.alt); onmouseout=display_txt(""); alt="Stats" src=images/icones/dkp.jpg></a>
					<?}?>
					<a href=dispo.php><img onmouseover=display_txt(this.alt); onmouseout=display_txt(""); alt="Disponibilité" src=images/icones/orga.jpg></a>
					<?if($conf['limit_wishlist']>0){?>
						<a href=wishlist.php><img onmouseover=display_txt(this.alt); onmouseout=display_txt(""); alt="Wish List" src=images/icones/wishlist.jpg></a>
					<?}?>
					<a href=options.php><img onmouseover=display_txt(this.alt); onmouseout=display_txt(""); alt="Options" src=images/icones/options.jpg></a>
				</td>
				<td align=center style=vertical-align:middle;font-weight:bold;font-family:verdana;font-size:20px;color:#72895b id="displaytext" name="displaytext" width=200px>
				</td>
				<td align=right>
					<a href=help.php><img onmouseover=display_txt(this.alt); onmouseout=display_txt(""); alt="AIDE/FAQ" src=images/icones/help.jpg></a>
					<a href=login.php?disco=1><img onmouseover=display_txt(this.alt); onmouseout=display_txt(""); alt="Déconnecter <br><i><?=$user_name?></i>" src=images/icones/disco.jpg></a>
				</td>
		</tr>
	</table>
</div>
<div align=center class=content>
