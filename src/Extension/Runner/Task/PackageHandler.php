<?php

namespace Maestro\Extension\Runner\Task;

use Amp\Promise;
use Amp\Success;
use Generator;
use Maestro\Extension\File\Task\PurgeDirectoryTask;
use Maestro\Extension\Vcs\Task\CheckoutTask;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Support\NodeMeta;
use Maestro\Library\Support\Package\Package;
use Maestro\Library\Artifact\Artifacts;
use Maestro\Library\Task\TaskRunner;
use Maestro\Library\Workspace\WorkspaceManager;
use function Amp\File\mkdir;

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
