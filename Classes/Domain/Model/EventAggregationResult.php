<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Domain\Model;

final readonly class EventAggregationResult
{
    public function __construct(
        private string $eventName,
        private int $conversions,
        private int $uniqueConversions,
        private int $value,
    ) {}

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getConversions(): int
    {
        return $this->conversions;
    }

    public function getUniqueConversions(): int
    {
        return $this->uniqueConversions;
    }

    /**
     * Monetary value in cents
     */
    public function getValue(): int
    {
        return $this->value;
    }

    public function getFormattedValue(): string
    {
        return number_format($this->value / 100, 2, '.', ',');
    }
}
