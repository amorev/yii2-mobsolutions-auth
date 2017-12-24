<?php

namespace Zvinger\Auth\Mobsolutions\models\user\push_device;

use yii\behaviors\TimestampBehavior;
use Zvinger\Auth\Mobsolutions\models\user\token\UserMobsolutionTokenObject;

/**
 * This is the model class for table "user_mobile_push_device".
 *
 * @property int $id
 * @property int $token_id
 * @property string $deviceType
 * @property string $deviceId
 * @property string $deviceName
 * @property string $pushToken
 * @property string $deviceVersion
 * @property string $appVersion
 * @property int $created_at
 *
 * @property UserMobsolutionTokenObject $token
 */
class UserMobilePushDeviceObject extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_mobile_push_device';
    }

    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::class,
                'updatedAtAttribute' => FALSE,
            ],
        ];
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['token_id', 'created_at'], 'integer'],
            [['deviceType', 'deviceId', 'deviceName', 'pushToken', 'deviceVersion', 'appVersion'], 'string', 'max' => 255],
            [['token_id', 'deviceId', 'deviceType'], 'unique', 'targetAttribute' => ['token_id', 'deviceId', 'deviceType']],
            [['token_id'], 'exist', 'skipOnError' => TRUE, 'targetClass' => UserMobsolutionTokenObject::className(), 'targetAttribute' => ['token_id' => 'id']],
            [['deviceId', 'deviceName', 'deviceType', 'pushToken'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'token_id'      => 'Token ID',
            'deviceType'    => 'Device Type',
            'deviceId'      => 'Device ID',
            'deviceName'    => 'Device Name',
            'pushToken'     => 'Push Token',
            'deviceVersion' => 'Device Version',
            'appVersion'    => 'App Version',
            'created_at'    => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToken()
    {
        return $this->hasOne(UserMobsolutionTokenObject::className(), ['id' => 'token_id']);
    }

    /**
     * @inheritdoc
     * @return UserMobilePushDeviceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserMobilePushDeviceQuery(get_called_class());
    }
}
