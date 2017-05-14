<?php
	require "../base/common.php";
	
	$raw_post = file_get_contents("php://input");
	$json = json_decode($raw_post);
	
	$params = array("code", "pass");
	required_params($params, $json);
	
	$email_code = load_session('email_code');
	$email = load_session('email');
	if ($email_code == null || $email == null || $json->code != $email_code)
		error_response($Error->NEED_EMAIL_SEND);
	
	$password = $json->pass;
	$ret = news_sql::select_reader($email);
	if ($ret == null) {
		$ret = news_sql::insert_reader($email, $password);
		if ($ret) {
			
			$reader = news_sql::select_reader($email);
			save_session('login', $email, 60*60*24*30);
			
			$channel_list = news_sql::select_channel_list();
			foreach ($channel_list as $channel) {
				news_sql::insert_favorite($reader->id, $channel->id, 1);
			}
			
			response();
		}
		error_response($Error->FAILED_INSERT_READER);
	}
	error_response($Error->EXIST_EMAIL);
?>