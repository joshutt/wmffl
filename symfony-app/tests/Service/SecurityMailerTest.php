<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Enum\ActiveEnum;
use App\Service\SecurityMailer;
use App\Service\UserEmailService;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class SecurityMailerTest extends TestCase
{
    /** @var Email[] */
    private array $sent = [];

    public function testPasswordLinkGoesToEveryAddress(): void
    {
        $this->mailer()->sendPasswordLink($this->user(), 'https://wmffl.test/login/reset/abc', false, new \DateTimeImmutable('+1 hour'));

        $this->assertSame(['a@example.com', 'b@example.com'], $this->recipients());
        $this->assertStringContainsString('https://wmffl.test/login/reset/abc', $this->sent[0]->getTextBody());
        $this->assertStringContainsString('Reset your password', $this->sent[0]->getSubject());
    }

    public function testNewAccountLinkIsWordedAsSetPassword(): void
    {
        $this->mailer()->sendPasswordLink($this->user(), 'https://wmffl.test/x', true, new \DateTimeImmutable('+1 hour'));

        $this->assertStringContainsString('Set your password', $this->sent[0]->getSubject());
        $this->assertStringContainsString('has been set up for you', $this->sent[0]->getTextBody());
    }

    public function testEmailChangeNoticeGoesToPreviousAddresses(): void
    {
        $user = $this->user();
        $this->mailer()->sendEmailChangedNotice($user, ['old@example.com', 'a@example.com'], 'removed old@example.com');

        $this->assertSame(['old@example.com', 'a@example.com'], $this->recipients());
        $this->assertStringContainsString('removed old@example.com', $this->sent[0]->getTextBody());
    }

    public function testPasswordChangeNoticeGoesToEveryAddress(): void
    {
        $this->mailer()->sendPasswordChangedNotice($this->user());

        $this->assertSame(['a@example.com', 'b@example.com'], $this->recipients());
    }

    public function testNoAddressNoMail(): void
    {
        $this->mailer()->sendPasswordChangedNotice((new User())->setUsername('x')->setActive(ActiveEnum::N));

        $this->assertSame([], $this->sent);
    }

    public function testTransportFailureIsSwallowed(): void
    {
        $mailer = $this->createStub(MailerInterface::class);
        $mailer->method('send')->willThrowException(new TransportException('down'));
        $twig = new Environment(new FilesystemLoader(__DIR__.'/../../templates'));

        (new SecurityMailer($mailer, $twig, new NullLogger()))->sendPasswordChangedNotice($this->user());
        $this->addToAssertionCount(1);
    }

    private function mailer(): SecurityMailer
    {
        $mailer = $this->createStub(MailerInterface::class);
        $mailer->method('send')->willReturnCallback(function (Email $email) { $this->sent[] = $email; return null; });
        $twig = new Environment(new FilesystemLoader(__DIR__.'/../../templates'));

        return new SecurityMailer($mailer, $twig, new NullLogger());
    }

    private function recipients(): array
    {
        return array_map(fn (Address $a) => $a->getAddress(), $this->sent[0]->getTo());
    }

    private function user(): User
    {
        $user = (new User())->setUsername('josh')->setName('Josh')->setActive(ActiveEnum::Y);
        $service = new UserEmailService();
        $service->add($user, 'a@example.com');
        $service->add($user, 'b@example.com');

        return $user;
    }
}
