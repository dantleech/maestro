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
        $this->workspace()->put('/README.md', <<<'EOT'
Hello World
EOT
        );

        $this->initPackage('one');
        $this->saveConfig([
            'packages' => [
                'acme/package' => [
                    'manifest' => [
                        [
                            'type' => 'initialize',
                            'url' => $this->packageUrl('one'),
                        ],
                        'README.md' => [
                            'type' => 'template',
                        ]
                    ]
                ]
            ]
        ]);
        $process = $this->command('apply');
        $this->assertProcessSuccess($process);
        $this->assertFileExists($this->packageWorkspacePath('acme-package/README.md'));
    }
}
