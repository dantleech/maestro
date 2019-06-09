<?php

namespace Maestro\Tests\Unit\Extension\Maestro\Task;

use Maestro\Extension\Maestro\Task\ManifestHandler;
use Maestro\Extension\Maestro\Task\ManifestTask;
use Maestro\Task\Test\HandlerTester;
use PHPUnit\Framework\TestCase;

class ManifestHandlerTest extends TestCase
{
    public function testAddsArtifacts()
    {
        $artifacts = HandlerTester::create(new ManifestHandler())->handle(ManifestTask::class, [
            'path' => 'foobar',
            'artifacts' => [
                'hello' => 'goodbye',
            ],
        ], []);

        $this->assertEquals([
            'manifest.path' => 'foobar',
            'manifest.dir' => '',
            'hello' => 'goodbye',
        ], $artifacts->toArray());
    }
}
