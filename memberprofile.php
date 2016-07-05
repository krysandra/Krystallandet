<?php

include('header.php');

if(isset($_GET['id']))
{
	$id = $_GET['id'];
	
	$userexists = $forum->check_for_existing_superuser($id)->fetch_assoc();
	if($userexists['res'] > 0)
	{
		$member = $forum->get_superuser($id)->fetch_assoc();
	
		echo "<div class='category'><a href=''>".$member['name']."</a></div>";
		$characters = $forum->get_characters_from_superuser_to_profile($id);
	
	
	
		echo "<div id='usercontent'>";	
	
			echo "<div id='userinformation'>";
	
				if($member['avatar'] == "") { echo "<img src='images/noavatar.png'/>"; }
				else { echo "<img src='".$member['avatar']."'/>";  }
				echo "<br/>";
				
				if($user_rank > 0) { echo "<a href='messages.php?mode=new&receiver=".$id."'>[Send privat besked]</a>"; }
				if($user_rank == 3) { echo " <a href='acp.php?username=".$member['name']."&edituser=Udfør'>[Redigér]</a>"; }
				if($user_rank == 2) { echo " <a href='mcp.php?username=".$member['name']."&edituser=Udfør'>[Redigér]</a>"; }
				echo"<br/><br/>";
	
				$ingame_posts = $forum->count_ingame_posts_from_superuser($id)->fetch_assoc(); 
				echo "<span class='userprofiletabletext'>Antal posts (Ingame): </span>".$ingame_posts['res']."<br/>";
				$overall_posts = $forum->count_all_posts_from_superuser($id)->fetch_assoc(); 
				echo "<span class='userprofiletabletext'>Antal posts (I alt): </span>".$overall_posts['res']."<br/>";
	
				echo "<span class='userprofiletabletext'>Medlem siden: </span>".date("d.m.Y", strtotime($member['date_joined']))."<br/>";
				echo "<span class='userprofiletabletext'>Sidst aktiv: </span>".date("d.m.Y G:i", strtotime($member['last_active']))."<br/>";
				if($member['fk_role_ID'] > 1)
				{
					$role = $forum->get_role($member['fk_role_ID'])->fetch_assoc();
					echo "<span class='userprofiletabletext'>Rang: </span>".$role['name']."<br/><br/>";
				}
				else
				{
					$postrank = $forum->get_user_postrank($overall_posts['res'])->fetch_assoc();
					echo "<span class='userprofiletabletext'>Rang: </span>".$postrank['title']."<br/><br/>";
				}
				
				$mostactivechar = $forum->get_most_active_character_from_user($id)->fetch_assoc();
				if($mostactivechar['NumberOfPosts'] > 0) // We only display information about characters, if the member has been active with any characters
				{
					echo "<span class='userprofiletabletext'>Første karakter: </span>";
					$firstchar = $forum->get_first_character_from_user($id)->fetch_assoc();
					echo "<a class='username' style='color:".$firstchar['color'].";' href='characterprofile.php?id=".$firstchar['character_ID']."'>".$firstchar['name']."</a><br/>";
					echo "<span class='userprofiletabletext'>Nyeste karakter: </span>";
					$newestchar = $forum->get_newest_character_from_user($id)->fetch_assoc();
					echo "<a class='username' style='color:".$newestchar['color'].";' href='characterprofile.php?id=".$newestchar['character_ID']."'>".$newestchar['name']."</a><br/>";
					echo "<span class='userprofiletabletext'>Mest aktive karakter: </span>";
					echo "<a class='username' style='color:".$mostactivechar['color'].";' href='characterprofile.php?id=".$mostactivechar['character_ID']."'>".$mostactivechar['name']."</a> 
					(".$mostactivechar['NumberOfPosts'];
					if($mostactivechar['NumberOfPosts'] > 1) { echo " posts)"; } else { echo " post)"; }
					echo "<br/>";
					$activechars = $forum->count_active_characters_from_superuser($id)->fetch_assoc();
					$allchars = $forum->count_accepted_characters_from_superuser($id)->fetch_assoc();;
					echo "<span class='userprofiletabletext'>Antal karakterer (Aktive): </span>".$activechars['res']."<br/>";
					echo "<span class='userprofiletabletext'>Antal karakterer (I alt): </span>".$allchars['res']."<br/>";
					
				}
				
				if($user_rank > 0) //only showing this data to members of the site 
				{
					echo "<span class='userprofiletableheader'>Brugeroplysninger: </span>";
					$age = "";
					
					if($member['birthday'] != "0000-00-00")
					{
						$date = new DateTime($member['birthday']);
						$now = new DateTime();

						$interval = $now->diff($date);
						$age = $interval->y;
					}
					
					
					echo "<span class='userprofiletabletext'>Alder: </span>".$age."<br/>";
					echo "<span class='userprofiletabletext'>Geografisk sted: </span>".$member['geography']."<br/>";
					if($member['website'] != "" ) { echo "<span class='userprofiletabletext'>Hjemmeside: </span>".$member['website']."<br/>"; }
					if($member['facebook'] != "" ) {echo "<span class='userprofiletabletext'>Facebook: </span>".$member['facebook']."<br/>"; }
					echo "<span class='userprofiletabletext'>Skype: </span>".$member['skype']."<br/>";
					if($member['reference'] != "" ) { echo "<span class='userprofiletabletext'>Hvordan fandt du siden? </span>".$member['reference']."<br/>"; }
				}
	
				if($overall_posts['res'] > 0)
				{
					echo "<span class='userprofiletableheader'>Posting-statistik: </span>";
					$activetopic = $forum->get_most_active_topic_from_user($id)->fetch_assoc();
					$activeforum = $forum->get_most_active_forum_from_user($id)->fetch_assoc();
					echo "<a href='memberprofile.php?showposts=".$id."'>[Se brugerens posts]</a> ";
					echo "<a href='memberprofile.php?showtopics=".$id."'>[Se brugerens emner]</a><br/>";
					echo "<span class='userprofiletabletext'>Mest aktive forum: </span><a href='viewforum.php?f=".$activeforum['forum_ID']."'>".$activeforum['title']."</a><br/>";
					echo "<span class='userprofiletabletext'>Mest aktive emne: </span><a href='viewtopic.php?t=".$activetopic['topic_ID']."'>".$activetopic['title']."</a><br/>";
				}
	
			echo "</div>";
				
	
			echo "<div id='characterlist'>";
				while($char = $characters->fetch_assoc())	
				{
						if($char['active'] == 1)	
						{
							echo "<div class='character_active'>";
						}
						else
						{
							echo "<div class='character_inactive'>";
						}
					
					$profiledata = $forum->get_character_profiledata($char['character_ID'])->fetch_assoc();	
					$race = $forum->get_race($profiledata['fk_race_ID'])->fetch_assoc();
					
					if($char['avatar'] == "") { echo "<div class='charimgoverlay' style='background-image:url(images/noavatar.png);'></div>"; } else { echo "<div class='charimgoverlay' style='background-image:url(".$char['avatar'].");'></div>"; }
					echo "<a href='characterprofile.php?id=".$char['character_ID']."'>".$char['name']."</a>
					".$profiledata['alignment']." &#9679; ".$race['name']."
					</div>";	
	
				}
	
			echo "</div>";
		echo "</div>";
		
		echo "<div class='category'><a href=''>Personlig profil</a></div>";
		echo "<div id='profiletext'>";
		if ($member['profiletext'] == "") { echo "<p class='center italic'>Ingen profiltekst</p>"; }
		$parser->parse($member['profiletext']);
		$profiletext = nl2br($parser->getAsHtml());
		echo parseURls($profiletext);
		echo "</div>";
		
		echo "<div class='category'><a href=''>Trofæer</a></div>";
		
		$achievementnumber = $forum->count_all_userachievements_from_user($id)->fetch_assoc();
		if($achievementnumber['res'] < 1)
		{
			echo "<div class='achievements'>";
			echo "<p class='italic'>Brugeren har endnu ikke opnået nogen trofæer</p>";
			echo "</div>";
		}
		else
		{
			$offgameachieves = $forum->count_userachievements_specific_type($id, 1)->fetch_assoc();
			$plotachieves = $forum->count_userachievements_specific_type($id, 2)->fetch_assoc();
			$placeachieves = $forum->count_userachievements_specific_type($id, 3)->fetch_assoc();
			$groupachieves = $forum->count_userachievements_specific_type($id, 4)->fetch_assoc();
			
			if($offgameachieves['res'] > 0)
			{
				$achievementlist = $forum->get_userachievements_specific_type($id, 1);
				echo "<div class='achievements'>";
				echo "<h5>Offgame trofæer</h5>";
				while($achi = $achievementlist->fetch_assoc())
				{
					echo "<div class='achievementinfo'>";
					echo "<img src='images/achievements/".$achi['img']."'/>";	
					echo "<h6>".$achi['title']."</h6>";
					echo "<span class='italic'>".$achi['description']."</span><br/>";
					echo "</div>";
				}
				echo "</div>";
			}
			
			if($plotachieves['res'] > 0)
			{
				$achievementlist = $forum->get_userachievements_specific_type($id, 2);
				echo "<div class='achievements'>";
				echo "<h5>Trofæer for at deltage i plots</h5>";
				while($achi = $achievementlist->fetch_assoc())
				{
					echo "<div class='achievementinfo'>";
					echo "<img src='images/achievements/".$achi['img']."'/>";	
					echo "<h6>".$achi['title']."</h6>";
					echo "<span class='italic'>".$achi['description']."</span><br/>";
					$characternumber = $forum->count_userachievements_from_achievement($id, $achi['achievement_ID'])->fetch_assoc();
					$characterlist = $forum->get_userachievements_from_achievement($id, $achi['achievement_ID']);
					echo "Karakter(er): "; 
					$count = 1;
					while($char = $characterlist->fetch_assoc()) 
					{ 
						echo $char['name'];
						if ($count < $characternumber['res']) { echo ", "; }
						$count++; 
					}
					echo "</div>";
				}
				echo "</div>";
				
				
			}
			
			if($placeachieves['res'] > 0)
			{
				$achievementlist = $forum->get_userachievements_specific_type($id, 3);
				echo "<div class='achievements'>";
				echo "<h5>Trofæer for at besøge forskellige steder i Krystallandet</h5>";
				while($achi = $achievementlist->fetch_assoc())
				{
					echo "<div class='achievementinfo'>";
					echo "<img src='images/achievements/".$achi['img']."'/>";	
					echo "<h6>".$achi['title']."</h6>";
					echo "<span class='italic'>".$achi['description']."</span><br/>";
					$characternumber = $forum->count_userachievements_from_achievement($id, $achi['achievement_ID'])->fetch_assoc();
					$characterlist = $forum->get_userachievements_from_achievement($id, $achi['achievement_ID']);
					echo "Karakter(er): "; 
					$count = 1;
					while($char = $characterlist->fetch_assoc()) 
					{ 
						echo $char['name'];
						if ($count < $characternumber['res']) { echo ", "; }
						$count++; 
					}
					echo "</div>";
				}
				echo "</div>";
			}
			
			if($groupachieves['res'] > 0)
			{
				$achievementlist = $forum->get_userachievements_specific_type($id, 4);
				echo "<div class='achievements'>";
				echo "<h5>Trofæer for at være en del af forskellige ingame grupper</h5>";
				while($achi = $achievementlist->fetch_assoc())
				{
					echo "<div class='achievementinfo'>";
					echo "<img src='images/achievements/".$achi['img']."'/>";	
					echo "<h6>".$achi['title']."</h6>";
					echo "<span class='italic'>".$achi['description']."</span><br/>";
					$characternumber = $forum->count_userachievements_from_achievement($id, $achi['achievement_ID'])->fetch_assoc();
					$characterlist = $forum->get_userachievements_from_achievement($id, $achi['achievement_ID']);
					echo "Karakter(er): "; 
					$count = 1;
					while($char = $characterlist->fetch_assoc()) 
					{ 
						echo $char['name'];
						if ($count < $characternumber['res']) { echo ", "; }
						$count++; 
					}
					echo "</div>";
				}
				echo "</div>";
			}
		
		} // End if achievements are found
	}
	else
	{	
		echo "Ingen bruger fundet";	
	}

}

if(isset($_GET['showposts']))
{
	$id = $_GET['showposts'];
	
	$userexists = $forum->check_for_existing_superuser($id)->fetch_assoc();
	if($userexists['res'] > 0)
	{
		$member = $forum->get_superuser($id)->fetch_assoc();
		$postnumber = $forum->get_number_of_posts_to_show_from_user($id, $user_rank)->fetch_assoc();
		$totalpages = ceil($postnumber['res'] / $postsperpage);		
		
		echo "<div class='category'><a href='memberprofile.php?id=".$id."'>Viser ".$postnumber['res']." posts fra brugeren ".$member['name']."</a></div>";
		
		echo "<div class='pagenavigaton'>";
		
		// get the current page or set a default
		if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) {
		   // cast var as int
		   $currentpage = (int) $_GET['currentpage'];
	
		} else {
		   // default page num
		   $currentpage = 1;
		} // end if

		// if current page is greater than total pages...
	
		if ($currentpage > $totalpages) {
		   // set current page to last page
		   $currentpage = $totalpages;
		} // end if
		// if current page is less than first page...
		if ($currentpage < 1) {
		   // set current page to first page
		   $currentpage = 1;
		} // end if
	
			
		$offset = ($currentpage - 1) * $postsperpage;

		$posts = $forum->get_posts_from_user($id, $offset, $postsperpage, $user_rank);
		
		// range of num links to show
		$range = 4;
		// if not on page 1, don't show back links
	
		if ($currentpage > 1) {
			
		// get previous page num
		$prevpage = $currentpage - 1;
		// show < link to go back to 1 page
		 echo " <a class='navlink' href='memberprofile.php?showposts=".$id."&currentpage=$prevpage'>«</a> ";	
			
		if ($currentpage - $range > 1) {
		// show << link to go back to page 1
		echo " <a class='navlink' href='memberprofile.php?showposts=".$id."&currentpage=1'>1</a> ";
		}
		
		} // end if

		
	
		// loop to show links to range of pages around current page
		for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) {
		   // if it's a valid page number...
		   if (($x > 0) && ($x <= $totalpages)) {	
			  // if we're on current page...	
			  if ($x == $currentpage) {
				 // 'highlight' it but don't make a link	
				 if($totalpages > 1) { echo " <span class='navlink_active'><b>$x</b></span>"; }
			  // if not current page...	
			  } else {
				 // make it a link
					 echo " <a class='navlink' href='memberprofile.php?showposts=".$id."&currentpage=$x'>$x</a> ";	
			  } // end else	
		   } // end if 
		} // end for
		// if not on last page, show forward and last page links        
	
		if ($currentpage != $totalpages) {	
		   // get next page	
		   $nextpage = $currentpage + 1;	
		   
		   if ($totalpages - $range > $currentpage) {		   
		   // echo forward link for lastpage	
		   echo " <a class='navlink' href='memberprofile.php?showposts=".$id."&currentpage=$totalpages'>".$totalpages."</a> ";	
		   }
		   
			// echo forward link for next page 	
		   if ($totalpages > 1) { echo " <a class='navlink' href='memberprofile.php?showposts=".$id."&currentpage=$nextpage'>»</a> "; }
		   
		} // end if
	
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
			
			$numberofprevposts = $forum->count_prev_posts($currenttopic['topic_ID'], $p['datetime'])->fetch_assoc();
			$numberofprevposts = $numberofprevposts['res'] + 1;
			$topicpagenumber = ceil($numberofprevposts / $postsperpage);
			
			echo "</div>";
			echo "<div class='smallpostcontent'>";
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
	
		if ($currentpage > 1) {
			
		// get previous page num
		$prevpage = $currentpage - 1;
		// show < link to go back to 1 page
		 echo " <a class='navlink' href='memberprofile.php?showposts=".$id."&currentpage=$prevpage'>«</a> ";	
			
		if ($currentpage - $range > 1) {
		// show << link to go back to page 1
		echo " <a class='navlink' href='memberprofile.php?showposts=".$id."&currentpage=1'>1</a> ";
		}
		
		} // end if

		
	
		// loop to show links to range of pages around current page
		for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) {
		   // if it's a valid page number...
		   if (($x > 0) && ($x <= $totalpages)) {	
			  // if we're on current page...	
			  if ($x == $currentpage) {
				 // 'highlight' it but don't make a link	
				 if($totalpages > 1) { echo " <span class='navlink_active'><b>$x</b></span>"; }
			  // if not current page...	
			  } else {
				 // make it a link
					 echo " <a class='navlink' href='memberprofile.php?showposts=".$id."&currentpage=$x'>$x</a> ";	
			  } // end else	
		   } // end if 
		} // end for
		// if not on last page, show forward and last page links        
	
		if ($currentpage != $totalpages) {	
		   // get next page	
		   $nextpage = $currentpage + 1;	
		   
		   if ($totalpages - $range > $currentpage) {		   
		   // echo forward link for lastpage	
		   echo " <a class='navlink' href='memberprofile.php?showposts=".$id."&currentpage=$totalpages'>".$totalpages."</a> ";	
		   }
		   
			// echo forward link for next page 	
		   if ($totalpages > 1) { echo " <a class='navlink' href='memberprofile.php?showposts=".$id."&currentpage=$nextpage'>»</a> "; }
		   
		} // end if
	
		echo "</div>";
		/****** end build pagination links ******/
	}
	
	else
	{	
		echo "Ingen bruger fundet";
	
	}
}

