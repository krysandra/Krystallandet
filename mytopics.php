<?php

include('header.php');

?>


<?php

echo "<div class='category'><a href=''>Mine tråde</a></div>";
echo "<div id='topmenu'>";
echo "<a href='mytopics.php'>Ubesvarede</a> &#9679; <a href='mytopics.php?all'>Alle (ingame)</a> &#9679; <a href='mytopics.php?offgame'>Offgame</a>";
echo "</div>";	

if($user_rank > 0)
{
	if(isset($_GET['all']))
	{
		$topicnumber = $forum->get_number_of_ingame_topics_to_show_from_user($user_logged_in_ID, $user_rank)->fetch_assoc();
		echo "<div class='category'><a href=''>Alle ingame tråde</a></div>";
		if ($topicnumber['res'] < 1) { echo "<p class='emptymsg'>Ingen tråde i denne kategori</p>"; }
		else
		{
			echo "<div class='pagenavigaton'>";	
			$totalpages = ceil($topicnumber['res'] / $topicsperpage);
				
			if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) { $currentpage = (int) $_GET['currentpage']; } 
			else { $currentpage = 1; } 
			if ($currentpage > $totalpages) { $currentpage = $totalpages; } 
			if ($currentpage < 1) { $currentpage = 1; } 
			
			$offset = ($currentpage - 1) * $topicsperpage;
			$usertopics = $forum->get_ingame_topics_to_show_from_user($user_logged_in_ID, $offset, $topicsperpage, $user_rank);		
			$range = 4; 
						
			
			if ($currentpage > 1) 
			{
				$prevpage = $currentpage - 1;
		 		echo " <a class='navlink' href='mytopics.php?all&currentpage=$prevpage'>«</a> ";	
				if ($currentpage - $range > 1) 
				{
					echo " <a class='navlink' href='mytopics.php?all&currentpage=1'>1</a> ";
				}		
			}
			
			// loop to show links to range of pages around current page
			for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) 
			{
		   		if (($x > 0) && ($x <= $totalpages)) 
				{	
					if ($x == $currentpage) 
					{
						 if($totalpages > 1) { echo " <span class='navlink_active'><b>$x</b></span>"; }

			  		} 
					else { echo " <a class='navlink' href='mytopics.php?all&currentpage=$x'>$x</a> "; } 
		   		} 
			} 
			
			if ($currentpage != $totalpages) 
			{	
				$nextpage = $currentpage + 1;	
				if ($totalpages - $range > $currentpage) {	echo " <a class='navlink' href='mytopics.php?all&currentpage=$totalpages'>".$totalpages."</a> ";	}
				if ($totalpages > 1) { echo " <a class='navlink' href='mytopics.php?all&currentpage=$nextpage'>»</a> "; }
			} 
			
			echo "</div>";				
			/* End building navigation */
			
			echo "<table id='topiclist'>";
			echo "<tr>";
			echo"<tr><th>Titel</th><th>Svar</th><th>Visninger</th><th>Seneste indlæg</th></tr>";
			
			while($t = $usertopics->fetch_assoc())
			{
				
				echo "<tr><td>";
				if($t['pinned'] == 1) { echo "<img class='topicimage hovereffect' src='images/topic_pinned.png'/>"; }
				else if($t['locked'] == 1) { echo "<img class='topicimage hovereffect' src='images/topic_locked.png'/>"; }
				else { echo "<img class='topicimage hovereffect' src='images/topic_normal.png'/>"; }
				
				$numofposts = $forum->get_numberof_posts($t['topic_ID'])->fetch_assoc();
				$numofanswers = ($numofposts['res'])-1;
				$pagenumber = ceil($numofposts['res'] / $postsperpage);
				
				echo "<a class='topictitle' href='viewtopic.php?t=".$t['topic_ID']."'>".$t['title']."</a>";
				echo "<br/>";

				if($t['fk_character_ID'] == 0)
				{
					echo "Af <a class='username'>slettet karakter</a> ";
				}
				else
				{
					$user = $forum->get_character($t['fk_character_ID'])->fetch_assoc();
					echo "Af <a class='username' style='color:".$user['color'].";' href='characterprofile.php?id=".$user['character_ID']."'>".$user['name']."</a> ";
				}

				echo " &raquo; ".date("j. M Y G:i", strtotime($t['datetime']));
				if($t['warning'] != "" ) { echo "<span class='topicwarning'>Advarsel: ".$t['warning']."</span>"; }

				echo "</td>";
				
				echo "<td class='center'>".$numofanswers."</td>";
				echo "<td class='center'>".$t['views']."</td>";
				
				$lastpost = $forum->get_last_post($t['topic_ID'])->fetch_assoc();
				echo "<td>";
				
				if($lastpost['fk_character_ID'] == 0)
				{
					echo "af <a class='username'>slettet karakter</a> ";
				}
				else
				{
					$user = $forum->get_character($lastpost['fk_character_ID'])->fetch_assoc();
					echo "af <a class='username' style='color:".$user['color'].";' href='characterprofile.php?id=".$user['character_ID']."'>".$user['name']."</a> ";
				}

				echo "<a href='viewtopic.php?t=".$t['topic_ID']."&currentpage=".$pagenumber."#".$lastpost['post_ID']."'><img src='images/icon_topic_latest.gif' title='Gå til post'/></a>";
				echo "</br>";	
				echo date("j. M Y G:i", strtotime($lastpost['datetime']))."</td>";
				echo "</tr>";
				
				
			}	
			echo "</table>";
			
			echo "<div class='pagenavigaton'>";	
			if ($currentpage > 1) 
			{
				$prevpage = $currentpage - 1;
		 		echo " <a class='navlink' href='mytopics.php?all&currentpage=$prevpage'>«</a> ";	
				if ($currentpage - $range > 1) 
				{
					echo " <a class='navlink' href='mytopics.php?all&currentpage=1'>1</a> ";
				}		
			}
			
			// loop to show links to range of pages around current page
			for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) 
			{
		   		if (($x > 0) && ($x <= $totalpages)) 
				{	
					if ($x == $currentpage) 
					{
						 if($totalpages > 1) { echo " <span class='navlink_active'><b>$x</b></span>"; }

			  		} 
					else { echo " <a class='navlink' href='mytopics.php?all&currentpage=$x'>$x</a> "; } 
		   		} 
			} 
			
			if ($currentpage != $totalpages) 
			{	
				$nextpage = $currentpage + 1;	
				if ($totalpages - $range > $currentpage) {	echo " <a class='navlink' href='mytopics.php?all&currentpage=$totalpages'>".$totalpages."</a> ";	}
				if ($totalpages > 1) { echo " <a class='navlink' href='mytopics.php?all&currentpage=$nextpage'>»</a> "; }
			} 
			
			echo "</div>";				
			/* End building navigation */	
		}
	}
	if(isset($_GET['offgame']))
	{
		$topicnumber = $forum->get_number_of_offgame_topics_to_show_from_user($user_logged_in_ID, $user_rank)->fetch_assoc();
		echo "<div class='category'><a href=''>Offgame</a></div>";
		if ($topicnumber['res'] < 1) { echo "<p class='emptymsg'>Ingen tråde i denne kategori</p>"; }
		else
		{
			echo "<div class='pagenavigaton'>";	
			$totalpages = ceil($topicnumber['res'] / $topicsperpage);
				
			if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) { $currentpage = (int) $_GET['currentpage']; } 
			else { $currentpage = 1; } 
			if ($currentpage > $totalpages) { $currentpage = $totalpages; } 
			if ($currentpage < 1) { $currentpage = 1; } 
			
			$offset = ($currentpage - 1) * $topicsperpage;
			$usertopics = $forum->get_offgame_topics_to_show_from_user($user_logged_in_ID, $offset, $topicsperpage, $user_rank);		
			$range = 4; 
						
			
			if ($currentpage > 1) 
			{
				$prevpage = $currentpage - 1;
		 		echo " <a class='navlink' href='mytopics.php?all&currentpage=$prevpage'>«</a> ";	
				if ($currentpage - $range > 1) 
				{
					echo " <a class='navlink' href='mytopics.php?all&currentpage=1'>1</a> ";
				}		
			}
			
			// loop to show links to range of pages around current page
			for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) 
			{
		   		if (($x > 0) && ($x <= $totalpages)) 
				{	
					if ($x == $currentpage) 
					{
						 if($totalpages > 1) { echo " <span class='navlink_active'><b>$x</b></span>"; }

			  		} 
					else { echo " <a class='navlink' href='mytopics.php?all&currentpage=$x'>$x</a> "; } 
		   		} 
			} 
			
			if ($currentpage != $totalpages) 
			{	
				$nextpage = $currentpage + 1;	
				if ($totalpages - $range > $currentpage) {	echo " <a class='navlink' href='mytopics.php?all&currentpage=$totalpages'>".$totalpages."</a> ";	}
				if ($totalpages > 1) { echo " <a class='navlink' href='mytopics.php?all&currentpage=$nextpage'>»</a> "; }
			} 
			
			echo "</div>";				
			/* End building navigation */
			
			echo "<table id='topiclist'>";
			echo "<tr>";
			echo"<tr><th>Titel</th><th>Svar</th><th>Visninger</th><th>Seneste indlæg</th></tr>";
			
			while($t = $usertopics->fetch_assoc())
			{
				
				echo "<tr><td>";
				if($t['pinned'] == 1) { echo "<img class='topicimage hovereffect' src='images/topic_pinned.png'/>"; }
				else if($t['locked'] == 1) { echo "<img class='topicimage hovereffect' src='images/topic_locked.png'/>"; }
				else { echo "<img class='topicimage hovereffect' src='images/topic_normal.png'/>"; }
				
				$numofposts = $forum->get_numberof_posts($t['topic_ID'])->fetch_assoc();
				$numofanswers = ($numofposts['res'])-1;
				$pagenumber = ceil($numofposts['res'] / $postsperpage);
				
				echo "<a class='topictitle' href='viewtopic.php?t=".$t['topic_ID']."'>".$t['title']."</a>";
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

				echo " &raquo; ".date("j. M Y G:i", strtotime($t['datetime']));
				if($t['warning'] != "" ) { echo "<span class='topicwarning'>Advarsel: ".$t['warning']."</span>"; }

				echo "</td>";
				
				echo "<td class='center'>".$numofanswers."</td>";
				echo "<td class='center'>".$t['views']."</td>";
				
				$lastpost = $forum->get_last_post($t['topic_ID'])->fetch_assoc();
				echo "<td>";
				if ($t['ingame'] == 1 ) 
				{
					if($lastpost['fk_character_ID'] == 0)
					{
						echo "af <a class='username'>slettet karakter</a> ";
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
						echo "af <a class='username'>Gæst</a> ";
					}
					else
					{
						$user = $forum->get_superuser($lastpost['fk_superuser_ID'])->fetch_assoc();
						echo "af <a class='username' style='color:".$user['color'].";'  href='memberprofile.php?id=".$user['superuser_ID']."'>".$user['name']."</a> ";
					}
				}	

				echo "<a href='viewtopic.php?t=".$t['topic_ID']."&currentpage=".$pagenumber."#".$lastpost['post_ID']."'><img src='images/icon_topic_latest.gif' title='Gå til post'/></a>";
				echo "</br>";	
				echo date("j. M Y G:i", strtotime($lastpost['datetime']))."</td>";
				echo "</tr>";
				
				
			}	
			echo "</table>";	
			
			echo "<div class='pagenavigaton'>";	
			if ($currentpage > 1) 
			{
				$prevpage = $currentpage - 1;
		 		echo " <a class='navlink' href='mytopics.php?all&currentpage=$prevpage'>«</a> ";	
				if ($currentpage - $range > 1) 
				{
					echo " <a class='navlink' href='mytopics.php?all&currentpage=1'>1</a> ";
				}		
			}
			
			// loop to show links to range of pages around current page
			for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) 
			{
		   		if (($x > 0) && ($x <= $totalpages)) 
				{	
					if ($x == $currentpage) 
					{
						 if($totalpages > 1) { echo " <span class='navlink_active'><b>$x</b></span>"; }

			  		} 
					else { echo " <a class='navlink' href='mytopics.php?all&currentpage=$x'>$x</a> "; } 
		   		} 
			} 
			
			if ($currentpage != $totalpages) 
			{	
				$nextpage = $currentpage + 1;	
				if ($totalpages - $range > $currentpage) {	echo " <a class='navlink' href='mytopics.php?all&currentpage=$totalpages'>".$totalpages."</a> ";	}
				if ($totalpages > 1) { echo " <a class='navlink' href='mytopics.php?all&currentpage=$nextpage'>»</a> "; }
			} 
			
			echo "</div>";				
			/* End building navigation */
		}
	}	
	if (empty($_GET))
	{
		echo "<div class='category'><a href=''>Mangler svar</a></div>";
		if ($topictags < 1) { echo "<p class='emptymsg'>Ingen tråde i denne kategori</p>"; }
		else
		{
			echo "<table id='topiclist'>";
			echo "<tr>";
			echo "<th>Titel</th><th>Min karakter</th><th>Dato for tag</th><th>Tagged af</th><th></th>";
			$usertags = $forum->get_all_tags_from_superuser($user_logged_in_ID);
			while($tag = $usertags->fetch_assoc())
			{
				echo "<tr>";
				echo "<td>";
				$current_topic = $forum->get_topic($tag['fk_topic_ID'])->fetch_assoc();	
				
				if($current_topic['pinned'] == 1) { echo "<img class='topicimage hovereffect' src='images/topic_pinned.png'/>"; }
				else { echo "<img class='topicimage hovereffect' src='images/topic_normal.png'/>"; }
				
				echo "<a class='topictitle' href='viewtopic.php?t=".$current_topic['topic_ID']."'>".$current_topic['title']."</a>";
				echo "<br/>";

				if($current_topic['fk_character_ID'] == 0)
				{
					echo "Af <a class='username'>slettet karakter</a> ";
				}
				else
				{
					$user = $forum->get_character($current_topic['fk_character_ID'])->fetch_assoc();
					echo "Af <a class='username' style='color:".$user['color'].";' href='characterprofile.php?id=".$user['character_ID']."'>".$user['name']."</a> ";
				}

				echo " &raquo; ".date("j. M Y G:i", strtotime($current_topic['datetime']));
				if($current_topic['warning'] != "" ) { echo "<span class='topicwarning'>Advarsel: ".$current_topic['warning']."</span>"; }

				echo "</td>";
				
				
				$tagged_character = $forum->get_character($tag['fk_character_ID'])->fetch_assoc();
				echo "<td class='center'>
				<a  class='username' style='color:".$tagged_character['color'].";' href='characterprofile.php?id=".$tag['fk_character_ID']."'>".$tagged_character['name']."</td>";
				echo "<td class='center'>".date("j. M Y G:i", strtotime($tag['date']))."</td>";
				$tagged_by_user = $forum->get_superuser($tag['tagged_by_ID'])->fetch_assoc();
				echo "<td class='center'><a class='username' style='color:".$tagged_by_user['color'].";' 
				href='memberprofile.php?id=".$tag['tagged_by_ID']."'>".$tagged_by_user['name']."</td>";
				
				echo "<td class='center'><form method='post'> <input type='hidden' name='tag' value='".$tag['tag_ID']."'/>
				<input type='submit' name='delete_tag' value='Fjern tag'/> </form></td>";
							
				echo "</tr>";
			}
			echo "</table>";
		}
		
		if($_POST['delete_tag'])
		{
			$tag_to_remove = $_POST['tag'];
			$update_tag = $forum->update_tag(0, $tag_to_remove);	
			header('Location:mytopics.php');
		}
	}

}

else
{
	echo "<span class='forumerror'>Du skal være logget ind for at se denne side.</span>";	
}

?>

<?php

include('footer.php');

?>