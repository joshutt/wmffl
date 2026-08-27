<?php

namespace App\Controller;

use App\Service\ScheduleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ScheduleController extends AbstractController
{
    public function __construct(
        private readonly ScheduleService $scheduleService
    ) {
    }

    #[Route('/schedule/{season?}', name: 'schedule', requirements: ['season' => '\d+'])]
    public function index(?int $season = null): Response
    {
        $season ??= $this->scheduleService->resolveDefaultSeason();

        return $this->render('schedule/index.html.twig', $this->scheduleService->getSchedule($season) + [
            'season' => $season,
        ]);
    }
}
