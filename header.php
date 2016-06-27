<?php

session_start();

include('config.php');

?>



	<body>



    <div id="contentwrap">

    <a style="display:block" href="index.php">

    <div id="forumheader"></div>

    </a>



    <div id="menu">



    <?php include('menu.php'); ?>



    </div>

    

    <div id="usermenu">
    <img class='scrollbutton' src='images/down.png' id='topofpage' onclick='window.scrollTo(0,document.body.scrollHeight);' style='cursor:pointer;' title='Til bunden'/>



    <?php include('usermenu.php'); ?>



    </div>




    <div id="content">