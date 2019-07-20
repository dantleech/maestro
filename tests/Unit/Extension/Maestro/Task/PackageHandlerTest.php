<?php

namespace Maestro\Tests\Unit\Extension\Maestro\Task;

use Maestro\Extension\Maestro\Task\PackageHandler;
use Maestro\Loader\Instantiator;
use Maestro\Node\Environment;
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

    public function testProducesEnvironment()
    {
        $package = new PackageTask('hello');
        $environment = \Amp\Promise\wait((new PackageHandler($this->workspaceFactory))->execute($package, Environment::empty()));
        $this->assertInstanceOf(Environment::class, $environment);
        $workspace = $this->workspaceFactory->createNamedWorkspace('hello');

        $this->assertEquals($workspace, $environment->workspace());
        $this->assertEquals([
            'PACKAGE_WORKSPACE_PATH' => $workspace->absolutePath(),
            'PACKAGE_NAME' => 'hello'
        ], $environment->envVars()->toArray());

        $this->assertEquals([
            'package' => $package,
        ], $environment->vars());
    }

    public function testProvidesConfiguredVars()
    {
        $environment = HandlerTester::create(new PackageHandler($this->workspaceFactory))->handle(
            PackageTask::class,
            [
                'name' => 'foobar',
                'vars' => [
                    'bonjour' => 'aurevoir'
                ],
            ],
            []
        );

        $this->assertInstanceOf(Environment::class, $environment);

        $this->assertEquals('aurevoir', $environment->get('bonjour'));
    }

    public function testProvidesConfiguredEnvVars()
    {
        $environment = HandlerTester::create(new PackageHandler($this->workspaceFactory))->handle(
            PackageTask::class,
            [
                'name' => 'foobar',
                'env' => [
                    'BONJOUR' => 'aurevoir'
                ],
            ],
            []
        );

        $this->assertEquals('aurevoir', $environment->envVars()->get('BONJOUR'));
    }

    public function testPurgeWorkspace()
    {
        $package = Instantiator::create()->instantiate(PackageTask::class, [
            'name' => 'hello',
            'purgeWorkspace' => true
        ]);

        \Amp\Promise\wait((new PackageHandler($this->workspaceFactory))->execute($package, Environment::empty()));
        $workspace = $this->workspaceFactory->createNamedWorkspace('hello');

        file_put_contents($workspace->absolutePath() . '/README', 'Hello');
        \Amp\Promise\wait((new PackageHandler($this->workspaceFactory))->execute($package, Environment::empty()));
        $this->assertFileNotExists($workspace->absolutePath() . '/README');
    }
}
