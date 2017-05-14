<?php
	require_once "../base/common.php";
	
	$raw_post = file_get_contents("php://input");
	$json = json_decode($raw_post);
	
	$params = array("keyword");
	required_params($params, $json);
	
	$keyword = $json->keyword;
	
	$news_list = news_sql::search_news($keyword);
	response(0, "ok", $news_list);
?>