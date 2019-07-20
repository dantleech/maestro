<?php

namespace Maestro\Node;

interface Schedule
{
    public function shouldRun(Node $node): bool;

    public function shouldReschedule(Node $node): bool;
}
