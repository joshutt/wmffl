<?php

namespace App\Tests\Service;

use App\Repository\TradeOfferRepository;
use App\Service\TradeMailer;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AllowMockObjectsWithoutExpectations]
class TradeMailerTest extends TestCase
{
    private ?Email $sent = null;

    // ---- printList (legacy sentence joining) ----

    public function testPrintListSingleItem(): void
    {
        $this->assertSame('A', TradeMailer::printList(['A']));
    }

    public function testPrintListTwoItemsJoinedWithAnd(): void
    {
        $this->assertSame('A and B', TradeMailer::printList(['A', 'B']));
    }

    public function testPrintListThreeItemsCommasThenAnd(): void
    {
        $this->assertSame('A, B and C', TradeMailer::printList(['A', 'B', 'C']));
    }

    public function testPrintListEmpty(): void
    {
        $this->assertSame('', TradeMailer::printList([]));
    }

    // ---- sentence rendering ----

    public function testTermsSentenceRendersPlayersPicksAndPoints(): void
    {
        $mailer = $this->makeMailer();

        $sentence = $mailer->termsSentence($this->offer(), 2, 'offer');

        $this->assertSame(
            'Mustangs offer Al Kaline (WR-DET), 2027 1st round draft pick and '
            . '5 transaction points in 2026 to the Rhinos in exchange for Bo Jackson (RB-LV)',
            $sentence
        );
    }

    public function testSingularTransactionPointHasNoS(): void
    {
        $offer = $this->offer();
        $offer['terms'][2] = ['players' => [], 'picks' => [], 'points' => [
            ['season' => 2026, 'points' => 1],
        ]];

        $sentence = $this->makeMailer()->termsSentence($offer, 2, 'offer');

        $this->assertStringContainsString('1 transaction point in 2026', $sentence);
    }

    public function testOrdinalEndings(): void
    {
        $this->assertSame('st', TradeMailer::ordinalEnding(1));
        $this->assertSame('nd', TradeMailer::ordinalEnding(2));
        $this->assertSame('rd', TradeMailer::ordinalEnding(3));
        $this->assertSame('th', TradeMailer::ordinalEnding(4));
        $this->assertSame('th', TradeMailer::ordinalEnding(11));
    }

    // ---- offer email ----

    public function testOfferEmailAddressesBothTeamsWithReplyToOnlyActing(): void
    {
        $mailer = $this->makeMailer();
        $mailer->sendOfferEmail($this->offer(), 2, 'Take it or leave it');

        $this->assertNotNull($this->sent);
        $this->assertSame('webmaster@wmffl.com', $this->sent->getFrom()[0]->getAddress());
        $this->assertSame('Trade Offer', $this->sent->getSubject());

        $to = array_map(fn ($a) => $a->getAddress(), $this->sent->getTo());
        $this->assertSame(['a1@example.com', 'a2@example.com', 'b1@example.com'], $to);

        $replyTo = array_map(fn ($a) => $a->getAddress(), $this->sent->getReplyTo());
        $this->assertSame(['a1@example.com', 'a2@example.com'], $replyTo, 'only the acting team');
    }

    public function testOfferEmailBodyLinksToTheNewTradeRoute(): void
    {
        $mailer = $this->makeMailer();
        $mailer->sendOfferEmail($this->offer(), 2, 'Deal?');

        $body = $this->sent->getTextBody();
        $this->assertStringContainsString('https://wmffl.com/trades', $body);
        $this->assertStringNotContainsString('tradescreen.php', $body);
        $this->assertStringContainsString('You have been offered a trade', $body);
        $this->assertStringContainsString('Deal?', $body);
        $this->assertStringContainsString('expire in 7 days', $body);
    }

    // ---- accepted / rejected ----

    public function testAcceptedEmailNamesBothTeamsAndCarriesComments(): void
    {
        $mailer = $this->makeMailer();
        $mailer->sendAcceptedEmail($this->offer(), 5, 'Pleasure doing business');

        $this->assertSame('Trade Offer Accepted', $this->sent->getSubject());
        $body = $this->sent->getTextBody();
        $this->assertStringContainsString('between Rhinos and Mustangs has been accepted', $body);
        $this->assertStringContainsString("Comments: \nPleasure doing business", $body);

        $replyTo = array_map(fn ($a) => $a->getAddress(), $this->sent->getReplyTo());
        $this->assertSame(['b1@example.com'], $replyTo, 'acting team is the accepting side');
    }

