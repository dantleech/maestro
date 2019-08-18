<?php

namespace Maestro\Extension\Git\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Graph\Task;
use Maestro\Script\ScriptRunner;
use Maestro\Graph\Environment;
use Maestro\Graph\Exception\TaskFailed;
use Maestro\Graph\TaskHandler;

class GitHandler implements TaskHandler
{
    /**
     * @var ScriptRunner
     */
    private $runner;

    /**
     * @var string
     */
    private $rootWorkspacePath;

    public function __construct(ScriptRunner $runner, string $rootWorkspacePath)
    {
        $this->runner = $runner;
        $this->rootWorkspacePath = $rootWorkspacePath;
    }

    public function execute(Task $task, Environment $environment): Promise
    {
        assert($task instanceof GitTask);
        return \Amp\call(function () use ($task, $environment) {
            $workspace = $environment->workspace();
            $env = $environment->env();

            if ($this->isGitRepository($workspace->absolutePath())) {
                return new Success($environment);
            }

            $result = yield $this->runner->run(
                sprintf('git clone %s %s', $task->url(), $workspace->absolutePath()),
                $this->rootWorkspacePath,
                $env->toArray()
            );

            if ($result->exitCode() !== 0) {
                throw new TaskFailed(sprintf(
                    'Git clone failed with exit code "%s": %s',
                    $result->exitCode(),
                    $result->stderr()
                ), $result->exitCode());
            }

            return $environment;
        });
    }

    private function isGitRepository(string $path): bool
    {
        return file_exists(sprintf('%s/.git', $path));
    }
}
