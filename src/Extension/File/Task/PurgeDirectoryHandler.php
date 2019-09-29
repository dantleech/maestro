<?php

namespace Maestro\Extension\File\Task;

use Amp\Parallel\Worker;
use Amp\Promise;
use Maestro\Extension\File\Amp\Task\PurgeDirectoryAmpTask;

class PurgeDirectoryHandler
{
    public function __invoke(PurgeDirectoryTask $task): Promise
    {
        return Worker\enqueue(new PurgeDirectoryAmpTask($task->path()));
    }
}
