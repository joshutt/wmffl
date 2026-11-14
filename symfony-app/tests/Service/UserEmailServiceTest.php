<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Entity\UserEmail;
use App\Enum\ActiveEnum;
use App\Exception\UserEmailException;
use App\Service\UserEmailService;
use PHPUnit\Framework\TestCase;

class UserEmailServiceTest extends TestCase
{
    private UserEmailService $service;

    protected function setUp(): void
    {
        $this->service = new UserEmailService();
    }

    public function testFirstAddressBecomesPrimary(): void
    {
        $user = $this->user();
        $email = $this->service->add($user, ' a@example.com ');

        $this->assertTrue($email->isPrimary());
        $this->assertSame('a@example.com', $email->getEmail());
    }

    public function testLaterAddressesAreNotPrimary(): void
    {
        $user = $this->user();
        $this->service->add($user, 'a@example.com');
        $second = $this->service->add($user, 'b@example.com');

        $this->assertFalse($second->isPrimary());
        $this->assertSame('a@example.com', $user->getPrimaryEmail());
    }

    public function testSameAddressTwiceOnOneUserIsRefusedCaseInsensitively(): void
    {
        $user = $this->user();
        $this->service->add($user, 'a@example.com');

        $this->expectException(UserEmailException::class);
        $this->service->add($user, 'A@Example.com');
    }

    public function testSameAddressOnTwoUsersIsAllowed(): void
    {
        $this->service->add($this->user(), 'shared@example.com');
        $this->service->add($other = $this->user(), 'shared@example.com');

        $this->assertSame(['shared@example.com'], $other->getEmailAddresses());
    }

    public function testInvalidAddressIsRefused(): void
    {
        $this->expectException(UserEmailException::class);
        $this->service->add($this->user(), 'not-an-address');
    }

    public function testSixthAddressIsRefused(): void
    {
        $user = $this->user();
        foreach (range(1, 5) as $i) {
            $this->service->add($user, "a$i@example.com");
        }

        $this->expectException(UserEmailException::class);
        $this->expectExceptionMessage('at most 5');
        $this->service->add($user, 'a6@example.com');
    }

    public function testLastAddressOfActiveUserCannotBeRemoved(): void
    {
        $user = $this->user();
        $only = $this->service->add($user, 'a@example.com');

        $this->expectException(UserEmailException::class);
        $this->service->remove($user, $only);
    }

    public function testInactiveUserMayRemoveLastAddress(): void
    {
        $user = $this->user(ActiveEnum::N);
        $only = $this->service->add($user, 'a@example.com');
        $this->service->remove($user, $only);

        $this->assertSame([], $user->getEmailAddresses());
        $this->assertNull($user->getPrimaryEmail());
    }

    public function testRemovingPrimaryPromotesOldestRemaining(): void
    {
        $user = $this->user();
        $first = $this->add($user, 'first@example.com', '2020-01-01');
        $this->add($user, 'third@example.com', '2022-01-01');
        $this->add($user, 'second@example.com', '2021-01-01');
        $this->service->setPrimary($user, $first);

        $this->service->remove($user, $first);

        $this->assertSame('second@example.com', $user->getPrimaryEmail());
        $this->assertSame(1, $this->primaryCount($user));
    }

    public function testRemovingNonPrimaryKeepsPrimary(): void
    {
        $user = $this->user();
        $this->service->add($user, 'a@example.com');
        $b = $this->service->add($user, 'b@example.com');

        $this->service->remove($user, $b);

        $this->assertSame('a@example.com', $user->getPrimaryEmail());
    }

    public function testSetPrimaryLeavesExactlyOne(): void
    {
        $user = $this->user();
        $this->service->add($user, 'a@example.com');
        $b = $this->service->add($user, 'b@example.com');
        $this->service->add($user, 'c@example.com');

        $this->service->setPrimary($user, $b);

        $this->assertSame('b@example.com', $user->getPrimaryEmail());
        $this->assertSame(1, $this->primaryCount($user));
    }

    public function testCannotActOnAnotherUsersAddress(): void
    {
        $other = $this->user();
        $theirs = $this->service->add($other, 'x@example.com');

        $this->expectException(UserEmailException::class);
        $this->service->setPrimary($this->user(), $theirs);
    }

    public function testActiveUserWithoutAddressCannotBeActive(): void
    {
        $this->expectException(UserEmailException::class);
        $this->service->assertCanBeActive($this->user(ActiveEnum::N));
    }

    // ---- replaceAll (admin form) ----

    public function testReplaceAllKeepsSurvivingRowsAndSetsPrimary(): void
    {
        $user = $this->user();
        $kept = $this->service->add($user, 'keep@example.com');
        $this->service->add($user, 'drop@example.com');

        $this->service->replaceAll($user, ['KEEP@example.com', '', 'new@example.com'], 2, true);

        $this->assertSame(['KEEP@example.com', 'new@example.com'], $user->getEmailAddresses());
        $this->assertSame('new@example.com', $user->getPrimaryEmail());
        $this->assertTrue($user->getEmails()->contains($kept), 'same row kept (case updated), not delete + insert');
    }

    public function testReplaceAllWithoutChosenPrimaryPromotesOldest(): void
    {
        $user = $this->user();
        $this->service->replaceAll($user, ['a@example.com', 'b@example.com'], null, true);

        $this->assertSame('a@example.com', $user->getPrimaryEmail());
        $this->assertSame(1, $this->primaryCount($user));
    }

    public function testReplaceAllRefusesEmptyListForActiveUser(): void
    {
        $user = $this->user();
        $this->service->add($user, 'a@example.com');

        try {
            $this->service->replaceAll($user, ['', ''], null, true);
            $this->fail('expected refusal');
        } catch (UserEmailException) {
            $this->assertSame(['a@example.com'], $user->getEmailAddresses(), 'nothing changed');
        }
    }

    public function testReplaceAllAllowsEmptyListForInactiveUser(): void
    {
        $user = $this->user(ActiveEnum::N);
        $this->service->add($user, 'a@example.com');

        $this->service->replaceAll($user, [''], null, false);

        $this->assertSame([], $user->getEmailAddresses());
    }

    public function testReplaceAllRefusesDuplicatesAndTooMany(): void
    {
        try {
            $this->service->replaceAll($this->user(), ['a@example.com', 'A@example.com'], 0, true);
            $this->fail('duplicate accepted');
        } catch (UserEmailException) {
        }

        $this->expectException(UserEmailException::class);
        $this->service->replaceAll($this->user(), array_map(fn ($i) => "a$i@example.com", range(1, 6)), 0, true);
    }

    public function testReplaceAllRefusesInvalidAddressWithoutChangingAnything(): void
    {
        $user = $this->user();
        $this->service->add($user, 'a@example.com');

        try {
            $this->service->replaceAll($user, ['b@example.com', 'nope'], 0, true);
            $this->fail('invalid accepted');
        } catch (UserEmailException) {
            $this->assertSame(['a@example.com'], $user->getEmailAddresses());
        }
    }

    // ---- helpers ----

    private function user(ActiveEnum $active = ActiveEnum::Y): User
    {
        return (new User())->setUsername('u')->setActive($active);
    }

    private function add(User $user, string $address, string $createdAt): UserEmail
    {
        $email = new UserEmail($user, $address, new \DateTimeImmutable($createdAt));
        $user->getEmails()->add($email);

        return $email;
    }

    private function primaryCount(User $user): int
    {
        return count(array_filter($user->getEmails()->toArray(), fn (UserEmail $e) => $e->isPrimary()));
    }
}
