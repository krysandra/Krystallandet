<?php
echo "<div id='sidebar'>";

echo "<div class='sidebox'>";
echo "<div class='topbar'>";
echo "<h3>Velkommen</h3>";
echo "</div>";
echo "<div class='sidebarcontent'>";
echo "<h5  class='center'>Online tekstrollespil</h5>
<p class='center'>
Vi befinder os i det magiske land, Krystallandet, hvis univers læner sig op ad en middelalderinspireret periode, men med magi, elvere, dæmoner og andre magiske væsner.
<br/><br/>
Skab din egen, originale karakter og vær med til at bestemme, hvordan historien skal udfolde sig.<br/><br/>
<a href=''>Klik her for at komme godt i gang</a></p>
</span>";
echo "</div></div>";


echo "<div class='sidebox'>";
echo "<div class='topbar'>";
echo "<h3>Top-posters</h3>";
echo "</div>";
echo "<div class='sidebarcontent'>";
echo "<span class='center'>";
echo "<h5>Denne måned</h5>";
echo "<p class='center'>";
$monthly_topposters = $forum->get_topposters_monthly(date('m'),date('Y'));
while($user = $monthly_topposters->fetch_assoc())
{
	echo "<a class='username' style='color:".$user['color'].";' href='memberprofile.php?id=".$user['superuser_ID']."'>".$user['name']."</a>: ".$user['NumberOfPosts']."<br/>";	
}
echo "</p>";
echo "<h5>I alt</h5>";
echo "<p class='center'>";
$overall_topposters = $forum->get_topposters_overall();
while($user = $overall_topposters->fetch_assoc())
{
	echo "<a class='username' style='color:".$user['color'].";' href='memberprofile.php?id=".$user['superuser_ID']."'>".$user['name']."</a>: ".$user['NumberOfPosts']."<br/>";	
}
echo "</p>";
echo "</span></div></div>";

echo "<div class='sidebox'>";
echo "<div class='topbar'>";
echo "<h3>Hurtige links</h3>";
echo "</div>";
echo "<div class='sidebarcontent'>";
echo "
<a href=''>Kort over Krystallandet</a><br/>
<a href=''>Sidens regler</a><br/>
<a href=''>Racebeskrivelser</a><br/>
<a href=''>Tidslinje</a><br/>
<a href=''>Trosretninger</a><br/>
<a href=''>Disse roller mangles</a><br/>
<a href=''>Faceclaimlisten</a><br/>
<a href=''>Reklamer</a><br/>
";
echo "</div></div>";

echo "<div class='sidebox'>";
echo "<div class='topbar'>";
echo "<h3>Statistik</h3>";
echo "</div>";
echo "<div class='sidebarcontent'>";
$numberofsuperusers = $forum->count_all_superusers()->fetch_assoc(); echo "Brugere: ".$numberofsuperusers['res']."<br/>";
$acceptedchars = $forum->count_all_accepted_characters()->fetch_assoc(); echo "Godkendte karakterer: ".$acceptedchars['res']."<br/>";
$activechars = $forum->count_all_active_characters()->fetch_assoc(); echo "Aktive karakterer: ".$activechars['res']."<br/>";
echo "Nyeste karakter: "; $newestchar = $forum->get_newest_character()->fetch_assoc(); 
echo "<a class='username' style='color:".$newestchar['color'].";' href='characterprofile.php?id=".$newestchar['character_ID']."'>".$newestchar['name']."</a><br/>";
echo "Nyeste bruger: "; $newestuser = $forum->get_newest_superuser()->fetch_assoc(); 
echo "<a class='username' style='color:".$newestuser['color'].";' href='memberprofile.php?id=".$newestuser['superuser_ID']."'>".$newestuser['name']."</a><br/><br/>";

$ingametopics = $forum->count_all_ingame_topics()->fetch_assoc(); echo "Emner (ingame): ".$ingametopics['res']."<br/>";
$alltopics = $forum->count_all_topics()->fetch_assoc(); echo "Emner (i alt): ".$alltopics['res']."<br/>";
$ingameposts = $forum->count_all_ingame_posts()->fetch_assoc(); "Posts (ingame): ".$ingameposts['res']."<br/>";
$allposts = $forum->count_all_posts()->fetch_assoc(); echo "Posts (i alt): ".$allposts['res']."<br/>";
echo "</div></div>";

