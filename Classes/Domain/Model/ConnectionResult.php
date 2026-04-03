<?php

declare(strict_types=1);

namespace Moselwal\FA4T3\Domain\Model;

final readonly class ConnectionResult
{
    public function __construct(
        private bool $success,
        private string $message,
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
