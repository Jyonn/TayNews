<?php
	require_once "../base/common.php";
	
	$channel_list = news_sql::select_channel_list();
	
	$channel_state = Array();
	$email = load_session('login', false);  // check if login
	if ($email != null) {
		$reader = news_sql::select_reader($email);  // get reader by email
		if ($reader != null) {
			$favorite_list = news_sql::select_favorite_list($reader->id);
			foreach ($favorite_list as $item) {
				$find = null;
				foreach ($channel_list as $channel_item)
					if ($item->channel == $channel_item->id)
						$find = $channel_item;
				if ($find == null)
					break;
				array_push($channel_state, Array("id" => $find->id, "channel" => $find->channel,  "state" => $item->times > 0));
			}
			response(0, "ok", Array("is_login" => true, "channel_state" => $channel_state));
		}
	}
	
	foreach ($channel_list as $item) {
		array_push($channel_state, Array("id" => $item->id, "channel" => $item->channel, "state" => true));
	}
	response(0, "ok", Array("is_login" => false, "channel_state" => $channel_state));
?>