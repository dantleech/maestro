<?php

namespace Maestro\Graph\Scheduler;

use Maestro\Graph\Schedule;

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
