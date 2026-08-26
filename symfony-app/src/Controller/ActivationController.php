<?php

namespace App\Controller;

use App\Repository\ActivationRepository;
use App\Service\ActivationService;
use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The weekly lineup pages, ported from football/activate/: everyone's
 * current activations, and the owner's own submit form.
 *
 * The team a submission belongs to always comes from the session, never
 * from the request — the legacy handler took the team from a global set
 * during login but trusted every playerid in the POST.
 */
class ActivationController extends AbstractController
{
    public const CSRF_TOKEN_ID = 'activations';

    public function __construct(
        private readonly ActivationService $activationService,
        private readonly ActivationRepository $activations,
        private readonly AuthenticationService $auth,
        private readonly SeasonWeekService $seasonWeek
    ) {
    }

    /** Everyone's lineup for a week, paired up by that week's games. */
    #[Route('/activations', name: 'activations')]
    public function index(Request $request): Response
    {
        $season = $request->query->has('season')
            ? $request->query->getInt('season')
            : $this->seasonWeek->getCurrentSeason();
        $week = $request->query->has('week')
            ? $request->query->getInt('week')
            : $this->seasonWeek->getCurrentWeek();

        $now = new \DateTimeImmutable();
        $rows = $this->activations->getCurrentActivations($season, $week);

        return $this->render('activations/index.html.twig', [
            'season' => $season,
            'week' => $week,
            'weeks' => $this->activations->getAllWeeks($season),
            'matchups' => $this->buildMatchups($rows, $now),
        ]);
    }

    #[Route('/activations/submit', name: 'activations_submit', methods: ['GET'])]
    public function submit(Request $request): Response
    {
        if (!$this->auth->isLoggedIn()) {
            return $this->render('activations/submit.html.twig', ['loggedIn' => false]);
        }

        $season = $this->seasonWeek->getCurrentSeason();
        $week = $request->query->has('week')
            ? $request->query->getInt('week')
            : $this->seasonWeek->getCurrentWeek();
        // Week 0 is the off-season placeholder - there is never a lineup
        // to submit for it, so a current week of 0 (or a hand-edited
        // ?week=0) lands on week 1 instead.
        $week = max(1, $week);
        $teamId = (int) $this->auth->getTeamNumber();

        return $this->renderForm($season, $week, $teamId, null, []);
    }

