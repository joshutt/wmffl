<?php

namespace App\Tests\Service;

use App\Entity\Season;
use App\Repository\SeasonRepository;
use App\Service\ScoringRuleRegistry;
use App\Service\SeasonRuleService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[AllowMockObjectsWithoutExpectations]
class SeasonRuleServiceTest extends TestCase
{
    private function serviceReturning(?Season $row): SeasonRuleService
    {
        $repo = $this->createStub(SeasonRepository::class);
        $repo->method('find')->willReturn($row);

        return new SeasonRuleService($repo, new NullLogger());
    }

    public function testStoredOverridesMergeOverRegistryDefaults(): void
    {
        $row = (new Season())->setSeason(2023)->setScoringRules(['k_fg60' => 10]);
        $rules = $this->serviceReturning($row)->getScoringRules(2023);

        $this->assertSame(10, $rules->int('k_fg60'));
        // Everything not stored falls back to the registry default
        $this->assertSame(5, $rules->int('k_fg50'));
        $this->assertSame(9, $rules->int('def_td'));
    }

    public function testUnknownStoredKeysAreIgnored(): void
    {
        $row = (new Season())->setSeason(2020)->setScoringRules(['retired_key' => 99]);
        $rules = $this->serviceReturning($row)->getScoringRules(2020);

        $this->assertSame(array_keys(ScoringRuleRegistry::defaults()), array_keys($rules->toArray()));
    }

    public function testNullValueMeansCategoryNotAwarded(): void
    {
        $row = (new Season())->setSeason(1995)->setScoringRules(['def_pass_defend' => null]);
        $rules = $this->serviceReturning($row)->getScoringRules(1995);

        $this->assertFalse($rules->awards('def_pass_defend'));
        $this->assertSame(0, $rules->int('def_pass_defend'));
        $this->assertTrue($rules->awards('def_tackle'));
    }

    public function testMissingRowSynthesizesDefaultsWithoutFailing(): void
    {
        $service = $this->serviceReturning(null);

        $season = $service->getSeason(2030);
        $this->assertSame(2030, $season->getSeason());
        $this->assertSame(14, $season->getRegularSeasonWeeks());
        $this->assertSame(7, $service->getScoringRules(2030)->int('k_fg60'));

        $finance = $service->getFinanceRules(2030);
        $this->assertSame(75.0, $finance->entryFee);
        $this->assertSame(84, $finance->numOfGames);
    }

    public function testFinanceRulesComeFromTheSeasonRow(): void
    {
        $row = (new Season())->setSeason(1998)
            ->setEntryFee(50.0)
            ->setWinPercent(0.30)
            ->setNumOfGames(70);
        $finance = $this->serviceReturning($row)->getFinanceRules(1998);

        $this->assertSame(50.0, $finance->entryFee);
        $this->assertSame(0.30, $finance->winPercent);
        $this->assertSame(70, $finance->numOfGames);
    }

    public function testSeasonRowIsCachedPerRequest(): void
    {
        $repo = $this->createMock(SeasonRepository::class);
        $repo->expects($this->once())->method('find')
            ->willReturn((new Season())->setSeason(2024));
        $service = new SeasonRuleService($repo, new NullLogger());

        $service->getScoringRules(2024);
        $service->getFinanceRules(2024);
        $service->getRegularSeasonWeeks(2024);
    }
}
