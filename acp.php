<?php
include('header.php');
?>

<?php

//There is no access if you are not an admin
if(!isset($_SESSION['user']) || $user_rank < 3)
{
	header('Location:index.php');
}

else
{
		echo "<div id='acpwrap'>";
		echo "<div id='sidemenu'>";
		echo "<div class='category'><a href=''>Menu</a></div>";
		echo "<div id='sidemenu_content'>";
		echo "<a href='acp.php'>Oversigt</a>";
		echo "<a href='acp.php?mode=approval'>Godkendelser"; if ($characters_need_approval['res'] > 0) { echo " (".$characters_need_approval['res'].")"; } echo "</a>"; 
		echo "<a href='acp.php?mode=groups'>Gruppeadministration</a>"; 
		echo "<a href='acp.php?mode=users'>Brugeradministration</a>"; 
		echo "<a href='acp.php?mode=forums'>Forumadministration</a>"; 
		echo "<a href='acp.php?mode=activity'>Aktivitetstjek</a>"; 
		echo "<a href='acp.php?mode=wantedlist'>Efterlysninger"; 
		if ($wantedposts_need_approval['res'] > 0) { echo " (".$wantedposts_need_approval['res'].")"; } echo "</a>"; 
		echo "<a href='acp.php?mode=achievements'>Trofæer</a>";
		echo "<a href='acp.php?mode=chat'>Chatbeskeder</a>";
		echo "</div></div>";

		if(empty($_GET))
		{
			//Overview & log
			echo "<div id='acp_page'>";
			echo "<div class='category'><a href=''>Oversigt</a></div>";
			echo "<div id='acp_content'>";
			
			$modlog = $forum->get_modlog();
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
					<td><a href='acp.php?approvechar=".$char['character_ID']."'>Besvar anmodning om godkendelse</td></tr>";	
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
						header('Location:acp.php?mode=approval');
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
						header('Location:acp.php?mode=approval');
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
				echo "<tr><td>".$group['title']."</td>";
				echo "<td class='center'>".$membercount['res']."</td>";
				echo "<td class='center'><a href='acp.php?editgroup=".$group['group_ID']."'>Opsætning</a>";
				echo " - <a href='acp.php?groupranks=".$group['group_ID']."'>Rangordner</a>";
				echo " - <a href='acp.php?groupmembers=".$group['group_ID']."'>Medlemmer</a>"; 
				echo " - <a href='acp.php?deletegroup=".$group['group_ID']."'"; ?> onclick='return confirm("Er du sikker på, at du vil slette denne gruppe?")'>Slet</td>
				<?php echo "</tr>";	
			}
			
			echo "</table>";
			
			echo "<hr/>";
			
			echo "<div id='newgroup'>";
			echo "<form method='get'>";
			echo "Opret ny gruppe: ";
			echo "<input type='text' name='groupname' required />";
			echo "<input type='submit' name='newgroup' value='Opret' />";
			echo "</form>";
			echo "</div>";
			
			echo "</div>";
			echo "</div>";
				
		} // End group mode
		
		if($_GET['newgroup'])
		{
			
			echo "<div id='acp_page'>";
			echo "<div class='category'><a href=''>Ny gruppe</a></div>";
			echo "<div id='acp_content' >";

			
			$groupname = $_GET['groupname'];
			
			echo "<table>";
			echo "<form method='post'>";
			echo "<tr><td>Gruppenavn: </td><td><input type='text' name='title' value='".$groupname."' required/></td></tr>";
			echo "<tr><td colspan='2'>Beskrivelse:</td></tr>";
			echo "<tr><td colspan='2'><textarea name='description'></textarea></td></tr>";
			echo "<tr><td>Gruppefarve: </td><td><input type='color' name='color' value='' required/></td></tr>";
			echo "<tr><td>Standard grupperang: </td><td><input type='text' name='defaultrank' value='' required/></td></tr>";	
			echo "<tr><td>Autojoin: </td><td><input type='checkbox' name='autojoin' value='yes'>Tillad brugere automatisk at blive medlem af gruppen</td></tr>";	
			echo "<tr><td colspan='2'><input type='submit' name='submit_newgroup' value='Opret gruppe' /></td></tr>";
			echo "</form>";
			echo "</table>";
			
			echo "</div>";
			echo "</div>";
			
			
			if($_POST['submit_newgroup'])
			{
				$title = htmlspecialchars($_POST["title"]);	
				$description = htmlspecialchars($_POST["description"]);	
				$rank = htmlspecialchars($_POST["defaultrank"]);	
				$color = htmlspecialchars($_POST["color"]);
				$autojoin = 0;
				if(isset($_POST['autojoin'])) { $autojoin = 1; }
				
				$newgroup = $forum->create_new_group($title, $description, $color, $autojoin);
				$defaultrank = $forum->create_new_grouprank($rank, $newgroup);
				$updaterank = $forum->update_default_grouprank($defaultrank, $newgroup);
				
				$log = $forum->insert_modlog("Ny gruppe '".$title."' blev oprettet", $user_logged_in_ID, 1);
				header('Location:acp.php?mode=groups');
				
			}
			
		} // End new group
		
		if($_GET['editgroup'])
		{
			
			$group_id = $_GET['editgroup'];
			
			echo "<div id='acp_page'>";
			echo "<div class='category'><a href=''>Redigér gruppe</a></div>";
			echo "<div id='acp_content'>";
			
			
			$group = $forum->get_group($group_id)->fetch_assoc();
			
			echo "<table>";
			echo "<form method='post'>";
			echo "<tr><td>Gruppenavn: </td><td><input type='text' name='title' value='".htmlspecialchars($group['title'], ENT_QUOTES, 'UTF-8')."' required/></td></tr>";
			echo "<tr><td colspan='2'>Beskrivelse:</td></tr>";
			echo "<tr><td colspan='2'><textarea name='description'>".htmlspecialchars($group['description'], ENT_QUOTES, 'UTF-8')."</textarea></td></tr>";
			echo "<tr><td>Gruppefarve: </td><td><input type='color' name='color' value='".$group['color']."' required/></td></tr>";
			echo "<tr><td>Standard grupperang: </td><td><select name='defaultrank'>";
			$groupranks = $forum->get_groupranks($group_id);
			while($rank = $groupranks->fetch_assoc())
			{
				if($rank['grouprank_ID'] == $group['fk_default_rank']) 
				{
					echo "<option value='".$rank['grouprank_ID']."' selected>".htmlspecialchars($rank['title'], ENT_QUOTES, 'UTF-8')."</option>";	
				}
				else
				{
					echo "<option value='".$rank['grouprank_ID']."'>".htmlspecialchars($rank['title'], ENT_QUOTES, 'UTF-8')."</option>";	
				}
			}			
			echo "</select></td></tr>";	
			if($group['autojoin'] == 1)
			{
				echo "<tr><td>Autojoin: </td><td><input type='checkbox' name='autojoin' value='yes' checked>Tillad brugere automatisk at blive medlem af gruppen</td></tr>";
			}
			else
			{
				echo "<tr><td>Autojoin: </td><td><input type='checkbox' name='autojoin' value='yes'>Tillad brugere automatisk at blive medlem af gruppen</td></tr>";
			}
			echo "<tr><td colspan='2'><input type='submit' name='submit_groupedit' value='Gem ændringer' /></td></tr>";
			echo "</form>";
			echo "</table>";
			
			echo "</div>";
			echo "</div>";
			
			
			if($_POST['submit_groupedit'])
			{
				$title = htmlspecialchars($_POST["title"]);	
				$description = htmlspecialchars($_POST["description"]);	
				$rank = htmlspecialchars($_POST["defaultrank"]);	
				$color = htmlspecialchars($_POST["color"]);
				$autojoin = 0;
				if(isset($_POST['autojoin'])) { $autojoin = 1; }
				
				$updategroup = $forum->update_group($title, $description, $color, $rank, $autojoin, $group_id);
				$log = $forum->insert_modlog("Gruppen '".$title."' blev ændret", $user_logged_in_ID, 1);
				
				$groupmembers = $forum->get_groupmembers($group_id);
			
				while($member = $groupmembers->fetch_assoc())
				{
					$character = $forum->get_character($member['fk_character_ID'])->fetch_assoc();
					if($character['active'] == 1)
					{
						$setcolor = $forum->update_character_color($color, $member['fk_character_ID']);
					}
				}
				
				header('Location:acp.php?mode=groups');
				
			}
			
		} // End edit roup
		
		if($_GET['groupranks'])
		{
			$group_id = $_GET['groupranks'];
			$group = $forum->get_group($group_id)->fetch_assoc();
			
			echo "<div id='acp_page'>";
			echo "<div class='category'><a href=''>Rangordener</a></div>";
			echo "<div id='acp_content'>";

			echo "<div class='center'>";
			$groupranks = $forum->get_groupranks($group_id);
			while ($rank = $groupranks->fetch_assoc())
			{
				echo "<form method='post'>";
				echo "<input type='hidden' name='rank_id' value='".$rank['grouprank_ID']."' />";
				echo "<input type='text' name='rankname' value='".htmlspecialchars($rank['title'], ENT_QUOTES, 'UTF-8')."' /> ";
				echo "<input type='submit' name='submit_rank' value='Gem ændringer' /> ";
				if($group['fk_default_rank'] == $rank['grouprank_ID'])
				{
					echo "<input type='submit' name='delete_rank' value='Slet grupperang' disabled/> ";
				}
				else
				{
					echo "<input type='submit' name='delete_rank' value='Slet grupperang' /> ";	
				}
				
				echo "</form>";
			}
			echo "</div>";
			
			echo "<br/>";
			echo "<hr/>";
			
			echo "<div id='newgroup'>";
			echo "<form method='post'>";
			echo "Opret ny grupperang: ";
			echo "<input type='text' name='rankname' required /> ";
			echo "<input type='submit' name='newrank' value='Opret' />";
			echo "</form>";
			echo "</div>";
			
			echo "<br/><br/><a href='acp.php?mode=groups'>Tilbage</a>";
			echo "</div>";
			echo "</div>";
			
			if($_POST['submit_rank'])
			{	
				$rank_id = $_POST['rank_id'];
				$title = htmlspecialchars($_POST["rankname"]);
				
				$update = $forum->update_grouprank_title($title, $rank_id);	
				$log = $forum->insert_modlog("Grupperangen '".$title."' blev ændret i gruppen '".$group['title']."'", $user_logged_in_ID, 1);
				header('Location:acp.php?groupranks='.$group_id);
			}
			if($_POST['delete_rank'])
			{	
				$rank_id = $_POST['rank_id'];				
				$userswithrank = $forum->get_groupmembers_by_rank($rank_id);
				
				while($u = $userswithrank->fetch_assoc())
				{
					$updaterank = $forum->update_groupmember_rank($group['fk_default_rank'], $u['groupmember_ID']);
				}
				
				if($rank_id != $group['fk_default_rank'])
				{
					$deleterank = $forum->delete_grouprank($rank_id);	
					$log = $forum->insert_modlog("Grupperang blev fjernet fra gruppen ".$group['title']."'", $user_logged_in_ID, 1);
				}
				
				header('Location:acp.php?groupranks='.$group_id);
				
			}
			if($_POST['newrank'])
			{	
				$title = htmlspecialchars($_POST["rankname"]);
				
				$update = $forum->create_new_grouprank($title, $group_id);	
				$log = $forum->insert_modlog("Grupperangen '".$title."' blev tilføjet til gruppen '".$group['title']."'", $user_logged_in_ID, 1);
				header('Location:acp.php?groupranks='.$group_id);
			}
			
			
		} // End edit group ranks 
		
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
			if ($membercount['res'] > 0 ) { echo "<br/><select name='change'><option value='defaultgroup'>Standardgruppe</option><option value='delete'>Fjern bruger fra gruppe</option></select>
			<input type='submit' name='submit_memberchanges' value='Udfør'/>"; }
			echo "</form>";
			echo "</div>";
			
			echo "<a href='acp.php?mode=groups'>Tilbage</a>";
			
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
				header('Location:acp.php?groupmembers='.$group_id);
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
							header('Location:acp.php?groupmembers='.$group_id);
						}
						if($change == 'delete')
						{
							//delete user from group
							$defaultgroup = $groupmember['defaultgroup'];
							if($defaultgroup == 1) { $setcolor = $forum->update_character_color("", $character['character_ID']); }
							
							$deletemember = $forum->delete_groupmember($selected);
							$log = $forum->insert_modlog($character['name']." blev fjernet fra gruppen '".$group['title']."'", $user_logged_in_ID, 0);
							header('Location:acp.php?groupmembers='.$group_id);
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
				header('Location:acp.php?groupmembers='.$group_id);
			}
			
		} // End group members
		
		if($_GET['deletegroup'])
		{
			$group_id = $_GET['deletegroup'];
			$group = $forum->get_group($group_id)->fetch_assoc();
			$groupname = $group['title'];
			
			$deletegroupmembers = $forum->delete_all_groupmembers($group_id);
			$deletegroupranks = $forum->delete_all_groupranks($group_id);
			$deletegroup = $forum->delete_group($group_id);
			$log = $forum->insert_modlog("Gruppen ".$groupname." blev slettet", $user_logged_in_ID, 1);
			header('Location:acp.php?mode=groups');
		} // End group deletion

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
						header('Location:acp.php?mode=users');
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
					header('Location:acp.php?username='.$superuser['name'].'&edituser=Udfør');
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
						header('Location:acp.php?username='.$superuser['name'].'&edituser=Udfør');
					}	
					
				}
				//deleting forummod status
				if($_POST['delete_forummod'])
				{
					$forum_id = $_POST['forum_id'];
					$forumname = $forum->get_forum($forum_id)->fetch_assoc();	
					$deleteforummod = $forum->delete_forummod($forum_id, $superuser['superuser_ID']);
					$log = $forum->insert_modlog("Brugeren ".$superuser['name']." blev fjernet som forummoderator for ".$forumname['title'], $user_logged_in_ID, 0);
					header('Location:acp.php?username='.$superuser['name'].'&edituser=Udfør');
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
				
				if($_POST['submit_userdelete'])
				{
					$forum->delete_userachievements_from_superuser($superuser['superuser_ID']);
					$forum->delete_forummod_statuses_from_user($superuser['superuser_ID']);
					$forum->delete_drafts_from_superuser($superuser['superuser_ID']);
					$forum->delete_messagereceivers_from_superuser($superuser['superuser_ID']);
					$usermessages = $forum->get_messages_send_by_user($superuser['superuser_ID']);
					while($msg = $usermessages->fetch_assoc())
					{
						$forum->delete_messagereceivers_from_message($msg['message_ID']) ;
					}
					$forum->delete_messages_from_user($superuser['superuser_ID']);
					$forum->delete_pollvotes_from_user($superuser['superuser_ID']); 
					
					//set posts and topics to guest
					$forum->update_all_superuser_posts_author_to_guest($superuser['superuser_ID']); 
					$forum->update_all_superuser_topics_author_to_guest($superuser['superuser_ID']); 
					
					//delete the user
					$log = $forum->insert_modlog("Brugeren ".$username." blev slettet fra systemet.", $user_logged_in_ID, 1);
					$forum->delete_superuser($superuser['superuser_ID']);
					header('Location:acp.php?mode=users');
					
				}
				
				echo "<h2>Brugerens karakterer</h2>";				
				$numberofchars = $forum->count_all_characters_from_superuser($superuser['superuser_ID'])->fetch_assoc();
				if($numberofchars['res'] < 1) 
				{ 
				echo "<span class='italic'>Denne bruger har endnu ingen karakterer.</span> <br/><br/>"; 
				echo "<form method='post'><input class='deletebutton' type='submit' value='Slet bruger' name='submit_userdelete'"; ?>
						onclick='return confirm("Er du sikker på, at du vil slette brugeren? Dette er permanent og kan ikke fortrydes.")'
						<?php echo "/></form>";
						echo "<br/>";
				}
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
					header('Location:acp.php?username='.$superuser['name'].'&edituser=Udfør');
				}
				
				echo "<hr/>";
				echo "<a href='acp.php?mode=users'>Tilbage</a>";
			}
			else
			{
				echo "<div id='acp_page'>";
				echo "<div class='category'><a href=''>Brugeradministration</a></div>";
				echo "<div id='acp_content' >";
				echo "Systemet fandt ingen bruger med det angivne navn.<br/><br/>";
				echo "<a href='acp.php?mode=users'>Tilbage</a>";	
			}
			
			echo "</div></div>";
			
		} // End edit user
		
		if($_GET['charedit']) 
		{
			$char_id = $_GET['charedit'];	
			$char_exists = $forum->check_for_existing_character($char_id)->fetch_assoc();
			
				echo "<div id='acp_page'>";
				echo "<div class='category'><a href=''>Karakteradministration</a></div>";
				echo "<div id='acp_content' >";
			
			if($char_exists['res'] > 0)
			{
				$character = $forum->get_character($char_id)->fetch_assoc();
				$superuser = $forum->get_superuser($character['fk_superuser_ID'])->fetch_assoc();
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
					
					header('Location:acp.php?charedit='.$character['character_ID']);
				}
				
				if($_POST['submit_charinactive'])
				{
					if($character['accepted'] == 1) //A non-accepted character must never be inactive
					{
						$updatestatus = $forum->update_character_active_status($character['character_ID'], 0);
						$setcolor = $forum->update_character_color($inactivecolor, $character['character_ID']);
						$log = $forum->insert_modlog("Karakteren ".$character['name']." blev angivet som inaktiv", $user_logged_in_ID, 0);
					}
					header('Location:acp.php?charedit='.$character['character_ID']);
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
					header('Location:acp.php?charedit='.$character['character_ID']);
					
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
						header('Location:acp.php?charedit='.$character['character_ID']);
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
					header('Location:acp.php?charedit='.$character['character_ID']);
				}
				
				
				if($_POST['submit_chardelete'])
				{
					$deletecharapprovalrequests = $forum->delete_approvalrequests_from_char($character['character_ID']);
					$deletechargroupmemberships = $forum->delete_groupmemberships_from_char($character['character_ID']);
					$deletetags = $forum->delete_tags_from_char($character['character_ID']);
					$deleteprofiledata = $forum->delete_profiledata_from_char($character['character_ID']); 
					$deleteachievements = $forum->delete_userachievements_from_character($character['character_ID']);
					$deletebounties = $forum->remove_bounty_from_character($character['character_ID']);
					$forum->delete_ic_chat_messages_from_char($character['character_ID']);
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
						$forum->update_ingame_posts_author_to_guest($character['character_ID']);
						$forum->update_ingame_topics_author_to_guest($character['character_ID']);
					}
						
					//Finally delete char
					$deletechar = $forum->delete_character($character['character_ID']);
					$log = $forum->insert_modlog("Karakteren ".$character['name']." blev slettet.", $user_logged_in_ID, 0);
					header('Location:acp.php?username='.$superuser['name'].'&edituser=Udfør');
					
				} // end char deletions
				
				
				if($_POST['submit_char_ownerchange'])
				{
					$newsuperuser = $_POST['superuser'];
					$superusercheck = $forum->check_for_existing_superuser($newsuperuser)->fetch_assoc();
					
					if ($superusercheck['res'] > 0)
					{
						//change usercharacter
						$forum->update_character_superuser($character['character_ID'], $newsuperuser);
						//change topics
						$forum->update_character_superuser_topics($character['character_ID'], $newsuperuser);
						//change posts
						$forum->update_character_superuser_posts($character['character_ID'], $newsuperuser);
						//change achievements
						$forum->update_character_superuser_achievements($character['character_ID'], $newsuperuser);
						
						$newsuperuserdata = $forum->get_superuser($newsuperuser)->fetch_assoc();
						$log = $forum->insert_modlog("Karakteren ".$character['name']." blev overført til superbrugeren ".$newsuperuserdata['name'], $user_logged_in_ID, 1);
						header('Location:acp.php?charedit='.$character['character_ID']);
					}
				}
				
				echo "<hr/>";
				
				echo "<div class='center'>";
				echo "<br/><span class='bold'>Flyt karakter til anden superbruger:</span><br/><br/>";
				echo "<form method='post'>
				
				Bruger-ID: <input type='number' class='numberinput_small' name='superuser' required/> 
				<input type='submit' value='Udfør' name='submit_char_ownerchange'"; ?>
				onclick='return confirm("Er du sikker på, at du vil ændre skaberen af denne karakter?")'
				<?php echo "/><br/>";
				echo "</div>";
				
				echo "<br/><br/><a href='acp.php?username=".$superuser['name']."&edituser=Udfør'>Tilbage</a>";

			}
			else
			{
				echo "Karakteren kunne ikke findes";	
			}
						
			echo "</div>";
			echo "</div>";
			
		}
		
		if($_GET['mode'] == 'forums')
		{
			echo "<div id='acp_page'>";
			echo "<div class='category'><a href=''>Forumadministration</a></div>";
			echo "<div id='acp_content' >";
			
			if(!$_GET['parentforum'] && !$_GET['edit'] && !$_GET['moveposts'])
			{
				$toplevelforums = $forum->count_toplevel_forums()->fetch_assoc();
				$max = $toplevelforums['res'];
				$next = 0;
				echo "<table id='adm_forumtable'>";
				echo "<tr><th class='left'>Forum</th><th>Kategori</th><th>Redigering</th></tr>";
				
				for($i = 0; $i < $max; $i++)
				{
					$currentforum = $forum->get_toplevel_forum($next)->fetch_assoc();
					echo "<tr>";
					echo "<td><a href='acp.php?mode=forums&parentforum=".$currentforum['forum_ID']."'>".$currentforum['title']."</td>";	
					if($currentforum['category'] == 1 ){ echo "<td class='center'>Ja</td>"; } else { echo "<td class='center'>Nej</td>"; }
					echo "<td class='center'><a href='acp.php?mode=forums&moveup=".$currentforum['forum_ID']."'>&#8679;</a>
					<a href='acp.php?mode=forums&movedown=".$currentforum['forum_ID']."'>&#8681;</a>
					[<a href='acp.php?mode=forums&edit=".$currentforum['forum_ID']."'>Redigér</a>]
					[<a href='acp.php?mode=forums&delete=".$currentforum['forum_ID']."'"; ?>
					onclick='return confirm("Er du sikker på, at du vil slette forummet? Dette kan ikke fortrydes")'
					<?php
					echo"
					>Slet</a>]
					[<a href='acp.php?mode=forums&moveposts=".$currentforum['forum_ID']."'>Flyt emner</a>]
					</td>";
					echo "</tr>";	
					$next = $currentforum['forum_ID'];		
				}	//End for loop
				echo "</table>";
				
				echo "<hr/>";
				echo "<div id='newgroup'>";
				echo "<form method='get'>";
				echo "Opret nyt forum: ";
				echo "<input type='hidden' name='lastforum' value='".$next."'/>";
				echo "<input type='hidden' name='addto' value='0'/>";
				echo "<input type='text' name='forumtitle' required /> ";
				echo "<input type='submit' name='newforum' value='Opret' />";
				echo "</form>";
				echo "</div>";
				
			} // End no parentforum
			
			if($_GET['parentforum'] && !$_GET['edit'] && !$_GET['moveposts'])
			{
				$f = $_GET['parentforum'];
				$viewforum = $forum->get_forum($f)->fetch_assoc();
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
				echo "<a href='acp.php?mode=forums' class='forumnavlink'>Boardindex</a>";
				foreach ($parents as $pval)
				{
					echo " &laquo; <a href='acp.php?mode=forums&parentforum=".$pval['forum_ID']."' class='forumnavlink'>".$pval['title']."</a>";		
				}
				echo " &laquo; <a href='acp.php?mode=forums&parentforum=".$viewforum['forum_ID']."' class='forumnavlink'>".$viewforum['title']."</a>";
				echo "</div>"; //End navigation div 
				
				$subforums = $forum->count_subforums($f)->fetch_assoc();
				$max = $subforums['res'];
				$next = 0;
				
				echo "<table id='adm_forumtable'>";
				echo "<tr><th class='left'>Forum</th><th>Kategori</th><th>Redigering</th></tr>";
				
				for($i = 0; $i < $max; $i++)
				{
					$currentforum = $forum->get_subforum($next, $f)->fetch_assoc();
					echo "<tr>";
					echo "<td><a href='acp.php?mode=forums&parentforum=".$currentforum['forum_ID']."'>".$currentforum['title']."</td>";	
					if($currentforum['category'] == 1 ){ echo "<td class='center'>Ja</td>"; } else { echo "<td class='center'>Nej</td>"; }
					echo "<td class='center'><a href='acp.php?mode=forums&moveup=".$currentforum['forum_ID']."'>&#8679;</a>
					<a href='acp.php?mode=forums&movedown=".$currentforum['forum_ID']."'>&#8681;</a>
					[<a href='acp.php?mode=forums&edit=".$currentforum['forum_ID']."'>Redigér</a>]
					[<a href='acp.php?mode=forums&delete=".$currentforum['forum_ID']."'"; ?>
					onclick='return confirm("Er du sikker på, at du vil slette forummet? Dette kan ikke fortrydes")'
					<?php
					echo"
					>Slet</a>]
					[<a href='acp.php?mode=forums&moveposts=".$currentforum['forum_ID']."'>Flyt emner</a>]
					</td>";
					echo "</tr>";	
					$next = $currentforum['forum_ID'];	
				}	
				
				echo "</table>";
				
				echo "<hr/>";
				echo "<div id='newgroup'>";
				echo "<form method='get'>";
				echo "Opret nyt forum: ";
				echo "<input type='hidden' name='lastforum' value='".$next."'/>";
				echo "<input type='hidden' name='addto' value='".$f."'/>";
				echo "<input type='text' name='forumtitle' required /> ";
				echo "<input type='submit' name='newforum' value='Opret' />";
				echo "</form>";		
				echo "</div>";						
				
			} //End if has parentforum
			
			if($_GET['moveup'])
			{
				$moveid = $_GET['moveup'];
				$moveforum = $forum->get_forum($moveid)->fetch_assoc();
				
				if($moveforum['above_ID'] != 0)
				{
					$aboveforum = $forum->get_forum($moveforum['above_ID'])->fetch_assoc();
					$has_forum_below = $forum->check_for_forum_below($moveid)->fetch_assoc();	
					$belowforum = $forum->get_forum_below($moveid)->fetch_assoc();
					
					$update_moveforum = $forum->update_forum_position($aboveforum['above_ID'], $moveid);					
					$update_aboveforum = $forum->update_forum_position($moveid, $aboveforum['forum_ID']);	
					
					if($has_forum_below['res'] > 0)
					{
						$update_belowforum = $forum->update_forum_position($aboveforum['forum_ID'], $belowforum['forum_ID']);	
					}
				}
				header('Location:acp.php?mode=forums&parentforum='.$moveforum['parent_ID']);	
			}
			
			if($_GET['movedown'])
			{
				$moveid = $_GET['movedown'];
				$moveforum = $forum->get_forum($moveid)->fetch_assoc();
				
				$has_forum_below = $forum->check_for_forum_below($moveid)->fetch_assoc();
				
				if($has_forum_below['res'] > 0)
				{
					$belowforum = $forum->get_forum_below($moveid)->fetch_assoc();	
					
					$has_second_forum_below = $forum->check_for_forum_below($belowforum['forum_ID'])->fetch_assoc();
					if($has_second_forum_below['res'] > 0)
					{
						$secondbelowforum = $forum->get_forum_below($belowforum['forum_ID'])->fetch_assoc();
						$update_second_belowforum = $forum->update_forum_position($moveid, $secondbelowforum['forum_ID']);
					}
					
					$update_belowforum = $forum->update_forum_position($moveforum['above_ID'], $belowforum['forum_ID']);
					$update_moveforum = $forum->update_forum_position($belowforum['forum_ID'], $moveid);	
									
				}
				header('Location:acp.php?mode=forums&parentforum='.$moveforum['parent_ID']);	
						
			} // end move forums
			
			if($_GET['edit'])
			{
				echo "<h2>Ret forum</h2>";
				
				$editid = $_GET['edit'];
				$forumexists = $forum->forum_exists($editid)->fetch_assoc();
			
				if($forumexists['res'] > 0 )
				{

					$editforum = $forum->get_forum($editid)->fetch_assoc();
					
					echo "<table>"; echo "<form method='post'>";
					echo "<tr><td>Forumtype: </td> <td><select name='category'>";
					if($editforum['category'] == 1) { echo "<option value='0'>Forum</option><option value='1' selected>Kategori</option>"; }
					else { echo "<option value='0' selected>Forum</option><option value='1'>Kategori</option>"; }					
					echo "</select></td></tr>";
					echo "<tr><td>Overordnet forum:</td><td><select name='parent'><option value='0'>Intet overforum</option>";
					$forumoptions = $forum->get_all_forums();
					while($forumoption = $forumoptions->fetch_assoc())
					{
						if($forumoption['forum_ID'] == $editforum['parent_ID']) 
						{ echo "<option value='".$forumoption['forum_ID']."' selected>".$forumoption['title']."</option>"; }
						else { echo "<option value='".$forumoption['forum_ID']."'>".$forumoption['title']."</option>"; }
						
					}
					echo "</select></td></tr>";
					echo "<input type='hidden' name='prevparent' value='".$editforum['parent_ID']."'/>";
					echo "<tr><td>Forumtitel: </td> <td><input type='text' name='forumtitle' value='".htmlspecialchars($editforum['title'], ENT_QUOTES, 'UTF-8')."' maxlength='23' required></td></tr>";	
					echo "<tr><td>Beskrivelse: </td> <td><textarea name='forumdesc' maxlength='150'>".htmlspecialchars($editforum['description'], ENT_QUOTES, 'UTF-8')."</textarea></td></tr>";
					echo "<tr><td>Forumbillede: </td> <td><input type='text' name='forumimg' value='".htmlspecialchars($editforum['picture'], ENT_QUOTES, 'UTF-8')."'></td></tr>";			
					echo "<tr><td>In-game: </td> <td><select name='ingame'>";
					if($editforum['ingame'] == 1) { echo "<option value='1' selected>Ja</option><option value='0'>Nej</option>"; }
					else { echo "<option value='1'>Ja</option><option value='0' selected>Nej</option>"; }
					echo "</select></td></tr>";
					echo "<tr><td>Officiel: </td> <td><select name='official'>";
					if($editforum['official'] == 1) { echo "<option value='1' selected>Ja</option><option value='0'>Nej</option>"; }
					else { echo "<option value='1'>Ja</option><option value='0' selected>Nej</option>"; }
					echo "</select></td></tr>";		
						
					echo "<tr><td>Hvem kan læse forummet?</td> 
					<td><select name='read_access'>";
					if($editforum['read_access'] == 0) 
					{ echo "<option value='0' selected>Alle (også gæster)</option>"; } else { echo "<option value='0'>Alle (også gæster)</option>"; }
					if($editforum['read_access'] == 1) { echo "<option value='1' selected>Alle brugere</option>"; } else { echo "<option value='1'>Alle brugere</option>"; }
					if($editforum['read_access'] == 2) { echo "<option value='2' selected>Administratorer og moderatorer</option>"; } 
					else { echo "<option value='2'>Administratorer og moderatorer</option>"; }
					if($editforum['read_access'] == 3) 
					{ echo "<option value='3' selected>Kun administratorer</option>"; } else { echo "<option value='3'>Kun administratorer</option>"; }
					echo "</select></td></tr>";	
					
					echo "<tr><td>Hvem kan skrive i forummet?</td><td><select name='write_access'>";
					if($editforum['write_access'] == 0 && $editforum['writeable'] == 1) { echo "<option value='0' selected>Alle (også gæster)</option>";}
					else { echo "<option value='0'>Alle (også gæster)</option>";}
					if($editforum['write_access'] == 1 && $editforum['writeable'] == 1) { echo "<option value='1' selected>Alle brugere</option>";}
					else { echo "<option value='1'>Alle brugere</option>";}
					if($editforum['write_access'] == 2 && $editforum['writeable'] == 1) { echo "<option value='2' selected>Administratorer og moderatorer</option>";}
					else { echo "<option value='2'>Administratorer og moderatorer</option>";}
					if($editforum['write_access'] == 3 && $editforum['writeable'] == 1) { echo "<option value='3' selected>Kun administratorer</option>";}
					else { echo "<option value='3'>Kun administratorer</option>";}
					if($editforum['writeable'] == 0) { echo "<option value='4' selected>Ingen</option>";}
					else { echo "<option value='4'>Ingen</option>";}
					echo "</select></td></tr>";		
					
					echo "<tr><td colspan='2'><input type='submit' name='submit_edit_forum' value='Gem ændringer'/></td></tr>";		
					echo "<form>"; echo "</table>";
					
					
					if($_POST['submit_edit_forum'])
					{
						$category = $_POST['category'];
						$parent = $_POST['parent'];
						$prevparent = $_POST['prevparent'];
						$title = htmlspecialchars($_POST['forumtitle']);
						$desc = htmlspecialchars($_POST['forumdesc']);
						$image = htmlspecialchars($_POST['forumimg']);
						$ingame = $_POST['ingame'];
						$official = $_POST['official'];
						$read_access = $_POST['read_access'];
						$write_access = $_POST['write_access'];
						
						
						
						if($ingame == 1 && $write_access == 0)
						{
							$write_access = 1;	
						}
						
						if($category == 1 || $write_access == 4)
						{
								$write = 0;
								$write_access = 3;
						}
						else
						{
								$write = 1;
						}
						
						$updateforum = $forum->edit_forum($parent, $title, $desc, $image, $ingame, $official, $category, $write, $read_access, $write_access, $editid);
						$log = $forum->insert_modlog("Forummet, ".$title.", blev ændret", $user_logged_in_ID, 1);
						
						if($parent != $prevparent)
						{
							//Find the forum below the edited forum
							$hasbelowforum = $forum->check_for_forum_below($editid)->fetch_assoc();
							if($hasbelowforum['res'] > 0)
							{
								$belowforum = $forum->get_forum_below($editid)->fetch_assoc();							
								//Set that forums above_ID to the editforum's above_ID
								$update_belowforum = $forum->update_forum_position($editforum['above_ID'], $belowforum['forum_ID']);
							}
							//Find the last forum in the new parent's forums
							$subforums = $forum->count_subforums($parent)->fetch_assoc();
							$max = $subforums['res']-1; // We have already moved the new forum, so this result will have one too many
							$lastforum = 0;
							for($i = 0; $i < $max; $i++)
							{
								$currentforum = $forum->get_subforum($lastforum, $parent)->fetch_assoc();
								$lastforum = $currentforum['forum_ID'];
							}							
							//Set editforum's above_ID to that forum
							$updateforumposition = $forum->update_forum_position($lastforum, $editid);
						}
						
						header('Location:acp.php?mode=forums&parentforum='.$parent);			
					}
					
				} //End check if forums exist
				
			} //End edit forum
			
			if($_GET['delete'])
			{
				$deleteid = $_GET['delete'];
				$forum_to_delete = $forum->get_forum($deleteid)->fetch_assoc();
				$subforums = $forum->count_subforums($deleteid)->fetch_assoc(); 
				$numberoftopics = $forum->count_forum_topics($deleteid)->fetch_assoc(); 
				
				if($subforums['res'] < 1 && $numberoftopics['res'] < 1)
				{
						$deleteforum = $forum->delete_forum($deleteid);
						$log = $forum->insert_modlog("Forummet, ".$forum_to_delete['title'].", blev slettet", $user_logged_in_ID, 1);
						header('Location:acp.php?mode=forums&parentforum='.$forum_to_delete["parent_ID"]);	
				}
				
				else 
				{
					echo "<span class='errormsg'>Forummet kan ikke slettes, så længe det indeholder emner eller underforums.</span>";		
				}
			}
			
			if($_GET['moveposts'])
			{
				echo "<h2>Flyt emner</h2>";				
				$forumid = $_GET['moveposts'];
				$forumdata = $forum->get_forum($forumid)->fetch_assoc();
				$forumchoices = $forum->get_all_writable_forums($forumdata['ingame'], $forumdata['official']);
				echo "<form method='post'>";
				echo "Flyt emner fra ".$forumdata['title']." til: ";				
				echo "<select name='destination'>";
				while($currentforum = $forumchoices->fetch_assoc())
				{
					echo "<option value='".$currentforum['forum_ID']."'>".$currentforum['title']."</option>";	
				}
				echo "</select>";
				echo " <input type='submit' name='move_forumposts' value='Udfør' />";
				echo "</form>";
				
				echo "<hr/>";
				echo "<a href='acp.php?mode=forums&parentforum=".$forumdata['parent_ID']."'>Tilbage</a>";
				
				if($_POST['move_forumposts'])
				{
					$destination = $_POST['destination'];
					$topics_to_be_moved = $forum->get_topics($forumid);
					while($mtopic = $topics_to_be_moved->fetch_assoc())
					{
						$updatetopicforum = $forum->update_topic_forum($destination, $mtopic['topic_ID']);	
					}
					$destinationdata = $forum->get_forum($destination)->fetch_assoc();
					$log = $forum->insert_modlog("Indlæg blev flyttet fra ".$forumdata['title']." til ".$destinationdata['title'], $user_logged_in_ID, 1);	
					header('Location:acp.php?mode=forums&parentforum='.$forumdata["parent_ID"]);	
				}
				
			}
			
						
			echo "</div>";
			echo "</div>";
		} //End forummode
		
		if(isset($_GET['newforum']) && isset($_GET['lastforum']))
		{
			echo "<div id='acp_page'>";
			echo "<h2>Opret nyt forum</h2>";
						
			$forumparent = $_GET['addto'];
			$forumtitle = $_GET['forumtitle'];
			$above = $_GET['lastforum'];
			
			$trygetparent = $forum->forum_exists($forumparent)->fetch_assoc();
			$trygetaboveforum = $forum->forum_exists($above)->fetch_assoc();
			
			if(($trygetparent['res'] > 0 || $forumparent == 0) && ($trygetaboveforum['res'] > 0 || $above == 0))
			{
				$ingame = 1;
				$official = 1;
				$read_access = 0;
				
				if($forumparent != 0)
				{
					$parentdata = $forum->get_forum($forumparent)->fetch_assoc();
					$ingame = $parentdata['ingame'];
					$official = $parentdata['official'];
					$read_access = $parentdata['read_access'];
				}
				
				echo "<table>"; echo "<form method='post'>";
				echo "<tr><td>Forumtype: </td> <td><select name='category'><option value='0'>Forum</option><option value='1'>Kategori</option></select></td></tr>";
				echo "<tr><td>Overordnet forum:</td><td><select name='parent'><option value='0'>Intet overforum</option>";
				$forumoptions = $forum->get_all_forums();
				while($forumoption = $forumoptions->fetch_assoc())
				{
					if($forumparent == $forumoption['forum_ID']) { echo "<option value='".$forumoption['forum_ID']."' selected>".$forumoption['title']."</option>"; }
					else { echo "<option value='".$forumoption['forum_ID']."'>".$forumoption['title']."</option>"; }
					
				}
				echo "</select></td></tr>";
				echo "<tr><td>Forumtitel: </td> <td><input type='text' name='forumtitle' value='".$forumtitle."' maxlength='23' required></td></tr>";	
				echo "<tr><td>Beskrivelse: </td> <td><textarea name='forumdesc' maxlength='150'></textarea></td></tr>";		
				echo "<tr><td>Forumbillede: </td> <td><input type='text' name='forumimg'></td></tr>";
				echo "<tr><td>In-game: </td> <td><select name='ingame'>";
				if($ingame == 1) { echo "<option value='1' selected>Ja</option><option value='0'>Nej</option>"; }
				else { echo "<option value='1'>Ja</option><option value='0' selected>Nej</option>"; }
				echo "</select></td></tr>";
				echo "<tr><td>Officiel: </td> <td><select name='official'>";
				if($official == 1) { echo "<option value='1' selected>Ja</option><option value='0'>Nej</option>"; }
				else { echo "<option value='1'>Ja</option><option value='0' selected>Nej</option>"; }
				echo "</select></td></tr>";			
				echo "<tr><td>Hvem kan læse forummet?</td> 
				<td><select name='read_access'>";
				if($read_access == 0) { echo "<option value='0' selected>Alle (også gæster)</option>"; } else { echo "<option value='0'>Alle (også gæster)</option>"; }
				if($read_access == 1) { echo "<option value='1' selected>Alle brugere</option>"; } else { echo "<option value='1'>Alle brugere</option>"; }
				if($read_access == 2) { echo "<option value='2' selected>Administratorer og moderatorer</option>"; } 
				else { echo "<option value='2'>Administratorer og moderatorer</option>"; }
				if($read_access == 3) { echo "<option value='3' selected>Kun administratorer</option>"; } else { echo "<option value='3'>Kun administratorer</option>"; }
				echo "</select></td></tr>";	
				echo "<tr><td>Hvem kan skrive i forummet?</td> 
				<td><select name='write_access'><option value='0'>Alle (også gæster)</option><option value='1' selected>Alle brugere</option>
				<option value='2'>Administratorer og moderatorer</option><option value='3'>Kun administratorer</option>
				<option value='4'>Ingen</option>
				</select></td></tr>";		
				
				echo "<tr><td colspan='2'><input type='submit' name='submit_new_forum' value='Opret'/></td></tr>";		
				echo "<form>"; echo "</table>";
				
				echo "</div>";
				
				if($_POST['submit_new_forum'])
				{
					$category = $_POST['category'];
					$parent = $_POST['parent'];
					$title = htmlspecialchars($_POST['forumtitle']);
					$desc = htmlspecialchars($_POST['forumdesc']);
					$image = htmlspecialchars($_POST['forumimg']);
					$ingame = $_POST['ingame'];
					$official = $_POST['official'];
					$read_access = $_POST['read_access'];
					$write_access = $_POST['write_access'];
					
					if($ingame == 1 && $write_access == 0)
					{
						$write_access = 1;	
					}
					
					if($category == 1 || $write_access == 4)
					{
							$write = 0;
							$write_access = 3;
					}
					else
					{
							$write = 1;
					}
					
					$insertforum = $forum->insert_new_forum($parent, $above, $title, $desc, $image, $ingame, $official, $category, $write, $read_access, $write_access);
					$log = $forum->insert_modlog("Nyt forum, ".$title.", blev oprettet", $user_logged_in_ID, 1);
					header('Location:acp.php?mode=forums&parentforum='.$parent);	
				}
				
			} //End check if forums exist
			
		} //End new forum
		
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
						echo "<td><a href='acp.php?mode=wantedlist&edit=".$wantedpost['wanted_ID']."' class='bold'>Godkend</a></td>";
						echo "<td><a href='acp.php?mode=wantedlist&delete=".$wantedpost['wanted_ID']."' class='bold'"; ?> 
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
						echo "<td><a href='acp.php?mode=wantedlist&edit=".$wantedpost['wanted_ID']."' class='bold'>Redigér</a></td>";
						echo "<td><a href='acp.php?mode=wantedlist&delete=".$wantedpost['wanted_ID']."' class='bold'>Slet</a></td>"; 
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
					header('Location:acp.php?mode=wantedlist');
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
				header('Location:acp.php?mode=wantedlist');
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
				echo "<div class='forumbutton'> <a href='acp.php?mode=achievements&give'>Tildel trofæ til bruger</a></div>";			
				echo "<div class='forumbutton'> <a href='acp.php?mode=achievements&new'>Opret nyt trofæ</a></div>";
				
				$achievements = $forum->get_all_achievements();
				echo "<table>";
				while($achi = $achievements->fetch_assoc())
				{
					echo "<tr>";
					echo "<td>".$achi['title']."</td>";
					if($achi['type'] == 1) { echo "<td>Offgame</td>"; }
					if($achi['type'] == 2) { echo "<td>Plot</td>"; }
					if($achi['type'] == 3) { echo "<td>Stedtilknyttet</td>"; }
					if($achi['type'] == 4) { echo "<td>Gruppe</td>"; }
					echo "<td><a href='acp.php?mode=achievements&edit=".$achi['achievement_ID']."'>Redigér</a></td>";
					echo "</tr>";	
				}
				echo "</table>";
			}
			if(isset($_GET['new']))
			{
				echo "<div class='category'><a href=''>Nyt trofæ</a></div>";
				echo "<div id='acp_content'>";
				
				echo "<table>";
				echo "<form method='post'>";
				echo "<tr><td>Titel: </td>"; echo "<td><input type='text' name='title' required/></td></tr>";
				echo "<tr><td>Beskrivelse: </td>"; echo "<td><input type='text' name='desc' required/></td></tr>";
				echo "<tr><td>Type: </td>"; 
				echo "<td><select name='type'/>
				<option value='1'>Offgame</option>
				<option value='2'>Plot</option>
				<option value='3'>Stedtilknyttet</option>
				<option value='4'>Gruppe</option>
				</select></td></tr>";
				echo "<tr><td>Forum-ID:<br/><span class='smalltext'>Hvis stedtilknyttet</span> </td>"; 
				echo "<td><input class='numberinput_small' type='number' name='forum'/></td></tr>";
				echo "<tr><td>Tråd-ID:<br/><span class='smalltext'>Hvis plot-trofæ</span> </td>"; echo "<td><input class='numberinput_small' type='number' name='topic'/></td></tr>";
				echo "<tr><td>Billede: </td>"; echo "<td><input type='text' name='image' required/></td></tr>";
				echo "<tr><td colspan='2'><input type='submit' name='submit_new_achievement' value='Tilføj'/></td></tr>";
				
				echo "</form>";
				echo "</table>";
				
				echo "<hr/>";
				echo "<a href='acp.php?mode=achievements'>Tilbage</a>";
				
				if($_POST['submit_new_achievement'])
				{
					$title = htmlspecialchars($_POST['title']);
					$desc = htmlspecialchars($_POST['desc']);
					$type = $_POST['type'];	
					$forumid = $_POST['forum'];	
					$topicid = $_POST['topic'];
					$img = htmlspecialchars($_POST['image']);	
					
					if($forumid == "" ) { $forumid = 0; }
					if($topicid == "" ) { $topicid = 0; }
					
					$insertachi = $forum->insert_new_achievement($title, $desc, $type, $forumid, $topicid, $img);
					$log = $forum->insert_modlog("Nyt trofæ, ".$title.", blev oprettet", $user_logged_in_ID, 1);
					header('Location:acp.php?mode=achievements');
				}
				
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
										header('Location:acp.php?mode=achievements');
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
				echo "<a href='acp.php?mode=achievements'>Tilbage</a>";
				
			}
			
			if(isset($_GET['edit']))
			{
				echo "<div class='category'><a href=''>Redigér trofæ</a></div>";
				echo "<div id='acp_content'>";
				
				$achievement_ID = $_GET['edit'];
				$achievementdata = $forum->get_achievement($achievement_ID)->fetch_assoc();
				
				echo "<table>";
				echo "<form method='post'>";
				echo "<tr><td>Titel: </td>"; echo "<td><input type='text' name='title' value='".htmlspecialchars($achievementdata['title'], ENT_QUOTES, 'UTF-8')."' required/></td></tr>";
				echo "<tr><td>Beskrivelse: </td>"; echo "<td><input type='text' name='desc' value='".htmlspecialchars($achievementdata['description'], ENT_QUOTES, 'UTF-8')."' required/></td></tr>";
				echo "<tr><td>Type: </td>"; 
				echo "<td><select name='type'/>";
				if($achievementdata['type'] == 1) { echo "<option value='1' selected>Offgame</option>"; } else { echo "<option value='1'>Offgame</option>"; }
				if($achievementdata['type'] == 2) { echo "<option value='2' selected>Plot</option>"; } else { echo "<option value='2'>Plot</option>"; }
				if($achievementdata['type'] == 3) { echo "<option value='3' selected>Stedtilknyttet</option>"; } else { echo "<option value='3'>Stedtilknyttet</option>"; }
				if($achievementdata['type'] == 4) { echo "<option value='4' selected>Gruppe</option>"; } else { echo "<option value='4'>Gruppe</option>"; }
				echo "</select></td></tr>";
				echo "<tr><td>Forum-ID:<br/><span class='smalltext'>Hvis stedtilknyttet</span> </td>"; 
				echo "<td><input class='numberinput_small' type='number' value='".$achievementdata['fk_forum_ID']."' name='forum'/></td></tr>";
				echo "<tr><td>Tråd-ID:<br/><span class='smalltext'>Hvis plot-trofæ</span> </td>"; 
				echo "<td><input class='numberinput_small' type='number' value='".$achievementdata['fk_topic_ID']."' name='topic'/></td></tr>";
				echo "<tr><td>Billede: </td>"; echo "<td><input type='text' name='image' value='".htmlspecialchars($achievementdata['img'], ENT_QUOTES, 'UTF-8')."' required/></td></tr>";
				echo "<tr><td colspan='2'><input type='submit' name='submit_edited_achievement' value='Gem ændringer'/></td></tr>";
				
				echo "</form>";
				echo "</table>";
				
				echo "<hr/>";
				echo "<a href='acp.php?mode=achievements'>Tilbage</a>";
				
				if($_POST['submit_edited_achievement'])
				{
					$title = htmlspecialchars($_POST['title']);
					$desc = htmlspecialchars($_POST['desc']);
					$type = $_POST['type'];	
					$forumid = $_POST['forum'];	
					$topicid = $_POST['topic'];
					$img = htmlspecialchars($_POST['image']);	
					
					if($forumid == "" ) { $forumid = 0; }
					if($topicid == "" ) { $topicid = 0; }
					
					$editachi = $forum->edit_achievement($achievement_ID, $title, $desc, $type, $forumid, $topicid, $img);
					$log = $forum->insert_modlog("Trofæet ".$title." blev ændret", $user_logged_in_ID, 1);
					header('Location:acp.php?mode=achievements');
				}
			}
						
			echo "</div></div>";
			
		} // End achievements
		
		if($_GET['mode'] == 'chat')
		{
			if($_POST['delete_chatmsg'])
			{
				$msgid = $_POST['msgid'];
				$forum->delete_chat_message($msgid);
				header('Location:acp.php?mode=chat');
			}
			if($_POST['delete_icchatmsg'])
			{
				$msgid = $_POST['msgid'];
				$forum->delete_ic_chat_message($msgid);
				header('Location:acp.php?mode=chat');
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