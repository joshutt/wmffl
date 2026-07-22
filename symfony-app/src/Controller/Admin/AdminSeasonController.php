<?php

namespace App\Controller\Admin;

use App\Entity\PositionCost;
use App\Entity\Season;
use App\Repository\SeasonRepository;
use App\Service\AuthenticationService;
use App\Service\ScoringRuleRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Season Rules: view and edit each season's structure, finances,
 * scoring parameters and protection settings. This is where historical
 * rules get recreated (rules that predate the codebase exist only in
 * league records), so every value is editable per season and `verified`
 * tracks which seasons have been confirmed.
 */
#[Route('/admin/seasons')]
class AdminSeasonController extends AbstractAdminController
{
    private const CSRF_ID = 'admin_seasons';

    #[Route('', name: 'admin_seasons')]
    public function index(AuthenticationService $auth, SeasonRepository $seasons): Response
    {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $all = $seasons->findAllDesc();

        return $this->render('admin/seasons/index.html.twig', [
            'seasons' => $all,
            'latest' => $all[0] ?? null,
        ]);
    }

    #[Route('/clone', name: 'admin_seasons_clone', methods: ['POST'])]
    public function clone(
        Request $request,
        AuthenticationService $auth,
        SeasonRepository $seasons,
        EntityManagerInterface $em,
        Connection $connection
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, self::CSRF_ID);

        $latest = $seasons->findLatest();
        if (!$latest) {
            throw $this->createNotFoundException('No seasons exist to clone');
        }
        $next = $latest->getSeason() + 1;
        if ($seasons->find($next)) {
            $this->addFlash('error', "Season $next already exists");

            return $this->redirectToRoute('admin_seasons');
        }

        $season = (clone $latest)->setSeason($next)->setVerified(false)->setNotes(null);
        $em->persist($season);
        $em->flush();

        // Give every team from the latest season its points budget for the
        // new season (spent points reset), skipping rows already created.
        $connection->executeStatement(
            'INSERT INTO transpoints (TeamID, season, ProtectionPts, TransPts, TotalPts)
             SELECT tp.TeamID, :next, 0, 0, tp.TotalPts FROM transpoints tp
             WHERE tp.season = :latest
               AND NOT EXISTS (SELECT 1 FROM transpoints t2 WHERE t2.TeamID = tp.TeamID AND t2.season = :next)',
            ['next' => $next, 'latest' => $latest->getSeason()]
        );

        $this->addFlash('success', "Season $next created as a copy of {$latest->getSeason()}");

