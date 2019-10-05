<?php

namespace Maestro\Extension\Runner\Task;

use Amp\Success;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Support\ManifestPath;
use Maestro\Library\Support\Variables\Variables;

class InitHandler
{
    public function __invoke(InitTask $task)
    {
        $artifacts = [
            new Environment($task->manifest()->env()),
            new Variables($task->manifest()->vars()),
        ];

        $path = $task->manifest()->path();

        if ($path) {
            $artifacts[] = new ManifestPath($path);
        }

        return new Success($artifacts);
    }
}
