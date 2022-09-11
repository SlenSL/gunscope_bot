<?php

namespace backend\models;

use yii\base\Model;
use yii\helpers\Json;


//Bot's token
const TOKEN = '1657466162:AAFPZkgaAKbCCoYoJ3paMbFKvlxlds8jDBQ';
//Base url for requests to Telegram
const BASE_URL = 'https://api.telegram.org/bot' . TOKEN . '/';

class Bot extends Model
{
    //id of user, who made an update
    private $userId;
    private $postData;

    /**
     * constructor
     */
    function __construct($update) 
    {
        parent::__construct();
        $postData = $update;
    }

    /** 
     * Sends Request to Telegram 
     * @param $method
     * @param array $fields
     * @return mixed
     */ 
    public static function botApiQuery($method, $fields = [])
    {
        $ch = curl_init(BASE_URL . $method);

        curl_setopt_array($ch, array(
            CURLOPT_POST => count($fields),
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 10
        ));
        $r = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $r;
    }

    /**
     * Creates keyboard  under the message
     * @param array $data
     * @return json array
     */
    public static function getInlineKeyBoard($data)
    {
        $inlineKeyboard = array(
            "inline_keyboard" => $data,
        );
        return Json::encode($inlineKeyboard);
    }

    /**
     * Creates keyboard located under the text field
     * @param array $data
     * @return json array
     */
    public static function getKeyBoard($data)
    {
        $keyboard = array(
            "keyboard" => $data,
            "one_time_keyboard" => false,
            "resize_keyboard" => true
        );
        return Json::encode($keyboard);
    }

    private static function getForcedReply() {
        $replyMarkup = array(
            'force_reply' => true,
            'selective' => true
          );
        return Json::encode($replyMarkup);
    }

    /**
     * KeyBoard button processing
     */
    private function actionKeyboardButton()
    {
        // sending message
        $this->botApiQuery("sendMessage", [
            "chat_id" => $this->userId,
            "text" => "Обработана кнопка " . $this->postData['message']['text'],
        ]);
    }

    /**
     * Sends message by $chat_id
     * @param mixed $chat_id unique identifier of chat in Telegram
     * @param mixed $text text of the message
     */
    public static function sendMessage($chat_id, $text)
    {
        self::botApiQuery("sendMessage", [
            "chat_id" => $chat_id,
            "text" => $text,
        ]);
    }

    /**
     * Sends message with keyboard by $chat_id
     * @param mixed $chat_id unique identifier of chat in Telegram
     * @param mixed $text text of the message
     */
    public static function sendMessageWithKeyboard($chat_id, $text, $keyboard)
    {
        self::botApiQuery("sendMessage", [
            "chat_id" => $chat_id,
            "text" => $text,
            "reply_markup" => $keyboard,
        ]);
    }

    /**
     * Sends message with keyboard by $chat_id
     * @param mixed $chat_id unique identifier of chat in Telegram
     * @param mixed $text text of the message
     */
    public static function sendForceReplyMessage($chat_id, $text)
    {
        self::botApiQuery("sendMessage", [
            "chat_id" => $chat_id,
            "text" => $text,
            "reply_markup" => Bot::getForcedReply(),
        ]);
    }
}
