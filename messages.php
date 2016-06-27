<?php
include('header.php');
?>
<?php
if(!isset($_SESSION['user']))
{
	header('Location:index.php');
}
else
{
	echo "<div class='category'><a href=''>Private Beskeder</a></div>";
	
	echo "<div id='topmenu'>";
	echo "<a href='messages.php?mode=new'>Opret besked</a> &#9679; ";
	echo "<a href='messages.php'>Indbakke</a> &#9679; ";
	echo "<a href='messages.php?mode=sent'>Sendte beskeder</a>";
	echo "</div>";	
	
	echo "<div id='forumpage'>";
	
	if(empty($_GET))
	{
		$inboxmessages = $forum->get_messages_sent_to_user($user_logged_in_ID); 
		
		echo "<div class='category'><a href=''>Indbakke</a></div>";
		echo "<table id='msgbox'>";
		echo "<tr><th>Emne</th><th>Afsender</th><th>Dato</th><th></th></tr>";
		
		while($m = $inboxmessages->fetch_assoc())
		{
			//$message = $forum->get_message($m['fk_message_ID'])->fetch_assoc();
			$sender = $forum->get_superuser($m['fk_sender_ID'])->fetch_assoc();
			if($m['readstatus'] == 0) { echo "<tr><td class='unread'>"; } else { echo "<tr><td>"; }
			echo "<a href='messages.php?mode=view&m=".$m['message_ID']."'>".$m['title']."</a>";
			echo "</td>";
			echo "<td><a class='username' style='color:".$sender['color'].";' href='memberprofile.php?id=".$sender['superuser_ID']."'>".$sender['name']."</a> </td>";
			echo "<td> ".date("d.m.Y G:i", strtotime($m['datetime']))."</td>";	
			echo "<td>"; 
			echo "<a href='messages.php?deletereceived=".$m['message_ID']."'"; ?>
				onclick='return confirm("Er du sikker på, at du vil slette denne besked? Den vil ikke længere kunne ses i din indbakke.")'
				<?php echo "><img src='images/topic_delete.png' title='Slet post'/></a> ";
			echo "</td>";	
			echo "</tr>";
		}
		
		echo "</table>";
	}
	
	if($_GET['mode'] == "new")
	{
		$errormsg = "";		
		$receiver = ""; if(isset($_GET['receiver'])) { $user = $forum->get_superuser($_GET['receiver'])->fetch_assoc(); $receiver = $user['name'];}
		$title = ""; if(isset($_GET['title'])) { $title = $_GET['title'];}
		$message= "";
		
		if($_POST['send_message'])
		{
			$error = false;
			
			if($user_rank > 1)
			{
				if($_POST['receiveroption'] == 'all_users') { $receiver = "all_users"; }
				if($_POST['receiveroption'] == 'single_receiver') { $receiver = htmlspecialchars($_POST['receiver']); }
			}
			else
			{
				$receiver = htmlspecialchars($_POST['receiver']);	
			}			
			
			$title = htmlspecialchars($_POST['title']);
			$message= htmlspecialchars($_POST['messagetext']);
			
			if($receiver == "") { $error = true; $errormsg = $errormsg."Du skal indtaste navnet på den bruger, der skal modtage beskeden.<br/>";	}
			else 
			{
				$existinguser = $forum->check_for_existing_superuser_name($receiver)->fetch_assoc();	
				if($existinguser['res'] < 1 && $receiver != "all_users") { $error = true; $errormsg = $errormsg."Modtagerens brugernavn blev ikke fundet i systemet.<br/>";	}
			}
			if($message == "") { $error = true; $errormsg = $errormsg."Du skal indtaste noget i beskedfeltet.<br/>";	}
			
			if($error == false)
			{
				
				$createdmessage = $forum->create_new_message($user_logged_in_ID, $title, $message);
				if($receiver == "all_users")
				{
					$superusers = $forum->get_all_superusers();
					while($u = $superusers->fetch_assoc())
					{
						if($u['superuser_ID'] != $user_logged_in_ID)
						{
							$sentmessage = $forum->send_new_message($createdmessage, $u['superuser_ID']);
						}
					}	
				}
				else
				{
					$superuser = $forum->get_superuser_by_name($receiver)->fetch_assoc();	
					$sentmessage = $forum->send_new_message($createdmessage, $superuser['superuser_ID']);
				}
				header('Location:messages.php');			
			}			
		}		
				
		echo "<div id='privmessage'>";
		echo "<div class='category'><a href=''>Ny besked</a></div>";
		
		echo "<span class='errormsg'>".$errormsg."</span>";	
		
		echo "<table>";
		
		echo "<form method='post' name='messageform'>";
		if($user_rank > 1)
		{
			echo "<tr><td>Modtager: </td><td>";
			echo "<input type='radio' name='receiveroption' value='single_receiver' checked='checked' required><input type='text' name='receiver' value='".$receiver."' />"; ?>
			<a class="smallbutton" href="" onclick="popup('findsuperuser.php'); return false;">Find bruger</a> 
            <?php echo "<br/>";	
			echo "<input type='radio' name='receiveroption' value='all_users' required> Send til alle brugere<br/>";	
			echo "</td></tr>";
		}
		else
		{
			echo "<tr><td>Modtager: </td>";
			echo "<td><input type='text' name='receiver' value='".$receiver."' required/>"; ?>
			<a class="smallbutton" href="" onclick="popup('findsuperuser.php'); return false;">Find bruger</a> 
            <?php echo "<br/>";	
			echo "</td></tr>";
		}
		
		?> 
        <script type="text/javascript">
			function popup (url) {
				win = window.open(url, "window1", "width=600,height=400,status=no,scrollbars=1,resizable=yes");
					win.focus();
				}
		
		</script>
        
		<?php
		echo "<tr><td>Titel: </td>";
		echo "<td><input type='text' name='title' value='".$title."' required/></td>";	
		echo "</tr>";
		echo "<tr><td colspan='2'>";
		echo "<textarea name='messagetext' class='postarea' required>".$message."</textarea></td>";	
		echo "</tr>";
		echo "<tr><td colspan='2'>";
		echo "<input type='submit' name='send_message' value='Send besked'></td>";	
		echo "</tr>";
		
		echo "</table>";
		echo "</div>";
	}
	
	if($_GET['mode'] == "sent")
	{
		$sentmessages = $forum->get_messages_send_by_user($user_logged_in_ID); 
		
		echo "<div class='category'><a href=''>Sendte Beskeder</a></div>";
		echo "<table id='msgbox'>";
		echo "<tr><th>Emne</th><th>Modtager</th><th>Dato</th></tr>";
		
		while($m = $sentmessages->fetch_assoc())
		{
			$receivercount = $forum->count_message_receivers($m['message_ID'])->fetch_assoc();
			$readstatus = 1;
			$msgreceivers = $forum->get_message_receivers($m['message_ID']);
			while($mr = $msgreceivers->fetch_assoc())
			{
				if($mr['readstatus'] == 0) {$readstatus = 0;}
			}
			
			if($receivercount['res'] > 0)
			{
				if($readstatus == 0) { echo "<tr><td class='unread'>"; } else { echo "<tr><td>"; }
				echo "<a href='messages.php?mode=view&m=".$m['message_ID']."'>".$m['title']."</a>";
				echo "</td>";
				echo "<td>";
				if($receivercount['res'] > 1)
				{
					echo "<span class='italic'>flere brugere </span>";	
				}
				else
				{
					if($receivercount['res'] < 1)
					{
						echo "<span class='italic'>ikke længere eksisterende bruger </span>";	
					}
					else
					{
						$msgreceiver = $forum->get_message_receivers($m['message_ID'])->fetch_assoc();	
						$superuser = $forum->get_superuser($msgreceiver['fk_receiver_ID'])->fetch_assoc();
						echo "<a class='username' style='color:".$superuser['color'].";'  href='memberprofile.php?id=".$superuser['superuser_ID']."'>".$superuser['name']."</a> ";
					}
				}
				echo "</td>";
				echo "<td> ".date("d.m.Y G:i", strtotime($m['datetime']))."</td>";	
				echo "<td>"; 
				echo "<a href='messages.php?deletesent=".$m['message_ID']."'"; ?>
				onclick='return confirm("Er du sikker på, at du vil slette denne besked? Den vil ikke længere kunne ses i din udbakke.")'
				<?php echo "><img src='images/topic_delete.png' title='Slet post'/></a> ";
				echo "</td>";	
				echo "</tr>";
			}
		}
		
		echo "</table>";
	}
	
	if($_GET['mode'] == "view")
	{
		if(isset($_GET['m']))
		{
			$messageid = $_GET['m'];
			$message = $forum->get_message($messageid)->fetch_assoc();
			
			$trygetreceiver = $forum->try_get_message_receivers($messageid, $user_logged_in_ID)->fetch_assoc();
			if($message['fk_sender_ID'] == $user_logged_in_ID || $trygetreceiver['res'] > 0)
			{
				if($trygetreceiver['res'] > 0) { $updateread = $forum->update_read_status($messageid, $user_logged_in_ID); }
				
				$user = $forum->get_superuser($message['fk_sender_ID'])->fetch_assoc();		

				echo "<div class='category'><a href=''>Vis besked</a></div>";
				
				echo "<div class='post'>";
				echo "<div class='postprofile'>";
	
				if ($user['avatar'] != "")
				{
					echo "<img src='".$user['avatar']."' />";	
				}
				echo "<a class='username' style='color:".$user['color'].";' href='memberprofile.php?id=".$user['superuser_ID']."'>".$user['name']."</a><br/>";	
				echo $user['title']."<br/><br/>";
				
				$activechars = $forum->count_all_accepted_active_characters_from_superuser($user['superuser_ID'])->fetch_assoc();
				$overall_posts = $forum->count_all_posts_from_superuser($user['superuser_ID'])->fetch_assoc(); 
				
				echo "<b>Tilmeldt:</b> ".date("d.m.Y", strtotime($user['date_joined']))."<br/>";
				echo "<b>Aktive karakterer:</b> ".$activechars['res']."<br/>";
				echo "<b>Posts:</b> ".$overall_posts['res']."<br/>";
					
				echo "</div>";
				echo "<div class='postcontent'>";
				
				echo "<a href='messages.php?mode=view&m=".$message['message_ID']."' class='posttitle'>".$message['title']."</a>";
				echo "<span class='postauthor'>";
				echo "af <a class='username' style='color:".$user['color'].";' href='memberprofile.php?id=".$user['superuser_ID']."'>".$user['name']."</a>";
				echo " » ".date("d.m.Y G:i", strtotime($message['datetime']))."</span>";
				
				$msgtext = nl2br($parser->parse($message['text'])->getAsHtml());
				echo parseURls($msgtext);
				if($user['signature'] != "")
				{
					echo "<div class='postsignature'>";
					$sigtext = nl2br($parser->parse($user['signature'])->getAsHtml());
					echo parseURls($sigtext);
					echo "</div>"; //signature	
				}
				echo "</div>"; //postcontent
				echo "</div>"; //post
				
				echo "<br/>";
				
				echo "<a class='forumbutton' href='messages.php?mode=new&receiver=".$user['superuser_ID']."&title=sv: ".$message['title']."'>Besvar</a>";
			}
		}
	}
	
	if(isset($_GET['deletereceived']))
	{
		$message_id = $_GET['deletereceived'];
		$forum->delete_inbox_message($message_id, $user_logged_in_ID);
		header('Location:messages.php');	
	}
	
	if(isset($_GET['deletesent']))
	{
		$message_id = $_GET['deletesent'];
		$message = $forum->get_message($message_id)->fetch_assoc();
		if($message['fk_sender_ID'] == $user_logged_in_ID)
		{
			$forum->delete_sent_message($message_id);
		}

		header('Location:messages.php?mode=sent');
	}
	
	echo "</div>";
}
			
include('footer.php');
?>