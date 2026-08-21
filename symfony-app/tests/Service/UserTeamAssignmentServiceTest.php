<?php

namespace App\Tests\Service;

use App\Entity\Owner;
use App\Entity\Team;
use App\Entity\User;
use App\Repository\OwnerRepository;
use App\Service\SeasonWeekService;
use App\Service\UserTeamAssignmentService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class UserTeamAssignmentServiceTest extends TestCase
{
    private function team(int $id): Team
    {
        $team = new Team();
        $ref  = new \ReflectionProperty(Team::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($team, $id);

        return $team;
    }

    private function userWithId(?int $id): User
    {
        $user = new User();
        if ($id !== null) {
            $ref = new \ReflectionProperty(User::class, 'id');
            $ref->setAccessible(true);
            $ref->setValue($user, $id);
        }

        return $user;
    }

    private function makeService(?Owner $existingOwner, EntityManagerInterface $em): UserTeamAssignmentService
    {
        $owners = $this->createStub(OwnerRepository::class);
        $owners->method('findForUserAndSeason')->willReturn($existingOwner);

        $seasonWeek = $this->createStub(SeasonWeekService::class);
        $seasonWeek->method('getCurrentSeason')->willReturn(2026);

        return new UserTeamAssignmentService($em, $owners, $seasonWeek);
    }

    public function testUnassignClearsTeamAndTouchesNoOwnerRow(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist');
        $service = $this->makeService(existingOwner: null, em: $em);

        $user = $this->userWithId(1);
        $service->assign($user, null);

        $this->assertNull($user->getTeam());
    }

    public function testAssignNewUserCreatesOwnerRowForCurrentSeason(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $persisted = null;
        $em->expects($this->once())->method('persist')->willReturnCallback(function ($arg) use (&$persisted) {
            // Owner's PK is composite and includes `team`; Doctrine computes
            // the identity hash synchronously inside persist(), so `team`
            // (and `user`/`season`) must already be set on the object *at
            // this exact call*, not merely by the time assign() returns -
            // a mock can't reproduce Doctrine's real "missing assigned ID"
            // error, so this is the regression guard for that ordering bug.
            $this->assertInstanceOf(Owner::class, $arg);
            $this->assertNotNull($arg->getTeam(), 'team must be set before persist() is called');
            $persisted = $arg;
        });
        $service = $this->makeService(existingOwner: null, em: $em);

        $user = $this->userWithId(null); // brand-new, no id yet
        $user->setPrimaryOwner(true);
        $team = $this->team(5);
        $service->assign($user, $team);

        $this->assertSame($team, $user->getTeam());
        $this->assertInstanceOf(Owner::class, $persisted);
        $this->assertSame($team, $persisted->getTeam());
        $this->assertSame($user, $persisted->getUser());
        $this->assertSame(2026, $persisted->getSeason());
        $this->assertSame(1, $persisted->getPrimary());
    }

    public function testAssignNewNonPrimaryOwnerCreatesOwnerRowWithPrimaryZero(): void
    {
        // Regression guard: a co-owner added without checking "Primary
        // Owner" must land as primary=0 in `owners`, not hardcoded to 1
        // (previously every new owners row was forced to primary=1
        // regardless of the form's checkbox).
        $em = $this->createMock(EntityManagerInterface::class);
        $persisted = null;
        $em->expects($this->once())->method('persist')->willReturnCallback(function ($arg) use (&$persisted) {
            $persisted = $arg;
        });
        $service = $this->makeService(existingOwner: null, em: $em);

        $user = $this->userWithId(null);
        $user->setPrimaryOwner(false);
        $service->assign($user, $this->team(5));

        $this->assertSame(0, $persisted->getPrimary());
    }

    public function testAssignExistingUserNoOwnerRowYetCreatesOne(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->willReturnCallback(function ($arg) {
            $this->assertInstanceOf(Owner::class, $arg);
            $this->assertNotNull($arg->getTeam(), 'team must be set before persist() is called');
        });
        $service = $this->makeService(existingOwner: null, em: $em);

        $user = $this->userWithId(7);
        $team = $this->team(3);
        $service->assign($user, $team);

        $this->assertSame($team, $user->getTeam());
    }

    public function testReassignUpdatesExistingCurrentSeasonOwnerRowInPlace(): void
    {
        $oldTeam = $this->team(1);
        $existingOwner = new Owner();
        $existingOwner->setTeam($oldTeam);
        $existingOwner->setSeason(2026);
        $existingOwner->setPrimary(1);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist'); // already-managed row, no new persist
        $service = $this->makeService(existingOwner: $existingOwner, em: $em);

        $user = $this->userWithId(9);
        $user->setPrimaryOwner(true);
        $newTeam = $this->team(2);
        $service->assign($user, $newTeam);

        $this->assertSame($newTeam, $user->getTeam());
        $this->assertSame($newTeam, $existingOwner->getTeam(), 'existing owner row updated in place, not duplicated');
    }

    public function testReassignDemotesOwnerRowWhenPrimaryOwnerUnchecked(): void
    {
        // Reported bug: adding a second owner to a team without checking
        // "Primary Owner" left BOTH owners rows at primary=1, because the
        // sync never touched `primary` on the update path at all.
        $existingOwner = new Owner();
        $existingOwner->setTeam($this->team(2));
        $existingOwner->setSeason(2026);
        $existingOwner->setPrimary(1);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist');
        $service = $this->makeService(existingOwner: $existingOwner, em: $em);

        $user = $this->userWithId(9);
        $user->setPrimaryOwner(false);
        $service->assign($user, $this->team(2));

        $this->assertSame(0, $existingOwner->getPrimary());
    }
}
