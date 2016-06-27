<?php
session_start();
include('../dbfunctions.php');
$forum = new dbFunctions();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>IC-Chat</title>
    
    <link rel="stylesheet" href="style.css" type="text/css" />
    
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script type="text/javascript" src="ic-chat.js"></script>
   
   <?php
   if(!isset($_SESSION['user']))
	{
		$user_rank = 0;
		$user_logged_in_ID = 0;
		$user_logged_in = array();
		$accepted_chars = 0;
	}
	else
	{
		$user_logged_in_ID = $_SESSION['user'];
		$user_logged_in = $forum->get_superuser($user_logged_in_ID)->fetch_assoc();
		$user_rank = $user_logged_in['fk_role_ID']; 	
		$user_accepted_chars = $forum->count_all_accepted_alive_characters_from_superuser($user_logged_in_ID)->fetch_assoc();
		$accepted_chars = $user_accepted_chars['res'];
	}
?>   

</head>

<body onload="setInterval('chat.update()', 1000)">

    <div id="page-wrap">
    
        <div id="chat-wrap">
        <table id="chat-area">
        </table>
        <div class='oldermessages' onclick="olderchatmsg('ic-messages.php');">[Se ældre chatbeskeder]</div>
        </div>

        <?php
		if($user_logged_in_ID > 0 && $accepted_chars > 0)
		{
		?>
        
        	<form id="send-message-area">
        	<table id='send-area'> <tr>
        	<td></td>
        	<td><select id='character'>
            <?php
			$userchars = $forum->get_characters_from_superuser($user_logged_in_ID);		
					while ($char = $userchars->fetch_assoc())
					{		
						echo "<option value='".$char['character_ID']."'>".$char['name']."</option>";
					}
			?>
            </select></td></tr>
            <tr><td colspan='4'><textarea id="icsendie" maxlength = '500' ></textarea></td></tr>
            </table>
        	</form>
            
         <?php
		}
		else
		{ ?>
        	<table id='send-area'> <tr>
        	<td></td>
        	<td><select disabled><option>Karakter</option></select></td></tr>
            <tr><td colspan='2'><textarea id="disabledicsendie" disabled></textarea></td></tr>
            </table>
		
        <?php
		}
		?>
        
         <script type="text/javascript">
			function popup (url) {
				win = window.open(url, "window1", "scrollbars=1, width=300,height=300,status=no,scrollbars=no,resizable=no,top=300, left=500");
					win.focus();
				}
			function olderchatmsg (url) {
				win = window.open(url, "window2", "scrollbars=1, width=800,height=600,status=no,scrollbars=no,resizable=no,top=300, left=500");
					win.focus();
				}
		</script>
        
        <script type="text/javascript">
      
		var charid = $("#character").val();

    	// kick off chat
        var chat =  new Chat();
    	$(function() {
    	
    		 chat.getState(); 
    		 
    		 // watch textarea for key presses
             $("#icsendie").keydown(function(event) {  
             
                 var key = event.which;  
           
                 //all keys including return.  
                 if (key >= 33) {
                   
                     var maxLength = $(this).attr("maxlength");  
                     var length = this.value.length;  
                     
                     // don't allow new content if length is maxed out
                     if (length >= maxLength) {  
                         event.preventDefault();  
                     }  
                  }  
    		 																																																});
    		 // watch textarea for release of key press
    		 $('#icsendie').keyup(function(e) {	
    		 					 
    			  if (e.keyCode == 13 && e.shiftKey == false) { 
    			  
                    var text = $(this).val();
    				var maxLength = $(this).attr("maxlength");  
                    var length = text.length; 
                     
                    // send 
                    if (length <= maxLength + 1) {                      
					 
					 	var charid = $("#character").val();
    			        chat.send(text, charid);	
    			        $(this).val("");
    			        
                    } else {
                    
    					$(this).val(text.substring(0, maxLength));
    					
    				}	
    				
    				
    			  }
             });
            
    	});
    </script>
    
    </div>

</body>

</html>