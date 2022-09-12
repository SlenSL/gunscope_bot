<?php

namespace backend\bots;

use yii\base\Model;
use yii\helpers\Json;


//Bot's token
// const TOKEN = ''; 
//Base url for requests to Telegram
// const BASE_URL = 'https://api.telegram.org/bot' . TOKEN . '/';

class Bot extends Model
{
    //id of user, who made an update
    private $chatId;
    private $token;

    /**
     * constructor
     */
    function __construct($token, $chatId, $update = null) 
    {
        $this->token = $token;
        $this->chatId = $chatId;
        
        parent::__construct();
        // $postData = $update;
    }

    /** 
     * Sends Request to Telegram 
     * @param $method
     * @param array $fields
     * @return mixed
     */ 
    public function botApiQuery($method, $fields = [])
    {
        $ch = curl_init('https://api.telegram.org/bot' . $this->token . '/'. $method);

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
     * Sends message by $this->chatId
     * @param mixed $this->chatId unique identifier of chat in Telegram
     * @param mixed $text text of the message
     */
    public function sendMessage($text)
    {
        self::botApiQuery("sendMessage", [
            "chat_id" => $this->chatId,
            "text" => $text,
            "parse_mode" => 'HTML'
        ]);
    }

    /**
     * Sends message with keyboard by $this->chatId
     * @param mixed $this->chatId unique identifier of chat in Telegram
     * @param mixed $text text of the message
     */
    public function sendMessageWithKeyboard($text, $keyboard)
    {
        self::botApiQuery("sendMessage", [
            "chat_id" => $this->chatId,
            "text" => $text,
            "reply_markup" => $keyboard,
            "parse_mode" => 'HTML'
        ]);
    }

    public function answerCallback($text, $callback_id) 
    {
        // отправляем Уведомление
        self::botApiQuery("answerCallbackQuery", [
            "callback_query_id" => $callback_id,
            "text" => $text,
            "alert" => false
        ]);
    }

    

    /**
     * Sends message with keyboard by $this->chatId
     * @param mixed $this->chatId unique identifier of chat in Telegram
     * @param mixed $text text of the message
     */
    public function sendMessageWithInlineKeyboard($text, $inlineKeyboard)
    {
        self::botApiQuery("sendMessage", [
            "chat_id" => $this->chatId,
            "text" => $text,
            "reply_markup" => $inlineKeyboard,
            "parse_mode" => 'HTML'
        ]);
    }

    /**
     * Sends message with keyboard by $this->chatId
     * @param mixed $this->chatId unique identifier of chat in Telegram
     * @param mixed $text text of the message
     */
    public function sendMessageWithPhoto($text, $photoUrl)
    {
        self::botApiQuery("sendPhoto", [
            "chat_id" => $this->chatId, 
            "photo" => $photoUrl,
            "caption" => $text,
            "parse_mode" => 'HTML'
        ]);
    }

    /**
     * Sends message with keyboard by $this->chatId
     * @param mixed $this->chatId unique identifier of chat in Telegram
     * @param mixed $text text of the message
     */
    public function sendMessageWithPhotoAndKeyboard($text, $inlineKeyboard, $photoUrl)
    {
        self::botApiQuery("sendPhoto", [
            "chat_id" => $this->chatId,
            "photo" => $photoUrl,
            "caption" => $text,
            "reply_markup" => $inlineKeyboard,
            "parse_mode" => 'HTML'
        ]);
    }

    /**
     * Sends message with keyboard by $this->chatId
     * @param mixed $this->chatId unique identifier of chat in Telegram
     * @param mixed $text text of the message
     */
    public function sendForceReplyMessage($text)
    {
        self::botApiQuery("sendMessage", [
            "chat_id" => $this->chatId,
            "text" => $text,
            "reply_markup" => self::getForcedReply(),
        ]);
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
        return json_encode($inlineKeyboard);
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

    /**
     * Creates keyboard located under the text field
     * @param array $data
     * @return json array
     */
    public static function getOneTimeKeyboard($data)
    {
        $keyboard = array(
            "keyboard" => $data,
            "one_time_keyboard" => true,
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
}
