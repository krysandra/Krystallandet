<?php

include('config.php');

?>
<style>
body
{
	background-color: #eeeeee;
	background-image:none;
}
</style>
<?php
echo "<div id='charactersearcher'>";
$superusers = $forum->get_all_superusers();
echo "<form name='usersearch'>";

echo "<table class='charlist'>";
		while($u = $superusers->fetch_assoc())
		{
			echo "<tr>";
			echo "<td><a class='username' style='color:".$u['color']."' href='memberprofile.php?id=".$u['superuser_ID']."' target='blank'>".$u['name']."</a></td>";
			echo "<td>"; ?>
			<form class='charsearch'>
			<button onclick='window.opener.messageform.receiver.value="<?php echo htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8'); ?>"; window.close();'>
			Vælg</button></form>
			<?php echo "</td>";
			echo "</tr>";
		}
		echo "</table><br/><br/>";
echo "</form>";

echo "</div>";
?>