<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Domain\Model;

final readonly class AggregationRequest
{
    public function __construct(
        private \DateTimeImmutable $dateFrom,
        private \DateTimeImmutable $dateTo,
        private string $timezone = 'UTC',
        private ?string $dateGrouping = null,
        private ?string $fieldGrouping = null,
        private ?array $filters = null,
        private ?string $sortBy = null,
        private ?int $limit = null,
    ) {}

    public static function fromDateRange(DateRange $range, string $timezone = 'UTC'): self
    {
        return new self(
            dateFrom: $range->getFrom(),
            dateTo: $range->getTo(),
            timezone: $timezone,
            dateGrouping: $range->getDateGrouping(),
        );
    }

    public function getDateFrom(): \DateTimeImmutable
    {
        return $this->dateFrom;
    }

    public function getDateTo(): \DateTimeImmutable
    {
        return $this->dateTo;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function getDateGrouping(): ?string
    {
        return $this->dateGrouping;
    }

    public function getFieldGrouping(): ?string
    {
        return $this->fieldGrouping;
    }

    public function getFilters(): ?array
    {
        return $this->filters;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function withoutDateGrouping(): self
    {
        return new self(
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
            timezone: $this->timezone,
            dateGrouping: null,
            fieldGrouping: $this->fieldGrouping,
            filters: $this->filters,
            sortBy: $this->sortBy,
            limit: $this->limit,
        );
    }

    public function withFieldGrouping(string $fieldGrouping): self
    {
        return new self(
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
            timezone: $this->timezone,
            dateGrouping: null,
            fieldGrouping: $fieldGrouping,
            filters: $this->filters,
            sortBy: $this->sortBy,
            limit: $this->limit,
        );
    }

    public function withFilters(array $filters): self
    {
        return new self(
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
            timezone: $this->timezone,
            dateGrouping: $this->dateGrouping,
            fieldGrouping: $this->fieldGrouping,
            filters: $filters,
            sortBy: $this->sortBy,
            limit: $this->limit,
        );
    }

    public function withSortBy(string $sortBy): self
    {
        return new self(
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
            timezone: $this->timezone,
            dateGrouping: $this->dateGrouping,
            fieldGrouping: $this->fieldGrouping,
            filters: $this->filters,
            sortBy: $sortBy,
            limit: $this->limit,
        );
    }

    public function withLimit(int $limit): self
    {
        return new self(
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
            timezone: $this->timezone,
            dateGrouping: $this->dateGrouping,
            fieldGrouping: $this->fieldGrouping,
            filters: $this->filters,
            sortBy: $this->sortBy,
            limit: $limit,
        );
    }
}
