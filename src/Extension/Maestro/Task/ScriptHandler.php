<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Maestro\Node\Task;
use Maestro\Script\ScriptRunner;
use Maestro\Node\Environment;
use Maestro\Node\Exception\TaskFailed;
use Maestro\Node\TaskHandler;

class ScriptHandler implements TaskHandler
{
    /**
     * @var ScriptRunner
     */
    private $scriptRunner;

    public function __construct(ScriptRunner $scriptRunner)
    {
        $this->scriptRunner = $scriptRunner;
    }

    public function execute(Task $script, Environment $environment): Promise
    {
        assert($script instanceof ScriptTask);
        return \Amp\call(function () use ($script, $environment) {
            $path = $environment->workspace()->absolutePath();
            $env = $environment->env()->toArray();

            $result = yield $this->scriptRunner->run($script->script(), $path, $env);

            $environment = $environment->builder()->withVars([
                'exitCode' => $result->exitCode(),
                'stderr' => $result->lastStderr(),
                'stdout' => $result->lastStdout(),
            ])->build();

            if ($result->exitCode() !== 0) {
                throw new TaskFailed(sprintf(
                    'Exited with code "%s"',
                    $result->exitCode()
                ), $environment);
            }

            return $environment;
        });
    }
}
