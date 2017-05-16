<?php
	require_once "../base/common.php";
/*
	function http_post_request($url, $data = null)
	{
	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	    if (!empty($data)){
	        curl_setopt($curl, CURLOPT_POST, 1);
	        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	    }
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	    $output = curl_exec($curl);
	    curl_close($curl);
	    return $output;
	}
*/

	function http_post_request($url, $post_data) {
		$postdata = http_build_query($post_data);
		$options = array(
			'http' => array(
				'method' => 'POST',
				'header' => 'Content-type:application/x-www-form-urlencoded',
				'content' => $postdata,
				'timeout' => 15 * 60 // 超时时间（单位:s）
			)
		);
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		return $result;
	}

	$url = 'https://qn.jzdxq.cn/work/detail/';
	$json = Array('wid' => rand(87, 106));
	$result = json_decode(http_post_request($url, $json));
	
	response(0, "ok", $result->body);
?>