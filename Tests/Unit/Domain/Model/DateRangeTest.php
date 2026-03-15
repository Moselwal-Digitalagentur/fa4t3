<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Tests\Unit\Domain\Model;

use Moselwal\FathomAnalytics\Domain\Model\DateRange;
use PHPUnit\Framework\TestCase;

class DateRangeTest extends TestCase
{
    /**
     * @test
     */
    public function fromPresetTodayUsesHourGrouping(): void
    {
        $range = DateRange::fromPreset('today');

        self::assertSame('today', $range->getPreset());
        self::assertSame('hour', $range->getDateGrouping());
        self::assertSame(date('Y-m-d'), $range->getFrom()->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function fromPreset7dUsesDayGrouping(): void
    {
        $range = DateRange::fromPreset('7d');

        self::assertSame('7d', $range->getPreset());
        self::assertSame('day', $range->getDateGrouping());
    }

    /**
     * @test
     */
    public function fromPreset30dUsesDayGrouping(): void
    {
        $range = DateRange::fromPreset('30d');

        self::assertSame('30d', $range->getPreset());
        self::assertSame('day', $range->getDateGrouping());
    }

    /**
     * @test
     */
    public function fromPreset90dUsesMonthGrouping(): void
    {
        $range = DateRange::fromPreset('90d');

        self::assertSame('90d', $range->getPreset());
        self::assertSame('month', $range->getDateGrouping());
    }

    /**
     * @test
     */
    public function fromPresetYearUsesMonthGrouping(): void
    {
        $range = DateRange::fromPreset('year');

        self::assertSame('year', $range->getPreset());
        self::assertSame('month', $range->getDateGrouping());
    }

    /**
     * @test
     */
    public function fromPresetMonthReturnsLastCalendarMonth(): void
    {
        $range = DateRange::fromPreset('month');

        self::assertSame('month', $range->getPreset());
        self::assertSame('day', $range->getDateGrouping());
        self::assertSame('01', $range->getFrom()->format('d'));
    }

    /**
     * @test
     */
    public function fromCustomShortRangeUsesHourGrouping(): void
    {
        $from = new \DateTimeImmutable('2026-03-15 00:00:00');
        $to = new \DateTimeImmutable('2026-03-15 23:59:59');

        $range = DateRange::fromCustom($from, $to);

        self::assertSame('custom', $range->getPreset());
        self::assertSame('hour', $range->getDateGrouping());
    }

    /**
     * @test
     */
    public function fromCustomMediumRangeUsesDayGrouping(): void
    {
        $from = new \DateTimeImmutable('2026-02-01');
        $to = new \DateTimeImmutable('2026-03-15');

        $range = DateRange::fromCustom($from, $to);

        self::assertSame('day', $range->getDateGrouping());
    }

    /**
     * @test
     */
    public function fromCustomLongRangeUsesMonthGrouping(): void
    {
        $from = new \DateTimeImmutable('2025-01-01');
        $to = new \DateTimeImmutable('2026-03-15');

        $range = DateRange::fromCustom($from, $to);

        self::assertSame('month', $range->getDateGrouping());
    }

    /**
     * @test
     */
    public function invalidPresetDefaultsTo30d(): void
    {
        $range = DateRange::fromPreset('invalid');

        self::assertSame('30d', $range->getPreset());
        self::assertSame('day', $range->getDateGrouping());
    }
}
