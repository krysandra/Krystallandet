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
<link rel="stylesheet" href="style.css" type="text/css" />
<title>Chatbeskeder</title>
</head>

<body>
    <div id="chat-wrap-large">
        <table id="chat-area">  
		<?php
		if(isset($_GET['offset'])) { $offset = $_GET['offset'];} else { $offset = 21; }
        $messages = $forum->get_older_chat_messages($offset, 20);
		$datetime = date("Y-m-d H:i:s");
        
        while($message = $messages->fetch_assoc())
        {
                $username = ""; $img = ""; $title = "";
				
				if($message['title'] != "") { $title = " " .$message['title'];} else { $title = ""; } 
				if($message['link'] != "") { $username = "<span style='color:".$message['color'].";'><a style='color:".$message['color'].";' 
				target='blank' href='".$message['link']."'>" . $message['username'] . $title . "</a></span>"; }
				else { $username = "<span style='color:".$message['color'].";'>".$message['username'] . $title . "</span>"; }
				if($message['avatar'] != "") { $img = "<td class='chatmsg' style='min-height: 45px;'><img class='chatavatar' src='".$message['avatar']."' width='45px' height='45px' />"; } 
				else { $img = "<td class='chatmsg'>"; }
				
				echo "<tr>".$img." <div class='datetxt'>".date("j. M y, H:i", strtotime($message['datetime']))."</div> ".$username.": ".$message['message']."</td></tr>";	
				$datetime = $message['datetime'];
        }
        
        ?>
        </table>
        
        <?php
		$olderoffset = $forum->count_older_chat_messages($datetime)->fetch_assoc();
		if($olderoffset['res'] > 0) { echo "<a id='backbtn' href='messages.php?offset=".($offset+20)."'>Tilbage</a>"; } 
		if($offset > 40) { echo "<a id='forwardbtn' href='messages.php?offset=".($offset-20)."'>Frem</a>"; } 
		?>
        
    </div>
</body>
</html>
