<?php

declare(strict_types=1);

namespace Moselwal\FA4T3\Domain\Model;

final readonly class DashboardData
{
    /**
     * @param array<int, array<string, mixed>> $topPages
     * @param array<int, array<string, mixed>> $topReferrers
     * @param EventAggregationResult[] $events
     */
    public function __construct(
        private AggregationResult $aggregation,
        private array $topPages,
        private array $topReferrers,
        private CurrentVisitors $currentVisitors,
        private array $events = [],
        private bool $hasError = false,
        private ?string $errorMessage = null,
    ) {}

    public static function createError(string $message): self
    {
        return new self(
            aggregation: AggregationResult::createError($message),
            topPages: [],
            topReferrers: [],
            currentVisitors: new CurrentVisitors(0),
            events: [],
            hasError: true,
            errorMessage: $message,
        );
    }

    public function getAggregation(): AggregationResult
    {
        return $this->aggregation;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTopPages(): array
    {
        return $this->topPages;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
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

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }
}
