<?php

namespace Maestro\Model\Job;

class QueueState
{
    private const RUNNING = 'running';
    private const DONE = 'done';
    private const FAILED = 'failed';
    private const PENDING = 'pending';

    /**
     * @var string
     */
    private $state;

    private function __construct(string $state)
    {
        $this->state = $state;
    }

    public static function PENDING(): self
    {
        return new self(self::PENDING);
    }

    public static function RUNNING(): self
    {
        return new self(self::RUNNING);
    }

    public static function DONE(): self
    {
        return new self(self::DONE);
    }

    public static function FAILED(): self
    {
        return new self(self::FAILED);
    }

    public function __toString(): string
    {
        return $this->state;
    }

    public function isFailed(): bool
    {
        return $this->state === self::FAILED;
    }

    public function isStarted(): bool
    {
        return $this->state === self::RUNNING;
    }

    public function isPending(): bool
    {
        return $this->state === self::PENDING;
    }

    public function isDone(): bool
    {
        return $this->state === self::DONE;
    }
}
