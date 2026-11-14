<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserEmail;
use App\Exception\UserEmailException;

/**
 * The one place the multi-email rules live (Phase 18 item 1), used by both
 * the account page and the admin user tool:
 *
 * - an address is a valid email, at most once per user (case-insensitive);
 *   the same address on different users is allowed and not checked;
 * - a user has at most MAX_ADDRESSES; an active user has at least one, an
 *   inactive user may have none (and can't be activated with none);
 * - whenever a user has any address, exactly one is primary; removing the
 *   primary promotes the oldest remaining address.
 *
 * Works on the User's email collection only and never flushes - the caller
 * flushes, so a primary change lands in one flush. Every rule violation is a
 * UserEmailException whose message can be shown to the user.
 */
class UserEmailService
{
    public const MAX_ADDRESSES = 5;

    public function add(User $user, string $address): UserEmail
    {
        $address = $this->normalize($address);
        $this->assertValid($address);

        if ($this->find($user, $address) !== null) {
            throw new UserEmailException(sprintf('%s is already on this account', $address));
        }
        if ($user->getEmails()->count() >= self::MAX_ADDRESSES) {
            throw new UserEmailException(sprintf('An account can have at most %d email addresses', self::MAX_ADDRESSES));
        }

        $email = new UserEmail($user, $address);
        $user->getEmails()->add($email);
        $this->ensureOnePrimary($user);

        return $email;
    }

    public function remove(User $user, UserEmail $email): void
    {
        $this->assertOwned($user, $email);

        if ($user->isActive() && $user->getEmails()->count() <= 1) {
            throw new UserEmailException('An active account must keep at least one email address');
        }

        $user->getEmails()->removeElement($email);
        if ($email->isPrimary()) {
            $email->setPrimary(false);
            $this->ensureOnePrimary($user);
        }
    }

    public function setPrimary(User $user, UserEmail $email): void
    {
        $this->assertOwned($user, $email);

        foreach ($user->getEmails() as $other) {
            $other->setPrimary($other === $email);
        }
    }

    /**
     * Refuse to activate (or keep active) a user with no address.
     */
    public function assertCanBeActive(User $user): void
    {
        if ($user->getEmails()->isEmpty()) {
            throw new UserEmailException('An active account needs at least one email address');
        }
    }

    /**
     * Make the user's address list exactly $addresses (the admin form),
     * with $addresses[$primaryIndex] primary. Rows for addresses that stay
     * are kept (preserving their age, which decides future promotion);
     * others are removed or added. Validates everything before changing
     * anything. $active is the active state the user is being saved with.
     *
     * @param string[] $addresses
     */
    public function replaceAll(User $user, array $addresses, ?int $primaryIndex, bool $active): void
    {
        $normalized = [];
        foreach ($addresses as $i => $address) {
            $address = $this->normalize((string) $address);
            if ($address === '') {
                continue;
            }
            $this->assertValid($address);
            $key = strtolower($address);
            if (isset($normalized[$key])) {
                throw new UserEmailException(sprintf('%s is listed more than once', $address));
            }
            $normalized[$key] = ['address' => $address, 'primary' => $i === $primaryIndex];
        }

        if (count($normalized) > self::MAX_ADDRESSES) {
            throw new UserEmailException(sprintf('An account can have at most %d email addresses', self::MAX_ADDRESSES));
        }
        if ($active && $normalized === []) {
            throw new UserEmailException('An active account needs at least one email address');
        }

        // Remove rows no longer listed; update the case of rows that stay
        // (a remove + re-add of the same address would trip the unique key,
        // since Doctrine inserts before it deletes).
        foreach ($user->getEmails()->toArray() as $existing) {
            $key = strtolower($existing->getEmail());
            if (!isset($normalized[$key])) {
                $user->getEmails()->removeElement($existing);
                continue;
            }
            $existing->setEmail($normalized[$key]['address']);
            $existing->setPrimary($normalized[$key]['primary']);
            unset($normalized[$key]);
        }

        foreach ($normalized as $row) {
            $email = new UserEmail($user, $row['address']);
            $email->setPrimary($row['primary']);
            $user->getEmails()->add($email);
        }

        $this->ensureOnePrimary($user);
    }

    public function find(User $user, string $address): ?UserEmail
    {
        $key = strtolower($this->normalize($address));
        foreach ($user->getEmails() as $email) {
            if (strtolower($email->getEmail()) === $key) {
                return $email;
            }
        }

        return null;
    }

    public function findById(User $user, int $id): ?UserEmail
    {
        foreach ($user->getEmails() as $email) {
            if ($email->getId() === $id) {
                return $email;
            }
        }

        return null;
    }

    /**
     * Exactly one primary whenever there are any addresses: keep the first
     * flagged one, else promote the oldest.
     */
    private function ensureOnePrimary(User $user): void
    {
        $emails = $user->getEmails()->toArray();
        if ($emails === []) {
            return;
        }

        usort($emails, static fn (UserEmail $a, UserEmail $b) => [$a->getCreatedAt(), $a->getId() ?? PHP_INT_MAX]
            <=> [$b->getCreatedAt(), $b->getId() ?? PHP_INT_MAX]);

        $primary = null;
        foreach ($emails as $email) {
            if ($email->isPrimary() && $primary === null) {
                $primary = $email;
            }
        }
        $primary ??= $emails[0];

        foreach ($emails as $email) {
            $email->setPrimary($email === $primary);
        }
    }

    private function normalize(string $address): string
    {
        return trim($address);
    }

    private function assertValid(string $address): void
    {
        if ($address === '' || strlen($address) > 255 || filter_var($address, FILTER_VALIDATE_EMAIL) === false) {
            throw new UserEmailException(sprintf('"%s" is not a valid email address', $address));
        }
    }

    private function assertOwned(User $user, UserEmail $email): void
    {
        if (!$user->getEmails()->contains($email)) {
            throw new UserEmailException('That address is not on this account');
        }
    }
}
