<?php

namespace Maestro\Tests\Unit\Extension\Workspace\Task;

use Maestro\Extension\Workspace\Task\MountedWorkspaceHandler;
use Maestro\Extension\Workspace\Task\MountedWorkspaceTask;
use Maestro\Library\Task\Exception\TaskFailure;
use Maestro\Library\Task\Test\HandlerTester;
use Maestro\Library\Workspace\Workspace;
use Maestro\Library\Workspace\WorkspaceManager;
use Maestro\Library\Workspace\WorkspaceRegistry;
use Maestro\Tests\IntegrationTestCase;
use Prophecy\Argument;

class MountedWorkspaceHandlerTest extends IntegrationTestCase
{
    const EXAMPLE_WS_NAME = 'test_workspace';
    const EXAMPLE_HOST_WS = 'host_workspace';

    /**
     * @var WorkspaceManager|ObjectProphecy
     */
    private $workspaceManager;

    /**
     * @var MountedWorkspaceHandler
     */
    private $handler;

    /**
     * @var WorkspaceRegistry
     */
    private $registry;


    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->workspaceManager = $this->prophesize(WorkspaceManager::class);
        $workspace = $this->workspace();
        $this->registry = new WorkspaceRegistry(...[
            new Workspace($this->workspace()->path(self::EXAMPLE_HOST_WS), self::EXAMPLE_HOST_WS)
        ]);

        $this->workspaceManager->createNamedWorkspace(Argument::any())->will(function ($args) use ($workspace) {
            $workspace = new Workspace($workspace->path('/'.$args[0]), $args[0]);

            return $workspace;
        });

        $this->handler = new MountedWorkspaceHandler($this->workspaceManager->reveal(), $this->registry);
    }

    public function testFailsIfHostPathNotExists()
    {
        $this->expectException(TaskFailure::class);
        $this->workspace()->mkdir(self::EXAMPLE_HOST_WS);

        HandlerTester::create($this->handler)->handle(MountedWorkspaceTask::class, [
            'name' => self::EXAMPLE_WS_NAME,
            'host' => self::EXAMPLE_HOST_WS,
            'path' => '/not/existing',
        ]);
    }

    public function testFailsIfExistingWorkspaceIsNotASymlink()
    {
        $this->expectException(TaskFailure::class);
        $this->expectExceptionMessage('already exists and is not');

        $this->workspace()->put(self::EXAMPLE_WS_NAME, 'FOOBAR');
        $this->workspace()->mkdir(self::EXAMPLE_HOST_WS);

        $responses = HandlerTester::create($this->handler)->handle(MountedWorkspaceTask::class, [
            'name' => self::EXAMPLE_WS_NAME,
            'host' => self::EXAMPLE_HOST_WS,
            'path' => '/',
        ]);
    }

    public function testCreatesSymlinkOnHostSystem()
    {
        $this->workspace()->mkdir(self::EXAMPLE_HOST_WS . '/sub/path');
        $this->workspace()->put(self::EXAMPLE_HOST_WS . '/sub/path/foobar', 'FOOBAR');

        $responses = HandlerTester::create($this->handler)->handle(MountedWorkspaceTask::class, [
            'name' => self::EXAMPLE_WS_NAME,
            'host' => self::EXAMPLE_HOST_WS,
            'path' => '/sub/path',
        ]);
        $workspace = $responses->get(Workspace::class);
        $this->assertInstanceOf(Workspace::class, $workspace);
        $this->assertEquals('FOOBAR', file_get_contents($workspace->absolutePath('foobar')));
    }
}
