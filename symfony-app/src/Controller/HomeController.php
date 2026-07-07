<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\ScoresRepository;
use App\Repository\StandingsRepository;
use App\Service\SeasonWeekService;
use App\Service\StandingsCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Homepage, ported from football/index.php and its includes
 * (article.php, scores.php, standings.php, forum/commentlist.php,
 * quicklinks.php)
 */
class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(
        SeasonWeekService $seasonWeekService,
        ArticleRepository $articleRepository,
        ScoresRepository $scoresRepository,
        StandingsRepository $standingsRepository,
        StandingsCalculatorService $calculatorService,
        EntityManagerInterface $em
    ): Response {
        $currentSeason = $seasonWeekService->getCurrentSeason();
        $currentWeek = $seasonWeekService->getCurrentWeek();

        // Scores show the previous season until week 1 (football/scores.php lines 9-13)
        $scoresSeason = $currentWeek < 1 ? $currentSeason - 1 : $currentSeason;
        $scores = $scoresRepository->getLatestWeekScores($scoresSeason);

        // Standings show the previous season's final standings in the
        // off-season (football/standings.php lines 10-14)
        $standingsSeason = $currentSeason;
        $standingsWeek = $currentWeek;
        if ($standingsWeek == 0) {
            $standingsWeek = 16;
            $standingsSeason = $standingsSeason - 1;
        }
        $teamData = $standingsRepository->getCurrentStandings($standingsSeason, $standingsWeek);
        $gameData = $standingsRepository->getTeamGames($standingsSeason, $standingsWeek);
        $teamArray = $calculatorService->buildTeamArray($teamData, $gameData);
        $calculatorService->sortTeams($teamArray);

        // Latest trash talk (football/forum/commentlist.php)
        $posts = $em->createQuery(
            'SELECT f FROM App\Entity\Forum f ORDER BY f.createTime DESC'
        )->setMaxResults(6)->getResult();

        return $this->render('home/index.html.twig', [
            'articles' => $articleRepository->findActivePage(4),
            'scores' => $scores,
            'teams' => $teamArray,
            'posts' => $posts,
            'season' => $currentSeason,
        ]);
    }
}
