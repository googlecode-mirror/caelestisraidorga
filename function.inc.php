<?
//////////////////////////////////////////////
// function.inc.php
// Librairie de fonctions
// NE PAS MODIFIER
// Version du 29/10/2008
// Auteur: Joyrock
//////////////////////////////////////////////:
// 21/10/2008: Nettoyage du code, première version 
// 24/10/2008 - Joyrock : Ajout d'une fonction de log 
// 29/10/2008 - Joyrock : Wowhead peut maintenant ajouter les objets de craft
// 30/10/2008 - Joyrock : Ajout de la fonction qui test l'existance d'une table

/* vérifier qu"une table existe */
function mysql_table_exists($table)
{
    $query = "SELECT COUNT(*) FROM $table";
    $result = mysql_query($query);
    $num_rows = @mysql_num_rows($result);

    if($num_rows)
    return TRUE;
    else
    return FALSE;
}

	function add_log($joueur,$page,$log)
	{
	$return = mysql_query("
		INSERT INTO ".prefix('log')." (log_utilisateur,log_page,log_message) 
		VALUES ('".$joueur."','".$page."','".$log."')
		") or die("Erreur mysql - function.inc.php - add_log:14 : ".mysql_error());
	return $return;
	}
	
	function prefix($table,$db=null){
		global $db_prefix;
		$db = $db===null ? (isset($_SESSION['db'])?$_SESSION['db']:$_GET['db']) : $db;
		return $db_prefix.$table;
	}
	
	function aff_date($date,$short=0,$timestamp=0){
			$heure=substr($date,8,2);
			$minute=substr($date,10,2);
			$sec=substr($date,12,2);
			$mois=substr($date,4,2);
			$jour=substr($date,6,2);
			$annee=substr($date,0,4);
			if($timestamp)
				return mktime($heure,$minute,$sec,$mois,$jour,$annee);
			if($short==1)
				return utf8_encode(strftime("%d/%m - %H:%M",mktime($heure,$minute,$sec,$mois,$jour,$annee)));
			if($short==2)
				return utf8_encode(strftime("%H:%M",mktime($heure,$minute,$sec,$mois,$jour,$annee)));
			if($short==3)
				return utf8_encode(strftime("%m",mktime($heure,$minute,$sec,$mois,$jour,$annee)));
			if($short==4)
				return utf8_encode(strftime("%A %d",mktime($heure,$minute,$sec,$mois,$jour,$annee)));
			if($short==5)
				return utf8_encode(strftime("%d/%m/%Y",mktime($heure,$minute,$sec,$mois,$jour,$annee)));
					
			return utf8_encode(strftime("%A %d %B - %H:%M",mktime($heure,$minute,$sec,$mois,$jour,$annee)));
	}
	
	function rem_acc($string){
		$remplace = array('à'=>'a',
                         'á'=>'a',
                         'â'=>'a',
                         'ã'=>'a',
                         'ä'=>'a',
                         'å'=>'a',
                         'ò'=>'o',
                         'ó'=>'o',
                         'ô'=>'o',
                         'õ'=>'o',
                         'ö'=>'o',
                         'è'=>'e',
                         'é'=>'e',
                         'ê'=>'e',
                         'ë'=>'e',
                         'ì'=>'i',
                         'í'=>'i',
                         'î'=>'i',
                         'ï'=>'i',
                         'ù'=>'u',
                         'ú'=>'u',
                         'û'=>'u',
                         'ü'=>'u',
                         'ÿ'=>'y',
                         'ñ'=>'n',
                         'ç'=>'c',
                         'ø'=>'0',
						 'à'=>'a',
                         'á'=>'a',
                         'â'=>'a',
                         'ã'=>'a',
                         'ä'=>'a',
                         'å'=>'a',
                         'ò'=>'o',
                         'ó'=>'o',
                         'ô'=>'o',
                         'õ'=>'o',
                         'ö'=>'o',
                         'è'=>'e',
                         'é'=>'e',
                         'ê'=>'e',
                         'ë'=>'e',
                         'ì'=>'i',
                         'í'=>'i',
                         'î'=>'i',
                         'ï'=>'i',
                         'ù'=>'u',
                         'ú'=>'u',
                         'û'=>'u',
                         'ü'=>'u',
                         'ÿ'=>'y',
                         'ñ'=>'n',
                         'ç'=>'c',
                         'ø'=>'0'
                         );
						 
     $string=strtr($string,$remplace); 
	 return $string;
	}

	function MyStripSlashes($chaine) {
	  return( get_magic_quotes_gpc() == 1 && is_string($chaine)?
	          stripslashes($chaine) :
	          $chaine );
	}

	function thottbot($address,$img=null,$car=null){
		$lines = file ($address);
		
		for($i=0;$i<count($lines);$i++){
			
			if(!$img && preg_match('<td width=64 valign=middle>', $lines[$i])){
				
				$img = $lines[$i+1];
				preg_match('#background=\'(.*?)\'#', $img, $m);
				$img = $m[1];
				
			}
			if(preg_match('<table class=ttb width=300>', $lines[$i])){
				$car = $lines[$i];
				break;
			}
			
		}
		
		return $img.$car;
	}
	
	function wowhead($idobj){
		//$idobj=32500;
		//$handle = fopen("page2.html", "r") or die("Fichier introuvable. L'analyse a ete suspendue");
		$handle = fopen("http://fr.wowhead.com/?item=".$idobj, "r") or die("Fichier introuvable. L'analyse a ete suspendue");
		//$handle = fopen("test.htm", "r") or die("Fichier introuvable. L'analyse a ete suspendue");
		
		$debgrep=0;
		while ($fdata = fread($handle, 2048)){
		$page_to_parse=$page_to_parse.$fdata;
		}
		$page_to_parse = str_replace(array("\r\n", "\n", "\r"), ' ', $page_to_parse);
		
		//Recupération de la donnée
		if($page_to_parse = stristr($page_to_parse, "<div id=\"ic".$idobj))
			{
			
			//Les caractéristiques
			$pos = strpos($page_to_parse, '<script type');
			$obj_carac=substr($page_to_parse,0, $pos);
			// Nettoyage code
			$obj_carac = eregi_replace('href="/\?([a-z]+)\=([0-9]*)"',"",$obj_carac);
			$obj_carac = eregi_replace('href="([a-z]+):;"',"",$obj_carac);
			$obj_carac = eregi_replace('href="/\?([a-z]+)&([a-z]+);([a-z]+)=([a-z]+)=([0-9]*);([a-z]+)=([0-9]*);([a-z]+)=([0-9]*)"',"",$obj_carac);
			$obj_carac=addslashes($obj_carac);
			
			//Le nom de l'objet
			$page_to_parse = stristr($page_to_parse, "q");
			$pos = strpos($page_to_parse, '</b>');
			$obj_nom=substr($page_to_parse,4, $pos-4);
			$obj_nom=addslashes($obj_nom);
			
			//L'image
			$page_to_parse = stristr($page_to_parse, "Icon.create('");
			
			$page_to_parse = stristr($page_to_parse, "'");
			$pos = strpos($page_to_parse, ',');
			$obj_img=substr($page_to_parse,1, $pos-2);
			$obj_img=addslashes($obj_img);
			$obj_img=utf8_encode(strtolower(utf8_decode($obj_img)));
			if(isset($obj_img))
			{
			copy("http://static.wowhead.com/images/icons/large/".$obj_img.".jpg", "images/objets/large/".$obj_img.".jpg");
			copy("http://static.wowhead.com/images/icons/medium/".$obj_img.".jpg", "images/objets/medium/".$obj_img.".jpg");
			}
			
			//Le boss
			$page_to_parse = stristr($page_to_parse, "listview-void");
			
			if($page_to_parse = stristr($page_to_parse, "npc="))
			{
			$pos = strpos($page_to_parse, '>');
			$boss_id=substr($page_to_parse,4, $pos-5);
			
			$page_to_parse = stristr($page_to_parse, ">");
			$pos = strpos($page_to_parse, '</a>');
			$boss_nom=substr($page_to_parse,1, $pos-1);
			$boss_nom=addslashes($boss_nom);
			}
			else
			{
			$boss_id=1;
			}
			
			//Ajout du boss dans la base si n'existe pas
			$boss_ = mysql_query("SELECT * 
						 FROM ".prefix('boss')."
						 WHERE real_id_boss=".$boss_id) or die("Erreur mysql - function.inc.php - wowhead:189 : ".mysql_error());
			$nb_boss = mysql_num_rows($boss_);

			if($nb_boss==0) 
				{
				mysql_query("INSERT INTO ".prefix('boss')." (real_id_boss,boss_nom) 
					VALUES (".$boss_id.",'".$boss_nom."')") or die("Erreur mysql - function.inc.php - wowhead:196 : ".mysql_error());
				}
				
			//Ajout objet s'il n'existe pas			
			$ob_ = mysql_query("SELECT * 
					FROM ".prefix('objets')."
					WHERE real_id=".$idobj) or die("Erreur mysql - function.inc.php - wowhead:201 : ".mysql_error());
			$nb_ob = mysql_num_rows($ob_);	
			
			if($nb_ob==0) 
				{
				mysql_query("
					INSERT INTO ".prefix('objets')." (real_id,obj_nom,obj_img,obj_carac,dropped_by) 
					VALUES (".$idobj.",'".$obj_nom."','".$obj_img."','".$obj_carac."',".$boss_id.")"
					) or die("Erreur mysql - function.inc.php - wowhead:208 : ".mysql_error());
					$result="Objet ajouté à la base depuis wowhead...";
				}
			else
				{
				$result="Id déjà dans la base.";
				}
			}
		else
			{
			$result="Id non trouvé sur wowhead.";
			}
		//return "Nom objet: ".$obj_nom."<br>Image: ".$obj_img."<br>Carac: ".$obj_carac."<br>Boss id: ".$boss_id."<br>Boss nom: ".$boss_nom."<br>---";
		return $result;
	}
	
	function pourcentage($nombre,$total,$pourcent){ 
		$nbr = ($nombre/$total) * $pourcent; 
		return round($nbr); 
	}
	
	function setxmlcache($xmlid_joueur){
	global $wow_serv;
	
	$d_=mysql_query("SELECT * FROM ".prefix('joueurs')." 
		WHERE id_joueur=".$xmlid_joueur) or die("Erreur mysql - function.inc.php - setxmlcache:235 : ".mysql_error());
	$d = mysql_fetch_assoc($d_);
	
	$url = "http://eu.wowarmory.com/character-sheet.xml?r=".$wow_serv."&n=".$d['nom'];

	if (($handle = curl_init($url)) === false)
	{
	    print "[-] curl_init() failed\n";
	    exit;
	}

	$headers = array(
	  'Accept: text/xml,application/xml,application/xhtml+xml',
	  'Accept-Charset: utf-8,ISO-8859-1'
	);

	curl_setopt($handle, CURLOPT_HEADER, 0);
	curl_setopt($handle, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($handle, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.3) Gecko/20070101 Firefox/2.0.0.4');
	curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

	if (($content = curl_exec($handle)) === false)
	{
	    print "[-] Impossible de mettre à jour depuis l'armory.\n";
	    curl_close($handle);    
	    exit;
	}
	curl_close($handle);    

	mysql_query("UPDATE ".prefix('joueurs')." set xml_data='".addslashes($content)."', xml_update='".date("YmdHis",time())."' 
		WHERE id_joueur=".$d['id_joueur']) or die("Erreur mysql - function.inc.php - setxmlcache:266 : ".mysql_error());
	return $content;
	}

	function getxmlcache($xmlid_joueur){
		
	$d_=mysql_query("SELECT * FROM ".prefix('joueurs')." 
		WHERE id_joueur=".$xmlid_joueur) or die("Erreur mysql - function.inc.php - getxmlcache:273 : ".mysql_error());
	$d = mysql_fetch_assoc($d_);
	
	$content=$d['xml_data'];
	return $content;
	}
?>