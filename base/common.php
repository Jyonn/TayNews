<?php
	require "sql.php";
	
	/*RESPONSE*/
	function response($code="0", $msg="ok", $body=null) {
		exit(json_encode(array("code" => $code, "msg" => $msg, "body" => $body), JSON_UNESCAPED_UNICODE));
	}
	
	/*DEAL WITH ERROR*/
	class Error {
		var $OK = 0;
		var $NOT_FOUND_ERROR_ID = 1;
		var $NEED_PARAMS = 2;
		var $ERROR_PARAM_TYPE = 3;
		var $NEED_LOGIN = 4;
		
		var $NEED_EMAIL_SEND = 1001;
		var $EXIST_EMAIL = 1002;
		var $FAILED_INSERT_READER = 1003;
		var $FAILED_SEND_EMAIL = 1004;
		var $NOT_EMAIL = 1005;
		var $NOT_FOUND_EMAIL = 1006;
		var $ERROR_PASSWORD = 1007;
		var $FAILED_GET_READER = 1008;
		var $FAILED_GET_FAVORITE = 1009;
	}
	$Error = new Error();
	$GLOBALS['error'] = $Error;
	
	$GLOBALS['error_table'] = array(
		array( $Error->OK, "ok" ),
		array( $Error->NOT_FOUND_ERROR_ID, "不存在的错误"),
		array( $Error->NEED_PARAMS, "缺少参数"),
		array( $Error->ERROR_PARAM_TYPE, "参数类型错误"),
		array( $Error->NEED_LOGIN, "需要登陆"),
		
		array( $Error->NEED_EMAIL_SEND, "错误的邮箱验证码" ),
		array( $Error->EXIST_EMAIL, "已存在的邮箱" ),
		array( $Error->FAILED_INSERT_READER, "新增用户失败" ),
		array( $Error->FAILED_SEND_EMAIL, "验证码发送失败"),
		array( $Error->NOT_EMAIL, "不是正确的邮箱账号"),
		array( $Error->NOT_FOUND_EMAIL, "不存在的邮箱"),
		array( $Error->ERROR_PASSWORD, "错误的邮箱或密码"),
		array( $Error->FAILED_GET_READER, "获取用户失败"),
		array( $Error->FAILED_GET_FAVORITE, "获取喜爱度失败"),
	);
	
	function error_response($error_id) {
		$error_table = $GLOBALS['error_table'];
		foreach ($error_table as $item) {
			if ($error_id == $item[0])
				response($error_id, $item[1]);
		}
		error_response($Error->NOT_FOUND_ERROR_ID);
	}
	
	/*SESSION*/
	function save_session($key, $value, $fail_time = 300) {
		$_SESSION['saved_' . $key . '_raw'] = $value;
		$_SESSION['saved_' . $key . '_time'] = time();
		$_SESSION['saved_' . $key . '_fail'] = $fail_time;
	}
	function load_session($key, $use_once = true) {
		$raw_value = 'saved_' . $key . '_raw';
		$create_time = 'saved_' . $key . '_time';
		$fail_time = 'saved_' . $key . '_fail';
		if (array_key_exists($raw_value, $_SESSION) && 
			array_key_exists($create_time, $_SESSION) && 
			array_key_exists($fail_time, $_SESSION)) {

			if ($_SESSION[$create_time]+$_SESSION[$fail_time] >= time()) {
				$value = $_SESSION[$raw_value];
				if ($use_once) {
					unset($_SESSION[$raw_value]);
					unset($_SESSION[$create_time]);
					unset($_SESSION[$fail_time]);
				}
				return $value;
			}
			return null;
		}
		return null;
	}
	
	/*REQUEST*/
	function required_params($params, $obj) {
		foreach ($params as $key => $param) {
			if (!isset($obj->{$param})) {
				error_response($GLOBALS['error']->NEED_PARAMS);
			}
		}
	}
?>