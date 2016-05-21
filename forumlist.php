<?php
echo "<div id='forumlist'>";
if(empty($_GET))
{
	$toplevelforums = $forum->count_toplevel_forums()->fetch_assoc();
	$max = $toplevelforums['res'];
	$next = 0;
	$allforumposts = array();
	$forumpostcount = $forum->count_all_forum_posts();
	while($forumposts = $forumpostcount->fetch_assoc()){ $allforumposts[$forumposts['fk_forum_ID']] = $forumposts['numberOfPosts']; }
	$allforumtopics = array();
	$forumtopiccount = $forum->count_all_forum_topics();
	while($forumtopics = $forumtopiccount->fetch_assoc()){ $allforumtopics[$forumtopics['fk_forum_ID']] = $forumtopics['numberOfTopics']; }
	for($i = 0; $i < $max; $i++)
	{
		$currentforum = $forum->get_toplevel_forum($next)->fetch_assoc();
		if ($user_rank == 3 && $currentforum['read_access'] >= 1 || $user_rank == 2 && $currentforum['read_access'] <= 2 
			|| $user_rank == 1 && $currentforum['read_access'] <= 1 || $currentforum['read_access'] == 0 )
		{
			if($currentforum['category'] == 1)
			{
				echo "<div class='category'><a href='viewforum.php?f=".$currentforum['forum_ID']."'>".$currentforum['title']."</a></div>";	
				$subforums = $forum->count_subforums($currentforum['forum_ID'])->fetch_assoc();
				$submax = $subforums['res'];
				$subnext = 0;
				echo "<div class='subforumcontainer'>";
				for($j = 0; $j < $submax; $j++)
				{
					$currentsubforum = $forum->get_subforum($subnext, $currentforum['forum_ID'])->fetch_assoc();
					
					if ($user_rank == 3 && $currentsubforum['read_access'] >= 1 || $user_rank == 2 && $currentsubforumm['read_access'] <= 2 
					|| $user_rank == 1 && $currentsubforum['read_access'] <= 1 || $currentsubforum['read_access'] == 0 )
					{						
						//$numberofposts = count_subforum_posts($currentsubforum['forum_ID']); //$forum->count_forum_posts($currentsubforum['forum_ID'])->fetch_assoc();
						//$numberoftopics = count_subforum_topics($currentsubforum['forum_ID']);//$forum->count_forum_topics($currentsubforum['forum_ID'])->fetch_assoc();
						
						$stack = array();
						array_push($stack, $currentsubforum['forum_ID']);
						$numberofposts = 0;
						$numberoftopics = 0;
						
						
						while(!empty($stack))
						{
							$currentforumid = array_pop($stack);
							//$forumposts = $forum->count_forum_topics($currentforumid)->fetch_assoc();
							$numberofposts += $allforumposts[$currentforumid];
							$numberoftopics += $allforumtopics[$currentforumid];
							
							$subforums = $forum->get_all_subforums($currentforumid);
							while($subforum = $subforums->fetch_assoc())	
							{
								array_push($stack, $subforum['forum_ID']);
							}
						}
						
						
						echo "<div class='subforum'>
						<a href='viewforum.php?f=".$currentsubforum['forum_ID']."'>";
						if($currentsubforum['picture'] == "") { echo "<img class='hovereffect' src='images/forumimages/default.png'/>"; }
						else { echo "<img class='hovereffect' src='images/forumimages/".$currentsubforum['picture']."'/>"; }					
						echo "<h5 class='center'>".$currentsubforum['title']."</h5></a>
						".$currentsubforum['description']."
						<br/>
						<div class='subforuminfo'>
						<span class='rightcorner'>emner: " . $numberoftopics . " posts: " . $numberofposts . " </span>
						</div>
						</div>";
						
					}
					$subnext = $currentsubforum['forum_ID'];
				}
				echo "</div>";
			}
			else
			{
				echo "<div class='toplevelforum'><a href='viewforum.php?f=".$currentforum['forum_ID']."'>".$currentforum['title']."</a></div>";
			}
		}
			
		$next = $currentforum['forum_ID'];		
	}
}
echo "</div>";
?>