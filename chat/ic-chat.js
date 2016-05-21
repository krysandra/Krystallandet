/* 
Created by: Kenrick Beckett

Name: Chat Engine
*/

var instanse = false;
var state;
var mes;
var file;

function Chat () {
    this.update = updateChat;
    this.send = sendChat;
	this.getState = getStateOfChat;
}

//gets the state of the chat
function getStateOfChat(){
	if(!instanse){
		 instanse = true;
		 $.ajax({
			   type: "POST",
			   url: "ic-process.php",
			   data: {  
			   			'function': 'getState',
						'file': file
						},
			   dataType: "json",
			
			   success: function(data){
				   state = data.state;
				   instanse = false;
			   },
			});
	}	 
}

//Updates the chat
function updateChat(){
	 if(!instanse){
		 instanse = true;
	     $.ajax({
			   type: "POST",
			   url: "ic-process.php",
			   data: {  
			   			'function': 'update',
						'state': state,
						'file': file
						},
			   dataType: "json",
			   success: function(data){
				   if(data.text)
				   {
						for (var i = 0; i < data.text.length; i++) {
							var username; 
							username = "<a href='../characterprofile.php?id="+data.charids[i]+"' style='color:"+data.colors[i]+"; font-weight:bold;' target='blank'>"+data.usernames[i] + "</a>"; 
                            $('#chat-area').prepend($("<tr><td class='chatmsg'> <div class='datetxt'>"+data.datetimes[i]+"</div> "+username+": " + data.text[i] +"</td></tr>"));		
                        }	
					
						while( $('#chat-area tr').length >= 20)
						{
							$('#chat-area tr:last').remove(); 
						}							  
				   }
				   //document.getElementById('chat-area').scrollTop = document.getElementById('chat-area').scrollHeight;
				   state = data.state;
				   instanse = false;
				   
			   },
			});
	 }
	 else {
		 setTimeout(updateChat, 1500);
	 }
}

//send the message
function sendChat(message, userid)
{       
    updateChat();
     $.ajax({
		   type: "POST",
		   url: "ic-process.php",
		   data: {  
		   			'function': 'send',
					'message': message,
					'userid' : userid,
					'file': file
				 },
		   dataType: "json",
		   success: function(data){
			   updateChat();
		   },
		});
}
