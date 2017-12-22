<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 20.12.17
 * Time: 13:15
 */

namespace Zvinger\Auth\Mobsolutions\exceptions;

use Throwable;

class WrongAppIdMobileSolutionsAuthException extends BaseMobileSolutionsAuthException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = NULL)
    {
        $message = "This App Id is wrong";
        parent::__construct($message, $code, $previous);
    }

}