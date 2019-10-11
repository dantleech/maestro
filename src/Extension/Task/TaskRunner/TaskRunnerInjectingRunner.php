<?php

namespace Maestro\Extension\Task\TaskRunner;

use Amp\Promise;
use Maestro\Library\Task\Artifact;
use Maestro\Library\Task\Artifacts;
use Maestro\Library\Task\Task;
use Maestro\Library\Task\TaskRunner;

class TaskRunnerInjectingRunner implements TaskRunner, Artifact
{
    /**
     * @var TaskRunner
     */
    private $innerTaskRunner;

    public function __construct(TaskRunner $innerTaskRunner)
    {
        $this->innerTaskRunner = $innerTaskRunner;
    }

    public function run(Task $task, Artifacts $artifacts): Promise
    {
        return $this->innerTaskRunner->run($task, $artifacts->spawnMutated(new Artifacts([
            $this
        ])));
    }
}
