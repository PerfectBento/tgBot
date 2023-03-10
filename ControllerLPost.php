<?php

class LPost
{
    
    public function getToken()
    {
        $select_token = new ActualToken();
        $select_token = $select_token::getFile();
        $token = $select_token[0]["token"];
        $valid_til = $select_token[0]["valid_till"];

        if( date('Y-m-d H:i:s') > $valid_til) {
            $secret = '8d603za2Cq3U7qd7';
            $method_auth = 'Auth';
            // после того как обновится апи, нужно будет переписать черзе curl, т.к. токен будет хранится как bear-token в headers
            $json_auth = file_get_contents('https://api.l-post.ru/?method='.$method_auth.'&secret='.$secret.'');
            $json_auth = json_decode($json_auth, true);
            $token = $json_auth["token"];
            $date_life_token = $json_auth["valid_till"];
            $update_row = new ActualToken();
            $update_row::putInFile($json_auth);
            
            return $token;
        }
        if( date('Y-m-d H:i:s') < $valid_til) {
            return $token;
        }
    }

    public function getOrderInfo($OrderNumber = null, $CustomerNumber = null, $CustomerPhone = null)
    { 
        if(!empty($OrderNumber) && empty($CustomerNumber) && empty($CustomerPhone)){
            $result_query = file_get_contents('https://api.l-post.ru/special-for-bot/orders-info?json={"Fields":["StatusInfo","StatusHistory","DeliveryInfo","PickupPointInfo","PaymentInfo","OrderProducts","CustomerInfo"],"OrderNumbers":["'.$OrderNumber.'"]}&type=lpost');
            return $result_query;
        }
        else if(!empty($CustomerNumber) && empty($OrderNumber) && empty($CustomerPhone)){
            $result_query = file_get_contents('https://api.l-post.ru/special-for-bot/orders-info?json={"Fields":["StatusInfo","StatusHistory","DeliveryInfo","PickupPointInfo","PaymentInfo","OrderProducts","CustomerInfo"],"CustomerNumbers":["'.$CustomerNumber.'"]}&type=lpost');
            return $result_query;
        }
        else if(!empty($CustomerPhone) && empty($OrderNumber) && empty($CustomerNumber)){
            $result_query = file_get_contents('https://api.l-post.ru/special-for-bot/orders-info?json={"Fields":["StatusInfo","StatusHistory","DeliveryInfo","PickupPointInfo","PaymentInfo","OrderProducts","CustomerInfo"],"CustomerPhones":["'.$CustomerPhone.'"]}&type=lpost');
            return $result_query;
        }
        else if(!empty($CustomerNumber) && !empty($CustomerPhone) && empty($OrderNumber)){
            $result_query = file_get_contents('https://api.l-post.ru/special-for-bot/orders-info?json={"Fields":["StatusInfo","StatusHistory","DeliveryInfo","PickupPointInfo","PaymentInfo","OrderProducts","CustomerInfo"],"CustomerNumbers":["'.$CustomerNumber.'"],"CustomerPhones":["'.$CustomerPhone.'"]}&type=lpost');
            return $result_query;
        }
    }
    public function parseJsonTrackingOrder($data,$input_json,$message)
    {
        $json = json_decode($input_json,true);
        $check_empty_json = $json["JSON_TXT"];
        $json = json_decode($check_empty_json,true);
        if(empty($check_empty_json)){
            $shablon[]= "Заказ не найден";
            return ["shablon" => $shablon];
        }
        else if(count($json) == 1){
            foreach ($json as $a){
                foreach ($a["StatusInfo"]["StatusHistory"] as $key => $value){
                    $b[] = $value;
                }
            }
            usort(
                $b,
                function($a, $b){
                    switch (true) {
                        case $a['DateChange'] > $b['DateChange']: 
                            return 1;
                            break;
                        case $a['DateChange'] < $b['DateChange']: 
                            return -1;
                            break;
                        default:
                            return 0;
                    } 
                }
            );
            foreach ($b as $key => $value){
                $date = $b[$key]["DateChange"];
                
                if($b[$key]["isVozvratStatus"] == false ){
                    
                    // Как вариант на заметку, можно просто брать по первому статусу из наименования и только его оставлять, остальные/другие в группе просто не показывать
                    if ($b[$key]["ID_StatusNew"] == "1"){
                        $b[$key]["ID_StatusNew"] = 'Создано';
                    }
                    else if ($b[$key]["ID_StatusNew"] == "2" ){
                        $b[$key]["ID_StatusNew"] = 'Готово к отгрузке на склад Л-Пост';
                    }
                    else if ($b[$key]["ID_StatusNew"] == "3" ){
                        $b[$key]["ID_StatusNew"] = 'Отгружено на склад Л-Пост';
                    }
                    else if ($b[$key]["ID_StatusNew"] == "4"){
                        $b[$key]["ID_StatusNew"] = 'Готово к отгрузке со склада Интернет-магазина';
                    }
                    else if ($b[$key]["ID_StatusNew"] == "5" ){
                        $b[$key]["ID_StatusNew"] = 'Прибыло на склад Л-Пост';
                    }
                    else if ($b[$key]["ID_StatusNew"] == "6" ){
                        $b[$key]["ID_StatusNew"] = 'Прибыло в пункт приёма';
                    }
                    else if ($b[$key]["ID_StatusNew"] == "7" ){
                        $b[$key]["ID_StatusNew"] = 'Отправлено в пункт выдачи';
                    }
                    else if ($b[$key]["ID_StatusNew"] == "9"){
                        $b[$key]["ID_StatusNew"] = 'Выдано получателю';
                    }
                    else if ($b[$key]["ID_StatusNew"] == "10"){
                        $b[$key]["ID_StatusNew"] = 'Аннулировано';
                    }
                    else if ($b[$key]["ID_StatusNew"] == "12" ){
                        $b[$key]["ID_StatusNew"] = 'Выдано получателю';
                    }
                    else if ($b[$key]["ID_StatusNew"] == "13"){
                        $b[$key]["ID_StatusNew"] = 'Выдано курьеру';
                    }
                    else if($a["PickupPointInfo"]["DeliveryKindType"] == "1"){
                        if ($b[$key]["ID_StatusNew"] == "8"){
                            $b[$key]["ID_StatusNew"] = 'Прибыло в пункт выдачи';
                        }
                    }
                    else if($a["PickupPointInfo"]["DeliveryKindType"] == "4"){
                        if ($b[$key]["ID_StatusNew"] == "8"){
                            $b[$key]["ID_StatusNew"] = 'Прибыло в распределительный центр';
                        }
                    }
                    $shablon[] = $b[$key]["ID_StatusNew"].PHP_EOL.date("d.m.Y H:i", strtotime($date)).PHP_EOL.'-------------------------------------'.PHP_EOL; 
                }
            }
            return ["shablon" => $shablon, "success" => 1];
            // return $json;
        }
        else if(count($json) > 1){
            $shablon[]= "Найдено более одного заказа! Пожалуйста, введите номер вашего мобильного телефона, указанного при оформлении заказа, в формате 9053446776 (10 цифр). Номер телефона нужен, чтобы идентифицировать Ваше отправление!";
            $name_comand = 'getTracingCostumerNumberPlusCustomerPhone';
            $check_message_reset = new Telegramm();
            $check_message_reset = $check_message_reset->checkMessageExistance($data,$name_comand); 
            $this->InsertOrUpdateOrderUserInDb($data,$message);  
            return ["shablon" => $shablon]   ;
            // return ["shablon" => $json];  
        }
    }

