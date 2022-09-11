<?php

namespace console\controllers;

use Yii;
use yii\helpers\Console;

use backend\models\Bot;
use yii\web\HttpException;
use backend\models\Reply;
use backend\models\Request;
use backend\models\Renter;
use backend\models\RenterCatChange;
use backend\models\RequestNoreply;

class RepliesController extends \yii\console\Controller {

    public function actionIndex() 
    {
        $requests = Request::find()->where(['status' => Request::STATUS_NEW])->all();
        // $renters = Renter::find()->all();

        // foreach ($renters as $renter) {
        foreach ($requests as $request) {
            //если время запроса истекло - меняем его статус на STATUS_CLOSED
            if ($request->expire_time <= Yii::$app->formatter->asTimestamp(date('Y-d-m h:i:s')))
            {
                $request->deactivate();
                $request->update();
            } 

            $replies = Reply::find()->where(['request_id' => $request->id])->all();
            //если нет ответов, создаем модель для ответов без запросов
            if(empty($replies)) {
                $requestNoReply = new RequestNoreply($request->getPrimaryKey(), $request->title, $request->category);
                $requestNoReply->save();
            }

            $message = "Запрос: " . $request->title . "\n" . 'Ответы:' . "\n";
            foreach ($replies as $reply) {
                $message .= "|\n" . "Цена: " . $reply->price . '₽'. "\n" . "Номер торгового места: " . $reply->renter->place_num . "\n";
            }
            //-558752378
            //712226559
            Bot::sendMessage(-558752378, $message);
            if(!empty($replies)) {
                $this->updateTicketOnUseDesk($request->ticket_id, $message);
            }
        }
    }

    private function updateTicketOnUseDesk($ticket_id, $reply_text) 
    {
        $status = (empty($reply_text)) 
                    ? 8  
                    : 8; 

        $data = [
            'api_token'=> '458176c69af5a0bd67913bd0e97b37bcc19b0dba',
            'ticket_id'=> $ticket_id,
            'priority'  => 'medium',
            'status'  => $status,
            'field_id'=> '16401',
            'field_value'=> $reply_text
        ];
        $mch_api = curl_init();
        
        curl_setopt($mch_api, CURLOPT_URL, 'https://api.usedesk.ru/update/ticket');
        curl_setopt($mch_api, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
        curl_setopt($mch_api, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($mch_api, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($mch_api, CURLOPT_TIMEOUT, 10);
        curl_setopt($mch_api, CURLOPT_POST, true);
        curl_setopt($mch_api, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($mch_api, CURLOPT_POSTFIELDS, $data);
        
        $result = curl_exec($mch_api);
        $response = \yii\helpers\Json::decode($result);
        return $response['status'];
    }   
}