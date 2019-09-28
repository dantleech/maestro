<?php

namespace Maestro\Extension\Runner\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Support\Package\Package;
use Maestro\Library\Workspace\Workspace;
use Maestro\Library\Workspace\WorkspaceManager;

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

    public function __invoke(PackageInitTask $task, Environment $enivonment): Promise
    {
        return new Success([
            $enivonment->spawnMerged([
                'PACKAGE_NAME' => $task->name()
            ]),
            new Package(
                $task->name(),
                $task->version()
            ),
            $this->createWorkspace($task)
        ]);
    }

    private function createWorkspace(PackageInitTask $task): Workspace
    {
        $workspace = $this->workspaceManager->createNamedWorkspace($task->name());

        if ($task->purgeWorkspace()) {
            $workspace->purge();
        }

        if (!file_exists($workspace->absolutePath())) {
            mkdir($workspace->absolutePath(), 0777, true);
        }

        return $workspace;
    }
}
