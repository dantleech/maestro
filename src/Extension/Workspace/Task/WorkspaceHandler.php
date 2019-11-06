<?php

namespace Maestro\Extension\Workspace\Task;

use Amp\Promise;
use Maestro\Library\Workspace\WorkspaceManager;
use function Amp\File\exists;
use Symfony\Component\Filesystem\Filesystem;

class WorkspaceHandler
{
    /**
     * @var WorkspaceManager
     */
    private $manager;

    public function __construct(WorkspaceManager $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(WorkspaceTask $task): Promise
    {
        return \Amp\call(function () use ($task) {
            $workspace = $this->manager->createNamedWorkspace($task->name());

            if (!yield exists($workspace->absolutePath())) {
                $filesystem = new Filesystem();
                $filesystem->mkdir($workspace->absolutePath());
            }

            return [
                $workspace
            ];
        });
    }
}
