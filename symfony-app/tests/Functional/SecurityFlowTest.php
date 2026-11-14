<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Enum\ActiveEnum;
use App\Repository\UserRepository;
use App\Service\UserEmailService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The login / remember-me / logout / access-control flow through the real
 * firewall (Phase 18 item 1). There is no test database, so UserRepository -
 * the only DB seam on this path (UserProvider, LoginAuthenticator's hash
 * upgrade, lastlog) - is replaced with an in-memory fake, and the pages used
 * (/login/forgotpassword, /account, /admin/users) need nothing else from the DB.
 */
#[AllowMockObjectsWithoutExpectations]
class SecurityFlowTest extends WebTestCase
{
    private KernelBrowser $client;
    /** @var array<string, User> */
    private array $users = [];

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->client = static::createClient();
        $this->client->disableReboot();

        $repo = $this->createMock(UserRepository::class);
        $repo->method('findOneByUsername')->willReturnCallback(fn (string $u) => $this->users[strtolower($u)] ?? null);
        $repo->method('find')->willReturnCallback(function ($id) {
            foreach ($this->users as $user) {
                if ($user->getId() === (int) $id) {
                    return $user;
                }
            }
            return null;
        });
        $repo->method('findAllOrdered')->willReturnCallback(fn () => array_values($this->users));
        $repo->method('upgradePassword')->willReturnCallback(fn (User $u, string $hash) => $u->setPassword($hash));
        static::getContainer()->set(UserRepository::class, $repo);
        static::getContainer()->get('cache.security_limiter')->clear();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    // ---- login ----

    public function testMd5UserLogsInHashIsUpgradedAndSecondLoginWorks(): void
    {
        $user = $this->addUser('owner', md5('short-pw'));

        $this->assertSame('Ok', $this->login('owner', 'short-pw'));
        $this->assertStringStartsWith('$', $user->getPassword(), 'modern hash after first login');
        $this->assertNotSame(md5('short-pw'), $user->getPassword());
        $this->assertTrue($_SESSION['isin']);
        $this->assertSame('owner', $_SESSION['user']);

        $this->logout();
        $this->assertSame('Ok', $this->login('owner', 'short-pw'));
    }

    public function testEscapedLegacyHashLogsIn(): void
    {
        $this->addUser('quoted', md5("it\\'s-a-test"));

        $this->assertSame('Ok', $this->login('quoted', "it's-a-test"));
    }

    public function testFailuresAllGiveTheSameGenericMessage(): void
    {
        $this->addUser('owner', md5('right-password'));
        $this->addUser('gone', md5('right-password'), ActiveEnum::N);
        $this->addUser('blank', '');

        foreach ([['owner', 'wrong'], ['nobody', 'x'], ['gone', 'right-password'], ['blank', '']] as [$u, $p]) {
            $this->assertSame('err - Invalid Username/Password', $this->login($u, $p), "$u should fail");
        }
        $this->assertFalse(!empty($_SESSION['isin']));
    }

    public function testSixthFailedAttemptIsThrottled(): void
    {
        $this->addUser('owner', md5('right-password'));

        foreach (range(1, 5) as $ignored) {
            $this->assertSame('err - Invalid Username/Password', $this->login('owner', 'wrong'));
        }
        $this->assertStringStartsWith('err - Too many failed login attempts', $this->login('owner', 'wrong'));
        $this->assertStringStartsWith('err - Too many failed login attempts', $this->login('owner', 'right-password'));
    }

    public function testBadCsrfTokenIsRejected(): void
    {
        $this->addUser('owner', md5('pw'));

        $this->client->request('POST', '/login/login', ['username' => 'owner', 'password' => 'pw', '_csrf_token' => 'forged']);

        $this->assertStringStartsWith('err - ', $this->client->getResponse()->getContent());
        $this->assertTrue(empty($_SESSION['isin']));
    }

    public function testLegacyPhpLoginUrlStillWorks(): void
    {
        $this->addUser('owner', md5('pw'));

        $this->assertSame('Ok', $this->login('owner', 'pw', path: '/login/login.php'));
    }

    // ---- logout ----

    public function testLogoutClearsStateAndReturnsToReferer(): void
    {
        $this->addUser('owner', md5('pw'));
        $this->login('owner', 'pw');

        $this->client->request('GET', '/account');
        $token = $this->client->getCrawler()->filter('form.form-signout input[name=_csrf_token]')->attr('value');
        $this->client->request('POST', '/login/logout', ['_csrf_token' => $token], [], ['HTTP_REFERER' => 'http://localhost/schedule']);

        $this->assertResponseRedirects('http://localhost/schedule');
        $this->assertTrue(empty($_SESSION['isin']));
        $this->client->request('GET', '/account');
        $this->assertResponseRedirects('/');
    }

    // ---- stay logged in ----

    public function testRememberMeSurvivesSessionLoss(): void
    {
        $this->addUser('owner', md5('pw'));
        $this->login('owner', 'pw', remember: true);
        $this->assertNotNull($this->client->getCookieJar()->get('REMEMBERME'));

        $this->loseSession();
        $this->client->request('GET', '/account');

        $this->assertResponseIsSuccessful();
        $this->assertSame('owner', $_SESSION['user'], 'legacy keys re-projected from the remember-me login');
    }

    public function testWithoutRememberMeSessionLossLogsOut(): void
    {
        $this->addUser('owner', md5('pw'));
        $this->login('owner', 'pw');
        $this->assertNull($this->client->getCookieJar()->get('REMEMBERME'));

        $this->loseSession();
        $this->client->request('GET', '/account');

        $this->assertResponseRedirects('/');
    }

