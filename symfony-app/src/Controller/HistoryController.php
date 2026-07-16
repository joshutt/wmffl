<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * League history pages, ported from football/history/'s top-level
 * files (Phase 9a). The per-season pages under {year}Season(.php|/)
 * are still legacy-served via LegacyBridge until Phase 9b.
 */
class HistoryController extends AbstractController
{
    /** Flat legacy pages (1992Season.php); later years are directories. */
    private const FIRST_SEASON = 1992;
    private const LAST_FLAT_SEASON = 2017;
    private const LAST_SEASON = 2025;

    #[Route('/history', name: 'history_index')]
    public function index(): Response
    {
        return $this->render('history/index.html.twig', [
            'firstSeason'   => self::FIRST_SEASON,
            'lastFlatSeason' => self::LAST_FLAT_SEASON,
            'lastSeason'    => self::LAST_SEASON,
        ]);
    }
}
