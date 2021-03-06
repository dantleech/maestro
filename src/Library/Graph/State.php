<?php

namespace Maestro\Library\Graph;

final class State
{
    private const IDLE = 'idle';
    private const DONE = 'done';
    private const DISPATCHED = 'dispatched';
    private const FAILED = 'failed';
    private const CANCELLED = 'cancelled';

    private $state;

    private function __construct(string $state)
    {
        $this->state = $state;
    }

    public static function DISPATCHED(): self
    {
        return new self(self::DISPATCHED);
    }

    public static function SUCCEEDED(): self
    {
        return new self(self::DONE);
    }

    public static function IDLE(): self
    {
        return new self(self::IDLE);
    }

    public static function CANCELLED(): self
    {
        return new self(self::CANCELLED);
    }

    public static function FAILED(): self
    {
        return new self(self::FAILED);
    }

    public function isDone(): bool
    {
        return $this->state === self::DONE;
    }

    public function isIdle(): bool
    {
        return $this->state === self::IDLE;
    }

    public function isDispatched(): bool
    {
        return $this->state === self::DISPATCHED;
    }

    public function isFailed(): bool
    {
        return $this->state === self::FAILED;
    }

    public function isCancelled(): bool
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
