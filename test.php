<?php
include('header.php');

	$toplevelforums = $forum->count_toplevel_forums()->fetch_assoc();
	$max = $toplevelforums['res'];
	$next = 0;
	for($i = 0; $i < $max; $i++)
	{
		$currentforum = $forum->get_toplevel_forum($next)->fetch_assoc();
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
					
					$stack = array();
					array_push($stack, $currentsubforum['forum_ID']);
					$numberofposts = 0;
					
					while(!empty($stack))
					{
						$currentforumid = array_pop($stack);
						$forumposts = $forum->count_forum_topics($currentforumid)->fetch_assoc();
						$numberofposts += $forumposts['res'];
						
						$subforums = $forum->get_all_subforums($currentforumid);
						while($subforum = $subforums->fetch_assoc())	
						{
							array_push($stack, $subforum['forum_ID']);
						}
					}
					echo $numberofposts;
						
					$subnext = $currentsubforum['forum_ID'];	
				}
			}
			$next = $currentforum['forum_ID'];		
	}

echo "</div>";
?>
