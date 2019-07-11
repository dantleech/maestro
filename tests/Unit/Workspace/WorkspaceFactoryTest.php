<?php

namespace Maestro\Tests\Unit\Workspace;

use Maestro\Tests\IntegrationTestCase;
use Maestro\Workspace\PathStrategy\NestedDirectoryStrategy;
use Maestro\Workspace\WorkspaceFactory;
use Phpactor\TestUtils\Workspace;

class WorkspaceFactoryTest extends IntegrationTestCase
{
    /**
     * @var Workspace
     */
    private $workspace;
    /**
     * @var WorkspaceFactory
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
        $this->workspace()->put('foobar/barfoo/README.md', '');
        $this->workspace()->put('foobar/foobar/README.md', '');
        $workspaces = $this->create('')->listWorkspaces();
        $this->assertCount(2, $workspaces);
        $this->assertEquals('foobar/barfoo', $workspaces->first()->name());
    }

    private function create(string $namespace): WorkspaceFactory
    {
        return new WorkspaceFactory(new NestedDirectoryStrategy(), $namespace, $this->workspace()->path('/'));
    }
}
