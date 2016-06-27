<?php
include('header.php');

$errormsg = "";

if(isset($_POST['login']))
{
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	
	$user = $forum->get_superuser_by_name($username)->fetch_assoc();
	$hash = $user['password'];	
	$userid = $user['superuser_ID'];
	//$hash = password_hash($password, PASSWORD_DEFAULT);
	if (password_verify ($password,$hash))
	{
		$_SESSION["user"] = $userid;
		if(isset($_POST['remember']) && $_POST['remember'] == 'yes')
		{
			setcookie("www_krystallandet_dk", $userid,
			time()+60*60*24*30*6);
		}
		header('Location:index.php');
		exit;
	}
	else
	{
		$errormsg = "<p class='errormsg'>Det indtastede brugernavn eller password er forkert.</p>";
	}
}

if(empty($_GET))
{
	echo "<h2>Log ind</h2>";
	echo "<hr/>";
	echo "<div id='loginform'>";
	echo "<form class='standardform' method='post'>";
	echo "Brugernavn: ";
	echo "<br/>";
	echo "<input type='text' name='username'/>";
	echo "<br/>";
	echo "Adgangskode: ";
	echo "<br/>";
	echo "<input type='password' name='password'/>";
	echo "<br/>";
	echo "<input type='checkbox' name='remember' value='yes'>Husk mig*<br/><br/>";
	echo "<input type='submit' name='login' value='Log ind'/>";
	echo "</form>";
	echo "<br/><a href='login.php?passwordreset'>Jeg har glemt mit password/brugernavn</a><br/>";
	echo $errormsg;
	echo "<br/><span class='smalltext italic'>* 'Husk mig'-funktionen gør brug af cookies</span>";
	echo "</div>";
}

if(isset($_POST['reset_password']))
{
	$email = $_POST['email'];

	$user_exists = $forum->check_for_existing_superuser_email($email)->fetch_assoc();
	if($user_exists['res'] > 0)
	{
		$user = $forum->get_superuser_by_email($email)->fetch_assoc();
		$guid = uniqid(NULL, TRUE);

		$forum->submit_reset_request($user['superuser_ID'], $guid);		
		
		require 'functions/PHPMailer/phpmailer.php';
		
		$mail = new PHPMailer;

			//$mail->SMTPDebug = 3;                               // Enable verbose debug output
			$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = 587;                                    // TCP port to connect to
			
			$mail->setFrom('admin@krystallandet.dk', 'Krystallandet');
			$mail->addAddress($email);    						 // Add a recipient
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->CharSet = 'UTF-8';
			
			$mail->Subject = 'Nulstilling af adgangskode på krystallandet.dk';
			$mail->Body    = 'Hej '.$user['name'].'<br/>
			Du modtager denne email, fordi du har anmodet om at nulstille din adgangskode på krystallandet.dk<br/>
			Har du ikke bedt om dette, kan du blot ignorere denne email, og din adgangskode vil forblive den samme.
			<br/><br/>
			
			Du kan nulstille dit kodeord ved at klikke på linket:<br/>
			<a href="http://www.krystallandet.dk/customsystem/login.php?userreset='.$guid.'">http://www.krystallandet.dk/beta/login.php?userreset='.$guid.'</a><br/>
			Herefter kan du logge ind med brugernavnet "'.$user['name'].'" og din valgte adgangskode.
			
			<br/><br/>
			Mvh <br/>
			Krystallandet';
			$mail->AltBody = 'Hej '.$user['name'].'
			Du modtager denne email, fordi du har anmodet om at nulstille din adgangskode på krystallandet.dk
			Har du ikke bedt om dette, kan du blot ignorere denne email, og din adgangskode vil forblive den samme.
			
			Du kan nulstille dit kodeord ved at klikke på linket:<br/>
			http://www.krystallandet.dk/beta/login.php?userreset='.$guid.'
			Herefter kan du logge ind med brugernavnet "'.$user['name'].'" og din valgte adgangskode.
			
			Mvh 
			Krystallandet';
			
			if(!$mail->send()) {
				$errormsg = "<p class='errormsg'>Emailen blev ikke sendt.<br/>
				Mailer Error: " . $mail->ErrorInfo."
				</p>";
			} else {
				$errormsg = "<p>Vi har sendt en besked til din email-adresse!</p>";
			}
	}
	
	else
	{
		$errormsg = "<p class='errormsg'>Den indtastede email-adresse findes ikke i vores system.</p>";
	}
}
	

if(isset($_GET['passwordreset']))
{
	echo "<h2>Nustil adgangskode</h2>";
	echo "<hr/>";
	echo "<div id='loginform'>";
	echo "<form class='standardform' method='post'>";
	echo "E-mail: ";
	echo "<br/>";
	echo "<input type='text' name='email'/>";
	echo "<br/>";
	echo "<input type='submit' name='reset_password' value='Send mine oplysninger'/>";
	echo "</form>";
	echo $errormsg;
	echo "<br/><br/>";
	echo "<span class='smalltext'>Du vil modtage dit brugernavn samt et link til at nulstille dit password på den indtastede email-adresse.<br/>
	Hvis du ikke modtager emailen, eller du ikke kan huske din e-mail-adresse, bedes du kontakte en af krystallandets administratorer - enten via chatboxen eller gennem e-mail: admin@krystallandet.dk</span>";
	
	echo "</div>";
}

if(isset($_GET['userreset']))
{
	$errormsg = "";
	$changed = false;
	
	$resetID = $_GET['userreset'];	
	
	$idexists = $forum->try_get_reset_request($resetID)->fetch_assoc();
	if($idexists['res'] > 0)
	{
		$request = $forum->get_reset_request($resetID)->fetch_assoc();
		$requesttime = $request['datetime'];
		$now = date("Y-m-d H:i:s");
		
		$secs =  strtotime($now) - strtotime($requesttime);// == <seconds between the two times>
		$hours = $secs / 3600;
		
		if($hours > 24)
		{
			echo "<p class='errormsg'>Der er gået mere end 24 timer siden, du forsøgte at nulstille dit password, og dette link virker derfor ikke længere.</p>";
		}
		else
		{
			
			if($_POST['new_password'])
			{
				$pass = htmlspecialchars($_POST['password']);
				$confirm = htmlspecialchars($_POST['password_confirm']);
				if($pass != $confirm || $pass == "" || $confirm == "") {$errormsg = "<p class='errormsg'>Værdierne indtastet i \"ny adgangskode\" og \" bekræft adgangskode\" var ikke ens.</p>";}
				else 
				{
					$hash = password_hash($pass, PASSWORD_DEFAULT);
					$forum->update_superuser_password($hash, $request['fk_superuser_ID']);
					echo "<div id='loginform'>";
					echo "<p>Dit password blev opdateret! Du kan nu logge ind.</p>";
					echo "</div>";	
					$changed = true;
				}
			}
			if($changed == false)
			{
				echo "<h2>Nulstil adgangskode</h2>";
				echo "<hr/>";
				echo "<div id='loginform'>";
				echo "<form class='standardform' method='post'>";
				echo "Ny adgangskode: ";
				echo "<br/>";
				echo "<input type='password' name='password' required/>";
				echo "<br/>";
				echo "Bekræft adgangskode: ";
				echo "<br/>";
				echo "<input type='password' name='password_confirm' required/>";
				echo "<br/>";
				echo "<input type='submit' name='new_password' value='Gem ændringer'/>";
				echo "</form>";
				echo $errormsg;
				echo "</div>";
			}
		}
	}
	else
	{
		echo "<p class='errormsg'>Dette link virker ikke.</p>";
	}
	
}

include('footer.php');
?>