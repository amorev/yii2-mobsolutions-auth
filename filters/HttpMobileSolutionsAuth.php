<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 20.12.17
 * Time: 11:18
 */

namespace Zvinger\Auth\Mobsolutions\filters;

use yii\filters\auth\AuthMethod;
use yii\web\Request;
use yii\web\Response;
use yii\web\User;
use Zvinger\Auth\Mobsolutions\components\MobileSolutionsAuthComponent;
use Zvinger\Auth\Mobsolutions\models\auth\AuthenticateData;

class HttpMobileSolutionsAuth extends AuthMethod
{
    const METHOD_SHA512 = 'sha512mob';

    /**
     * @var MobileSolutionsAuthComponent
     */
    private $_mobileSolutionsAuthComponent;

    /**
     * @return MobileSolutionsAuthComponent
     */
    private function getMobileSolutionsAuthComponent(): MobileSolutionsAuthComponent
    {
        return $this->_mobileSolutionsAuthComponent;
    }

    /**
     * @param MobileSolutionsAuthComponent $mobileSolutionsAuthComponent
     */
    public function setMobileSolutionsAuthComponent(MobileSolutionsAuthComponent $mobileSolutionsAuthComponent): void
    {
        $this->_mobileSolutionsAuthComponent = $mobileSolutionsAuthComponent;
    }

    /**
     * Authenticates the current user.
     * @param User $user
     * @param Request $request
     * @param Response $response
     * @return
     * @throws \Zvinger\Auth\Mobsolutions\exceptions\WrongAppIdMobileSolutionsAuthException
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \yii\web\UnprocessableEntityHttpException
     */
    public function authenticate($user, $request, $response)
    {
        $component = $this->getMobileSolutionsAuthComponent();
        /** @var AuthenticateData $data */
        $data = \Yii::configure(new AuthenticateData(), [
            'appId'     => \Yii::$app->request->headers->get('X-Auth-AppId'),
            'time'      => \Yii::$app->request->headers->get('X-Auth-Time'),
            'signature' => \Yii::$app->request->headers->get('X-Auth-Signature'),
            'method'    => \Yii::$app->request->headers->get('X-Auth-Method'),
            'rawBody'   => \Yii::$app->request->rawBody,
        ]);

        $identity = $component->authenticate($data);
        if ($identity === FALSE) {
            $this->handleFailure($response);
        }
        $user->login($identity);

        return $identity;
    }
}