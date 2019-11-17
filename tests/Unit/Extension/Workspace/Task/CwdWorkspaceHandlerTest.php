<?php

namespace Maestro\Tests\Unit\Extension\Workspace\Task;

use Maestro\Extension\Workspace\Task\CwdWorkspaceHandler;
use Maestro\Extension\Workspace\Task\CwdWorkspaceTask;
use Maestro\Extension\Workspace\Task\MountedWorkspaceHandler;
use Maestro\Library\Task\Test\HandlerTester;
use Maestro\Library\Workspace\Workspace;
use Maestro\Library\Workspace\WorkspaceRegistry;
use Maestro\Tests\IntegrationTestCase;

class CwdWorkspaceHandlerTest extends IntegrationTestCase
{
    const EXAMPLE_WS_NAME = 'test_workspace';

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
        $this->registry = new WorkspaceRegistry();
        $this->handler = new CwdWorkspaceHandler(
            $this->registry,
            $this->workspace()->path('/')
        );
    }

    public function testCreates()
    {
        $responses = HandlerTester::create($this->handler)->handle(CwdWorkspaceTask::class, [
            'name' => self::EXAMPLE_WS_NAME,
        ]);
        $workspace = $responses->get(Workspace::class);
        $this->assertInstanceOf(Workspace::class, $workspace);
        $this->assertSame($workspace, $this->registry->get(self::EXAMPLE_WS_NAME));
    }
}
