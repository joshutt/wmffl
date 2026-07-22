<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Per-season league rules: schedule structure, finances and scoring.
 * League rules changed over the years but were hardcoded as constants;
 * this table is the source of truth, backfilled/corrected via the
 * admin Season Rules pages (`verified` tracks which historical seasons
 * have had their recreated rules confirmed).
 */
#[ORM\Entity]
#[ORM\Table(name: 'seasons')]
class Season
{
    #[ORM\Id]
    #[ORM\Column(name: 'season', type: 'integer')]
    private ?int $season = null;

    // Structure
    #[ORM\Column(name: 'regular_season_weeks', type: 'integer', options: ['default' => 14])]
    private int $regularSeasonWeeks = 14;

    #[ORM\Column(name: 'total_weeks', type: 'integer', options: ['default' => 16])]
    private int $totalWeeks = 16;

    #[ORM\Column(name: 'max_active_players', type: 'integer', options: ['default' => 25])]
    private int $maxActivePlayers = 25;

    #[ORM\Column(name: 'num_of_games', type: 'integer', options: ['default' => 84])]
    private int $numOfGames = 84;

    // Finance
    #[ORM\Column(name: 'entry_fee', type: 'decimal', precision: 6, scale: 2, options: ['default' => '75.00'])]
    private string $entryFee = '75.00';

    #[ORM\Column(name: 'illegal_activation_fine', type: 'decimal', precision: 5, scale: 2, options: ['default' => '5.00'])]
    private string $illegalActivationFine = '5.00';

    #[ORM\Column(name: 'bye_week_activation_fine', type: 'decimal', precision: 5, scale: 2, options: ['default' => '1.00'])]
    private string $byeWeekActivationFine = '1.00';

    #[ORM\Column(name: 'extra_transaction_fine', type: 'decimal', precision: 5, scale: 2, options: ['default' => '1.00'])]
    private string $extraTransactionFine = '1.00';

    #[ORM\Column(name: 'win_percent', type: 'decimal', precision: 5, scale: 4, options: ['default' => '0.2500'])]
    private string $winPercent = '0.2500';

    #[ORM\Column(name: 'post_percent', type: 'decimal', precision: 5, scale: 4, options: ['default' => '0.5000'])]
    private string $postPercent = '0.5000';

    #[ORM\Column(name: 'div_percent', type: 'decimal', precision: 5, scale: 4, options: ['default' => '0.0500'])]
    private string $divPercent = '0.0500';

    #[ORM\Column(name: 'playoff_percent', type: 'decimal', precision: 5, scale: 4, options: ['default' => '0.0500'])]
    private string $playoffPercent = '0.0500';

    #[ORM\Column(name: 'final_percent', type: 'decimal', precision: 5, scale: 4, options: ['default' => '0.2500'])]
    private string $finalPercent = '0.2500';

    #[ORM\Column(name: 'champ_percent', type: 'decimal', precision: 5, scale: 4, options: ['default' => '0.5000'])]
    private string $champPercent = '0.5000';

    // Scoring
    #[ORM\Column(name: 'scoring_strategy', length: 32, options: ['default' => 'standard'])]
    private string $scoringStrategy = 'standard';

    /** @var array<string, mixed> flat parameter key => value map */
    #[ORM\Column(name: 'scoring_rules', type: 'json')]
    private array $scoringRules = [];

    // Workflow
    #[ORM\Column(name: 'verified', type: 'boolean', options: ['default' => false])]
    private bool $verified = false;

    #[ORM\Column(name: 'notes', type: 'text', nullable: true)]
    private ?string $notes = null;

    public function getSeason(): ?int
    {
        return $this->season;
    }

    public function setSeason(int $season): static
    {
        $this->season = $season;
        return $this;
    }

    public function getRegularSeasonWeeks(): int
    {
        return $this->regularSeasonWeeks;
    }

    public function setRegularSeasonWeeks(int $regularSeasonWeeks): static
    {
        $this->regularSeasonWeeks = $regularSeasonWeeks;
        return $this;
    }

    public function getTotalWeeks(): int
    {
        return $this->totalWeeks;
    }

    public function setTotalWeeks(int $totalWeeks): static
    {
        $this->totalWeeks = $totalWeeks;
        return $this;
    }

    public function getMaxActivePlayers(): int
    {
        return $this->maxActivePlayers;
    }

    public function setMaxActivePlayers(int $maxActivePlayers): static
    {
        $this->maxActivePlayers = $maxActivePlayers;
        return $this;
    }

    public function getNumOfGames(): int
    {
        return $this->numOfGames;
    }

    public function setNumOfGames(int $numOfGames): static
    {
        $this->numOfGames = $numOfGames;
        return $this;
    }

    public function getEntryFee(): float
    {
        return (float) $this->entryFee;
    }

    public function setEntryFee(float $entryFee): static
    {
        $this->entryFee = number_format($entryFee, 2, '.', '');
        return $this;
    }

    public function getIllegalActivationFine(): float
    {
        return (float) $this->illegalActivationFine;
    }

    public function setIllegalActivationFine(float $fine): static
    {
        $this->illegalActivationFine = number_format($fine, 2, '.', '');
        return $this;
    }

    public function getByeWeekActivationFine(): float
    {
        return (float) $this->byeWeekActivationFine;
    }

    public function setByeWeekActivationFine(float $fine): static
    {
        $this->byeWeekActivationFine = number_format($fine, 2, '.', '');
        return $this;
    }

    public function getExtraTransactionFine(): float
    {
        return (float) $this->extraTransactionFine;
    }

    public function setExtraTransactionFine(float $fine): static
    {
        $this->extraTransactionFine = number_format($fine, 2, '.', '');
        return $this;
    }

    public function getWinPercent(): float
    {
        return (float) $this->winPercent;
    }

    public function setWinPercent(float $percent): static
    {
        $this->winPercent = number_format($percent, 4, '.', '');
        return $this;
    }

    public function getPostPercent(): float
    {
        return (float) $this->postPercent;
    }

    public function setPostPercent(float $percent): static
    {
        $this->postPercent = number_format($percent, 4, '.', '');
        return $this;
    }

    public function getDivPercent(): float
    {
        return (float) $this->divPercent;
    }

    public function setDivPercent(float $percent): static
    {
        $this->divPercent = number_format($percent, 4, '.', '');
        return $this;
    }

    public function getPlayoffPercent(): float
    {
        return (float) $this->playoffPercent;
    }

    public function setPlayoffPercent(float $percent): static
    {
        $this->playoffPercent = number_format($percent, 4, '.', '');
        return $this;
    }

    public function getFinalPercent(): float
    {
        return (float) $this->finalPercent;
    }

    public function setFinalPercent(float $percent): static
    {
        $this->finalPercent = number_format($percent, 4, '.', '');
        return $this;
    }

    public function getChampPercent(): float
    {
        return (float) $this->champPercent;
    }

    public function setChampPercent(float $percent): static
    {
        $this->champPercent = number_format($percent, 4, '.', '');
        return $this;
    }

    public function getScoringStrategy(): string
    {
        return $this->scoringStrategy;
    }

    public function setScoringStrategy(string $scoringStrategy): static
    {
        $this->scoringStrategy = $scoringStrategy;
        return $this;
    }

    /** @return array<string, mixed> */
    public function getScoringRules(): array
    {
        return $this->scoringRules;
    }

    /** @param array<string, mixed> $scoringRules */
    public function setScoringRules(array $scoringRules): static
    {
        $this->scoringRules = $scoringRules;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): static
    {
        $this->verified = $verified;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }
}
