<?php

namespace Maestro\Tests\Unit\Extension\Maestro\Task;

use Amp\Success;
use Maestro\Extension\Maestro\Task\ScriptHandler;
use Maestro\Extension\Maestro\Task\ScriptTask;
use Maestro\Node\EnvVars;
use Maestro\Script\ScriptResult;
use Maestro\Script\ScriptRunner;
use Maestro\Node\Environment;
use Maestro\Node\Exception\TaskFailed;
use Maestro\Node\Test\HandlerTester;
use Maestro\Tests\IntegrationTestCase;
use Maestro\Workspace\Workspace;

class ScriptHandlerTest extends IntegrationTestCase
{
    const EXAMPLE_SCRIPT = 'echo Hello';

    /**
     * @var ObjectProphecy
     */
    private $scriptRunner;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->scriptRunner = $this->prophesize(ScriptRunner::class);
    }

    public function testExecutesSuccessfulScript()
    {
        $this->primeScriptRunner(0, 'Yes', 'No');

        $workspace = new Workspace($this->workspace()->path('/'), 'test');
        $environment = HandlerTester::create(
            new ScriptHandler(
                $this->scriptRunner->reveal(),
                )
        )->handle(ScriptTask::class, [
            'script' => self::EXAMPLE_SCRIPT,
        ], [
            'workspace' => $workspace,
        ]);

        $this->assertEquals(Environment::create([
            'workspace' => $workspace
        ]), $environment, 'Returns no environment');
    }

    public function testFailsOnNonZeroExitCode()
    {
        $this->expectException(TaskFailed::class);

        $this->primeScriptRunner(1, 'Yes', 'No');

        try {
            $environment = HandlerTester::create(
                new ScriptHandler(
                    $this->scriptRunner->reveal(),
                    )
            )->handle(ScriptTask::class, [
                'script' => self::EXAMPLE_SCRIPT,
            ], [
                'workspace' => new Workspace($this->workspace()->path('/'), 'test'),
                'env' => EnvVars::create([]),
            ]);
        } catch (TaskFailed $failed) {
            $this->assertEquals(1, $failed->getCode());
            throw $failed;
        }
    }

    private function primeScriptRunner(int $exitCode, string $stdout, string $stderr)
    {
        $this->scriptRunner->run(
            self::EXAMPLE_SCRIPT,
            $this->workspace()->path('/'),
            []
        )->willReturn(new Success(new ScriptResult($exitCode, $stdout, $stderr)))->shouldBeCalled();
    }
}
