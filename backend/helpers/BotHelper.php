<?php

namespace  backend\helpers;
use backend\models\upmarket\UpmarketEmail as Email;

class BotHelper
{
    public static function getPostData() {
        return json_decode(file_get_contents('php://input'), true);
    }
   

    public static function isTextMessage($postData) {
        return array_key_exists('text', $postData['message']['text']);
    }

    public static function isButtonClick($postData) {
        return array_key_exists('text', $postData['callback_query']['data']);
    }

    // public static function parseMail() 
    // {

    //     foreach ($emails as $email) {
    //         $emailModel = Email::find()->where(['email' => $email])->limit(1)->one();
    //         if(!$emailModel) {
    //             $emailModel = new Email($email);
    //             $emailModel->save();
    //         }
    //     }
    // }
}