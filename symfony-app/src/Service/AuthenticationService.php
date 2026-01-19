<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Service to manage authentication state from legacy session.
 * Replaces legacy globals $isin, $fullname, $teamnum
 */
class AuthenticationService
{
    public function __construct(
        private RequestStack $requestStack
    ) {
    }

    private function getSession(): ?SessionInterface
    {
        $request = $this->requestStack->getCurrentRequest();
        return $request?->getSession();
    }

    /**
     * Check if user is logged in
     * Replaces legacy $isin global
     */
    public function isLoggedIn(): bool
    {
        $session = $this->getSession();
        if (!$session) {
            return false;
        }

        $isin = $session->get('isin');
        return !empty($isin);
    }

    /**
     * Get logged-in user's full name
     * Replaces legacy $fullname global
     */
    public function getFullName(): ?string
    {
        $session = $this->getSession();
        if (!$session) {
            return null;
        }

        return $session->get('fullname');
    }

    /**
     * Get logged-in user's team number
     * Replaces legacy $teamnum global
     */
    public function getTeamNumber(): ?int
    {
        $session = $this->getSession();
        if (!$session) {
            return null;
        }

        $teamnum = $session->get('teamnum');
        return $teamnum !== null ? (int) $teamnum : null;
    }

    /**
     * Get logged-in user's ID
     * Replaces legacy $userid global
     */
    public function getUserId(): ?int
    {
        $session = $this->getSession();
        if (!$session) {
            return null;
        }

        $userid = $session->get('userid');
        return $userid !== null ? (int) $userid : null;
    }

    /**
     * Check if the logged-in user is a commissioner (team 2)
     */
    public function isCommissioner(): bool
    {
        return $this->isLoggedIn() && $this->getTeamNumber() === 2;
    }
}
