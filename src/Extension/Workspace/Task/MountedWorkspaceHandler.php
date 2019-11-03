<?php

namespace Maestro\Extension\Workspace\Task;

use Amp\Promise;
use Maestro\Library\Task\Exception\TaskFailure;
use Maestro\Library\Workspace\WorkspaceManager;
use function Amp\File\symlink;
use function Amp\File\unlink;
use function Amp\File\exists;

class MountedWorkspaceHandler
{
    /**
     * @var WorkspaceManager
     */
    private $manager;

    public function __construct(WorkspaceManager $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(MountedWorkspaceTask $task): Promise
    {
        return \Amp\call(function () use ($task) {
            $hostWorkspace = $this->manager->createNamedWorkspace($task->host());
            $mountedWorkspace = $this->manager->createNamedWorkspace($task->name());
            $targetPath = $hostWorkspace->absolutePath($task->path());

            if (!yield exists($targetPath)) {
                throw new TaskFailure(sprintf(
                    'Path "%s" in workspace "%s" does not exist, is the workspace initialized?',
                    $task->path(), $task->host()
                ));
            }

            if (yield exists($mountedWorkspace->absolutePath())) {
                return [
                    $mountedWorkspace
                ];
            }

            yield symlink($targetPath, $mountedWorkspace->absolutePath());
            return [
                $mountedWorkspace
            ];
        });
    }
}
