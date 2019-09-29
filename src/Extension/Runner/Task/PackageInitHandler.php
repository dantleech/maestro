<?php

namespace Maestro\Extension\Runner\Task;

use Amp\Promise;
use Amp\Success;
use Generator;
use Maestro\Extension\File\Task\PurgeDirectoryTask;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Support\Package\Package;
use Maestro\Library\Task\Artifacts;
use Maestro\Library\Task\Job;
use Maestro\Library\Task\Queue;
use Maestro\Library\Task\TaskRunner;
use Maestro\Library\Workspace\Workspace;
use Maestro\Library\Workspace\WorkspaceManager;
use function Amp\File\{
    rmdir,
    mkdir,
    exists
};

class PackageInitHandler
{
    /**
     * @var WorkspaceManager
     */
    private $workspaceManager;

    public function __construct(WorkspaceManager $workspaceManager)
    {
        $this->workspaceManager = $workspaceManager;
    }

    public function __invoke(PackageInitTask $task, Environment $enivonment, TaskRunner $taskRunner): Promise
    {
        return \Amp\call(function () use ($task, $enivonment, $taskRunner) {
            return new Success([
                $enivonment->spawnMerged([
                    'PACKAGE_NAME' => $task->name()
                ]),
                new Package(
                    $task->name(),
                    $task->version()
                ),
                yield from $this->createWorkspace($taskRunner, $task)
            ]);
        });
    }

    private function createWorkspace(TaskRunner $taskRunner, PackageInitTask $task): Generator
    {
        $workspace = $this->workspaceManager->createNamedWorkspace($task->name());

        if ($task->purgeWorkspace()) {
            yield $taskRunner->run(new PurgeDirectoryTask($workspace->absolutePath()), new Artifacts());
        }

        if (!file_exists($workspace->absolutePath())) {
            // if we don't yield, then the directory doesn't get created
            yield mkdir($workspace->absolutePath(), 0777, true);
        }

        return $workspace;
    }
}
