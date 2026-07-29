<?php

namespace App\Controller\Admin;

use App\Entity\Ballot;
use App\Entity\Issue;
use App\Entity\IssueSponsor;
use App\Entity\Team;
use App\Entity\User;
use App\Enum\IssueStatus;
use App\Enum\VoteEnum;
use App\Repository\IssueRepository;
use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Commissioner management of rule proposals: list everything (incl.
 * unpublished member submissions), approve/publish, full CRUD over every
 * field (name, num, season, dates, description, rationale, rule-change
 * markdown, Status), ordered co-sponsors, put-on-ballot, and
 * withdraw/void.
 */
#[Route('/admin/proposals')]
class AdminProposalController extends AbstractAdminController
{
    private const CSRF_ID = 'admin_proposals';

    #[Route('', name: 'admin_proposals', methods: ['GET'])]
    public function index(
        AuthenticationService $auth,
        IssueRepository $issues,
        EntityManagerInterface $em,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $all = $issues->findAllForAdmin();

        // Per-issue ballot vote tally: For (Accept) / Against (Reject) /
        // Abstain / No Vote. Issues that have any ballot row are "on the
        // ballot"; for decided proposals the template shows this tally
        // instead of an "On ballot" label.
        $onBallot = [];
        $ballotTally = [];
        $rows = $em->getConnection()->fetchAllAssociative(
            "SELECT IssueID,
                    SUM(Vote = 'Accept')  AS forVotes,
                    SUM(Vote = 'Reject')  AS against,
                    SUM(Vote = 'Abstain') AS abstain,
                    SUM(Vote = 'No Vote') AS novote
             FROM ballot GROUP BY IssueID"
        );
        foreach ($rows as $row) {
            $id = (int) $row['IssueID'];
            $onBallot[$id] = true;
            $ballotTally[$id] = [
                'for' => (int) $row['forVotes'],
                'against' => (int) $row['against'],
                'abstain' => (int) $row['abstain'],
                'novote' => (int) $row['novote'],
            ];
        }

        $bySeason = [];
        foreach ($all as $issue) {
            $bySeason[$issue->getSeason()][] = $issue;
        }

        return $this->render('admin/proposals/index.html.twig', [
            'bySeason' => $bySeason,
            'onBallot' => $onBallot,
            'ballotTally' => $ballotTally,
            'pendingCount' => count(array_filter($all, static fn (Issue $i) => !$i->isPublished())),
            'teams' => $em->getRepository(Team::class)->findBy(['active' => true], ['name' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'admin_proposals_new', methods: ['GET'])]
    public function new(
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
        EntityManagerInterface $em,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        return $this->render('admin/proposals/form.html.twig', [
            'issue' => null,
            'currentSeason' => $seasonWeek->getCurrentSeason(),
            'users' => $this->activeUsers($em),
        ]);
    }

    #[Route('/new', name: 'admin_proposals_create', methods: ['POST'])]
    public function create(
        Request $request,
        AuthenticationService $auth,
        EntityManagerInterface $em,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, self::CSRF_ID);

        $issue = new Issue();
        $this->hydrate($issue, $request, $em);
        $em->persist($issue);
        $em->flush();

        $this->addFlash('success', 'Proposal created');

        return $this->redirectToRoute('admin_proposals');
    }

    #[Route('/{id}/edit', name: 'admin_proposals_edit', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function edit(
        int $id,
        AuthenticationService $auth,
        IssueRepository $issues,
        EntityManagerInterface $em,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $issue = $issues->find($id);
        if (!$issue) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/proposals/form.html.twig', [
            'issue' => $issue,
            'currentSeason' => $issue->getSeason(),
            'users' => $this->activeUsers($em),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_proposals_update', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function update(
        int $id,
        Request $request,
        AuthenticationService $auth,
        IssueRepository $issues,
        EntityManagerInterface $em,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, self::CSRF_ID);

        $issue = $issues->find($id);
        if (!$issue) {
            throw $this->createNotFoundException();
        }

        $this->hydrate($issue, $request, $em);
        $em->flush();

        $this->addFlash('success', 'Proposal saved');

        return $this->redirectToRoute('admin_proposals');
    }

