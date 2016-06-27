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
				if($t['topictype'] != "" ) { echo "<span class='topicwarning'>".$t['topictype']; if($t['warning'] != "" ) { echo " <span class='italic'>(Advarsel: ".$t['warning'].")</span>"; } echo "</span>"; }
				else if($t['warning'] != "" ) { echo "<span class='topicwarning'><span class='italic'>Advarsel: ".$t['warning']."</span></span>"; } 
				else { echo "<br/>"; }
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
else if(isset($_GET['openthreads']))
{
	echo "<div class='category'><a href=''>Åbne tråde</a></div>";
	
	/* Pages if number of topics > topics per page */
			
			$topicnumber = $forum->count_open_topics($user_rank)->fetch_assoc();
			$totalpages = ceil($topicnumber['res'] / $topicsperpage);
			
			echo "<div class='pagenavigaton'>";			
			if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) { $currentpage = (int) $_GET['currentpage']; } 
			else { $currentpage = 1; } 
			if ($currentpage > $totalpages) { $currentpage = $totalpages; } 
			if ($currentpage < 1) { $currentpage = 1; } 
			
			$offset = ($currentpage - 1) * $topicsperpage;
			$topics = $forum->get_open_topics($user_rank);		
			$range = 4; 
			
			if ($currentpage > 1) 
			{
				$prevpage = $currentpage - 1;
		 		echo " <a class='navlink' href='search.php?openthreads&currentpage=$prevpage'>«</a> ";	
				if ($currentpage - $range > 1) 
				{
					echo " <a class='navlink' href='search.php?openthreads&currentpage=1'>1</a> ";
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
					else { echo " <a class='navlink' href='search.php?openthreads&currentpage=$x'>$x</a> "; } 
		   		} 
			} 
			
			if ($currentpage != $totalpages) 
			{	
				$nextpage = $currentpage + 1;	
				if ($totalpages - $range > $currentpage) {	echo " <a class='navlink' href='search.php?openthreads&currentpage=$totalpages'>".$totalpages."</a> ";	}
				if ($totalpages > 1) { echo " <a class='navlink' href='search.php?openthreads&currentpage=$nextpage'>»</a> "; }
			} 
			
			echo "</div>";				
			/* End building navigation */
			
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
				if($t['topictype'] != "" ) { echo "<span class='topicwarning'>".$t['topictype']; if($t['warning'] != "" ) { echo " <span class='italic'>(Advarsel: ".$t['warning'].")</span>"; } echo "</span>"; }
				else if($t['warning'] != "" ) { echo "<span class='topicwarning'><span class='italic'>Advarsel: ".$t['warning']."</span></span>"; } 
				else { echo "<br/>"; }
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
			
			echo "<div class='pagenavigaton'>";			
			
			if ($currentpage > 1) 
			{
				$prevpage = $currentpage - 1;
		 		echo " <a class='navlink' href='search.php?openthreads&currentpage=$prevpage'>«</a> ";	
				if ($currentpage - $range > 1) 
				{
					echo " <a class='navlink' href='search.php?openthreads&currentpage=1'>1</a> ";
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
					else { echo " <a class='navlink' href='search.php?openthreads&currentpage=$x'>$x</a> "; } 
		   		} 
			} 
			
			if ($currentpage != $totalpages) 
			{	
				$nextpage = $currentpage + 1;	
				if ($totalpages - $range > $currentpage) {	echo " <a class='navlink' href='search.php?openthreads&currentpage=$totalpages'>".$totalpages."</a> ";	}
				if ($totalpages > 1) { echo " <a class='navlink' href='search.php?openthreads&currentpage=$nextpage'>»</a> "; }
			} 
			
			echo "</div>";				
			/* End building navigation */
}
else if(isset($_GET['plots']))
{
	echo "<div class='category'><a href=''>Plottråde</a></div>";
	
	/* Pages if number of topics > topics per page */
			
			$topicnumber = $forum->count_plot_topics($user_rank)->fetch_assoc();
			$totalpages = ceil($topicnumber['res'] / $topicsperpage);
			
			echo "<div class='pagenavigaton'>";			
			if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) { $currentpage = (int) $_GET['currentpage']; } 
			else { $currentpage = 1; } 
			if ($currentpage > $totalpages) { $currentpage = $totalpages; } 
			if ($currentpage < 1) { $currentpage = 1; } 
			
			$offset = ($currentpage - 1) * $topicsperpage;
			$topics = $forum->get_plot_topics($user_rank);		
			$range = 4; 
			
			if ($currentpage > 1) 
			{
				$prevpage = $currentpage - 1;
		 		echo " <a class='navlink' href='search.php?plots&currentpage=$prevpage'>«</a> ";	
				if ($currentpage - $range > 1) 
				{
					echo " <a class='navlink' href='search.php?plots&currentpage=1'>1</a> ";
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
					else { echo " <a class='navlink' href='search.php?plots&currentpage=$x'>$x</a> "; } 
		   		} 
			} 
			
			if ($currentpage != $totalpages) 
			{	
				$nextpage = $currentpage + 1;	
				if ($totalpages - $range > $currentpage) {	echo " <a class='navlink' href='search.php?plots&currentpage=$totalpages'>".$totalpages."</a> ";	}
				if ($totalpages > 1) { echo " <a class='navlink' href='search.php?plots&currentpage=$nextpage'>»</a> "; }
			} 
			
			echo "</div>";				
			/* End building navigation */
			
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
				if($t['topictype'] != "" ) { echo "<span class='topicwarning'>".$t['topictype']; if($t['warning'] != "" ) { echo " <span class='italic'>(Advarsel: ".$t['warning'].")</span>"; } echo "</span>"; }
				else if($t['warning'] != "" ) { echo "<span class='topicwarning'><span class='italic'>Advarsel: ".$t['warning']."</span></span>"; } 
				else { echo "<br/>"; }
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
			
			echo "<div class='pagenavigaton'>";			
			
			if ($currentpage > 1) 
			{
				$prevpage = $currentpage - 1;
		 		echo " <a class='navlink' href='search.php?plots&currentpage=$prevpage'>«</a> ";	
				if ($currentpage - $range > 1) 
				{
					echo " <a class='navlink' href='search.php?plots&currentpage=1'>1</a> ";
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
					else { echo " <a class='navlink' href='search.php?plots&currentpage=$x'>$x</a> "; } 
		   		} 
			} 
			
			if ($currentpage != $totalpages) 
			{	
				$nextpage = $currentpage + 1;	
				if ($totalpages - $range > $currentpage) {	echo " <a class='navlink' href='search.php?plots&currentpage=$totalpages'>".$totalpages."</a> ";	}
				if ($totalpages > 1) { echo " <a class='navlink' href='search.php?plots&currentpage=$nextpage'>»</a> "; }
			} 
			
			echo "</div>";				
			/* End building navigation */
}
else if(isset($_GET['search']))
{
	echo "<div class='category'><a href=''>Søgeresultat</a></div>";
	
	if($_GET['keyword'] != "")
	{
		$postnumber = $forum->count_search_posts($_GET['keyword'], $user_rank)->fetch_assoc();	
		$totalpages = ceil($postnumber['res'] / $postsperpage);
		
		echo "<div class='pagenavigaton'>";
	
		if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) {  $currentpage = (int) $_GET['currentpage']; }
		else { $currentpage = 1; } 
	
		if ($currentpage > $totalpages) { $currentpage = $totalpages; } 
		if ($currentpage < 1) { $currentpage = 1; } 
	
		$offset = ($currentpage - 1) * $postsperpage;
		$posts = $forum->search_posts($_GET['keyword'], $user_rank, $offset, $postsperpage);
	
		$range = 4;
	
		if ($currentpage > 1) 
		{ 
			$prevpage = $currentpage - 1;
			echo " <a class='navlink' href='search.php?keyword=".$_GET['keyword']."&search=Søg&currentpage=$prevpage'>«</a> ";
			if ($currentpage - $range > 1) 
			{
				echo " <a class='navlink' href='search.php?keyword=".$_GET['keyword']."&search=Søg&currentpage=1'>1</a> ";
			}
		} 
	
		for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) 
		{
		   if (($x > 0) && ($x <= $totalpages)) 
		   {
			  if ($x == $currentpage) { if($totalpages > 1) { echo " <span class='navlink_active'><b>$x</b></span>"; } } 
			  else { echo " <a class='navlink' href='search.php?keyword=".$_GET['keyword']."&search=Søg&currentpage=$x'>$x</a> "; } 
		   }  
		} 
	
		if ($currentpage != $totalpages) 
		{
		   $nextpage = $currentpage + 1;
		   if ($totalpages - $range > $currentpage) { echo " <a class='navlink' href='search.php?keyword=".$_GET['keyword']."&search=Søg&currentpage=$totalpages'>".$totalpages."</a> "; }
		   if ($totalpages > 1) { echo " <a class='navlink' href='search.php?keyword=".$_GET['keyword']."&search=Søg&currentpage=$nextpage'>»</a> "; }
		} 
	
		echo "</div>";
		/****** end build pagination links ******/
		
		
		
		while($p = $posts->fetch_assoc())
		{
			$currenttopic = $forum->get_topic($p['fk_topic_ID'])->fetch_assoc();
			$currentforum = $forum->get_forum($p['fk_forum_ID'])->fetch_assoc();
			
			echo "<div class='smallpost'>";
			echo "<div class='smallpostprofile'>";
			if ($p['ingame'] == 1)
			{
				if($p['fk_character_ID'] == 0) { echo "<span class='username' />Slettet karakter</span><br/>"; }
				else
				{
					$user = $forum->get_character($p['fk_character_ID'])->fetch_assoc();
					if ($user['avatar'] != "")
					{
						echo "<img src='".$user['avatar']."' />";	
					}
					echo "<a class='username' style='color:".$user['color'].";' href='characterprofile.php?id=".$user['character_ID']."'>".$user['name']." </a><br/>";
				}
			}
			else
			{
				$member = $forum->get_superuser($p['fk_superuser_ID'])->fetch_assoc();
				if ($member['avatar'] != "")
				{
					echo "<img src='".$member['avatar']."' />";	
				}
				echo "<a class='username' style='color:".$member['color'].";'  href='memberprofile.php?id=".$member['superuser_ID']."'>".$member['name']." </a><br/>";	
			}			
			echo date("d.m.Y G:i", strtotime($p['datetime']));
			echo "<br/><br/>";
			
			echo "<b>Forum:</b> <a href='viewforum.php?f=".$currentforum['forum_ID']."'>".$currentforum['title']."</a><br/>";
			echo "<b>Emne:</b> <a href='viewtopic.php?t=".$currenttopic['topic_ID']."'>".$currenttopic['title']."</a><br/>";
			$numofposts = $forum->get_numberof_posts($currenttopic['topic_ID'])->fetch_assoc();
			$numofanswers = ($numofposts['res'])-1;
			echo "<b>Svar:</b>  ".$numofanswers."<br/>";
			echo "<b>Visninger:</b>  ".$currenttopic['views']."<br/>";
			
			echo "</div>";
			echo "<div class='smallpostcontent'>";
			
			$numberofprevposts = $forum->count_prev_posts($currenttopic['topic_ID'], $p['datetime'])->fetch_assoc();
			$numberofprevposts = $numberofprevposts['res'] + 1;
			$topicpagenumber = ceil($numberofprevposts / $postsperpage);

			echo "<a class='smallposttitle' href='viewtopic.php?t=".$currenttopic['topic_ID']."&currentpage=".$topicpagenumber."#".$p['post_ID']."'>".$currenttopic['title']."</a>";
			
			if (strlen($p['text']) > 300) 
			{
				$stringCut = substr($p['text'], 0, 300);
				$posttext = substr($stringCut, 0, strrpos($stringCut, ' ')).'...';
			}
			else { $posttext = $p['text']; }
			$posttext = nl2br($parser->parse($posttext)->getAsHtml());	
			echo parseURls($posttext);
			echo "</div></div>";
		}
		
		echo "<div class='pagenavigaton'>";
	
		if ($currentpage > 1) 
		{ 
			$prevpage = $currentpage - 1;
			echo " <a class='navlink' href='search.php?keyword=".$_GET['keyword']."&search=Søg&currentpage=$prevpage'>«</a> ";
			if ($currentpage - $range > 1) 
			{
				echo " <a class='navlink' href='search.php?keyword=".$_GET['keyword']."&search=Søg&currentpage=1'>1</a> ";
			}
		} 
	
		for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) 
		{
		   if (($x > 0) && ($x <= $totalpages)) 
		   {
			  if ($x == $currentpage) { if($totalpages > 1) { echo " <span class='navlink_active'><b>$x</b></span>"; } } 
			  else { echo " <a class='navlink' href='search.php?keyword=".$_GET['keyword']."&search=Søg&currentpage=$x'>$x</a> "; } 
		   }  
		} 
	
		if ($currentpage != $totalpages) 
		{
		   $nextpage = $currentpage + 1;
		   if ($totalpages - $range > $currentpage) { echo " <a class='navlink' href='search.php?keyword=".$_GET['keyword']."&search=Søg&currentpage=$totalpages'>".$totalpages."</a> "; }
		   if ($totalpages > 1) { echo " <a class='navlink' href='search.php?keyword=".$_GET['keyword']."&search=Søg&currentpage=$nextpage'>»</a> "; }
		} 
	
		echo "</div>";
		/****** end build pagination links ******/
	}
	
}
else
{	
	echo "<span class='forumerror'>Intet resultat</span>";
}

echo "</div>";
include('sidebar.php');
echo "</div>";


?>
<?php
include('footer.php');
?>