    public function InsertOrUpdateOrderUserInDb($data,$message){
        $select_query = "SELECT * FROM `users_orders` where `user`=:user and `tracking_number`=:tracking_number";
        $params = [
            ':user' => $data["chat"]["username"],
            ':tracking_number' => $message
        ];
        $db = new DB();
        $select_row = $db->getRow($select_query,$params);  

        if(empty($select_row)){
            $insert_query = "INSERT INTO `users_orders` (`chat_id`, `user`, `tracking_number`, `date_last_appeal`) VALUES (:chat_id, :user, :tracking_number, :date_last_appeal)";
            $params = [
                ':user' => $data["chat"]["username"],
                ':tracking_number' => $message,
                ':date_last_appeal' => time(),
                ':chat_id' => $data["chat"]["id"],
            ];
            $db->add($insert_query,$params); 
        }
        else{
            $update_query = "UPDATE `users_orders` SET `date_last_appeal`= :date_last_appeal WHERE `chat_id`= :chat_id and `tracking_number` = :tracking_number";
            $params = [
                ':user' => $data["chat"]["username"],
                ':tracking_number' => $message,
                ':date_last_appeal' => time(),
                ':chat_id' => $data["chat"]["id"],
            ];
            $db->updateRow($update_query,$params); 
        }
         
    }

