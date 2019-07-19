<?php

namespace Maestro\Tests\Unit\Extension\Maestro\Task;

use Maestro\Extension\Maestro\Task\PackageHandler;
use Maestro\Loader\Instantiator;
use Maestro\Script\EnvVars;
use Maestro\Node\Artifacts;
use Maestro\Extension\Maestro\Task\PackageTask;
use Maestro\Node\Test\HandlerTester;
use Maestro\Tests\IntegrationTestCase;
use Maestro\Workspace\PathStrategy\NestedDirectoryStrategy;
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
        $this->workspaceFactory = new WorkspaceFactory(
            new NestedDirectoryStrategy(),
            'foobar',
            $this->workspace()->path('/')
        );
    }

    public function testProducesArtifacts()
    {
        $package = new PackageTask('hello');
        $artifacts = \Amp\Promise\wait((new PackageHandler($this->workspaceFactory))->execute($package, Artifacts::empty()));
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

    public function testProvidesConfiguredArtifacts()
    {
        $artifacts = HandlerTester::create(new PackageHandler($this->workspaceFactory))->handle(
            PackageTask::class,
            [
                'name' => 'foobar',
                'artifacts' => [
                    'bonjour' => 'aurevoir'
                ],
            ],
            []
        );

        $this->assertInstanceOf(Artifacts::class, $artifacts);
        $this->assertEquals('aurevoir', $artifacts->get('bonjour'));
    }

    public function testPurgeWorkspace()
    {
        $package = Instantiator::create()->instantiate(PackageTask::class, [
            'name' => 'hello',
            'purgeWorkspace' => true
        ]);

        \Amp\Promise\wait((new PackageHandler($this->workspaceFactory))->execute($package, Artifacts::empty()));
        $workspace = $this->workspaceFactory->createNamedWorkspace('hello');

        file_put_contents($workspace->absolutePath() . '/README', 'Hello');
        \Amp\Promise\wait((new PackageHandler($this->workspaceFactory))->execute($package, Artifacts::empty()));
        $this->assertFileNotExists($workspace->absolutePath() . '/README');
    }
}
