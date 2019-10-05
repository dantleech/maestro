<?php

namespace Maestro\Library\Task;

class JobState
{
    /**
     * @var string
     */
    private $state;

    private function __construct(string $state)
    {
        $this->state = $state;
    }

    public static function WAITING(): self
    {
        return new self('waiting');
    }

    public static function BUSY(): self
    {
        return new self('busy');
    }

    public static function SUCCEEDED(): self
    {
        return new self('succeeded');
    }

    public static function FAILED(): self
    {
        return new self('failed');
    }

    public function is(JobState $state): bool
    {
        return $this->state === $state->state;
    }

    public function isNot(JobState $state)
    {
        return $this->state !== $state->state;
    }
}
