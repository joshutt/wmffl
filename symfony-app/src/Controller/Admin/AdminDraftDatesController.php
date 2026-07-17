<?php

namespace App\Controller\Admin;

use App\Service\AuthenticationService;
use App\Service\DraftScheduleService;
use App\Service\SeasonWeekService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/draftdates')]
class AdminDraftDatesController extends AbstractAdminController
{
    #[Route('/{season}', name: 'admin_draftdates', requirements: ['season' => '\d+'], defaults: ['season' => null])]
    public function index(
        Request $request,
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
        EntityManagerInterface $em,
        DraftScheduleService $scheduleService,
        ?int $season = null
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $season ??= $seasonWeek->getCurrentSeason();
        $conn     = $em->getConnection();

        $rows = $conn->fetchAllAssociative(
            "SELECT t.name, d.Date AS date, MIN(d.Attend) AS attend
             FROM draftdate d
             JOIN user u ON u.UserID = d.UserID
             JOIN team t ON t.teamid = u.teamid
             WHERE d.Date > :cutoff
             GROUP BY u.teamid, t.name, d.Date
             ORDER BY d.Date",
            ['cutoff' => $season . '-01-01']
        );

        $dates  = [];
        $maxYes = 0;
        foreach ($rows as $row) {
            $date = $row['date'];
            if (!isset($dates[$date])) {
                $dates[$date] = ['yes' => 0, 'no' => 0];
            }
            if ($row['attend'] === 'Y') {
                $dates[$date]['yes']++;
            } else {
                $dates[$date]['no']++;
            }
            $maxYes = max($maxYes, $dates[$date]['yes']);
        }

        $noVote = $conn->fetchFirstColumn(
            "SELECT tn.name
             FROM owners o
             JOIN draftvote dv ON o.userid = dv.userid AND dv.season = o.season
             JOIN teamnames tn ON tn.season = o.season AND tn.teamid = o.teamid
             WHERE o.season = :season
             GROUP BY o.teamid, tn.name
             HAVING MAX(dv.lastUpdate) IS NULL",
            ['season' => $season]
        );

        return $this->render('admin/draftdates/index.html.twig', [
            'season'      => $season,
            'dates'       => $dates,
            'maxYes'      => $maxYes,
            'noVote'      => $noVote,
            'windowStart' => DraftScheduleService::windowStart($season),
            'windowEnd'   => DraftScheduleService::windowEnd($season),
            'candidates'  => $this->buildCandidates($request, $season, $scheduleService),
        ]);
    }

    /**
     * Step 2 of the schedule builder: when a valid ?first/?last range is
     * in the query string, the candidate dates for the calendar. A date
     * already scheduled is pre-checked; when nothing is scheduled yet,
     * Saturdays and Sundays are (the legacy default). Null = builder is
     * still on step 1.
     *
     * @return array<array{date: \DateTimeImmutable, checked: bool}>|null
     */
    private function buildCandidates(Request $request, int $season, DraftScheduleService $scheduleService): ?array
    {
        $first = (string) $request->query->get('first', '');
        $last  = (string) $request->query->get('last', '');
        if ($first === '' || $last === '') {
            return null;
        }

        $firstDate = \DateTimeImmutable::createFromFormat('Y-m-d|', $first) ?: null;
        $lastDate  = \DateTimeImmutable::createFromFormat('Y-m-d|', $last) ?: null;
        if (!$firstDate || !$lastDate || $firstDate > $lastDate
            || $first < DraftScheduleService::windowStart($season)
            || $last > DraftScheduleService::windowEnd($season)
        ) {
            $this->addFlash('error', sprintf(
                'Pick a first and last date, in order, between %s and %s',
                DraftScheduleService::windowStart($season),
                DraftScheduleService::windowEnd($season)
            ));

            return null;
        }

        $candidates = $scheduleService->candidateDates($firstDate, $lastDate);
        $existing = $scheduleService->existingDates($season);
        if ($existing !== []) {
            foreach ($candidates as &$candidate) {
                $candidate['checked'] = in_array($candidate['date']->format('Y-m-d'), $existing, true);
            }
        }

        return $candidates;
    }

    #[Route('/{season}/schedule', name: 'admin_draftdates_schedule', requirements: ['season' => '\d+'], methods: ['POST'])]
    public function schedule(
        int $season,
        Request $request,
        AuthenticationService $auth,
        DraftScheduleService $scheduleService
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, 'admin_draftdates');

        $dates = $request->request->all('dates');
        if ($dates === [] || array_filter($dates, fn($d) => !preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) !== []) {
            $this->addFlash('error', 'Select at least one candidate date');

            return $this->redirectToRoute('admin_draftdates', ['season' => $season]);
        }

        try {
            $counts = $scheduleService->applySchedule($season, $dates);
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToRoute('admin_draftdates', ['season' => $season]);
        }

        $this->addFlash('success', sprintf(
            'Schedule saved: %d ballot%s created, %d date row%s added, %d date%s removed',
            $counts['createVotes'], $counts['createVotes'] === 1 ? '' : 's',
            $counts['createDates'], $counts['createDates'] === 1 ? '' : 's',
            $counts['deleteDates'], $counts['deleteDates'] === 1 ? '' : 's'
        ));

        return $this->redirectToRoute('admin_draftdates', ['season' => $season]);
    }
}
