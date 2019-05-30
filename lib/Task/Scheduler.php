<?php

namespace Maestro\Task;

interface Scheduler
{
    public function schedule(Graph $graph, Queue $queue): Queue;
}
