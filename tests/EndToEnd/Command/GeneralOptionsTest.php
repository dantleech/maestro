<?php

namespace Maestro\Tests\EndToEnd\Command;

use Maestro\Tests\EndToEnd\EndToEndTestCase;

class GeneralOptionsTest extends EndToEndTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->initPackage('one');
        $this->saveConfig([
            'packages' => [
                'phpactor/config-loader' => [
                    'manifest' => [
                        [
                            'type' => 'initialize',
                            'parameters' => [
                                'url' => $this->packageUrl('one')
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function testNonVerboseModeDoesNotShowStdOutAndStdErrOfAllConsoles()
    {
        $process = $this->command('execute "echo Hello"');
        $this->assertProcessSuccess($process);
        $this->assertStringNotContainsString('echo Hello', $process->getOutput());
    }

    public function testVerboseModeShowsStdOutAndStdErrOfAllConsoles()
    {
        $process = $this->command('execute "echo Hello" -v');
        $this->assertProcessSuccess($process);
        $this->assertStringContainsString('echo Hello', $process->getOutput());
    }
}
