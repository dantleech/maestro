<?php

namespace Maestro\Tests\Integration\Extension\Process\Job;

use Maestro\Extension\Process\Job\PackageProcess;
use Maestro\Extension\Process\Job\PackageProcessHandler;
use Maestro\Extension\Process\Job\Process;
use Maestro\Model\Job\Queue;
use Maestro\Model\Job\Test\HandlerTester;
use Maestro\Model\Package\PackageDefinition;
use Maestro\Model\Package\Workspace;
use Maestro\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\TestCase;

class PackageProcessHandlerTest extends IntegrationTestCase
{
    /**
     * @var Workspace
     */
    private $workspace;
    /**
     * @var PackageProcessHandler
     */
    private $handler;
    /**
     * @var PackageDefinition
     */
    private $package;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->workspace = Workspace::create($this->workspace()->path(''));
        $this->handler = new PackageProcessHandler(
            $this->workspace
        );
        $this->package = new PackageDefinition('foobar/barfoo');
    }

    public function testCreatesProcessToBeRunInPackageWorkspace()
    {
        $queue = new Queue('one');

        HandlerTester::create($this->handler)->dispatch(PackageProcess::class, [
            'queue' => $queue,
            'packageDefinition' => $this->package,
            'command' => 'Hello',
        ]);

        $this->assertCount(1, $queue);
        $job = $queue->dequeue();
        $this->assertInstanceOf(Process::class, $job);
        $this->assertEquals('Hello', $job->command());
        $this->assertEquals($this->workspace->package($this->package)->path(), $job->workingDirectory());
    }
}
