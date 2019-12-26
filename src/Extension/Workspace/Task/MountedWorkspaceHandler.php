<?php

namespace Maestro\Extension\Workspace\Task;

use Amp\Promise;
use Maestro\Library\Task\Exception\TaskFailure;
use Maestro\Library\Workspace\Workspace;
use Maestro\Library\Workspace\WorkspaceManager;
use function Amp\File\symlink;
use function Amp\File\exists;
use Maestro\Library\Workspace\WorkspaceRegistry;

class MountedWorkspaceHandler
{
    /**
     * @var WorkspaceManager
     */
    private $manager;

    /**
     * @var WorkspaceRegistry
     */
    private $workspaceRegistry;

    public function __construct(WorkspaceManager $manager, WorkspaceRegistry $workspaceRegistry)
    {
        $this->manager = $manager;
        $this->workspaceRegistry = $workspaceRegistry;
    }

    public function __invoke(MountedWorkspaceTask $task): Promise
    {
        return \Amp\call(function () use ($task) {
            $hostWorkspace = $this->workspaceRegistry->get($task->host());
            $mountedWorkspace = $this->manager->createNamedWorkspace($task->name());

            if (!yield exists($hostWorkspace->absolutePath('/'))) {
                throw new TaskFailure(sprintf(
                    'Host worksapce "%s" does not exist',
                    $task->host()
                ));
            }

            $targetPath = $hostWorkspace->absolutePath($task->path());

            if (!yield exists($targetPath)) {
                throw new TaskFailure(sprintf(
                    'Path "%s" in workspace "%s" does not exist, is the workspace initialized?',
                    $task->path(),
                    $task->host()
                ));
            }

            if (yield exists($mountedWorkspace->absolutePath())) {
                return $this->handleExisting($mountedWorkspace, $targetPath);
            }

            yield symlink($targetPath, $mountedWorkspace->absolutePath());
            return [
                $mountedWorkspace
            ];
        });
    }

    private function handleExisting(Workspace $mountedWorkspace, string $targetPath): array
    {
        // no async function for this...
        if (false === is_link($mountedWorkspace->absolutePath())) {
            throw new TaskFailure(sprintf(
                'Mounted workspace path "%s" already exists and is not a link',
                $mountedWorkspace->absolutePath()
            ));
        }

        return [
            $mountedWorkspace
        ];
    }
}
