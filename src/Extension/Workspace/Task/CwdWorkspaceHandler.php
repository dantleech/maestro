<?php

namespace Maestro\Extension\Workspace\Task;

use Amp\Promise;
use Maestro\Library\Workspace\Workspace;
use Maestro\Library\Workspace\WorkspaceManager;
use function Amp\File\exists;
use Maestro\Library\Workspace\WorkspaceRegistry;
use Symfony\Component\Filesystem\Filesystem;
use Maestro\Extension\Workspace\Task\CwdWorkspaceTask;

class CwdWorkspaceHandler
{
    /**
     * @var string
     */
    private $workingDirectory;

    /**
     * @var WorkspaceRegistry
     */
    private $registry;

    public function __construct(WorkspaceRegistry $registry, string $workingDirectory)
    {
        $this->workingDirectory = $workingDirectory;
        $this->registry = $registry;
    }

    public function __invoke(CwdWorkspaceTask $task): Promise
    {
        return \Amp\call(function () use ($task) {
            $workspace = new Workspace($this->workingDirectory, $task->name());
            $this->registry->register($workspace);
            return [
                $workspace
            ];
        });
    }
}
