<?php
session_start();
include('../dbfunctions.php');
$forum = new dbFunctions();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="style.css" type="text/css" />
<title>Chatbeskeder</title>
</head>

<body>
    <div id="chat-wrap-large">
        <table id="chat-area">  
		<?php
		if(isset($_GET['offset'])) { $offset = $_GET['offset'];} else { $offset = 21; }
        $messages = $forum->get_older_ic_chat_messages($offset, 20);
		$datetime = date("Y-m-d H:i:s");
        
        while($message = $messages->fetch_assoc())
        {
				$character = $forum->get_character($message['fk_character_ID'])->fetch_assoc();

				$username = "<a style='color:".$character['color'].";' href='../characterprofile.php?id=".$character['character_ID']."'>".$character['name']."</a></span>"; 
				echo "<tr><td class='chatmsg'> <div class='datetxt'>".date("j. M y, H:i", strtotime($message['datetime']))."</div> ".$username.": ".$message['message']."</td></tr>";	
				$datetime = $message['datetime'];
        }
        
        ?>
        </table>
        <?php
		$olderoffset = $forum->count_older_ic_chat_messages($datetime)->fetch_assoc();
		if($olderoffset['res'] > 0) { echo "<a id='backbtn' href='ic-messages.php?offset=".($offset+20)."'>Tilbage</a>"; } 
		if($offset > 40) { echo "<a id='forwardbtn' href='ic-messages.php?offset=".($offset-20)."'>Frem</a>"; } 
		?>
    </div>
</body>
</html>
