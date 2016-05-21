<?php



/* Simple PHP BBCode Parser function */



//BBCode Parser function



function parseBBcodes($text) {



// BBcode array

$find = array(

'~\[b\](.*?)\[/b\]~s',

'~\[i\](.*?)\[/i\]~s',

'~\[u\](.*?)\[/u\]~s',

'~\[quote\](.*?)\[/quote\]~s',

'~\[center\](.*?)\[/center\]~s',

'~\[left\](.*?)\[/left\]~s',

'~\[right\](.*?)\[/right\]~s',

'~\[justify\](.*?)\[/justify\]~s',

'~\[size=1](.*?)\[/size\]~s',

'~\[size=2](.*?)\[/size\]~s',

'~\[size=3](.*?)\[/size\]~s',

'~\[size=4](.*?)\[/size\]~s',

'~\[size=5](.*?)\[/size\]~s',

'~\[size=6](.*?)\[/size\]~s',

'~\[size=7](.*?)\[/size\]~s',

'~\[color=(.*?)\](.*?)\[/color\]~s',

'~\[url\]((?:ftp|https?)://.*?)\[/url\]~s',

'~\[img\](https?://.*?\.(?:jpg|jpeg|gif|png|bmp))\[/img\]~s',

'~\[ul](.*?)\[/ul\]~s',

'~\[li](.*?)\[/li\]~s',

'~\[code](.*?)\[/code\]~s',

'~\[s](.*?)\[/s\]~s',

'~\[hr\]~s',

);



// HTML tags to replace BBcode

$replace = array(

'<b>$1</b>',

'<i>$1</i>',

'<span style="text-decoration:underline;">$1</span>',

'<pre>$1</'.'pre>',

'<div style="text-align: center;">$1</div>',

'<div style="text-align: left;">$1</div>',

'<div style="text-align: right;">$1</div>',

'<div style="text-align: justify;">$1</div>',

'<span style="font-size:10px;">$1</span>',

'<span style="font-size:11px;">$1</span>',

'<span style="font-size:13px;">$1</span>',

'<span style="font-size:16px;">$1</span>',

'<span style="font-size:18px;">$1</span>',

'<span style="font-size:24px;">$1</span>',

'<span style="font-size:30px;">$1</span>',

'<span style="color:$1;">$2</span>',

'<a href="$1">$1</a>',

'<img src="$1" alt="" />',

'<ul>$1</ul>',

'<li>$1</li>',

'<code>$1</code>',

'<span style="text-decoration: line-through;">$1</span>',

'<hr/>',

);



// Replacing the BBcodes with corresponding HTML tags

return preg_replace($find,$replace,$text);

}



// How to use the above function:

/*

$bbtext = "This is [b]bold[/b] and this is [u]underlined[/u] and this is in [i]italics[/i] with a [color=red] red color[/color]";

$htmltext = parseBBcodes($bbtext);

echo $htmltext;

*/

?>