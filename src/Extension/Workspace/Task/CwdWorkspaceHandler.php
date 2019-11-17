<?php

namespace Maestro\Extension\Workspace\Task;

use Amp\Promise;
use Maestro\Library\Workspace\Workspace;
use Maestro\Library\Workspace\WorkspaceManager;
use function Amp\File\exists;
use Symfony\Component\Filesystem\Filesystem;
use Maestro\Extension\Workspace\Task\CwdWorkspaceTask;

class CwdWorkspaceHandler
{
    /**
     * @var string
     */
    private $workingDirectory;

    public function __construct(string $workingDirectory)
    {
        $this->workingDirectory = $workingDirectory;
    }

    public function __invoke(CwdWorkspaceTask $task): Promise
    {
        return \Amp\call(function () use ($task) {
            return [
                yield new Workspace($this->workingDirectory, $task->name())
            ];
        });
    }
}
