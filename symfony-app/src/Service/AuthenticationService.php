<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service to manage authentication state from legacy session.
 * Replaces legacy globals $isin, $fullname, $teamnum
 *
 * Reads directly from $_SESSION because the legacy login code sets variables
 * at the root session level, while Symfony's Session component stores its
 * own data under $_SESSION['_sf2_attributes']. Reading $_SESSION directly
 * ensures both Symfony controllers and legacy code share the same auth state.
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
    }
}
