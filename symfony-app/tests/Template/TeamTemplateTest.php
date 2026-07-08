<?php

namespace App\Tests\Template;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Renders the team templates directly to pin the header layouts, the
 * roster/schedule/h2h/history table rules, and the index cards.
 */
class TeamTemplateTest extends TestCase
{
    private Environment $twig;

    protected function setUp(): void
    {
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));

        // path() that renders as /route_name?params, so link assertions can
        // check route + query string without a real router.
        $generator = new class implements UrlGeneratorInterface {
            public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
            {
                return "/$name" . ($parameters ? '?' . http_build_query($parameters) : '');
            }

            public function setContext(RequestContext $context): void
            {
            }

            public function getContext(): RequestContext
            {
                return new RequestContext();
            }
        };
        $this->twig->addExtension(new RoutingExtension($generator));
    }

    // ---- _header + _linkbar ----

    public function testStandardHeaderShowsNameEstablishedAndOwner(): void
    {
        $html = $this->render('team/roster.html.twig', $this->rosterParams());

        $this->assertStringContainsString('Amish Electricians', $html);
        $this->assertStringContainsString('Established 1992', $html);
        $this->assertStringContainsString('Owner: Josh Utterback', $html);
        $this->assertStringContainsString('Since 1992', $html);
        $this->assertStringContainsString('/images/teams/ae.jpg', $html);
    }

    public function testFullLogoHeaderUsesTheAlternateLayoutWithMotto(): void
    {
        $html = $this->render('team/roster.html.twig', $this->rosterParams(header: [
            'fulllogo' => true, 'motto' => 'Winning is everything',
        ]));

        $this->assertStringContainsString('Member Since 1992', $html);
        $this->assertStringContainsString('Winning is everything', $html);
        $this->assertStringNotContainsString('teamLogoBlock', $html);
    }

    public function testFullLogoFlagWithoutALogoFallsBackToTheStandardHeader(): void
    {
        $html = $this->render('team/roster.html.twig', $this->rosterParams(header: [
            'fulllogo' => true, 'logo' => null,
        ]));

        // no broken <img src="/images/teams/"> — the standard layout still
        // shows the team name as text
        $this->assertStringNotContainsString('src="/images/teams/"', $html);
        $this->assertStringContainsString('teamLogoBlock', $html);
        $this->assertStringContainsString('Amish Electricians', $html);
    }

    public function testCoOwnersGetThePluralLabel(): void
    {
        $html = $this->render('team/roster.html.twig', $this->rosterParams(header: [
            'owners' => 'Tim Shoobridge and Jon Solomon', 'owner_count' => 2,
        ]));

        $this->assertStringContainsString('Owners: Tim Shoobridge and Jon Solomon', $html);
    }

    public function testLinkbarShowsTrophiesAndHighlightsTheActivePage(): void
    {
        $html = $this->render('team/roster.html.twig', $this->rosterParams(championships: [1998, 2002]));

        $this->assertSame(2, substr_count($html, 'greystone15-2a.jpg'));
        $this->assertStringContainsString('1998', $html);
        $this->assertMatchesRegularExpression('/nav-link active"\s+href="\/team_roster\?id=2"/', $html);
        $this->assertMatchesRegularExpression('/nav-link"\s+href="\/team_history\?id=2"/', $html);
        $this->assertMatchesRegularExpression('/nav-link"\s+href="\/team_schedule\?id=2"/', $html);
    }

    // ---- roster ----

    public function testRosterTableRendersEveryColumnWithProfileLink(): void
    {
        $html = $this->render('team/roster.html.twig', $this->rosterParams(roster: [[
            'pos' => 'WR', 'lastname' => 'Largent', 'firstname' => 'Steve',
            'team' => 'SEA', 'bye' => 8, 'age' => 25, 'injury' => 'IR',
            'date_on' => '2025-09-01', 'cost' => 10, 'pts' => 100, 'playerid' => 7,
        ]]));

        $this->assertStringContainsString('id="statTable"', $html);
        $this->assertStringContainsString('/player_profile?id=7', $html);
        $this->assertStringContainsString('Largent, Steve', $html);
        $this->assertStringContainsString('<td>SEA</td>', $html);
        $this->assertStringContainsString('<td>8</td>', $html);
        $this->assertStringContainsString('<td>25</td>', $html);
        $this->assertStringContainsString('<td>IR</td>', $html);
        $this->assertStringContainsString('Sep 1, 2025', $html);
        $this->assertStringContainsString('<td>10</td>', $html);
        $this->assertStringContainsString('<td>100</td>', $html);
        // tablesorter include so the columns stay sortable
        $this->assertStringContainsString('jquery.tablesorter.min.js', $html);
        $this->assertStringContainsString('/base/js/team.js', $html);
    }

    public function testRosterColumnHeadersShowTheCostAndPtsSeasons(): void
    {
        $html = $this->render('team/roster.html.twig', $this->rosterParams());

        $this->assertStringContainsString("2026<br/>Cost", $html);
        $this->assertStringContainsString("2025<br/>Pts", $html);
    }

    public function testRosterSummaryPhrasingPositiveAndOverspent(): void
    {
        $positive = $this->render('team/roster.html.twig', $this->rosterParams(
            summary: ['remaining' => 55, 'roster_count' => 26]
        ));
        $this->assertStringContainsString('55 Remaining Free Transactions', $positive);
        $this->assertStringContainsString('26 players on roster', $positive);

        $overspent = $this->render('team/roster.html.twig', $this->rosterParams(
            summary: ['remaining' => -4, 'roster_count' => 25]
        ));
        $this->assertStringContainsString('4 extra transactions used', $overspent);
    }

    public function testEmptyRosterWithoutSummaryRendersCleanly(): void
    {
        $html = $this->render('team/roster.html.twig', $this->rosterParams(roster: [], summary: null));

        $this->assertStringContainsString('Current Roster', $html);
        $this->assertStringNotContainsString('players on roster', $html);
    }

    // ---- schedule ----

    public function testScheduleShowsResultsOnlyForCompletedWeeks(): void
    {
        $html = $this->render('team/schedule.html.twig', $this->scheduleParams(games: [
            ['weekname' => 'Week 7', 'opponent' => 'Crusaders', 'week' => 7, 'score' => 100, 'oppscore' => 90],
            ['weekname' => 'Week 8', 'opponent' => 'Norsemen', 'week' => 8, 'score' => 50, 'oppscore' => 60],
            ['weekname' => 'Week 9', 'opponent' => 'MeggaMen', 'week' => 9, 'score' => null, 'oppscore' => null],
        ], checkWeek: 8));

        $this->assertStringContainsString('WIN', $html);
        $this->assertStringContainsString('100 - 90', $html);
        // week 8 is in progress: opponent shown, no result even though scored
        $this->assertStringNotContainsString('LOSS', $html);
        $this->assertStringNotContainsString('50 - 60', $html);
        $this->assertStringContainsString('vs Norsemen', $html);
        $this->assertStringContainsString('vs MeggaMen', $html);
    }

    public function testScheduleTieAndLossLabels(): void
    {
        $html = $this->render('team/schedule.html.twig', $this->scheduleParams(games: [
            ['weekname' => 'Week 1', 'opponent' => 'Crusaders', 'week' => 1, 'score' => 90, 'oppscore' => 90],
            ['weekname' => 'Week 2', 'opponent' => 'Norsemen', 'week' => 2, 'score' => 50, 'oppscore' => 60],
        ], checkWeek: 17));

        $this->assertStringContainsString('TIE', $html);
        $this->assertStringContainsString('LOSS', $html);
    }

    public function testScheduleOffersSeasonAndHeadToHeadSelectors(): void
    {
        $html = $this->render('team/schedule.html.twig', $this->scheduleParams(
            seasons: [2025, 2024],
            opponents: [['name' => 'Jew Thing', 'teamid' => 4]]
        ));

        $this->assertStringContainsString('View previous seasons:', $html);
        $this->assertStringContainsString('<option value="2024">2024</option>', $html);
        $this->assertStringContainsString('Head-to-head vs:', $html);
        $this->assertStringContainsString('name="vs"', $html);
        $this->assertStringContainsString('<option value="4">Jew Thing</option>', $html);
        // both selectors submit to the schedule route
        $this->assertSame(2, substr_count($html, 'action="/team_schedule?id=2"'));
    }

    // ---- h2h ----

    public function testHeadToHeadShowsAggregateRecordAndGameList(): void
    {
        $html = $this->render('team/h2h.html.twig', $this->h2hParams(games: [
            ['season' => 1993, 'weekname' => 'Week 1', 'opponent' => 'Jew Thing', 'week' => 1, 'score' => 100, 'oppscore' => 90],
            ['season' => 2025, 'weekname' => 'Week 8', 'opponent' => 'Jew Thing', 'week' => 8, 'score' => 50, 'oppscore' => 60],
        ]));

        $this->assertStringContainsString('Vs Jew Thing', $html);
        $this->assertStringContainsString('(37 - 31 - 1 - 0.543)', $html);
        $this->assertStringContainsString('WIN', $html);
        $this->assertStringContainsString('100 - 90', $html);
        // current-season in-progress week hidden
        $this->assertStringNotContainsString('LOSS', $html);
        $this->assertStringNotContainsString('50 - 60', $html);
    }

    public function testHeadToHeadOpponentDropdownPreselectsTheOpponent(): void
    {
        $html = $this->render('team/h2h.html.twig', $this->h2hParams());

        $this->assertStringContainsString('<option value="4" selected>Jew Thing</option>', $html);
    }

    // ---- history ----

    public function testHistoryBoldsTheAllTimeRowFirstAndDropsTheFinishColumn(): void
    {
        $html = $this->render('team/history.html.twig', $this->historyParams());

        $this->assertMatchesRegularExpression(
            '/<tr class="font-weight-bold">\s*<td>All-Time<\/td>/',
            $html
        );
        $this->assertStringNotContainsString('FINISH', $html);
        $this->assertStringContainsString('<td>Playoffs</td>', $html);
        $this->assertStringContainsString('<td>0.556</td>', $html);
    }

    public function testHistoryPhrasesPlayoffResultsAndSplitsTitles(): void
    {
        $html = $this->render('team/history.html.twig', $this->historyParams(
            playoffResults: [
                ['event' => 'Playoffs', 'season' => 1993, 'opponent' => 'Slayers', 'myscore' => 121, 'otherscore' => 87, 'won' => true],
                ['event' => 'Championship', 'season' => 1993, 'opponent' => 'Tsunami', 'myscore' => 87, 'otherscore' => 121, 'won' => false],
            ],
            titles: ['league' => [1998], 'division' => [['season' => 1993, 'division' => 'Orange Division']]]
        ));

        $this->assertStringContainsString('Beat Slayers', $html);
        $this->assertStringContainsString('Lost to Tsunami', $html);
        $this->assertStringContainsString('121-87', $html);
        $this->assertStringContainsString('Orange Division Title', $html);
        $this->assertStringContainsString('WMFFL Champions', $html);
    }

    public function testHistoryRendersPastOwnerAndNameRanges(): void
    {
        $html = $this->render('team/history.html.twig', $this->historyParams(
            pastOwners: [
                ['start' => 1993, 'end' => 1993, 'name' => 'Tim Shoobridge and Jon Solomon'],
                ['start' => 1994, 'end' => 0, 'name' => 'Andrew Kadish'],
            ],
            pastNames: [['start' => 1993, 'end' => 1994, 'name' => 'Tsunami']]
        ));

        $this->assertStringContainsString('1993-1993', $html);
        $this->assertStringContainsString('Tim Shoobridge and Jon Solomon', $html);
        $this->assertStringContainsString('1994- current', $html);
        $this->assertStringContainsString('1993-1994', $html);
        $this->assertStringContainsString('Tsunami', $html);
    }

    // ---- index ----

    public function testIndexRendersDivisionCardsWithRosterLinks(): void
    {
        $html = $this->render('team/index.html.twig', ['divisions' => [
            'Gold Division' => [
                ['teamid' => 2, 'team' => 'Amish Electricians', 'divisionid' => 2, 'owner' => 'Josh Utterback'],
                ['teamid' => 6, 'team' => 'Crusaders', 'divisionid' => 2, 'owner' => 'Jon Hall'],
            ],
        ]]);

        $this->assertStringContainsString('bg-div-2', $html);
        $this->assertStringContainsString('Gold Division', $html);
        $this->assertStringContainsString('/team_roster?id=2', $html);
        $this->assertStringContainsString('Amish Electricians', $html);
        $this->assertStringContainsString('Josh Utterback', $html);
        $this->assertStringContainsString('/team_roster?id=6', $html);
    }

    public function testIndexDefunctAndOtherFeatureCards(): void
    {
        $html = $this->render('team/index.html.twig', ['divisions' => []]);

        $this->assertStringContainsString('/team_squirrels', $html);
        $this->assertStringContainsString('Fighting Squirrels', $html);
        // Kingsmen is a plain card entry, not a link
        $this->assertStringNotContainsString('href="/kingsmen', $html);
        $this->assertStringContainsString('Kingsmen', $html);
        $this->assertStringContainsString('/team_compare', $html);
        $this->assertStringContainsString('/transactions/displayWaiverOrder', $html);
    }

    // ---- squirrels + compare ----

    public function testSquirrelsPageRendersTheStory(): void
    {
        $html = $this->render('team/squirrels.html.twig', []);

        $this->assertStringContainsString('The Fighting Squirrels', $html);
        $this->assertStringContainsString('Andy Eckert', $html);
        $this->assertStringContainsString('5th Place - Orange Division', $html);
    }

    public function testCompareRendersSideBySideRostersWithProfileLinks(): void
    {
        $html = $this->render('team/compare.html.twig', [
            'teams' => [['name' => 'Amish Electricians', 'teamid' => 2], ['name' => 'Jew Thing', 'teamid' => 4]],
            'teamOne' => 2,
            'teamTwo' => 4,
            'rosters' => [
                'Amish Electricians' => [['name' => 'Steve Largent', 'playerid' => 7, 'pos' => 'WR', 'team' => 'SEA']],
                'Jew Thing' => [['name' => 'Jim Zorn', 'playerid' => 9, 'pos' => 'QB', 'team' => 'SEA']],
            ],
        ]);

        $this->assertStringContainsString('/player_profile?id=7', $html);
        $this->assertStringContainsString('Steve Largent', $html);
        $this->assertStringContainsString('/player_profile?id=9', $html);
        $this->assertStringContainsString('Jim Zorn', $html);
        $this->assertStringContainsString('<option value="2" selected>', $html);
    }

    public function testCompareBareFormRendersWithoutRosters(): void
    {
        $html = $this->render('team/compare.html.twig', [
            'teams' => [['name' => 'Amish Electricians', 'teamid' => 2]],
            'teamOne' => 0,
            'teamTwo' => 0,
            'rosters' => null,
        ]);

        $this->assertStringContainsString('Compare Rosters', $html);
        $this->assertStringContainsString('name="teamone"', $html);
        $this->assertStringContainsString('name="teamtwo"', $html);
    }

    // ---- helpers ----

    private function render(string $template, array $params): string
    {
        return $this->twig->render($template, $params);
    }

    private function header(array $overrides = []): array
    {
        return array_merge([
            'teamid' => 2, 'name' => 'Amish Electricians', 'member' => 1992,
            'motto' => null, 'logo' => 'ae.jpg', 'fulllogo' => false,
            'owners' => 'Josh Utterback', 'owner_count' => 1, 'owner_since' => '1992',
        ], $overrides);
    }

    private function rosterParams(
        array $header = [],
        array $championships = [],
        array $roster = [],
        ?array $summary = null
    ): array {
        return [
            'header' => $this->header($header),
            'championships' => $championships,
            'page' => 'Roster',
            'roster' => $roster,
            'summary' => $summary,
            'costSeason' => 2026,
            'ptsSeason' => 2025,
        ];
    }

    private function scheduleParams(array $games = [], int $checkWeek = 17, array $seasons = [], array $opponents = []): array
    {
        return [
            'header' => $this->header(),
            'championships' => [],
            'page' => 'Schedule',
            'season' => 2025,
            'checkWeek' => $checkWeek,
            'games' => $games,
            'seasons' => $seasons,
            'opponents' => $opponents,
        ];
    }

    private function h2hParams(array $games = []): array
    {
        return [
            'header' => $this->header(),
            'championships' => [],
            'page' => 'Schedule',
            'opponentName' => 'Jew Thing',
            'opponents' => [['name' => 'Jew Thing', 'teamid' => 4]],
            'vs' => 4,
            'games' => $games,
            'record' => ['win' => 37, 'loss' => 31, 'tie' => 1, 'pct' => '0.543'],
            'currentSeason' => 2025,
            'checkWeek' => 8,
        ];
    }

    private function historyParams(
        array $playoffResults = [],
        array $titles = ['league' => [], 'division' => []],
        array $pastOwners = [],
        array $pastNames = []
    ): array {
        return [
            'header' => $this->header(),
            'championships' => [],
            'page' => 'History',
            'records' => [
                ['label' => 'All-Time', 'win' => 14, 'lose' => 13, 'tie' => 1, 'pct' => '0.518'],
                ['label' => 'Playoffs', 'win' => 10, 'lose' => 8, 'tie' => 0, 'pct' => '0.556'],
                ['label' => '2025', 'win' => 9, 'lose' => 4, 'tie' => 1, 'pct' => '0.679'],
            ],
            'playoffResults' => $playoffResults,
            'titles' => $titles,
            'pastOwners' => $pastOwners,
            'pastNames' => $pastNames,
        ];
    }
}
