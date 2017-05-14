<?php
	session_start();
	
	// 数据库IP, 用户名, 密码, 数据库名
	$GLOBALS["mysqli"] = new mysqli("localhost", "web_news", "web_news", "web_news");
	$mysqli = $GLOBALS["mysqli"];
	if (mysqli_connect_errno($mysqli)) {
		die("Connect Error: " . mysqli_connect_error());
	}
	$mysqli->query("SET CHARACTER SET utf8");
	$mysqli->query("SET NAMES UTF8");
?>