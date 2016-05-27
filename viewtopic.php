<?php
include('header.php');
?>
<?php
if(isset($_GET['t']))
{	
	$topic_id = $_GET['t'];
	$viewtopic = $forum->get_topic($topic_id)->fetch_assoc();
	$forum_id = $viewtopic['fk_forum_ID'];
	$viewforum = $forum->get_forum($forum_id)->fetch_assoc();
	$forummod = $forum->forummod_exists($forum_id, $user_logged_in_ID)->fetch_assoc();
	
	if($viewforum['read_access'] <= $user_rank)
	{
	
		$topicviews = $viewtopic['views'] + 1;
		$updateviewcount = $forum->update_topic_viewcount($topicviews, $topic_id);
		
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
	
		$subforums = $forum->count_subforums($f)->fetch_assoc();
	
		
		echo "<div class='category'><a href='viewtopic.php?t=".$viewtopic['topic_ID']."'>".$viewtopic['title']."</a></div>";
			
		$numofposts = $forum->get_numberof_posts($topic_id)->fetch_assoc();	
		$postnumber = $forum->get_numberof_posts($topic_id)->fetch_assoc();	
		$totalpages = ceil($postnumber['res'] / $postsperpage);
	
		echo "<div class='pagenavigaton'>";
	
		if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) {  $currentpage = (int) $_GET['currentpage']; }
		else { $currentpage = 1; } 
	
		if ($currentpage > $totalpages) { $currentpage = $totalpages; } 
		if ($currentpage < 1) { $currentpage = 1; } 
	
		$offset = ($currentpage - 1) * $postsperpage;
		$posts = $forum->get_posts($topic_id, $offset, $postsperpage);
	
		$range = 4;
	
		if ($currentpage > 1) 
		{ 
			$prevpage = $currentpage - 1;
			echo " <a class='navlink' href='viewtopic.php?t=".$topic_id."&currentpage=$prevpage'>«</a> ";
			if ($currentpage - $range > 1) 
			{
				echo " <a class='navlink' href='viewtopic.php?t=".$topic_id."&currentpage=1'>1</a> ";
			}
		} 
	
		for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) 
		{
		   if (($x > 0) && ($x <= $totalpages)) 
		   {
			  if ($x == $currentpage) { if($totalpages > 1) { echo " <span class='navlink_active'><b>$x</b></span>"; } } 
			  else { echo " <a class='navlink' href='viewtopic.php?t=".$topic_id."&currentpage=$x'>$x</a> "; } 
		   }  
		} 
	
		if ($currentpage != $totalpages) 
		{
		   $nextpage = $currentpage + 1;
		   if ($totalpages - $range > $currentpage) { echo " <a class='navlink' href='viewtopic.php?t=".$topic_id."&currentpage=$totalpages'>".$totalpages."</a> "; }
		   if ($totalpages > 1) { echo " <a class='navlink' href='viewtopic.php?t=".$topic_id."&currentpage=$nextpage'>»</a> "; }
		} 
	
		echo "</div>";
		/****** end build pagination links ******/
		
		$topic_has_poll = $forum->check_if_topic_has_poll($topic_id)->fetch_assoc();
		if($topic_has_poll['res'] > 0)
		{
			$topicpoll = $forum->get_poll_from_topic($topic_id)->fetch_assoc();
			
			echo "<div id='poll'>";
			echo "<h3>".$topicpoll['question']."</h3>";
			echo "<hr/>";
			echo "<form method='post'>";
			echo "<table>";
			$polloptions = $forum->get_poll_options($topicpoll['poll_ID']);
			$highestvote = $forum->get_highest_poll_vote($topicpoll['poll_ID'])->fetch_assoc();
			$allvotes = $forum->count_all_poll_votes($topicpoll['poll_ID'])->fetch_assoc();
			
			if($highestvote['numberOfVotes'] == "") { $maxvote = 0; }
			else { $maxvote = $highestvote['numberOfVotes']; }
			
			while($option = $polloptions->fetch_assoc())
			{
				$votes = $forum->get_polloptions_votes($option['option_ID'])->fetch_assoc();
				if($votes['numberOfVotes'] == "") { $optionvotes = 0; }
				else { $optionvotes = $votes['numberOfVotes']; }
				
				$voteshow = ($optionvotes / $maxvote) * 100;
				$voteper = ($optionvotes /$allvotes['numberOfVotes']) * 100;
				
				echo "<tr><td class='bold'>".$option['option']." </td>";
				if($user_logged_in_ID > 0)
				{
					$uservote = $forum->check_polloption_uservote($user_logged_in_ID, $option['option_ID'])->fetch_assoc(); 
					if($topicpoll['multiple_options'] == 1) 
					{
						if($uservote['res'] > 0 ) { echo "<td><input type='checkbox' name='pollvotes[]' value='".$option['option_ID']."' checked/></td>"; }
						else { echo "<td><input type='checkbox' name='pollvotes[]' value='".$option['option_ID']."'/></td>"; }
					}
					else 
					{ 
						if($uservote['res'] > 0 ) { echo "<td><input type='radio' name='pollvote' value='".$option['option_ID']."' checked/></td>"; }
						else { echo "<td><input type='radio' name='pollvote' value='".$option['option_ID']."'/></td>"; }
					}				
				}
				echo "<td><div id='pollvote".$option['option_ID']."' class='pollvote'><span class='pollvotetext'>".$optionvotes."</span></div> 
				<span class='pollvotescore'>".round($voteper)."%</span></td>
				</tr>";
				
				echo "
				<script>
				  $(function() {
					$( '#pollvote".$option['option_ID']."' ).progressbar({
					  value: ".$voteshow.",
					});
				  });
				  </script>
				";
			}
			echo "</table>";
			if($user_logged_in_ID > 0)
			{
				echo "<input type='submit' value='Stem' name='submit_pollvote'/>";
			}
			echo "</form>";
			echo "</div>";	
			
			if($_POST['submit_pollvote'])
			{
				if($_POST['pollvote']) 
				{ 
					$vote = $_POST['pollvote'];
					$uservote = $forum->check_polloption_uservote($user_logged_in_ID, $vote)->fetch_assoc(); 
					if($uservote['res'] < 1)
					{
						$forum->add_uservote($user_logged_in_ID, $vote);
					}
					$polloptions = $forum->get_poll_options($topicpoll['poll_ID']);
					while($option = $polloptions->fetch_assoc())
					{
						if($option['option_ID'] != $vote)
						{
							$forum->delete_uservote($user_logged_in_ID, $option['option_ID']);
						}
					}
					
				}
				else if($_POST['pollvotes']) 
				{ 
					//If the user can add multiple votes we need to loop trough all the options to make sure, that we delete old votes, if the user changes his/her vote.
					$votedata = array();
					foreach($_POST['pollvotes'] as $vote) { array_push($votedata, $vote); }
					
					$polloptions = $forum->get_poll_options($topicpoll['poll_ID']);
					while($option = $polloptions->fetch_assoc())
					{
						$uservote = $forum->check_polloption_uservote($user_logged_in_ID, $option['option_ID'])->fetch_assoc(); 
						if($uservote['res'] < 1 && in_array($option['option_ID'], $votedata))
						{
							$forum->add_uservote($user_logged_in_ID, $option['option_ID']);
						}
						else if($uservote['res'] > 0 && !in_array($option['option_ID'], $votedata))
						{
							$forum->delete_uservote($user_logged_in_ID, $option['option_ID']);
						}
					}
					
				}
				
				header('Location:viewtopic.php?t='.$topic_id);
			}
		}
		
		$topicposters = array();
		$topicpostertime = array();
		$mychar = 0;	
	
		while($p = $posts->fetch_assoc())
		{
			if ($p['ingame'] == 1)
			{
				//used for quick reply options			
				$posterchar = $forum->get_character($p['fk_character_ID'])->fetch_assoc();
				if ($posterchar['fk_superuser_ID'] != $user_logged_in_ID && $posterchar['dead'] == 0 && $posterchar['accepted'] == 1)
				{
					if (!in_array($posterchar, $topicposters)) {
						$topicposters[$p['fk_character_ID']] = $posterchar;
					}
					$topicpostertime[$p['fk_character_ID']] = strtotime($p['datetime']);
				}
				else if ($posterchar['fk_superuser_ID'] == $user_logged_in_ID)
				{
					$mychar = $posterchar['character_ID'];
				}
			}
			
			echo "<div class='post' id='".$p['post_ID']."'>";
			echo "<div class='postprofile'>";
	
			if ($p['ingame'] == 1)
			{
				if($p['fk_character_ID'] == 0)
				{
					echo "<span class='username' />Slettet karakter</span><br/><br/>";
					if($p['fk_superuser_ID'] != 0)
					{
						$superuser = $forum->get_superuser($p['fk_superuser_ID'])->fetch_assoc();
						echo "Skaber: <a class='username smalltext' href='memberprofile.php?id=".$superuser['superuser_ID']."' style='color:".$superuser['color'].";'>".$superuser['name']."</a><br/><br/>";
					}
				}
				else
				{				
					$user = $forum->get_character($p['fk_character_ID'])->fetch_assoc();
					if ($user['avatar'] != "")
					{
						echo "<img src='".$user['avatar']."' />";	
					}
					echo "<a class='username smalltext' href='characterprofile.php?id=".$user['character_ID']."' style='color:".$user['color'].";'>".$user['name']."</a><br/>";
					
					$user_has_default_group = $forum->check_users_default_group($user['character_ID'])->fetch_assoc();
					if($user_has_default_group['res'] > 0)
					{
						$defaultgroup = $forum->get_users_default_group($user['character_ID'])->fetch_assoc(); 
						$grouprank = $forum->get_grouprank($defaultgroup['fk_rank_ID'])->fetch_assoc(); 
						echo $grouprank['title']."<br/><br/>";
					}
					else
					{
						echo "Krystalisianer<br/><br/>";
					}
					$charprofile = $forum->get_character_profiledata($user['character_ID'])->fetch_assoc();
					$race = $forum->get_race($charprofile['fk_race_ID'])->fetch_assoc();
					echo $charprofile['alignment']."<br/>";
					echo "<b>Race:</b> ".$race['name']."<br/>";
					echo "<b>Alder:</b> ".$charprofile['age']." år<br/><br/>";
					
					/*
					echo "<b>Fysisk styrke: </b>".$charprofile['skill_strength']."<br/>";
					echo "<b>Våbenfærdigheder: </b>".$charprofile['skill_weapons']."<br/>";
					echo "<b>Smidighed: </b>".$charprofile['skill_flexiness']."<br/>";
					echo "<b>Fysisk udholdenhed: </b>".$charprofile['skill_endurance']."<br/>";
					echo "<b>Taktik: </b>".$charprofile['skill_tactics']."<br/>";
					echo "<b>Styrke: </b>".$charprofile['skill_strength']."<br/>";
					echo "<b>Intelligens: </b>".$charprofile['skill_intelligence']."<br/>";
					echo "<b>Kreativitet: </b>".$charprofile['skill_creativity']."<br/>";
					echo "<b>Mental udholdenhed: </b>".$charprofile['skill_mental']."<br/>";
					echo "<b>Chakra: </b>".$charprofile['skill_chakra']."<br/><br/>";
					*/
					
					$existingbounty = $forum->check_bounty($user['character_ID'])->fetch_assoc();
					if($existingbounty['res'] > 0)
					{
						$bounty = $forum->get_wantedpost_by_character($user['character_ID'])->fetch_assoc();
						echo "<a href='wantedlist.php#".$bounty['wanted_ID']."' class='wanted'>Karakteren er efterlyst</a>";
					}
					
					$superuser = $forum->get_superuser($user['fk_superuser_ID'])->fetch_assoc();
					echo "Skaber: <a class='username' href='memberprofile.php?id=".$superuser['superuser_ID']."'>".$superuser['name']."</a><br/><br/>";	
				}
			}
			else
			{
				if($p['fk_superuser_ID'] == 0)
				{
					echo "<a class='username'>Gæst</a><br/>";	
				}
				else
				{				
					$user = $forum->get_superuser($p['fk_superuser_ID'])->fetch_assoc();			
					if ($user['avatar'] != "")
					{
						echo "<img src='".$user['avatar']."' />";	
					}
					echo "<a class='username' href='memberprofile.php?id=".$user['superuser_ID']."' style='color:".$user['color'].";'>".$user['name']."</a><br/>";	
					echo $user['title']."<br/><br/>";
					
					$activechars = $forum->count_all_accepted_active_characters_from_superuser($user['superuser_ID'])->fetch_assoc();
					$overall_posts = $forum->count_all_posts_from_superuser($user['superuser_ID'])->fetch_assoc(); 
					$achievementnumber = $forum->count_all_userachievements_from_user($user['superuser_ID'])->fetch_assoc();
					
					if($user['fk_role_ID'] > 1)
					{
						$role = $forum->get_role($user['fk_role_ID'])->fetch_assoc();
						echo "<b>Rang: </b>".$role['name']."<br/>";
					}
					else
					{
						$postrank = $forum->get_user_postrank($overall_posts['res'])->fetch_assoc();
						echo "<b>Rang: </b>".$postrank['title']."<br/>";
					}
					echo "<b>Tilmeldt:</b> ".date("d.m.Y", strtotime($user['date_joined']))."<br/>";
					echo "<b>Aktive karakterer:</b> ".$activechars['res']."<br/>";
					echo "<b>Trofæer:</b> ".$achievementnumber['res']."<br/>";
					echo "<b>Posts:</b> ".$overall_posts['res']."<br/>";
				}
						
			}
			echo "</div>";
			echo "<div class='postcontent'>";
			
			echo "<div class='posttop'>";
			
			echo "<div class='postauthor'>";
			echo "<a href='#".$p['post_ID']."' class='posttitle'>Sv: ".$viewtopic['title']."</a>";
			echo "<span class='postauthor'>";
			if ($p['ingame'] == 1) 
			{
				if($p['fk_character_ID'] == 0)
				{
					echo "af <span class='username' />Slettet karakter</span>";
				}
				else	
				{ 
					echo "af <a class='username' style='color:".$user['color'].";' 
					href='characterprofile.php?id=".$user['character_ID']."'>".$user['name']."</a>";
				}
			}
			else 
			{ 
				if($p['fk_superuser_ID'] == 0)
				{
					echo "af <a class='username'>Gæst</a>"; 	
				}
				else
				{
					echo "af <a class='username' href='memberprofile.php?id=".$user['superuser_ID']."' style='color:".$user['color'].";'>".$user['name']."</a>"; 
				}
			}
			echo " » ".date("d.m.Y G:i", strtotime($p['datetime']))."</span>";
			echo "</div>";
			
			$lastpost = $forum->get_last_post($topic_id)->fetch_assoc();
			echo "<div class='postbuttons'>";
			//Edit post if own or if mod/admin			
			if($user_rank > 1 || $forummod['res'] > 0 || $superuser['superuser_ID'] == $user_logged_in_ID)
			{
				echo "<a href='posting.php?edit=".$p['post_ID']."&page=".$currentpage."'><img src='images/topic_edit.png' title='Redigér post'/></a> ";
			}
			//Delete post if mod/admin or if own posts AND no other posts has been written since
			if($user_rank > 1 || $forummod['res'] > 0 || ($superuser['superuser_ID'] == $user_logged_in_ID && $lastpost['post_ID'] == $p['post_ID']))
			{
				echo "<a href='posting.php?delete=".$p['post_ID']."' "; ?>
				onclick='return confirm("Er du sikker på, at du vil slette denne post? Dette kan ikke fortrydes")'
				<?php echo "><img src='images/topic_delete.png' title='Slet post'/></a> ";
			}
			if($viewforum['ingame'] != 1)
			{
				echo "<a href='posting.php?t=".$topic_id."&quote=".$p['post_ID']."'><img src='images/topic_quote.png' title='Citér post'/></a> ";	
			}
			echo "</div>"; //postbuttons
			echo "</div>"; //posttop
			
			$parser->parse($p['text']);
			echo nl2br($parser->getAsHtml());
			if($user['signature'] != "" && ($p['fk_character_ID'] !=0 || $p['fk_superuser_ID'] != 0))
			{
				echo "<div class='postsignature'>";
				$parser->parse($user['signature']);
				echo nl2br($parser->getAsHtml());
				echo "</div>"; //signature	
			}
			echo "</div>"; //postcontent
			echo "</div>"; //post
	
		}
		
		
		if ($user_rank >= $viewforum['write_access'])	
		{
			echo "<hr/>";
			
			if ((isset($_POST['submit_quickreply'])) && $viewforum['ingame'] == 1)
			{
		
			  $topic_text = htmlspecialchars($_POST["posttext"]);
			  $topic_author = $_POST["poster"];	
			  
			  if($viewforum['official'] == 0)
			  {
				  $topic_next = "no_tag_wanted";
			  }
			  else
			  {
				  $topic_next = $_POST["nextposter"];
			  }
			    
			  if ($topic_text == "")
			  {
					$errormsg = "<br/><br/>Du skal skrive noget, før du kan besvare emnet";		
			  }
					  
			  else
			  {		  
				if ($topic_next == 'no_tag_wanted') { $validate_tag = 1; }	  	
	
				else 
				{ 
					if ($topic_next == 'textinput') { $topic_next = $_POST["nextposter_value"]; }
					if ($topic_next == 'dropdown') { $topic_next = $_POST["nextposter_chosen"]; }	
					$tryfindchar = $forum->try_find_character_by_name($topic_next)->fetch_assoc(); 	
					$validate_tag = $tryfindchar['res'];  				
				}			
	
				if($validate_tag < 1) // No user was found, display error message	
				{	
					$errormsg = "<br/><br/>Karakteren ".$topic_next.", som du forsøgte at tagge, findes ikke eller er endnu ikke godkendt.";	
				}
	
				else //Insert the data
				{					
					$addpost = $forum->insert_new_post($topic_id, $topic_text, $user_logged_in_ID, $topic_author, 1, $viewforum['official']);
					$update_lastpost = $forum->update_topic_lastpost($topic_id);
					
					//if character was inactive, set status to active. 
					$author = $forum->get_character($topic_author)->fetch_assoc();
					if($author['active'] != 1)
					{
						$setactive = $forum->update_character_active_status($topic_author, 1);
						
						//If the char has a default group, we need to change its color to the group color.
						$user_has_default_group = $forum->check_users_default_group($topic_author)->fetch_assoc();
						if($user_has_default_group['res'] > 0)
						{
							$defaultgroup = $forum->get_users_default_group($topic_author)->fetch_assoc(); 
							$defgroupdata = $forum->get_group($defaultgroup['fk_group_ID'])->fetch_assoc();
							$setcolor = $forum->update_character_color($defgroupdata['color'], $topic_author);
						}
						//Otherwise, no color.
						else
						{
							$setcolor = $forum->update_character_color("", $topic_author);
						}
					}
					
					//Remove existing tags from user
					$active_tag = $forum->try_get_active_tag($topic_author, $topic_id)->fetch_assoc();
					if($active_tag['res'] > 0)
					{
						$current_tag = $forum->get_tag($topic_author, $topic_id)->fetch_assoc();
						//$updatetag = $forum->update_tag(0, $current_tag['tag_ID']);
						$forum->delete_tag($current_tag['tag_ID']);
					}
					
					//Tag the next user
					if($topic_next != 'no_tag_wanted')
					{
						$tagged_char = $forum->get_character_by_name($topic_next)->fetch_assoc(); 	
						$tag_exists = $forum->try_get_tag($tagged_char['character_ID'], $topic_id)->fetch_assoc(); 
						if($tag_exists['res'] > 0)
						{
							$existing_tag = $forum->get_tag($tagged_char['character_ID'], $topic_id)->fetch_assoc(); 
							//$update_tag = $forum->update_tag(1, $existing_tag['tag_ID']);
							$forum->delete_tag($existing_tag['tag_ID']);
							$addtag = $forum->insert_new_tag($topic_id, $tagged_char['character_ID'], $user_logged_in_ID);	
						}
						else
						{
							$addtag = $forum->insert_new_tag($topic_id, $tagged_char['character_ID'], $user_logged_in_ID);	
						}
						
					}
					
					//If posting to this forum awards an achievement, we add it to the user
					$forumachievements = $forum->get_achievements_for_specific_forum($viewforum['forum_ID']);
					while($achi = $forumachievements->fetch_assoc())
					{
						$charhasachievement = $forum->check_if_user_has_achievement($user_logged_in_ID, $topic_author, $achi['achievement_ID'])->fetch_assoc();	
						if($charhasachievement['res'] < 1) //making sure the specific character doesn't already have this achievement
						{
							$addachievement = $forum->add_achievement_to_user($user_logged_in_ID, $topic_author, $achi['achievement_ID']);
						}	
					}
					
					//If posting to this topic awards an achievement, we add it to the user
					$topicachievements = $forum->get_achievements_for_specific_topic($topic_id);
					while($achi = $topicachievements->fetch_assoc())
					{
						$charhasachievement = $forum->check_if_user_has_achievement($user_logged_in_ID, $topic_author, $achi['achievement_ID'])->fetch_assoc();	
						if($charhasachievement['res'] < 1) //making sure the specific character doesn't already have this achievement
						{
							$addachievement = $forum->add_achievement_to_user($user_logged_in_ID, $topic_author, $achi['achievement_ID']);
						}	
					}
					
					$numofposts = $forum->get_numberof_posts($topic_id)->fetch_assoc();
					$numofanswers = ($numofposts['res'])-1;
					$pagenumber = ceil($numofposts['res'] / $postsperpage);
					
					
					header('Location:viewtopic.php?t='.$topic_id."&currentpage=".$pagenumber."#".$addpost);	
	
					exit;																
	
				} // end data insert
				
				
			  } // end if textarea != empty
			  
	
			}
	
			if ((isset($_POST['submit_quickreply'])) && $viewforum['ingame'] == 0)
			{
				$topic_text = htmlspecialchars($_POST["posttext"]);	
				$topic_author = $_POST["poster"];
				
				if ($topic_text == "")
				{
					$errormsg = "Du skal skrive noget, før du kan besvare emnet";		
				}
	
				else
				{
					$new_topic = 0;		
					if($topic_author == $user_logged_in_ID)	  
					{		  		
						$addpost = $forum->insert_new_post($topic_id, $topic_text, $user_logged_in_ID, 0, 0, $viewforum['official']);
						$update_lastpost = $forum->update_topic_lastpost($topic_id);		
					}
					
					$numofposts = $forum->get_numberof_posts($topic_id)->fetch_assoc();
					$numofanswers = ($numofposts['res'])-1;
					$pagenumber = ceil($numofposts['res'] / $postsperpage);
									
					header('Location:viewtopic.php?t='.$topic_id."&currentpage=".$pagenumber."#".$addpost);		
					exit;																
				}
			}
			
			if($viewtopic['locked'] != 1 && $user_rank >= $viewforum['write_access'])
			{
			
				echo "<div id='quickreply'>";
				echo "<span class='bold' style='cursor:pointer' onclick='showQuickReply()'>Hurtigt svar</span> ";
					echo "<div id='postquickreply'>";
					echo "<hr/>";
					echo "<table>";
					echo "<form name='postform' method='post' >";
					echo "<tr><td>";
					echo "<span class='bold'>Forfatter: ";
	
					if ($viewforum['ingame'] == 0 || $user_rank == 0)
					{
						echo $user_name;
						echo "<input type='hidden' name='poster' value='".$user_logged_in_ID."'/>";				
					}
			
					if ($viewforum['ingame'] == 1 && $user_rank > 0)
					{
			
						echo "<select name='poster'>";
						$userchars = $forum->get_characters_from_superuser($user_logged_in_ID);
						while ($char = $userchars->fetch_assoc())
						{
							//if the user has written with this character in the thread before, we want it to be default
							if ($char['character_ID'] == $mychar)
							{
								echo "<option value='".$char['character_ID']."' selected>".$char['name']."</option>";	
							}
							else
							{
								echo "<option value='".$char['character_ID']."'>".$char['name']."</option>";	
							}		
						}		
						echo "</select>";	
					}	
					echo "</td></tr>";	
					echo "<tr><td>";
					echo "<textarea name='posttext' class='postarea quickpostingtext'></textarea>";
					echo "</td></tr>";
					if ($viewforum['ingame'] == 1 && $user_rank > 0)
					{
						echo "<tr><td><span class='bold'>Angiv hvem der næste gang, skal svare i tråden:</span><br/><br/>";	
		
							if (count($topicposters) > 0)
							{
								echo "<input type='radio' name='nextposter' value='dropdown' required> Nuværende tråddeltager ";	
								echo "<select name='nextposter_chosen'>";
								foreach ($topicpostertime as $tpid => $time)
								{
									$tp = $topicposters[$tpid];
									echo "<option value='".$tp['name']."'>".$tp['name']."</option>";
								}
								echo "</select>";
								echo "<br/>";
								echo "<br/>";
							}
				
							echo "<input type='radio' name='nextposter' value='textinput' id='nextposter_radio' required> ";				
							echo "<input type='text' name='nextposter_value' id='nextposter_value'/>";
							?>
				
							<a class="smallbutton" href="" onclick="popup('findcharacter.php'); return false;">Find karakter</a>
				
							<script type="text/javascript">
								function popup (url) {
									win = window.open(url, "window1", "width=820,height=580,status=no,scrollbars=1,resizable=yes");
									win.focus();
								}
				
							</script>
				
							<?php
				
							echo"<br/>";			
							echo "<input type='radio' name='nextposter' value='no_tag_wanted' required>Jeg ønsker ikke at angive den næste i tråden";	
					}
						echo "</td></tr><span class='errormsg'>".$errormsg."</span>";	
					echo "<tr><td class='center'><input type='submit' name='submit_quickreply' value='Besvar emne'/></td></tr>
					</form></table>";
					echo "</div>";
				echo "</div>"; ?>
				
				<script>
				document.getElementById('postquickreply').style.display = 'none';
				function showQuickReply()
				{
					if(document.getElementById('postquickreply').style.display == 'none')
					{
						document.getElementById('postquickreply').style.display = 'block';	
					}
					else
					{
						document.getElementById('postquickreply').style.display = 'none';	
					}
				}
				</script>
				
				<?php
			}
				
				}
			
			echo "<div class='pagenavigaton'>";
	
			if ($currentpage > 1) 
			{ 
				$prevpage = $currentpage - 1;
				echo " <a class='navlink' href='viewtopic.php?t=".$topic_id."&currentpage=$prevpage'>«</a> ";
				if ($currentpage - $range > 1) 
				{
					echo " <a class='navlink' href='viewtopic.php?t=".$topic_id."&currentpage=1'>1</a> ";
				}
			} 
		
			for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) 
			{
			   if (($x > 0) && ($x <= $totalpages)) 
			   {
				  if ($x == $currentpage) { if($totalpages > 1) { echo " <span class='navlink_active'><b>$x</b></span>"; } } 
				  else { echo " <a class='navlink' href='viewtopic.php?t=".$topic_id."&currentpage=$x'>$x</a> "; } 
			   }  
			} 
		
			if ($currentpage != $totalpages) 
			{
			   $nextpage = $currentpage + 1;
			   if ($totalpages - $range > $currentpage) { echo " <a class='navlink' href='viewtopic.php?t=".$topic_id."&currentpage=$totalpages'>".$totalpages."</a> "; }
			   if ($totalpages > 1) { echo " <a class='navlink' href='viewtopic.php?t=".$topic_id."&currentpage=$nextpage'>»</a> "; }
			} 
		
			echo "</div>";
			/****** end build pagination links ******/
			
			if ($user_rank >= $viewforum['write_access'])	
			{
			
			if($_GET['lock'] && ($user_rank > 1 || $forummod['res'] > 0))
			{
				if($_GET['lock'] == "true") 
				{ 
					$updatelock = $forum->update_topic_lock(1, $topic_id);
					$forum->delete_tags_from_topic($topic_id);				
					header('Location:viewtopic.php?t='.$topic_id); 
				}
				if($_GET['lock'] == "false") 
				{ 
					$updatelock = $forum->update_topic_lock(0, $topic_id);
					header('Location:viewtopic.php?t='.$topic_id); 
				}
			}
			
			echo "<div id='optionswrap'>";
			echo "<div id='optionsleft'>";
			if($viewtopic['locked'] != 1)
			{
				echo "<a href='posting.php?t=".$topic_id."' class='forumbutton'>Besvar</a>";	
				if($user_rank > 1 || $forummod['res'] > 0) //mods/forummods and admins can lock a topic
				{
					echo "<a href='viewtopic.php?t=".$topic_id."&lock=true' class='forumbutton'>Lås emne</a>";						
				}
			}
			else
			{
				echo "<a href='' class='forumbutton'>Emnet er låst</a>";	
				if($user_rank > 1 || $forummod['res'] > 0) //mods/forummods and admins can lock a topic
				{
					echo "<a href='viewtopic.php?t=".$topic_id."&lock=false' class='forumbutton'>Lås emne op</a>";						
				}
			}
			echo "</div>";
			
			if($user_rank > 1) //mods/forummods and admins can lock a topic
			{
				echo "<div id='optionsright'>";
				//Move topic
				echo "<form method='post'>";
				echo "Flyt emne til: "; $forumoptions = $forum->get_all_writable_forums($viewforum['ingame'], $viewforum['official']);
				echo "<select name='moveforum'>";
				while($moveforum = $forumoptions->fetch_assoc())
				{
					echo "<option value='".$moveforum['forum_ID']."'>".$moveforum['title']."</option>";	
				}
				echo "</select> <input type='submit' name='move_topic' value='Udfør'/> ";
				echo "<input type='submit' name='delete_topic' value='Slet emne' class='deletebutton' "; ?>
				onclick='return confirm("Er du sikker på, at du vil slette emnet? Dette kan ikke fortrydes")'
				<?php echo "/>";
				echo "</form>";
				echo "</div>";
			}
			else if ($forummod['res'] > 0)
			{
				echo "<div id='optionsright'>";
				echo "<form method='post'>";
				echo "<input type='submit' name='delete_topic' value='Slet emne' class='deletebutton' "; ?>
				onclick='return confirm("Er du sikker på, at du vil slette emnet? Dette kan ikke fortrydes")'
				<?php echo "/>";
				echo "</form>";
				echo "</div>";	
			}
					
			echo "</div>";
			
			if($_POST['move_topic'])
			{
				$moveforum = $_POST['moveforum'];				
				$updatetopicforum = $forum->update_topic_forum($moveforum, $topic_id); 	
				header('Location:viewtopic.php?t='.$topic_id); 
			}
			if($_POST['delete_topic'])
			{
				$deletetopictags = $forum->delete_tags_from_topic($topic_id);
				$deletetopicposts = $forum->delete_posts_from_topic($topic_id);
				$topic_has_poll = $forum->check_if_topic_has_poll($topic_id)->fetch_assoc();
				if($topic_has_poll['res'] > 0)
				{
					$topicpoll = $forum->get_poll_from_topic($topic_id)->fetch_assoc();
					$polloptions = $forum->get_poll_options($topicpoll['poll_ID']);
					
					while($option = $polloptions->fetch_assoc())
					{
						//delete all votes from options
						$forum->delete_votes_from_poll_option($option['option_ID']); 
					}
					//delete all options
					$forum->delete_options_from_poll($topicpoll['poll_ID']);
					//delete the poll
					$forum->delete_poll($topicpoll['poll_ID']);
				}
				$deletetopic = $forum->delete_topic($topic_id);
				header('Location:viewforum.php?f='.$forum_id); 
			}
			
		} //End if user has write access
		
	} //End if has read access
	
} //end if forum is found
else
{	
	echo "No topic found";
}
?>
<?php
$pagetitle = $viewtopic['title']." - ";
include('footer.php');
?>