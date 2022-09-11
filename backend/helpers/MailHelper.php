<?php

namespace  backend\helpers;

use Yii;

class BotHelper
{
    public static function getPostData() {
        return json_decode(file_get_contents('php://input'), true);
    }
   

    public static function isTextMessage($postData) {
        return array_key_exists('text', $postData['message']);
    }

    public static function sendEmail($email, $name, $text)
    {
       
    }
}

