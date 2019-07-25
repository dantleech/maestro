<?php

namespace Maestro\Graph\Scheduler;

use Maestro\Graph\Node;
use Maestro\Graph\Schedule;
use Maestro\Graph\Scheduler;

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