    public function testPasswordChangeInvalidatesOtherRememberMeCookies(): void
    {
        $user = $this->addUser('owner', md5('pw'));
        $this->login('owner', 'pw', remember: true);

        $user->setPassword(password_hash('elsewhere-changed', PASSWORD_BCRYPT, ['cost' => 4]));
        $this->loseSession();
        $this->client->request('GET', '/account');

        $this->assertResponseRedirects('/');
    }

    public function testDeactivatedUserIsLoggedOutOnNextRequest(): void
    {
        $user = $this->addUser('owner', md5('pw'));
        $this->login('owner', 'pw');

        $user->setActive(ActiveEnum::N);
        $this->client->request('GET', '/account');

        $this->assertResponseRedirects('/');
        $this->assertTrue(empty($_SESSION['isin']));
    }

    // ---- access control ----

    public function testAnonymousAccountAndAdminGoHomeWithLoginModal(): void
    {
        foreach (['/account', '/admin/users'] as $path) {
            $this->client->request('GET', $path);
            $this->assertResponseRedirects('/');
            $this->assertSame('1', $this->client->getResponse()->headers->getCookies()[0]->getValue());
        }
    }

    public function testNonCommissionerIsSentHomeFromAdmin(): void
    {
        $this->addUser('owner', md5('pw'));
        $this->login('owner', 'pw');

        $this->client->request('GET', '/admin/users');

        $this->assertResponseRedirects('/');
    }

    public function testCommissionerReachesAdminUsers(): void
    {
        $this->addUser('commish', md5('pw'), commish: true);
        $this->login('commish', 'pw');

        $this->client->request('GET', '/admin/users');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table', 'commish');
        $this->assertSelectorTextContains('table', 'Modern');
    }

    // ---- account page ----

    public function testAccountPasswordChange(): void
    {
        $user = $this->addUser('owner', md5('old-password'));
        $this->login('owner', 'old-password');

        $this->changePassword('wrong', 'brand-new-password', 'brand-new-password');
        $this->assertSelectorTextContains('main .alert-danger', 'current password was not correct');

        $this->changePassword('old-password', 'too-short', 'too-short');
        $this->assertSelectorTextContains('main .alert-danger', 'at least 12');

        $this->changePassword('old-password', 'brand-new-password', 'brand-new-passwerd');
        $this->assertSelectorTextContains('main .alert-danger', 'did not match');

        $this->changePassword('old-password', 'brand-new-password', 'brand-new-password');
        $this->assertSelectorTextContains('main .alert-success', 'password has been changed');
        $this->assertTrue(password_verify('brand-new-password', $user->getPassword()));
        $this->assertSame('owner', $_SESSION['user'], 'still logged in after the change');

        $this->logout();
        $this->assertSame('Ok', $this->login('owner', 'brand-new-password'));
    }

    public function testAccountEmailChanges(): void
    {
        $user = $this->addUser('owner', md5('pw'));
        $this->login('owner', 'pw');

        $this->saveEmails(['newEmail' => 'second@example.com', 'primary' => 'new'], 'wrong');
        $this->assertSelectorTextContains('main .alert-danger', 'current password');

        $this->saveEmails(['newEmail' => 'second@example.com', 'primary' => 'new']);
        $this->assertSelectorTextContains('main .alert-success', 'updated');
        $this->assertSame(['owner@example.com', 'second@example.com'], $user->getEmailAddresses());
        $this->assertSame('second@example.com', $user->getPrimaryEmail());

        $this->saveEmails(['newEmail' => 'SECOND@example.com']);
        $this->assertSelectorTextContains('main .alert-danger', 'already on this account');
    }

    // ---- helpers ----

    private function addUser(string $username, string $password, ActiveEnum $active = ActiveEnum::Y, bool $commish = false): User
    {
        $user = (new User())->setUsername($username)->setName(ucfirst($username))->setPassword($password)
            ->setActive($active)->setCommish($commish);
        (new \ReflectionProperty(User::class, 'id'))->setValue($user, count($this->users) + 100);
        (new UserEmailService())->add($user, "$username@example.com");

        return $this->users[strtolower($username)] = $user;
    }

    private function login(string $username, string $password, bool $remember = false, string $path = '/login/login'): string
    {
        $this->client->request('GET', '/login/forgotpassword');
        $token = $this->client->getCrawler()->filter('form.form-signin input[name=_csrf_token]')->attr('value');

        $params = ['username' => $username, 'password' => $password, '_csrf_token' => $token];
        if ($remember) {
            $params['_remember_me'] = 'on';
        }
        $this->client->request('POST', $path, $params);

        return $this->client->getResponse()->getContent();
    }

    private function logout(): void
    {
        $this->client->request('GET', '/account');
        $token = $this->client->getCrawler()->filter('form.form-signout input[name=_csrf_token]')->attr('value');
        $this->client->request('POST', '/login/logout', ['_csrf_token' => $token]);
    }

    /** The PHP session is gone (expired/cookie deleted); cookies like REMEMBERME remain. */
    private function loseSession(): void
    {
        $this->client->getCookieJar()->expire('MOCKSESSID');
        $_SESSION = [];
    }

    private function changePassword(string $current, string $new, string $confirm): void
    {
        $this->client->request('GET', '/account');
        $form = $this->client->getCrawler()->selectButton('Change Password')->form([
            'currentPassword' => $current, 'newPassword' => $new, 'confirmPassword' => $confirm,
        ]);
        $this->client->submit($form);
        $this->client->followRedirect();
    }

    private function saveEmails(array $fields, string $currentPassword = 'pw'): void
    {
        $this->client->request('GET', '/account');
        $token = $this->client->getCrawler()->filter('form[action="/account/emails"] input[name=_token]')->attr('value');
        $this->client->request('POST', '/account/emails', $fields + ['currentPassword' => $currentPassword, '_token' => $token]);
        $this->client->followRedirect();
    }
}