    public function parseJsonGetInfoTracingNumberOnPhone($input_json){
        $json = json_decode($input_json,true);
        $check_empty_json = $json["JSON_TXT"];
        $json = json_decode($check_empty_json,true);
        if(empty($check_empty_json)){
            $shablon[]= "Заказ не найден";
            return ["shablon" => $shablon];
        }
        else{
            foreach ($json as $a){

                if ($a["StatusInfo"]["ID_StatusProd"] == "1"){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Создано';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "2" ){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Готово к отгрузке на склад Л-Пост';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "3" ){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Отгружено на склад Л-Пост';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "4"){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Готово к отгрузке со склада Интернет-магазина';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "5" ){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Прибыло на склад Л-Пост';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "6" ){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Прибыло в пункт приёма';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "7" ){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Отправлено в пункт выдачи';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "9"){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Выдано получателю';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "10"){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Аннулировано';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "12" ){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Выдано получателю';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "13"){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Выдано курьеру';
                }      
                else if($a["PickupPointInfo"]["DeliveryKindType"] == "1"){
                    if ($a["StatusInfo"]["ID_StatusProd"] == "8"){
                        $a["StatusInfo"]["ID_StatusProd"] = 'Прибыло в пункт выдачи';
                    }
                }
                else if($a["PickupPointInfo"]["DeliveryKindType"] == "4"){
                    if ($a["StatusInfo"]["ID_StatusProd"] == "8"){
                        $a["StatusInfo"]["ID_StatusProd"] = 'Прибыло в распределительный центр';
                    }
                }       
                $shablon[] = 'Номер заказа - ABC'.$a["OrderNumber"].PHP_EOL.'Актуальный статус - '.$a["StatusInfo"]["ID_StatusProd"].PHP_EOL.'-------------------------------------------------------'.PHP_EOL;
            }
            return ["shablon" => $shablon, "success" => 1];
        }
    
    }

