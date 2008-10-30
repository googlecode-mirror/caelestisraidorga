<?
include 'commun.inc.php';
include 'bbcode.php';

$action = $_REQUEST['action'] ? $_REQUEST['action'] : null;

$load 	= null;

switch($action){
	case 'del':
	    $delid = intval($_REQUEST['del']);
        $verif = $_GET['verif'] ? true:false;
        
        if($verif){
			$del = mysql_query("SELECT * FROM ".prefix('forum')." WHERE id_mess='$delid' AND poster='$id'");
			$del = mysql_fetch_assoc($del);
			
			if($del['poster']){
				$new_file = null;
				$load = $root.'/forum.php?action=view&view='.$del['id_file'];
				
				if($del['id_mess'] == $del['id_file']){
					//Renommage de la file
					$tmp = mysql_query("SELECT id_mess,titre FROM ".prefix('forum')." WHERE id_file='".$delid."' ORDER BY -date LIMIT 1") or die(mysql_error());
					$tmp = mysql_fetch_assoc($tmp);
					
					$new_file = $tmp['id_mess'];
					if($new_file){
						mysql_query("UPDATE ".prefix('forum')." SET id_file='$new_file' WHERE id_file='$delid'") or die(mysql_error());
						if(!$tmp['titre']){
							mysql_query("UPDATE ".prefix('forum')." SET titre='".$del['titre']."' WHERE id_mess='$new_file'") or die(mysql_error());
						}
					}
					$load = $new_file ? $root.'/forum.php?action=view&view='.$new_file:$root.'/forum.php';
				}
				mysql_query("DELETE FROM ".prefix('forum')." WHERE id_mess='$delid' AND poster='$id'") or die(mysql_error());
		    }
		}
		else{
			include 'header.php';
			?>
				<big>Êtes vous sûr ?</big>
				<br>
				<br>
				<a href=forum.php?del=<?=$delid?>&action=del&verif=1>OUI</a> / <a href=forum.php?action=view&view=<?=$delid?>>NON</a>
			<?
			include 'footer.php';
		}

	break;
	case 'post':
		$reply = intval($_REQUEST['reply']);
		$titre = addslashes($_POST['titre']);
		$mess  = addslashes($_POST['message']);
		$editid= intval($_REQUEST['edit']);
		
		$edit = array();
		if($editid){
			
			$edit = mysql_query("SELECT * FROM ".prefix('forum')." WHERE id_mess='$editid'");
			$edit = mysql_fetch_assoc($edit);
			
			$edit = $edit['poster']==$id ? $edit:array();
			
		}
		$edit['data'] = $mess ? $mess : $edit['data'];
		$edit['titre']= $titre ? $titre : $edit['titre'];
		
		if(!$reply && !$titre && $mess && !$editid){
			$msg['erreur'] = 'Vous devez donner un titre à votre message.';
		}
		elseif(!$id){
			$msg['erreur'] = 'Deconnectez-vous et reconnectez-vous suite au changement de manipulation des sessions. Vous ne verrez ce message qu\'une fois, après ca marchera (normalement ^^)';
		}
		elseif($titre && !$mess){
			$msg['erreur'] = 'Votre message est vide.';
		}
		elseif($mess){
			
			$time   = date("YmdHis",time());
			
			if($edit['id_mess']){
				
				mysql_query("UPDATE ".prefix('forum')." SET edited='$time', data='$mess', titre='$titre' WHERE id_mess='".$edit['id_mess']."'") or die(mysql_error());
				$mess_id = $edit['id_mess'];
				
			}
			else{

				$file   = $reply ? "'".$reply."'" : 'id_mess';
				mysql_query("INSERT INTO ".prefix('forum')." (`titre`,`data`,`id_file`,`poster`,`date`) VALUES ('$titre','$mess',$reply,'$id','$time')") or die(mysql_error());
				$mess_id = mysql_insert_id();
				$id_file = $reply ? $reply:$mess_id;
				mysql_query("UPDATE  ".prefix('forum')." SET id_file=$file WHERE id_mess='$mess_id'") or die(mysql_error());
				mysql_query("UPDATE  ".prefix('forum')." SET last_reply_id='$mess_id' WHERE id_mess='$reply'") or die(mysql_error());
				mysql_query("UPDATE  ".prefix('forum')." SET read_by='' WHERE id_file='$id_file'") or die(mysql_error());
				
			}
			$load = $root.'/forum.php?action=view&view='.$mess_id;
		}
		
		if($load) break;
		
		include 'header.php';
		?>
			<table style=width:50% class=forum>
			<form action=forum.php?action=post&reply=<?=$reply?> method=POST>
				<tr>
					<th>Titre
					</th>
					<td align=center><input type=text name=titre value="<?=$edit['titre']?>">
					</td>
				</tr>
				<tr>
					<th>Message
					</th>
					<td align=center><textarea name=message rows=14><?=strip_tags($edit['data'])?></textarea>
					</td>
				</tr>
				<tr>
					<td colspan=2 align=center><input type=submit value=Envoyer>
					</td>
				</tr>
				<input type=hidden name=edit value=<?=$edit['id_mess']?>>
			</form>
			</table>
		<?
		include 'footer.php';

	break;
	case 'view':
		
		$id_mess = intval($_GET['view']);
		
		$d_ = mysql_query("
							SELECT id_file FROM ".prefix('forum')."
							WHERE id_mess='$id_mess'
							ORDER BY date
							"
						) or die(mysql_error());
		$d = mysql_fetch_assoc($d_);
		
		$id_file = $d['id_file'];
		
		$mess = array();
		$first = null;
		$d_ = mysql_query("
							SELECT F.*,J.nom,J.id_joueur,J.race,J.classe FROM (".prefix('forum')." F, ".prefix('joueurs')." J)
							WHERE id_file='$id_file' AND J.id_joueur=F.poster
							ORDER BY date
							"
						) or die(mysql_error());
		while($d = mysql_fetch_assoc($d_)){
			
			$first = $first ? $first:$d;
			$mess[] = $d;
			
		}
		
		//UPDATE DE "LU"
		$read = explode(',',$first['read_by']);
		$read[] = $id;
		$read = array_unique($read);
		$read = implode(',',$read);
		mysql_query("UPDATE ".prefix('forum')." SET read_by='$read' WHERE id_file='$id_file'");
		
		include 'header.php';
		?>
			<big><a href=forum.php><?=$base['display']?> : Index du Forum</a>> <?=$first['titre']?></big>
			<table style=width:800px class=forum>
		<?
			$i=0;
			foreach($mess as $m){
				echo "
						<tr ".($i%2?'class=odd':'').">
							<th rowspan=2 class=info style=background-color:black;>
								<a href='info_player.php?player=".$m['id_joueur']."'>
									".$m['nom']."<br>
									<!--<img src=image.php?race=".$m['race']."&class=".$m['classe'].">-->
									<img src=".$root."/images/races/".strtolower($m['race']).".jpg>
								</a>
								<br>
								<!--<img style=width:20px src=".$root."/images/classes/".rem_acc(strtolower($m['classe'])).".gif>-->&nbsp;".($m['poster']==$id ? "<a href=forum.php?edit=".$m['id_mess']."&action=post><img src=".$root."/images/edit.gif></a><a href=forum.php?del=".$m['id_mess']."&action=del><img src=".$root."/images/del.gif></a>":'')."&nbsp
							</th>
							<th style=height:15px>".$m['titre']."
							</th>
							<th>".aff_date($m['date'])."
							</th>
						</tr>
						<tr>
							<td colspan=2 align=justify>".bb(nl2br(strip_tags($m['data'])))."
							</td>
						</tr>
				";
				$i++;
			}
		?>
			<form action=forum.php?action=post&reply=<?=$id_file?> method=POST>
				<tr>
					<td colspan=3 style='border-top:1px solid green'>
						<input type=submit value=Repondre style=width:150px;>
					</td>
				</tr>
			</form>
			</table>
		<?
		include 'footer.php';
		
	break;
	default:
	
		$d_ = mysql_query("
							SELECT F.*, ff.date last_reply_date, jj.nom last_reply_poster,jj.id_joueur last_reply_poster_id, J.nom, J.id_joueur
							FROM (".prefix('forum')." F)
							LEFT JOIN ".prefix('forum')." ff ON F.last_reply_id=ff.id_mess
							LEFT JOIN ".prefix('joueurs')." J ON F.poster=J.id_joueur
							LEFT JOIN ".prefix('joueurs')." jj ON ff.poster=jj.id_joueur
							WHERE F.id_mess=F.id_file order by -ff.date
							"
						) or die('E : '.mysql_error());
		$mess = array();
		while($d = mysql_fetch_assoc($d_)){
			
			$d['date'] = $d['last_reply_date'] ? $d['last_reply_date'] : $d['date'];
			
			$mess[$d['date']] = $d;
			
		}

		$d_ = mysql_query("
							SELECT id_file,read_by,count(*) nb
							FROM (".prefix('forum')." F)
							GROUP BY id_file
							"
						) or die(mysql_error());
		$answer = array();
		$read	= array();
		while($d = mysql_fetch_assoc($d_)){
			
			$answer[$d['id_file']] = $d['nb']-1;
			$read[$d['id_file']]   = explode(',',$d['read_by']);
		}
		/*
		echo '<pre>';
		print_r($read);
		//*/
		krsort($mess);

		include 'header.php';
		?>
			<big>Forum <?=$base['display']?></big>
			<table style=width:800px class=forum>
				<tr>
					<th>&nbsp;
					</th>
					<th style=width:250px>Titre
					</th>
					<th>Réponses
					</th>
					<th>Poster
					</th>
					<th>Dernier Message
					</th>
					<th style=width:75px>Date
					</th>
				</tr>
				<?
					$i=0;
					foreach($mess as $m){
						echo "
							<tr ".($i%2 ? 'class=odd':'').">
								<td>".(array_search($id,$read[$m['id_file']])===false ? '<img src=images/unread.gif>':'<img src=images/read.gif>')."
								</td>
								<td><a href='forum.php?action=view&view=".$m['id_file']."'>".$m['titre']."</a>
								</td>
								<td align=center><b>".$answer[$m['id_file']]."</b>
								</td>
								<td><a href='info_player.php?player=".$m['id_joueur']."'>".$m['nom']."</a>
								</td>
								<td><a href='info_player.php?player=".$m['last_reply_poster_id']."'>".$m['last_reply_poster']."</a>
								</td>
								<td>".aff_date($m['date'],1)."
								</td>
							</tr>
							";
						$i++;
					}
				?>
				<form action=forum.php?action=post method=POST>
				<tr>
					<td colspan=5 align=left style='border-top:1px solid green'>
						<input type=submit value="Nouveau Message" style=width:150px;>
					</td>
				</tr>
				</form>
			</table>
		<?
		include 'footer.php';
	break;
}
if($load){
	include 'header.php';
		echo '<big>ACTION EFFECTUEE</big><br><meta http-equiv="Refresh" content="2; url='.$load.'" /><a href='.$load.'>Si la page ne se recharge pas cliquer sur ce lien.</a>';
	include 'footer.php';
	exit;
}
?>