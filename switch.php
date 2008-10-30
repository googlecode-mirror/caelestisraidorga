<?
	include "commun.inc.php";
	
	$d_ = mysql_query("SELECT * FROM ".prefix('drops')." ORDER BY drop_nom") or die(mysql_error());
	
	$drop = array();
	while($d = mysql_fetch_assoc($d_)){
		
		$drop[$d['drop_nom']] = $d;
		
	}
	
	$k = array_keys($drop);
	for($i=0;$i<count($k);$i++){
		
		$d = $drop[$k[$i]];
		
		mysql_query("
					INSERT INTO ".prefix('objets')." 
					(`objet_nom`,`objet_dkp`,`thottbot`,`wowdbu`,`allakhazam`)
					VALUES
					('".addslashes($d['drop_nom'])."','".$d['drop_dkp']."','".$d['thottbot']."','".$d['wowdbu']."','".$d['allakhazam']."')
					");
					
		mysql_query("
					UPDATE ".prefix('drops')." SET
					id_objet = '".mysql_insert_id()."'
					WHERE drop_nom = '".addslashes($drop[$k[$i]]['drop_nom'])."'
					");
	}
	
?>