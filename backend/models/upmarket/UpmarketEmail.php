<?php

namespace backend\models\upmarket;

use Yii;

/**
 * This is the model class for table "upmarket_email".
 *
 * @property int $id
 * @property string $email 
 * @property int|null $status
 *
 * @property UpmarketMailRequest[] $upmarketMailRequests
 */
class UpmarketEmail extends \yii\db\ActiveRecord
{
    function __construct($email = null)
    {
        if ($email) {
            $this->email = $email;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'upmarket_email';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['email'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'status' => 'Status',
        ];
    }

    /**
     * Gets query for [[UpmarketMailRequests]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpmarketMailRequests()
    {
        return $this->hasMany(UpmarketMailRequest::className(), ['email_id' => 'id']);
    }
}
