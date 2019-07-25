<?php

namespace Maestro\Graph\Scheduler;

use Maestro\Graph\Node;
use Maestro\Graph\Schedule;

class AsapSchedule implements Schedule
{
    public function shouldRun(Node $node): bool
    {
        return true;
    }

    public function shouldReschedule(Node $node): bool
    {
        return false;
    }
}
