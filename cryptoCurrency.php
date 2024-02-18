<?php

    $url = "https://api.nobitex.ir/v2/orderbook/all";
    $cryptoNamesArray = ['BTCIRT', 'ETHIRT', 'LTCIRT', 'USDTIRT', 'XRPIRT', 'BCHIRT', 'BNBIRT', 'EOSIRT', 'XLMIRT', 'ETCIRT', 'TRXIRT', 'DOGEIRT', 'UNIIRT', 'DAIIRT', 'LINKIRT', 'DOTIRT', 'AAVEIRT', 'ADAIRT', 'SHIBIRT', 'FTMIRT', 'MATICIRT', 'AXSIRT', 'MANAIRT', 'SANDIRT', 'AVAXIRT', 'MKRIRT', 'GMTIRT', 'USDCIRT'];

    $ch = curl_init();
    curl_setopt($ch , CURLOPT_URL , $url);
    curl_setopt($ch , CURLOPT_RETURNTRANSFER , true);
    $result = curl_exec($ch);

    curl_close($ch);

    $data = json_decode($result);
    $SYMBOLS = ['BTCIRT', 'ETHIRT', 'LTCIRT', 'USDTIRT', 'XRPIRT','BNBIRT', 'EOSIRT', 'XLMIRT','ETCIRT', 'TRXIRT', 'DOGEIRT', 'UNIIRT', 'DAIIRT', 'LINKIRT', 'DOTIRT',
        'AAVEIRT', 'ADAIRT', 'SHIBIRT', 'FTMIRT', 'MATICIRT', 'AXSIRT', 'MANAIRT', 'SANDIRT', 'AVAXIRT', 'MKRIRT',
        'GMTIRT', 'USDCIRT'];
    $finalDataText = '';
    foreach ($SYMBOLS as $key => $value) {
        // echo date('H:m:s m/d/Y ' , $data->$value->lastUpdate);
        // $date = jdate($data->$value->lastUpdate)->format('datetime');
        $finalDataText .= " <pre> نام ارز : {$value}
   قیمت ارز :  " . number_format($data->$value->lastTradePrice) . "</pre>";
    }
