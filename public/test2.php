<?php
$API_client_id = "129d304d293e9c600e8be2119819cde3";
$API_client_secret = "1af11e4ef9b71c818e7c2659d4a06e20";

$data = API($API_client_id,$API_client_secret);

echo "<pre>";
print_r($data);

function API($API_client_id,$API_client_secret){

    // =================================================
    //    學生榮譽榜 (url: https://api.chc.edu.tw)
    //    校務佈告欄 (url: https://api.chc.edu.tw/school-news)
    //    同步學期資料 (url: https://api.chc.edu.tw/semester-data)
    //    更改師生密碼 (url: https://api.chc.edu.tw/change-password)

    // API NAME
    $api_name = '/semester-data';
    //$api_name = '/school-news';
    // 更改師生密碼 (url: https://api.chc.edu.tw/change-password)

    // API URL
    $api_url = 'https://api.chc.edu.tw';
    //: https://api.chc.edu.tw/school-news
    // 建立 CURL 連線
    $ch = curl_init();
    // 取 access token
    curl_setopt($ch, CURLOPT_URL, $api_url."/oauth?authorize");
    // 設定擷取的URL網址
    curl_setopt($ch, CURLOPT_POST, TRUE);
    // the variable
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    curl_setopt($ch, CURLOPT_POSTFIELDS, array(
        'client_id' => $API_client_id,
        'client_secret' => $API_client_secret,
        'grant_type' => 'client_credentials'
    ));

    $data = curl_exec($ch);
    $data = json_decode($data);

    $access_token = $data->access_token;
    $authorization = "Authorization: Bearer ".$access_token;

    curl_setopt($ch, CURLOPT_URL, $api_url.$api_name);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // **Inject Token into Header**
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $result = curl_exec($ch);
    return json_decode($result);
}
