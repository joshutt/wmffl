<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Enum\ActiveEnum;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserCheckerTest extends TestCase
{
    public function testActiveUserWithPasswordPasses(): void
    {
        (new UserChecker())->checkPreAuth($this->user(ActiveEnum::Y, md5('x')));
        $this->addToAssertionCount(1);
    }

    public function testInactiveUserIsRejectedWithGenericMessage(): void
    {
        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage('Invalid Username/Password');
        (new UserChecker())->checkPreAuth($this->user(ActiveEnum::N, md5('x')));
    }

    public function testBlankPasswordIsRejectedWithGenericMessage(): void
    {
        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage('Invalid Username/Password');
        (new UserChecker())->checkPreAuth($this->user(ActiveEnum::Y, ''));
    }

    private function user(ActiveEnum $active, string $password): User
    {
        return (new User())->setUsername('u')->setActive($active)->setPassword($password);
    }
}
