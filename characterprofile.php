<?php
include('header.php');

if(isset($_GET['id']))
{
	$id = $_GET['id'];
	
	$charexists = $forum->check_for_existing_character($id)->fetch_assoc();
	if($charexists['res'] > 0)
	{
	
		$character = $forum->get_character($id)->fetch_assoc();
		echo "<div class='category'><a href='characterprofile.php?id=".$id."'>".$character['name']."</a></div>";
		echo "<div id='characterinfo'>";
		
			
		echo "<div id='profilepic'>";
		if($character['avatar']	== "") 
		{
			echo "<img src='images/noavatar.png'/>"; 
		}
		else
		{
			echo "<img src='".$character['avatar']."'/>"; 
		}
		echo "</div>";
		if($user_rank > 0) { echo "<a href='messages.php?mode=new&receiver=".$character['fk_superuser_ID']."'>[Send privat besked]</a>";}
		if($user_rank == 3) { echo " <a href='acp.php?charedit=".$id."&submit_char=Ret+karakteroplysninger'>[Redigér]</a>"; }
		if($user_rank == 2) { echo " <a href='mcp.php?charedit=".$id."&submit_char=Ret+karakteroplysninger'>[Redigér]</a>"; }
		echo"<br/><br/>";
		
		$characterposts = $forum->count_ingame_posts_from_character($id)->fetch_assoc();
		echo "<span class='userprofiletabletext'>Antal posts: </span>".$characterposts['res']."<br/>";
		echo "<span class='userprofiletabletext'>Oprettet: </span>".date("d.m.Y", strtotime($character['date_created']))."<br/>";
		if($character['dead'] == 1) { echo "<span class='userprofiletabletext'>Status:</span> Død<br/>"; }
		else { if($character['accepted'] == 0) { echo "<span class='userprofiletabletext'>Status:</span> Ikke godkendt<br/>"; }
		else { if($character['active'] == 1) { echo "<span class='userprofiletabletext'>Status:</span> Aktiv<br/>"; } else { echo "Status: Inaktiv<br/>";}}}
		$user_has_default_group = $forum->check_users_default_group($id)->fetch_assoc();
		if($user_has_default_group['res'] > 0)
		{
			$defaultgroup = $forum->get_users_default_group($id)->fetch_assoc(); 
			$grouprank = $forum->get_grouprank($defaultgroup['fk_rank_ID'])->fetch_assoc(); 
			echo "<span class='userprofiletabletext'>Grupperang: </span>".$grouprank['title']."<br/>";
		}
		$creator = $forum->get_superuser($character['fk_superuser_ID'])->fetch_assoc();
		echo "<span class='userprofiletabletext'>Skaber:</span> <a class='username' style='color:".$creator['color'].";'  
		href='memberprofile.php?id=".$creator['superuser_ID']."'>".$creator['name']."</a><br/>";
		
		if($characterposts['res'] > 0)
		{
			echo "<br/>";
			echo "<span class='userprofiletableheader'>Posting-statistik: </span>";
			$activetopic = $forum->get_most_active_topic_from_char($id)->fetch_assoc();
			$activeforum = $forum->get_most_active_forum_from_char($id)->fetch_assoc();
			echo "<a href='characterprofile.php?showposts=".$id."'>[Se karakterens posts]</a> ";
			echo "<a href='characterprofile.php?showtopics=".$id."'>[Se karakterens emner]</a><br/>";
			echo "<span class='userprofiletabletext'>Mest aktive forum: </span><a href='viewforum.php?f=".$activeforum['forum_ID']."'>".$activeforum['title']."</a><br/>";
			echo "<span class='userprofiletabletext'>Mest aktive emne: </span><a href='viewtopic.php?t=".$activetopic['topic_ID']."'>".$activetopic['title']."</a><br/>";
		}
		
		echo "</div>";
		
		echo "<div id='characterprofile'>";
		echo "<div class='category'><a href=''>Grundlæggende oplysninger</a></div>";
		//echo nl2br(parseBBcodes($character['profile']));	
		$profiledata = $forum->get_character_profiledata($id)->fetch_assoc();	
		$race = $forum->get_race($profiledata['fk_race_ID'])->fetch_assoc();
	
		//echo "<h3>Grundlæggende karakterinformation</h3>";
		echo "<div class='profiledata'>";
		echo "<span class='profiletabletext'>Fulde navn: </span>".nl2br($parser->parse($profiledata['fullname'])->getAsHtml())."<br/>";
		echo "<span class='profiletabletext'>Kaldet: </span>".nl2br($parser->parse($profiledata['shortname'])->getAsHtml())."<br/>";
		echo "<span class='profiletabletext'>Alder: </span>".nl2br($parser->parse($profiledata['age'])->getAsHtml())." år<br/>";
		echo "<span class='profiletabletext'>Fødselsdag: </span>".nl2br($parser->parse($profiledata['birthday'])->getAsHtml())."<br/>";
		echo "<span class='profiletabletext'>Køn: </span>".nl2br($parser->parse($profiledata['gender'])->getAsHtml())."<br/>";
		echo "<span class='profiletabletext'>Tilhørsforhold: </span>".nl2br($parser->parse($profiledata['alignment'])->getAsHtml())."<br/>";
		echo "<span class='profiletabletext'>Tro: </span>".nl2br($parser->parse($profiledata['faith'])->getAsHtml())."<br/>";
		echo "<span class='profiletabletext'>Erhverv: </span>".nl2br($parser->parse($profiledata['profession'])->getAsHtml())."<br/>";
		echo "<span class='profiletabletext'>Race: </span>".nl2br($parser->parse($race['name'])->getAsHtml())."<br/>";
		if($profiledata['raceinfo'] != "") { echo "<span class='profiletabletext'>Uddybende om race: </span>".nl2br($parser->parse($profiledata['raceinfo'])->getAsHtml())."<br/>"; }
		echo "</div>";
		
		echo "<div class='category'><a href=''>Udseende</a></div>";
		echo "<div class='profiledata'>";
		echo "<span class='profiletabletext'>Højde: </span>".nl2br($parser->parse($profiledata['height'])->getAsHtml())." cm <br/>";
		echo "<span class='profiletabletext'>Vægt: </span>".nl2br($parser->parse($profiledata['weight'])->getAsHtml())." kg <br/>";
		echo nl2br($parser->parse($profiledata['looks'])->getAsHtml())."<br/>";
		echo "</div>";
		
		echo "<div class='category'><a href=''>Magi</a></div>";
		echo "<div class='profiledata'>";
		echo "<span class='profiletabletext'>Magisk evne (1): </span>".nl2br($parser->parse($profiledata['magic1'])->getAsHtml())."<br/>";
		echo "<span class='profiletabletext'>Dygtighed til at kontrollere evne: </span>".nl2br($parser->parse($profiledata['magic1_skill'])->getAsHtml());
		if ($profiledata['magic1_skill'] == 0) { echo " (passiv)"; } echo "<br/>";
		echo "<span class='profiletabletext'>Magisk evne (2): </span>".nl2br($parser->parse($profiledata['magic2'])->getAsHtml())."<br/>";
		echo "<span class='profiletabletext'>Dygtighed til at kontrollere evne: </span>".nl2br($parser->parse($profiledata['magic2_skill'])->getAsHtml());
		if ($profiledata['magic2_skill'] == 0) { echo " (passiv)"; } echo "<br/>";
		echo "</div>";
		
		echo "<div class='category'><a href=''>Personlighed</a></div>";
		echo "<div class='profiledata'>";
		echo nl2br($parser->parse($profiledata['personality'])->getAsHtml())."<br/>";
		echo "</div>";
		
		echo "<div class='category'><a href=''>Baggrundshistorie</a></div>";
		echo "<div class='profiledata'>";
		echo nl2br($parser->parse($profiledata['story'])->getAsHtml())."<br/>";
		echo "<span class='profiletabletext'>Familie: </span>".nl2br($parser->parse($profiledata['family'])->getAsHtml())."<br/>";
		echo "<span class='profiletabletext'>Nuværende levested: </span>".nl2br($parser->parse($profiledata['habitat'])->getAsHtml())."<br/>";
		echo "</div>";
		
		if($profiledata['other'] != "")
		{
			echo "<div class='category'><a href=''>Andet</a></div>";
			echo "<div class='profiledata'>";
			$othertext = nl2br($parser->parse($profiledata['other'])->getAsHtml());
			echo parseURls($othertext)."<br/>";
			echo "</div>";	
		}
		
		
		echo "<div class='category'><a href=''>Færdigheder</a></div>";
		echo "<div class='profiledata'>";
		echo "<span class='profiletabletext'>Fysisk styrke: </span>".$profiledata['skill_strength']."<br/>";
		echo "<span class='profiletabletext'>Våbenfærdigheder: </span>".$profiledata['skill_weapons']."<br/>";
		echo "<span class='profiletabletext'>Smidighed: </span>".$profiledata['skill_flexiness']."<br/>";
		echo "<span class='profiletabletext'>Fysisk udholdenhed: </span>".$profiledata['skill_endurance']."<br/>";
		echo "<span class='profiletabletext'>Taktik: </span>".$profiledata['skill_tactics']."<br/>";
		echo "<span class='profiletabletext'>Styrke: </span>".$profiledata['skill_strength']."<br/>";
		echo "<span class='profiletabletext'>Intelligens: </span>".$profiledata['skill_intelligence']."<br/>";
		echo "<span class='profiletabletext'>Kreativitet: </span>".$profiledata['skill_creativity']."<br/>";
		echo "<span class='profiletabletext'>Mental udholdenhed: </span>".$profiledata['skill_mental']."<br/>";
		echo "<span class='profiletabletext'>Chakra: </span>".$profiledata['skill_chakra']."<br/>";
		echo "</div>";
		
		if($character['signature'] != "")
		{
			echo "<div class='category'><a href=''>Signatur</a></div>";
				$parser->parse($character['signature']);
				$sigtext = nl2br($parser->getAsHtml());
				echo parseURls($sigtext);
		}
		echo "</div>";
		
		
	}
	
	else
	{
		echo "Ingen bruger fundet";
	}

}