    public function testRejectedEmailUsesTheLegacyCancelledWording(): void
    {
        $mailer = $this->makeMailer();
        $mailer->sendRejectedEmail($this->offer(), 5, 'No thanks');

        $this->assertSame('Trade Offer Rejected', $this->sent->getSubject());
        $this->assertStringContainsString(
            'between Rhinos and Mustangs has been cancelled by the Rhinos',
            $this->sent->getTextBody()
        );
    }

    public function testVoidedEmailIdentifiesLeagueActionWithNoReplyTo(): void
    {
        $mailer = $this->makeMailer();
        $mailer->sendVoidedEmail($this->offer(), 'Collusion suspected');

        $this->assertSame('Trade Offer Voided', $this->sent->getSubject());
        $body = $this->sent->getTextBody();
        $this->assertStringContainsString('voided by the league commissioner', $body);
        $this->assertStringContainsString("Reason: \nCollusion suspected", $body);
        $this->assertSame([], $this->sent->getReplyTo());
    }

    // ---- resilience ----

    public function testNoRecipientsMeansNoSendAttempt(): void
    {
        $transport = $this->createMock(MailerInterface::class);
        $transport->expects($this->never())->method('send');

        $repo = $this->createStub(TradeOfferRepository::class);
        $repo->method('getActiveUserEmails')->willReturn([]);

        (new TradeMailer($transport, $repo))->sendOfferEmail($this->offer(), 2, '');
    }

    public function testTransportFailureDoesNotBubbleUp(): void
    {
        $transport = $this->createMock(MailerInterface::class);
        $transport->method('send')->willThrowException(new TransportException('down'));

        $repo = $this->createStub(TradeOfferRepository::class);
        $repo->method('getActiveUserEmails')->willReturn([
            ['email' => 'a1@example.com', 'teamId' => 2],
        ]);

        (new TradeMailer($transport, $repo))->sendOfferEmail($this->offer(), 2, '');

        $this->addToAssertionCount(1); // no exception escaped
    }

    // ---- helpers ----

    /** Offer array in the TradeOfferRepository shape: team 2 (Mustangs) vs 5 (Rhinos) */
    private function offer(): array
    {
        return [
            'offerId' => 100,
            'teamAId' => 2,
            'teamAName' => 'Mustangs',
            'teamBId' => 5,
            'teamBName' => 'Rhinos',
            'status' => 'Pending',
            'date' => new \DateTimeImmutable('-1 day'),
            'expires' => new \DateTimeImmutable('+6 days'),
            'lastOfferTeamId' => 2,
            'prevOfferId' => null,
            'terms' => [
                2 => [
                    'players' => [['playerid' => 50, 'name' => 'Al Kaline', 'pos' => 'WR', 'nflteam' => 'DET']],
                    'picks' => [['season' => 2027, 'round' => 1, 'orgTeamId' => 2, 'orgTeamName' => 'Mustangs']],
                    'points' => [['season' => 2026, 'points' => 5]],
                ],
                5 => [
                    'players' => [['playerid' => 60, 'name' => 'Bo Jackson', 'pos' => 'RB', 'nflteam' => 'LV']],
                    'picks' => [],
                    'points' => [],
                ],
            ],
        ];
    }

    private function makeMailer(): TradeMailer
    {
        $transport = $this->createMock(MailerInterface::class);
        $transport->method('send')->willReturnCallback(function (Email $email) {
            $this->sent = $email;
        });

        $repo = $this->createStub(TradeOfferRepository::class);
        $repo->method('getActiveUserEmails')->willReturn([
            ['email' => 'a1@example.com', 'teamId' => 2],
            ['email' => 'a2@example.com', 'teamId' => 2],
            ['email' => 'b1@example.com', 'teamId' => 5],
        ]);

        return new TradeMailer($transport, $repo);
    }
}
