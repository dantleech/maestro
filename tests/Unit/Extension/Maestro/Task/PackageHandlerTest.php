<?php

namespace Maestro\Tests\Unit\Extension\Maestro\Task;

use Maestro\Extension\Maestro\Task\PackageHandler;
use Maestro\Script\EnvVars;
use Maestro\Task\Artifacts;
use Maestro\Task\Task\PackageTask;
use Maestro\Tests\IntegrationTestCase;
use Maestro\Workspace\WorkspaceFactory;

class PackageHandlerTest extends IntegrationTestCase
{
    /**
     * @var WorkspaceFactory
     */
    private $workspaceFactory;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->workspaceFactory = new WorkspaceFactory('foobar', $this->workspace()->path('/'));
    }
    public function testProducesArtifacts()
    {
        $package = new PackageTask('hello');
        $artifacts = \Amp\Promise\wait((new PackageHandler($this->workspaceFactory))($package));
        $this->assertInstanceOf(Artifacts::class, $artifacts);
        $workspace = $this->workspaceFactory->createNamedWorkspace('hello');
        $this->assertEquals([
            'package' => $package,
            'workspace' => $workspace,
            'env' => EnvVars::create([
                'PACKAGE_WORKSPACE_PATH' => $workspace->absolutePath(),
                'PACKAGE_NAME' => 'hello'
            ])
        ], $artifacts->toArray());
    }
}
