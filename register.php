<?php
include('header.php');

echo "<div id='forumpage' class='register'>";

echo "<h2>Tilmelding</h2>";

echo "<span class='smalltext'>Ved at tilmelde dig krystallandet.dk, giver du accept til, at vi gemmer dine personlige oplysninger i vores database. Disse oplysninger vil ikke blive misbrugt, og du vælger selv, hvor meget du ønsker at vise på din personlige profil.<br/>
Det er vigtigt, at du indtaster din rigtige e-mail-adresse, da den skal benyttes til at nulstille dit password, i tilfælde af, at du glemmer det. Krystallandet vil ikke bruge din email til at sende nyhedsbreve eller andre opdateringer, der kan virke irriterende.<br/>
Der vil på siden forekomme skriftligt indhold af seksuelt eller voldelig karakter, og det er vigtigt, at du er indforstået med dette, når du opretter dig. Vi anbefaler også derfor, at du er mindst fyldt 13 år. Når du tilmelder dig, accepterer du samtidig at overholde vores <a href=''>regler</a>.</span>";

if(empty($_GET))
{
	$errormsg = "";
	
	if($_POST['submit_registration'])
	{
		$error = false;
		$errormsg = "Der skete en fejl under registreringen af din bruger:<br/>";
		
		$username = htmlspecialchars($_POST['username']);
		$email = htmlspecialchars($_POST['email']);
		$pass = htmlspecialchars($_POST['password']);
		$confirm = htmlspecialchars($_POST['password_confirm']);
		$reference = htmlspecialchars($_POST['reference']);
		$number = htmlspecialchars($_POST['spamprotection']);
		
		$namecheck = $forum->check_for_existing_superuser_name($username)->fetch_assoc();
		if($namecheck['res'] > 0) { $error = true; $errormsg = $errormsg."Der eksisterer allerede en bruger med dette navn.<br/>";}		
		if($pass == "" || $confirm == "" || $username == "" || $number == "") { $error = true; $errormsg = $errormsg."Du skal udfylde alle felter.<br/>";}	
		if($pass != $confirm) { $error = true; $errormsg = $errormsg."Værdierne indtastet i \"kodekord\" og \" bekræft kodekord\" var ikke ens.<br/>";}		
		if($number > 26 || $number < 20) { $error = true; $errormsg = $errormsg."Du fejlede vores spam-tjek. Den indtastede værdi var forkert.<br/>";}
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = true; $errormsg = $errormsg."Du skal indtaste en rigtig e-mail adressse.<br/>";}
		
		if($error == false)
		{
			$errormsg = "";
			$hash = password_hash($pass, PASSWORD_DEFAULT);
			$created = $forum->create_new_superuser($username, $hash, $email, $reference);
			header('Location:register.php?confirmed');	
		}
	}
	
	echo "<span class='errormsg'>".$errormsg."</span>";	
	
	echo "<hr/>";
	
	echo "<table>";
	echo "<form method='post' class='confirmform'>";
	
	echo "<tr><td>Brugernavn: </td>";
	echo "<td><input type='text' name='username' maxlength='20' required></td></tr>";
	echo "<tr><td>E-mailadresse: </td>";
	echo "<td><input type='text' name='email' required></td></tr>";
	
	echo "<tr><td>Nyt kodeord: </td>";
	echo "<td><input type='password' name='password' required></td></tr>";
	echo "<tr><td>Bekræft kodeord: </td>";
	echo "<td><input type='password' name='password_confirm' required></td></tr>";
	echo "</table>";
	
	echo "<hr/>";
	
	echo "<table>";
	echo "<tr><td>Hvordan fandt du KL? </td>";
	echo "<td><input type='text' name='reference'</td></tr>";
	echo "<tr><td>Skriv et tal mellem 20 og 26 (spam-beskyttelse)</td>";
	echo "<td><input type='text' name='spamprotection' required></td></tr>";
	
	echo "</table>";
	
	echo "<hr/>";
	
	echo "<table>";
	echo "<tr><td></td><td><input type='submit' name='submit_registration' value='Udfør'/></td></tr>";
	echo "</form>";
	echo "</table>";
}
if(isset($_GET['confirmed']))
{
	echo "<hr/>";
	echo "<p>Din bruger er blevet oprettet. Tryk på \"Log ind\" i menuen for at få adgang til forummet og oprette din første karakter.</p>";
}
echo "</div>";
			
include('footer.php');
?>