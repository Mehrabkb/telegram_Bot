<?php
    include ("apiKey.php");
    $inData = file_get_contents("php://input");
    $tData = json_decode($inData);
    reply($tData);

    function reply($data){
        $textMessage = $data->message->text;
        switch ($textMessage){
            case '/start' :
                bot('sendMessage' , [
                    'chat_id' => $data->message->chat->id,
                    'text' => welcomeMessage()
                ]);
                break;

            default :
                bot('sendMessage' , [
                    'chat_id' => $data->message->chat->id,
                    'text' => welcomeMessage()
                ]);
        }

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
