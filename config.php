<?php
ob_start();
session_start();

$time = microtime(true);
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

require_once('dbfunctions.php');
$forum = new dbFunctions();
//require_once('functions/findsubforums.php');
require_once('functions/JBBCode/Parser.php');
require_once('functions/urlparser.php');

if(!isset($_SESSION['user']) && !isset($_COOKIE['www_krystallandet_dk']))
{
	$user_rank = 0;
	$user_logged_in_ID = 0;
	$user_logged_in = array();
	$topictags = 0;
	$accepted_chars = 0;
	$unread_messages = 0;
}
else
{
	if(isset($_COOKIE['www_krystallandet_dk']))
	{
		$_SESSION['user'] = $_COOKIE['www_krystallandet_dk'];
	}
	$user_logged_in_ID = $_SESSION['user'];
	$user_logged_in = $forum->get_superuser($user_logged_in_ID)->fetch_assoc();
	$user_rank = $user_logged_in['fk_role_ID']; 	
	
	$user_topics = $forum->count_all_tags_from_superuser($user_logged_in_ID)->fetch_assoc();
	$user_accepted_chars = $forum->count_all_accepted_alive_characters_from_superuser($user_logged_in_ID)->fetch_assoc();
	$user_unread_messages = $forum->count_unread_messages($user_logged_in_ID)->fetch_assoc();
	$topictags = $user_topics['res'];
	$accepted_chars = $user_accepted_chars['res'];
	$unread_messages = $user_unread_messages['res'];
	
	if($user_rank > 1)
	{
		$characters_need_approval = $forum->count_approval_waitlist()->fetch_assoc();
		$wantedposts_need_approval = $forum->count_nonaccepted_wantedposts()->fetch_assoc();
		$adminapproval = $characters_need_approval['res'] + $wantedposts_need_approval['res'];
	}
	
	$updatelogin = $forum->update_superuser_login($user_logged_in_ID);	
}

setlocale(LC_ALL, 'da_DA');

$postsperpage = 20;
$topicsperpage = 50;
$usersperpage = 50;
$messagesperpage = 50;

$inactivecolor = "#919191";
$deadcolor = "#656565";

$parser = new JBBCode\Parser();

$parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());

$builder = new JBBCode\CodeDefinitionBuilder('size', '<span class="bbsize-{option}">{param}</span>');
$builder->setUseOption(true)->setOptionValidator(new \JBBCode\validators\NumberValidator());
$parser->addCodeDefinition($builder->build());

$builder = new JBBCode\CodeDefinitionBuilder('font', '<span class="font-family:{option}">{param}</span>');
$builder->setUseOption(true)->setOptionValidator(new \JBBCode\validators\TextValidator());
$parser->addCodeDefinition($builder->build());


$builder = new JBBCode\CodeDefinitionBuilder('center', '<div style="text-align:center;">{param}</div>');
$parser->addCodeDefinition($builder->build());

$builder = new JBBCode\CodeDefinitionBuilder('url', '<a href="{option}">{param}</a>');
$builder->setUseOption(true)->setOptionValidator(new \JBBCode\validators\UrlValidator());
$parser->addCodeDefinition($builder->build());

$builder = new JBBCode\CodeDefinitionBuilder('left', '<div style="text-align:left;">{param}</div>');
$parser->addCodeDefinition($builder->build());

$builder = new JBBCode\CodeDefinitionBuilder('right', '<div style="text-align:right;">{param}</div>');
$parser->addCodeDefinition($builder->build());

$builder = new JBBCode\CodeDefinitionBuilder('justify', '<div style="text-align:justify;">{param}</div>');
$parser->addCodeDefinition($builder->build());

$builder = new JBBCode\CodeDefinitionBuilder('s', '<span style="text-decoration:line-through;">{param}</span>');
$parser->addCodeDefinition($builder->build());

$builder = new JBBCode\CodeDefinitionBuilder('ul', '<ul>{param}</ul>');
$parser->addCodeDefinition($builder->build());

$builder = new JBBCode\CodeDefinitionBuilder('li', '<li>{param}</li>');
$parser->addCodeDefinition($builder->build());

$builder = new JBBCode\CodeDefinitionBuilder('youtube', '<iframe src="http://www.youtube.com/embed/{param}" frameborder=\"0\" allowfullscreen></iframe>');
$parser->addCodeDefinition($builder->build());

$builder = new JBBCode\CodeDefinitionBuilder('quote', '<pre>{param}</pre>');
$parser->addCodeDefinition($builder->build());
 
$builder = new JBBCode\CodeDefinitionBuilder('code', '<div class="code">{param}</div>');
$builder->setParseContent(false);
$parser->addCodeDefinition($builder->build());

$pagetitle = "";
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
    	<title>%TITLE% Krystallandet - Online Rollespil</title>
        
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
               
        <link href='https://fonts.googleapis.com/css?family=Raleway:300' rel='stylesheet' type='text/css'>  
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <link rel="stylesheet" href="sce_editor/minified/themes/square.min.css" type="text/css" media="all" />
		<link rel="stylesheet" type="text/css" href="css/mainstyle.css">
        <link rel="stylesheet" type="text/css" href="css/mobilestyle.css">
        
        <script type="text/javascript" src="sce_editor/minified/jquery.sceditor.bbcode.min.js"></script>        
        <script src="sce_editor/languages/da.js"></script>
        <script type="text/javascript" src="functions/slimtable.min.js"></script> 
        <script src="chat/main.js"></script>
     
        <link rel="stylesheet" type="text/css" href="functions/slimtable.css">
        
                <script>
		$(function(){  // $(document).ready shorthand
		  $(".hovereffect").hover(function() {
			$(this).animate({opacity: 0.5}, 250);
			}, function() {
				$(this).animate({opacity: 1.0}, 250);
			});	
			
			$(".charimg").hover(function() {
			$(this).animate({opacity: 0.3}, 250);
			}, function() {
				$(this).animate({opacity: 0.6}, 250);
			});	
			  
		});
		
		</script>
        
        <script>
		
		$(function() {
			if(!/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
    || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4)))
			{
				$(".postarea").sceditor({
					plugins: "bbcode",
					
					toolbar: "bold,italic,underline,strike|left,center,right,justify|size,color|bulletlist,code,quote|image,link,unlink,youtube|maximize,source",
					
					locale: "da",
					
					style: "sce_editor/minified/jquery.sceditor.default.min.css",
					
					emoticonsEnabled: false,
				});
				
				//"are you sure"-dialog
				$('textarea[name="posttext"]').sceditor();
				$('textarea[name="posttext"]').sceditor('instance').keyDown(function(e) 
				{
					warn_on_leave = true;
				});
				// let through when submitting
				$('input:submit').click( function() 
				{
					warn_on_leave = false;
					return true;
				});
				// show popup when leaving
				$(window).bind('beforeunload', function() 
				{
					if(warn_on_leave) 
					{
						return "Du har ugemte ændringer. Hvis du navigerer væk fra siden nu, vil de gå tabt.";
					}
				});
			}
		});
		</script>
        
        
         <script>
		  function countChar(val) {
			var len = val.value.length;
			if (len >= 500) {
			  val.value = val.value.substring(0, 500);
			} else {
			  $('#charNum').text(500 - len);
			}
		  };
		</script>
			
            
			
    </head>