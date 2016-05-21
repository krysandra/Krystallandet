</div>

<div id="footer">
<div id="usermenu">
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

ob_end_flush();
?>

            
        </div>    
        </div>
	</body>

</html>