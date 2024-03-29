<?php
include('header.php');
include('functions/SimpleImage.php');
?>
<?php
if(!isset($_SESSION['user']))
{
	header('Location:index.php');	
}
else
{
	echo "<div class='category'><a href=''>Kontrolpanel</a></div>";
	
	
	echo "<div id='topmenu'>";
	echo "<a href='ucp.php'>Min profil</a> &#9679; <a href='ucp.php?menu=char'>Mine karakterer</a> &#9679; <a href='ucp.php?menu=new'>Ny karakter</a>";
	echo "</div>";	
	
	if(!isset($_GET['menu']))
	{
		echo "<div id='ucpwrap'>";
		
		echo "<div id='sidemenu'>";		
		echo "<div class='category'><a href=''>Menu</a></div>";
		echo "<div id='sidemenu_content'>";
		echo "<a href='ucp.php?mode=profile'>Profil</a>";
		echo "<a href='ucp.php?mode=personaltext'>Profiltekst</a>";
		echo "<a href='ucp.php?mode=avatar'>Avatar</a>";
		echo "<a href='ucp.php?mode=sig'>Signatur</a>";
		echo "<a href='ucp.php?mode=konto'>Konto</a>";
		echo "</div></div>";
		
		echo "<div id='ucp_page'>";
		
		if(!isset($_GET['mode']))
		{
			echo "<div class='category'><a href=''>Min brugerprofil</a></div>";
			echo "<div id='ucp_content'>";
			echo "<p class='center'>Her kan du redigere din personlige brugerprofil.<br/>
			Ønsker du i stedet at oprette en ny karakter eller redigere dine eksisterende karakterer, så benyt den ovenstående menu.</p>";
			echo "</div>";
			
		}
		
		
		if($_GET['mode'] == "profile")
		{
				
				echo "<div class='category'><a href=''>Min profil</a></div>";
				
				echo "<div id='ucp_content'>";
				echo "<table>";
				echo "<form method='post' class='confirmform'>";
				
				//Birthday variables
				$profileyear = 0;
				$profilemonth = 0;
				$profileday = 0;				
				
				//We shouldn't set the date if the user doesn't have a birthday in the database
				if($user_logged_in['birthday'] != "0000-00-00")
				{
					$profileyear = date("Y", strtotime($user_logged_in['birthday']));
					$profilemonth = date("n", strtotime($user_logged_in['birthday']));
					$profileday = date("j", strtotime($user_logged_in['birthday']));
				}
				
				$overall_posts = $forum->count_all_posts_from_superuser($user_logged_in_ID)->fetch_assoc(); 
				
				//Tables for the profile info
				echo "<tr><td>Fødselsdag: </td>"; 
				echo "<td> <select name='birthyear' class='select_small'> <option value=''>--</option>";
				$currentyear = date("Y"); for($i = $currentyear; $i > 1939; $i--) { 
					if($i == $profileyear) { echo "<option value='".$i."' selected>".$i."</option>"; } else { echo "<option value='".$i."'>".$i."</option>"; }} echo "</select>"; 
				echo "<select name='birthday' class='select_small'><option value=''>--</option>"; for($i = 1; $i < 32; $i++) { 
					if($i == $profileday) { echo "<option value='".$i."' selected>".$i."</option>"; } else { echo "<option value='".$i."'>".$i."</option>"; } }  echo "</select>"; 	
				echo "<select name='birthmonth' class='select_small'><option value=''>--</option>"; for($i = 1; $i < 13; $i++) { 
					if($i == $profilemonth) { echo "<option value='".$i."' selected>".$i."</option>"; } else { echo "<option value='".$i."'>".$i."</option>"; } } echo "</select>"; 
				echo "</td></tr>";
				if($overall_posts['res'] >= 1000 || $user_rank > 1)
				{
					echo "<tr><td>Farve: </td>";
					echo "<td> <input type='color' name='color' value='".$user_logged_in['color']."'> </td></tr>";
				}
				else
				{
					echo "<input type='hidden' name='color' value='".$user_logged_in['color']."'>";
				}
				echo "<tr><td>Titel</td>"; echo "<td> <input type='text' name='title' maxlength='30' value='".htmlspecialchars($user_logged_in['title'], ENT_QUOTES, 'UTF-8')."'>* </td></tr>";
				echo "<tr><td>Hvordan fandt du siden? </td>"; echo "<td> <input type='text' name='reference' value='".htmlspecialchars($user_logged_in['reference'], ENT_QUOTES, 'UTF-8')."'> </td></tr>";
				echo "<tr><td>Geografisk sted: </td>"; echo "<td> <input type='text' name='geography' value='".htmlspecialchars($user_logged_in['geography'], ENT_QUOTES, 'UTF-8')."'> </td></tr>";
				echo "<tr><td>Hjemmeside: </td>"; echo "<td> <input type='text' name='website' value='".htmlspecialchars($user_logged_in['website'], ENT_QUOTES, 'UTF-8')."'> </td></tr>";
				echo "<tr><td>Facebook: </td>"; echo "<td> <input type='text' name='facebook' value='".htmlspecialchars($user_logged_in['facebook'], ENT_QUOTES, 'UTF-8')."'> </td></tr>";
				echo "<tr><td>Skype: </td>"; echo "<td> <input type='text' name='skype' value='".htmlspecialchars($user_logged_in['skype'], ENT_QUOTES, 'UTF-8')."'> </td></tr>";
				
				echo "<tr><td></td><td><input type='submit' name='submit_profiledata' value='Gem ændringer'/></td></tr>";
				
				echo "</form>";
									
				echo "</table>";
				
				echo "<span class='smalltext'>*Din titel er den tekst, der står under dit avatar.</span>";
				echo "</div>";
				
				//Submitting profile info
				if(isset($_POST['submit_profiledata']))
				{
					$birthyear = $_POST['birthyear'];
					$birthmonth = $_POST['birthmonth'];
					$birthday = $_POST['birthday'];
					
					$birthdate = "0000-00-00";
					if($birthyear != "" && $birthmonth != "" && $birthday != "") { $birthdate = $birthyear."-".$birthmonth."-".$birthday; }
					
					$title = htmlspecialchars($_POST["title"]);	
					$reference = htmlspecialchars($_POST["reference"]);	
					$geography = htmlspecialchars($_POST["geography"]);	
					$website = htmlspecialchars($_POST["website"]);
					$facebook = htmlspecialchars($_POST["facebook"]);	
					$skype = htmlspecialchars($_POST["skype"]);	
					$color = htmlspecialchars($_POST["color"]);	
					
					//echo $birthdate;
					
					$updateprofile = $forum->update_superuser_profile($birthdate, $title, $reference, $geography, $website, $facebook, $skype, $color, $user_logged_in_ID);
					header('Location:ucp.php?mode=profile');					
				}
		} //end profile
		
		if(($_GET['mode']) == "personaltext")
		{
			echo "<div class='category'><a href=''>Personlig profiltekst</a></div>";				
			echo "<div id='ucp_content' class='center'>";
			
			if(isset($_POST['submit_profiletext']))
			{
				$profiletext = htmlspecialchars($_POST["profiletext"]);	
				
				$updatesig = $forum->update_superuser_profiletext($profiletext, $user_logged_in_ID);
				header('Location:ucp.php?mode=personaltext');	
			}
			
			echo "<table>";
			echo "<form method='post'>";			
			echo "<tr><td><textarea name='profiletext' class='postarea profiletextarea'>".$user_logged_in['profiletext']."</textarea></td></tr>";
			echo "<tr><td><input type='submit' name='submit_profiletext' value='Gem ændringer'/></td></tr>";			
			echo "</form>";
			echo "</table>";
			
			echo "</div>";
			
		} //end profile text
		
		if(($_GET['mode']) == "avatar")
		{
			$errormsg = "";
			
			//Submitting new avatar
			if(isset($_POST['submit_profileavatar']))
			{
				if (!empty($_FILES['avatarimg']['name'])) {
					$validExtensions = array('.jpg', '.jpeg', '.gif', '.png');
					 // get extension of the uploaded file
					$fileExtension = strrchr($_FILES['avatarimg']['name'], ".");
					// check if file Extension is on the list of allowed ones
					if (in_array($fileExtension, $validExtensions)) {
						//image is allowed
						
						$newName = time() . '_' . $_FILES['avatarimg']['name'];
						$destination = 'images/avatars/' . $newName;
						if (move_uploaded_file($_FILES['avatarimg']['tmp_name'], $destination)) {
							//file copy successful
							
							list($origWidth, $origHeight) = getimagesize($destination);
							$maxWidth = 150;
							$maxHeight = 150;
							
							$widthRatio = $maxWidth / $origWidth;
							$heightRatio = $maxHeight / $origHeight;
						
							// Ratio used for calculating new image dimensions.
							$ratio = min($widthRatio, $heightRatio);
							
							// Calculate new image dimensions.
							$newWidth  = (int) ($origWidth  * $ratio);
							$newHeight = (int) ($origHeight * $ratio);
							
							$img = new abeautifulsite\SimpleImage($destination);
							$img->resize($newWidth, $newHeight);
							$img->save();
							$updateavatar = $forum->update_superuser_avatar($destination, $user_logged_in_ID);
							
							header('Location:ucp.php?mode=avatar');	
							
						}
						
					} else {
						$errormsg = "Den uploadede fil skal ende på .png, .jpg, .jpeg eller .gif";
					}
				}
				
				else
				{
					$newavatar = htmlspecialchars($_POST["avatar_url"]);	
					$img_formats = array("png", "jpg", "jpeg", "gif");//Etc. . . 
					$path_info = pathinfo($newavatar);
					
					if (!in_array(strtolower($path_info['extension']), $img_formats)) 
					{
					   $errormsg = "Den indtastede URL skal ende på .png, .jpg, .jpeg eller .gif";
					}
					
					else
					{
						list($width, $height, $type, $attr) = getimagesize($newavatar);
						if($height > 150 || $width > 150)
						{
							$errormsg = "Billedet er for stort. Det må maks være 150px i højden samt 150px i bredden.";
						}
						else
						{
							$updateavatar = $forum->update_superuser_avatar($newavatar, $user_logged_in_ID);
							header('Location:ucp.php?mode=avatar');	
						}
					}
				}
			}
			
			echo "<div class='category'><a href=''>Avatar</a></div>";
			echo "<div id='ucp_content' class='center'>";
			echo "Nuværende avatar: <br/>";
						
			if($user_logged_in['avatar'] != "")
			{
				echo "<img src='".$user_logged_in['avatar']."' alt='".$user_logged_in['name']."'/>";	
			}
			else
			{
				echo "Intet avatar";
			}
			
			echo "<h3>Nyt avatar</h3>";
			
			echo "<table>";
			echo "<form method='post' enctype='multipart/form-data' class='confirmform'>";
			
			echo "<tr><td><input type='file' name='avatarimg' id='fileToUpload' /></td></tr>";
			
			echo "<tr><td>Upload via URL: <br/>";
			echo "<input type='text' name='avatar_url'></td></tr>";
			
			echo "<tr><td><input type='submit' name='submit_profileavatar' value='Gem ændringer'/></td></tr>";
			
			echo "</form>";
			echo "</table>";
			
			echo "<span class='errormsg'>".$errormsg."</span>";	
			
			echo "<p class='smalltext'>Dit avatar kan max være 150px X 150px. 
			For at undgå underligt skalerede billeder, kræver KL, at dit billede på forhånd er denne størrelse, hvis du tilføjer billede via URL.<br/>
			Vi anbefaler at du benytter et billedbehandlingsprogram samt en gratis billede-hosting side for at opnå dette.<br/>
			Alternativt kan du uploade billedet direkte fra din computer.</p>";
			echo "</div>";
			
		} //end avatar
		
		if(($_GET['mode']) == "sig")
		{
			echo "<div class='category'><a href=''>Signatur</a></div>";				
			echo "<div id='ucp_content' class='center'>";
			
			if(isset($_POST['submit_profilesig']))
			{
				$sigtext = htmlspecialchars($_POST["signature"]);	
				
				$updatesig = $forum->update_superuser_signature($sigtext, $user_logged_in_ID);
				header('Location:ucp.php?mode=sig');		
			}
			
			echo "<table>";
			echo "<form method='post' class='confirmform'>";
			
			echo "<tr><td><textarea name='signature' class='postarea textarea_large' required>".$user_logged_in['signature']."</textarea></td></tr>";
			echo "<tr><td><input type='submit' name='submit_profilesig' value='Gem ændringer'/></td></tr>";			
			echo "</form>";
			echo "</table>";
			
			echo "</div>";
			
		} //end signature
		
		if(($_GET['mode']) == "konto")
		{
			$errormsg = "";
			
			if(isset($_POST['submit_profilecore']))
			{
				$error = false;
				
				$username = htmlspecialchars($_POST["username"]);	
				$email = htmlspecialchars($_POST['email']);
				$current = htmlspecialchars($_POST['password_current']);
				$pass = htmlspecialchars($_POST['password']);
				$confirm = htmlspecialchars($_POST['password_confirm']);
								
				if($username != $user_logged_in['name'])
				{
					$namecheck = $forum->check_for_existing_superuser_name($username)->fetch_assoc();
					if($namecheck['res'] > 0) { $error = true; $errormsg = $errormsg."Der eksisterer allerede en bruger med dette navn.<br/>";}
					else 
					{ 
						$updatename = $forum->update_superuser_name($username, $user_logged_in_ID);
					}	
				}
				
				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = true; $errormsg = $errormsg."Du skal indtaste en rigtig e-mail adressse.<br/>";}	
				else 
				{ 
					$updateemail = $forum->update_superuser_email($email, $user_logged_in_ID);
				}	
				
				if(!($pass == "" && $confirm == ""))
				{
					$hash = $user_logged_in['password'];
					if(password_verify ($current,$hash))
					{
						if($pass != $confirm || $pass == "" || $confirm == "") 
						{ $error = true; $errormsg = $errormsg."Værdierne indtastet i \"kodekord\" og \" bekræft kodekord\" var ikke ens.<br/>";}	
						else
						{
							$newhash = password_hash($pass, PASSWORD_DEFAULT);								
							$updatepass = $forum->update_superuser_password($newhash, $user_logged_in_ID);
						}
					}
					else
					{
						$error = true; $errormsg = $errormsg."Det indtastede nuværende kodeord var ikke korrekt.<br/>";
					}
				}
			}
			
			$userdata = $forum->get_superuser($user_logged_in_ID)->fetch_assoc();
			echo "<div class='category'><a href=''>Konto</a></div>";				
			echo "<div id='ucp_content'>";
			echo "<table>";
			echo "<form method='post' class='confirmform'>";
			
			echo "<tr><td>Brugernavn: </td>";
			echo "<td><input type='text' name='username' value='".htmlspecialchars($userdata['name'], ENT_QUOTES, 'UTF-8')."' maxlength='20' required></td></tr>";
			echo "<tr><td>E-mailadresse: </td>";
			echo "<td><input type='text' name='email' value='".htmlspecialchars($userdata['email'], ENT_QUOTES, 'UTF-8')."' required></td></tr>";
			
			echo "<tr><td>Nyt kodeord: </td>";
			echo "<td><input type='password' name='password' maxlength='20'></td></tr>";
			echo "<tr><td>Bekræft kodeord: </td>";
			echo "<td><input type='password' name='password_confirm' maxlength='20'></td></tr>";
			echo "<tr><td>Nuværende kodeord: </td>";
			echo "<td><input type='password' name='password_current' maxlength='20'>*</td></tr>";
			
			echo "<tr><td></td><td><input type='submit' name='submit_profilecore' value='Gem ændringer'/></td></tr>";
			
			echo "</form>";
			echo "</table>";
			
			echo "<span class='errormsg'>".$errormsg."</span>";	
			echo "<span class='smalltext'><br/>*Felterne med kodeord skal kun udfyldes, hvis du ønsker at ændre kodeordet.</span>";
			
			echo "</div>";
			
		} //end konto	
		
		echo "</div>";	
		echo "</div>";
		
	} //end no menu
	
	if($_GET['menu'] == "char")
	{
		$selectedchar = 0;
		$postedchar = 0;
		$charcount = 0;
		if(isset($_GET['characterselect'])) { $postedchar = $_GET['characterselect']; }	
				
		echo "<div id='ucpwrap'>";
		
		echo "<div id='sidemenu'>";		
		echo "<div class='category'><a href=''>Menu</a></div>";
		echo "<div id='sidemenu_content'>";
		echo "<form method='get'>";
		echo "<input type='hidden' name='menu' value='char' />";
		$mode = "none"; if(isset($_GET['mode'])) { $mode = $_GET['mode'];}
		echo "<input type='hidden' name='mode' value='".$mode."' />";
		echo "Vælg karakter: <br/><select name='characterselect'  onchange='this.form.submit();'>";
        
		$activechars = $forum->get_characters_from_superuser_to_profile_edit($user_logged_in_ID, 1, 0, 1);
		$inactivechars = $forum->get_characters_from_superuser_to_profile_edit($user_logged_in_ID, 0, 0, 1);
		$deadchars = $forum->get_characters_from_superuser_to_profile_edit($user_logged_in_ID, 0, 1, 1);
		$notacceptedchars = $forum->get_characters_from_superuser_to_profile_edit($user_logged_in_ID, 1, 0, 0);
		
		//Putting all characters into the dropdown menu grouped by: active, inactive, dead, not accepted
		//Charcount and get_first_character_from_superuser_to_profile_edit is used to get the first item in the dropdown
		
		$numberofchars = $forum->count_characters_from_superuser_to_profile_edit($user_logged_in_ID, 1, 0, 1)->fetch_assoc();
		echo $numberofchars['res'];
		if($numberofchars['res'] > 0) { echo "<optgroup label='Aktive'>";  
			$firstchar = $forum->get_first_character_from_superuser_to_profile_edit($user_logged_in_ID, 1, 0, 1)->fetch_assoc(); $selectedchar = $firstchar['character_ID'];
				while($char = $activechars->fetch_assoc()) { $charcount++; 
					if($postedchar == $char['character_ID']) { echo "<option value='".$char['character_ID']."' selected>".$char['name']."</option>"; }
					else { echo "<option value='".$char['character_ID']."' >".$char['name']."</option>"; } } 
					echo "</optgroup>"; }
		$numberofchars = $forum->count_characters_from_superuser_to_profile_edit($user_logged_in_ID, 0, 0, 1)->fetch_assoc();
		if($numberofchars['res'] > 0) { echo "<optgroup label='Inaktive'>";  
			if($charcount == 0) { $firstchar = $forum->get_first_character_from_superuser_to_profile_edit($user_logged_in_ID, 1, 0, 1)->fetch_assoc(); $selectedchar = $firstchar['character_ID']; }
				while($char = $inactivechars->fetch_assoc()) { $charcount++; 
					if($postedchar == $char['character_ID']) { echo "<option value='".$char['character_ID']."' selected>".$char['name']."</option>"; }
					else { echo "<option value='".$char['character_ID']."' >".$char['name']."</option>"; } } 
					echo "</optgroup>"; }		
		$numberofchars = $forum->count_characters_from_superuser_to_profile_edit($user_logged_in_ID, 0, 1, 1)->fetch_assoc();
		if($numberofchars['res'] > 0) { echo "<optgroup label='Døde'>";  
			if($charcount == 0) { $firstchar = $forum->get_first_character_from_superuser_to_profile_edit($user_logged_in_ID, 0, 1, 1)->fetch_assoc(); $selectedchar = $firstchar['character_ID']; }
				while($char = $deadchars->fetch_assoc()) { $charcount++; 
					if($postedchar == $char['character_ID']) { echo "<option value='".$char['character_ID']."' selected>".$char['name']."</option>"; }
					else { echo "<option value='".$char['character_ID']."' >".$char['name']."</option>"; } } 
					echo "</optgroup>"; }
		$numberofchars = $forum->count_characters_from_superuser_to_profile_edit($user_logged_in_ID, 1, 0, 0)->fetch_assoc();
		if($numberofchars['res'] > 0) { echo "<optgroup label='Endnu ikke godkendte'>";  
			if($charcount == 0) { $firstchar = $forum->get_first_character_from_superuser_to_profile_edit($user_logged_in_ID, 1, 0, 0)->fetch_assoc(); $selectedchar = $firstchar['character_ID']; }
				while($char = $notacceptedchars->fetch_assoc()) { $charcount++; 
					if($postedchar == $char['character_ID']) { echo "<option value='".$char['character_ID']."' selected>".$char['name']."</option>"; }
					else { echo "<option value='".$char['character_ID']."' >".$char['name']."</option>"; } } 
					echo "</optgroup>"; }
					
		if($postedchar != 0) { $selectedchar = $postedchar; }	
		$correct_character = true;
		//Getting data from the selected character
		
		if($selectedchar != 0)
		{
			$currentchar = $forum->get_character($selectedchar)->fetch_assoc();	
			//check if the selected char actually belongs to the superuser 
			if($user_logged_in_ID != $currentchar['fk_superuser_ID']) 
			{ 
				header('Location:ucp.php?mode=char');	
			}
			else { $correct_character = true;}
		}				
		echo "</select><br/>";
		echo "</form>";
		echo "<a href='ucp.php?menu=char&mode=profile&characterselect=".$selectedchar."'>Profiltekst</a>";
		echo "<a href='ucp.php?menu=char&mode=avatar&characterselect=".$selectedchar."'>Avatar</a>";
		echo "<a href='ucp.php?menu=char&mode=sig&characterselect=".$selectedchar."'>Signatur</a>";
		echo "<a href='ucp.php?menu=char&mode=settings&characterselect=".$selectedchar."'>Indstillinger</a>";
		echo "</div>";
		echo "</div>";
		
		echo "<div id='ucp_page'>";
		
		if(!isset($_GET['mode']))
		{
			echo "<div class='category'><a href=''>Mine karakterer</a></div>";
			echo "<div id='ucp_content'>";
			echo "<p class='center'>Her kan du ændre i de karakterer, du har i rollespillet.<br/>
			Ønsker du at oprette en ny karakter i stedet, så benyt den ovenstående menu.</p>";
			echo "</div>";
			
		}
		
		
		if($correct_character)
		{	
		
			if(($_GET['mode']) == "none")
			{
				echo "<div class='category'><a href=''>Mine karakterer</a></div>";
				echo "<div id='ucp_content'>";
				echo "<p class='center'>Her kan du ændre i de karakterer, du har i rollespillet.<br/>
				Ønsker du at oprette en ny karakter i stedet, så benyt den ovenstående menu.</p>";
				echo "</div>";
				
			}
			
			if(($_GET['mode']) == "settings")
			{
				echo "<div class='category'><a href=''>Indstillinger</a></div>";
				echo "<div id='ucp_content' class='center'>";
				
				if(isset($_GET['characterselect'])) { $selectedchar = $_GET['characterselect']; }
				if($selectedchar == 0)
	
				{	
					echo "Du kan ikke udføre denne handling, når du endnu ikke har tilknyttet nogen karakterer til din bruger.";		
				}
	
				else
				{	

				$currentchar = $forum->get_character($selectedchar)->fetch_assoc();	
				
				if($_POST['submit_chardead'])
				{
					if($currentchar['accepted'] == 1) //A non-accepted character must never be dead
					{
						//A dead character must also be inactive
						$updateactivestatus = $forum->update_character_active_status($currentchar['character_ID'], 0);
						$updatedeadstatus = $forum->update_character_dead_status($currentchar['character_ID'], 1);
						$setcolor = $forum->update_character_color($deadcolor, $currentchar['character_ID']);
						header('Location:ucp.php?menu=char&mode=settings&characterselect='.$currentchar['character_ID']);
					}
				}
				if($_POST['submit_charalive'])
				{
					$updateactivestatus = $forum->update_character_active_status($currentchar['character_ID'], 1);
					$updatedeadstatus = $forum->update_character_dead_status($currentchar['character_ID'], 0);
					
					//If the char has a default group, we need to change its color to the group color.
					$user_has_default_group = $forum->check_users_default_group($currentchar['character_ID'])->fetch_assoc();
					if($user_has_default_group['res'] > 0)
					{
						$defaultgroup = $forum->get_users_default_group($currentchar['character_ID'])->fetch_assoc(); 
						$defgroupdata = $forum->get_group($defaultgroup['fk_group_ID'])->fetch_assoc();
						$setcolor = $forum->update_character_color($defgroupdata['color'], $currentchar['character_ID']);
					}
					//Otherwise, no color.
					else
					{
						$setcolor = $forum->update_character_color("", $currentchar['character_ID']);
					}
					header('Location:ucp.php?menu=char&mode=settings&characterselect='.$currentchar['character_ID']);
				}
				if($_POST['submit_chardelete'])
				{
					$deletecharapprovalrequests = $forum->delete_approvalrequests_from_char($currentchar['character_ID']);
					$deletechargroupmemberships = $forum->delete_groupmemberships_from_char($currentchar['character_ID']);
					$deletetags = $forum->delete_tags_from_char($currentchar['character_ID']);
					$deleteprofiledata = $forum->delete_profiledata_from_char($currentchar['character_ID']); 
					$deleteachievements = $forum->delete_userachievements_from_character($currentchar['character_ID']);
					$deletebounties = $forum->remove_bounty_from_character($currentchar['character_ID']);
					$forum->delete_ic_chat_messages_from_char($currentchar['character_ID']);
					//finally delete the char
					$deletechar = $forum->delete_character($currentchar['character_ID']);
					header('Location:ucp.php?menu=char');
				}
								
				echo "<p>Din karakter er i øjeblikket angivet som ";
				if($currentchar['dead'] == 1) { echo "<span class='italic bold'>død</span>."; }
				else if($currentchar['accepted'] == 0) { echo "<span class='italic bold'>ikke godkendt</span>."; }
				else if($currentchar['active'] == 1) { echo "<span class='italic bold'>aktiv</span>."; } 
				else { echo "<span class='italic bold'>inaktiv</span>."; }
				echo "</p>";

				if($currentchar['dead'] == 0)
				{
					if($currentchar['accepted'] == 1)
					{
						echo "<form method='post'><input type='submit' value='Angiv som død' name='submit_chardead'"; ?>
						onclick='return confirm("Er du sikker på, at du vil angive karakteren som værende død? Det vil ikke længere være muligt at skrive med den.")'
						<?php echo "/>*</form>";
						echo "<br/><br/>";
						echo "<p class='smallertext'>* Hvis du angiver din karakter som død, kan du ikke længere skrive nye posts med den, 
						og den vil ikke længere optræde på listen af karakterer på din personlige profil.</p>";	
					}
					else
					{
						echo "<p class='italic'>Du skal først have din karakter godkendt af en admin eller moderator</p>";	
					}
				}
				else
				{
					echo "<form method='post'><input type='submit' value='Genopliv karakter' name='submit_charalive'/></form>";
				}
				
				$characterposts = $forum->count_all_posts_from_character($currentchar['character_ID'])->fetch_assoc();
				if($characterposts['res'] < 1)
				{
					echo "<form method='post'><input class='deletebutton' type='submit' value='Slet karakter' name='submit_chardelete'"; ?>
						onclick='return confirm("Er du sikker på, at du vil slette karakteren? Dette kan ikke fortrydes.")'
						<?php echo "/></form>";
						echo "<br/>";
				}
				echo "</div>";
				
				}
				
			}
				
			
			if(($_GET['mode']) == "avatar")
	
			{
				echo "<div class='category'><a href=''>Avatar</a></div>";
				echo "<div id='ucp_content' class='center'>";
				
				$selectedchar = 0;
	
				if(isset($_GET['characterselect'])) { $selectedchar = $_GET['characterselect']; }
				if($selectedchar == 0)
	
				{	
					echo "Du kan ikke udføre denne handling, når du endnu ikke har tilknyttet nogen karakterer til din bruger.";		
				}
	
				else
				{	
	
					$currentchar = $forum->get_character($selectedchar)->fetch_assoc();	
					$errormsg = "";
	
					//Submitting new avatar
					if(isset($_POST['submit_profileavatar']))	
					{
						if (!empty($_FILES['avatarimg']['name'])) 
						{
							$validExtensions = array('.jpg', '.jpeg', '.gif', '.png');
							 // get extension of the uploaded file
							$fileExtension = strrchr($_FILES['avatarimg']['name'], ".");
							// check if file Extension is on the list of allowed ones
							if (in_array($fileExtension, $validExtensions)) {
								//image is allowed
								
								$newName = time() . '_' . $_FILES['avatarimg']['name'];
								$destination = 'images/characteravatars/' . $newName;
								if (move_uploaded_file($_FILES['avatarimg']['tmp_name'], $destination)) {
									//file copy successful
									
									list($origWidth, $origHeight) = getimagesize($destination);
									$maxWidth = 150;
									$maxHeight = 200;
									
									$widthRatio = $maxWidth / $origWidth;
									$heightRatio = $maxHeight / $origHeight;
								
									// Ratio used for calculating new image dimensions.
									$ratio = min($widthRatio, $heightRatio);
									
									// Calculate new image dimensions.
									$newWidth  = (int) ($origWidth  * $ratio);
									$newHeight = (int) ($origHeight * $ratio);
									
									$img = new abeautifulsite\SimpleImage($destination);
									$img->resize($newWidth, $newHeight);
									$img->save();
									$updateavatar = $forum->update_character_avatar($destination, $selectedchar);
									
									header('Location:ucp.php?menu=char&mode=avatar&characterselect='.$selectedchar);	
									
								}
								
							}	 
							else {
								$errormsg = "Den uploadede fil skal ende på .png, .jpg, .jpeg eller .gif";
							}
						}
				
					else
					{
							$newavatar = htmlspecialchars($_POST["avatar_url"]);	
							$img_formats = array("png", "jpg", "jpeg", "gif");//Etc. . . 
							$path_info = pathinfo($newavatar);
		
							if (!in_array(strtolower($path_info['extension']), $img_formats)) 
							{
							   $errormsg = "Den indtastede URL skal ende på .png, .jpg, .jpeg eller .gif";
							}
		
							else	
							{	
								list($width, $height, $type, $attr) = getimagesize($newavatar);	
								if($height > 200 || $width > 150)	
								{	
									$errormsg = "Billedet er for stort. Det må maks være 200px i højden samt 150px i bredden.";	
								}	
								else
								{
									$updateavatar = $forum->update_character_avatar($newavatar, $selectedchar);
									header('Location:ucp.php?menu=char&mode=avatar&characterselect='.$selectedchar);		
								}
							}					
						}
					}
	

					echo "<span class='bold'>Nuværende avatar: </span><br/>";
					if($currentchar['avatar'] != "")	
					{
						echo "<img src='".$currentchar['avatar']."' alt='".$currentchar['name']."'/>";	
					}
	
					else
					{
						echo "Intet avatar";
					}
	
					echo "<h3>Nyt avatar</h3>";
					echo "<table>";
	
					echo "<form method='post' enctype='multipart/form-data' class='confirmform'>";
			
					echo "<tr><td><input type='file' name='avatarimg' id='fileToUpload' /></td></tr>";
	
					echo "<tr><td>Upload via URL:<br/>";
	
					echo "<input type='text' name='avatar_url'></td></tr>";
					echo "<tr><td><input type='submit' name='submit_profileavatar' value='Gem ændringer'/></td></tr>";
					echo "</form>";
	
					echo "</table>";
					echo "<span class='errormsg'>".$errormsg."</span>";	
	
					echo "<p class='smalltext'>Dit avatar kan max være 200px højt og 150px bredt. For at undgå underligt skalerede billeder, kræver KL, at dit billede på forhånd er denne størrelse, hvis du tilføjer billede via URL.<br/>
			Vi anbefaler at du benytter et billedbehandlingsprogram samt en gratis billede-hosting side for at opnå dette.<br/>
			Alternativt kan du uploade billedet direkte fra din computer.</p>";
	
					echo "</div>";
	
				}
	
			} //end avatar
	
			
	
			if(($_GET['mode']) == "sig")
	
			{
				
				echo "<div class='category'><a href=''>Signatur</a></div>";
				echo "<div id='ucp_content' class='center'>";
	
				$selectedchar = 0;
				if(isset($_GET['characterselect'])) { $selectedchar = $_GET['characterselect']; }
				if($selectedchar == 0)
				{
					echo "Du kan ikke udføre denne handling, når du endnu ikke har tilknyttet nogen karakterer til din bruger.";		
				}
	
				else
				{	
					$currentchar = $forum->get_character($selectedchar)->fetch_assoc();			
					if(isset($_POST['submit_profilesig']))
					{
						$sigtext = htmlspecialchars($_POST["signature"]);						
						$updatesig = $forum->update_character_signature($sigtext, $selectedchar);
						header('Location:ucp.php?menu=char&mode=sig&characterselect='.$selectedchar);	
					}
	
					echo "<table>";
					echo "<form method='post' class='confirmform'>";
					echo "<tr><td><textarea name='signature' class='postarea textarea_large' required>".$currentchar['signature']."</textarea></td></tr>";
					echo "<tr><td><input type='submit' name='submit_profilesig' value='Gem ændringer'/></td></tr>";			
					echo "</form>";	
					echo "</table>";
	
				echo "</div>";
	
				}
	
				
	
			} //end signature
	
					
	
			if(($_GET['mode']) == "profile")
	
			{			
	
				/* PROFILE DATA COMES HERE */
	
				echo "<div class='category'><a href=''>Profiltekst</a></div>";
				echo "<div id='ucp_content'>";
	
				$selectedchar = 0;
	
				if(isset($_GET['characterselect'])) { $selectedchar = $_GET['characterselect']; }
	
				if($selectedchar == 0)
	
				{
	
					echo "Du kan ikke udføre denne handling, når du endnu ikke har tilknyttet nogen karakterer til din bruger.";	
	
				}
	
				else
	
				{	
	
					$currentchar = $forum->get_character($selectedchar)->fetch_assoc();	
	
					$profiledata = $forum->get_character_profiledata($selectedchar)->fetch_assoc();	
	
	
					
	
					if(isset($_GET['full']) || $currentchar['accepted'] != 1)
	
					{
						
						$errormsg = "";
						if(isset($_GET['nameerror'])) { $errormsg = "Ændringen af din karakters viste forumnavn blev ikke fuldført. Der eksisterer allerede en karakter med dette navn!"; }
						if(isset($_GET['submissionerror'])) { $errormsg = "Karakteren bliv ikke sendt til godkendelse. Du mangler at udfylde felter i profilskemaet."; }
						if(isset($_GET['nameerror']) && isset($_GET['submissionerror'])) {$errormsg = "Karakteren bliv ikke sendt til godkendelse. Du mangler at udfylde felter i profilskemaet.
						<br/><br/> Ændringen af din karakters viste forumnavn blev ikke fuldført. Der eksisterer allerede en karakter med dette navn!"; }
						
						
						if($_POST['submit_changes'])
						{
							//update character name
							$charname = htmlspecialchars($_POST["charactername_forum"]);
							
							$nameerror = false;
							
							if($charname != $currentchar['name'])
							{
								$nameexists = $forum->check_for_existing_character_name($charname)->fetch_assoc();
								if ($nameexists['res'] > 0) { $nameerror = true; }
								else
								{
									$newname = $forum->update_character_forumname($charname, $selectedchar);
								}
								
							}
													
							//update profiledata
							$fullname = htmlspecialchars($_POST["charactername_full"]);
							$shortname = htmlspecialchars($_POST["charactername_short"]);
							$age = $_POST["characterage"];
							$gender = htmlspecialchars($_POST["charactergender"]);
							$birthday = htmlspecialchars($_POST["characterbirthday"]);
							$faith = htmlspecialchars($_POST["characterfaith"]);
							$alignment = htmlspecialchars($_POST["characteralignments"]);
							$profession = htmlspecialchars($_POST["characterprofession"]);
							$race = $_POST["race"];
							$raceinfo= htmlspecialchars($_POST["raceinfo"]);
							$height = $_POST["characterheight"];
							$weight = $_POST["characterweight"];
							$looks = htmlspecialchars($_POST["characterlooks"]);
							$magic1 = htmlspecialchars($_POST["charactermagic1"]);
							$magic2 = htmlspecialchars($_POST["charactermagic2"]);
							$magic1_skill = $_POST["charactermagic1_skill"];
							$magic2_skill = $_POST["charactermagic2_skill"];
							$personality = htmlspecialchars($_POST["characterpersonality"]);
							$story = htmlspecialchars($_POST["characterstory"]);
							$family = htmlspecialchars($_POST["characterfamily"]);
							$habitat = htmlspecialchars($_POST["characterhabitat"]);
							$other = htmlspecialchars($_POST["characterother"]);
							
							$str = $_POST["skill_strength"];
							$weap = $_POST["skill_weapons"];
							$flx = $_POST["skill_flexibility"];
							$end = $_POST["skill_endurance"];
							$tact = $_POST["skill_tactics"];
							$int = $_POST["skill_intelligence"];
							$crea = $_POST["skill_creativity"];
							$men = $_POST["skill_mental"];
							$cha = $_POST["skill_chakra"];
							
							echo "wat wat";
							
							$updateinfo = $forum->update_character_profiledata_full($fullname, $shortname, $age, $gender, $birthday, $faith, $alignment, $profession, $race, $raceinfo, 
							$height, $weight, $looks, $magic1, $magic2, $magic1_skill, $magic2_skill, $personality, $story, $family, $habitat, $other, $str, $weap, $flx, $end, $tact, 
							$int, $crea, $men, $cha, $selectedchar);
							
							if($nameerror) { header('Location:ucp.php?menu=char&mode=profile&characterselect='.$selectedchar.'&full&nameerror');	}
							else { header('Location:ucp.php?menu=char&mode=profile&characterselect='.$selectedchar.'&full');}
								
						}
						
						if($_POST['submit_for_approval'])
						{
							//update character name
							$charname = htmlspecialchars($_POST["charactername_forum"]);
							
							$nameerror = false;
							
							if($charname != $currentchar['name'])
							{
								$nameexists = $forum->check_for_existing_character_name($charname)->fetch_assoc();
								if ($nameexists['res'] > 0) { $nameerror = true; }
								else
								{
									$newname = $forum->update_character_forumname($charname, $selectedchar);
								}
								
							}
													
							//update profiledata
							$fullname = htmlspecialchars($_POST["charactername_full"]);
							$shortname = htmlspecialchars($_POST["charactername_short"]);
							$age = $_POST["characterage"];
							$gender = htmlspecialchars($_POST["charactergender"]);
							$birthday = htmlspecialchars($_POST["characterbirthday"]);
							$faith = htmlspecialchars($_POST["characterfaith"]);
							$alignment = htmlspecialchars($_POST["characteralignments"]);
							$profession = htmlspecialchars($_POST["characterprofession"]);
							$race = $_POST["race"];
							$raceinfo= htmlspecialchars($_POST["raceinfo"]);
							$height = $_POST["characterheight"];
							$weight = $_POST["characterweight"];
							$looks = htmlspecialchars($_POST["characterlooks"]);
							$magic1 = htmlspecialchars($_POST["charactermagic1"]);
							$magic2 = htmlspecialchars($_POST["charactermagic2"]);
							$magic1_skill = $_POST["charactermagic1_skill"];
							$magic2_skill = $_POST["charactermagic2_skill"];
							$personality = htmlspecialchars($_POST["characterpersonality"]);
							$story = htmlspecialchars($_POST["characterstory"]);
							$family = htmlspecialchars($_POST["characterfamily"]);
							$habitat = htmlspecialchars($_POST["characterhabitat"]);
							$other = htmlspecialchars($_POST["characterother"]);
							
							$str = $_POST["skill_strength"];
							$weap = $_POST["skill_weapons"];
							$flx = $_POST["skill_flexibility"];
							$end = $_POST["skill_endurance"];
							$tact = $_POST["skill_tactics"];
							$int = $_POST["skill_intelligence"];
							$crea = $_POST["skill_creativity"];
							$men = $_POST["skill_mental"];
							$cha = $_POST["skill_chakra"];
							
							$updateinfo = $forum->update_character_profiledata_full($fullname, $shortname, $age, $gender, $birthday, $faith, $alignment, $profession, $race, $raceinfo, 
							$height, $weight, $looks, $magic1, $magic2, $magic1_skill, $magic2_skill, $personality, $story, $family, $habitat, $other, $str, $weap, $flx, $end, $tact, 
							$int, $crea, $men, $cha, $selectedchar);
							
							if($fullname == "" || $shortname == "" || $age == "" || $gender == "" || $birthday == "" || $faith == "" || $alignment == "" || $profession == "" || $race == ""
							|| $height == "" || $weight == "" || $looks == "" || $magic1 == "" || $magic2 == "" || $magic1_skill == "" || $magic2_skill == "" || $personality == "" || 
							$story == "" || $family == "" || $habitat == "")
							{
								if($nameerror) { header('Location:ucp.php?menu=char&mode=profile&characterselect='.$selectedchar.'&full&nameerror&submissionerror'); }
								else { header('Location:ucp.php?menu=char&mode=profile&characterselect='.$selectedchar.'&full&submissionerror');}
								break;	
							}
																				
							if($currentchar['accepted'] == 1)
							{
								$disapproved = $forum->update_character_accepted_status($selectedchar, 0);	
							}
							$approval = $forum->submit_for_approval($selectedchar, $profiledata);
													
							if($nameerror) { header('Location:ucp.php?menu=char&mode=submitted&nameerror'); }
							else { header('Location:ucp.php?menu=char&mode=submitted');}
								
						}
	
	
						echo "<table><form method='post' class='confirmform'>";
	
						echo "<span class='errormsg'>".$errormsg."</span>";	
					
	
						echo "<h3>Grundlæggende karakterinformation</h3>";
	
					
	
						/* Character forum name */
	
						echo "<tr><td class='tableleft'><span class='bold'>Viste karakternavn:</span>
						 <span class='tablesubtext'>Det karakternavn, der vises på forummet.</span></td>"; 
	
						echo "<td><input type='text' name='charactername_forum' value='".htmlspecialchars($currentchar['name'], ENT_QUOTES, 'UTF-8')."' maxlength='25'></td></tr>";	
	
							
	
						/* Full character name */	
	
						echo "<tr><td class='tableleft'><span class='bold'>Fulde karakternavn:</span>
						 <span class='tablesubtext'>Karakterens fornavn og efternavn samt eventuelle mellemnavne.</span></td>"; 
	
						echo "<td><input type='text' name='charactername_full' value='".htmlspecialchars($profiledata['fullname'], ENT_QUOTES, 'UTF-8')."'></td></tr>";		
	
						
	
						/* Character nicknames */
	
						echo "<tr><td class='tableleft'><span class='bold'>Kaldet: </span>
						<span class='tablesubtext'>Hvad din karakter til dagligt kaldes af andre karakterer.</span></td>"; 
	
						echo "<td><input type='text' name='charactername_short' value='".htmlspecialchars($profiledata['shortname'], ENT_QUOTES, 'UTF-8')."'></td></tr>";	
	
						
	
						/* Character age */
	
						echo "<tr><td class='tableleft'><span class='bold'>Alder:</span>
						 <span class='tablesubtext'>Din karakters alder. Se eventuelt raceinformation, hvis du spiller andet end menneske.</span></td>"; 
	
						echo "<td><input type='number' name='characterage' min='1' value='".$profiledata['age']."'></td></tr>";	
	
						
	
						/* Character gender */
	
						$genders = array("Mand", "Kvinde", "Intetkøn");
	
						echo "<tr><td class='tableleft'><span class='bold'>Køn:</span> <span class='tablesubtext'></span></td>"; 
	
						echo "<td><select name='charactergender'>";
	
						foreach($genders as $g) 
	
						{ 
	
							if($g == $profiledata['gender']) { echo "<option value='".$g."' selected>".$g."</option>"; } else { echo "<option value='".$g."'>".$g."</option>"; }
	
						}					
	
						echo "</td></tr>";	

	
						/* Character birthday */
	
						echo "<tr><td class='tableleft'><span class='bold'>Fødselsdag:</span> 
						<span class='tablesubtext'>Behøver blot indeholde dag og måned. Du skal bruge dette til at holde styr på din karakters alder.</span></td>";
	
						echo "<td><input type='text' name='characterbirthday' value='".htmlspecialchars($profiledata['birthday'], ENT_QUOTES, 'UTF-8')."'></td></tr>";	
	
						
	
						/* Character faith*/
	
						echo "<tr><td class='tableleft'><span class='bold'>Tro: </span>
						<span class='tablesubtext'>Hvilken tro har din karakter? Husk at læse om Krystallandets trosretninger, før du udfylder dette.</span></td>"; 
	
						echo "<td><input type='text' name='characterfaith' value='".htmlspecialchars($profiledata['faith'], ENT_QUOTES, 'UTF-8')."'></td></tr>";	
	
						
	
						/* Character aligment */
	
						$alignments = 
						array("Retmæssig Ond", "Neutral Ond", "Kaotisk Ond", "Retmæssig Neutral", "Sand Neutral", "Kaotisk Neutral", "Retmæssig God", "Neutral God", "Kaotisk God");
	
						echo "<tr><td class='tableleft'><span class='bold'>Tilhørsforhold:</span>
						 <span class='tablesubtext'>Du kan læse nærmere om tilhørsforholdene under karakterinformation.</span></td>"; 
	
						echo "<td><select name='characteralignments'>";
	
						foreach($alignments as $a) 
	
						{ 
	
							if($a == $profiledata['alignment']) { echo "<option value='".$a."' selected>".$a."</option>"; } else { echo "<option value='".$a."'>".$a."</option>"; }
	
						}					
	
						echo "</td></tr>";	
	
						
	
						/* Character profession*/
	
						echo "<tr><td class='tableleft'><span class='bold'>Erhverv:</span> 
						<span class='tablesubtext'>Hvad arbejder din karakter med? F.eks. bonde, kriger, adelig..</span></td>"; 
	
						echo "<td><input type='text' name='characterprofession' value='".htmlspecialchars($profiledata['profession'], ENT_QUOTES, 'UTF-8')."'></td></tr>";	
	
								
	
						/* Character race */		
	
						echo "<tr><td class='tableleft'><span class='bold'>Race:</span> </td>"; $races = $forum->get_all_races(); 
	
						echo "<td><select name='race' id='race'>"; while($r = $races->fetch_assoc()) 
	
							{ 
	
								if ($r['race_ID'] == $profiledata['fk_race_ID']) { echo "<option value='".$r['race_ID']."' selected>".$r['name']."</option>"; }
	
								else { echo "<option value='".$r['race_ID']."'>".$r['name']."</option>"; }						
	
							}
	
						echo "</select>";		
									
	
						echo "<tr class='raceinfo_tr'><td colspan='2'><span class='bold'>Uddybende om race: </span>
	
						<span id='werewolf' class='tablesubtext'>Hvilken race, din karakter er, udover varulveforbandelsen.<br/> Bemærk at ikke alle er tilladte!</span>
	
						<span id='special' class='tablesubtext'>Navnet på racen samt lidt information om udseende og historie.</span>
	
						<span id='vampire' class='tablesubtext'>Hvilken race, din karakter er, udover vampyrforbandelsen.<br/> Bemærk at ikke alle er tilladte!</span>
	
						<span id='animal' class='tablesubtext'>Hvilken dyreart, din halvdyrskarakter er.</span>
						
						<span id='angel' class='tablesubtext'>Hvilken race, din karakter var, før han eller hun døde og genopstod.</span>
	
						<span id='combo' class='tablesubtext'>Hvilke racer, din karakter er en blanding af.</span></td></tr>"; 
						
						echo "<tr class='raceinfo_tr'><td colspan='2'><textarea name='raceinfo' class='textarea_small'>".$profiledata['raceinfo']."</textarea></td></tr>";	
	
						// Script to show extra race information //
	
						echo " <script> 
	
								function hide() { $('.raceinfo_tr').hide();  $('#angel').hide(); $('#werewolf').hide(); $('#special').hide(); $('#vampire').hide(); $('#animal').hide(); $('#combo').hide();}															
								
								function show() {
								
									if( $('#race').val()==='25'){
		
									hide();	
		
									$('.raceinfo_tr').show(),
		
									$('#special').show()
		
									}
									else if( $('#race').val()==='24'){
		
									hide();	
		
									$('.raceinfo_tr').show(),
		
									$('#combo').show()
		
									}	
		
									else if( $('#race').val()==='18'){
		
									hide();	
		
									$('.raceinfo_tr').show(),
		
									$('#vampire').show()
		
									}	
		
									else if( $('#race').val()==='19'){
		
									hide();	
		
									$('.raceinfo_tr').show(),
		
									$('#werewolf').show()
		
									}	
		
									else if( $('#race').val()==='8'){
		
									hide();	
		
									$('.raceinfo_tr').show(),
		
									$('#animal').show()
		
									}	
									
									else if( $('#race').val()==='7'){
		
									hide();	
		
									$('.raceinfo_tr').show(),
		
									$('#angel').show()
		
									}
										
									else{	
									
									hide();	
									
									}							
								}
								
								show();							
				
								$('#race').on('change',function(){
	
								show();
	
							});</script>";		
			
	
						echo "</td></tr>";
	
						echo "</table>"; echo "<hr/>";
	
						
	
						/* CHARACTER LOOKS */
	
						
	
						echo "<h3>Udseende</h3>";
	
						echo "<table>";
	
					
	
						/* Character height */
	
						echo "<tr><td class='tableleft'><span class='bold'>Højde:</span> <span class='tablesubtext'>Karakterens højde i cm.</span></td>"; 
	
						echo "<td><input type='number' name='characterheight' min='0' value='".$profiledata['height']."'></td></tr>";		
	
						
	
						/* Character weight */
	
						echo "<tr><td class='tableleft'><span class='bold'>Vægt:</span> <span class='tablesubtext'>Karakterens vægt i kilogram.</span></td>"; 
	
						echo "<td><input type='number' name='characterweight' min='0' value='".$profiledata['weight']."'></td></tr>";
	
						
	
						/* Character looks */
	
						echo "<tr><td colspan='2'><span class='bold'>Udseende: </span>
	
						<span class='tablesubtext'>Beskriv din karakters udseende. Skal indholde hud-, hår- og øjenfarve, kropsbygning og eventuelle særlige kendetegn.
	
						<br/> Det er muligt at bruge bbcodes som [b][/b], [i][/i] og [img][/img]</span></td></tr>"; 
	
						echo "<tr><td colspan='2'><textarea name='characterlooks' class='textarea_large'>".$profiledata['looks']."</textarea></td></tr>";	
	
						
	
						echo "</table>"; echo "<hr/>";
	
						
	
						/* CHARACTER MAGIC */
	
						
	
						echo "<h3>Magiske evner</h3>";
	
						echo "<table>";
	
						
	
						/* Character magic 1 */
	
						echo "<tr><td colspan='2'><span class='bold'>Magisk evne (1): </span>
	
						<span class='tablesubtext'>Beskriv din karakters første magiske evne. Jo mere du går i deltaljer, jo mere sandsynligt er det, at evnen bliver godkendt.
						Jo stærkere evnen er, jo højere chakra (færdighedspoint) skal din karakter have.
	
						<br/> Det er muligt at bruge bbcodes som [b][/b], [i][/i] og [img][/img]</span></td></tr>"; 
	
						echo "<tr><td colspan='2'><textarea name='charactermagic1' class='textarea_small'>".$profiledata['magic1']."</textarea></td></tr>";	
	
						
	
						/* Character magic skill 1 */
	
						echo "<tr><td class='tableleft'><span class='bold'>Dygtighed til evne (1):</span> <span class='tablesubtext'>Angiv på en skala fra 1-10, 
	
						hvor god din karakter er til at styre sin første evne. </span></td>"; 
	
						echo "<td><input type='number' class='numberinput_small' name='charactermagic1_skill' min='0' max='10' value='".$profiledata['magic1_skill']."'>
	
						*</td></tr>";	
						
	
						/* Character magic 2 */
	
						echo "<tr><td colspan='2'><span class='bold'>Magisk evne (2):</span> 
	
						<span class='tablesubtext'>Beskriv din karakters anden magiske evne. Jo mere du går i deltaljer, jo mere sandsynligt er det, at evnen bliver godkendt.
						Jo stærkere evnen er, jo højere chakra (færdighedspoint) skal din karakter have.
	
						<br/> Det er muligt at bruge bbcodes som [b][/b], [i][/i] og [img][/img]</span></td></tr>"; 
	
						echo "<tr><td colspan='2'><textarea name='charactermagic2' class='textarea_small'>".$profiledata['magic2']."</textarea></td></tr>";
	
						
	
						/* Character magic skill 2 */
	
						echo "<tr><td class='tableleft'><span class='bold'>Dygtighed til evne (2): </span><span class='tablesubtext'>Angiv på en skala fra 1-10, 
	
						hvor god din karakter er til at styre sin anden evne. </span></td>"; 
	
						echo "<td><input type='number' class='numberinput_small' name='charactermagic2_skill' min='0' max='10' value='".$profiledata['magic2_skill']."'>
	
						*</td></tr>";
	
										
	
						echo "</table>"; 
	
						echo "<span class='smalltext'><br/>*En dygtighed på 0 til at kontrollere en magisk evne, svarer til, at evnen er passiv. 
	
						Er dygtigheden 6 eller mere, skal træningen af evnen fremgå af baggrundenshistorien.</span>";
						
						echo "<hr/>";
	
						/* CHARACTER PERSONALITY */
	
						
	
						echo "<h3>Personlighed</h3>";
	
						echo "<table>";
	
						
	
						echo "<tr><td colspan='2'><span class='bold'>Beskrivelse af personlighed: </span>
	
						<span class='tablesubtext'>Beskriv din karakters personlighed. Skal indholde styrker og svagheder (mindst 3) samt en generel beskrivelse af personligheden. 
	
						 Må også gerne indeholde oplysninger om interesser, fremtidsdrømme og ting karakteren elsker/hader.
	
						<br/> Det er muligt at bruge bbcodes som [b][/b], [i][/i] og [img][/img]</span></td></tr>"; 
	
						echo "<tr><td colspan='2'><textarea name='characterpersonality' class='textarea_large'>".$profiledata['personality']."</textarea></td></tr>";	
	
						
	
						echo "</table>"; echo "<hr/>";
	
						
	
						/* CHARACTER BACKGROUND */
	
						
	
						echo "<h3>Baggrund</h3>";
	
						echo "<table>";
	
						
	
						/* Character background story */
	
						echo "<tr><td colspan='2'><span class='bold'>Baggrundshistorie: </span>
	
						<span class='tablesubtext'>Beskrivelse af, hvad der indtil nu er sket i din karakters liv. Skal også indeholde karakterens barndom. 
	
						<br/> Det er muligt at bruge bbcodes som [b][/b], [i][/i] og [img][/img]</span></td></tr>"; 
	
						echo "<tr><td colspan='2'><textarea class='profiletextarea' name='characterstory'>".$profiledata['story']."</textarea></td></tr>";	
	
						
	
						/* Character family */
	
						echo "<tr><td colspan='2'><span class='bold'>Familie:</span>
	
						<span class='tablesubtext'>Her nævnes din karakters familiemedlemmer. Husk at angive, om de er døde eller levende.
	
						<br/> Det er muligt at bruge bbcodes som [b][/b], [i][/i] og [img][/img]</span></td></tr>"; 
	
						echo "<tr><td colspan='2'><textarea name='characterfamily' class='textarea_small'>".$profiledata['family']."</textarea></td></tr>";	
	
						
	
						/* Character habitat*/
	
						echo "<tr><td class='tableleft'><span class='bold'>Nuværende levested:</span> 
						<span class='tablesubtext'>Det sted i landet, din karakter i øjeblikket holder til.</span></td>"; 
	
						echo "<td><input type='text' name='characterhabitat' value='".htmlspecialchars($profiledata['habitat'], ENT_QUOTES, 'UTF-8')."'></td></tr>";	
	
						
						echo "</table>"; echo "<hr/>";
						
						
						/* OTHER */
						
						echo "<h3>Andet</h3>";
	
						echo "<table>";
						
	
						echo "<tr><td colspan='2'><span class='bold'>Andre karakteroplysninger: </span>
	
						<span class='tablesubtext'Hvis du har ekstra at tilføje til profilen, kan det gøres her. Det kan f.eks. være oplysninger om våben, kæledyr el.lign.
	
						<br/> Det er muligt at bruge bbcodes som [b][/b], [i][/i] og [img][/img]</span></td></tr>"; 
	
						echo "<tr><td colspan='2'><textarea name='characterother' class='textarea_large'>".$profiledata['other']."</textarea></td></tr>";	
	
						
	
						echo "</table>"; echo "<hr/>";
	
						
	
						/* CHARACTER SKILLS */
											
						echo "<h3>Færdigheder</h3>";
	
						echo "<table>";
						
						echo "<tr><td colspan='2'><span class='tablesubtext'>Fordel højest 50 points, du behøver ikke at bruge dem alle. Hvert punkt kan højest have 10 points.<br/>
						Hvis der er mere end 5 points på et punkt, skal det også fremgå af resten af profilteksten.
						Mere end 5 points vil sige, at din karakter er rigtig god til dette. 10 points er helt exeptionelt..<br/>
						Læs eventuelt vores guide til færdighedspoints. </span></td></tr>";
						
						/* Character skills points */
	
						echo "<tr><td class='tableleft'>Styrke: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_strength")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_strength' id='skill_strength' min='0' max='10' 
						value='".$profiledata['skill_strength']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_strength")'>></button>
						<?php echo "</td></tr>";	
						
						echo "<tr><td class='tableleft'>Våbenfærdigheder: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_weapons")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_weapons' id='skill_weapons' min='0' max='10' 
						value='".$profiledata['skill_weapons']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_weapons")'>></button>
						<?php echo "</td></tr>";	
						
						echo "<tr><td class='tableleft'>Smidighed: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_flexibility")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_flexibility' id='skill_flexibility' min='0' max='10' 
						value='".$profiledata['skill_flexiness']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_flexibility")'>></button>
						<?php echo "</td></tr>";	
						
						echo "<tr><td class='tableleft'>Fysisk udholdenhed: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_endurance")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_endurance' id='skill_endurance' min='0' max='10' 
						value='".$profiledata['skill_endurance']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_endurance")'>></button>
						<?php echo "</td></tr>";	
						
						echo "<tr><td class='tableleft'>Taktik: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_tactics")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_tactics' id='skill_tactics' min='0' max='10' 
						value='".$profiledata['skill_tactics']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_tactics")'>></button>
						<?php echo "</td></tr>";
						
						echo "<tr><td class='tableleft'>Intelligens: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_intelligence")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_intelligence' id='skill_intelligence' min='0' max='10' 
						value='".$profiledata['skill_intelligence']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_intelligence")'>></button>
						<?php echo "</td></tr>";
						
						
						echo "<tr><td class='tableleft'>Kreativitet: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_creativity")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_creativity' id='skill_creativity' min='0' max='10' 
						value='".$profiledata['skill_creativity']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_creativity")'>></button>
						<?php echo "</td></tr>";
						
						echo "<tr><td class='tableleft'>Mental udholdenhed: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_mental")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_mental' id='skill_mental' min='0' max='10' 
						value='".$profiledata['skill_mental']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_mental")'>></button>
						<?php echo "</td></tr>";
						
						echo "<tr><td class='tableleft'>Chakra: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_chakra")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_chakra' id='skill_chakra' min='0' max='10'
						 value='".$profiledata['skill_chakra']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_chakra")'>></button>
						<?php echo "</td></tr>";
						
						/* Total skill points */
						
						$totalskillpoints = $profiledata['skill_strength'] + $profiledata['skill_weapons'] + $profiledata['skill_flexiness'] + $profiledata['skill_endurance'] + 
						$profiledata['skill_tactics'] + $profiledata['skill_intelligence'] + $profiledata['skill_creativity'] + $profiledata['skill_mental'] + $profiledata['skill_chakra'];
						echo "<tr><td class='tableleft'>I alt: </td>"; 
						echo "<td><span id='skill_total'>".$totalskillpoints."</span></td></tr>";
						
						/* This function prevents the user from entering more skill points than allowed */
						echo "<script>
						function reduce(max, skill)
						{
							var str = parseInt(document.getElementById('skill_strength').value);
							var weap = parseInt(document.getElementById('skill_weapons').value);
							var flx = parseInt(document.getElementById('skill_flexibility').value);
							var end = parseInt(document.getElementById('skill_endurance').value);
							var tact = parseInt(document.getElementById('skill_tactics').value);
							var int = parseInt(document.getElementById('skill_intelligence').value);
							var crea = parseInt(document.getElementById('skill_creativity').value);
							var men = parseInt(document.getElementById('skill_mental').value);
							var cha = parseInt(document.getElementById('skill_chakra').value);
						
							var totalskills = str+weap+flx+end+tact+int+crea+men+cha;	
							
							var changeskill = parseInt(document.getElementById(skill).value);
							
							if( changeskill > 0)
							{
								document.getElementById(skill).value = changeskill - 1;
								document.getElementById('skill_total').innerHTML = totalskills - 1;
							}
							
						}
						
						function add(max, skill)
						{
							var str = parseInt(document.getElementById('skill_strength').value);
							var weap = parseInt(document.getElementById('skill_weapons').value);
							var flx = parseInt(document.getElementById('skill_flexibility').value);
							var end = parseInt(document.getElementById('skill_endurance').value);
							var tact = parseInt(document.getElementById('skill_tactics').value);
							var int = parseInt(document.getElementById('skill_intelligence').value);
							var crea = parseInt(document.getElementById('skill_creativity').value);
							var men = parseInt(document.getElementById('skill_mental').value);
							var cha = parseInt(document.getElementById('skill_chakra').value);
						
							var totalskills = str+weap+flx+end+tact+int+crea+men+cha;	
							
							var changeskill = parseInt(document.getElementById(skill).value);
							
							if( changeskill < 10 && totalskills < max)
							{
								document.getElementById(skill).value = changeskill + 1;
								document.getElementById('skill_total').innerHTML = totalskills + 1;
							}
							
						}		
						
						</script>";
						
						echo "</table>";
						echo "<hr/>";
						 
						
						/* Submit the stuff */
						echo "<table>";
						echo "<tr><td class='profilsubmittd' colspan=2>";
						if ($currentchar['accepted'] != 1){
							$approval_status = $forum->check_if_waiting_for_approval($selectedchar)->fetch_assoc();
							if($approval_status['res'] > 0) { echo "Du kan ikke ændre i profilen, mens den venter på godkendelse fra en administrator."; }
							else 
							{
								echo "<input type='submit' class='profilesubmit' name='submit_for_approval' value='Send til godkendelse'>";
								echo "<input type='submit' class='profilesubmit' name='submit_changes' value='Gem ændringer'>";
								echo "<br/><br/><a href='characterprofile.php?id=".$selectedchar."' target='blank'>Klik her for at se din karakters profil</a> 
								<br/><span class='smalltext'>(Husk at gemme ændringer først)</span>";
							}
						}
						else {
							$approval_status = $forum->check_if_waiting_for_approval($selectedchar)->fetch_assoc();
							if($approval_status['res'] > 0) { echo "Du kan ikke ændre i profilen, mens den venter på godkendelse fra en administrator."; }
							else 
							{
							echo "<input type='submit' class='profilesubmit' name='submit_for_approval' value='Send til gengodkendelse'>";	
							}
						}
						
						echo "</td></tr></form>";
						
	
						echo "</table>"; echo "<hr/>";
						
						
						//Preventing writing data to the small number inputs
						/*
						echo "<script> $('.numberinput_small').keypress(function (evt) {
							evt.preventDefault();
						});</script>";
						*/
	
					}
	
					
	
					if(!isset($_GET['full']) && $currentchar['accepted'] == 1)
	
					{
						$errormsg = "";
						if(isset($_GET['nameerror'])) { $errormsg = "Ændringen af din karakters viste forumnavn blev ikke fuldført. Der eksisterer allerede en karakter med dette navn!"; }
						
						if($_POST['submit_changes'])
						{
							//update character name
							$charname = htmlspecialchars($_POST["charactername_forum"]);
							
							$nameerror = false;
							
							if($charname != $currentchar['name'])
							{
								$nameexists = $forum->check_for_existing_character_name($charname)->fetch_assoc();
								if ($nameexists['res'] > 0) { $nameerror = true; }
								else
								{
									$newname = $forum->update_character_forumname($charname, $selectedchar);
								}
								
							}
													
							//update profiledata
							//$fullname = htmlspecialchars($_POST["charactername_full"]);
							$shortname = htmlspecialchars($_POST["charactername_short"]);
							$age = $_POST["characterage"];
							//$gender = htmlspecialchars($_POST["charactergender"]);
							//$birthday = htmlspecialchars($_POST["characterbirthday"]);
							$faith = htmlspecialchars($_POST["characterfaith"]);
							$alignment = htmlspecialchars($_POST["characteralignments"]);
							$profession = htmlspecialchars($_POST["characterprofession"]);
							//$race = htmlspecialchars($_POST["race"]);
							//$raceinfo= htmlspecialchars($_POST["raceinfo"]);
							$height = $_POST["characterheight"];
							$weight = $_POST["characterweight"];

							$looks = htmlspecialchars($_POST["characterlooks"]);
							$magic1_skill = $_POST["charactermagic1_skill"];
							$magic2_skill = $_POST["charactermagic2_skill"];
							$story = htmlspecialchars($_POST["characterstory"]);
							$family = htmlspecialchars($_POST["characterfamily"]);
							$habitat = htmlspecialchars($_POST["characterhabitat"]);
							$other = htmlspecialchars($_POST["characterother"]);
							
							$str = $_POST["skill_strength"];
							$weap = $_POST["skill_weapons"];
							$flx = $_POST["skill_flexibility"];
							$end = $_POST["skill_endurance"];
							$tact = $_POST["skill_tactics"];
							$int = $_POST["skill_intelligence"];
							$crea = $_POST["skill_creativity"];
							$men = $_POST["skill_mental"];
							$cha = $_POST["skill_chakra"];
							
							$updateinfo = $forum->update_character_profiledata($shortname, $age, $faith, $alignment, $profession, $height, $weight, $looks, $magic1_skill, $magic2_skill,
							$story, $family, $habitat, $other, $str, $weap, $flx, $end, $tact, $int, $crea, $men, $cha, $selectedchar);
							
							if($nameerror) { header('Location:ucp.php?menu=char&mode=profile&characterselect='.$selectedchar.'&nameerror'); }
							else { header('Location:ucp.php?menu=char&mode=profile&characterselect='.$selectedchar); }
								
						}
	
						echo "<table><form method='post' class='confirmform'>";
						echo "<a href='ucp.php?menu=char&mode=profile&characterselect=".$selectedchar."&full'>Klik her for at få fuld adgang til at rette profilen. <br/>
						(Bemærk at det kræver gengodkendelse!)</a>";
						
						echo "<span class='errormsg'>".$errormsg."</span>";	
					
	
						echo "<h3>Grundlæggende karakterinformation</h3>";
	
					
	
						/* Character forum name */
	
						echo "<tr><td class='tableleft'><span class='bold'>Viste karakternavn:</span> 
						<span class='tablesubtext'>Det karakternavn, der vises på forummet.</span></td>"; 
	
						echo "<td><input type='text' name='charactername_forum' value='".htmlspecialchars($currentchar['name'], ENT_QUOTES, 'UTF-8')."' maxlength='25'></td></tr>";	
	
							
	
						/* Full character name */	
	
						echo "<tr><td class='tableleft'><span class='bold'>Fulde karakternavn:</span>
						 <span class='tablesubtext'>Din karakters fornavn og efternavn samt eventuelle mellemnavne.</span></td>"; 
	
						echo "<td><input type='text' name='charactername_full' value='".htmlspecialchars($profiledata['fullname'], ENT_QUOTES, 'UTF-8')."' disabled></td></tr>";		
	
						
	
						/* Character nicknames */
	
						echo "<tr><td class='tableleft'><span class='bold'>Kaldet: </span>
						<span class='tablesubtext'>Hvad din karakter til dagligt kaldes af andre karakterer.</span></td>"; 
	
						echo "<td><input type='text' name='charactername_short' value='".htmlspecialchars($profiledata['shortname'], ENT_QUOTES, 'UTF-8')."'></td></tr>";	
	
						
	
						/* Character age */
	
						echo "<tr><td class='tableleft'><span class='bold'>Alder:</span> 
						<span class='tablesubtext'>Din karakters alder. Se eventuelt raceinformation, hvis du spiller andet end menneske.</span></td>"; 
	
						echo "<td><input type='number' name='characterage' min='1' value='".$profiledata['age']."'></td></tr>";	
	
						
	
						/* Character gender */
	
						$genders = array("Mand", "Kvinde", "Intetkøn");
	
						echo "<tr><td class='tableleft'><span class='bold'>Køn:</span> <span class='tablesubtext'></span></td>"; 
	
						echo "<td><select name='charactergender' disabled>";
	
						foreach($genders as $g) 
	
						{ 
	
							if($g == $profiledata['gender']) { echo "<option value='".$g."' selected>".$g."</option>"; } else { echo "<option value='".$g."'>".$g."</option>"; }
	
						}					
	
						echo "</td></tr>";	
	
						
	
						/* Character birthday */
	
						echo "<tr><td class='tableleft'><span class='bold'>Fødselsdag:</span>
						 <span class='tablesubtext'>Behøver blot indeholde dag og måned. Du skal selv bruge dette til at holde styr på din karakters alder.</span></td>";
						echo "<td><input type='text' name='characterbirthday' value='".htmlspecialchars($profiledata['birthday'], ENT_QUOTES, 'UTF-8')."' disabled></td></tr>";	

						/* Character faith*/
	
						echo "<tr><td class='tableleft'><span class='bold'>Tro:</span> 
						<span class='tablesubtext'>Hvilken tro har din karakter? Husk at læse om Krystallandets trosretninger, før du udfylder dette.</span></td>"; 
	
						echo "<td><input type='text' name='characterfaith' value='".htmlspecialchars($profiledata['faith'], ENT_QUOTES, 'UTF-8')."'></td></tr>";	
	
						
	
						/* Character aligment */
	
						$alignments = 
						array("Retmæssig Ond", "Neutral Ond", "Kaotisk Ond", "Retmæssig Neutral", "Sand Neutral", "Kaotisk Neutral", "Retmæssig God", "Neutral God", "Kaotisk God");
	
						echo "<tr><td class='tableleft'><span class='bold'>Tilhørsforhold:</span>
						 <span class='tablesubtext'>Du kan læse nærmere om tilhørsforholdene under karakterinformation.</span></td>"; 
	
						echo "<td><select name='characteralignments'>";
	
						foreach($alignments as $a) 
	
						{ 
	
							if($a === $profiledata['alignment']) { echo "<option value='".$a."' selected>".$a."</option>"; } else { echo "<option value='".$a."'>".$a."</option>"; }
	
						}					
	
						echo "</td></tr>";	
	
						
	
						/* Character profession*/
	
						echo "<tr><td class='tableleft'><span class='bold'>Erhverv: </span>
						<span class='tablesubtext'>Hvad arbejder din karakter med? F.eks. bonde, kriger, adelig..</span></td>"; 
	
						echo "<td><input type='text' name='characterprofession' value='".htmlspecialchars($profiledata['profession'], ENT_QUOTES, 'UTF-8')."'></td></tr>";	
	
								
	
						/* Character race */		
	
						echo "<tr><td class='tableleft'><span class='bold'>Race:</span> </td>"; $races = $forum->get_all_races(); 
	
						echo "<td><select name='race' id='race' disabled>"; while($r = $races->fetch_assoc()) 
	
							{ 
	
								if ($r['race_ID'] == $profiledata['fk_race_ID']) { echo "<option value='".$r['race_ID']."' selected>".$r['name']."</option>"; }
	
								else { echo "<option value='".$r['race_ID']."'>".$r['name']."</option>"; }						
	
							}
	
						echo "</select>";					
						
	
						echo "<tr class='raceinfo_tr'><td colspan='2'><span class='bold'>Uddybende om race: </span>
	
						<span id='werewolf' class='tablesubtext'>Hvilken race, din karakter er, udover varulveforbandelsen.<br/> Bemærk at ikke alle er tilladte!</span>
	
						<span id='special' class='tablesubtext'>Navnet på racen samt lidt information om udseende og historie.</span>
	
						<span id='vampire' class='tablesubtext'>Hvilken race, din karakter er, udover vampyrforbandelsen.<br/> Bemærk at ikke alle er tilladte!</span>
	
						<span id='animal' class='tablesubtext'>Hvilken dyreart, din halvdyrskarakter er.</span>
						
						<span id='angel' class='tablesubtext'>Hvilken race, din karakter var, før han eller hun døde og genopstod.</span>
	
						<span id='combo' class='tablesubtext'>Hvilke racer, din karakter er en blanding af.</span></td></tr>"; 
						
						echo "<tr class='raceinfo_tr'><td colspan='2'><textarea name='raceinfo' class='textarea_small' disabled>".$profiledata['raceinfo']."</textarea></td></tr>";	
						
						
	
	
				
								
	
						// Script to show extra race information //
	
								echo " <script> 
	
								function hide() { $('.raceinfo_tr').hide(); $('#angel').hide(); $('#werewolf').hide(); $('#special').hide(); $('#vampire').hide(); $('#animal').hide(); $('#combo').hide();}															
								
								function show() {
								
									if( $('#race').val()==='25'){
		
									hide();	
		
									$('.raceinfo_tr').show(),
		
									$('#special').show()
		
									}
									else if( $('#race').val()==='24'){
		
									hide();	
		
									$('.raceinfo_tr').show(),
		
									$('#combo').show()
		
									}	
		
									else if( $('#race').val()==='18'){
		
									hide();	
		
									$('.raceinfo_tr').show(),
		
									$('#vampire').show()
		
									}	
		
									else if( $('#race').val()==='19'){
		
									hide();	
		
									$('.raceinfo_tr').show(),
		
									$('#werewolf').show()
		
									}	
		
									else if( $('#race').val()==='8'){
		
									hide();	
		
									$('.raceinfo_tr').show(),
		
									$('#animal').show()
		
									}	
									
									else if( $('#race').val()==='8'){
		
									hide();	
		
									$('.raceinfo_tr').show(),
		
									$('#angel').show()
		
									}	
										
									else{	
									
									hide();	
									
									}							
								}
								
								show();							
				
								$('#race').on('change',function(){
	
								show();
	
							});</script>";		
	
										
	
						echo "</td></tr>";
	
						echo "</table>"; echo "<hr/>";
	
						
	
						/* CHARACTER LOOKS */
	
						
	
						echo "<h3>Udseende</h3>";
	
						echo "<table>";
	
					
	
						/* Character height */
	
						echo "<tr><td class='tableleft'><span class='bold'>Højde:</span> <span class='tablesubtext'>Karakterens højde i cm.</span></td>"; 
	
						echo "<td><input type='number' name='characterheight' min='0' value='".$profiledata['height']."'></td></tr>";		
	
						
	
						/* Character weight */
	
						echo "<tr><td class='tableleft'><span class='bold'>Vægt:</span> <span class='tablesubtext'>Karakterens vægt i kilogram.</span></td>"; 
	
						echo "<td><input type='number' name='characterweight' min='0' value='".$profiledata['weight']."'></td></tr>";
	
						
	
						/* Character looks */
	
						echo "<tr><td colspan='2'><span class='bold'>Udseende: </span>
	
						<span class='tablesubtext'>Beskriv din karakters udseende. Skal indholde hud-, hår- og øjenfarve, kropsbygning og eventuelle særlige kendetegn.
	
						<br/> Det er muligt at bruge bbcodes som [b][/b], [i][/i] og [img][/img]</span></td></tr>"; 
	
						echo "<tr><td colspan='2'><textarea name='characterlooks' class='textarea_large'>".$profiledata['looks']."</textarea></td></tr>";	
	
						
	
						echo "</table>"; echo "<hr/>";
	
						
	
						/* CHARACTER MAGIC */
	
						
	
						echo "<h3>Magiske evner</h3>";
	
						echo "<table>";
	
						
	
						/* Character magic 1 */
	
						echo "<tr><td colspan='2'><span class='bold'>Magisk evne (1): </span>
	
						<span class='tablesubtext'>Beskriv din karakters første magiske evne. Jo mere du går i deltaljer, jo mere sandsynligt er det, at evnen bliver godkendt.
						Jo stærkere evnen er, jo højere chakra (færdighedspoint) skal din karakter have.
	
						<br/> Det er muligt at bruge bbcodes som [b][/b], [i][/i] og [img][/img]</span></td></tr>"; 
	
						echo "<tr><td colspan='2'><textarea name='charactermagic1' class='textarea_small' disabled>".$profiledata['magic1']."</textarea></td></tr>";	
	
						
	
						/* Character magic skill 1 */
	
						echo "<tr><td class='tableleft'><span class='bold'>Dygtighed til evne (1):</span> <span class='tablesubtext'>Angiv på en skala fra 1-10, 
	
						hvor god din karakter er til at styre sin første evne. </span></td>"; 
	
						echo "<td><input type='number' class='numberinput_small' name='charactermagic1_skill' min='0' max='10' value='".$profiledata['magic1_skill']."'>
	
						*</td></tr>";	
						
	
						/* Character magic 2 */
	
						echo "<tr><td colspan='2'><span class='bold'>Magisk evne (2): </span>
	
						<span class='tablesubtext'>Beskriv din karakters anden magiske evne. Jo mere du går i deltaljer, jo mere sandsynligt er det, at evnen bliver godkendt.
						Jo stærkere evnen er, jo højere chakra (færdighedspoint) skal din karakter have.
	
						<br/> Det er muligt at bruge bbcodes som [b][/b], [i][/i] og [img][/img]</span></td></tr>"; 
	
						echo "<tr><td colspan='2'><textarea name='charactermagic2' class='textarea_small' disabled>".$profiledata['magic2']."</textarea></td></tr>";
	
						
	
						/* Character magic skill 2 */
	
						echo "<tr><td class='tableleft'><span class='bold'>Dygtighed til evne (2):</span> <span class='tablesubtext'>Angiv på en skala fra 1-10, 
	
						hvor god din karakter er til at styre sin anden evne. </span></td>"; 
	
						echo "<td><input type='number' class='numberinput_small' name='charactermagic2_skill' min='0' max='10' value='".$profiledata['magic2_skill']."'>
	
						*</td></tr>";
	
										
	
						echo "</table>"; 
	
						echo "<span class='smalltext'><br/>* En dygtighed på 0 til at kontrollere en magisk evne, svarer til, at evnen er passiv. 
	
						Er dygtigheden 6 eller mere, skal træningen af evnen fremgå af baggrundenshistorien.</span>";
						
						echo "<hr/>";
	
						/* CHARACTER PERSONALITY */
	
						
	
						echo "<h3>Personlighed</h3>";
	
						echo "<table>";
	
						
	
						echo "<tr><td colspan='2'><span class='bold'>Beskrivelse af personlighed: </span>
	
						<span class='tablesubtext'>Beskriv din karakters personlighed. Skal indholde styrker og svagheder (mindst 3!) samt en generel beskrivelse af personligheden. 
	
						Må også gerne indeholde oplysninger om interesser, fremtidsdrømme og ting karakteren elsker/hader.
	
						<br/> Det er muligt at bruge bbcodes som [b][/b], [i][/i] og [img][/img]</span></td></tr>"; 
	
						echo "<tr><td colspan='2'><textarea name='characterpersonality' class='textarea_large' disabled>".$profiledata['personality']."</textarea></td></tr>";	
	
						
	
						echo "</table>"; echo "<hr/>";
	
						
	
						/* CHARACTER BACKGROUND */
	
						
	
						echo "<h3>Baggrund</h3>";
	
						echo "<table>";
	
						
	
						/* Character background story */
	
						echo "<tr><td colspan='2'><span class='bold'>Baggrundshistorie: </span>
	
						<span class='tablesubtext'>Beskrivelse af, hvad der indtil nu er sket i din karakters liv. Skal også indeholde karakterens barndom. 
	
						<br/> Det er muligt at bruge bbcodes som [b][/b], [i][/i] og [img][/img]</span></td></tr>"; 
	
						echo "<tr><td colspan='2'><textarea class='profiletextarea' name='characterstory'>".$profiledata['story']."</textarea></td></tr>";	
	
						
	
						/* Character family */
	
						echo "<tr><td colspan='2'><span class='bold'>Familie:</span>
	
						<span class='tablesubtext'>Her nævnes din karakters familiemedlemmer. Husk at angive, om de er døde eller levende.
	
						<br/> Det er muligt at bruge bbcodes som [b][/b], [i][/i] og [img][/img]</span></td></tr>"; 
	
						echo "<tr><td colspan='2'><textarea name='characterfamily' class='textarea_small'>".$profiledata['family']."</textarea></td></tr>";	
	
						
	
						/* Character habitat*/
	
						echo "<tr><td class='tableleft'><span class='bold'>Nuværende levested:</span> 
						<span class='tablesubtext'>Det sted i landet, din karakter i øjeblikket holder til.</span></td>"; 
	
						echo "<td><input type='text' name='characterhabitat' value='".htmlspecialchars($profiledata['habitat'], ENT_QUOTES, 'UTF-8')."'></td></tr>";	
	
						
						echo "</table>"; echo "<hr/>";
	
						
						/* OTHER */
						
						echo "<h3>Andet</h3>";
	
						echo "<table>";
						
	
						echo "<tr><td colspan='2'><span class='bold'>Andre karakteroplysninger: </span>
	
						<span class='tablesubtext'>Hvis du har ekstra at tilføje til profilen, kan det gøres her. Det kan f.eks. være oplysninger om våben, kæledyr el.lign.
	
						<br/> Det er muligt at bruge bbcodes som [b][/b], [i][/i] og [img][/img]</span></td></tr>"; 
	
						echo "<tr><td colspan='2'><textarea name='characterother'  class='textarea_large'>".$profiledata['other']."</textarea></td></tr>";	
	
						
	
						echo "</table>"; echo "<hr/>";
						
	
						/* CHARACTER SKILLS */
											
						echo "<h3>Færdigheder</h3>";
	
						echo "<table>";
						
						echo "<tr><td colspan='2'><span class='tablesubtext'>Fordel højest 50 points, du behøver ikke at bruge dem alle. Hvert punkt kan højest have 10 points.<br/>
						Hvis der er mere end 5 points på et punkt, skal det også fremgå af resten af profilteksten.
						Mere end 5 points vil sige, at din karakter er rigtig god til dette. 10 points er helt exeptionelt..<br/>
						Læs eventuelt vores guide til færdighedspoints. </span></td></tr>";
						
						/* Character skills points */
	
						echo "<tr><td class='tableleft'>Styrke: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_strength")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_strength' id='skill_strength' min='0' max='10' 
						value='".$profiledata['skill_strength']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_strength")'>></button>
						<?php echo "</td></tr>";	
						
						echo "<tr><td class='tableleft'>Våbenfærdigheder: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_weapons")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_weapons' id='skill_weapons' min='0' max='10' 
						value='".$profiledata['skill_weapons']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_weapons")'>></button>
						<?php echo "</td></tr>";	
						
						echo "<tr><td class='tableleft'>Smidighed: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_flexibility")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_flexibility' id='skill_flexibility' min='0' max='10' 
						value='".$profiledata['skill_flexiness']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_flexibility")'>></button>
						<?php echo "</td></tr>";	
						
						echo "<tr><td class='tableleft'>Fysisk udholdenhed: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_endurance")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_endurance' id='skill_endurance' min='0' max='10' 
						value='".$profiledata['skill_endurance']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_endurance")'>></button>
						<?php echo "</td></tr>";	
						
						echo "<tr><td class='tableleft'>Taktik: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_tactics")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_tactics' id='skill_tactics' min='0' max='10' 
						value='".$profiledata['skill_tactics']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_tactics")'>></button>
						<?php echo "</td></tr>";
						
						echo "<tr><td class='tableleft'>Intelligens: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_intelligence")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_intelligence' id='skill_intelligence' min='0' max='10' 
						value='".$profiledata['skill_intelligence']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_intelligence")'>></button>
						<?php echo "</td></tr>";
						
						
						echo "<tr><td class='tableleft'>Kreativitet: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_creativity")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_creativity' id='skill_creativity' min='0' max='10' 
						value='".$profiledata['skill_creativity']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_creativity")'>></button>
						<?php echo "</td></tr>";
						
						echo "<tr><td class='tableleft'>Mental udholdenhed: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_mental")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_mental' id='skill_mental' min='0' max='10' 
						value='".$profiledata['skill_mental']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_mental")'>></button>
						<?php echo "</td></tr>";
						
						echo "<tr><td class='tableleft'>Chakra: </td>"; 
						echo "<td>"; ?>
						<button type='button' class='reducebutton' onclick='reduce(<?php echo $currentchar['maxskill']; ?>,"skill_chakra")'><</button>
						<?php echo "<input oninput='maxskills(".$currentchar['maxskill'].")' type='text' class='numberinput_small' name='skill_chakra' id='skill_chakra' min='0' max='10'
						 value='".$profiledata['skill_chakra']."' readonly>"; ?>
						<button type='button' class='addbutton' onclick='add(<?php echo $currentchar['maxskill']; ?>,"skill_chakra")'>></button>
						<?php echo "</td></tr>";
						
						/* Total skill points */
						
						$totalskillpoints = $profiledata['skill_strength'] + $profiledata['skill_weapons'] + $profiledata['skill_flexiness'] + $profiledata['skill_endurance'] + 
						$profiledata['skill_tactics'] + $profiledata['skill_intelligence'] + $profiledata['skill_creativity'] + $profiledata['skill_mental'] + $profiledata['skill_chakra'];
						echo "<tr><td class='tableleft'>I alt: </td>"; 
						echo "<td><span id='skill_total'>".$totalskillpoints."</span></td></tr>";
						
						/* This function prevents the user from entering more skill points than allowed */
						echo "<script>
						function reduce(max, skill)
						{
							var str = parseInt(document.getElementById('skill_strength').value);
							var weap = parseInt(document.getElementById('skill_weapons').value);
							var flx = parseInt(document.getElementById('skill_flexibility').value);
							var end = parseInt(document.getElementById('skill_endurance').value);
							var tact = parseInt(document.getElementById('skill_tactics').value);
							var int = parseInt(document.getElementById('skill_intelligence').value);
							var crea = parseInt(document.getElementById('skill_creativity').value);
							var men = parseInt(document.getElementById('skill_mental').value);
							var cha = parseInt(document.getElementById('skill_chakra').value);
						
							var totalskills = str+weap+flx+end+tact+int+crea+men+cha;	
							
							var changeskill = parseInt(document.getElementById(skill).value);
							
							if( changeskill > 0)
							{
								document.getElementById(skill).value = changeskill - 1;
								document.getElementById('skill_total').innerHTML = totalskills - 1;
							}
							
						}
						
						function add(max, skill)
						{
							var str = parseInt(document.getElementById('skill_strength').value);
							var weap = parseInt(document.getElementById('skill_weapons').value);
							var flx = parseInt(document.getElementById('skill_flexibility').value);
							var end = parseInt(document.getElementById('skill_endurance').value);
							var tact = parseInt(document.getElementById('skill_tactics').value);
							var int = parseInt(document.getElementById('skill_intelligence').value);
							var crea = parseInt(document.getElementById('skill_creativity').value);
							var men = parseInt(document.getElementById('skill_mental').value);
							var cha = parseInt(document.getElementById('skill_chakra').value);
						
							var totalskills = str+weap+flx+end+tact+int+crea+men+cha;	
							
							var changeskill = parseInt(document.getElementById(skill).value);
							
							if( changeskill < 10 && totalskills < max)
							{
								document.getElementById(skill).value = changeskill + 1;
								document.getElementById('skill_total').innerHTML = totalskills + 1;
							}
							
						}
						
						function maxskills(max)
						{
						/*			
						var str = parseInt(document.getElementById('skill_strength').value);
						var weap = parseInt(document.getElementById('skill_weapons').value);
						var flx = parseInt(document.getElementById('skill_flexibility').value);
						var end = parseInt(document.getElementById('skill_endurance').value);
						var tact = parseInt(document.getElementById('skill_tactics').value);
						var int = parseInt(document.getElementById('skill_intelligence').value);
						var crea = parseInt(document.getElementById('skill_creativity').value);
						var men = parseInt(document.getElementById('skill_mental').value);
						var cha = parseInt(document.getElementById('skill_chakra').value);
						
						var totalskills = str+weap+flx+end+tact+int+crea+men+cha;
						
						document.getElementById('skill_total').innerHTML = totalskills;
						
							if( totalskills >= max)
							{
								document.getElementById('skill_strength').max = str;
								document.getElementById('skill_weapons').max = weap;
								document.getElementById('skill_flexibility').max = flx;
								document.getElementById('skill_endurance').max = end;
								document.getElementById('skill_tactics').max = tact;
								document.getElementById('skill_intelligence').max = int;
								document.getElementById('skill_creativity').max = crea;
								document.getElementById('skill_mental').max = men;
								document.getElementById('skill_chakra').max = cha;
							}
							else
							{
								document.getElementById('skill_strength').max = '10';
								document.getElementById('skill_weapons').max = '10';
								document.getElementById('skill_flexibility').max = '10';
								document.getElementById('skill_endurance').max = '10';
								document.getElementById('skill_tactics').max = '10';
								document.getElementById('skill_intelligence').max = '10';
								document.getElementById('skill_creativity').max = '10';
								document.getElementById('skill_mental').max = '10';
								document.getElementById('skill_chakra').max = '10';
							}
							*/
						}					
						
						</script>";
						
						echo "</table>";
						echo "<hr/>";
											 
						
						/* Submit the stuff */
						echo "<table>";
						echo "<tr><td class='profilsubmittd' colspan=2>";
						$approval_status = $forum->check_if_waiting_for_approval($selectedchar)->fetch_assoc();
						if($approval_status['res'] > 0) { echo "Du kan ikke ændre i profilen, mens den venter på godkendelse fra en administrator."; }
						else 
						{
							echo "<input type='submit' class='profilesubmit' name='submit_changes' value='Gem ændringer'>";
							echo "<br/><br/><a href='characterprofile.php?id=".$selectedchar."' target='blank'>Klik her for at se din karakters profil</a> 
								<br/><span class='smalltext'>(Husk at gemme ændringer først)</span>";
						}
						echo "</td></tr></form>";
						
	
						echo "</table>"; echo "<hr/>";
	
						
						
							//Preventing writing data to the small number inputs
						/*
						echo "<script> $('.numberinput_small').keypress(function (evt) {
							evt.preventDefault();
						});</script>";
						*/
	
					}
	
				 echo "</div>";					
	
				}
	
			} //end profile
			

			if(($_GET['mode']) == "submitted")
			{	
				echo "Din karakterprofil vil blive tilset og godkendt af en admin. Du vil modtage en PB med godkendelsesstatus.";
				
				if(isset($_GET['nameerror'])) { echo "<br/><br/>Ændringen af din karakters viste forumnavn blev ikke fuldført. Der eksisterer allerede en karakter med dette navn!"; }
	
			}
			
		} //end correct character check
		echo "</div></div>";
	} //end char menu
	if($_GET['menu'] == "new")
	{
		$errormsg = "";
		
		if($_POST['submit_new_character'])
		{
			$charname = htmlspecialchars($_POST["name"]);
			$nameexists = $forum->check_for_existing_character_name($charname)->fetch_assoc();
			if ($nameexists['res'] > 0) 
			{
				 $errormsg = "Der eksisterer allerede en karakter med dette navn.";
			}
			else
			{
				$newchar = $forum->submit_new_character($charname, $user_logged_in_ID);
				$profiledata = $forum->create_character_profiledata($newchar);
				
				header('Location:ucp.php?menu=char&mode=profile&characterselect='.$newchar); 
				break;
			}
								
		}
				
		
		echo "<div id='ucp_page_wide'>";
		
		echo "<div class='category'><a href=''>Opret ny karakter</a></div>";
		echo "<div id='ucp_content' class='center'>";
		echo "<span class='errormsg'>".$errormsg."</span>";	
				
		echo "<table><form method='post' class='confirmform'>";		
		echo "<tr><td><h3>Karakternavn:</h3>";		
		echo "<input type='text' name='name'/></td></tr>";		
		echo "<tr><td><input type='submit' name='submit_new_character' value='opret'/></td></tr>";		
		echo "</form></table>";		
		echo "</div>";
		echo "</div>";
	}
}
?>
<?php
include('footer.php');
?>