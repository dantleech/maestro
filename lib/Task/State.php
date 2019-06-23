<?php

namespace Maestro\Task;

final class State
{
    private const WAITING = 'waiting';
    private const DONE = 'done';
    private const BUSY = 'busy';
    private const FAILED = 'failed';
    private const CANCELLED = 'cancelled';

    private $state;

    private function __construct(string $state)
    {
        $this->state = $state;
    }

    public static function BUSY(): self
    {
        return new self(self::BUSY);
    }

    public static function DONE(): self
    {
        return new self(self::DONE);
    }

    public static function WAITING(): self
    {
        return new self(self::WAITING);
    }

    public static function FAILED(): self
    {
        return new self(self::FAILED);
    }

    public static function CANCELLED(): self
    {
        return new self(self::CANCELLED);
    }

    public function isIdle(): bool
    {
        return $this->state === self::DONE;
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

    public function isCancelled()
    {
        return $this->state === self::CANCELLED;
    }

    public function toString(): string
    {
        return $this->state;
    }

    public function is(State $state): bool
    {
        return $state->state === $this->state;
    }

    public function in(State ...$states): bool
    {
        foreach ($states as $state) {
            if ($this->state === $state->state) {
                return true;
            }
        }

        return false;
    }
}
