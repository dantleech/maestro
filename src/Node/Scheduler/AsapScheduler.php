<?php

namespace Maestro\Node\Scheduler;

use Maestro\Node\Node;
use Maestro\Node\Schedule;
use Maestro\Node\Scheduler;

class AsapScheduler implements Scheduler
{
    public function shouldRun(Schedule $schedule, Node $node): bool
    {
        return true;
    }

    public function shouldReschedule(Schedule $schedule, Node $node): bool
    {
        return false;
    }
}
