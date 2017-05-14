<?php
	require_once "../base/common.php";
	
	$raw_post = file_get_contents("php://input");
	$json = json_decode($raw_post);
	
	$params = array("channel");
	required_params($params, $json);
	
	$channel = $json->channel;
	
	$email = load_session('login', false);  // check if login
	if ($email == null)
		error_response($Error->NEED_LOGIN);
	
	$reader = news_sql::select_reader($email);  // get reader by email
	if ($reader == null)
		error_response($Error->FAILED_GET_READER);
	
	$favorite = news_sql::select_favorite($reader->id, $channel);
	if ($favorite == null)
		error_response($Error->FAILED_GET_FAVORITE);
	
	if ($favorite->times > 0)
		news_sql::update_favorite($reader->id, $channel, $favorite->times+1);
	else
		news_sql::update_favorite($reader->id, $channel, $favorite->times-1);
	response();
?>