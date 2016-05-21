<?php
include('../dbfunctions.php');
$forum = new dbFunctions();
?>

<?php

    $function = $_POST['function'];
    
    $log = array();
    
    switch($function) {
    
    	 case('getState'):
        	 
			 $log['state'] = strtotime('2016-01-01 00:00:00');
			 break;	
    	
    	 case('update'):
        	$state = $_POST['state'];
			$lastmessage = $forum->get_time_of_last_ic_chat_message()->fetch_assoc();
			$count = strtotime($lastmessage['datetime']);
			 
			 if($count > $state)
			 {
				 $text= array();
				 $usernames= array();
				 $datetimes = array();
				 $colors = array();
				 $charids = array();
				 $log['state'] = $count;
				 $chatdata = $forum->get_ic_chat_messages();
	 
					 while($chatmsg = $chatdata->fetch_assoc())
					 {
						 if(strtotime($chatmsg['datetime']) > $state)
						 {
							 $character = $forum->get_character($chatmsg['fk_character_ID'])->fetch_assoc();
							 
							 $text[] = $chatmsg['message']; 
							 $usernames[] = $character['name'];
							 $datetimes[] = date("j. M y, H:i", strtotime($chatmsg['datetime']));
							 $colors[] = $character['color'];  
							 $charids[] = $character['character_ID'];  
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
					 $log['datetimes'] = $datetimes;
					 $log['colors'] = $colors;
					 $log['charids'] = $charids;
        		 }
				else
				{
					$log['state'] = $state;
        		 	$log['text'] = false;
				}
			 
             break;
    	 
    	 case('send'):

			 $userid = htmlentities(strip_tags($_POST['userid']));
			 
			$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
			 $reg_exItalic = "#\[i\](.+)\[\/i\]#iUs";
			 $reg_exBold = "#\[b\](.+)\[\/b\]#iUs";
			 $reg_exUnderline = "#\[u\](.+)\[\/u\]#iUs";
			 $reg_exBig = "#\[big\](.+)\[\/big\]#iUs";
			 $reg_exSmall = "#\[small\](.+)\[\/small\]#iUs";
			 $reg_exColor = "/\[color\=?(.*?)?\](.*?)\[\/color\]/ms";
			 
			  $message = htmlentities(strip_tags($_POST['message']));
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
				
        			$forum->insert_ic_chat_message($userid, $message);
		 	  }
        	 break;
    	
    }
    
    echo json_encode($log);

?>