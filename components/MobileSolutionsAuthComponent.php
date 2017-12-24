<?php

namespace Zvinger\Auth\Mobsolutions\components;

use app\components\user\handler\UserActivationHandler;
use app\components\user\identity\UserIdentity;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;
use yii\web\UnprocessableEntityHttpException;
use yii\web\User;
use Zvinger\Auth\Mobsolutions\exceptions\WrongAppIdMobileSolutionsAuthException;
use Zvinger\Auth\Mobsolutions\models\auth\AuthenticateData;
use Zvinger\Auth\Mobsolutions\models\user\token\UserMobsolutionTokenObject;
use Zvinger\BaseClasses\app\components\user\identity\VendorUserIdentity;
use Zvinger\BaseClasses\app\components\user\VendorUserHandlerComponent;
use Zvinger\BaseClasses\app\exceptions\model\ModelValidateException;

class MobileSolutionsAuthComponent extends BaseObject
{
    const MOBSOL_TOKEN_KEY = 'mobsolutions_token';
    const METHOD_SHA512 = 'sha512mob';


    public $userComponentName = 'user';

    /**
     * @var UserMobsolutionTokenObject
     */
    private $_current_token_object;

    private $_allow_wrong_signature = FALSE;

    public function init()
    {
        if (YII_ENV_DEV && env('ALLOW_NOW_SIGN_API') === TRUE) {
            $this->_allow_wrong_signature = TRUE;
        }
        parent::init();
    }


    /**
     * @param AuthenticateData $authenticateData
     * @return bool|IdentityInterface|VendorUserIdentity
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

        return ($authResult || $this->_allow_wrong_signature) ? $identity : FALSE;
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

    /**
     * @param $identity
     * @return UserMobsolutionTokenObject
     * @throws ModelValidateException
     * @throws \yii\base\Exception
     */
    public function loginIdentity(IdentityInterface $identity)
    {
        $tokenObject = new UserMobsolutionTokenObject();
        $tokenObject->user_id = $identity->getId();
        $appId = NULL;
        while (!$tokenObject::checkAppIdValid($appId)) {
            $appId = \Yii::$app->security->generateRandomString(16);
        }
        $tokenObject->app_id = $appId;
        $tokenObject->secret = \Yii::$app->security->generateRandomString(64);
        $tokenObject->status = $tokenObject::STATUS_ACTIVE;
        if (!$tokenObject->save()) {
            throw new ModelValidateException($tokenObject);
        }

        return $tokenObject;
    }

    /**
     * @param $user_id
     * @param $type
     * @param $code
     * @return bool
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function confirmUser($user_id, $type, $code)
    {
        $handler = (new UserActivationHandler())->setUserId($user_id);

        return $handler->activate($type, $code);
    }

    /**
     * @param $user_id
     * @param $type
     * @return void
     * @throws \Zvinger\Telegram\exceptions\component\NoTokenProvidedException
     * @throws \Zvinger\Telegram\exceptions\message\EmptyChatIdException
     * @throws \Zvinger\Telegram\exceptions\message\EmptyMessageTextException
     * @throws \yii\base\InvalidConfigException
     */
    public function revalidateUser($user_id, $type)
    {
        $handler = (new UserActivationHandler())->setUserId($user_id);

        return $handler->handle([$type]);
    }


    /**
     * @var User
     */
    private $_user_component;

    public function getUserComponent()
    {
        if (empty($this->_user_component)) {
            $this->_user_component = \Yii::$app->get($this->userComponentName);
        }

        return $this->_user_component;
    }

    /**
     * @return UserMobsolutionTokenObject
     */
    public function getCurrentTokenObject(): UserMobsolutionTokenObject
    {
        return $this->_current_token_object;
    }
}