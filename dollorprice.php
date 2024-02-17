<?php
    include ('apiKey.php');
    $ch = curl_init('http://api.navasan.tech/latest/?api_key=' . APIKEYDOLLOR);
    curl_setopt($ch , CURLOPT_RETURNTRANSFER , true);
    $result = curl_exec($ch);
    $price = json_decode($result)->usd_buy->value ;
    $data = json_decode($result)->usd_buy->date;
    $finalText = "<pre>
               آخرین قیمت دلار در تاریخ و ساعت : {$data}
               {$price} تومان
    </pre>";