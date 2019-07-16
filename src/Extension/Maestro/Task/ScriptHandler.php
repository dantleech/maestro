<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Maestro\Node\TaskContext;
use Maestro\Script\ScriptRunner;
use Maestro\Node\Artifacts;
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

    public function __invoke(ScriptTask $script, TaskContext $context): Promise
    {
        return \Amp\call(function () use ($script, $context) {
            $path = $context->artifacts()->get('workspace')->absolutePath();
            $env = $context->artifacts()->get('env')->toArray();

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
