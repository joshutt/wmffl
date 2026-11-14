<?php

namespace App\Tests\Service;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Enum\ActiveEnum;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use App\Service\PasswordResetService;
use App\Service\SecurityMailer;
use App\Service\UserEmailService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AllowMockObjectsWithoutExpectations]
class PasswordResetServiceTest extends TestCase
{
    private MockClock $clock;
    private $em;
    private $users;
    private $tokens;
    private $hasher;
    private $mailer;
    /** @var PasswordResetToken[] */
    private array $persisted = [];
    private ?string $mailedUrl = null;

    protected function setUp(): void
    {
        $this->clock = new MockClock('2026-11-14 12:00:00');
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->em->method('persist')->willReturnCallback(function ($e) { $this->persisted[] = $e; });
        $this->users = $this->createMock(UserRepository::class);
        $this->tokens = $this->createMock(PasswordResetTokenRepository::class);
        $this->hasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->mailer = $this->createMock(SecurityMailer::class);
        $this->mailer->method('sendPasswordLink')->willReturnCallback(function ($u, string $url) { $this->mailedUrl = $url; });
    }

    public function testUnknownUsernameSendsNothing(): void
    {
        $this->users->method('findOneByUsername')->willReturn(null);
        $this->mailer->expects($this->never())->method('sendPasswordLink');

        $this->service()->requestReset('nobody', '127.0.0.1');

        $this->assertSame([], $this->persisted);
    }

    public function testInactiveUserSendsNothing(): void
    {
        $this->users->method('findOneByUsername')->willReturn($this->user(ActiveEnum::N));
        $this->mailer->expects($this->never())->method('sendPasswordLink');

        $this->service()->requestReset('josh', '127.0.0.1');
    }

    public function testActiveUserGetsHashedTokenAndMailedLink(): void
    {
        $user = $this->user();
        $this->users->method('findOneByUsername')->willReturn($user);
        $this->tokens->expects($this->once())->method('invalidateAllForUser')->with($user);
        $this->mailer->expects($this->once())->method('sendPasswordLink')->with($user, $this->anything(), false);

        $this->service()->requestReset('josh', '127.0.0.1');

        $this->assertCount(1, $this->persisted);
        $token = $this->persisted[0];
        $plain = substr($this->mailedUrl, strlen('https://wmffl.test/login/reset/'));
        $this->assertGreaterThanOrEqual(43, strlen($plain), '32 random bytes, base64url');
        $this->assertSame(hash('sha256', $plain), $token->getTokenHash(), 'only the hash is stored');
        $this->assertNotSame($plain, $token->getTokenHash());
        $this->assertEquals(new \DateTimeImmutable('2026-11-14 13:00:00'), $token->getExpiresAt());
    }

    public function testRequestsAreRateLimitedSilently(): void
    {
        $this->users->method('findOneByUsername')->willReturn($this->user());
        $this->mailer->expects($this->exactly(3))->method('sendPasswordLink');

        $service = $this->service();
        foreach (range(1, 5) as $ignored) {
            $service->requestReset('josh', '127.0.0.1');
        }
    }

    public function testSendLinkWithoutAddressReturnsFalse(): void
    {
        $user = (new User())->setUsername('josh')->setActive(ActiveEnum::N);

        $this->assertFalse($this->service()->sendLink($user, true));
        $this->assertSame([], $this->persisted);
    }

    public function testValidTokenIsUsableUntilItExpires(): void
    {
        $token = new PasswordResetToken($this->user(), hash('sha256', 'abc'), new \DateTimeImmutable('2026-11-14 13:00:00'), new \DateTimeImmutable('2026-11-14 12:00:00'));
        $this->tokens->method('findOneByTokenHash')->with(hash('sha256', 'abc'))->willReturn($token);
        $service = $this->service();

        $this->assertSame($token, $service->findUsableToken('abc'));

        $this->clock->modify('+61 minutes');
        $this->assertNull($service->findUsableToken('abc'), 'expired');
    }

    public function testUsedTokenIsNotUsable(): void
    {
        $token = new PasswordResetToken($this->user(), hash('sha256', 'abc'), new \DateTimeImmutable('2026-11-14 13:00:00'), new \DateTimeImmutable('2026-11-14 12:00:00'));
        $token->markUsed(new \DateTimeImmutable('2026-11-14 12:10:00'));
        $this->tokens->method('findOneByTokenHash')->willReturn($token);

        $this->assertNull($this->service()->findUsableToken('abc'));
    }

    public function testUnknownOrMalformedTokenIsNotUsable(): void
    {
        $this->tokens->method('findOneByTokenHash')->willReturn(null);

        $this->assertNull($this->service()->findUsableToken('abc'));
        $this->assertNull($this->service()->findUsableToken(''));
    }

    public function testResetSetsNewHashAndBurnsAllTokens(): void
    {
        $user = $this->user();
        $token = new PasswordResetToken($user, hash('sha256', 'abc'), new \DateTimeImmutable('2026-11-14 13:00:00'), new \DateTimeImmutable('2026-11-14 12:00:00'));
        $this->hasher->method('hashPassword')->with($user, 'brand-new-password')->willReturn('$2y$13$newhash');
        $this->tokens->expects($this->once())->method('invalidateAllForUser')->with($user);
        $this->em->expects($this->once())->method('flush');

        $this->service()->resetPassword($token, 'brand-new-password');

        $this->assertSame('$2y$13$newhash', $user->getPassword());
        $this->assertNotNull($token->getUsedAt());
        $this->assertFalse($token->isUsable(new \DateTimeImmutable('2026-11-14 12:30:00')), 'single use');
    }

    private function service(): PasswordResetService
    {
        $urls = $this->createStub(UrlGeneratorInterface::class);
        $urls->method('generate')->willReturnCallback(fn (string $route, array $params) => 'https://wmffl.test/login/reset/'.$params['token']);
        $limiter = new RateLimiterFactory(['id' => 'password_reset', 'policy' => 'sliding_window', 'limit' => 3, 'interval' => '1 hour'], new InMemoryStorage());

        return new PasswordResetService($this->em, $this->users, $this->tokens, $this->hasher, $this->mailer, $urls, $this->clock, $limiter);
    }

    private function user(ActiveEnum $active = ActiveEnum::Y): User
    {
        $user = (new User())->setUsername('josh')->setActive($active)->setPassword(md5('x'));
        (new UserEmailService())->add($user, 'josh@example.com');

        return $user;
    }
}
