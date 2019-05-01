<?php

namespace Maestro\Tests\Integration\Extension\Process\Job;

use Maestro\Extension\Process\Job\Checkout;
use Maestro\Extension\Process\Job\CheckoutHandler;
use Maestro\Extension\Process\Job\Process;
use Maestro\Model\Job\Queue;
use Maestro\Model\Job\Test\HandlerTester;
use Maestro\Model\Package\PackageDefinition;
use Maestro\Model\Package\Workspace;
use Maestro\Tests\Integration\IntegrationTestCase;
use Webmozart\PathUtil\Path;

class CheckoutHandlerTest extends IntegrationTestCase
{
    /**
     * @var CheckoutHandler
     */
    private $handler;

    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var PackageDefinition
     */
    private $package;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->workspace = Workspace::create($this->workspace()->path(''));
        $this->handler = new CheckoutHandler(
            $this->workspace
        );
        $this->package = new PackageDefinition('foobar/barfoo');
    }

    public function testPurgesPackageBeforeCheckingOut()
    {
        $filePath = $this->workspace->package($this->package)->path();
        mkdir($filePath, 0777);
        $filePath .= '/foobar';
        file_put_contents($filePath, 'hello');

        $queue = new Queue('one');

        HandlerTester::create($this->handler)->dispatch(Checkout::class, [
            'queue' => $queue,
            'packageDefinition' => $this->package,
            'purge' => true,
        ]);

        $this->assertFileNotExists($filePath);
    }

    public function testNoOpIfPackageWorkspaceAlreadyExists()
    {
        $filePath = $this->workspace->package($this->package)->path();
        mkdir($filePath, 0777);
        $queue = new Queue('one');

        HandlerTester::create($this->handler)->dispatch(Checkout::class, [
            'queue' => $queue,
            'packageDefinition' => $this->package,
        ]);

        $this->assertCount(0, $queue);
    }

    public function testCreatesCheckoutJob()
    {
        $filePath = $this->workspace->package($this->package)->path();
        $queue = new Queue('one');

        HandlerTester::create($this->handler)->dispatch(Checkout::class, [
            'queue' => $queue,
            'packageDefinition' => $this->package,
            'url' => 'https://myawesomecheckout.com',
        ]);

        $this->assertCount(1, $queue);
        $job = $queue->dequeue();
        $this->assertInstanceOf(Process::class, $job);
        $this->assertStringContainsString('git clone https://myawesomecheckout.com', $job->command());
        $this->assertEquals(
            Path::normalize($this->workspace()->path('')),
            Path::normalize($job->workingDirectory()),
            'Checkout should happen in main working directory'
        );
    }

    public function testDefaultsToGithubIfNoUrlSpecified()
    {
        $filePath = $this->workspace->package($this->package)->path();
        $queue = new Queue('one');

        HandlerTester::create($this->handler)->dispatch(Checkout::class, [
            'queue' => $queue,
            'packageDefinition' => $this->package,
        ]);

        $this->assertCount(1, $queue);
        $job = $queue->dequeue();
        $this->assertInstanceOf(Process::class, $job);
        $this->assertStringContainsString(
            'git@github.com:' . $this->package->name(),
            $job->command()
        );
    }
}
