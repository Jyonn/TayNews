<?php
	require "../base/common.php";
	
	$raw_post = file_get_contents("php://input");
	$json = json_decode($raw_post);
	
	$params = array("email");
	required_params($params, $json);
	$email = $json->email;
	
	$email_pattern = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
	if (!preg_match($email_pattern, $email))
		error_response($Error->NOT_EMAIL);
	
	$chars = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	$code = "";
	for ($i = 0; $i < 4; $i++) {
		$code .= $chars[rand(0, strlen($chars)-1)];
	}
	save_session('email', $json->email);
	save_session('email_code', $code);
	
	$url = 'http://news.timlorie.me/api/send_email_captcha.php?user=Timlorie&pass=QCloudTimlorieAt17May5TH&to=' . $email . '&code=' . $code;
	$result = file_get_contents($url);
	if ($result == 0)
		response();
	else
		error_response($Error->FAILED_SEND_EMAIL);
?>