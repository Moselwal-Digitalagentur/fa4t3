<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Domain\Model;

class DashboardData
{
    /** @var AggregationResult */
    private $aggregation;

    /** @var array */
    private $topPages;

    /** @var array */
    private $topReferrers;

    /** @var CurrentVisitors */
    private $currentVisitors;

    /** @var array */
    private $events;

    /** @var bool */
    private $hasError;

    /** @var string|null */
    private $errorMessage;

    /**
     * @param AggregationResult $aggregation
     * @param array $topPages
     * @param array $topReferrers
     * @param CurrentVisitors $currentVisitors
     * @param EventAggregationResult[] $events
     * @param bool $hasError
     * @param string|null $errorMessage
     */
    public function __construct(
        AggregationResult $aggregation,
        array $topPages,
        array $topReferrers,
        CurrentVisitors $currentVisitors,
        array $events = [],
        bool $hasError = false,
        string $errorMessage = null
    ) {
        $this->aggregation = $aggregation;
        $this->topPages = $topPages;
        $this->topReferrers = $topReferrers;
        $this->currentVisitors = $currentVisitors;
        $this->events = $events;
        $this->hasError = $hasError;
        $this->errorMessage = $errorMessage;
    }

    public static function createError(string $message): self
    {
        return new self(
            AggregationResult::createError($message),
            [],
            [],
            new CurrentVisitors(0),
            [],
            true,
            $message
        );
    }

    public function getAggregation(): AggregationResult
    {
        return $this->aggregation;
    }

    public function getTopPages(): array
    {
        return $this->topPages;
    }

    public function getTopReferrers(): array
    {
        return $this->topReferrers;
    }

    public function getCurrentVisitors(): CurrentVisitors
    {
        return $this->currentVisitors;
    }

    /**
     * @return EventAggregationResult[]
     */
    public function getEvents(): array
    {
        return $this->events;
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
