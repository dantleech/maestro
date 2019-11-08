<?php

namespace Maestro\Tests\Unit\Library\Workspace;

use Maestro\Tests\IntegrationTestCase;
use Maestro\Library\Workspace\PathStrategy\NestedDirectoryStrategy;
use Maestro\Library\Workspace\WorkspaceManager;
use Phpactor\TestUtils\Workspace;

class WorkspaceManagerTest extends IntegrationTestCase
{
    const EMPTY_NAMESPACE = '';

    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var WorkspaceManager
     */
    private $factory;

    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    /**
     * @dataProvider provideName
     */
    public function testCreateWorkspace(string $namespace, string $name, string $expectedPath)
    {
        $workspace = $this->create($namespace)->createNamedWorkspace($name);
        $this->assertEquals($this->workspace()->path($expectedPath), $workspace->absolutePath());
    }

    public function provideName()
    {
        yield [
            'hello',
            'hello',
            'hello/hello'
        ];

        yield [
            'hello',
            'hello/world',
            'hello/hello/world'
        ];

        yield [
            'namespace/with/slashes',
            'vendor/my-package',
            'namespace/with/slashes/vendor/my-package',
        ];
    }

    public function testListsWorkspaces()
    {
        $this->workspace()->put('foobar/barfoo/README.md', self::EMPTY_NAMESPACE);
        $this->workspace()->put('foobar/foobar/README.md', self::EMPTY_NAMESPACE);
        $workspaces = $this->create(self::EMPTY_NAMESPACE)->listWorkspaces();
        $this->assertCount(2, $workspaces);
        $this->assertEquals('foobar/barfoo', $workspaces->first()->name());
    }

    private function create(string $namespace): WorkspaceManager
    {
        return new WorkspaceManager(new NestedDirectoryStrategy(), $namespace, $this->workspace()->path('/'));
    }
}
