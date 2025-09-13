<?php

// src/LegacyBridge.php
namespace App;

use Exception;
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
     * @throws Exception
     */
    public static function getLegacyScript(Request $request): string
    {
        $requestPathInfo = $request->getPathInfo();
        $projectRoot = dirname(__DIR__, 2);
        $legacyRoot = $projectRoot . '/football';

        // Set up the include path
        $includePath = '/home/joshutt/php';
        $includePath .= PATH_SEPARATOR.$legacyRoot;
        $includePath .= PATH_SEPARATOR.$projectRoot;

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
        error_log("PAGE Legacy: $requestPathInfo");


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

        error_log("Path: $path");
        error_log('Real path: '.realpath($path));

        // Check if the path contains 'admin' and log it
        if ($requestPathInfo && str_contains($requestPathInfo, 'admin')) {
            error_log("Admin path accessed: $requestPathInfo");
        }

        if (is_dir($path) || is_file($path)) {
            chdir(dirname($path));
            return $path;
        } else {
            throw new Exception("Unhandled legacy mapping for $requestPathInfo");
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

//        throw new \Exception("Unhandled legacy mapping for $requestPathInfo");
    }

    /**
     * @throws Exception
     */
    public static function handleRequest(Request $request, Response $response, ContainerInterface $container, string $publicDirectory): void
    {
        $legacyScriptFilename = LegacyBridge::getLegacyScript($request);

        // Make the Symfony entity manager available to the legacy script's scope.
        // This variable will be accessible by the required file below.
        $symEntityManager = $container->get('doctrine')->getManager();

        // Possibly (re-)set some env vars (e.g. to handle forms
        // posting to PHP_SELF):
        $p = $request->getPathInfo();
        $_SERVER['PHP_SELF'] = $p;
        $_SERVER['SCRIPT_NAME'] = $p;
        $_SERVER['SCRIPT_FILENAME'] = $legacyScriptFilename;

        require $legacyScriptFilename;
    }
}
