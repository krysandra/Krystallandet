<?php
include('header.php');
?>

<script src="functions/charts/Chart.js"></script>

<?php
echo "<div id='forumcontainer'>";
echo "<div id='forumlist'>";

if(empty($_GET))
{
	echo "<div class='category'><a href=''>Efterlyste karakterer</a></div>";	
	
	$wantedlist = $forum->get_wantedlist();
	while($wantedpost = $wantedlist->fetch_assoc())
	{
		$profiledata = $forum->get_character_profiledata($wantedpost['fk_character_ID'])->fetch_assoc();
		$character = $forum->get_character($wantedpost['fk_character_ID'])->fetch_assoc();
		echo "<div class='wantedpost' id='".$wantedpost['wanted_ID']."'>";
		if($character['avatar'] != ""){ echo "<img src='".$character['avatar']."'/>"; }
		else { echo "<img src='images/noavatar.png'/>"; }
		echo "<h5><a href='characterprofile.php?id=".$wantedpost['fk_character_ID']."'>".$profiledata['fullname']."</a></h5>";
		echo "<p><span class='bold'>Forbrydelse:</span> ".$wantedpost['crime']."</p>
		<p><span class='bold'>Kendetegn:</span> ".$wantedpost['features']."</p>
		<p><span class='bold'>Opholdssted:</span> ".$wantedpost['whereabouts']."</p>
		<p><span class='bold'>Dusør:</span> ".$wantedpost['bounty']."</p>";
		echo "</div>";
	}

	echo "<br/>";
	echo "<hr/>";
	echo "<br/>";
	if($user_rank > 0)
	{
		echo "<a href='wantedlist.php?new' class='forumbutton'>Ny efterlysning</a>";
	}
}

if(isset($_GET['new']))
{
	$errormsg = "";
	
	if($_POST['submit_new_wantedpost'])
	{
		$wantedchar = $_POST["nextposter_value"];
		$crime = $_POST['crime'];
		$features = $_POST['features'];
		$whereabouts = $_POST['whereabouts'];
		$bounty = $_POST['bounty'];
		
		$tryfindchar = $forum->try_find_character_by_name($wantedchar)->fetch_assoc(); 	
		if($tryfindchar['res'] > 0)
		{
			$character = $forum->get_character_by_name($wantedchar)->fetch_assoc();
			$existingbounty = $forum->check_bounty($character['character_ID'])->fetch_assoc();
			
			if($existingbounty['res'] < 1)
			{
				$newbounty = $forum->add_new_bounty($character['character_ID'], $crime, $features, $whereabouts, $bounty);
				echo "<script>window.location = 'wantedlist.php';</script>";
				header('Location:wantedlist.php'); 
			}
			else
			{
				$errormsg = "Denne karakter er allerede efterlyst";	
			}
			
		}
		else
		{
			$errormsg = "Den valgte karakter findes ikke";	
		}
	}
	
	echo "<div class='category'><a href=''>Ny efterlysning</a></div>";
	echo "<div id='newwantedpost'>";
	echo "<table>";
	echo "<form id='postform' method='post'>";
	echo "<tr><td class='bold'>Karakter: "; echo "</td><td>";
	echo "<input type='radio' style='display:none;' name='nextposter' value='textinput' id='nextposter_radio' checked> ";
	echo "<input type='text' name='nextposter_value' id='nextposter_value' class='charname' required/>";
					?>
        <a class="smallbutton" href="" onclick="popup('findcharacter.php'); return false;">Find karakter</a>

        <script type="text/javascript">
            function popup (url) {
                win = window.open(url, "window1", "width=820,height=580,status=no,scrollbars=no,resizable=yes");
                win.focus();
            }
        </script>
   <?php
   	echo "</td></tr>";
	echo "<tr><td colspan='2' class='bold'>Forbrydelse:</td></tr>";
	echo "<tr><td colspan='2'><textarea name='crime' required></textarea></td></tr>";
	echo "<tr><td colspan='2' class='bold'>Kendetegn:</td></tr>";
	echo "<tr><td colspan='2'><textarea name='features' required></textarea></td></tr>";
	echo "<tr><td class='bold'>Opholdssted:</td>";
	echo "<td><input type='text' name='whereabouts' required/></td></tr>";
	echo "<tr><td class='bold'>Dusør:</td>";
	echo "<td><input type='text' name='bounty' required/></td></tr>";
	echo "<tr><td colspan='2' class='center'><input type='submit' name='submit_new_wantedpost' value='Send til godkendelse'>*</td></tr>";
	
   	echo "</form>";
	echo "</table>";
	echo "<span class='errormsg'>".$errormsg."</span>";
	echo "<br/><br/>*<span class='smalltext italic'>En admin skal godkende efterlysningen, før den bliver vist på siden.</span>";
	
	echo "</div>";
}

echo "</div>";

include('sidebar.php');
echo "</div>";
?>

<?php
include('footer.php');
?>