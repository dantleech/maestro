<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Node\TaskContext;
use Maestro\Script\EnvVars;
use Maestro\Script\ScriptRunner;
use Maestro\Node\Artifacts;
use Maestro\Node\Exception\TaskFailed;
use Maestro\Node\TaskHandler;
use Maestro\Workspace\Workspace;

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

    public function __invoke(GitTask $task, TaskContext $context): Promise
    {
        return \Amp\call(function () use ($task, $context) {
            $workspace = $context->artifacts()->get('workspace');
            $env = $context->artifacts()->get('env');
            assert($env instanceof EnvVars);
            assert($workspace instanceof Workspace);

            if ($this->isGitRepository($workspace->absolutePath())) {
                return new Success();
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
                    $result->lastStderr()
                ), Artifacts::create([
                    'exitCode' => $result->exitCode(),
                    'stderr' => $result->lastStderr(),
                    'stdout' => $result->lastStdout(),
                ]));
            }

            return Artifacts::create([]);
        });
    }

    private function isGitRepository(string $path): bool
    {
        return file_exists(sprintf('%s/.git', $path));
    }
}
