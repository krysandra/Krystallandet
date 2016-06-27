


<?php
switch ($user_rank) {
    case 0:
        echo "<a href='login.php'>Log ind</a> "; 
		echo "<a href='register.php'>Opret bruger</a>";
        break;
    case 1:
        echo "<a href='memberprofile.php?id=".$user_logged_in_ID."'>Min profil</a>";
		echo "<a href='mytopics.php'>Mine tråde"; if ($topictags > 0) { echo " (".$topictags.")"; } echo "</a>"; 
		echo "<a href='ucp.php?menu=char'>Karakterer</a>";
		echo "<a href='messages.php'>Private beskeder"; if ($unread_messages > 0) { echo " (".$unread_messages.")"; } echo "</a>";
		echo "<a href='ucp.php'>Kontrolpanel</a>";
		echo "<a href='logout.php'>Log ud</a>";
        break;
    case 2:
        echo "<a href='memberprofile.php?id=".$user_logged_in_ID."'>Min profil</a>";
		echo "<a href='mytopics.php'>Mine tråde"; if ($topictags > 0) { echo " (".$topictags.")"; } echo "</a>";
		echo "<a href='ucp.php?menu=char'>Karakterer</a>";
		echo "<a href='messages.php'>Private beskeder"; if ($unread_messages > 0) { echo " (".$unread_messages.")"; } echo "</a>";
		echo "<a href='ucp.php'>Kontrolpanel</a>";
		echo "<a href='mcp.php'>MCP"; if ($adminapproval > 0) { echo " (".$adminapproval.")"; } echo "</a>";
		echo "<a href='logout.php'>Log ud</a>";
        break;
	case 3:
        echo "<a href='memberprofile.php?id=".$user_logged_in_ID."'>Min profil</a>";
		echo "<a href='mytopics.php'>Mine tråde"; if ($topictags > 0) { echo " (".$topictags.")"; } echo "</a>";
		echo "<a href='ucp.php?menu=char'>Karakterer</a>";
		echo "<a href='messages.php'>Private beskeder"; if ($unread_messages > 0) { echo " (".$unread_messages.")"; } echo "</a>";
		echo "<a href='ucp.php'>Kontrolpanel</a>";
		echo "<a href='acp.php'>ACP"; if ($adminapproval > 0) { echo " (".$adminapproval.")"; } echo "</a>";
		echo "<a href='logout.php'>Log ud</a>";
        break;
    default:
        echo "<a href='login.php'>Log ind</a>";
		echo "<a href='register.php'>Opret bruger</a>";
}


?>