        return $this->redirectToRoute('admin_seasons_edit', ['season' => $next]);
    }

    #[Route('/{season}', name: 'admin_seasons_edit', requirements: ['season' => '\d+'], methods: ['GET'])]
    public function edit(
        int $season,
        AuthenticationService $auth,
        SeasonRepository $seasons,
        Connection $connection
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $row = $seasons->find($season);
        if (!$row) {
            throw $this->createNotFoundException("No season $season");
        }

        return $this->render('admin/seasons/edit.html.twig', [
            'season' => $row,
            'groups' => ScoringRuleRegistry::groups(),
            'groupLabels' => ScoringRuleRegistry::GROUPS,
            'values' => $row->getScoringRules() + ScoringRuleRegistry::defaults(),
            'transpoints' => $this->getTranspoints($connection, $season),
            'positionCosts' => $this->getEffectivePositionCosts($connection, $season),
        ]);
    }

    #[Route('/{season}', name: 'admin_seasons_save', requirements: ['season' => '\d+'], methods: ['POST'])]
    public function save(
        int $season,
        Request $request,
        AuthenticationService $auth,
        SeasonRepository $seasons,
        EntityManagerInterface $em,
        Connection $connection
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, self::CSRF_ID);

        $row = $seasons->find($season);
        if (!$row) {
            throw $this->createNotFoundException("No season $season");
        }

        $post = $request->request;

        // Structure
        $row->setRegularSeasonWeeks(max(1, $post->getInt('regularSeasonWeeks', 14)));
        $row->setTotalWeeks(max(1, $post->getInt('totalWeeks', 16)));
        $row->setMaxActivePlayers(max(1, $post->getInt('maxActivePlayers', 25)));
        $row->setNumOfGames(max(1, $post->getInt('numOfGames', 84)));

        // Finance
        $row->setEntryFee((float) $post->get('entryFee', 75));
        $row->setIllegalActivationFine((float) $post->get('illegalActivationFine', 5));
        $row->setByeWeekActivationFine((float) $post->get('byeWeekActivationFine', 1));
        $row->setExtraTransactionFine((float) $post->get('extraTransactionFine', 1));
        $row->setWinPercent((float) $post->get('winPercent', 0.25));
        $row->setPostPercent((float) $post->get('postPercent', 0.5));
        $row->setDivPercent((float) $post->get('divPercent', 0.05));
        $row->setPlayoffPercent((float) $post->get('playoffPercent', 0.05));
        $row->setFinalPercent((float) $post->get('finalPercent', 0.25));
        $row->setChampPercent((float) $post->get('champPercent', 0.5));

        // Scoring: one input per registry key; blank = category not awarded
        $rules = [];
        foreach (ScoringRuleRegistry::definitions() as $key => $def) {
            $raw = trim((string) $post->get('rule_' . $key, ''));
            if ($raw === '') {
                $rules[$key] = null;
                continue;
            }
            if ($def['type'] === 'int') {
                if (!is_numeric($raw)) {
                    $this->addFlash('error', sprintf('"%s" (%s) must be a number', $def['label'], $key));

                    return $this->redirectToRoute('admin_seasons_edit', ['season' => $season]);
                }
                $rules[$key] = (int) $raw;
            } else {
                $decoded = json_decode($raw, true);
                if (!is_array($decoded)) {
                    $this->addFlash('error', sprintf('"%s" (%s) must be valid JSON like the default', $def['label'], $key));

                    return $this->redirectToRoute('admin_seasons_edit', ['season' => $season]);
                }
                $rules[$key] = $decoded;
            }
        }
        $row->setScoringRules($rules);
        $row->setScoringStrategy(trim((string) $post->get('scoringStrategy', 'standard')) ?: 'standard');

        $row->setVerified($post->getBoolean('verified'));
        $notes = trim((string) $post->get('notes', ''));
        $row->setNotes($notes === '' ? null : $notes);

        $em->flush();

        // Protections: per-team points budgets for this season
        foreach ($post->all('totalPts') as $teamId => $totalPts) {
            if (is_numeric($totalPts)) {
                $connection->executeStatement(
                    'UPDATE transpoints SET TotalPts = :pts WHERE TeamID = :teamId AND season = :season',
                    ['pts' => (int) $totalPts, 'teamId' => (int) $teamId, 'season' => $season]
                );
            }
        }

        // Protections: costs of the rows effective this season. Note a
        // row spans a season range, so editing one changes every season
        // in its range (the form says so).
        foreach ($post->all('positionCost') as $id => $cost) {
            [$position, $years, $startSeason] = explode('|', $id) + [null, null, null];
            if ($position !== null && is_numeric($cost)) {
                $connection->executeStatement(
                    'UPDATE positioncost SET cost = :cost
                     WHERE position = :position AND years = :years AND startSeason = :startSeason',
                    ['cost' => (int) $cost, 'position' => $position, 'years' => (int) $years, 'startSeason' => (int) $startSeason]
                );
            }
        }

        $this->addFlash('success', "Season $season saved");

        return $this->redirectToRoute('admin_seasons_edit', ['season' => $season]);
    }

    /**
     * Start a new protection cost for a position/years tier as of this
     * season: closes the currently effective row (endSeason = season-1)
     * and opens a new open-ended one, the same way the 2012 cost change
     * was recorded.
     */
    #[Route('/{season}/positioncost', name: 'admin_seasons_addcost', requirements: ['season' => '\d+'], methods: ['POST'])]
    public function addPositionCost(
        int $season,
        Request $request,
        AuthenticationService $auth,
        EntityManagerInterface $em,
        Connection $connection
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, self::CSRF_ID);

        $position = strtoupper(trim((string) $request->request->get('position', '')));
        $years = $request->request->getInt('years', -1);
        $cost = $request->request->get('cost', '');

        if ($position === '' || strlen($position) > 2 || $years < 0 || !is_numeric($cost)) {
            $this->addFlash('error', 'A new cost needs a position, years (0+) and a numeric cost');

            return $this->redirectToRoute('admin_seasons_edit', ['season' => $season]);
        }

        $existing = $connection->fetchAssociative(
            'SELECT startSeason FROM positioncost
             WHERE position = :position AND years = :years AND startSeason <= :season
               AND (endSeason IS NULL OR endSeason >= :season)',
            ['position' => $position, 'years' => $years, 'season' => $season]
        );
        if ($existing && (int) $existing['startSeason'] === $season) {
            $this->addFlash('error', "A $position/$years-years cost already starts in $season - edit it in the table instead");

            return $this->redirectToRoute('admin_seasons_edit', ['season' => $season]);
        }
        if ($existing) {
            $connection->executeStatement(
                'UPDATE positioncost SET endSeason = :ended
                 WHERE position = :position AND years = :years AND startSeason = :startSeason',
                ['ended' => $season - 1, 'position' => $position, 'years' => $years, 'startSeason' => (int) $existing['startSeason']]
            );
        }

        $em->persist((new PositionCost())
            ->setPosition($position)
            ->setYears($years)
            ->setStartSeason($season)
            ->setCost((int) $cost));
        $em->flush();

        $this->addFlash('success', "New $position cost (protected $years years) effective $season");

        return $this->redirectToRoute('admin_seasons_edit', ['season' => $season]);
    }

    private function getTranspoints(Connection $connection, int $season): array
    {
        // teamnames has no rows yet for future seasons - fall back to the
        // team table's current name
        return $connection->fetchAllAssociative(
            'SELECT tp.TeamID as teamid, COALESCE(tn.name, t.name) as name,
                    tp.ProtectionPts, tp.TransPts, tp.TotalPts
             FROM transpoints tp
             JOIN team t ON t.teamid = tp.TeamID
             LEFT JOIN teamnames tn ON tn.teamid = tp.TeamID AND tn.season = tp.season
             WHERE tp.season = :season
             ORDER BY name',
            ['season' => $season]
        );
    }

    private function getEffectivePositionCosts(Connection $connection, int $season): array
    {
        return $connection->fetchAllAssociative(
            'SELECT position, years, cost, startSeason, endSeason
             FROM positioncost
             WHERE startSeason <= :season AND (endSeason IS NULL OR endSeason >= :season)
             ORDER BY position, years',
            ['season' => $season]
        );
    }
}
