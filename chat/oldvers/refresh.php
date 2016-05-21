<?php

$id = intval( $_GET[ 'lastTimeID' ] );
$jsonData = chatClass::getRestChatLines( $id );
print $jsonData;

?>