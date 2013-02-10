<?php
$webpath ="http://yoururl/folder"; #Web path to root directory
$directory = "/path/to/your/folder"; #Local Path to root directory to monitor
$filetypes = "mkv"; #seperate with comma
$files = array();
$return = array();

if(isset($_GET['since'])){
	$since = $_GET['since'];
}else{
	$since = 0;
}

foreach(glob($directory.'*/*.{'.$filetypes.'}', GLOB_BRACE) as $video){  
	if(filemtime($video) > $since){    
		$files[] = str_replace($directory, $webpath, $video);
	}
}  

$return['files'] = $files;
$return['time'] = time();

header('Content-Type: application/json');
echo json_encode($return);
?>