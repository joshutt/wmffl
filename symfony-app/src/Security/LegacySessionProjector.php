<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Writes the six raw $_SESSION identity keys legacy pages (and the
 * AuthenticationService facade) read - `isin`, `teamnum`, `usernum`, `user`,
 * `fullname`, `commish` (+ `message`) - from the Symfony-authenticated user
 * (Phase 18 item 1). Symfony Security is the source of truth; these keys are
 * only a projection of it, rewritten on every request by
 * LegacySessionSubscriber.
 *
 * The `impersonating` marker is set by AuthenticationService::becomeTeam():
 * while it is present the per-request sync leaves the (impersonated) keys
 * alone; logout or a fresh login removes it.
 */
class LegacySessionProjector
{
    public const KEYS = ['isin', 'teamnum', 'usernum', 'user', 'fullname', 'commish', 'message'];
    public const IMPERSONATING = 'impersonating';

    public function __construct(private ?RequestStack $requestStack = null)
    {
    }

    public function project(User $user): void
    {
        $this->ensureSessionStarted();
        $_SESSION['isin'] = true;
        $_SESSION['teamnum'] = $user->getTeam()?->getId();
        $_SESSION['usernum'] = $user->getId();
        $_SESSION['user'] = $user->getUsername();
        $_SESSION['fullname'] = $user->getName();
        $_SESSION['commish'] = (bool) $user->isCommish();
        $_SESSION['message'] = '';
        unset($_SESSION[self::IMPERSONATING]);
    }

    public function clear(): void
    {
        $this->ensureSessionStarted();
        foreach (self::KEYS as $key) {
            unset($_SESSION[$key]);
        }
        unset($_SESSION[self::IMPERSONATING]);
    }

    public function isImpersonating(): bool
    {
        $this->ensureSessionStarted();

        return !empty($_SESSION[self::IMPERSONATING] ?? null);
    }

    /** Whether any identity key is present (so clearing is needed). */
    public function hasIdentity(): bool
    {
        $this->ensureSessionStarted();

        return !empty($_SESSION['isin'] ?? null) || isset($_SESSION[self::IMPERSONATING]);
    }

    /**
     * Through Symfony's session when a request is in scope (a raw
     * session_start() would leave Symfony's storage unaware the session is
     * open - see AuthenticationService::ensureSessionStarted()).
     */
    private function ensureSessionStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $request = $this->requestStack?->getMainRequest();
            if ($request?->hasSession()) {
                $request->getSession()->start();
            } elseif (!headers_sent()) {
                session_start();
            }
        }

        // Mock session storage (tests) never populates $_SESSION.
        $_SESSION ??= [];
    }
}
