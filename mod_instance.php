<?
include "commun.inc.php";

asort($instance);

//INSTANCE

//AND I.date='$date' AND I.inst_orga='$inst_orga'

$inst_ = mysql_query("
						SELECT * FROM (".prefix('instances')." I)
						WHERE I.id_inst='$id_inst' AND I.inst_type='$inst_type' 
						ORDER BY date
					"
					) or die (mysql_error());
					
$inst  = mysql_fetch_assoc($inst_);

//id_inst = '$id_inst',
//date = '$date',
//inst_orga = '$inst_orga',
if(isset($_POST['instance'])){
	
mysql_query("

		UPDATE ".prefix('instances')." SET
			
			inst_type = '".$_POST['inst_type']."',
			
			
			inst_nom = '".addslashes($instance[$_POST['instance']])."'
		WHERE id_inst='$id_inst'
") or die(mysql_error());
	$msg['message'] = "L'instance est maintenant ".$instance[$_POST['instance']]." en ".$inst_type[$_POST['inst_type']]." ! <a href=index.php>RETOUR</a>";
}

include 'header.php';
?>
<div align=center>
	<big>MODIFICATION DE L'INSTANCE <?=$inst['inst_nom']?> du <?=aff_date($inst['date'])?> en <?=$inst_type[$inst['inst_type']]?>.<br></big><br><br>
	
		<table style=width:500px;>
		<form action=mod_instance.php?id_inst=<?=$id_inst?> method=POST>
			<tr>
				<td>Instance
				</td>
				<td><select name=instance><?foreach($instance as $i=>$n){echo "<option value=$i>$n</option>";}?></select>
				</td>
			</tr>
			<tr>
				<td>En
				</td>
				<td><select name=inst_type><?foreach($inst_type as $i=>$n){echo "<option value=$i>$n</option>";}?></select>
				</td>
			</tr>
			
			<tr>
				<td colspan=2 align=center>
					<input type=submit value=Modifier>
				</td>
			</tr>
		</form>
		</table>
</div>
<?
include 'footer.php';
?>