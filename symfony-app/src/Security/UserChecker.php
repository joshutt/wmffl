<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Inactive users and users with a blank password can never authenticate
 * (form login or remember-me). Same generic message as a wrong password, so
 * the response doesn't reveal which accounts exist or are inactive.
 */
class UserChecker implements UserCheckerInterface
{
    public const MESSAGE = 'Invalid Username/Password';

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isActive() || (string) $user->getPassword() === '') {
            throw new CustomUserMessageAccountStatusException(self::MESSAGE);
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
    }
}
