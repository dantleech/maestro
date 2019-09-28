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

    public static function PROCESSING(): self
    {
        return new self('processing');
    }

    public static function DONE(): self
    {
        return new self('done');
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
