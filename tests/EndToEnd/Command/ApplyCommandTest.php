<?php

namespace Maestro\Tests\EndToEnd\Command;

use Maestro\Tests\EndToEnd\EndToEndTestCase;

class ApplyCommandTest extends EndToEndTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testAppliesTemplates()
    {
        $this->workspace()->put(
            '/README.md',
            <<<'EOT'
Hello World
EOT
        );

        $this->initPackage('one');
        $this->saveConfig([
            'packages' => [
                'acme/package' => [
                    'manifest' => [
                        'foo' => [
                            'type' => 'checkout',
                            'parameters' => [
                                'url' => $this->packageUrl('one'),
                            ]
                        ],
                        'README.md' => [
                            'type' => 'template',
                            'parameters' => [
                                'from' => 'README.md',
                                'to' => 'README.md',
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        $process = $this->command('apply');
        $this->assertProcessSuccess($process);
        $this->assertFileExists($this->packageWorkspacePath('acme-package/README.md'));
    }

    public function testAppliesNamedTarget()
    {
        $this->workspace()->put(
            '/README1.md',
            <<<'EOT'
Hello World
EOT
        );
        $this->workspace()->put(
            '/README2.md',
            <<<'EOT'
Goodbye World
EOT
        );

        $this->initPackage('one');
        $this->saveConfig([
            'packages' => [
                'acme/package' => [
                    'manifest' => [
                        "initialize" => [
                            'type' => 'checkout',
                            'parameters' => [
                                'url' => $this->packageUrl('one'),
                            ]
                        ],
                        'one' => [
                            'type' => 'template',
                            'parameters' => [
                                'from' => 'README1.md',
                                'to' => 'README.md',
                            ]
                        ],
                        'two' => [
                            'type' => 'template',
                            'parameters' => [
                                'from' => 'README2.md',
                                'to' => 'README.md',
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        $process = $this->command('apply one');
        $this->assertProcessSuccess($process);
        $this->assertFileExists($this->packageWorkspacePath('acme-package/README.md'));
        $this->assertEquals('Hello World', file_get_contents($this->packageWorkspacePath('acme-package/README.md')));
    }
}
