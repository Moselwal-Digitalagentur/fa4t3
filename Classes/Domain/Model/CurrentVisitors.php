<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Domain\Model;

class CurrentVisitors
{
    /** @var int */
    private $total;

    /** @var array|null */
    private $topPages;

    /** @var array|null */
    private $topReferrers;

    /**
     * @param int $total
     * @param array|null $topPages
     * @param array|null $topReferrers
     */
    public function __construct(int $total, array $topPages = null, array $topReferrers = null)
    {
        $this->total = $total;
        $this->topPages = $topPages;
        $this->topReferrers = $topReferrers;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return array|null
     */
    public function getTopPages()
    {
        return $this->topPages;
    }

    /**
     * @return array|null
     */
    public function getTopReferrers()
    {
        return $this->topReferrers;
    }
}
