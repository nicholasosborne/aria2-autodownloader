<?php
#Path to JSON FEED
define('JSONFEED'	, 	'http://pathto/listfiles.php');

#File to load text filters from
define('FILTERPATH'	,	'filters.txt');

#Default download path if none is specified with filter
define('DEFAULTPATH',	'/defualtdownloadfolder/');

#Location where Aria2 RPC IS Running
define('ARIA2PATH', 	'http://127.0.0.1:6800/jsonrpc');

#SEND PUSHOVER NOTIFICATIONS yes|no
define('PUSHOVER'	, 	'no');

#PUSHOVER DEVICE KEY
define('PUSHOVERKEY',	'pushoverkeyhere');

?>