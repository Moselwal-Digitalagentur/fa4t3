<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Domain\Model;

class DateRange
{
    /** @var string */
    private $preset;

    /** @var \DateTimeImmutable */
    private $from;

    /** @var \DateTimeImmutable */
    private $to;

    /** @var string */
    private $dateGrouping;

    private function __construct(string $preset, \DateTimeImmutable $from, \DateTimeImmutable $to, string $dateGrouping)
    {
        $this->preset = $preset;
        $this->from = $from;
        $this->to = $to;
        $this->dateGrouping = $dateGrouping;
    }

    /**
     * @param string $preset One of: today, 7d, 30d, month, 90d, year, custom
     * @return self
     */
    public static function fromPreset(string $preset): self
    {
        $now = new \DateTimeImmutable('now');
        $today = $now->setTime(0, 0, 0);

        switch ($preset) {
            case 'today':
                return new self($preset, $today, $now, 'hour');
            case '7d':
                return new self($preset, $today->modify('-7 days'), $now, 'day');
            case '30d':
                return new self($preset, $today->modify('-30 days'), $now, 'day');
            case 'month':
                $firstOfMonth = $today->modify('first day of last month');
                $lastOfMonth = $today->modify('last day of last month')->setTime(23, 59, 59);
                return new self($preset, $firstOfMonth, $lastOfMonth, 'day');
            case '90d':
                return new self($preset, $today->modify('-90 days'), $now, 'month');
            case 'year':
                return new self($preset, $today->modify('-1 year'), $now, 'month');
            default:
                return new self('30d', $today->modify('-30 days'), $now, 'day');
        }
    }

    public static function fromCustom(\DateTimeImmutable $from, \DateTimeImmutable $to): self
    {
        $diffDays = (int)$from->diff($to)->days;

        if ($diffDays <= 1) {
            $grouping = 'hour';
        } elseif ($diffDays <= 90) {
            $grouping = 'day';
        } else {
            $grouping = 'month';
        }

        return new self('custom', $from, $to, $grouping);
    }

    public function getPreset(): string
    {
        return $this->preset;
    }

    public function getFrom(): \DateTimeImmutable
    {
        return $this->from;
    }

    public function getTo(): \DateTimeImmutable
    {
        return $this->to;
    }

    public function getDateGrouping(): string
    {
        return $this->dateGrouping;
    }
}
