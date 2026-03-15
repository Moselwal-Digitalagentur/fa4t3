<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Domain\Model;

class AggregationRequest
{
    /** @var \DateTimeImmutable */
    private $dateFrom;

    /** @var \DateTimeImmutable */
    private $dateTo;

    /** @var string */
    private $timezone;

    /** @var string|null */
    private $dateGrouping;

    /** @var string|null */
    private $fieldGrouping;

    /** @var array|null */
    private $filters;

    /** @var string|null */
    private $sortBy;

    /** @var int|null */
    private $limit;

    public function __construct(
        \DateTimeImmutable $dateFrom,
        \DateTimeImmutable $dateTo,
        string $timezone = 'UTC',
        string $dateGrouping = null,
        string $fieldGrouping = null,
        array $filters = null,
        string $sortBy = null,
        int $limit = null
    ) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->timezone = $timezone;
        $this->dateGrouping = $dateGrouping;
        $this->fieldGrouping = $fieldGrouping;
        $this->filters = $filters;
        $this->sortBy = $sortBy;
        $this->limit = $limit;
    }

    public static function fromDateRange(DateRange $range, string $timezone = 'UTC'): self
    {
        return new self(
            $range->getFrom(),
            $range->getTo(),
            $timezone,
            $range->getDateGrouping()
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

    /**
     * @return string|null
     */
    public function getDateGrouping()
    {
        return $this->dateGrouping;
    }

    /**
     * @return string|null
     */
    public function getFieldGrouping()
    {
        return $this->fieldGrouping;
    }

    /**
     * @return array|null
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return string|null
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @return int|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    public function withFieldGrouping(string $fieldGrouping): self
    {
        $clone = clone $this;
        $clone->fieldGrouping = $fieldGrouping;
        return $clone;
    }

    public function withFilters(array $filters): self
    {
        $clone = clone $this;
        $clone->filters = $filters;
        return $clone;
    }

    public function withSortBy(string $sortBy): self
    {
        $clone = clone $this;
        $clone->sortBy = $sortBy;
        return $clone;
    }

    public function withLimit(int $limit): self
    {
        $clone = clone $this;
        $clone->limit = $limit;
        return $clone;
    }
}
