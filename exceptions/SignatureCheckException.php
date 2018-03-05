<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 05.03.18
 * Time: 15:06
 */

namespace Zvinger\Auth\Mobsolutions\exceptions;

use yii\web\HttpException;

class SignatureCheckException extends HttpException
{
    public function __construct(?string $message = NULL, int $code = 0, \Exception $previous = NULL)
    {
        $status = 401;
        parent::__construct($status, $message, $code, $previous);
    }
}