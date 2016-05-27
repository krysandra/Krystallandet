<?php
include('../dbfunctions.php');
$forum = new dbFunctions();
?>

<?php

    $function = $_POST['function'];
    
    $log = array();
    
    switch($function) {
    
    	 case('getState'):
		 	 
		 	 /*
        	 if(file_exists('chat.txt')){
               $lines = file('chat.txt');
        	 }
			 */
             //$log['state'] = count($lines); 
        	 
			 $log['state'] = strtotime('2016-01-01 00:00:00');
			 break;	
    	
    	 case('update'):
        	$state = $_POST['state'];
			
			//$rows = $forum->count_chat_messages()->fetch_assoc();
			//$count = $rows['res'];
			
			$lastmessage = $forum->get_time_of_last_chat_message()->fetch_assoc();
			$count = strtotime($lastmessage['datetime']);
			/*
        	if(file_exists('chat.txt')){
        	   $lines = file('chat.txt');
        	 }
			 */
        	 //$count = count($lines);
			 
			 if($count > $state)
			 {
				 $text= array();
				 $usernames= array();
				 $links= array();
				 $titles= array();
				 $avatars= array();
				 $datetimes = array();
				 $colors = array();
				 $log['state'] = $count;
				 $chatdata = $forum->get_chat_messages();
	 
					 
					 while($chatmsg = $chatdata->fetch_assoc())
					 {
						 if(strtotime($chatmsg['datetime']) > $state)
						 {
							 $text[] = $chatmsg['message']; 
							 $usernames[] = $chatmsg['username'];
							 $links[] = $chatmsg['link']; 
							 $titles[] = $chatmsg['title']; 
							 $avatars[] = $chatmsg['avatar'];
							 $datetimes[] = date("j. M y, H:i", strtotime($chatmsg['datetime']));
							 $colors[] = $chatmsg['color'];  
						 }
					 }
					 
					 /*
        			 foreach ($lines as $line_num => $line)
                       {
        				   if($line_num >= $state){
                         $text[] =  $line = str_replace("\n", "", $line);
        				   }
         
                        }
					 */
        			 $log['text'] = $text; 
					 $log['usernames'] = $usernames;
					 $log['titles'] = $titles;
					 $log['links'] = $links;
					 $log['avatars'] = $avatars;
					 $log['datetimes'] = $datetimes;
					 $log['colors'] = $colors;
        		 }
				else
				{
					$log['state'] = $state;
        		 	$log['text'] = false;
				}
			 
             break;
    	 
    	 case('send'):
		  	 $nickname = htmlentities(strip_tags($_POST['nickname']));
			 $title = htmlentities(strip_tags($_POST['title']));
			 $chatimg = htmlentities(strip_tags($_POST['chatimg']));
			 $chatlink = htmlentities(strip_tags($_POST['chatlink']));
			 $chatcolor = htmlentities(strip_tags($_POST['chatcolor']));
			 $userid = htmlentities(strip_tags($_POST['userid']));
			 
			 $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
			 $reg_exItalic = "#\[i\](.+)\[\/i\]#iUs";
			 $reg_exBold = "#\[b\](.+)\[\/b\]#iUs";
			 $reg_exUnderline = "#\[u\](.+)\[\/u\]#iUs";
			 $reg_exBig = "#\[big\](.+)\[\/big\]#iUs";
			 $reg_exSmall = "#\[small\](.+)\[\/small\]#iUs";
			 $reg_exColor = "/\[color\=?(.*?)?\](.*?)\[\/color\]/ms";
			 $reg_exEyebrow = "/:naughty:/";
			 $reg_exHappy = "/:\)/";
			 $reg_exHappy2 = "/:D/";
			 $reg_exTongue = "/:P/";
			 $reg_exLove = "/:love:/";  
			 $reg_exSurprise = "/:O/"; 
			 $reg_exWink = "/;\)/"; 
			 $reg_exSad = "/:\(/"; 
			 $reg_exCry = "/:cry:/"; 
			 $reg_exHeart = "/\<3/"; 
			 
			  	$message = htmlentities($_POST['message']);
		 	   if(($message) != "\n"){
        	
			    if(preg_match($reg_exUrl, $message, $url))
				{
       			$message = preg_replace($reg_exUrl, '<a href="'.$url[0].'" target="_blank">'.'[link]'.'</a>', $message);
				} 
				if(preg_match($reg_exItalic, $message, $data))
				{
       			$message = preg_replace($reg_exItalic, '<i>'.$data[1].'</i>', $message);
				} 
				if(preg_match($reg_exUnderline, $message, $data))
				{
       			$message = preg_replace($reg_exUnderline, '<u>'.$data[1].'</u>', $message);
				} 
				if(preg_match($reg_exBold, $message, $data))
				{
       			$message = preg_replace($reg_exBold, '<b>'.$data[1].'</b>', $message);
				}
				if(preg_match($reg_exBig, $message, $data))
				{
       			$message = preg_replace($reg_exBig, '<span style="font-size:150%;">'.$data[1].'</span>', $message);
				} 
				if(preg_match($reg_exSmall, $message, $data))
				{
       			$message = preg_replace($reg_exSmall, '<span style="font-size:70%; font-weight: normal;">'.$data[1].'</span>', $message);
				} 
				if(preg_match($reg_exColor, $message, $data))
				{
       			$message = preg_replace($reg_exColor, '<span style="color:'.$data[1].'">'.$data[2].'</span>', $message);
				} 
				 
				if(preg_match($reg_exEyebrow, $message, $smiley))
				{
       			$message = preg_replace($reg_exEyebrow, ' <img src="images/eyebrows.gif"/> ', $message);
				} 
				if(preg_match($reg_exHappy, $message, $smiley))
				{
       			$message = preg_replace($reg_exHappy, ' <img src="images/happy.gif"/> ', $message);
				} 
				if(preg_match($reg_exHappy2, $message, $smiley))
				{
       			$message = preg_replace($reg_exHappy2, ' <img src="images/happy2.gif"/> ', $message);
				} 
			 	if(preg_match($reg_exTongue, $message, $smiley))
				{
       			$message = preg_replace($reg_exTongue, ' <img src="images/tongue.gif"/> ', $message);
				} 
				if(preg_match($reg_exLove, $message, $smiley))
				{
       			$message = preg_replace($reg_exLove, ' <img src="images/love.gif"/> ', $message);
				} 
				if(preg_match($reg_exSurprise, $message, $smiley))
				{
       			$message = preg_replace($reg_exSurprise, ' <img src="images/surprise.gif"/> ', $message);
				} 
				if(preg_match($reg_exWink, $message, $smiley))
				{
       			$message = preg_replace($reg_exWink, ' <img src="images/wink.gif"/> ', $message);
				} 
				if(preg_match($reg_exSad, $message, $smiley))
				{
       			$message = preg_replace($reg_exSad, ' <img src="images/sad.gif"/> ', $message);
				} 
				if(preg_match($reg_exCry, $message, $smiley))
				{
       			$message = preg_replace($reg_exCry, ' <img src="images/cry.gif"/> ', $message);
				} 
        			$forum->insert_chat_message($nickname, $title, $chatimg, $message, $chatlink, $chatcolor);
					if($userid != 0 && $userid != "")
					{
						$forum->update_superuser_chatimg($chatimg, $userid);
						$forum->update_superuser_chattitle($title, $userid);
						$forum->update_superuser_chatlink($chatlink, $userid);
					}
        	 		//fwrite(fopen('chat.txt', 'a'), "<span>". $nickname . "</span>" . $message = str_replace("\n", " ", $message) . "\n"); 
			 
		 	  }
        	 break;
    	
    }
    
    echo json_encode($log);

?>