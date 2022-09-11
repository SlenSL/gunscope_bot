<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;

use backend\helpers\BotHelper;
use backend\helpers\ValidationHelper;

use backend\bots\Bot;
use backend\models\BotUser;
 
class BotController extends Controller
{
    private $bot;

    private $chatId;
    private $currentMessage;
    private $postData;
    private $user;

    public function behaviors()
    {
        return array_merge([
            'cors' => [
                'class' => \yii\filters\Cors::className(),
                #special rules for particular action
                'actions' => [
                    'test-request' => [
                        #web-servers which you alllow cross-domain access
                        'Origin' => ['*'],
                        'Access-Control-Request-Method' => ['POST'],
                        'Access-Control-Request-Headers' => ['*'],
                        'Access-Control-Allow-Credentials' => null,
                        'Access-Control-Max-Age' => 86400,
                        'Access-Control-Expose-Headers' => [],
                    ]
                ],
                #common rules
                'cors' => [
                    'Origin' => [],
                    'Access-Control-Request-Method' => [],
                    'Access-Control-Request-Headers' => [],
                    'Access-Control-Allow-Credentials' => null,
                    'Access-Control-Max-Age' => 0,
                    'Access-Control-Expose-Headers' => [],
                ]
            ],
        ], parent::behaviors());

    }

    public function beforeAction($action)//Обязательно нужно отключить Csr валидацию, так не будет работать
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionWebhook()
    {        
        $this->chatId = 712226559;

        /* Инициализация бота */
        $this->bot = new Bot('5780876936:AAGtj-8WeL-WlsE9QmzuH6URFTPxPd3EMI8', $this->chatId);

         $this->sendMessageWithInlineKeyboardArray(
            "Что вы хотите сделать?",
            [
                ['text' => '📧Отправить пост', 'callback_data' => 'send_post'],
                ['text' => "💻Смотреть посты (5 крайних)", 'callback_data' => 'watch_posts'],
            ]
        );

        $this->postData = BotHelper::getPostData();

        $this->sendMessageWithInlineKeyboard(
            "<b>Приветствую терпил😌</b>\n",
            $this->postData['callback_query']['data'],
            // 'af',
            'da'
        );

        /* Текущий ответ от пользователя */
        $this->postData = BotHelper::getPostData();
        $this->chatId = $this->postData['message']['chat']['id'];
        $this->currentMessage = trim($this->postData['message']['text']);
        
        /* Инициализация бота */
        $this->bot = new Bot('5780876936:AAGtj-8WeL-WlsE9QmzuH6URFTPxPd3EMI8', $this->chatId);
        
        /* Текущий пользователь */
        $user = BotUser::getUser($this->chatId);
        $user->setLastSendAt();
        $user->saveLastMessage($this->currentMessage);

        // Если текстовое сообщение
        if (BotHelper::isTextMessage($this->postData)) {

            switch($user->step_message) {
                case 0:
                    $this->sendMessage(
                        "<b>Приветствую терпил😌</b>\n"
                    );

                    $user->setStepMessage(1);
                break;
    
                case 1:
                    $this->sendMessage(
                        "<b>Отправь сообщение для других терпил</b>\n"
                    );

                    $user->setStepMessage(2);
                break;
                
                case 2:
                break;
    
                default:
                    $this->sendMessage(
                        "<b>Спасибо, что вы с нами😌</b>\n"
                    );
                break;
            }

            $user->save();           
        }

        return true;
    }

    private function sendMessage($message) 
    {
        $this->bot->sendMessage($message);
    }

    private function sendMessageWithKeyboard($message, $keyboardData  = [[["text" => "Дальше"]]]) 
    {
        $keyboard = $this->bot->getOneTimeKeyboard($keyboardData);

        $this->bot->sendMessageWithKeyboard($message, $keyboard);
    }

    private function sendMessageWithInlineKeyboard($message, $button_text, $callback) 
    {
        $keyboardData = [
            ['text' => $button_text, 'callback_data' => $callback],
        ];

        $keyboard = $this->bot->getInlineKeyBoard([$keyboardData]);
        $this->bot->sendMessageWithInlineKeyboard($message, $keyboard);
    }

    private function sendMessageWithInlineKeyboardArray($message, $keyboardData) 
    {
        $keyboard = $this->bot->getInlineKeyBoard([$keyboardData]); 
        $this->bot->sendMessageWithInlineKeyboard($message, $keyboard);
    }

    private function sendMessageWithPhotoAndKeyboard($message, $button_text = null, $callback = null, $button_url = null, $photoUrl = null) 
    {
        if (!empty($button_url)) {
            $keyboardData = [
                ['text' => $button_text, 'url' => $button_url,'callback_data' => $callback],
            ];
        } else {
            $keyboardData = [
                ['text' => $button_text,'callback_data' => $callback],
            ];
        }

        $keyboard = $this->bot->getInlineKeyBoard([$keyboardData]);

        if (!empty($photoUrl) && !empty($keyboardData)) {

            $this->bot->sendMessageWithPhotoAndKeyboard($message, $keyboard, $photoUrl);

        } else if (empty($photoUrl) && !empty($keyboardData)) {

            $this->bot->sendMessageWithInlineKeyboard($message, $keyboard);

        } else if (!empty($photoUrl) && empty($keyboardData)) {

            $this->bot->sendMessageWithPhoto($message, $photoUrl);

        } else {

            $this->bot->sendMessage($message);

        }
    }

    private function sendMessageWithPhoto($message, $photoUrl = null) 
    {
        if (!empty($photoUrl)) {
            $this->bot->sendMessageWithPhoto($message, $photoUrl);
        } else {
            $this->bot->sendMessage($message);
        }
    }
}
