<?php

namespace Maestro\Library\Task;

interface Queue
{
    public function dequeue(): ?Job;

    public function enqueue(Job $job): void;
}
