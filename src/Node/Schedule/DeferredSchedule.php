<?php

namespace Maestro\Node\Schedule;

use Maestro\Node\Node;
use Maestro\Node\Schedule;

class DeferredSchedule implements Schedule
{
    /**
     * @var int
     */
    private $time;

    /**
     * @var int
     */
    private $startTime;


    public function __construct(int $time)
    {
        $this->time = $time;
        $this->startTime = time();
    }

    public function shouldRun(Node $node): bool
    {
        if (time() - $this->startTime > $this->time) {
            return true;
        }
        return false;
    }

    public function shouldReschedule(Node $node): bool
    {
        return false;
    }
}
