<?php

namespace backend\models\upmarket;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use backend\models\upmarket\UpmarketMailRequest as MailRequest;

/**
 * This is the model class for table "upmarket_user".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property UpmarketMailRequest[] $upmarketMailRequests
 */
class UpmarketUser extends \yii\db\ActiveRecord
{
    function __construct($chatId = null)
    {
        if ($chatId) {
            $this->id = $chatId;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'upmarket_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['status', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
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

    /**
     * Gets query for [[UpmarketMailRequests]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMailRequests()
    {
        return $this->hasMany(UpmarketMailRequest::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UpmarketMailRequests]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUnfinishedMailRequest()
    {
        return UpmarketMailRequest::find()  
                                ->where(['user_id' => $this->id])   
                                ->andWhere(['not in', 'status', [MailRequest::STATUS_PRE_SEND, MailRequest::STATUS_SENT, MailRequest::STATUS_NOT_SENT, MailRequest::STATUS_CANCELED]]) 
                                ->orderBy(['id' => SORT_DESC])  
                                ->limit(1)  
                                ->one();
    }
}
