<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 20.12.17
 * Time: 13:26
 */

namespace Zvinger\Auth\Mobsolutions\models\auth;

class AuthenticateData
{
    public $appId;

    public $time;

    public $signature;

    public $method;

    public $rawBody;
}