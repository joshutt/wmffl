<?php

namespace App\Controller;

use App\Repository\TeamRepository;
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

    /**
     * Championship-game MVPs, carried over verbatim from the legacy
     * hardcoded table — there is no DB source for MVP awards (ballot is
     * issue voting; MvpScoringService ranks on demand, it doesn't store
     * winners). A future data-driven candidate, same convention as
     * PlayerRecordsService's thresholds. Seasons without an entry render
     * blank.
     */
    private const CHAMPIONSHIP_MVPS = [
        1992 => 'Barry Foster',
        1993 => 'Chris Warren',
        1994 => 'San Francisco QB',
        1995 => 'San Francisco QB',
        1996 => 'Green Bay QB',
        1997 => 'Jason Sehorn',
        1998 => 'Jamal Anderson',
        1999 => 'Edgerrin James',
        2000 => 'Randy Moss',
        2001 => 'Dallas OL',
        2002 => 'Charlie Garner',
        2003 => 'Aaron Brooks',
        2004 => 'Denver OL',
        2005 => 'Clinton Portis',
        2006 => 'Ladell Betts',
        2007 => 'Jared Allen',
        2008 => 'Baltimore OL',
        2009 => 'Randy Moss',
        2010 => 'Jamaal Charles',
        2011 => 'Jordy Nelson',
        2012 => 'Drew Brees',
        2013 => 'LeSean McCoy',
        2014 => 'Jordy Nelson',
        2015 => 'Brandon Marshall',
        2016 => 'Jordy Nelson',
        2017 => 'Marshon Lattimore',
        2018 => 'Deshaun Watson',
        2019 => 'Fred Warner',
        2020 => 'Davante Adams',
        2021 => 'Patrick Mahomes',
        2022 => 'TJ Hockenson',
        2023 => 'Derrick Henry',
        2024 => 'Indianapolis OL',
    ];

    #[Route('/history', name: 'history_index')]
    public function index(): Response
    {
        return $this->render('history/index.html.twig', [
            'firstSeason'   => self::FIRST_SEASON,
            'lastFlatSeason' => self::LAST_FLAT_SEASON,
            'lastSeason'    => self::LAST_SEASON,
        ]);
    }

    #[Route('/history/pastchamps', name: 'history_pastchamps')]
    public function pastchamps(TeamRepository $teams): Response
    {
        return $this->render('history/pastchamps.html.twig', [
            'divisionTitles' => $teams->getDivisionTitles(),
            'championships'  => $teams->getChampionshipGames(),
            'toiletBowls'    => $teams->getToiletBowlGames(),
            'mvps'           => self::CHAMPIONSHIP_MVPS,
        ]);
    }
}
