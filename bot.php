<?php
    include ("apiKey.php");
    $inData = file_get_contents("php://input");
    $tData = json_decode($inData);
    $chat_id = $tData->message->chat->id;
    $text= $tData->message->text;



    bot('sendMessage' , [
        'chat_id' => $chat_id,
        'text' => 'hello'
    ]);


    function bot($method , $data=[]){
        $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
        $ch = curl_init();
        curl_setopt($ch , CURLOPT_URL , $url);
        curl_setopt($ch , CURLOPT_RETURNTRANSFER , true);
        curl_setopt($ch , CURLOPT_POSTFIELDS , $data);
        $result = curl_exec($ch);

        return $result;

    }

