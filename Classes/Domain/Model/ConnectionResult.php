<?php

declare(strict_types=1);

namespace Moselwal\FathomAnalytics\Domain\Model;

class ConnectionResult
{
    /** @var bool */
    private $success;

    /** @var string */
    private $message;

    public function __construct(bool $success, string $message)
    {
        $this->success = $success;
        $this->message = $message;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
