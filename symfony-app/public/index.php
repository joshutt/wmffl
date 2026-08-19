<?php

use App\Kernel;
use App\LegacyBridge;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

//use Symfony\Component\ErrorHandler\Debug;
//use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__).'/vendor/autoload.php';
//require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

/*
 * The kernel will always be available globally, allowing you to
 * access it from your existing application and through it the
 * service container. This allows for introducing new features in
 * the existing application.
 */
global $kernel;

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(
        explode(',', $trustedProxies),
        Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO
    );
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);

if (null !== $request->attributes->get('_route')) {
    // Symfony's router matched a controller for this request. Whatever
    // that controller (or Symfony's own exception handling) produced —
    // 200, a deliberate 404/403 via createNotFoundException()/
    // createAccessDeniedException(), or a 500 — is the response, full
    // stop. Only a request the router couldn't match at all falls back
    // to legacy; a matched route's own 404 must never be handed to
    // LegacyBridge, which would otherwise try (and typically fail) to
    // map it to a file under /football/.
    $response->send();
} else {
    LegacyBridge::handleRequest($request, $response, $kernel->getContainer(), __DIR__);
}

$kernel->terminate($request, $response);


//return function (array $context) {
//    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
//};
