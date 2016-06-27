<?php

function parseURls($text)
{
	$reg_exUrl = "/(?<!src=\"|'|href=\"|')(\b[\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/";
	
	if(preg_match($reg_exUrl, $text, $url))
	{
		$text = preg_replace($reg_exUrl, '<a href="'.$url[0].'" target="_blank">'.$url[0].'</a>', $text);
	} 
	
	return $text;
		
}

?>