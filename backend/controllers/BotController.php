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
    private $clickedButton;
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
        // $this->chatId = 712226559;
        // $this->currentMessage = 'baza';
        // $this->bot = new Bot('5780876936:AAGtj-8WeL-WlsE9QmzuH6URFTPxPd3EMI8', $this->chatId);

        /* Текущий ответ от пользователя */
        $this->postData = BotHelper::getPostData();
        $this->chatId = $this->postData['message']['chat']['id'] ?: $this->postData['callback_query']['from']['id'];
        $this->clickedButton = $this->postData['callback_query']['data'];
        $this->currentMessage = trim($this->postData['message']['text']);
        $callback_id = (string) $this->postData['callback_query']['id'];

        /* Инициализация бота */
        $this->bot = new Bot('5780876936:AAGtj-8WeL-WlsE9QmzuH6URFTPxPd3EMI8', $this->chatId);

        $this->bot->answerCallback(
            $this->clickedButton,
            $callback_id
        );

        /* Текущий пользователь */
        $this->user = BotUser::getUser($this->chatId);
        $this->user->setLastSendAt();
        $this->user->saveLastMessage($this->currentMessage);
        // $this->user->saveLastMessage($this->clickedButton);

        // Если нажатие на кнопку     
        if (!empty($this->clickedButton)) {
            $this->processButtonClick();
        // Если текстовое сообщение
        } else if (!empty($this->currentMessage)) {
            $this->processTextMessage();
        } else {
            $this->sendMessage(
                "😔Некорректный формат сообщения. Попробуйте снова"
            );
        }

        return $this->user->save();  
    }

    private function processButtonClick()
    {
        switch($this->clickedButton) {
            case 'send':
                $this->sendMessageWithInlineKeyboard(
                    'Введите текст, который хотите отправить:',
                    'Отмена',
                    'cancel'
                );
                
                $this->user->setStepMessage(1);
            break;

            case 'watch':
                $this->sendMessage(
                    'Типо работает окда'
                );
                //TODO: Метод отправки сообщений
                $this->user->setStepMessage(0);
            break;

            default:
                $this->user->setStepMessage(0);
                $this->sendMenu();
            break;
        }
    }

    private function processTextMessage()
    {
        switch($this->user->step_message) {
            case 1:
                $this->sendMessage(
                    "Благодарю за ответ! Ваш пост улетел как птичка от пинка под зад😁"
                );

                $this->sendMenu(); 
                
                $this->user->setStepMessage(0);
            break;

            default:
                $this->sendMenu();
            break;
        }  
    }

    private function sendMenu() 
    {
        $this->sendMessageWithInlineKeyboardArray(
            "Что вы хотите сделать?",
            [
                ['text' => '📧Отправить пост', 'callback_data' => 'send'],
                ['text' => "💻Смотреть посты", 'callback_data' => 'watch'],
            ]
        );
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
