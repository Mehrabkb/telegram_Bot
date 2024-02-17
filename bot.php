<?php
    include ("apiKey.php");
    include 'dataBaseConnection.php';
    $inData = file_get_contents("php://input");
    $tData = json_decode($inData);
    if(isset($tData->message->contact)){
        $conn = connection();
        if(!checkUserExist($tData->message->chat->id)){
            $sql = "INSERT INTO `users`( `chat_id` ,`phone_number`) VALUES ('{$tData->message->chat->id}','{$tData->message->contact->phone_number}')";
            $result = $conn->query($sql);
            if($result->num_rows){
                verifyMobileNumber($tData->message->chat->id , $tData);
            }
        }else{
            verifyMobileNumber($tData->message->chat->id , $tData);
        }
    }else{
        reply($tData);
    }

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
            case 'دسترسی شماره موبایل':
                verifyMobileNumber($data->message->chat->id , $data );
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
    function verifyMobileNumber($chat_id , $data){
        $conn = connection();
        $sql = "SELECT * FROM `users` WHERE  `chat_id`";
        $result = $conn->query($sql);
        if(!$result->num_rows){
            $text = 'کابر گرامی به منظور دسترسی به شما مشتری گرامی برای پیگیری سفارشات ما نیاز داریم تا شماره همراه شما را در اختیار داشته باشیم لطفا با کلیک کردن روی دکمه اجازه دسترسی شماره ی خود را با ما به اشتراک بذارید';
            bot('sendMessage' , [
                'chat_id' => $chat_id,
                'text' => $text,
                'reply_markup' => json_encode(['keyboard' => get_main_keyboard('mobile_verify') , 'resize_keyboard' => true])
            ]);
        }else{
            $text = 'کاربر گرامی شماره شما قبلا در ربات ثبت شده است لطفا از قسمت اطلاعات کاربری بقیه موارد را تکمیل فرمایید';
            bot('sendMessage' , [
                'chat_id' => $chat_id,
                'text' => $text,
                'reply_markup' => json_encode(['keyboard' => get_main_keyboard('mobile_verified') , 'resize_keyboard' => true])
            ]);
        }

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
            case 'mobile_verify':
                $keyboard = getMobileVerifyKeyboard();
                break;
            case 'mobile_verified':
                $keyboard = getMobileVerifiedKeyboard();
                break;
            default :
                $keyboard = getHomeKeyboard();
        }
        return $keyboard;
}
function getHomeKeyboard(){
        return [
            ['لیست قیمت', 'نرخ دلار'],
            ['دسترسی شماره موبایل']
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
function getMobileVerifyKeyboard(){
        return [
            [['text' => 'اجازه دسترسی',
                'request_contact' => true
            ]],
            ['خانه']
        ];
}
function getMobileVerifiedKeyboard(){
        return [['خانه']];
}
function checkUserExist($chat_id){
        $conn = connection();
        $sql = "SELECT * FROM `users` WHERE `chat_id` = {$chat_id} ";
        if($conn->query($sql)){
            return true;
        }
        return false;
}