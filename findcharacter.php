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
<body>
<div id="charactersearcher">
<?php
echo "<div class='category'><a href=''>Find karakter</a></div>";
$active_option = 1;
$letter = "";
if(isset($_GET['l'])) 
{ 
	if($_GET['l'] == "Alle") { $letter = ""; }
	else { $letter = $_GET['l']; }
}
if(isset($_GET['active'])) 
{ 
	if($_GET['active'] == "Aktive") { $active_option = 1; }
	if($_GET['active'] == "Inaktive") { $active_option = 0; }
	if($_GET['active'] == "Alle") { $active_option = 2; }
}
if(isset($_GET['search'])) 
{
	$active_option = 2; 
	$letter = $_GET['name'];
}

echo "<div class='charnavigation'>";

?>
<form name='activeoptions' method='get'>
<input class='searchoptions' type='submit' name='active' value='Aktive'>
<input class='searchoptions' type='submit' name='active' value='Inaktive'>
<input class='searchoptions' type='submit' name='active' value='Alle'>
<input type='hidden' name='l' value='<?php echo $letter?>' />
</form>
<form name='activeoptions' method='get'>
<input type='hidden' name='active' value='<?php if ($active_option == 1) {echo "Aktive";}if ($active_option == 0) {echo "Inaktive";} if ($active_option == 2) {echo "Alle";}?>' />
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
<form name='searchoption' method='get'>
<input type='text' class='searchchar' name='name'/> 
<input type='submit' name='search' class='searchchar_submit' value='Søg'>
</form>
<?php
	echo "</div>";
	
	if($letter == "Andet") 
	{ 
		if($active_option == 2) { $charnumber = $forum->count_character_search_all_specialchars()->fetch_assoc(); }
		else { $charnumber = $forum->count_character_search_specialchars($active_option)->fetch_assoc(); }
	}
	else
	{
		if($active_option == 2) { $charnumber = $forum->count_character_search_all($letter)->fetch_assoc(); }
		else { $charnumber = $forum->count_character_search($active_option, $letter)->fetch_assoc(); }
	}
	
	if ($charnumber['res']  == 0 )
	{
		echo "<div class='infotext italic center'>Ingen brugere fundet<br/><br/>";	
        echo "<button onclick='window.close();'>Luk</button></form></div>";      
	}
	else
	{
		
		if($letter == "Andet") 
		{ 
			
			if($active_option == 2) { $characters = $forum->get_character_search_all_specialchars(0, $charnumber['res']); }
			else { $characters = $forum->get_character_search_specialchars($active_option, 0, $charnumber['res']); }
		}
		else
		{
			if($active_option == 2) { $characters = $forum->get_character_search_all($letter, 0, $charnumber['res']); }
			else { $characters = $forum->get_character_search($active_option, $letter, 0, $charnumber['res']); }
		
		}

		echo "<table class='charlist'>";
		echo "<thead>";
		echo "<tr><th>Navn</th><th></th></tr>";
		echo "</thead>";
		echo "<tbody>";
		while($c = $characters->fetch_assoc())
		{
			echo "<tr>";
			echo "<td><a class='username' style='color:".$c['color'].";' target='blank' href='characterprofile.php?id=".$c['character_ID']."'>".$c['name']."</a></td>";
			echo "<td>"; ?>
			<form class='charsearch'>
			<button onclick='window.opener.postform.nextposter_value.value="<?php echo htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8'); ?>"; window.opener.postform.nextposter_radio.checked=true; window.close();'>
			Vælg</button></form>
			<?php echo "</td>";
			echo "</tr>";
		}
		echo "</tbody></table>";
?>
    
    <script>
	
	$(".charlist").slimtable({
		tableData: null,
		dataUrl: null,
		
		itemsPerPage: 10,
		ipp_list: [10,20,50],
		
		colSettings: [],
		
		text1: "per side",
		text2: "Loading...",
		
		sortStartCB: null,
		sortEndCB: null
		});
		
	</script>


<?php
}
?>
</div>
</body>
</html>