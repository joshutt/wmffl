<?php

namespace App\Controller\Admin;

use App\Entity\Paid;
use App\Entity\SeasonFlag;
use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/money')]
class AdminMoneyController extends AbstractAdminController
{
    #[Route('/updatePaid/{season}', name: 'admin_money_paid', defaults: ['season' => null])]
    public function updatePaid(
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
        EntityManagerInterface $em,
        ?int $season = null
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $season = $season ?? $seasonWeek->getCurrentSeason();
        $paid = $em->getRepository(Paid::class)->findBy(['season' => $season]);

        return $this->render('admin/money/updatePaid.html.twig', [
            'season'        => $season,
            'currentSeason' => $seasonWeek->getCurrentSeason(),
            'paid'          => $paid,
        ]);
    }

    #[Route('/recordChange', name: 'admin_money_record_change', methods: ['POST'])]
    public function recordChange(
        Request $request,
        AuthenticationService $auth,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$auth->isCommissioner()) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        if (!$this->isCsrfTokenValid('admin_money_record', (string) $request->getPayload()->get('_token'))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], Response::HTTP_FORBIDDEN);
        }

        $field = (string) $request->request->get('field');
        $val   = $request->request->get('val');

        $parts = explode('-', $field, 2);
        if (count($parts) !== 2 || !in_array($parts[0], ['paid', 'late', 'amt'], true) || !ctype_digit($parts[1])) {
            return new JsonResponse(['error' => 'Invalid field'], Response::HTTP_BAD_REQUEST);
        }
        $param = $parts[0];
        $idx   = (int) $parts[1];

        try {
            $paid = $em->find(Paid::class, $idx);
            if (!$paid instanceof Paid) {
                return new JsonResponse(['error' => 'Record not found'], Response::HTTP_NOT_FOUND);
            }

            switch ($param) {
                case 'paid':
                    $paid->setPaid(filter_var($val, FILTER_VALIDATE_BOOL));
                    break;
                case 'late':
                    $paid->setLateFee((float) filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
                    break;
                case 'amt':
                    $paid->setAmtPaid((float) filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
                    break;
            }

            $em->getConnection()->executeStatement("UPDATE config SET value=now() WHERE `key`='money.update'");
            $em->flush();

            return new JsonResponse(['ok' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/updateFlags/{season}', name: 'admin_money_flags', defaults: ['season' => null])]
    public function updateFlags(
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
        EntityManagerInterface $em,
        ?int $season = null
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $season = $season ?? $seasonWeek->getCurrentSeason();
        $flags  = $em->getRepository(SeasonFlag::class)->findBy(['season' => $season]);

        return $this->render('admin/money/updateFlags.html.twig', [
            'season'        => $season,
            'currentSeason' => $seasonWeek->getCurrentSeason(),
            'flags'         => $flags,
        ]);
    }

    #[Route('/processFlags', name: 'admin_money_process_flags', methods: ['POST'])]
    public function processFlags(
        Request $request,
        AuthenticationService $auth,
        EntityManagerInterface $em
    ): RedirectResponse {
        if (!$auth->isCommissioner()) {
            return new RedirectResponse('/');
        }
        $this->assertCsrfToken($request, 'admin_money_flags');

        $season = (int) $request->request->get('season');
        $flags = [];

        // First pass: collect flag values and zero-out all booleans
        foreach ($request->request->all() as $key => $value) {
            if (str_starts_with($key, 'flag-')) {
                $id = (int) substr($key, 5);
                $flags[$id] = ['flags' => $value, 'div' => 0, 'po' => 0, 'fin' => 0, 'cham' => 0];
            }
        }

        // Second pass: set boolean fields from checkboxes
        foreach ($request->request->all() as $key => $value) {
            if (!str_starts_with($key, 'flag-')) {
                $parts = explode('-', $key, 2);
                if (count($parts) === 2) {
                    $field = $parts[0];
                    $id    = (int) $parts[1];
                    if (isset($flags[$id])) {
                        $flags[$id][$field] = $value ? 1 : 0;
                    }
                }
            }
        }

        foreach ($flags as $id => $f) {
            /** @var SeasonFlag $current */
            $current = $em->find(SeasonFlag::class, $id);
            if ($current === null) {
                continue;
            }
            if ($current->getFlags() !== $f['flags']) {
                $current->setFlags($f['flags']);
            }
            if ($current->isDivisionWinner() != $f['div']) {
                $current->setDivisionWinner((bool) $f['div']);
            }
            if ($current->isPlayoffTeam() != $f['po']) {
                $current->setPlayoffTeam((bool) $f['po']);
            }
            if ($current->isFinalist() != $f['fin']) {
                $current->setFinalist((bool) $f['fin']);
            }
            if ($current->isChampion() != $f['cham']) {
                $current->setChampion((bool) $f['cham']);
            }
            $em->flush();
        }

        return $this->redirectToRoute('admin_money_flags', ['season' => $season]);
    }
}