if(isset($_GET['showposts']))
{
	$id = $_GET['showposts'];
	
	$userexists = $forum->check_for_existing_character($id)->fetch_assoc();
	if($userexists['res'] > 0)
	{
		$member = $forum->get_character($id)->fetch_assoc();
		$postnumber = $forum->get_number_of_posts_to_show_from_character($id, $user_rank)->fetch_assoc();
		$totalpages = ceil($postnumber['res'] / $postsperpage);		
		
		echo "<div class='category'><a href='characterprofile.php?id=".$id."'>Viser ".$postnumber['res']." posts fra karakteren ".$member['name']."</a></div>";
		
		echo "<div class='pagenavigaton'>";
		
		// get the current page or set a default
		if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) {
		   // cast var as int
		   $currentpage = (int) $_GET['currentpage'];
	
		} else {
		   // default page num
		   $currentpage = 1;
		} // end if

		// if current page is greater than total pages...
	
		if ($currentpage > $totalpages) {
		   // set current page to last page
		   $currentpage = $totalpages;
		} // end if
		// if current page is less than first page...
		if ($currentpage < 1) {
		   // set current page to first page
		   $currentpage = 1;
		} // end if
	
			
		$offset = ($currentpage - 1) * $postsperpage;

		$posts = $forum->get_posts_from_character($id, $offset, $postsperpage, $user_rank);
		
		// range of num links to show
		$range = 4;
		// if not on page 1, don't show back links
	
		if ($currentpage > 1) {
			
		// get previous page num
		$prevpage = $currentpage - 1;
		// show < link to go back to 1 page
		 echo " <a class='navlink' href='characterprofile.php?showposts=".$id."&currentpage=$prevpage'>«</a> ";	
			
		if ($currentpage - $range > 1) {
		// show << link to go back to page 1
		echo " <a class='navlink' href='characterprofile.php?showposts=".$id."&currentpage=1'>1</a> ";
		}
		
		} // end if

		
	
		// loop to show links to range of pages around current page
		for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) {
		   // if it's a valid page number...
		   if (($x > 0) && ($x <= $totalpages)) {	
			  // if we're on current page...	
			  if ($x == $currentpage) {
				 // 'highlight' it but don't make a link	
				 if($totalpages > 1) { echo " <span class='navlink_active'><b>$x</b></span>"; }
			  // if not current page...	
			  } else {
				 // make it a link
					 echo " <a class='navlink' href='characterprofile.php?showposts=".$id."&currentpage=$x'>$x</a> ";	
			  } // end else	
		   } // end if 
		} // end for
		// if not on last page, show forward and last page links        
	
		if ($currentpage != $totalpages) {	
		   // get next page	
		   $nextpage = $currentpage + 1;	
		   
		   if ($totalpages - $range > $currentpage) {		   
		   // echo forward link for lastpage	
		   echo " <a class='navlink' href='characterprofile.php?showposts=".$id."&currentpage=$totalpages'>".$totalpages."</a> ";	
		   }
		   
			// echo forward link for next page 	
		   echo " <a class='navlink' href='characterprofile.php?showposts=".$id."&currentpage=$nextpage'>»</a> ";	
		   
		} // end if
	
		echo "</div>";
		/****** end build pagination links ******/
	
		while($p = $posts->fetch_assoc())
		{
			$currenttopic = $forum->get_topic($p['fk_topic_ID'])->fetch_assoc();
			$currentforum = $forum->get_forum($p['fk_forum_ID'])->fetch_assoc();
			
			echo "<div class='smallpost'>";
			echo "<div class='smallpostprofile'>";
			if ($member['avatar'] != "")
			{
				echo "<img src='".$member['avatar']."' />";	
			}
			if ($p['ingame'] == 1)
			{
					echo "<a class='username' style='color:".$member['color'].";' href='characterprofile.php?id=".$member['character_ID']."'>".$member['name']." </a><br/>";
			}		
			echo date("d.m.Y G:i", strtotime($p['datetime']));
			echo "<br/><br/>";
			
			echo "<b>Forum:</b> <a href='viewforum.php?f=".$currentforum['forum_ID']."'>".$currentforum['title']."</a><br/>";
			echo "<b>Emne:</b> <a href='viewtopic.php?t=".$currenttopic['topic_ID']."'>".$currenttopic['title']."</a><br/>";
			$numofposts = $forum->get_numberof_posts($currenttopic['topic_ID'])->fetch_assoc();
			$numofanswers = ($numofposts['res'])-1;
			echo "<b>Svar:</b>  ".$numofanswers."<br/>";
			echo "<b>Visninger:</b>  ".$currenttopic['views']."<br/>";
			
			$numberofprevposts = $forum->count_prev_posts($currenttopic['topic_ID'], $p['datetime'])->fetch_assoc();
			$numberofprevposts = $numberofprevposts['res'] + 1;
			$topicpagenumber = ceil($numberofprevposts / $postsperpage);
			
			echo "</div>";
			echo "<div class='smallpostcontent'>";
			echo "<a class='smallposttitle' href='viewtopic.php?t=".$currenttopic['topic_ID']."&currentpage=".$topicpagenumber."#".$p['post_ID']."'>".$currenttopic['title']."</a>";
			
			if (strlen($p['text']) > 300) 
			{
				$stringCut = substr($p['text'], 0, 300);
				$posttext = substr($stringCut, 0, strrpos($stringCut, ' ')).'...';
			}
			else { $posttext = $p['text']; }
			$posttext = nl2br($parser->parse($posttext)->getAsHtml());	
			echo parseURls($posttext);
			echo "</div></div>";
		}
		
		echo "<div class='pagenavigaton'>";
		if ($currentpage > 1) {
			
		// get previous page num
		$prevpage = $currentpage - 1;
		// show < link to go back to 1 page
		 echo " <a class='navlink' href='characterprofile.php?showposts=".$id."&currentpage=$prevpage'>«</a> ";	
			
		if ($currentpage - $range > 1) {
		// show << link to go back to page 1
		echo " <a class='navlink' href='characterprofile.php?showposts=".$id."&currentpage=1'>1</a> ";
		}
		
		} // end if

		
	
		// loop to show links to range of pages around current page
		for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) {
		   // if it's a valid page number...
		   if (($x > 0) && ($x <= $totalpages)) {	
			  // if we're on current page...	
			  if ($x == $currentpage) {
				 // 'highlight' it but don't make a link	
				 if($totalpages > 1) { echo " <span class='navlink_active'><b>$x</b></span>"; }
			  // if not current page...	
			  } else {
				 // make it a link
					 echo " <a class='navlink' href='characterprofile.php?showposts=".$id."&currentpage=$x'>$x</a> ";	
			  } // end else	
		   } // end if 
		} // end for
		// if not on last page, show forward and last page links        
	
		if ($currentpage != $totalpages) {	
		   // get next page	
		   $nextpage = $currentpage + 1;	
		   
		   if ($totalpages - $range > $currentpage) {		   
		   // echo forward link for lastpage	
		   echo " <a class='navlink' href='characterprofile.php?showposts=".$id."&currentpage=$totalpages'>".$totalpages."</a> ";	
		   }
		   
			// echo forward link for next page 	
		   echo " <a class='navlink' href='characterprofile.php?showposts=".$id."&currentpage=$nextpage'>»</a> ";	
		   
		} // end if
	
		echo "</div>";
		/****** end build pagination links ******/
	}
	
	else
	{	
		echo "Ingen bruger fundet";
	
	}
}

