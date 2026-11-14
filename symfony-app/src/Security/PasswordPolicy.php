<?php

namespace App\Security;

/**
 * Rules for a password a user chooses (change, reset, set). Passwords
 * carried over from the legacy site are never length-checked - whatever a
 * user has today keeps working.
 */
final class PasswordPolicy
{
    public const MIN_LENGTH = 12;

    /** @return string|null an error to show, or null when acceptable */
    public static function validate(string $new, string $confirm): ?string
    {
        if ($new !== $confirm) {
            return 'The new passwords did not match each other';
        }
        if (mb_strlen($new) < self::MIN_LENGTH) {
            return sprintf('The new password must be at least %d characters long', self::MIN_LENGTH);
        }
        if (strlen($new) > 4096) {
            return 'The new password is too long';
        }

        return null;
    }
}
