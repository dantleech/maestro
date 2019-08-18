<?php

namespace Maestro\Extension\Git\Task;

use Amp\Promise;
use Maestro\Extension\Git\Model\Git;
use Maestro\Graph\Environment;
use Maestro\Graph\Task;
use Maestro\Graph\TaskHandler;

class GitTagHandler implements TaskHandler
{
    /**
     * @var Git
     */
    private $git;

    public function __construct(Git $git)
    {
        $this->git = $git;
    }

    public function execute(Task $task, Environment $environment): Promise
    {
        assert($task instanceof GitTagTask);

        return \Amp\call(function () use ($task, $environment) {
            $path = $environment->workspace()->absolutePath();
            $env = $environment->env()->toArray();
            $existingTags = yield $this->git->listTags($environment->workspace()->absolutePath());

            if (!in_array($task->tagName(), $existingTags)) {
                return $environment;
            }

            $this->git->tag($environment->workspace()->absolutePath(), $task->tagName());

            return $environment;
        });
    }
}
