<?php
	require_once "../base/common.php";
	
	$raw_post = file_get_contents("php://input");
	$json = json_decode($raw_post);
	
	$params = array("channel_list");
	required_params($params, $json);
	
	$channel_list = $json->channel_list;
	if (!is_array($channel_list))
		error_response($Error->ERROR_PARAM_TYPE);
	
	$params = array("channel", "start");
	foreach ($channel_list as $item) {
		required_params($params, $item);
		if (!is_int($item->start) || !is_int($item->channel) || $item->start < -1)
			error_response($Error->ERROR_PARAM_TYPE);
		if ($item->start == -1)
			$item->start = 0x7fffffff;
	}
	
	$email = load_session('login', false);  // check if login
	if ($email == null)
		error_response($Error->NEED_LOGIN);
	
	$reader = news_sql::select_reader($email);  // get reader by email
	if ($reader == null)
		error_response($Error->FAILED_GET_READER);
	
	$favorite_list = news_sql::select_favorite_list($reader->id);
	$total_click = 0;
	$total_need = count($favorite_list) * 10;
	foreach ($favorite_list as $item) {
		if ($item->times < 0)
			$item->times = -$item->times;
		$total_click += $item->times;
	}
	
	$news_list = Array();
	$info_list = Array();
	foreach ($favorite_list as $item) {
		$start = 0x7fffffff;
		foreach ($channel_list as $channel_item)
			if ($item->channel == $channel_item->channel) {
				$start = $channel_item->start;
				break;
			}
		$count = intval($item->times * $total_need / $total_click);
		$channel_news_list = news_sql::select_news_list($item->channel, $start, $count);
		$news_count = count($channel_news_list);
		$news_list = array_merge($news_list, $channel_news_list);
		$channel_info = Array();
		$channel_info['channel'] = $item->channel;
		$channel_info['is_over'] = $news_count < $count;
		if ($news_count == 0)
			$channel_info['news_start'] = 0;
		else
			$channel_info['news_start'] = $channel_news_list[$news_count-1]->id;
		array_push($info_list, $channel_info);
	}
	
	response(0, "ok", Array("news_list" => $news_list, "info" => $info_list));
?>