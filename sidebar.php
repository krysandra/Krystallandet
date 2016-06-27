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
<a href='viewtopic.php?t=35'>Klik her for at komme godt i gang</a></p>
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

$time = microtime(true);
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$bftopposters_time = round(($finish - $start), 4);

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

$time = microtime(true);
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$topposters_time = round(($finish - $start), 4);


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
echo "<h3>Plots</h3>";
echo "</div>";
echo "<div class='sidebarcontent center'>";
echo "<img src='http://i3.photobucket.com/albums/y82/Ametyst/klplots_zpsogfxcjcj.png'/>";
echo "<p><a>Kzar Moras porte er blevet åbnet</a></p>";
echo "<h5>Den første plage</h5>";
echo "<p class='smallsidebartext bold'>";
echo "<a href='search.php?plots'>FIND PLOTTRÅDE</a>";
echo "</p>";
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
$ingameposts = $forum->count_all_ingame_posts()->fetch_assoc(); echo "Posts (ingame): ".$ingameposts['res']."<br/>";
$allposts = $forum->count_all_posts()->fetch_assoc(); echo "Posts (i alt): ".$allposts['res']."<br/>";
echo "</div></div>";

$time = microtime(true);
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$statistics_time = round(($finish - $start), 4);

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


$time = microtime(true);
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$latesttopics_time = round(($finish - $start), 4);

echo "<table>";
while($post = $latestposts->fetch_assoc())
{
	$numofposts = $forum->get_numberof_posts($post['topic_ID'])->fetch_assoc();
	$numofanswers = ($numofposts['res'])-1;
	$pagenumber = ceil($numofposts['res'] / $postsperpage);
	$lastpost = $forum->get_last_post($post['topic_ID'])->fetch_assoc();
	
	echo "<tr><td>";
	echo "<a class='bold' href='viewtopic.php?t=".$post['topic_ID']."&currentpage=".$pagenumber."#".$lastpost['post_ID']."'>".$post['topictitle']."</a><br/>";
	if($post['topictype'] != ""){ echo "<span class='topicwarning'>".$post['topictype']."</span>"; }
	if($post['warning'] != ""){ echo "<span class='topicwarning italic'>".$post['warning']."</span>"; }
	$lastposter = $forum->get_last_topic_poster($post['topic_ID'])->fetch_assoc();
	echo "<span class='smallsidebartext'>";
	if($post['ingame'] == 1) 
	{ 
		if($lastposter['fk_character_ID'] == 0)
		{
			echo "af <a class='username'>slettet karakter</a> ";	
		}
		else
		{
			$character = $forum->get_character($lastposter['fk_character_ID'])->fetch_assoc();
			echo "af <a class='username' style='color:".$character['color'].";' href='characterprofile.php?id=".$lastposter['fk_character_ID']."'>".$character['name']."</a> ";
		}
	}
	else
	{
		if($lastposter['fk_superuser_ID'] == 0)
		{
			echo "af <a class='username'>Gæst</a> ";	
		}
		else
		{
			$superuser = $forum->get_superuser($lastposter['fk_superuser_ID'])->fetch_assoc();
			echo "af <a class='username' style='color:".$superuser['color'].";' href='memberprofile.php?id=".$lastposter['fk_superuser_ID']."'>".$superuser['name']."</a> ";
		}
	}
	echo date("d.m.Y G:i", strtotime($post['last_posted']))."<br/>";
	echo " i <a class='bold' href='viewforum.php?f=".$post['forum_ID']."'>".$post['forumtitle']."</a><br/>";
	echo "</span>";
	echo "</td></tr>";	
}
echo "</table>";
echo "<p class='center'>";
echo "<a href='search.php?latestposts'>(Se flere)</a>";
echo "</p>";
echo "<p class='center smallsidebartext bold'>";
echo "<a href='search.php?openthreads'>FIND ÅBNE TRÅDE</a>";
echo "</p>";
echo "</div></div>";

