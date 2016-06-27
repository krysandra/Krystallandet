<?php
include('header.php');
?>

<?php

//There is no access if you are not an admin
if(!isset($_SESSION['user']) || $user_rank != 2)
{
	header('Location:index.php');
}

else
{
		echo "<div id='acpwrap'>";
		echo "<div id='sidemenu'>";
		echo "<div class='category'><a href=''>Menu</a></div>";
		echo "<div id='sidemenu_content'>";
		echo "<a href='mcp.php'>Oversigt</a>";
		echo "<a href='mcp.php?mode=approval'>Godkendelser"; if ($characters_need_approval['res'] > 0) { echo " (".$characters_need_approval['res'].")"; } echo "</a>"; 
		echo "<a href='mcp.php?mode=groups'>Gruppeadministration</a>"; 
		echo "<a href='mcp.php?mode=users'>Brugeradministration</a>"; 
		echo "<a href='mcp.php?mode=activity'>Aktivitetstjek</a>"; 
		echo "<a href='mcp.php?mode=wantedlist'>Efterlysninger"; 
		if ($wantedposts_need_approval['res'] > 0) { echo " (".$wantedposts_need_approval['res'].")"; } echo "</a>"; 
		echo "<a href='mcp.php?mode=achievements'>Trofæer</a>";
		echo "<a href='mcp.php?mode=chat'>Chatbeskeder</a>";
		echo "</div></div>";

		if(empty($_GET))
		{
			//Overview & log
			echo "<div id='acp_page'>";
			echo "<div class='category'><a href=''>Oversigt</a></div>";
			echo "<div id='acp_content'>";
			
			$modlog = $forum->get_modlog_modonly();
			echo "<table>";
			echo "<tr><th>Handling</th><th>Bruger</th><th>Dato og tidspunkt</th></tr>";
			while($log = $modlog->fetch_assoc())
			{
				$superuser = $forum->get_superuser($log['fk_superuser_ID'])->fetch_assoc();				
				echo "<tr>";	
				echo "<td>".$log['logdata']."</td>";
				echo "<td class='center'><a class='username' style='color:".$superuser['color'].";' 
				href='memberprofile.php?id=".$superuser['superuser_ID']."'>".$superuser['name']."</a></td>";
				echo "<td class='center'>".date("j. M Y G:i", strtotime($log['datetime']))."</td>";
				echo "</tr>";
			}
			echo "</table>";
			echo "</div>";
			echo "</div>";
		}
		
		if($_GET['mode'] == 'approval')
		{
			
			echo "<div id='acp_page'>";
			echo "<div class='category'><a href=''>Karaktergodkendelse</a></div>";
			echo "<div id='acp_content' class='center'>";
			
			if($adminapproval < 1) { echo "Ingen karakterer venter i øjeblikket på godkendelse"; }
			else {
				$approvallist = $forum->get_approval_waitlist();
				echo "<table>";
				echo "<tr><th>Navn</th><th>Profilejer</th><th>Profiltekst</th><th>Godkendelse</th></tr>";
				while($request = $approvallist->fetch_assoc())
				{
					
					$char = $forum->get_character($request['fk_character_ID'])->fetch_assoc();
					$superuser = $forum->get_superuser($char['fk_superuser_ID'])->fetch_assoc();
					echo "<tr><td>".$char['name']."</td>";
					echo "<td><a class='username' style='color:".$superuser['color'].";' href='memberprofile.php?id=".$superuser['superuser_ID']."'>".$superuser['name']."</a></td>
					<td><a href='characterprofile.php?id=".$char['character_ID']."'>Læs profiltekst</a></td>
					<td><a href='mcp.php?approvechar=".$char['character_ID']."'>Besvar anmodning om godkendelse</td></tr>";	
				}
				echo "</table>";	
			}
			
			echo "</div>";
			echo "</div>";
		} // End approval mode
		
		if(isset($_GET['approvechar']))
		{
			//echo "<div id='acp_charapproval'>";

			$char_id = $_GET['approvechar'];
			$char = $forum->get_character($char_id)->fetch_assoc();
			$superuser = $forum->get_superuser($char['fk_superuser_ID'])->fetch_assoc();
			$groups = $forum->get_all_groups();
			
			echo "<div id='acp_page'>";
			echo "<div class='category'><a href=''>Godkendelse af ".$char['name']."</a></div>";
			echo "<div id='acp_charapproval'>";
			
			echo "<form method='post'>";
			echo "<input type='radio' name='approval_status' value='yes' required> Godkend<br/>";
  			echo "<input type='radio' name='approval_status' value='no' required> Afvis<br/>";
			
			$user_has_default_group = $forum->check_users_default_group($char_id)->fetch_assoc();
			if($user_has_default_group['res'] > 0)
			{
				$defaultgroup = $forum->get_users_default_group($char_id)->fetch_assoc(); 
				$defgroupdata = $forum->get_group($defaultgroup['fk_group_ID'])->fetch_assoc();
				echo "<br/>Gruppe: "; echo $defgroupdata['title']."<br/><br/>";
			}
			else
			{
				echo "Tilføj gruppe: <select name='group'>";			
				echo "<option value='0' selected>Ingen gruppe</option>";
				while($group = $groups->fetch_assoc())
				{
					echo "<option value='".$group['group_ID']."'>".$group['title']."</option>";	
				}
				echo "</select><br/><br/>";
			}
			echo "Kommentar/begrundelse:<br/>";
			echo "<textarea name='comment'></textarea>";
			echo "<input type='submit' name='submit_approval' value='Godkend/afvis karakter' />";
			echo "</form>";
			
			if($_POST['submit_approval'])
			{
				$waitingapproval = $forum->check_if_waiting_for_approval($char_id)->fetch_assoc();
				if($waitingapproval['res'] > 0)
				{
					$status = $_POST['approval_status'];
					$comment = htmlspecialchars($_POST['comment']);
					$group = $_POST['group'];					
				
					if($status == 'no')
					{					
						$title="Din karakter, ".$char['name'].", er blevet afvist";	
						$message="Hej ".$superuser['name']."\n\n Din karakter, ".$char['name'].", er desværre blevet afvist.\n\n Kommentar: \n <i>".$comment."</i>\n 
						Når du har rettet det nødvendige, kan du sende karakteren til godkendelse igen gennem dit brugerkontrolpanel.";
						$createmsg = $forum->create_new_message($user_logged_in_ID, $title, $message);
						$sendmsg = $forum->send_new_message($createmsg, $superuser['superuser_ID']);
						$remove_request = $forum->delete_approval_request($char['character_ID']);
						$log = $forum->insert_modlog("Karakteren ".$char['name']." blev afvist", $user_logged_in_ID, 0);
						header('Location:mcp.php?mode=approval');
					}
					if($status == 'yes')
					{					
						$title="Din karakter, ".$char['name'].", er blevet godkendt";	
						if($comment == "") { $comment = "Ingen kommentar fra admin";}
						$message="Hej ".$superuser['name']."\n\n Din karakter, ".$char['name'].", er blevet godkendt, og du kan nu deltage i rollespillet med den.
						\n\n Kommentar: \n <i>".$comment."</i>\n 
						Hvis du bruger et faceclaim til karakteren, så husk at skrive det på faceclaimlisten.";
						
						$createmsg = $forum->create_new_message($user_logged_in_ID, $title, $message);
						$sendmsg = $forum->send_new_message($createmsg, $superuser['superuser_ID']);
						$updatestatus = $forum->update_character_accepted_status($char['character_ID'], 1); 
						$remove_request = $forum->delete_approval_request($char['character_ID']);
						
						if($group != 0)
						{
							$groupdata = $forum->get_group($group)->fetch_assoc();
							$groupmemberexists = $forum->try_find_groupmember($group, $char['character_ID'])->fetch_assoc();
							if($groupmemberexists['res'] < 1)
							{
								$addgroupmember = $forum->add_new_groupmember($group, $char_id, $groupdata['fk_default_rank'], 1);
								$setcolor = $forum->update_character_color($groupdata['color'], $char_id);
							}
						}
						$log = $forum->insert_modlog("Karakteren ".$char['name']." blev godkendt", $user_logged_in_ID, 0);
						header('Location:mcp.php?mode=approval');
					}
				}
				else
				{
					echo "<span class='errormsg'>Karakteren er allerede blevet godkendt/afvist</span>";	
				}

				
			}	
			echo "</div>";
			echo "</div>";
			

		} // End approval mode
		
		if($_GET['mode'] == 'groups')
		{
			echo "<div id='acp_page'>";
			echo "<div class='category'><a href=''>Grupper</a></div>";
			echo "<div id='acp_content'>";
			
			echo "<table>";
			echo "<tr><th>Gruppenavn</th><th>Medlemmer</th><th>Handling</th></tr>";
			
			$groups = $forum->get_all_groups();
			while($group = $groups->fetch_assoc())
			{
				$membercount = $forum->count_groupmembers($group['group_ID'])->fetch_assoc();
				echo "<tr><td class='center'>".$group['title']."</td>";
				echo "<td class='center'>".$membercount['res']."</td>";
				echo "<td class='center'><a href='mcp.php?groupmembers=".$group['group_ID']."'>Tilføj/ret medlemmer</a></td>"; 
				echo "</tr>";	
			}
			
			echo "</table>";
			echo"<hr/>";
			
			
			echo "</div>";
			echo "</div>";
				
		} // End group mode
		
		if($_GET['groupmembers'])
		{
			$group_id = $_GET['groupmembers'];
			$group = $forum->get_group($group_id)->fetch_assoc();
			
			echo "<div id='acp_page'>";
			echo "<div class='category'><a href=''>Medlemmer: ".$group['title']."</a></div>";
			echo "<div id='acp_content' >";
			
			echo "<table>";
			echo "<tr><th class='left'>Gruppemedlemmer</th><th class='left'>Standardgruppe</th><th class='left'>Grupperang</th><th class='left'>Vælg</th></tr>";
			echo "<form method='post'>";			
			
			$membercount = $forum->count_groupmembers($group['group_ID'])->fetch_assoc();
			if ($membercount['res'] < 1 ) { echo "<td colspan='4'>Denne gruppe har ingen medlemmer</td>"; }
			
			$groupmembers = $forum->get_groupmembers($group_id);
			
			while($member = $groupmembers->fetch_assoc())
			{
				$character = $forum->get_character($member['fk_character_ID'])->fetch_assoc();
				echo "<tr>";
				echo "<td><a class='username' style='color:".$character['color'].";' 
				href='characterprofile.php?id=".$member['fk_character_ID']."'>".$character['name']."</a></td>";	
				if ($member['defaultgroup'] == 1) { echo "<td>Ja</td>";} else { echo "<td>Nej</td>";}
				
				$groupranks = $forum->get_groupranks($group_id);
				echo "<form method='post'>";
				echo "<td><select name='grouprank' class='smallerselect'>";
				while($rank = $groupranks->fetch_assoc())
				{
					if($rank['grouprank_ID'] == $member['fk_rank_ID']) 
					{
						echo "<option value='".$rank['grouprank_ID']."' selected>".$rank['title']."</option>";	
					}
					else
					{
						echo "<option value='".$rank['grouprank_ID']."'>".$rank['title']."</option>";	
					}
				}	
				echo "</select> ";
				echo "<input type='hidden' name='groupmember_ID' value='".$member['groupmember_ID']."'/>";
				echo "<input type='submit' name='change_member_grouprank' value='Udfør'/></form></td>";
				echo "<td><input type='checkbox' name='groupmember[]' value='".$member['groupmember_ID']."'></td></tr>";
			}
						
			echo "</table>";
			
			echo "<div id='newgroup'>";
			if ($membercount['res'] > 0 ) 
			{ echo "<br/><select name='change'><option value='defaultgroup'>Standardgruppe</option><option value='delete'>Fjern bruger fra gruppe</option></select>
			<input type='submit' name='submit_memberchanges' value='Udfør'/>"; }
			echo "</form>";
			echo "</div>";
			
			echo "<a href='mcp.php?mode=groups'>Tilbage</a>";
			
			echo "<hr/>";
			echo "<h3>Tilføj brugere</h3>";
			echo "<form method='post'>";
			echo "<input type='checkbox' name='defaultgroup' value='yes' checked> Sæt som standardgruppe<br><br>";
			echo "Grupperang: <select name='grouprank'>";
			$groupranks = $forum->get_groupranks($group_id);
			while($rank = $groupranks->fetch_assoc())
			{
				if($rank['grouprank_ID'] == $group['fk_default_rank']) 
				{
					echo "<option value='".$rank['grouprank_ID']."' selected>".$rank['title']."</option>";	
				}
				else
				{
					echo "<option value='".$rank['grouprank_ID']."'>".$rank['title']."</option>";	
				}
			}			
			echo "</select><br><br>";	
			echo "Angiv hvert brugernavn på en seperat linje:<br/>";
			echo "<textarea name='members'></textarea><br/>";
			echo "<input type='submit' name='submit_new_members' value='Tilføj brugere til gruppen'>";
			echo "</form>";
			
			echo "</div>";
			echo "</div>";
			
			if($_POST['change_member_grouprank'])
			{
				$groupmember = $_POST['groupmember_ID'];
				$grouprank = $_POST['grouprank'];
				
				$updaterank = $forum->update_groupmember_rank($grouprank, $groupmember);
				header('Location:mcp.php?groupmembers='.$group_id);
			}
			
			if($_POST['submit_memberchanges'])
			{
				$change = $_POST['change'];
				if(!empty($_POST['groupmember'])){
				// Loop to store and display values of individual checked checkbox.
				foreach($_POST['groupmember'] as $selected){
					    $groupmember = $forum->get_groupmember($selected)->fetch_assoc();
						$character = $forum->get_character($groupmember['fk_character_ID'])->fetch_assoc();
						if($change == 'defaultgroup')
						{
							//change users defaultgroup	
							$setdefault = $forum->update_groupmember_defaultgroup(1, $selected);
							$setcolor = $forum->update_character_color($group['color'], $character['character_ID']);
							$log = $forum->insert_modlog("Gruppen '".$group['title']."' blev sat som standardgruppe for karakteren ".$character['name'], $user_logged_in_ID, 0);
							header('Location:mcp.php?groupmembers='.$group_id);
						}
						if($change == 'delete')
						{
							//delete user from group
							$defaultgroup = $groupmember['defaultgroup'];
							if($defaultgroup == 1) { $setcolor = $forum->update_character_color("", $character['character_ID']); }
							
							$deletemember = $forum->delete_groupmember($selected);
							$log = $forum->insert_modlog($character['name']." blev fjernet fra gruppen '".$group['title']."'", $user_logged_in_ID, 0);
							header('Location:mcp.php?groupmembers='.$group_id);
						}
					}
				}
			}
			
			if($_POST['submit_new_members'])
			{
				$defaultgroup = 0;
				if(isset($_POST['defaultgroup'])) { $defaultgroup = 1; }
				$grouprank = $_POST['grouprank'];
				
				$newmembers = $_POST['members'];
				$arr = explode("\n", $newmembers);
				
				foreach($arr as $value)
				{
					$value = trim($value);
					$characterexists = $forum->try_find_character_by_name($value)->fetch_assoc();
					if($characterexists['res'] > 0)
					{
						$character = $forum->get_character_by_name($value)->fetch_assoc();
						$groupmemberexists = $forum->try_find_groupmember($group_id, $character['character_ID'])->fetch_assoc();
						if($groupmemberexists['res'] < 1)
						{
							$addgroupmember = $forum->add_new_groupmember($group_id, $character['character_ID'], $grouprank, $defaultgroup);
							if($defaultgroup == 1){ $setcolor = $forum->update_character_color($group['color'], $character['character_ID']); }
							$log = $forum->insert_modlog($character['name']." blev tilføjet gruppen '".$group['title']."'", $user_logged_in_ID, 0);
						}
					}
				}
				header('Location:mcp.php?groupmembers='.$group_id);
			}
			
		} // End group members
		
		//everything group related ends here
		
		if($_GET['mode'] == 'users')
		{
			echo "<div id='acp_page'>";
			echo "<div class='category'><a href=''>Brugeradministration</a></div>";
			echo "<div id='acp_content' class='center'>";
			
			echo "<form method='get'>";
			echo "Angiv brugernavn: ";
			echo "<input type='text' name='username' required/> ";
			echo "<input type='submit' name='edituser' value='Udfør' />";
			
			echo "</div>";
			echo "</div>";
		}
		
		if($_GET['username'] && $_GET['edituser'])
		{
			$username = $_GET['username'];
			$nameexists = $forum->check_for_existing_superuser_name($username)->fetch_assoc();
			$errormsgcore = "";
			$errormsgrank = "";
			$errormsgchar = "";
			
			if($nameexists['res'] > 0)
			{
				$superuser = $forum->get_superuser_by_name($username)->fetch_assoc();
				
				if($superuser['fk_role_ID'] > 1 && $superuser['superuser_ID'] != $user_logged_in_ID)
				{
					echo "<div id='acp_page'>";
					echo "<div class='category'><a href=''>".$username."</a></div>";
					echo "<div id='acp_content' class='center' >";
					echo "<span class='errormsg'>Du kan ikke redigere brugere, der har samme eller højere rang end dig selv!<br/><br/></span>";	
					echo "<a href='mcp.php?mode=users'>Tilbage</a>";	
				}
				else
				{
					//Updating superuser email & password
					if($_POST['submit_core'])
					{
						$error = false;
						
						$email = htmlspecialchars($_POST['email']);
						$pass = htmlspecialchars($_POST['password']);
						$confirm = htmlspecialchars($_POST['password_confirm']);	
						
						if($pass != $confirm) 
						{ 
							$error = true; $errormsgcore = $errormsgcore."Værdierne indtastet i \"kodeord\" og \" bekræft kodeord\" var ikke ens.<br/>";
						}	
						else if($pass != "" || $confirm != "")
						{
							$hash = password_hash($pass, PASSWORD_DEFAULT);						
							$updatepass = $forum->update_superuser_password($hash, $superuser['superuser_ID']);
							$log = $forum->insert_modlog("Password blev ændret for brugeren ".$superuser['name'], $user_logged_in_ID, 0);
						}
						
						if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
						{ 
							$error = true; $errormsgcore = $errormsgcore."Du skal indtaste en rigtig e-mail adressse.<br/>";
						}
						else
						{
							$updateemail = $forum->update_superuser_email($email, $superuser['superuser_ID']);
							$log = $forum->insert_modlog("Email blev ændret for brugeren ".$superuser['name'], $user_logged_in_ID, 0);
						}
								
						if($error == false)
						{
							header('Location:mcp.php?mode=users');
						}	
					}
					
					//form to update superuser email &password
					echo "<div id='acp_page'>";
					echo "<div class='category'><a href=''>".$username."</a></div>";
					echo "<div id='acp_content' >";
					
					echo "<span class='errormsg'>".$errormsgcore."</span>";	
					
					echo "<table>"; echo "<form method='post'>";
					echo "<tr><td>E-mailadresse: </td>";
					echo "<td><input type='text' name='email' value='".htmlspecialchars($superuser['email'], ENT_QUOTES, 'UTF-8')."' required/></tr>";
					echo "<tr><td>Nyt kodeord: </td>";
					echo "<td><input type='password' name='password' ></td></tr>";
					echo "<tr><td>Bekræft kodeord: </td>";
					echo "<td><input type='password' name='password_confirm' ></td></tr>";
					echo "<tr><td></td><td><input type='submit' name='submit_core' value='Gem ændringer'/></td>";
					echo "</post>"; echo "</table>";
					
					
					echo "<hr/>";
					
					//Updating a user's rank
					if($_POST['submit_new_role'])
					{
						$role = $_POST['role'];
						$rolename = $forum->get_role($role)->fetch_assoc();
						$updaterole = $forum->update_superuser_role($role, $superuser['superuser_ID']);	
						$log = $forum->insert_modlog("Brugeren ".$superuser['name']." fik ændret sin rolle til ".$rolename['name'], $user_logged_in_ID, 0);
						header('Location:mcp.php?username='.$superuser["name"].'&edituser=Udfør');
					}
					//Updating forummod status
					if($_POST['new_forummod'])
					{
						$forum_id = $_POST['forummod'];
						$existing_forummod = $forum->forummod_exists($forum_id, $superuser['superuser_ID'])->fetch_assoc();
						
						if($existing_forummod['res'] > 0)
						{
							$errormsgrank = "Brugeren er allerede moderator for det angivne forum";
						}
						else
						{
							$forumname = $forum->get_forum($forum_id)->fetch_assoc();
							$insertforummod = $forum->insert_new_forummod($forum_id, $superuser['superuser_ID']);
							$log = $forum->insert_modlog("Brugeren ".$superuser['name']." blev sat som forummoderator for ".$forumname['title'], $user_logged_in_ID, 0);
							header('Location:mcp.php?username='.$superuser["name"].'&edituser=Udfør');
						}	
						
					}
					//deleting forummod status
					if($_POST['delete_forummod'])
					{
						$forum_id = $_POST['forum_id'];
						$forumname = $forum->get_forum($forum_id)->fetch_assoc();	
						$deleteforummod = $forum->delete_forummod($forum_id, $superuser['superuser_ID']);
						$log = $forum->insert_modlog("Brugeren ".$superuser['name']." blev fjernet som forummoderator for ".$forumname['title'], $user_logged_in_ID, 0);
						header('Location:mcp.php?username='.$superuser["name"].'&edituser=Udfør');
					}
					
					echo "<h2>Brugerrang og -rettigheder</h2>";
					echo "<span class='errormsg'>".$errormsgrank."</span>";	
					
					$roles = $forum->get_all_roles();
					echo "<form method='post'>";
					echo "Brugerens rolle: "; echo "<select name='role'>";
					while ($role = $roles->fetch_assoc())
					{
						if ($role['role_ID'] == $superuser['fk_role_ID']) { echo "<option value='".$role['role_ID']."' selected>".$role['name']."</option>";	}
						else { echo "<option value='".$role['role_ID']."'>".$role['name']."</option>";	}
					}				
					echo "</select> "; 
					echo "<input type='submit' name='submit_new_role' value='Gem ændringer'/>"; 
					echo "</form>";
					
					//If a user isn't already a moderator or administrator, it can be moderator for a specific forum
					if($superuser['fk_role_ID'] < 2)
					{
						//A list of the forums the user is already moderating
						$forumsmodded = $forum->count_forums_where_user_is_forummod($superuser['superuser_ID'])->fetch_assoc();
						if($forumsmodded['res'] > 0)
						{
							$forummodlist = $forum->get_forums_where_user_is_forummod($superuser['superuser_ID']);
							
							echo "<h4>Denne bruger er forummoderator for følgende forums: </h3>";
							echo "<table>";
							
							while($forummod = $forummodlist->fetch_assoc())
							{
								$currentforum = $forum->get_forum($forummod['fk_forum_ID'])->fetch_assoc();	
								echo "<tr><td>".$currentforum['title']."</td>";
								echo "<form method='post'><input type='hidden' name='forum_id' value=".$currentforum['forum_ID']."/>";
								echo "<td><input type='submit' name='delete_forummod' value='Fjern moderatorstatus'/></td></tr>"; 
								echo "</form>";
							}
							
							echo "</table>";	
						}
						echo "<br/><br/>";
						echo "<form method='post'>";
						echo "Angiv som forummoderator for: ";
						$forumlist = $forum->get_all_forums();
						echo "<select name='forummod'>";
						while ($f = $forumlist->fetch_assoc())
							{
								echo "<option value='".$f['forum_ID']."'>".$f['title']."</option>";	
							}				
						echo "</select>"; 
						echo " <input type='submit' name='new_forummod' value='Udfør'/>";
						echo "</form>";
					
					} // end forummoderator
					
					echo "<hr/>";
					
					echo "<h2>Brugerens karakterer</h2>";				
					$numberofchars = $forum->count_all_characters_from_superuser($superuser['superuser_ID'])->fetch_assoc();
					if($numberofchars['res'] < 1) { echo "Denne bruger har endnu ingen karakterer"; }
					else 
					{
						echo "<form method='get'>";
						echo "Vælg karakter: "; echo "<select name='charedit'>";
						$usercharacters = $forum->get_all_characters_from_superuser($superuser['superuser_ID']);
						while ($char = $usercharacters->fetch_assoc())
						{
							echo "<option value='".$char['character_ID']."'>".$char['name']."</option>";	
						}				
						echo "</select> "; 
						echo "<input type='submit' value='Ret karakteroplysninger' name='submit_char' />";
						echo "</form>";
					}
							
					$numberOfAchievements = $forum->count_all_userachievements_from_user($superuser['superuser_ID'])->fetch_assoc();
					if($numberOfAchievements['res'] > 0)
					{
						echo "<hr/>";
						echo "<h2>Trofæer</h2>";	
						
						$userachievements = $forum->get_all_userachievements_from_user($superuser['superuser_ID']);
						echo "<table>";
						echo "<tr class='left'><th>Navn</th><th>Type</th><th>Karakter</th><th>Handling</th></tr>";
						while($achi = $userachievements->fetch_assoc())
						{
							echo "<tr>";
							echo "<td>".$achi['title']."</td>";
							if($achi['type'] == 1) { echo "<td>Offgame</td>"; }
							if($achi['type'] == 2) { echo "<td>Plot</td>"; }
							if($achi['type'] == 3) { echo "<td>Stedtilknyttet</td>"; }
							if($achi['type'] == 4) { echo "<td>Gruppe</td>"; }
							if($achi['type'] > 1) 
							{
								$characterwithachievement = $forum->get_character($achi['fk_character_ID'])->fetch_assoc();	
								echo "<td>".$characterwithachievement['name']."</td>";
							}
							else
							{
								echo "<td>Ingen karakter</td>";	
							}
							echo "<td><form method='post'><input type='hidden' name='userachievement_ID' value='".$achi['userachievement_ID']."'/>
							<input type='submit' name='delete_userachievement' value='Fjern'"; ?>
							onclick='return confirm("Er du sikker på, at du vil fjerne dette trofæ fra brugeren?")'
							<?php echo "/></form></td>";
							echo "</tr>";	
						}
						echo "</table>";
						
					}
					
					if($_POST['delete_userachievement'])
					{
						$userachievement_ID = $_POST['userachievement_ID'];	
						$deleteuserachievement = $forum->delete_userachievement($userachievement_ID);
						$log = $forum->insert_modlog("Trofæ blev fjernet fra brugeren ".$superuser['name'], $user_logged_in_ID, 0);
						header('Location:mcp.php?username='.$superuser["name"].'&edituser=Udfør');
					}
					
					echo "<hr/>";
					echo "<a href='mcp.php?mode=users'>Tilbage</a>";
				 } //end access to edit user
				
				}
				else
				{
					echo "<div id='acp_page'>";
					echo "<div class='category'><a href=''>Brugeradministration</a></div>";
					echo "<div id='acp_content' >";
					echo "Systemet fandt ingen bruger med det angivne navn.<br/><br/>";
					echo "<a href='mcp.php?mode=users'>Tilbage</a>";	
				}
				
				echo "</div></div>";
				
			} // End edit user
			
			if($_GET['charedit']) 
			{
				$char_id = $_GET['charedit'];	
				$char_exists = $forum->check_for_existing_character($char_id)->fetch_assoc();

				if($char_exists['res'] > 0)
				{
					$character = $forum->get_character($char_id)->fetch_assoc();
					$superuser = $forum->get_superuser($character['fk_superuser_ID'])->fetch_assoc();
					
					if($superuser['fk_role_ID'] > 1 && $superuser['superuser_ID'] != $user_logged_in_ID)
					{
						echo "<div id='acp_page'>";
						echo "<div class='category'><a href=''>".$character['name']."</a></div>";
						echo "<div id='acp_content' class='center' >";
						echo "<span class='errormsg'>Du kan ikke redigere brugere, der har samme eller højere rang end dig selv!<br/><br/></span>";	
						echo "<a href='mcp.php?mode=users'>Tilbage</a>";	
					}
					else
					{
						
						echo "<div id='acp_page'>";
						echo "<div class='category'><a href=''>Karakteradministration</a></div>";
						echo "<div id='acp_content' >";
							
						echo "<h3>".$character['name']."</h2>";
						
						echo "<table>";	
						echo "<form method='post'>";			
						echo "<tr><td>Antal tilladte færdighedspoints: </td>";
						echo "<td><input type='number' name='skillpoints' value='".$character['maxskill']."' min='50' required/></td></tr>";
						echo "<tr><td>Speciel farve: </td>";
						if($character['color'] == "")
						{
							echo "<td><input type='radio' name='color' value='yes' /> <input type='color' name='charcolor' /><br/>";
							echo "<input type='radio' name='color' value='no' checked/>";
						}
						else
						{
							echo "<td><input type='radio' name='color' value='yes' checked/> <input type='color' name='charcolor' value='".$character['color']."'/><br/>";
							echo "<input type='radio' name='color' value='no'/>";
						}				
						echo "Ingen farve </td></tr>";
						echo "<tr><td colspan='2'><input type='submit' value='Gem ændringer' name='submit_char' /></td></tr>";
						echo "</form>";
						echo "</table>";
						
						
						echo "<hr/>";
						
						echo "<div id='buttonarea'>";
						
						
						if($character['active'] == 1)
						{
							echo "<form method='post'><input type='submit' value='Angiv som inaktiv' name='submit_charinactive'/></form>";
						}
						else
						{
							echo "<form method='post'><input type='submit' value='Angiv som aktiv' name='submit_charactive'/></form>";	
						}
						
						if($character['dead'] == 0)
						{
							echo "<form method='post'><input type='submit' value='Angiv som død' name='submit_chardead'"; ?>
							onclick='return confirm("Er du sikker på, at du vil angive karakteren som værende død? Det vil ikke længere være muligt at skrive med den.")'
							<?php echo "/></form>";				
						}
						else
						{
							echo "<form method='post'><input type='submit' value='Genopliv karakter' name='submit_charalive'/></form>";
						}
						
						echo "<br/<br/>";
						
						echo "<form method='post'><input type='submit' value='Slet karakteren' name='submit_chardelete'"; ?>
						onclick='return confirm("Er du sikker på, at du vil slette denne karakter?")'
						<?php echo "/><br/>
						<input type='checkbox' name='postsdelete' value='yes' /> Slet karakterens posts </form>";
										
						echo "</div>";				
										
						if($_POST['submit_char'])
						{
							$color = "";
							if($_POST['color'] == "yes") { $color = $_POST['charcolor']; }
							$maxskill = $_POST['skillpoints'];
							
							$updatecolor = $forum->update_character_color($color, $character['character_ID']);
							$updateskills = $forum->update_character_maxskill($maxskill, $character['character_ID']);
							$log = $forum->insert_modlog("Indstillinger blev opdateret for karakteren ".$character['name'], $user_logged_in_ID, 0);
							
							header('Location:mcp.php?charedit='.$character["character_ID"]);
						}
						
						if($_POST['submit_charinactive'])
						{
							if($character['accepted'] == 1) //A non-accepted character must never be inactive
							{
								$updatestatus = $forum->update_character_active_status($character['character_ID'], 0);
								$setcolor = $forum->update_character_color($inactivecolor, $character['character_ID']);
								$log = $forum->insert_modlog("Karakteren ".$character['name']." blev angivet som inaktiv", $user_logged_in_ID, 0);
							}
							header('Location:mcp.php?charedit='.$character["character_ID"]);
						}
						
						if($_POST['submit_charactive'])
						{
							if($character['dead'] == 0) //A dead character must never be active
							{
								$updatestatus = $forum->update_character_active_status($character['character_ID'], 1);
								
								//If the char has a default group, we need to change its color to the group color.
								$user_has_default_group = $forum->check_users_default_group($character['character_ID'])->fetch_assoc();
								if($user_has_default_group['res'] > 0)
								{
									$defaultgroup = $forum->get_users_default_group($character['character_ID'])->fetch_assoc(); 
									$defgroupdata = $forum->get_group($defaultgroup['fk_group_ID'])->fetch_assoc();
									$setcolor = $forum->update_character_color($defgroupdata['color'], $character['character_ID']);
								}
								//Otherwise, no color.
								else
								{
									$setcolor = $forum->update_character_color("", $character['character_ID']);
								}
								
								$log = $forum->insert_modlog("Karakteren ".$character['name']." blev angivet som aktiv", $user_logged_in_ID, 0);
							}
							header('Location:mcp.php?charedit='.$character["character_ID"]);
							
						}
						
						if($_POST['submit_chardead'])
						{
							if($character['accepted'] == 1) //A non-accepted character must never be dead
							{
								//A dead character must also be inactive
								$updateactivestatus = $forum->update_character_active_status($character['character_ID'], 0);
								$updatedeadstatus = $forum->update_character_dead_status($character['character_ID'], 1);
								$setcolor = $forum->update_character_color($deadcolor, $character['character_ID']);
								$log = $forum->insert_modlog("Karakteren ".$character['name']." blev angivet som død", $user_logged_in_ID, 0);
								header('Location:mcp.php?charedit='.$character["character_ID"]);
							}
						}
						
						if($_POST['submit_charalive'])
						{
							$updateactivestatus = $forum->update_character_active_status($character['character_ID'], 1);
							$updatedeadstatus = $forum->update_character_dead_status($character['character_ID'], 0);
							
							//If the char has a default group, we need to change its color to the group color.
							$user_has_default_group = $forum->check_users_default_group($character['character_ID'])->fetch_assoc();
							if($user_has_default_group['res'] > 0)
							{
								$defaultgroup = $forum->get_users_default_group($character['character_ID'])->fetch_assoc(); 
								$defgroupdata = $forum->get_group($defaultgroup['fk_group_ID'])->fetch_assoc();
								$setcolor = $forum->update_character_color($defgroupdata['color'], $character['character_ID']);
							}
							//Otherwise, no color.
							else
							{
								$setcolor = $forum->update_character_color("", $character['character_ID']);
							}
							
							$log = $forum->insert_modlog("Karakteren ".$character['name']." blev angivet som ikke længere død", $user_logged_in_ID, 0);
							header('Location:mcp.php?charedit='.$character["character_ID"]);
						}
						
						
						if($_POST['submit_chardelete'])
						{
							$deletecharapprovalrequests = $forum->delete_approvalrequests_from_char($character['character_ID']);
							$deletechargroupmemberships = $forum->delete_groupmemberships_from_char($character['character_ID']);
							$deletetags = $forum->delete_tags_from_char($character['character_ID']);
							$deleteprofiledata = $forum->delete_profiledata_from_char($character['character_ID']); 
							$deleteachievements = $forum->delete_userachievements_from_character($character['character_ID']);
							$deletebounties = $forum->remove_bounty_from_character($character['character_ID']);
							if($_POST['postsdelete'] == "yes") 
								{ 
									//delete all posts from character
									$deleteallposts = $forum->delete_all_posts_from_char($character['character_ID']); 
									//get all topics where author is character
									$chartopics = $forum->get_topics_from_character($character['character_ID']);
									while($topic = $chartopics->fetch_assoc())
									{
										$replacechar = 0;
										$topicposts = $forum->get_all_posts($topic['topic_ID']);
										while($post = $topicposts->fetch_assoc())
										{
											//foreach user who answered the topic, check if id == character to delete. If not replacechar = the char id								
											if($post['fk_character_ID'] != $character['character_ID'])
											{
												$replacechar = $post['fk_character_ID'];	
												break;
											}
										}
										//If replacechar is 0 delete the topic, because no other characters has answered it
										if($replacechar == 0)
										{
											$deletetopic = $forum->delete_topic($topic['topic_ID']);
										}
										//If the topic is not to be deleted, set each the character_ID of the topic to the replacechar ID
										else
										{
											$replacechar_info = $forum->get_character($replacechar)->fetch_assoc();
											$updateauthor = $forum->update_ingame_topic_author($replacechar, $replacechar_info['fk_superuser_ID'], $topic['topic_ID']);
										}
										
									}
																					
								}
							else
							{
								$updatepostsauthor = $forum->update_ingame_posts_author_to_guest($character['character_ID']);
							}
								
							//Finally delete char
							$deletechar = $forum->delete_character($character['character_ID']);
							$log = $forum->insert_modlog("Karakteren ".$character['name']." blev slettet.", $user_logged_in_ID, 0);
							header('Location:mcp.php?username='.$superuser["name"].'&edituser=Udfør');
							
						} // end char deletions
						
						
						echo "<hr/>";
						echo "<br/><br/><a href='mcp.php?username=".$superuser['name']."&edituser=Udfør'>Tilbage</a>";	
					} // End user is allowed to edit char
			} 
			else
			{
				echo "Karakteren kunne ikke findes";	
			}
						
			echo "</div>";
			echo "</div>";
			
		}
		
		if($_GET['mode'] == 'activity')
		{
			$confirmmsg = "";
			echo "<div id='acp_page'>";
			echo "<div class='category'><a href=''>Aktivitetstjek</a></div>";
			echo "<div id='acp_content' class='center'>";
			
			if($_POST['submit_activitycheck'])
			{
				$characters = $forum->get_all_accepted_active_characters_simple();
				$lastyear = date("Y",strtotime("-1 year"));
				$firstofjanuarylastyear = date("Y-m-d H:i:s", mktime(0,0,0,1,1,$lastyear));
				
				$charactersaffected = 0;
				
				while($char = $characters->fetch_assoc())
				{
					//First checking whether the character has actually posted anything ingame
					$postcount = $forum->count_ingame_posts_from_character($char['character_ID'])->fetch_assoc();
					if($postcount['res'] > 0)
					{
							//Getting the last post from the character
							$lastpost = $forum->get_newest_post_from_character($char['character_ID'])->fetch_assoc();
							//If the last post was before the first of january last year, the user should be set to inactive
							if($lastpost['datetime'] < $firstofjanuarylastyear) 
							{
								if($char['accepted'] == 1) //A non-accepted character must never be inactive
								{
									$updatestatus = $forum->update_character_active_status($char['character_ID'], 0);
									$setcolor = $forum->update_character_color($inactivecolor, $char['character_ID']);
									$charactersaffected++;
								} 
							}					
					}
					//If the character hasn't been posting anything, we set it to inactive, if the registration date is < $firstofjanuarylastyear
					else
					{
						if($char['date_created'] < $firstofjanuarylastyear) 
							{
								if($char['accepted'] == 1) //A non-accepted character must never be inactive
								{
									$updatestatus = $forum->update_character_active_status($char['character_ID'], 0);
									$setcolor = $forum->update_character_color($inactivecolor, $char['character_ID']);
									$charactersaffected++;
								} 
							}	
					}
					
				}
				$log = $forum->insert_modlog("Et aktivitetstjek blev udført, hvor ".$charactersaffected." karakter(er) blev sat som inaktive", $user_logged_in_ID, 0);
				$confirmmsg = "Aktivitetstjekket blev udført. ".$charactersaffected." karakter(er) blev sat som inaktive";
			}
			
			echo "<form method='post'>";
			echo "<input type='submit' class='bigsubmit' name='submit_activitycheck' value='Udfør aktivitetstjek'/>";
			echo "</form>";
			
			echo "<p>".$confirmmsg."</p>";
			
			echo "<p class='smalltext'>Dette vil sætte alle karakterer, 
			der ikke har deltaget i en ingame tråd siden den 1. januar ".date("Y",strtotime("-1 year"))." kl. 0:00 som inaktive.</p>";
			
			echo "</div></div>";
			
		} // End activity check
		
		if($_GET['mode'] == 'wantedlist')
		{
			echo "<div id='acp_page'>";
			echo "<div class='category'><a href=''>Efterlyste Karakterer</a></div>";
			echo "<div id='acp_content' class='center'>";
						
			if(!isset($_GET['edit']) && !isset($_GET['delete']))
			{
				$nonaccepted = $forum->count_nonaccepted_wantedposts()->fetch_assoc();
				if($nonaccepted['res'] > 0)
				{
					$nonacceptedlist = $forum->get_nonaccepted_wantedposts();
					echo "<table class='acp_table'>";
					echo "<tr><th>Navn</th><th>Dusør</th><th></th><th></th></tr>";
					while($wantedpost = $nonacceptedlist->fetch_assoc())
					{
						$character = $forum->get_character($wantedpost['fk_character_ID'])->fetch_assoc();
						echo "<tr><td><a href='characterprofile.php?id=".$character['character_ID']."' class='username' style='color:".$character['color'].";'>
						".$character['name']."</a></td>";
						echo "<td>".$wantedpost['bounty']."</td>";
						echo "<td><a href='mcp.php?mode=wantedlist&edit=".$wantedpost['wanted_ID']."' class='bold'>Godkend</a></td>";
						echo "<td><a href='mcp.php?mode=wantedlist&delete=".$wantedpost['wanted_ID']."' class='bold'"; ?> 
						onclick='return confirm("Er du sikker på, at du vil slette denne efterlysning?")'>Slet</a></td>
						<?php echo "</tr>";	
					}
					echo "</table>";
				}
				
				$accepted = $forum->get_wantedlist_size()->fetch_assoc();
				if($accepted['res'] > 0)
				{
					$wantedlist = $forum->get_wantedlist();
					echo "<table class='acp_table'>";
					echo "<tr><th>Navn</th><th>Dusør</th><th></th><th></th></tr>";
					while($wantedpost = $wantedlist->fetch_assoc())
					{
						$character = $forum->get_character($wantedpost['fk_character_ID'])->fetch_assoc();
						echo "<tr><td><a href='characterprofile.php?id=".$character['character_ID']."' class='username' style='color:".$character['color'].";'>
						".$character['name']."</a></td>";
						echo "<td>".$wantedpost['bounty']."</td>";
						echo "<td><a href='mcp.php?mode=wantedlist&edit=".$wantedpost['wanted_ID']."' class='bold'>Redigér</a></td>";
						echo "<td><a href='mcp.php?mode=wantedlist&delete=".$wantedpost['wanted_ID']."' class='bold'>Slet</a></td>"; 
					}
					echo "</table>";
				}
				
			}
			if(isset($_GET['edit']))
			{
				$postid = $_GET['edit'];
				$wantedpost = $forum->get_wantedpost($postid)->fetch_assoc();
				$character = $forum->get_character($wantedpost['fk_character_ID'])->fetch_assoc();
				
				if($_POST['submit_edited_wantedpost'])
				{
					$crime = $_POST['crime'];
					$features = $_POST['features'];
					$whereabouts = $_POST['whereabouts'];
					$bounty = $_POST['bounty'];

					$editbounty = $forum->edit_bounty($postid, $crime, $features, $whereabouts, $bounty);
					$log = $forum->insert_modlog("Karakteren ".$character['name']." er nu efterlyst", $user_logged_in_ID, 0);
					header('Location:mcp.php?mode=wantedlist');
				}
				
				echo "<div id='newwantedpost'>";
				echo "<table>";
				echo "<form id='postform' method='post'>";
				echo "<tr><td class='bold'>Karakter: "; echo "</td></tr>";
				echo "<tr><td><a href='characterprofile.php?id=".$wantedpost['fk_character_ID']."' class='username' style='color:".$character['color'].";'>".$character['name']."</a>";
				echo "</td></tr>";
				echo "<tr><td class='bold'>Forbrydelse:</td></tr>";
				echo "<tr><td><textarea name='crime' required>".htmlspecialchars($wantedpost['crime'], ENT_QUOTES, 'UTF-8')."</textarea></td></tr>";
				echo "<tr><td class='bold'>Kendetegn:</td></tr>";
				echo "<tr><td ><textarea name='features' required>".htmlspecialchars($wantedpost['features'], ENT_QUOTES, 'UTF-8')."</textarea></td></tr>";
				echo "<tr><td class='bold'>Opholdssted:</td></tr>";
				echo "<tr><td><input type='text' name='whereabouts' value='".htmlspecialchars($wantedpost['whereabouts'], ENT_QUOTES, 'UTF-8')."' required/></td></tr>";
				echo "<tr><td class='bold'>Dusør:</td></tr>";
				echo "<tr><td><input type='text' name='bounty' value='".htmlspecialchars($wantedpost['bounty'], ENT_QUOTES, 'UTF-8')."' required/></td></tr>";
				echo "<tr><td class='center'><input type='submit' name='submit_edited_wantedpost' value='Godkend/Gem ændringer'></td></tr>";
				
				echo "</form>";
				echo "</table>";
				echo "<span class='errormsg'>".$errormsg."</span>";
				
			}
			if(isset($_GET['delete']))
			{
				$postid = $_GET['delete'];
				$wantedpost = $forum->get_wantedpost($postid)->fetch_assoc();
				$character = $forum->get_character($wantedpost['fk_character_ID'])->fetch_assoc();
				
				$deletedbounty = $forum->remove_bounty($postid);
				$log = $forum->insert_modlog("Karakteren ".$character['name']." er ikke længere efterlyst", $user_logged_in_ID, 0);
				header('Location:mcp.php?mode=wantedlist');
			}
			
			echo "</div></div>";
		}
		
		if($_GET['mode'] == 'achievements')
		{
			echo "<div id='acp_page'>";		
			if(!isset($_GET['give']) && !isset($_GET['new']) && !isset($_GET['edit']))
			{
				echo "<div class='category'><a href=''>Trofæadministration</a></div>";
				echo "<div id='acp_content' class='center'>";
				echo "<div class='forumbutton'> <a href='mcp.php?mode=achievements&give'>Tildel trofæ til bruger</a></div>";
			}
				
			if(isset($_GET['give']))
			{
				echo "<div class='category'><a href=''>Tildel trofæ til bruger</a></div>";
				echo "<div id='acp_content'>";
				$errormsg = "";
				
				if($_POST['add_achievement_to_user'])
				{
					$achievement = $_POST['achievement'];	
					$userid = $_POST['userid'];	
					$charid = $_POST['charid'];
					
					$achievementdata = $forum->get_achievement($achievement)->fetch_assoc();
					
					if($charid == "" ) { $charid = 0; }
					
					//If achievement type is not offgame, we need a character
					if(($achievementdata['type'] > 1 && $charid != 0) || $achievementdata['type'] == 1)
					{
						//See if the superuser exists
						$superuserexists = $forum->check_for_existing_superuser($userid)->fetch_assoc();
						if($superuserexists['res'] > 0)
						{
							$charexists = $forum->check_for_existing_character($charid)->fetch_assoc();
							if($charexists['res'] > 0 || $charid == 0)
							{
								if($charid != 0)
								{
									$chardata = $forum->get_character($charid)->fetch_assoc();
									$charsuperuser = $chardata['fk_superuser_ID'];
								}
								else
								{
									$charsuperuser = $userid;	
								}
								
								if($charsuperuser == $userid)
								{
									$userhasachievement = $forum->check_if_user_has_achievement($userid, 0, $achievement)->fetch_assoc();
									$charhasachievement = $forum->check_if_user_has_achievement($userid, $charid, $achievement)->fetch_assoc();	
									
									if(($charhasachievement['res'] < 1 && $userhasachievement['res'] < 1) || ($charhasachievement['res'] < 1 && $achievementdata['type'] > 1))
									{
										$addachievement = $forum->add_achievement_to_user($userid, $charid, $achievement);
										$superuser = $forum->get_superuser($userid)->fetch_assoc();
										$achievementdata = $forum->get_achievement($achievement)->fetch_assoc();
										$log = $forum->insert_modlog("Trofæet ".$achievementdata['title']." blev givet til brugeren ".$superuser['name'], $user_logged_in_ID, 0);
										header('Location:mcp.php?mode=achievements');
									}
									else
									{
										$errormsg = "Brugeren har allerede dette trofæ";	
									}
								}
								else
								{
									$errormsg = "Den angivede karakter hører ikke til den angivede bruger";	
								}
							
							}
							else
							{
								$errormsg = "Der eksisterer ikke en karakter med det angivede karakter-ID";	
							}
						}
						else
						{
							$errormsg = "Der eksisterer ikke en bruger med det angivede bruger-ID";	
						}
					}
					else
					{
						$errormsg = "In-game trofæer skal tildeles en specifik karakter";	
					}
				}
				

				echo "<table>";
				echo "<form method='post'>";
				echo "<tr><td>Trofæ: </td>"; 
				echo "<td><select name='achievement'/>";
				$achievements = $forum->get_all_achievements();
				while($achi = $achievements->fetch_assoc())
				{
					echo "<option value='".$achi['achievement_ID']."'>".$achi['title']."</option>";
				}
				echo "</select></td></tr>";
				echo "<tr><td>Bruger-ID:<br/></td>"; echo "<td><input class='numberinput_small' type='number' name='userid' required/></td></tr>";
				echo "<tr><td>Karakter-ID:<br/></td>"; echo "<td><input class='numberinput_small' type='number' name='charid'/></td></tr>";
				echo "<tr><td colspan='2'><input type='submit' name='add_achievement_to_user' value='Tilføj'/></td></tr>";
				
				echo "</form>";
				echo "</table>";
				
				echo "<span class='errormsg'>".$errormsg."</span>";	
				
				echo "<hr/>";
				echo "<a href='mcp.php?mode=achievements'>Tilbage</a>";
				
			}
						
			echo "</div></div>";
			
		} // End achievements
		
		if($_GET['mode'] == 'chat')
		{
			if($_POST['delete_chatmsg'])
			{
				$msgid = $_POST['msgid'];
				$forum->delete_chat_message($msgid);
				header('Location:mcp.php?mode=chat');
			}
			if($_POST['delete_icchatmsg'])
			{
				$msgid = $_POST['msgid'];
				$forum->delete_ic_chat_message($msgid);
				header('Location:mcp.php?mode=chat');
			}
			
			
			$confirmmsg = "";
			echo "<div id='acp_page'>";
			echo "<div class='category'><a href=''>Chatbeskeder</a></div>";
			echo "<div id='acp_content' class='center'>";
			
			$chatdata = $forum->get_chat_messages();
			echo "<h4>20 seneste chatbox-beskeder:</h4>";
			echo "<table>";
			while($chatmsg = $chatdata->fetch_assoc())
			{
				echo "<tr><td style='border:1px solid #c5c5c5; padding: 5px;'>";
				echo $chatmsg['message'];
				echo "</td>";
				echo "<form method='post'>";
				echo "<input type='hidden' name='msgid' value='".$chatmsg['chat_ID']."'/>";
				echo "<td style='border:1px solid #c5c5c5; padding: 5px;'><input type='submit' name='delete_chatmsg' value='Slet chatbesked' "; ?>
						onclick='return confirm("Er du sikker på, at du vil fjerne denne besked?")'
						<?php echo "/></td>";
				echo "</form>";
				echo "</tr>";
			}
			echo "</table>";
			
			echo "<hr/>";
			
			$chatdata = $forum->get_ic_chat_messages();
			echo "<h4>20 seneste IC-chatbox-beskeder:</h4>";
			echo "<table>";
			while($chatmsg = $chatdata->fetch_assoc())
			{
				echo "<tr><td style='border:1px solid #c5c5c5; padding: 5px;'>";
				echo $chatmsg['message'];
				echo "</td>";
				echo "<form method='post'>";
				echo "<input type='hidden' name='msgid' value='".$chatmsg['icchat_ID']."'/>";
				echo "<td style='border:1px solid #c5c5c5; padding: 5px;'><input type='submit' name='delete_icchatmsg' value='Slet chatbesked' "; ?>
						onclick='return confirm("Er du sikker på, at du vil fjerne denne besked?")'
						<?php echo "/></td>";
				echo "</form>";
				echo "</tr>";
			}
			echo "</table>";
			
			echo "</div></div>";
			
		}

	echo "</div>"; //acp-wrap

}
?>

<?php
include('footer.php');
?>