<?php

namespace App\Service;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Emailed, expiring, single-use set-password links (Phase 18 item 1) - both
 * "Forgot password?" and the admin tool's "send set-password link".
 *
 * Tokens are 32 random bytes; only their SHA-256 is stored. A link expires
 * after an hour, works once, and issuing a new one (or using one) voids the
 * user's other outstanding links.
 */
class PasswordResetService
{
    public const TTL_SECONDS = 3600;

    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $users,
        private PasswordResetTokenRepository $tokens,
        private UserPasswordHasherInterface $hasher,
        private SecurityMailer $mailer,
        private UrlGeneratorInterface $urls,
        private ClockInterface $clock,
        private RateLimiterFactoryInterface $passwordResetLimiter,
    ) {
    }

    /**
     * "Forgot password?": mail a link to every address of an active user
     * with this username. Says nothing about whether the account exists -
     * unknown, inactive, address-less and rate-limited requests all just do
     * nothing, and the caller shows the same response either way.
     */
    public function requestReset(string $username, ?string $clientIp): void
    {
        $username = trim($username);
        if ($username === '') {
            return;
        }

        $limit = $this->passwordResetLimiter->create(strtolower($username).'|'.($clientIp ?? ''))->consume();
        if (!$limit->isAccepted()) {
            return;
        }

        $user = $this->users->findOneByUsername($username);
        if ($user === null || !$user->isActive() || $user->getEmailAddresses() === []) {
            return;
        }

        $this->sendLink($user, false);
    }

    /**
     * Issue a fresh link and mail it to all of the user's addresses. Used
     * directly by the admin tool (new users get one automatically). Returns
     * false when the user has no address to send to.
     */
    public function sendLink(User $user, bool $newAccount): bool
    {
        if ($user->getEmailAddresses() === []) {
            return false;
        }

        $now = $this->now();
        $this->tokens->invalidateAllForUser($user, $now);

        $plain = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $expiresAt = $now->modify('+'.self::TTL_SECONDS.' seconds');
        $this->em->persist(new PasswordResetToken($user, self::hash($plain), $expiresAt, $now));
        $this->em->flush();

        $url = $this->urls->generate('password_reset', ['token' => $plain], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->mailer->sendPasswordLink($user, $url, $newAccount, $expiresAt);

        return true;
    }

    public function findUsableToken(string $plain): ?PasswordResetToken
    {
        if ($plain === '' || strlen($plain) > 128) {
            return null;
        }

        $token = $this->tokens->findOneByTokenHash(self::hash($plain));

        return $token !== null && $token->isUsable($this->now()) ? $token : null;
    }

    /**
     * Set the new (already policy-checked) password and burn the token and
     * every other outstanding link of the user. The new hash also
     * invalidates any "stay logged in" cookies issued before.
     */
    public function resetPassword(PasswordResetToken $token, string $plainPassword): void
    {
        $user = $token->getUser();
        $user->setPassword($this->hasher->hashPassword($user, $plainPassword));
        $this->tokens->invalidateAllForUser($user, $this->now());
        $token->markUsed($this->now());
        $this->em->flush();
    }

    public static function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }

    private function now(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromInterface($this->clock->now());
    }
}
