<?php
$clientId = '55d457f87dfa416b8bd6f8be66d2928f';

$apiUrl = 'https://api.chc.edu.tw/school-oauth/authorize?client_id='.$clientId.'&response_type=code&state=abc';

if (isset($_GET['data'])) {

    $data = json_decode($_GET['data']);
    echo "<pre>";
    print_r($data);

}

else {

    header('Location: '.$apiUrl);

}
