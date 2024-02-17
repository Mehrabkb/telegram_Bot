<?php
    include ("apiKey.php");
    $inData = file_get_contents("php://input");
    $tData = json_decode($inData);
    reply($tData);
    function reply($data){
        $textMessage = $data->message->text;
        switch ($textMessage){
            case 'خانه':
            case '/start' :
                sender($data->message->chat->id , welcomeMessage());
                break;
            case 'نرخ دلار':
                dollorPriceSender($data->message->chat->id);
                break;
            case 'لیست قیمت':
                listPriceMenu($data->message->chat->id , 'یکی از موارد زیر را انتخاب کنید');
                break;
            case 'بنکن':
                sendPhoto($data->message->chat->id , '/benkan/');
                break;
            default :
                sender($data->message->chat->id , welcomeMessage());
        }

    }

    function sender($chat_id , $text){
        $main_keyboard = [
            ['لیست قیمت' , 'نرخ دلار']
        ];
        bot('sendMessage' , [
            'chat_id' => $chat_id,
            'text' => $text,
            'reply_markup' => json_encode(['keyboard' => get_main_keyboard('main') , 'resize_keyboard' => true])
        ]);
    }
    function dollorPriceSender($chat_id ){
        $main_keyboard = [
            ['لیست قیمت' , 'نرخ دلار']
        ];
        include ('dollorprice.php');
        $text = $finalText;
        bot('sendMessage' , [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => get_main_keyboard('main') , 'resize_keyboard' => true])
        ]);
    }
    function listPriceMenu($chat_id , $text = ''){
        $list_price_keyboard = [
            ['میراب'],
            ['بنکن'],
            ['پلیران'],
            ['خانه']
        ];
        bot('sendMessage' , [
            'chat_id' => $chat_id,
            'text' => $text,
            'reply_markup' => json_encode(['keyboard' => get_main_keyboard('listPrice') , 'resize_keyboard' => true])
        ]);
    }
    function sendPhoto($chat_id , $folderPath){
        $sendPhotoKeyboard = [['خانه']];
        $directory = 'assets' . $folderPath;
        $files = scandir($directory);
        $imageFiles = array();
        foreach($files as $file){
            $filePath = $directory . $file;
            if (is_file($filePath) && in_array(pathinfo($file, PATHINFO_EXTENSION), array('jpg', 'jpeg', 'png', 'gif'))) {
                // Add image file path to array
                $imageFiles[] = $filePath;
            }
        }
        foreach($imageFiles as $imageFile){
            $imageFilePath = new CURLFile(realpath($imageFile));
            bot('sendPhoto' , array(
                'chat_id' => $chat_id,
                'photo' => $imageFilePath,
                'reply_markup' => json_encode(['keyboard' => $sendPhotoKeyboard , 'resize_keyboard' => true])
            ));
        }
    }

function bot($method , $data=[]){
        print_r($data);
        $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
        $ch = curl_init();
        curl_setopt($ch , CURLOPT_URL , $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch , CURLOPT_RETURNTRANSFER , true);
        curl_setopt($ch , CURLOPT_POSTFIELDS , $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
        $result = curl_exec($ch);

        return $result;


    }
function welcomeMessage(){
        return 'سلام به ربات تاسیسات خانه خوش آمدید با تشکر از شما بابت همکاری و حضور در این سایت لطفا خدمت مورد نظر خود را انتخاب کنید ';
}
function get_main_keyboard($keyboardType){
        $keyboard = [];
        switch ($keyboardType) {
            case 'main':
                $keyboard = getHomeKeyboard();
                break;
            case 'listPrice':
                $keyboard = getListPriceKeyboard();
                break;
            default :
                $keyboard = getHomeKeyboard();
        }
        return $keyboard;
}
function getHomeKeyboard(){
        return [
            ['لیست قیمت', 'نرخ دلار']
        ];
}
function getListPriceKeyboard(){
        return  [
            ['میراب'],
            ['بنکن'],
            ['پلیران'],
            ['خانه']
        ];
}