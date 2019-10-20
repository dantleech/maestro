<?php

namespace Maestro\Tests\Unit\Extension\File\Task;

use Maestro\Extension\File\Task\Exception\CouldNotPurgeDirectory;
use Maestro\Extension\File\Task\PurgeDirectoryHandler;
use Maestro\Extension\File\Task\PurgeDirectoryTask;
use Maestro\Library\Task\Test\HandlerTester;
use Maestro\Tests\IntegrationTestCase;

class PurgeDirectoryHandlerTest extends IntegrationTestCase
{
    /**
     * @var Workspace
     */
    private $packageWorkspace;

    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testDoesNotPurgeBeyondWorkspace()
    {
        $this->expectException(CouldNotPurgeDirectory::class);

        HandlerTester::create(new PurgeDirectoryHandler(
            $this->workspace()->path('/rootPath')
        ))->handle(PurgeDirectoryTask::class, [
            'path' => $this->workspace()->path('/'),
        ], []);
    }

    public function testPurgesDirectory()
    {
        $fooPath = '/rootPath/foobar';
        $this->workspace()->put($fooPath, 'bar');
        $this->assertFileExists($this->workspace()->path($fooPath));

        HandlerTester::create(new PurgeDirectoryHandler(
            $this->workspace()->path('/rootPath')
        ))->handle(PurgeDirectoryTask::class, [
            'path' => $this->workspace()->path('/rootPath'),
        ], []);

        $this->assertFileNotExists($this->workspace()->path($fooPath));
    }

    public function testPurgesRootPath()
    {
        $fooPath = '/rootPath/foobar';
        $this->workspace()->put($fooPath, 'bar');
        $this->assertFileExists($this->workspace()->path($fooPath));

        HandlerTester::create(new PurgeDirectoryHandler(
            $this->workspace()->path('/rootPath')
        ))->handle(PurgeDirectoryTask::class, [
            'path' => $this->workspace()->path('/rootPath'),
        ], []);

        $this->assertFileNotExists($this->workspace()->path($fooPath));
    }
}
