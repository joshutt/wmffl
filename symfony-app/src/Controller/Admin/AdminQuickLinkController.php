<?php

namespace App\Controller\Admin;

use App\Entity\QuickLink;
use App\Repository\QuickLinkRepository;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Manage the homepage "Other Links" quicklinks: label, url, an optional
 * visibility window, active flag, and sort order. Replaces the yearly
 * hand-edit of the static list (football/quicklinks.php).
 */
#[Route('/admin/quicklinks')]
class AdminQuickLinkController extends AbstractAdminController
{
    #[Route('', name: 'admin_quicklinks')]
    public function index(AuthenticationService $auth, QuickLinkRepository $quickLinks): Response
    {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        return $this->render('admin/quicklinks/index.html.twig', [
            'links' => $quickLinks->findAllOrdered(),
            'today' => new \DateTimeImmutable('today'),
        ]);
    }

    #[Route('/new', name: 'admin_quicklinks_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        AuthenticationService $auth,
        QuickLinkRepository $quickLinks,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $link = new QuickLink();
        // Default to the end of the list
        $existing = $quickLinks->findAllOrdered();
        $link->setSortOrder($existing === [] ? 1 : end($existing)->getSortOrder() + 1);

        if ($request->isMethod('POST')) {
            $this->assertCsrfToken($request, 'admin_quicklink');
            if ($this->applyForm($request, $link)) {
                $em->persist($link);
                $em->flush();
                $this->addFlash('success', 'Link added');

                return $this->redirectToRoute('admin_quicklinks');
            }
        }

        return $this->render('admin/quicklinks/edit.html.twig', ['link' => $link]);
    }

    #[Route('/{id}/edit', name: 'admin_quicklinks_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        Request $request,
        AuthenticationService $auth,
        QuickLinkRepository $quickLinks,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $link = $quickLinks->find($id);
        if (!$link) {
            throw $this->createNotFoundException("No quicklink with id $id");
        }

        if ($request->isMethod('POST')) {
            $this->assertCsrfToken($request, 'admin_quicklink');
            if ($this->applyForm($request, $link)) {
                $em->flush();
                $this->addFlash('success', 'Link updated');

                return $this->redirectToRoute('admin_quicklinks');
            }
        }

        return $this->render('admin/quicklinks/edit.html.twig', ['link' => $link]);
    }

    #[Route('/{id}/toggle', name: 'admin_quicklinks_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggle(
        int $id,
        Request $request,
        AuthenticationService $auth,
        QuickLinkRepository $quickLinks,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, 'admin_quicklink');

        $link = $quickLinks->find($id);
        if (!$link) {
            throw $this->createNotFoundException("No quicklink with id $id");
        }

        $link->setActive(!$link->isActive());
        $em->flush();
        $this->addFlash('success', sprintf(
            '"%s" %s', $link->getLabel(), $link->isActive() ? 'activated' : 'deactivated'
        ));

        return $this->redirectToRoute('admin_quicklinks');
    }

    #[Route('/{id}/delete', name: 'admin_quicklinks_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        AuthenticationService $auth,
        QuickLinkRepository $quickLinks,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, 'admin_quicklink');

        $link = $quickLinks->find($id);
        if (!$link) {
            throw $this->createNotFoundException("No quicklink with id $id");
        }

        $em->remove($link);
        $em->flush();
        $this->addFlash('success', sprintf('"%s" deleted', $link->getLabel()));

        return $this->redirectToRoute('admin_quicklinks');
    }

    /**
     * Copy submitted fields onto the link. Returns false (with an error
     * flash) when validation fails, leaving the link unchanged.
     */
    private function applyForm(Request $request, QuickLink $link): bool
    {
        $label = trim($request->request->get('label', ''));
        $url   = trim($request->request->get('url', ''));
        if ($label === '' || $url === '') {
            $this->addFlash('error', 'Label and URL are both required');

            return false;
        }

        $startDate = $this->nullableDate($request, 'startDate');
        $endDate   = $this->nullableDate($request, 'endDate');
        if ($startDate === false || $endDate === false) {
            $this->addFlash('error', 'Dates must be valid (or left blank for an open-ended window)');

            return false;
        }
        if ($startDate && $endDate && $startDate > $endDate) {
            $this->addFlash('error', 'The start date must not be after the end date');

            return false;
        }

        $link->setLabel($label);
        $link->setUrl($url);
        $link->setStartDate($startDate);
        $link->setEndDate($endDate);
        $link->setActive($request->request->getBoolean('active'));
        $link->setSortOrder((int) $request->request->get('sortOrder', 0));

        return true;
    }

    /**
     * A date input's value: null when blank, false when unparseable.
     */
    private function nullableDate(Request $request, string $field): \DateTime|null|false
    {
        $value = trim($request->request->get($field, ''));
        if ($value === '') {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d|', $value);
    }
}
