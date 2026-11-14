<?php

namespace App\Service;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

/**
 * Account security mail (Phase 18 item 1): set-password links and notices
 * of password/email changes. Goes to every address on the user (or, for an
 * email change, the addresses as they were before it). Best-effort like
 * TradeMailer: a mail outage is logged, never fails the action.
 */
class SecurityMailer
{
    private const FROM = 'webmaster@wmffl.com';

    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private LoggerInterface $logger,
    ) {
    }

    public function sendPasswordLink(User $user, string $url, bool $newAccount, \DateTimeImmutable $expiresAt): void
    {
        $this->send(
            $user->getEmailAddresses(),
            $newAccount ? 'WMFFL - Set your password' : 'WMFFL - Reset your password',
            'email/password_link.txt.twig',
            ['user' => $user, 'url' => $url, 'newAccount' => $newAccount, 'expiresAt' => $expiresAt]
        );
    }

    public function sendPasswordChangedNotice(User $user): void
    {
        $this->send(
            $user->getEmailAddresses(),
            'WMFFL - Your password was changed',
            'email/password_changed.txt.twig',
            ['user' => $user]
        );
    }

    /**
     * @param string[] $previousAddresses the addresses before the change
     */
    public function sendEmailChangedNotice(User $user, array $previousAddresses, string $change): void
    {
        $this->send(
            $previousAddresses,
            'WMFFL - Your email addresses were changed',
            'email/email_changed.txt.twig',
            ['user' => $user, 'change' => $change, 'addresses' => $user->getEmailAddresses()]
        );
    }

    /** @param string[] $to */
    private function send(array $to, string $subject, string $template, array $context): void
    {
        $to = array_values(array_unique(array_filter($to)));
        if ($to === []) {
            return;
        }

        $email = (new Email())
            ->from(self::FROM)
            ->to(...$to)
            ->subject($subject)
            ->text($this->twig->render($template, $context));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Security mail failed: '.$subject, ['exception' => $e]);
        }
    }
}
