<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Domain\Model;

class EventAggregationResult
{
    /** @var string */
    private $eventName;

    /** @var int */
    private $conversions;

    /** @var int */
    private $uniqueConversions;

    /** @var int */
    private $value;

    public function __construct(string $eventName, int $conversions, int $uniqueConversions, int $value)
    {
        $this->eventName = $eventName;
        $this->conversions = $conversions;
        $this->uniqueConversions = $uniqueConversions;
        $this->value = $value;
    }

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
