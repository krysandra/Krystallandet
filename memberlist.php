<?php
include('header.php');

echo "<div class='category'><a href=''>Medlemsliste</a></div>";
echo "<div id='topmenu'>";
echo "<a href='memberlist.php'>Brugere</a> &#9679; <a href='memberlist.php?chars'>Karakterer</a> &#9679; <a href='memberlist.php?groups'>Grupper</a>";
echo "</div>";	


if(isset($_GET['chars']))
{
	echo "<div class='category'><a href=''>Karakterliste</a></div>";
	echo "<div class='charnavigation'>";
	echo "
	<form name='letters' method='get'>
	<input type='hidden' name='chars'/>
	<input class='letter_button' type='submit' name='l' value='Alle'>
	<input class='letter_button' type='submit' name='l' value='A'>
	<input class='letter_button' type='submit' name='l' value='B'>
	<input class='letter_button' type='submit' name='l' value='C'>
	<input class='letter_button' type='submit' name='l' value='D'>
	<input class='letter_button' type='submit' name='l' value='E'>
	<input class='letter_button' type='submit' name='l' value='F'>
	<input class='letter_button' type='submit' name='l' value='G'>
	<input class='letter_button' type='submit' name='l' value='H'>
	<input class='letter_button' type='submit' name='l' value='I'>
	<input class='letter_button' type='submit' name='l' value='J'>
	<input class='letter_button' type='submit' name='l' value='K'>
	<input class='letter_button' type='submit' name='l' value='L'>
	<input class='letter_button' type='submit' name='l' value='M'>
	<input class='letter_button' type='submit' name='l' value='N'>
	<input class='letter_button' type='submit' name='l' value='O'>
	<input class='letter_button' type='submit' name='l' value='P'>
	<input class='letter_button' type='submit' name='l' value='Q'>
	<input class='letter_button' type='submit' name='l' value='R'>
	<input class='letter_button' type='submit' name='l' value='S'>
	<input class='letter_button' type='submit' name='l' value='T'>
	<input class='letter_button' type='submit' name='l' value='U'>
	<input class='letter_button' type='submit' name='l' value='V'>
	<input class='letter_button' type='submit' name='l' value='W'>
	<input class='letter_button' type='submit' name='l' value='X'>
	<input class='letter_button' type='submit' name='l' value='Y'>
	<input class='letter_button' type='submit' name='l' value='Z'>
	<input class='letter_button' type='submit' name='l' value='Andet'>
	</form>
	";
	echo "</div>";
	
	$letter = "";
	if(isset($_GET['l'])) 
	{ 
		if($_GET['l'] == "Alle") { $letter = ""; }
		else { $letter = $_GET['l']; }
	}
	
		
	if($letter == "Andet") 
	{ 
		$charnumber = $forum->count_character_search_all_specialchars_include_dead()->fetch_assoc(); 
	}
	else
	{
		$charnumber = $forum->count_character_search_all_include_dead($letter)->fetch_assoc(); 
	}
	
	
	if ($charnumber['res']  == 0 )
	{
		echo "<div class='center'><span class='infotext'>Ingen karakterer fundet<br/></span></div>";	
  
	}
	else
	{		
		if($letter == "Andet") 
		{ 
			
			$characters = $forum->get_character_search_all_specialchars_include_dead(); 
		}
		else
		{
			$characters = $forum->get_character_search_all_include_dead($letter); 		
		}
			
	echo "<table id='memberlist' class='tablesorter'>
	<thead><tr><th>Navn</th>	
	<th>Status</th>
	<th>Race</th>
	<th>Alder</th>
	<th>Tilhørsforhold</th>
	<th>Posts</th>
	<th>Skaber</th>
	<th>Oprettelsesdato</th></thead></tr>";
	echo "<tbody>";
	
	while($member = $characters->fetch_assoc())
	{
		echo "<tr>";
		echo "<td><span class='hidden'>".$member['name']."</span>
		<a class='username' href='characterprofile.php?id=".$member['character_ID']."' style='color:".$member['color'].";'>".$member['name']."</a></td>";
		
		//$profiledata = $forum->get_character_profiledata($member['character_ID'])->fetch_assoc();	
		$race = $forum->get_race($member['fk_race_ID'])->fetch_assoc();
		
		if($member['dead'] == 1) { echo "<td class='activeuser'>Død</td>"; } 
		else
		{
			if($member['active'] == 1) { echo "<td class='activeuser'>Aktiv</td>"; } 	
			else { echo "<td class='inactiveuser'>Inaktiv</td>"; } 
		}
		
		echo "<td>".$race['name']."</td>";
		echo "<td>".$member['age']."</td>";
		echo "<td>".$member['alignment']."</td>";
		
		//$characterposts = $forum->count_ingame_posts_from_character($member['character_ID'])->fetch_assoc();
		if($member['characterposts'] == "") { $characterposts = 0; } else { $characterposts = $member['characterposts']; }
		echo "<td>".$characterposts."</td>";
		//$superuser = $forum->get_superuser($member['fk_superuser_ID'])->fetch_assoc();
		echo "<td><span class='hidden'>".$member['superusername']."</span>
		<a class='username' style='color:".$member['superusercolor'].";' href='memberprofile.php?id=".$member['fk_superuser_ID']."'>".$member['superusername']."</a></td>";
		echo "<td><span class='hidden'>".date("Y.m.d", strtotime($member['date_created']))."</span>".date("d.m.Y", strtotime($member['date_created']))."</td>";
		echo "</tr>";
	}
	
	echo "</tbody>";
	echo "<table>";	
	
	?>
    

    
    <script>
	$(".tablesorter").slimtable({
		tableData: null,
		dataUrl: null,
		
		itemsPerPage: 50,
		ipp_list: [50,200,500],
		
		colSettings: [],
		
		text1: "per side",
		text2: "Loading...",
		
		sortStartCB: null,
		sortEndCB: null
		});
	</script>
    
    <?php
	
	}
}
if(isset($_GET['groups']))
{
	$grouplist = $forum->get_all_groups();
	$count = 1;
	
	while($group = $grouplist->fetch_assoc())
	{	
	
		echo "<div class='category' id='".$group['group_ID']."'><a href='#".$group['group_ID']."'>".$group['title']."</a></div>";
	
		echo "<table id='grouptable".$count."' class='tablesorter grouplist'>";
		echo "<thead>";
		echo "<tr><th>Karakter</th><th>Rang</th><th>Race</th><th>Tilhørsforhold</th><th>Status</th><th>Skaber</th></tr>";
		echo "</thead>";
		echo "<tbody>";
		
		$groupmembers = $forum->get_groupmembers($group['group_ID']);
		while($groupmember = $groupmembers->fetch_assoc())
		{
			$member = $forum->get_character($groupmember['fk_character_ID'])->fetch_assoc();
			$grouprank = $forum->get_grouprank($groupmember['fk_rank_ID'])->fetch_assoc();
			$profiledata = $forum->get_character_profiledata($member['character_ID'])->fetch_assoc();	
			$race = $forum->get_race($profiledata['fk_race_ID'])->fetch_assoc();
			
			
			
			echo "<tr>";
			echo "<td><span class='hidden'>".$member['name']."</span>
			<a class='username' href='characterprofile.php?id=".$member['character_ID']."' style='color:".$member['color'].";'>".$member['name']."</a></td>";
			echo "<td class='center'>".$grouprank['title']."</td>";		
			echo "<td>".$race['name']."</td>";
			echo "<td>".$profiledata['alignment']."</td>";
			if($member['dead'] == 1) { echo "<td>Død</td>"; } 
			else
			{
				if($member['active'] == 1) { echo "<td >Aktiv</td>"; } 	
				else { echo "<td>Inaktiv</td>"; } 
			}
			$superuser = $forum->get_superuser($member['fk_superuser_ID'])->fetch_assoc();
			echo "<td><span class='hidden'>".$superuser['name']."</span>
			<a class='username' style='color:".$superuser['color'].";'  href='memberprofile.php?id=".$superuser['superuser_ID']."'>".$superuser['name']."</a></td>";
			echo "</tr>";
		}
		echo "</tbody>";
		echo "</table>";
		
		echo"
		<script>
	    $('#grouptable".$count."').slimtable({
		tableData: null,
		dataUrl: null,
		
		itemsPerPage: 50,
		ipp_list: [10,50,100],
		
		colSettings: [],
		
		text1: 'per side',
		text2: 'Loading...',
		
		sortStartCB: null,
		sortEndCB: null
		});
		</script>";
		
		$count++;
		
	}
	?>
    <?php
	
}
if(empty($_GET))
{
	echo "<div class='category'><a href=''>Brugerliste</a></div>";
	$memberlist = $forum->get_all_superusers();
	echo "<table id='memberlist' class='tablesorter'>";
	echo "<thead>";
	echo "<tr><th class='left'>Brugernavn</th><th>Posts (ingame)</th><th>Posts (I alt)</th><th>Karakterer (aktive)</th><th>Karakterer (I alt)</th>
	<th>Trofæer</th><th>Medlem siden</th><th>Senest aktiv</th></tr>";
	echo "</thead>";
	echo "<tbody>";
	while($member = $memberlist->fetch_assoc())
	{
		//$ingame_posts = $forum->count_ingame_posts_from_superuser($member['superuser_ID'])->fetch_assoc(); 
		//$overall_posts = $forum->count_all_posts_from_superuser($member['superuser_ID'])->fetch_assoc(); 
		//$activechars = $forum->count_active_characters_from_superuser($member['superuser_ID'])->fetch_assoc();
		//$allchars = $forum->count_accepted_characters_from_superuser($member['superuser_ID'])->fetch_assoc();
		//$achievementnumber = $forum->count_all_userachievements_from_user($member['superuser_ID'])->fetch_assoc();
		if($member['ingame_posts'] == "") { $ingame_posts = 0; } else { $ingame_posts = $member['ingame_posts']; }
		if($member['overall_posts'] == "") { $overall_posts = 0; } else { $overall_posts = $member['overall_posts']; }
		if($member['activechars'] == "") { $activechars = 0; } else { $activechars = $member['activechars']; }
		if($member['allchars'] == "") { $allchars = 0; } else { $allchars = $member['allchars']; }
		if($member['achievementnumber'] == "") { $achievementnumber = 0; } else { $achievementnumber = $member['achievementnumber']; }
		
		echo "<tr>";
		echo "<td><span class='hidden'>".$member['name']."</span>
		<a class='username' style='color:".$member['color'].";' href='memberprofile.php?id=".$member['superuser_ID']."'>".$member['name']."</a></td>";
		echo "<td class='center'>".$ingame_posts."</td>";
		echo "<td class='center'>".$overall_posts."</td>";
		echo "<td class='center'>".$activechars."</td>";
		echo "<td class='center'>".$allchars."</td>";
		echo "<td>".$achievementnumber."</td>";
		echo "<td class='center'><span class='hidden'>".date("Y.m.d", strtotime($member['date_joined']))."</span>".date("d.m.Y", strtotime($member['date_joined']))."</td>";
		echo "<td class='center'><span class='hidden'>".date("Y.m.d H:i", strtotime($member['last_active']))."</span>".date("d.m.Y H:i", strtotime($member['last_active']))."</td>";
		echo "</tr>";
	}
	echo "</tbody>";
	echo "</table>";
	?>
    
    <script>
	$(".tablesorter").slimtable({
		tableData: null,
		dataUrl: null,
		
		itemsPerPage: 50,
		ipp_list: [50,100,200],
		
		colSettings: [],
		
		text1: "per side",
		text2: "Loading...",
		
		sortStartCB: null,
		sortEndCB: null
		});
	</script>
    
    <?php
	
}
include('footer.php');
?>