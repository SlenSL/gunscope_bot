<?php

namespace  backend\helpers;
use backend\models\upmarket\UpmarketEmail as Email;

class BotHelper
{
    public static function getPostData() {
        return json_decode(file_get_contents('php://input'), true);
    }
   

    public static function isTextMessage($postData) {
        return array_key_exists('text', $postData['message']);
    }

    public static function parseMail() 
    {
        // $emails = ['noa@upmarket.cc', 'kp@upmarket.cc', 'mo@upmarket.cc', 'us@upmarket.cc', 'es@upmarket.cc'];
        // $emails = ['ok@upmarket.cc', 'pt@upmarket.cc', 'ek@upmarket.cc', 'buh@upmarket.cc', 'uea@upmarket.cc'];
        $emails = ['ke@upmarket.cc', 'ka@upmarket.cc', 'pe@upmarket.cc'];


        foreach ($emails as $email) {
            $emailModel = Email::find()->where(['email' => $email])->limit(1)->one();
            if(!$emailModel) {
                $emailModel = new Email($email);
                $emailModel->save();
            }
        }
    }
}