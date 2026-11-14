<?php

namespace App\Exception;

/**
 * A user email change that breaks one of UserEmailService's rules. The
 * message is safe to show to the user as-is.
 */
class UserEmailException extends \DomainException
{
}