if(isset($_GET['showtopics']))
{
	$id = $_GET['showtopics'];
	
	$userexists = $forum->check_for_existing_superuser($id)->fetch_assoc();
	if($userexists['res'] > 0)
	{
		$member = $forum->get_superuser($id)->fetch_assoc();
		$topicnumber = $forum->get_number_of_topics_to_show_from_user($id, $user_rank)->fetch_assoc();
		$totalpages = ceil($topicnumber['res'] / $topicsperpage);		
		
		echo "<div class='category'><a href='memberprofile.php?id=".$id."'>Viser ".$topicnumber['res']." emner fra brugeren ".$member['name']."</a></div>";
		
		echo "<div class='pagenavigaton'>";
		
		// get the current page or set a default
		if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) {
		   // cast var as int
		   $currentpage = (int) $_GET['currentpage'];
	
		} else {
		   // default page num
		   $currentpage = 1;
		} // end if

		// if current page is greater than total pages...
	
		if ($currentpage > $totalpages) {
		   // set current page to last page
		   $currentpage = $totalpages;
		} // end if
		// if current page is less than first page...
		if ($currentpage < 1) {
		   // set current page to first page
		   $currentpage = 1;
		} // end if
	
			
		$offset = ($currentpage - 1) * $topicsperpage;

		$topics = $forum->get_topics_to_show_from_user($id, $offset, $topicsperpage, $user_rank);
		
	
		// range of num links to show
		$range = 4;
		// if not on page 1, don't show back links
	
		if ($currentpage > 1) {
			
		// get previous page num
		$prevpage = $currentpage - 1;
		// show < link to go back to 1 page
		 echo " <a class='navlink' href='memberprofile.php?showtopics=".$id."&currentpage=$prevpage'>«</a> ";	
			
		if ($currentpage - $range > 1) {
		// show << link to go back to page 1
		echo " <a class='navlink' href='memberprofile.php?showtopics=".$id."&currentpage=1'>1</a> ";
		}

		
		} // end if

		
	
		// loop to show links to range of pages around current page
		for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) {
		   // if it's a valid page number...
		   if (($x > 0) && ($x <= $totalpages)) {	
			  // if we're on current page...	
			  if ($x == $currentpage) {
				 // 'highlight' it but don't make a link	
				 if($totalpages > 1) { echo " <span class='navlink_active'><b>$x</b></span>"; }
			  // if not current page...	
			  } else {
				 // make it a link
					 echo " <a class='navlink' href='memberprofile.php?showtopics=".$id."&currentpage=$x'>$x</a> ";	
			  } // end else	
		   } // end if 
		} // end for
		// if not on last page, show forward and last page links        
	
		if ($currentpage != $totalpages) {	
		   // get next page	
		   $nextpage = $currentpage + 1;	
		   
		   if ($totalpages - $range > $currentpage) {		   
		   // echo forward link for lastpage	
		   echo " <a class='navlink' href='memberprofile.php?showtopics=".$id."&currentpage=$totalpages'>".$totalpages."</a> ";	
		   }
		   
			// echo forward link for next page 	
		   echo " <a class='navlink' href='memberprofile.php?showtopics=".$id."&currentpage=$nextpage'>»</a> ";	
		   
		} // end if
	
		echo "</div>";
		/****** end build pagination links ******/
	
		echo "<table id='topiclist'>";
		echo"<tr><th>Titel</th><th>Svar</th><th>Visninger</th><th>Seneste indlæg</th></tr>";
		while($t = $topics->fetch_assoc())
		{
			echo "<tr><td>";
			if($t['pinned'] == 1) { echo "<img class='topicimage hovereffect' src='images/topic_pinned.png'/>"; }
			else if($t['locked'] == 1) { echo "<img class='topicimage hovereffect' src='images/topic_locked.png'/>"; }
			else { echo "<img class='topicimage hovereffect' src='images/topic_normal.png'/>"; }
			
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
					echo "Af <a class='username' style='color:".$user['color'].";'  href='memberprofile.php?id=".$user['superuser_ID']."'>".$user['name']."</a> ";
				}
			}	
			echo "<a href='viewtopic.php?t=".$t['topic_ID']."&currentpage=".$pagenumber."#".$lastpost['post_ID']."'><img src='images/icon_topic_latest.gif' title='Gå til post'/></a>";	
			echo "&raquo; ".date("d.m.Y G:i", strtotime($t['datetime']));
			if($t['topictype'] != "" ) { echo "<span class='topicwarning'>".$t['topictype']; if($t['warning'] != "" ) { echo " <span class='italic'>(Advarsel: ".$t['warning'].")</span>"; } echo "</span>"; }
			else if($t['warning'] != "" ) { echo "<span class='topicwarning'><span class='italic'>Advarsel: ".$t['warning']."</span></span>"; }  
			else { echo "<br/>"; }
			echo "i <a class='topictitle' href='viewforum.php?f=".$t['forum_ID']."'>".$t['forumtitle']."</a>";
			echo "</td>";
			
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
				$user = $forum->get_superuser($lastpost['fk_superuser_ID'])->fetch_assoc();
				echo "af <a class='username' style='color:".$user['color'].";' href='memberprofile.php?id=".$user['superuser_ID']."'>".$user['name']."</a> ";
			}	
			echo "<a href='viewtopic.php?t=".$t['topic_ID']."&currentpage=".$pagenumber."#".$lastpost['post_ID']."'><img src='images/icon_topic_latest.gif' title='Gå til post'/></a>";
			echo "</br>";	
			echo date("d.m.Y G:i", strtotime($lastpost['datetime']))."</td>";
			echo "</tr>";
		}
		echo "</table>";
		
		echo "<div class='pagenavigaton'>";
		if ($currentpage > 1) {
			
		// get previous page num
		$prevpage = $currentpage - 1;
		// show < link to go back to 1 page
		 echo " <a class='navlink' href='memberprofile.php?showtopics=".$id."&currentpage=$prevpage'>«</a> ";	
			
		if ($currentpage - $range > 1) {
		// show << link to go back to page 1
		echo " <a class='navlink' href='memberprofile.php?showtopics=".$id."&currentpage=1'>1</a> ";
		}
		
		} // end if

		
	
		// loop to show links to range of pages around current page
		for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) {
		   // if it's a valid page number...
		   if (($x > 0) && ($x <= $totalpages)) {	
			  // if we're on current page...	
			  if ($x == $currentpage) {
				 // 'highlight' it but don't make a link	
				 if($totalpages > 1) { echo " <span class='navlink_active'><b>$x</b></span>"; }
			  // if not current page...	
			  } else {
				 // make it a link
					 echo " <a class='navlink' href='memberprofile.php?showtopics=".$id."&currentpage=$x'>$x</a> ";	
			  } // end else	
		   } // end if 
		} // end for
		// if not on last page, show forward and last page links        
	
		if ($currentpage != $totalpages) {	
		   // get next page	
		   $nextpage = $currentpage + 1;	
		   
		   if ($totalpages - $range > $currentpage) {		   
		   // echo forward link for lastpage	
		   echo " <a class='navlink' href='memberprofile.php?showtopics=".$id."&currentpage=$totalpages'>".$totalpages."</a> ";	
		   }
		   
			// echo forward link for next page 	
		   echo " <a class='navlink' href='memberprofile.php?showtopics=".$id."&currentpage=$nextpage'>»</a> ";	
		   
		} // end if
	
		echo "</div>";
		/****** end build pagination links ******/
	}
	
	else
	{	
		echo "Ingen bruger fundet";
	
	}
}
$pagetitle = $member['name']." - ";
include('footer.php');

?>