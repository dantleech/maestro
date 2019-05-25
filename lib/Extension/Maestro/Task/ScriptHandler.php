<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Maestro\Script\ScriptRunner;
use Maestro\Task\Artifacts;
use Maestro\Task\Exception\TaskFailed;
use Maestro\Task\TaskHandler;
use Maestro\Extension\Maestro\Task\ScriptTask;

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

    public function __invoke(ScriptTask $script, Artifacts $artifacts): Promise
    {
        return \Amp\call(function () use ($script, $artifacts) {
            $path = $artifacts->get('workspace')->absolutePath();
            $env = $artifacts->get('env')->toArray();

            $result = yield $this->scriptRunner->run($script->script(), $path, $env);

            $artifacts = Artifacts::create([
                'exitCode' => $result->exitCode(),
                'stderr' => $result->lastStderr(),
                'stdout' => $result->lastStdout(),
            ]);

            if ($result->exitCode() !== 0) {
                throw new TaskFailed(sprintf(
                    'Exited with code "%s"',
                    $result->exitCode()
                ), $artifacts);
            }

            return $artifacts;
        });
    }
}
