<?
$idobj = $_REQUEST['idobj'];

$handle = fopen("http://www.wowhead.com/?item=".$idobj, "r") or die("Fichier introuvable. L'analyse a ete suspendue");
while ($fdata = fread($handle, 2048)){
echo $fdata;
}
?>