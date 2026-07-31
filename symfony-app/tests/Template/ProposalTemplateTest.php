<?php

namespace App\Tests\Template;

use App\Entity\Issue;
use App\Enum\IssueStatus;
use App\Service\MarkdownService;
use App\Twig\MarkdownExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

/**
 * Renders proposals/list.html.twig and proposals/ballot.html.twig directly
 * to pin the field-visibility split: the list page shows Rationale (and
 * RuleChangeText), never the short Description; the ballot page shows only
 * Description, never Rationale/RuleChangeText.
 */
class ProposalTemplateTest extends TestCase
{
    private Environment $twig;

    protected function setUp(): void
    {
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));
        $this->twig->addExtension(new MarkdownExtension(new MarkdownService()));
        $this->twig->addGlobal('auth', new class {
            public function isLoggedIn(): bool
            {
                return false;
            }
        });

        $generator = new class implements UrlGeneratorInterface {
            public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
            {
                return "/$name" . ($parameters ? '?' . http_build_query($parameters) : '');
            }

            public function setContext(RequestContext $context): void
            {
            }

            public function getContext(): RequestContext
            {
                return new RequestContext();
            }
        };
        $this->twig->addExtension(new RoutingExtension($generator));
        $this->twig->addFunction(new TwigFunction('csrf_token', static fn (string $id): string => "token-$id"));
    }

    public function testListShowsRationaleNotDescription(): void
    {
        $issue = $this->makeIssue(
            description: 'Short description text',
            rationale: 'Rationale explaining the change',
        );

        $html = $this->twig->render('proposals/list.html.twig', [
            'season' => 2026,
            'seasons' => [2026],
            'proposals' => [$issue],
            'onBallot' => [],
        ]);

        $this->assertStringContainsString('Rationale explaining the change', $html);
        $this->assertStringNotContainsString('Short description text', $html);
    }

    public function testBallotShowsDescriptionNotRationaleOrRuleChange(): void
    {
        $issue = $this->makeIssue(
            description: 'Short description text',
            rationale: 'Rationale explaining the change',
            ruleChangeText: 'Change rule IV.A to read something else',
        );

        $html = $this->twig->render('proposals/ballot.html.twig', [
            'isLoggedIn' => true,
            'items' => [[
                'issue' => $issue,
                'currentVote' => '',
                'currentLabel' => '',
                'labels' => ['accept' => 'Accept', 'reject' => 'Reject', 'abstain' => 'Abstain'],
            ]],
        ]);

        $this->assertStringContainsString('Short description text', $html);
        $this->assertStringNotContainsString('Rationale explaining the change', $html);
        $this->assertStringNotContainsString('Change rule IV.A to read something else', $html);
    }

    private function makeIssue(?string $description, ?string $rationale = null, ?string $ruleChangeText = null): Issue
    {
        $issue = new Issue();
        $issue->setIssueNum('26.1');
        $issue->setIssueName('Test Proposal');
        $issue->setSeason(2026);
        $issue->setStatus(IssueStatus::Open);
        $issue->setDescription($description);
        $issue->setRationale($rationale);
        $issue->setRuleChangeText($ruleChangeText);

        return $issue;
    }
}