$time = microtime(true);
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$latestposts_time = round(($finish - $start), 4);


echo "<div class='sidebox'>";
echo "<div class='topbar'>";
echo "<h3>Credits</h3>";
echo "</div>";
echo "<div class='sidebarcontent center'>";
echo "<h5>Billeder</h5>";
echo "<a href='http://lingdumstudog.deviantart.com/'>LINGDUMSTUDOG</a><br/>";
echo "<a href='http://sandara.deviantart.com/'>Sandara</a><br/>";
echo "</div></div>";

echo "<div class='sidebox'>";
echo "<div class='topbar'>";
echo "<h3>Søg</h3>";
echo "</div>";
echo "<div class='sidebarcontent center'>";
echo "<form method='get' action='search.php'>";
echo "<input type='text' placeholder='Søg i alle forumposts..' name='keyword'/><br/>";
echo "<input type='submit' value='Søg' name='search'/>";
echo "</form>";
echo "</div></div>";

echo "<div class='dropbtn' onclick='show()'><h3>Hurtigmenu</h3></div>";
		echo
		"<div id='submenu' style='display:none;'>";
		$toplevelforums = $forum->get_toplevel_forums();
		$categorylist = array();
		
		while($toplevelforum = $toplevelforums->fetch_assoc())
		{
			$categorylist[$toplevelforum['above_ID']] = $toplevelforum;	
		}
		
		$max = count($categorylist);
		$next = 0;
		
		for($i = 0; $i < $max; $i++)
		{
			$margin = 0;
			$currentforum = $categorylist[$next];
			
						$stack = array();
						if ($user_rank == 3 && $currentforum['read_access'] >= 1 || $user_rank == 2 && $currentforum['read_access'] <= 2 
						|| $user_rank == 1 && $currentforum['read_access'] <= 1 || $currentforum['read_access'] == 0 )
						{
						array_push($stack, array($currentforum['forum_ID'], $margin, "<span class='bold'>".$currentforum['title']."</span>"));
						}
						
						while(!empty($stack))
						{
							$currentforumid = array_pop($stack);
							
							$subforums = $forum->get_all_subforums($currentforumid[0]);
							$subforumlist = array();
							
							while($subforum = $subforums->fetch_assoc())
							{
								$subforumlist[$subforum['above_ID']] = $subforum;	
							}

							echo "<a style='margin-left:".$currentforumid[1]."px' href='viewforum.php?f=".$currentforumid[0]."'>".$currentforumid[2]."</a>";
							
							//$subforumnumber = $forum->count_subforums($currentforumid[0])->fetch_assoc();
							//$subforums = $forum->get_all_subforums($currentforumid[0]);
							if(count($subforumlist) > 0) { $margin = $currentforumid[1]+12; }
							$submax = count($subforumlist);
							$subnext = 0;
							$subarray = array();
							
							for($j = 0; $j < $submax; $j++)
							{
								$subforum = $subforumlist[$subnext];
								if ($user_rank == 3 && $subforum['read_access'] >= 1 || $user_rank == 2 && $subforum['read_access'] <= 2 
								|| $user_rank == 1 && $subforum['read_access'] <= 1 || $subforum['read_access'] == 0 )
								{
									array_push($subarray, array($subforum['forum_ID'], $margin, $subforum['title']));
								}
								$subnext = $subforum['forum_ID'];
							}
							$subarray = array_reverse($subarray);
							foreach($subarray as $val)
							{
								array_push($stack, $val);
								
							}
							
						}
						
			
			$next = $currentforum['forum_ID'];	
			
		}
		
      	echo "</div>";
		
$time = microtime(true);
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$quickmenu_time = round(($finish - $start), 4);		
		
?>

<script type="text/javascript">
function show() {
    var x = document.getElementById('submenu');
    if (x.style.display === 'none') {
        x.style.display = 'block';
    } else {
        x.style.display = 'none';
    }
}
</script>