<?php

namespace Maestro\Node;

interface Scheduler
{
    public function shouldRun(Schedule $schedule, Node $node): bool;

    public function shouldReschedule(Schedule $schedule, Node $node): bool;
}
