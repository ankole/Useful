<?php

$token = ' ТОКЕН ';
$recipient = ' ЧАТ ';

switch ($modx->event->name) {
  case 'msOnChangeOrderStatus':
    if ($status == 1) {
        $darr = [
            1 => 'Самовывоз',
            4 => 'Доставка по городу',
            5 => 'Почта России'
			...
        ];
        $parr = [
            1 => 'Оплата наличными',
            3 => 'Онлайн оплата Tinkoff',
            ...
        ];
        $payid = (string)$order->Payment->id;
        $messege = "На сайте сделан заказ №" . $order->get('num');

        $promocode = $_SESSION['promocode'];
        if($promocode) $messege.= "\nПромокод: ".$promocode."\n";

        $messege.= "\n";
        $messege.= "Товары:\n";
        foreach($order->Products as $product) {
            $messege.= $product->product_id.') '.$product->name." - ".$product->count."шт. - ".$product->price."руб.\n";
        }
        $messege.= "\nСпособ доставки: ".$darr[$_POST['delivery']];
        if($parr[$payid]) {
            $messege.= "\nСпособ оплаты: ".$parr[$payid];
        }
        $messege.= "\nПолучатель: ".$_POST['receiver'];
        $messege.= "\nТелефон: ".$_POST['phone'];
        $messege.= "\nКомментарий: ".$_POST['comment'];
        $messege.= "\nПочтовый индекс: ".$_POST['index'];
        $messege.= "\nОбласть: ".$_POST['region'];
        $messege.= "\nГород: ".$_POST['city'];
        $messege.= "\nАдрес: ".$_POST['text_address'];
        if($_POST['point']) $messege.= "\nПункт выдачи: ".$_POST['point'];

        // Tg bot V2
        require_once( MODX_ASSETS_PATH . 'vendor/autoload.php' );
        try {
        	$bot = new \TelegramBot\Api\BotApi( $token );
        	$bot->sendMessage( $recipient, $messege );
        	$media = new \TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia();
        	$sendMedia = false;
        	foreach($order->Products as $product) {
        		$productEl = $modx->getObject('msProductData', [ 'id' => $product->product_id ]);
        		if($productEl->id) {
        		    $media->addItem(new TelegramBot\Api\Types\InputMedia\InputMediaPhoto( 'https://домен' . $productEl->get('image') ));
        		    $sendMedia = true;
        		}
        	}
            if($sendMedia) $bot->sendMediaGroup($recipient, $media);
        } catch (Exception $e) {
        	file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$recipient&parse_mode=html&text=".urlencode($messege));
        }

        $_SESSION['promocode'] = '';

    } else if($status == 2) {

        $messege = "На сайте оплачен заказ №" . $order->get('num');

        // Tg bot V2
        require_once( MODX_ASSETS_PATH . 'vendor/autoload.php' );
        try {
        	$bot = new \TelegramBot\Api\BotApi( $token );
        	$bot->sendMessage( $recipient, $messege );
        } catch (Exception $e) {
        	file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$recipient&parse_mode=html&text=".urlencode($messege));
        }

    }
    break;
}

return true;