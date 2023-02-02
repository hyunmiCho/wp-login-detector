<?php
// Silence is golden.
ini_set('display_errors', true);
//var_dump(get_post(1));


$path = preg_replace('/wp-content.*$/', '', __DIR__);
require_once $path . 'wp-load.php';

//var_dump( $path . 'wp-load.php'); 

	$ip = '104.42.192.23'; 
	$full = 'true'; 
	$params = '?ip=' . $ip . '&full=' . $true;

	$headers = "x-api-key: SCvzhUcfca3ZQvCGYFYFHvlpdcWlWPlu4hjkSp4AWmFI6pzXYQVZOBD0pi0a\r\n";

	// NOTE: Use the key 'http' even if you are making an HTTPS request. See:
	// https://php.net/manual/en/function.stream-context-create.php
	$options = array (
		'http' => array (
			'header' => $headers,
			'method' => 'GET'
		)
	);
	
	$host = 'https://api.criminalip.io';
	$path = '/core/v1/getipdata';  
	$context  = stream_context_create ($options);
	$result = file_get_contents ($host . $path . $params, false, $context);
	

	$result = json_decode($result); 
	var_dump($result);  
	exit(0); 
	return $result;
	
	
	 