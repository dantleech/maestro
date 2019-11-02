<?php

namespace Maestro\Extension\Runner\Task;

use Amp\Success;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Support\ManifestPath;

class InitHandler
{
    public function __invoke(InitTask $task)
    {
        $artifacts = [
            new Environment($task->env()),
        ];

        $path = $task->path();

        if ($path) {
            $artifacts[] = new ManifestPath($path);
        }

        return new Success($artifacts);
    }
}
