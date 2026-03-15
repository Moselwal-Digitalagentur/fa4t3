<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Domain\Model;

class FathomEvent
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $siteId;

    /** @var \DateTimeImmutable */
    private $createdAt;

    public function __construct(string $id, string $name, string $siteId, \DateTimeImmutable $createdAt)
    {
        $this->id = $id;
        $this->name = $name;
        $this->siteId = $siteId;
        $this->createdAt = $createdAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSiteId(): string
    {
        return $this->siteId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
