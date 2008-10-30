<?
	
	$class  = $_GET['class'];
	$race	= $_GET['race'];
	include 'config.inc.php';
	header ("Content-type: image/png");
	$background = imagecreatefromjpeg("$root/images/races/".$race.".jpg");
	$c = imagecreatefrompng("$root/images/classes/".$class.".png");

	imagecolortransparent($c,imagecolorexact($c,0,0,0)); 

	$b_x = imagesx($background); 
	$b_y = imagesy($background); 	
	$c_x = imagesx($c); 
	$c_y = imagesy($c); 
	
	$percent=3;
	
	/*
	imageline ( $c  , 0, 0, 0, $c_y, imagecolorexact($c,50,50,50) );
	imageline ( $c  , 1, 0, 1, $c_y, imagecolorexact($c,50,50,50) );
	imageline ( $c  , 2, 0, 2, $c_y, imagecolorexact($c,50,50,50) );
	*/
	//imagecopyresized($background, $c, $b_x, $b_y, $c_x, $c_y, $b_x, $b_y, $c_x, $c_y);
	//imagecopyresized($background, $c, 0,0,0,0, $b_x, $b_y, $c_x*$percent, $c_y*$percent);
	
	imagecopymerge($background,$c,$b_x-$c_x,$b_y-$c_y,0,0,$c_x,$c_y,100);
	
	ImagePng ($background);
?>