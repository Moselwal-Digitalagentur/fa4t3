<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Domain\Model;

class AggregationResult
{
    /** @var int */
    private $visits;

    /** @var int */
    private $uniques;

    /** @var int */
    private $pageviews;

    /** @var float */
    private $avgDuration;

    /** @var float */
    private $bounceRate;

    /** @var \DateTimeImmutable */
    private $dateFrom;

    /** @var \DateTimeImmutable */
    private $dateTo;

    /** @var array|null */
    private $groupedData;

    /** @var bool */
    private $hasError;

    /** @var string|null */
    private $errorMessage;

    public function __construct(
        int $visits,
        int $uniques,
        int $pageviews,
        float $avgDuration,
        float $bounceRate,
        \DateTimeImmutable $dateFrom,
        \DateTimeImmutable $dateTo,
        array $groupedData = null,
        bool $hasError = false,
        string $errorMessage = null
    ) {
        $this->visits = $visits;
        $this->uniques = $uniques;
        $this->pageviews = $pageviews;
        $this->avgDuration = $avgDuration;
        $this->bounceRate = $bounceRate;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->groupedData = $groupedData;
        $this->hasError = $hasError;
        $this->errorMessage = $errorMessage;
    }

    public static function createError(string $message): self
    {
        $now = new \DateTimeImmutable();
        return new self(0, 0, 0, 0.0, 0.0, $now, $now, null, true, $message);
    }

    public function getVisits(): int
    {
        return $this->visits;
    }

    public function getUniques(): int
    {
        return $this->uniques;
    }

    public function getPageviews(): int
    {
        return $this->pageviews;
    }

    public function getAvgDuration(): float
    {
        return $this->avgDuration;
    }

    public function getBounceRate(): float
    {
        return $this->bounceRate;
    }

    public function getDateFrom(): \DateTimeImmutable
    {
        return $this->dateFrom;
    }

    public function getDateTo(): \DateTimeImmutable
    {
        return $this->dateTo;
    }

    /**
     * @return array|null
     */
    public function getGroupedData()
    {
        return $this->groupedData;
    }

    public function hasError(): bool
    {
        return $this->hasError;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}
