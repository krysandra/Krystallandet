<?php

echo "
<div id='view_ajax'></div>
<div id='ajaxForm'>
	<input type='text' id='chatInput' /><input type='button' value='Send' id='btnSend' />
</div>";

?>

<?php

  class chatClass
  {
    public static function getRestChatLines($id)
    {
      $arr = array();
      $jsonData = '{"results":[';

      $chatdata = $forum->get_chat_messages();
	  
      $line = new stdClass;
      while ($chatmsg = $chatdata->fetch_assoc()) {
        $line->id = $chatmsg['chat_ID'];
        $line->username = $chatmsg['username'];
        $line->color = $chatmsg['color'];
        $line->chattext = $chatmsg['message'];
        $line->chattime = date('H:i:s', strtotime($chatmsg['datetime']));
        $arr[] = json_encode($line);
      }
      $jsonData .= implode(",", $arr);
      $jsonData .= ']}';
      return $jsonData;
    }
    
  }
?>