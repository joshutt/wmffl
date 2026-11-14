<?php

namespace App\Tests\Functional;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Enum\ActiveEnum;
use App\Service\PasswordResetService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The forgot-password and set-password pages (PasswordResetController). The
 * token/mail rules themselves are unit-tested in PasswordResetServiceTest;
 * here the service is faked so no database is needed.
 */
#[AllowMockObjectsWithoutExpectations]
class PasswordResetFlowTest extends WebTestCase
{
    private KernelBrowser $client;
    private $resets;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->resets = $this->createMock(PasswordResetService::class);
        static::getContainer()->set(PasswordResetService::class, $this->resets);
    }

    public function testForgotResponseIsIdenticalWhetherOrNotTheUserExists(): void
    {
        $this->resets->expects($this->exactly(2))->method('requestReset');

        $bodies = [];
        foreach (['real_owner', 'no_such_user'] as $username) {
            $this->client->request('GET', '/login/forgotpassword');
            $this->client->submit($this->client->getCrawler()->selectButton('Email Me a Link')->form(['username' => $username]));
            $this->assertResponseIsSuccessful();
            $bodies[] = $this->client->getCrawler()->filter('main .alert-success')->text();
        }

        $this->assertSame($bodies[0], $bodies[1]);
        $this->assertStringContainsString('If that account exists', $bodies[0]);
    }

    public function testInvalidOrUsedLinkIsRefused(): void
    {
        $this->resets->method('findUsableToken')->willReturn(null);

        $this->client->request('GET', '/login/reset/expired-or-used');

        $this->assertResponseStatusCodeSame(410);
        $this->assertSelectorTextContains('main .alert-danger', 'invalid, has already been used, or has expired');
    }

    public function testLinkEnforcesMinimumLengthThenSetsPassword(): void
    {
        $token = $this->token();
        $this->resets->method('findUsableToken')->with('good-token')->willReturn($token);
        $this->resets->expects($this->once())->method('resetPassword')->with($token, 'twelve-chars');

        $this->client->request('GET', '/login/reset/good-token');
        $this->assertResponseIsSuccessful();
        $this->assertSame('no-referrer', $this->client->getResponse()->headers->get('Referrer-Policy'));

        $this->client->submit($this->client->getCrawler()->selectButton('Set Password')->form([
            'newPassword' => 'elevenchars', 'confirmPassword' => 'elevenchars',
        ]));
        $this->assertSelectorTextContains('main .alert-danger', 'at least 12');

        $this->client->submit($this->client->getCrawler()->selectButton('Set Password')->form([
            'newPassword' => 'twelve-chars', 'confirmPassword' => 'twelve-chars',
        ]));
        $this->assertSelectorTextContains('main .alert-success', 'Your password has been set');
    }

    private function token(): PasswordResetToken
    {
        $user = (new User())->setUsername('owner')->setActive(ActiveEnum::Y)->setPassword('');

        return new PasswordResetToken($user, str_repeat('a', 64), new \DateTimeImmutable('+1 hour'), new \DateTimeImmutable());
    }
}
