<?php

namespace Maestro\Tests\Unit\Extension\Maestro\Task;

use Amp\Success;
use Maestro\Extension\Maestro\Task\ScriptHandler;
use Maestro\Extension\Maestro\Task\ScriptTask;
use Maestro\Script\EnvVars;
use Maestro\Script\ScriptResult;
use Maestro\Script\ScriptRunner;
use Maestro\Task\Artifacts;
use Maestro\Task\Test\HandlerTester;
use Maestro\Tests\IntegrationTestCase;
use Maestro\Workspace\Workspace;
use PHPUnit\Framework\TestCase;

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
        $this->scriptRunner->run(
            self::EXAMPLE_SCRIPT,
            $this->workspace()->path('/'),
            []
        )->willReturn(new Success(new ScriptResult(0, 'Yes', 'No')))->shouldBeCalled();

        $artifacts = HandlerTester::create(
            new ScriptHandler(
                $this->scriptRunner->reveal(),
            )
        )->handle(ScriptTask::class, [
            'script' => self::EXAMPLE_SCRIPT,
        ], [
            'workspace' => new Workspace($this->workspace()->path('/')),
            'env' => EnvVars::create([]),
        ]);

        $this->assertEquals(Artifacts::create([
            'exitCode' => 0,
            'stdout' => 'Yes',
            'stderr' => 'No'
        ]), $artifacts, 'Returns no artifacts');
    }
}
