<?php
include('config.php');
if(!isset($_SESSION['user']))
{
	header('Location:index.php');
}
else
{
	unset($_SESSION['user']);
	if(isset($_COOKIE['www_krystallandet_dk']))
	{
		setcookie("www_krystallandet_dk", "", time()-3600);
	}
	header('Location:index.php');
}
?>
