<?
function bb($string,$colors=1){
	
	$bb_replace 		= array (
									'#\[[Bb]\](.*?)\[/[Bb]\]#si',
									'#\[[Ii]\](.*?)\[/[Ii]\]#si',
									'#\[[Uu]\](.*?)\[/[Uu]\]#si',
									'#\[[Ss]\](.*?)\[/[Ss]\]#si',
									'#\[[Qq][Uu][Oo][Tt][Ee]\](.*?)\[/[Qq][Uu][Oo][Tt][Ee]\]#si',
									'#\[img\]((ht|f)tp://)([^\r\n\t<\"]*?)\[/img\]#si',
									"#\[url\]([\w]+?://[^ \"\n\r\t<]*?)\[/url\]#si",
									'#\[url\]((www|ftp)\.[^ \"\n\r\t<]*?)\[/url\]#is',
									'#\[url=([\w]+?://[^ \"\n\r\t<]*?)\](.*?)\[/url\]#si',
									'#\[url=((www|ftp)\.[^ \"\n\r\t<]*?)\](.*?)\[/url\]#is',
									'#\[[Cc][Oo][Ll][Oo][Rr]=(\#[0-9A-F]{6}|[a-z\-]+)\](.*?)\[/[Cc][Oo][Ll][Oo][Rr]\]#si',
								);
								
	$bb_replacements 	= array (
									'<b>\\1</b>',
									'<i>\\1</i>',
									'<u>\\1</u>',
									'<s>\\1</s>',
									'<br/><b>Citation :</b><br/><table width="90%" cellspacing="1" cellpadding="3" align="center"><tr><td style="border:1px solid '.($colors ? $bg[$align[0]] : '#000000').'">\\1</td></tr></table>',
									'<img src=\\1' . str_replace(' ', '%20', '\\3') . '>',
									'<a href=\\1 target=_blank>\\1</a>',
									'<a href=http://\\1 target=_blank>\\1</a>',
									'<a href=\\1 target=_blank>\\2</a>',
									'<a href=http://\\1 target=_blank>\\3</a>',
									'<font color="\\1">\\2</font>',
								);
	
	$string = preg_replace($bb_replace, $bb_replacements, $string);
	
	return $string;
}
?>