<?php
	require "../base/common.php";
	
	$raw_post = file_get_contents("php://input");
	$json = json_decode($raw_post);
	
	$params = array("email", "pass");
	required_params($params, $json);
	
	$email = $json->email;
	$pass = $json->pass;
	
	$reader = news_sql::select_reader($email);
	if ($reader == null)
		error_response($Error->NOT_FOUND_EMAIL);
	if ($reader->password != md5($pass))
		error_response($Error->ERROR_PASSWORD);
	
	save_session('login', $email, 60*60*24*30);
	response();
?>