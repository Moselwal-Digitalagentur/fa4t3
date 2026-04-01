<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Domain\Model;

final readonly class CurrentVisitors
{
    public function __construct(
        private int $total,
        private ?array $topPages = null,
        private ?array $topReferrers = null,
    ) {}

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getTopPages(): ?array
    {
        return $this->topPages;
    }

    public function getTopReferrers(): ?array
    {
        return $this->topReferrers;
    }
}
