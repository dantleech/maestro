<?php

namespace Maestro\Task;

interface Scheduler
{
    public function schedule(Node $node, Queue $queue): Queue;
}
