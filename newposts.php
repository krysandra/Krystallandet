<?php
include('config.php'); ?>

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

.username
{
	font-weight: bold;
	text-decoration: none;
	color: #674c31;
}

.smallsidebartext
{
	font-size: 11px;	
}

table
{
	width: 100%;
}

td
{
	text-align: center;	
	word-wrap: break-word;
	max-width: 210px;
}

a:hover
{
	color: #9c7651;
}

</style>

<?php

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

?>