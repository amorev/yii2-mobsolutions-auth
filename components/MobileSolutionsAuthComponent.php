<?php

namespace Zvinger\Auth\Mobsolutions\components;

use app\components\user\handler\UserActivationHandler;
use app\components\user\identity\UserIdentity;
use app\models\work\user\object\UserObject;
use yii\base\BaseObject;
use yii\web\BadRequestHttpException;
use yii\web\IdentityInterface;
use yii\web\UnprocessableEntityHttpException;
use yii\web\User;
use Zvinger\Auth\Mobsolutions\exceptions\SignatureCheckException;
use Zvinger\Auth\Mobsolutions\exceptions\WrongAppIdMobileSolutionsAuthException;
use Zvinger\Auth\Mobsolutions\models\auth\AuthenticateData;
use Zvinger\Auth\Mobsolutions\models\user\token\UserMobsolutionTokenObject;
use Zvinger\BaseClasses\app\components\user\identity\VendorUserIdentity;
use Zvinger\BaseClasses\app\exceptions\model\ModelValidateException;
use Zvinger\BaseClasses\app\helpers\FunctionsHelpers;

class MobileSolutionsAuthComponent extends BaseObject
{
    const MOBSOL_TOKEN_KEY = 'mobsolutions_token';
    const METHOD_SHA512 = 'sha512mob';


    public $userComponentName = 'user';

    /**
     * @var UserMobsolutionTokenObject
     */
    private $_current_token_object;

    /** @var IdentityInterface */
    private $_current_identity;

    private $_allow_wrong_signature = FALSE;

    public function init()
    {
        if (env('ALLOW_NOW_SIGN_API') === TRUE) {
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
        MobileSolutionsLogger::info("Identity #" . $identity->getId());

        if (empty($identity)) {
            throw new WrongAppIdMobileSolutionsAuthException();
        }

        return $identity;
    }

    /**
     * @param AuthenticateData $authenticateData
     * @return bool
     * @throws UnprocessableEntityHttpException
     */
    public function checkSignature(AuthenticateData $authenticateData): bool
    {
        FunctionsHelpers::saveDebug($authenticateData, 'auth');
        $secret = $this->_current_token_object->secret;
        $hashedSecret = md5($secret);
        $cryptBody = $authenticateData->rawBody . $hashedSecret . $authenticateData->time;
        if ($authenticateData->method == self::METHOD_SHA512 || $this->_allow_wrong_signature) {
            $crypt = hash('sha512', $cryptBody);
        } else {
            throw new UnprocessableEntityHttpException("Неизвестный метод подписи данных: " . $authenticateData->method);
        }
        MobileSolutionsLogger::info("Auth info to check: " . print_r([
                'bodyToCrypt'  => $cryptBody,
                'cryptHash'    => $crypt,
                'secret'       => $secret,
                'hashedSecret' => $hashedSecret,
                'signature'    => $authenticateData->signature,
            ], 1));

        $authResult = \Yii::$app->security->compareString($authenticateData->signature, $crypt);

        return $this->_allow_wrong_signature || $authResult;
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
        $this->_current_identity = $user;

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
     * @return bool
     * @throws \Zvinger\BaseClasses\app\components\email\exceptions\EmailComponentException
     * @throws \Zvinger\BaseClasses\app\components\sms\exceptions\SmsException
     * @throws \Zvinger\Telegram\exceptions\component\NoTokenProvidedException
     * @throws \Zvinger\Telegram\exceptions\message\EmptyChatIdException
     * @throws \Zvinger\Telegram\exceptions\message\EmptyMessageTextException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function revalidateUser($user_id, $type)
    {
        $handler = (new UserActivationHandler())->setUserId($user_id);

        return $handler->handle([$type]);
    }

    /**
     * @param $phone
     * @return bool
     * @throws BadRequestHttpException
     * @throws \Zvinger\BaseClasses\app\components\email\exceptions\EmailComponentException
     * @throws \Zvinger\BaseClasses\app\components\sms\exceptions\SmsException
     * @throws \Zvinger\Telegram\exceptions\component\NoTokenProvidedException
     * @throws \Zvinger\Telegram\exceptions\message\EmptyChatIdException
     * @throws \Zvinger\Telegram\exceptions\message\EmptyMessageTextException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function revalidatePhone($phone)
    {
        $userId = UserObject::find()->where(['username' => $phone])->select('id')->scalar();
        if (empty($userId)) {
            throw new BadRequestHttpException("Такой номер телефона не зарегистрирован");
        }

        return $this->revalidateUser($userId, UserActivationHandler::ACTIVATION_PHONE);
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

    /**
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function logout()
    {
        $currentTokenObject = $this->getCurrentTokenObject();
        $currentTokenObject->delete();
    }


}