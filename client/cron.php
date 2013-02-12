<?php
chdir(__DIR__);
require_once("config.php");
require_once("aria2.class.php");
define('PUSHOVERAPP','FMZfyI6zJvTfgr8JQLT5xJlvo70mbO');

#If the timestamp file doesn't exist, create it and seed it with the current time
if (!file_exists('timestamp')) {
    $handle = fopen('timestamp', 'w') or die('Cannot create file');
    fwrite($handle, time());
    fclose($handle);
}

#Get the timestamp from file.
$handle = fopen('timestamp', 'r');
$timestamp = fread($handle,filesize('timestamp')); 
fclose($handle);

#Load the filters from file
$handle = fopen(FILTERPATH, "r");
$filters = array();
while (!feof($handle)) {
	$line = str_replace("\n","",fgets($handle));
	
	if(strlen($line) > 0 && $line[0] != '#'){
		$temp = explode("|", $line); //0 = conditions #1 = path
		if(!isset($temp[1])){
			$temp[1]=DEFAULTPATH;
		}
		$temp[0] = strtolower($temp[0]); //Make keywords lower case for matching
		
		//Create #matching regx
		$pattern = "/^";
		$temp2 = explode(" ",$temp[0]);
		foreach ($temp2 as &$keyword) {
			$pattern.="(?=.*$keyword)";
		}
		$pattern.="/s";
		
   		array_push($filters,array('pattern' => $pattern, 'path' => $temp[1]));
	}
}
fclose($handle);


#Now Grab a list of new tiles from the server since the timestamp
$url = JSONFEED."?since=$timestamp";
$curl_handle=curl_init();
curl_setopt($curl_handle,CURLOPT_URL,$url);
curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,3600);
curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
curl_exec($curl_handle);
$buffer = curl_exec($curl_handle);
curl_close($curl_handle);
$result = json_decode($buffer);
	
#Write the new timestamp to the file 
if(isset($result->time)){
	$handle = fopen('timestamp', 'w') or die('Cannot create file');
	fwrite($handle, $result->time);
	fclose($handle);
}

#Initialize Aria2
$aria2 = new aria2(ARIA2PATH);

#Find Matches and start downloads
foreach ($result->files as &$file) {
    #create a lower case version of file for string matching
    $file2 = strtolower($file);
    
    foreach ($filters as &$filter) {
    	
    	if(preg_match($filter['pattern'],$file2)){
    		
    		$aria2->addUri(array($file),array('dir'=>$filter['path']));
    		
    		#Send pushover notification if requested
			if(PUSHOVER == 'yes'){
				#Get just the filename
				$filename = explode("/", $file);
				$filename = str_replace(".mkv","",end($filename));
				
				curl_setopt_array($ch = curl_init(), array(
				CURLOPT_RETURNTRANSFER => true,
  				CURLOPT_URL => "https://api.pushover.net/1/messages.json",
  				CURLOPT_POSTFIELDS => array(
  				"token" => PUSHOVERAPP,
  				"user" => PUSHOVERKEY,
 				"message" => "$filename",
				)));
				curl_exec($ch);
				curl_close($ch);
			}
    		
    		break; //We found a match so let's stop looping
    	} 
    }
    
}
exit();
?>
