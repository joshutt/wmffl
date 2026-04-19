<?php

namespace App\Service;

/**
 * Service to manage authentication state from legacy session.
 * Replaces legacy globals $isin, $fullname, $teamnum
 *
 * Reads directly from $_SESSION because the legacy login code sets variables
 * at the root session level, while Symfony's Session component stores its
 * own data under $_SESSION['_sf2_attributes']. Reading $_SESSION directly
 * ensures both Symfony controllers and legacy code share the same auth state.
 *
 * session_start() is called lazily to ensure $_SESSION is populated on
 * pure Symfony routes where legacy start.php has not run.
 */
class AuthenticationService
{
    private function ensureSessionStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
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
}