    public function parseJsonInfoDeliveryOrder($data,$input_json,$message){
        $json = json_decode($input_json,true);
        $check_empty_json = $json["JSON_TXT"];
        $json = json_decode($check_empty_json,true);
        if(empty($check_empty_json)){
            $shablon[]= "Заказ не найден";
            return ["shablon" => $shablon];
        }
        else if(count($json) == 1){
            foreach ($json as $a){
                if ($a["StatusInfo"]["ID_StatusProd"] == "1"){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Создано';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "2" ){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Готово к отгрузке на склад Л-Пост';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "3" ){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Отгружено на склад Л-Пост';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "4"){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Готово к отгрузке со склада Интернет-магазина';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "5" ){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Прибыло на склад Л-Пост';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "6" ){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Прибыло в пункт приёма';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "7" ){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Отправлено в пункт выдачи';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "9"){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Выдано получателю';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "10"){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Аннулировано';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "12" ){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Выдано получателю';
                }
                else if ($a["StatusInfo"]["ID_StatusProd"] == "13"){
                    $a["StatusInfo"]["ID_StatusProd"] = 'Выдано курьеру';
                }      
                else if($a["PickupPointInfo"]["DeliveryKindType"] == "1"){
                    if ($a["StatusInfo"]["ID_StatusProd"] == "8"){
                        $a["StatusInfo"]["ID_StatusProd"] = 'Прибыло в пункт выдачи';
                    }
                }
                else if($a["PickupPointInfo"]["DeliveryKindType"] == "4"){
                    $a["PickupPointInfo"]["DeliveryKindType"] = "Курьерская доставка";
                    if ($a["StatusInfo"]["ID_StatusProd"] == "8"){
                        $a["StatusInfo"]["ID_StatusProd"] = 'Прибыло в распределительный центр';
                    }
                }   
                if($a["PickupPointInfo"]["DeliveryKindType"] == "1"){
                    // самовывоз
                    $a["PickupPointInfo"]["DeliveryKindType"] = "Самовывоз";
                    $shablon[] = 'Спасибо, что обратились ко мне! Я нашел информацию по вашему заказу:'.PHP_EOL.'Актуальный статус заказа: '.$a["StatusInfo"]["ID_StatusProd"].PHP_EOL.'Трек-номер заказа Л-Пост: ABC'.$a["OrderNumber"]
                    .PHP_EOL.'Номер заказа интернет-магазина: '.$a["CustomerNumber"].PHP_EOL.'Способ доставки: '.$a["PickupPointInfo"]["DeliveryKindType"].PHP_EOL.
                    'Адрес доставки: '.$a["PickupPointInfo"]["address"].PHP_EOL."Срок хранения: ".$a["PickupPointInfo"]["DaysInAgent"].' дней,'.PHP_EOL.'График работы: '.$a["PickupPointInfo"]["wokmode"].PHP_EOL.
                    'Дата доставки: '.date("d.m.Y H:i", strtotime($a["DeliveryInfo"]["DeliveryDate"])).'-'.date("H:i", strtotime($a["DeliveryInfo"]["DeliveryDateBound"])).'.';
                }
                else if($a["PickupPointInfo"]["DeliveryKindType"] =="4"){
                    // курьер
                    $a["PickupPointInfo"]["DeliveryKindType"] = "курьерская доставка";
                    $shablon[] = 'Спасибо, что обратились ко мне! Я нашел информацию по вашему заказу:'.PHP_EOL.'Актуальный статус заказа: '.$a["StatusInfo"]["ID_StatusProd"].PHP_EOL.'Трек-номер заказа Л-Пост: ABC'.$a["OrderNumber"]
                    .PHP_EOL.'Номер заказа интернет-магазина: '.$a["CustomerNumber"].PHP_EOL.'Способ доставки: '.$a["PickupPointInfo"]["DeliveryKindType"].PHP_EOL.
                    'Адрес доставки: '.$a["DeliveryInfo"]["Address"].PHP_EOL.'Дата доставки: '.date("d.m.Y H:i", strtotime($a["DeliveryInfo"]["DeliveryDate"])).'-'.date("H:i", strtotime($a["DeliveryInfo"]["DeliveryDateBound"])).'.';
                }
            }
            // return $shablon;
            return ["shablon" => $shablon, "success" => 1];
        }
        else if(count($json) > 1){
           $shablon[]= "Найдено более одного заказа! Пожалуйста, введите номер вашего мобильного телефона, указанного при оформлении заказа, в формате 9053446776 (10 цифр). Номер телефона нужен, чтобы идентифицировать Ваше отправление!";
           $name_comand = 'getInfoDeliveryCostumerNumberPlusCustomerPhone';
           $check_message_reset = new Telegramm();
           $check_message_reset = $check_message_reset->checkMessageExistance($data,$name_comand); 
           $this->InsertOrUpdateOrderUserInDb($data,$message);  
           return ["shablon" => $shablon];
        }
    }
}
?>