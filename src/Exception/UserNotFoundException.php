<?php
declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class UserNotFoundException
 * @package App\Exception
 */
class UserNotFoundException extends NotFoundHttpException
{
}
