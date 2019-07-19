<?php

namespace Maestro\Tests\Unit\Extension\Maestro\Task;

use Amp\Success;
use Maestro\Extension\Maestro\Task\GitHandler;
use Maestro\Extension\Maestro\Task\GitTask;
use Maestro\Script\EnvVars;
use Maestro\Script\ScriptResult;
use Maestro\Script\ScriptRunner;
use Maestro\Node\Environment;
use Maestro\Node\Exception\TaskFailed;
use Maestro\Node\Test\HandlerTester;
use Maestro\Workspace\Workspace;
use PHPUnit\Framework\TestCase;

class GitHandlerTest extends TestCase
{
    const EXAMPLE_URL = 'http://test_url';
    const EXAMPLE_WORKSPACE_ROOT = '/path/to/example/workspace-container';
    const EXAMPLE_WORKSPACE = self::EXAMPLE_WORKSPACE_ROOT . '/blah';
    const EXAMPLE_WORKSPACE_NAME = 'workspace_name';

    /**
     * @var ObjectProphecy
     */
    private $scriptRunner;

    protected function setUp(): void
    {
        $this->scriptRunner = $this->prophesize(ScriptRunner::class);
    }

    public function testRunsGitClone()
    {
        $this->scriptRunner->run(
            sprintf(
                'git clone %s %s',
                self::EXAMPLE_URL,
                self::EXAMPLE_WORKSPACE
            ),
            self::EXAMPLE_WORKSPACE_ROOT,
            []
        )->willReturn(new Success(new ScriptResult(0, '', '')))->shouldBeCalled();

        $environment = HandlerTester::create(
            new GitHandler(
                $this->scriptRunner->reveal(),
                self::EXAMPLE_WORKSPACE_ROOT,
                )
        )->handle(GitTask::class, [
            'url' => self::EXAMPLE_URL,
        ], [
            'workspace' => new Workspace(self::EXAMPLE_WORKSPACE, self::EXAMPLE_WORKSPACE_NAME),
            'env' => EnvVars::create([]),
        ]);

        $this->assertEquals(Environment::create([]), $environment, 'Returns no environment');
    }

    public function testFailsOnNonZeroExitCode()
    {
        $this->expectException(TaskFailed::class);
        $this->scriptRunner->run(
            sprintf(
                'git clone %s %s',
                self::EXAMPLE_URL,
                self::EXAMPLE_WORKSPACE
            ),
            self::EXAMPLE_WORKSPACE_ROOT,
            []
        )->willReturn(new Success(new ScriptResult(1, '', '')))->shouldBeCalled();

        $environment = HandlerTester::create(
            new GitHandler(
                $this->scriptRunner->reveal(),
                self::EXAMPLE_WORKSPACE_ROOT,
                )
        )->handle(GitTask::class, [
            'url' => self::EXAMPLE_URL,
        ], [
            'workspace' => new Workspace(self::EXAMPLE_WORKSPACE, self::EXAMPLE_WORKSPACE_NAME),
            'env' => EnvVars::create([]),
        ]);
    }

    public function testPurgeExistingProjectWorkspace()
    {
        $this->expectException(TaskFailed::class);
        $this->scriptRunner->run(
            sprintf(
                'git clone %s %s',
                self::EXAMPLE_URL,
                self::EXAMPLE_WORKSPACE
            ),
            self::EXAMPLE_WORKSPACE_ROOT,
            []
        )->willReturn(new Success(new ScriptResult(1, '', '')))->shouldBeCalled();

        $environment = HandlerTester::create(
            new GitHandler(
                $this->scriptRunner->reveal(),
                self::EXAMPLE_WORKSPACE_ROOT,
                )
        )->handle(GitTask::class, [
            'url' => self::EXAMPLE_URL,
        ], [
            'workspace' => new Workspace(self::EXAMPLE_WORKSPACE, self::EXAMPLE_WORKSPACE_NAME),
            'env' => EnvVars::create([]),
        ]);
    }
}
