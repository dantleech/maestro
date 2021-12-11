<?php

namespace Maestro\Extension\Workspace\Task;

use Amp\Promise;
use Maestro\Library\Workspace\Workspace;
use Maestro\Library\Workspace\WorkspaceRegistry;

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
