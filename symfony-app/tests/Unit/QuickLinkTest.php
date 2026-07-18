<?php

namespace App\Tests\Unit;

use App\Entity\QuickLink;
use PHPUnit\Framework\TestCase;

class QuickLinkTest extends TestCase
{
    private function makeLink(?string $start, ?string $end, bool $active = true): QuickLink
    {
        return (new QuickLink())
            ->setLabel('Test')
            ->setUrl('/test')
            ->setStartDate($start !== null ? new \DateTime($start) : null)
            ->setEndDate($end !== null ? new \DateTime($end) : null)
            ->setActive($active);
    }

    private const TODAY = '2026-07-17';

    public function testOpenEndedLinkIsVisible(): void
    {
        $this->assertTrue($this->makeLink(null, null)->isVisibleOn(new \DateTimeImmutable(self::TODAY)));
    }

    public function testBoundsAreInclusive(): void
    {
        $link = $this->makeLink(self::TODAY, self::TODAY);
        $this->assertTrue($link->isVisibleOn(new \DateTimeImmutable(self::TODAY)));
    }

    public function testFutureStartHidesLink(): void
    {
        $link = $this->makeLink('2026-07-18', null);
        $this->assertFalse($link->isVisibleOn(new \DateTimeImmutable(self::TODAY)));
    }

    public function testPastEndHidesLink(): void
    {
        $link = $this->makeLink(null, '2026-07-16');
        $this->assertFalse($link->isVisibleOn(new \DateTimeImmutable(self::TODAY)));
    }

    public function testNullStartWithFutureEndIsVisible(): void
    {
        $link = $this->makeLink(null, '2026-08-01');
        $this->assertTrue($link->isVisibleOn(new \DateTimeImmutable(self::TODAY)));
    }

    public function testPastStartWithNullEndIsVisible(): void
    {
        $link = $this->makeLink('2026-01-01', null);
        $this->assertTrue($link->isVisibleOn(new \DateTimeImmutable(self::TODAY)));
    }

    public function testInactiveLinkIsHiddenRegardlessOfWindow(): void
    {
        $link = $this->makeLink(null, null, active: false);
        $this->assertFalse($link->isVisibleOn(new \DateTimeImmutable(self::TODAY)));
    }

    public function testWindowComparesDatesNotTimes(): void
    {
        // A late-in-the-day "now" on the end date must still count as inside
        $link = $this->makeLink(null, self::TODAY);
        $this->assertTrue($link->isVisibleOn(new \DateTimeImmutable(self::TODAY . ' 23:59:59')));
    }
}