    #[Route('/activations/submit', name: 'activations_save', methods: ['POST'])]
    public function save(Request $request): Response
    {
        if (!$this->auth->isLoggedIn()) {
            return $this->render('activations/submit.html.twig', ['loggedIn' => false], new Response(status: Response::HTTP_UNAUTHORIZED));
        }
        if (!$this->isCsrfTokenValid(self::CSRF_TOKEN_ID, (string) $request->getPayload()->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $season = $this->seasonWeek->getCurrentSeason();
        $week = $request->request->getInt('week', $this->seasonWeek->getCurrentWeek());
        if ($week < 1) {
            // The form never renders week 0 as an option, so this only
            // happens to a hand-crafted POST - nothing to save either way.
            $this->addFlash('error', 'Activations cannot be submitted for the off-season.');

            return $this->redirectToRoute('activations_submit');
        }
        $teamId = (int) $this->auth->getTeamNumber();

        $submitted = self::readSelections($request, $this->activationService->getLineupRules($season)->positions());
        $errors = $this->activationService->save(
            $season,
            $week,
            $teamId,
            $submitted['selections'],
            $submitted['actingHcId']
        );

        if ($errors === []) {
            $this->addFlash('success', 'Your activations have been saved.');

            return $this->redirectToRoute('activations', ['season' => $season, 'week' => $week]);
        }

        return $this->renderForm($season, $week, $teamId, $submitted['view'], $errors, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Pull the lineup out of a request: position => playerids, plus the
     * acting-head-coach picker. Values stay raw here; the service is
     * what decides whether they are real playerids.
     *
     * @param list<string> $positions
     * @return array{selections: array<string, list<mixed>>, actingHcId: ?int, view: array<string, mixed>}
     */
    public static function readSelections(Request $request, array $positions): array
    {
        $payload = $request->getPayload();
        $all = $payload->all();
        $selections = [];
        foreach ($positions as $pos) {
            $value = $all[$pos] ?? [];
            $selections[$pos] = is_array($value) ? array_values($value) : [$value];
        }

        $actingHcOn = (string) $payload->get('actHC', '') !== '';
        $actingHcRaw = $payload->get('actHCid');
        $actingHcId = $actingHcOn && is_scalar($actingHcRaw) ? (int) $actingHcRaw : null;

        $view = $selections;
        $view['actHC'] = $actingHcOn;
        $view['actHCid'] = $actingHcId;

        return ['selections' => $selections, 'actingHcId' => $actingHcId, 'view' => $view];
    }

    /**
     * Group the week's rows into one block per scheduled game so a
     * matchup's two lineups sit side by side, replacing the legacy
     * odd/even column split that paired unrelated teams.
     *
     * @return list<array{gameid: int, teams: list<array<string, mixed>>}>
     */
    private function buildMatchups(array $rows, \DateTimeImmutable $now): array
    {
        $games = [];
        foreach ($rows as $row) {
            $gameId = (int) $row['gameid'];
            $teamId = (int) $row['teamid'];
            $games[$gameId]['gameid'] ??= $gameId;
            $games[$gameId]['teams'][$teamId] ??= [
                'teamid' => $teamId,
                'name' => (string) $row['name'],
                'players' => [],
            ];

            if ($row['pos'] === null) {
                continue;
            }

            $games[$gameId]['teams'][$teamId]['players'][] = [
                'pos' => (string) $row['pos'],
                'name' => trim((string) $row['firstname'] . ' ' . (string) $row['lastname']),
                'opp' => self::currentOpponent($row),
                'locked' => $this->activationService->lockStateFor(
                    ['kickoffTs' => $row['kickoffTs'] === null ? null : (int) $row['kickoffTs']],
                    $now
                ),
                'injuryLabel' => self::injuryLabel($row),
                'injuryDetail' => (string) ($row['status'] ?? '') === ''
                    ? '' : $row['status'] . ': ' . (string) ($row['details'] ?? ''),
            ];
        }

        return array_values(array_map(
            fn (array $game) => ['gameid' => $game['gameid'], 'teams' => array_values($game['teams'])],
            $games
        ));
    }

    /**
     * "SEA vs ARI" / "LV @ DEN" / "Bye" — the current-activations view
     * names the player's own NFL team as well as his opponent.
     */
    private static function currentOpponent(array $row): string
    {
        $nfl = (string) ($row['nflteamid'] ?? '');
        if ($nfl !== '' && $nfl === (string) $row['homeTeam']) {
            return $nfl . ' vs ' . $row['roadTeam'];
        }
        if ($nfl !== '' && $nfl === (string) $row['roadTeam']) {
            return $nfl . ' @ ' . $row['homeTeam'];
        }

        return 'Bye';
    }

    private static function injuryLabel(array $row): string
    {
        if ((string) ($row['ir'] ?? '') === '1') {
            return 'IR';
        }

        return ActivationRepository::injuryShort((string) ($row['status'] ?? ''));
    }

    private function renderForm(
        int $season,
        int $week,
        int $teamId,
        ?array $submitted,
        array $errors,
        int $status = Response::HTTP_OK
    ): Response {
        $view = $this->activationService->buildSubmitView($season, $week, $teamId, $submitted);

        return $this->render('activations/submit.html.twig', $view + [
            'loggedIn' => true,
            'errors' => $errors,
            'weeks' => $this->activations->getWeekOptions($season),
            'admin' => false,
        ], new Response(status: $status));
    }
}
