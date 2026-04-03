<?php

declare(strict_types=1);

namespace Moselwal\FA4T3\Domain\Model;

final readonly class Fa4t3Event
{
    public function __construct(
        private string $id,
        private string $name,
        private string $siteId,
        private \DateTimeImmutable $createdAt,
    ) {}

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
