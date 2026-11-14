<?php

namespace App\Service;

use App\Security\LegacySessionProjector;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Facade over the logged-in identity, used by controllers, templates (the
 * `auth` Twig global) and tests. Replaces legacy globals $isin, $fullname,
 * $teamnum.
 *
 * Symfony Security authenticates (Phase 18 item 1); LegacySessionProjector
 * projects the result into six raw root-level $_SESSION keys on every
 * request, and this reads those keys - the same ones legacy pages read - so
 * Symfony and legacy share one view of who is logged in, including while a
 * commissioner is impersonating a team (becomeTeam()).
 *
 * The session is started lazily to ensure $_SESSION is populated on
 * pure Symfony routes where legacy start.php has not run.
 */
class AuthenticationService
{
    public function __construct(private ?RequestStack $requestStack = null)
    {
    }

    private function ensureSessionStarted(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        // Start through Symfony's session layer when a request is in scope: a
        // raw session_start() here would leave Symfony's storage unaware the
        // session is open, and any later Session::start() — notably CSRF
        // validation in isCsrfTokenValid() — throws "Failed to start the
        // session: already started by PHP."
        $request = $this->requestStack?->getMainRequest();
        if ($request?->hasSession()) {
            $request->getSession()->start();
        } else {
            session_start();
        }
    }

    /**
     * Check if user is logged in
     * Replaces legacy $isin global
     */
    public function isLoggedIn(): bool
    {
        $this->ensureSessionStarted();
        return !empty($_SESSION['isin'] ?? null);
    }

    /**
     * Get logged-in user's full name
     * Replaces legacy $fullname global
     */
    public function getFullName(): ?string
    {
        $this->ensureSessionStarted();
        return $_SESSION['fullname'] ?? null;
    }

    /**
     * Get logged-in user's team number
     * Replaces legacy $teamnum global
     */
    public function getTeamNumber(): ?int
    {
        $this->ensureSessionStarted();
        $teamnum = $_SESSION['teamnum'] ?? null;
        return $teamnum !== null ? (int) $teamnum : null;
    }

    /**
     * Get logged-in user's ID
     * Replaces legacy $usernum global
     */
    public function getUserId(): ?int
    {
        $this->ensureSessionStarted();
        $usernum = $_SESSION['usernum'] ?? null;
        return $usernum !== null ? (int) $usernum : null;
    }

    /**
     * Check if the logged-in user is a commissioner
     */
    public function isCommissioner(): bool
    {
        return $this->isLoggedIn() && !empty($_SESSION['commish'] ?? null);
    }

    /**
     * Overwrite the session to impersonate a team (commissioner-only tool).
     * Clears the commish flag so the impersonated session has no elevated rights.
     */
    public function becomeTeam(int $teamId, string $name, string $username, int $userId): void
    {
        $this->ensureSessionStarted();
        $_SESSION['isin']     = true;
        $_SESSION['teamnum']  = $teamId;
        $_SESSION['usernum']  = $userId;
        $_SESSION['fullname'] = $name;
        $_SESSION['user']     = $username;
        $_SESSION['message']  = '';
        unset($_SESSION['commish']);
        // Tells LegacySessionSubscriber not to re-project the commissioner's
        // own identity over this one; logout clears it.
        $_SESSION[LegacySessionProjector::IMPERSONATING] = true;
    }
}
