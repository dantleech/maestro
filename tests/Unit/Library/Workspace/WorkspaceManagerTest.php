<?php

namespace Maestro\Tests\Unit\Library\Workspace;

use Maestro\Library\Workspace\WorkspaceRegistry;
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

    private function create(string $namespace): WorkspaceManager
    {
        return new WorkspaceManager(
            new NestedDirectoryStrategy(),
            new WorkspaceRegistry(),
            $namespace,
            $this->workspace()->path('/')
        );
    }
}
