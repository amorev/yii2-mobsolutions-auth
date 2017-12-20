<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 20.12.17
 * Time: 11:18
 */

namespace Zvinger\Auth\Mobsolutions\filters;

use yii\filters\auth\AuthMethod;

class HttpMobileSolutionsAuth extends AuthMethod
{
    const METHOD_SHA512 = 'sha512mob';

    /**
     * Authenticates the current user.
     * @param User $user
     * @param Request $request
     * @param Response $response
     * @return IdentityInterface the authenticated user identity. If authentication information is not provided, null will be returned.
     * @throws UnauthorizedHttpException
     * @throws UnprocessableEntityHttpException
     */
    public function authenticate($user, $request, $response)
    {
        $body = \Yii::$app->request->rawBody;
        $appId = \Yii::$app->request->headers->get('X-Auth-AppId');
        $time = \Yii::$app->request->headers->get('X-Auth-Time');
        $signature = \Yii::$app->request->headers->get('X-Auth-Signature');
        $method = \Yii::$app->request->headers->get('X-Auth-Method');
        d($method);die;
        /** @var UserMobileIdentity $identity */
        $identity = \Yii::$app->user->loginByAccessToken($appId);
        if (empty($identity)) {
            $this->handleFailure($response);
        }
        if ($identity->status == $identity::STATUS_NOT_ACTIVE) {
            throw new UnauthorizedHttpException("Ваш пользователь еще не подтвержден");
        }
        if ($identity->status == $identity::STATUS_DELETED) {
            throw new UnauthorizedHttpException("Ваш пользователь удален");
        }
        $userMobileToken = $identity->getCurrentUserMobileToken();
        if (empty($userMobileToken)) {
            $this->handleFailure($response);
        }
        $secret = md5($userMobileToken->secret);
        $cryptBody = $body . $secret . $time;
        if ($method == self::METHOD_SHA512) {
            $crypt = hash('sha512', $cryptBody);
        } else {
            throw new UnprocessableEntityHttpException("Неизвестный метод подписи данных: " . $method);
        }
        $authResult = \Yii::$app->security->compareString($signature, $crypt);
        if (!$authResult) {
            if (true) {
                if ($turnOff && \Yii::$app->request->headers->get("BYPASS-AUTH-FOR-DEVELOP") == 1) {
                    return UserMobileIdentity::findOne(1);
                } else {
                    \Yii::$app->response->headers->add("X-Needed-Signature", $crypt);
                    $this->handleFailure($response);
                }
            } else {
                $this->handleFailure($response);
            }
        }

        return $identity;
    }
}