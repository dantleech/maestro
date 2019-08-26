<?php

namespace Maestro\Extension\Maestro\Task;

use Amp\Promise;
use Maestro\Graph\Task;
use Maestro\Script\ScriptResult;
use Maestro\Script\ScriptRunner;
use Maestro\Graph\Environment;
use Maestro\Graph\Exception\TaskFailed;
use Maestro\Graph\TaskHandler;
use Maestro\Util\StringUtil;

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
            assert($result instanceof ScriptResult);

            if ($result->exitCode() !== 0) {
                throw new TaskFailed(sprintf(
                    'Exited with code "%s": %s',
                    $result->exitCode(),
                    StringUtil::firstLine($result->stderr())
                ), $result->exitCode());
            }

            return $environment;
        });
    }
}
