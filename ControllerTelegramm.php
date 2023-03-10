<?php
//file_put_contents('file.txt','$data: '.print_r($data,1)."\n",FILE_APPEND);
//https://api.telegram.org/bot5120392907:AAFoWF9k9PAMGRlx0n69O3WRSSb94cCGpzs/setWebhook?url=https://portfoliosirotin.ru/TelegrammBot/index.php


# Важные константы
define('TOKEN', '5120392907:AAFoWF9k9PAMGRlx0n69O3WRSSb94cCGpzs');

class Telegramm{
    
    public function sendTelegram($method, $data, $headers = [])
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://api.telegram.org/bot' . TOKEN . '/' . $method,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array_merge(array("Content-Type: application/json"), $headers)
        ]);   
        
        $result = curl_exec($curl);
        curl_close($curl);
        return (json_decode($result, 1) ? json_decode($result, 1) : $result);
    }

    public function getFileState(){
        //Если файл существует - получаем его содержимое
        if (file_exists('state.txt')){
            $jsonFileState = file_get_contents('state.txt');
              echo "<pre>";
            $new = json_decode($jsonFileState,true);
            var_dump($new); 
            print_r($new);
        }
        else{
            return "Нет такого файла";
        }
    }

    public function checkMessageExistance($data,$comand){
        $chat_id = $data["chat"]["id"]; //ок
        $user_name = $data["chat"]["username"];
        $name_comand = $comand;
        $date = $data["date"];
        $select_row = new DB(); // ок
        $query = "SELECT * FROM check_message WHERE chat_id = :chat_id and name_comand = :name_comand";
        $params = [
            ':chat_id' => $chat_id,
            ':name_comand' => $name_comand
        ];

        $select_row = $select_row::getcheckMessageExistance($query,$params);
        
        // return $select_row;
        if(empty($select_row)){
            $insert_query = "INSERT INTO `check_message`(`chat_id`, `user_name`, `name_comand`, `date`) VALUES (:chat_id, :user_name, :name_comand, :date )";
            $params = [
                ':chat_id' => $chat_id,
                ':user_name' => $user_name,
                ':name_comand' => $name_comand,
                ':date' => $date
            ];

            $insert_row = new DB();
            $insert_row = $insert_row::add($insert_query,$params);
            // $select_row::crashConnect();

        }
        else{
            $update_query = "UPDATE `check_message` SET `date`=:date WHERE `chat_id`=:chat_id and `user_name`=:user_name and `name_comand`=:name_comand";
            $params = [
                ':chat_id' => $chat_id,
                ':user_name' => $user_name,
                ':name_comand' => $name_comand,
                ':date' => $date
            ];

            $update_row = new DB();
            $update_row = $update_row::updateRow($update_query,$params);
            // $update_row::crashConnect();
        }


    }

    public function checkLastMessage($data){
        $chat_id = $data["chat"]["id"]; //ок
        // $name_comand = 'getTracingOrderNumber';
        $user_name = $data["chat"]["username"];
        $date_last_check = new DB();
        $query = "SELECT * FROM check_message WHERE chat_id = :chat_id  order by `date` desc limit 1";
        // $query = "SELECT * FROM check_message WHERE chat_id = :chat_id and `user_name` = :user_name order by `date` desc limit 1";
        $params = [
            ':chat_id' => $chat_id
            // ,':user_name' => $user_name
        ];
        $date_last_check = $date_last_check::getcheckMessageExistance($query,$params);

        $d1 = time();
        $d2 = $date_last_check["0"]["date"];
        $countMinute = $d1 - $d2;
        $name_comand = $date_last_check["0"]["name_comand"];
        // return $countMinute;
        return ["countMinute" => $countMinute, "name_comand" => $name_comand];

    }

}



?>