<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ForceStatReloadController extends AbstractController
{
    #[Route('/service/force-stat-reload', name: 'service_force_stat_reload', methods: ['GET'])]
    public function __invoke(
        #[Autowire('%env(default:app.default_scripts_dir:SCRIPTS_DIR)%')] string $scriptsPath
    ): Response {
        $outArr = [];
        $this->execScript("$scriptsPath/livescore/livescore.sh 2>&1", $outArr);

        $output = implode("\n", $outArr) . "\n";

        $this->appendLog("$scriptsPath/logs/livelog", $output);

        return new Response($output, Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }

    protected function execScript(string $script, array &$outArr): void
    {
        exec($script, $outArr);
    }

    protected function appendLog(string $path, string $output): void
    {
        file_put_contents($path, $output, FILE_APPEND);
    }
}
