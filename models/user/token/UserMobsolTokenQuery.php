<?php

namespace Zvinger\Auth\Mobsolutions\models\user\token;

/**
 * This is the ActiveQuery class for [[UserMobsolutionTokenObject]].
 *
 * @see UserMobsolutionTokenObject
 */
class UserMobsolTokenQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/
    public function byAppId($appId)
    {
        return $this->andWhere(['app_id'=>$appId]);
    }

    public function byUser($userId)
    {
        return $this->andWhere(['user_id'=>$userId]);
    }

    /**
     * @inheritdoc
     * @return UserMobsolutionTokenObject[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return UserMobsolutionTokenObject|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
