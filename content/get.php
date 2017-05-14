<?php
	require "../base/common.php";
	
	$raw_post = file_get_contents("php://input");
	$json = json_decode($raw_post);
	
	$params = array("count", "channel_list");
	required_params($params, $json);
	
	$count = $json->count;
	$channel_list = $json->channel_list;
	if (!is_int($count) || $count < 1)
		error_response($Error->ERROR_PARAM_TYPE);
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
	if ($email != null) {
		$reader = news_sql::select_reader($email);  // get reader by email
		if ($reader != null) {
			$favorite_list = news_sql::select_favorite_list($reader->id);
			foreach ($favorite_list as $item) {
				if ($item->times > 0)
					news_sql::update_favorite($reader->id, $item->channel, -$item->times);
			}
			foreach ($channel_list as $item) {
				$favorite = news_sql::select_favorite($reader->id, $item->channel);
				if ($favorite->times < 0)
					news_sql::update_favorite($reader->id, $item->channel, -$favorite->times);
			}
		}
	}
	
	$news_list = Array();
	$info_list = Array();
	foreach ($channel_list as $item) {
		$channel_news_list = news_sql::select_news_list($item->channel, $item->start, $count);
		$news_count = count($channel_news_list);
		$news_list = array_merge($news_list, $channel_news_list);
		//$news_list += $channel_news_list;
		$channel_info = Array();
		$channel_info['channel'] = $item->channel;
		$channel_info['is_over'] = $news_count < $count;
		if ($news_count == 0)
			$channel_info['news_start'] = 0;
		else
			$channel_info['news_start'] = $channel_news_list[$news_count-1]->id;
		array_push($info_list, $channel_info);
	}
	
	shuffle($news_list);
	
	response(0, "ok", Array("news_list" => $news_list, "info" => $info_list));
?>