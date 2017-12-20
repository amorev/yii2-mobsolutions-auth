<?php

namespace Zvinger\Auth\Mobsolutions\components;

use app\components\user\identity\UserIdentity;
use yii\helpers\ArrayHelper;
use yii\web\UnprocessableEntityHttpException;
use yii\web\User;
use Zvinger\Auth\Mobsolutions\exceptions\WrongAppIdMobileSolutionsAuthException;
use Zvinger\Auth\Mobsolutions\models\auth\AuthenticateData;
use Zvinger\Auth\Mobsolutions\models\user\token\UserMobsolutionTokenObject;

class MobileSolutionsAuthComponent
{
    const MOBSOL_TOKEN_KEY = 'mobsolutions_token';
    const METHOD_SHA512 = 'sha512mob';


    public $userComponentName = 'user';

    /**
     * @var UserMobsolutionTokenObject
     */
    private $_current_token_object;

    /**
     * @param AuthenticateData $authenticateData
     * @throws WrongAppIdMobileSolutionsAuthException
     * @throws UnprocessableEntityHttpException
     */
    public function authenticate(AuthenticateData $authenticateData)
    {
        $identity = $this->getIdentityByAppId($authenticateData->appId);

        if (empty($identity)) {
            throw new WrongAppIdMobileSolutionsAuthException();
        }
        $secret = $this->_current_token_object->secret;
        $hashedSecret = md5($secret);
        $cryptBody = $authenticateData->rawBody . $hashedSecret . $authenticateData->time;
        if ($authenticateData->method == self::METHOD_SHA512) {
            $crypt = hash('sha512', $cryptBody);
        } else {
            throw new UnprocessableEntityHttpException("Неизвестный метод подписи данных: " . $authenticateData->method);
        }
        $authResult = \Yii::$app->security->compareString($authenticateData->signature, $crypt);

        return $authResult ? $identity : FALSE;
    }

    /**
     * @param $appId
     * @return \yii\web\IdentityInterface|static
     * @throws WrongAppIdMobileSolutionsAuthException
     */
    private function getIdentityByAppId($appId)
    {
        $token = UserMobsolutionTokenObject::find()->byAppId($appId)->one();
        if (empty($token)) {
            throw new WrongAppIdMobileSolutionsAuthException();
        }
        $this->_current_token_object = $token;
        $user = UserIdentity::findIdentity($token->user_id);

        return $user;
    }
}