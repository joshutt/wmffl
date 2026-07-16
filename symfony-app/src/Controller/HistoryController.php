<?php

namespace App\Controller;

use App\Repository\DraftPickRepository;
use App\Repository\TeamRepository;
use App\Service\SeasonWeekService;
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

    /**
     * #1 overall picks whose drafts predate the recorded selections in
     * draftpicks (playerid is NULL before 2006), verbatim from the
     * legacy hardcoded table. teamid identifies the franchise so the
     * by-team summary can group renamed teams (Warriors → ZEN → … →
     * today's name) the way the legacy summary did by hand.
     */
    private const STATIC_NUMBER_ONE_PICKS = [
        ['season' => 1992, 'teamid' => 3, 'team' => 'Legions of Byron', 'player' => 'Barry Sanders', 'pos' => 'RB', 'nflteam' => 'DET'],
        ['season' => 1993, 'teamid' => 2, 'team' => 'Slayers', 'player' => 'Sterling Sharpe', 'pos' => 'WR', 'nflteam' => 'GB'],
        ['season' => 1994, 'teamid' => 3, 'team' => 'Norsemen', 'player' => 'Seattle QB', 'pos' => 'QB', 'nflteam' => 'SEA'],
        ['season' => 1995, 'teamid' => 8, 'team' => 'Warriors', 'player' => 'Dallas QB', 'pos' => 'QB', 'nflteam' => 'DAL'],
        ['season' => 1996, 'teamid' => 6, 'team' => 'Renegades', 'player' => 'Lawrence Philips', 'pos' => 'RB', 'nflteam' => 'STL'],
        ['season' => 1997, 'teamid' => 9, 'team' => 'Iradicators', 'player' => 'Emmitt Smith', 'pos' => 'RB', 'nflteam' => 'DAL'],
        ['season' => 1998, 'teamid' => 10, 'team' => 'Barbarians', 'player' => 'Corey Dillion', 'pos' => 'RB', 'nflteam' => 'CIN'],
        ['season' => 1999, 'teamid' => 1, 'team' => 'Archers Who Say Ni', 'player' => 'Terrell Owens', 'pos' => 'WR', 'nflteam' => 'SF'],
        ['season' => 2000, 'teamid' => 4, 'team' => 'Hempaholics', 'player' => 'Albert Connell', 'pos' => 'WR', 'nflteam' => 'WAS'],
        ['season' => 2001, 'teamid' => 8, 'team' => 'ZEN', 'player' => 'Michael Bennett', 'pos' => 'RB', 'nflteam' => 'MIN'],
        ['season' => 2002, 'teamid' => 10, 'team' => 'Barbarians', 'player' => 'Rich Gannon', 'pos' => 'QB', 'nflteam' => 'OAK'],
        ['season' => 2003, 'teamid' => 6, 'team' => 'Crusaders', 'player' => 'Edgerrin James', 'pos' => 'RB', 'nflteam' => 'IND'],
        ['season' => 2004, 'teamid' => 10, 'team' => 'Whiskey Tango', 'player' => 'Derrick Mason', 'pos' => 'WR', 'nflteam' => 'TEN'],
        ['season' => 2005, 'teamid' => 4, 'team' => 'Lindbergh Baby Casserole', 'player' => 'Brian Westbrook', 'pos' => 'RB', 'nflteam' => 'PHI'],
    ];

    /** Position labels for the picks-by-position summary, per the legacy table. */
    private const POSITION_LABELS = [
        'RB' => 'Runningback', 'WR' => 'Wide Receivers', 'QB' => 'Quarterbacks',
        'TE' => 'Tight Ends', 'K' => 'Kickers', 'HC' => 'Head Coaches',
        'OL' => 'Offensive Lines', 'DL' => 'Defensive Linemen',
        'LB' => 'Linebackers', 'DB' => 'Defensive Backs',
    ];

    #[Route('/history/pastdrafts', name: 'history_pastdrafts')]
    public function pastdrafts(DraftPickRepository $draftPicks, TeamRepository $teams): Response
    {
        $picks = array_merge(self::STATIC_NUMBER_ONE_PICKS, $draftPicks->getNumberOnePicks());
        $currentNames = $teams->getCurrentTeamNames();

        $byTeam = [];
        $byPos = [];
        foreach ($picks as $pick) {
            $byTeam[$pick['teamid']]['seasons'][] = $pick['season'];
            $byTeam[$pick['teamid']]['name'] = $currentNames[$pick['teamid']] ?? $pick['team'];
            $byPos[$pick['pos']][] = $pick['season'];
        }

        // most picks first; earliest first pick breaks ties
        uasort($byTeam, fn($a, $b) => [count($b['seasons']), $a['seasons'][0]] <=> [count($a['seasons']), $b['seasons'][0]]);
        uasort($byPos, fn($a, $b) => count($b) <=> count($a));

        return $this->render('history/pastdrafts.html.twig', [
            'picks'          => $picks,
            'byTeam'         => $byTeam,
            'byPos'          => $byPos,
            'positionLabels' => self::POSITION_LABELS,
        ]);
    }

    /** The six record tables of the all-time records page, in legacy order. */
    private const RECORD_TABLES = [
        'overall'      => ['title' => 'Overall Records', 'ties' => true],
        'regular'      => ['title' => 'Regular Season Records', 'ties' => true],
        'postseason'   => ['title' => 'Post-Season Records', 'ties' => false],
        'playoffs'     => ['title' => 'Playoff Records', 'ties' => false],
        'championship' => ['title' => 'Championship Game Records', 'ties' => false],
        'toilet'       => ['title' => 'Toilet Bowl Game Records', 'ties' => false],
    ];

    #[Route('/history/alltimerecords', name: 'history_alltimerecords')]
    public function alltimerecords(TeamRepository $teams, SeasonWeekService $seasonWeek): Response
    {
        $season = $seasonWeek->getCurrentSeason();

        $tables = [];
        foreach (self::RECORD_TABLES as $split => $meta) {
            $tables[] = $meta + ['rows' => $teams->getAllTimeRecords($season, $split)];
        }

        return $this->render('history/alltimerecords.html.twig', [
            'tables'        => $tables,
            'throughSeason' => $season - 1,
        ]);
    }
}
