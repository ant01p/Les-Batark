<?php

namespace App\Service\Exception;

/**
 * Levée par MemberModerationService lorsqu'une action de modération est refusée
 * (auto-modération, dernier super-administrateur, niveau insuffisant...). Le message
 * est destiné à être affiché tel quel dans un flash.
 */
class MemberModerationException extends \RuntimeException
{
}
