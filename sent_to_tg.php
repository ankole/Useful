<?php

$token = ' ТОКЕН ';
$recipient = ' ЧАТ ';

$keys = [
    'name' => 'Имя',
    'phone' => 'Телефон',
    'massage' => 'Сообщение',
    'pageId' => 'id страницы',
    'product' => 'Товар'
];

$messege = "";
foreach($_POST as $k => $v) {
    if($k != 'g-recaptcha-response') {
        if($keys[$k]) $k = $keys[$k];
        $v = preg_replace('/\s+/', ' ', $v);
        $v = str_replace(array("\r\n", "\r", "\n"), '',  strip_tags($v));
        $messege.= $k.": ".$v."; \n";
    }
}

$messege.= "IP посетителя: ".$_SERVER['REMOTE_ADDR']."; \n";
$messege.= "Тема: ".$emailSubject."; \n";

// Tg bot V2
require_once( MODX_ASSETS_PATH . 'vendor/autoload.php' );
try {
    $bot = new \TelegramBot\Api\BotApi( $token );
    $bot->sendMessage( $recipient, $messege );
} catch (Exception $e) {
    file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$recipient&parse_mode=html&text=".urlencode($messege));
}