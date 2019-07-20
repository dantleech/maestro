<?php

namespace Maestro\Node\Schedule;

use Maestro\Node\Node;
use Maestro\Node\Schedule;

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
