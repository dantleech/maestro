<?php

namespace Maestro\Tests\Unit\Extension\Maestro\Task;

use Maestro\Extension\Maestro\Task\ManifestHandler;
use Maestro\Extension\Maestro\Task\ManifestTask;
use Maestro\Node\Test\HandlerTester;
use PHPUnit\Framework\TestCase;

class ManifestHandlerTest extends TestCase
{
    public function testAddsEnvironment()
    {
        $environment = HandlerTester::create(new ManifestHandler())->handle(ManifestTask::class, [
            'path' => 'foobar',
            'environment' => [
                'hello' => 'goodbye',
            ],
        ], []);

        $this->assertEquals([
            'manifest.path' => 'foobar',
            'manifest.dir' => '',
            'hello' => 'goodbye',
        ], $environment->toArray());
    }
}
