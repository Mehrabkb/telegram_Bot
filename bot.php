<?php
    include 'vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    include ("apiKey.php");
    include 'dataBaseConnection.php';
    $inData = file_get_contents("php://input");
    $tData = json_decode($inData);
    function setDataTmpUserData ($data){
        global $tmpUserData ;
        $tmpUserData = $data;
    }
    if(isset($tData->message->photo) && getUserStatus($tData->message->chat->id) == 'sendFactor'){
        $photo = end($tData->message->photo);
        $photo_id = $photo->file_id;

        // Download the photo
        $photo_file = file_get_contents("https://api.telegram.org/bot" . API_KEY . "/getFile?file_id=$photo_id");
        $photo_file = json_decode($photo_file, true);
        $file_path = json_decode(file_get_contents("https://api.telegram.org/bot" . API_KEY . "/getFile?file_id=$photo_id"), true)['result']['file_path'];
        $photo_url = "https://api.telegram.org/file/bot" . API_KEY . "/$file_path";
        $photo_content = file_get_contents($photo_url);
        $photo_name = 'photos/' . $tData->message->chat->id + rand(100000 , 100000000). '.jpg'; // Directory 'photos' must exist
        file_put_contents($photo_name, $photo_content);
//        sendEmailWithAttachment($photo_path , $tData->message->chat->id);
        clearUserStatus($tData->message->chat->id);
    }
    if(isset($tData->message->contact)){
        if(!checkUserExistByChatId($tData->message->chat->id)){
            $conn = connection();
            $sql = "INSERT INTO `users`( `chat_id` ,`phone_number`) VALUES ('{$tData->message->chat->id}','{$tData->message->contact->phone_number}')";
            $result = $conn->query($sql);
            if($result){
                bot('sendMessage' , [
                    'chat_id' => $tData->message->chat->id,
                    'text' => 'شماره موبایل شما با موفقیت ثبت شد ',
                    'reply_markup' => json_encode(['keyboard' => get_main_keyboard('mobile_verified') , 'resize_keyboard' => true])
                ]);
            }
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
                verifyMobileNumber($data->message->chat->id);
                break;
            case 'بنکن':
                sendPhoto($data->message->chat->id , '/benkan/');
                break;
            case 'ارزدیجیتال':
                getCryptoCurrencies($data->message->chat->id);
                break;
            case 'اطلاعات کابری':
                informationUser($data->message->chat->id , 'به ربات خودتون خوش آمدید لطفا برای تکمیل اطلاعات کاربری از دکمه های مربوطه استفاده کنید');
                break;
            case 'نام':
                if(checkUserExistByChatId($data->message->chat->id)){
                    setUserStatus($data->message->chat->id , 'setName');
                    getUserName($data->message->chat->id , 'لطفا نام خود را برای ما ارسال کنید');
                }else{
                    verifyMobileNumber($data->message->chat->id);
                }
                break;
            case 'نام خانوادگی':
                if(checkUserExistByChatId($data->message->chat->id)){
                    setUserStatus($data->message->chat->id , 'setLastName');
                    getUserName($data->message->chat->id , 'لطفا نام خانوادگی خود را برای ما ارسال کنید');
                }else{
                    verifyMobileNumber($data->message->chat->id);
                }
                break;
            case 'ایمیل':
                if(checkUserExistByChatId($data->message->chat->id)){
                    setUserStatus($data->message->chat->id , 'setEmail');
                    getUserName($data->message->chat->id , 'لطفا ایمیل خود را وارد کنید');
                }else{
                    verifyMobileNumber($data->message->chat->id);
                }
                break;
            case 'نام شرکت':
                if(checkUserExistByChatId($data->message->chat->id)){
                    setUserStatus($data->message->chat->id , 'setCompanyName');
                    getUserName($data->message->chat->id , 'لطفا نام شرکت خود را وارد کنید');
                }else{
                    verifyMobileNumber($data->message->chat->id);
                }
                break;
            case 'تلفن شرکت':
                if(checkUserExistByChatId($data->message->chat->id)){
                    setUserStatus($data->message->chat->id , 'setCompanyPhone');
                    getUserName($data->message->chat->id , 'لطفا تلفن شرکت خود را وارد کنید');
                }else{
                    verifyMobileNumber($data->message->chat->id);
                }
                break;
            case 'ارسال پیش فاکتور':
                if(checkUserExistByChatId($data->message->chat->id)){
                    setUserStatus($data->message->chat->id , 'sendFactor');
                    getUserName($data->message->chat->id , 'لطفا عکس فاکتور خود را برای ما ارسال کنید');
                }else{
                    verifyMobileNumber($data->message->chat->id);
                }
            default :
                $userStatus = getUserStatus($data->message->chat->id);
                switch ($userStatus){
                    case 'setName':
                        userSetoneColumn($data->message->chat->id , $data->message->text , 'first_name' , 'نام شما با موفقیت ثبت شد');
                        break;
                    case 'setLastName':
                        userSetoneColumn($data->message->chat->id , $data->message->text , 'last_name' , 'نام خانوادگی شما با موفقیت ثبت شد');
                        break;
                    case 'setEmail':
                        userSetoneColumn($data->message->chat->id , $data->message->text , 'email' , 'ایمیل شما با موفقیت ثبت شد');
                        break;
                    case 'setCompanyName':
                        userSetoneColumn($data->message->chat->id , $data->message->text , 'company_name' , 'نام شرکت شما با موفقیت ثبت شد');
                        break;
                    case 'setCompanyPhone':
                        userSetoneColumn($data->message->chat->id , $data->message->text , 'company_phone' , 'تلفن شرکت با موفقیت ثبت شد');
                }
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
        include ('dollorprice.php');
        $text = $finalText;
        bot('sendMessage' , [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => get_main_keyboard('main') , 'resize_keyboard' => true])
        ]);
    }
    function verifyMobileNumber($chat_id ){
        $conn = connection();
        $sql = "SELECT * FROM `users` WHERE  `chat_id` = '{$chat_id}'";
        $result = $conn->query($sql);
        if($result->num_rows == 0){
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
    function informationUser($chat_id , $text){
        bot('sendMessage' , [
            'chat_id' => $chat_id,
            'text' => $text,
            'reply_markup' => json_encode([ 'keyboard'=> get_main_keyboard('information_user')])
        ]);
    }
    function listPriceMenu($chat_id , $text = ''){
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
                case 'information_user';
                    $keyboard = getUserInformationKeyboard();
                    break;
                case 'getUserName' :
                    $keyboard = getUserNameKeyboard();
                    break;
                default :
                    $keyboard = getHomeKeyboard();
            }
            return $keyboard;
    }
    function getHomeKeyboard(){
            return [
                ['ارسال پیش فاکتور'],
                ['لیست قیمت', 'نرخ دلار'],
                ['دسترسی شماره موبایل' , 'اطلاعات کابری' , 'ارزدیجیتال']
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
    function getUserInformationKeyboard(){
            return [
                    ['نام' , 'نام خانوادگی' , 'ایمیل']
                ,['نام شرکت' , 'تلفن شرکت'] , ['خانه']
                ];
    }
    function getUserNameKeyboard(){
            return [
                    ["خانه"]
            ];
    }
    function getMobileVerifiedKeyboard(){
            return [['خانه']];
    }
    function getUserName($chat_id , $text){
        bot('sendMessage' , [
            'chat_id' => $chat_id,
            'text' => $text,
            'reply_markup' => json_encode([ 'keyboard'=> get_main_keyboard('getUserName') , 'resize_keyboard' => true])
        ]);
    }
    function setUserStatus($chat_id , $status_value){
        $conn = connection();
        $sql = "UPDATE `users` SET `status`= '{$status_value}' WHERE `chat_id` = {$chat_id}";
        $conn->query($sql);
    }
    function getUserStatus($chat_id){
        $conn = connection();
        $sql = "SELECT * FROM `users` WHERE `chat_id` = {$chat_id} ";
        $result = $conn->query($sql);
        return $result->fetch_assoc()['status'];
    }
    function clearUserStatus($chat_id){
        $conn = connection();
        $sql = "UPDATE `users` SET `status`= 'null' WHERE `chat_id` = {$chat_id}";
        $conn->query($sql);
    }
    function userSetoneColumn($chat_id , $userName , $column , $success_text){
        if(checkUserExistByChatId($chat_id)){
            $userName = htmlspecialchars($userName);
            $conn = connection();
            $sql = "UPDATE `users` SET `{$column}`= '{$userName}' WHERE `chat_id` = {$chat_id}";
            if($conn->query($sql)){
                bot('sendMessage' , [
                    'chat_id' => $chat_id,
                    'text' => $success_text,
                    'reply_markup' => json_encode([ 'keyboard'=> get_main_keyboard('information_user') , 'resize_keyboard' => true])
                ]);
            }
            clearUserStatus($chat_id);
        }
    }
    function checkUserExistByChatId($chat_id){
        $conn = connection();
        $sql = "SELECT * FROM `users` WHERE `chat_id` = {$chat_id} ";
        $result = $conn->query($sql);
        if($result->num_rows > 0 ){
            return true;
        }
        return false;
    }
    function getCryptoCurrencies($chat_id){
        include 'cryptoCurrency.php';
        bot('sendMessage' , [
            'chat_id' => $chat_id,
            'text' => $finalDataText,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => get_main_keyboard('main') , 'resize_keyboard' => true])
        ]);
    }
function sendEmailWithAttachment($photo_path , $chat_id) {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true); // Enable exceptions
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Debug mode (optional)
//    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
//    $mail->SMTPAuth = true;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->Username = 'tasisatkhanecompany@gmail.com'; // Your Gmail email address
    $mail->Password = 'Asdf_567'; // Your Gmail password

    $conn = connection();
    $sql = "SELECT * FROM `users` WHERE `chat_id` = {$chat_id} ";
    $result = $conn->query($sql);
    // Set up email headers
    $mail->setFrom('tasisatkhanecompany@gmail.com', 'Sender Name');
    $mail->addAddress('info@tasisatkhane.com', 'Receiver Name');
    $mail->addReplyTo('tasisatkhanecompany@gmail.com', 'Sender Name'); // Optional: Set the reply-to address


    $mail->IsHTML(true); // Set email content as HTML
    $mail->Subject =  'پیش فاکتور تلگرام'; // Email subject
    $mail->Body = 'تلفن مشتری' . ':' . $result->fetch_assoc()['phone_number']; // Email body

    // Attach the photo
//    $mail->addAttachment($photo_path , 'test.jpg');

    // Send the email
    if (!$mail->send()) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        bot('sendMessage' , [
            'chat_id' => $chat_id,
            'text' => 'پیش فاکتور شما برای کارشناسان ما ارسال شد با شما تماس خواهند گرفت',
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => get_main_keyboard('main') , 'resize_keyboard' => true])
        ]);
    }
}