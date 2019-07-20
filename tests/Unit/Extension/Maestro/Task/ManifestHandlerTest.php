<?php

namespace Maestro\Tests\Unit\Extension\Maestro\Task;

use Maestro\Extension\Maestro\Task\ManifestHandler;
use Maestro\Extension\Maestro\Task\ManifestTask;
use Maestro\Node\Test\HandlerTester;
use Maestro\Script\EnvVars;
use PHPUnit\Framework\TestCase;

class ManifestHandlerTest extends TestCase
{
    public function testAddsVarsAndEnv()
    {
        $environment = HandlerTester::create(new ManifestHandler())->handle(ManifestTask::class, [
            'path' => 'foobar',
            'vars' => [
                'hello' => 'goodbye',
            ],
            'env' => [
                'HELLO' => 'goodbye',
            ],
        ], []);

        $this->assertEquals([
            'manifest.path' => 'foobar',
            'manifest.dir' => '',
            'hello' => 'goodbye',
        ], $environment->vars());

        $this->assertEquals(EnvVars::create(['HELLO' => 'goodbye']), $environment->envVars());
    }
}
