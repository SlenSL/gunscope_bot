<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;

use backend\helpers\BotHelper;
use backend\helpers\ValidationHelper;
use yii\helpers\Json;

use backend\bots\Bot;
use backend\models\BotUser;
 
class SiteController extends Controller
{
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

    public function actionGetPosts()
    {      
        if ($_SERVER['REQUEST_METHOD'] === 'POST') 
        {
            $postArray = Json::encode([
                [
                    'username'  => 'username1',
                    'password' => 'pass1',
                    'postText' => "post1"
                ],
                [
                    'username'  => 'username2',
                    'password' => 'pass2',
                    'postText' => "post2"
                ]
            ]);

            return $postArray;
        }

        return false;
    }

    public function actionSendPostPlug()
    {      
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}
