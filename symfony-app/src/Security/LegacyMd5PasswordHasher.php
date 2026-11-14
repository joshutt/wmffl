<?php

namespace App\Security;

use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

/**
 * Verifies the legacy unsalted MD5 hashes so each user can be upgraded to
 * the modern hasher at their next login (`migrate_from` in security.yaml).
 * Never creates hashes.
 *
 * Legacy login.php ran mysqli_real_escape_string() on the password *before*
 * md5()-ing it, while forgotpassword.php stored md5(raw). Both forms are
 * accepted so nobody who can log in today is locked out (they only differ
 * for passwords containing quotes, backslashes or control characters).
 */
class LegacyMd5PasswordHasher implements PasswordHasherInterface
{
    public function hash(#[\SensitiveParameter] string $plainPassword): string
    {
        throw new \LogicException('Legacy MD5 hashes are verify-only; new hashes use the modern hasher.');
    }

    public function verify(string $hashedPassword, #[\SensitiveParameter] string $plainPassword): bool
    {
        if ($hashedPassword === '' || strlen($plainPassword) > self::MAX_PASSWORD_LENGTH) {
            return false;
        }

        $hashedPassword = strtolower($hashedPassword);

        return hash_equals($hashedPassword, md5($plainPassword))
            || hash_equals($hashedPassword, md5(self::mysqliEscape($plainPassword)));
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return true;
    }

    /**
     * What mysqli_real_escape_string() does for single-byte-safe charsets
     * (latin1/utf8): backslash-escape NUL, \n, \r, \, ', " and Ctrl-Z.
     */
    public static function mysqliEscape(string $value): string
    {
        return strtr($value, [
            "\\" => "\\\\",
            "\0" => "\\0",
            "\n" => "\\n",
            "\r" => "\\r",
            "'" => "\\'",
            '"' => '\\"',
            "\x1a" => "\\Z",
        ]);
    }
}
