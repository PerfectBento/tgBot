<?php

class ActualToken{

    public $jsonArray = [];
    public $todoName = [];
    
    public static function getFile(){
        //Если файл существует - получаем его содержимое
        if (file_exists('auth.json')){
            $json = file_get_contents('auth.json');
            $jsonArray = json_decode($json, true);
            return $jsonArray;
        }
    }

    public static function putInFile($data){
        // Делаем запись в файл
        if (file_exists('auth.json')){
            $jsonArray[] = $data;
            file_put_contents('auth.json', json_encode($jsonArray, JSON_FORCE_OBJECT));
        }
    }
}
?>