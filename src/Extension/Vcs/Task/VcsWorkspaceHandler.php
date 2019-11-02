<?php

namespace Maestro\Extension\Vcs\Task;

use Amp\Promise;
use Maestro\Library\Artifact\Artifacts;
use Maestro\Library\Instantiator\Instantiator;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Task\TaskRunner;
use Maestro\Library\Workspace\Workspace;
use Maestro\Library\Workspace\WorkspaceManager;

class VcsWorkspaceHandler
{
    /**
     * @var WorkspaceManager
     */
    private $workspaceManager;

    public function __construct(WorkspaceManager $workspaceManager)
    {
        $this->workspaceManager = $workspaceManager;
    }

    public function __invoke(VcsWorkspaceTask $task, TaskRunner $taskRunner): Promise
    {
        return \Amp\call(function () use ($task, $taskRunner) {
            $workspace = $this->workspaceManager->createNamedWorkspace($task->name());

            yield $taskRunner->run(Instantiator::instantiate(CheckoutTask::class, [
                'url' => $task->url(),
                'update' => $task->update(),
            ]), 
                new Artifacts([$workspace])
            );

            return [
                $workspace
            ];
        });
    }
}
