<?php

declare(strict_types=1);

namespace Moselwal\FA4T3\Domain\Model;

final readonly class AggregationResult
{
    /**
     * @param array<int, array<string, mixed>>|null $groupedData
     */
    public function __construct(
        private int $visits,
        private int $uniques,
        private int $pageviews,
        private float $avgDuration,
        private float $bounceRate,
        private \DateTimeImmutable $dateFrom,
        private \DateTimeImmutable $dateTo,
        private ?array $groupedData = null,
        private bool $hasError = false,
        private ?string $errorMessage = null,
    ) {}

    public static function createError(string $message): self
    {
        $now = new \DateTimeImmutable();

        return new self(
            visits: 0,
            uniques: 0,
            pageviews: 0,
            avgDuration: 0.0,
            bounceRate: 0.0,
            dateFrom: $now,
            dateTo: $now,
            groupedData: null,
            hasError: true,
            errorMessage: $message,
        );
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
     * @return array<int, array<string, mixed>>|null
     */
    public function getGroupedData(): ?array
    {
        return $this->groupedData;
    }

    public function hasError(): bool
    {
        return $this->hasError;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }
}
