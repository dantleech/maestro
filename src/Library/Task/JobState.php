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

    public static function WAITING()
    {
        return new self('waiting');
    }

    public static function PROCESSING()
    {
        return new self('processing');
    }

    public static function DONE()
    {
        return new self('done');
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
