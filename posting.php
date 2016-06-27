<?php
include('header.php');
if(!isset($_SESSION['user']))
{
	$user_name = "Gæst";
	$user_id = 0;
}
else
{
	$user_name = $user_logged_in['name'];	
	$user_id = $user_logged_in['superuser_ID'];	
}
?>

<?php
if(isset($_GET['f']))
{
	$f = $_GET['f'];
	
	$forum_exists = $forum->forum_exists($f)->fetch_assoc();	
	$postforum = $forum->get_forum($f)->fetch_assoc();		
	
	if($forum_exists['res'] > 0)
	{
		
		if ($user_rank < $postforum['write_access'])		
		{	
			echo "<script>";	
			header('Location:viewforum.php?f='.$f);
			echo "</script>";		
		}
		else
		{	
	
		if(!isset($_GET['t']))	
		{
			// Show the list of parent forums in the top of the page //
			$parents = array();
			$parentf = $postforum['parent_ID'];
			
			while($parentf != 0)
			{
				$parentdata = $forum->get_forum($parentf)->fetch_assoc();
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
			
			echo " &laquo; <a href='viewforum.php?f=".$postforum['forum_ID']."' class='forumnavlink'>".$postforum['title']."</a>";
			echo "</div>"; //End navigation div 
			
			/* POSTING A NEW TOPIC */
		
			// define variables and set to empty values 
			$topic_title = $topic_text = $topic_author = $topic_next = $errormsg = "";
	
			if ((isset($_POST['submit_new_topic'])) && $postforum['ingame'] == 1)	
			{	
			  $topic_title = htmlspecialchars($_POST["title"], ENT_QUOTES, 'UTF-8');
			  $topic_text = htmlspecialchars($_POST["posttext"], ENT_QUOTES, 'UTF-8');
			  $topic_author = $_POST["poster"];	
			  $topic_pinned = 0;
			  $topictype = "";
			  
			  if($postforum['official'] == 0)
			  {
				  $topic_next = "no_tag_wanted";
				  $topic_warning = "";
			  }
			  else
			  {
				  $topic_next = $_POST["nextposter"];
			  	  $topic_warning = $_POST["warning"];
			  }
			  						  
			  if ($topic_text == "")
			  {
					$errormsg = "<br/><br/>Du skal skrive noget, før du kan oprette emnet";		
			  }
			  	  
			  else
			  {		  
				if ($topic_next == 'no_tag_wanted') { $validate_tag = 1; }	 	
	
				else 	
				{ 
					//Making sure a tag is valid
					if ($topic_next == 'textinput') { $topic_next = $_POST["nextposter_value"]; }	
					$tryfindchar = $forum->try_find_character_by_name($topic_next)->fetch_assoc(); 	
					$validate_tag = $tryfindchar['res'];  				
				}
				
				//Warning handling
				if($topic_warning == "yes")
				{
					 $topic_warning = htmlspecialchars($_POST["warningtype"], ENT_QUOTES, 'UTF-8');
				}
			    else
			    {
					  $topic_warning = "";
				}		
				if($_POST['pinned'] == "yes") { $topic_pinned = 1; }	
				if($_POST['open'] == "yes") { $topictype = "Åben tråd"; } if($_POST['plot'] == "yes") { $topictype = "Plottråd"; } 						
				if($validate_tag < 1) // No user was found, display error message	
				{	
					$errormsg = "<br/><br/>Karakteren ".$topic_next.", som du forsøgte at tagge, findes ikke eller er endnu ikke godkendt.";	
				}	
				else //Insert the data	
				{
					$new_topic = $forum->insert_new_topic($postforum['forum_ID'], $topic_title, $user_id, $topic_author, 1, $postforum['official'], $topic_pinned, $topic_warning, $topictype);					
					$addpost = $forum->insert_new_post($new_topic, $topic_text, $user_id, $topic_author, 1, $postforum['official']);
		
					//Tag the next user
					if($topic_next != 'no_tag_wanted')
					{
						$tagged_char = $forum->get_character_by_name($topic_next)->fetch_assoc();
						$addtag = $forum->insert_new_tag($new_topic, $tagged_char['character_ID'], $user_id);
					}
					
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

					//If posting to this forum awards an achievement, we add it to the user
					$forumachievements = $forum->get_achievements_for_specific_forum($postforum['forum_ID']);
					while($achi = $forumachievements->fetch_assoc())
					{
						$charhasachievement = $forum->check_if_user_has_achievement($user_id, $topic_author, $achi['achievement_ID'])->fetch_assoc();	
						if($charhasachievement['res'] < 1) //making sure the specific character doesn't already have this achievement
						{
							$addachievement = $forum->add_achievement_to_user($user_id, $topic_author, $achi['achievement_ID']);
						}	
					}
					
	
					header('Location:viewtopic.php?t='.$new_topic);
					exit;																
	
				} // end data insert				
			  } // end if textarea != empty	
			} // End submit ingame topic
	
			
	
			if ((isset($_POST['submit_new_topic'])) && $postforum['ingame'] == 0)	
			{
				$topic_title = htmlspecialchars($_POST["title"], ENT_QUOTES, 'UTF-8');
				$topic_text = htmlspecialchars($_POST["posttext"], ENT_QUOTES, 'UTF-8');
				$topic_author = $_POST["poster"];
				$topic_pinned = 0;
								
				if ($topic_text == "")
				{
					$errormsg = "<br/><br/>Du skal skrive noget, før du kan oprette emnet";		
				}	
				else
				{
					$new_topic = 0;
					if($_POST['pinned'] == "yes") { $topic_pinned = 1; }
					if($topic_author == $user_id)	  		
					{		  		
						$new_topic = $forum->insert_new_topic($postforum['forum_ID'], $topic_title, $user_id, 0, 0, $postforum['official'], $topic_pinned, "", "");							
						$addpost = $forum->insert_new_post($new_topic, $topic_text, $user_id, 0, 0, $postforum['official']);		
					}
					
					if($_POST['pollquestion'] != "" && $_POST['polloptions'] != "")
					{
						$numberofchoices = 0;
						if($_POST['pollnumberofchoices']) { $numberofchoices = 1; }
						
						$new_poll = $forum->insert_new_poll($new_topic, $_POST['pollquestion'], $numberofchoices);
						
						$polloptions = $_POST['polloptions'];
						$arr = explode("\n", $polloptions);
						foreach($arr as $value)
						{
							$value = trim($value);
							$polloption = $forum->insert_polloption($new_poll, $value);
						}
					}
					header('Location:viewtopic.php?t='.$new_topic);
					exit;																
				}			  	
			}
	
			//We do not allow not-registered users or users with no accepted chars to post in a forum that is ingame
			if($postforum['ingame'] == 1 && ($accepted_chars < 1 || $user_rank == 0))			
			{
				echo "<p class='errormsg center'>Du har ikke adgang til at skrive i dette forum.<br/>
				Dette kan skyldes, at du ikke er logget ind, eller at du ikke har nogen accepterede karakterer.</p>";	
			}
			else
			{
	
				/* HTML FORM FOR A NEW TOPIC */			
				echo "<div class='category'><a href=''>Skriv et nyt emne</a></div>";	
				echo "<table class='postingtable'>";		
				echo "<form name='postform' method='post' >";
						
		
				echo "<tr><td class='bold'>Forfatter: </td>";
		
				if ($postforum['ingame'] == 0 || $user_rank == 0)		
				{
					echo "<td>".$user_name."</td>";		
					echo "<input type='hidden' name='poster' value='".$user_id."'/>";						
				}
		
				if ($postforum['ingame'] == 1 && $user_rank > 0 && $accepted_chars > 0)		
				{
		
					$selected_char = "no_character_selected";
					echo "<td><select name='poster'>";
		
					$userchars = $forum->get_characters_from_superuser($user_logged_in['superuser_ID']);		
					while ($char = $userchars->fetch_assoc())
					{
		
						$tag_exists = $forum->try_get_active_tag($char['character_ID'], $posttopic['topic_ID'])->fetch_assoc();
						if($tag_exists['res'] > 0)
						{				
							echo "<option value='".$char['character_ID']."' selected>".$char['name']."</option>";		
						}
		
						else
						{		
							echo "<option value='".$char['character_ID']."'>".$char['name']."</option>";
						}
		
					}
					echo "</select></td>";			
				}			
				echo "</tr>";	
		
				echo "<tr><td class='bold'>Titel: </td>";		
				echo "<td><input class='postinginput titleinput' type='text' name='title' value='".$topic_title."' maxlength='65' required/></td>";			
				echo "</tr>";
				echo "<tr><td colspan='2'>";		
				echo "<textarea name='posttext' class='postarea postingtext'>".$topic_text."</textarea></td>";			
				echo "</tr>";
				echo "<tr><td id='charNum'> </td><td></td></tr>";
				
				?>
                
                
                <?php
				$forummod = $forum->forummod_exists($postforum['forum_ID'], $user_logged_in_ID)->fetch_assoc();
				if($user_rank > 1 || $forummod['res'] > 0) //mods/forummods and admins can pin a topic
				{
					echo "<tr><td class='bold'>Angiv som opslag: </td>";		
					echo "<td><input type='checkbox' name='pinned' value='yes' /> (Emnet vises øverst på underforummets side sammen med andre opslag)</td>";			
					echo "</tr>";
					if($user_rank > 1 && $postforum['ingame'] == 1)
					{
						echo "<tr><td class='bold'>Angiv som plottråd: </td>";		
						echo "<td><input type='checkbox' name='plot' value='yes' /> (Dette overskriver et evt. valg om 'åben tråd')</td>";			
						echo "</tr>";
					}
				}
		
				if ($postforum['ingame'] == 1 && $postforum['official'] == 1)
				{
					echo "<tr><td class='bold'>Angiv som åben tråd: </td>";		
					echo "<td><input type='checkbox' name='open' value='yes' /> (Alle må deltage i tråden)</td>";			
					echo "</tr>";
					
					echo "<tr><td class='bold'>Tilføj advarsel:</td>";
					echo "<td><input type='radio' name='warning' value='no' checked required/>Ingen ";
					echo "<input type='radio' name='warning' value='yes' required/>";
					echo "Vælg: <select name='warningtype'> 
					<option value='Seksuelt indhold'>Seksuelt indhold</option>
					<option value='Voldsomt indhold'>Voldsomt indhold</option>
					<option value='Seksuelt og voldsomt indhold'>Seksuelt og voldsomt indhold</option>";
					echo "</select>";
					echo "</td></tr>";
					echo "</table>";
					
					echo "<hr/>";
					
					echo "<table class='postingtable'>";
					echo "<tr><td colspan='2'>";
					echo "<span class='bold'>Angiv hvem der næste gang, skal svare i tråden:</span><br/><br/>";	
					echo "<input type='radio' name='nextposter' value='textinput' id='nextposter_radio' required>";		
					echo "<input type='text' name='nextposter_value' id='nextposter_value'/>";
					?>
					<a class="smallbutton" href="" onclick="popup('findcharacter.php'); return false;">Find karakter</a>
		
					<script type="text/javascript">
						function popup (url) {
							win = window.open(url, "window1", "scrollbars=1, width=820,height=580,status=no,resizable=yes");
							win.focus();
						}
					</script>
		
					<?php
		
					echo"<br/>";			
		
					
		
					echo "<input type='radio' name='nextposter' value='no_tag_wanted' required>Jeg ønsker ikke at angive den næste i tråden";	
		
					echo "<span class='errormsg'>".$errormsg."</span>";	
		
					echo "</tr>";
		
					
		
				}
		
				
				echo "</table>";
				echo "<hr/>";
				
				// Making a poll
				echo "<div id='pollcreate'  style='display:none;'>";
				echo "<table class='postingtable'>";
				echo "<tr><td><span class='bold'>Afstemningsspørgsmål:</span></td>";
				echo "<td><input type='text' class='postinginput' name='pollquestion'></td></tr>";
				echo "<tr><td><span class='bold'>Afstemningsmuligheder:</span><br/><span class='smallertext'>Angiv hver på en ny linje</span></td>";
				echo "<td><textarea name='polloptions' class='postingpoll'></textarea></td></tr>";
				echo "<tr><td><span class='bold'>Flere afstemningsmuligheder</span>
				</br><span class='smallertext'>Gør det muligt at stemme på mere end én mulighed</span></td>";
				echo "<td><input type='checkbox' name='pollnumberofchoices'/> Ja</td></tr>";
				echo "</table>";
				echo "<hr/>";
				echo "</div>";
				
				echo "<table class='postingtable'>";
				echo "<tr><td colspan='2' class='center'>";
				if($postforum['ingame'] == 0)
				{
					echo "<input class='submitbutton' type='button' value='Tilføj afstemning' onclick='showPoll()'> ";	
				}
				echo "<input class='submitbutton' type='submit' name='submit_new_topic' value='Udfør'></td>";	
				echo "</tr>";			
				echo "</form>";
				
				?>
                <script>
				function showPoll() {
					   document.getElementById('pollcreate').style.display = "block";
					}
				</script>
                
                <?php
		
				echo "</table>";
			
			} // End ingame+accepted characters check
	
		} // End forum access
		}	// end "if != t
	} // end forum exists
	else
	{
		echo "<span class='forumerror'>Forummet eksisterer ikke</span>";	
	}
} //end if get f
if(isset($_GET['t']))
{
	$t = $_GET['t'];
	$topic_exists = $forum->topic_exists($t)->fetch_assoc();
	
	if($topic_exists['res'] > 0)
	{	
		$posttopic = $forum->get_topic($t)->fetch_assoc();	
			
		$f = $posttopic['fk_forum_ID'];
		$postforum = $forum->get_forum($f)->fetch_assoc();	
		
		if($_GET['quote'])
		{
			$quote_id = $_GET['quote'];
			$quotepost = $forum->get_post($quote_id)->fetch_assoc();	
			
			$topic_text = "[quote]".$quotepost['text']."[/quote]";
		}
	
		if ($user_rank < $postforum['write_access'] || $posttopic['locked'] == 1)	
		{
			header('Location:viewforum.php?f='.$f);	
		}
		else
		{
			
			$parents = array();
			$parentf = $postforum['parent_ID'];
			
			while($parentf != 0)
			{
				$parentdata = $forum->get_forum($parentf)->fetch_assoc();
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
			
			echo " &laquo; <a href='viewforum.php?f=".$postforum['forum_ID']."' class='forumnavlink'>".$postforum['title']."</a>";
			echo "</div>"; //End navigation div 
			
			if ((isset($_POST['submit_new_post'])) && $postforum['ingame'] == 1)
			{
		
			  $topic_text = htmlspecialchars($_POST["posttext"], ENT_QUOTES, 'UTF-8');
			  $topic_author = $_POST["poster"];	
			  
			  if($postforum['official'] == 0)
			  {
				  $topic_next = "no_tag_wanted";
			  }
			  else
			  {
				  $topic_next = $_POST["nextposter"];
			  }
			    
			  if ($topic_text == "")
			  {
					$errormsg = "<br/><br/>Du skal skrive noget, før du kan oprette emnet";		
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
					$addpost = $forum->insert_new_post($t, $topic_text, $user_id, $topic_author, 1, $postforum['official']);
					$update_lastpost = $forum->update_topic_lastpost($t);
					
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
					$active_tag = $forum->try_get_active_tag($topic_author, $t)->fetch_assoc();
					if($active_tag['res'] > 0)
					{
						$current_tag = $forum->get_tag($topic_author, $t)->fetch_assoc();
						//$updatetag = $forum->update_tag(0, $current_tag['tag_ID']);
						$forum->delete_tag($current_tag['tag_ID']);
					}
					
					//Tag the next user
					if($topic_next != 'no_tag_wanted')
					{
						$tagged_char = $forum->get_character_by_name($topic_next)->fetch_assoc(); 	
						$tag_exists = $forum->try_get_tag($tagged_char['character_ID'], $t)->fetch_assoc(); 
						if($tag_exists['res'] > 0)
						{
							$existing_tag = $forum->get_tag($tagged_char['character_ID'], $t)->fetch_assoc(); 
							//$update_tag = $forum->update_tag(1, $existing_tag['tag_ID']);
							$forum->delete_tag($existing_tag['tag_ID']);
							$addtag = $forum->insert_new_tag($t, $tagged_char['character_ID'], $user_id);	
						}
						else
						{
							$addtag = $forum->insert_new_tag($t, $tagged_char['character_ID'], $user_id);	
						}
						
					}
					
					//If posting to this forum awards an achievement, we add it to the user
					$forumachievements = $forum->get_achievements_for_specific_forum($postforum['forum_ID']);
					while($achi = $forumachievements->fetch_assoc())
					{
						$charhasachievement = $forum->check_if_user_has_achievement($user_id, $topic_author, $achi['achievement_ID'])->fetch_assoc();	
						if($charhasachievement['res'] < 1) //making sure the specific character doesn't already have this achievement
						{
							$addachievement = $forum->add_achievement_to_user($user_id, $topic_author, $achi['achievement_ID']);
						}	
					}
					
					//If posting to this topic awards an achievement, we add it to the user
					$topicachievements = $forum->get_achievements_for_specific_topic($t);
					while($achi = $topicachievements->fetch_assoc())
					{
						$charhasachievement = $forum->check_if_user_has_achievement($user_id, $topic_author, $achi['achievement_ID'])->fetch_assoc();	
						if($charhasachievement['res'] < 1) //making sure the specific character doesn't already have this achievement
						{
							$addachievement = $forum->add_achievement_to_user($user_id, $topic_author, $achi['achievement_ID']);
						}	
					}
					
					$numofposts = $forum->get_numberof_posts($t)->fetch_assoc();
					$numofanswers = ($numofposts['res'])-1;
					$pagenumber = ceil($numofposts['res'] / $postsperpage);
									
					header('Location:viewtopic.php?t='.$t."&currentpage=".$pagenumber."#".$addpost);	
	
					exit;																
	
				} // end data insert
				
				
			  } // end if textarea != empty
			  
	
			}
	
			if ((isset($_POST['submit_new_post'])) && $postforum['ingame'] == 0)
			{
				$topic_text = htmlspecialchars($_POST["posttext"], ENT_QUOTES, 'UTF-8');
				$topic_author = $_POST["poster"];
				
				if ($topic_text == "")
				{
					$errormsg = "Du skal skrive noget, før du kan besvare emnet";		
				}
	
				else
				{
					$new_topic = 0;		
					if($topic_author == $user_id)	  
					{		  		
						$addpost = $forum->insert_new_post($t, $topic_text, $user_id, 0, 0, $postforum['official']);
						$update_lastpost = $forum->update_topic_lastpost($t);		
					}
					$numofposts = $forum->get_numberof_posts($t)->fetch_assoc();
					$numofanswers = ($numofposts['res'])-1;
					$pagenumber = ceil($numofposts['res'] / $postsperpage);
									
					header('Location:viewtopic.php?t='.$t."&currentpage=".$pagenumber."#".$addpost);		
					exit;																
				}
			}
	
		
			//We do not allow not-registered users or users with no accepted chars to post in a forum that is ingame
			if($postforum['ingame'] == 1 && ($accepted_chars < 1 || $user_rank == 0))
			
			{
				echo "Du har ikke adgang til at skrive i dette forum.<br/>
				Dette kan skyldes, at du ikke er logget ind, eller at du ikke har nogen accepterede karakterer.";	
			}
			else
			{
	
				/* HTML FORM FOR ANSWERING AN EXISTING TOPIC */	
				echo "<div class='category'><a href=''>Besvar emne</a></div>";	
		
				echo "<table class='postingtable'>";
		
				echo "<form name='postform' method='post' >";
				
				$topicposters = array();
				$topicpostertime = array();
				$mychar = 0;	
				$topicposts = $forum->get_all_posts($t);	
				
				while ($currentpost = $topicposts->fetch_assoc())
				{					
					//if (!in_array($p['fk_character_ID'], $topicposters)) 
					//{					
						$posterchar = $forum->get_character($currentpost['fk_character_ID'])->fetch_assoc();
						if ($posterchar['fk_superuser_ID'] != $user_id && $posterchar['dead'] == 0 && $posterchar['accepted'] == 1)
						{
							if (!in_array($posterchar, $topicposters)) {
								$topicposters[$currentpost['fk_character_ID']] = $posterchar;
							}
							$topicpostertime[$currentpost['fk_character_ID']] = strtotime($currentpost['datetime']);
						}
						else
						{
							$mychar = $posterchar['character_ID'];
						}
					//}	
					
				}
				
				asort($topicpostertime);
		
				echo "<tr><td class='bold'>Forfatter: </td>";

				if ($postforum['ingame'] == 0 || $user_rank == 0)
				{
					echo "<td>".$user_name."</td>";
					echo "<input type='hidden' name='poster' value='".$user_id."'/>";				
				}
		
				if ($postforum['ingame'] == 1 && $user_rank > 0)
				{
		
					echo "<td><select name='poster'>";
					$userchars = $forum->get_characters_from_superuser($user_logged_in['superuser_ID']);
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
					echo "</select></td>";	
				}	
				echo "</tr>";	
		
				echo "<tr><td class='bold'>Titel: </td>";		
				echo "<td>Besvar: ".$posttopic['title']."</td>";	
				echo "</tr>";
		
				echo "<tr><td colspan='2'>";		
				echo "<textarea name='posttext' class='postarea postingtext'>".$topic_text."</textarea></td>";	
				echo "</tr>";
				echo "</table>";
		
				
				
				if ($postforum['ingame'] == 1 && $postforum['official'] == 1)
				{
					echo "<table class='postingtable'>";
					echo "<tr><td colspan='2'>";
					echo "<span class='bold'>Angiv hvem der næste gang, skal svare i tråden:</span><br/><br/>";	

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
					echo "<span class='errormsg'>".$errormsg."</span>";	
					echo "</tr>";
					echo "</table>";
				}
	
				echo "<hr/>";	
				echo "<table class='postingtable'>";
				echo "<tr><td colspan='2' class='center'>";
				echo "<input class='submitbutton' type='submit' name='submit_new_post' value='Udfør'></td>";		
				echo "</tr>";			
				echo "</form>";
				echo "</table>";	
				
				echo "<hr/>";
				echo "<a href='viewtopic.php?t=".$posttopic['topic_ID']."' class='backbtn'>Tilbage</a>";
				
				echo "<br/>"; echo "<br/>";
				echo "<span class='bold'>Tidligere indlæg i emnet: </span>";
				/* The topics last posts are shown here */
				echo "<hr/>";
				echo "<div id='prevposts'>";
				
				$prevposts = $forum->get_ten_last_posts($t);
				while ($p = $prevposts->fetch_assoc())
				{
					echo "<div class='prevpost'>";
					echo "<span class='smalltext'>";
					echo "af ";
					
					if ($p['ingame'] == 1)
					{
						$user = $forum->get_character($p['fk_character_ID'])->fetch_assoc();
						echo "<a class='username' style='color:".$user['color'].";' href='characterprofile.php?id=".$user['character_ID']."'>".$user['name']." </a> » ";
					}
					else
					{
						$user = $forum->get_superuser($p['fk_superuser_ID'])->fetch_assoc();
						echo "<a class='username' style='color:".$user['color'].";' href='memberprofile.php?id=".$user['superuser_ID']."'>".$user['name']." </a> » ";	
					}			
					echo date("j. M Y G:i", strtotime($p['datetime']));
					echo "<br/>";
					echo "</span>";
					$posttext = nl2br($parser->parse($p['text'])->getAsHtml());	
					echo parseURls($posttext);
					echo "</div>";
				}
			
			echo "</div>";
			} //end accepted characters check
		} // End forum access
	} //end if topic exists
	else
	{
		echo "<span class='forumerror'>Emnet eksisterer ikke</span>";	
	}
}
// EDITING AN ALREADY EXISTING TOPIC/POST
else if(isset($_GET['edit']))
{
	$post_id = $_GET['edit'];
	if(isset($_GET['page'])) { $pagenumber = $_GET['page']; } else { $pagenumber = 1; }
	$existingpost = $forum->post_exists($post_id)->fetch_assoc();
	
	if($existingpost['res'] > 0)
	{
		
		$post_to_edit = $forum->get_post($post_id)->fetch_assoc();	
		$topic_to_edit = $forum->get_topic($post_to_edit['fk_topic_ID'])->fetch_assoc();
		$superuser = $forum->get_superuser($post_to_edit['fk_superuser_ID'])->fetch_assoc();
		$forummod = $forum->forummod_exists($topic_to_edit['fk_forum_ID'], $user_logged_in_ID)->fetch_assoc();
		$postforum = $forum->get_forum($topic_to_edit['fk_forum_ID'])->fetch_assoc();
		
		$parents = array();
		$parentf = $postforum['parent_ID'];
		
		while($parentf != 0)
		{
			$parentdata = $forum->get_forum($parentf)->fetch_assoc();
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
		
		echo " &laquo; <a href='viewforum.php?f=".$postforum['forum_ID']."' class='forumnavlink'>".$postforum['title']."</a>";
		echo "</div>"; //End navigation div 
	
		if($user_rank > 1 || $forummod['res'] > 0 || $post_to_edit['fk_superuser_ID'] == $user_logged_in_ID)
		{
			//Submitting the post edit
			if($_POST['submit_edited_post'] && $postforum['ingame'] == 1)
			{
			  $topic_author = $_POST["poster"];		
			  $topic_title = htmlspecialchars($_POST["title"], ENT_QUOTES, 'UTF-8');	
			  $topic_text = htmlspecialchars($_POST["posttext"], ENT_QUOTES, 'UTF-8');
			  $topic_next = $_POST["nextposter"];
			  $topic_warning = $_POST["warning"];
			  $topic_pinned = 0;	
			  $topictype = "";
			  
			  if ($topic_text == "")
			  {
					$errormsg = "<br/><br/>Du skal skrive noget, før du kan oprette emnet";		
			  }
			  	  
			  else
			  {		  
				if ($topic_next == 'no_tag_wanted') { $validate_tag = 1; }	 	
	
				else 	
				{ 
					//Making sure a tag is valid
					if ($topic_next == 'textinput') { $topic_next = $_POST["nextposter_value"]; }	
					if ($topic_next == 'dropdown') { $topic_next = $_POST["nextposter_chosen"]; }
					$tryfindchar = $forum->try_find_character_by_name($topic_next)->fetch_assoc(); 	
					$validate_tag = $tryfindchar['res'];  				
				}
				
				//Warning handling
				if($topic_warning == "yes")
				{
					 $topic_warning = htmlspecialchars($_POST["warningtype"], ENT_QUOTES, 'UTF-8'); 
				}
			    else
			    {
					  $topic_warning = "";
				}		
				if($_POST['pinned'] == "yes") { $topic_pinned = 1; }	
				if($_POST['open'] == "yes") { $topictype = "Åben tråd"; } if($_POST['plot'] == "yes") { $topictype = "Plottråd"; } 	
						
				if($validate_tag < 1) // No user was found, display error message	
				{	
					$errormsg = "<br/><br/>Karakteren ".$topic_next.", som du forsøgte at tagge, findes ikke eller er endnu ikke godkendt.";	
				}	
				else //Update the data
				{
					if($_POST['edittype'] == "topic") //Only updating the topic if we actually was editing the topic and not just a post.
					{
						$update_topic = $forum->update_topic($topic_title, $topic_author, $topic_pinned, $topic_warning, $topictype, $topic_to_edit['topic_ID']);
					}
					$update_post = $forum->update_post($topic_text, $topic_author, $post_id);				
		
					if($topic_next != 'no_tag_wanted')
					{
						$tagged_char = $forum->get_character_by_name($topic_next)->fetch_assoc(); 
						$addtag = $forum->insert_new_tag($topic_to_edit['topic_ID'], $tagged_char['character_ID'], $user_id);
					}
					
	
					header('Location:viewtopic.php?t='.$topic_to_edit["topic_ID"]."&currentpage=".$pagenumber."#".$post_id);		
					exit;																
	
				} // end data insert				
			  } // end if textarea != empty	
			  
			  
			}
			
			if($_POST['submit_edited_post'] && $postforum['ingame'] == 0)
			{
			  $topic_author = $_POST["poster"];		
			  $topic_title = htmlspecialchars($_POST["title"], ENT_QUOTES, 'UTF-8');
			  $topic_text = htmlspecialchars($_POST["posttext"], ENT_QUOTES, 'UTF-8');	
			  $topic_pinned = 0;	
			  
			  if ($topic_text == "")
			  {
					$errormsg = "<br/><br/>Du skal skrive noget, før du kan oprette emnet";		
			  }
			  	  
			  else
			  {		  	
				if($_POST['pinned'] == "yes") { $topic_pinned = 1; }	
						
					if($_POST['edittype'] == "topic") //Only updating the topic if we actually was editing the topic and not just a post.
					{
						$update_topic = $forum->update_topic($topic_title, 0, $topic_pinned, "", "", $topic_to_edit['topic_ID']);
						
						if($_POST['pollquestion'] != "" && $_POST['polloptions'] != "")
						{
							$numberofchoices = 0;
							if($_POST['pollnumberofchoices']) { $numberofchoices = 1; }
							
							$new_poll = $forum->insert_new_poll($topic_to_edit['topic_ID'], $_POST['pollquestion'], $numberofchoices);
							
							$polloptions = $_POST['polloptions'];
							$arr = explode("\n", $polloptions);
							foreach($arr as $value)
							{
								$value = trim($value);
								$polloption = $forum->insert_polloption($new_poll, $value);
							}
						}
					}
					$update_post = $forum->update_post($topic_text, 0, $post_id);	
					
					if($_POST['deletepoll'] == "yes")
					{
						$topic_has_poll = $forum->check_if_topic_has_poll($topic_to_edit['topic_ID'])->fetch_assoc();
						if($topic_has_poll['res'] > 0)
						{
							$topicpoll = $forum->get_poll_from_topic($topic_to_edit['topic_ID'])->fetch_assoc();
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
					}			
	
					header('Location:viewtopic.php?t='.$topic_to_edit["topic_ID"]."&currentpage=".$pagenumber."#".$post_id);	
					exit;																
	
							
			  } // end if textarea != empty	
			  
			  
			}
			
			$firstpost = $forum->get_first_post($topic_to_edit['topic_ID'])->fetch_assoc();
			$lastpost = $forum->get_last_post($topic_to_edit['topic_ID'])->fetch_assoc();
			
			/* HTML FORM FOR EDITING AN EXISTING POST */	
			
				echo "<div class='category'><a href=''>Redigér post</a></div>";
				echo "<table class='postingtable'>";
				echo "<form name='postform' method='post' >";
				echo "<tr><td class='bold'>Forfatter: </td>";				
				
				if ($postforum['ingame'] == 0)
				{
					//if the post is written by a guest or a no longer existing user
					if ($post_to_edit['fk_superuser_ID'] == 0) 
					{ 
						echo "<td>Gæst</td>"; 
						echo "<input type='hidden' name='poster' value='0'/>";	
					}
					else { echo "<td>".$superuser['name']."</td>"; 
					echo "<input type='hidden' name='poster' value='".$superuser['superuser_ID']."'/>";	}			
				}
		
				if ($postforum['ingame'] == 1)
				{
					if ($post_to_edit['fk_superuser_ID'] == 0) { echo "<td>Gæst</td>"; echo "<input type='hidden' name='poster' value='0'/>"; }
					else
					{
							$postcharacter = $forum->get_character($post_to_edit['fk_character_ID'])->fetch_assoc();
							
							echo "<td><select name='poster'>";
							echo "<option value='".$postcharacter['character_ID']."' selected>".$postcharacter['name']."</option>";
							$userchars = $forum->get_characters_from_superuser($superuser['superuser_ID']);	
							while ($char = $userchars->fetch_assoc())
							{
								if ($char['character_ID'] != $post_to_edit['fk_character_ID'])
								{
									echo "<option value='".$char['character_ID']."'>".$char['name']."</option>";
								}
							}
							echo "</select></td>";	
					}
				}	
		
				echo "</tr>";	
		
				
				if($firstpost['post_ID'] == $post_id)
				{
					echo "<input type='hidden' name='edittype' value='topic'/>";
					echo "<tr><td class='bold'>Titel: </td>";		
					echo "<td><input type='text' name='title' class='postinginput' value='".$topic_to_edit['title']."' maxlength='65' required/></td>";			
					echo "</tr>";
				}
				else
				{
					echo "<tr><td class='bold'>Titel: </td>";
					echo "<td>Sv: ".$topic_to_edit['title']."</td>";	
					echo "<input type='hidden' name='title' value='".$topic_to_edit['title']."'/>";	
					echo "</tr>";
				}
				
				echo "<tr><td colspan='2'>";
				echo "<textarea name='posttext' class='postarea postingtext'>".$post_to_edit['text']."</textarea></td>";		
				echo "</tr>";
				echo "</table>";
		
				
				if ($postforum['ingame'] == 1 && $post_id == $lastpost['post_ID'] && $postforum['official'] == 1)
				{
					echo "<table class='postingtable'>";
					echo "<tr><td>";
					echo "<span class='bold'>Eksisterende tags:</span></td>";	
					$number_of_topictags = $forum->count_topic_tags($topic_to_edit['topic_ID'])->fetch_assoc();
					if($number_of_topictags['res'] < 1) { echo "<td>Ingen</td></tr>"; }
					else
					{
						$topictags = $forum->get_topic_tags($topic_to_edit['topic_ID']);		
						echo "<td>";
						while($tag = $topictags->fetch_assoc())
						{
							$taggedchar = $forum->get_character($tag['fk_character_ID'])->fetch_assoc();
							echo "<a href='characterprofile.php?id=".$taggedchar['character_ID']."'>".$taggedchar['name']." </a>";	
						}
						echo "</td></tr>";
					}
					echo "<tr><td class='bold'>";
					echo "Tilføj nyt tag:</td>";	
					echo "<td>";
					
					$topicposters = array();
					$topicposts = $forum->get_all_posts($topic_to_edit['topic_ID']);	
					
					while ($currentpost = $topicposts->fetch_assoc())
					{					
						if (!in_array($p['fk_character_ID'], $topicposters)) 
						{					
							$posterchar = $forum->get_character($currentpost['fk_character_ID'])->fetch_assoc();
							if ($posterchar['fk_superuser_ID'] != $post_to_edit['fk_superuser_ID'] && $posterchar['dead'] == 0 && $posterchar['accepted'] == 1)
							{
								array_push($topicposters, $posterchar);
							}
						}	
						
					}
					
					if (count($topicposters) > 0)
					{/*
						echo "<input type='radio' name='nextposter' value='dropdown' required> Nuværende tråddeltager ";	
						echo "<select name='nextposter_chosen'>";
						foreach ($topicposters as $tp)
						{
							echo "<option value='".$tp['name']."'>".$tp['name']."</option>";
						}
						echo "</select>";
						echo "<br/>";
						*/
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
		
					echo "<input type='radio' name='nextposter' value='no_tag_wanted' checked required>Jeg ønsker ikke at angive den næste i tråden";				
					echo "</tr>";
					echo "</table>";
		
				}
				else
				{
					echo "<input type='hidden' name='nextposter' value='no_tag_wanted'/>";
				}
				
					echo "<table class='postingtable'>";
				//A warning can be added if it is the first post
				if($firstpost['post_ID'] == $post_id && $postforum['ingame'] == 1 && $postforum['official'] == 1)
				{
					
					echo "<tr><td class='bold'>Advarsel:</td>";
					if($topic_to_edit['warning'] == "")
					{
						echo "<td><input type='radio' name='warning' value='no' checked required/>Ingen ";
						echo "<input type='radio' name='warning' value='yes' required/>";
					}
					else
					{
						echo "<td><input type='radio' name='warning' value='no' required/>Ingen ";
						echo "<input type='radio' name='warning' value='yes' checked required/>";
					}						
					echo "Vælg: <select name='warningtype'>";
					if($topic_to_edit['warning'] == "Seksuelt indhold")
					{
						echo "<option value='Seksuelt indhold' selected>Seksuelt indhold</option>"; 
					} 
					else { echo "<option value='Seksuelt indhold'>Seksuelt indhold</option>"; }
					if($topic_to_edit['warning'] == "Voldsomt indhold")
					{
						echo "<option value='Voldsomt indhold' selected>Voldsomt indhold</option>"; 
					} 
					else { echo "<option value='Voldsomt indhold'>Voldsomt indhold</option>"; }
					if($topic_to_edit['warning'] == "Seksuelt og voldsomt indhold")
					{
						echo "<option value='Seksuelt og voldsomt indhold' selected>Seksuelt og voldsomt indhold</option>"; 
					} 
					else { echo "<option value='Seksuelt og voldsomt indhold'>Seksuelt og voldsomt indhold</option>"; }
					echo "</select>";
					echo "</td></tr>";
				}	
				else //if the user can't edit the topic warning, we still want to keep the old one
				{	
					if($topic_to_edit['warning'] == "") { echo "<input type='hidden' name='warning' value='no'/>";	}
					else
					{
						echo "<input type='hidden' name='warning' value='yes'/>";
						echo "<input type='hidden' name='warningtype' value='".$topic_to_edit['warning']."'/>";
					}
				}
				
				//Pinned topic, if the post is the first post and if the logged in user has the correct rank
				if(($user_rank > 1 || $forummod['res'] > 0) && $firstpost['post_ID'] == $post_id)
				{
					echo "<hr/>";
					echo "<tr><td class='bold'>Angiv som opslag: </td>";		
					if($topic_to_edit['pinned'] == 1) { echo "<td><input type='checkbox' name='pinned' value='yes' checked/>"; }
					else { echo "<td><input type='checkbox' name='pinned' value='yes' />"; }
					echo "(Emnet vises øverst på underforummets side sammen med andre opslag)</td>";			
					echo "</tr>";	
					if($user_rank > 1 && $postforum['ingame'] == 1)
					{
						echo "<tr><td class='bold'>Angiv som plottråd: </td><td>";	
						if($topic_to_edit['topictype'] == 'Plottråd') { echo "<input type='checkbox' name='plot' value='yes' checked/>"; }
						else { echo "<input type='checkbox' name='plot' value='yes' />"; }
						echo "(Dette overskriver et evt. valg om 'åben tråd')</td>";			
						echo "</tr>";
					}
					else
					{
						if($topic_to_edit['topictype'] == 'Plottråd') { echo "<input type='hidden' name='plot' value='yes'/>";	}
					}
				}
				else //if the user can't edit the pinned status, we still want to keep the old one
				{	
					if($topic_to_edit['pinned'] == 1) { echo "<input type='hidden' name='pinned' value='yes'/>";	}
				}
				
				
		
				if ($postforum['ingame'] == 1 && $postforum['official'] == 1)
				{
					echo "<tr><td class='bold'>Angiv som åben tråd: </td><td>";	
					if($topic_to_edit['topictype'] == 'Åben tråd') { echo "<input type='checkbox' name='open' value='yes' checked/>"; }
					else { echo "<input type='checkbox' name='open' value='yes' />"; }	
					echo "(Alle må deltage i tråden)</td>";			
					echo "</tr>";
				}
				else
				{
					if($topic_to_edit['topictype'] == 'Åben tråd') { echo "<input type='hidden' name='open' value='yes'/>";	}
				}
				
				
				//If the topic has a poll, the user should have the option to delete it
				$topic_has_poll = $forum->check_if_topic_has_poll($topic_to_edit['topic_ID'])->fetch_assoc();
				if($topic_has_poll['res'] > 0 && $firstpost['post_ID'] == $post_id)
				{
					echo "<tr><td class='bold'>Fjern afstemning: </td>";		
					echo "<td><input type='checkbox' name='deletepoll' value='yes' />";
					echo "(Dette vil fjerne hele afstemningen inklusive stemmer)</td>";			
					echo "</tr>";	
				}
				
				echo "</table>";
				
				// Making a poll
				echo "<div id='pollcreate'  style='display:none;'>";
				echo "<table class='postingtable'>";
				echo "<tr><td><span class='bold'>Afstemningsspørgsmål:</span></td>";
				echo "<td><input type='text' class='postinginput' name='pollquestion'></td></tr>";
				echo "<tr><td><span class='bold'>Afstemningsmuligheder:</span><br/><span class='smallertext'>Angiv hver på en ny linje</span></td>";
				echo "<td><textarea name='polloptions' class='postingpoll'></textarea></td></tr>";
				echo "<tr><td><span class='bold'>Flere afstemningsmuligheder</span>
				</br><span class='smallertext'>Gør det muligt at stemme på mere end én mulighed</span></td>";
				echo "<td><input type='checkbox' name='pollnumberofchoices'/> Ja</td></tr>";
				echo "</table>";
				echo "</div>";
				
				echo "<hr/>";
				echo "<table class='postingtable'>";
				echo "<tr><td colspan='2' class='center'>";
				if($postforum['ingame'] == 0 && $topic_has_poll['res'] < 1)
				{
					echo "<input class='submitbutton' type='button' value='Tilføj afstemning' onclick='showPoll()'> ";	
				}
				?>
                <script>
				function showPoll() {
					   document.getElementById('pollcreate').style.display = "block";
					}
				</script>
                
                <?php
				
				echo "<input class='submitbutton' type='submit' name='submit_edited_post' value='Udfør'></td>";	
		
				echo "</tr>";			
		
				
		
				echo "</form>";
		
				echo "</table>";	
				
				echo "<span class='errormsg'>".$errormsg."</span>";	
			
		}	
		
		echo "<hr/>";
		echo "<a href='viewtopic.php?t=".$topic_to_edit['topic_ID']."#".$post_to_edit['post_ID']."' class='backbtn'>Tilbage</a>";
		
	} //end post exists
	
}
else if(isset($_GET['delete']))
{
	$post_id = $_GET['delete'];
	$existingpost = $forum->post_exists($post_id)->fetch_assoc();
	
	if($existingpost['res'] > 0)
	{		
		$post_to_delete = $forum->get_post($post_id)->fetch_assoc();	
		$topic_to_delete = $forum->get_topic($post_to_delete['fk_topic_ID'])->fetch_assoc();
		$lastpost = $forum->get_last_post($topic_to_delete['topic_ID'])->fetch_assoc();
		$firstpost = $forum->get_first_post($topic_to_delete['topic_ID'])->fetch_assoc();
		$isfirstpost = false; if($firstpost['post_ID'] == $post_id) { $isfirstpost = true; }
		$forummod = $forum->forummod_exists($topic_to_delete['fk_forum_ID'], $user_logged_in_ID)->fetch_assoc();
		$postdatetime = $post_to_delete['datetime'];
		$prevpost = $forum->get_previous_post($postdatetime, $post_to_delete['fk_topic_ID'])->fetch_assoc();
		
		if($user_rank > 1 || $forummod['res'] > 0 || ($post_to_delete['fk_superuser_ID'] == $user_logged_in_ID && $lastpost['post_ID'] == $post_id))
		{
			$deletepost = $forum->delete_post($post_id);
			$numofposts = $forum->get_numberof_posts($topic_to_delete['topic_ID'])->fetch_assoc();
			
			if($numofposts['res'] == 0) //If it was the last post in the topic, delete the topic as well
			{
				$deletetopictags = $forum->delete_tags_from_topic($topic_to_delete['topic_ID']);
				$topic_has_poll = $forum->check_if_topic_has_poll($topic_to_delete['topic_ID'])->fetch_assoc();
				if($topic_has_poll['res'] > 0)
				{
					$topicpoll = $forum->get_poll_from_topic($topic_to_delete['topic_ID'])->fetch_assoc();
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
				$deletetopic = $forum->delete_topic($topic_to_delete['topic_ID']);
				header('Location:viewforum.php?f='.$topic_to_delete["fk_forum_ID"]);
				exit;	
			}
			else //update last posted datetime
			{
				$newlastpost = $forum->get_last_post($topic_to_delete['topic_ID'])->fetch_assoc();
				$forum->update_topic_lastpost_with_date($newlastpost['datetime'], $topic_to_delete['topic_ID']);
				echo $isfirstpost;
				if($isfirstpost)
				{
					$newfirstpost = $forum->get_first_post($topic_to_delete['topic_ID'])->fetch_assoc();
					$forum->update_topic_author($newfirstpost['fk_character_ID'], $newfirstpost['fk_superuser_ID'], $topic_to_delete['topic_ID']);
				}
			}
		}		
		
		header('Location:viewtopic.php?t='.$topic_to_delete["topic_ID"].'#'.$prevpost["post_ID"]);	
		
	} // End if post exists
	
	echo "<a href='viewtopic.php?t=".$topic_to_delete['topic_ID']."#".$post_id."' class='backbtn'>Tilbage</a>";
	
} // End delete post
?>

<?php
include('footer.php');
?>