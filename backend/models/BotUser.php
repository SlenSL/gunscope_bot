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
            ['is_login', 'default', 'value' => 0],
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
        // $this->sendGetCourseRequest();

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

    public function setLogin($login)
    {
        if (!empty($login)) {
            $this->login = $login;
            return true;
        }
        
        return false;
    }

    public function setPassword($password)
    {
        if (!empty($password)) {
            $this->password = $password;
            return true;
        }

        return false;
    }

    public function isLoggedIn()
    {
        return (!empty($this->password) && !empty($this->login));
    }

    public function saveLastMessage($message)
    {
        $this->last_message = $message;
    }

    public function setLoggedAt()
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

    public function setStepLogin($step) 
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

    public function incrementStepLogin()
    {
        $this->step_login = $this->step_login + 1;
    }

    public function isTimerIsReadyByMinutes($minutes) 
    {
        return $this->last_recieved_at <= (time() - ($minutes) * 60);
    }

    public function isTimerIsReadyByHours($hours) 
    {
        return $this->last_recieved_at <= (time() - ($hours) * 60 * 60);
    }
}
