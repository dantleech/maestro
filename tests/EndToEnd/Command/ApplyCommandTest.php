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
                    'url' => $this->packageUrl('one'),
                    'manifest' => [
                        'README.md' => [
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