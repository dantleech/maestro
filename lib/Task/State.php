<?php

namespace Maestro\Task;

final class State
{
    private const WAITING = 'waiting';
    private const IDLE = 'idle';
    private const BUSY = 'busy';
    private const FAILED = 'failed';

    private $state;

    private function __construct(string $state)
    {
        $this->state = $state;
    }

    public static function BUSY(): self
    {
        return new self(self::BUSY);
    }

    public static function IDLE(): self
    {
        return new self(self::IDLE);
    }

    public static function WAITING(): self
    {
        return new self(self::WAITING);
    }

    public function FAILED()
    {
        return new self(self::FAILED);
    }

    public function isIdle(): bool
    {
        return $this->state === self::IDLE;
    }

    public function isWaiting(): bool
    {
        return $this->state === self::WAITING;
    }

    public function isFailed(): bool
    {
        return $this->state === self::FAILED;
    }

    public function isBusy()
    {
        return $this->state === self::BUSY;
    }

    public function toString(): string
    {
        return $this->state;
    }
}
