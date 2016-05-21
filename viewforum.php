<?php
include('header.php');
?>
<?php
echo "<div id='forumcontainer'>";
echo "<div id='forumlist'>";
if(isset($_GET['f']))
{
	$allforumposts = array();
	$forumpostcount = $forum->count_all_forum_posts();
	while($forumposts = $forumpostcount->fetch_assoc()){ $allforumposts[$forumposts['fk_forum_ID']] = $forumposts['numberOfPosts']; }
	$allforumtopics = array();
	$forumtopiccount = $forum->count_all_forum_topics();
	while($forumtopics = $forumtopiccount->fetch_assoc()){ $allforumtopics[$forumtopics['fk_forum_ID']] = $forumtopics['numberOfTopics']; }
	$f = $_GET['f'];
	$viewforum = $forum->get_forum($f)->fetch_assoc();
	echo "<div class='category'><a href='viewforum.php?f=".$viewforum['forum_ID']."'>".$viewforum['title']."</a></div>";	
	
	if($viewforum['read_access'] <= $user_rank)
	{
		$parents = array();
		$parentf = $viewforum['parent_ID'];
		
		while($parentf != 0)
		{
			$parentdata = $forum-> get_forum($parentf)->fetch_assoc();
			array_push($parents, $parentdata);			
			$parentf = $parentdata['parent_ID'];
		}
		$parents = array_reverse($parents);
		echo "<div id='forumnavigation'>";
		echo "<a href='index.php' class='forumnavlink'>Index</a>";
		foreach ($parents as $pval)
		{
			echo " &laquo; <a href='viewforum.php?f=".$pval['forum_ID']."' class='forumnavlink'>".$pval['title']."</a>";		
		}
		
		echo " &laquo; <a href='viewforum.php?f=".$viewforum['forum_ID']."' class='forumnavlink'>".$viewforum['title']."</a>";
		
		echo "</div>"; //End navigation div 
		echo "<div class='subforumcontainer'>";
		
		$subforums = $forum->count_subforums($f)->fetch_assoc();
		$max = $subforums['res'];
		$next = 0;
		for($i = 0; $i < $max; $i++)
		{
			$currentforum = $forum->get_subforum($next, $f)->fetch_assoc();
			
			if ($user_rank == 3 && $currentforum['read_access'] >= 1 || $user_rank == 2 && $currentforum['read_access'] <= 2 
			|| $user_rank == 1 && $currentforum['read_access'] <= 1 || $currentforum['read_access'] == 0 )
			{	
				$stack = array();
				array_push($stack, $currentforum['forum_ID']);
				$numberofposts = 0;
				$numberoftopics = 0;
				
				while(!empty($stack))
				{
					$currentforumid = array_pop($stack);
					//$forumposts = $forum->count_forum_topics($currentforumid)->fetch_assoc();
					$numberofposts += $allforumposts[$currentforumid];
					$numberoftopics += $allforumtopics[$currentforumid];
					
					$subforums = $forum->get_all_subforums($currentforumid);
					while($subforum = $subforums->fetch_assoc())	
					{
						array_push($stack, $subforum['forum_ID']);
					}
				}
				
				$numberofforummods = $forum->count_forummods($currentforum['forum_ID'])->fetch_assoc();
				$forummods = $forum->get_forummods($currentforum['forum_ID']);
						
				echo "<div class='subforum'>
				<a href='viewforum.php?f=".$currentforum['forum_ID']."'>";
				if($currentforum['picture'] == "") { echo "<img class='hovereffect' src='images/forumimages/default.png'/>"; }
				else { echo "<img class='hovereffect' src='images/forumimages/".$currentforum['picture']."'/>"; }					
				echo "<h5 class='center'>".$currentforum['title']."</h5></a>
				".$currentforum['description']."
				<br/>
				<div class='subforuminfo'>";
				if($numberofforummods['res'] > 0) { echo "Redaktør: ";}
				while($mod = $forummods->fetch_assoc())
				{
					$forummod = $forum->get_superuser($mod['fk_superuser_ID'])->fetch_assoc();
					echo "<a class='username' href='memberprofile.php?id=".$forummod['superuser_ID']."'>".$forummod['name']."</a> ";	
				}
				echo "<span class='rightcorner'>emner: " . $numberoftopics . " posts: " . $numberofposts . " </span>
				</div>
				</div>";
				
			}
			$next = $currentforum['forum_ID'];
		}
		
		echo "</div>"; //End subforums
		
		if ($viewforum['writeable'] == 1)
		{
			echo "<div class='category'><a href='viewforum.php?f=".$viewforum['forum_ID']."'>Emner</a></div>";
			
			
			/* Pages if number of topics > topics per page */
			
			$topicnumber = $forum->count_topics($f)->fetch_assoc();
			$totalpages = ceil($topicnumber['res'] / $topicsperpage);
			
			echo "<div class='pagenavigaton'>";			
			if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) { $currentpage = (int) $_GET['currentpage']; } 
			else { $currentpage = 1; } 
			if ($currentpage > $totalpages) { $currentpage = $totalpages; } 
			if ($currentpage < 1) { $currentpage = 1; } 
			
			$offset = ($currentpage - 1) * $topicsperpage;
			$topics = $forum->get_topics($f, $offset, $topicsperpage);			
			$range = 4; 
			
			if ($currentpage > 1) 
			{
				$prevpage = $currentpage - 1;
		 		echo " <a class='navlink' href='viewforum.php?f=".$f."&currentpage=$prevpage'>«</a> ";	
				if ($currentpage - $range > 1) 
				{
					echo " <a class='navlink' href='viewforum.php?f=".$f."&currentpage=1'>1</a> ";
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
					else { echo " <a class='navlink' href='viewforum.php?f=".$f."&currentpage=$x'>$x</a> "; } 
		   		} 
			} 
			
			if ($currentpage != $totalpages) 
			{	
				$nextpage = $currentpage + 1;	
				if ($totalpages - $range > $currentpage) {	echo " <a class='navlink' href='viewforum.php?f=".$f."&currentpage=$totalpages'>".$totalpages."</a> ";	}
				if ($totalpages > 1) { echo " <a class='navlink' href='viewforum.php?f=".$f."&currentpage=$nextpage'>»</a> "; }
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
						echo "Af <a class='username' href='memberprofile.php?id=".$user['superuser_ID']."'>".$user['name']."</a> ";
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
						echo "af <a class='username' href='memberprofile.php?id=".$user['superuser_ID']."'>".$user['name']."</a> ";
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
		 		echo " <a class='navlink' href='viewforum.php?f=".$f."&currentpage=$prevpage'>«</a> ";	
				if ($currentpage - $range > 1) 
				{
					echo " <a class='navlink' href='viewforum.php?f=".$f."&currentpage=1'>1</a> ";
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
					else { echo " <a class='navlink' href='viewforum.php?f=".$f."&currentpage=$x'>$x</a> "; } 
		   		} 
			} 
			
			if ($currentpage != $totalpages) 
			{	
				$nextpage = $currentpage + 1;	
				if ($totalpages - $range > $currentpage) {	echo " <a class='navlink' href='viewforum.php?f=".$f."&currentpage=$totalpages'>".$totalpages."</a> ";	}
				if ($totalpages > 1) { echo " <a class='navlink' href='viewforum.php?f=".$f."&currentpage=$nextpage'>»</a> "; }
			} 
			
			echo "</div>";				
			/* End building navigation */
			
			if ($user_rank >= $viewforum['write_access'])	
			{
				echo "<a href='posting.php?f=".$f."' class='forumbutton'>Nyt Emne</a>";		
			}
		}
	} //end if has read access
} //end if f is found
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