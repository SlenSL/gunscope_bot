<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;

use backend\helpers\BotHelper;
use backend\helpers\ValidationHelper;
use yii\helpers\Json;

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

    public function actionTest()
    {      
        $postData = Yii::$app->request->post();

        // echo '<pre>';
        // var_dump($postData);
        // echo '</pre>';
        
        $this->chatId = 712226559;
        $this->user = BotUser::getUser($this->chatId);
        $this->bot = new Bot('5780876936:AAGtj-8WeL-WlsE9QmzuH6URFTPxPd3EMI8', $this->chatId);
        
        // return json_decode($this->user->sendPostJson('https://andbots.ru/bot/get-posts'));
        return (string) $this->user->sendPostJson('https://andbots.ru/bot/get-posts');

        return http_response_code(200);
    }

    public function actionGetPosts()
    {        
        $postData = Yii::$app->request->post();
        // $postData =  Json::decode(Yii::$app->request->post());

        $this->chatId = 712226559;
        $this->currentMessage = 'baza';
        $this->bot = new Bot('5780876936:AAGtj-8WeL-WlsE9QmzuH6URFTPxPd3EMI8', $this->chatId);

        // $this->sendMessage(
        //     (string) $postData['username']
        // );

        return json_decode(file_get_contents("php://input"))[1]->postText; 
        // return '<pre>'.print_r(json_decode(file_get_contents("php://input")),1).'</pre>';
        // return 'a'; 
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
            '',
            $callback_id
        );

        /* Текущий пользователь */
        $this->user = BotUser::getUser($this->chatId);
        $this->user->setLastSendAt();
        $this->user->saveLastMessage($this->currentMessage);

        // Если нажатие на кнопку     
        if (!empty($this->clickedButton)) {
            $this->processButtonClick();

        // Если текстовое сообщение
        } else if (!empty($this->currentMessage)) {

            // Если не ввел лог+пароль
            if (!$this->user->isLoggedIn()) {
                $this->processLogin();
                
            // Если ввел лог+пароль
            } else {
                $this->processTextMessage();
            }
        }

        $this->user->save();  
        return http_response_code(200);
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
                $this->getPosts();

                $this->user->setStepMessage(0);
            break;

            case 'login':
                $this->sendMessageWithInlineKeyboard(
                    'Введите логин:',
                    'Отмена',
                    'cancel'
                );

                $this->user->incrementStepLogin();
            break;

            default:
                $this->user->setStepMessage(0);
                $this->sendMenu();
            break;
        }
    }

    private function getPosts()
    {
        $answer =  Json::decode($this->sendRequest('https://andbots.ru/site/get-posts'));
        $posts = json_decode($answer, true);
        
        foreach ($posts as $post) {
            $this->sendMessage(
                "{$post['postText']}\n<b>Автор: </b> {$post['username']}"
            );
        }

    }

    private function processLogin()
    {
        if (empty($this->user->login)) {
            switch($this->user->step_login) {
                case 0:
                    $this->sendMenu();
                break;

                case 1:
                    if ($username = ValidationHelper::validateUsername($this->currentMessage)) {
                        $username = $this->currentMessage;
                        $this->user->setLogin($username);
                        $this->user->incrementStepLogin();

                        $this->sendMessageWithInlineKeyboard(
                            'Введите пароль:',
                            'Отмена',
                            'cancel'
                        );
                        

                    } else {
                        $this->sendMessage(
                            "Некорректный формат логина. Он должен содержать только латинские символы и цифры."
                        );
                    }
                break;
            }
        } else if (empty($this->user->password)) {
            $this->user->setPassword(trim($this->currentMessage));
            $this->user->incrementStepLogin();
            $this->sendMessage( 
                "Успешно!"
            );
            $this->sendMenu();
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
        if ($this->user->isLoggedIn()) {
            $firstButton = ['text' => '📧Отправить пост', 'callback_data' => 'send'];
        } else {
            $firstButton = ['text' => '👨🏿‍💻Войти в аккаунт', 'callback_data' => 'login'];
        }

        $this->sendMessageWithInlineKeyboardArray(
            "Что вы хотите сделать?",
            [
                $firstButton,
                ['text' => "💻Смотреть посты", 'callback_data' => 'watch'],
            ]
        );
    }


    private function sendMessage($message) 
    {
        $this->bot->sendMessage($message);
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

    private function sendMessageWithPhoto($message, $photoUrl = null) 
    private function sendRequest($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);

        $postArray = [
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postArray);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);
         
        $res = json_encode($res, JSON_UNESCAPED_UNICODE);

        return $res;
    }
}
