<?php

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class AccountSuspendedException extends CustomUserMessageAccountStatusException
{
}
