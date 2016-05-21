<?php
include('header.php');
?>

<?php
echo "<div id='forumcontainer'>";
echo "<div id='forumlist'>";
if(isset($_GET['latestposts']))
{
			echo "<div class='category'><a href=''>50 senest besvarede emner</a></div>";
			
			$topics = $forum->get_fifty_latest_overall_posts($user_rank);
			echo "<table id='topiclist'>";
			echo"<tr><th>Titel</th><th>Svar</th><th>Visninger</th><th>Seneste indlæg</th></tr>";
			while($t = $topics->fetch_assoc())
			{
				echo "<tr><td>";
				if($t['pinned'] == 1) { echo "<img class='topicimage hovereffect' src='images/topic_pinned.png'/>"; }
				else if($t['locked'] == 1) { echo "<img class='topicimage hovereffect' src='images/topic_locked.png'/>"; }
				else { echo "<img class='topicimage hovereffect' src='images/topic_normal.png'/>"; }
				
				echo "<a class='topictitle' href='viewtopic.php?t=".$t['topic_ID']."'>".$t['topictitle']."</a>";
				echo "<br/>";
				if ($t['ingame'] == 1 ) 
				{
					if($t['fk_character_ID'] == 0)
					{
						echo "Af <a class='username'>slettet karakter</a> ";
					}
					else
					{
						$user = $forum->get_character($t['fk_character_ID'])->fetch_assoc();
						echo "Af <a class='username' style='color:".$user['color'].";' href='characterprofile.php?id=".$user['character_ID']."'>".$user['name']."</a> ";
					}
				}
				else 
				{
					if($t['fk_superuser_ID'] == 0)
					{
						echo "Af <a class='username'>Gæst</a> ";
					}
					else
					{
						$user = $forum->get_superuser($t['fk_superuser_ID'])->fetch_assoc();
						echo "Af <a class='username' style='color:".$user['color'].";' href='memberprofile.php?id=".$user['superuser_ID']."'>".$user['name']."</a> ";
					}
				}		
				echo "&raquo; ".date("j. M Y G:i", strtotime($t['datetime']));
				if($t['warning'] != "" ) { echo "<span class='topicwarning'>Advarsel: ".$t['warning']."</span>"; } else { echo "<br/>"; }
				echo " i <a href='viewforum.php?f=".$t['forum_ID']."'>".$t['forumtitle']."</a></td>";
				
				$numofposts = $forum->get_numberof_posts($t['topic_ID'])->fetch_assoc();
				$numofanswers = ($numofposts['res'])-1;
				$pagenumber = ceil($numofposts['res'] / $postsperpage);
				
				echo "<td class='center'>".$numofanswers."</td>";
				echo "<td class='center'>".$t['views']."</td>";
				
				$lastpost = $forum->get_last_post($t['topic_ID'])->fetch_assoc();
				echo "<td>";
				if ($t['ingame'] == 1 ) 
				{
					if($lastpost['fk_character_ID'] == 0)
					{
						echo "Af <a class='username'>slettet karakter</a> ";
					}
					else
					{
						$user = $forum->get_character($lastpost['fk_character_ID'])->fetch_assoc();
						echo "af <a class='username' style='color:".$user['color'].";' href='characterprofile.php?id=".$user['character_ID']."'>".$user['name']."</a> ";
					}
				}
				else 
				{
					if($lastpost['fk_superuser_ID'] == 0)
					{
						echo "Af <a class='username'>Gæst</a> ";
					}
					else
					{
						$user = $forum->get_superuser($lastpost['fk_superuser_ID'])->fetch_assoc();
						echo "af <a class='username' style='color:".$user['color'].";' href='memberprofile.php?id=".$user['superuser_ID']."'>".$user['name']."</a> ";
					}
				}	
				echo "<a href='viewtopic.php?t=".$t['topic_ID']."&currentpage=".$pagenumber."#".$lastpost['post_ID']."'><img src='images/icon_topic_latest.gif' title='Gå til post'/></a>";
				echo "</br>";	
				echo date("j. M Y G:i", strtotime($lastpost['datetime']))."</td>";
				echo "</tr>";
			}
			echo "</table>";
}
else
{	
	echo "<span class='forumerror'>Intet forum</span>";
}

echo "</div>";
include('sidebar.php');
echo "</div>";


?>
<?php
include('footer.php');
?>