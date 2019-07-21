<?php

namespace Maestro\Node\Scheduler;

use Maestro\Node\Schedule;

class RepeatSchedule implements Schedule
{
    /**
     * @var int
     */
    private $delay;

    public function __construct(int $delay)
    {
        $this->delay = $delay;
    }

    public function delay(): int
    {
        return $this->delay;
    }
}
