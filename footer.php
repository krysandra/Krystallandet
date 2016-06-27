</div>

<div id="footer">
<div id="usermenu">
	<img class='scrollbutton' src='images/up.png' id='topofpage' onclick='window.scrollTo(0,0);' style='cursor:pointer;' title='Til toppen'/>
	<?php include('usermenu.php'); ?>
</div>
<?php

echo "<a id='mobilecbox' href='chat/index.php'>Åben chatbox</a>";
echo "<a id='mobileiccbox' href='chat/ic-index.php'>Åben IC-chatbox</a>";

echo "<div id='cbox'>";
	echo "<div id='mainchat'>";
		echo "<div class='category' id='mainchatcategory'><a href='#mainchat'>Chatbox</a></div>";
		echo "<div id='mainchatcontent'>";
			echo "<iframe src='chat/index.php'></iframe>";
		echo "</div>";	
	echo "</div>";
	echo "<div id='icchat'>";
		echo "<div class='category' id='icchatcategory'><a href='#icchat'>IC-chat</a></div>";
		echo "<div id='iccontent'>";
			echo "<iframe src='chat/ic-index.php'></iframe>";
		echo "</div>";	
	echo "</div>";
echo "<div>";
$notificationnumber = (int) $topictags + (int) $unread_messages;
if($user_rank > 1){ $notificationnumber += (int) $adminapproval; }
$notification = "";
if($notificationnumber > 0) { $notification = "(".$notificationnumber.") "; }
$buffer=ob_get_contents();
ob_end_clean();
$buffer=str_replace("%TITLE%",$notification.$pagetitle,$buffer);
echo $buffer;
?>

            
        </div>    
        </div>
                        <?php
						echo "<div style='font-size: 10px; text-align: center;'>";
$time = microtime(true);
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);
echo 'Forum info generated in ' . $foruminfo_time . ' seconds.<br/>';
echo 'Forums generated in ' . $forumcreation_time . ' seconds.<br/>';
echo 'Content before topposter-info generated in ' . $bftopposters_time . ' seconds.<br/>';
echo 'Topposter-info generated in ' . $topposters_time . ' seconds.<br/>';
echo 'Statistics generated in ' . $statistics_time . ' seconds.<br/>';
echo 'Data for latest posts generated in ' . $latesttopics_time . ' seconds.<br/>';
echo 'Latest posts generated in ' . $latestposts_time . ' seconds.<br/>';
echo 'Quick menu generated in ' . $quickmenu_time . ' seconds.<br/>';
echo 'Page generated in ' . $total_time . ' seconds.<br/>';
echo "</div>";
		?>
	</body>

</html>