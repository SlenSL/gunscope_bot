<?php

namespace backend\models\upmarket;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "upmarket_mail_request".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $email_id
 * @property string $sender_name
 * @property string $text
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property UpmarketEmail $email
 * @property UpmarketUser $user
 */
class UpmarketMailRequest extends \yii\db\ActiveRecord
{
    const STATUS_CREATED = 0;
    const STATUS_NAME = 1;
    const STATUS_EMAIL = 2;
    const STATUS_TEXT = 3;
    const STATUS_PRE_SEND = 4;
    const STATUS_SENT = 5;
    const STATUS_NOT_SENT = 6;
    const STATUS_CANCELED = 7;

    function __construct($userId = null, $emailId = null)
    {
            $this->user_id = $userId;
            $this->email_id = $emailId;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'upmarket_mail_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'email_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['sender_name', 'text'], 'string'],
            [['email_id'], 'exist', 'skipOnError' => true, 'targetClass' => UpmarketEmail::className(), 'targetAttribute' => ['email_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => UpmarketUser::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'email_id' => 'Email ID',
            'sender_name' => 'Sender Name',
            'text' => 'Text',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    public function prevStep() 
    {
        if ($this->status == 1) {
            $this->status = $this->status - 1;
        } else if($this->status === 2) {
            $this->status = $this->status - 1;
        } else if($this->status === 3) {
            $this->status = $this->status - 1;
        }
        // else if($this->status > 0) {
        //     $this->status = $this->status - 1;
        // }

        return $this->save();

    }

    public function nextStep()
    {
        $this->status = $this->status + 1;
        return $this->save();
    }

    public function cancelRequest() 
    {
        $this->status = self::STATUS_CANCELED;
        $this->save();

        return $this->delete();
    }

    public function saveName($string)
    {
        $this->sender_name = $string;
        $this->nextStep();

        return $this->save();
    }

    public function saveEmail($string) 
    {
        $email = trim($string);

        $emailModel = UpmarketEmail::find()->where(['email' => $email])->limit(1)->one();
        if(!$emailModel) {
            return false;
        }

        $this->email_id = $emailModel->id;
        $this->nextStep();
        
        return $this->save();
    }

    public function saveText($string) 
    {
        $this->text = $string;
        $this->nextStep();

        return $this->save();
    }

    public function sendEmail()
    {
        $isSent =  Yii::$app->mailer->compose()
                    ->setFrom(['upmarketbot@gmail.com' => 'UpmarketСпасибо'])
                    ->setTo($this->getEmailString())
                    ->setSubject("Вам благодарность от {$this->sender_name}")
                    ->setTextBody($this->text)
                    ->send();

        if ($isSent) {
            $this->status = self::STATUS_SENT;
        } else {
            $this->status = self::STATUS_NOT_SENT;
        }
        $this->save();

        return $isSent;
    }

    /**
     * Gets query for [[Email]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmail()
    {
        return $this->hasOne(UpmarketEmail::className(), ['id' => 'email_id']);
    }

    /**
     * Gets query for [[Email]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmailString()
    {
        return $this->email->email;
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(UpmarketUser::className(), ['id' => 'user_id']);
    }
}
