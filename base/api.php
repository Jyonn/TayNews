<?php
	require_once "common.php";
	
	function grab_channel() {
		$url = 'https://api.jisuapi.com/news/channel?appkey=f19ded98c0f37a29';
		$result = file_get_contents($url);
		$json = json_decode($result);
		foreach ($json->result as $channel) {
			$ret = news_sql::select_channel($channel);
			if ($ret == null) {
				news_sql::insert_channel($channel);
			}
		}
	}
	
	function grab_news() {
		$channel_list = news_sql::select_channel_list();
		foreach ($channel_list as $Channel) {
			$url = 'https://api.jisuapi.com/news/get?channel=' . $Channel->channel . '&start=0&num=40&appkey=f19ded98c0f37a29';
			$result = file_get_contents($url);
			$json = json_decode($result);
			foreach ($json->result->list as $news_item) {
				$ret = news_sql::select_news($news_item->title);
				if ($ret == null) {
					news_sql::insert_news($news_item->title, $news_item->time, $news_item->pic, $news_item->url, $Channel->id);
				}
			}
		}
	}
	
	$action = $_GET['action'];
	switch ($action) {
		case 'get-channel': grab_channel(); break;
		case 'get-news': grab_news(); break;
		default: break;
	}
	
	response();
?>