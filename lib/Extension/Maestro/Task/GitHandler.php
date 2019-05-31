<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Maestro\Script\ScriptResult;
use Maestro\Script\ScriptRunner;
use Maestro\Task\Artifacts;
use Maestro\Task\Exception\TaskFailed;
use Maestro\Task\TaskHandler;

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

    public function __invoke(GitTask $task, Artifacts $artifacts): Promise
    {
        return \Amp\call(function () use ($task, $artifacts) {
            $workspace = $artifacts->get('workspace');
            $env = $artifacts->get('env');

            $result = yield $this->runner->run(
                sprintf('git clone %s %s', $task->url(), $workspace->absolutePath()),
                $this->rootWorkspacePath,
                $env->toArray()
            );

<<<<<<< HEAD
            if ($result->exitCode() !== 0) {
                throw new TaskFailed(sprintf(
                    'Exited with code "%s"',
                    $result->exitCode()
                ), $artifacts);
=======
            assert($result instanceof ScriptResult);

            if ($result->exitCode() !== 0) {
                throw new TaskFailed(sprintf(
                    'Git clone failed with exit code "%s": %s',
                    $result->exitCode(), $result->lastStderr()
                ), Artifacts::create([
                    'exitCode' => $result->exitCode(),
                    'stderr' => $result->lastStderr(),
                    'stdout' => $result->lastStdout(),
                ]));
>>>>>>> Show per-package progress
            }

            return Artifacts::create([]);
        });
    }
}
