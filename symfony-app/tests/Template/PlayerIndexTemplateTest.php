<?php

namespace App\Tests\Template;

use App\Entity\Team;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Renders player/index.html.twig directly to pin the row links, the
 * filter-preserving pagination links, and the empty-result message.
 */
class PlayerIndexTemplateTest extends TestCase
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

    public function testRowsLinkToPlayerProfiles(): void
    {
        $html = $this->render([
            'players' => [
                $this->row(7, 'Largent', 'Steve', wmfflTeam: 'Aardvarks'),
                $this->row(9, 'Zorn', 'Jim', wmfflTeam: null),
            ],
            'total' => 2,
            'totalPages' => 1,
        ]);

        $this->assertStringContainsString('/player_profile?id=7', $html);
        $this->assertStringContainsString('Steve Largent', $html);
        $this->assertStringContainsString('/player_profile?id=9', $html);
        $this->assertStringContainsString('Aardvarks', $html);
        $this->assertStringContainsString('2 players found', $html);
    }

    public function testPaginationLinksPreserveActiveFilters(): void
    {
        $html = $this->render([
            'players' => [$this->row(7, 'Largent', 'Steve')],
            'filters' => ['q' => 'larg', 'team' => '3', 'nfl' => '', 'pos' => 'WR', 'inactive' => true],
            'page' => 1,
            'total' => 150,
            'totalPages' => 3,
        ]);

        $this->assertStringContainsString('/player_index?q=larg&amp;team=3&amp;pos=WR&amp;inactive=1&amp;page=0', $html);
        $this->assertStringContainsString('/player_index?q=larg&amp;team=3&amp;pos=WR&amp;inactive=1&amp;page=2', $html);
        // Empty filters stay out of the links
        $this->assertStringNotContainsString('nfl=', $html);
    }

    public function testFirstPageHasNoPreviousLinkAndLastPageHasNoNextLink(): void
    {
        $firstPage = $this->render([
            'players' => [$this->row(7, 'Largent', 'Steve')],
            'page' => 0,
            'total' => 60,
            'totalPages' => 2,
        ]);
        $this->assertStringNotContainsString('Previous', $firstPage);
        $this->assertStringContainsString('Next', $firstPage);

        $lastPage = $this->render([
            'players' => [$this->row(7, 'Largent', 'Steve')],
            'page' => 1,
            'total' => 60,
            'totalPages' => 2,
        ]);
        $this->assertStringContainsString('Previous', $lastPage);
        $this->assertStringNotContainsString('Next', $lastPage);
    }

    public function testEmptyResultsRenderFriendlyMessage(): void
    {
        $html = $this->render(['players' => [], 'total' => 0, 'totalPages' => 0]);

        $this->assertStringContainsString('No players match your search.', $html);
        $this->assertStringNotContainsString('<tbody>', $html);
    }

    public function testFilterFormRetainsSelectionsAndListsDropdownSources(): void
    {
        $team = new Team();
        $ref = new \ReflectionProperty(Team::class, 'id');
        $ref->setValue($team, 3);
        $ref = new \ReflectionProperty(Team::class, 'name');
        $ref->setValue($team, 'Aardvarks');

        $html = $this->render([
            'players' => [$this->row(7, 'Largent', 'Steve')],
            'filters' => ['q' => 'larg', 'team' => '3', 'nfl' => 'SEA', 'pos' => 'WR', 'inactive' => true],
            'teams' => [$team],
            'nflTeams' => ['GB', 'SEA'],
            'positions' => ['K', 'WR'],
            'total' => 1,
            'totalPages' => 1,
        ]);

        $this->assertStringContainsString('value="larg"', $html);
        $this->assertStringContainsString('<option value="3" selected>Aardvarks</option>', $html);
        $this->assertStringContainsString('<option value="SEA" selected>SEA</option>', $html);
        $this->assertStringContainsString('<option value="WR" selected>WR</option>', $html);
        $this->assertStringContainsString('<option value="GB" >GB</option>', $html);
        $this->assertStringContainsString('Free Agents', $html);
        $this->assertMatchesRegularExpression('/name="inactive"[^>]*checked/', $html);
    }

    public function testClearButtonLinksBackToTheUnfilteredListing(): void
    {
        $html = $this->render([
            'players' => [$this->row(7, 'Largent', 'Steve')],
            'filters' => ['q' => 'larg', 'team' => '3', 'nfl' => 'SEA', 'pos' => 'WR', 'inactive' => true],
            'total' => 1,
            'totalPages' => 1,
        ]);

        // Bare route, no query params: resets every filter
        $this->assertStringContainsString('<a class="btn btn-wmffl" href="/player_index">Clear</a>', $html);
    }

    private function render(array $overrides): string
    {
        return $this->twig->render('player/index.html.twig', $overrides + [
            'players' => [],
            'filters' => ['q' => '', 'team' => '', 'nfl' => '', 'pos' => '', 'inactive' => false],
            'page' => 0,
            'total' => 0,
            'totalPages' => 0,
            'freeAgentValue' => 'fa',
            'teams' => [],
            'nflTeams' => [],
            'positions' => [],
        ]);
    }

    private function row(int $id, string $last, string $first, ?string $wmfflTeam = null): array
    {
        return [
            'id' => $id,
            'lastname' => $last,
            'firstname' => $first,
            'pos' => 'WR',
            'nfl_team' => 'SEA',
            'retired' => null,
            'wmffl_team' => $wmfflTeam,
        ];
    }
}