    #[Route('/{id}/approve', name: 'admin_proposals_approve', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function approve(
        int $id,
        Request $request,
        AuthenticationService $auth,
        IssueRepository $issues,
        EntityManagerInterface $em,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, self::CSRF_ID);

        $issue = $issues->find($id);
        if (!$issue) {
            throw $this->createNotFoundException();
        }

        $issue->setPublished(true);
        $em->flush();

        $this->addFlash('success', 'Proposal published');

        return $this->redirectToRoute('admin_proposals');
    }

    #[Route('/{id}/withdraw', name: 'admin_proposals_withdraw', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function withdraw(
        int $id,
        Request $request,
        AuthenticationService $auth,
        IssueRepository $issues,
        EntityManagerInterface $em,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, self::CSRF_ID);

        $issue = $issues->find($id);
        if (!$issue) {
            throw $this->createNotFoundException();
        }

        $issue->setStatus(IssueStatus::Withdrawn);
        $em->flush();

        $this->addFlash('success', 'Proposal withdrawn');

        return $this->redirectToRoute('admin_proposals');
    }

    #[Route('/{id}/delete', name: 'admin_proposals_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        AuthenticationService $auth,
        IssueRepository $issues,
        EntityManagerInterface $em,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, self::CSRF_ID);

        $issue = $issues->find($id);
        if (!$issue) {
            throw $this->createNotFoundException();
        }

        // Remove any ballot rows first (no cascade from ballot -> issue).
        $em->getConnection()->executeStatement(
            'DELETE FROM ballot WHERE IssueID = :id',
            ['id' => $id]
        );
        $em->remove($issue);
        $em->flush();

        $this->addFlash('success', 'Proposal deleted');

        return $this->redirectToRoute('admin_proposals');
    }

    #[Route('/{id}/ballot', name: 'admin_proposals_putonballot', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function putOnBallot(
        int $id,
        Request $request,
        AuthenticationService $auth,
        IssueRepository $issues,
        EntityManagerInterface $em,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, self::CSRF_ID);

        $issue = $issues->find($id);
        if (!$issue) {
            throw $this->createNotFoundException();
        }

        $startDate = $request->request->get('startDate');
        $issue->setStartDate($startDate ? new \DateTime($startDate) : new \DateTime());

        $deadline = $request->request->get('deadline');
        $issue->setDeadline($deadline ? new \DateTime($deadline) : null);

        // An issue must be published to be on the ballot.
        $issue->setPublished(true);

        foreach ($request->request->all('teams') as $teamId) {
            $team = $em->getRepository(Team::class)->find((int) $teamId);
            if (!$team) {
                continue;
            }
            // Skip if a ballot row already exists for this pair.
            $existing = $em->getConnection()->fetchOne(
                'SELECT 1 FROM ballot WHERE IssueID = :issueId AND TeamID = :teamId',
                ['issueId' => $id, 'teamId' => (int) $teamId]
            );
            if ($existing !== false) {
                continue;
            }

            $ballot = (new Ballot())
                ->setIssue($issue)
                ->setTeam($team)
                ->setVote(VoteEnum::NoVote);
            $em->persist($ballot);
        }

        $em->flush();

        $this->addFlash('success', 'Proposal put on the ballot');

        return $this->redirectToRoute('admin_proposals');
    }

    /**
     * Populate every editable field from the POST, including ordered
     * co-sponsors (submitted as sponsors[] of user ids, in display order).
     */
    private function hydrate(Issue $issue, Request $request, EntityManagerInterface $em): void
    {
        $post = $request->request;

        $issue->setIssueNum(trim((string) $post->get('issueNum', '')));
        $issue->setIssueName(trim((string) $post->get('issueName', '')));
        $issue->setSeason((int) $post->get('season'));
        $issue->setDescription(($d = trim((string) $post->get('description', ''))) === '' ? null : $d);
        $issue->setRationale(($r = trim((string) $post->get('rationale', ''))) === '' ? null : $r);
        $issue->setRuleChangeText(($c = trim((string) $post->get('ruleChange', ''))) === '' ? null : $c);
        $issue->setStatus(IssueStatus::from((string) $post->get('status', IssueStatus::Open->value)));
        $issue->setPublished($post->getBoolean('published'));

        $start = $post->get('startDate');
        $issue->setStartDate($start ? new \DateTime($start) : null);
        $deadline = $post->get('deadline');
        $issue->setDeadline($deadline ? new \DateTime($deadline) : null);

        // Reconcile the ordered co-sponsor set against the submitted user
        // ids. IssueSponsor has a composite PK (Issue, User); removing a row
        // and re-adding the same (Issue, User) within one flush collides in
        // Doctrine's identity map (EntityIdentityCollisionException). So we
        // update rows that survive in place and only remove/add the diff,
        // rather than clearing and recreating every sponsor.
        $desired = [];   // userId => sortOrder, de-duplicated, in form order
        $order = 0;
        foreach ($post->all('sponsors') as $userId) {
            $userId = (int) $userId;
            if ($userId <= 0 || isset($desired[$userId])) {
                continue;
            }
            $desired[$userId] = $order++;
        }

        // Keep/reorder existing sponsors; drop those no longer desired.
        foreach ($issue->getSponsors()->toArray() as $sponsor) {
            $uid = $sponsor->getUser()?->getId();
            if ($uid !== null && isset($desired[$uid])) {
                $sponsor->setSortOrder($desired[$uid]);
                unset($desired[$uid]);
            } else {
                $issue->removeSponsor($sponsor);
            }
        }

        // Add sponsors that are newly selected.
        foreach ($desired as $userId => $sortOrder) {
            $user = $em->find(User::class, $userId);
            if ($user !== null) {
                $issue->addSponsor(new IssueSponsor($issue, $user, $sortOrder));
            }
        }
    }

    /** @return User[] active users, name-ordered, for the sponsor picker. */
    private function activeUsers(EntityManagerInterface $em): array
    {
        return $em->createQuery(
            'SELECT u FROM App\Entity\User u WHERE u.active = :active ORDER BY u.name ASC'
        )->setParameter('active', \App\Enum\ActiveEnum::Y)->getResult();
    }
}