echo "<div class='sidebox'>";
echo "<div class='topbar'>";
echo "<h3>Online nu</h3>";
echo "</div>";
echo "<div class='sidebarcontent'>";
$onlinenumber = $forum->count_online_users()->fetch_assoc();
if($onlinenumber['res'] > 0)
{
	$onlineusers = $forum->get_online_users();
	while($onlineuser = $onlineusers->fetch_assoc())
	{
		echo "<a class='username' style='color:".$onlineuser['color'].";' href='memberprofile.php?id=".$onlineuser['superuser_ID']."'>".$onlineuser['name']."</a> ";	
	}
}
else
{
	echo "Ingen online";	
}
echo "<br/><br/>";
echo "Lige nu: ".$onlinenumber['res']."<br/>";
$onlinetoday = $forum->count_online_users_today()->fetch_assoc(); echo "I dag: ".$onlinetoday['res']."<br/>";
echo "</div></div>";

echo "<div class='sidebox'>";
echo "<div class='topbar'>";
echo "<h3>Admins</h3>";
echo "</div>";
echo "<div class='sidebarcontent center'>";
$admins = $forum->get_admins();

while($admin = $admins->fetch_assoc())
{
	echo " <a href='memberprofile.php?id=".$admin['superuser_ID']."'>";
	if($admin['avatar'] != "" )
	{
		echo "<img src='".$admin['avatar']."' width='75px' height='75px' title='".$admin['name']."'/>";	
	}
	else
	{
		echo "<img src='images/noavatar.png' width='75px' height='75px' title='".$admin['name']."'/>"; 	
	}
	echo "</a> ";
}

echo "";
echo "</div></div>";

echo "<div class='sidebox'>";
echo "<div class='topbar'>";
echo "<h3>Forumposts</h3>";
echo "</div>";
echo "<div class='sidebarcontent'>";
$latestposts = $forum->get_five_latest_overall_posts($user_rank);
echo "<table>";
while($post = $latestposts->fetch_assoc())
{
	$numofposts = $forum->get_numberof_posts($post['topic_ID'])->fetch_assoc();
	$numofanswers = ($numofposts['res'])-1;
	$pagenumber = ceil($numofposts['res'] / $postsperpage);
	$lastpost = $forum->get_last_post($post['topic_ID'])->fetch_assoc();
	
	echo "<tr><td>";
	echo "<a class='bold' href='viewtopic.php?t=".$post['topic_ID']."&currentpage=".$pagenumber."#".$lastpost['post_ID']."'>".$post['topictitle']."</a><br/>";
	if($post['warning'] != ""){ echo "<span class='italic'>".$post['warning']."</span><br/>"; }
	$lastposter = $forum->get_last_topic_poster($post['topic_ID'])->fetch_assoc();
	if($post['ingame'] == 1) 
	{ 
		$character = $forum->get_character($lastposter['fk_character_ID'])->fetch_assoc();
		echo "af <a class='username' style='color:".$character['color'].";' href='characterprofile.php?id=".$lastposter['fk_character_ID']."'>".$character['name']."</a> ";
	}
	else
	{
		$superuser = $forum->get_superuser($lastposter['fk_superuser_ID'])->fetch_assoc();
		echo "<a class='username' style='color:".$superuser['color'].";' href='memberprofile.php?id=".$lastposter['fk_superuser_ID']."'>".$superuser['name']."</a> ";
	}
	echo date("j. M Y G:i", strtotime($post['last_posted']))."<br/>";
	echo " i <a href='viewforum.php?f=".$post['forum_ID']."'>".$post['forumtitle']."</a><br/>";
	echo "</td></tr>";	
}
echo "</table>";
echo "<p class='center'>";
echo "<a href='search.php?latestposts'>Se flere</a>";
echo "</p>";
echo "</div></div>";

echo "<div class='sidebox'>";
echo "<div class='topbar'>";
echo "<h3>Credits</h3>";
echo "</div>";
echo "<div class='sidebarcontent center'>";
echo "<h5>Billeder</h5>";
echo "<a href='http://lingdumstudog.deviantart.com/'>LINGDUMSTUDOG</a><br/>";
echo "<a href='http://sandara.deviantart.com/'>Sandara</a><br/>";
echo "</div></div>";

?>