if(isset($_GET['showtopics']))
{
	$id = $_GET['showtopics'];
	
	$userexists = $forum->check_for_existing_character($id)->fetch_assoc();
	if($userexists['res'] > 0)
	{
		$member = $forum->get_character($id)->fetch_assoc();
		$topicnumber = $forum->get_number_of_topics_to_show_from_character($id, $user_rank)->fetch_assoc();
		$totalpages = ceil($topicnumber['res'] / $topicsperpage);		
		
		echo "<div class='category'><a href='characterprofile.php?id=".$id."'>Viser ".$topicnumber['res']." emner fra karakteren ".$member['name']."</a></div>";
		
		echo "<div class='pagenavigaton'>";
		
		// get the current page or set a default
		if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) {
		   // cast var as int
		   $currentpage = (int) $_GET['currentpage'];
	
		} else {
		   // default page num
		   $currentpage = 1;
		} // end if

		// if current page is greater than total pages...
	
		if ($currentpage > $totalpages) {
		   // set current page to last page
		   $currentpage = $totalpages;
		} // end if
		// if current page is less than first page...
		if ($currentpage < 1) {
		   // set current page to first page
		   $currentpage = 1;
		} // end if
	
			
		$offset = ($currentpage - 1) * $topicsperpage;

		$topics = $forum->get_topics_to_show_from_character($id, $offset, $topicsperpage, $user_rank);
		
	
		// range of num links to show
		$range = 4;
		// if not on page 1, don't show back links
	
		if ($currentpage > 1) {
			
		// get previous page num
		$prevpage = $currentpage - 1;
		// show < link to go back to 1 page
		 echo " <a class='navlink' href='characterprofile.php?showtopics=".$id."&currentpage=$prevpage'>«</a> ";	
			
		if ($currentpage - $range > 1) {
		// show << link to go back to page 1
		echo " <a class='navlink' href='characterprofile.php?showtopics=".$id."&currentpage=1'>1</a> ";
		}
		
		} // end if

		
	
		// loop to show links to range of pages around current page
		for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) {
		   // if it's a valid page number...
		   if (($x > 0) && ($x <= $totalpages)) {	
			  // if we're on current page...	
			  if ($x == $currentpage) {
				 // 'highlight' it but don't make a link	
				 if($totalpages > 1) { echo " <span class='navlink_active'><b>$x</b></span>"; }
			  // if not current page...	
			  } else {
				 // make it a link
					 echo " <a class='navlink' href='characterprofile.php?showtopics=".$id."&currentpage=$x'>$x</a> ";	
			  } // end else	
		   } // end if 
		} // end for
		// if not on last page, show forward and last page links        
	
		if ($currentpage != $totalpages) {	
		   // get next page	
		   $nextpage = $currentpage + 1;	
		   
		   if ($totalpages - $range > $currentpage) {		   
		   // echo forward link for lastpage	
		   echo " <a class='navlink' href='characterprofile.php?showtopics=".$id."&currentpage=$totalpages'>".$totalpages."</a> ";	
		   }
		   
			// echo forward link for next page 	
		   echo " <a class='navlink' href='characterprofile.php?showtopics=".$id."&currentpage=$nextpage'>»</a> ";	
		   
		} // end if
	
		echo "</div>";
		/****** end build pagination links ******/
	
		echo "<table id='topiclist'>";
		echo"<tr><th>Titel</th><th>Svar</th><th>Visninger</th><th>Seneste indlæg</th></tr>";
		while($t = $topics->fetch_assoc())
		{
			echo "<tr><td>";
			if($t['pinned'] == 1) { echo "<img class='topicimage hovereffect' src='images/topic_pinned.png'/>"; }
			else if($t['locked'] == 1) { echo "<img class='topicimage hovereffect' src='images/topic_locked.png'/>"; }
			else { echo "<img class='topicimage hovereffect' src='images/topic_normal.png'/>"; }
			
			echo "<a class='topictitle' href='viewtopic.php?t=".$t['topic_ID']."'>".$t['title']."</a>";
			echo "<br/>";

			$user = $forum->get_character($t['fk_character_ID'])->fetch_assoc();
			echo "Af <a class='username' style='color:".$user['color'].";' href='characterprofile.php?id=".$user['character_ID']."'>".$user['name']."</a> ";
			
			echo "<a href='viewtopic.php?t=".$t['topic_ID']."&currentpage=".$pagenumber."#".$lastpost['post_ID']."'><img src='images/icon_topic_latest.gif' title='Gå til post'/></a>";	
			echo "&raquo; ".date("d.m.Y G:i", strtotime($t['datetime']));
			if($t['topictype'] != "" ) { echo "<span class='topicwarning'>".$t['topictype']; if($t['warning'] != "" ) { echo " <span class='italic'>(Advarsel: ".$t['warning'].")</span>"; } echo "</span>"; }
			else if($t['warning'] != "" ) { echo "<span class='topicwarning'><span class='italic'>Advarsel: ".$t['warning']."</span></span>"; }  
			else { echo "<br/>"; }
			echo "i <a class='topictitle' href='viewforum.php?f=".$t['forum_ID']."'>".$t['forumtitle']."</a>";
			echo "</td>";
			
			$numofposts = $forum->get_numberof_posts($t['topic_ID'])->fetch_assoc();
			$numofanswers = ($numofposts['res'])-1;
			$pagenumber = ceil($numofposts['res'] / $postsperpage);
			
			echo "<td class='center'>".$numofanswers."</td>";
			echo "<td class='center'>".$t['views']."</td>";
			
			$lastpost = $forum->get_last_post($t['topic_ID'])->fetch_assoc();
			echo "<td>";

			$user = $forum->get_character($lastpost['fk_character_ID'])->fetch_assoc();
			echo "af <a class='username' style='color:".$user['color'].";' href='characterprofile.php?id=".$user['character_ID']."'>".$user['name']."</a> ";

			echo "<a href='viewtopic.php?t=".$t['topic_ID']."&currentpage=".$pagenumber."#".$lastpost['post_ID']."'><img src='images/icon_topic_latest.gif' title='Gå til post'/></a>";
			echo "</br>";	
			echo date("d.m.Y G:i", strtotime($lastpost['datetime']))."</td>";
			echo "</tr>";
		}
		echo "</table>";
		
		echo "<div class='pagenavigaton'>";
		if ($currentpage > 1) {
			
		// get previous page num
		$prevpage = $currentpage - 1;
		// show < link to go back to 1 page
		 echo " <a class='navlink' href='characterprofile.php?showtopics=".$id."&currentpage=$prevpage'>«</a> ";	
			
		if ($currentpage - $range > 1) {
		// show << link to go back to page 1
		echo " <a class='navlink' href='characterprofile.php?showtopics=".$id."&currentpage=1'>1</a> ";
		}
		
		} // end if

		
	
		// loop to show links to range of pages around current page
		for ($x = ($currentpage - $range); $x < (($currentpage + $range)  + 1); $x++) {
		   // if it's a valid page number...
		   if (($x > 0) && ($x <= $totalpages)) {	
			  // if we're on current page...	
			  if ($x == $currentpage) {
				 // 'highlight' it but don't make a link	
				 if($totalpages > 1) { echo " <span class='navlink_active'><b>$x</b></span>"; }
			  // if not current page...	
			  } else {
				 // make it a link
					 echo " <a class='navlink' href='characterprofile.php?showtopics=".$id."&currentpage=$x'>$x</a> ";	
			  } // end else	
		   } // end if 
		} // end for
		// if not on last page, show forward and last page links        
	
		if ($currentpage != $totalpages) {	
		   // get next page	
		   $nextpage = $currentpage + 1;	
		   
		   if ($totalpages - $range > $currentpage) {		   
		   // echo forward link for lastpage	
		   echo " <a class='navlink' href='characterprofile.php?showtopics=".$id."&currentpage=$totalpages'>".$totalpages."</a> ";	
		   }
		   
			// echo forward link for next page 	
		   echo " <a class='navlink' href='characterprofile.php?showtopics=".$id."&currentpage=$nextpage'>»</a> ";	
		   
		} // end if
	
		echo "</div>";
		/****** end build pagination links ******/
		
	}
	
	else
	{	
		echo "Ingen bruger fundet";
	
	}
}

$pagetitle = $character['name']." - ";
include('footer.php');
?>