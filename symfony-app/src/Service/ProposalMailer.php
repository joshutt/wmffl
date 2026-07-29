<?php

namespace App\Service;

use App\Entity\Issue;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Commissioner notifications for the rule-proposal workflow, ported from
 * the legacy mail() calls in proposesubmit.php and ballotcount.php.
 * Best-effort like the legacy @mail(): a mail outage never fails the
 * action that already happened.
 */
class ProposalMailer
{
    private const FROM = 'webmaster@wmffl.com';
    private const COMMISSIONER = 'commish@wmffl.com';

    public function __construct(private MailerInterface $mailer)
    {
    }

    /** A member submitted a new proposal awaiting approval. */
    public function sendProposalSubmitted(Issue $issue, string $submitterName): void
    {
        $body = "$submitterName has submitted a new rule proposal for the "
            . $issue->getSeason() . " season:\n\n"
            . $issue->getIssueName() . "\n\n"
            . ($issue->getDescription() ?? '') . "\n\n"
            . "Review and approve it at https://wmffl.com/admin/proposals";

        $this->send('New Rule Proposal', $body);
    }

    /** A cast vote pushed a proposal over its pass or fail threshold. */
    public function sendThresholdCrossed(Issue $issue, bool $passed): void
    {
        $outcome = $passed ? 'passed' : 'failed';
        $body = 'Proposal ' . $issue->getIssueNum() . ' - ' . $issue->getIssueName()
            . " has $outcome.";

        $this->send('Proposal Results', $body);
    }

    private function send(string $subject, string $body): void
    {
        $email = (new Email())
            ->from(self::FROM)
            ->to(self::COMMISSIONER)
            ->subject($subject)
            ->text($body);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface) {
            // Best-effort, as legacy's @mail() was.
        }
    }
}
