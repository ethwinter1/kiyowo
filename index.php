<?php
require_once('core.php');
require_once 'env.php';
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
$content = file_get_contents("php://input");
$update = json_decode($content , true);

$login_data = login(URL);

submit_score($login_data->access_token,$login_data->player->energy);
foreach ($login_data->player->boost as $boost){
	if($boost->type =="energy" && $boost->cnt > 0){
		$response = use_full_tank_and_turbo($login_data->access_token,"energy");
		$response && submit_score($login_data->access_token,$response->player->energy);
	}elseif ($boost->type =="turbo" && $boost->cnt > 0){
		$response = use_full_tank_and_turbo($login_data->access_token,"turbo");
		$response && submit_score($login_data->access_token,$login_data->player->energy);
	}
}

function login($user_url){


	$api_url = 'https://api.tapswap.ai/api/account/login';
	$postData = array(
		'init_data' => $user_url,
		'referrer' => '',
		'bot_key' => "app_bot_0"
	);

	$jsonData = json_encode($postData);
	$ch = curl_init($api_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Host: api.tapswap.ai',
		'User-Agent: '.userAgent(),
		'Accept: */*',
		'Accept-Language: en,en-US;q=0.5',
		'Accept-Encoding: gzip, deflate, br',
		'Referer: https://app.tapswap.club/',
		'X-App: tapswap_server',
		'X-Cv: 606',
		'Origin: https://app.tapswap.club',
		'Sec-Fetch-Dest: empty',
		'Sec-Fetch-Mode: cors',
		'Sec-Fetch-Site: cross-site',
		'Priority: u=4',
		'Pragma: no-cache',
		'Cache-Control: no-cache',
		'Te: trailers',
		'Connection: close'
	));
//	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//	curl_setopt($ch, CURLOPT_CAINFO, 'cacert.pem');

	$response = curl_exec($ch);

// Check for cURL errors
	if ($response === false) {
		MessageRequestJson("sendMessage", array('chat_id'=>CHAT_ID,'text'=>"problem in log ❌"));
		echo 'cURL Error: ' . curl_error($ch);
	}
	else {
		// Print the response

		return json_decode($response);
	}

// Close cURL session
	curl_close($ch);


}

function submit_score($access_token, $score){
	$url = 'https://api.tapswap.ai/api/player/submit_taps';

	$postData = array(
		'taps' => $score,
		'time' => time()
	);

	$jsonData = json_encode($postData);

	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Host: api.tapswap.ai',
		'User-Agent: '.userAgent(),
		'Accept: */*',
		'Accept-Language: en,en-US;q=0.5',
		'Accept-Encoding: gzip, deflate, br',
		'Referer: https://app.tapswap.club/',
		'Content-Type: application/json',
		'Authorization: Bearer '.$access_token,
		'X-App: tapswap_server',
		'X-Cv: 606',
		'Content-Id: 116761',
		'Origin: https://app.tapswap.club',
		'Sec-Fetch-Dest: empty',
		'Sec-Fetch-Mode: cors',
		'Sec-Fetch-Site: cross-site',
		'Priority: u=4',
		'Pragma: no-cache',
		'Cache-Control: no-cache',
		'Te: trailers',
		'Connection: close'
	));
//	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//	curl_setopt($ch, CURLOPT_CAINFO, 'cacert.pem');

	$response = curl_exec($ch);


	// Check for cURL errors
	if ($response === false) {
		MessageRequestJson("sendMessage", array('chat_id'=>CHAT_ID,'text'=>"problem in submit score ❌"));
		echo 'cURL Error: ' . curl_error($ch);
	} else {
		// Print the response
		ui_text(json_decode($response),$score);
		echo 'Response: ' . $response;
	}

	curl_close($ch);

}

function use_full_tank_and_turbo($access_token,$type){

	$url = "https://api.tapswap.ai/api/player/apply_boost";

	$data = array("type" => $type);

	$json_data = json_encode($data);

	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/json",
		"Authorization: Bearer ".$access_token,
		"X-App: tapswap_server",
		"X-Cv: 606",
		"Origin: https://app.tapswap.club",
		"User-Agent: ".userAgent(),
		"Accept: */*",
		"Accept-Language: en,en-US;q=0.5",
		"Accept-Encoding: gzip, deflate, br",
		"Referer: https://app.tapswap.club/",
		"Sec-Fetch-Dest: empty",
		"Sec-Fetch-Mode: cors",
		"Sec-Fetch-Site: cross-site",
		"Priority: u=1",
		"Pragma: no-cache",
		"Cache-Control: no-cache"
	));

	$response = curl_exec($ch);

	curl_close($ch);

		$icon = $type == "energy" ? "🔋":"🚀" ;
	if ($response === false) {
		$text = "$icon $type boost failed ❌";
		MessageRequestJson("sendMessage", array('chat_id'=>CHAT_ID,'text'=>$text));
		$error = curl_error($ch);
		echo "cURL Error: $error";
		return  false;
	}
	else {
		$text = "$icon $type boost 🔥";
		MessageRequestJson("sendMessage", array('chat_id'=>CHAT_ID,'text'=>$text));
		echo "Response: $response";
		return json_decode($response);
	}

}

function userAgent(){
	return 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Mobile/15E148 Safari/604.1';
}

function ui_text($response,$submitted_score){
	$data = $response->player;
	$text = "🎁 submitted score: $submitted_score"."\n"."💰 coins: $data->shares"."\n"."♻️ access coin: $data->energy"."\n"."🔮 ligue: $data->ligue"."\n"."🔋 energy boost: ".$data->boost[0]->cnt."\n"."🚀 turbo boost: ".$data->boost[1]->cnt;
	MessageRequestJson("sendMessage", array('chat_id'=>CHAT_ID,'text'=>$text));
}