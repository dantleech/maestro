<?php

namespace Maestro\Tests\Unit\Extension\Maestro\Task;

use Maestro\Extension\Maestro\Task\PackageHandler;
use Maestro\Task\Artifacts;
use Maestro\Task\Task\PackageTask;
use Maestro\Tests\IntegrationTestCase;
use Maestro\Workspace\WorkspaceFactory;
use PHPUnit\Framework\TestCase;

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
        $artifacts = (new PackageHandler($this->workspaceFactory))($package);
        $this->assertInstanceOf(Artifacts::class, $artifacts);
        $this->assertEquals([
            'package' => $package,
            'workspace' => $this->workspaceFactory->createNamedWorkspace('hello')
        ], $artifacts->toArray());
    }
}
