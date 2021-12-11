<?php

namespace Maestro\Extension\Script\Task;

use Amp\Promise;
use Maestro\Library\Script\ScriptResult;
use Maestro\Library\Script\ScriptRunner;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Task\Exception\TaskFailure;
use Maestro\Library\Util\StringUtil;
use Maestro\Library\Workspace\Workspace;
use Maestro\Library\Workspace\WorkspaceRegistry;

class ScriptHandler
{
    /**
     * @var ScriptRunner
     */
    private $scriptRunner;

    /**
     * @var WorkspaceRegistry
     */
    private $workspaceRegistry;

    public function __construct(ScriptRunner $scriptRunner, WorkspaceRegistry $workspaceRegistry)
    {
        $this->scriptRunner = $scriptRunner;
        $this->workspaceRegistry = $workspaceRegistry;
    }

    public function __invoke(ScriptTask $script, Environment $environment = null, Workspace $workspace = null): Promise
    {
        $customWorkspace = $script->workspace();
        if ($customWorkspace) {
            $workspace = $this->workspaceRegistry->get($customWorkspace);
        }

        $environment = $environment ?: new Environment();
        return \Amp\call(function () use ($script, $environment, $workspace) {
            $result = yield $this->scriptRunner->run(
                $script->script(),
                $workspace ? $workspace->absolutePath() : null,
                $environment->toArray()
            );

            assert($result instanceof ScriptResult);

            if ($result->exitCode() !== 0) {
                throw new TaskFailure(sprintf(
                    'Exited with code "%s": %s',
                    $result->exitCode(),
                    StringUtil::firstLine($result->stderr())
                ), $result->exitCode());
            }

            return [
                $result
            ];
        });
    }
}
