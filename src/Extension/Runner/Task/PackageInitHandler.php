<?php

namespace Maestro\Extension\Runner\Task;

use Amp\Promise;
use Amp\Success;
use Generator;
use Maestro\Extension\File\Task\PurgeDirectoryTask;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Support\Package\Package;
use Maestro\Library\Artifact\Artifacts;
use Maestro\Library\Task\TaskRunner;
use Maestro\Library\Workspace\WorkspaceManager;
use function Amp\File\mkdir;

class PackageInitHandler
{
    /**
     * @var WorkspaceManager
     */
    private $workspaceManager;

    /**
     * @var bool
     */
    private $purge;


    public function __construct(WorkspaceManager $workspaceManager, bool $purge = false)
    {
        $this->workspaceManager = $workspaceManager;
        $this->purge = $purge;
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

        if ($this->purge || $task->purgeWorkspace()) {
            yield $taskRunner->run(new PurgeDirectoryTask($workspace->absolutePath()), new Artifacts());
        }

        if (!file_exists($workspace->absolutePath())) {
            // if we don't yield, then the directory doesn't get created
            yield mkdir($workspace->absolutePath(), 0777, true);
        }

        return $workspace;
    }
}
