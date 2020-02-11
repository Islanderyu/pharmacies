<?php

$config = require __DIR__ . '/config.php';
$fh = fopen(dirname(__DIR__) . '/data.csv', 'r');
$head = fgetcsv($fh, 2048);
while(count($head) !== 14) {
    array_pop($head);
}
$head[] = '縣市';
$head[] = '鄉鎮市區';
$head[] = '村里';
$pool = array();
$pool[] = $head;
while($line = fgetcsv($fh, 2048)) {
    while(count($line) !== 14) {
        array_pop($line);
    }    
    $data = array(
        'COUNTY' => '',
        'TOWN' => '',
        'VILLAGE' => '',
    );
    if(!empty($line[4])) {
        $tgosFile = dirname(__DIR__) . '/tgos/' . $line[4] . '.json';
        if(!file_exists($tgosFile)) {
            $apiUrl = $config['tgos']['url'] . '?' . http_build_query(array(
                'oAPPId' => $config['tgos']['APPID'], //應用程式識別碼(APPId)
                'oAPIKey' => $config['tgos']['APIKey'], // 應用程式介接驗證碼(APIKey)
                'oAddress' => $line[4], //所要查詢的門牌位置
                'oSRS' => 'EPSG:4326', //回傳的坐標系統
                'oFuzzyType' => '2', //模糊比對的代碼
                'oResultDataType' => 'JSON', //回傳的資料格式
                'oFuzzyBuffer' => '0', //模糊比對回傳門牌號的許可誤差範圍
                'oIsOnlyFullMatch' => 'false', //是否只進行完全比對
                'oIsLockCounty' => 'true', //是否鎖定縣市
                'oIsLockTown' => 'false', //是否鎖定鄉鎮市區
                'oIsLockVillage' => 'false', //是否鎖定村里
                'oIsLockRoadSection' => 'false', //是否鎖定路段
                'oIsLockLane' => 'false', //是否鎖定巷
                'oIsLockAlley' => 'false', //是否鎖定弄
                'oIsLockArea' => 'false', //是否鎖定地區
                'oIsSameNumber_SubNumber' => 'true', //號之、之號是否視為相同
                'oCanIgnoreVillage' => 'true', //找不時是否可忽略村里
                'oCanIgnoreNeighborhood' => 'true', //找不時是否可忽略鄰
                'oReturnMaxCount' => '0', //如為多筆時，限制回傳最大筆數
            ));
            $content = file_get_contents($apiUrl);
            file_put_contents($tgosFile, $content);
        }
        $content = file_get_contents($tgosFile);
        $pos = strpos($content, '{');
        $posEnd = strrpos($content, '}');
        $json = json_decode(substr($content, $pos, $posEnd - $pos + 1), true);
        if(isset($json['AddressList'][0])) {
            $data['COUNTY'] = $json['AddressList'][0]['COUNTY'];
            $data['TOWN'] = $json['AddressList'][0]['TOWN'];
            $data['VILLAGE'] = $json['AddressList'][0]['VILLAGE'];
        }
    }
    $line[] = $data['COUNTY'];
    $line[] = $data['TOWN'];
    $line[] = $data['VILLAGE'];
    $pool[] = $line;
}
fclose($fh);
$oFh = fopen(dirname(__DIR__) . '/data.csv', 'w');
foreach($pool AS $line) {
    fputcsv($oFh, $line);
}