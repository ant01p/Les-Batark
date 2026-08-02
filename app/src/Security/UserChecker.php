<?php

namespace App\Security;

use App\Entity\User;
use App\Security\Exception\AccountSuspendedException;
use App\Security\Exception\UnverifiedEmailException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isSuspended()) {
            throw new AccountSuspendedException('Ce compte a été suspendu.');
        }

        if (!$user->isVerified()) {
            throw new UnverifiedEmailException('Merci de confirmer votre email avant de vous connecter.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
