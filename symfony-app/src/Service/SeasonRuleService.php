<?php

namespace App\Service;

use App\Entity\Season;
use App\Model\FinanceRules;
use App\Model\ScoringRules;
use App\Repository\SeasonRepository;
use Psr\Log\LoggerInterface;

/**
 * Access point for per-season league rules. Loads the season row once
 * per request; a missing row (e.g. a future season nobody has created
 * yet) synthesizes an in-memory Season with current defaults so pages
 * keep working, without inserting anything.
 */
class SeasonRuleService
{
    /** @var array<int, Season> */
    private array $cache = [];

    public function __construct(
        private SeasonRepository $seasons,
        private LoggerInterface $logger
    ) {
    }

    public function getSeason(int $season): Season
    {
        if (!isset($this->cache[$season])) {
            $row = $this->seasons->find($season);
            if ($row === null) {
                $this->logger->warning('No seasons row for {season}; using default rules', ['season' => $season]);
                $row = (new Season())
                    ->setSeason($season)
                    ->setScoringRules(ScoringRuleRegistry::defaults());
            }
            $this->cache[$season] = $row;
        }

        return $this->cache[$season];
    }

    public function getScoringRules(int $season): ScoringRules
    {
        $row = $this->getSeason($season);

        return ScoringRules::fromArray($row->getScoringRules(), $row->getScoringStrategy());
    }

    public function getFinanceRules(int $season): FinanceRules
    {
        $row = $this->getSeason($season);

        return new FinanceRules(
            entryFee: $row->getEntryFee(),
            illegalActivationFine: $row->getIllegalActivationFine(),
            byeWeekActivationFine: $row->getByeWeekActivationFine(),
            extraTransactionFine: $row->getExtraTransactionFine(),
            numOfGames: $row->getNumOfGames(),
            winPercent: $row->getWinPercent(),
            postPercent: $row->getPostPercent(),
            divPercent: $row->getDivPercent(),
            playoffPercent: $row->getPlayoffPercent(),
            finalPercent: $row->getFinalPercent(),
            champPercent: $row->getChampPercent(),
        );
    }

    public function getRegularSeasonWeeks(int $season): int
    {
        return $this->getSeason($season)->getRegularSeasonWeeks();
    }

    public function getMaxActivePlayers(int $season): int
    {
        return $this->getSeason($season)->getMaxActivePlayers();
    }
}
