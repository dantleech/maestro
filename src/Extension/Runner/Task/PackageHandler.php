<?php

namespace Maestro\Extension\Runner\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Support\Package\Package;

class PackageHandler
{
    public function __invoke(
        PackageTask $task,
        Environment $environment
    ): Promise {
        return \Amp\call(function () use ($task, $environment) {
            $name = $task->name();
            $environment = $environment->spawnMerged([
                'PACKAGE_NAME' => $name
            ]);
            return new Success([
                $environment,
                new Package(
                    $name,
                    $task->version()
                )
            ]);
        });
    }
}
