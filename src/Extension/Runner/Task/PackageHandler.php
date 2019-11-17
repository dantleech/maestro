<?php

namespace Maestro\Extension\Runner\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Support\Package\Package;

class PackageHandler
{
    public function __invoke(
        PackageTask $task
    ): Promise {
        return \Amp\call(function () use ($task) {
            $name = $task->name();
            return new Success([
                new Package(
                    $name,
                    $task->version()
                )
            ]);
        });
    }
}
