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
        $this->initPackage('one');
        $this->saveConfig([
            'packages' => [
                'phpactor/config-loader' => [
                    'manifest' => [
                        'one' => [
                            'type' => 'initialize',
                            'parameters' => [
                                'url' => $this->packageUrl('one')
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        $process = $this->command('execute "echo HelloWorld"');
        $this->assertProcessSuccess($process);
        $this->assertStringContainsString('HelloWorld', $process->getOutput());
    }

    public function testQueriesRepositories()
    {
        $this->initPackage('one');
        $this->saveConfig([
            'packages' => [
                'phpactor/config-loader' => [
                ],
                'phpactor/console-extension' => [
                ],
            ]
        ]);
        $process = $this->command('execute "echo HelloWorld" -t"phpactor/config-loader"');
        $this->assertProcessSuccess($process);
        $this->assertStringContainsString('HelloWorld', $process->getOutput());
    }
}
