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
        $this->bot = new Bot('5256166937:AAG4vebZUiiGYf0jaDgkuwGf5BCwBSj5WC0');
        
        /* Основные параметры */
        $this->postData = BotHelper::getPostData();
        
        $this->chatId = $this->postData['message']['chat']['id'];
        $this->currentMessage = trim($this->postData['message']['text']);

        $user = BotUser::getUser($this->chatId);
        
        $this->sendMessage('сосать');

        // Если текстовое сообщение
        if (BotHelper::isTextMessage($this->postData)) {

            $user->saveLastMessage($this->currentMessage);
            $user->setLastSendAt();

            switch($user->step_message) {
                case 0:
                    $user->setStepMessage(1);
                break;
    
                case 1:
                    // $user->setStepMessage(2);
                    $this->sendMessage('сосать');
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

    // private function processFill($mailRequest) 
    // {
    //     switch($mailRequest->status) {
    //         case MailRequest::STATUS_CREATED:
    //             $this->askName();
    //             $mailRequest->nextStep();
    //         break;

    //         case MailRequest::STATUS_NAME:
    //             if ($mailRequest->saveName($this->currentMessage)) {
    //                 $this->askEmail();
    //             } else {
    //                 $this->sendError();
    //             }
    //         break;

    //         case MailRequest::STATUS_EMAIL:
    //             if($mailRequest->saveEmail($this->currentMessage)) {
    //                 $this->askText();
    //             } else {
    //                 $this->sendNoSuchEmail();
    //             }
    //         break;

    //         case MailRequest::STATUS_TEXT:
    //             if($mailRequest->saveText($this->currentMessage)) {
    //                 if($mailRequest->sendEmail()) {
    //                     $this->sendSuccess();
    //                 } else {
    //                     $this->sendCouldntSendMail();
    //                 }
    //             } else {
    //                 $this->sendError();
    //             }
    //         break;
    //     }
    // } 

    private function sendMessage($message) 
    {
        $this->bot->sendMessage($this->chatId, $message);
    }

    private function sendMessageTest($message) 
    {
        $this->bot($this->chatId, $message);
    }

    private function sendMessageWithKeyboard($message, $keyboardData  = [[["text" => "Дальше"]]]) 
    {
        $keyboard = Bot::getOneTimeKeyboard($keyboardData);
        Bot::sendMessageWithKeyboard($this->chatId, $message, $keyboard);
    }

    private function sendMessageWithInlineKeyboard($message, $button_text, $callback) 
    {
        $keyboardData = [
            ['text' => $button_text, 'callback_data' => $callback],
        ];

        $keyboard = Bot::getInlineKeyBoard([$keyboardData]);
        Bot::sendMessageWithInlineKeyboard($this->chatId, $message, $keyboard);
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


        $keyboard = Bot::getInlineKeyBoard([$keyboardData]);

        if (!empty($photoUrl) && !empty($keyboardData)) {

            Bot::sendMessageWithPhotoAndKeyboard($this->chatId, $message, $keyboard, $photoUrl);

        } else if (empty($photoUrl) && !empty($keyboardData)) {

            Bot::sendMessageWithInlineKeyboard($this->chatId, $message, $keyboard);

        } else if (!empty($photoUrl) && empty($keyboardData)) {

            Bot::sendMessageWithPhoto($this->chatId, $message, $photoUrl);

        } else {

            Bot::sendMessage($this->chatId, $message);

        }
    }

    private function sendMessageWithPhoto($message, $photoUrl = null) 
    {
        if (!empty($photoUrl)) {
            Bot::sendMessageWithPhoto($this->chatId, $message, $photoUrl);
        } else {
            Bot::sendMessage($this->chatId, $message);
        }
    }
}
