<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// Load .env/.env.local/.env.test.local the same way public/index.php and
// bin/console do — needed once the suite includes kernel-booting tests
// (WebTestCase/KernelTestCase; see tests/Controller/ErrorPagesTest.php),
// since those compile the real container, which resolves %env(DATABASE_URL)%
// eagerly. Every other test in this suite never boots the kernel, so this
// was never needed before Phase 13's error-page tests
// (specs/2026-08-18-error-handling/).
if (file_exists(dirname(__DIR__).'/.env')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}
