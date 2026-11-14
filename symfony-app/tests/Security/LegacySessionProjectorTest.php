<?php

namespace App\Tests\Security;

use App\Entity\Team;
use App\Entity\User;
use App\Security\LegacySessionProjector;
use App\Service\AuthenticationService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class LegacySessionProjectorTest extends TestCase
{
    private LegacySessionProjector $projector;
    private AuthenticationService $auth;

    protected function setUp(): void
    {
        $_SESSION = [];
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $stack = new RequestStack();
        $stack->push($request);
        $this->projector = new LegacySessionProjector($stack);
        $this->auth = new AuthenticationService($stack);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testProjectWritesTheSixLegacyKeys(): void
    {
        $this->projector->project($this->user(commish: true));

        $this->assertTrue($_SESSION['isin']);
        $this->assertSame(4, $_SESSION['teamnum']);
        $this->assertSame(12, $_SESSION['usernum']);
        $this->assertSame('josh', $_SESSION['user']);
        $this->assertSame('Josh U', $_SESSION['fullname']);
        $this->assertTrue($_SESSION['commish']);
        $this->assertSame('', $_SESSION['message']);
    }

    public function testFacadeReadsTheProjection(): void
    {
        $this->projector->project($this->user(commish: true));

        $this->assertTrue($this->auth->isLoggedIn());
        $this->assertTrue($this->auth->isCommissioner());
        $this->assertSame(4, $this->auth->getTeamNumber());
        $this->assertSame(12, $this->auth->getUserId());
        $this->assertSame('Josh U', $this->auth->getFullName());
    }

    public function testClearRemovesIdentityButKeepsOtherSessionData(): void
    {
        $_SESSION['currentSeason'] = 2026;
        $this->projector->project($this->user());
        $this->projector->clear();

        $this->assertFalse($this->auth->isLoggedIn());
        foreach (LegacySessionProjector::KEYS as $key) {
            $this->assertArrayNotHasKey($key, $_SESSION);
        }
        $this->assertSame(2026, $_SESSION['currentSeason']);
    }

    public function testBecomeTeamSetsImpersonationMarkerAndDropsCommish(): void
    {
        $this->projector->project($this->user(commish: true));
        $this->auth->becomeTeam(9, 'Other Owner', 'other', 30);

        $this->assertTrue($this->projector->isImpersonating());
        $this->assertFalse($this->auth->isCommissioner());
        $this->assertSame(9, $this->auth->getTeamNumber());
    }

    public function testFreshProjectionAndClearBothEndImpersonation(): void
    {
        $this->auth->becomeTeam(9, 'Other Owner', 'other', 30);
        $this->projector->project($this->user());
        $this->assertFalse($this->projector->isImpersonating());

        $this->auth->becomeTeam(9, 'Other Owner', 'other', 30);
        $this->projector->clear();
        $this->assertFalse($this->projector->isImpersonating());
        $this->assertFalse($this->projector->hasIdentity());
    }

    public function testUserWithoutTeamProjectsNullTeam(): void
    {
        $user = $this->user();
        $user->setTeam(null);
        $this->projector->project($user);

        $this->assertNull($this->auth->getTeamNumber());
        $this->assertTrue($this->auth->isLoggedIn());
    }

    private function user(bool $commish = false): User
    {
        $team = new Team();
        (new \ReflectionProperty(Team::class, 'id'))->setValue($team, 4);
        $user = (new User())->setUsername('josh')->setName('Josh U')->setCommish($commish)->setTeam($team);
        (new \ReflectionProperty(User::class, 'id'))->setValue($user, 12);

        return $user;
    }
}
