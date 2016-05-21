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
			   url: "process.php",
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
			   url: "process.php",
			   data: {  
			   			'function': 'update',
						'state': state,
						'file': file
						},
			   dataType: "json",
			   success: function(data){
				   if(data.text){
						for (var i = 0; i < data.text.length; i++) {
							var username; var img; var title;
							if(data.titles[i] != "") { title = " " + data.titles[i];} else { title = ""; } 
							if(data.links[i] != "") { username = "<span style='color:"+data.colors[i]+";'><a style='color:"+data.colors[i]+";' target='blank' href='"+data.links[i]+"'>"+ data.usernames[i] + 
							title + "</a></span>"; }
							else { username = "<span style='color:"+data.colors[i]+";'>"+data.usernames[i] + title + "</span>"; }
							if(data.avatars[i] != "") { img = "<td class='chatmsg' style='min-height: 45px;'><img class='chatavatar' src='"+data.avatars[i]+"' width='45px' height='45px' />"; } 
							else { img = "<td class='chatmsg'>"; }
							
                            $('#chat-area').prepend($("<tr>"+img+" <div class='datetxt'>"+data.datetimes[i]+"</div> "+username+": " + data.text[i] +"</td></tr>"));		
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
function sendChat(message, title, chatimg, chatlink, chatcolor, nickname, userid)
{       
    updateChat();
     $.ajax({
		   type: "POST",
		   url: "process.php",
		   data: {  
		   			'function': 'send',
					'message': message,
					'nickname': nickname,
					'title' : title,
					'chatimg' : chatimg,
					'chatlink' : chatlink,
					'chatcolor' : chatcolor,
					'userid' : userid,
					'file': file
				 },
		   dataType: "json",
		   success: function(data){
			   updateChat();
		   },
		});
}
