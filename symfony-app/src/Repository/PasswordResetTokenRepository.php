<?php

namespace App\Repository;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordResetToken>
 */
class PasswordResetTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetToken::class);
    }

    public function findOneByTokenHash(string $tokenHash): ?PasswordResetToken
    {
        return $this->findOneBy(['tokenHash' => $tokenHash]);
    }

    /**
     * Mark every still-unused token of this user as used, so only the newest
     * link (or none, after a successful reset) works. Does not flush.
     */
    public function invalidateAllForUser(User $user, \DateTimeImmutable $at): void
    {
        foreach ($this->findBy(['user' => $user, 'usedAt' => null]) as $token) {
            $token->markUsed($at);
        }
    }
}
