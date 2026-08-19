<?php

// src/LegacyBridge.php
namespace App;

use App\Exception\LegacyRouteNotFoundException;
use App\Service\LegacyErrorPageService;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LegacyBridge
{

    /**
     * Map the incoming request to the right file. This is the
     * key function of the LegacyBridge.
     *
     * Sample code only. Your implementation will vary, depending on the
     * architecture of the legacy code and how it's executed.
     *
     * If your mapping is complicated, you may want to write unit tests
     * to verify your logic, hence this is public static.
     * @param Request $request
     * @return string
     * @throws LegacyRouteNotFoundException if the path has no Symfony route
     *         and doesn't map to a real file under /football/ either
     */
    public static function getLegacyScript(Request $request): string
    {
        $requestPathInfo = $request->getPathInfo();
        $projectRoot = dirname(__DIR__, 2);
        $legacyRoot = $projectRoot . '/football';

        // Set up the include path. An optional extra directory (e.g. a shared
        // PHP library dir outside the project) can be supplied per-environment
        // via the LEGACY_INCLUDE_PATH env var instead of being hard-coded.
        $includePath = $legacyRoot;
        $includePath .= PATH_SEPARATOR.$projectRoot;

        $extraIncludePath = $_SERVER['LEGACY_INCLUDE_PATH'] ?? $_ENV['LEGACY_INCLUDE_PATH'] ?? '';
        if ($extraIncludePath !== '' && is_dir($extraIncludePath)) {
            $includePath = $extraIncludePath.PATH_SEPARATOR.$includePath;
        }

        // Add src, lib and conf if the directories exist
        if (is_dir($projectRoot.'/src')) {
            $includePath .= PATH_SEPARATOR.$projectRoot.'/src';
        }
        if (is_dir($projectRoot.'/lib')) {
            $includePath .= PATH_SEPARATOR.$projectRoot.'/lib';
        }
        if (is_dir($projectRoot.'/conf')) {
            $includePath .= PATH_SEPARATOR.$projectRoot.'/conf';
        }

        set_include_path(get_include_path().PATH_SEPARATOR. $includePath);

        if (!is_dir($projectRoot.'/logs')) {
            mkdir($projectRoot.'/logs', 0775, true);
        }

        // set the log files
        ini_set('error_log', "$projectRoot/logs/wmffl.log");
        ini_set('log_errors', 1);

        // Keep the original path info if you need it before overwriting $requestPathInfo
        $originalRequestPathInfo = $request->getPathInfo();
        if (str_starts_with($originalRequestPathInfo, '/img/')) {
            $requestPathInfo = '/img.php'; // You're already correctly using $requestPathInfo later
            // Use the original path string for substr
            $_REQUEST['url'] = substr($originalRequestPathInfo, 7); // e.g., from "/img/S/logo.gif" to "logo.gif"
            $_REQUEST['size'] = substr($originalRequestPathInfo, 5, 1); // e.g., from "/img/S/logo.gif" to "S"
        }

        // If it's a directory but not a trailing slash, add one
        if (is_dir($legacyRoot.$requestPathInfo) && !str_ends_with($requestPathInfo, '/')) {
            $requestPathInfo .= '/';
        }

        // If it's a directory refer to index
        if (str_ends_with($requestPathInfo, '/')) {
            $requestPathInfo .= 'index.php';
        }

        // If it doesn't include the ending add it, using a switch
        $extension = pathinfo($requestPathInfo, PATHINFO_EXTENSION);
        switch ($extension) {
            case 'css':
            case 'js':
            case 'gif':
            case 'jpg':
                // Do nothing, there are fine as they are
                break;
            case '':
                $requestPathInfo .= '.php';
                break;
        }

        $path =  realpath("$legacyRoot/$requestPathInfo");

        if (is_dir($path) || is_file($path)) {
            chdir(dirname($path));
            return $path;
        } else {
            throw new LegacyRouteNotFoundException("Unhandled legacy mapping for $requestPathInfo");
        }


//        // Map a route to a legacy script:
//        if ($requestPathInfo == '/customer/') {
//            return "{$legacyRoot}src/customers/list.php";
//        }
//
//        // Map a direct file call, e.g. an ajax call:
//        if ($requestPathInfo == 'inc/ajax_cust_details.php') {
//            return "{$legacyRoot}inc/ajax_cust_details.php";
//        }

        // ... etc.
    }

    public static function handleRequest(Request $request, Response $response, ContainerInterface $container, string $publicDirectory): void
    {
        $errorPages = $container->get(LegacyErrorPageService::class);

        try {
            $legacyScriptFilename = LegacyBridge::getLegacyScript($request);
        } catch (LegacyRouteNotFoundException $e) {
            // Symfony's router had no route, and this path doesn't map to a
            // real file under /football/ either — a genuine dead end. Log
            // it and render the branded 404 instead of letting the
            // exception escape uncaught (it would, since the Symfony
            // kernel has already finished handling this request by the
            // time we get here).
            $errorPages->logNotFound($e->getMessage());
            self::respond($errorPages, 404);
            return;
        }

        // Make the Symfony entity manager available to the legacy script's scope.
        // This variable will be accessible by the required file below.
        $symEntityManager = $container->get('doctrine')->getManager();

        // Inject Symfony services as global variables for legacy code compatibility
        $seasonWeekService = $container->get('App\Service\SeasonWeekService');
        $authService = $container->get('App\Service\AuthenticationService');

        // Make season/week data available as globals (replaces connect.php logic)
        $GLOBALS['currentSeason'] = $seasonWeekService->getCurrentSeason();
        $GLOBALS['currentWeek'] = $seasonWeekService->getCurrentWeek();
        $GLOBALS['weekName'] = $seasonWeekService->getWeekName();
        $GLOBALS['previousWeekName'] = $seasonWeekService->getPreviousWeekName();
        $GLOBALS['previousWeek'] = $seasonWeekService->getPreviousWeek();
        $GLOBALS['previousWeekSeason'] = $seasonWeekService->getPreviousWeekSeason();

        // Make authentication data available as globals (replaces start.php logic)
        $GLOBALS['isin'] = $authService->isLoggedIn();
        $GLOBALS['fullname'] = $authService->getFullName();
        $GLOBALS['teamnum'] = $authService->getTeamNumber();

        // Possibly (re-)set some env vars (e.g. to handle forms
        // posting to PHP_SELF):
        $p = $request->getPathInfo();
        $_SERVER['PHP_SELF'] = $p;
        $_SERVER['SCRIPT_NAME'] = $p;
        $_SERVER['SCRIPT_FILENAME'] = $legacyScriptFilename;

        // Guard against a legacy fatal (or uncaught exception) producing a
        // raw, unstyled PHP error dump instead of a logged, branded 500.
        // Two layers, because PHP doesn't route every kind of failure
        // through normal exception handling:
        //  - try/catch below covers Throwables — modern PHP turns most
        //    fatal-class errors (TypeError, ParseError in an included
        //    file, etc.) into catchable Error objects.
        //  - the shutdown function covers what's left: true fatals
        //    (out-of-memory, execution timeout) that PHP never throws as
        //    an object at all.
        // $errorHandled prevents the shutdown function from double-firing
        // when the catch block below already logged and rendered — it
        // still runs at request end either way, so it has to check first.
        $errorHandled = false;

        register_shutdown_function(static function () use ($errorPages, $legacyScriptFilename, &$errorHandled): void {
            if ($errorHandled) {
                return;
            }

            $error = error_get_last();
            if (!self::isFatalErrorType($error)) {
                // Not a fatal (could be a stray warning/notice from a
                // successful request) — nothing to do.
                return;
            }

            $errorPages->logFatal(sprintf(
                'Legacy fatal error in %s: %s in %s on line %d',
                $legacyScriptFilename,
                $error['message'],
                $error['file'],
                $error['line'],
            ));
            self::respond($errorPages, 500);
        });

        try {
            require $legacyScriptFilename;
        } catch (\Throwable $e) {
            $errorHandled = true;
            $errorPages->logFatal(sprintf('Uncaught %s in legacy script %s', get_class($e), $legacyScriptFilename), $e);
            self::respond($errorPages, 500);
        }
    }

    /**
     * Whether an error_get_last()-shaped array represents one of PHP's
     * true fatal error types — the ones that never surface as a catchable
     * \Throwable and so can only be observed here, in a shutdown function.
     * A plain static method, not inlined in the shutdown closure, so this
     * decision is unit-testable without needing to trigger a real fatal.
     *
     * @param array{type: int, message: string, file: string, line: int}|null $error
     */
    public static function isFatalErrorType(?array $error): bool
    {
        if ($error === null) {
            return false;
        }

        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR];
        return in_array($error['type'], $fatalTypes, true);
    }

    /**
     * Emit the branded error page for the given status, unless the legacy
     * script already sent output (headers or otherwise) before failing —
     * in that case there's a partial response on the wire already and
     * layering another full HTML page on top of it would just corrupt it,
     * so we settle for having logged the failure.
     */
    private static function respond(LegacyErrorPageService $errorPages, int $statusCode): void
    {
        if (headers_sent()) {
            return;
        }

        http_response_code($statusCode);
        echo $errorPages->renderErrorPage($statusCode);
    }
}
