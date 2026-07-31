<?php

namespace App\Controller\Admin;

use App\Entity\Config;
use App\Repository\ConfigRepository;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Generic key/value CRUD over the `config` table. The table mixes real
 * settings (draft.hangout.url, protections.deadline, ...) with per-team/
 * per-user draft-runtime state (draft.login.<userid>, draft.team.<teamid>,
 * ...) written by the draft flow; this editor treats every row the same,
 * deliberately no special-casing.
 */
#[Route('/admin/config')]
class AdminConfigController extends AbstractAdminController
{
    #[Route('', name: 'admin_config')]
    public function index(AuthenticationService $auth, ConfigRepository $configs): Response
    {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        return $this->render('admin/config/index.html.twig', [
            'configs' => $configs->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'admin_config_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        AuthenticationService $auth,
        ConfigRepository $configs,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $key   = '';
        $value = '';

        if ($request->isMethod('POST')) {
            $this->assertCsrfToken($request, 'admin_config');

            $key   = trim($request->request->get('key', ''));
            $value = trim($request->request->get('value', ''));

            if ($key === '' || $value === '') {
                $this->addFlash('error', 'Key and value are both required');
            } elseif ($configs->find($key) !== null) {
                $this->addFlash('error', sprintf('Key "%s" already exists', $key));
            } else {
                $config = new Config();
                $config->setName($key);
                $config->setValue($value);
                $em->persist($config);
                $em->flush();
                $this->addFlash('success', sprintf('"%s" added', $key));

                return $this->redirectToRoute('admin_config');
            }
        }

        return $this->render('admin/config/edit.html.twig', ['isEdit' => false, 'key' => $key, 'value' => $value]);
    }

    #[Route('/{key}/edit', name: 'admin_config_edit', requirements: ['key' => '[^/]+'], methods: ['GET', 'POST'])]
    public function edit(
        string $key,
        Request $request,
        AuthenticationService $auth,
        ConfigRepository $configs,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $config = $configs->find($key);
        if (!$config) {
            throw $this->createNotFoundException("No config key \"$key\"");
        }

        $value = $config->getValue();

        if ($request->isMethod('POST')) {
            $this->assertCsrfToken($request, 'admin_config');

            $value = trim($request->request->get('value', ''));
            if ($value === '') {
                $this->addFlash('error', 'Value is required');
            } else {
                $config->setValue($value);
                $em->flush();
                $this->addFlash('success', sprintf('"%s" updated', $key));

                return $this->redirectToRoute('admin_config');
            }
        }

        return $this->render('admin/config/edit.html.twig', ['isEdit' => true, 'key' => $key, 'value' => $value]);
    }

    #[Route('/{key}/delete', name: 'admin_config_delete', requirements: ['key' => '[^/]+'], methods: ['POST'])]
    public function delete(
        string $key,
        Request $request,
        AuthenticationService $auth,
        ConfigRepository $configs,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, 'admin_config');

        $config = $configs->find($key);
        if (!$config) {
            throw $this->createNotFoundException("No config key \"$key\"");
        }

        $em->remove($config);
        $em->flush();
        $this->addFlash('success', sprintf('"%s" deleted', $key));

        return $this->redirectToRoute('admin_config');
    }
}
