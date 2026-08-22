<?php

namespace App\Tests\Service;

use App\Model\LineupRules;
use App\Repository\ActivationRepository;
use App\Service\ActivationService;
use App\Service\SeasonRuleService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ActivationServiceTest extends TestCase
{
    private const KICKOFF = 1762711200; // 2025-11-09 13:00 ET, the early window
    private const LATE_KICKOFF = self::KICKOFF + 3 * 3600; // the Sunday night game

    /** A legal lineup for the roster built by rosterFixture() */
    private const LEGAL = [
        'HC' => [1], 'QB' => [2], 'RB' => [3, 4], 'WR' => [5, 6], 'TE' => [7],
        'K' => [8], 'OL' => [9], 'DL' => [10, 11], 'LB' => [12, 13], 'DB' => [14, 15],
    ];

    // ---- locks ----

    public function testAPlayerIsUnlockedUntilFiveMinutesBeforeKickoff(): void
    {
        $service = $this->service();
        $player = ['kickoffTs' => self::KICKOFF];

        // 5:01 out: still editable
        $this->assertFalse($service->lockStateFor($player, $this->at(self::KICKOFF - 301)));
        // exactly 5:00 out: still editable (the lock is strictly after)
        $this->assertFalse($service->lockStateFor($player, $this->at(self::KICKOFF - 300)));
        // 4:59 out: frozen
        $this->assertTrue($service->lockStateFor($player, $this->at(self::KICKOFF - 299)));
        $this->assertTrue($service->lockStateFor($player, $this->at(self::KICKOFF + 3600)));
    }

    public function testTheOldTwoHourAndThirtyMinuteThresholdsAreGone(): void
    {
        $service = $this->service();
        $player = ['kickoffTs' => self::KICKOFF];

        $this->assertFalse($service->lockStateFor($player, $this->at(self::KICKOFF - 2 * 3600 + 1)));
        $this->assertFalse($service->lockStateFor($player, $this->at(self::KICKOFF - 30 * 60)));
    }

    public function testAPlayerWithNoGameIsNeverLocked(): void
    {
        $service = $this->service();

        $this->assertFalse($service->lockStateFor(['kickoffTs' => null], $this->at(self::KICKOFF + 99999)));
        $this->assertFalse($service->lockStateFor([], $this->at(self::KICKOFF + 99999)));
    }

    // ---- the submit view ----

    public function testEverythingLocksOnceTheLastKickoffHasPassed(): void
    {
        $view = $this->service()->buildSubmitView(2025, 10, 6, null, $this->at(self::LATE_KICKOFF + 10));

        $this->assertTrue($view['allLock']);
    }

    public function testNothingIsAllLockedWhileAKickoffIsStillAhead(): void
    {
        $view = $this->service()->buildSubmitView(2025, 10, 6, null, $this->at(self::KICKOFF - 3600));

        $this->assertFalse($view['allLock']);
    }

    public function testTheEarlyWindowLocksWithoutFreezingTheLateOne(): void
    {
        $view = $this->service()->buildSubmitView(2025, 10, 6, null, $this->at(self::KICKOFF - 120));

        $this->assertFalse($view['allLock']);
        $locks = array_column(array_merge($view['starters'], $view['reserves']), 'lock', 'playerid');
        $this->assertTrue($locks[3], 'the 1pm games are five minutes out');
        $this->assertFalse($locks[16], 'the late game is not');
    }

    public function testARosterWithNoGamesAtAllDoesNotAllLock(): void
    {
        $roster = [$this->player(1, 'HC', active: true, kickoff: null)];
        $view = $this->service($roster)->buildSubmitView(2025, 10, 6, null, $this->at(self::KICKOFF + 99999));

        $this->assertFalse($view['allLock']);
        $this->assertFalse($view['starters'][0]['lock']);
    }

    public function testStartersComeFromWhatIsStoredWhenNothingWasSubmitted(): void
    {
        $view = $this->service()->buildSubmitView(2025, 10, 6, null, $this->at(self::KICKOFF - 3600));

        $this->assertSame(
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
            array_column($view['starters'], 'playerid')
        );
        $this->assertSame([16], array_column($view['reserves'], 'playerid'));
    }

    public function testARejectedPostKeepsTheBoxesTheOwnerActuallyTicked(): void
    {
        // Owner benched 15 and started 16 instead
        $submitted = self::LEGAL;
        $submitted['DB'] = [14, 16];

        $view = $this->service()->buildSubmitView(2025, 10, 6, $submitted, $this->at(self::KICKOFF - 3600));

        $this->assertContains(16, array_column($view['starters'], 'playerid'));
        $this->assertContains(15, array_column($view['reserves'], 'playerid'));
    }

    public function testAPostDeadlineAcquisitionIsBenchedAndLocked(): void
    {
        $service = $this->service(postDeadline: [3]);
        $view = $service->buildSubmitView(2025, 10, 6, null, $this->at(self::KICKOFF - 99999));

        $benched = array_column($view['reserves'], 'lock', 'playerid');
        $this->assertArrayHasKey(3, $benched, 'a post-deadline pickup belongs in reserves');
        $this->assertTrue($benched[3], 'and cannot be activated even with kickoff hours away');
        $this->assertNotContains(3, array_column($view['starters'], 'playerid'));
    }

    public function testTheCommissionerOverrideUnlocksEverything(): void
    {
        $service = $this->service(postDeadline: [3]);
        $view = $service->buildSubmitView(2025, 10, 6, null, $this->at(self::KICKOFF + 99999), bypassLocks: true);

        $this->assertFalse($view['allLock']);
        foreach (array_merge($view['starters'], $view['reserves']) as $player) {
            $this->assertFalse($player['lock'], "player {$player['playerid']} should be editable");
        }
    }

    public function testTheActingHeadCoachPickerAppearsOnlyWhenTheTeamsOwnCoachHasNoGame(): void
    {
        $withGame = $this->service();
        $this->assertNull($withGame->buildSubmitView(2025, 10, 6, null, $this->at(self::KICKOFF - 3600))['actingHc']);

        $roster = $this->rosterFixture();
        $roster[0] = $this->player(1, 'HC', active: true, kickoff: null);
        $onBye = $this->service($roster, hcOptions: [$this->player(99, 'HC', active: false)]);

        $picker = $onBye->buildSubmitView(2025, 10, 6, null, $this->at(self::KICKOFF - 3600))['actingHc'];
        $this->assertSame([99], array_column($picker['options'], 'playerid'));
    }

    public function testNoPickerIsShownWhenNoFreeAgentCoachIsEligible(): void
    {
        $roster = $this->rosterFixture();
        $roster[0] = $this->player(1, 'HC', active: true, kickoff: null);

        $view = $this->service($roster, hcOptions: [])->buildSubmitView(2025, 10, 6, null, $this->at(self::KICKOFF));

        $this->assertNull($view['actingHc']);
    }

    // ---- saving ----

    public function testALegalLineupIsDeletedAndReinsertedInOneTransaction(): void
    {
        $statements = [];
        $service = $this->service(statements: $statements);

        $this->assertSame([], $service->save(2025, 10, 6, self::LEGAL, null, now: $this->at(self::KICKOFF - 3600)));

        $this->assertCount(2, $statements);
        $this->assertStringContainsString('DELETE FROM activations', $statements[0]['sql']);
        $this->assertSame(['season' => 2025, 'week' => 10, 'teamId' => 6], $statements[0]['params']);
        $this->assertStringContainsString('INSERT INTO activations', $statements[1]['sql']);
        $this->assertSame(15, substr_count($statements[1]['sql'], '(:season, :week, :teamId, :pos'));
        $this->assertTrue($statements[0]['inTransaction'] && $statements[1]['inTransaction']);
    }

    public function testEveryValueInTheInsertIsBoundNotInterpolated(): void
    {
        $statements = [];
        $this->service(statements: $statements)->save(2025, 10, 6, self::LEGAL, null, now: $this->at(self::KICKOFF - 3600));

        $insert = $statements[1];
        $this->assertDoesNotMatchRegularExpression('/VALUES\s*\(\d/', $insert['sql']);
        $this->assertSame(3, (int) $insert['params']['player2']);
        $this->assertSame('RB', $insert['params']['pos2']);
    }

    public function testASqlFragmentSubmittedAsAPlayeridIsRejectedAndWritesNothing(): void
    {
        $statements = [];
        $service = $this->service(statements: $statements);

        $lineup = self::LEGAL;
        $lineup['RB'] = ['1) ; DROP TABLE activations; --', 4];

        $errors = $service->save(2025, 10, 6, $lineup, null, now: $this->at(self::KICKOFF - 3600));

        $this->assertSame(['That lineup contained something that is not a player - nothing was saved.'], $errors);
        $this->assertSame([], $statements, 'a rejected lineup must not touch the database');
    }

    public function testAnIllegalLineupIsRejectedWithTheRuleMessagesAndWritesNothing(): void
    {
        $statements = [];
        $lineup = self::LEGAL;
        $lineup['WR'] = [5];
        $lineup['RB'] = [3, 4];
        $lineup['TE'] = [7];

        $errors = $this->service(statements: $statements)
            ->save(2025, 10, 6, $lineup, null, now: $this->at(self::KICKOFF - 3600));

        $this->assertContains('You must activate at least 2 WRs', $errors);
        $this->assertContains('You must activate 1 RB, 2 WR, 1 TE and 1 flex', $errors);
        $this->assertSame([], $statements);
    }

    public function testAPlayerFromAnotherTeamsRosterIsRejected(): void
    {
        $statements = [];
        $lineup = self::LEGAL;
        $lineup['RB'] = [3, 777];

        $errors = $this->service(statements: $statements)
            ->save(2025, 10, 6, $lineup, null, now: $this->at(self::KICKOFF - 3600));

        $this->assertSame(['You can only activate players on your own roster.'], $errors);
        $this->assertSame([], $statements);
    }

    public function testAPlayerCannotBeActivatedAtSomebodyElsesPosition(): void
    {
        $lineup = self::LEGAL;
        $lineup['RB'] = [3, 8]; // 8 is the kicker
        $lineup['K'] = [];

        $errors = $this->service()->save(2025, 10, 6, $lineup, null, now: $this->at(self::KICKOFF - 3600));

        $this->assertContains('Kicker Eight is a K and cannot be activated at RB.', $errors);
    }

    public function testTheSamePlayerCannotFillTwoSlots(): void
    {
        $lineup = self::LEGAL;
        $lineup['RB'] = [3, 3];

        $errors = $this->service()->save(2025, 10, 6, $lineup, null, now: $this->at(self::KICKOFF - 3600));

        $this->assertSame(['The same player cannot be activated twice.'], $errors);
    }

    public function testALockedPlayerCannotBeBenched(): void
    {
        $statements = [];
        $lineup = self::LEGAL;
        $lineup['DB'] = [14, 16];

        // Kickoff is two minutes away: player 15 is frozen where he is,
        // even though the lineup swapping him out is otherwise legal
        $errors = $this->service(statements: $statements)
            ->save(2025, 10, 6, $lineup, null, now: $this->at(self::KICKOFF - 120));

        $this->assertSame(['Back Fifteen is locked for this week and cannot be changed.'], $errors);
        $this->assertSame([], $statements);
    }

    public function testALockedPlayerCanBeLeftExactlyWhereHeIs(): void
    {
        $errors = $this->service()->save(2025, 10, 6, self::LEGAL, null, now: $this->at(self::KICKOFF - 120));

        $this->assertSame([], $errors);
    }

    public function testAPostDeadlineAcquisitionCannotBeActivatedAtAll(): void
    {
        $lineup = self::LEGAL;
        $lineup['DB'] = [14, 16];

        $errors = $this->service(postDeadline: [16])
            ->save(2025, 10, 6, $lineup, null, now: $this->at(self::KICKOFF - 99999));

        $this->assertContains('Back Sixteen is locked for this week and cannot be changed.', $errors);
    }

    public function testTheCommissionerOverrideSavesPastAnyLock(): void
    {
        $statements = [];
        $lineup = self::LEGAL;
        $lineup['DB'] = [14, 16];

        $errors = $this->service(statements: $statements, postDeadline: [16])->save(
            2025, 10, 6, $lineup, null,
            bypassLocks: true, now: $this->at(self::KICKOFF + 99999)
        );

        $this->assertSame([], $errors);
        $this->assertCount(2, $statements);
    }

    public function testTheCommissionerOverrideStillEnforcesPositionRulesByDefault(): void
    {
        $statements = [];
        $lineup = self::LEGAL;
        $lineup['DB'] = [14];

        $errors = $this->service(statements: $statements)->save(
            2025, 10, 6, $lineup, null,
            bypassLocks: true, now: $this->at(self::KICKOFF + 99999)
        );

        $this->assertSame(['You must activate exactly 2 DBs'], $errors);
        $this->assertSame([], $statements);
    }

    public function testAllowIllegalSavesAnOutOfLimitLineupOnPurpose(): void
    {
        $statements = [];
        $lineup = self::LEGAL;
        $lineup['DB'] = [14];

        $errors = $this->service(statements: $statements)->save(
            2025, 10, 6, $lineup, null,
            allowIllegal: true, bypassLocks: true, now: $this->at(self::KICKOFF + 99999)
        );

        $this->assertSame([], $errors);
        $this->assertSame(14, substr_count($statements[1]['sql'], '(:season, :week, :teamId, :pos'));
    }

    public function testAllowIllegalStillRefusesAPlayerTheTeamDoesNotOwn(): void
    {
        $lineup = self::LEGAL;
        $lineup['RB'] = [777];

        $errors = $this->service()->save(
            2025, 10, 6, $lineup, null,
            allowIllegal: true, bypassLocks: true, now: $this->at(self::KICKOFF)
        );

        $this->assertSame(['You can only activate players on your own roster.'], $errors);
    }

    public function testAnActingHeadCoachIsWrittenAsAnHcRow(): void
    {
        $statements = [];
        $lineup = self::LEGAL;
        $lineup['HC'] = [];

        $errors = $this->service(statements: $statements, hcOptions: [$this->player(99, 'HC', active: false)])
            ->save(2025, 10, 6, $lineup, 99, now: $this->at(self::KICKOFF - 3600));

        $this->assertSame([], $errors);
        $params = $statements[1]['params'];
        $this->assertSame('HC', $params['pos14']);
        $this->assertSame(99, $params['player14']);
    }

    public function testAnIneligibleActingHeadCoachIsRejected(): void
    {
        $lineup = self::LEGAL;
        $lineup['HC'] = [];

        $errors = $this->service(hcOptions: [$this->player(99, 'HC', active: false)])
            ->save(2025, 10, 6, $lineup, 98, now: $this->at(self::KICKOFF - 3600));

        $this->assertContains('That head coach is not available as an acting head coach this week.', $errors);
    }

    public function testAnActingHeadCoachCountsTowardTheOneHeadCoachSlot(): void
    {
        // The team's own coach is still ticked as well as the borrowed one
        $errors = $this->service(hcOptions: [$this->player(99, 'HC', active: false)])
            ->save(2025, 10, 6, self::LEGAL, 99, now: $this->at(self::KICKOFF - 3600));

        $this->assertSame(['You must activate exactly 1 HC'], $errors);
    }

    // ---- fixtures ----

    private function at(int $timestamp): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())->setTimestamp($timestamp);
    }

    private function player(int $id, string $pos, bool $active, ?int $kickoff = self::KICKOFF): array
    {
        $names = [
            1 => 'Coach One', 2 => 'Passer Two', 3 => 'Runner Three', 4 => 'Runner Four',
            5 => 'Catcher Five', 6 => 'Catcher Six', 7 => 'End Seven', 8 => 'Kicker Eight',
            9 => 'Line Nine', 10 => 'Rusher Ten', 11 => 'Rusher Eleven', 12 => 'Backer Twelve',
            13 => 'Backer Thirteen', 14 => 'Back Fourteen', 15 => 'Back Fifteen',
            16 => 'Back Sixteen', 99 => 'Spare Coach',
        ];

        return [
            'playerid' => $id,
            'name' => $names[$id] ?? "Player $id",
            'pos' => $pos,
            'nfl' => 'SEA',
            'opp' => $kickoff === null ? 'Bye' : 'vs ARI',
            'kickoff' => $kickoff === null ? null : '2025-11-09 13:00:00',
            'kickoffTs' => $kickoff,
            'active' => $active,
            'injuryLabel' => '',
            'injuryDetail' => '',
            'ir' => false,
        ];
    }

    /** 15 activated players plus one spare defensive back, kicking off later. */
    private function rosterFixture(): array
    {
        $spec = [
            [1, 'HC'], [2, 'QB'], [3, 'RB'], [4, 'RB'], [5, 'WR'], [6, 'WR'], [7, 'TE'],
            [8, 'K'], [9, 'OL'], [10, 'DL'], [11, 'DL'], [12, 'LB'], [13, 'LB'],
            [14, 'DB'], [15, 'DB'],
        ];
        $roster = array_map(fn (array $p) => $this->player($p[0], $p[1], active: true), $spec);
        // The bench back plays in the late window, so there is a stretch
        // where the rest of the roster is frozen and he is not
        $roster[] = $this->player(16, 'DB', active: false, kickoff: self::LATE_KICKOFF);

        return $roster;
    }

    private function service(
        ?array $roster = null,
        array $postDeadline = [],
        array $hcOptions = [],
        ?array &$statements = null
    ): ActivationService {
        $repo = $this->createStub(ActivationRepository::class);
        $repo->method('getSubmitRoster')->willReturn($roster ?? $this->rosterFixture());
        $repo->method('getPostDeadlineAcquisitions')->willReturn($postDeadline);
        $repo->method('getActingHeadCoachOptions')->willReturn($hcOptions);

        $rules = $this->createStub(SeasonRuleService::class);
        $rules->method('getLineupRules')->willReturn(LineupRules::defaults());

        return new ActivationService($repo, $rules, $this->connection($statements));
    }

    private function connection(?array &$statements): Connection
    {
        $inTransaction = false;
        $conn = $this->createMock(Connection::class);
        $conn->method('transactional')->willReturnCallback(
            function (callable $callback) use ($conn, &$inTransaction) {
                $inTransaction = true;
                try {
                    return $callback($conn);
                } finally {
                    $inTransaction = false;
                }
            }
        );
        $conn->method('executeStatement')->willReturnCallback(
            function (string $sql, array $params = []) use (&$statements, &$inTransaction) {
                $statements[] = ['sql' => $sql, 'params' => $params, 'inTransaction' => $inTransaction];

                return 1;
            }
        );

        return $conn;
    }
}
