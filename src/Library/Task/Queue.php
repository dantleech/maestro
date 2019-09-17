<?php

namespace Maestro\Library\Task;

use Countable;

interface Queue extends Countable
{
    public function dequeue(): ?Job;

    public function enqueue(Job $job): void;
}
