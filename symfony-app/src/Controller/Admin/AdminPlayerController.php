<?php

namespace App\Controller\Admin;

use App\Entity\Player;
use App\Enum\PosEnum;
use App\Repository\PlayerRepository;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/players')]
class AdminPlayerController extends AbstractAdminController
{
    /**
     * Search results are capped rather than paginated; admin lookups are by
     * name, where a couple hundred rows already means "type more letters".
     */
    public const MAX_RESULTS = 200;

    #[Route('', name: 'admin_players')]
    public function index(Request $request, AuthenticationService $auth, PlayerRepository $players): Response
    {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $q = trim($request->query->get('q', ''));

        return $this->render('admin/player/index.html.twig', [
            'q' => $q,
            // null = no search yet (prompt to search); non-active players are
            // included since fixing their records is the point of this page
            'players' => $q === ''
                ? null
                : $players->searchPlayers(['q' => $q, 'inactive' => true], 0, self::MAX_RESULTS),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_players_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        Request $request,
        AuthenticationService $auth,
        PlayerRepository $players,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $player = $players->find($id);
        if (!$player) {
            throw $this->createNotFoundException("No player with id $id");
        }

        if ($request->isMethod('POST')) {
            $this->assertCsrfToken($request, 'admin_player');
        }
        if ($request->isMethod('POST') && $this->applyForm($request, $player)) {
            $em->flush();
            $this->addFlash('success', 'Player updated');

            return $this->redirectToRoute('admin_players', ['q' => $player->getLastname()]);
        }

        return $this->render('admin/player/edit.html.twig', [
            'player' => $player,
            'positions' => PosEnum::cases(),
        ]);
    }

    /**
     * Copy submitted fields onto the player. Returns false (with an error
     * flash) when validation fails, leaving the player unchanged.
     */
    private function applyForm(Request $request, Player $player): bool
    {
        $lastname = trim($request->request->get('lastname', ''));
        if ($lastname === '') {
            $this->addFlash('error', 'A last name is required');

            return false;
        }

        $dob = null;
        $dobInput = trim($request->request->get('dob', ''));
        if ($dobInput !== '') {
            $dob = \DateTime::createFromFormat('Y-m-d|', $dobInput) ?: null;
            if (!$dob) {
                $this->addFlash('error', 'Date of birth must be a valid date');

                return false;
            }
        }

        $player->setLastname($lastname);
        $player->setFirstname(trim($request->request->get('firstname', '')) ?: null);
        $player->setPos(PosEnum::tryFrom($request->request->get('pos', '')));
        $player->setTeam(trim($request->request->get('team', '')) ?: null);
        $player->setNumber($this->nullableInt($request, 'number'));
        // retired is a year(4) column: the season the player retired, or NULL
        // while still playing (informational; the active flag drives search)
        $player->setRetired($this->nullableInt($request, 'retired'));
        $player->setHeight($this->nullableInt($request, 'height'));
        $player->setWeight($this->nullableInt($request, 'weight'));
        $player->setDob($dob);
        $player->setDraftTeam(trim($request->request->get('draftTeam', '')) ?: null);
        $player->setDraftYear($this->nullableInt($request, 'draftYear'));
        // active drives /players search visibility and (with usePos) the
        // legacy transaction player pool; unchecked boxes arrive absent
        $player->setActive($request->request->getBoolean('active'));
        $player->setUsePos($request->request->getBoolean('usePos'));

        return true;
    }

    private function nullableInt(Request $request, string $field): ?int
    {
        $value = trim($request->request->get($field, ''));

        return $value === '' ? null : (int) $value;
    }
}
