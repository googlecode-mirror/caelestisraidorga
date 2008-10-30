<?
//////////////////////////////////////////////
// footer.php
// Pied de toutes les pages, sauf login.
// NE PAS MODIFIER
// Version du 21/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 21/10/2008 : Nettoyage du code, première version 
// 
?>
</div>
<table class=footer_menu>
	<tr>
		<td width=25% align=left style=font-size:10px;vertical-align:bottom>
			<?=$user_name?$user_name:'&nbsp;'?>
		</td>
		<td width=50%>
			<a href="<? echo $_SERVER['HTTP_REFERER'];?>">RETOUR</a>
		</td>
		<td>
		</td>
		<td width=25% align=right style=font-size:10px;vertical-align:bottom>
			<?=$guild?>
		</td>
	</tr>
</table>
<div class=erreur>	<?=$msg['erreur']?>	</div>
<div class=message>	<?=$msg['message']?></div>
</body>
</html>