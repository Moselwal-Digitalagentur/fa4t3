<?php

declare(strict_types=1);

namespace Moselwal\FA4T3\Domain\Model;

final readonly class CurrentVisitors
{
    /**
     * @param array<int, array<string, mixed>>|null $topPages
     * @param array<int, array<string, mixed>>|null $topReferrers
     */
    public function __construct(
        private int $total,
        private ?array $topPages = null,
        private ?array $topReferrers = null,
    ) {}

    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    public function getTopPages(): ?array
    {
        return $this->topPages;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    public function getTopReferrers(): ?array
    {
        return $this->topReferrers;
    }
}
