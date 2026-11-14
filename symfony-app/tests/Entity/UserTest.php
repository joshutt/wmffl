<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Enum\ActiveEnum;
use App\Service\UserEmailService;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testRegularUserHasRoleUserOnly(): void
    {
        $this->assertSame(['ROLE_USER'], (new User())->setCommish(false)->getRoles());
    }

    public function testCommissionerAlsoHasRoleCommish(): void
    {
        $this->assertSame(['ROLE_USER', 'ROLE_COMMISH'], (new User())->setCommish(true)->getRoles());
    }

    public function testIdentifierIsUsername(): void
    {
        $this->assertSame('josh', (new User())->setUsername('josh')->getUserIdentifier());
    }

    public function testPasswordHashStatus(): void
    {
        $this->assertSame('none', (new User())->setPassword('')->getPasswordHashStatus());
        $this->assertSame('legacy', (new User())->setPassword(md5('x'))->getPasswordHashStatus());
        $this->assertSame('modern', (new User())->setPassword(password_hash('x', PASSWORD_BCRYPT, ['cost' => 4]))->getPasswordHashStatus());
    }

    public function testSessionSerializationKeepsOnlyIdentityAndPasswordChecksum(): void
    {
        $user = (new User())->setUsername('josh')->setPassword('$2y$04$abcdefghijklmnopqrstuuJ1aW3m7')->setCommish(true)->setActive(ActiveEnum::Y);
        (new \ReflectionProperty(User::class, 'id'))->setValue($user, 7);
        (new UserEmailService())->add($user, 'josh@example.com');

        $data = $user->__serialize();
        $this->assertSame(['id', 'username', 'password', 'commish', 'active'], array_keys($data));
        $this->assertSame(8, strlen($data['password']), 'crc32c checksum, never the hash');

        /** @var User $copy */
        $copy = unserialize(serialize($user));
        $this->assertSame(7, $copy->getId());
        $this->assertSame('josh', $copy->getUserIdentifier());
        $this->assertSame([], $copy->getEmailAddresses());
    }

    public function testPrimaryEmailAndAddresses(): void
    {
        $user = (new User())->setActive(ActiveEnum::Y);
        $service = new UserEmailService();
        $service->add($user, 'a@example.com');
        $b = $service->add($user, 'b@example.com');
        $service->setPrimary($user, $b);

        $this->assertSame(['a@example.com', 'b@example.com'], $user->getEmailAddresses());
        $this->assertSame('b@example.com', $user->getPrimaryEmail());
    }
}
