<?php

namespace Zvinger\Auth\Mobsolutions\models\user\token;

use app\models\work\user\object\UserObject;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "user_mobsol_token".
 *
 * @property int $id
 * @property int $user_id
 * @property string $app_id
 * @property string $secret
 * @property string $status
 * @property int $created_at
 *
 * @property User $user
 */
class UserMobsolutionTokenObject extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_mobsol_token';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at', 'status'], 'integer'],
            [['app_id', 'secret'], 'string', 'max' => 64],
            [['app_id'], 'unique'],
            [['secret'], 'unique'],
            [['user_id'],
                'exist',
                'skipOnError'     => TRUE,
                'targetClass'     => UserObject::className(),
                'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'user_id'    => 'User ID',
            'app_id'     => 'App ID',
            'secret'     => 'Secret',
            'status'     => 'Status',
            'created_at' => 'Created At',
        ];
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
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     * @return UserMobsolTokenQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserMobsolTokenQuery(get_called_class());
    }

    public static function checkAppIdValid($appId)
    {
        return !empty($appId) && static::find()->andWhere(['app_id' => $appId])->count() == 0;
    }
}
