<?php

namespace App\Tests\Template;

use App\Entity\Player;
use App\Enum\PosEnum;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Renders player/profile.html.twig directly to pin the section rules: bio
 * rows only for populated fields, roster history and stats tables only when
 * data exists, and stat columns limited to activeStatColumns.
 */
class PlayerProfileTemplateTest extends TestCase
{
    private Environment $twig;

    protected function setUp(): void
    {
        // Non-strict variables so base.html.twig's app.* session lookups
        // resolve to null in the bare test environment.
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));
    }

    public function testFullProfileRendersBioRosterHistoryAndStats(): void
    {
        $html = $this->twig->render('player/profile.html.twig', [
            'player' => $this->makePlayer(),
            'currentRoster' => ['team_name' => 'Aardvarks', 'team_id' => 3, 'date_on' => '2024-09-01', 'date_off' => null],
            'rosterHistory' => [
                ['team_name' => 'Aardvarks', 'date_on' => '2024-09-01', 'date_off' => null, 'games_activated' => 12, 'active_pts' => 88],
                ['team_name' => 'Badgers/Cougars', 'date_on' => '2022-09-01', 'date_off' => '2023-11-01', 'games_activated' => 20, 'active_pts' => 140],
            ],
            'statsBySeason' => [
                ['season' => 2024, 'weeks_played' => 14, 'total_pts' => 180, 'active_pts' => 120, 'yards' => 1200, 'tds' => 9],
                ['season' => 2023, 'weeks_played' => 12, 'total_pts' => 150, 'active_pts' => 90, 'yards' => 900, 'tds' => 6],
            ],
            'activeStatColumns' => ['yards' => 'Yards', 'tds' => 'TDs'],
        ]);

        // Page heading: h1 name with a pos · number · NFL-team subtitle
        $this->assertMatchesRegularExpression('#<h1[^>]*>Steve Largent</h1>#', $html);
        $this->assertStringContainsString('WR · #80 · SEA', $html);

        // Bio
        $this->assertStringContainsString('Steve Largent', $html);
        $this->assertStringContainsString('WR', $html);
        $this->assertStringContainsString('SEA', $html);
        $this->assertStringContainsString('#80', $html);
        $this->assertStringContainsString('5\'11"', $html);
        $this->assertStringContainsString('191 lbs', $html);
        $this->assertStringContainsString('Sep 28, 1954', $html);

        // Current roster spot
        $this->assertStringContainsString('Aardvarks', $html);
        $this->assertStringNotContainsString('Free Agent', $html);

        // Roster history: open stint shows Active, closed one its end date
        $this->assertStringContainsString('WMFFL Roster History', $html);
        $this->assertStringContainsString('Badgers/Cougars', $html);
        $this->assertStringContainsString('Active', $html);
        $this->assertStringContainsString('Nov 1, 2023', $html);

        // Stats: only the active columns, plus career totals
        $this->assertStringContainsString('Stats by Season', $html);
        $this->assertStringContainsString('Yards', $html);
        $this->assertStringContainsString('TDs', $html);
        $this->assertStringNotContainsString('Tackles', $html);
        $this->assertStringContainsString('Career', $html);
        $this->assertStringContainsString('2100', $html); // career yards
        $this->assertStringContainsString('330', $html);  // career total pts
    }

    public function testEmptyProfileRendersFreeAgentWithoutHistoryOrStats(): void
    {
        $player = new Player();
        $player->setLastname('Newcomer');
        $player->setFirstname('Rookie');

        $html = $this->twig->render('player/profile.html.twig', [
            'player' => $player,
            'currentRoster' => null,
            'rosterHistory' => [],
            'statsBySeason' => [],
            'activeStatColumns' => [],
        ]);

        $this->assertMatchesRegularExpression('#<h1[^>]*>Rookie Newcomer</h1>#', $html);
        // No pos/number/NFL team means no subtitle line at all
        $this->assertStringNotContainsString('<p class="player-subtitle">', $html);
        $this->assertStringContainsString('Free Agent', $html);
        $this->assertStringNotContainsString('WMFFL Roster History', $html);
        $this->assertStringNotContainsString('Stats by Season', $html);
    }

    private function makePlayer(): Player
    {
        $player = new Player();
        $player->setLastname('Largent');
        $player->setFirstname('Steve');
        $player->setPos(PosEnum::WR);
        $player->setTeam('SEA');
        $player->setNumber(80);
        $player->setHeight(71);
        $player->setWeight(191);
        $player->setDob(new \DateTime('1954-09-28'));
        return $player;
    }
}
