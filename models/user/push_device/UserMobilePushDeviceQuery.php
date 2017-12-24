<?php

namespace Zvinger\Auth\Mobsolutions\models\user\push_device;

/**
 * This is the ActiveQuery class for [[UserMobilePushDeviceObject]].
 *
 * @see UserMobilePushDeviceObject
 */
class UserMobilePushDeviceQuery extends \yii\db\ActiveQuery
{
    public function byToken($token_id)
    {
        return $this->andWhere(['token_id'=>$token_id]);
    }

    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return UserMobilePushDeviceObject[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return UserMobilePushDeviceObject|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
