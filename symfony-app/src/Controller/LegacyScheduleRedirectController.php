<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 301 redirects from the retired football/history/{year}Season/ schedule
 * pages (Phase 16a) to the new /schedule/{year} route. One route covers
 * all three legacy filename patterns: the DB-backed wrapper's
 * extensionless and .php forms (2000-2025's "schedule"/"schedule.php")
 * and the hand-written NNschedule.php pages (1992-1999's
 * "92schedule.php" .. "99schedule.php").
 */
class LegacyScheduleRedirectController extends AbstractController
{
    #[Route(
        '/history/{year}Season/{file}',
        name: 'legacy_schedule_year_redirect',
        requirements: [
            'year' => '199[2-9]|20(0[0-9]|1[0-9]|2[0-5])',
            'file' => 'schedule|schedule\.php|\d{2}schedule\.php',
        ]
    )]
    public function year(int $year): Response
    {
        return $this->redirectToRoute('schedule', ['season' => $year], Response::HTTP_MOVED_PERMANENTLY);
    }
}
