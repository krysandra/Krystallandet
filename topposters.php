<style>
body
{
	font-family: Verdana, Geneva, sans-serif;
	font-size: 12px;	
	background-color: #e6e6e6;
	color: #343a41;	
}
a
{
	text-decoration: none;
	color: #674c31;
}
.center
{
	text-align: center;
}
h5
{
	text-decoration: none;
	color: #674c31;
	font-size: 14px;
	font-family: Verdana, Geneva, sans-serif;
	text-transform: uppercase;
	padding: 5px;
	margin: 0;
}
.username
{
	font-weight: bold;
	text-decoration: none;
	color: #674c31;
}

</style>

<?php
require_once('dbfunctions.php');
$forum = new dbFunctions();
echo "<h5 class='center'>Denne måned</h5>";
echo "<p class='center'>";

$monthly_topposters = $forum->get_topposters_monthly(date('m'),date('Y'));

while($user = $monthly_topposters->fetch_assoc())
{
	echo "<a class='username' style='color:".$user['color'].";' href='memberprofile.php?id=".$user['superuser_ID']."'>".$user['name']."</a>: ".$user['NumberOfPosts']."<br/>";	
}

echo "</p>";
echo "<h5 class='center'>I alt</h5>";
echo "<p class='center'>";
$overall_topposters = $forum->get_topposters_overall();
while($user = $overall_topposters->fetch_assoc())
{
	echo "<a class='username' style='color:".$user['color'].";' href='memberprofile.php?id=".$user['superuser_ID']."'>".$user['name']."</a>: ".$user['NumberOfPosts']."<br/>";	
}
echo "</p>";

?>
