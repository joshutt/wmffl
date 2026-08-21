<?php

namespace App\Service;

use App\Entity\Owner;
use App\Entity\Team;
use App\Entity\User;
use App\Repository\OwnerRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Keeps `user.TeamID` (the current-team FK on the user row) and the
 * current season's `owners` row in step whenever the admin user tool
 * assigns/reassigns/unassigns a team (Phase 14). `owners` is the
 * season-keyed historical ownership record — this service only ever
 * writes the current season's row; past seasons' rows are left alone.
 */
class UserTeamAssignmentService
{
    public function __construct(
        private EntityManagerInterface $em,
        private OwnerRepository $owners,
        private SeasonWeekService $seasonWeek
    ) {
    }

    /**
     * Point $user at $team (or null to unassign). Does not flush - the
     * caller flushes alongside its own other changes to the user.
     */
    public function assign(User $user, ?Team $team): void
    {
        $user->setTeam($team);

        if ($team === null) {
            // Unassigning only clears the current-team pointer; owners
            // rows are historical record and are never deleted here.
            return;
        }

        $season = $this->seasonWeek->getCurrentSeason();
        $owner  = $user->getId() !== null
            ? $this->owners->findForUserAndSeason($user->getId(), $season)
            : null;

        if ($owner === null) {
            $owner = new Owner();
            $owner->setUser($user);
            $owner->setSeason($season);
            $owner->setPrimary($user->isPrimaryOwner() ? 1 : 0);
            // Owner's PK is composite and includes `team`, so it must be set
            // before persist() - Doctrine computes the identity hash at
            // persist() time and errors if any Id-mapped association is
            // still unset then.
            $owner->setTeam($team);
            $this->em->persist($owner);

            return;
        }

        $owner->setTeam($team);
        $owner->setPrimary($user->isPrimaryOwner() ? 1 : 0);
    }
}
