<?php

namespace App\Tests\Template;

use App\Model\LineupRules;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Renders activations/_lineup_form.html.twig directly. This partial is
 * shared by the member submit form and the commissioner override, so a
 * change that breaks one silently breaks the other.
 */
class LineupFormTemplateTest extends TestCase
{
    private Environment $twig;

    protected function setUp(): void
    {
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));

        $generator = new class implements UrlGeneratorInterface {
            public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
            {
                return "/$name";
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

    public function testAnUnlockedStarterGetsATickedCheckboxNamedForHisPosition(): void
    {
        $html = $this->render(starters: [$this->player(3, 'RB')]);

        $this->assertStringContainsString('name="RB[]" value="3"', $html);
        $this->assertStringContainsString('checked="checked"', $html);
        $this->assertStringNotContainsString('lock-clipart2', $html);
    }

    public function testAnUnlockedReserveGetsAnUntickedCheckbox(): void
    {
        $html = $this->render(reserves: [$this->player(3, 'RB')]);

        $this->assertStringContainsString('name="RB[]" value="3"', $html);
        $this->assertStringNotContainsString('checked="checked"', $html);
    }

    /**
     * A locked starter keeps his slot through a hidden input, so a save
     * cannot silently bench him; a locked reserve gets no input at all,
     * so he cannot be slipped into the lineup.
     */
    public function testALockedStarterRidesAlongAsAHiddenInputAndALockedReserveHasNoInput(): void
    {
        $starter = $this->render(starters: [$this->player(3, 'RB', lock: true)]);
        $this->assertStringContainsString('type="hidden" class="lineup-locked"', $starter);
        $this->assertStringContainsString('name="RB[]" value="3"', $starter);
        $this->assertStringContainsString('lock-clipart2', $starter);
        $this->assertStringNotContainsString('type="checkbox"', $starter);

        $reserve = $this->render(reserves: [$this->player(3, 'RB', lock: true)]);
        $this->assertStringNotContainsString('name="RB[]"', $reserve);
        $this->assertStringContainsString('lock-clipart2', $reserve);
    }

    public function testTheTeamWideLockFreezesEveryRow(): void
    {
        $html = $this->render(
            starters: [$this->player(3, 'RB')],
            reserves: [$this->player(4, 'RB')],
            allLock: true
        );

        $this->assertStringNotContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('type="hidden" class="lineup-locked"', $html);
    }

    public function testAPostDeadlineAcquisitionSaysWhyHeIsBenched(): void
    {
        $html = $this->render(reserves: [$this->player(3, 'RB', lock: true, postDeadline: true)]);

        $this->assertStringContainsString('week 14 activation deadline', $html);
    }

    public function testTheCountersReadTheSeasonsOwnLimits(): void
    {
        $html = $this->render(rules: LineupRules::fromArray(['WR' => ['min' => 2, 'max' => 4], 'flex_total' => 6]));

        $this->assertMatchesRegularExpression('/data-lineup-rules="[^"]*WR/', $html);
        $decoded = json_decode(html_entity_decode($this->attribute($html)), true);
        $this->assertSame(['min' => 2, 'max' => 4], $decoded['positions']['WR']);
        $this->assertSame(6, $decoded['flexTotal']);
    }

    public function testTheActingHeadCoachPickerListsOnlyTheEligibleCoaches(): void
    {
        $html = $this->render(actingHc: [
            'options' => [
                ['playerid' => 99, 'name' => 'Spare Coach', 'nfl' => 'SEA', 'opp' => 'vs ARI'],
                ['playerid' => 98, 'name' => 'Other Coach', 'nfl' => 'BUF', 'opp' => '@ MIA'],
            ],
            'selected' => 98,
            'checked' => true,
        ]);

        $this->assertStringContainsString('name="actHC"', $html);
        $this->assertStringContainsString('<option value="99">', $html);
        $this->assertStringContainsString('<option value="98" selected>', $html);
    }

    public function testNoPickerMarkupAtAllWhenThereIsNoActingHeadCoach(): void
    {
        $html = $this->render(starters: [$this->player(1, 'HC')]);

        $this->assertStringNotContainsString('actHC', $html);
    }

    // ---- the drag-and-drop contract with /js/activations.js ----

    public function testStartersAndReservesAreSeparateDropTargets(): void
    {
        $html = $this->render(
            starters: [$this->player(3, 'RB')],
            reserves: [$this->player(4, 'RB')]
        );

        $this->assertStringContainsString('class="lineup-list" id="lineup-starters"', $html);
        $this->assertStringContainsString('class="lineup-list" id="lineup-reserves"', $html);
    }

    public function testEveryDraggableRowIdentifiesItsPlayerAndPosition(): void
    {
        $html = $this->render(starters: [$this->player(3, 'RB')]);

        $this->assertStringContainsString('data-playerid="3" data-pos="RB"', $html);
        $this->assertStringContainsString('lineup-grip', $html);
    }

    /**
     * The script filters these out of the drag layer, because a locked
     * player cannot change state and the acting-HC row is not a player.
     */
    public function testRowsThatMustNotMoveAreMarkedForTheDragFilter(): void
    {
        $locked = $this->render(starters: [$this->player(3, 'RB', lock: true)]);
        $this->assertStringContainsString('lineup-row-locked', $locked);

        $actingHc = $this->render(actingHc: [
            'options' => [['playerid' => 99, 'name' => 'Spare Coach', 'nfl' => 'SEA', 'opp' => 'vs ARI']],
            'selected' => 99,
            'checked' => true,
        ]);
        $this->assertStringContainsString('lineup-row-fixed', $actingHc);
    }

    public function testAnUnlockedRowIsNotMarkedForTheDragFilter(): void
    {
        $html = $this->render(starters: [$this->player(3, 'RB')]);

        $this->assertStringNotContainsString('lineup-row-locked', $html);
    }

    /**
     * The empty-list message is rendered outside the drop target (so it
     * never becomes a draggable item) and hidden when the list has rows.
     */
    public function testTheEmptyListMessageIsHiddenWhenTheListHasPlayers(): void
    {
        $html = $this->render(starters: [$this->player(3, 'RB')], reserves: []);

        $this->assertMatchesRegularExpression(
            '/data-list="lineup-starters"\s+hidden/', $html,
            'a populated starters list hides its placeholder'
        );
        $this->assertMatchesRegularExpression(
            '/data-list="lineup-reserves"\s*>/', $html,
            'an empty reserves list shows its placeholder'
        );
    }

    public function testTheActingHeadCoachCountsAsAStarterForTheEmptyMessage(): void
    {
        $html = $this->render(actingHc: [
            'options' => [['playerid' => 99, 'name' => 'Spare Coach', 'nfl' => 'SEA', 'opp' => 'vs ARI']],
            'selected' => 99,
            'checked' => true,
        ]);

        $this->assertMatchesRegularExpression('/data-list="lineup-starters"\s+hidden/', $html);
    }

    /** Without JS there is no dragging, so the hint stays hidden */
    public function testTheDragHintStartsHiddenForTheNoJavascriptCase(): void
    {
        $html = $this->render(starters: [$this->player(3, 'RB')]);

        $this->assertMatchesRegularExpression('/lineup-hint d-none/', $html);
    }

    /**
     * The browser re-sorts the lists after a drag and has to arrive at
     * the same order the roster query did (pos, then surname). The
     * visible label is "First Last", so the surname is carried
     * separately rather than parsed back out of the name.
     */
    public function testEachRowCarriesTheSurnameTheServerSortedBy(): void
    {
        $html = $this->render(starters: [$this->player(3, 'RB', lastname: 'Hubbard')]);

        $this->assertStringContainsString('data-sortname="hubbard"', $html);
    }

    public function testAnInjuryLabelRendersWithItsDetailAsATooltip(): void
    {
        $player = $this->player(3, 'RB') + [];
        $player['injuryLabel'] = 'Ques';
        $player['injuryDetail'] = 'Questionable: Shoulder';

        $html = $this->render(starters: [$player]);

        $this->assertStringContainsString('title="Questionable: Shoulder"', $html);
        $this->assertStringContainsString('(Ques)', $html);
    }

    private function player(
        int $id,
        string $pos,
        bool $lock = false,
        bool $postDeadline = false,
        string $lastname = ''
    ): array {
        return [
            'playerid' => $id, 'name' => "Player $id", 'nfl' => 'SEA',
            'lastname' => $lastname !== '' ? $lastname : "Player$id", 'pos' => $pos,
            'opp' => 'vs ARI', 'lock' => $lock, 'postDeadline' => $postDeadline,
            'injuryLabel' => '', 'injuryDetail' => '', 'ir' => false,
        ];
    }

    private function render(
        array $starters = [],
        array $reserves = [],
        bool $allLock = false,
        ?array $actingHc = null,
        ?LineupRules $rules = null
    ): string {
        return $this->twig->render('activations/_lineup_form.html.twig', [
            'starters' => $starters,
            'reserves' => $reserves,
            'allLock' => $allLock,
            'actingHc' => $actingHc,
            'rules' => $rules ?? LineupRules::defaults(),
        ]);
    }

    private function attribute(string $html): string
    {
        preg_match('/data-lineup-rules="([^"]*)"/', $html, $m);

        return $m[1] ?? '';
    }
}
