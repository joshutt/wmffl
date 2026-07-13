<?php

namespace App\Service;

use App\Repository\TradeOfferRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * The three plain-text trade notifications, ported from
 * processconfirm.php / finalprocess.php: offered, accepted, and
 * rejected/withdrawn/cancelled — plus the new admin-void notice.
 *
 * All go From webmaster@wmffl.com To both teams' active users, with
 * Reply-To the acting team's users, terms rendered as a sentence
 * (legacy printList: commas with a final "and").
 */
class TradeMailer
{
    private const FROM = 'webmaster@wmffl.com';
    private const TRADE_SCREEN_URL = 'https://wmffl.com/trades';

    public function __construct(
        private MailerInterface $mailer,
        private TradeOfferRepository $offers
    ) {
    }

    /** Offer made (also used for amend/counter — a new offer each time). */
    public function sendOfferEmail(array $offer, int $actingTeamId, string $comments): void
    {
        $body = "You have been offered a trade: \n\n"
            . $this->termsSentence($offer, $actingTeamId, 'offer')
            . "\n\n" . $comments . "\n\n"
            . 'To accept, reject or modify this trade please go to the trade page: '
            . self::TRADE_SCREEN_URL . '  '
            . 'This offer will expire in ' . TradeOfferRepository::EXPIRY_DAYS . ' days.';

        $this->send($offer, $actingTeamId, 'Trade Offer', $body);
    }

    public function sendAcceptedEmail(array $offer, int $actingTeamId, string $comments): void
    {
        $acting = $this->teamName($offer, $actingTeamId);
        $other = $this->teamName($offer, $this->otherTeamId($offer, $actingTeamId));

        $body = "The trade offer between $acting and $other has been accepted.\n\n"
            . $this->termsSentence($offer, $actingTeamId, 'send')
            . "\n\nComments: \n" . $comments . "\n\n";

        $this->send($offer, $actingTeamId, 'Trade Offer Accepted', $body);
    }

    /** Covers reject and withdraw (legacy used one "cancelled" message). */
    public function sendRejectedEmail(array $offer, int $actingTeamId, string $comments): void
    {
        $acting = $this->teamName($offer, $actingTeamId);
        $other = $this->teamName($offer, $this->otherTeamId($offer, $actingTeamId));

        $body = "The trade offer between $acting and $other has been cancelled by the $acting\n\n"
            . $this->termsSentence($offer, $actingTeamId, 'send')
            . "\n\nComments: \n" . $comments . "\n\n";

        $this->send($offer, $actingTeamId, 'Trade Offer Rejected', $body);
    }

    /** Admin void: both teams notified; identifies league action. */
    public function sendVoidedEmail(array $offer, string $reason): void
    {
        $body = "The trade offer between {$offer['teamAName']} and {$offer['teamBName']}"
            . " has been voided by the league commissioner.\n\n"
            . $this->termsSentence($offer, $offer['teamAId'], 'send')
            . "\n\nReason: \n" . $reason . "\n\n";

        $this->send($offer, actingTeamId: null, subject: 'Trade Offer Voided', body: $body);
    }

    // ---- assembly ----

    /**
     * "{Acting} offer|send {their terms} to the {Other} in exchange for
     * {other terms}" — the sentence shared by all the notifications.
     */
    public function termsSentence(array $offer, int $actingTeamId, string $verb): string
    {
        $otherTeamId = $this->otherTeamId($offer, $actingTeamId);

        return $this->teamName($offer, $actingTeamId)
            . " $verb "
            . self::printList($this->termItems($offer['terms'][$actingTeamId]))
            . ' to the ' . $this->teamName($offer, $otherTeamId)
            . ' in exchange for '
            . self::printList($this->termItems($offer['terms'][$otherTeamId]));
    }

    /**
     * "{Team} receive {the other side's terms}" — the respond page's
     * per-team summary (legacy processTrade.php).
     */
    public function receiveSentence(array $offer, int $receivingTeamId): string
    {
        $givingTeamId = $this->otherTeamId($offer, $receivingTeamId);

        return $this->teamName($offer, $receivingTeamId) . ' receive '
            . self::printList($this->termItems($offer['terms'][$givingTeamId]));
    }

    /**
     * One side's terms as phrases (legacy nicePrint on Player/Pick/Points).
     *
     * @param array{players: array, picks: array, points: array} $side
     * @return string[]
     */
    private function termItems(array $side): array
    {
        $items = [];
        foreach ($side['players'] as $player) {
            $items[] = "{$player['name']} ({$player['pos']}-{$player['nflteam']})";
        }
        foreach ($side['picks'] as $pick) {
            $items[] = $pick['season'] . ' ' . $pick['round'] . self::ordinalEnding($pick['round'])
                . ' round draft pick';
        }
        foreach ($side['points'] as $point) {
            $items[] = $point['points'] . ' transaction point'
                . ($point['points'] > 1 ? 's' : '') . ' in ' . $point['season'];
        }

        return $items;
    }

    /**
     * Legacy printList joining: "A", "A and B", "A, B and C".
     *
     * @param string[] $items
     */
    public static function printList(array $items): string
    {
        $count = count($items);
        if ($count <= 1) {
            return $items[0] ?? '';
        }

        return implode(', ', array_slice($items, 0, -1)) . ' and ' . $items[$count - 1];
    }

    public static function ordinalEnding(int $count): string
    {
        return match ($count) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }

    private function send(array $offer, ?int $actingTeamId, string $subject, string $body): void
    {
        $recipients = $this->offers->getActiveUserEmails([$offer['teamAId'], $offer['teamBId']]);
        if ($recipients === []) {
            return;
        }

        $email = (new Email())
            ->from(self::FROM)
            ->subject($subject)
            ->text($body);

        foreach ($recipients as $recipient) {
            $email->addTo($recipient['email']);
            if ($actingTeamId !== null && $recipient['teamId'] === $actingTeamId) {
                $email->addReplyTo($recipient['email']);
            }
        }

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface) {
            // Notification is best-effort, as legacy's @mail() was: a mail
            // outage must not fail the trade action that already happened.
        }
    }

    private function teamName(array $offer, int $teamId): string
    {
        return $teamId === $offer['teamAId'] ? $offer['teamAName'] : $offer['teamBName'];
    }

    private function otherTeamId(array $offer, int $teamId): int
    {
        return $teamId === $offer['teamAId'] ? $offer['teamBId'] : $offer['teamAId'];
    }
}
