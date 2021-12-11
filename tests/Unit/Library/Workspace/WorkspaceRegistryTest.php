<?php

namespace Maestro\Tests\Unit\Library\Workspace;

use Maestro\Library\Workspace\Exception\WorkspaceAlreadyRegistred;
use Maestro\Library\Workspace\Exception\WorkspaceNotFound;
use Maestro\Library\Workspace\Workspace;
use Maestro\Library\Workspace\WorkspaceRegistry;
use PHPUnit\Framework\TestCase;

class WorkspaceRegistryTest extends TestCase
{
    const EXAMPLE_PATH = 'dir';
    const EXAMPLE_WS_NAME = 'one';


    public function testThrowsExceptionIfWorkspaceAlreadyRegistered()
    {
        $this->expectException(WorkspaceAlreadyRegistred::class);
        $this->create([new Workspace(__DIR__, self::EXAMPLE_WS_NAME), new Workspace(__DIR__, self::EXAMPLE_WS_NAME)]);
    }

    public function testRegistersWorkspaces()
    {
        $ws = new Workspace(
            self::EXAMPLE_PATH,
            self::EXAMPLE_WS_NAME
        );
        $registry = $this->create();
        $registry->register($ws);
        $this->assertSame($ws, $registry->get(self::EXAMPLE_WS_NAME));
    }

    public function testThrowsExceptionIfWorkspaceNotExisting()
    {
        $this->expectException(WorkspaceNotFound::class);

        $ws = new Workspace(
            self::EXAMPLE_PATH,
            self::EXAMPLE_WS_NAME
        );
        $this->create()->get(self::EXAMPLE_WS_NAME);
    }

    private function create($workspaces = []): WorkspaceRegistry
    {
        return new WorkspaceRegistry(...$workspaces);
    }
}
