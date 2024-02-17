<?php
    include ("apiKey.php");
    $inData = file_get_contents("php://input");
    $tData = json_decode($inData);
    reply($tData);

    function reply($data){
        $textMessage = $data->message->text;
        switch ($textMessage){
            case '/start' :
                sender($data->message->chat->id , welcomeMessage());
                break;
            case 'نرخ دلار':
                dollorPriceSender($data->message->chat->id);
                break;
            default :
                sender($data->message->chat->id , welcomeMessage());
        }

    }

    function sender($chat_id , $text){
        $main_keyboard = [
            ['لیست قیمت ' , 'نرخ دلار']
        ];
        bot('sendMessage' , [
            'chat_id' => $chat_id,
            'text' => $text,
            'reply_markup' => json_encode(['keyboard' => $main_keyboard , 'resize_keyboard' => true])
        ]);
    }
    function dollorPriceSender($chat_id ){
        $main_keyboard = [
            ['لیست قیمت ' , 'نرخ دلار']
        ];
        include ('dollorprice.php');
        $text = $finalText;
        bot('sendMessage' , [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => $main_keyboard , 'resize_keyboard' => true])
        ]);
    }


function bot($method , $data=[]){
        $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
        $ch = curl_init();
        curl_setopt($ch , CURLOPT_URL , $url);
        curl_setopt($ch , CURLOPT_RETURNTRANSFER , true);
        curl_setopt($ch , CURLOPT_POSTFIELDS , $data);
        $result = curl_exec($ch);

        return $result;

    }
function welcomeMessage(){
        return 'سلام به ربات تاسیسات خانه خوش آمدید با تشکر از شما بابت همکاری و حضور در این سایت لطفا خدمت مورد نظر خود را انتخاب کنید ';
}
