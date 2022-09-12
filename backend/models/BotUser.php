<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "user_visotsky".
 *
 * @property int $id
 * @property string $email
 * @property string $reasons
 * @property int|null $step_login
 * @property int|null $step_message
 * @property int|null $logged_at
 * @property int|null $first_send_at
 * @property int|null $last_send_at
 * @property int|null $last_revieved_at
 */
class BotUser extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_user';
    }

    // /**
    //  * Constructor
    //  */
    // public function __construct($chatId = null)
    // {
    //     parent::__construct();
    // }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['step_login', 'step_message', 'step_message_precise', 'logged_at', 'first_send_at', 'last_send_at', 'last_recieved_at', 'chat_id'], 'integer'],
            [['is_login'], 'integer', 'max' => 1],
            [['email', 'reasons', 'login', 'password'], 'string', 'max' => 255], 
            [['last_message'], 'string', 'max' => 6555],
        ];
    }

    public function beforeSave($insert){
        // $this->last_send_at = time();

        if($insert) {
            $this->first_send_at = time();
            $this->step_login = 0;
            $this->step_message = 0;
            $this->step_message_precise = 0;
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->sendGetCourseRequest();

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'reasons' => 'Reasons',
            'step_login' => 'Step Fill',
            'step_message' => 'Step Message',
            'logged_at' => 'Filled At',
            'first_send_at' => 'First Send At',
            'last_send_at' => 'Last Send At',
            'last_revieved_at' => 'Last Revieved At',
        ];
    }

    public static function getUser($id) 
    {
        $user = BotUser::find()->where(['chat_id' => $id])->limit(1)->one();

        if (!$user) {
            $user = new BotUser();
            $user->chat_id = $id;
            $user->save();
        }

        return $user;
    }

    public function saveLastMessage($message)
    {
        $this->last_message = $message;
    }

    public function setFilledAt()
    {
        $this->logged_at = time();
    }

    public function setRecievedAt()
    {
        $this->last_recieved_at = time();
    }

    public function setLastSendAt()
    {
        $this->last_send_at = time();
    }

    public function setStepMessage($step) 
    {
        $this->step_message = (int) $step;
    }

    public function setStepMessagePrecise($step) 
    {
        $this->step_message_precise = (int) $step;
    }

    public function incrementStepMessage()
    {
        $this->step_message = $this->step_message + 1;
    }

    public function incrementStepFill()
    {
        $this->step_login = $this->step_login + 1;
    }

    public function isTimerIsReadyByMinutes($minutes) 
    {
        // if ($this->chat_id == '5161405964') {
        //     return true;
        // }

        return $this->last_recieved_at <= (time() - ($minutes) * 60);
        // return true;
    }

    public function isTimerIsReadyByHours($hours) 
    {
        // if ($this->chat_id == '5161405964') {
        //     return true;
        // }

        return $this->last_recieved_at <= (time() - ($hours) * 60 * 60);
        // return true;
    }

    public function sendGetCourseRequest($email = null, $reason =  null, $step = null) 
    {
        if (empty($this->email)) {
            return false;
        }

        $ch = curl_init('https://ck-visotsky.ru/motivation_course/update-user.php');
        curl_setopt($ch, CURLOPT_POST, true);

        $postArray = array(
            'email'  => $this->email,
            'reason' => $this->reasons,
            'step' => $this->step_message_precise
        );

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postArray);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);
         
        // $res = json_encode($res, JSON_UNESCAPED_UNICODE);

        return $res;
    }
    
}
