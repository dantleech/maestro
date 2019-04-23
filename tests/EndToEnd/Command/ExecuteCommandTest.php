<?php

namespace Maestro\Tests\EndToEnd\Command;

use Maestro\Tests\EndToEnd\EndToEndTestCase;

class ExecuteCommandTest extends EndToEndTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testExecuteHelloWorld()
    {
        $this->saveConfig([
            'packages' => [
                'phpactor/config-loader' => []
            ]
        ]);
        $process = $this->command('execute "echo HelloWorld"');
        $this->assertProcessSuccess($process);
        $this->assertStringContainsString('HelloWorld', $process->getOutput());
    }

    public function testResetsRepositories()
    {
        $this->saveConfig([
            'packages' => [
                'phpactor/config-loader' => []
            ]
        ]);
        $process = $this->command('execute "echo HelloWorld" --reset');
        $this->assertProcessSuccess($process);
        $this->assertStringContainsString('HelloWorld', $process->getOutput());
    }

    public function testQueriesRepositories()
    {
        $this->saveConfig([
            'packages' => [
                'phpactor/config-loader' => [],
                'phpactor/console-extension' => [],
            ]
        ]);
        $process = $this->command('execute "echo HelloWorld" -t"phpactor/config-loader"');
        $this->assertProcessSuccess($process);
        $this->assertStringContainsString('HelloWorld', $process->getOutput());
    }
}
