<?php
	session_start();
	
	// ���ݿ�IP, �û���, ����, ���ݿ���
	$GLOBALS["mysqli"] = new mysqli("localhost", "web_news", "web_news", "web_news");
	$mysqli = $GLOBALS["mysqli"];
	if (mysqli_connect_errno($mysqli)) {
		die("Connect Error: " . mysqli_connect_error());
	}
	$mysqli->query("SET CHARACTER SET utf8");
	$mysqli->query("SET NAMES UTF8");
?>