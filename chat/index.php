<?php
session_start();
include('../dbfunctions.php');
$forum = new dbFunctions();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    
    <title>Chat</title>
    
    <link rel="stylesheet" href="style.css" type="text/css" />
    
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script type="text/javascript" src="chat.js"></script>
   
   <?php
   if(!isset($_SESSION['user']))
	{
		$user_rank = 0;
		$user_logged_in_ID = 0;
		$user_logged_in = array();
	}
	else
	{
		$user_logged_in_ID = $_SESSION['user'];
		$user_logged_in = $forum->get_superuser($user_logged_in_ID)->fetch_assoc();
		$user_rank = $user_logged_in['fk_role_ID']; 	
	}
?>   

</head>

<body onload="setInterval('chat.update()', 1000)">

    <div id="page-wrap">
    
        <div id="chat-wrap">
        <table id="chat-area">
        </table>
        <div class='oldermessages' onclick="olderchatmsg('messages.php');">[Se ældre chatbeskeder]</div>
        </div>
        
        <?php 
		if($user_logged_in_ID > 0)
		{
		?>
        
        	<form id="send-message-area">
        	<table id='send-area'> <tr>
            <input type='hidden' id='chatcolor' value='<?php echo $user_logged_in['color']; ?>'/>
            <input type='hidden' id='userid' value='<?php echo $user_logged_in_ID; ?>'/>
        	<td class='chatlabel'>Navn:</td>
        	<td><input type='text' id='chatname' maxlength='10' value='<?php echo $user_logged_in['name']; ?>' disabled/></td>
            <td class='chatlabel'>Titel:</td>
        	<td><input type='text' id='chattitle' maxlength='10' value='<?php echo $user_logged_in['chattitle']; ?>'/> </td></tr>
            <tr><td class='chatlabel'>Billede:</td>
            <td><input type='text' id='chatimg' value='<?php if($user_logged_in['chatavatar'] != "") {echo $user_logged_in['chatavatar']; }
			else { echo $user_logged_in['avatar'];} ?>'/></td>
            <td class='chatlabel'>Link:</td>
            <td><input type='text' id='chatlink' value='<?php if ($user_logged_in['chatlink'] != "") {echo $user_logged_in['chatlink']; }
			else {echo '../memberprofile.php?id='.$user_logged_in_ID; } ?>'/> </td></tr>
            <tr><td colspan='4'><textarea id="sendie" maxlength = '500' ></textarea></td></tr>
            </table>
            <div class='linkspan' onclick="popup('smilies.php');">[smilies]</div>
        </form>
            
         <?php
		}
		else
		{ ?>
        <form id="send-message-area">
        	<table id='send-area'> <tr>
            <input type='hidden' id='chatcolor' maxlength='10' value=''/>
            <input type='hidden' id='userid' value='0'/>
        	<td>Navn:</td>
        	<td><input type='text' id='chatname' maxlength='10' value='Gæst'/></td>
            <td>Titel:</td>
        	<td><input type='text' id='chattitle' maxlength='10' value=''/> </td></tr>
            <tr><td>Billede:</td>
            <td><input type='text' id='chatimg' value=''/></td>
            <td>Link:</td>
            <td><input type='text' id='chatlink' value=''/> </td></tr>
            <tr><td colspan='4'><textarea id="sendie" maxlength = '500' ></textarea></td></tr>
            </table>
            <div class='linkspan' onclick="popup('smilies.php');">[smilies]</div>
        </form>
		
        <?php
		}
		?>
        
         <script type="text/javascript">
			function popup (url) {
				win = window.open(url, "window1", "width=300,height=300,status=no,scrollbars=no,resizable=no,top=300, left=500");
					win.focus();
				}
			function olderchatmsg (url) {
				win = window.open(url, "window2", "width=800,height=600,status=no,scrollbars=no,resizable=no,top=300, left=500");
					win.focus();
				}
		</script>
        
        <script type="text/javascript">
      
        var name = $("#chatname").val();
		var title = $("#chattitle").val();
		var chatimg = $("#chatimg").val();
		var chatlink = $("#chatlink").val();
		var chatcolor = $("#chatcolor").val();
		var userid = $("#userid").val();
        
        // default name is 'Guest'
    	if (!name || name === ' ') {
    	   name = "Gæst";	
    	}
    	
    	// strip tags
    	name = name.replace(/(<([^>]+)>)/ig,"");   	
    	
    	// kick off chat
        var chat =  new Chat();
    	$(function() {
    	
    		 chat.getState(); 
    		 
    		 // watch textarea for key presses
             $("#sendie").keydown(function(event) {  
             
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
    		 $('#sendie').keyup(function(e) {	
    		 					 
    			  if (e.keyCode == 13) { 
    			  
                    var text = $(this).val();
    				var maxLength = $(this).attr("maxlength");  
                    var length = text.length; 
                     
                    // send 
                    if (length <= maxLength + 1) {                      
					 
					 	var name = $("#chatname").val();
						var title = $("#chattitle").val();
						var chatimg = $("#chatimg").val();
						var chatlink = $("#chatlink").val();
						var chatcolor = $("#chatcolor").val();
						var userid = $("#userid").val();
    			        chat.send(text, title, chatimg, chatlink, chatcolor, name, userid);	
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