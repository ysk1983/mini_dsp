<?php

// BitRequest reception
$postdata = '';
$stdin = fopen('php://input', 'r');
while (($d = fgets($stdin)) !== false) {
    $postdata .= $d;
}
$bid_request = json_decode($postdata, true);

// 広告取得
$ads = json_decode(file_get_contents('./adds.json'), true);

// bidrequestのブロック対象を除外
foreach($ads as $ad_key => $ad_body) {
    foreach($ad_body['blocked_app_ids'] as $block_id){
        if ($bid_request['app_id'] === $block_id) {
            unset($ads[$ad_key]);
        }
    }
}

// mlへのリクエスト
$ml_request_url = 'http://localhost:8079/ml/predict';

$ml_request_ad_ids = array();
foreach($ads as $ad) {
    $ml_request_ad_ids[] = $ad['id'];
}

$white_ads = array_values($ml_request_ad_ids);
var_dump($white_ads);
$ml_request_body = [];
$ml_request_body += ['ads' => $white_ads];
$ml_request_body += ['user_id' => $bid_request['user_id']];
//$ml_request_body['ads'] = implode(',', $ml_request_ad_ids);
//$ml_request_body['user_id'] = $bid_request['user_id'];

$ml_request_json = json_encode($ml_request_body);
//$ml_request_json = http_build_query($ml_request_body);

$options = array (
    'http' => array (
        'method' => 'POST',
        'header'=> "Content-type: application/json\r\n" . "Content-Length: " . strlen($ml_request_json) . "\r\n",
        'content' => $ml_request_json
    )
);
//fprintf($fp, $ml_request_json);

$context = stream_context_create($options);

$predict_response_json = file_get_contents($ml_request_url, false, $context);
$predict_response = json_decode($predict_response_json, true);
//var_dump($predict_response);

// sspへレスポンス
$ssp_responce_url = 'http://localhost:8080/ssp/res';

$ssp_responce_body = array(
    'request_id' => '111111',
    'url' => 'http://aaaa.com',
    'price' => '50',
);

$ssp_responce_json = json_encode($ssp_responce_body);

$options_res = array (
    'http' => array (
        'method' => 'POST',
        'header'=> "Content-type: application/json\r\n" . "Content-Length: " . strlen($ssp_responce_json) . "\r\n",
        'content' => $ssp_responce_json
    )
);

$context_res = stream_context_create($options_res);

//$ssp_response_back = file_get_contents($ssp_responce_url, false, $context_res);



//exit;

